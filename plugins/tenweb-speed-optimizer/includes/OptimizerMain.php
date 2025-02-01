<?php

namespace TenWebOptimizer;

use DiDom\Exceptions\InvalidSelectorException;
use JSMin\JSMin;

class OptimizerMain
{
    protected static $instance = null;

    private $is_testing = false;

    private $tenweb_handle;

    private $two_delay_js_execution = false;

    private $two_delay_all_js_execution = false;

    private $two_timeout_js_load = false;

    private $two_delay_js_exclusions = null;

    private $two_exclude_js = '';

    private $two_load_excluded_js_normally = '';

    private $two_exclude_delay_js = [];

    private $two_disabled_delay_all_js_pages = [];

    private $two_exclude_css = '';

    private $two_include_inline_js = '';

    private $two_include_inline_css = '';

    private $two_dequeue_jquery_migrate = '';

    private $current_url = null;

    private $async_all = false;

    private $aggregate_js = false;

    private $aggregate_css = false;

    private $two_do_not_optimize_images = 'off';

    private $two_lazyload = 'off';

    private $two_add_noscript = 'off';

    private $two_bg_lazyload = 'off';

    private $two_enable_use_srcset = 'off';

    private $two_enable_picture_webp_delivery = 'off';

    private $two_video_lazyload = 'off';

    private $two_iframe_lazyload = 'off';

    private $two_delay_iframe_lazyload = 'off';

    private $two_elemrntor_video_iframe = 'off';

    private $two_youtube_vimeo_iframe_lazyload = 'off';

    private $two_gzip = false;

    private $browser_lazy = false;

    private $vanilla_lazy = false;

    /**
     * @var mixed|void
     */
    private $two_delayed_js_execution_list;

    /**
     * @var OptimizerCacheStructure
     */
    private $cacheStructure;

    private $TwoSettings;

    private $use_extended_exception_list_js;

    /**
     * @var string
     */
    private $two_delayed_js_load_libs_first;

    private $critical;

    private $two_webp_delivery_working = false;

    private $ao_imgopt = null;

    private $elementor_youtube_video = false;

    /**
     * @var false|mixed|string|null
     */
    public $two_test_mode;

    /**
     * @var false|mixed|string|null
     */
    public $two_minify_html;

    /**
     * @var bool
     */
    public $minify_js;

    /**
     * @var bool
     */
    public $minify_css;

    private function __construct()
    {
        @ini_set('pcre.backtrack_limit', 5000000); // phpcs:ignore
        @ini_set('pcre.recursion_limit', 5000000); // phpcs:ignore

        add_action('elementor/widget/render_content', [$this, 'detect_elementor_video_widget'], 10, 2);
        add_action('send_headers', [ $this, 'two_headers' ]);
        do_action('wp_meta');
        global $TwoSettings;
        $this->two_webp_delivery_working = $TwoSettings->get_settings('two_webp_delivery_working');
        // Move the initialization to wp action to have wp query already set as we need to identify the search pages.
        // Set the priority to the end to avoid conflicts with third party plugins.
        add_action('wp', [ $this, 'init' ], PHP_INT_MAX - 1);
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        OptimizerUtils::download_critical();

        if (!defined('TWO_CACHE_DIR')) {
            define('TWO_CACHE_DIR', OptimizerCache::get_path());
        }

        global $TwoSettings;
        $this->TwoSettings = $TwoSettings;
        $lazy_load_type = $this->TwoSettings->get_settings('lazy_load_type');
        $this->is_testing = $this->TwoSettings->getTestMode();

        if ($lazy_load_type === 'browser') {
            $this->browser_lazy = true;
        }

        if ($lazy_load_type === 'vanilla') {
            $this->vanilla_lazy = true;
        }
        $this->two_do_not_optimize_images = $this->TwoSettings->get_settings('two_do_not_optimize_images');
        $this->two_enable_use_srcset = $this->TwoSettings->get_settings('two_enable_use_srcset');
        $this->two_enable_picture_webp_delivery = $this->TwoSettings->get_settings('two_enable_picture_webp_delivery');
        $this->two_lazyload = $this->TwoSettings->get_settings('two_lazyload');
        $this->two_add_noscript = $this->TwoSettings->get_settings('two_add_noscript');
        $this->two_bg_lazyload = $this->TwoSettings->get_settings('two_bg_lazyload');

        if ($this->two_bg_lazyload !== 'on') {
            $this->two_bg_lazyload = $this->TwoSettings->get_settings('two_img_in_viewport_lazyload');
        }
        $this->two_video_lazyload = $this->TwoSettings->get_settings('two_video_lazyload');
        $this->two_iframe_lazyload = $this->TwoSettings->get_settings('two_iframe_lazyload');
        $this->two_delay_iframe_lazyload = $this->TwoSettings->get_settings('two_delay_iframe_lazyload');
        $this->two_elemrntor_video_iframe = $this->TwoSettings->get_settings('two_elemrntor_video_iframe');
        $this->two_youtube_vimeo_iframe_lazyload = $this->TwoSettings->get_settings('two_youtube_vimeo_iframe_lazyload');
        $this->two_gzip = $this->TwoSettings->get_settings('two_gzip');
        $this->aggregate_js = empty($this->TwoSettings->get_settings('two_aggregate_js', false)) ? false : true;
        $this->aggregate_css = empty($this->TwoSettings->get_settings('two_aggregate_css', false)) ? false : true;
        $this->two_delay_js_execution = empty($this->TwoSettings->get_settings('two_delay_js_execution', false)) ? false : true;
        $this->two_delay_all_js_execution = empty($this->TwoSettings->get_settings('two_delay_all_js_execution', false)) ? false : true;
        $this->two_timeout_js_load = $this->TwoSettings->get_settings('two_timeout_js_load', false) === 'on';
        $this->two_delay_js_exclusions = $TwoSettings->get_settings('two_delay_js_exclusions', false);
        $this->two_test_mode = $this->TwoSettings->get_settings('two_test_mode');
        $this->two_minify_html = $this->TwoSettings->get_settings('two_minify_html');

        $this->minify_js = empty($this->TwoSettings->get_settings('two_minify_js', false)) ? false : true;
        $this->minify_css = empty($this->TwoSettings->get_settings('two_minify_css', false)) ? false : true;

        $this->use_extended_exception_list_js = empty($this->TwoSettings->get_settings('two_use_extended_exception_list_js', false)) ? false : true;
        $two_generate_ccss_on_load = $TwoSettings->get_settings('two_generate_ccss_on_load');
        $two_generate_ccss_on_load_types = is_array($two_generate_ccss_on_load) ? $two_generate_ccss_on_load : [];

        $this->current_url = OptimizerUtils::get_page_url();

        $dir = OptimizerCache::get_path();

        if (OptimizerUtils::dirsize($dir) > 1000000000) { // 1Gb
            $this->clear_cache(false, false, true, true, 'front_page', false, true, true, true);
        }

        $this->cacheStructure = OptimizerCacheStructure::init();

        global $post;
        $post_mode = '';

        if (isset($post) && isset($post->ID)) {
            $post_mode = get_post_meta($post->ID, 'two_mode', true);
        }
        $two_default_mode = get_option('two_default_mode');

        if (TENWEB_SO_HOSTED_ON_10WEB && !is_array($two_default_mode) && !isset($_GET['mode']) && !wp_doing_ajax()) { // phpcs:ignore
            OptimizerUtils::set_global_mode();
        }

        $should_serve_optimized_page = $this->should_serve_optimized_page($post_mode);

        if ($should_serve_optimized_page === true) {
            /*Check if user free and not critical page ont optimize */
            $request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . sanitize_url($_SERVER['HTTP_HOST']) . sanitize_url($_SERVER['REQUEST_URI']); // phpcs:ignore
            $home_url = rtrim(get_home_url(), '/');
            $page_url = rtrim($request_url, '/');
            $critical_page_id = null;

            if ($home_url === $page_url) {
                $critical_page_id = 'front_page';
            }
            $this->critical = new OptimizerCriticalCss($critical_page_id);
            global $tenweb_subscription_id;
            $tenweb_subscription_id = (int) $tenweb_subscription_id;
            $page_id = $this->critical->page_id;

            if (empty($page_id)) {
                self::add_optimization_status_headers(0, 'Page id not found');

                return;
            }
            $page_mode = OptimizerUrl::getPageModeByID($page_id);
            $id = $page_mode[ 'id' ];
            $type = $page_mode[ 'type' ];
            $page_mode = $page_mode[ 'page_mode' ];
            $post_type = get_post_type($page_id);

            $conditions_for_generating_ccss = false;

            if ((in_array('page', $two_generate_ccss_on_load_types) && $post_type == 'page')
                || (in_array('post', $two_generate_ccss_on_load_types) && $post_type == 'post')) {
                $conditions_for_generating_ccss = true;
            } elseif (in_array('taxonomy', $two_generate_ccss_on_load_types) && str_contains($page_id, 'term_')) {
                $conditions_for_generating_ccss = true;
            }

            if (in_array((int) $tenweb_subscription_id, TENWEB_SO_FREE_SUBSCRIPTION_IDS) && $page_id != 'front_page' && !is_array($page_mode)) {
                if (isset($this->critical->two_critical_pages[$page_id]['status']) && $this->critical->two_critical_pages[$page_id]['status'] != 'success' && $this->critical->two_critical_pages[$page_id]['status'] != 'in_progress') {
                    OptimizerCriticalCss::generate_critical_css_by_id($page_id);
                    self::add_optimization_status_headers(0, 'Critical id not found');

                    return;
                }

                if (!isset($this->critical->critical_css)) {
                    self::add_optimization_status_headers(0, 'Critical id not found');

                    return;
                }
            } elseif (TWO_ALWAYS_CRITICAL && !TENWEB_SO_HOSTED_ON_10WEB &&
                (!isset($this->critical->two_critical_pages) || !isset($this->critical->two_critical_pages[$page_id])
                    || $this->critical->two_critical_pages[$page_id]['status'] === 'not_started')) {
                if ($page_id === 'front_page' || $conditions_for_generating_ccss) {
                    $two_flow_critical_start = get_option('two_flow_critical_start');

                    if ($two_flow_critical_start === '1' && \Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                        OptimizerCriticalCss::generate_critical_css_by_id($page_id);
                    }
                }
            } elseif (TENWEB_SO_HOSTED_ON_10WEB
                        && (
                            (!isset($this->critical->two_critical_pages) || !isset($this->critical->two_critical_pages[$page_id]))
                            || ($this->critical->two_critical_pages[$page_id]['status'] != 'success'
                                && $this->critical->two_critical_pages[$page_id]['status'] != 'in_progress')
                        )
                        && $conditions_for_generating_ccss) {
                OptimizerCriticalCss::generate_critical_css_by_id($page_id);
            }
            $date = time();

            if ($page_id === 'front_page') {
                update_option('two_optimized_date_front_page', $date, false);
            } elseif ('term_' == $type) {
                update_term_meta($id, 'two_optimized_date', $date);
            } elseif ('user_' == $type) {
                update_user_meta($id, 'two_optimized_date', $date);
            } else {
                update_post_meta($page_id, 'two_optimized_date', $date);
            }

            OptimizerUtils::init_defines();
            $this->check_cache_dir(OptimizerCache::get_path());
            $two_delayed_js_execution_list = $this->TwoSettings->get_settings('two_delayed_js_execution_list');
            $two_exclude_js = $this->TwoSettings->get_settings('two_exclude_js');
            $two_load_excluded_js_normally = $this->TwoSettings->get_settings('two_load_excluded_js_normally');
            $two_exclude_delay_js = $this->TwoSettings->get_settings('two_exclude_delay_js');
            $two_disabled_delay_all_js_pages = $this->TwoSettings->get_settings('two_disabled_delay_all_js_pages');
            $two_delayed_js_load_libs_first = $this->TwoSettings->get_settings('two_delayed_js_load_libs_first');
            $two_exclude_css = $this->TwoSettings->get_settings('two_exclude_css');
            $two_include_inline_js = $this->TwoSettings->get_settings('two_include_inline_js');
            $two_include_inline_css = $this->TwoSettings->get_settings('two_include_inline_css');
            $two_dequeue_jquery_migrate = $this->TwoSettings->get_settings('two_dequeue_jquery_migrate');
            $async_all = $this->TwoSettings->get_settings('two_async_all');
            $two_disable_wp_dashicons = $this->TwoSettings->get_settings('two_disable_wp_dashicons');

            if ($two_delayed_js_execution_list != false) {
                $this->two_delayed_js_execution_list = $two_delayed_js_execution_list;
            }

            if ($two_exclude_js != false) {
                $this->two_exclude_js = $two_exclude_js;
            }

            if ($two_load_excluded_js_normally != false) {
                $this->two_load_excluded_js_normally = $two_load_excluded_js_normally;
            }

            if (!empty($two_exclude_delay_js)) {
                $this->two_exclude_delay_js = explode(',', $two_exclude_delay_js);
            }

            if (!empty($two_disabled_delay_all_js_pages)) {
                $this->two_disabled_delay_all_js_pages = $two_disabled_delay_all_js_pages;
                $two_disabled_delay_all_js_pages_arr = array_filter(
                    array_map('trim', explode(',', $this->two_disabled_delay_all_js_pages))
                );

                foreach ($two_disabled_delay_all_js_pages_arr as $optimizerDisabledPage) {
                    if (preg_match('~' . $optimizerDisabledPage . '~', $_SERVER['REQUEST_URI'])) { // phpcs:ignore
                        $this->two_delay_all_js_execution = false;
                    }
                }
            }

            if ($two_exclude_css != false) {
                $this->two_exclude_css = $two_exclude_css;
            }

            if ($async_all === 'on') {
                $this->async_all = true;
            }

            if ($two_include_inline_js != false) {
                $this->two_include_inline_js = 'on';
            }

            if ($two_include_inline_css != false) {
                $this->two_include_inline_css = 'on';
            }

            if ($two_delayed_js_load_libs_first != false) {
                $this->two_delayed_js_load_libs_first = 'on';
            }

            if ($two_dequeue_jquery_migrate != false) {
                $this->two_dequeue_jquery_migrate = 'on';
            }

            $this->check_and_create_dirs();

            // Fix Elementor slideshow background before delayed js are loaded.
            add_action('elementor/frontend/before_render', function ($element) {
                if ('slideshow' == $element->get_settings('background_background')) {
                    if (!empty($element->get_settings('background_slideshow_gallery')[0]['url'])) {
                        $element->add_render_attribute(
                            '_wrapper',
                            [
                                'style' => 'background: url("' . $element->get_settings('background_slideshow_gallery')[ 0 ][ 'url' ] . '") ' . (!empty($element->get_settings('background_slideshow_background_position')) ? $element->get_settings('background_slideshow_background_position') : 'center') . '/' . (!empty($element->get_settings('background_slideshow_background_size')) ? $element->get_settings('background_slideshow_background_size') : 'cover'),
                            ]
                        );
                    }
                }
            });

            // We need to remove default WP lazyloading as it conflicts with vanilla lazy on iOS 15.
            if (!$this->browser_lazy) {
                add_filter('wp_lazy_loading_enabled', '__return_false');

                if ($TwoSettings->get_settings('two_remove_elementor_lazyload') == 'on') {
                    add_action('elementor/widget/render_content', function ($content, $widget) {
                        if ($widget->get_name() === 'image') {
                            $content = str_replace(' loading="lazy"', '', $content);
                        }

                        return $content;
                    }, 10, 2);
                }
            }

            if (!isset($_GET['two_optimize_only_images'])) { // phpcs:ignore
                add_action('wp_head', [ $this, 'add_delayed_javascript_execution_script_header' ], -1000000000, 0);
                add_action('wp_default_scripts', [ $this, 'dequeue_jquery_migrate' ]);

                if ($two_disable_wp_dashicons == 'on') {
                    add_action('wp_print_styles', [ $this, 'two_deregister_styles' ], 100);
                }

                if ($this->two_youtube_vimeo_iframe_lazyload == 'on') {
                    add_action('wp_enqueue_scripts', [ $this, 'two_enqueue_styles' ]);
                }

                if ($this->two_lazyload == 'on' ||
                    $this->two_iframe_lazyload == 'on' ||
                    $this->two_bg_lazyload == 'on' ||
                    $this->two_video_lazyload == 'on' ||
                    $this->two_youtube_vimeo_iframe_lazyload == 'on' ||
                    $this->two_do_not_optimize_images == 'on') {
                    $this->ao_imgopt = new OptimizerImages([
                        'two_iframe_lazyload' => $this->two_iframe_lazyload,
                        'two_youtube_vimeo_iframe_lazyload' => $this->two_youtube_vimeo_iframe_lazyload,
                    ]);
                    $this->ao_imgopt->run();
                }

                if (!empty($this->is_testing)) {
                    echo wp_kses_post($this->end_buffering($this->is_testing));
                    die;
                }

                if (isset($post->ID)) {
                    $this->set_modes($post->ID);
                }
                add_action('wp_meta', [ $this, 'two_meta' ]);
            }
            self::add_optimization_status_headers();
            ob_start([ $this, 'end_buffering' ], 0, PHP_OUTPUT_HANDLER_REMOVABLE);
        } elseif (is_string($should_serve_optimized_page)) {
            OptimizerLogger::add_not_optimized_page_log($should_serve_optimized_page);
            self::add_optimization_status_headers(0, $should_serve_optimized_page);
        }
        global $disableTwoCacheStructureCache;

        if ($disableTwoCacheStructureCache === true) {
            OptimizerLogger::add_not_optimized_page_log('Page is served optimized but bypassed cache structure caching');
        }

        if (isset($_GET[ 'two_detect_post_id' ])) { // phpcs:ignore
            $current_post = OptimizerUtils::get_current_post_info();
            header('X-TWO-POST-ID: ' . $current_post);
        }
    }

    public static function add_optimization_status_headers($status = 1, $code = '')
    {
        header('X-TWO-PAGE-IS-OPTIMIZED: ' . $status);

        if ($code) {
            header('X-TWO-OPTIMIZE-REASON: ' . $code);
        }
    }

    public static function two_headers()
    {
        global $TwoSettings;
        header('X-TWO-OPTIMIZE: 1'); // do not edit, this is used in service to determine whether plugin is enabled.
        header('X-TWO-VERSION: ' . $TwoSettings->get_settings('tenweb_so_version', 'Clear the cache to see the version'));
        header('X-TWO-CACHE-DATE: ' . $TwoSettings->get_settings('two_clear_cache_date'));

        if ($TwoSettings->get_settings('two_webp_delivery_working') == 1) {
            header('X-TWO-WEBP: 1');
        }

        if ($TwoSettings->get_settings('two_test_mode') == 'on') {
            header('X-TWO-TEST-MODE: 1');
        }

        if (isset($_GET['two_critical_status'])) { // phpcs:ignore
            $two_critical_status = $TwoSettings->get_settings('two_critical_status');
            $two_critical_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            if ($two_critical_status == 'true' && isset($two_critical_pages) && is_array($two_critical_pages)) {
                if (isset($two_critical_pages[ 'front_page' ]) && get_option('tenweb_import_in_progress') != 1) {
                    header('X-TWO-CRITICAL: ' . $two_critical_pages[ 'front_page' ][ 'status' ]);
                } else {
                    header('X-TWO-CRITICAL: not_started');
                }
            }
        }
    }

    public function two_meta()
    {
        global $TwoSettings;

        if ($this->two_webp_delivery_working == 1) {
            echo '<meta name="X-TWO-WEBP" content="1">';
        }
        echo '<meta name="X-TWO-OPTIMIZE" content="1">';
        echo '<meta name="X-TWO-CACHE-DATE" content="' . esc_attr($TwoSettings->get_settings('two_clear_cache_date')) . '">';
    }

    public function two_deregister_styles()
    {
        wp_deregister_style('dashicons');
    }

    public function two_enqueue_styles()
    {
        wp_enqueue_style('two_yt_vi_css', TENWEB_SO_URL . '/includes/external/css/two_yt_vi_lazyload.min.css', '', TENWEB_SO_VERSION);
    }

    public function add_delayed_javascript_execution_script_header()
    {
        echo trim(JSMin::minify('<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">' . file_get_contents(__DIR__ . '/external/js/loader.js') . '</script>')); // phpcs:ignore

        if ($this->two_delay_all_js_execution) {
            global $TwoSettings;
            $two_delay_custom_js_new = $TwoSettings->get_settings('two_delay_custom_js_new');

            if (!empty($two_delay_custom_js_new) && !ctype_space($two_delay_custom_js_new)) {
                echo '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">
                        const two_delay_custom_js_new = new Event("two_delay_custom_js_new");
                        document.addEventListener("two_delay_custom_js_new", event => {
                          ' . esc_js(stripslashes($two_delay_custom_js_new)) . '
                        })
                    </script>';
            }

            echo trim(JSMin::minify('<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">' . // phpcs:ignore
                'window.two_delayed_loading_attribute = "' . OptimizerScripts::TWO_DELAYED_JS_ATTRIBUTE . '";
            window.two_delayed_js_load_libs_first = "' . esc_html($this->two_delayed_js_load_libs_first) . '";
            window.two_delayed_loading_events = ["mousemove", "click", "keydown", "wheel", "touchmove", "touchend"];
            window.two_event_listeners = [];

                </script>'));

            if ($this->two_timeout_js_load) {
                echo '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">
                        setTimeout(function() {
                          two_load_delayed_javascript();
                        },1500);
                     </script>';
            }
        } elseif ($this->two_delay_js_execution) {
            echo '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">' .
                'window.two_delayed_loading_attribute = "' . esc_attr(OptimizerScripts::TWO_DELAYED_JS_ATTRIBUTE) . '";
            window.two_delayed_js_load_libs_first = "' . esc_html($this->two_delayed_js_load_libs_first) . '";
            window.two_delayed_loading_events = ["mousemove", "click", "keydown", "wheel", "touchmove", "touchend"];
            window.two_event_listeners = [];
            var two_load_delayed_javascript = function(event) {
                var delayedScripts = [].map.call(document.querySelectorAll("script[" + window.two_delayed_loading_attribute + "]"), function(elm){
                                return elm;
                              })

                if (window.two_delayed_js_load_libs_first === "on" ) {
                    delayedScripts = delayedScripts.sort(function (a, b) {
                                           isLiba = a.outerHTML.indexOf("data:");
                                           isLibb = b.outerHTML.indexOf("data:");

                                           return isLiba - isLibb;
                                        });
                }
                delayedScripts.forEach(function(elem) {
                    var src = elem.getAttribute(window.two_delayed_loading_attribute);
                    elem.setAttribute("src", src);
                    elem.removeAttribute(window.two_delayed_loading_attribute);
                    window.two_delayed_loading_events.forEach(function(event) {
                        document.removeEventListener(event, two_load_delayed_javascript, false)
                    });
                })
            };' .
                '</script>';
        }

        if ($this->critical->uncritical_load_type === 'on_interaction' && $this->critical->critical_enabled) {
            echo trim(JSMin::minify('<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">' . // phpcs:ignore
                'window.two_delayed_loading_attribute_css = "' . esc_attr(OptimizerStyles::TWO_DELAYED_CSS_ATTRIBUTE) . '";
            window.two_delayed_loading_events_css = ["keydown", "mouseover", "touchmove", "touchstart"];
            window.two_event_listeners_css = [];
            var two_load_delayed_css = function(event) {
                var delayedStyle = [].map.call(document.querySelectorAll("link[" + window.two_delayed_loading_attribute_css + "]"), function(elm){
                                return elm;
                              })

                delayedStyle.forEach(function(elem) {
                    var src = elem.getAttribute(window.two_delayed_loading_attribute_css);
                    elem.setAttribute("href", src);
                    elem.removeAttribute(window.two_delayed_loading_attribute_css);
                    window.two_delayed_loading_events_css.forEach(function(event) {
                        document.removeEventListener(event, two_load_delayed_css, false)
                    });
                })
            };' .
                '</script>'));
        }

        if ($this->two_delay_iframe_lazyload == 'on') {
            echo '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript">' .
                'window.two_delayed_iframe_loading_attribute = "' . esc_attr('data-src') . '";
            window.two_delayed_loading_iframe_events = ["keydown", "mouseover", "touchmove", "touchstart"];
            window.two_iframe_event_listeners = [];
            var two_load_delayed_iframe = function(event) {
                var delayedIframes = [].map.call(document.querySelectorAll("iframe.lazy_delay"), function(elm){
                                return elm;
                              })

                delayedIframes.forEach(function(elem) {
                    var src = elem.getAttribute(window.two_delayed_iframe_loading_attribute);
                    elem.setAttribute("src", src);
                    elem.removeAttribute(window.two_delayed_iframe_loading_attribute);
                    window.two_delayed_loading_iframe_events.forEach(function(event) {
                        document.removeEventListener(event, two_load_delayed_iframe, false)
                    });
                })
            };' .
                '</script>';
        }
        echo trim(JSMin::minify('<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) .  // phpcs:ignore
            ' type="text/javascript">
                    // Register delayed scripts load on document load event
                    document.onreadystatechange = function () {
                        if (document.readyState === "interactive") {
                            if(window.two_delayed_loading_attribute !== undefined){
                                 window.two_delayed_loading_events.forEach(function(ev) { window.two_event_listeners[ev] = document.addEventListener(ev, two_load_delayed_javascript, {passive: false}) });
                            }
                            if(window.two_delayed_loading_attribute_css !== undefined){
                                 window.two_delayed_loading_events_css.forEach(function(ev) { window.two_event_listeners_css[ev] = document.addEventListener(ev, two_load_delayed_css, {passive: false}) });
                            }
                            if(window.two_load_delayed_iframe !== undefined){
                                 window.two_delayed_loading_iframe_events.forEach(function(ev) { window.two_iframe_event_listeners[ev] = document.addEventListener(ev, two_load_delayed_iframe, {passive: false}) });
                            }
                        }
                    }
              </script>'));
    }

    public function add_two_js_footer($content)
    {
        $replaceTag = [ '</body>', 'before' ];

        if ($this->TwoSettings->get_settings('two_add_overflow_to_page') != 'off') {
            // Not using tag_escape as it removes comma from 'body, html'
            $css = '<style id="two_fix_scroll_issues_style" >' . wp_strip_all_tags($this->TwoSettings->get_settings('two_add_overflow_to_page')) . ' {overflow-y: visible !important;}</style>';
            $content = OptimizerUtils::inject_in_html($content, $css, $replaceTag);
        }

        if (is_plugin_active('elementor/elementor.php') && $this->two_delay_all_js_execution && !ExcludeJsFromDelay::plugin_is_excluded('elementor/elementor.php')) {
            $init_elementor_animations_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' >
                            ( function () {
                              window.addEventListener( "two_css_loaded", function () {
                                window.two_observer = new IntersectionObserver( items => {
                                  items.forEach( item => {
                                    if (item.isIntersecting) {
                                        if (item.target.dataset.settings) {
                                            let settings = JSON.parse(item.target.dataset.settings);
                                            let animation = settings.animation ? settings.animation : settings._animation ? settings._animation : "zoomIn";
                                            let delay = settings.animation_delay ? settings.animation_delay : settings._animation_delay ? settings._animation_delay : 0;
                                            setTimeout(() => {
                                                item.target.className = item.target.className.replace("elementor-invisible", "") + " animated " + animation;
                                            }, delay);
                                            delete settings.animation;
                                            delete settings._animation;
                                            item.target.dataset.settings = JSON.stringify(settings);
                                        } else {
                                            item.target.className = item.target.className.replace("elementor-invisible", "") + " animated zoomIn";
                                        }
                                    }
                                  } )
                                }, {} );
                                document.querySelectorAll( ".elementor-invisible" ).forEach( e => {
                                  window.two_observer.observe( e )
                                } )
                              } )
                            } )();
                            </script>';
            $content = OptimizerUtils::inject_in_html($content, $init_elementor_animations_js, $replaceTag);
        }

        if (TWO_LAZYLOAD) {
            if ($this->two_bg_lazyload == 'on' || $this->two_lazyload == 'on' || $this->two_iframe_lazyload == 'on' || $this->two_video_lazyload == 'on' || $this->two_youtube_vimeo_iframe_lazyload == 'on') {
                if ($this->two_bg_lazyload == 'on') {
                    $bg_placeholder = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' >window["two_svg_placeholder"] = "' . OptimizerUtils::BG_SVG_PLACEHOLDER . '"</script>';
                    $content = OptimizerUtils::inject_in_html($content, $bg_placeholder, $replaceTag);
                }

                $noptimize_flag = ' data-noptimize="1"';

                if ($this->two_iframe_lazyload == 'on' && $this->two_elemrntor_video_iframe == 'on') {
                    $two_elementor_video_to_iframe_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/two_elementor_video_to_iframe.js', __FILE__) . '"></script>';
                    $content = OptimizerUtils::inject_in_html($content, $two_elementor_video_to_iframe_js, $replaceTag);
                }

                if ($this->vanilla_lazy) {
                    $lazyload_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . '  type="text/javascript"  src="' . plugins_url('external/js/vanilla-lazyload/lazyload.min.js', __FILE__) . '"></script>';
                    $content = OptimizerUtils::inject_in_html($content, $lazyload_min_js, $replaceTag);

                    if ($this->two_bg_lazyload == 'on') {
                        $two_bg_vanilla_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' type="text/javascript" src="' . plugins_url('external/js/vanilla-lazyload/two_bg_vanilla.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $two_bg_vanilla_js, $replaceTag);
                    }

                    if ($this->two_youtube_vimeo_iframe_lazyload == 'on') {
                        $two_yt_vi_lazyload_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . '  type="text/javascript" data-pagespeed-no-defer ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' src="' . plugins_url('external/js/two_yt_vi_lazyload.min.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $two_yt_vi_lazyload_min_js, $replaceTag);
                    }
                    $init_vanilla_lazy_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' >
                                if(typeof two_lazyLoadInstance === "undefined"){
                                    window.two_lazyLoadInstance = new LazyLoad({
                                          "callback_applied": function(element, instance){
                                                                let settings = instance._settings;
                                                                var bgDataValue = element.getAttribute("data-" + settings.data_bg_multi);
                                                                if (!bgDataValue) {
                                                                    return;
                                                                }
                                                                if(window.getComputedStyle(element).getPropertyValue("background-image") !== bgDataValue) {
                                                                    let style = element.getAttribute("style");
                                                                    style += "background-image: " + bgDataValue + " !important;";
                                                                    element.setAttribute("style", style);
                                                                }
                                                            }
                                   });
                                }else{
                                     two_lazyLoadInstance.update();
                                }
                                window.addEventListener("scroll", function() {
                                   if(two_lazyLoadInstance.toLoadCount>0){
                                      two_lazyLoadInstance.update();
                                   }
                                });
                                /*
                                 * Updates lazy-load instance from every ajax request
                                 * When we use Ajax requests and get pictures back, we need to update lazy-load instance
                                 * */
                                if (window.jQuery) {
                                    jQuery.ajaxSetup({
                                            complete: function() {
                                                two_lazyLoadInstance.update();
                                            }
                                        });
                                }
                            </script>';
                    $content = OptimizerUtils::inject_in_html($content, $init_vanilla_lazy_js, $replaceTag);

                    return $content;
                }

                if (($this->browser_lazy && $this->two_bg_lazyload == 'on') || $this->browser_lazy == false) {
                    $lazySizesConfig_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' ' . esc_attr($noptimize_flag) . '>window.lazySizesConfig=window.lazySizesConfig||{};window.lazySizesConfig.loadMode=1;</script>';
                    $content = OptimizerUtils::inject_in_html($content, $lazySizesConfig_js, $replaceTag);
                    $jquery_lazy_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/jquery.lazy.min.js', __FILE__) . '"></script>';
                    $content = OptimizerUtils::inject_in_html($content, $jquery_lazy_min_js, $replaceTag);

                    if ($this->two_bg_lazyload == 'on') {
                        $two_lazyload_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/two_lazyload.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $two_lazyload_js, $replaceTag);
                    }
                }

                if ($this->browser_lazy == false) {
                    if ($this->two_iframe_lazyload == 'on') {
                        $jquery_lazy_iframe_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/jquery.lazy.iframe.min.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $jquery_lazy_iframe_min_js, $replaceTag);
                    }

                    if ($this->two_video_lazyload == 'on') {
                        $jquery_lazy_av_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/jquery.lazy.av.min.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $jquery_lazy_av_min_js, $replaceTag);
                    }

                    if ($this->two_youtube_vimeo_iframe_lazyload == 'on') {
                        $two_yt_vi_lazyload_min_js = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/two_yt_vi_lazyload.min.js', __FILE__) . '"></script>';
                        $content = OptimizerUtils::inject_in_html($content, $two_yt_vi_lazyload_min_js, $replaceTag);
                    }
                }

                if (($this->browser_lazy && $this->two_bg_lazyload == 'on') || $this->browser_lazy == false) {
                    $lazy_script = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . " >
                        window.onload = function() {
                             jQuery('.lazyload').Lazy({
                              visibleOnly:true,
                              afterLoad: function(element) {
                                 element.css('display', '');
                              }
                             });
                        }
                     </script>";
                    $content = OptimizerUtils::inject_in_html($content, $lazy_script, $replaceTag);
                }
            }
        }

        return $content;
    }

    public function dequeue_jquery_migrate($scripts)
    {
        if ($this->two_dequeue_jquery_migrate == 'on' && !is_admin() && !empty($scripts->registered['jquery']) && !empty($scripts->registered['jquery']->deps)) {
            $scripts->registered['jquery']->deps = array_diff(
                $scripts->registered['jquery']->deps,
                ['jquery-migrate']
            );
        }
    }

    /**
     * Returns optimized HTML content
     *
     * @return mixed|string|void
     *
     * @throws InvalidSelectorException
     */
    private function end_buffering($content)
    {
        $content = apply_filters('two_content_before_optimization', $content, 10, 1);

        $is_content_optimizable = OptimizerUrl::isUrlOptimizableByContent($content);

        if (true !== $is_content_optimizable) {
            self::add_optimization_status_headers(0, $is_content_optimizable);

            return $content;
        }

        /**
         * optimize options
         */
        $classoptions = [
            'OptimizerScripts' => [
                'aggregate' => $this->aggregate_js,
                'forcehead' => '',
                'trycatch' => '',
                'delay_js_execution' => $this->two_delay_js_execution,
                'delay_all_js_execution' => $this->two_delay_all_js_execution,
                'use_extended_exception_list_js' => $this->use_extended_exception_list_js,
                'delayed_js_execution_list' => $this->two_delayed_js_execution_list,
                'js_exclude' => $this->two_exclude_js,
                'exclude_delay_js' => $this->two_exclude_delay_js,
                'disabled_delay_all_js_pages' => $this->two_disabled_delay_all_js_pages,
                'load_excluded_js_normally' => $this->two_load_excluded_js_normally,
                'cdn_url' => '',
                'include_inline' => $this->two_include_inline_js,
                'minify_excluded' => $this->minify_js,
                'two_delay_js_exclusions' => $this->two_delay_js_exclusions,
            ],
            'OptimizerStyles' => [
                'aggregate' => $this->aggregate_css,
                'defer' => '',
                'defer_inline' => '',
                'inline' => '',
                'css_exclude' => $this->two_exclude_css,
                'include_inline' => $this->two_include_inline_css,
                'nogooglefont' => false,
                'minify_css' => $this->minify_css,
                'async_all' => $this->async_all,
                'disable_async' => false
            ],
        ];

        if (isset($_GET['two_optimize_only_images']) && $_GET['two_optimize_only_images'] === '1') { // phpcs:ignore
            if (!empty($content) && (function_exists('wp_filter_content_tags') || function_exists('wp_make_content_images_responsive'))) {
                $content = OptimizerImages::add_attachment_id_to_img($content);
            }
            $content = OptimizerUtils::injectCriticalBg($content, $this->critical, $this->cacheStructure);

            return $content;
        }

        if ($this->cacheStructure->check($content)) {
            $content = $this->cacheStructure->retrieve($content);
        } elseif ($this->cacheStructure->getCacheStatus()) {
            $this->cacheStructure->setCacheHeaderString('MISS');
        }

        // Moved HTML minification before js injection as it breaks scripts containing =>
        // Can be moved to the end if library fixes the issue
        if ($this->two_minify_html) {
            $content = \Minifier\TinyMinify::html($content);
        }

        if (!$this->cacheStructure->isFromCache()) {
            /*
             * optimize css
             * */
            $twoptimizeStyles = new OptimizerStyles($content, $this->cacheStructure, $this->critical);

            if (isset($this->critical) && isset($this->critical->critical_css) && $this->critical->critical_enabled && $this->critical->status == 'success') {
                if ($this->critical->uncritical_load_type !== 'not_load') {
                    if ($this->critical->uncritical_load_type === 'async') {
                        $classoptions['OptimizerStyles']['async_all'] = true;
                    } elseif ($this->critical->uncritical_load_type === 'on_interaction') {
                        $classoptions['OptimizerStyles']['include_inline'] = true;
                        $classoptions['OptimizerStyles']['disable_async'] = true;
                    }
                    $twoptimizeStyles->read($classoptions['OptimizerStyles']);
                    $twoptimizeStyles->optimize();
                    $twoptimizeStyles->cache();
                }
            } else {
                $twoptimizeStyles->read($classoptions['OptimizerStyles']);
                $twoptimizeStyles->optimize();
                $twoptimizeStyles->cache();
            }
            $content = $twoptimizeStyles->getcontent();

            /*
             * optimize js
             * */
            $twoptimizeScripts = new OptimizerScripts($content, $this->cacheStructure);
            $twoptimizeScripts->read($classoptions['OptimizerScripts']);
            $twoptimizeScripts->optimize();
            $twoptimizeScripts->cache();
            $content = $twoptimizeScripts->getcontent();

            if (!empty($twoptimizeStyles->two_async_css_arr) || !empty($twoptimizeScripts->two_js_list) || !empty($this->critical_fonts_arr) || !empty($twoptimizeScripts->two_js_list_excluded)) {
                $two_worker_data_to_inject_css = $this->get_two_worker_data_script_tag(
                    [
                        'css' => $twoptimizeStyles->two_async_css_arr,
                    ],
                    '_css'
                );
                $two_worker_data_to_inject_js = $this->get_two_worker_data_script_tag(
                    [
                        'js' => $twoptimizeScripts->two_js_list
                    ],
                    '_js'
                );
                $two_worker_data_to_inject_excluded_js = $this->get_two_worker_data_script_tag(
                    [
                        'js' => $twoptimizeScripts->two_js_list_excluded
                    ],
                    '_excluded_js'
                );

                $two_worker_data_to_inject_font = $this->get_two_worker_data_script_tag(
                    [
                        'font' => $twoptimizeStyles->critical_fonts_arr,
                    ],
                    '_font'
                );
                $two_worker_data_to_inject_critical_status = $this->get_two_worker_data_script_tag(
                    [
                        'critical_data' => $twoptimizeStyles->two_critical_connection_data,
                    ],
                    '_critical_data'
                );
                $two_worker_script = OptimizerUtils::get_worker_script();

                if (!empty($twoptimizeStyles->two_async_css_arr)) {
                    foreach ($twoptimizeStyles->two_async_css_arr as $async_css) {
                        if (!empty($async_css) && isset($async_css['media'], $async_css['url'])) {
                            $two_worker_data_to_inject_css .= '<noscript><link rel="stylesheet" crossorigin="anonymous" class="two_uncritical_css_noscript" media="' . $async_css['media'] . '" href="' . stripslashes($async_css['url']) . '"></noscript>';
                        }
                    }
                }

                $content = OptimizerUtils::inject_in_html($content, $two_worker_data_to_inject_css, ['<head', 'after_tag']);
                $content = OptimizerUtils::inject_in_html($content, $two_worker_data_to_inject_js, ['<head', 'after_tag']);
                $content = OptimizerUtils::inject_in_html($content, $two_worker_data_to_inject_excluded_js, ['<head', 'after_tag']);
                $content = OptimizerUtils::inject_in_html($content, $two_worker_data_to_inject_font, ['<head', 'after_tag']);
                $content = OptimizerUtils::inject_in_html($content, $two_worker_data_to_inject_critical_status, ['<head', 'after_tag']);
                $content = OptimizerUtils::inject_in_html($content, $two_worker_script, ['<body', 'after_tag']);

                $this->cacheStructure->addToTagsToAdd($two_worker_data_to_inject_css, ['<head', 'after_tag']);
                $this->cacheStructure->addToTagsToAdd($two_worker_data_to_inject_js, ['<head', 'after_tag']);
                $this->cacheStructure->addToTagsToAdd($two_worker_data_to_inject_excluded_js, ['<head', 'after_tag']);
                $this->cacheStructure->addToTagsToAdd($two_worker_data_to_inject_font, ['<head', 'after_tag']);
                $this->cacheStructure->addToTagsToAdd($two_worker_data_to_inject_critical_status, ['<head', 'after_tag']);
                $this->cacheStructure->addToTagsToAdd($two_worker_script, ['<body', 'after_tag']);
            }

            //sets cache
            $this->cacheStructure->set();
        }

        if ($this->cacheStructure->isFromCache()) {
            $webFontList = $this->cacheStructure->getWebFontList();
        } else {
            $this->cacheStructure->addToWebFontList($twoptimizeStyles->webFont_list);
            $webFontList = $twoptimizeStyles->webFont_list;
        }

        /*
         * optimize fonts
         */
        if (!empty($webFontList) && is_array($webFontList)) {
            $fontOptimize_options = [
                'webFont_list' => $webFontList
            ];
            $fontOptimize = new OptimizerFonts($content);
            $fontOptimize->read($fontOptimize_options);
            $fontOptimize->optimize();
            $content = $fontOptimize->getcontent();

            $this->cacheStructure->addCacheHeaders();
        }

        // This code is moved after the script optimization
        // Otherwise img tags inside ElementorFrontend config are breaking the js
        // They may be in ElementorFrontendConfig.post.excerpt and WP is trying to add decoding="async" to them without adding slashes
        // Not a perfect solution as the issue wil arise if js aggregation is off.
        if ($this->two_enable_use_srcset === 'on') {
            // Skip parsing the html if WP version does not support responsive images.
            if (!empty($content) && (function_exists('wp_filter_content_tags') || function_exists('wp_make_content_images_responsive'))) {
                $content = OptimizerImages::add_attachment_id_to_img($content);
            }
        }

        // Do not replace images with picture if delivery by hosting is enabled.
        if ($this->two_enable_picture_webp_delivery === 'on' && !TENWEB_SO_HOSTED_ON_10WEB && !$this->two_webp_delivery_working) {
            $content = OptimizerImages::replace_img_with_picture($content);
        }

        if ($this->two_bg_lazyload === 'on') {
            $content = OptimizerUtils::replace_bg($content);
        }

        if ($this->two_lazyload == 'on') {
            $content = apply_filters('twoptimize_html_after_minify', $content);
        }

        if ($this->two_video_lazyload == 'on') {
            $content = apply_filters('twoptimize_html_after_minify_video', $content);
        }

        if ($this->two_iframe_lazyload == 'on' || $this->two_youtube_vimeo_iframe_lazyload == 'on') {
            $content = apply_filters('twoptimize_html_after_minify_iframe', $content);
        }
        $content = apply_filters('twoptimize_html_images', $content);

        $this->reinit_lazy_load_options();
        $content = $this->add_two_js_footer($content);

        $content = apply_filters('two_content_after_optimization', $content, 10, 1);

        return $content;
    }

    private function get_two_worker_data_script_tag($data, $suffix = '')
    {
        return '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' ' .
            'type="text/javascript" >var two_worker_data' . $suffix . ' = ' . json_encode($data) . '</script>'; // phpcs:ignore
    }

    private function check_cache_dir($dir)
    {
        // Try creating the dir if it doesn't exist.
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) { // phpcs:ignore
                return false;
            }

            if (!file_exists($dir)) {
                return false;
            }
        }

        // If we still cannot write, bail.
        if (!is_writable($dir)) { // phpcs:ignore
            return false;
        }
        // Create an index.html in there to avoid prying eyes!
        $idx_file = rtrim($dir, '/\\') . '/index.html';

        if (!is_file($idx_file)) {
            @file_put_contents($idx_file, '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>'); // phpcs:ignore
        }

        return true;
    }

    private function check_and_create_dirs()
    {
        if (!defined('TENWEB_SO_CACHE_CHILD_DIR')) {
            // We didn't set a cache.
            return false;
        }

        foreach (['', 'js', 'css'] as $dir) {
            if (!$this->check_cache_dir(OptimizerCache::get_path() . $dir)) {
                return false;
            }
        }

        return true;
    }

    private function clear_cache()
    {
        $dir = OptimizerCache::get_path();
        $delete_cache_db = OptimizerUtils::delete_all_cache_db();
        $delete_cache_file = OptimizerUtils::delete_all_cache_file($dir, [$dir, $dir . '/css', $dir . '/js']);
        OptimizerUtils::flushCloudflareCache();
        do_action('tenweb_purge_all_caches');

        return $delete_cache_file && $delete_cache_db;
    }

    private function set_modes($post_id)
    {
        $post_mode = get_post_meta($post_id, 'two_mode', true);

        if ((isset($_GET['two_mode']) && !empty($_GET['two_mode'])) || (isset($_GET['two_level']) && !empty($_GET['two_level']))) { // phpcs:ignore
            if (isset($_GET['two_mode'])) { // phpcs:ignore
                $mode_name = sanitize_text_field($_GET['two_mode']); // phpcs:ignore
                $mode = OptimizerUtils::get_modes($mode_name);
            } elseif (isset($_GET['two_level'])) { // phpcs:ignore
                $level = (int) $_GET['two_level']; // phpcs:ignore
                $mode = OptimizerUtils::get_modes(null, false, $level);
            }
        } else {
            $mode = $post_mode;
        }

        if (is_array($mode) && isset($mode['mode'])) {
            foreach ($mode as $key => $val) {
                $val = ($val === '1' || $val === true);

                if ($key == 'critical_enabled') {
                    $this->critical->critical_enabled = $val;
                } elseif (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    private function reinit_lazy_load_options()
    {
        if (!$this->ao_imgopt) {
            return;
        }

        $smart_ll_data = $this->ao_imgopt->get_smart_lazy_load_data();

        if ($this->two_youtube_vimeo_iframe_lazyload === 'on' && !isset($smart_ll_data['two_youtube_vimeo_iframe_lazyload'])) {
            $this->two_youtube_vimeo_iframe_lazyload = 'off';
        }

        if ($this->two_iframe_lazyload === 'on' && !isset($smart_ll_data['iframe'])) {
            $this->two_iframe_lazyload = 'off';
        }

        if ($this->two_bg_lazyload === 'on' && OptimizerUtils::has_changed_bg_image() === false) {
            $this->two_bg_lazyload = 'off';
        }

        if ($this->two_elemrntor_video_iframe === 'on' && $this->elementor_youtube_video === false) {
            $this->two_elemrntor_video_iframe = 'off';
        }
    }

    public function detect_elementor_video_widget($widget_content, $widget)
    {
        if ($widget->get_name() === 'video') {
            $active_settings = $widget->get_active_settings();

            if (isset($active_settings['video_type']) && $active_settings['video_type'] === 'youtube' && !empty($active_settings['youtube_url'])) {
                $this->elementor_youtube_video = true;
            }
        }

        return $widget_content;
    }

    private function should_serve_optimized_page($post_mode)
    {
        /*
         * Function decides whether optimized page should be served or no. It returns boolean or sting. If returns sting
         * it contains the reason why page should not be optimized
         * @returns bool | string
         * */

        if (is_admin() || is_user_logged_in()) {
            return false;
        }

        if (is_search()) {
            return 'Search page';
        }

        if (isset($_GET['two_critical_status']) || isset($_GET['two_action']) || isset($_GET['two_nooptimize']) || isset($_GET['elementor-preview'])) { // phpcs:ignore
            return false;
        }

        // don't optimize rest requests
        if (\TenWebOptimizer\OptimizerUtils::is_rest()) {
            return 'Rest request';
        }

        // only run on GET requests
        if (!\TenWebOptimizer\OptimizerCache::isGetRequest()) {
            $method = (isset($_SERVER['REQUEST_METHOD'])) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : 'unknown';

            return 'Request mode is: ' . $method;
        }

        if (isset($_GET['two_mode']) && $_GET['two_mode'] === 'no_optimize') { // phpcs:ignore
            return 'two_mode GET parameter is no_optimize';
        }

        if ($post_mode == 'no_optimize') {
            return 'post mode is ' . $post_mode;
        }

        if ($this->two_test_mode === 'on' && !isset($_GET['twbooster'])) { // phpcs:ignore
            return 'test mode is enabled';
        }

        return \TenWebOptimizer\OptimizerUrl::urlIsOptimizableWithReason();
    }
}
