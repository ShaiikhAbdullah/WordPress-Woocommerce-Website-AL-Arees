<?php

namespace TenWebOptimizer;

use DateTime;

/*
 * General helpers.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerNPS
{
    public static function update_nps_survey_data()
    {
        $return_data = [
            'success' => false,
            'message' => 'error'
        ];
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $nps_survey_response = wp_remote_get( // phpcs:ignore
            TENWEB_SO_CRITICAL_URL . '/v1/workspaces/domains/' . $domain_id . '/get-nps-data', [
                'timeout' => 5, // phpcs:ignore
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'cookies' => []
            ]);

        if (!is_wp_error($nps_survey_response) && is_array($nps_survey_response) && isset($nps_survey_response['body'])) {
            $nps_data = $nps_survey_response['body'];
            $nps_data_args = json_decode($nps_data, true);

            if (isset($nps_data_args['status_code']) && $nps_data_args['status_code'] == 200) {
                if (isset($nps_data_args['data'])) {
                    $nps_data = $nps_data_args['data'];
                    $show_nps_survey = 0;

                    if ($nps_data && isset($nps_data['agreement_date']) && !isset($nps_data['nps'])) {
                        $agreement_date = date_create($nps_data['agreement_date']);
                        $current_date = new DateTime();

                        $interval = date_diff($agreement_date, $current_date);
                        $interval_in_days = $interval->days;

                        if ($interval_in_days > 7) {
                            $show_nps_survey = 1;
                        }
                    }
                    $nps_data['show_nps_survey'] = $show_nps_survey;
                    update_option(
                        'two_nps_data',
                        $nps_data,
                        false
                    );
                }
                $return_data['success'] = true;
                $return_data['message'] = 'success';
            }
        }

        return json_encode($return_data); // phpcs:ignore
    }

    public static function set_nps_survey_data($args)
    {
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');

        if ($access_token && $domain_id) {
            wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/domains/' . $domain_id . '/set-nps-data', [
                'timeout' => 1,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [
                    'nps' => $args['nps'],
                    'source' => $args['source'],
                ],
                'cookies' => []
            ]);
        }
        delete_action('two_check_nps', function () { wp_clear_scheduled_hook('two_check_nps'); });
    }

    public static function front_page_significant_improved()
    {
        $homepage_speed = get_option('two-front-page-speed');

        if (!empty($homepage_speed) && isset($homepage_speed['current_score']) && isset($homepage_speed['previous_score'])
            && isset($homepage_speed['current_score']['desktop_score']) && isset($homepage_speed['previous_score']['desktop_score'])
            && (int) $homepage_speed['previous_score']['desktop_score'] !== 0 && (int) $homepage_speed['previous_score']['mobile_score'] !== 0) {
            /* score improvement calculation */
            $desktopScoreImprove = (($homepage_speed['current_score']['desktop_score']
                        - $homepage_speed['previous_score']['desktop_score']) / $homepage_speed['previous_score']['desktop_score']) * 100;
            $mobileScoreImprove = (($homepage_speed['current_score']['mobile_score']
                        - $homepage_speed['previous_score']['mobile_score']) / $homepage_speed['previous_score']['mobile_score']) * 100;
            $maxScore = max($desktopScoreImprove, $mobileScoreImprove);

            if (round($maxScore) > 20) {
                return true;
            }
        }

        return false;
    }
}
