<?php

namespace TenWebOptimizer\WebPageCache;

use TenWebOptimizer\OptimizerUrl;

class OptimizerWebPageValidations
{
    protected static $instance = null;

    public function __construct()
    {
    }

    public function allowed_request_method()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return in_array(strtoupper($_SERVER['REQUEST_METHOD']), ['GET', 'HEAD']);
        }

        return false;
    }

    /**
     * Tell if the constant DONOTCACHEPAGE is set and not overridden.
     * When defined, the page must not be cached.
     *
     * @return bool
     */
    public function has_donotcachepage()
    {
        if (! defined('DONOTCACHEPAGE') || ! DONOTCACHEPAGE) {
            return false;
        }

        /*
         * At this point the constant DONOTCACHEPAGE is set to true.
         * This filter allows to force the page caching.
         * It prevents conflict with some plugins like Thrive Leads.
         *
         * @since 2.5
         *
         * @param bool $override_donotcachepage True will force the page to be cached.
         */
        return ! apply_filters('two_override_donotcachepage', false);
    }

    public function allowed_by_optimizer()
    {
        global $TwoSettings;

        return (!isset($_GET['two_critical_status']) && !isset($_GET['two_action']) && !isset($_GET['two_nooptimize']) && !isset($_GET['elementor-preview']) // phpcs:ignore
                && OptimizerUrl::urlIsOptimizable() //whether url is excluded from fastcgi cached pages or manually excluded
                && !(defined('REST_REQUEST') && REST_REQUEST)
                && ('on' != $TwoSettings->get_settings('two_test_mode') || isset($_GET[ 'twbooster' ]))) // phpcs:ignore
            || (isset($_GET['rest_route'])); // phpcs:ignore
    }

    public function allowed_wp_page()
    {
        if (is_admin()) {
            return false;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        if (isset($_POST['wp_customize'])) { // phpcs:ignore
            return false;
        }

        return true;
    }

    public function rejected_file()
    {
        $files = ['robots.txt', '.htaccess'];

        foreach ($files as $file) {
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/' . $file) !== false) { // phpcs:ignore
                return true;
            }
        }

        return false;
    }

    public function reject_uri()
    {
        $uri_pattern = '/(?:.+/)?feed(?:/(?:.+/?)?)?$|/(?:.+/)?embed/|/(index\.php/)?wp\-json(/.*|$)';

        return !isset($_SERVER['REQUEST_URI']) || (bool) preg_match('#^(' . $uri_pattern . ')$#i', $_SERVER['REQUEST_URI']); // phpcs:ignore
    }

    public function rejected_cookie()
    {
        if (empty($_COOKIE)) {
            return false;
        }

        global $TwoSettings;
        $rejected_cookies = '#wp-postpass_|wptouch_switch_toggle|comment_author_|comment_author_email_';

        if ('on' !== $TwoSettings->get_settings('two_page_cache_user')) {
            $rejected_cookies .= '|wordpress_logged_in_.+';
        }
        $rejected_cookies .= '#';
        $excluded_cookies = [];

        foreach (array_keys($_COOKIE) as $cookie_name) { // phpcs:ignore
            if (preg_match($rejected_cookies, $cookie_name)) {
                $excluded_cookies[] = $cookie_name;
            }
        }

        return count($excluded_cookies) > 0;
    }

    public function reject_ua()
    {
        $rejected_uas = 'facebookexternalhit|WhatsApp';

        return !isset($_SERVER['REQUEST_URI'], $_SERVER['HTTP_USER_AGENT']) || (bool) preg_match('#' . $rejected_uas . '#', $_SERVER['HTTP_USER_AGENT']); // phpcs:ignore
    }

    public function allowed_query_string()
    {
        $get_params = $this->get_query_params();

        if (empty($get_params)) {
            return true;
        }

        $allowed_params = [
            'lang',
            's',
            'permalink_name',
            'lp-variation-id',
            'twbooster'
        ];

        return count(array_intersect(array_keys($get_params), $allowed_params)) > 0;
    }

    public function valid_buffer_to_cache($buffer)
    {
        if (strlen($buffer) <= 255) {
            // Buffer length must be > 255 (IE does not read pages under 255 c).
            return false;
        }

        if (http_response_code() !== 200) {
            return false;
        }

        if (!function_exists('is_404') || is_404()) {
            return false;
        }

        if (!function_exists('is_search') && is_search()) {
            return false;
        }

        if (!preg_match('/<\s*\/\s*html\s*>/i', $buffer) && !preg_match('/<\s*\/\s*body\s*>/i', $buffer)) {
            return false;
        }

        return true;
    }

    public function get_query_params()
    {
        if (empty($_GET)) { // phpcs:ignore
            return [];
        }

        $params_to_ignore = [
            'utm_source' => 1,
            'utm_medium' => 1,
            'utm_campaign' => 1,
            'utm_expid' => 1,
            'utm_term' => 1,
            'utm_content' => 1,
            'mtm_source' => 1,
            'mtm_medium' => 1,
            'mtm_campaign' => 1,
            'mtm_keyword' => 1,
            'mtm_cid' => 1,
            'mtm_content' => 1,
            'pk_source' => 1,
            'pk_medium' => 1,
            'pk_campaign' => 1,
            'pk_keyword' => 1,
            'pk_cid' => 1,
            'pk_content' => 1,
            'fb_action_ids' => 1,
            'fb_action_types' => 1,
            'fb_source' => 1,
            'fbclid' => 1,
            'campaignid' => 1,
            'adgroupid' => 1,
            'adid' => 1,
            'gclid' => 1,
            'age-verified' => 1,
            'ao_noptimize' => 1,
            'usqp' => 1,
            'cn-reloaded' => 1,
            '_ga' => 1,
            'sscid' => 1,
            'gclsrc' => 1,
            '_gl' => 1,
            'mc_cid' => 1,
            'mc_eid' => 1,
            '_bta_tid' => 1,
            '_bta_c' => 1,
            'trk_contact' => 1,
            'trk_msg' => 1,
            'trk_module' => 1,
            'trk_sid' => 1,
            'gdfms' => 1,
            'gdftrk' => 1,
            'gdffi' => 1,
            '_ke' => 1,
            'redirect_log_mongo_id' => 1,
            'redirect_mongo_id' => 1,
            'sb_referer_host' => 1,
            'mkwid' => 1,
            'pcrid' => 1,
            'ef_id' => 1,
            's_kwcid' => 1,
            'msclkid' => 1,
            'dm_i' => 1,
            'epik' => 1,
            'pp' => 1,
            'gbraid' => 1,
            'wbraid' => 1,
            'zenid' => 1,
            'lons1' => 1,
            'appns' => 1,
            'tstt1' => 1,
            'tsts6' => 1,
            'tsts12' => 1,
            'lpcid' => 1,
            '3x-7' => 1,
            'mm_src' => 1,
            'muid' => 1,
            'vgo_ee' => 1,
            'mn' => 1,
            'irclickid' => 1,
            'irgwc' => 1,
            '_xuid' => 1,
            'ref' => 1,
            'dclid' => 1,
            'mkt_tok' => 1,
            'ck_subscriber_id' => 1,
            'pk_kwd' => 1,
            'piwik_campaign' => 1,
            'piwik_kwd' => 1,
            'piwik_keyword' => 1,
            'mtm_group' => 1,
            'mtm_placement' => 1,
            'matomo_campaign' => 1,
            'matomo_keyword' => 1,
            'matomo_source' => 1,
            'matomo_medium' => 1,
            'matomo_content' => 1,
            'matomo_cid' => 1,
            'matomo_group' => 1,
            'matomo_placement' => 1,
            'hsa_cam' => 1,
            'hsa_grp' => 1,
            'hsa_mt' => 1,
            'hsa_src' => 1,
            'hsa_ad' => 1,
            'hsa_acc' => 1,
            'hsa_net' => 1,
            'hsa_kw' => 1,
            'hsa_tgt' => 1,
            'hsa_ver' => 1,
            '_branch_match_id' => 1,
            'mkevt' => 1,
            'mkcid' => 1,
            'mkrid' => 1,
            'campid' => 1,
            'toolid' => 1,
            'customid' => 1,
            'igshid' => 1,
            'si' => 1,
            'iid' => 1,
            '_iid' => 1,
            'pretty' => 1,
            'mod' => 1,
            'li_fat_id' => 1,
            'ttclid' => 1,
            'trueroas' => 1,
            '_vsrefdom' => 1,
        ];
        $params = array_diff_key($_GET, $params_to_ignore); // phpcs:ignore

        if ($params) {
            // order is important because cache file name is being generated using $params
            ksort($params);
        }

        return $params;
    }

    public function get_cookies()
    {
        if (empty($_COOKIE)) {
            return [];
        }

        $cookies_to_keep = [
            'wcml_client_currency'
        ];

        $cookies = [];

        foreach ($cookies_to_keep as $cookie_name) {
            if (isset($_COOKIE[$cookie_name])) { // phpcs:ignore
                $cookies[$cookie_name] = $_COOKIE[$cookie_name]; //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            }
        }

        return $cookies;
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
