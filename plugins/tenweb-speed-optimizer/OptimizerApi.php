<?php

namespace TenWebOptimizer;

use Exception;
use Tenweb_Authorization\Login;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class OptimizerApi
{
    public $modes = [];

    private $OptimizerDataRepository = null;

    public function __construct()
    {
        require __DIR__ . '/OptimizerDataRepository.php';
        $this->OptimizerDataRepository = new \TenWebOptimizer\OptimizerDataRepository();

        $this->modes = \TenWebOptimizer\OptimizerUtils::get_modes();
        add_action('rest_api_init', [$this, 'two_rest']);
    }

    private function clear_response()
    {
        // Clear all unexpected output. We don't want to see a warning in rest response.
        while (ob_get_level() !== 0) {
            ob_end_clean();
        }
    }

    public function two_rest()
    {
        register_rest_route(
            'tenweb_so/v1',
            'set_score',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_score'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'score_data' => [
                        'required' => true,
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'set_critical',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_critical'],
                'permission_callback' => [$this, 'check_critical_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                    'token' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'optimization_data',
            [
                'methods' => 'GET',
                'callback' => [$this, 'optimization_data'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'optimization_data',
            [
                'methods' => 'POST',
                'callback' => [$this, 'optimization_data'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'set_modes',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_modes'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                    'mode' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_mode']
                    ],
                    'is_custom' => [
                        'type' => 'string',
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_modes',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_modes'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_modes',
            [
                'methods' => 'POST',
                'callback' => [$this, 'get_modes'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'clear_cache',
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'clear_cache'],
                'args' => [
                    'clear_critical' => [
                        'type' => 'boolean',
                        'required' => false,
                        'validate_callback' => [$this, 'validate_boolean'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_page_id',
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'get_page_id'],
                'args' => [
                    'page_url' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_page_id',
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'get_page_id'],
                'args' => [
                    'page_url' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_pages',
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'get_pages'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_pages',
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'get_pages'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'delete_so_page',
            [
                'methods' => 'POST',
                'callback' => [$this, 'delete_so_page'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'logout',
            [
                'methods' => 'POST',
                'callback' => [$this, 'logout'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        /*
         * Used in old connection logic with redirects and other places, do not remove
         */
        register_rest_route(
            'tenweb_so/v1',
            'check_domain',
            [
                'methods' => 'GET',
                'permission_callback' => '__return_true',
                'callback' => [$this, 'check_domain'],
            ]
        );

        /*
         * Used in old connection logic with redirects and other places, do not remove
         */
        register_rest_route(
            'tenweb_so/v1',
            'check_domain',
            [
                'methods' => 'POST',
                'permission_callback' => '__return_true',
                'callback' => [$this, 'check_domain'],
            ]
        );

        /*
         * Used in new connection logic without redirects
         */
        register_rest_route(
            'tenweb_so/v1',
            'connect_from_core',
            [
                'methods' => 'POST',
                'permission_callback' => '__return_true',
                'callback' => [$this, 'connect_from_core'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_webp_status',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_webp_status'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_webp_status',
            [
                'methods' => 'POST',
                'callback' => [$this, 'get_webp_status'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'set_webp_status',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_webp_status'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'webp_delivery' => [
                        'type' => 'boolean',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_boolean'],
                    ],
                    'picture_webp_delivery' => [
                        'type' => 'boolean',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_boolean'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'page_cache',
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'page_cache'],
                'args' => [
                    'status' => [
                        'type' => 'boolean',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_boolean'],
                    ],
                ],
            ]
        );
        register_rest_route(
            'tenweb_so/v1',
            'get_page_cache_status',
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_authorization'],
                'callback' => [$this, 'get_page_cache_status'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'update_settings',
            [
                'methods' => 'POST',
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'key' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                    'val' => [
                        'type' => 'string',
                        'validate_callback' => [$this, 'validate_option_value']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_settings',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'get_incompatible_active_plugins',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_incompatible_active_plugins'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'set_cloudflare_status',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_cloudflare_cache_status'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'status' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_cloudflare_status']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'update_nps',
            [
                'methods' => 'POST',
                'callback' => [$this, 'update_nps'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'nps' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'two_settings',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_two_settings'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'filter' => [
                        'type' => 'string',
                        'validate_callback' => [$this, 'validate_string'],
                    ],
                ],
            ]
        );
        register_rest_route(
            'tenweb_so/v1',
            'two_settings',
            [
                'methods' => 'POST',
                'callback' => [$this, 'set_two_settings'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'settings' => [
                        'type' => 'json',
                        'required' => true,
                    ],
                ],
            ]
        );
        register_rest_route(
            'tenweb_so/v1',
            'regenerate_critical',
            [
                'methods' => 'POST',
                'callback' => [$this, 'regenerate_critical'],
                'permission_callback' => [$this, 'check_authorization'],
                'args' => [
                    'page_id' => [
                        'type' => 'txt',
                        'required' => true,
                        'validate_callback' => [$this, 'validate_page_id']
                    ],
                ],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'regenerate_webp',
            [
                'methods' => 'POST',
                'callback' => [$this, 'regenerate_home_webp'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );

        register_rest_route(
            'tenweb_so/v1',
            'delete_webp_images',
            [
                'methods' => 'POST',
                'callback' => [$this, 'delete_webp_images'],
                'permission_callback' => [$this, 'check_authorization'],
            ]
        );
    }

    public function regenerate_home_webp(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot regenerate critical',
        ];

        try {
            $request_webp_action = OptimizerUtils::request_webp_action('regenerate');
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Regenerate critical started';
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in regenerate critical';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function delete_webp_images(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot regenerate critical',
        ];

        try {
            $request_webp_action = OptimizerUtils::request_webp_action('delete');
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Regenerate critical started';
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in regenerate critical';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function regenerate_critical(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot regenerate critical',
        ];

        try {
            $page_id = $request['page_id'];
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Regenerate critical started';
            \TenWebOptimizer\OptimizerCriticalCss::generate_critical_css_by_id($page_id, false, 'dashboard_settings');
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in regenerate critical';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function get_two_settings(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot get settings',
        ];

        try {
            $filter = sanitize_text_field($request['filter']);
            $settings = $this->OptimizerDataRepository->get_settings($filter);

            return new WP_REST_Response($settings, 200);
        } catch (Exception $exception) {
            return new WP_REST_Response($data_for_response, 500);
        }
    }

    public function set_two_settings(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot set option',
            'accepted_value_error' => [],
        ];

        try {
            global $TwoSettings;
            $settings_names = $TwoSettings->settings_names;
            $settings = json_decode($request['settings'], true);

            if (is_array($settings)) {
                if (isset($settings['two_bg_lazyload'])) {
                    $settings['two_img_in_viewport_lazyload'] = $settings['two_bg_lazyload'];
                }

                foreach ($settings as $key => $val) {
                    $key = sanitize_text_field($key);

                    if (is_array($val)) {
                        $val = array_map('sanitize_text_field', $val);
                    } else {
                        $val = sanitize_text_field($val);
                    }

                    if (isset($settings_names[$key])) {
                        if ($this->validate_accepted_value($settings_names[$key], $val)) {
                            if ($key === 'two_critical_status') {
                                $val = 'on' === $val ? 'true' : 'false';
                            }

                            $off_is_empty_string_options = [
                                'two_delay_all_js_execution',
                                'two_aggregate_js',
                                'two_minify_js',
                                'two_lazyload',
                                'two_bg_lazyload',
                                'two_aggregate_css',
                                'two_include_inline_css',
                                'two_minify_css',
                                'two_iframe_lazyload',
                                'two_video_lazyload',
                            ];

                            if (in_array($key, $off_is_empty_string_options) && $val === 'off') {
                                $val = '';
                            }
                            $TwoSettings->update_setting($key, $val);
                        } else {
                            $data_for_response['accepted_value_error'][$key] = $settings_names[$key]['accepted_value'];
                        }
                    }
                }

                if (!empty($data_for_response['accepted_value_error'])) {
                    return new WP_REST_Response($data_for_response, 429);
                }
                \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true, true, true, 'front_page', false, true, true, false, '');
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Option updated successfully.';
                $mode = '';

                if (isset($settings['two_delay_all_js_execution']) && $settings['two_delay_all_js_execution'] == 'off') {
                    $mode = 'balanced';
                } elseif (isset($settings['two_delay_all_js_execution']) && $settings['two_delay_all_js_execution'] == 'on') {
                    $mode = 'strong';
                }

                if (isset($settings['two_critical_status']) && $settings['two_critical_status'] == 'off') {
                    if ($mode == 'balanced') {
                        $mode = 'standard';
                    } else {
                        $mode = 'strong';
                    }
                } elseif (isset($settings['two_critical_status']) && $settings['two_critical_status'] == 'on') {
                    if ($mode == 'strong') {
                        $mode = 'extreme';
                    }
                }

                \TenWebOptimizer\OptimizerAdmin::set_global_mode($mode);

                return new WP_REST_Response($data_for_response, 200);
            }
            $data_for_response['message'] = 'Option name not found.';
            $data_for_response['error'] = 'Option name not found.';

            return new WP_REST_Response($data_for_response, 404);
        } catch (Exception $exception) {
            return new WP_REST_Response($data_for_response, 500);
        }
    }

    public function update_nps(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot update nps',
        ];

        try {
            $nps = sanitize_text_field($request['nps']);
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Nps updated successfully';
            $two_nps_data = [
                'nps' => $nps,
            ];
            update_option('two_nps_data', $two_nps_data, false);

            return new WP_REST_Response($data_for_response, 500);
        } catch (Exception $exception) {
            return new WP_REST_Response($data_for_response, 500);
        }
    }

    public function set_cloudflare_cache_status(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Invalid status',
        ];

        try {
            $status = $request['status'];
            global $TwoSettings;
            $TwoSettings->update_setting('cloudflare_cache_status', $status);
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Cloudflare status installed successfully';
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in set Cloudflare status';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function optimization_data()
    {
        $this->clear_response();
        $global_data = $this->OptimizerDataRepository->get_global_data();
        $global_mode_data = $this->OptimizerDataRepository->get_modes('all');
        $page_cache_status = $this->OptimizerDataRepository->get_page_cache_status();
        $pages_data = $this->OptimizerDataRepository->get_pages();
        $pages_data_custom = $this->OptimizerDataRepository->get_pages(1);
        $incompatible_active_plugins = $this->OptimizerDataRepository->get_incompatible_active_plugins();
        $webp_status = $this->OptimizerDataRepository->get_webp_status();
        $return_data = [
            'global_data' => $global_data,
            'get_modes' => $global_mode_data,
            'get_page_cache_status' => $page_cache_status,
            'get_pages' => [
                'free' => $pages_data,
                'paid' => $pages_data_custom,
            ],
            'get_incompatible_active_plugins' => $incompatible_active_plugins,
            'get_webp_status' => $webp_status,
        ];

        return new WP_REST_Response($return_data, 200);
    }

    public function set_critical()
    {
        $this->clear_response();
        OptimizerUtils::set_critical();
    }

    public function get_incompatible_active_plugins()
    {
        $this->clear_response();
        $incompatible_active_plugins = $this->OptimizerDataRepository->get_incompatible_active_plugins();

        if ($incompatible_active_plugins['success']) {
            return new WP_REST_Response($incompatible_active_plugins, 200);
        }

        return new WP_REST_Response($incompatible_active_plugins, 500);
    }

    public function get_settings()
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot get settings',
        ];

        try {
            $two_settings = get_option('two_settings');
            $two_settings = json_decode($two_settings, true);
            $two_critical_pages = OptimizerUtils::getCriticalPages();

            if (isset($two_critical_pages) && is_array($two_critical_pages)) {
                $two_settings['two_critical_pages'] = $two_critical_pages;
            }
            $two_triggerPostOptimizationTasks = get_option('two_triggerPostOptimizationTasks');
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Successfully';
            $data_for_response['two_triggerPostOptimizationTasks'] = $two_triggerPostOptimizationTasks;
            $data_for_response['settings'] = $two_settings;

            return new WP_REST_Response($data_for_response, 200);
        } catch (Exception $exception) {
            return new WP_REST_Response($data_for_response, 500);
        }
    }

    public function update_settings(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot set option',
        ];

        try {
            global $TwoSettings;
            $settings_names = $TwoSettings->settings_names;
            $key = sanitize_text_field($request['key']);

            if (!isset($request['val'])) {
                $option = $TwoSettings->get_settings($key);
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Successfully';
                $data_for_response[$key] = $option;

                return new WP_REST_Response($data_for_response, 200);
            } else {
                $val = sanitize_text_field($request['val']);
            }

            if (isset($settings_names[$key])) {
                if (isset($settings_names[$key]['type']) && $settings_names[$key]['type'] === 'textarea') {
                    $option = $TwoSettings->get_settings($key);

                    if (!empty($option)) {
                        $arr_option = explode(',', $option);
                        $el_key = array_search($val, $arr_option, false); // phpcs:ignore

                        if ($el_key !== false) {
                            unset($arr_option[$el_key]);
                            $val = implode(',', $arr_option);
                            $data_for_response['success'] = true;
                            $data_for_response['message'] = 'Option deleted successfully.';
                        } else {
                            $val = $option . ',' . $val;
                        }
                    }
                }

                if (!$data_for_response['success']) {
                    $data_for_response['success'] = true;
                    $data_for_response['message'] = 'Option updated successfully.';
                }
                $TwoSettings->update_setting($key, $val);

                return new WP_REST_Response($data_for_response, 200);
            }

            $data_for_response['message'] = 'Option name not found.';
            $data_for_response['error'] = 'Option name not found.';

            return new WP_REST_Response($data_for_response, 500);
        } catch (Exception $exception) {
            return new WP_REST_Response($data_for_response, 500);
        }
    }

    /**
     * @return WP_REST_Response
     */
    public function logout(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot logout client',
            'code' => 'not_ok'
        ];

        try {
            \TenWebOptimizer\OptimizerAdmin::disconnect_from_tenweb(true);
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Successfully logged out';
            $data_for_response['code'] = 'ok';
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in logging out client';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function get_page_cache_status(WP_REST_Request $request)
    {
        $this->clear_response();
        $page_cache_status = $this->OptimizerDataRepository->get_page_cache_status();

        if ($page_cache_status['success']) {
            return new WP_REST_Response($page_cache_status, 200);
        }

        return new WP_REST_Response($page_cache_status, 500);
    }

    /**
     * @return WP_REST_Response
     */
    public function page_cache(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot change page cache status',
        ];

        try {
            $status = $request['status'];
            global $TwoSettings;

            if ($status) {
                $TwoSettings->update_setting('two_page_cache', 'on');
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Page cache enabled';
            } else {
                $TwoSettings->update_setting('two_page_cache', '');
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Page cache disabled';
            }
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in updating page cache status';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function delete_so_page(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cannot delete page',
        ];

        try {
            global $TwoSettings;
            $page_id = $request['page_id'];

            if (OptimizerUrl::isCriticalSavedInSettings($page_id)) {
                delete_option('two_mode_front_page');
                $two_critical_pages = $TwoSettings->get_settings('two_critical_pages');
                unset($two_critical_pages[$page_id], $two_critical_pages['']);
                $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
            } else {
                delete_post_meta($page_id, 'two_mode');
                delete_post_meta($page_id, 'two_critical_pages');
            }

            if (has_action('two_page_optimized_removed')) {
                do_action('two_page_optimized_removed', $page_id);
            }
            $prefix = 'critical/two_' . $page_id . '_*.*';
            \TenWebOptimizer\OptimizerUtils::delete_files_by_prefix($prefix);
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
            //check if page from no_optimize_pages list delete the option
            $no_optimize_pages_list = get_option('no_optimize_pages');

            if (isset($no_optimize_pages_list[$page_id])) {
                unset($no_optimize_pages_list[$page_id]);
                update_option('no_optimize_pages', $no_optimize_pages_list);
            }
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Page has been deleted';
            OptimizerUtils::update_post();
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in deleting page';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function get_pages(WP_REST_Request $request)
    {
        $this->clear_response();
        $is_custom = 0;

        if (isset($request['is_custom'])) {
            $is_custom = $request['is_custom'];
        }
        $pages_data = $this->OptimizerDataRepository->get_pages($is_custom);

        if ($pages_data['success']) {
            return new WP_REST_Response($pages_data, 200);
        }

        return new WP_REST_Response($pages_data, 500);
    }

    /**
     * @return WP_REST_Response
     */
    public function check_domain(WP_REST_Request $request)
    {
        $this->clear_response();

        if (get_site_option(TENWEB_PREFIX . '_is_available') !== '1') {
            update_site_option(TENWEB_PREFIX . '_is_available', '1');
        }
        $parameters = self::wp_unslash_conditional($request->get_body_params());

        if (isset($parameters['confirm_token'])) {
            if (Login::get_instance()->checkConfirmToken($parameters['confirm_token'])) {
                $data_for_response = [
                    'code' => 'ok',
                    'data' => 'it_was_me'  // do not change
                ];
                $headers_for_response = ['tenweb_check_domain' => 'it_was_me'];
            } else {
                $data_for_response = [
                    'code' => 'ok',
                    'data' => 'it_was_not_me' // do not change
                ];
                $headers_for_response = ['tenweb_check_domain' => 'it_was_not_me'];
            }
        } else {
            $data_for_response = [
                'code' => 'ok',
                'data' => 'alive'  // do not change
            ];
            $headers_for_response = ['tenweb_check_domain' => 'alive'];

            if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                $data_for_response['data'] = 'alive_but_not_connected';
                $headers_for_response['tenweb_check_domain'] = 'alive_but_not_connected'; //do not change
            }
        }

        $tenweb_hash = $request->get_header('tenweb-check-hash');

        if (!empty($tenweb_hash)) {
            $encoded = '__' . $tenweb_hash . '.';
            $encoded .= base64_encode(json_encode($data_for_response)); // phpcs:ignore
            $encoded .= '.' . $tenweb_hash . '__';

            $data_for_response['encoded'] = $encoded;
            \Tenweb_Authorization\Helper::set_error_log('tenweb-check-hash', $encoded);
        }

        return new WP_REST_Response($data_for_response, 200, $headers_for_response);
    }

    /**
     * @return WP_REST_Response
     */
    public function clear_cache(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Cache not cleared',
        ];
        $clear_critical = true;

        if (isset($request['clear_critical'])) {
            $clear_critical = rest_sanitize_boolean($request['clear_critical']);
        }

        try {
            \TenWebOptimizer\OptimizerUtils::two_update_subscription()['tenweb_subscription_id'];
            OptimizerUtils::update_post();

            if (\TenWebOptimizer\OptimizerAdmin::clear_cache(false, true, true, true, 'front_page', $clear_critical)) {
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Cache cleared';
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in clearing cache';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function get_modes(WP_REST_Request $request)
    {
        $this->clear_response();
        $page_id = $request['page_id'];
        $modes_data = $this->OptimizerDataRepository->get_modes($page_id);

        if ($modes_data['success']) {
            return new WP_REST_Response($modes_data, 200);
        }

        return new WP_REST_Response($modes_data, 500);
    }

    /**
     * @return WP_REST_Response
     */
    public function set_modes(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Invalid mode',
        ];

        try {
            global $TwoSettings;
            $settings_names = $TwoSettings->settings_names;
            $mode = $request['mode'];
            $page_id = $request['page_id'];
            $is_custom = (int) $request['is_custom'];
            $no_optimize_pages_list = get_option('no_optimize_pages');

            if ($page_id != 'all') {
                $post_data = OptimizerUtils::get_permalink_name_by_id($page_id);
                $page_url = $post_data[ 'url' ];
            }

            if (isset($page_url)) {
                if ($mode == 'no_optimize') {
                    if (!is_array($no_optimize_pages_list)) {
                        $no_optimize_pages_list = [];
                    }
                    $no_optimize_pages_list[$page_id] = $page_url;
                } elseif (is_array($no_optimize_pages_list)) {
                    unset($no_optimize_pages_list[$page_id]);
                }
                update_option('no_optimize_pages', $no_optimize_pages_list);
            }

            if (isset($this->modes[$mode])) {
                if ($page_id == 'all') {
                    foreach ($this->modes[$mode] as $key => $val) {
                        if ($key === 'two_delay_all_js_execution') {
                            if ($val) {
                                $TwoSettings->update_setting('two_delay_all_js_execution', 'on');
                            } else {
                                $TwoSettings->update_setting('two_delay_all_js_execution', '');
                            }
                        } elseif (isset($settings_names[$key])) {
                            $TwoSettings->update_setting($key, $val);
                        } elseif ($key === 'critical_enabled') {
                            if ($val) {
                                $TwoSettings->update_setting('two_critical_status', 'true');
                            } else {
                                $TwoSettings->update_setting('two_critical_status', '');
                            }
                        }
                    }
                    update_option('two_default_mode', $this->modes[$mode]);
                } else {
                    OptimizerCriticalCss::generate_critical_css_by_id($page_id, false, 'from_api');
                    $this->modes[$mode]['is_custom'] = 0;

                    if ($is_custom === 1) {
                        $this->modes[$mode]['is_custom'] = 1;
                    }

                    if ($page_id === 'front_page') {
                        update_option('two_mode_front_page', $this->modes[$mode]);
                    } elseif (false !== strpos($page_id, 'term_')) {
                        $term_id = (int) ltrim($page_id, 'term_');
                        update_term_meta($term_id, 'two_mode', $this->modes[$mode]);
                    } elseif (false !== strpos($page_id, 'user_')) {
                        $user_id = (int) ltrim($page_id, 'user_');
                        update_user_meta($user_id, 'two_mode', $this->modes[$mode]);
                    } else {
                        update_post_meta($page_id, 'two_mode', $this->modes[$mode]);
                    }
                }
                OptimizerUtils::update_post();
                $data_for_response['success'] = true;
                $data_for_response['message'] = 'Mode installed successfully';
            } else {
                return new WP_REST_Response($data_for_response, 404);
            }

            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in applying page';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function get_webp_status(WP_REST_Request $request)
    {
        $this->clear_response();
        $webp_status = $this->OptimizerDataRepository->get_webp_status();

        if ($webp_status['success']) {
            return new WP_REST_Response($webp_status, 200);
        } else {
            return new WP_REST_Response($webp_status, 500);
        }
    }

    /**
     * @return WP_REST_Response
     */
    public function set_webp_status(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Nothing to change',
        ];

        try {
            global $TwoSettings;
            $webp_delivery = $request['webp_delivery'] ? 'on' : '';
            $picture_webp_delivery = $request['picture_webp_delivery'] ? 'on' : '';

            if ($TwoSettings->get_settings('two_enable_picture_webp_delivery') != $picture_webp_delivery) {
                $TwoSettings->update_setting('two_enable_picture_webp_delivery', $picture_webp_delivery);
                $data_for_response['success'] = true;
            }

            if (TENWEB_SO_HOSTED_ON_10WEB) {
                if ($TwoSettings->get_settings('two_enable_nginx_webp_delivery') != $webp_delivery) {
                    $TwoSettings->update_setting('two_enable_nginx_webp_delivery', $webp_delivery);
                    $data_for_response['success'] = true;
                }
            } elseif (!TENWEB_SO_HOSTED_ON_NGINX && TENWEB_SO_HTACCESS_WRITABLE) {
                if ($TwoSettings->get_settings('two_enable_htaccess_webp_delivery') != $webp_delivery) {
                    $TwoSettings->update_setting('two_enable_htaccess_webp_delivery', $webp_delivery);
                    $data_for_response['success'] = true;
                }
            }

            if ($data_for_response['success']) {
                $code = apply_filters('two_save_settings_code', 0);

                if ('nginx_webp_delivery' === $code) {
                    $data_for_response['config_changed'] = false;
                } else {
                    $data_for_response['config_changed'] = true;
                }
                $data_for_response['message'] = 'WebP status changed successfully';
            }
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in setting webp status';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @return WP_REST_Response
     */
    public function get_page_id(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [
            'success' => false,
            'message' => 'Invalid url',
        ];

        try {
            $check_redirect = \TenWebOptimizer\OptimizerUtils::check_page_has_no_redirects($request['page_url']);

            if ($check_redirect) {
                $page_id = \TenWebOptimizer\OptimizerUtils::get_post_id($request['page_url']);

                if ($page_id) {
                    $data_for_response['success'] = true;
                    $data_for_response['message'] = 'Page found successfully';
                    $data_for_response['page_id'] = $page_id;
                } else {
                    $data_for_response['message'] = 'Page not found';

                    return new WP_REST_Response($data_for_response, 404);
                }
            } else {
                $data_for_response['message'] = 'Url has redirect';

                return new WP_REST_Response($data_for_response, 400);
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting pageId';
            $data_for_response['error'] = $exception->getMessage() . ' in ' . $exception->getFile() . ' on ' . $exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function set_score(WP_REST_Request $request)
    {
        $score_data = $request->get_param('score_data');
        $two_front_page_speed = get_option('two-front-page-speed', []);

        if (isset($score_data['previous_score'])) {
            $two_front_page_speed['previous_score'] = $score_data['previous_score'];
        } elseif ($score_data['current_score']) {
            $two_front_page_speed['current_score'] = $score_data['current_score'];
        }
        update_option('two-front-page-speed', $two_front_page_speed, false);
    }

    public function check_critical_authorization(WP_REST_Request $request)
    {
        $token = $request->get_param('token');
        $page_id = $request->get_param('page_id');

        return isset($token, $page_id) && get_option('two_critical' . $page_id) === $token;
    }

    public function validate_mode($param, $request, $key)
    {
        return isset($this->modes[$param]);
    }

    public function validate_option_value($param, $request, $key)
    {
        global $TwoSettings;
        $valid_params = [
            '',
            'on',
            'off',
            'true',
            'false',
            '1',
            '0',
            'vanilla',
            'browser',
        ];
        $settings_names = $TwoSettings->settings_names;
        $option_name = sanitize_text_field($request['key']);

        if (isset($settings_names[$option_name])) {
            if (isset($settings_names[$option_name]['type']) && $settings_names[$option_name]['type'] === 'textarea') {
                return true;
            }
            $el_key = in_array($param, $valid_params, false); // phpcs:ignore

            if ($el_key !== false) {
                return true;
            }
        }

        return false;
    }

    public function validate_page_id($param, $request, $key)
    {
        return $param === 'front_page' || $param === 'all' || (int) ($param) > 0 || (false !== strpos($param, 'term_') && (int) (ltrim($param, 'term_')) > 0) || (false !== strpos($param, 'user_') && (int) (ltrim($param, 'user_')) > 0);
    }

    public function validate_cloudflare_status($param, $request, $key)
    {
        if ($param == 'on' || $param == 'off') {
            return true;
        }

        return false;
    }

    public function check_authorization(WP_REST_Request $request)
    {
        if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
            $data_for_response = [
                'code' => 'unauthorized',
                'message' => 'unauthorized',
                'data' => [
                    'status' => 401
                ]
            ];

            return new WP_Error('rest_forbidden', $data_for_response, 401);
        }
        add_filter('http_request_args', [$this, 'add_booster_version'], 10, 2); // phpcs:ignore
        $authorize = \Tenweb_Authorization\Login::get_instance()->authorize($request);

        if (is_array($authorize)) {
            return new WP_Error('rest_forbidden', $authorize, 401);
        }

        return true;
    }

    public function validate_string($param, $request, $key)
    {
        return is_string($param);
    }

    public function validate_boolean($param, $request, $key)
    {
        //axios which sents requests to this uses application/x-www-form-urlencoded, and converts all bools to string
        //example: false gets converted to 'false' and true to 'true'
        //so we convert that to actual bool and pass downstream
        $param = filter_var($param, FILTER_VALIDATE_BOOLEAN);

        return is_bool($param);
    }

    private function validate_accepted_value($option, $val)
    {
        if (isset($option['accepted_value'])) {
            if ($option['accepted_value'] == 'array') {
                return is_array($val);
            } elseif (is_array($option['accepted_value'])) {
                return in_array($val, $option['accepted_value']);
            }
            // ignore string
        }

        return true;
    }

    /*
        * wp 4.4 adds slashes, removes them
        *
        * https://core.trac.wordpress.org/ticket/36419
        **/
    private static function wp_unslash_conditional($data)
    {
        global $wp_version;

        if ($wp_version < 4.5) {
            $data = wp_unslash($data);
        }

        return $data;
    }

    /**
     * Add manager_version to POST data to avoid blocking it in manager plugin
     *
     * @return array
     */
    public function add_booster_version($args, $url)
    {
        if (is_array($args['body'])) {
            $args['body']['manager_version'] = TENWEB_VERSION;
            $args['body']['other_data']['manager_version'] = TENWEB_VERSION;
        }

        return $args;
    }

    public function connect_from_core(WP_REST_Request $request)
    {
        $this->clear_response();
        $data_for_response = [];
        $headers_for_response = [];
        $parameters = self::wp_unslash_conditional($request->get_body_params());

        if (isset($parameters['nonce'])) {
            $saved_nonce = get_site_option(TW_OPTIMIZE_PREFIX . '_saved_nonce');

            if ($parameters['nonce'] === $saved_nonce) {
                \TenWebOptimizer\OptimizerAdmin::get_instance()->connect_to_tenweb($parameters); //no need for response because of die() call inside
            } else {
                $data_for_response = [
                    'code' => 'ok',
                    'data' => 'it_was_not_me' // do not change
                ];
                $headers_for_response = ['tenweb_connect_from_core' => 'it_was_not_me'];
            }
            delete_site_option(TW_OPTIMIZE_PREFIX . '_saved_nonce');
        } else {
            $headers_for_response = ['tenweb_connect_from_core' => 'it_was_not_me'];
        }

        return new WP_REST_Response($data_for_response, 200, $headers_for_response);
    }
}
