<?php

namespace TenWebOptimizer;

use Plugin_Upgrader;
use TenWebOptimizer\WebPageCache\OptimizerWebPageCache;
use Theme_Upgrader;
use WP_Query;

/**
 * Class OptimizerAdmin
 */
class OptimizerAdmin
{
    const TWO_DELAYED_DEFAULT_LIST = 'getbutton.io,//a.omappapi.com/app/js/api.min.js,' .
    'feedbackcompany.com/includes/widgets/feedback-company-widget.min.js,snap.licdn.com/li.lms-analytics/insight.min.js,' .
    'static.ads-twitter.com/uwt.js,platform.twitter.com/widgets.js,twq(,/sdk.js#xfbml,static.leadpages.net/leadbars/current/embed.js,' .
    'translate.google.com/translate_a/element.js,widget.manychat.com,xfbml.customerchat.js,static.hotjar.com/c/hotjar-,' .
    'smartsuppchat.com/loader.js,grecaptcha.execute,Tawk_API,shareaholic,sharethis,simple-share-buttons-adder,addtoany,' .
    'font-awesome,wpdiscuz,cookie-law-info,pinit.js,/gtag/js,gtag(,/gtm.js,/gtm-,fbevents.js,fbq(,' .
    "google-analytics.com/analytics.js,ga( ',ga(',adsbygoogle,ShopifyBuy,widget.trustpilot.com/bootstrap," .
    'ft.sdk.min.js,apps.elfsight.com/p/platform.js,livechatinc.com/tracking.js,LiveChatWidget,/busting/facebook-tracking/,' .
    'olark,pixel-caffeine/build/frontend.js,wp-emoji-release.min.js';

    protected static $instance = null;

    private $page_url;

    private $TwoSettings;

    const TENWEB_SO_REGENERATE_CRITICAL_ON_UPDATE = false;

    private $plugins_requiring_critical_regeneration = [
        'elementor/elementor.php',
        'elementor-pro/elementor-pro.php',
        'beaver-builder-lite-version/fl-builder.php',
        'siteorigin-panels/siteorigin-panels.php',
        'revslider/revslider.php'
    ];

    public function __construct()
    {
        $two_plugin_activated_flow_init = get_option('two_plugin_activated_flow_init');

        if ($two_plugin_activated_flow_init === '1') {
            delete_option('two_plugin_activated_flow_init');
            delete_option('two_flow_speed');
            delete_option('flow_score_check_init');
            OptimizerUtils::add_log_for_score_check_flow('two_activate', 'start init_flow_score_check=>true');
            OptimizerUtils::init_flow_score_check(true);
        }
        global $TwoSettings;
        $this->TwoSettings = $TwoSettings;
        $this->init_admin();
        $this->page_url = OptimizerUtils::get_page_url();
        $two_triggerPostOptimizationTasks = get_option('two_triggerPostOptimizationTasks');

        // if (!empty($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'two_10web_connection')) {
        // wp_verify_nonce has been changed as the request from the core service isn't able to pass nonce verification//
        if (!empty($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'two_10web_connection')) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            add_action('admin_init', [$this, 'connect_to_tenweb']); //changed from in_admin_header hook, because of gallery flow, ask Hrach and Serine why
        } elseif (isset($_GET['two_disconnect'])) {
            if (isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'two_disconnect_nonce')) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                add_action('in_admin_header', ['\TenWebOptimizer\OptimizerAdmin', 'disconnect_from_tenweb']);
            }
        } elseif (!empty($_GET['new_connection_flow']) && !empty($_GET['connection_error']) && empty($_GET['old_connection_flow'])) {
            \TenWebOptimizer\OptimizerUtils::two_redirect(OptimizerUtils::get_tenweb_connection_link('sign-up', ['old_connection_flow' => 1]));
        }
        add_action('delete_post', [$this, 'delete_optimized_pages_by_id']);
        add_action('wp_trash_post', [$this, 'delete_optimized_pages_by_id']);

        add_action('permalink_structure_changed', [$this, 'wp_permalink_structure_changed_'], 10, 2);

        if ($this->TwoSettings->get_settings('two_enable_plugin_autoupdate') === 'on') {
            add_filter('auto_update_plugin', [ $this, 'two_add_two_plugin_to_autoupdate' ], 10, 2);
        }

        if (get_option(TW_OPTIMIZE_PREFIX . '_cache_structure_size') && (int) get_option(TW_OPTIMIZE_PREFIX . '_cache_structure_size') > TENWEB_CACHE_STRUCTURE_ALLOWED_SIZE) {
            self::clear_cache(false, true);
        }
    }

    public function checkDiviSettingsChange()
    {
        $diviOption = get_option('et_divi');

        if ($diviOption && is_array($diviOption) && isset($diviOption['divi_custom_css'])) {
            $newCustomCSS = $diviOption['divi_custom_css'];
        }

        if (get_option(TW_OPTIMIZE_PREFIX . '_clear_cache_after_divi')
            || (false !== get_option(TW_OPTIMIZE_PREFIX . '_old_divi_custom_css') && isset($newCustomCSS) && get_option(TW_OPTIMIZE_PREFIX . '_old_divi_custom_css') !== $newCustomCSS)) {
            $this->diviChangesClearCache();
        }
    }

    private function diviChangesClearCache()
    {
        $two_critical_pages = OptimizerUtils::getCriticalPages();

        if (!is_array($two_critical_pages)) {
            $two_critical_pages = [];
        }

        foreach ($two_critical_pages as $critical_page) {
            if (isset($critical_page['id']) && $critical_page['id'] != 'front_page') {
                OptimizerUtils::delete_critical_page($critical_page['id']);

                if (has_action('two_page_optimized_removed')) {
                    do_action('two_page_optimized_removed', $critical_page['id']);
                }
            }
        }
        self::clear_cache();
        delete_option(TW_OPTIMIZE_PREFIX . '_clear_cache_after_divi');
        delete_option(TW_OPTIMIZE_PREFIX . '_old_divi_custom_css');
    }

    public function two_add_two_plugin_to_autoupdate($update, $item)
    {
        $plugins = [ 'tenweb-speed-optimizer' ];

        if (isset($item->slug) && in_array($item->slug, $plugins)) {
            // update plugin
            return true;
        }

        // use default settings
        return $update;
    }

    public function delete_optimized_pages_by_id($page_id)
    {
        OptimizerUtils::delete_critical_page($page_id);

        if (has_action('two_page_optimized_removed')) {
            do_action('two_page_optimized_removed', $page_id);
        }
    }

    public function change_front_page($old_value, $value, $option)
    {
        // Remove new front page from list.
        OptimizerUtils::delete_critical_page($value);

        if (has_action('two_page_optimized_removed')) {
            do_action('two_page_optimized_removed', $value);
        }
        self::clear_cache();
    }

    public function wp_permalink_structure_changed_($old_permalink_structure, $permalink_structure)
    {
        $no_optimize_pages = get_option('no_optimize_pages');

        if (is_array($no_optimize_pages)) {
            foreach ($no_optimize_pages as $key => $val) {
                if ($key != 'front_page') {
                    $post_data = OptimizerUtils::get_permalink_name_by_id($key);
                    $no_optimize_pages[$key] = $post_data[ 'url' ];
                }
            }
            update_option('no_optimize_pages', $no_optimize_pages);
        }
    }

    public function init_admin()
    {
        ob_start();

        // phpcs:ignore
        if (!isset($_GET[ 'two_nooptimize' ]) && !isset($_GET[ 'two_action' ]) && current_user_can('manage_options')) {
            add_action('admin_init', [ $this, 'update' ]);
            add_action('admin_init', [$this, 'redirect_after_activation'], 20);
            add_action('admin_init', [$this, 'checkDiviSettingsChange']);
            add_action('admin_menu', [ $this, 'admin_menu' ]);
            add_action('admin_enqueue_scripts', [ '\TenWebOptimizer\OptimizerAdmin', 'two_enqueue_admin_assets' ]);
            add_action('wp_enqueue_scripts', [ $this, 'two_enqueue_assets' ]);

            add_action('wp_ajax_two_settings', [ $this, 'ajax_two_settings' ]);
            add_action('wp_ajax_two_update_setting', [ $this, 'ajax_two_update_setting' ]);
            add_action('wp_ajax_nopriv_two_manager_clear_cache', [ $this, 'manager_clear_cache' ]);
            add_action('wp_ajax_two_critical', [ $this, 'two_critical' ]);
            add_action('wp_ajax_two_critical_statuses', [ $this, 'two_critical_statuses' ]);
            add_action('wp_ajax_two_deactivate_plugins', [ $this, 'two_deactivate_plugin' ]);
            add_action('wp_ajax_two_white_label', [ $this, 'two_white_label' ]);
            add_action('wp_ajax_two_elementor_regenerate_ccss', [$this, 'two_elementor_regenerate_ccss']);

            add_filter('plugin_action_links_' . TENWEB_SO_BASENAME, [ $this, 'add_action_link' ], 10, 2);

            if (!is_admin() && !isset($_GET[ 'elementor-preview' ]) && isset($_GET[ 'two-menu' ])) {  // phpcs:ignore
                add_action('admin_bar_menu', [ $this, 'two_admin_bar_menu' ], 99999);
            }

            add_action('wp_ajax_two_css_options', [ $this, 'save_css_options' ]);

            add_action('wp_ajax_two_get_posts_for_critical', [ $this, 'get_posts_for_critical' ]);
            // TODO: BOOST-1575 Ensure that the cache for the updated post is cleared for both logged-in and non-logged-in users.
            // Once TODO is fixed, delete the 'post_clear_all_cache' function and use the post_clear_cache.
            //add_action('save_post', [$this, 'post_clear_cache'], 10, 3); // Clearing all the caches to handle templates. Editing a template will clear entire cache.
            add_action('save_post', [$this, 'post_clear_all_cache'], 10, 3); // Clearing all the page caches

            add_action('switch_theme', [$this, 'clear_cache'], 10, 0);  // When user change theme.
            add_action('update_option_show_on_front', [ $this, 'change_front_page' ], 10, 3);  // When reading settings for front page are updated.
            add_action('update_option_page_on_front', [ $this, 'change_front_page' ], 10, 3);  // When reading settings for front page are updated.
            add_action('wp_update_nav_menu', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When a custom menu is update.
            add_action('update_option_sidebars_widgets', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When you change the order of widgets.
            add_action('update_option_category_base', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When category permalink is updated.
            add_action('update_option_tag_base', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When tag permalink is updated.
            add_action('permalink_structure_changed', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When permalink structure is update.
            add_action('add_link', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When a link is added.
            add_action('edit_link', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When a link is updated.
            add_action('delete_link', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);  // When a link is deleted.
            add_action('customize_save', [$this, 'clear_cache'], 10, 0);  // When customizer is saved.
            add_action('update_option_theme_mods_' . get_option('stylesheet'), [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0); // When location of a menu is updated.
            add_action('sidebar_admin_setup', [$this, 'clear_cache'], 10, 0);
            add_action('activated_plugin', [$this, 'clear_cache_conditionally_activate'], 10, 1);
            add_action('upgrader_process_complete', [$this, 'clear_cache_conditionally_update'], 10, 2);
            add_action('deactivated_plugin', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);
            add_action('_core_updated_successfully', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);

            //detect ContactForm7 changes
            add_action('wpcf7_save_contact_form', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);

            //detect WooThemes settings changes
            add_action('update_option_woo_options', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);

            // Disabled temporarily as ACF triggers save_post from front.
            // phpcs:ignore
            /*if ( class_exists( 'ACF' ) ) {
              add_action( 'save_post', array('\TenWebOptimizer\OptimizerAdmin', 'acf_update_fields'), 10, 2 );
            }*/

            //detect Formidable changes
            add_action('frm_update_form', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);

            //detect Contact Form by WP Forms changes
            add_action('wpforms_builder_save_form', [$this, 'clear_cache_without_critical_css_regeneration'], 10, 0);
        }
        add_action('wp_ajax_two_flow_set_mode', [ $this, 'two_flow_set_mode' ]);
        add_action('wp_ajax_two_update_flow_status', [ $this, 'two_update_flow_status' ]);
        add_action('wp_ajax_two_finish_flow', [ $this, 'two_finish_flow' ]);
        add_action('wp_ajax_two_flow_incompatible_plugins', [ $this, 'two_flow_incompatible_plugins' ]);
        add_action('wp_ajax_two_clear_cloudflare_cache', [ $this, 'two_clear_cloudflare_cache' ]);
        add_filter('two_clear_cache_action', [ $this, 'clear_cache' ], 10, 0);
        add_action('two_clear_cache', [ $this, 'clear_cache' ], 10, 0);
        add_action('pre_current_active_plugins', [ $this, 'add_deactivation_popup' ]);

        /* show custom columns only for administrators */
        if (strtolower(TWO_SO_ORGANIZATION_NAME) == '10web' && current_user_can('administrator')) {
            add_action('manage_post_posts_custom_column', [ $this, 'two_manage_posts_column'], 10, 2);
            add_action('manage_page_posts_custom_column', [ $this, 'two_manage_posts_column'], 10, 2);
        }
    }

    public function two_manage_posts_column($column_key, $post_id)
    {
        if ($column_key == 'two-speed') {
            if (get_post_status($post_id) != 'publish') {
                return;
            }
            require __DIR__ . '/views/post_custom_column.php';
        }
    }

    public function two_flow_set_mode()
    {
        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')
            && isset($_POST['mode'])
            && OptimizerUtils::check_admin_capabilities()) {
            $two_conflicting_plugins = OptimizerUtils::get_conflicting_plugins();
            $two_triggerPostOptimizationTasks = get_option('two_triggerPostOptimizationTasks');
            $mode = sanitize_text_field($_POST['mode']);
            global $TwoSettings;
            self::set_global_mode($mode);

            if (isset($_POST['test_mode']) && $_POST['test_mode'] === '1') { //if clicked on contact us we don't disable test mode
                $TwoSettings->update_setting('two_test_mode', 'on', true);
            } else {
                $TwoSettings->update_setting('two_test_mode', 'off', true);
                OptimizerUtils::update_connection_flow_progress('running', 'mode_apply_and_test_mode_disable', [$mode]);
            }

            if (empty($two_conflicting_plugins) && $two_triggerPostOptimizationTasks !== '1') {
                update_option('two_triggerPostOptimizationTasks', '1', false);
            }

            if (isset($_POST['redirect']) && $_POST['redirect'] === '1') {
                OptimizerUtils::two_redirect(TENWEB_DASHBOARD . '?flow_success=1&optimizing_website=' . get_site_option(TENWEB_PREFIX . '_domain_id'));
            }
        }
    }

    public function two_update_flow_status()
    {
        $return_data = [
            'success' => false,
        ];

        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce') && isset($_POST['status']) && !empty($_POST['status']) && OptimizerUtils::check_admin_capabilities()) {
            // 1 in-progress
            // 2 finished (Looks good)
            // 3 contact-us
            // 4 contact-us button clicked (new step contact-us clicked)
            update_option('two_flow_status', sanitize_text_field($_POST['status']));

            if ($_POST['status'] === '4') {
                OptimizerUtils::update_connection_flow_progress('running', 'contact_us_clicked');
            } elseif ($_POST['status'] === '3') {
                OptimizerUtils::update_connection_flow_progress('running', 'contact_us');
            }
            $return_data['success'] = true;
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
        echo json_encode($return_data);
        die;
    }

    public function two_finish_flow()
    {
        $return_data = [
            'success' => false,
        ];

        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce') && OptimizerUtils::check_admin_capabilities()) {
            update_option('two_flow_status', '2');
            OptimizerUtils::update_connection_flow_progress('done', 'connection_flow_finish');
            $return_data['success'] = true;
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
        echo json_encode($return_data);
        die;
    }

    public function two_flow_incompatible_plugins()
    {
        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) {
            $incompatible_plugins = [];
            $two_triggerPostOptimizationTasks = get_option('two_triggerPostOptimizationTasks');

            if (isset($_POST['two_disable_incompatible_plugins']) && isset($_POST['incompatible_plugins']) && is_array($_POST['incompatible_plugins']) && !empty($_POST['incompatible_plugins'])) {
                $incompatible_plugins = sanitize_text_field($_POST['incompatible_plugins']);
                $two_conflicting_plugins = OptimizerUtils::get_conflicting_plugins();
                $incompatible_plugins_inactive = [];

                foreach ($incompatible_plugins as $incompatible_plugin) {
                    if (isset($two_conflicting_plugins[$incompatible_plugin])) {
                        $incompatible_plugins_inactive[] = $two_conflicting_plugins[$incompatible_plugin];
                    }
                }

                if (!empty($incompatible_plugins_inactive) && $two_triggerPostOptimizationTasks !== '1') {
                    OptimizerUtils::update_connection_flow_progress('running', 'incompatible_plugins_inactive', $incompatible_plugins_inactive);
                }
                deactivate_plugins($incompatible_plugins);
            }

            if ($two_triggerPostOptimizationTasks !== '1') {
                update_option('two_triggerPostOptimizationTasks', '1', false);
            }
            OptimizerUtils::two_redirect(TENWEB_DASHBOARD . '?flow_success=1&optimizing_website=' . get_site_option(TENWEB_PREFIX . '_domain_id'));
        }
    }

    public function add_deactivation_popup()
    {
        if (!TENWEB_SO_HOSTED_ON_10WEB && OptimizerUtils::is_tenweb_booster_connected()) {
            include 'views/deactivation_popup.php';
        }
    }

    public function clear_cache_conditionally_activate($plugin)
    {
        $exclude_critical_regeneration = true;

        if ($plugin) {
            // Regenerate criticals only if an allowed plugin is actived.
            if (in_array($plugin, $this->plugins_requiring_critical_regeneration)) {
                $exclude_critical_regeneration = false;
            }
        }
        self::clear_cache(false, $exclude_critical_regeneration, true, true, 'front_page', false, false);
    }

    public function clear_cache_conditionally_update($upgrader, $hook_extra)
    {
        $exclude_critical_regeneration = true;

        if ($upgrader instanceof Theme_Upgrader) {
            // Regenerate citicals only if active theme is updated.
            if (isset($hook_extra['themes']) && (is_array($hook_extra['themes']) && in_array(get_option('stylesheet'), $hook_extra['themes']))) {
                $exclude_critical_regeneration = false;
            }
        } elseif ($upgrader instanceof Plugin_Upgrader) {
            // Regenerate criticals only if an allowed plugin is active and updated.
            if (isset($upgrader->skin->plugin_active) && $upgrader->skin->plugin_active &&
                isset($hook_extra['plugins']) && (is_array($hook_extra['plugins']) && array_intersect($this->plugins_requiring_critical_regeneration, $hook_extra['plugins']))) {
                $exclude_critical_regeneration = false;
            }
        }
        self::clear_cache(false, $exclude_critical_regeneration, true, true, 'front_page', false, false);
    }

    public function clear_cache_without_critical_css_regeneration()
    {
        self::clear_cache(false, true, true, true, 'front_page', false, false);
    }

    public function post_clear_cache($post_ID, $post, $update)
    {
        if (isset($post->post_status) && $post->post_status === 'publish' && $update) {
            $permalink = get_permalink($post_ID);
            OptimizerWebPageCache::delete_cache_by_url($permalink);

            if ('post' === $post->post_type && (int) get_option('page_for_posts') > 0) {
                OptimizerWebPageCache::delete_cache_by_url(get_option('page_for_posts'));
            }
            $post_type_archive = get_post_type_archive_link($post->post_type);

            if ($post_type_archive) {
                OptimizerWebPageCache::delete_cache_by_url($post_type_archive);
            }
            // Add next post.
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_adjacent_post_get_adjacent_post
            $next_post = get_adjacent_post(false, '', false);

            if ($next_post) {
                OptimizerWebPageCache::delete_cache_by_url(get_permalink($next_post));
            }

            // Add next post in same category.
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_adjacent_post_get_adjacent_post
            $next_in_same_cat_post = get_adjacent_post(true, '', false);

            if ($next_in_same_cat_post && $next_in_same_cat_post !== $next_post) {
                OptimizerWebPageCache::delete_cache_by_url(get_permalink($next_in_same_cat_post));
            }

            // Add previous post.
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_adjacent_post_get_adjacent_post
            $previous_post = get_adjacent_post(false, '', true);

            if ($previous_post) {
                OptimizerWebPageCache::delete_cache_by_url(get_permalink($previous_post));
            }

            // Add previous post in same category.
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_adjacent_post_get_adjacent_post
            $previous_in_same_cat_post = get_adjacent_post(true, '', true);

            if ($previous_in_same_cat_post && $previous_in_same_cat_post !== $previous_post) {
                OptimizerWebPageCache::delete_cache_by_url(get_permalink($previous_in_same_cat_post));
            }

            // Add the author page.
            $author_url = trailingslashit(get_author_posts_url($post->post_author));

            if (trailingslashit(site_url()) !== $author_url && trailingslashit(home_url()) !== $author_url) {
                OptimizerWebPageCache::delete_cache_by_url($author_url);
            }

            // Add all parents.
            $parents = get_post_ancestors($post_ID);

            if ((bool) $parents) {
                foreach ($parents as $parent_id) {
                    OptimizerWebPageCache::delete_cache_by_url(get_permalink($parent_id));
                }
            }

            OptimizerWebPageCache::delete_cache_by_url(home_url());

            remove_action('save_post', [$this, 'post_clear_cache'], 10, 2);
        }
    }

    public function post_clear_all_cache()
    {
        OptimizerWebPageCache::delete_all_cached_pages();
        remove_action('save_post', [$this, 'post_clear_all_cache'], 10, 2);
    }

    public static function acf_update_fields($post_id, $post)
    {
        if ($post->post_type == 'acf-field-group' || $post->post_type == 'acf-field') {
            self::clear_cache(false, true);
            remove_action('save_post', ['\TenWebOptimizer\OptimizerAdmin', 'acf_update_fields'], 10, 2);
        }
    }

    private static function fix_delayed_list_slashes()
    {
        if (empty(get_option('two_delayed_js_execution_list_updated_fix_slashes'))) {
            global $TwoSettings;
            $option = $TwoSettings->get_settings('two_delayed_js_execution_list');

            if (!empty($option)) {
                $option = implode('', explode('\\', $option));
                $TwoSettings->update_setting('two_delayed_js_execution_list', stripslashes(trim($option)));
            }
        }
        update_option('two_delayed_js_execution_list_updated_fix_slashes', 1);
    }

    public function connect_to_tenweb($parameters = null)
    {
        if (empty($parameters)) {
            $parameters = [];
            $parameters['email'] = !empty($_GET['email']) ? sanitize_email($_GET['email']) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $parameters['token'] = !empty($_GET['token']) ? sanitize_text_field($_GET['token']) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $parameters['new_connection_flow'] = !empty($_GET['new_connection_flow']) ? rest_sanitize_boolean($_GET['new_connection_flow']) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $parameters['sign_up_from_free_plugin'] = !empty($_GET['sign_up_from_free_plugin']) ? rest_sanitize_boolean($_GET['sign_up_from_free_plugin']) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }
        $email = !empty($parameters['email']) ? sanitize_email($parameters['email']) : null;
        $token = !empty($parameters['token']) ? sanitize_text_field($parameters['token']) : null;
        $new_connection_flow = !empty($parameters['new_connection_flow']);
        $sign_up_from_free_plugin = !empty($parameters['sign_up_from_free_plugin']);

        if (!empty($email) && !empty($token)) {
            $pwd = md5($token);
            $class_login = \Tenweb_Authorization\Login::get_instance();
            $args = [ 'connected_from' => TENWEB_SO_CONNECTED_FROM ];

            if ($class_login->login($email, $pwd, $token, $args) == true && $class_login->check_logged_in()) {
                OptimizerUtils::add_log_for_score_check_flow('connect_to_tenweb', 'start init_flow_score_check=>true');
                OptimizerUtils::init_flow_score_check(true);
                $two_first_connect = get_option('two_first_connect', false);
                $date = time();

                if (!$two_first_connect) {
                    //this option also used to check booster real connection when IO is connected
                    update_option('two_first_connect', $date);
                }
                delete_option('two_triggerPostOptimizationTasks');
                delete_option('incompatible_plugins_active_send');
                global $TwoSettings;

                \Tenweb_Authorization\Helper::remove_error_logs();

                $TwoSettings->update_setting('two_connected', '1');
                $TwoSettings->sync_configs_with_plugin_state();
                /*set test mode and set global mode extreme*/
                self::set_global_mode('extreme');
                /*-----------------------------*/
                delete_option('two_flow_critical_start');
                self::generateCriticalCssOnInit(true);
                $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
                $url = TENWEB_DASHBOARD . '/websites?optimizing_website=' . $domain_id . '&from_plugin=' . OptimizerUtils::FROM_PLUGIN;

                if ($sign_up_from_free_plugin) {
                    $url .= '&from_free_plugin=1';
                }

                if ($new_connection_flow) {
                    // Clear all unexpected output. We don't want to see a warning in rest response.
                    while (ob_get_level() !== 0) {
                        ob_end_clean();
                    }

                    die(json_encode(['connected_domain_id' => get_site_option(TENWEB_PREFIX . '_domain_id')])); //phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
                }
                OptimizerUtils::two_redirect($url);
            } else {
                $errors = $class_login->get_errors();
                $err_msg = (!empty($errors)) ? $errors['message'] : 'Something went wrong. ' .
              'If this website is already connected to the 10Web dashboard via the manager plugin, ' .
              'please disconnect it from the manager plugin to be able to use 10Web Booster.';
                set_site_transient('tenweb_so_auth_error_logs', $err_msg, MINUTE_IN_SECONDS);
            }
        }

        if (is_multisite()) {
            OptimizerUtils::two_redirect(network_admin_url() . 'admin.php?page=two_settings_page');
        }
        OptimizerUtils::two_redirect(get_admin_url() . 'admin.php?page=two_settings_page');
    }

    public static function disconnect_from_tenweb($silent = false)
    {
        global $TwoSettings;
        $TwoSettings->update_setting('two_connected', '0');
        $TwoSettings->sync_configs_with_plugin_state('inactive');
        delete_option('two_flow_status');
        delete_option('two_triggerPostOptimizationTasks');
        delete_option('incompatible_plugins_active_send');
        delete_option('flow_score_check_init');
        delete_option('two_flow_score_log');
        delete_option('two_clear_cache_from');
        delete_option('two_flow_critical_start');
        delete_option('wp_two_nonce_two_init_flow_score');
        delete_option('two_activate_score_check_nonce_data');
        //deleting option which is showing IO connection
        delete_site_option(TENWEB_PREFIX . '_from_image_optimizer');
        delete_site_option(TENWEB_PREFIX . '_client_referral_hash');
        delete_site_option(TW_OPTIMIZE_PREFIX . '_saved_nonce');

        $two_critical_pages = OptimizerUtils::getCriticalPages();

        foreach ($two_critical_pages as $c_page) {
            OptimizerUtils::delete_critical_page($c_page['id']);
        }

        $class_login = \Tenweb_Authorization\Login::get_instance();
        \Tenweb_Authorization\Helper::remove_error_logs();
        $class_login->logout(false);
        $redirection_link = get_admin_url() . 'admin.php?page=two_settings_page';

        if (isset($_GET['two_reconnect']) && isset($_GET['two_reconnect_nonce'])
            && wp_verify_nonce(sanitize_text_field($_GET['two_reconnect_nonce']), 'two_reconnect_nonce')) {
            $redirection_link = \TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link();
        }

        if (!$silent) {
            self::clear_cache(false, true);
            self::two_uninstall();

            if (is_multisite()) {
                OptimizerUtils::two_redirect($redirection_link);
            }
            OptimizerUtils::two_redirect($redirection_link);
        } else {
            self::two_uninstall();
        }
    }

    public static function get_incompatible_active_plugins()
    {
        $incompatiblePluginList = [];

        foreach (OptimizerUtils::TWO_INCOMPATIBLE_PLUGIN_LIST as $pluginSlug => $pluginName) {
            if (is_plugin_active($pluginSlug)) {
                $incompatiblePluginList[] = $pluginName;
            }
        }

        return $incompatiblePluginList;
    }

    /*
    *  check state activate and deactivate plugin
    */
    public function get_plugins_state()
    {
        $screen = get_current_screen();

        if ($screen->id === 'plugins') {
            $two_active_plugins_list = get_option('two_active_plugins_list');
            $active_plugins_current = get_option('active_plugins');

            if (is_array($two_active_plugins_list) && is_array($active_plugins_current)) {
                $diff = array_merge(array_diff($active_plugins_current, $two_active_plugins_list), array_diff($two_active_plugins_list, $active_plugins_current));

                if (!empty($diff)) {
                    self::clear_cache(false, true);
                    update_option('two_active_plugins_list', $active_plugins_current);
                }
            } else {
                update_option('two_active_plugins_list', $active_plugins_current);
            }
        }
    }

    public function two_admin_bar_menu($wp_admin_bar)
    {
        $wp_admin_bar->add_menu([
            'id' => 'two_options',
            'title' => '10Web Booster',
        ]);
    }

    public function two_enqueue_assets()
    {
        wp_register_style('two-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,800&display=swap');
        $two_exclude_css = $this->TwoSettings->get_settings('two_exclude_css');
        $two_async_css = $this->TwoSettings->get_settings('two_async_css');
        $two_disable_css = $this->TwoSettings->get_settings('two_disable_css');
        $two_async_page = $this->TwoSettings->get_settings('two_async_page');
        $two_disable_page = $this->TwoSettings->get_settings('two_disable_page');
        $two_async_all = $this->TwoSettings->get_settings('two_async_all');
        $two_disable_css_page = [];
        $two_async_css_page = [];

        if (is_array($two_disable_page) && isset($two_disable_page[$this->page_url])) {
            $two_disable_css_page = explode(',', $two_disable_page[$this->page_url]);
        }

        if (is_array($two_async_page) && isset($two_async_page[$this->page_url])) {
            $two_async_css_page = explode(',', $two_async_page[$this->page_url]);
        }

        $two_async_css = explode(',', $two_async_css);
        $two_disable_css = explode(',', $two_disable_css);
        $two_exclude_css = explode(',', $two_exclude_css);

        wp_enqueue_script('two_admin_bar_js', TENWEB_SO_URL . '/assets/js/two_admin_bar.js', ['jquery'], TENWEB_SO_VERSION);
        wp_enqueue_style('two_admin_bar_css', TENWEB_SO_URL . '/assets/css/two_admin_bar.css', [], TENWEB_SO_VERSION);
        wp_localize_script('two_admin_bar_js', 'two_admin_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajaxnonce' => wp_create_nonce('two_ajax_nonce'),
            'two_async_css' => json_encode($two_async_css),  // phpcs:ignore
            'two_disable_css' => json_encode($two_disable_css),  // phpcs:ignore
            'two_disable_css_page' => json_encode($two_disable_css_page),  // phpcs:ignore
            'two_async_css_page' => json_encode($two_async_css_page),  // phpcs:ignore
            'two_async_all' => $two_async_all,
            'two_exclude_css' => $two_exclude_css,
        ]);
    }

    public function save_css_options()
    {
        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) {
            $page_url_without_pagespeed = '';
            $two_async_css = $this->TwoSettings->get_settings('two_async_css');
            $two_disable_css = $this->TwoSettings->get_settings('two_disable_css');
            $two_async_page = $this->TwoSettings->get_settings('two_async_page');
            $two_disable_page = $this->TwoSettings->get_settings('two_disable_page');
            $two_exclude_css = $this->TwoSettings->get_settings('two_exclude_css');
            $page_url = sanitize_url($_POST['page_url']);  // phpcs:ignore

            if (OptimizerUtils::get_url_query($page_url, 'PageSpeed') === 'off') {
                $page_url_without_pagespeed = str_replace(['?PageSpeed=off', '&PageSpeed=off'], '', $page_url);
            }
            $page_url = OptimizerUtils::remove_domain_part($page_url);
            $page_url_without_pagespeed = OptimizerUtils::remove_domain_part($page_url_without_pagespeed);
            $el_id = sanitize_text_field($_POST['el_id']);  // phpcs:ignore
            $task = sanitize_text_field($_POST['task']);  // phpcs:ignore
            $state = sanitize_text_field($_POST['state']);  // phpcs:ignore

            if (!is_array($two_disable_page)) {
                $two_disable_page = [];
            }

            if (!is_array($two_async_page)) {
                $two_async_page = [];
            }
            $two_disable_page[$page_url] = sanitize_text_field($_POST['two_disable_page']); // phpcs:ignore
            $two_async_page[$page_url] = sanitize_text_field($_POST['two_async_page']); // phpcs:ignore

            if (!empty($page_url_without_pagespeed)) {
                $two_disable_page[$page_url_without_pagespeed] = sanitize_text_field($_POST['two_disable_page']); // phpcs:ignore
                $two_async_page[$page_url_without_pagespeed] = sanitize_text_field($_POST['two_async_page']); // phpcs:ignore
            }
            $this->TwoSettings->update_setting('two_disable_page', $two_disable_page);
            $this->TwoSettings->update_setting('two_async_page', $two_async_page);

            if ($task == 'two_async') {
                if ($state === '1') {
                    $this->TwoSettings->update_setting('two_async_css', $two_async_css . ',' . $el_id);
                    $this->TwoSettings->update_setting('two_disable_css', str_replace(',' . $el_id, '', $two_disable_css));
                } else {
                    $this->TwoSettings->update_setting('two_async_css', str_replace($el_id, '', $two_async_css));
                }
            } elseif ($task == 'two_disable') {
                if ($state === '1') {
                    $this->TwoSettings->update_setting('two_disable_css', $two_disable_css . ',' . $el_id);
                    $this->TwoSettings->update_setting('two_async_css', str_replace(',' . $el_id, '', $two_async_css));
                } else {
                    $this->TwoSettings->update_setting('two_disable_css', str_replace($el_id, '', $two_disable_css));
                }
            } elseif ($task == 'two_exclude_css') {
                if ($state === '1') {
                    $this->TwoSettings->update_setting('two_exclude_css', $two_exclude_css . ',' . $el_id);
                    $this->TwoSettings->update_setting('two_async_css', str_replace(',' . $el_id, '', $two_async_css));
                    $this->TwoSettings->update_setting('two_disable_css', str_replace(',' . $el_id, '', $two_disable_css));
                } else {
                    $this->TwoSettings->update_setting('two_exclude_css', str_replace($el_id, '', $two_disable_css));
                }
            } else {
                $this->TwoSettings->update_setting('two_async_css', str_replace(',' . $el_id, '', $two_async_css));
                $this->TwoSettings->update_setting('two_disable_css', str_replace(',' . $el_id, '', $two_disable_css));
            }
        }
    }

    public static function two_enqueue_admin_assets($hook_suffix)
    {
        wp_register_style('two-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,800&display=swap');

        if ($hook_suffix == 'toplevel_page_two_settings_page') {
            wp_enqueue_script('two_deactivate_plugin', TENWEB_SO_URL . '/assets/js/two_deactivate_plugin.js', [ 'jquery' ], TENWEB_SO_VERSION);
            wp_localize_script('two_deactivate_plugin', 'two_admin_vars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajaxnonce' => wp_create_nonce('two_ajax_nonce')
            ]);
            $two_admin_vars = [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajaxnonce' => wp_create_nonce('two_ajax_nonce')
            ];
            $two_critical_default_settings = get_option('two_critical_default_settings');
            global $TwoSettings;

            if (empty($two_critical_default_settings)) {
                $TwoSettings->init_critical_defaults();
            }

            if (isset($_GET[ 'mode' ]) && 'advanced' === $_GET[ 'mode' ] && (!defined('TWO_INCOMPATIBLE_ERROR') || !TWO_INCOMPATIBLE_ERROR)) { // phpcs:ignore
                wp_enqueue_script('two_tagsinput_js', TENWEB_SO_URL . '/assets/js/jquery.tagsinput.min.js', [ 'jquery' ], TENWEB_SO_VERSION);
                wp_enqueue_script('two_admin_js', TENWEB_SO_URL . '/assets/js/two_admin.js', [ 'jquery' ], TENWEB_SO_VERSION);

                wp_localize_script('two_admin_js', 'two_critical_options', $TwoSettings->critical_options);
                wp_enqueue_script('datatables_js', TENWEB_SO_URL . '/assets/js/datatables.min.js', [ 'jquery' ], TENWEB_SO_VERSION);
                wp_enqueue_script('two_jquery_multi-select_js', TENWEB_SO_URL . '/assets/js/jquery.multi-select.js', [ 'jquery' ], TENWEB_SO_VERSION);
                wp_enqueue_style('two_admin_css', TENWEB_SO_URL . '/assets/css/two_admin.css', '', TENWEB_SO_VERSION);
                wp_enqueue_style('two_multi-select_css', TENWEB_SO_URL . '/assets/css/multi-select.css', '', TENWEB_SO_VERSION);
                wp_enqueue_style('jquery_tagsinput_css', TENWEB_SO_URL . '/assets/css/jquery.tagsinput.min.css', '', TENWEB_SO_VERSION);
                wp_enqueue_style('datatables_min_css', TENWEB_SO_URL . '/assets/css/datatables.min.css', '', TENWEB_SO_VERSION);

                wp_localize_script('two_admin_js', 'two_admin_vars', $two_admin_vars);
                wp_enqueue_script('two_select2', TENWEB_SO_URL . '/assets/lib/select2/js/select2.min.js', [ 'jquery' ], TENWEB_SO_VERSION);
                wp_enqueue_style('two_select2', TENWEB_SO_URL . '/assets/lib/select2/css/select2.min.css', '', TENWEB_SO_VERSION);
            } else {
                wp_enqueue_style('two_settings_basic_css', TENWEB_SO_URL . '/assets/css/settings_basic.css', ['two-open-sans'], TENWEB_SO_VERSION);
            }
        }

        if ($hook_suffix != 'toplevel_page_two_settings_page') {
            // Not add the css to the 10Web Booster page.
            wp_enqueue_style('two_speed_css', TENWEB_SO_URL . '/assets/css/speed.css', ['two-open-sans'], TENWEB_SO_VERSION);
        }
        wp_enqueue_script('two_circle_js', TENWEB_SO_URL . '/assets/js/circle-progress.js', ['jquery'], TENWEB_SO_VERSION);
        $is_two_advanced = isset($_GET['page']) && $_GET['page'] == 'two_settings_page' && isset($_GET['mode']) && $_GET['mode'] == 'advanced'; // phpcs:ignore
        $optimizing_in_progress_transinent = false;

        if (get_the_ID()) {
            $optimizing_in_progress_transinent = \TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . get_the_ID());
        }

        if (!$is_two_advanced) {
            wp_enqueue_script('two_speed_js', TENWEB_SO_URL . '/assets/js/speed.js', ['jquery', 'two_circle_js'], TENWEB_SO_VERSION);
            wp_localize_script('two_speed_js', 'two_speed', [
                'nonce' => wp_create_nonce('two_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'clearing' => __('Clearing...', 'tenweb-speed-optimizer'),
                'cleared' => __('Cleared cache', 'tenweb-speed-optimizer'),
                'clear' => __('Clear cache', 'tenweb-speed-optimizer'),
                'optimize_entire_website' => \TenWebOptimizer\OptimizerOnInit::two_reached_limit(),
                'critical_pages' => \TenWebOptimizer\OptimizerUtils::getCriticalPages(),
                'front_page_id' => get_option('page_on_front'),
                'optimizing_in_progress' => $optimizing_in_progress_transinent,
                'two_front_page_speed' => get_option('two-front-page-speed', []),
                'wrong_email' => __('Please enter a valid email address.', 'tenweb-speed-optimizer'),
                'sign_up' => __('SIGN UP & CONNECT', 'tenweb-speed-optimizer'),
                'connection_link' => esc_url(\TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link()),
                'something_wrong' => __('Something went wrong, please try again.', 'tenweb-speed-optimizer'),
            ]);
            wp_localize_script('two_speed_js', 'two_limit_exceed_popup_content', [
                'exceed_url' => \TenWebOptimizer\OptimizerOnInit::two_reached_limit() ? esc_url(\TenWebOptimizer\OptimizerOnInit::two_reached_limit() . '?two_comes_from=pagesListAfterLimit') : '',
                'exceed_title' => esc_html__('You’ve reached the Free Plan limit', 'tenweb-speed-optimizer'),
                'exceed_desc_1' => esc_html__('Upgrade to 10Web Booster Pro to optimize all pages', 'tenweb-speed-optimizer'),
                'exceed_desc_2' => esc_html__('and enable Cloudflare Enterprise CDN.', 'tenweb-speed-optimizer'),
                'exceed_button_text' => esc_html__('Upgrade', 'tenweb-speed-optimizer'),
            ]);
        }
    }

    public function admin_menu()
    {
        add_menu_page(
            TWO_SO_ORGANIZATION_NAME . ' Booster',
            TWO_SO_ORGANIZATION_NAME . ' Booster',
            'manage_options',
            'two_settings_page',
            [
                '\TenWebOptimizer\OptimizerAdmin',
                'settings_page',
            ],
            strtolower(TWO_SO_ORGANIZATION_NAME) == '10web' ? TENWEB_SO_URL . '/assets/images/logo_green.svg' : '',
            30
        );
        add_submenu_page(
            '',
            __('White Label', 'tenweb-speed-optimizer'),
            __('White Label', 'tenweb-speed-optimizer'),
            'manage_options',
            'two_white_label',
            ['\TenWebOptimizer\OptimizerAdmin', 'white_label_view']
        );

        $cloudflare_cdn_class = 'two-submenu-cdn-free';
        $cloudflare_cdn_class_enabled = '';

        if (\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
            $cloudflare_cdn_class = 'two-submenu-cdn-paid';
            global $TwoSettings;

            if ($TwoSettings->get_settings('cloudflare_cache_status') == 'on') {
                $cloudflare_cdn_class_enabled = 'two-submenu-cdn-enabled';
            }
        }

        if (!defined('TWO_INCOMPATIBLE_ERROR')
            && !TENWEB_SO_HOSTED_ON_10WEB
            && \TenWebOptimizer\OptimizerUtils::is_tenweb_booster_connected()
            && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web') {
            add_submenu_page(
                'two_settings_page',
                'Main Page',
                'Main Page',
                'manage_options',
                'two_settings_page',
                [
                    '\TenWebOptimizer\OptimizerAdmin',
                    'settings_page',
                ]
            );
            add_submenu_page(
                'two_settings_page',
                'Cloudflare CDN',
                '<span class="' . sanitize_html_class($cloudflare_cdn_class) . ' ' . $cloudflare_cdn_class_enabled . '">Cloudflare CDN</span>',
                'manage_options',
                'two_cloudflare_cdn',
                [
                    '\TenWebOptimizer\OptimizerAdmin',
                    'cloudflare_cdn_page',
                ]
            );

            if (!\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
                add_submenu_page(
                    'two_settings_page',
                    'Customer support',
                    'Customer support',
                    'manage_options',
                    'two_customer_support',
                    [
                        '\TenWebOptimizer\OptimizerAdmin',
                        'customer_support',
                    ]
                );
            }
        }
    }

    public static function white_label_view()
    {
        require_once __DIR__ . '/views/white_label_view.php';
    }

    public static function settings_page()
    {
        if (isset($_GET['mode']) && 'advanced' === $_GET['mode'] && (!defined('TWO_INCOMPATIBLE_ERROR') || !TWO_INCOMPATIBLE_ERROR)) { // phpcs:ignore
            if (OptimizerUtils::is_wpml_active() && (empty($_GET['lang']) || $_GET['lang'] !== 'all')) { // phpcs:ignore
                $baseUrl = sanitize_text_field($_SERVER['REQUEST_SCHEME']) . '://' . sanitize_text_field($_SERVER['SERVER_NAME']) . sanitize_text_field($_SERVER['REQUEST_URI']); // phpcs:ignore
                $location = add_query_arg(sanitize_text_field($_SERVER['QUERY_STRING']), '', $baseUrl); // phpcs:ignore
                $location = add_query_arg('lang', 'all', $location);
                OptimizerUtils::two_redirect($location);
            }
            require_once __DIR__ . '/views/settings_view.php';
        } else {
            if ((!defined('TWO_INCOMPATIBLE_ERROR') || !TWO_INCOMPATIBLE_ERROR) && OptimizerUtils::is_tenweb_booster_connected() && get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id')) {
                require_once __DIR__ . '/views/settings_basic.php';
            } else {
                require_once __DIR__ . '/views/settings_connect.php';
            }
        }
    }

    public static function cloudflare_cdn_page()
    {
        require_once __DIR__ . '/views/cdn_page.php';
    }

    public static function referral_program_page()
    {
        require_once __DIR__ . '/views/referral_program_page.php';
    }

    public static function customer_support()
    {
        $main_class = 'two-customer-support-main';
        $close_icon = false;
        require_once __DIR__ . '/views/customer_support.php';
        customer_care_html($main_class, $close_icon);
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function ajax_two_settings()
    {
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce') && isset($_POST['task'])) { // phpcs:ignore
            $ajax_task = sanitize_text_field($_POST['task']);
            $clear_cache_from = isset($_POST['clear_cache_from']) ? sanitize_text_field($_POST['clear_cache_from']) : '';

            if ($ajax_task === 'clear_cache') {
                self::clear_cache(
                    true,
                    true,
                    true,
                    true,
                    'front_page',
                    false,
                    true,
                    true,
                    false,
                    $clear_cache_from
                );
            } elseif ($ajax_task === 'regenerate_critical') {
                self::clear_cache(true, false, true, true, 'all');
            } elseif ($ajax_task === 'settings') {
                if (isset($_POST['two_critical_pages'])) {
                    $two_critical_pages = OptimizerUtils::getCriticalPages();

                    foreach ($_POST['two_critical_pages'] as $key => $val) { // phpcs:ignore
                        if (isset($two_critical_pages[$key]) && isset($_POST['two_critical_pages'][$key])) {
                            if (isset($two_critical_pages[$key]['critical_css'])) {
                                $_POST['two_critical_pages'][$key]['critical_css'] = $two_critical_pages[$key]['critical_css'];
                            }

                            if (isset($two_critical_pages[$key]['uncritical_css'])) {
                                $_POST['two_critical_pages'][$key]['uncritical_css'] = $two_critical_pages[$key]['uncritical_css'];
                            }

                            if (isset($two_critical_pages[$key]['critical_fonts'])) {
                                $_POST['two_critical_pages'][$key]['critical_fonts'] = $two_critical_pages[$key]['critical_fonts'];
                            }

                            if (isset($two_critical_pages[$key]['critical_bg'])) {
                                $_POST['two_critical_pages'][$key]['critical_bg'] = $two_critical_pages[$key]['critical_bg'];
                            }

                            if (isset($two_critical_pages[$key]['status'])) {
                                $_POST['two_critical_pages'][$key]['status'] = $two_critical_pages[$key]['status'];
                            }

                            if (isset($two_critical_pages[$key]['critical_date'])) {
                                $_POST['two_critical_pages'][$key]['critical_date'] = $two_critical_pages[$key]['critical_date'];
                            }

                            if (isset($two_critical_pages[$key]['images_in_viewport'])) {
                                $_POST['two_critical_pages'][$key]['images_in_viewport'] = $two_critical_pages[$key]['images_in_viewport'];
                            }
                        }
                    }
                }
                $this->TwoSettings->set_settings($_POST);
            } elseif ($ajax_task == 'regenerate_webp') {
                $url_list = sanitize_text_field($_POST[ 'url_list' ]); // phpcs:ignore
                $request_webp_action = OptimizerUtils::request_webp_action('regenerate', $url_list);
                echo json_encode($request_webp_action); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
                die;
            } elseif ($ajax_task == 'delete_webp') {
                $request_webp_action = OptimizerUtils::request_webp_action('delete');
                echo json_encode($request_webp_action); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
                die;
            } elseif ($ajax_task === 'delete_logs' && !empty($_POST['log_type'])) {
                OptimizerLogger::delete_logs(sanitize_text_field($_POST['log_type']));
                wp_send_json_success();
            }
            global $TwoSettings;

            if ($TwoSettings->get_settings('two_remove_elementor_lazyload') == 'on') {
                // change elementor background_lazyload to inactive
                if (is_plugin_active('elementor/elementor.php') && get_option('elementor_experiment-e_lazyload') != 'inactive') {
                    update_option('elementor_experiment-e_lazyload', 'inactive', false);
                }
            }
            // Purge 10Web cache.
            do_action('tenweb_purge_all_caches');
            $message = apply_filters('two_save_settings_message', __('Success!', 'tenweb-speed-optimizer'));
            $code = apply_filters('two_save_settings_code', 0);
            $two_webp_delivery_working = OptimizerUtils::testWebPDelivery();
            echo json_encode([ 'success' => true, 'message' => $message, 'code' => $code, 'webp_delivery_status' => $two_webp_delivery_working ]); // phpcs:ignore
            die;
        }
        echo json_encode(['success' => false]); // phpcs:ignore
        die;
    }

    public function ajax_two_update_setting()
    {
        if (isset($_POST[ 'nonce' ]) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) { // phpcs:ignore
            $name = sanitize_text_field($_POST['name']); // phpcs:ignore
            $value = sanitize_text_field($_POST['value']); // phpcs:ignore
            $this->TwoSettings->update_setting($name, $value);
            echo json_encode([ 'success' => true ]); // phpcs:ignore
            die;
        }
        echo json_encode([ 'success' => false ]); // phpcs:ignore
        die;
    }

    public function two_critical()
    {
        $return_data = [
            'success' => false,
        ];

        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) { // phpcs:ignore
            \TenWebWpTransients\OptimizerTransients::set('two_critical_in_process', '1', 360);

            if (isset($_POST['data']['task'])) {
                $task = sanitize_text_field($_POST['data']['task']);

                if ($task === 'generate') {
                    if (isset($_POST['data'])) {
                        $_POST['data']['initiator'] = 'manual_from_ccss_tab';
                    }
                    $return_data = OptimizerCriticalCss::generateCriticalCSS($_POST);
                } elseif ($task === 'delete' && isset($_POST['data']['page_id'])) {
                    $page_id = sanitize_text_field($_POST['data']['page_id']);
                    OptimizerUtils::delete_critical_page($page_id);
                    self::clear_cache(false, true);

                    if (has_action('two_page_optimized_removed')) {
                        do_action('two_page_optimized_removed', $page_id);
                    }
                } elseif ('insert/update' === $task && isset($_POST['data']['page_id'])) {
                    $page_id = sanitize_text_field($_POST['data']['page_id']);
                    $two_critical_pages = $this->TwoSettings->get_settings('two_critical_pages');

                    $update_data = map_deep($_POST['data']['two_critical_pages'][$page_id], 'sanitize_text_field'); // phpcs:ignore

                    if (isset($two_critical_pages[$page_id])) {
                        if (isset($two_critical_pages[$page_id]['critical_css'])) {
                            $update_data['critical_css'] = $two_critical_pages[$page_id]['critical_css'];
                        }

                        if (isset($two_critical_pages[$page_id]['uncritical_css'])) {
                            $update_data['uncritical_css'] = $two_critical_pages[$page_id]['uncritical_css'];
                        }

                        if (isset($two_critical_pages[$page_id]['critical_fonts'])) {
                            $update_data['critical_fonts'] = $two_critical_pages[$page_id]['critical_fonts'];
                        }

                        if (isset($two_critical_pages[$page_id]['critical_bg'])) {
                            $update_data['critical_bg'] = $two_critical_pages[$page_id]['critical_bg'];
                        }

                        if (isset($two_critical_pages[$page_id]['critical_date'])) {
                            $update_data['critical_date'] = $two_critical_pages[$page_id]['critical_date'];
                        }
                    }

                    if (!is_array($two_critical_pages)) {
                        $two_critical_pages = [];
                    }
                    $two_critical_pages[$page_id] = $update_data;
                    $this->TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
                    $return_data['success'] = true;
                } elseif ($task === 'update_defaults') {
                    if (isset($_POST['data']['default_settings'])) {
                        $default_settings = map_deep($_POST['data']['default_settings'], 'sanitize_text_field'); // phpcs:ignore

                        if (empty($_POST['data']['default_settings']['default_sizes'])) {
                            $default_settings['default_sizes'] = [];
                        }
                        update_option('two_critical_default_settings', $default_settings);
                    }
                    $return_data['success'] = true;
                }
            }
        }
        echo json_encode($return_data); // phpcs:ignore
        die;
    }

    public function two_critical_statuses()
    {
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) { // phpcs:ignore
            $two_critical_pages = OptimizerUtils::getCriticalPages();
            $two_critical_in_process = \TenWebWpTransients\OptimizerTransients::get('two_critical_in_process');
            $return_data = [
                'pages' => [],
                'status' => $two_critical_in_process,
            ];

            if (is_array($two_critical_pages)) {
                foreach ($two_critical_pages as $page_id => $critical_page) {
                    $critical_page_status = $critical_page[ 'status' ];

                    if ($critical_page_status == 'success') {
                        if (!isset($critical_page[ 'critical_css' ]) || empty($critical_page[ 'critical_css' ])) {
                            $critical_page_status = 'not_started';
                            $two_critical_pages[ $page_id ][ 'status' ] = 'not_started';
                        }
                    }
                    $return_data[ 'pages' ][] = [
                        'page_id' => $critical_page[ 'id' ],
                        'status' => $critical_page_status,
                    ];
                }
            }
            $this->TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
            echo json_encode($return_data, true); // phpcs:ignore
            die;
        }
    }

    public function add_action_link($links, $file)
    {
        if (TENWEB_SO_BASENAME === $file) {
            $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=two_settings_page')) . '">' . __('Settings') . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    public function manager_clear_cache()
    {
        $two_token_clear_cache = \TenWebWpTransients\OptimizerTransients::get('two_token_clear_cache');

        if (isset($_POST['two_token']) && $two_token_clear_cache === $_POST['two_token']) { // phpcs:ignore
            \TenWebWpTransients\OptimizerTransients::delete('two_token_clear_cache');
            self::clear_cache(false, !$_POST['regenerate_critical_css']); // phpcs:ignore
        }
    }

    public function two_clear_cloudflare_cache()
    {
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce') && OptimizerUtils::check_admin_capabilities()) { // phpcs:ignore
            if (isset($_POST['page_url'])) {
                OptimizerUtils::clear_cloudflare_cache([sanitize_url($_POST['page_url'])]); // phpcs:ignore
            }
        }
    }

    public static function clear_cache(
        $is_json = false,
        $excludeCriticalRegeneration = false,
        $delete_tenweb_manager_cache = true,
        $delete_cloudflare_cache = true,
        $critical_regeneration_mode = 'front_page',
        $clear_critical = false,
        $clear_two_cloudflare_cache = true,
        $warmup_cache = true,
        $delete_files = false,
        $clear_cache_from = ''
    ) {
        do_action('two_before_clear_cache', debug_backtrace()); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
        $date = time();
        global $TwoSettings;
        $TwoSettings->update_setting('two_clear_cache_date', $date);
        $TwoSettings->update_setting('tenweb_so_version', TENWEB_SO_VERSION);
        $dir = OptimizerCache::get_path();
        $delete_cache_db = OptimizerUtils::delete_all_cache_db();
        OptimizerCacheStructure::flushAllCache();
        $exclude_dir = null;
        $two_critical_status = $TwoSettings->get_settings('two_critical_status');

        if ($excludeCriticalRegeneration) {
            $exclude_dir = 'critical';
        }
        $cache_file_delete_status = true;

        if ($delete_files) {
            $cache_file_delete_status = OptimizerUtils::delete_all_cache_file($dir, [$dir, $dir . 'css', $dir . 'js', $dir . 'critical'], $exclude_dir);
        } else {
            \TenWebOptimizer\WebPageCache\OptimizerWebPageCacheWP::get_instance()->delete_all_cache();
        }
        OptimizerUtils::purge_pagespeed_cache();

        if ($delete_tenweb_manager_cache) {
            do_action('tenweb_purge_all_caches', false);
        }

        if ($delete_cloudflare_cache) {
            OptimizerUtils::flushCloudflareCache();
        }
        wp_cache_flush();

        $success = false;

        if ($cache_file_delete_status && $delete_cache_db) {
            $success = true;
        }

        OptimizerUtils::clear_third_party_cache();

        if (!$excludeCriticalRegeneration && $two_critical_status === 'true') {
            OptimizerUtils::regenerate_critical($critical_regeneration_mode);
        }

        if ($clear_critical) {
            self::clear_critical_cache();
        }

        OptimizerLogger::add_clear_cache_log($is_json, $excludeCriticalRegeneration, $delete_tenweb_manager_cache, $delete_cloudflare_cache, $critical_regeneration_mode, $clear_critical);

        if ($clear_two_cloudflare_cache) {
            OptimizerUtils::clear_cloudflare_cache([], $clear_cache_from);
        }

        if ($warmup_cache) {
            OptimizerUtils::warmup_cache();
        }

        //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace, PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
        do_action('two_after_clear_cache', debug_backtrace());

        if ($is_json) {
            echo json_encode(['success' => $success]); // phpcs:ignore
            die;
        }

        return $success;
    }

    public static function clear_critical_cache()
    {
        global $TwoSettings;
        $two_critical_pages = OptimizerUtils::getCriticalPages();
        $home_critical = false;

        if (is_array($two_critical_pages)) {
            foreach ($two_critical_pages as $id => $page) {
                if (!$home_critical && $id === 'front_page') {
                    $home_critical = true;
                }
                $two_critical_pages[$id]['status'] = 'not_started';
                unset($two_critical_pages[$id]['critical_css'], $two_critical_pages[$id]['uncritical_css'], $two_critical_pages[$id]['critical_fonts'], $two_critical_pages[$id]['critical_bg'], $two_critical_pages[$id]['critical_date']);
            }
            $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
        }
        $prefix = 'critical/two_*.*';
        OptimizerUtils::delete_files_by_prefix($prefix);

        if ($home_critical) {
            OptimizerCriticalCss::generate_critical_css_by_id('front_page');
        }
    }

    public static function two_activate($networkwide)
    {
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token', false);

        if (!$access_token) {
            update_option('two_plugin_activated_flow_init', '1');
        }

        if (function_exists('is_multisite') && is_multisite()) {
            // Check if it is a network activation - if so, run the activation function for each blog id.
            if ($networkwide) {
                global $wpdb;
                // Get all blog ids.
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs"); // phpcs:ignore

                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    self::activate();
                    restore_current_blog();
                }

                return;
            }
        }
        add_option('redirect_after_activation_option', true);
        self::activate();
    }

    public static function activate()
    {
        global $TwoSettings;
        $two_version = get_option('tw_optimize_version');

        if ($two_version === false) {
            $TwoSettings->set_default_settings();
        }
        self::set_additional_settings();

        if (\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
            $TwoSettings->update_setting('two_connected', '1');

            $habit_version = '2.8.1';

            if (version_compare($two_version, $habit_version, '<') && !TENWEB_SO_HOSTED_ON_10WEB) {
                $nonce = uniqid('two_activate_score_check_', false);
                update_option('two_activate_score_check_nonce_data', $nonce);
                wp_remote_post(admin_url('admin-ajax.php'), [
                    'timeout' => 5, // phpcs:ignore
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => false,
                    'body' => [
                        'action' => 'two_activate_score_check',
                        'nonce' => $nonce
                    ],
                    'cookies' => []
                ]);
            }
            $TwoSettings->sync_configs_with_plugin_state();
        } else {
            $TwoSettings->update_setting('two_connected', '0');
        }
        $TwoSettings->update_setting('two_critical_url_args', 'PageSpeed=off&two_nooptimize=1&two_action=generating_critical_css');
        OptimizerUtils::testWebPDelivery();
        self::add_two_delayed_js_execution_list();

        if (TENWEB_SO_HOSTED_ON_10WEB && strpos(get_site_url(), 'TENWEBLXC') === false) { //if hosted on 10web
            // Set WebP delivery to on by default.
            if (false === $TwoSettings->get_settings('two_enable_nginx_webp_delivery')) {
                $TwoSettings->update_setting('two_enable_nginx_webp_delivery', 'on');
            }

            if (!$two_version || self::TENWEB_SO_REGENERATE_CRITICAL_ON_UPDATE) {
                self::generateCriticalCssOnInit();
            }
        } elseif (!TENWEB_SO_HOSTED_ON_10WEB) { //connected website
            if (\Tenweb_Authorization\Login::get_instance()->check_logged_in() && (!$two_version || self::TENWEB_SO_REGENERATE_CRITICAL_ON_UPDATE)) {
                if (!empty(\TenWebOptimizer\OptimizerUtils::TWOConnected()) && empty(\TenWebOptimizer\OptimizerUtils::IOConnected())) {
                    self::generateCriticalCssOnInit();
                }
            } elseif (!$two_version) {
                if (defined('TENWEB_SO_AUTOCONNECT') && TENWEB_SO_AUTOCONNECT) {
                    $two_php_not_compatible = defined('TWO_INCOMPATIBLE_ERROR') && TWO_INCOMPATIBLE_ERROR;
                    $two_connect_link = $two_php_not_compatible ? '' : OptimizerUtils::get_tenweb_connection_link();
                    OptimizerUtils::two_redirect($two_connect_link, false);
                }
            }
        }
    }

    public static function generateCriticalCssOnInit($rightAfterConnect = false)
    {
        $two_version = get_option('tw_optimize_version');
        $two_critical_pages = OptimizerUtils::getCriticalPages();

        if (empty($two_critical_pages)) {
            OptimizerCriticalCss::generate_critical_css_by_id('front_page', $rightAfterConnect);
        } else {
            if ($two_version === false || version_compare($two_version, '1.54.6', '<')) {
                if (OptimizerUtils::is_wpml_active()) {
                    OptimizerUtils::add_wpml_home_pages_into_critical_pages($two_critical_pages, $two_critical_pages[ 'front_page' ][ 'url' ]);
                }
            }

            if (TENWEB_SO_HOSTED_ON_10WEB) {
                OptimizerUtils::regenerate_critical('all', $rightAfterConnect);
            } elseif (\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                if ($rightAfterConnect) {
                    OptimizerUtils::update_critical_statuses($two_critical_pages, 'not_started');
                    OptimizerCriticalCss::generate_critical_css_by_id('front_page', $rightAfterConnect);
                } else {
                    OptimizerUtils::regenerate_critical('front_page', $rightAfterConnect);
                }
            }
        }
    }

    public function update()
    {
        $version = get_option('tw_optimize_version');
        $new_version = TENWEB_SO_VERSION;

        if (version_compare($version, $new_version, '<')) {
            if (defined('TENWEB_CONNECTED_SPEED')
                && \Tenweb_Authorization\Login::get_instance()->check_logged_in()
                && \Tenweb_Authorization\Login::get_instance()->get_connection_type() == TENWEB_CONNECTED_SPEED
                && empty(get_option('two_first_connect', false))
                && version_compare($version, '2.19.49', '<')
                && empty(OptimizerUtils::IOConnected())) {
                // this option also used to check booster real connection when IO is connected
                // and if you connected your site before November 2022 you don't have this option
                // so in WordPress admin you see Connection screen with Optimize button, to fix that we set that option

                update_option('two_first_connect', time());
            }
            global $TwoSettings;
            /* Update TW optimize version */
            update_option('tw_optimize_version', $new_version);
            self::add_two_delayed_js_execution_list();
            self::fix_delayed_list_slashes();
            self::set_additional_settings();
            $two_critical_sizes = $TwoSettings->get_settings('two_critical_sizes');

            if ($two_critical_sizes === false) {
                $TwoSettings->set_critical_defaults();
            }

            if ($TwoSettings->get_settings('two_critical_status') === 'true' && $TwoSettings->get_settings('two_critical_font_status', null) === null) {
                $TwoSettings->update_setting('two_critical_font_status', 'true');
            }

            if (!$TwoSettings->get_settings('two_page_cache_life_time')) {
                $TwoSettings->update_setting('two_page_cache_life_time', $TwoSettings->get_default_setting('two_page_cache_life_time'));
            }

            \TenWebOptimizer\WebPageCache\OptimizerWebPageCacheWP::get_instance()->store_page_cache_configs();

            if (TENWEB_SO_HOSTED_ON_10WEB
                || (\Tenweb_Authorization\Login::get_instance()->check_logged_in() && \TenWebOptimizer\OptimizerUtils::TWOConnected())) {
                \Tenweb_Authorization\Helper::check_site_state(true);
                self::clear_cache(false, !self::TENWEB_SO_REGENERATE_CRITICAL_ON_UPDATE);
            }
        }
    }

    public static function two_uninstall()
    {
        $site_options = [
            TENWEB_PREFIX . '_is_available',
            'two_flow_mode_select',
            'two_conflicting_plugins',
            'two_flow_speed',
            TENWEB_PREFIX . '_client_referral_hash'];
        $options = [
            'two_first_connect',
            'two_delayed_js_execution_list_updated',
            'two_delayed_js_execution_list_updated_fix_slashes',
            'two_active_plugins_list',
            'two_optimized_date_front_page',
            'two_optimization_notif_status',
            'two_clear_cache_logs',
            'two_default_mode',
            'two_optimized_date',
            'two_page_speed',
            'two_critical_blocked',
            'no_optimize_pages',
            'two_triggerPostOptimizationTasks',
            'two_flow_status',
            'two-front-page-speed',
            'two_mode_front_page',
            'two_optimization_notif_status',
            'two_triggerPostOptimizationTasks',
            'two_flow_status',
            'two_performance_requests_logs',
            'two_clear_cache_logs',
            'two_critical_css_logs',
            'two_serve_not_optimized_page_logs',
            'tw_optimize_version'
        ];

        foreach ($site_options as $option) {
            delete_site_option($option);
        }

        foreach ($options as $option) {
            delete_option($option);
        }

        foreach (wp_load_alloptions() as $option => $value) {
            if (0 === strpos($option, \TenWebWpTransients\OptimizerTransients::TRANSIENT_KEY) ||
                0 === strpos($option, \TenWebWpTransients\OptimizerTransients::TRANSIENT_TIMEOUT_KEY)) {
                delete_option($option);
            }
        }
    }

    public static function two_deactivate()
    {
        // Disable WebP delivery on plugin deactivation.
        global $TwoSettings;

        $two_critical_pages = OptimizerUtils::getCriticalPages();

        if (is_array($two_critical_pages)) {
            foreach ($two_critical_pages as $id => $page) {
                if (isset($page['status']) && $page['status'] == 'in_progress') {
                    $page['status'] = 'not_started';
                }
                $critical_key = 'two_critical_' . $id;
                $critical_in_progress_key = 'two_critical_in_progress_' . $id;
                \TenWebWpTransients\OptimizerTransients::delete($critical_key);
                \TenWebWpTransients\OptimizerTransients::delete($critical_in_progress_key);
            }
            $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
        }

        $timestamp = wp_next_scheduled('two_daily_cron_hook');

        if ($timestamp) {
            wp_unschedule_event($timestamp, 'two_daily_cron_hook');
        }

        $TwoSettings->update_setting('two_enable_nginx_webp_delivery', '');

        if (isset($_GET['two_disconnect'])) {
            if (isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'two_disconnect_nonce')) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                self::disconnect_from_tenweb(true);
            }
        }
        self::clear_cache(false, true, true, true, 'front_page', false, true, false, true);
        $TwoSettings->sync_configs_with_plugin_state('inactive');
        //call IO deactivation hook
        \TenWebIO\Init::deactivate();
        \Tenweb_Authorization\Helper::check_site_state(true, null, null, ['tenweb-speed-optimizer' => 0]);
    }

    private static function add_two_delayed_js_execution_list()
    {
        global $TwoSettings;
        $two_delayed_js_execution_list_updated = get_option('two_delayed_js_execution_list_updated');

        if ($two_delayed_js_execution_list_updated != '1') {
            $two_delayed_js_execution_list = $TwoSettings->get_settings('two_delayed_js_execution_list');
            $default_data = self::TWO_DELAYED_DEFAULT_LIST;

            if (isset($two_delayed_js_execution_list) && $two_delayed_js_execution_list && !empty($two_delayed_js_execution_list)) {
                $default_data = $two_delayed_js_execution_list . ',' . $default_data;
            }

            $TwoSettings->update_setting(
                'two_delayed_js_execution_list',
                $default_data
            );
            update_option('two_delayed_js_execution_list_updated', '1');
        }
    }

    public function get_posts_for_critical()
    {
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) { // phpcs:ignore
            die('Invalid nonce');
        }
        $return = [];
        $two_critical_pages = OptimizerUtils::getCriticalPages();
        $is_wpml_active = OptimizerUtils::is_wpml_active();

        if (!isset($two_critical_pages[ 'front_page' ])) {
            $flag_url = null;

            if ($is_wpml_active) {
                $flag_url = OptimizerUtils::get_wpml_post_flag_url('front_page');
            }
            $return[] = [ 'front_page', 'Home', site_url(), $flag_url];
        }

        if ($is_wpml_active) {
            do_action('wpml_switch_language', 'all'); // to get translated posts to
        }

        add_filter('posts_where', [ $this, 'title_filter' ], 10, 2);
        $search_params = [
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => 50 // phpcs:ignore
        ];

        if (isset($_GET['q'])) {
            $search_params[ 'search_post_title' ] = sanitize_text_field($_GET['q']);
        }
        $search_results = new WP_Query($search_params);

        if ($search_results->have_posts()) {
            while ($search_results->have_posts()) {
                $search_results->the_post();

                if (!isset($two_critical_pages[$search_results->post->ID])) {
                    if ('page' !== get_option('show_on_front')
                        || !get_option('page_on_front') || get_option('page_on_front') != $search_results->post->ID
                    ) {
                        // shorten the title a little
                        $title = (mb_strlen($search_results->post->post_title) > 50) ? mb_substr($search_results->post->post_title, 0, 49) . '...' : $search_results->post->post_title;
                        $flag_url = null;

                        if ($is_wpml_active) {
                            $flag_url = OptimizerUtils::get_wpml_post_flag_url($search_results->post->ID);
                        }
                        $return[] = [ $search_results->post->ID, $title, get_permalink($search_results->post->ID), $flag_url ];
                    }
                }
            }
        }
        remove_filter('posts_where', [ $this, 'title_filter' ]);
        echo json_encode($return); // phpcs:ignore
        die;
    }

    public static function title_filter($where, $wp_query)
    {
        global $wpdb;

        if ($search_term = $wp_query->get('search_post_title')) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($search_term) . '%\'';
        }

        return $where;
    }

    public function redirect_after_activation()
    {
        /* added to send site state after plugin activation as it was not working in two_activate function */
        if (get_option('redirect_after_activation_option', false) && \Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
            \Tenweb_Authorization\Helper::check_site_state(true);
        }

        if (!TENWEB_SO_HOSTED_ON_10WEB && get_option('redirect_after_activation_option', false)
            && (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()
                || (empty(\TenWebOptimizer\OptimizerUtils::TWOConnected())
                    && !empty(\TenWebOptimizer\OptimizerUtils::IOConnected())))) {
            delete_option('redirect_after_activation_option');
            OptimizerUtils::two_redirect(admin_url('admin.php?page=two_settings_page&two_after_activation=true'));
        }
        delete_option('redirect_after_activation_option');
    }

    public static function two_deactivate_plugin()
    {
        if (isset($_POST['nonce'], $_POST['plugin_slug']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce') && current_user_can('deactivate_plugins')) { // phpcs:ignore
            $two_incompatible_plugins = OptimizerUtils::get_conflicting_plugins();
            $plugin_slug = sanitize_text_field($_POST['plugin_slug']);

            if (array_key_exists($plugin_slug, $two_incompatible_plugins)) {
                deactivate_plugins($plugin_slug);
            }
        }
    }

    public static function two_white_label()
    {
        if (isset($_POST['nonce'], $_POST['company_name'], $_POST['support_url']) && wp_verify_nonce($_POST['nonce'], 'two_ajax_nonce')) { // phpcs:ignore
            $company_name = trim(sanitize_text_field($_POST['company_name']));
            $support_url = trim(sanitize_url($_POST['support_url'])); // phpcs:ignore_
            update_option('two_so_organization_name', $company_name);
            update_option('two_so_organization_support_url', $support_url);
        }
    }

    public static function set_global_mode($mode)
    {
        global $TwoSettings;
        $mode_list = OptimizerUtils::get_modes();
        $settings_names = $TwoSettings->settings_names;

        foreach ($mode_list[$mode] as $key => $val) {
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
        update_option('two_default_mode', $mode_list[$mode]);
    }

    private static function set_additional_settings()
    {
        global $TwoSettings;

        if (!get_option('two_set_not_optimizable_and_turn_off_mode_settings_v2')) {
            // This option 'two_set_not_disabled_pages' shouldn't be deleted in uninstall
            $TwoSettings->update_setting('two_non_optimizable_speed_optimizer_pages', '/wp-admin/, /xmlrpc.php, wp-.*.php, \/feed(\/)*, index.php, sitemap(_index)?.xml, /store.*,/cart.*, /my-account.*, /checkout.*, /addons.*, well-known, acme-challenge');

            $two_optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            $args = [
                'post_type' => 'page',
                'meta_key' => 'two_mode',
            ];
            $optimized_posts = new WP_Query($args);

            if (isset($optimized_posts->posts)) {
                foreach ($optimized_posts->posts as $post) {
                    if (isset($post->ID) && !isset($two_optimized_pages[$post->ID])) {
                        $two_optimized_pages[$post->ID] = [
                            'id' => $post->ID,
                            'url' => get_permalink($post->ID),
                        ];
                    }
                }
            }

            $so_pages_list = [];

            if (is_array($two_optimized_pages)) {
                foreach ($two_optimized_pages as $so_page) {
                    if (isset($so_page['id'], $so_page['url'])) {
                        $so_page_data = [
                            'page_id' => $so_page['id'],
                            'url' => $so_page['url'],
                        ];

                        if ($so_page['id'] === 'front_page') {
                            $page_mode = get_option('two_mode_front_page');
                        } elseif (false !== strpos($so_page['id'], 'term_')) {
                            $so_page['id'] = (int) ltrim($so_page['id'], 'term_');
                            $so_page_data['page_id'] = 'term_' . (int) ltrim($so_page_data['page_id'], 'term_');
                            $page_mode = get_term_meta($so_page['id'], 'two_mode', true);
                        } elseif (false !== strpos($so_page['id'], 'user_')) {
                            $so_page['id'] = (int) ltrim($so_page['id'], 'user_');
                            $so_page_data['page_id'] = 'user_' . (int) ltrim($so_page_data['page_id'], 'user_');
                            $page_mode = get_user_meta($so_page['id'], 'two_mode', true);
                        } else {
                            $so_page['id'] = (int) $so_page['id'];
                            $so_page_data['page_id'] = (int) $so_page_data['page_id'];
                            $page_mode = get_post_meta($so_page['id'], 'two_mode', true);
                        }

                        if (is_array($page_mode) && isset($page_mode['mode']) && $page_mode['mode'] == 'no_optimize') {
                            $so_pages_list[$so_page_data['page_id']] = $so_page_data['url'];
                        }
                    }
                }

                $no_optimize_pages_list = get_option('no_optimize_pages');

                if (is_array($no_optimize_pages_list)) {
                    foreach ($so_pages_list as $id) {
                        if (isset($no_optimize_pages_list[$id])) {
                            $so_pages_list[$id] = $no_optimize_pages_list[$id];
                        }
                    }
                }

                update_option('no_optimize_pages', $so_pages_list, false);
            }

            update_option('two_set_not_optimizable_and_turn_off_mode_settings_v2', 1, false);
        }

        if (!get_option('two_set_dispatch_events')) {
            $TwoSettings->update_setting('two_events_after_load', ['DOMContentLoaded', 'Load', 'Click']);
            update_option('two_set_dispatch_events', '1');
        }

        if (!get_option('two_set_generate_ccss_on_load')) {
            if (TWO_ALWAYS_CRITICAL && !TENWEB_SO_HOSTED_ON_10WEB) {
                $TwoSettings->update_setting('two_generate_ccss_on_load', ['page']);
            } else {
                $TwoSettings->update_setting('two_generate_ccss_on_load', []);
            }
            update_option('two_set_generate_ccss_on_load', '1');
        }
        $set_compress_html_default = get_option('two_set_compress_html_default');

        if ($set_compress_html_default != '1') {
            $TwoSettings->update_setting(
                'two_serve_gzip',
                'on'
            );
            update_option('two_set_compress_html_default', '1');
        }
    }

    public function two_elementor_regenerate_ccss()
    {
        $two_critical_pages = OptimizerUtils::getCriticalPages();

        if (!is_array($two_critical_pages)) {
            $two_critical_pages = [];
        }
        global $TwoSettings;
        $two_critical_status = $TwoSettings->get_settings('two_critical_status');
        $return_data = [
            'success' => false,
        ];

        // phpcs:ignore
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'two_elementor_regenerate_ccss')
        && $two_critical_status) {
            $post_types = isset($_POST['allowed_post_types']) ? sanitize_text_field($_POST['allowed_post_types']) : '';
            $allowed_post_types = explode(',', $post_types);
            $page_id = isset($_POST['page_id']) ? sanitize_text_field($_POST['page_id']) : '';

            if (in_array(get_post_type($page_id), $allowed_post_types)) {
                if (get_option('page_on_front') == $page_id) {
                    $page_id = 'front_page';
                }

                foreach ($two_critical_pages as $critical_page) {
                    if (isset($critical_page['id']) && $critical_page['id'] == $page_id) {
                        OptimizerCriticalCss::generate_critical_css_by_id($page_id, false, 'auto_from_elementor_page_edit');
                        $return_data = [
                            'success' => true,
                        ];
                    }
                }
            }
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
        echo json_encode($return_data);
        die;
    }

    // get and set flow_id and notif_id for starting optimization flow
    public static function setFlowIdNotificationId()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'two_ajax_nonce') || !OptimizerUtils::check_admin_capabilities()) {
            die('Permission Denied.');
        }
        $domain_id = (int) get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0);

        if ($domain_id) {
            $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
            $response = wp_remote_post(TENWEB_API_URL . '/domains/' . $domain_id . '/products/run-booster-flow', [
                'method' => 'POST',
                'timeout' => 1500, // phpcs:ignore
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Accept' => 'application/x.10webmanager.v1+json'
                ],
            ]);

            if (!is_wp_error($response) && isset($response['body'], $response['response']['code']) && $response['response']['code'] === 200) {
                $response_body = json_decode($response['body'], true);

                if ($response_body['data'] && $response_body['status'] == 'ok') {
                    if (isset($response_body['data']['flow_id'])) {
                        update_site_option(TENWEB_PREFIX . '_flow_id', $response_body['data']['flow_id']);
                    }

                    if (isset($response_body['data']['notification_id'])) {
                        update_site_option(TENWEB_PREFIX . '_notification_id', $response_body['data']['notification_id']);
                    }
                }
            } else {
                //just for easy check if something went wrong
                update_option('two_connection_after_io', [$response], false);
            }
        }
        self::startTheFlow();
    }

    public static function startTheFlow()
    {
        $class_login = \Tenweb_Authorization\Login::get_instance();

        if ($class_login->check_logged_in()) {
            OptimizerUtils::add_log_for_score_check_flow('connect_to_tenweb', 'start init_flow_score_check=>true');
            OptimizerUtils::init_flow_score_check(true);
            $two_first_connect = get_option('two_first_connect', false);
            $date = time();

            if (!$two_first_connect) {
                //this option also used to check booster real connection when IO is connected
                update_option('two_first_connect', $date);
            }
            delete_option('two_triggerPostOptimizationTasks');
            delete_option('incompatible_plugins_active_send');
            global $TwoSettings;

            \Tenweb_Authorization\Helper::remove_error_logs();

            $TwoSettings->update_setting('two_connected', '1');
            $TwoSettings->sync_configs_with_plugin_state();
            /*set test mode and set global mode extreme*/
            self::set_global_mode('extreme');
            /*-----------------------------*/
            delete_option('two_flow_critical_start');
            self::generateCriticalCssOnInit(true);
            $domain_id = get_site_option(TENWEB_PREFIX . '_domain_id');
            $url = TENWEB_DASHBOARD . '/websites?optimizing_website=' . $domain_id . '&from_plugin=' . OptimizerUtils::FROM_PLUGIN . '&two_only_optimize=1';
            $sign_up_from_free_plugin = !empty($_GET['sign_up_from_free_plugin']) ? rest_sanitize_boolean($_GET['sign_up_from_free_plugin']) : null; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            if (!empty($sign_up_from_free_plugin)) {
                $url .= '&from_free_plugin=1';
            }
            wp_send_json_success($url);
        }
    }
}
