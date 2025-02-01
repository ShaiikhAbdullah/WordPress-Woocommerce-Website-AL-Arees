<?php

// Only run through WP CLI.
use TenWebOptimizer\OptimizerSettings;

if (! defined('WP_CLI')) {
    return;
}
// phpcs:ignore WordPressVIPMinimum.Classes.RestrictedExtendClasses.wp_cli
class OptimizerCli extends WP_CLI_Command
{
    /**
     * @var OptimizerSettings|null
     */
    private $twoSettings;

    /**
     * TWO CLI Command to activate test mode
     *
     * <action>
     *      activate or deactivate test mode
     *
     * [--file]
     *      File or URL to set test mode
     */
    public function test_mode($args, $assoc_args)
    {
        $default = [
            0 => '',
        ];

        if (! isset($args[0])) {
            $args[0] = $default[0];
        }

        $action = $args[0];

        if ($action == '') {
            WP_CLI::error('Please enter action: activate or deactivate');

            return false;
        }

        if (!in_array($action, ['activate', 'deactivate'])) {
            WP_CLI::error('Please enter action: activate or deactivate');

            return false;
        }

        global $TwoSettings;
        $this->twoSettings = $TwoSettings;

        switch ($action) {
            case 'activate':
                if (empty($assoc_args['file'])) {
                    WP_CLI::error('Missing --file. Please provide file or url');

                    return false;
                }
                $testHtml = $this->getHtml($assoc_args['file']);
                $this->setTestMode($testHtml);
                break;

            case 'deactivate':
                $this->deactivateTestMode();
                break;
        }
    }

    /**
     * TWO CLI Command to easily clear cache
     */
    public function flush($args, $assoc_args)
    {
        \TenWebOptimizer\OptimizerAdmin::clear_cache() ? WP_CLI::success('Cache cleared.') : WP_CLI::error('Error during cache clear.');
    }

    /**
     * TWO CLI Command to easily regenerate critical CSS for all pages
     */
    public function regenerate($args, $assoc_args)
    {
        $default = [
            0 => '',
        ];

        if (! isset($args[0])) {
            $args[0] = $default[0];
        }

        $action = $args[0];

        if ($action !== 'critical_css') {
            WP_CLI::error('Please enter item: critical_css');

            return false;
        }

        WP_CLI::debug('Starting regenerate critical CSS!');
        \TenWebOptimizer\OptimizerUtils::regenerate_critical('all');
        \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true, true);

        WP_CLI::success('Critical CSS is regenerated for all pages!');
    }

    /**
     * TWO CLI Command to easily import/export configuration
     *
     * ## OPTIONS
     *
     *
     * <action>
     *      Import or export configuration
     *
     * [--file]
     *      Filename to import or export
     */
    public function config($args, $assoc_args)
    {
        $default = [
            0 => '',
        ];

        if (! isset($args[0])) {
            $args[0] = $default[0];
        }

        $action = $args[0];

        if ($action == '') {
            WP_CLI::error('Please enter action: import or export');

            return false;
        }

        if (!in_array($action, ['import', 'export'])) {
            WP_CLI::error('Please enter action: import or export');

            return false;
        }
        global $TwoSettings;
        $this->twoSettings = $TwoSettings;

        switch ($action) {
            case 'import':
                if (empty($assoc_args['file'])) {
                    WP_CLI::error('Please provide filename to import');
                }
                $file = $assoc_args['file'];
                $this->importConfiguration($file);

                break;

            case 'export':
                $dir = __DIR__ . '/exported/';
                $file = !empty($assoc_args['file']) ? str_replace('.json', '', $assoc_args['file']) : $dir . 'optimizer_settings_' . TENWEB_SO_VERSION . '_' . date('Y-m-d_H:i:s'); // phpcs:ignore
                $this->exportConfiguration($file);
                break;
        }

        return 0;
    }

    public function generate_critical($file_path)
    {
        \TenWebWpTransients\OptimizerTransients::delete('two_critical_in_process');

        if (is_array($file_path) && isset($file_path[0]) && file_exists($file_path[0])) {
            if (is_readable($file_path[0])) {
                \TenWebOptimizer\OptimizerCriticalCss::createCriticalCSS($file_path[0]);

                return true;
            } else {
                $cli_response = 'File not readable';
            }
        } else {
            $cli_response = 'File path not exist';
        }
        WP_CLI::error($cli_response);

        return false;
    }

    /**
     * TWO CLI Command to easily enable/disable webp serving
     *
     * ## OPTIONS
     *
     *
     * <action>
     *      enable or disable configuration
     */
    public function change_nginx_webp_delivery($args)
    {
        $default = [
            0 => '',
        ];

        if (! isset($args[0])) {
            $args[0] = $default[0];
        }

        $action = $args[0];

        if ($action === '') {
            WP_CLI::error('Please enter action: enable or disable');

            return false;
        }

        if (!in_array($action, ['enable', 'disable'])) {
            WP_CLI::error('Please enter action: enable or disable');

            return false;
        }

        global $TwoSettings;
        $this->twoSettings = $TwoSettings;
        $oldValue = $this->twoSettings->get_settings('two_enable_nginx_webp_delivery');
        $value = $action === 'enable' ? 'on' : '';

        if ('' === $value || 'on' === $value) {
            $this->twoSettings->update_setting('two_enable_nginx_webp_delivery', $value);
        }

        $webpOption = $this->twoSettings->get_settings('two_enable_nginx_webp_delivery');
        WP_CLI::success('NginX WebP Delivery option is: ' . $webpOption);

        if ($webpOption != $oldValue) {
            WP_CLI::success('NginX WebP Delivery successfully ' . ('on' === $value ? 'enabled' : 'disabled'));
        } else {
            WP_CLI::warning('NginX WebP Delivery option is not changed and now is ' . ('on' === $oldValue ? 'enabled' : 'disabled'));
        }
    }

    private function importConfiguration($file)
    {
        $this->twoSettings->import_settings($file) ? WP_CLI::success('Imported!') : WP_CLI::error('Imported failed!');
    }

    private function exportConfiguration($file)
    {
        $data = $this->twoSettings->export_settings();

        if ($data !== false) {
            file_put_contents($file . '.json', $data); // phpcs:ignore
            WP_CLI::success('Exported successfully to ' . $file . '.json !');

            return true;
        }

        WP_CLI::error('Export failed!');

        return false;
    }

    private function getHtml($file)
    {
        return trim(file_get_contents($file)); // phpcs:ignore
    }

    private function setTestMode($testHtml)
    {
        $this->twoSettings->setTestMode($testHtml) ? WP_CLI::success('Test mode is set!') : WP_CLI::error('Error setting test mode!');
        $this->twoSettings->update_setting('two_files_cache', '');
        $this->flush([], []);
        WP_CLI::success('Files cache is disabled!');
    }

    private function deactivateTestMode()
    {
        $this->twoSettings->removeTestMode() ? WP_CLI::success('Test mode is deactivated!') : WP_CLI::error('Error deactivating test mode!');
        $this->twoSettings->update_setting('two_files_cache', '');
        $this->flush([], []);
        WP_CLI::success('Files cache is disabled!');
    }
}

WP_CLI::add_command('two', 'OptimizerCli');
