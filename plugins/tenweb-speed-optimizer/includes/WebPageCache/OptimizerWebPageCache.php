<?php

namespace TenWebOptimizer\WebPageCache;

use TenWebOptimizer\OptimizerSettings;
use TenWebOptimizer\OptimizerUtils;

/*
 * Base class other (more-specific) classes inherit from.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 *  The cache structure
 *  wp-content/tw_optimize/page_cache/{host}/{request_uri}/{query_args}
 *  e.g. wp-content/tw_optimize/page_cache/{wp.local}/sample-page/#lang=us
 *
 * */
class OptimizerWebPageCache
{
    protected static $instance = null;

    protected static $config = null;

    protected $accept_gzip = false;

    protected $validator = null;

    protected $cache_dir = null;

    protected $cache_file = null;

    protected $cache_headers_file = null;

    protected $cache_file_gzip = null;

    public function __construct()
    {
        $two_settings = self::get_config('two_settings');
        $two_settings = json_decode($two_settings, true);

        if (!isset($two_settings['two_connected']) || $two_settings['two_connected'] !== '1') {
            return;
        }

        if (!defined('TENWEB_SO_PER_USER_CACHE_LIFETIME')) {
            define('TENWEB_SO_PER_USER_CACHE_LIFETIME', 172800); // 2 days
        }

        if (!defined('TENWEB_SO_PER_USER_CACHE_SIZE_LIMIT')) {
            define('TENWEB_SO_PER_USER_CACHE_SIZE_LIMIT', 3000000000); // 3GB
        }

        // Additional check for the old users who have cache enabled with old configs.
        if (!defined('TWO_PLUGIN_DIR_CACHE')) {
            define('TWO_PLUGIN_DIR_CACHE', WP_CONTENT_DIR . '/plugins/tenweb-speed-optimizer/');
        }
        require_once __DIR__ . '/OptimizerWebPageValidations.php';

        require_once TWO_PLUGIN_DIR_CACHE . '/includes/OptimizerUtils.php';
        require_once TWO_PLUGIN_DIR_CACHE . '/includes/OptimizerCache.php';
        require_once TWO_PLUGIN_DIR_CACHE . '/includes/OptimizerUrl.php';
        require_once TWO_PLUGIN_DIR_CACHE . '/includes/OptimizerSettings.php';
        global $TwoSettings;
        $TwoSettings = OptimizerSettings::get_instance();
        $this->validator = OptimizerWebPageValidations::get_instance();

        if ($this->can_process_page()) {
            $this->init();
            $this->maybe_clear_all_cache();
            $this->process_page();
        }
    }

    protected function process_page()
    {
        $file_to_serve = $this->get_cached_file_to_serve();

        if ($file_to_serve !== null && !$this->maybe_clear_cache($file_to_serve)) {
            $this->serve_cached_file($file_to_serve);
        }

        // cache content
        ob_start([$this, 'cache_content']);
    }

    protected function init()
    {
        global $TwoSettings;
        $accept_encoding_value = '';

        if (isset($this->request_headers()['ACCEPT-ENCODING'])) {
            $accept_encoding = $this->request_headers()['ACCEPT-ENCODING'];
            $accept_encoding_value = $accept_encoding && strpos($accept_encoding, 'gzip') !== false;
        }

        $serve_gzip = $TwoSettings->get_settings('two_serve_gzip') == 'yes' ? true : false;
        $serve_gzip_in_any_case = $TwoSettings->get_settings('two_empty_encoding_serve_gzip') == 'yes' ? true : false;

        if ($serve_gzip && ($accept_encoding_value || $serve_gzip_in_any_case)) {
            $this->accept_gzip = true;
        }

        $this->cache_dir = $this->get_cache_file_dir();
        // don't use index.html to be able to use delete_all_cache_file() function
        $this->cache_file = $this->cache_dir . '/source.html';
        $this->cache_headers_file = $this->cache_dir . '/headers.json';
        $this->cache_file_gzip = $this->cache_dir . '/source.html.gzip';
    }

    protected function request_headers()
    {
        $arh = [];
        $rx_http = '/\AHTTP_/';

        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }

        return $arh;
    }

    protected function get_cached_file_to_serve()
    {
        if ($this->accept_gzip && file_exists($this->cache_file_gzip) && is_readable($this->cache_file_gzip)) {
            return $this->cache_file_gzip;
        }

        if (file_exists($this->cache_file) && is_readable($this->cache_file)) {
            return $this->cache_file;
        }

        return null;
    }

    protected function serve_cached_file($file_to_serve)
    {
        $file_time = filemtime($file_to_serve);

        if (!isset($_COOKIE['wcml_client_currency'])) {
            // if this is wcml_client_currency cookie, browser should not use it's cache
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file_time) . ' GMT');
        }
        header('X-TWO-PAGE-CACHED: 1');

        if (file_exists($this->cache_headers_file)) {
            $cache_headers = json_decode(file_get_contents($this->cache_headers_file)); // phpcs:ignore

            foreach ($cache_headers as $header) {
                header($header);
            }
        }

        // If the cache has not been modified since $_SERVER["HTTP_IF_MODIFIED_SINCE"], then send 304 not modified and
        // browser use cached html (cached in browser)
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $file_time)) { // phpcs:ignore
            header(strip_tags($_SERVER['SERVER_PROTOCOL']) . ' 304 Not Modified', true, 304); // phpcs:ignore
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            die;
        }

        if (substr($file_to_serve, -5) === '.gzip') {
            readgzfile($file_to_serve);
        } else {
            readfile($file_to_serve);
        }

        die;
    }

    protected function cache_content($buffer)
    {
        if (!is_dir($this->cache_dir)) {
            if (!mkdir($concurrentDirectory = $this->cache_dir, 0777, true) && !is_dir($concurrentDirectory)) { // phpcs:ignore
                return $buffer;
            }
        }

        if (!is_writable($this->cache_dir) || !is_readable($this->cache_dir)) { // phpcs:ignore
            return $buffer;
        }

        if (!$this->validator->valid_buffer_to_cache($buffer)) {
            return $buffer;
        }

        $buffer .= "\n<!-- =^..^= Cached =^..^= -->";

        file_put_contents($this->cache_file, $buffer); // phpcs:ignore
        file_put_contents($this->cache_headers_file, json_encode(headers_list())); // phpcs:ignore
        file_put_contents($this->cache_file_gzip, gzencode($buffer)); // phpcs:ignore

        header('X-TWO-PAGE-CACHED: 0');

        return $buffer;
    }

    protected function can_process_page()
    {
        if (!$this->validator->allowed_request_method()) {
            return false;
        }

        if (!$this->validator->allowed_wp_page()) {
            return false;
        }

        if ($this->validator->rejected_file()) {
            return false;
        }

        if ($this->validator->rejected_cookie()) {
            return false;
        }

        if ($this->validator->reject_uri()) {
            return false;
        }

        if ($this->validator->reject_ua()) {
            return false;
        }

        if (!$this->validator->allowed_query_string()) {
            return false;
        }

        if ($this->validator->has_donotcachepage()) {
            return false;
        }

        if (!$this->validator->allowed_by_optimizer()) {
            return false;
        }

        return true;
    }

    protected function maybe_clear_cache($file_to_serve)
    {
        global $TwoSettings;
        $cache_life_time = intval($TwoSettings->get_settings('two_page_cache_life_time'));

        if (!$cache_life_time) {
            $cache_life_time = $TwoSettings->get_default_setting('two_page_cache_life_time');
        }
        $per_user_cache = $TwoSettings->get_settings('two_page_cache_user');

        if ($per_user_cache === 'on') {
            $cache_life_time = TENWEB_SO_PER_USER_CACHE_LIFETIME;
        }

        if ((time() - filemtime($file_to_serve) < $cache_life_time)) {
            return false;
        }

        $is_home = self::is_home(explode('?', strip_tags($_SERVER['REQUEST_URI']))[0]); // phpcs:ignore
        $cache_dir_without_get_args = explode('/#', $this->cache_dir)[0];

        if (substr($cache_dir_without_get_args, -1) !== '/') {
            $cache_dir_without_get_args .= '/';
        }

        self::delete_page_cache($cache_dir_without_get_args, $is_home);

        return true;
    }

    public function get_cache_file_dir()
    {
        $request_uri = explode('?', strip_tags($_SERVER['REQUEST_URI']))[0]; // phpcs:ignore
        $cache_file_dir = self::get_cache_dir_for_page(strip_tags($_SERVER['HTTP_HOST']), $request_uri); // phpcs:ignore

        $cookies = $this->validator->get_cookies();

        if (!empty($cookies)) {
            $cookie_string = '@';

            foreach ($cookies as $cookie_name => $cookie_value) {
                $cookie_string .= $cookie_name . '=' . $cookie_value . '&';
            }

            $cookie_string = rtrim($cookie_string, '&');
            $cache_file_dir .= $cookie_string . '/';
        }

        $query_params = $this->validator->get_query_params();

        if (empty($query_params)) {
            return $cache_file_dir;
        }

        $query_string = '#';

        foreach ($query_params as $param_name => $param_value) {
            $query_string .= $param_name . '=' . $param_value . '&';
        }

        $query_string = rtrim($query_string, '&');

        return $cache_file_dir . $query_string . '/';
    }

    public static function delete_cache_by_post_id($post_id)
    {
        self::delete_cache_by_url(get_permalink($post_id));
    }

    public static function delete_cache_by_url($url)
    {
        $parsed_url = wp_parse_url($url);
        $is_home_url = false;

        if (isset($parsed_url['path'])) {
            $is_home_url = self::is_home($parsed_url['path']);
        }
        self::delete_page_cache(self::get_cache_dir_for_page_from_url($url), $is_home_url);
    }

    public static function delete_page_cache($dir, $is_home_url)
    {
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = $dir . $file;

                if (is_file($path)) {
                    unlink($path); // phpcs:ignore
                } else {
                    $not_allow_delete = [];

                    if ($is_home_url) {
                        if (substr($file, 0, 1) === '#') {
                            \TenWebOptimizer\OptimizerUtils::delete_all_cache_file($path, $not_allow_delete);
                        }
                    } else {
                        \TenWebOptimizer\OptimizerUtils::delete_all_cache_file($path, $not_allow_delete);
                    }
                }
            }

            // if dir is empty
            if (is_dir($dir)) {
                $dir_arr = scandir($dir);

                if (is_array($dir_arr) && count($dir_arr) === 2) {
                    rmdir($dir); // phpcs:ignore
                }
            }
        }
    }

    public static function delete_all_cached_pages()
    {
        \TenWebOptimizer\OptimizerUtils::delete_all_cache_file(TENWEB_SO_PAGE_CACHE_DIR);

        return !is_dir(TENWEB_SO_PAGE_CACHE_DIR);
    }

    public static function get_cache_dir_for_page($host, $request_uri)
    {
        $is_home_page = self::is_home($request_uri);
        $cache_dir_name = '';

        if (!$is_home_page) {
            $cache_dir_name = rtrim($request_uri, '/');
            $cache_dir_name = ltrim($cache_dir_name, '/');
        }
        global $TwoSettings;
        $cache_hash = '';

        if ('on' === $TwoSettings->get_settings('two_page_cache_user')) {
            // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
            $cookies = $_COOKIE;
            $cookie_name = array_filter(array_keys($cookies), function ($cookie) {
                return strpos($cookie, 'wordpress_logged_in_') === 0;
            });

            if (! empty($cookie_name)) {
                $cookie_name = reset($cookie_name);
                $cookie_value = $cookies[ $cookie_name ];
                $username = explode('|', $cookie_value)[ 0 ];
            }

            if (! empty($username) && isset($cookies[ 'tenweb_so_page_cache_hash' ])) {
                $unique_hash = $cookies[ 'tenweb_so_page_cache_hash' ];

                // This is an additional check to make sure the cookies are identical,
                // it will pass if both cookies are just identical,
                // needs to be improved to make sure user is logged in.
                if (md5($cookie_value) === $unique_hash) {
                    $cache_hash = '-' . $username . '-' . $unique_hash;
                }
            }
        }

        // add $host, to support multisite to
        $cache_dir = TENWEB_SO_PAGE_CACHE_DIR . $host . $cache_hash . '/';

        if ($cache_dir_name) {
            $cache_dir .= $cache_dir_name . '/';
        }

        return $cache_dir;
    }

    public static function get_cache_dir_for_page_from_url($url)
    {
        $parsed_url = wp_parse_url($url);

        return self::get_cache_dir_for_page($parsed_url['host'], $parsed_url['path']);
    }

    public static function get_config($config_name)
    {
        if (self::$config === null) {
            self::$config = json_decode(file_get_contents(WP_CONTENT_DIR . '/10web-page-cache-config/config.json'));
        }

        return self::$config->$config_name;
    }

    public static function is_home($request_uri)
    {
        return empty(ltrim($request_uri, '/'));
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function maybe_clear_all_cache()
    {
        global $TwoSettings;

        if ($TwoSettings->get_settings('two_page_cache_user') === 'on' && OptimizerUtils::dirsize(TENWEB_SO_PAGE_CACHE_DIR) > TENWEB_SO_PER_USER_CACHE_SIZE_LIMIT) {
            self::delete_all_cached_pages();
        }
    }
}
