<?php

namespace TenWebOptimizer\WebPageCache;

use TenWebOptimizer\OptimizerUtils;

/*
 * Base class other (more-specific) classes inherit from.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This class will work with wp hooks/filter. Will decide when to clear cache, enable/disable web caching, etc.
 *
 * */
class OptimizerWebPageCacheWP
{
    protected static $instance = null;

    public $wp_config_file_path = ABSPATH . '/wp-config.php';

    public $page_cache_config_dir = WP_CONTENT_DIR . '/10web-page-cache-config';

    public $wp_cache_define = "define( 'WP_CACHE', true );\ndefine( 'TWO_PLUGIN_DIR_CACHE', '" . TENWEB_SO_PLUGIN_DIR . "' );";

    public $advanced_cache_path = WP_CONTENT_DIR . '/advanced-cache.php';

    public function __construct()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'two_clear_page_cache') {  // phpcs:ignore
            add_action('admin_init', [ $this, 'maybe_clear_page_cache' ]);
        }

        global $TwoSettings;

        if ($TwoSettings->get_settings('two_page_cache') === 'on' && get_option('show_on_front') === 'posts') {
            add_action('transition_post_status', [$this, 'transition_post_status'], 10, 3);
        }

        if ((!isset($_GET['action']) || $_GET['action'] != 'deactivate') // phpcs:ignore
            && (!isset($_GET['rest_route']) || $_GET['rest_route'] != '/tenweb_so/v1/set_modes')) { // phpcs:ignore
            add_action('update_option_two_settings', [ $this, 'enable_disable_page_cache' ], 10, 3);
        }

        add_filter('page_row_actions', [$this, 'post_row_actions'], 10, 2);
        add_filter('post_row_actions', [$this, 'post_row_actions'], 10, 2);

        if ($TwoSettings->get_settings('two_page_cache') === 'on' && $TwoSettings->get_settings('two_page_cache_user') === 'on') {
            if (is_user_logged_in()) {
                // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
                $logged_in_cookie = sanitize_text_field($_COOKIE[LOGGED_IN_COOKIE]);
                // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
                setcookie('tenweb_so_page_cache_hash', md5($logged_in_cookie), time() + (int) $TwoSettings->get_settings('two_page_cache_life_time'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            } else {
                if (isset($_COOKIE['tenweb_so_page_cache_hash'])) {
                    // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
                    setcookie('tenweb_so_page_cache_hash', '', time() + (int) $TwoSettings->get_settings('two_page_cache_life_time'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
                }
            }
        }
    }

    public function delete_all_cache()
    {
        OptimizerWebPageCache::delete_all_cached_pages();
    }

    public function post_updated($post_id, $post_after, $post_before)
    {
        OptimizerWebPageCache::delete_cache_by_post_id($post_id);
    }

    public function enable_disable_page_cache($old_value, $value, $option)
    {
        $value = json_decode($value, true);
        $value = (isset($value[ 'two_page_cache' ])) ? $value['two_page_cache'] : '';

        if ($value === 'on') {
            $this->store_page_cache_configs();

            if (!file_exists(WP_CONTENT_DIR . '/advanced-cache.php')) {
                $this->enable_page_cache();
            } else {
                $file_content = file_get_contents(WP_CONTENT_DIR . '/advanced-cache.php');

                if (strpos($file_content, 'TENWEB_SO_ADVANCED_CACHE') === false) {
                    $this->enable_page_cache();
                } elseif (!defined('WP_CACHE') || !WP_CACHE || !defined('TWO_PLUGIN_DIR_CACHE')) {
                    $this->add_wp_cache_constant();
                }
            }
        } else {
            $this->disable_page_cache();
        }
    }

    public function enable_page_cache()
    {
        if ($this->add_wp_cache_constant() === false) {
            return false;
        }

        return copy(TENWEB_SO_PLUGIN_DIR . 'includes/WebPageCache/advanced-cache.php', $this->advanced_cache_path);
    }

    public function disable_page_cache()
    {
        if ($this->remove_wp_cache_constant() === false) {
            return false;
        }

        if ($this->delete_page_cache_configs() === false) {
            return false;
        }

        if (!file_exists($this->advanced_cache_path)) {
            return true;
        }

        return unlink($this->advanced_cache_path); // phpcs:ignore
    }

    public function add_wp_cache_constant()
    {
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_is_writable
        if (!is_writable($this->wp_config_file_path) || !is_readable($this->wp_config_file_path)) {
            return false;
        }
        $file_content = file_get_contents($this->wp_config_file_path); // phpcs:ignore
        // Remove our constants to clear the file in case if somebody removed the WP_CACHE define
        $file_content = preg_replace('/# BEGIN WP Cache by 10Web[\s\S]*# END WP Cache by 10Web/', '', $file_content);
        // Remove WP_CACHE defined by others
        $file_content = OptimizerUtils::delete_define('WP_CACHE', $file_content);
        // Add new defines
        $replacement = "<?php\n# BEGIN WP Cache by 10Web\n" . $this->wp_cache_define . "\n# END WP Cache by 10Web\n";
        $file_content = preg_replace('@<\?php\s*@i', $replacement, $file_content, 1);

        return (bool) file_put_contents($this->wp_config_file_path, $file_content); // phpcs:ignore
    }

    public function remove_wp_cache_constant()
    {
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_is_writable
        if (!is_writable($this->wp_config_file_path) || !is_readable($this->wp_config_file_path)) {
            return false;
        }

        $file_content = file_get_contents($this->wp_config_file_path); // phpcs:ignore
        $file_content = preg_replace('/# BEGIN WP Cache by 10Web[\s\S]*# END WP Cache by 10Web/', '', $file_content);

        return (bool) file_put_contents($this->wp_config_file_path, $file_content); // phpcs:ignore
    }

    public function store_page_cache_configs()
    {
        if (!is_dir($this->page_cache_config_dir) && !mkdir($concurrentDirectory = $this->page_cache_config_dir, 0777) && !is_dir($concurrentDirectory)) { // phpcs:ignore
            return false;
        }
        $configs = [];
        $TwoSettings = new \TenWebOptimizer\OptimizerSettings();
        $configs['two_settings'] = json_encode($TwoSettings->get_settings()); // phpcs:ignore

        return (bool) file_put_contents($this->page_cache_config_dir . '/config.json', json_encode($configs)); // phpcs:ignore
    }

    public function delete_page_cache_configs()
    {
        if (file_exists($this->page_cache_config_dir . '/config.json')) {
            return unlink($this->page_cache_config_dir . '/config.json'); // phpcs:ignore
        }

        return true;
    }

    public function maybe_clear_page_cache()
    {
        if (!empty($_GET['permalink']) && wp_verify_nonce('two_clear_page_cache') !== null) {
            $permalink = sanitize_url($_GET['permalink']); // phpcs:ignore
            OptimizerUtils::clear_cloudflare_cache([$permalink]);
            OptimizerWebPageCache::delete_cache_by_url($permalink);
        }
        $redirect_to = (!empty($_SERVER['HTTP_REFERER'])) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : '/wp-admin/edit.php';

        wp_safe_redirect($redirect_to);
        die;
    }

    public function post_row_actions($actions, $post)
    {
        global $TwoSettings;

        if ($TwoSettings->get_settings('two_page_cache') !== 'on') {
            return $actions;
        }

        $url = wp_nonce_url(admin_url('admin-post.php?action=two_clear_page_cache&permalink=' . get_permalink($post)), 'two_clear_page_cache');
        $actions['two_clear_page_cache'] = sprintf('<a href="%s">%s</a>', $url, __('Clear page cache', 'tenweb-speed-optimizer'));

        return $actions;
    }

    public function transition_post_status($new_status, $old_status, $post)
    {
        if ($post->post_type !== 'post') {
            return;
        }

        if ($new_status === $old_status) {
            return;
        }

        // if status changed from publish (e.g. to trash) or to publish (e.g. from draft to publish) clear page cache
        if ($new_status === 'publish' || $old_status === 'publish') {
            OptimizerUtils::clear_cloudflare_cache([get_home_url()]);
            OptimizerWebPageCache::delete_cache_by_url(get_home_url());
        }
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
