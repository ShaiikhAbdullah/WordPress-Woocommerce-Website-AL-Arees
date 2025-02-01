<?php

namespace TenWebIO;

use TenWebQueue\DTO\QueueConfigDTO;
use TenWebQueue\DTO\QueueDataDTO;
use TenWebQueue\QueueOrchestrator;
use TenWebQueue\Exceptions\QueueException;
use \TenWebQueue\QueueContext;

class CompressService
{
    const QUEUE_NAME              = 'tenweb_image_optimizer';
    const QUEUE_RESTART_FILE_NAME = 'tenweb_image_optimizer_restart';
    /**
     * @var CompressDataService
     */
    private $compress_data;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var int
     */
    private $restart = 0;

    /**
     * @var string
     */
    private $restart_route;


    /**
     * @var array
     */
    private $restart_body;

    /**
     * @var array
     */
    private $images = array();

    /**
     * @param int $restart
     */
    public function __construct($restart = 0)
    {
        $this->compress_data = new CompressDataService();
        $this->config = new Config();
        $this->restart = $restart;
    }

    /**
     *
     * @param bool $force
     *
     * @return bool
     * @throws QueueException
     */
    public function compressBulk($force = false)
    {
        if ($this->ifRunningCompressExist() && !$this->restart) {
            Utils::finishQueue('bulk', false, false);

            return false;
        }
        ini_set('max_execution_time', 5000);
        if (!$this->restart) {
            $attachments = $this->compress_data->getImagesReadyForOptimize($force, true);
            if ($attachments instanceof Attachments) {
                $this->images = $attachments->getData();
            }
            if (empty($this->images)) {
                Utils::finishQueue('bulk', false, false);

                return false;
            }
        }
        $this->restart_route = add_query_arg(array('rest_route' => '/tenwebio/v2/compress'), get_home_url() . "/");
        $this->restart_body['force'] = $force;
        $this->compress('bulk');

        return true;
    }

    /**
     *
     * @param        $image_urls
     * @param        $page_id
     * @param int    $only_convert_webp
     * @param string $origin
     *
     * @return bool
     * @throws QueueException
     */
    public function compressCustom($image_urls, $page_id, $only_convert_webp = 0, $origin = 'io')
    {
        if (!$this->restart) {
            $attachments = new Attachments();
            $this->images = $attachments->getCustomAttachmentsData($image_urls);
            if (empty($this->images)) {
                Utils::finishQueue('custom_' . $page_id, false, false);

                return false;
            }
        }
        $this->restart_route = add_query_arg(array('rest_route' => '/tenwebio/v2/compress-custom'), get_home_url() . "/");
        $this->restart_body['page_id'] = $page_id;
        $this->restart_body['only_convert_webp'] = $only_convert_webp;
        $this->restart_body['origin'] = $origin;
        update_site_option(TENWEBIO_PREFIX . '_custom_compress_only_convert_webp_' . $page_id, $only_convert_webp);
        update_site_option(TENWEBIO_PREFIX . '_custom_compress_origin_' . $page_id, $origin);
        $this->compress('custom_' . $page_id);

        return true;
    }

    /**
     * @param $post_id
     *
     * @return bool
     * @throws QueueException
     */
    public function compressOne($post_id)
    {
        if (!$this->restart) {
            $this->images = $this->compress_data->getImageReadyForOptimize($post_id)->getData();
            if (empty($this->images)) {
                Utils::finishQueue('single_' . $post_id, false, false);

                return false;
            }
        }
        $this->restart_route = add_query_arg(array('rest_route' => '/tenwebio/v2/compress-one'), get_home_url() . "/");
        $this->compress('single_' . $post_id);

        return true;
    }

    /**
     * @param     $type
     *
     * @return void
     * @throws QueueException
     */
    public function compress($type)
    {
        Logs::setLog("compress:" . $type . ":restart", $this->restart);
        $queue = $this->createQueue($type);

        if (!$this->restart) {
            Utils::deleteQueueTransients($type, false);
            update_site_option(TENWEBIO_PREFIX . '_compress_images_count_' . $type, count($this->images));
            $queue->enqueue($this->images);
        }
        $queue->dequeue();
    }

    /***
     * @param $type
     *
     * @return QueueContext
     * @throws QueueException
     */
    public function createQueue($type)
    {
        $queue_dir = Utils::getQueueDir($type);
        $this->restart_body['restart'] = 1;
        $this->restart_body['tenwebio_nonce'] = wp_create_nonce('tenwebio_rest');

        return QueueOrchestrator::initQueue(new QueueConfigDTO([
            'connection_type'         => 'file',
            'file_connection_path'    => $queue_dir,
            'restart_file_path'       => $queue_dir . '/' . self::QUEUE_RESTART_FILE_NAME . '_' . $type . '.txt',
            'restart_route'           => $this->restart_route,
            'restart_body'            => $this->restart_body,
            'items_count_for_restart' => $this->config->getImagesLimitForRestart(),
            'force'                   => (!$this->restart),
            'queue_data'              => new QueueDataDTO([
                'name'           => self::QUEUE_NAME . '_' . $type,
                'multiple_items' => true
            ])
        ]), \TenWebIO\Queue\QueueProducer::class, \TenWebIO\Queue\QueueConsumer::class);
    }

    /**
     * @param      $data
     * @param bool $is_finished
     *
     * @return void
     */
    public function storeDataInService($data, $is_finished = 0)
    {
        $api_instance = new Api(Api::API_COMPRESS_LOG_STORE_ACTION);
        $api_instance->apiRequest('POST', array('data' => $data, 'is_finished' => $is_finished));
    }

    /**
     * Counter can be number, or other directory name like elementor
     *
     * @param string  $counter
     *
     * @param integer $webP_paths
     *
     * @return array
     */
    public function getAlreadyConvertedImagesPaths($counter = '1', $webP_paths = 1)
    {
        $data = get_option(TENWEBIO_PREFIX . '_converted_images_' . $counter);
        if (empty($data)) {
            return array();
        }
        $data = json_decode($data);
        $paths = array();
        foreach ($data as $row) {
            $row = json_decode($row, true);
            $paths[] = $webP_paths ? $row['path'] : rtrim($row['path'], '.webp');
        }

        return array_unique($paths);
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function ifRunningCompressExist($type = 'bulk')
    {
        $running_compress = get_site_option(TENWEBIO_PREFIX . '_running_compress_' . $type);
        Logs::setLog("compress:" . $type . ":running:check", $running_compress);
        if ($running_compress) {
            return true;
        }
        update_site_option(TENWEBIO_PREFIX . '_running_compress_' . $type, 1);

        return false;
    }

}
