<?php

namespace TenWebIO;

class Attachments
{
    private $allowed_extensions = array('jpeg', 'jpg', 'png');
    private $excluded_ids = array();
    private $excluded_thumb_ids = array();
    private $excluded_other_paths = array();
    private $other_directories = array();
    private $filtered_ids = array();
    private $first_in_queue = array();
    private $limit_query = 0;
    private $allowed_image_size = 8388608; // 8mb todo make this with config

    public function setExcludedIds($ids = array())
    {
        $this->excluded_ids = array_map('intval', $ids);
    }

    public function setExcludedThumbIds($ids = array())
    {
        $this->excluded_thumb_ids = $ids;
    }

    public function setExcludedOtherPaths($paths = array())
    {
        $this->excluded_other_paths = $paths;
    }

    public function setOtherDirectories($dirs = array())
    {
        $this->other_directories = $dirs;
    }

    public function setFilteredIds($ids = array())
    {
        $this->filtered_ids = array_map('intval', $ids);;
    }

    public function setFirstInQueue($first_in_queue = array())
    {
        $this->first_in_queue = $first_in_queue;
    }

    public function setLimitQuery($limit_query = 0)
    {
        $this->limit_query = $limit_query;
    }

    /**
     *
     * @param string[] $order
     *
     * @return array
     */
    public function getData($order = array('attachments_other', 'attachments_full', 'attachments_meta', 'attachments_first_in_queue'))
    {
        $attachments = $this->getDataSeparate();
        $return_data = array();
        foreach ($order as $item) {
            $return_data = array_merge($return_data, $attachments[$item]);
        }

        return $return_data;
    }

    /**
     * @return array
     */
    public function getDataSeparate()
    {
        $attachments = $this->getAttachmentsParsedData();
        $other_attachments = $this->getOtherAttachmentsData();
        $total_size = $attachments['total_size'] + $this->getPhotoGalleryTotalSize();

        return [
            'attachments_full'           => $attachments['full_sizes'],
            'attachments_meta'           => $attachments['meta_sizes'],
            'attachments_other'          => $other_attachments,
            'total_size'                 => $total_size,
            'attachments_first_in_queue' => $attachments['first_in_queue_sizes'],
        ];
    }

    /**
     *
     * @return array
     */
    public function getAttachmentsParsedData()
    {
        $config = new Config();
        $attachments = Utils::getGenerator($this->getAttachmentsData());
        $first_in_queue_sizes = array();
        $full_sizes = array();
        $meta_sizes = array();
        $files = array();
        $uploads = wp_get_upload_dir();
        $total_size = 0;

        foreach ($attachments as $attachment) {
            $meta_value = $attachment->meta_value ? unserialize($attachment->meta_value) : array();
            $guid = $attachment->guid;
            $file_not_exist = false;
            $image_file_size = 0;
            if (!empty($meta_value['file'])) {
                $guid = $uploads['baseurl'] . '/' . $meta_value['file'];
                $file_not_exist = !file_exists($uploads['basedir'] . '/' . $meta_value['file']);
                $file_is_readable = is_readable($uploads['basedir'] . '/' . $meta_value['file']);
                if ($file_not_exist || !$file_is_readable) {
                    continue;
                }
                $image_file_size = filesize($uploads['basedir'] . '/' . $meta_value['file']);

            }
            if (!$file_not_exist && $image_file_size <= $this->allowed_image_size) {
                $extension = strtolower(pathinfo($guid, PATHINFO_EXTENSION));
                if (!in_array($extension, array('jpg', 'jpeg', 'png'))) {
                    continue;
                }
                if (in_array($guid, $files) === false) {
                    $image = array(
                        'guid' => $guid,
                        'ID'   => $attachment->ID,
                        'size' => 'full',
                    );
                    $total_size += !empty($meta_value['filesize']) ? $meta_value['filesize'] : 0;
                    if (!empty($this->first_in_queue) && in_array($guid, $this->first_in_queue)) {
                        $first_in_queue_sizes[] = $image;
                    } else {
                        $full_sizes[] = $image;
                    }
                    $files[] = $guid;
                }
            }

            $files = array();
            if (!empty($meta_value['sizes']) && !$config->getCompressOnlyFullSize()) {
                $data = Utils::getAttachmentData($meta_value['file']);
                foreach ($meta_value['sizes'] as $size_name => $size_data) {
                    $guid = $uploads['baseurl'] . '/' . $data['destination'] . '/' . $size_data['file'];
                    $file_path = $uploads['basedir'] . '/' . $data['destination'] . '/' . $size_data['file'];
                    if (in_array($attachment->ID . '_' . $size_name, $this->excluded_thumb_ids)) {
                        continue;
                    }
                    if (!file_exists($file_path) || !is_readable($file_path)) {
                        continue;
                    }
                    $image_file_size = filesize($file_path);
                    if (file_exists($file_path) && in_array($guid, $files) === false && $image_file_size <= $this->allowed_image_size) {
                        $image = array(
                            'guid' => $guid,
                            'ID'   => $attachment->ID,
                            'size' => $size_name,
                        );
                        $total_size += (!empty($size_data['filesize']) ? $size_data['filesize'] : 0);
                        if (!empty($this->first_in_queue) && in_array($guid, $this->first_in_queue)) {
                            $first_in_queue_sizes[] = $image;
                        } else {
                            $meta_sizes[] = $image;
                        }
                        $files[] = $guid;
                    }
                }
            }
        }

        return array('full_sizes' => $full_sizes, 'meta_sizes' => $meta_sizes, 'first_in_queue_sizes' => $first_in_queue_sizes, 'total_size' => $total_size);
    }

    /**
     * @return array
     */
    public function getOtherAttachmentsData()
    {
        if (empty($this->other_directories)) {
            return array();
        }
        $other_attachments = array();
        $result = array();
        foreach ($this->other_directories as $dir) {
            if (is_dir(ABSPATH . $dir)) {
                $other_attachments = array_merge($other_attachments, Utils::getFilesFromDir(ABSPATH . $dir));
            }
        }

        foreach ($other_attachments as $other_attachment) {
            $attachment_data = Utils::getAttachmentData($other_attachment);
            if (in_array($attachment_data['absolute_url'], $this->excluded_other_paths) ||
                !in_array($attachment_data['extension'], $this->allowed_extensions)) {
                continue;
            }
            $result[] = array(
                'guid' => $attachment_data['absolute_url'],
                'ID'   => 0,
                'size' => 'full_other'
            );
        }

        return $result;
    }

    /**
     * @param        $image_urls
     * @param string $size
     *
     * @return array|array[]
     */
    public function getCustomAttachmentsData($image_urls, $size = 'full_custom')
    {
        if (empty($image_urls)) {
            return array();
        }

        return array_map(function ($value) {
            return array('guid' => $value, 'ID' => 0, 'size' => 'full_custom');
        }, $image_urls);
    }

    /**
     * @param      $id
     * @param null $meta
     *
     * @return array
     */
    public static function getPostMeta($id, $meta = null)
    {
        if ($meta === null) {
            $meta = get_post_meta($id);
        }
        $files = array();
        if ($meta) {
            if (!empty($meta['sizes'])) {
                foreach ($meta['sizes'] as $meta_size) {
                    $files[$meta_size['file']] = !empty($meta_size['filesize']) ? $meta_size['filesize'] : 0;
                }
            }
        }

        return $files;
    }

    /**
     * @return mixed
     */
    private function getAttachmentsData()
    {
        global $wpdb;
        $prepare_data = array();
        $where_ids = '';
        $where_exclude_ids = '';
        $limit_by = '';
        if ($this->filtered_ids) {
            $where_ids = " AND ID IN (" . implode(', ', array_fill(0, count($this->filtered_ids), '%d')) . ")";
            $prepare_data = array_merge($prepare_data, $this->filtered_ids);
        }
        if ($this->excluded_ids) {
            $where_exclude_ids = " AND ID NOT IN (" . implode(', ', array_fill(0, count($this->excluded_ids), '%d')) . ")";
            $prepare_data = array_merge($prepare_data, $this->excluded_ids);
        }
        if ($this->limit_query) {
            $limit_by = " LIMIT 0,%d";
            $prepare_data[] = $this->limit_query;
        }

        return $wpdb->get_results($wpdb->prepare("SELECT ID, guid, 'full' as size, meta_value   FROM " . $wpdb->prefix . "posts 
         left join " . $wpdb->prefix . "postmeta on " . $wpdb->prefix . "posts.ID=" . $wpdb->prefix . "postmeta.post_id and meta_key='_wp_attachment_metadata'
         WHERE post_type='attachment' and (post_mime_type LIKE '%jpeg%' or post_mime_type LIKE '%jpg%' or post_mime_type LIKE '%png%') "
            . $where_ids . " " . $where_exclude_ids . $limit_by,
            $prepare_data
        ), OBJECT_K);
    }

    /**
     * @return int
     */
    private function getPhotoGalleryTotalSize()
    {
        if (file_exists(WP_PLUGIN_DIR . '/photo-gallery/framework/WDWLibrary.php')) {
            include_once WP_PLUGIN_DIR . '/photo-gallery/framework/WDWLibrary.php';
            if (function_exists('\WDWLibrary::get_images_total_size')) {
                return \WDWLibrary::get_images_total_size();
            }
        }

        return 0;
    }
}

