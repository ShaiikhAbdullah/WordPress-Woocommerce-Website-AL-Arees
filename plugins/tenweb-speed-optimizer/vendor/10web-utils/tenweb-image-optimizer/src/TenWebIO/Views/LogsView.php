<?php
namespace TenWebIO\Views;

use TenWebIO\CompressDataService;
use TenWebIO\Config;
use TenWebIO\Logs;
use TenWebIO\Settings;
use TenWebIO\Utils;

class LogsView
{
    private $config;
    private $compress_data_service;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->compress_data_service = new CompressDataService();
        $this->config = new Config();
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'init'));
    }

    /**
     * @return void
     */
    public function menu()
    {
        add_submenu_page('', 'Image Optimizer', 'Logs', 'manage_options', TENWEBIO_PREFIX . '_logs', array($this, 'view'));
    }

    public function init()
    {
        if (isset($_POST["clear_logs"])) {
            check_admin_referer('nonce_' . TENWEBIO_PREFIX, 'nonce_' . TENWEBIO_PREFIX);
            Logs::clearLogs();
        } else if (isset($_POST["clear_settings"])) {
            check_admin_referer('nonce_' . TENWEBIO_PREFIX, 'nonce_' . TENWEBIO_PREFIX);
            Settings::purgeCompressSettings();
            delete_site_option(TENWEBIO_PREFIX . '_running_compress_bulk');
        } else if (isset($_POST["save_configs"])) {
            check_admin_referer('nonce_' . TENWEBIO_PREFIX, 'nonce_' . TENWEBIO_PREFIX);
            $this->config->save($_POST);
        } else if (isset($_POST["cancel_compress"])) {
            check_admin_referer('nonce_' . TENWEBIO_PREFIX, 'nonce_' . TENWEBIO_PREFIX);
            Utils::finishQueue('bulk');
        }
    }

    /**
     * @return void
     */
    public function view()
    {
        $logs = Logs::getLogs();
        $compress_logs = $this->compress_data_service->getNotCompressedNumbers(true);
        ?>
        <div>
            <div>
                <h1>Compress Report:</h1>
                <div>
                    <table class="wp-list-table widefat fixed striped pages">
                        <tr>
                            <td>Not optimized full</td>
                            <td><?php echo $compress_logs['not_optimized']['full']; ?></td>
                        </tr>
                        <tr>
                            <td>Not optimized thumbs</td>
                            <td><?php echo $compress_logs['not_optimized']['thumbs']; ?></td>
                        </tr>
                        <tr>
                            <td>Not optimized other</td>
                            <td><?php echo $compress_logs['not_optimized']['other']; ?></td>
                        </tr>
                        <tr>
                            <td>Last optimized total</td>
                            <td><?php echo $compress_logs['last_optimized']['size']; ?>
                                (<?php echo $compress_logs['last_optimized']['percent']; ?>%)
                            </td>
                        </tr>
                    </table>
                    <br><br>
                </div>
            </div>
            <form
                    method="post"
                    id="logs_form"
                    action="">
                <?php wp_nonce_field('nonce_' . TENWEBIO_PREFIX, 'nonce_' . TENWEBIO_PREFIX); ?>
                <h1>Configs</h1>
                <table class="wp-list-table widefat fixed striped pages">
                    <tr>
                        <td>Debug mode</td>
                        <td><input type="checkbox" value="1" id="iowd_config_debug"
                                   name="debug_mode" <?php echo $this->config->getDebugMode() ? 'checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>Images count limit for restart</td>
                        <td><input type="text" value="<?php echo $this->config->getImagesLimitForRestart(); ?>"
                                   id="iowd_config_images_limit_for_restart"
                                   name="images_limit_for_restart"></td>
                    </tr>
                    <tr>
                        <td>Queue chunk images limit</td>
                        <td><input type="text" value="<?php echo $this->config->getQueueChunkImagesLimit(); ?>"
                                   id="iowd_config_queue_chunk_images_limit"
                                   name="queue_chunk_images_limit"></td>
                    </tr>
                    <tr>
                        <td>Stat chunk images limit</td>
                        <td><input type="text" value="<?php echo $this->config->getStatChunkImagesLimit(); ?>"
                                   id="iowd_config_stat_chunk_images_limit"
                                   name="stat_chunk_images_limit"></td>
                    </tr>
                    <tr>
                        <td>Auto optimize with rest</td>
                        <td><input type="checkbox" value="1" id="iowd_config_auto_optimize_with_rest"
                                   name="auto_optimize_with_rest" <?php echo $this->config->getAutoOptimizeWithRest() ? 'checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>Compress only full size</td>
                        <td><input type="checkbox" value="1" id="iowd_compress_only_full_size"
                                   name="compress_only_full_size" <?php echo $this->config->getCompressOnlyFullSize() ? 'checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                </table>
                <br>
                <input type="submit" class="button action" value="Clear Logs" name="clear_logs">
                <input type="submit" class="button action" value="Clear Settings" name="clear_settings">
                <input type="submit" class="button action" value="Save Configs" name="save_configs">
                <input type="submit" class="button action" value="Cancel running compress" name="cancel_compress">
            </form>
            <h1>Optimization Logs</h1>
            <table class="wp-list-table widefat fixed striped pages">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Key</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
                </thead>
                <?php foreach ($logs as $key => $log) { ?>
                    <tr>
                        <td><?php echo !empty($log["type"]) ? $log["type"] : 'log'; ?></td>
                        <td><?php echo $key; ?></td>
                        <td><?php echo $log["msg"]; ?></td>
                        <td><?php echo $log["date"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <?php
    }

}
