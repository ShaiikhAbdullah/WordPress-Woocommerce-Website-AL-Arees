<?php

namespace TenWebIO;


class CompressDataService
{
    private $compress_settings;

    public function __construct()
    {
        $this->compress_settings = new Settings();
    }

    /**
     * @param bool  $force
     * @param bool  $force_stat
     * @param array $first_in_queue
     *
     * @return Attachments|bool
     */
    public function getImagesReadyForOptimize($force = false, $force_stat = false, $limit_query = 0, $first_in_queue = array())
    {
        $stat = $this->compress_settings->getStat($force_stat, 0, 1);
        $settings = $this->compress_settings->getSettings(false, 1, 1);

        $exclude_ids = array();
        $exclude_thumb_ids = array();
        $exclude_other = array();
        if (!$force) {
            if (!empty($stat['full_ids'])) {
                $exclude_ids = $stat['full_ids'];
            }
            if (!empty($stat['thumb_ids'])) {
                $exclude_thumb_ids = $stat['thumb_ids'];
            }
            if (!empty($stat['other'])) {
                $exclude_other = $stat['other'];
            }
        }
        $other_directories = !empty($settings['other_directories']) ? $settings['other_directories'] : array();

        $attachments = new Attachments();
        $attachments->setExcludedIds($exclude_ids);
        $attachments->setExcludedThumbIds($exclude_thumb_ids);
        $attachments->setOtherDirectories($other_directories);
        $attachments->setExcludedOtherPaths($exclude_other);
        $attachments->setFirstInQueue($first_in_queue);
        $attachments->setLimitQuery($limit_query);

        return $attachments;
    }

    /**
     * @param $post_id
     * @param $force_stat
     *
     * @return Attachments
     */
    public function getImageReadyForOptimize($post_id, $force_stat = false)
    {
        $stat = $this->compress_settings->getStat($force_stat, 1, 1);
        $exclude_ids = !empty($stat['full_ids']) ? $stat['full_ids'] : array();
        $exclude_thumb_ids = !empty($stat['thumb_ids']) ? $stat['thumb_ids'] : array();

        $attachments = new Attachments();
        $attachments->setExcludedIds($exclude_ids);
        $attachments->setExcludedThumbIds($exclude_thumb_ids);
        $attachments->setFilteredIds(array($post_id));

        return $attachments;
    }

    /**
     * @param bool $force
     *
     * @return array|array[]
     */
    public function getCompressResults($force = false)
    {
        $compressed_data = get_transient(TENWEBIO_PREFIX . '_images_report');
        $not_compressed = $this->getNotCompressedNumbers();
        if (empty($compressed_data) || $force === true) {
            $compressed_data = $this->getStat(1, 0);
            set_transient(TENWEBIO_PREFIX . '_images_report', $compressed_data, 86400);
        }

        return $not_compressed + $compressed_data;
    }

    /**
     * @param bool $force_stat
     *
     * @return array
     */
    public function getNotCompressedNumbers($force_stat = false)
    {
        $config = new Config();
        $not_optimized = get_transient(TENWEBIO_PREFIX . '_not_optimized_data');
        if (!$not_optimized) {
            $not_optimized = $this->getImagesReadyForOptimize(false, $force_stat, $config->getStatChunkImagesLimit())->getDataSeparate();
            $not_optimized = array(
                'full'       => count($not_optimized['attachments_full']),
                'thumbs'     => count($not_optimized['attachments_meta']),
                'other'      => count($not_optimized['attachments_other']),
                'total_size' => $not_optimized['total_size']
            );
            set_transient(TENWEBIO_PREFIX . '_not_optimized_data', $not_optimized, 43200);

        }
        $last_optimized = new LastCompress();
        $size = $last_optimized->getImageOrigSize() - $last_optimized->getImageSize();
        $percent = $last_optimized->getImageOrigSize() ? ($size / $last_optimized->getImageOrigSize()) * 100 : 0;

        return array(
            'not_optimized'  => $not_optimized,
            'last_optimized' => array(
                'size'    => $size,
                'percent' => $percent
            )
        );
    }

    /**
     * @param int $skip_local_data
     * @param int $skip_reduced
     * @param int $skip_custom_pages_count
     *
     * @return array
     */
    public function getStat($skip_local_data = 1, $skip_reduced = 1, $skip_custom_pages_count = 1)
    {
        $data = array();
        $api_instance = new Api(Api::API_GET_STAT);

        $response_data = $api_instance->apiRequest('GET', array(), array(
            'skip_local_data'         => $skip_local_data,
            'skip_reduced'            => $skip_reduced,
            'skip_custom_pages_count' => $skip_custom_pages_count,
        ));
        if (is_array($response_data)) {
            $data = $response_data;
        }

        return $data;
    }
}