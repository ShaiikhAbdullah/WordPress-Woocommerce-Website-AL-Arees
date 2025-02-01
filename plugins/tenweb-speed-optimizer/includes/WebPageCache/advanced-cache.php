<?php

use TenWebOptimizer\WebPageCache\OptimizerWebPageCache;

defined('ABSPATH') || exit;

define('TENWEB_SO_ADVANCED_CACHE', true);
define('TENWEB_SO_PAGE_CACHE_DIR', WP_CONTENT_DIR . '/cache/tw_optimize/page_cache/');

$file_path = TWO_PLUGIN_DIR_CACHE . '/includes/WebPageCache/OptimizerWebPageCache.php';

if (file_exists($file_path)) {
    require_once $file_path;
    OptimizerWebPageCache::get_instance();
}
