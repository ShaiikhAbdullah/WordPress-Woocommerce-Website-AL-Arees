<?php

namespace TenWebOptimizer;

use Exception;
use WP_Query;

class OptimizerDataRepository
{
    public $modes = [];

    public function __construct()
    {
        $this->modes = \TenWebOptimizer\OptimizerUtils::get_modes();
    }

    public function get_incompatible_active_plugins()
    {
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot get incompatible plugins',
        ];

        try {
            $two_incompatible_plugins = OptimizerUtils::get_conflicting_plugins();
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Successfully';
            $data_for_response['two_incompatible_plugins'] = $two_incompatible_plugins;

            return $data_for_response;
        } catch (Exception $exception) {
            return new $data_for_response();
        }
    }

    public function get_modes($page_id)
    {
        $data_for_response = [
            'success' => false,
            'message' => 'Mode not found',
        ];

        try {
            if ($page_id === 'all') {
                $mode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);
            } elseif ($page_id === 'front_page') {
                $mode = get_option('two_mode_front_page');
            } elseif (false !== strpos($page_id, 'term_')) {
                $term_id = (int) ltrim($page_id, 'term_');
                $mode = get_term_meta($term_id, 'two_mode', true);
            } elseif (false !== strpos($page_id, 'user_')) {
                $user_id = (int) ltrim($page_id, 'user_');
                $mode = get_user_meta($user_id, 'two_mode', true);
            } else {
                $mode = get_post_meta($page_id, 'two_mode', true);
            }

            if (is_array($mode) && isset($mode['mode']) && isset($this->modes[$mode['mode']])) {
                $mode = $mode['mode'];
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'success';
                $data_for_response['mode'] = $mode;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting modes';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return $data_for_response;
        }

        return $data_for_response;
    }

    public function get_global_data()
    {
        $data_for_response = [
            'success' => false,
            'message' => 'Data not found',
            'data' => []
        ];

        try {
            $global_mode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);

            if (is_array($global_mode) && isset($global_mode['mode'])) {
                $global_mode = $global_mode['mode'];
            } else {
                $global_mode = false;
            }
            $count_posts = wp_count_posts('post');
            $count_pages = wp_count_posts('page');
            $count_terms = (int) get_terms(['fields' => 'count', 'hide_empty' => false]);
            $two_update_available = OptimizerUtils::check_plugin_update();
            global $TwoSettings;
            $global_data = [
                'update_available' => $two_update_available,
                'site_url' => site_url(),
                'version' => TENWEB_SO_VERSION,
                'global_mode' => $global_mode,
                'test_mode' => 'on' == $TwoSettings->get_settings('two_test_mode') ? 'on' : 'off',
                'page_count' => $count_pages->publish,
                'post_count' => $count_posts->publish,
                'term_count' => $count_terms,
            ];
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Data found successfully';
            $data_for_response['data'] = $global_data;
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting data';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return $data_for_response;
        }

        return $data_for_response;
    }

    public function get_pages($is_custom = 0)
    {
        $data_for_response = [
            'success' => false,
            'message' => 'Pages not found',
            'data' => []
        ];

        try {
            $so_pages_list = [
                'pages' => [],
            ];
            $two_optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            $posts_args = [
                'meta_key' => 'two_mode',
                'post_type' => 'any',
            ];
            $optimized_posts = new WP_Query($posts_args);

            $terms_args = [
                'taxonomy' => [],
                'hide_empty' => false,
                'meta_key' => 'two_mode',
            ];

            $terms = get_terms($terms_args);

            $users_args = [
                'meta_key' => 'two_mode',
            ];

            $users = get_users($users_args);

            if (isset($optimized_posts->posts)) {
                foreach ($optimized_posts->posts as $post) {
                    if (isset($post->ID) && !isset($two_optimized_pages[$post->ID])) {
                        $two_optimized_pages[$post->ID] = [
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'url' => get_permalink($post->ID),
                            'status' => 'success',
                        ];
                    }
                }
            }

            if (is_array($terms)) {
                foreach ($terms as $term) {
                    if (isset($term->term_id)) {
                        $id = 'term_' . $term->term_id;

                        if (!isset($two_optimized_pages[$id])) {
                            $two_optimized_pages[$id] = [
                                'id' => $id,
                                'title' => $term->name,
                                'url' => get_term_link($term->term_id),
                                'status' => 'success',
                            ];
                        }
                    }
                }
            }

            if (is_array($users)) {
                foreach ($users as $user) {
                    if (isset($user->data, $user->data->ID)) {
                        $id = 'user_' . $user->data->ID;

                        if (!isset($two_optimized_pages[$id])) {
                            $two_optimized_pages[$id] = [
                                'id' => $id,
                                'title' => $user->data->display_name,
                                'url' => get_author_posts_url($user->data->ID),
                                'status' => 'success',
                            ];
                        }
                    }
                }
            }

            if (is_array($two_optimized_pages)) {
                foreach ($two_optimized_pages as $p_id => $so_page) {
                    $so_page_data = [
                        'page_id' => $p_id,
                        'title' => $so_page['title'],
                        'url' => $so_page['url'],
                        'status' => $so_page['status'],
                    ];

                    if ($p_id === 'front_page') {
                        $page_mode = get_option('two_mode_front_page');
                        $two_optimized_date_front_page = get_option('two_optimized_date_front_page');
                    } elseif (false !== strpos($p_id, 'term_')) {
                        $p_id = (int) ltrim($p_id, 'term_');
                        $so_page_data['page_id'] = 'term_' . (int) ltrim($so_page_data['page_id'], 'term_');
                        $page_mode = get_term_meta($p_id, 'two_mode', true);
                        $two_optimized_date = get_term_meta($p_id, 'two_optimized_date', true);
                    } elseif (false !== strpos($p_id, 'user_')) {
                        $p_id = (int) ltrim($p_id, 'user_');
                        $so_page_data['page_id'] = 'user_' . (int) ltrim($so_page_data['page_id'], 'user_');
                        $page_mode = get_user_meta($p_id, 'two_mode', true);
                        $two_optimized_date = get_user_meta($p_id, 'two_optimized_date', true);
                    } else {
                        $p_id = (int) $p_id;
                        $so_page_data['page_id'] = (int) $so_page_data['page_id'];
                        $page_mode = get_post_meta($p_id, 'two_mode', true);
                        $two_optimized_date = get_post_meta($p_id, 'two_optimized_date', true);
                    }

                    if (is_array($page_mode) && isset($page_mode['mode'])) {
                        $page_mode_name = $page_mode['mode'];
                    } else {
                        $page_mode_name = false;
                    }
                    $so_page_data['mode'] = $page_mode_name;

                    if ((int) $is_custom === 1) {
                        if (!isset($page_mode['is_custom']) || !$page_mode['is_custom']) {
                            continue;
                        }
                    }

                    if (isset($so_page['critical_date'])) {
                        $so_page_data['date'] = $so_page['critical_date'];
                    } else {
                        if (isset($two_optimized_date)) {
                            $so_page_data['date'] = $two_optimized_date;
                        } elseif (isset($two_optimized_date_front_page)) {
                            $so_page_data['date'] = $two_optimized_date_front_page;
                        }
                    }

                    $so_page_data['is_custom'] = 0;

                    if (isset($page_mode['is_custom'])) {
                        $so_page_data['is_custom'] = $page_mode['is_custom'];
                    }

                    $so_pages_list['pages'][] = $so_page_data;
                }
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Pages found successfully';
                $data_for_response['data'] = $so_pages_list;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting pages';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return $data_for_response;
        }

        return $data_for_response;
    }

    public function get_page_cache_status()
    {
        $data_for_response = [
            'success' => false,
            'page_cache' => true,
            'message' => 'Cannot get page cache status',
            'clear_cache_date' => '',
        ];

        try {
            global $TwoSettings;
            $data_for_response['clear_cache_date'] = $TwoSettings->get_settings('two_clear_cache_date', '');
            $two_page_cache = $TwoSettings->get_settings('two_page_cache', '');
            $data_for_response['success'] = true;

            if ($two_page_cache === 'on') {
                $data_for_response['message'] = 'Page cache enabled';
                $data_for_response['page_cache'] = true;
            } else {
                $data_for_response['message'] = 'Page cache disabled';
                $data_for_response['page_cache'] = false;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting page cache status';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return $data_for_response;
        }

        return $data_for_response;
    }

    public function get_webp_status()
    {
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot get webp status.',
        ];

        try {
            global $TwoSettings;
            $webp_status = [];

            if (TENWEB_SO_HOSTED_ON_10WEB) {
                $webp_status['hosting'] = '10Web';
                $webp_status['webp_delivery'] = $TwoSettings->get_settings('two_enable_nginx_webp_delivery');
            } else {
                if (TENWEB_SO_HOSTED_ON_NGINX) {
                    $webp_status['hosting'] = 'NGINX';
                } else {
                    $webp_status['hosting'] = 'APACHE';
                    $webp_status['htaccess_writable'] = TENWEB_SO_HTACCESS_WRITABLE;
                    $webp_status['webp_delivery'] = $TwoSettings->get_settings('two_enable_htaccess_webp_delivery');
                }
                $two_webp_delivery_working = \TenWebOptimizer\OptimizerUtils::testWebPDelivery();
                $webp_status['webp_delivery_working'] = $two_webp_delivery_working;
                $webp_status['picture_webp_delivery'] = $TwoSettings->get_settings('two_enable_picture_webp_delivery');
            }

            if ($webp_status) {
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'WebP status collected successfully.';
                $data_for_response['data'] = $webp_status;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting webp status';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return $data_for_response;
        }

        return $data_for_response;
    }

    public function get_settings($filter = '')
    {
        $filter_arr = [];

        if (!empty($filter)) {
            $filter_arr = explode(',', $filter);
        }
        $TwoSettings = new \TenWebOptimizer\OptimizerSettings();
        $settings_data = $TwoSettings->settings_names;
        $default_settings = $TwoSettings->default_settings;
        $two_settings = get_option('two_settings');
        $two_settings = json_decode($two_settings, true);
        $two_settings['two_critical_pages'] = OptimizerUtils::getCriticalPages();
        $filter_data = [];

        if (!empty($filter_arr)) {
            foreach ($filter_arr as $option_name) {
                if (isset($settings_data[$option_name])) {
                    $filter_data[$option_name] = $settings_data[$option_name];
                }
            }

            if (!empty($filter_data)) {
                $settings_data = $filter_data;
            }
        }

        foreach ($two_settings as $key => $val) {
            if (isset($settings_data[$key])) {
                if ($settings_data[$key]['type'] == 'checkbox' && (empty($val) || $val === 'false')) {
                    $val = 'off';
                }

                if ($settings_data[$key]['default'] == 'true') {
                    $settings_data[$key]['default'] = 'on';
                }

                if ($key === 'two_critical_status' && $val === 'true') {
                    $val = 'on';
                }

                if ($settings_data[$key]['type'] == 'select') {
                    if (empty($val) && isset($default_settings[$key])) {
                        $val = $default_settings[$key];
                    }
                }
                $settings_data[$key]['value'] = $val;
            }
        }

        return $settings_data;
    }
}
