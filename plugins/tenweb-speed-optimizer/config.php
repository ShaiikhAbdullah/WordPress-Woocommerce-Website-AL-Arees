<?php

define('TENWEB_SO_VERSION', '2.30.9');
define('TENWEB_SO_CONNECTED_FROM', 'speed_optimizer');
define('TENWEB_SO_DIR', plugin_dir_path(TWO_PLUGIN_FILE));
define('TENWEB_SO_PLUGIN_DIR', plugin_dir_path(TWO_PLUGIN_FILE));
define('TENWEB_SO_BASENAME', plugin_basename(TWO_PLUGIN_FILE));
define('TW_OPTIMIZE_PREFIX', 'two');

if (!defined('TENWEB_PREFIX')) {
    define('TENWEB_PREFIX', 'tenweb');
}

if (!defined('TENWEB_VERSION')) {
    define('TENWEB_VERSION', 'two-' . TENWEB_SO_VERSION);
}

// in seconds
if (!defined('TENWEB_SO_IN_PROGRESS_LOCK')) {
    define('TENWEB_SO_IN_PROGRESS_LOCK', 300);
}

if (!defined('TENWEB_SO_URL')) {
    define('TENWEB_SO_URL', plugins_url(plugin_basename(__DIR__)));
}

if (!defined('TENWEB_SO_NOOPTIMIZE_URL')) {
    define('TENWEB_SO_NOOPTIMIZE_URL', get_site_url() . '?two_nooptimize=1');
}

if (!defined('TENWEB_SO_CACHE_CHILD_DIR')) {
    define('TENWEB_SO_CACHE_CHILD_DIR', '/cache/tw_optimize/');
}

if (!defined('TENWEB_SO_LOGS_MAX_LINES_LIMIT')) {
    define('TENWEB_SO_LOGS_MAX_LINES_LIMIT', 100);
}

if (!defined('TENWEB_SO_DEBUG_MODE')) {
    define('TENWEB_SO_DEBUG_MODE', false); //set this to true to enable logging in plugin
}

if (!defined('TENWEB_CACHE_STRUCTURE_ALLOWED_SIZE')) {
    //MAX Allowed size 5GB
    define('TENWEB_CACHE_STRUCTURE_ALLOWED_SIZE', 4882813);
}

if (!function_exists('get_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$tenweb_so_mu_plugins = get_mu_plugins();
define('TENWEB_SO_HOSTED_ON_10WEB', isset($tenweb_so_mu_plugins['tenweb-init.php']));
define('TENWEB_SO_HOSTED_ON_NGINX', TENWEB_SO_HOSTED_ON_10WEB || (isset($_SERVER['SERVER_SOFTWARE']) && strpos(strtolower(sanitize_text_field($_SERVER['SERVER_SOFTWARE'])), 'nginx') !== false));

if (!function_exists('get_home_path')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}
// phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
$tw_is_htaccess_writable = (file_exists(get_home_path() . '.htaccess') && is_writable(get_home_path() . '.htaccess'))
  || (!file_exists(get_home_path() . '.htaccess') && file_exists(get_home_path()) && is_writable(get_home_path()));
// phpcs:enable
define('TENWEB_SO_HTACCESS_WRITABLE', $tw_is_htaccess_writable);

if (!defined('TENWEB_SO_PAGE_CACHE_DIR')) {
    define('TENWEB_SO_PAGE_CACHE_DIR', WP_CONTENT_DIR . '/cache/tw_optimize/page_cache/');
}

global $two_incompatible_plugin_list;

require_once __DIR__ . '/env.php';
