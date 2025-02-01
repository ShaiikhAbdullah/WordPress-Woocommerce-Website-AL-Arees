<?php
namespace TenWebIO;

class Utils
{
    /**
     * @return array
     */
    public static function getAttachmentData($url)
    {
        $no_schema_url = str_replace(array('https://', 'http://', 'www.'), '', $url);
        $no_schema_site_url = str_replace(array('https://', 'http://', 'www.'), '', site_url());

        $absolute_path = rtrim(self::strReplaceFirstOccurrence($no_schema_site_url, rtrim(ABSPATH, '/'), $no_schema_url), '/');
        $absolute_url = rtrim(self::strReplaceFirstOccurrence(rtrim(ABSPATH, '/'), site_url(), $url), '/');

        $base_name = rtrim(pathinfo($url, PATHINFO_BASENAME), '/');
        $destination = rtrim(pathinfo($absolute_path, PATHINFO_DIRNAME), '/');
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        return [
            'base_name'     => $base_name,
            'destination'   => $destination,
            'extension'     => $extension,
            'absolute_url'  => $absolute_url,
            'absolute_path' => $absolute_path,
        ];
    }

    /**
     * @param $dir
     *
     * @return array
     */
    public static function getFilesFromDir($dir)
    {
        $result = array();
        foreach (scandir($dir) as $value) {
            if (!in_array($value, array(".", "..", ".original"))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result = array_merge($result, self::getFilesFromDir($dir . DIRECTORY_SEPARATOR . $value));
                } else {
                    $result[] = $dir . DIRECTORY_SEPARATOR . $value;
                }
            }
        }

        return $result;
    }

    public static function removeDir($dir)
    {
        if (is_dir($dir)) {
            foreach (scandir($dir) as $value) {
                if (!in_array($value, array(".", "..", ".original"))) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                        rrmdir($dir . DIRECTORY_SEPARATOR . $value);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $value);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * @param      $type
     * @param bool $delete_all
     *
     * @return void
     */
    public static function deleteQueueTransients($type, $delete_all = true)
    {
        $page_id = Utils::getCustomQueueIdByType($type);
        delete_site_option(TENWEBIO_PREFIX . '_compress_images_count_' . $type);
        delete_site_option(TENWEBIO_PREFIX . '_compress_images_counter_' . $type);
        if ($delete_all) {
            delete_site_option(TENWEBIO_PREFIX . '_custom_compress_only_convert_webp_' . $page_id);
            delete_site_option(TENWEBIO_PREFIX . '_custom_compress_origin_' . $page_id);
            delete_site_option(TENWEBIO_PREFIX . '_running_compress_' . $type);
        }
    }

    /**
     * @return void
     */
    public static function clearSpeedCache()
    {
        if (class_exists('\OptimizerAdmin')) {
            \OptimizerAdmin::clear_cache(false, true);
            wp_remote_get(get_site_url(), array('method' => 'GET', 'sslverify' => false, 'timeout' => 0.1));
        }
    }

    /**
     * @param      $type
     * @param bool $clear_booster_cache
     * @param bool $retry_queue_chunks
     *
     * @return void
     */
    public static function finishQueue($type, $clear_booster_cache = true, $retry_queue_chunks = true)
    {
        $queue_dir = Utils::getQueueDir($type);
        if (is_dir($queue_dir)) {
            Utils::removeDir($queue_dir);
        }
        Utils::deleteQueueTransients($type);
        Settings::purgeCompressSettings();
        if ($clear_booster_cache) {
            Utils::clearSpeedCache();
        }
        /*$config = new Config();
        if ($type === 'bulk' && $retry_queue_chunks) {
            self::compressBulkRequest();
        }*/
    }

    public static function compressBulkRequest()
    {
        $route = add_query_arg(array('rest_route' => '/tenwebio/v2/compress', 'chunk' => rand(1, 5000)), get_home_url() . "/");
        wp_remote_post($route, array('method' => 'POST', 'sslverify' => false, 'timeout' => 0.1, 'body' => array(
            'tenwebio_nonce' => wp_create_nonce('tenwebio_rest')
        )));
    }

    public static function compressOneRequest($id)
    {
        $route = add_query_arg(array('rest_route' => '/tenwebio/v2/compress-one', 'single' => $id), get_home_url() . "/");
        wp_remote_post($route, array('method' => 'POST', 'sslverify' => false, 'timeout' => 0.1, 'body' => array(
            "id"             => $id,
            'tenwebio_nonce' => wp_create_nonce('tenwebio_rest')
        )));
    }

    /**
     * @param $queue_name
     *
     * @return array|string|string[]
     */
    public static function getQueueTypeByName($queue_name)
    {
        return str_replace(CompressService::QUEUE_NAME . '_', '', $queue_name);
    }

    /**
     * @param $queue_type
     *
     * @return int
     */
    public static function getCustomQueueIdByType($queue_type)
    {
        return str_replace('custom_', '', $queue_type);
    }

    /**
     * @param $queue_type
     *
     * @return string
     */
    public static function getQueueDir($queue_type)
    {
        $upload_dir = wp_get_upload_dir();
        $queue_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $queue_type;
        if (!is_dir($queue_dir)) {
            mkdir($queue_dir);
        }

        return $queue_dir;
    }

    /**
     * @param $url
     *
     * @return void
     */
    public static function storeWebPLog($url)
    {
        $data = get_option(TENWEBIO_PREFIX . '_webp_converted', array());
        $attachment_data = self::getAttachmentData($url);
        $data[] = $attachment_data['absolute_path'];
        update_option(TENWEBIO_PREFIX . '_webp_converted', $data, false);
    }

    /**
     * @return int
     */
    public static function deleteWebPImages()
    {
        $data = get_option(TENWEBIO_PREFIX . '_webp_converted', array());
        $deleted_count = 0;
        if (!empty($data)) {
            foreach ($data as $image) {
                $extension = pathinfo($image, PATHINFO_EXTENSION);
                if (file_exists($image) && strtolower($extension) === 'webp') {
                    unlink($image);
                    $deleted_count++;
                }
            }
        }
        delete_option(TENWEBIO_PREFIX . '_webp_converted');

        return $deleted_count;
    }

    /**
     * @param $search
     * @param $replacement
     * @param $src
     *
     * @return array|mixed|string|string[]
     */
    public static function strReplaceFirstOccurrence($search, $replacement, $src)
    {
        return (false !== ($pos = strpos($src, $search))) ? substr_replace($src, $replacement, $pos, strlen($search)) : $src;
    }

    /**
     * @param $url
     * @param $destination
     *
     * @return bool
     */
    public static function downloadFile($url, $destination)
    {
        $response = wp_remote_get($url);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 || empty($response['body'])) {
            Logs::setLog("download:url:" . $url . ":error", json_encode($response), 'error');

            return false;
        }

        return file_put_contents($destination, $response['body']);
    }

    /**
     * @param $data
     *
     * @return \Generator
     */
    public static function getGenerator($data)
    {
        foreach ($data as $item) {
            yield $item;
        }
    }


    /**
     * @return bool
     */
    public static function ifWorkspaceOrDomainEmpty()
    {
        $workspace_id = get_site_option(TENWEBIO_MANAGER_PREFIX . '_workspace_id', 0);
        $domain_id = get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0);
        $access_token = get_site_option(TENWEBIO_MANAGER_PREFIX . '_access_token');

        return empty($workspace_id) || empty($domain_id) || empty($access_token);
    }
}
