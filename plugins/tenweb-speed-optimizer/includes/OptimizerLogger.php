<?php

namespace TenWebOptimizer;

class OptimizerLogger
{
    protected static $instance = null;

    protected static $log_options = [
        'performance_requests' => 'two_performance_requests_logs',
        'clear_cache' => 'two_clear_cache_logs',
        'critical_css' => 'two_critical_css_logs',
        'not_optimized_pages' => 'two_serve_not_optimized_page_logs'
    ];

    private function __construct()
    {
        add_action('http_api_debug', [$this, 'log_performance_requests_api_debug'], 10, 5);

        if (!wp_next_scheduled('two_daily_cron_hook')) {
            wp_schedule_event(time(), 'daily', 'two_daily_cron_hook');
        }
        add_action('two_daily_cron_hook', [$this, 'two_daily_cron_hook_function']);
    }

    private static function add_performance_requests_log($response, $url, $parsed_args)
    {
        if (strpos($url, TENWEB_SO_CRITICAL_URL) !== 0) {
            return;
        }

        if (isset($parsed_args['blocking']) && $parsed_args['blocking'] === false) {
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $msg = wp_remote_retrieve_response_message($response);

        if ($code === 200) {
            return;
        }

        $log_data = [
            'code' => $code,
            'msg' => $msg,
            'url' => $url
        ];

        self::add_log('performance_requests', $log_data, false);
    }

    public static function add_clear_cache_log($is_json, $excludeCriticalRegeneration, $delete_tenweb_manager_cache, $delete_cloudflare_cache, $critical_regeneration_mode, $clear_critical)
    {
        $log_data = [
            'is_json' => $is_json,
            'excludeCriticalRegeneration' => $excludeCriticalRegeneration,
            'delete_tenweb_manager_cache' => $delete_tenweb_manager_cache,
            'delete_cloudflare_cache' => $delete_cloudflare_cache,
            'critical_regeneration_mode' => $critical_regeneration_mode,
            'clear_critical' => boolval($clear_critical),
        ];

        self::add_log('clear_cache', $log_data);
    }

    public static function add_critical_css_log($request_data, $newly_connected_website, $flow_id, $status_code, $res_body = '')
    {
        $log_data = [
            'domain_id' => $request_data['tenweb_domain_id'],
            'newly_connected_website' => $newly_connected_website,
            'flow_id' => $flow_id,
            'page_id' => $request_data['page_id'],
            'status_code' => $status_code,
            'res_body' => $res_body
        ];

        if (isset($request_data['notification_id'])) {
            $log_data['notification_id'] = $request_data['notification_id'];
        }
        $force_write_log = false;

        if ($newly_connected_website) {
            $force_write_log = true;
        }
        self::add_log('critical_css', $log_data, true, $force_write_log);
    }

    public static function add_not_optimized_page_log($reason)
    {
        $log_data = [
            'request_uri' => (isset($_SERVER['REQUEST_URI'])) ? sanitize_text_field($_SERVER['REQUEST_URI']) : 'No REQUEST_URI',
            'reason' => $reason
        ];

        self::add_log('not_optimized_pages', $log_data, false);
    }

    public static function add_log($log_type, $log_data, $include_stack_trace = true, $force_write_log = false)
    {
        if (!$force_write_log && !TENWEB_SO_DEBUG_MODE) {
            return;
        }
        $option_name = self::get_log_option_name($log_type);

        foreach ($log_data as $key => $value) {
            if (!is_bool($value)) {
                $log_data[$key] = sanitize_text_field($value);
            }
        }

        if ($include_stack_trace) {
            $log_data['stack_trace'] = self::get_stack_trace();
        }
        $log_data['date'] = time();

        $clear_cache_log = get_option($option_name, []);
        $clear_cache_log = array_slice($clear_cache_log, -TENWEB_SO_LOGS_MAX_LINES_LIMIT);
        $clear_cache_log[] = $log_data;
        update_option($option_name, $clear_cache_log, false);
    }

    public static function get_logs($log_type)
    {
        $logs = get_option(self::get_log_option_name($log_type), []);
        self::sort_logs($logs);

        return $logs;
    }

    public static function delete_logs($log_type)
    {
        $option_name = self::get_log_option_name($log_type);

        if ($option_name) {
            delete_option($option_name);

            return true;
        }

        return false;
    }

    public static function get_all_logs()
    {
        $all_logs = [];

        foreach (self::$log_options as $key => $val) {
            $logs = self::get_logs($key);

            foreach ($logs as $index => $log) {
                $logs[$index]['log_type'] = $key;
            }
            $all_logs = array_merge($all_logs, $logs);
        }

        self::sort_logs($all_logs);

        return $all_logs;
    }

    public static function get_log_option_name($log_type)
    {
        return (isset(self::$log_options[$log_type])) ? self::$log_options[$log_type] : null;
    }

    public function log_performance_requests_api_debug($response, $context, $class, $parsed_args, $url)
    {
        self::add_performance_requests_log($response, $url, $parsed_args);
    }

    public function log_performance_requests_http_response($response, $parsed_args, $url)
    {
        self::add_performance_requests_log($response, $url, $parsed_args);

        return $response;
    }

    public function two_daily_cron_hook_function()
    {
        foreach (array_values(self::$log_options) as $option_name) {
            $logs = get_option($option_name);
            $filtered_logs = [];
            $three_days_in_seconds = 3 * 24 * 60 * 60;

            foreach ($logs as $log) {
                if ($log['date'] + $three_days_in_seconds > time()) {
                    $filtered_logs[] = $log;
                }
            }

            update_option($option_name, $filtered_logs, false);
        }
    }

    public static function sort_logs(&$logs)
    {
        usort($logs, function ($item1, $item2) {
            return $item2['date'] <=> $item1['date'];
        });
    }

    protected static function get_stack_trace($limit = 10, $remove_first_n = 2)
    {
        $stack = [];

        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit + $remove_first_n) as $num => $frame) { // phpcs:ignore

            // remove first n frames. This will remove get_stack_trace and add_log functions
            if ($num < $remove_first_n) {
                continue;
            }

            $file = !empty($frame['file']) ? str_replace(ABSPATH, '', $frame['file']) : '';
            $line = !empty($frame['line']) ? $frame['line'] : '';
            $func = $frame['function'];
            $class = (isset($frame['class'])) ? $frame['class'] : null;
            $type = (isset($frame['type'])) ? $frame['type'] : null;

            $stack_msg = '#' . $num . ' ' . $file . '(' . $line . '): ';

            if ($class) {
                $stack_msg .= $class . $type;
            }

            $stack[] = $stack_msg . $func . '()';
        }

        return $stack;
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
