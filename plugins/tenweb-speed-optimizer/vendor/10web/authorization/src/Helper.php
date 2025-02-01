<?php
namespace Tenweb_Authorization;
use Tenweb_Authorization\ProductState;
class Helper
{
    private static $site_state = array();
    private static $error_logs = array();
    private static $plugins_state = array();
    private static $themes_state = array();
    private static $addons_state = array();
    private static $domain_id;

    private static $instance;
    private static $network_domain_id;
    private static $auto_update_plugins;
    private $login_instance;
    public static $products_raw_data = array();

    private static $installed_plugins_wp_info = null;
    private static $installed_themes_wp_info = null;
    private static $expiration = array(
        'send_states'     => array(
            'expiration' => 43200,//12 hour 43200
            'block_time' => 300,//5 minute 300
        ),
        'user_info'       => array(
            'expiration' => 43200,//12 hour
            'block_time' => 300,//5 minute
        ),
        'user_agreements' => array(
            'expiration' => 43200,//12 hour
            'block_time' => 300,//5 minute
        ),
        'user_products'   => array(
            'expiration' => 86400,//24 hour
            'block_time' => 300,//5 minute
        ),
    );

    private static $plugins = array();
    private static $themes = array();
    private static $addons = array();

    public static $notices = array();
    public $last_response;

    public function __construct()
    {
        $this->login_instance = Login::get_instance();
        self::$network_domain_id = get_site_option('tenweb_domain_id');
        self::$domain_id = get_option('tenweb_domain_id');
    }

    public static function get_site_info($blog_id = null, $reset = false)
    {
        if (self::$site_state != null && $reset === false && is_null($blog_id)) {
            return self::$site_state;
        }

        global $wp_version, $wpdb;

        if ((is_multisite() && is_null($blog_id)) || (is_multisite() && $blog_id == 'multisite')) {
            $home_url = network_admin_url();// or site_url
            $admin_url = network_admin_url();
            $site_title = get_site_option('site_name');
        } else {
            $home_url = get_home_url($blog_id);
            $admin_url = get_admin_url($blog_id);
            $site_title = get_bloginfo('name');
        }

        $sql_version = $wpdb->get_var("SELECT VERSION() AS version");

        if (is_multisite() && $blog_id && $blog_id != 'multisite') {
            $time_zone = get_blog_option($blog_id, 'timezone_string');
        } else {
            $time_zone = get_option('timezone_string');
        }

        if (empty($time_zone)) {
            $time_zone = date_default_timezone_get();
            if (!$time_zone || empty($time_zone)) {
                $time_zone = "America/Los_Angeles";
            }
        }

        $server_software = isset($_SERVER['SERVER_SOFTWARE']) && trim($_SERVER['SERVER_SOFTWARE']) !== '' ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';

        $iowd_version = defined('TENWEBIO_VERSION') ? 'iowd_'. TENWEBIO_VERSION : 'iowd_';
        $site_info = array(
            'platform'            => 'wordpress',
            'site_url'            => $home_url,
            'admin_url'           => $admin_url,
            'name'                => $home_url,
            'site_title'          => $site_title,
            'site_screenshot_url' => $home_url,
            'platform_version'    => $wp_version,
            'php_version'         => PHP_VERSION,
            'mysql_version'       => $sql_version,
            'timezone'            => $time_zone,//todo check on multisite
            'server_type'         => $server_software,
            'server_version'      => $server_software,
            'other_data'          => array(
                'file_system'     => array(
                    'method' => self::get_fs_method(),
                    'config' => self::check_fs_configs() ? 1 : 0
                ),
                "is_network"      => ((is_multisite()) ? 1 : 0),
                "blog_id"         => $blog_id,
                "manager_version" => TENWEB_VERSION
            ),
            "is_network"          => ((is_multisite()) ? 1 : 0),
            "manager_version"     =>  get_site_option(TENWEB_PREFIX . '_from_image_optimizer') ? $iowd_version : TENWEB_VERSION,
        );

        if (is_multisite() && is_numeric($blog_id)) {
            $blog_details = get_blog_details($blog_id);
            if (!empty($blog_details)) {
                $site_info['other_data']['multisite_data'] = array(
                    'registered'   => $blog_details->registered,
                    'last_updated' => $blog_details->last_updated,
                );
            }
        }

        self::$site_state = $site_info;

        return self::$site_state;
    }

    public static function get_blogs_info()
    {
        $domains = array();
        if (is_multisite()) {
            $sites = get_sites();
            foreach ($sites as $site) {
                $blog_time_zone = get_blog_option($site->blog_id, 'timezone_string');
                if (empty($blog_time_zone)) {
                    $blog_time_zone = date_default_timezone_get();
                    if (!$blog_time_zone || empty($blog_time_zone)) {
                        $blog_time_zone = "America/Los_Angeles";
                    }
                }

                $blog_details = get_blog_details($site->blog_id);
                $domains[$site->blog_id]['site_url'] = get_home_url($site->blog_id);
                $domains[$site->blog_id]['admin_url'] = get_admin_url($site->blog_id);
                $domains[$site->blog_id]['site_title'] = $blog_details->blogname;
                $domains[$site->blog_id]['timezone'] = $blog_time_zone;
                $domains[$site->blog_id]['name'] = get_home_url($site->blog_id);
            }
        }

        return $domains;
    }

    public static function set_error_log($key, $msg)
    {
        $logs = self::get_error_logs();
        $logs[$key] = array('msg' => $msg, 'date' => date('Y-m-d H:i:s'));
        $expiration = 31 * 24 * 60 * 60;
        set_site_transient(TENWEB_PREFIX . '_auth_error_logs', $logs, $expiration);
        self::$error_logs = $logs;
    }
    public static function clear_cache()
    {
        delete_site_transient(TENWEB_PREFIX . '_client_products_transient');
        delete_site_transient(TENWEB_PREFIX . '_send_states_transient');
        delete_site_transient(TENWEB_PREFIX . '_user_info_transient');
        delete_site_transient(TENWEB_PREFIX . '_user_agreements_transient');
        delete_site_option(TENWEB_PREFIX . '_requests_block');
    }

    public static function check_site_state($force_send = false, $screen_id = null, $current_blog_id = null, $additional_data=null)
    {
        if (is_multisite()) {
            if (in_array($screen_id, array('options-general', 'site-info-network', 'plugins', 'themes'))) {
                switch_to_blog($current_blog_id);
                self::check_site_state_single($force_send, $current_blog_id);
                restore_current_blog();
            } else if ($screen_id == 'settings-network') {
                self::check_site_state_single($force_send, 'multisite');
            } else {
                self::check_site_state_single($force_send, 'multisite');
                $sites = get_sites();
                foreach ($sites as $site) {
                    switch_to_blog($site->blog_id);
                    self::check_site_state_single($force_send, $site->blog_id);
                    restore_current_blog();
                }
            }
        } else {
            self::check_site_state_single($force_send, null, $additional_data);
        }
    }

    public static function get_error_logs()
    {

        if (self::$error_logs == null) {
            $logs = get_site_transient(TENWEB_PREFIX . '_auth_error_logs');
            if (!is_array($logs)) {
                $logs = array();
            }
            self::$error_logs = $logs;
        }

        return self::$error_logs;
    }

    public static function remove_error_logs(){
      delete_site_transient(TENWEB_PREFIX . '_auth_error_logs');
    }

    private static function check_site_state_single($force_send, $blog_id = null, $additional_data=null)
    {
        $self = self::get_instance();
        $self->set_products();
        self::site_state($force_send, $blog_id, $additional_data);
    }

    public static function site_state($force_send = false, $current_blog_id = null, $additional_data=null)
    {
        $plugins_hash = get_option(TENWEB_PREFIX . '_plugins_state_hash');
        $themes_hash = get_option(TENWEB_PREFIX . '_themes_state_hash');
        $addons_hash = get_option(TENWEB_PREFIX . '_addons_state_hash');
        if ($current_blog_id == 'multisite') {
            $site_hash = get_site_option(TENWEB_PREFIX . '_site_state_hash');
        } else {
            $site_hash = get_option(TENWEB_PREFIX . '_site_state_hash');
        }
        $plugins_current_state = md5(json_encode(self::$plugins_state));
        $themes_current_state = md5(json_encode(self::$themes_state));
        $addons_current_state = md5(json_encode(self::$addons_state));
        $site_info = self::get_site_info($current_blog_id);
        $site_current_state = md5(json_encode($site_info));

        if ($force_send === false) {
            /* transient expired after 12 hour*/
            $transient = get_site_transient(TENWEB_PREFIX . '_send_states_transient');
        } else {
            $transient = false;
        }

        $state_data = array("blog_id" => $current_blog_id);
        if ($current_blog_id != 'multisite') {
            if ($plugins_hash !== $plugins_current_state || $transient == false) {

                $state_data['plugins_info'] = array(
                    "is_network" => ((is_multisite()) ? 1 : 0),
                    "products"   => self::states_to_array(self::$plugins_state)
                );
                update_option(TENWEB_PREFIX . '_plugins_state_hash', $plugins_current_state);
            }
            if ($themes_hash !== $themes_current_state || $transient == false) {

                $state_data['themes_info'] = array(
                    "is_network" => ((is_multisite()) ? 1 : 0),
                    "products"   => self::states_to_array(self::$themes_state)
                );
                update_option(TENWEB_PREFIX . '_themes_state_hash', $themes_current_state);
            }

            if ($addons_hash !== $addons_current_state || $transient == false) {

                $state_data['addons_info'] = array(
                    "is_network" => ((is_multisite()) ? 1 : 0),
                    "products"   => self::states_to_array(self::$addons_state)
                );
                update_option(TENWEB_PREFIX . '_addons_state_hash', $addons_current_state);
            }
        }
        if ($site_hash !== $site_current_state || $transient == false) {

            $state_data['site_info'] = $site_info;
            if ($current_blog_id == 'multisite') {
                update_site_option(TENWEB_PREFIX . '_site_state_hash', $site_current_state);
            } else {
                update_option(TENWEB_PREFIX . '_site_state_hash', $site_current_state);
            }
        }

        if (!empty($state_data)) {
            if ($current_blog_id == 'multisite') {
                $domain_id = get_site_option('tenweb_domain_id');
            } else {
                $domain_id = get_option('tenweb_domain_id');
            }
            self::set_domain_id($domain_id);

            $result = self::send_site_state($state_data, $additional_data);

            $send_all_data = (
                !empty($state_data['plugins_info']) &&
                !empty($state_data['themes_info']) &&
                !empty($state_data['addons_info']) &&
                !empty($state_data['site_info'])
            );

            if ($send_all_data == true && $result == true) {
                $expiration = self::$expiration['send_states']['expiration'];
                self::calc_request_block('send_states', true);
            } else {
                $block_count = self::calc_request_block('send_states');
                $expiration = self::$expiration['send_states']['block_time'] * $block_count;
            }

            set_site_transient(TENWEB_PREFIX . '_send_states_transient', '1', $expiration);
            do_action('tenweb_state_changed');
        }
    }
    public static function states_to_array($states = array())
    {
        foreach ($states as $key => $state) {
            if ($state instanceof ProductState) {
                $states[$key] = $state->get_info();
            } else {
                unset($states[$key]);
            }
        }

        if (!is_array($states)) {
            return array();
        }

        return $states;
    }
    public static function send_site_state($data, $additional_data=null)
    {
        $connected_from = get_site_option(TENWEB_PREFIX . '_connected_from');
        if ($connected_from == 'speed_optimizer') {
            $data = self::filter_plugins_data($data, $additional_data);
        }
        if(empty(self::$domain_id)) {
            self::$domain_id = get_option('tenweb_domain_id');
        }
        $url = TENWEB_API_URL . '/site-state/' . self::$domain_id;
        if (!empty($data["site_info"])) {
            $data["site_info"]["other_data"] = json_encode($data["site_info"]["other_data"]);
        }
        $args = array(
            'method' => 'POST',
            'body'   => array('data' => $data)
        );
        $self = self::get_instance();
        $response = $self->request($url, $args, 'send_site_state');


        if ($response == null || isset($response['error'])) {
            false;
        }

        return true;
    }

    public static function set_domain_id($domain_id)
    {
        self::$domain_id = $domain_id;
    }

    public function request($url, $args = array(), $error_key = null)
    {
        $blocked_request_option = get_site_transient(TENWEB_PREFIX . '_refresh_request_count', 0);

        if ($blocked_request_option > 2 && $error_key !== 'check_single_token') {
            return null;
        }

        if ($this->check_url($url) === false) {
            return null;
        }

        if (empty($args['headers'])) {
            $args['headers'] = array();
        }

        if ($error_key == null) {
            $error_key = uniqid();
        }

        $args['headers']["Authorization"] = "Bearer " . $this->login_instance->get_access_token();
        if (empty($args['headers']["Accept"])) {
            $args['headers']["Accept"] = "application/x.10webmanager.v1+json";
        }
        $args['timeout'] = 50000;
        $result = wp_remote_request($url, $args);

        $this->last_response = $result;


        if (is_wp_error($result)) {
            self::set_error_log($error_key . '_wp_error', $result->get_error_message());

            return null;
        }

        $body = json_decode($result['body'], true);
        unset($args['headers']["Authorization"]); //do not log Auth token

        $code = wp_remote_retrieve_response_code($result);
        $is_hosted_website = self::check_if_manager_mu();
        /* token refresh */
        if (
            $code == 401 &&
            isset($body['error']['status_code']) && $body['error']['status_code'] == 401 &&
            isset($body['error']['message']) &&
            $body['error']['message'] == '10WebError:Authorization Error') {

            self::set_error_log($error_key . '_token_error', json_encode($body['error']));

            $in_progress_key = TENWEB_PREFIX . '_refreshing_token_in_progress';
            $refreshing_token_in_progress = get_site_transient($in_progress_key);

            if ($refreshing_token_in_progress) {
                // Do NOT allow other refresh tokens while one of them is in progress
                return $body;
            } else {
                set_site_transient($in_progress_key, 1, 300);

                $token_refreshed = $this->refresh_token();

                delete_site_transient($in_progress_key);

                if ($token_refreshed) {
                    set_site_transient(TENWEB_PREFIX . '_refresh_request_count', ($blocked_request_option + 1), 3600 * 2); // 2 hours
                    // repeat current request
                    return $this->request($url, $args = array(), $error_key = null);
                } else {
                    // error log already preserved
                    // force logout, token_refresh failed
                    if (!$is_hosted_website) {
                        $this->login_instance->logout(false);
                    }

                    return $body;
                }
            }
        } else if ($code == 401) { // unknown authorization error
            self::set_error_log($error_key . '_api_auth_error', json_encode([$body['error'], $url, $args]));
            if (!$is_hosted_website) {
                $this->login_instance->logout(false);
            }

            return $body;
        } else if (isset($body['error'])) {   // other errors
            self::set_error_log($error_key . '_api_error', json_encode([$body['error'], $url, $args]));
        }


        return $body;
    }

    public function check_url($url)
    {
        global $tenweb_services;
        $parsed_url = parse_url($url);

        return (in_array($parsed_url['host'], $tenweb_services));
    }

    public static function check_if_manager_mu()
    {
        if (is_file(WPMU_PLUGIN_DIR . '/10web-manager/10web-manager.php')) {
            return true;
        }

        return false;
    }

    public function refresh_token()
    {
        $tokens_data = array(
            'refresh_token' => $this->login_instance->get_refresh_token(),
            'access_token'  => $this->login_instance->get_access_token(),
        );


        $url = TENWEB_API_URL . '/token/refresh';
        $args = array(
            'method'  => 'POST',
            'body'    => $tokens_data,
            'headers' => array(
                'Accept' => "application/x.10webmanager.v1+json"
            )
        );

        $this->login_instance->set_access_token(false);
        $result = wp_remote_request($url, $args);


        if (is_wp_error($result)) {
            self::set_error_log('refresh_token_error', $result->get_error_message());

            return false;
        }

        $res_array = json_decode($result['body'], true);

        if (isset($res_array['error'])) {
            /*API error */

            self::set_error_log('refresh_token_error', json_encode($res_array['error']));

            $this->login_instance->set_refresh_token(false);

            return false;

        } else if (isset($res_array['status']) && $res_array['status'] == 'ok') {

            /* success */

            $access_token = isset($res_array['token']) ? $res_array['token'] : false;
            $refresh_token = isset($res_array['refresh_token']) ? $res_array['refresh_token'] : false;


            self::set_error_log('refresh_token_success', ($access_token ? 'A' : '') . ($refresh_token ? 'R' : ''));

            $this->login_instance->set_access_token($access_token);
            $this->login_instance->set_refresh_token($refresh_token);

            return true;
        } else {
            /* unknown error */
            self::set_error_log('refresh_token_error', "unknown error");

            return false;
        }

    }

    public static function calc_request_block($key, $reset = false)
    {

        $blocks = get_site_option(TENWEB_PREFIX . '_requests_block');

        if (!is_array($blocks)) {
            $blocks = array();
        }

        if ($reset == true) {
            $blocks[$key] = 0;
            update_site_option(TENWEB_PREFIX . '_requests_block', $blocks);

            return 1;
        }

        if (!isset($blocks[$key]) || $blocks[$key] < 0) {
            $blocks[$key] = 0;
        }

        $blocks[$key] = ($blocks[$key] == 0) ? 1 : $blocks[$key] * 2;


        if ($blocks[$key] > 200) {
            $blocks[$key] = 200;
        }

        update_site_option(TENWEB_PREFIX . '_requests_block', $blocks);

        return $blocks[$key];
    }
    public static function get_fs_method()
    {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/misc.php'); // extract_from_markers() wp-super-cache deactivation fatal error fix

        return get_filesystem_method();
    }

    public static function check_fs_configs()
    {

        $fs_method = self::get_fs_method();

        if ($fs_method == "direct") {
            return true;
        }

        $credentials['connection_type'] = $fs_method;
        $credentials['hostname'] = (defined('FTP_HOST')) ? FTP_HOST : "";
        $credentials['username'] = (defined('FTP_USER')) ? FTP_USER : "";
        $credentials['password'] = (defined('FTP_PASS')) ? FTP_PASS : "";
        $credentials['public_key'] = (defined('FTP_PUBKEY')) ? FTP_PUBKEY : "";
        $credentials['private_key'] = (defined('FTP_PRIKEY')) ? FTP_PRIKEY : "";

        if (
            (!empty($credentials['password']) && !empty($credentials['username']) && !empty($credentials['hostname'])) ||
            ('ssh' == $credentials['connection_type'] && !empty($credentials['public_key']) && !empty($credentials['private_key']))
        ) {
            return true;
        } else {
            return false;
        }

    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function set_products($reset = false)
    {
        $in_progress_key = TENWEB_PREFIX . '_setting_products_in_progress';
        $setting_products_in_progress = get_site_transient($in_progress_key);


        $plugins = get_site_option(TENWEB_PREFIX . '_plugins_list');
        $themes = get_site_option(TENWEB_PREFIX . '_themes_list');
        $addons = get_site_option(TENWEB_PREFIX . '_addons_list');

        $transient = get_site_transient(TENWEB_PREFIX . '_client_products_transient');
        if (($transient === false || $reset === true) && !$setting_products_in_progress) {
            set_site_transient($in_progress_key, 1, 300);

            $products = $this->get_products();

            if (!(empty($products['plugins']) && empty($products['themes']) && empty($products['addons']))) {
                $plugins = $products['plugins'];
                $themes = $products['themes'];
                $addons = $products['addons'];

                update_site_option(TENWEB_PREFIX . '_plugins_list', $plugins);
                update_site_option(TENWEB_PREFIX . '_themes_list', $themes);
                update_site_option(TENWEB_PREFIX . '_addons_list', $addons);

                self::calc_request_block('user_products', true);
                $expiration = self::get_expiration('user_products');
                $expiration = $expiration['expiration'];
            } else {

                $block_count = self::calc_request_block('user_products');

                $expiration = self::get_expiration('user_products');
                $expiration = $expiration['block_time'] * $block_count;
            }

            set_site_transient(TENWEB_PREFIX . '_client_products_transient', '1', $expiration);
        }

        //if first api call failed
        $plugins = (!is_array($plugins)) ? array() : $plugins;
        $themes = (!is_array($themes)) ? array() : $themes;
        $addons = (!is_array($addons)) ? array() : $addons;

        self::$products_raw_data = array('plugins' => $plugins, 'themes' => $themes, 'addons' => $addons);

        $products_objects = self::get_products_objects($plugins, $themes, $addons);

        self::$plugins = $products_objects['plugins'];
        self::$themes = $products_objects['themes'];
        self::$addons = $products_objects['addons'];

        delete_site_transient($in_progress_key);
    }

    public function get_products($type = 'all')
    {

        $result = array(
            'plugins' => array(),
            'themes'  => array(),
            'addons'  => array()
        );
        $endpoint = TENWEB_API_URL . '/products';

        /*$data = $this->get_product_data_from_api($endpoint . '/plugins');

        if (!empty($data)) {
            $result['plugins'] = $data;
        }*/

        $data = $this->get_product_data_from_api($endpoint);

        if (!empty($data['plugins'])) {
            $result['plugins'] = $data['plugins'];
        }
        if (!empty($data['themes'])) {
            $result['themes'] = $data['themes'];
        }
        if (!empty($data['addons'])) {
            $result['addons'] = $data['addons'];
        }


        return $result;
    }

    public function get_product_data_from_api($url)
    {
        $args = array(
            'method' => 'GET',
        );

        $response = $this->request($url, $args, 'get_product_data');

        if ($response == null || isset($response['error'])) {
            null;
        }

        if (!empty($response['data'])) {
            return $response['data'];
        }

        return array();
    }

    public static function get_products_objects($plugins_data = array(), $themes_data = array(), $addons_data = array())
    {

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        self::$plugins_state = array();
        self::$addons_state = array();
        self::$themes_state = array();
        self::$auto_update_plugins = get_site_option('auto_update_plugins', array());

        $site_installed_plugins = get_plugins();
        //if domain hosted on 10Web
        $mu_plugins = get_mu_plugins();
        if (isset($mu_plugins['tenweb-init.php'])) {
            $site_installed_plugins['10web-manager/10web-manager.php'] = $mu_plugins['tenweb-init.php'];
        }

        $site_installed_themes = wp_get_themes(array('errors' => null));

        $plugins = self::get_plugins_objects($plugins_data, $site_installed_plugins);
        $addons = self::get_plugins_objects($addons_data, $site_installed_plugins, true);
        $themes = self::get_themes_objects($themes_data, $site_installed_themes);

        $result = array(
            'plugins' => $plugins,
            'addons'  => $addons,
            'themes'  => $themes
        );
        self::add_more_states($site_installed_plugins, $site_installed_themes);

        return $result;
    }


    private static function add_more_states($site_installed_plugins, $site_installed_themes)
    {

        foreach ($site_installed_plugins as $file_name => $installed_plugin) {
            $slug = explode('/', $file_name);
            $slug = $slug[0];

            $found = false;
            foreach (self::$plugins_state as $state) {
                if ($state->slug === $slug) {
                    $found = true;
                    break;
                }
            }


            if ($found === false) {
                foreach (self::$addons_state as $state) {
                    if ($state->slug === $slug) {
                        $found = true;
                        break;
                    }
                }
            }

            if ($found === false) {
                $state = new ProductState(
                    0,
                    $slug,
                    $installed_plugin['Title'],
                    $installed_plugin['Description'],
                    'plugin',
                    $installed_plugin['Version'],
                    1
                );

                $state->set_active($file_name);
                $state->set_tenweb_product(false);
                $state->set_other_wp_info($file_name, $installed_plugin, self::get_installed_plugins_wp_info());
                $state->set_is_autoupdate_enabled(in_array($file_name, self::$auto_update_plugins, true));


                self::$plugins_state[] = $state;
            }

        }

        foreach ($site_installed_themes as $slug => $installed_theme) {

            $found = false;
            foreach (self::$themes_state as $state) {
                if ($state->slug === $slug) {
                    $found = true;
                    break;
                }
            }

            if ($found === false) {
                $state = new ProductState(
                    0,
                    $slug,
                    $installed_theme['Name'],
                    $installed_theme->get('Description'),
                    'theme',
                    $installed_theme['Version'],
                    1
                );

                $state->set_active($slug);
                $state->set_screenshot(self::get_theme_screenshot_url($slug));
                $state->set_tenweb_product(false);
                $state->set_other_wp_info($slug, $installed_theme, self::get_installed_themes_wp_info());

                self::$themes_state[] = $state;
            }

        }
    }

    private static function get_themes_objects($themes_data, $site_installed_themes)
    {


        $themes = array();
        $installed_themes = array();

        foreach ($themes_data as $theme_data) {

            if (isset($site_installed_themes[$theme_data['slug']])) {

                $installed_theme = $site_installed_themes[$theme_data['slug']];

                $state = new ProductState(
                    $theme_data['product_id'],
                    $theme_data['slug'],
                    $installed_theme['Name'],
                    $installed_theme->get('Description'),
                    'theme',
                    $installed_theme['Version'],
                    1
                );

                $state->set_active($theme_data['slug']);
                $state->set_screenshot(self::get_theme_screenshot_url($theme_data['slug']));
                $state->set_is_paid($theme_data['current_version']);
                $state->set_other_wp_info($theme_data['slug'], $installed_theme, self::get_installed_themes_wp_info());

                self::$themes_state[] = $state;

                $theme = new InstalledTheme(
                    $state,
                    $theme_data['product_id'],
                    $theme_data['slug'],
                    $theme_data['title'],
                    $theme_data['description']
                );

                $theme->set_product_data($theme_data);
                $installed_themes[] = $theme;

            } else {

                $theme = new Product(
                    $theme_data['product_id'],
                    $theme_data['slug'],
                    $theme_data['title'],
                    $theme_data['description'],
                    'theme'
                );

                $theme->set_product_data($theme_data);
                $themes[] = $theme;

            }

        }

        return array(
            'installed_products' => $installed_themes,
            'products'           => $themes
        );
    }

    private static function get_plugins_objects($plugins_data, $site_installed_plugins, $addons = false)
    {

        $plugins = array();
        $installed_plugins = array();

        $manager_exists = false;
        $installed_plugins_info = self::get_installed_plugins_wp_info();
        foreach ($plugins_data as $plugin_data) {

            $plugin_slug = null;
            foreach ($site_installed_plugins as $slug => $plugin) {
                $slug_data = explode('/', $slug);
                if ($slug_data[0] == $plugin_data['slug']) {
                    $plugin_slug = $slug;
                    break;
                }
            }

            if ($plugin_slug != null) {
                $installed_plugin = $site_installed_plugins[$plugin_slug];

                if ($plugin_data['product_id'] == TENWEB_MANAGER_ID) {
                    $manager_exists = true;
                }

                $state = new ProductState(
                    $plugin_data['product_id'],
                    $plugin_data['slug'],
                    $installed_plugin['Title'],
                    $installed_plugin['Description'],
                    'plugin',
                    $installed_plugin['Version'],
                    1
                );

                $state->set_active($plugin_slug);
                $state->set_is_paid($plugin_data['current_version']);
                $state->set_other_wp_info($plugin_slug, $installed_plugin, $installed_plugins_info);
                $state->set_is_autoupdate_enabled(in_array($plugin_slug, self::$auto_update_plugins, true));


                if ($addons == true) {
                    self::$addons_state[] = $state;
                } else {
                    self::$plugins_state[] = $state;
                }


                $plugin = new InstalledPlugin(
                    $state,
                    $plugin_data['product_id'],
                    $plugin_data['slug'],
                    $plugin_data['title'],
                    $plugin_data['description'],
                    $plugin_slug
                );

                $plugin->set_product_data($plugin_data);
                $installed_plugins[] = $plugin;
            } else {

                $plugin = new Product(
                    $plugin_data['product_id'],
                    $plugin_data['slug'],
                    $plugin_data['title'],
                    $plugin_data['description']
                );

                $plugin->set_product_data($plugin_data);
                $plugins[] = $plugin;
            }

        }

        if ($manager_exists == false && $addons == false && is_admin()) {
            $plugin = self::create_manager_plugin_object();
            $installed_plugins[] = $plugin;
            $notice = "Fail on connection with api. <a href='#' class='tenweb_clear_cache_button'>Try again</a>";
            self::add_notices($notice);
        }

        return array(
            'installed_products' => $installed_plugins,
            'products'           => $plugins
        );
    }

    public static function get_theme_screenshot_url($slug)
    {
        $theme_folder = get_theme_root();
        $theme_folder .= '/' . $slug;

        //file extensions https://codex.wordpress.org/Theme_Development#Screenshot
        $file_name = "";
        if (file_exists($theme_folder . '/screenshot.png')) {
            $file_name = 'screenshot.png';
        } else if (file_exists($theme_folder . '/screenshot.jpg')) {
            $file_name = 'screenshot.jpg';
        } else if (file_exists($theme_folder . '/screenshot.jpeg')) {
            $file_name = 'screenshot.jpeg';
        } else if (file_exists($theme_folder . '/screenshot.gif')) {
            $file_name = 'screenshot.gif';
        }

        if (!empty($file_name)) {
            $file = get_theme_root_uri();
            $file .= '/' . $slug . '/' . $file_name;

            return $file;
        } else {
            return "";
        }

    }


    public static function get_expiration($key)
    {
        return (isset(self::$expiration[$key])) ? self::$expiration[$key] : null;
    }

    public static function get_installed_plugins_wp_info()
    {

        if (self::$installed_plugins_wp_info === null) {

            include_once ABSPATH . WPINC . '/update.php';
            wp_update_plugins();
            self::$installed_plugins_wp_info = get_site_transient('update_plugins');
            self::filter_installed_plugins_wp_info();
        }
        return self::$installed_plugins_wp_info;
    }

    public static function get_installed_themes_wp_info()
    {

        if (self::$installed_themes_wp_info === null) {

            include_once ABSPATH . WPINC . '/update.php';
            wp_update_themes();
            self::$installed_themes_wp_info = get_site_transient('update_themes');
            self::filter_installed_themes_wp_info();
        }
        return self::$installed_themes_wp_info;
    }


    private static function filter_installed_plugins_wp_info()
    {
        $slugs = array(
            'js_composer/js_composer.php',
            'elementor-pro/elementor-pro.php',
            'wordpress-seo-premium/wp-seo-premium.php'
        );

        foreach ($slugs as $slug) {

            if (isset(self::$installed_plugins_wp_info->response[$slug])) {
                unset(self::$installed_plugins_wp_info->response[$slug]);
            }

            if (isset(self::$installed_plugins_wp_info->no_update[$slug])) {
                unset(self::$installed_plugins_wp_info->no_update[$slug]);
            }
        }
    }

    private static function filter_installed_themes_wp_info()
    {
        $slugs = array('divi');

        foreach ($slugs as $slug) {
            if (isset(self::$installed_themes_wp_info->response[$slug])) {
                unset(self::$installed_themes_wp_info->response[$slug]);
            }
        }

    }

    public function get_amazon_tokens($product_id)
    {
        $url = TENWEB_API_URL . '/products/' . $product_id . '/request';
        $args = array(
            'method' => 'GET',
        );

        $response = $this->request($url, $args, 'get_amazon_tokens');
        if ($response == null || isset($response['error'])) {
            return null;
        }

        return $response;
    }

    public static function send_state_before_deactivation()
    {
        if (is_multisite()) {
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                self::send_state_before_deactivation_single();
                restore_current_blog();
            }
        } else {
            self::send_state_before_deactivation_single();
        }
    }
    private static function send_state_before_deactivation_single()
    {
        $self = self::get_instance();
        $self->set_products();

        foreach (self::$plugins_state as $i => $state) {
            if ($state->product_id == TENWEB_MANAGER_ID) {
                $state->active = false;
            }
        }

        $manager_info = self::get_manager_info();
        $result = self::send_site_state($manager_info);
    }

    public static function get_manager_info()
    {
        return array(
            'site_info'    => self::get_site_info(),
            'plugins_info' => array(
                "is_network" => ((is_multisite()) ? 1 : 0),
                "products"   => self::states_to_array(self::$plugins_state)
            ),
            'themes_info'  => array(
                "is_network" => ((is_multisite()) ? 1 : 0),
                "products"   => self::states_to_array(self::$themes_state)
            ),
            'addons_info'  => array(
                "is_network" => ((is_multisite()) ? 1 : 0),
                "products"   => self::states_to_array(self::$addons_state)
            )
        );
    }
    public static function get_site_full_state()
    {

        $plugins_state = array();
        $themes_state = array();

        $plugins = get_plugins();

        foreach ($plugins as $slug => $plugin) {
            $state = new ProductState($slug, $slug, $plugin['Title'], $plugin['Description'], 'plugin', $plugin['Version'], 1);
            $state->set_active($slug);
            $plugins_state[] = $state->get_wp_info();
        }

        $themes = wp_get_themes(array('errors' => null));
        foreach ($themes as $slug => $theme) {
            $state = new ProductState($slug, $slug, $theme['Name'], $theme->get('Description'), 'theme', $theme['Version'], 1);
            $state->set_active($slug);
            $state->set_screenshot(self::get_theme_screenshot_url($slug));
            $themes_state[] = $state->get_wp_info();
        }

        return array(
            'site_info' => self::get_site_info(),
            'plugins'   => $plugins_state,
            'themes'    => $themes_state
        );

    }

    private static function create_manager_plugin_object()
    {
        $plugin_slug = explode('/', TENWEB_SLUG);
        $plugin_slug = $plugin_slug[0];


        $state = new ProductState(
            TENWEB_MANAGER_ID,
            $plugin_slug,
            "10WEB Manager",
            "",
            'plugin',
            "0.0.0",
            1
        );

        $state->active = true;
        $state->is_paid = false;

        self::$plugins_state[] = $state;

        $plugin = new InstalledPlugin(
            $state,
            TENWEB_MANAGER_ID,
            $plugin_slug,
            "10WEB Manager",
            "",
            TENWEB_SLUG
        );

        return $plugin;
    }
    public static function get_products_state()
    {
        return array(
            'plugins' => self::$plugins_state,
            'addons'  => self::$addons_state,
            'themes'  => self::$themes_state
        );
    }


    public static function get_site_info_diff($screen)
    {
        $current_blog_id = null;
        if (is_multisite()) {
            $current_blog_id = $screen->id == 'settings-network' ? 'multisite' : get_current_blog_id();
        }

        $site_info = self::get_site_info($current_blog_id);
        $site_current_state = md5(json_encode($site_info));

        if ($current_blog_id == 'multisite') {
            $site_hash = get_site_option(TENWEB_PREFIX . '_site_state_hash');
        } else {
            $site_hash = get_option(TENWEB_PREFIX . '_site_state_hash');
        }

        if ($site_hash !== $site_current_state) {
            return true;
        }

        return false;
    }

    public static function add_notices($notice_text, $error = true)
    {
        $container_class = "notice is-dismissible";
        if ($error) {
            $container_class .= " error";
        }
        if (!function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();
        $notice = '<div class="' . $container_class . ' tenweb_manager_notice ' . ($screen !== null && $screen->parent_base == "tenweb_menu" ? "tenweb_menu_notice" : "") . '">'
            . '<p>' . $notice_text . '</p>'
            . '</div>';
        self::$notices[] = $notice;
    }


    public function check_single_token($token, $check_for_network = false, $is_login = false, $email = null)
    {
        if ($check_for_network) {
            $domain_id = self::$network_domain_id;
        } else {
            $domain_id = self::$domain_id;
        }
        $body = array('one_time_token' => $token);

        if ($email) {
            $body['email'] = $email;
        }

        if ($is_login) {
            $body['is_login'] = true;
        }

        $args = array(
            'method' => 'POST',
            'body'   => $body
        );

        $url = TENWEB_API_URL . '/domains/' . $domain_id . '/check-single';
        $response = $this->request($url, $args, 'check_single_token');

        if ($response == null || isset($response['error'])) {
            return false;
        }

        return (!empty($response['status']) && $response['status'] == "ok");
    }

    private static function filter_plugins_data($data, $additional_data=null)
    {
        $slugs = array(
            'tenweb-speed-optimizer',
            'image-optimizer-wd'
        );
        foreach($data as $k => $v) {

            if ($k != "plugins_info" && $k != "site_info"){
                unset($data[$k]);
            } else if ($k == "plugins_info") {
                foreach ($data["plugins_info"] as $key => $value) {

                    if(!empty($value) and is_array($value)){
                        foreach ($value as $k=>$v){
                            if (!in_array($v["slug"], $slugs)){
                                unset($data["plugins_info"][$key][$k]);

                            } else {
                                if (!empty($additional_data) && isset($additional_data[$v["slug"]])){
                                    $data["plugins_info"][$key][$k]["active"] = $additional_data[$v["slug"]];
                                }
                            }
                        }
                    }
                }
            }
        }
        $data['plugins_info']['products'] = array_values($data['plugins_info']['products']);
        unset($data["themes_info"]);
        return $data;
    }
}