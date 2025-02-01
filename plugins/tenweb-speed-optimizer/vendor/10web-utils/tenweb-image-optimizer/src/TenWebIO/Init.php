<?php

namespace TenWebIO;

use TenWebIO\Views\LogsView;
use TenWebWpTransients\OptimizerTransients;

class Init
{
    private static $instance = null;

    private function __construct()
    {
        $this->initCLI();
        $this->initRest();
        $this->initViews();
        $this->initBulkCompressCron();
        $this->initMediaUpload();
        $this->initMediaUploadDelete();
    }

    /**
     * @return Init|null
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            return new self();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function deactivate()
    {
        wp_clear_scheduled_hook('tenwebio_compress_hook');
        OptimizerTransients::delete(TENWEBIO_PREFIX . '_origin');
    }

    /**
     * @return bool
     */
    public function autoOptimizeBulk()
    {
        try {
            $compress_service = new CompressService();
            $compress_service->compressBulk();
        } catch (\Exception $e) {
            Logs::setLog("compress:cron:error", $e->getMessage(), 'error');
        }

        return true;
    }

    /**
     * @param $meta
     * @param $id
     *
     * @return mixed
     */
    public function addNotOptimizedNumbers($meta, $id)
    {
        $not_optimized = get_transient(TENWEBIO_PREFIX . '_not_optimized_data');
        if ($not_optimized) {
            $not_optimized["full"] = $not_optimized["full"] + 1;
            $files = Attachments::getPostMeta($id, $meta);
            $not_optimized["thumbs"] = $not_optimized["thumbs"] + count($files);
            $full_size = !empty($meta['filesize']) ? $meta['filesize'] : 0;
            $thumbs_size = array_sum($files);
            $not_optimized["total_size"] = $not_optimized["total_size"] + $full_size + $thumbs_size;
            set_transient(TENWEBIO_PREFIX . '_not_optimized_data', $not_optimized, 43200);
        }

        return $meta;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function subNotOptimizedNumbers($id)
    {
        /*$compress_settings = new Settings();
        $stat = $compress_settings->getStat(false, 1, 1);
        $compressed_ids = !empty($stat['full_ids']) ? $stat['full_ids'] : array();
        if (!in_array($id, $compressed_ids)) {
            $not_optimized = get_transient(TENWEBIO_PREFIX . '_not_optimized_data');
            $not_optimized["full"] = $not_optimized["full"] - 1;
            $not_optimized["thumbs"] = $not_optimized["thumbs"] - count(Attachments::getPostMeta($id));
            set_transient(TENWEBIO_PREFIX . '_not_optimized_data', $not_optimized, 43200);
        }*/
        delete_transient(TENWEBIO_PREFIX . '_not_optimized_data');

        return $id;
    }

    /**
     * @return void
     */
    private function initCLI()
    {
        if (class_exists('\WP_CLI')) {
            \WP_CLI::add_command('10web-tb-optimized-images', array('\TenWebIO\CLI', 'readyToOptimizeImages'));
            \WP_CLI::add_command('10web-store-optimized-images-log', array('\TenWebIO\CLI', 'storeOptimizedImagesLog'));
            \WP_CLI::add_command('10web-store-last-optimized-log', array('\TenWebIO\CLI', 'storeLastOptimizationLog'));
            \WP_CLI::add_command('10web-converted-images', array('\TenWebIO\CLI', 'convertedImages'));
        }
    }

    /**
     * @return void
     */
    private function initRest()
    {
        if (class_exists("\WP_REST_Controller")) {
            add_action('rest_api_init', function () {
                $rest = new Rest();
                $rest->registerRoutes();
            });
        }
    }

    /**
     * @return void
     */
    private function initBulkCompressCron()
    {
        $compress_settings = new Settings(false);
        $options = $compress_settings->getSettings(false, 1, 1);
        add_action('tenwebio_compress_hook', array($this, 'autoOptimizeBulk'));
        if (!wp_next_scheduled('tenwebio_compress_hook') && isset($options["enable_auto_optimization"]) && $options["enable_auto_optimization"]) {
            wp_schedule_event(time(), 'daily', 'tenwebio_compress_hook');
        }
    }

    /**
     * @return void
     */
    private function initMediaUpload()
    {
        add_filter('wp_generate_attachment_metadata', array($this, 'addNotOptimizedNumbers'), 15, 2);
    }

    /**
     * @return void
     */
    private function initMediaUploadDelete()
    {
        add_action('delete_attachment', array($this, 'subNotOptimizedNumbers'));
    }

    /**
     * @return void
     */
    private function initViews()
    {
        new LogsView();
    }
}