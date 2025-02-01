<?php
namespace TenWebWpBenchmark;

class OptimizerBenchmark
{
    private $configuration;
    protected static $instance = null;
    public const TWO_BENCHMARK_DATA_KEY = 'two_benchmark_data';

    public function __construct()
    {
    }

    private function initConfiguration()
    {
        //do test
        global $wpdb;
        $arr_cfg = array();

        // We need special handling for hyperdb
        if ( is_a( $wpdb, 'hyperdb' ) && ! empty( $wpdb->hyper_servers ) ) {
            // Grab a `write` server for the `global` dataset and fallback to `read`.
            // We're not really paying attention to priority or have much in the way of error checking. Use at your own risk :)
            $db_server = false;
            if ( ! empty( $wpdb->hyper_servers['global']['write'] ) ) {
                foreach ( $wpdb->hyper_servers['global']['write'] as $group => $dbs ) {
                    $db_server = current( $dbs );
                    break;
                }
            } elseif ( ! empty( $wpdb->hyper_servers['global']['read'] ) ) {
                foreach ( $wpdb->hyper_servers['global']['read'] as $group => $dbs ) {
                    $db_server = current( $dbs );
                    break;
                }
            }

            if ( $db_server ) {
                $arr_cfg['db.host'] = $db_server['host'];
                $arr_cfg['db.user'] = $db_server['user'];
                $arr_cfg['db.pw'] = $db_server['password'];
                $arr_cfg['db.name'] = $db_server['name'];
            }
        } else {
            // Vanilla WordPress install with standard `wpdb`
            $arr_cfg['db.host'] = DB_HOST;
            $arr_cfg['db.user'] = DB_USER;
            $arr_cfg['db.pw'] = DB_PASSWORD;
            $arr_cfg['db.name'] = DB_NAME;
        }

        $this->configuration = $arr_cfg;
    }

    /**
     * Runs a benchmark and returns array with data. Also saves data in wp_options under the "two_benchmark_data".
     * @return array|false
     */
    public function test()
    {
        $arr_return = [];
        $this->initConfiguration();
        try {
            $arr_return['system'] = $this->test_benchmark();
            $arr_return['wordpress'] = $this->test_wordpress();
            $arr_return['check_timestamp'] = current_time('timestamp');
        } catch (\Exception $exception) {
            return false;
        }

        update_option(self::TWO_BENCHMARK_DATA_KEY, $arr_return, false);
        return $arr_return;
    }

    /**
     * Returns benchmark data stored in wp_options under the "two_benchmark_data"
     * @return mixed
     */
    public function getData()
    {
        return get_option(self::TWO_BENCHMARK_DATA_KEY);
    }

    private function test_benchmark()
    {

        $arr_return = array();
        $arr_return['version'] = '1.6';
        $arr_return['sysinfo']['time'] = date("Y-m-d H:i:s"); // phpcs:ignore
        $arr_return['sysinfo']['php_version'] = PHP_VERSION;
        $arr_return['sysinfo']['platform'] = PHP_OS;
        $arr_return['sysinfo']['server_name'] = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';
        $arr_return['sysinfo']['server_addr'] = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field($_SERVER['SERVER_ADDR']) : '';

        $time_start = microtime(true);

        $this->test_math($arr_return);

        $this->test_string($arr_return);

        $this->test_loops($arr_return);

        $this->test_ifelse($arr_return);

        if (isset($this->configuration['db.host'])) {
            $this->test_mysql($arr_return, $this->configuration);
        }

        $arr_return['total'] = self::timer_diff($time_start);

        return $arr_return;
    }

    private function test_math(&$arr_return, $count = 99999)
    {
        $time_start = microtime(true);

        for ($i = 0; $i < $count; $i++) {
            sin($i);
            asin($i);
            cos($i);
            acos($i);
            tan($i);
            atan($i);
            abs($i);
            floor($i);
            exp($i);
            is_finite($i);
            is_nan($i);
            sqrt($i);
            log10($i);
        }

        $arr_return['benchmark']['math'] = self::timer_diff($time_start);
    }

    private function test_string(&$arr_return, $count = 99999)
    {
        $time_start = microtime(true);
        $string = 'the quick brown fox jumps over the lazy dog';
        for ($i = 0; $i < $count; $i++) {
            addslashes($string);
            chunk_split($string);
            metaphone($string);
            strip_tags($string); // phpcs:ignore
            md5($string);
            sha1($string);
            strtoupper($string);
            strtolower($string);
            strrev($string);
            strlen($string);
            soundex($string);
            ord($string);
        }
        $arr_return['benchmark']['string'] = self::timer_diff($time_start);
    }

    private function test_loops(&$arr_return, $count = 999999)
    {
        $time_start = microtime(true);
        for ($i = 0; $i < $count; ++$i) {

        }
        $i = 0;
        while ($i < $count) {
            ++$i;
        }

        $arr_return['benchmark']['loops'] = self::timer_diff($time_start);
    }

    private function test_ifelse(&$arr_return, $count = 999999)
    {
        $time_start = microtime(true);
        for ($i = 0; $i < $count; $i++) {
            if ($i == -1) {

            } elseif ($i == -2) {

            } else if ($i == -3) {

            }
        }
        $arr_return['benchmark']['ifelse'] = self::timer_diff($time_start);
    }

    private function test_mysql(&$arr_return)
    {

        $time_start = microtime(true);


        //detect socket connection
        if (stripos($this->configuration['db.host'], '.sock') !== false) {
            //parse socket location
            //set a default guess
            $socket = "/var/lib/mysql.sock";
            $serverhost = explode(':', $this->configuration['db.host']);
            if (count($serverhost) == 2 && $serverhost[0] == 'localhost') {
                $socket = $serverhost[1];
            }
            $link = mysqli_connect('localhost', $this->configuration['db.user'], $this->configuration['db.pw'], $this->configuration['db.name'], null, $socket);
        } else {
            //parse out port number if exists
            $port = 3306;//default
            if (stripos($this->configuration['db.host'], ':')) {
                $port = substr($this->configuration['db.host'], stripos($this->configuration['db.host'], ':') + 1);
                $this->configuration['db.host'] = substr($this->configuration['db.host'], 0, stripos($this->configuration['db.host'], ':'));
            }
            $link = mysqli_connect($this->configuration['db.host'], $this->configuration['db.user'], $this->configuration['db.pw'], $this->configuration['db.name'], $port);
        }
        $arr_return['benchmark']['mysql_connect'] = self::timer_diff($time_start);

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        //$arr_return['sysinfo']['mysql_version'] = '';
        //$arr_return['benchmark']['mysql_select_db'] = self::timer_diff($time_start);

        $result = mysqli_query($link, 'SELECT VERSION() as version;');
        $arr_row = mysqli_fetch_assoc($result);
        $arr_return['sysinfo']['mysql_version'] = $arr_row['version'];
        $arr_return['benchmark']['mysql_query_version'] = self::timer_diff($time_start);

        $query = "SELECT BENCHMARK(5000000, AES_ENCRYPT(CONCAT('WPHostingBenchmarks.com',RAND()), UNHEX(SHA2('is part of Review Signal.com',512))))";
        $result = mysqli_query($link, $query);
        $arr_return['benchmark']['mysql_query_benchmark'] = self::timer_diff($time_start);

        mysqli_close($link);

        $arr_return['benchmark']['mysql_total'] = self::timer_diff($time_start);

        return $arr_return;
    }

    private function test_wordpress()
    {
        //create dummy text to insert into database
        $dummytextseed = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque sollicitudin iaculis libero id pellentesque. Donec sodales nunc id lorem rutrum molestie. Duis ac ornare diam. In hac habitasse platea dictumst. Donec nec mi ipsum. Aenean dictum imperdiet erat, at lacinia mi ultrices ut. Phasellus quis nibh ornare, pulvinar dui sit amet, venenatis arcu. Suspendisse eget vehicula ligula, et placerat sapien. Cras enim erat, scelerisque sit amet tellus vel, tempor venenatis risus. In ultricies tristique ante, eu lobortis leo. Cras ullamcorper eleifend libero, quis sollicitudin massa venenatis a. Vestibulum sed pellentesque urna, nec consectetur nulla. Vestibulum sodales purus metus, non scelerisque.";
        $dummytext = "";
        for ($x = 0; $x < 100; $x++) {
            $dummytext .= str_shuffle($dummytextseed);
        }

        //start timing wordpress mysql functions
        $time_start = microtime(true);
        global $wpdb;
        $table = $wpdb->prefix . 'options';
        $optionname = 'wpperformancetesterbenchmark_';
        $count = 250;
        for ($x = 0; $x < $count; $x++) {
            //insert
            $data = array('option_name' => $optionname . $x, 'option_value' => $dummytext);
            $wpdb->insert($table, $data); // phpcs:ignore
            //select
            $select = $wpdb->prepare( "SELECT option_value FROM $table WHERE option_name='%s'", $data['option_name'] ); // phpcs:ignore
            $wpdb->get_var($select); // phpcs:ignore
            //update
            $data = array('option_value' => $dummytextseed);
            $where = array('option_name' => $optionname . $x);
            $wpdb->update($table, $data, $where); // phpcs:ignore
            //delete
            $where = array('option_name' => $optionname . $x);
            $wpdb->delete($table, $where); // phpcs:ignore
        }

        $time = self::timer_diff($time_start);
        $queries = ($count * 4) / $time;
        return array('time' => $time, 'queries' => $queries);
    }


    private static function timer_diff($time_start)
    {
        return number_format(microtime(true) - $time_start, 3);
    }

    public static function array_to_html($my_array)
    {
        $strReturn = '';
        if (is_array($my_array)) {
            $strReturn .= '<table>';
            foreach ($my_array as $k => $v) {
                $strReturn .= "\n<tr><td style=\"vertical-align:top;\">";
                $strReturn .= '<strong>' . htmlentities($k) . "</strong></td><td>";
                $strReturn .= self::array_to_html($v);
                $strReturn .= "</td></tr>";
            }
            $strReturn .= "\n</table>";
        } else {
            $strReturn = htmlentities($my_array);
        }
        return $strReturn;
    }

    public static function get_instance(){
        if(null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}