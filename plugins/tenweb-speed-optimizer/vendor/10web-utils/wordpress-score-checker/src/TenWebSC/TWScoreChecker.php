<?php

namespace TenWebSC;

class TWScoreChecker
{
    /**
     * Save the page speed in the post meta.
     *
     * @param $post_id
     * @param $old
     * @param $no_optimized
     *
     * @return void
     */
    public static function twsc_check_score( $post_id, $old = FALSE, $no_optimized = FALSE ) {
        $key = $old ? 'previous_score' : 'current_score';
        // Getting front_page placeholder instead of page ID for Home page.
        if ($post_id == 'front_page' || $post_id == get_option('page_on_front')) {
            $url = get_home_url();
            $page_score_old_meta = get_option('two-front-page-speed');
        } else {
            $url = get_permalink( $post_id );
            $page_score_old_meta = get_post_meta($post_id, 'two_page_speed', TRUE);
        }
        if ( empty($page_score_old_meta) ) {
            $page_score_old_meta = array();
            $page_score_old_meta[$key] = array();
        }
        if (!$url) {
            $page_score_old_meta[$key]['status'] = 'error_no_url';
            \TenWebWpTransients\OptimizerTransients::delete('two_optimize_inprogress_' . $post_id );
            self::twsc_update_score_info($post_id,$page_score_old_meta);
            return;
        }

        // To check the not optimized page score. This will need on the plugin update to have old scores for existing users.
        if ( $no_optimized ) {
            $url = add_query_arg(array('two_nooptimize' => 1), $url);
        }
        $desktop_score = self::twsc_google_check_score( $url, 'desktop' );
        if ( isset($desktop_score['error']) ) {
            $page_score_old_meta[$key]['status'] = 'error_no_score';
            \TenWebWpTransients\OptimizerTransients::delete('two_optimize_inprogress_' . $post_id );
            self::twsc_update_score_info($post_id,$page_score_old_meta);
            return;
        }
        $score = $desktop_score;

        $mobile_score = self::twsc_google_check_score( $url, 'mobile' );
        if ( isset($mobile_score['error']) ) {
            $page_score_old_meta[$key]['status'] = 'error_no_score';
            \TenWebWpTransients\OptimizerTransients::delete('two_optimize_inprogress_' . $post_id );
            self::twsc_update_score_info($post_id,$page_score_old_meta);
            return;
        }
        $score = array_merge($score, $mobile_score);
        $score['date'] = date('d.m.Y h:i:s a', strtotime(current_time( 'mysql' )));
        $score['status'] = 'completed';
        if ( $post_id == 'front_page' || $post_id == get_option('page_on_front') ) {
            $page_score = get_option('two-front-page-speed');
        }
        else {
            $page_score = get_post_meta($post_id, 'two_page_speed', TRUE);
        }
        if (empty($page_score)) {
            $page_score = array();
        }
        $page_score[$key] = $score;
        self::twsc_update_score_info($post_id,$page_score);
    }

    /**
     * Update the page speed in the post meta or option.
     *
     * @param $post_id
     * @param $page_score
     *
     * @return void
     */
    public static function twsc_update_score_info($post_id,$page_score)
    {
        if ($post_id == 'front_page' || $post_id == get_option('page_on_front')) {
            update_option('two-front-page-speed', $page_score);
            // If front page is a page and has ID, check and save the score also as post meta.
            $home_page_id = url_to_postid(get_home_url());
            if ( $home_page_id ) {
                update_post_meta($home_page_id, 'two_page_speed', $page_score);
            }
        } else {
            update_post_meta($post_id, 'two_page_speed', $page_score);
        }
    }

    /**
     * Get the page speed from Google by URL.
     *
     * @param $page_url
     * @param $strategy
     *
     * @return array
     */
    public static function twsc_google_check_score( $page_url, $strategy ) {
        $google_api_keys = array(
            'AIzaSyCQmF4ZSbZB8prjxci3GWVK4UWc-Yv7vbw',
            'AIzaSyAgXPc9Yp0auiap8L6BsHWoSVzkSYgHdrs',
            'AIzaSyCftPiteYkBsC2hamGbGax5D9JQ4CzexPU',
            'AIzaSyC-6oKLqdvufJnysAxd0O56VgZrCgyNMHg',
            'AIzaSyB1QHYGZZ6JIuUUce4VyBt5gF_-LwI5Xsk',
            'AIzaSyDZLf5UpZ914NoCZF16ad0PrspINs6ak0g',
            'AIzaSyDvLQHgtF94eha7sDCLIUiQ0lmfsIOR_sw',
            'AIzaSyAh8baU4m_C1qgSNsGiYU6q4iMDe6q_dSY',
            'AIzaSyCjwzqteBYBPdYxyXPrcQGNtoQ20U89G2A',
        );

        //flag to get not repeated google api key
        $taken_indexes = array();
        do {
            $random_index = self::getRandomIndex($taken_indexes, 0, count($google_api_keys) - 1);
            $taken_indexes[] = $random_index;
            $key = $google_api_keys[$random_index];
            $response = self::googleCheckScoreResponse( $key, $page_url, $strategy );
        } while ( is_wp_error($response) && count($taken_indexes) < 3 );
        $data = array();

        if ( is_array($response) && !is_wp_error($response) ) {
            $body = $response['body'];
            $body = json_decode($body);
            if ( isset($body->error) ) {
                $data['error'] = 1;
            }
            else {
                $data[$strategy . '_score'] = (float)(100 * $body->lighthouseResult->categories->performance->score);
                $data[$strategy . '_tti'] = trim(rtrim($body->lighthouseResult->audits->interactive->displayValue, 's'));
            }
        }
        else {
            $data['error'] = 1;
        }

        return $data;
    }

    protected static function googleCheckScoreResponse( $key, $page_url, $strategy ) {
        $url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . $page_url . "&key=".$key;
        if ( $strategy == "mobile" ) {
            $url .= "&strategy=mobile";
        }

        $response = wp_remote_get($url, array('timeout' => 300));
        return $response;
    }

    protected static function getRandomIndex($taken_indexes, $min, $max ) {
        do {
            $random_index = rand( $min, $max );
        } while ( in_array( $random_index, $taken_indexes ) && count($taken_indexes) < $max );
        return $random_index;
    }

    public static function twsc_recount_score( $post_id, $reanalyze_score_for ) {
        if ( $post_id == 'front_page' || $post_id == get_option('page_on_front') ) {
            $page_score = get_option('two-front-page-speed');
        }
        else {
            $page_score = get_post_meta($post_id, 'two_page_speed', TRUE);
        }

        if ( empty($page_score) ) {
            $page_score = array(
                'previous_score' => array(),
                'current_score' => array(),
            );
        }

        if ( $reanalyze_score_for == 'both' ) {
            $page_score['previous_score']['status'] = 'inprogress';
            $page_score['current_score']['status'] = 'inprogress';
            self::twsc_update_score_info($post_id,$page_score);
            self::twsc_check_score($post_id,TRUE,TRUE);
            self::twsc_check_score($post_id);
        } elseif ( $reanalyze_score_for == 'old' ) {
            $page_score['previous_score']['status'] = 'inprogress';
            self::twsc_update_score_info($post_id,$page_score);
            self::twsc_check_score($post_id,TRUE,TRUE);
        } else {
            $page_score['current_score']['status'] = 'inprogress';
            self::twsc_update_score_info($post_id,$page_score);
            self::twsc_check_score($post_id );
        }
        if ( $post_id == 'front_page' || $post_id == get_option('page_on_front') ) {
            $page_score = get_option('two-front-page-speed');
        }
        else {
            $page_score = get_post_meta($post_id, 'two_page_speed', TRUE);
        }
        return $page_score;
    }
}