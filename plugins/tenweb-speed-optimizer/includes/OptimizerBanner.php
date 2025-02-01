<?php

namespace TenWebOptimizer;

class OptimizerBanner
{
    private $autoUpdateBanner = false;

    private $NPSSureveyBanner = false;

    private $NPSBannerShowMainCondition = false;

    public function __construct()
    {
        $this->setArgs();
    }

    public function printBanners()
    {
        if ($this->autoUpdateBanner) {
            $this->AutoUpdateBanner();
        }

        if ($this->NPSSureveyBanner) {
            $this->NPSBanner();
        }
    }

    private function setArgs()
    {
        global $pagenow;
        $auto_update_banner_array = ['plugins.php', 'edit.php'];
        $nps_survey_banner_array = ['index.php', 'edit.php'];
        $this->autoUpdateBanner = empty(get_option('two_auto_update_banner_was_shown'))
            && in_array($pagenow, $auto_update_banner_array)
            && \TenWebOptimizer\OptimizerUtils::is_tenweb_booster_connected();
        $nps_data = get_option('two_nps_data');

        if ($nps_data && isset($nps_data['show_nps_survey']) && !TENWEB_SO_HOSTED_ON_10WEB
            && strtolower(TWO_SO_ORGANIZATION_NAME) == '10web'
            && \TenWebOptimizer\OptimizerUtils::is_tenweb_booster_connected()) {
            $this->NPSBannerShowMainCondition = true;
        }

        $this->NPSSureveyBanner = ($this->NPSBannerShowMainCondition && in_array($pagenow, $nps_survey_banner_array));
    }

    private function enqueueMainScripts()
    {
        wp_enqueue_style(
            'two_banner_css',
            TENWEB_SO_URL . '/assets/css/banner_main.css',
            [],
            TENWEB_SO_VERSION
        );
        wp_enqueue_script(
            'banner_main_js',
            TENWEB_SO_URL . '/assets/js/banner_main.js',
            ['jquery', 'two_speed_js'],
            TENWEB_SO_VERSION
        );
    }

    private function enqueueNPSScripts()
    {
        $this->enqueueMainScripts();
        wp_enqueue_style(
            'two_nps_survey_css',
            TENWEB_SO_URL . '/assets/css/nps_survey.css',
            ['two_banner_css'],
            TENWEB_SO_VERSION
        );
        wp_enqueue_script(
            'two_nps_survey_js',
            TENWEB_SO_URL . '/assets/js/nps_survey.js',
            ['jquery', 'two_speed_js', 'banner_main_js'],
            TENWEB_SO_VERSION
        );
    }

    private function enqueueAutoUpdateScripts()
    {
        $this->enqueueMainScripts();
        wp_enqueue_style(
            'two_autoupdate_banner_css',
            TENWEB_SO_URL . '/assets/css/autoupdate_banner.css',
            ['two_banner_css'],
            TENWEB_SO_VERSION
        );

        wp_enqueue_script(
            'two_autoupdate_banner_js',
            TENWEB_SO_URL . '/assets/js/autoupdate_banner.js',
            ['jquery', 'two_speed_js', 'banner_main_js'],
            TENWEB_SO_VERSION
        );
    }

    private function NPSBanner()
    {
        $this->enqueueNPSScripts();
        require_once TENWEB_SO_PLUGIN_DIR . '/views/nps_survey_popup.php';
    }

    public function NPSBannerPluginPage()
    {
        if ($this->NPSBannerShowMainCondition) {
            $this->NPSBanner();
        }
    }

    private function AutoUpdateBanner()
    {
        $this->enqueueAutoUpdateScripts();
        require_once TENWEB_SO_PLUGIN_DIR . '/views/autoupdate_banner.php';
    }

    public static function two_set_autoupdate_from_banner()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'two_ajax_nonce') || !OptimizerUtils::check_admin_capabilities()) {
            die('Permission Denied.');
        }
        $auto_update = isset($_POST['auto_update']) ? sanitize_text_field($_POST['auto_update']) : '';

        if ($auto_update == 'enable') {
            global $TwoSettings;
            $TwoSettings->update_setting('two_enable_plugin_autoupdate', 'on');
        }
        add_option( //show autoupdate banner only once and don/t delete this option even on disconnect
            'two_auto_update_banner_was_shown',
            1,
            false
        );

        wp_send_json_success(['status' => 'success']);
    }

    public static function two_send_nps_survey_data()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'two_ajax_nonce') || !OptimizerUtils::check_admin_capabilities()) {
            die('Permission Denied.');
        }
        $nps_rate = isset($_POST['nps_rate']) ? (int) sanitize_text_field($_POST['nps_rate']) : '';
        $nps_from = isset($_POST['nps_from']) ? sanitize_text_field($_POST['nps_from']) : '';
        $nps_data = get_option('two_nps_data');
        $nps_data['show_nps_survey'] = 0;
        update_option( //just to be a 100% sure it won't show the banner again
            'two_nps_data',
            $nps_data,
            false
        );

        if ($nps_rate == 10) {
            $nps_data['nps'] = $nps_rate;
            $nps_data['show_share_love'] = 1;
        } elseif ($nps_rate == 11) {
            //this is local per site solution now, so why we are not updating rate in performance in this case
            $nps_data['show_share_love'] = 0;
        } else {
            $nps_data['nps'] = $nps_rate;
            $args = [ 'nps' => $nps_rate, 'source' => $nps_from ];
            \TenWebOptimizer\OptimizerNPS::set_nps_survey_data($args);
        }
        update_option(
            'two_nps_data',
            $nps_data,
            false
        );

        wp_send_json_success(['status' => 'success']);
    }
}
