<?php

/**
 * Plugin Name: 10Web Booster
 * Plugin URI: https://10web.io/page-speed-booster/
 * Description: Optimize your website speed and performance with 10Web Booster by compressing CSS and JavaScript.
 * Version: 2.30.9
 * Author: 10Web - Website speed optimization team
 * Author URI: https://10web.io/
 * Text Domain: tenweb-speed-optimizer
 */

use TenWebOptimizer\OptimizerOnInit;
use TenWebOptimizer\OptimizerScripts;
use TenWebOptimizer\OptimizerUtils;
use TenWebOptimizer\OptimizerWhiteLabel;

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('TWO_ALWAYS_CRITICAL')) {
    define('TWO_ALWAYS_CRITICAL', true);
}

if (!defined('TWO_PLUGIN_FILE')) {
    define('TWO_PLUGIN_FILE', __FILE__);
}

if (isset($_GET['two_check_redirect']) && $_GET['two_check_redirect'] === '1') { // phpcs:ignore
    return;
}

global $two_incompatible_errors;
$two_incompatible_errors = [];
require_once __DIR__ . '/config.php';

if (PHP_MAJOR_VERSION < 7 || (PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION <= 3)) {
    if (!defined('TWO_INCOMPATIBLE_ERROR')) {
        define('TWO_INCOMPATIBLE_ERROR', true);
    }
    $two_incompatible_errors[] = [ 'title' => __('PHP compatibility error:', 'tenweb-speed-optimizer'),
        'message' => __('PHP 7.4 or a newer version is required for 10Web Booster. Please update your PHP version to proceed.', 'tenweb-speed-optimizer') ];
}

if (!in_array('dom', get_loaded_extensions())) {
    if (!defined('TWO_INCOMPATIBLE_ERROR')) {
        define('TWO_INCOMPATIBLE_ERROR', true);
    }
    $two_incompatible_errors[] = [ 'title' => __('Class \'DOMDocument\' is not found in PHP:', 'tenweb-speed-optimizer'),
        'message' => __('PHP \'DOMDocument\' extension is required for 10Web Booster. Please install PHP \'DOMDocument\' extension to proceed.', 'tenweb-speed-optimizer') ];
}

if (get_site_transient('tenweb_so_auth_error_logs')) {
    if (!defined('TWO_INCOMPATIBLE_WARNING')) {
        define('TWO_INCOMPATIBLE_WARNING', true);
    }
    $two_incompatible_errors[] = [ 'title' => __('Trouble connecting your website to 10Web:', 'tenweb-speed-optimizer'),
        'message' => __(get_site_transient('tenweb_so_auth_error_logs'), 'tenweb-speed-optimizer') ];
    delete_site_transient('tenweb_so_auth_error_logs');
}

if (is_multisite() && !TENWEB_SO_HOSTED_ON_10WEB) {
    if (!defined('TWO_HOSTED_MULTISITE')) {
        define('TWO_HOSTED_MULTISITE', true);
    }

    if (!defined('TWO_INCOMPATIBLE_ERROR')) {
        define('TWO_INCOMPATIBLE_ERROR', true);
    }
    $two_incompatible_errors[] = [ 'title' => __('Multisite not supported:', 'tenweb-speed-optimizer'),
        'message' => __('This feature will be available soon.', 'tenweb-speed-optimizer') ];
}

if (defined('TWO_INCOMPATIBLE_ERROR') && TWO_INCOMPATIBLE_ERROR) {
    if (is_plugin_active('airlift/airlift.php')) {
        two_incompatible_admin_requirements();
        add_action('wp_ajax_two_deactivate_plugins', [ '\TenWebOptimizer\OptimizerAdmin', 'two_deactivate_plugin' ]);
    }
    add_action('admin_menu', function () {
        two_incompatible_admin_requirements();
        two_define_so_organization_name();
        add_menu_page(
            TWO_SO_ORGANIZATION_NAME . ' Booster',
            TWO_SO_ORGANIZATION_NAME . ' Booster',
            'manage_options',
            'two_settings_page',
            [
                '\TenWebOptimizer\OptimizerAdmin',
                'settings_page',
            ],
            TENWEB_SO_URL . '/assets/images/speed/logo.svg',
            10
        );
    });
    add_action('admin_enqueue_scripts', [ '\TenWebOptimizer\OptimizerAdmin', 'two_enqueue_admin_assets' ]);
} else {
    include_files();
    two_define_so_organization_name();

    \TenWebOptimizer\OptimizerLogger::get_instance();

    global $tenweb_subscription_id;
    global $tenweb_plan_title;
    $tenweb_plan_title = \TenWebWpTransients\OptimizerTransients::get(TENWEB_PREFIX . '_plan_title');
    $tenweb_subscription_id = \TenWebWpTransients\OptimizerTransients::get(TENWEB_PREFIX . '_subscription_id');

    if (empty($tenweb_subscription_id) && $tenweb_subscription_id !== '0') {
        $tenweb_subscription_array = \TenWebOptimizer\OptimizerUtils::two_update_subscription();

        if ($tenweb_subscription_array['tenweb_subscription_id'] !== false) {
            $tenweb_subscription_id = $tenweb_subscription_array['tenweb_subscription_id'];
            $tenweb_plan_title = strtolower($tenweb_subscription_array['tenweb_plan_title']) == 'speed' ? 'Free' : $tenweb_plan_title; //sometimes we get 'speed' from service, it means free
        } else {
            $tenweb_subscription_id = TENWEB_SO_FREE_SUBSCRIPTION_ID;
            $tenweb_plan_title = 'Free';
        }
    } elseif ($tenweb_subscription_id == '0' && !TENWEB_SO_HOSTED_ON_10WEB) {
        $tenweb_subscription_id = TENWEB_SO_FREE_SUBSCRIPTION_ID;
        $tenweb_plan_title = 'Free';
    }

    new OptimizerOnInit();
    register_deactivation_hook(__FILE__, ['\TenWebOptimizer\OptimizerAdmin', 'two_deactivate']);
    register_uninstall_hook(__FILE__, ['\TenWebOptimizer\OptimizerAdmin', 'two_uninstall']);

    global $TwoSettings;
    $TwoSettings = \TenWebOptimizer\OptimizerSettings::get_instance();

    if (!isset($_GET['action']) || $_GET['action'] != 'deactivate') { // phpcs:ignore
        register_activation_hook(__FILE__, ['\TenWebOptimizer\OptimizerAdmin', 'two_activate']);
        add_action('plugins_loaded', 'two_init');
    }
}

function two_incompatible_admin_requirements()
{
    require_once TENWEB_SO_PLUGIN_DIR . '/includes/OptimizerOnInit.php';
    require_once TENWEB_SO_PLUGIN_DIR . 'OptimizerAdmin.php';
    require_once TENWEB_SO_PLUGIN_DIR . '/includes/OptimizerUtils.php';
    require_once TENWEB_SO_PLUGIN_DIR . '/includes/OptimizerWhiteLabel.php';
}

if (defined('TWO_SO_COMPANY_NAME') && get_option('two_so_organization_name') === false) {
    update_option('two_so_organization_name', TWO_SO_COMPANY_NAME);
}

function include_files()
{
    require_once __DIR__ . '/vendor/autoload.php';
}

function add_attr_to_script($tag, $handle)
{
    if ($handle === 'two_preview_js' || $handle === 'jquery-core') {
        return str_replace('<script', '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' ', $tag);
    }

    return $tag;
}

if (isset($_GET['two_preview']) && $_GET['two_preview'] === '1') { // phpcs:ignore
    add_filter('determine_current_user', function ($user_id) {
        if ($user_id) {
            return 0;
        }

        return $user_id;
    }, 99);
}

function two_init()
{
    if (isset($_GET['two_setup']) && $_GET['two_setup'] === '1') { // phpcs:ignore
        if (is_user_logged_in()) {
            two_init_preview();
        } else {
            $two_preview_url = add_query_arg(['two_setup' => '1'], get_home_url() . '/');
            $two_preview_url = urlencode($two_preview_url);
            $two_preview_login_url = add_query_arg([ 'redirect_to' => $two_preview_url], wp_login_url());
            OptimizerUtils::two_redirect($two_preview_login_url);
        }
        $two_conflicting_plugins = OptimizerUtils::get_conflicting_plugins();
        $two_triggerPostOptimizationTasks = get_option('two_triggerPostOptimizationTasks');

        if (empty($two_conflicting_plugins)) {
            $two_conflicting_plugins = [];
        }
        $incompatible_plugins_active_send = get_option('incompatible_plugins_active_send');

        if ($two_triggerPostOptimizationTasks !== '1' && $incompatible_plugins_active_send !== '1') {
            update_option('incompatible_plugins_active_send', '1');
            OptimizerUtils::update_connection_flow_progress('running', 'incompatible_plugins_active', array_values($two_conflicting_plugins));
        }
    }

    if (isset($_GET['two_preview']) && $_GET['two_preview'] === '1') { // phpcs:ignore
        if (isset($_GET['two_level'])) { // phpcs:ignore
            add_filter('option_active_plugins', function ($plugins) {
                $two_plugin_filter_data = OptimizerUtils::filter_incompatible_plugins($plugins);

                if (isset($two_plugin_filter_data['compatible'])) {
                    return $two_plugin_filter_data['compatible'];
                }

                return $plugins;
            });
        }
    }

    add_filter('wcml_user_store_strategy', function () {
        // wcml_client_currency should be kept in cookies (not in session), otherwise page cache will not work
        return 'cookie';
    });

    add_action('wp_ajax_two_set_critical', 'two_set_critical');
    add_action('wp_ajax_nopriv_two_set_critical', 'two_set_critical');

    add_action('wp_ajax_two_init_flow_score', 'two_init_flow_score');
    add_action('wp_ajax_nopriv_two_init_flow_score', 'two_init_flow_score');

    add_action('wp_ajax_two_activate_score_check', 'two_activate_score_check');
    add_action('wp_ajax_nopriv_two_activate_score_check', 'two_activate_score_check');

    add_action('wp_ajax_two_optimize_page', 'two_optimize_page');

    require __DIR__ . '/OptimizerApi.php';
    $OptimizerApi = new \TenWebOptimizer\OptimizerApi();

    \TenWebIO\PreInit::check('booster');
    \TenWebWpBenchmark\Init::getInstance();

    global $TwoSettings;

    if (defined('WP_CLI') && WP_CLI) { //Run only TWO CLI in WP_CLI mode
        require __DIR__ . '/OptimizerCli.php';

        return;
    }

    $two_disable_jetpack_optimization = $TwoSettings->get_settings('two_disable_jetpack_optimization');

    if ('on' === $two_disable_jetpack_optimization) {
        add_filter('option_jetpack_active_modules', 'two_jetpack_module_override');
        function two_jetpack_module_override($modules)
        {
            $disabled_modules = [
                'lazy-images',
                'photon',
                'photon-cdn',
            ];

            foreach ($disabled_modules as $module_slug) {
                $found = array_search($module_slug, $modules);

                if (false !== $found) {
                    unset($modules[ $found ]);
                }
            }

            return $modules;
        }
    }
    \TenWebOptimizer\OptimizerAdmin::get_instance();
    $global_mode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);
    $global_mode_name = '';

    if (is_array($global_mode)) {
        $global_mode_name = $global_mode['mode'];
    }

    if (\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
        if ($global_mode_name !== 'no_optimize' &&
            (empty(\TenWebOptimizer\OptimizerUtils::IOConnected()) || !empty(\TenWebOptimizer\OptimizerUtils::TWOConnected()))) {
            \TenWebOptimizer\OptimizerMain::get_instance();
            \TenWebOptimizer\WebPageCache\OptimizerWebPageCacheWP::get_instance();
        }
    } else {
        do_action('two_main_authorization_failed');
    }

    if (isset($_GET['two_action']) && $_GET['two_action'] === 'generating_critical_css') { // phpcs:ignore
        ob_start('two_critical', 0, PHP_OUTPUT_HANDLER_REMOVABLE);
    }
}

function two_init_flow_score()
{
    if (isset($_POST['nonce'])) { // phpcs:ignore
        $p_nonce = sanitize_text_field($_POST['nonce']); // phpcs:ignore
        $nonce = get_option('wp_two_nonce_two_init_flow_score');
        delete_option('wp_two_nonce_two_init_flow_score');

        if ($p_nonce === $nonce && OptimizerUtils::check_admin_capabilities()) {
            OptimizerUtils::init_flow_score_check();
        }
    }
}
function two_activate_score_check()
{
    if (isset($_POST['nonce'])) { // phpcs:ignore
        $p_nonce = sanitize_text_field($_POST['nonce']); // phpcs:ignore
        $nonce = get_option('two_activate_score_check_nonce_data');
        delete_option('two_activate_score_check_nonce_data');

        if ($p_nonce === $nonce) {
            // Check already optimized pages scores before and after optimize.
            $optimized_pages = array_keys(\TenWebOptimizer\OptimizerUtils::getCriticalPages());

            foreach ($optimized_pages as $optimizedPageID) {
                if ($optimizedPageID != 'front_page') {
                    \TenWebSC\TWScoreChecker::twsc_check_score($optimizedPageID, true, true); /* Not optimized.*/
                    \TenWebSC\TWScoreChecker::twsc_check_score($optimizedPageID); /* Optimized.*/
                }
            }
        }
    }
}

function two_init_preview()
{
    $two_flow_mode_select = get_site_option('two_flow_mode_select');

    if ($two_flow_mode_select !== '1') {
        update_site_option('two_flow_mode_select', '1');
        OptimizerUtils::update_connection_flow_progress('running', 'mode_selection');
    }
    add_action('wp_enqueue_scripts', 'two_preview_assets');
}

function two_preview_assets()
{
    $flow_id = get_site_option(TENWEB_PREFIX . '_flow_id');

    $two_conflicting_plugins = OptimizerUtils::get_conflicting_plugins();
    $two_first_optimization = get_option('two_first_optimization');
    wp_enqueue_style('two_google-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap', [], TENWEB_SO_VERSION);
    wp_enqueue_script('two_preview_js', TENWEB_SO_URL . '/assets/js/two_preview.js', ['jquery'], TENWEB_SO_VERSION);
    wp_enqueue_style('two_preview_css', TENWEB_SO_URL . '/assets/css/two_preview.css', [], TENWEB_SO_VERSION);
    $two_preview_localize_data = [
        'global_mode' => '',
        'two_first_optimization' => $two_first_optimization,
        'home_url' => get_home_url() . '/',
        'flow_id' => $flow_id,
        'skip_url' => TENWEB_DASHBOARD . '?flow_skip=1&optimizing_website=' . get_site_option(TENWEB_PREFIX . '_domain_id'),
        'contact_us_url' => TENWEB_DASHBOARD . '?flow_contact_us=1&optimizing_website=' . get_site_option(TENWEB_PREFIX . '_domain_id') . '&open=livechat',
        'success_url' => TENWEB_DASHBOARD . '?flow_success=1&optimizing_website=' . get_site_option(TENWEB_PREFIX . '_domain_id'),
        'two_modes' => json_encode(\TenWebOptimizer\OptimizerUtils::get_modes(null, true)), // phpcs:ignore
        'no_delay' => esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE),
        'incompatible_plugins' => false,
        'ajaxurl' => admin_url('admin-ajax.php'),
        'ajaxnonce' => wp_create_nonce('two_ajax_nonce'),
        'two_company_name' => TWO_SO_ORGANIZATION_NAME,
    ];
    $two_default_mode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);

    if (isset($two_default_mode) && is_array($two_default_mode)) {
        $two_preview_localize_data['global_mode'] = $two_default_mode['mode'];
    }

    if (is_array($two_conflicting_plugins) && !empty($two_conflicting_plugins)) {
        update_site_option('two_conflicting_plugins', $two_conflicting_plugins);
        $two_preview_localize_data['incompatible_plugins'] = json_encode($two_conflicting_plugins); // phpcs:ignore
    }
    wp_localize_script('two_preview_js', 'two_preview_vars', $two_preview_localize_data);
    add_filter('script_loader_tag', 'add_attr_to_script', 10, 3);
}

function two_critical($content)
{
    return \TenWebOptimizer\OptimizerUtils::clear_iframe_src($content);
}

function two_set_critical()
{
    \TenWebOptimizer\OptimizerUtils::set_critical();
}

/**
 * Optimize the given page.
 *
 * @param $check_score
 *
 * @return void
 */
function two_optimize_page()
{
    $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'two_ajax_nonce') || !OptimizerUtils::check_admin_capabilities()) {
        die('Permission Denied.');
    }

    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    if (!$post_id) {
        return;
    }

    \TenWebWpTransients\OptimizerTransients::set('two_optimize_inprogress_' . $post_id, '1', 30 * MINUTE_IN_SECONDS);
    // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
    //two_update_score_info($post_id, $page_score);
    $initiator = isset($_GET['initiator']) ? sanitize_text_field($_GET['initiator']) : '';
    /* Keeping all posts statuses which is in progress or optimized to manage notif popup view one time for each case */
    $two_optimization_notif_status = get_option('two_optimization_notif_status');
    $two_optimization_notif_status[$post_id] = 'optimizing';
    update_option('two_optimization_notif_status', $two_optimization_notif_status, 1);

    // Get and score in DB the page speed score before optimize.
    \TenWebSC\TWScoreChecker::twsc_check_score($post_id, true, true);
    \TenWebOptimizer\OptimizerCriticalCss::generate_critical_css_by_id($post_id, false, $initiator);

    die;
}

function two_define_so_organization_name()
{
    if (get_option('two_so_organization_name') && get_option('two_so_organization_name') != '') {
        $organization_name = get_option('two_so_organization_name');
        define('TWO_SO_ORGANIZATION_NAME', $organization_name);
        $whiteLabel = OptimizerWhiteLabel::get_instance();
        $whiteLabel->register_hooks();
    } elseif (class_exists('\Tenweb_Manager\Helper') && method_exists('\Tenweb_Manager\Helper', 'get_company_name') && strtolower(\Tenweb_Manager\Helper::get_company_name()) !== '10web') {
        define('TWO_SO_ORGANIZATION_NAME', \Tenweb_Manager\Helper::get_company_name());
    } else {
        define('TWO_SO_ORGANIZATION_NAME', '10Web');
    }
}
