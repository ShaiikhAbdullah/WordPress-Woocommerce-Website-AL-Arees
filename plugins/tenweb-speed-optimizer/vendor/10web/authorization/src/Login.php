<?php
namespace Tenweb_Authorization;

use Tenweb_Authorization\Helper;

class Login {

    protected static $instance = null;

    private $domain_id;
    private $access_token = false;
    private $refresh_token = false;
    private $connected_from;
    private $error_logs = array();

    protected function __construct()
    {
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $refresh_token = get_site_option(TENWEB_PREFIX . '_refresh_token');
        $connected_from = get_site_option(TENWEB_PREFIX . '_connected_from');
        $this->access_token = !empty($access_token) ? $access_token : false;
        $this->refresh_token = !empty($refresh_token) ? $refresh_token : false;
        $this->connected_from = !empty($connected_from) ? $connected_from : TENWEB_CONNECTED_MANAGER;
        add_action('init', array($this, 'fire_actions'));
    }

    public function login($email = "", $pwd = "", $site_type = "", $args = array())
    {

        /*if ($this->access_token !== false) {
            $this->error_logs["error"] = 1;
            $this->error_logs["message"] = 'User already logged in.';

            return false;
        }*/



        $blog_id = null;
        if (is_multisite()) {
            //CHECK get_site_info function
            $blog_id = 'multisite';
        }
        $body = Helper::get_site_info($blog_id);

        if (is_multisite()) {
            $body["domains"] = Helper::get_blogs_info();
        }

        $body['is_logged_in'] = false;
        if($this->check_logged_in()){
            $body['is_logged_in'] = true;
        }

        if (empty($email)) {
            $body['email'] = $_POST['email'];
            $body['password'] = $_POST['password'];
            if (isset($_POST['site_type'])) {
                $body['site_type'] = $_POST['site_type'];
            }
        } else {
            $body['email'] = $email;
            $body['password'] = $pwd;
            $body['site_type'] = $site_type;
        }


        $confirm_token = md5(uniqid(mt_rand(), true));
        $this->setConfirmToken($confirm_token);

        $body['confirm_token'] = $confirm_token;

        $body = $body + $args;

        $url = TENWEB_API_URL . '/login';
        $result = wp_remote_post($url, array(
            'method'  => 'POST',
            'body'    => $body,
            'timeout' => 1500,
            'headers' => array(
                "Accept" => "application/x.10webmanager.v1+json"
            )
        ));
        if (is_wp_error($result)) {
            Helper::set_error_log('login_wp_error', $result->get_error_message());
            $this->error_logs["error"] = 1;
            $this->error_logs["message"] = $result->get_error_message();

            return false;
        } else {
            $res_obj = json_decode($result['body']);

            if (isset($res_obj->error)) {
                Helper::set_error_log('login_error_from_api', json_encode($res_obj->error));
                $this->access_token = false;
                if (isset($res_obj->error->message)) {
                    $this->error_logs["error"] = 1;
                    $this->error_logs["message"] = $res_obj->error->message;
                }

                return false;

            } else if (isset($res_obj->status) && $res_obj->status == 'ok') {

                if(!empty($args['connected_from']) && $args['connected_from'] == 'ai_assistant') {
                    $this->set_access_token($res_obj->token, true);
                    $this->set_refresh_token($res_obj->refresh_token, true);
                    update_site_option(TENWEB_PREFIX . '_ai_assistant_domain_id', $res_obj->domain_id);
                } else {
                    $this->set_access_token($res_obj->token);
                    $this->set_refresh_token($res_obj->refresh_token);
                    update_site_option(TENWEB_PREFIX . '_domain_id', $res_obj->domain_id);
                }

                update_site_option(TENWEB_PREFIX . '_workspace_id', $res_obj->workspace_id);
                update_site_option(TENWEB_PREFIX . '_is_available', $res_obj->is_available . '');
                update_site_option(TENWEB_PREFIX . '_user_timezone_offset', $res_obj->timezone_offset);

                $user_info = array(
                    'client_info'    => array(
                        'name'            => $res_obj->client_name,
                        'timezone_offset' => $res_obj->timezone_offset
                    ),
                    'agreement_info' => $res_obj->agreement_info
                );

                set_site_transient(TENWEB_PREFIX . '_user_info_transient', '1', 43200); //12 hours
                update_site_option(TENWEB_PREFIX . '_user_info', $user_info);

                if(property_exists($res_obj, 'connected_from')) {
                    update_site_option(TENWEB_PREFIX . '_connected_from', $res_obj->connected_from);
                }

                if (property_exists($res_obj, 'two_flow_data')) {
                    if(property_exists($res_obj->two_flow_data, 'flow_id')) {
                        update_site_option(TENWEB_PREFIX . '_flow_id', $res_obj->two_flow_data->flow_id);
                    }

                    if(property_exists($res_obj->two_flow_data, 'notification_id')) {
                        update_site_option(TENWEB_PREFIX . '_notification_id', $res_obj->two_flow_data->notification_id);
                    }

                    if(property_exists($res_obj->two_flow_data, 'referral_hash')) {
                        update_site_option(TENWEB_PREFIX . '_client_referral_hash', $res_obj->two_flow_data->referral_hash);
                    }
                }

                if (is_multisite() && !empty($res_obj->domains)) {
                    foreach ($res_obj->domains as $blog_id => $domain) {
                        if (!empty($res_obj->connected_from) && $res_obj->connected_from == 'ai_assistant') {
                            update_blog_option($blog_id, TENWEB_PREFIX . '_ai_assistant_domain_id', $domain->domain_id);
                        } else {
                            update_blog_option($blog_id, TENWEB_PREFIX . '_domain_id', $domain->domain_id);
                        }
                        update_blog_option($blog_id, TENWEB_PREFIX . '_is_available', $domain->is_available);
                    }
                }

                $this->domain_id = $res_obj->domain_id;
                /* create 10web user */

              //  $user = User::get_instance($res_obj->password);
                Helper::clear_cache();
                Helper::check_site_state(true);

                do_action('tenweb_logged_in');

                return true;
            }
        }

        return false;
    }

    public function get_access_token()
    {
        return $this->access_token;
    }

    public function set_access_token($token, $ai_assistant = false)
    {
        $this->access_token = $token;
        $this->save_access_token($token, $ai_assistant);
    }

    public function get_refresh_token()
    {
        return $this->refresh_token;
    }

    public function set_refresh_token($token, $ai_assistant = false)
    {
        $this->refresh_token = $token;
        $this->save_refresh_token($token, $ai_assistant);
    }

    private function save_access_token($new_token, $ai_assistant = false)
    {
        if ($ai_assistant) {
            update_site_option(TENWEB_PREFIX . '_ai_assistant_access_token', $new_token);
        } else {
            update_site_option(TENWEB_PREFIX . '_access_token', $new_token);
        }
    }

    private function save_refresh_token($new_token, $ai_assistant=false)
    {

        if ($ai_assistant) {
            update_site_option(TENWEB_PREFIX . '_ai_assistant_refresh_token', $new_token);
        } else {
            update_site_option(TENWEB_PREFIX . '_refresh_token', $new_token);
        }
    }

    public function logout($redirect = true, $disconnected_service = 'manager')
    {
        // $workspace_id = \TenwebServices::get_workspace_id();
        $args = array();
        $domain_id = get_site_option('tenweb_domain_id');
        if ($disconnected_service == 'ai_assistant') {
            $domain_id = get_site_option('tenweb_ai_assistant_domain_id');
            $this->access_token = get_site_option(TENWEB_PREFIX . '_ai_assistant_access_token');
            $this->refresh_token = get_site_option(TENWEB_PREFIX . '_ai_assistant_refresh_token');
            $args['disconnected_service'] = $disconnected_service;
        }
        $url = TENWEB_API_URL . '/domains/' . $domain_id . '/logout';

        $result = wp_remote_request($url, array(
            'timeout' => 1500,
            'method'  => 'POST',
            'headers' => array(
                "Authorization" => "Bearer " . $this->access_token,
                "Accept"        => "application/x.10webmanager.v1+json"
            ),
            'body'   => $args
        ));

        if (is_wp_error($result)) {
            Helper::set_error_log('logout_wp_error', $result->get_error_message());
        } else {
            $res_arr = json_decode($result['body'], true);
            if (isset($res_arr['error'])) {
                Helper::set_error_log('logout_api_error', json_encode($res_arr['error']));
            }
        }


        $this->force_logout($disconnected_service);
      //  $user = User::get_instance();

        do_action('tenweb_logged_out');

        if ($redirect) {
            wp_safe_redirect('admin.php?page=tenweb_menu');
            exit();
        }


    }

    public function check_logged_in()
    {
        return !empty($this->access_token);
    }

    public function get_connection_type()
    {
        return $this->connected_from;
    }


    public function force_logout($disconnected_service='manager')
    {
        if ($disconnected_service == 'ai_assistant'){
            delete_site_option(TENWEB_PREFIX . '_ai_assistant_access_token');
            delete_site_option(TENWEB_PREFIX . '_ai_assistant_refresh_token');
        } else {
            delete_site_option(TENWEB_PREFIX . '_access_token');
            delete_site_option(TENWEB_PREFIX . '_refresh_token');
        }
        $this->access_token = false;
        $this->refresh_token = false;
        Helper::clear_cache();
    }

    public function fire_actions()
    {
        $action = $this->find_request_var('action', '');


        if (!empty($action)) {
            switch ($action) {
                case 'logout':
                    //delete_site_option(TENWEB_PREFIX . '_access_token');
                    if (isset($_POST[TENWEB_PREFIX . '_nonce']) && wp_verify_nonce($_POST[TENWEB_PREFIX . '_nonce'], TENWEB_PREFIX . '_nonce')) {
                        $this->logout();
                        break;
                    }
            }
        }

    }

    private function find_request_var($key, $default_value = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        } else if (isset($_GET[$key])) {
            return $_GET[$key];
        } else if (isset($default_value)) {
            return $default_value;
        }

        return false;
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get_errors()
    {
        return $this->error_logs;
    }

    public function check_request($request)
    {
        if (!$this->check_logged_in()) {
            $data_for_response = array(
                "code" => "unauthorized",
                "message" => "manager unauthorized, please login",
                "data" => array(
                    "status" => 401
                )
            );

            return new \WP_REST_Response($data_for_response, 401);
        }
    }

    public function authorize($request, $check_for_network = false)
    {

        $response = array(
            "code"    => "unauthorized",
            "message" => "unauthorized: incorrect token",
            "data"    => array(
                "status" => 401
            )
        );

        $token = $request->get_header('tenweb-authorization');
        if (empty($token)) {
            $response['message'] = 'unauthorized: no token';

            return $response;
        }

        //check user pwd
        if ($this->check_password($token) === true) {
            return true;
        }
        //check token
        $helper = Helper::get_instance();
        if ($helper->check_single_token($token, $check_for_network) === true) {
            return true;
        }

        return $response;
    }

    public function check_password($pwd)
    {

        $failed_login_attempts = intval(get_site_transient(TENWEB_PREFIX . 'failed_login_attempts'));
        /* do not allow more than three login attempts with wrong pwd*/
        if ($failed_login_attempts >= 12) {
            return false;
        }

        set_site_transient(TENWEB_PREFIX . 'failed_login_attempts', $failed_login_attempts + 1, 12 * 60 * 60);

        return false;

    }

    public function setConfirmToken($confirm_token){
        update_site_option(TENWEB_PREFIX . '_confirm_token', $confirm_token); // 5 min
    }
    public function getConfirmToken(){
        // if both option and transient exist in db
        return array(get_site_option(TENWEB_PREFIX . '_confirm_token'), get_site_transient(TENWEB_PREFIX . '_confirm_token'));
    }

    public function checkConfirmToken($confirm_token) {
        $saved_token =$this->getConfirmToken();
        return $confirm_token === $saved_token[0] || $confirm_token === $saved_token[1];
    }


}
