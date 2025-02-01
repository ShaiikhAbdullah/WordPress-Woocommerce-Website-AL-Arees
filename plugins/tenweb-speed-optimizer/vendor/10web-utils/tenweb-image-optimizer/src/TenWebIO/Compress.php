<?php

namespace TenWebIO;

use TenWebIO\Exceptions\IOException;
use TenWebWpTransients\OptimizerTransients;

class Compress
{
    private $queue_type;
    private $url;
    private $post_id;
    private $thumb_size;

    private $destination;
    private $absolute_path;

    private $compress_response = array();
    private $compress_settings;

    /**
     * @param        $queue_name
     * @param        $url
     * @param int    $post_id
     * @param string $thumb_size
     */
    public function __construct($queue_name, $url, $post_id = 0, $thumb_size = 'full')
    {
        $this->url = $url;
        $this->post_id = (int)$post_id;
        $this->thumb_size = $thumb_size;

        $data = Utils::getAttachmentData($url);
        $this->destination = $data['destination'];
        $this->absolute_path = $data['absolute_path'];

        $this->compress_settings = new Settings(false);

        $this->queue_type = Utils::getQueueTypeByName($queue_name);
        update_site_option(TENWEBIO_PREFIX . '_compress_images_counter_' . $this->queue_type, get_site_option(TENWEBIO_PREFIX . '_compress_images_counter_' . $this->queue_type, 0) + 1);
    }

    /**
     *
     * @return void
     * @throws IOException
     */
    public function compress()
    {
        $settings = $this->compress_settings->getSettings(false, 1, 1);
        Logs::setLog("compress:settings:log", $settings);
        if (!$this->checkImage()) {
            throw new IOException('Error during compress request. Wrong mime type for ' . $this->url);
        }
        $this->compressRequest();
        if (!$this->compress_response) {
            throw new IOException('Error during compress request');
        }
        $compress_response = $this->compress_response;
        if (!empty($compress_response['aws_url'])) {
            if (!empty($settings['keep_originals'])) {
                $this->keepOriginals();
            }
        }
        $convert_webp = !empty($settings) ? (int)$settings['convert_webp'] : 1;
        if (!$this->download($convert_webp)) {
            throw new IOException('Error during download from s3');
        }
        if (!$this->checkHashes()) {
            throw new IOException('Not matching image hashes.');
        }
        $this->saveLastCompress();
    }

    /**
     * @return void
     * @throws IOException
     */
    public function compressRequest()
    {
        $action = strpos($this->queue_type, 'custom_') !== false ? Api::API_COMPRESS_CUSTOM_ACTION : Api::API_COMPRESS_ACTION;
        $page_id = Utils::getCustomQueueIdByType($this->queue_type);
        $api_instance = new Api($action);
        $origin = get_site_option(TENWEBIO_PREFIX . '_custom_compress_origin_' . $page_id);
        $this->compress_response = $api_instance->apiRequest('POST', array(
            "wp_options"        => array(
                "attachment_id" => $this->post_id,
                "thumb_size"    => $this->thumb_size,
                "page_id"       => $page_id
            ),
            "url"               => $this->url,
            "total"             => get_site_option(TENWEBIO_PREFIX . '_compress_images_count_' . $this->queue_type, 0),
            "counter"           => get_site_option(TENWEBIO_PREFIX . '_compress_images_counter_' . $this->queue_type, 0),
            "only_convert_webp" => get_site_option(TENWEBIO_PREFIX . '_custom_compress_only_convert_webp_' . $page_id),
            "origin"            => $origin ? $origin : OptimizerTransients::get(TENWEBIO_PREFIX . '_origin')
        ));
        if ($api_instance->getResponseStatusCode() === 400 ||
            $api_instance->getResponseStatusCode() === 401 ||
            $api_instance->getResponseStatusCode() === 403 ||
            $api_instance->getResponseStatusCode() === 503) {
            throw new IOException('finish_queue');
        }
    }

    /**
     * @return void
     */
    public function keepOriginals()
    {
        $backup = new Backup();
        $backup->backupBeforeReplace($this->absolute_path, $this->destination);
        Logs::setLog("compress:backup:" . $this->url . ":log", 'finished');
    }

    /**
     *
     * @param int $with_webp
     *
     * @return bool
     * @throws IOException
     */
    public function download($with_webp = 1)
    {
        $compress_response = $this->compress_response;
        if ((int)$compress_response['final_size'] > (int)$compress_response['orig_size']) {
            return true;
        }

        return $this->downloadAssignedUrl($with_webp);
    }

    /**
     *
     * @return void
     */
    public function saveLastCompress()
    {
        $compress_images_counter = get_site_option(TENWEBIO_PREFIX . '_compress_images_counter_' . $this->queue_type);
        $last_force = $compress_images_counter == 1;
        $last_compress = new LastCompress($last_force);
        $response = $this->compress_response;
        $orig_size = !empty($response['orig_size']) ? $response['orig_size'] : 0;
        $size = !empty($response['final_size']) ? $response['final_size'] : 0;
        $last_compress->update($size, $orig_size);

        Logs::setLog("compress:last:" . $this->url . ":log", array('orig_size' => $orig_size, 'final_size' => $size));
    }


    /**
     * @param $with_webp
     *
     * @return bool
     */
    private function downloadAssignedUrl($with_webp = 1)
    {
        $aws_webp = $aws = false;
        $compress_response = $this->compress_response;
        if (!empty($compress_response['aws_signed_webp_url']) && $with_webp) {
            $aws_webp = Utils::downloadFile($compress_response['aws_signed_webp_url'], $this->destination . '/' . basename($this->url) . '.webp');
            Utils::storeWebPLog($this->url . '.webp');
        }
        if (!empty($compress_response['aws_signed_url'])) {
            $url = parse_url($compress_response['aws_signed_url'], PHP_URL_PATH);
            $aws = Utils::downloadFile($compress_response['aws_signed_url'], $this->destination . '/' . basename($url));
        }

        return ($aws_webp || $aws);

    }

    /**
     * @return bool
     */
    private function checkHashes()
    {
        $checked = false;
        $compress_response = $this->compress_response;
        $url = parse_url($compress_response['aws_signed_url'], PHP_URL_PATH);
        $downloaded_file = $this->destination . '/' . basename($url);
        if (is_file($downloaded_file)) {
            $downloaded_file_hash = hash_file("sha256", $downloaded_file);
            if ($compress_response['image_hash'] !== $downloaded_file_hash) {
                unlink($downloaded_file);
            } else {
                $checked = true;
                rename($downloaded_file, $this->destination . '/' . basename($this->url));
            }
        }

        return $checked;
    }

    /**
     * @return bool
     */
    private function checkImage()
    {
        $data = getimagesize($this->absolute_path);
        if (!$data) {
            return false;
        }
        $allowed_mime_types = array('image/png', 'image/jpg', 'image/jpeg', 'image/gif');
        if (!empty($data['mime']) && in_array(strtolower($data['mime']), $allowed_mime_types)) {
            return true;
        }

        return false;
    }

}
