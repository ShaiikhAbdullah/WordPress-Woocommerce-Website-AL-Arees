<?php
$two_clear_cache_logs = \TenWebOptimizer\OptimizerLogger::get_logs('clear_cache');
$two_clear_cache_headlines = [
    'is_json' => 'Ajax call',
    'excludeCriticalRegeneration' => 'Exclude crit. regen.',
    'delete_tenweb_manager_cache' => 'Delete manager cache',
    'delete_cloudflare_cache' => 'Delete cloudflare cache',
    'critical_regeneration_mode' => 'Crit. regen. mode',
    'clear_critical' => 'Clear crit.',
    'stack_trace' => 'Stack',
    'date' => 'Date'
];

$two_critical_css_logs = \TenWebOptimizer\OptimizerLogger::get_logs('critical_css');
$two_critical_css_headlines = [
    'domain_id' => 'Domain ID',
    'notification_id' => 'Notif. ID',
    'newly_connected_website' => 'Newly conn.',
    'flow_id' => 'Flow ID',
    'page_id' => 'Page ID',
    'status_code' => 'Status Code',
    'stack_trace' => 'Stack',
    'date' => 'Date'
];

$two_not_optimized_logs = \TenWebOptimizer\OptimizerLogger::get_logs('not_optimized_pages');
$two_not_optimized_headlines = [
    'request_uri' => 'Request URI',
    'reason' => 'Reason',
    'date' => 'Date'
];

$two_performance_requests_logs = \TenWebOptimizer\OptimizerLogger::get_logs('performance_requests');
$two_performance_requests_headlines = [
    'url' => 'URL',
    'code' => 'Status code',
    'msg' => 'Msg',
    'date' => 'Date'
];

$two_general_logs = \TenWebOptimizer\OptimizerLogger::get_all_logs();
$two_general_logs = array_slice($two_general_logs, 0, 20, true);
$two_general_headlines = [
    'log_type' => 'Log type',
    'request_uri' => 'Request URI',
    'url' => 'URL',
    'stack_trace' => 'Stack',
    'date' => 'Date',
];

?>
<div class="two_settings_tab two_tab_two_logs">

    <div>
        <h3 style="display: inline-block">General logs</h3>
    </div>
    <table class="display" style="width:100%">
        <thead>
        <tr>
          <?php foreach ($two_general_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($two_general_logs as $log_info) { ?>
            <tr>
              <?php foreach ($two_general_headlines as $headline => $title) { // phpcs:ignore
    if (isset($log_info[$headline])) {
        $val = $log_info[$headline];

        if (is_bool($val)) {
            $msg = $val ? 'true' : 'false';
        } elseif ($headline == 'date') {
            $msg = date('Y-m-d H:i:s', $val); // phpcs:ignore
        } elseif ($headline == 'log_type') {
            $msg = str_replace('_', ' ', ucfirst($val));
        } else {
            $msg = $val;
        }
    } else {
        $msg = '-';
    }

    if ($headline == 'stack_trace' && is_array($val)) {
        echo "<th><code class='two_clear_cache_stack_trace'>";

        foreach ($val as $frame) {
            echo '<div>' . esc_html($frame) . '</div>';
        }
        echo '</code></th>';
    } else {
        echo '<th>' . esc_html($msg) . '</th>';
    }
} ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
          <?php foreach ($two_general_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </tfoot>
    </table>
    <hr/>

    <div style="margin-top: 40px;">
        <h3 style="display: inline-block">Clear cache logs</h3>
        <span style="float: right;" class="button" data-log-type="clear_cache">Delete clear cache logs</span>
    </div>
    <table class="display" data-log-type="clear_cache" style="width:100%">
        <thead>
        <tr>
          <?php foreach ($two_clear_cache_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($two_clear_cache_logs as $log_info) { ?>
            <tr>
              <?php foreach ($two_clear_cache_headlines as $headline => $title) { // phpcs:ignore
    if (isset($log_info[$headline])) {
        $val = $log_info[$headline];

        if (is_bool($val)) {
            $msg = $val ? 'true' : 'false';
        } elseif ($headline == 'date') {
            $msg = date('Y-m-d H:i:s', $val); // phpcs:ignore
        } else {
            $msg = $val;
        }
    } else {
        $msg = '-';
    }

    if ($headline == 'stack_trace') {
        echo "<th><code class='two_clear_cache_stack_trace'>";

        foreach ($val as $frame) {
            echo '<div>' . esc_html($frame) . '</div>';
        }
        echo '</code></th>';
    } else {
        echo '<th>' . esc_html($msg) . '</th>';
    }
} ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
          <?php foreach ($two_clear_cache_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </tfoot>
    </table>
    <hr/>

    <div style="margin-top: 40px;">
        <h3 style="display: inline-block">Generate critical CSS Logs</h3>
        <span style="float: right;" class="button" data-log-type="critical_css">Delete critical CSS logs</span>
    </div>
    <table class="display" data-log-type="critical_css" style="width:100%">
        <thead>
        <tr>
          <?php foreach ($two_critical_css_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($two_critical_css_logs as $log_info) { ?>
            <tr>
              <?php foreach ($two_critical_css_headlines as $headline => $title) { // phpcs:ignore
    if (isset($log_info[$headline])) {
        $val = $log_info[$headline];

        if (is_bool($val)) {
            $msg = $val ? 'true' : 'false';
        } elseif ($headline == 'date') {
            $msg = date('Y-m-d H:i:s', $val); // phpcs:ignore
        } else {
            $msg = $val;
        }
    } else {
        $msg = '-';
    }

    if ($headline == 'stack_trace') {
        echo "<th><code class='two_clear_cache_stack_trace'>";

        foreach ($val as $frame) {
            echo '<div>' . esc_html($frame) . '</div>';
        }
        echo '</code></th>';
    } else {
        echo '<th>' . esc_html($msg) . '</th>';
    }
} ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
          <?php foreach ($two_critical_css_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </tfoot>
    </table>
    <hr/>

    <div style="margin-top: 40px;">
        <h3 style="display: inline-block">Performance responses</h3>
        <span style="float: right;"
              class="button" data-log-type="performance_requests">Delete performance requests logs </span>
    </div>
    <table class="display two_performance_requests_logs" data-log-type="performance_requests" style="width:100%">
        <thead>
        <tr>
          <?php foreach ($two_performance_requests_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($two_performance_requests_logs as $log_info) { ?>
            <tr>
              <?php foreach ($two_performance_requests_headlines as $headline => $title) { // phpcs:ignore
    if (isset($log_info[$headline])) {
        $val = $log_info[$headline];

        if (is_bool($val)) {
            $msg = $val ? 'true' : 'false';
        } elseif ($headline == 'date') {
            $msg = date('Y-m-d H:i:s', $val); // phpcs:ignore
        } else {
            $msg = $val;
        }
    } else {
        $msg = '-';
    }

    if ($headline == 'stack_trace') {
        echo "<th><code class='two_clear_cache_stack_trace'>";

        foreach ($val as $frame) {
            echo '<div>' . esc_html($frame) . '</div>';
        }
        echo '</code></th>';
    } else {
        echo '<th>' . esc_html($msg) . '</th>';
    }
} ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
          <?php foreach ($two_performance_requests_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </tfoot>
    </table>
    <hr/>

    <div style="margin-top: 40px;">
        <h3 style="display: inline-block">Not optimized pages reasons</h3>
        <span style="float: right;"
              class="button" data-log-type="not_optimized_pages">Delete not optimized pages logs</span>
    </div>
    <table class="display two_not_optimized_pages_logs" data-log-type="not_optimized_pages" style="width:100%">
        <thead>
        <tr>
          <?php foreach ($two_not_optimized_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($two_not_optimized_logs as $log_info) { ?>
            <tr>
              <?php foreach ($two_not_optimized_headlines as $headline => $title) { // phpcs:ignore
    if (isset($log_info[$headline])) {
        $val = $log_info[$headline];

        if (is_bool($val)) {
            $msg = $val ? 'true' : 'false';
        } elseif ($headline == 'date') {
            $msg = date('Y-m-d H:i:s', $val); // phpcs:ignore
        } else {
            $msg = $val;
        }
    } else {
        $msg = '-';
    }

    if ($headline == 'stack_trace') {
        echo "<th><code class='two_clear_cache_stack_trace'>";

        foreach ($val as $frame) {
            echo '<div>' . esc_html($frame) . '</div>';
        }
        echo '</code></th>';
    } else {
        echo '<th>' . esc_html($msg) . '</th>';
    }
} ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
          <?php foreach ($two_not_optimized_headlines as $headline) { ?>
              <th><?php echo esc_html($headline); ?></th>
          <?php } ?>
        </tr>
        </tfoot>
    </table>
</div>
