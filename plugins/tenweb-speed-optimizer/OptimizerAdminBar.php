<?php

namespace TenWebOptimizer;

/**
 * Class OptimizerAdminBar
 */
class OptimizerAdminBar
{
    /* 1 - active and connected, 2 - test mode, 3 - disconnected, 4 - pro case, 5 - abandoned, 6 - connection issues  */
    public $two_booster_status = 1;

    /* Total pages count */
    public $total_pages_count;

    /* Not optimized pages count */
    public $notoptimized_pages_count;

    /* Optimized pages count */
    public $optimized_pages_count;

    /* Total count of optimized images */
    public $optimized_images_count;

    /* Total count of images */
    public $total_images_count;

    /* Domain Id */
    private $domain_id;

    /* Workspace Id */
    private $workspace_id;

    /* Booster active and connected */
    public const TWO_CONNECTED = 1;

    /* Booster active and in test mode */
    public const TWO_TEST_MODE = 2;

    /* Booster disconnected */
    public const TWO_DISCONNECTED = 3;

    /* Booster connection issues */
    public const TWO_CONNECTIONISSUES = 6;

    /* Booster is PRO */
    public const TWO_PRO_CONNECTED = 4;

    /* Booster is ABANDONED */
    public const TWO_ABANDONED = 5;

    private $current_plan;

    private $empty_images_count_transient;

    private $referral_hash;

    public $two_frontend;

    public function __construct($wp_admin_bar)
    {
        $this->referral_hash = get_site_option(TENWEB_PREFIX . '_client_referral_hash');
        global $tenweb_plan_title;
        $this->current_plan = !empty($tenweb_plan_title) ? $tenweb_plan_title : 'Free'; //this check is just to be sure that plan_title is set
        $this->current_plan = strtolower($this->current_plan) == 'speed' ? 'Free' : $this->current_plan; // just a dirty fix

        if (!is_admin() && !OptimizerUrl::urlIsOptimizable(null, true)) {
            return;
        }
        $this->two_set_data();

        /* Case when page is frontend and user is Pro*/
        if (!is_admin() && $this->two_booster_status == 4) {
            return;
        }

        wp_enqueue_style('two_speed_css', TENWEB_SO_URL . '/assets/css/speed.css', ['two-open-sans'], TENWEB_SO_VERSION);

        //add some inline style to have free user's admin bar visible for small screens too
        if ($this->two_booster_status == 1) {
            $style_for_free_admin_bar = '@media screen and (max-width:1279px) {
                                          #wpadminbar div.two_admin_bar_menu_main {
                                            left: -322px;
                                          }
                                        }';
            wp_add_inline_style('two_speed_css', $style_for_free_admin_bar);
        }
        wp_enqueue_script('two_circle_js', TENWEB_SO_URL . '/assets/js/circle-progress.js', ['jquery'], TENWEB_SO_VERSION);
        wp_enqueue_script('two_speed_js', TENWEB_SO_URL . '/assets/js/speed.js', ['jquery', 'two_circle_js'], TENWEB_SO_VERSION);
        wp_localize_script('two_speed_js', 'two_speed', [
            'nonce' => wp_create_nonce('two_ajax_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'clearing' => __('Clearing...', 'tenweb-speed-optimizer'),
            'cleared' => __('Cleared cache', 'tenweb-speed-optimizer'),
            'clear' => __('Clear cache', 'tenweb-speed-optimizer'),
        ]);
        wp_enqueue_script(
            'two_referral_program_js',
            TENWEB_SO_URL . '/assets/js/referral_program.js',
            ['jquery'],
            TENWEB_SO_VERSION
        );

        $wp_admin_bar->add_menu([
            'id' => 'two_adminbar_info',
            'title' => $this->two_admin_menu(),
            'meta' => [
                'html' => $this->two_admin_bar_menu_content(),
            ],
        ]);

        if (is_admin()) {
            $wp_admin_bar->add_menu([
                'id' => 'two_adminbar_progress_info',
                'title' => $this->two_admin_notif_menu(),
                'meta' => [
                    'html' => $this->two_optimize_notification(),
                ],
            ]);
        }
    }

    /**
     * Set values to class variables.
     */
    public function two_set_data()
    {
        if (is_admin()) {
            $this->two_frontend = 0;
        }
        $this->optimized_pages_count = count(\TenWebOptimizer\OptimizerUtils::getCriticalPages());
        $count_pages = wp_count_posts('page');
        $count_posts = wp_count_posts('post');
        $terms_count = (int) get_terms(['fields' => 'count', 'hide_empty' => false]);
        $this->total_pages_count = $count_pages->publish + $count_posts->publish + $terms_count;

        if ($this->optimized_pages_count > $this->total_pages_count) {
            $this->optimized_pages_count = $this->total_pages_count;
        }
        $this->notoptimized_pages_count = $this->total_pages_count - $this->optimized_pages_count;
        $this->workspace_id = (int) get_site_option(TENWEBIO_MANAGER_PREFIX . '_workspace_id', 0);
        $this->domain_id = (int) get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0);

        $two_settings = get_option('two_settings');
        $two_settings = json_decode($two_settings, 1);
        $this->two_booster_status = self::TWO_DISCONNECTED;

        if ((\Tenweb_Authorization\Login::get_instance()->check_logged_in() && $this->domain_id == 0)
            || (!\Tenweb_Authorization\Login::get_instance()->check_logged_in() && !empty(get_option('two_first_connect')) && $this->domain_id != 0)) {
            $this->two_booster_status = self::TWO_CONNECTIONISSUES;
        } elseif ((defined('TWO_INCOMPATIBLE_ERROR') && TWO_INCOMPATIBLE_ERROR) || !OptimizerUtils::is_tenweb_booster_connected()) {
            $this->two_booster_status = self::TWO_DISCONNECTED;
        } elseif (\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
            $this->two_booster_status = self::TWO_PRO_CONNECTED;
        } elseif (!empty($two_settings)) {
            if (isset($two_settings['two_test_mode']) && $two_settings['two_test_mode'] == 'on') {
                $this->two_booster_status = self::TWO_TEST_MODE;
            } elseif (isset($two_settings['two_connected']) && $two_settings['two_connected'] == 1) {
                $two_flow_finished = get_option('two_flow_status') != 1 ? true : false;

                if (!$two_flow_finished) {
                    $this->two_booster_status = self::TWO_ABANDONED;
                } else {
                    $this->two_booster_status = self::TWO_CONNECTED;
                }
            }
        }

        if ($this->two_booster_status != 3 && $this->two_booster_status != 2) {
            $this->get_images_data_api();
        }
    }

    /**
     * Admin bar menu.
     *
     * @return string
     */
    public function two_admin_menu()
    {
        if (!is_admin() && $this->two_booster_status != 3 && $this->two_booster_status != 2) {
            $img = '<img src="' . TENWEB_SO_URL . '/assets/images/logo_green.svg" />';
            $img_display_none = '<img src="' . TENWEB_SO_URL . '/assets/images/logo_green.svg" / style="display:none">';
            $className = ' two_frontpage_not_optimized';

            if ($this->two_is_page_optimized()) {
                $className = ' two_frontpage_optimized';
            }
            $two_admin_bar_menu = '<div class="two_admin_bar_menu two_frontend"><div class="two_admin_bar_menu_header' . $className . '"><span class="two_hidden"></span>' . $img . TWO_SO_ORGANIZATION_NAME . ' Booster' . '</div></div>';

            if ($this->two_is_optimize_inprogress()) {
                $className = ' two_frontpage_optimizing';
                $two_admin_bar_menu = '<div class="two_admin_bar_menu two_frontend"><div class="two_admin_bar_menu_header' . $className . '"><span></span>' . $img_display_none . TWO_SO_ORGANIZATION_NAME . ' Booster' . '</div></div>';
            }
        } else {
            if ($this->two_booster_status == 1) {
                $img = '<img src="' . TENWEB_SO_URL . '/assets/images/logo_green.svg" />' . TWO_SO_ORGANIZATION_NAME . ' Booster' . '<p class="two_page_count">' . $this->notoptimized_pages_count . '</p>';
            } elseif ($this->two_booster_status == 4) {
                $img = '<img src="' . TENWEB_SO_URL . '/assets/images/logo_green.svg" />' . TWO_SO_ORGANIZATION_NAME . ' Booster';
            } else {
                $img = '<img src="' . TENWEB_SO_URL . '/assets/images/logo_disconnect.svg" />' . TWO_SO_ORGANIZATION_NAME . ' Booster';
            }
            $two_admin_bar_menu = '<div class="two_admin_bar_menu two_backend"><div class="two_admin_bar_menu_header">' . $img . '</div></div>';
        }

        return $two_admin_bar_menu;
    }

    /**
     * Admin bar notif menu.
     *
     * @return string
     */
    public function two_admin_notif_menu()
    {
        if ($this->two_booster_status == 1) {
            $two_admin_bar_menu = '<div class="two_admin_bar_notif_menu two_backend"><div class="two_admin_bar_menu_header"><span></span></div></div>';

            return $two_admin_bar_menu;
        }
    }

    /**
     * Adminbar menu content.
     *
     * @return string
     */
    public function two_admin_bar_menu_content()
    {
        $front_score_data = get_option('two-front-page-speed');

        $optimized_images_count = $this->optimized_images_count;
        $total_images_count = $this->total_images_count;
        $rest_page_count = (int) (6 - $this->optimized_pages_count);

        $free_reached = 1;

        if ($this->optimized_pages_count < 6) {
            $free_reached = 0;
        }
        $post_id = get_option('page_on_front');
        ob_start();
        $reanalyze_button_status_current = false;

        if (!empty($front_score_data)) {
            if (isset($front_score_data['current_score']) && isset($front_score_data['current_score']['status'])
                && $front_score_data['current_score']['status'] == 'inprogress') {
                $reanalyze_button_status_current = true;
            }
        }
        $hidden_class = TW_OPTIMIZE_PREFIX . '_hidden';

        if ($this->two_booster_status == 6 && !get_option(TW_OPTIMIZE_PREFIX . '_reconnection_bar_was_shown')) {
            $hidden_class = '';
        } ?>
        <div class="two_admin_bar_menu_main <?php echo esc_attr($hidden_class); ?>" dir="ltr">
            <?php
            /* Frontend and booster is not disconnected or in test mode  or has some connection issues */
            if (!is_admin() && $this->two_booster_status != 3 && $this->two_booster_status != 6 && $this->two_booster_status != 2) {
                if (!$this->two_is_page_optimized()) {
                    $this->two_front_not_optimized_content();
                } else {
                    $this->two_front_optimized_content();
                }
            } else {
                if ($this->two_booster_status == 1) {
                    ?>
                    <div class="two_admin_bar_menu_content two_booster_on_free two-any-reanalyzing-score-section" data-id="front_page">
                        <p class="two_info_row"><?php echo esc_html(sprintf(__('Not optimized pages: %s', 'tenweb-speed-optimizer'), (int) $this->notoptimized_pages_count)); ?></p>
                        <p class="two_status_title"><?php echo esc_html(sprintf(__('%s is ON', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
                        <div class="two_plan_container">
                            <p><?php echo sprintf(__('Current Plan: %s', 'tenweb-speed-optimizer'), esc_html($this->current_plan)); ?></p>
                            <a href="#" class="two_clear_cache"><?php _e('Clear cache', 'tenweb-speed-optimizer'); ?></a>
                        </div>
                        <hr>

                        <div class="two_score_success_container">
                            <div class="two_score_title_container">
                                <p class="two_score_title"><?php _e('Your optimized homepage score:', 'tenweb-speed-optimizer'); ?></p>
                                <div class="two_reanalyze_container">
                                    <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
                                    <a onclick="two_reanalyze_score(this)" data-from-wp-admin="1" data-post_id="front_page"
                                       data-initiator="admin-bar" class="two_reanalyze_button">
                                        <?php $reanalyze_button_status_current ? _e('Reanalyzing...', 'tenweb-speed-optimizer') : _e('Reanalyze', 'tenweb-speed-optimizer'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php $this->two_score_circles($front_score_data, 'front_page'); ?>

                            <div class="two_pages_count_info <?php echo esc_attr($free_reached) ? 'two_free_reached' : ''; ?>">
                                <p>
                                    <?php
                                    if (!$free_reached) {
                                        echo sprintf(__('Optimize %s more pages within the Free Plan limit.', 'tenweb-speed-optimizer'), (int) $rest_page_count);
                                    } else {
                                        _e('You have reached the Free plan limit.', 'tenweb-speed-optimizer'); ?>
                                </p>
                                <p><?php _e('6 of 6', 'tenweb-speed-optimizer'); ?></p>
                                <div class="two_red_counter_line"></div>
                                <?php
                                    } ?>
                            </div>
                        </div>
                        <div class="two_optimized_pages_info">
                            <p><?php _e('Optimized pages', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php echo sprintf(__('%s of %s', 'tenweb-speed-optimizer'), (int) $this->optimized_pages_count, (int) $this->total_pages_count); ?></p>
                        </div>
                        <div class="two_optimized_images_info">
                            <p><?php _e('Optimized images', 'tenweb-speed-optimizer'); ?></p>
                            <?php if (empty($optimized_images_count) && empty($total_images_count)) { ?>
                                <p class="<?php echo esc_attr($this->empty_images_count_transient); ?>"><?php _e('0', 'tenweb-speed-optimizer'); ?></p>
                            <?php } else { ?>
                                <p><?php echo sprintf(__('%s of %s', 'tenweb-speed-optimizer'), (int) $optimized_images_count, (int) $total_images_count); ?></p>
                            <?php } ?>
                        </div>
                        <?php if (!$free_reached) {
                                        $url = admin_url('edit.php?post_type=page'); ?>
                            <a href="<?php echo esc_url($url); ?>" class="two_add_page_button"><?php _e('Optimize more pages', 'tenweb-speed-optimizer'); ?></a>
                        <?php
                                    } ?>
                    </div>
                    <?php
                    $checkout_url = TENWEB_DASHBOARD . '/websites/' . $this->domain_id . '/booster/pro';
                    $current_ts = time();
                    $deadline_ts = mktime(0, 0, 0, 11, 29, 2022);
                    $black_friday_on = true;

                    if ($current_ts > $deadline_ts) {
                        $black_friday_on = false;
                    }

                    if ($black_friday_on) {
                        $black_friday_upgrade_button = trim(TENWEB_DASHBOARD, '/') . '/upgrade-plan'
                            . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN . '?two_comes_from=adminBarAfterLimit';
                        $black_friday_total_pages = (int) $this->total_pages_count;
                        $black_friday_total_images = (int) $total_images_count; ?>
                        <div class="two_pro_container two_black_friday_offer">
                            <?php require __DIR__ . '/views/two_black_friday.php'; ?>
                        </div>
                    <?php
                    } else {
                        ?>
                        <div>
                            <div class="two_pro_container">
                                <p class="two_pro_container_title"><?php _e('Achieve more with Booster Pro', 'tenweb-speed-optimizer'); ?></p>
                                <p class="two_pro_option two_pro_option_diamond"><?php echo sprintf(__('Auto-optimize all %s pages and %s images', 'tenweb-speed-optimizer'), (int) $this->total_pages_count, (int) $total_images_count); ?></p>
                                <p class="two_pro_option two_pro_option_diamond"><?php _e('Pro optimization with Cloudflare CDN', 'tenweb-speed-optimizer'); ?></p>
                                <p class="two_pro_option"><?php _e('50% faster load times', 'tenweb-speed-optimizer'); ?></p>
                                <p class="two_pro_option"><?php _e('30% higher PageSpeed score', 'tenweb-speed-optimizer'); ?></p>
                                <p class="two_pro_option"><?php _e('275 caching locations worldwide', 'tenweb-speed-optimizer'); ?></p>
                                <div class="two_pages_count_info two_agency_plan_intro">
                                    <p><?php _e('Introducing our new Agency plan:', 'tenweb-speed-optimizer'); ?></p>
                                    <p><?php _e('Optimize unlimited number of websites.', 'tenweb-speed-optimizer'); ?></p>
                                </div>
                                <a href="<?php echo esc_url($checkout_url . '?two_comes_from=adminBarAfterLimit'); ?>" target="_blank" class="two_add_page_button"><?php _e('Upgrade', 'tenweb-speed-optimizer'); ?></a>
                            </div>
                        </div>
                        <?php
                    }
                } elseif ($this->two_booster_status == 2) {
                    $this->two_booster_testmode_content();
                } elseif ($this->two_booster_status == 3) {
                    $this->two_booster_disconnect_content();
                } elseif ($this->two_booster_status == 6) {
                    $this->two_booster_reonnect_content();
                } elseif ($this->two_booster_status == 4) {
                    $this->two_booster_pro_content();
                } elseif ($this->two_booster_status == 5) {
                    $this->two_booster_abandoned_content();
                }
            } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /* Adminbar menu content in case of booster disconnected */
    public function two_booster_disconnect_content()
    {
        $care_url = TENWEB_DASHBOARD . '/websites/?open=livechat';

        if (!\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
            $care_url = '#';
            add_action('admin_footer', function () {
               $this->two_contact_care_popup();
            });
            add_action('wp_footer', function () {
                $this->two_contact_care_popup();
            });
        } ?>
        <div class="two_admin_bar_menu_content two_booster_disconnect">
            <p class="two_status_title"><?php echo esc_html(sprintf(__('%s is disconnected', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php echo esc_html(sprintf(__('Your website is disconnected from %s service.', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php
            esc_html_e('Please reconnect your website or ', 'tenweb-speed-optimizer'); ?>
            <a href="<?php echo esc_url($care_url); ?>" class="two-open-contact-care-team" target="_blank">
            <?php esc_html_e('contact our Customer Care Team', 'tenweb-speed-optimizer'); ?></a>
           <?php esc_html_e(' for further assistance.', 'tenweb-speed-optimizer'); ?></p>
        </div>
        <?php
    }

    /* Adminbar menu content in case of booster needs reconnect */
    public function two_booster_reonnect_content()
    {
        $two_disconnect_nonce = wp_create_nonce('two_disconnect_nonce');
        $two_reconnect_nonce = wp_create_nonce('two_reconnect_nonce');
        $query_args['two_disconnect'] = 1;
        $query_args['two_reconnect'] = 1;
        $query_args['two_reconnect_nonce'] = $two_reconnect_nonce;
        $query_args['nonce'] = $two_disconnect_nonce;
        $reconnect_link = add_query_arg(
            $query_args,
            get_admin_url() . 'admin.php?page=two_settings_page'
        );
        update_option(TW_OPTIMIZE_PREFIX . '_reconnection_bar_was_shown', 1, false); ?>
        <div class="two_admin_bar_menu_content two_booster_disconnect">
            <p class="two_status_title"><?php echo esc_html(sprintf(__('%s is disconnected', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php echo wp_kses_post(sprintf(__('Looks like there might be some problems with<br> the %s connection.', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php
                echo esc_html(sprintf(__('Reconnect to %s to take full advantage of its features.', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME)); ?>
            </p>
            <a href="<?php echo esc_url($reconnect_link); ?>" class="two_add_page_button two_button_small"><?php _e('Reconnect', 'tenweb-speed-optimizer'); ?></a>
        </div>
        <?php
    }

    private function two_contact_care_popup()
    {
        $main_class = 'two-contact-care-popup-main two-hidden';
        $close_icon = true;
        require_once __DIR__ . '/views/customer_support.php';
        customer_care_html($main_class, $close_icon);
    }

    /* Adminbar menu content in case of booster active in test mode */
    public function two_booster_testmode_content()
    {
        $settings_url = TENWEB_DASHBOARD . '/websites/' . $this->domain_id . '/booster/frontend?tab=settings'; ?>
        <div class="two_admin_bar_menu_content two_booster_test">
            <p class="two_status_title"><?php echo esc_html(sprintf(__('%s is in Test mode', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php echo wp_kses_post(sprintf(__('Test mode temporarily disables %s <br>for website visitors.', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <p><?php _e('Go to 10Web dashboard to manage Test mode settings and preview optimization levels.', 'tenweb-speed-optimizer'); ?></p>
            <a href="<?php echo esc_url($settings_url); ?>" target="_blank" class="two_add_page_button"><?php _e('Manage settings', 'tenweb-speed-optimizer'); ?></a>
        </div>
        <?php
    }

    /* Adminbar if booster plugin is PRO content */
    public function two_booster_pro_content()
    {
        $front_score_data = get_option('two-front-page-speed');
        $db_cloudflare_page = TENWEB_DASHBOARD . '/websites/' . $this->domain_id . '/booster/cloudflare';
        $is_homepage_score = false;

        if (!empty($front_score_data) && isset($front_score_data['current_score']) && isset($front_score_data['current_score']['desktop_score'])) {
            $is_homepage_score = true;
        }
        $reanalyze_button_status_current = false;

        if (!empty($front_score_data)) {
            if (isset($front_score_data['current_score']) && isset($front_score_data['current_score']['status'])
                && $front_score_data['current_score']['status'] == 'inprogress') {
                $reanalyze_button_status_current = true;
            }
        } ?>
        <div class="two_admin_bar_menu_content two_booster_on_free two-any-reanalyzing-score-section" data-id="front_page">
            <?php if (!$is_homepage_score) { ?>
                <p class="two_info_row two_success"><?php _e('All pages are optimized', 'tenweb-speed-optimizer'); ?></p>
            <?php } ?>
            <p class="two_status_title"><?php echo esc_html(sprintf(__('%s is ON', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
            <div class="two_plan_container">
                <p><?php echo esc_html(__('Current Plan: ' . $this->current_plan, 'tenweb-speed-optimizer')); ?></p>
                <a href="#" class="two_clear_cache"><?php _e('Clear cache', 'tenweb-speed-optimizer'); ?></a>
            </div>
            <hr>
            <div class="two_score_title_container">
                <p class="two_score_title"><?php _e('Your optimized homepage score:', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_reanalyze_container">
                    <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
                    <a onclick="two_reanalyze_score(this)" data-from-wp-admin="1" data-post_id="front_page"
                       data-initiator="admin-bar" class="two_reanalyze_button">
                        <?php $reanalyze_button_status_current ? _e('Reanalyzing...', 'tenweb-speed-optimizer') : _e('Reanalyze', 'tenweb-speed-optimizer'); ?>
                    </a>
                </div>
            </div>
            <?php $this->two_score_circles($front_score_data, 'front_page'); ?>
            <div class="two_pages_count_info two_pages_count_all">
                <?php
                _e('All pages are optimized', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two_optimized_pages_info">
                <p><?php _e('Optimized pages', 'tenweb-speed-optimizer'); ?></p>
                <p><?php echo (int) $this->total_pages_count; ?></p>
            </div>
        </div>
        <?php
        global $TwoSettings;

        if (!TENWEB_SO_HOSTED_ON_10WEB && $TwoSettings->get_settings('cloudflare_cache_status') != 'on'
            && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web') {?>
            <div class="two_pro_container" style="background-image: none;">
                <div class="two-cdn-not-applied">
                    <p class="two_pro_container_title"><?php esc_html_e('Pro optimization hasn’t been applied yet', 'tenweb-speed-optimizer'); ?></p>
                    <p class="two_pro_container_desc"><?php echo wp_kses('You have upgraded to 10Web Booster Pro but<br> haven’t enabled the Pro optimization on your<br> website.', ['br' => []]); ?></p>
                </div>
                <p class="two_pro_container_title"><?php esc_html_e('Enable CDN to enjoy the benefits:', 'tenweb-speed-optimizer'); ?></p>
                <p class="two_pro_option"><?php esc_html_e('30% higher PageSpeed score', 'tenweb-speed-optimizer'); ?></p>
                <p class="two_pro_option"><?php esc_html_e('50% faster load times', 'tenweb-speed-optimizer'); ?></p>
                <p class="two_pro_option"><?php esc_html_e('275 caching locations worldwide', 'tenweb-speed-optimizer'); ?></p>
                <a href="<?php echo esc_url($db_cloudflare_page); ?>" target="_blank" class="two_add_page_button"><?php esc_html_e('Enable', 'tenweb-speed-optimizer'); ?></a>
            </div>
            <?php
        }
    }

    /* Frontend Adminbar menu content in case of page not optimized */
    public function two_front_not_optimized_content()
    {
        global $post;

        if (empty($post)) {
            return false;
        }

        $post_id = $post->ID;

        $posts_in_progress = $this->two_is_optimize_inprogress();

        if ($posts_in_progress) {
            $this->two_front_optimize_in_progress_content($post_id, true);
        } else {
            $checkout_url = TENWEB_DASHBOARD . '/websites/' . $this->domain_id . '/booster/pro'; ?>
            <div class="two_admin_bar_menu_content two_not_optimized_content">
                <p class="two_status_title"><?php echo esc_html(sprintf(__('Optimize this page with %s', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
                <p><?php echo esc_html(sprintf(__('We found that this page isn’t optimized with %s.', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
                <p><?php _e('Get a 90+ PageSpeed score, faster load times and smoother user experience by optimizing this page.', 'tenweb-speed-optimizer'); ?></p>
                <a <?php echo ($this->optimized_pages_count >= 6) ? 'href="' . esc_url($checkout_url) . '"' : 'id="two_optimize_now_button"'; ?> data-post-id="<?php echo esc_attr($post_id); ?>" data-initiator="admin-bar" target="_blank"
                                                                                                                                               class="two_add_page_button"><?php _e('Optimize', 'tenweb-speed-optimizer'); ?></a>
            </div>
            <?php
            $this->two_front_optimize_in_progress_content($post_id);
        }
    }

    /* Frontend Adminbar menu content in case of page is optimizing */
    public function two_front_optimize_in_progress_content($post_id, $optimize_inprogress = false)
    {
        ?>
        <div class="two_in_progress_cont <?php echo !$optimize_inprogress ? 'two_hidden' : ''; ?>">
            <p class="two_status_title"><?php _e('Optimization in progress…', 'tenweb-speed-optimizer'); ?></p>
            <?php
            if ($post_id == 'front_page') {
                $page_title = 'Homepage'; ?>
                <p><?php echo sprintf(__('Your %s is currently being optimized.', 'tenweb-speed-optimizer'), '<span>' . esc_html($page_title) . '</span>'); ?></p>
            <?php
            } else {
                $page_title = get_the_title($post_id); ?>
                <p><?php echo sprintf(__('Your %s page is currently being optimized.', 'tenweb-speed-optimizer'), '<span>' . esc_html($page_title) . '</span>'); ?></p>
            <?php
            } ?>
            <p><?php _e('You will receive a notification once optimization is completed.', 'tenweb-speed-optimizer'); ?></p>
        </div>
        <?php
        $this->two_empty_front_optimized_content_template($post_id);
    }

    /* Adminbar menu content in case of abandoned optimization */
    public function two_booster_abandoned_content()
    {
        $abandon_url = home_url() . '?two_setup=1'; ?>
        <div class="two_admin_bar_menu_content two_not_optimized_content">
            <p class="two_status_title"><?php _e('Optimization not finished', 'tenweb-speed-optimizer'); ?></p>
            <p><?php _e('You haven’t finished optimizing your website,<br> which means no changes were applied to your live site.', 'tenweb-speed-optimizer'); ?></p>
            <p><?php _e('Return to the flow to finish the optimization.', 'tenweb-speed-optimizer'); ?></p>
            <a href="<?php echo esc_url($abandon_url); ?>" target="_blank" class="two_add_page_button"><?php _e('Finish optimization', 'tenweb-speed-optimizer'); ?></a>
        </div>
        <?php
    }

    /* Frontend Adminbar menu content in case of page is already optimized */
    public function two_front_optimized_content()
    {
        global $post;

        if (empty($post)) {
            return false;
        }

        $post_id = $post->ID;

        if (is_front_page()) {
            $page_title = __('Homepage', 'tenweb-speed-optimizer');
        } else {
            $page_title = get_the_title($post_id);
        }

        /* Check home page */
        if (is_front_page()) {
            $score_data = get_option('two-front-page-speed');
            $post_id = 'front_page';
        } else {
            $score_data = get_post_meta($post_id, 'two_page_speed');
            $score_data = end($score_data);
        }
        $date = 0;

        if (!empty($score_data) && !isset($score_data['previous_score'])) {
            return false;
        } elseif (!empty($score_data) && isset($score_data['current_score']) && isset($score_data['current_score']['desktop_score'])) {
            $optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            if (isset($optimized_pages[$post_id]) && isset($optimized_pages[$post_id]['critical_date'])) {
                $date = $optimized_pages[$post_id]['critical_date'];
            } elseif (isset($score_data['current_score']['date'])) {
                $date = strtotime($score_data['current_score']['date']);
            }
        }
        $modified_date = get_the_modified_date('d.m.Y h:i:s a', $post_id);
        $modified_date = strtotime($modified_date);
        $posts_in_progress = $this->two_is_optimize_inprogress();

        if ($posts_in_progress) {
            $this->two_front_optimize_in_progress_content($post_id, true);
        } else { ?>
            <div class="two_admin_bar_menu_content two_optimized two-any-reanalyzing-score-section" data-id="<?php echo esc_attr($post_id); ?>">
                <p class="two_status_title"><?php _e('Congrats!', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_optimized_congrats_container">
                    <p class="two_optimized_congrats_subtitle">
                        <span class="two_optimized_congrats_title"><?php echo esc_html($page_title) . ' '; ?></span>
                        <?php echo esc_html(sprintf(
            __(' %s is successfully optimized.', 'tenweb-speed-optimizer'),
            (!is_front_page() ? 'page' : '')
        )); ?>
                    </p>
                    <?php echo wp_kses_post($this->two_check_score_improvement($score_data)); ?>
                </div>
                <div class="two_score_success_container">
                    <?php $this->two_score_circles($score_data, $post_id); ?>
                </div>

                <?php if ($modified_date > $date && $date != 0) { ?>
                    <a id="two_optimize_now_button" data-post-id="<?php echo esc_attr($post_id); ?>" data-initiator="admin-bar" target="_blank"
                       class="two_add_page_button"><?php _e('Re-optimize', 'tenweb-speed-optimizer'); ?></a>
                <?php } ?>
            </div>
            <?php
            $this->two_front_optimize_in_progress_content($post_id);
        }
    }

    public function two_empty_front_optimized_content_template($post_id)
    {
        if ($post_id == 'front_page') {
            $page_title = 'Homepage';
        } else {
            $page_title = get_the_title($post_id);
        } ?>
        <div class="two_admin_bar_menu_content two_empty_front_optimized_content two_hidden two-any-reanalyzing-score-section" data-id="<?php echo esc_attr($post_id); ?>">
            <p class="two_status_title"><?php _e('Congrats!', 'tenweb-speed-optimizer'); ?></p>
            <div class="two_optimized_congrats_container two-any-reanalyzing-score-section"  data-id="<?php echo esc_attr($post_id); ?>">
                <p class="two_optimized_congrats_subtitle">
                    <span class="two_optimized_congrats_title"><?php echo esc_html($page_title) . ' '; ?></span>
                    <?php echo esc_html(sprintf(
            __(' %s is successfully optimized.', 'tenweb-speed-optimizer'),
            (!is_front_page() ? 'page' : '')
        )); ?>
                </p>
                <?php $this->two_check_score_improvement(''); ?>
            </div>
            <?php $this->two_score_circles([], ''); ?>
        </div>
        <?php
    }

    /* Show notification during the page load if there is optimizing page in progress */
    public function two_optimize_notification()
    {
        if (is_admin() && $this->two_booster_status == 1) {
            if ($this->two_booster_status == 3 || $this->two_booster_status == 2 || $this->two_booster_status == 5) {
                return;
            }
            $data = ['optimized' => [], 'optimizing' => []];
            $post_ids = new \WP_Query([
                'post_type' => ['page', 'post'],
                'fields' => 'ids',
                // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                //                'meta_query' => array(
                //                    array(
                //                        'key'   => 'two_page_speed',
                //                    ),
                //                )
            ]);
            $post_ids = isset($post_ids->posts) ? $post_ids->posts : [];

            $two_optimization_notif_status = get_option('two_optimization_notif_status');

            foreach ($post_ids as $post_id) {
                $page_score = get_post_meta($post_id, 'two_page_speed', true);
                $page_title = get_the_title($post_id);
                $status = 'optimized';
                $critical_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

                if (\TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . $post_id)) {
                    $status = 'optimizing';
                } elseif (!array_key_exists($post_id, $critical_pages)) {
                    $status = 'notOptimized';
                }

                if (\TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . $post_id)
                    || (isset($two_optimization_notif_status[$post_id]) && $two_optimization_notif_status[$post_id] == 'optimizing')) {
                    $data['optimizing'][] = [
                        'status' => $status,
                        'post_id' => $post_id,
                        'post_title' => $page_title,
                    ];
                } elseif (!empty($page_score) && isset($page_score['current_score']) && isset($page_score['previous_score'])
                    && isset($two_optimization_notif_status[$post_id]) && $two_optimization_notif_status[$post_id] == 'optimized_not_closed') {
                    $data['optimized'][] = [
                        'status' => $status,
                        'post_id' => $post_id,
                        'post_title' => $page_title,
                        'mobile_new' => $page_score['current_score']['mobile_score'] ?? '',
                        'mobile_loading_time_new' => $page_score['current_score']['mobile_tti'] ?? '',
                        'desktop_new' => $page_score['current_score']['desktop_score'] ?? '',
                        'desktop_loading_time_new' => $page_score['current_score']['desktop_tti'] ?? '',
                        'mobile_old' => $page_score['previous_score']['mobile_score'] ?? '',
                        'mobile_loading_time_old' => $page_score['previous_score']['mobile_tti'] ?? '',
                        'desktop_old' => $page_score['previous_score']['desktop_score'] ?? '',
                        'desktop_loading_time_old' => $page_score['previous_score']['desktop_tti'] ?? '',
                    ];
                }
            }

            if (empty($data['optimized']) && empty($data['optimizing'])) {
                return;
            }
            ob_start(); ?>
            <div class="two_admin_bar_menu_main_notif <?php echo empty($data['optimized']) ? 'two_hidden' : ''; ?>">
                <?php
                $i = 1;

            foreach ($data['optimized'] as $optimized) {
                $score_data = [
                    'previous_score' => [
                        'mobile_score' => $optimized['mobile_old'],
                        'mobile_tti' => $optimized['mobile_loading_time_old'],
                        'desktop_score' => $optimized['desktop_old'],
                        'desktop_tti' => $optimized['desktop_loading_time_old'],
                    ],
                    'current_score' => [
                        'mobile_score' => $optimized['mobile_new'],
                        'mobile_tti' => $optimized['mobile_loading_time_new'],
                        'desktop_score' => $optimized['desktop_new'],
                        'desktop_tti' => $optimized['desktop_loading_time_new']
                    ],
                ]; ?>
                    <div class="two_admin_bar_menu_content two_optimized">
                        <span class="two_admin_bar_menu_main_notif_optimized_close" data-post_id="<?php echo esc_attr($optimized['post_id']); ?>"></span>
                        <div class="two_optimized_cont two-any-reanalyzing-score-section" data-id="<?php echo esc_attr($optimized['post_id']); ?>">
                            <div class="two_optimized_congrats_row">
                                <p class="two_status_title"><?php _e('Congrats!', 'tenweb-speed-optimizer'); ?></p>
                                <?php if (count($data['optimized']) > 1) { ?>
                                <span class="two_arrow <?php echo ($i == 1) ? 'two_up_arrow' : 'two_down_arrow'; ?>"></span>
                                <?php } ?>
                            </div>
                            <div class="two_optimized_congrats_container">
                                <p class="two_optimized_congrats_subtitle">
                                    <span class="two_optimized_congrats_title"><?php echo esc_html($optimized['post_title'] . ' '); ?></span>
                                    <?php echo esc_html(sprintf(
                    __(' %s is successfully optimized.', 'tenweb-speed-optimizer'),
                    (!is_front_page() ? 'page' : '')
                )); ?>
                                </p>
                                <?php echo wp_kses_post($this->two_check_score_improvement($score_data)); ?>
                            </div>
                            <div class="two_score_block_container <?php echo ($i == 1) ? '' : 'two_hidden'; ?>">
                                <?php
                                $this->two_score_circles($score_data, $optimized['post_id']); ?>
                            </div>
                        </div>
                <?php if ($this->referral_hash) { ?>
                        <div class="two_get_referral_link_admin_bar">
                            <p class="two_get_referral_title">
                                <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/shape.png'); ?>">
                                <?php _e('Refer a friend and receive a $20 credit for the first referral and $10 credit for each additional one.', 'tenweb-speed-optimizer'); ?>
                            </p>
                            <a class="two-link-to-wp-referral" href="<?php echo esc_url(admin_url('admin.php?page=two_referral_program')); ?>">
                                <?php esc_html_e('Get Referral link', 'tenweb-speed-optimizer'); ?>
                            </a>
                        </div>
                    <?php } ?>
                    </div>
                    <?php
                    $i++;
            } ?>
                <?php foreach ($data['optimizing'] as $optimizing) { ?>
                    <div class="two_admin_bar_menu_content two_optimizing_container" data-post_id="<?php echo esc_attr($optimizing['post_id']); ?>">
                        <p class="two_optimizing_title"><span></span><?php _e('Optimization in progress…', 'tenweb-speed-optimizer'); ?></p>
                        <p><?php echo sprintf(__('Your %s page is currently being optimized.', 'tenweb-speed-optimizer'), '<span>' . esc_html($optimizing['post_title']) . '</span>'); ?></p>
                    </div>
                <?php } ?>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    /* Get website images total count and optimized images count from endpoint */
    public function get_images_data_api()
    {
        $two_images_count = \TenWebWpTransients\OptimizerTransients::get('two_images_count');

        if (!empty($two_images_count)) {
            $this->optimized_images_count = $two_images_count['optimized_images_count'];
            $this->total_images_count = $two_images_count['total_images_count'];
            $this->empty_images_count_transient = '';
        } else {
            $this->empty_images_count_transient = 'two-adminBar two_empty_images_count';
        }
    }

    /* Check if current frontend page is optimized */
    public function two_is_page_optimized()
    {
        global $post;

        if (empty($post)) {
            return false;
        }
        $post_id = $post->ID;

        if (is_front_page()) {
            $post_id = 'front_page';
        }
        $optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

        if (isset($optimized_pages[$post_id])) {
            return true;
        }

        return false;
    }

    /* Check current page optimization in progress */
    public function two_is_optimize_inprogress()
    {
        global $post;

        if (empty($post)) {
            return false;
        }
        $post_id = $post->ID;

        if (is_front_page()) {
            $post_id = 'front_page';
        }

        if (\TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . $post_id)) {
            return true;
        }

        return false;
    }

    /* Before and After score template */
    public function two_score_circles($score_data, $post_id)
    {
        $reanalyze_button_status_previous = false;
        $reanalyze_button_status_current = false;

        if (!empty($score_data)) {
            if (isset($score_data['current_score']) && isset($score_data['current_score']['status'])
                && $score_data['current_score']['status'] == 'inprogress') {
                $reanalyze_button_status_current = true;
            }

            if (isset($score_data['previous_score']) && isset($score_data['previous_score']['status'])
                && $score_data['previous_score']['status'] == 'inprogress') {
                $reanalyze_button_status_previous = true;
            }
        } ?>
        <div class="two_score_block">
            <div class="two_score_block_left">
                <p class="two_score_block_title two_score_title_adminbar"><?php _e('Before optimization', 'tenweb-speed-optimizer'); ?></p>
                <?php if (empty($score_data) || !isset($score_data['previous_score'])
                    || !isset($score_data['previous_score']['desktop_score']) || $reanalyze_button_status_previous) {
            $no_old_scores = 'two-no-scores';
        } ?>
                <div class="two_score_container_both two-old-scores <?php echo isset($no_old_scores) ? esc_html($no_old_scores) : ''; ?>"
                     data-no-score-for="<?php echo $reanalyze_button_status_previous ? esc_attr($post_id) : ''; ?>">
                    <div class="two_score_container two_score_container_mobile_old">
                        <div class="two-score-circle" data-score="<?php echo isset($no_old_scores) ? '' : (int) $score_data['previous_score']['mobile_score']; ?>" data-size="30"
                             data-thickness="2" data-id="mobile">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($score_data['previous_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <div class="two_score_container two_score_container_desktop_old">
                        <div class="two-score-circle" data-score="<?php echo isset($no_old_scores) ? '' : (int) $score_data['previous_score']['desktop_score']; ?>" data-size="30"
                             data-thickness="2" data-id="desktop">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($score_data['previous_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <?php if ($post_id != '') { ?>
                        <a onclick="<?php echo 'two_reanalyze_score(this)'; ?>" data-from-wp-admin="1" data-post_id="<?php echo esc_attr($post_id); ?>" target="_blank"
                           data-initiator="admin-bar" class="two_reanalyze_link <?php echo $reanalyze_button_status_previous ? 'two-hidden' : ''; ?>">
                        </a>
                        <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_previous ? '' : 'two-hidden'; ?>"></span>
                    <?php } ?>
                </div>
            </div>
            <div class="two_score_block_right">
                <p class="two_score_block_title two_score_title_adminbar"><?php echo esc_html(sprintf(__('After %s optimization', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
                <?php if (empty($score_data) || !isset($score_data['current_score'])
                    || !isset($score_data['current_score']['desktop_score']) || $reanalyze_button_status_current) {
            $no_new_scores = 'two-no-scores';
        } ?>
                <div class="two_score_container_both two-new-scores <?php echo isset($no_new_scores) ? esc_html($no_new_scores) : ''; ?>"
                     data-no-score-for="<?php echo $reanalyze_button_status_current ? esc_attr($post_id) : ''; ?>">
                    <div class="two_score_container two_score_container_mobile">
                        <div class="two-score-circle" data-score="<?php echo isset($no_new_scores) ? '' : (int) $score_data['current_score']['mobile_score']; ?>" data-size="30"
                             data-thickness="2" data-id="mobile">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($score_data['current_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <div class="two_score_container two_score_container_desktop">
                        <div class="two-score-circle" data-score="<?php echo isset($no_new_scores) ? '' : (int) $score_data['current_score']['desktop_score']; ?>" data-size="30"
                             data-thickness="2" data-id="desktop">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($score_data['current_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <?php if ($post_id != '') { ?>
                        <a onclick="<?php echo 'two_reanalyze_score(this)'; ?>" data-from-wp-admin="1" data-post_id="<?php echo esc_attr($post_id); ?>" target="_blank"
                           data-initiator="admin-bar" class="two_reanalyze_link <?php echo $reanalyze_button_status_current ? 'two-hidden' : ''; ?>">
                        </a>
                        <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php
    }

    public function two_check_score_improvement($score_data)
    {
        $improvement_sec = '';

        if ($score_data == '') {
            $improvement_sec = '';
        } else {
            if (!empty($score_data) && isset($score_data['current_score']) && isset($score_data['previous_score'])
                && isset($score_data['current_score']['desktop_score']) && isset($score_data['previous_score']['desktop_score'])
                && (float) $score_data['previous_score']['desktop_score'] != 0 && (float) $score_data['previous_score']['mobile_score'] != 0) {
                /* score improvement calculation */
                $desktopScoreImprove = (((float) $score_data['current_score']['desktop_score']
                            - (float) $score_data['previous_score']['desktop_score']) / (float) $score_data['previous_score']['desktop_score']) * 100;
                $mobileScoreImprove = (((float) $score_data['current_score']['mobile_score']
                            - (float) $score_data['previous_score']['mobile_score']) / (float) $score_data['previous_score']['mobile_score']) * 100;
                $maxScore = max($desktopScoreImprove, $mobileScoreImprove);
                $showImproveBadge = round($maxScore) > 20;
                $improvedPercent = round($maxScore) > 20 ? round($maxScore) : 0;

                if ($showImproveBadge) {
                    $improvement_sec = '<p class="two_optimized_improvement">Improved<span>' . esc_html($improvedPercent) . '%' . '</span></p>';
                }
            }
        }

        return $improvement_sec;
    }
}
