<?php

namespace TenWebIO;

use TenWebWpTransients\OptimizerTransients;

class Settings
{
    private $settings;
    private $aws_credentials;
    private $stat;
    private $request_api;

    public function __construct($request_api = true)
    {
        $this->request_api = $request_api;
    }

    /**
     *
     * @param bool $force
     * @param int  $exclude_aws
     * @param int  $exclude_stat
     *
     * @return mixed
     */
    public function getSettings($force = false, $exclude_aws = 0, $exclude_stat = 0)
    {
        $this->settings = get_option(TENWEBIO_PREFIX . '_settings');
        if (empty($this->settings) || $force) {
            $this->getCompressSettings(0, $exclude_aws, $exclude_stat);
        }

        return $this->settings;
    }

    /**
     * @param bool $force
     * @param int  $exclude_settings
     * @param int  $exclude_stat
     *
     * @return mixed
     */
    public function getAwsCredentials($force = false, $exclude_settings = 0, $exclude_stat = 0)
    {
        $domain_id = get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id');
        $this->aws_credentials = OptimizerTransients::get(TENWEBIO_PREFIX . '_aws_credentials_' . $domain_id);
        if (empty($this->aws_credentials) || $force) {
            $this->getCompressSettings($exclude_settings, 0, $exclude_stat);
        }

        return $this->aws_credentials;
    }

    /**
     * @param bool $force
     * @param int  $exclude_settings
     * @param int  $exclude_aws
     *
     * @return mixed
     */
    public function getStat($force = false, $exclude_settings = 0, $exclude_aws = 0)
    {
        $this->stat = get_option(TENWEBIO_PREFIX . '_stat');
        if (empty($this->stat) || $force) {
            $this->getCompressSettings($exclude_settings, $exclude_aws, 0);
        }

        return $this->stat;
    }

    /**
     * @return void
     */
    public static function purgeCompressSettings()
    {
        delete_option(TENWEBIO_PREFIX . '_stat');
        delete_option(TENWEBIO_PREFIX . '_settings');
        $domain_id = get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id');
        OptimizerTransients::delete(TENWEBIO_PREFIX . '_aws_credentials_' . $domain_id);
        delete_transient(TENWEBIO_PREFIX . '_not_optimized_data');
        delete_transient(TENWEBIO_PREFIX . '_images_report');
    }

    public function settingActions()
    {
        $settings = $this->settings;
        if ($settings["enable_auto_optimization"] == 0) {
            wp_clear_scheduled_hook('tenwebio_compress_hook');
        }
    }

    /**
     * @param int $exclude_settings
     * @param int $exclude_aws
     * @param int $exclude_stat
     *
     * @return void
     */

    private function getCompressSettings($exclude_settings = 0, $exclude_aws = 0, $exclude_stat = 0)
    {
        if (!$this->request_api) {
            return;
        }
        $api_instance = new Api(Api::API_COMPRESS_SETTINGS_ACTION);
        $response = $api_instance->apiRequest('GET', array(), array(
            'exclude_aws_credentials' => $exclude_aws,
            'exclude_stat'            => $exclude_stat,
            'exclude_settings'        => $exclude_settings,
        ));
        if ($response) {
            if (!empty($response['settings'])) {
                update_option(TENWEBIO_PREFIX . '_settings', $response['settings']);
                $this->settings = $response['settings'];
                $this->settingActions();
            }
            if (!empty($response['stat'])) {
                update_option(TENWEBIO_PREFIX . '_stat', $response['stat']);
                $this->stat = $response['stat'];
            }
            if (!empty($response['aws_credentials'])) {
                $domain_id = get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id');
                OptimizerTransients::set(TENWEBIO_PREFIX . '_aws_credentials_' . $domain_id, $response['aws_credentials'], 17000);
                $this->aws_credentials = $response['aws_credentials'];
            }
        }
    }
}