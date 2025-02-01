<?php

$two_disconnect_nonce = wp_create_nonce('two_disconnect_nonce');
$two_domain_id = get_site_option('tenweb_domain_id');
$two_manage_url = trim(TENWEB_DASHBOARD, '/') . '/websites/' . $two_domain_id . '/booster/frontend' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_upgrade_link = trim(TENWEB_DASHBOARD, '/') . '/upgrade-plan' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_upgrade_link_pricing = trim(TENWEB_DASHBOARD, '/') . '/upgrade-plan' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_wp_plugin_url = 'https://wordpress.org/support/plugin/tenweb-speed-optimizer/';
$two_disconnect_link = get_admin_url() . 'admin.php?page=two_settings_page&two_disconnect=1&nonce=' . $two_disconnect_nonce;
$two_current_user = wp_get_current_user();
$username = get_site_option(TENWEB_PREFIX . '_user_info') ? get_site_option(TENWEB_PREFIX . '_user_info')['client_info']['name'] : $two_current_user->display_name;
$two_flow_finished = get_option('two_flow_status') != 1 ? true : false;
$db_cloudflare_page = TENWEB_DASHBOARD . '/websites/' . $two_domain_id . '/booster/cloudflare';

if (\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
    $two_plan_name = __('Paid Plan', 'tenweb-speed-optimizer');

    if (TENWEB_SO_HOSTED_ON_10WEB) {
        $two_plan_description_1 = __('The plugin is now optimizing your website.', 'tenweb-speed-optimizer');
        $two_plan_description_2 = __('', 'tenweb-speed-optimizer');
    } else {
        $two_plan_description_1 = __('The plugin is now optimizing your website.', 'tenweb-speed-optimizer');
        $two_plan_description_2 = __('Manage optimization settings and assign custom rules from the ' . TWO_SO_ORGANIZATION_NAME . ' dashboard.', 'tenweb-speed-optimizer');

        if (strtolower(TWO_SO_ORGANIZATION_NAME) != '10web') {
            $two_plan_description_2 = '';
        }
    }
    $two_contact_text = __('Please contact our support via', 'tenweb-speed-optimizer');
    $two_contact_link_text = __('Live Chat', 'tenweb-speed-optimizer');
    $two_contact_link = $two_manage_url . '&open=livechat';
    $full_cont = 'two-full-width';
    $half_cont = 'two-half-width';
} else {
    $two_plan_name = __('Free Plan', 'tenweb-speed-optimizer');
    $two_plan_description_1 = __('The plugin is now optimizing your website.', 'tenweb-speed-optimizer');
    $two_plan_description_2 = __('Manage optimization settings from the ' . TWO_SO_ORGANIZATION_NAME . ' dashboard.', 'tenweb-speed-optimizer');

    if (strtolower(TWO_SO_ORGANIZATION_NAME) != '10web') {
        $two_plan_description_2 = '';
    }
    $two_contact_text = __('Please create a topic in', 'tenweb-speed-optimizer');
    $two_contact_link_text = __('WordPress.org', 'tenweb-speed-optimizer');
    $two_contact_link = $two_wp_plugin_url;
    $full_cont = '';
    $half_cont = '';
}
$two_finish_opt_url = add_query_arg(['two_setup' => 1], get_home_url());
$compressed_pages = count(\TenWebOptimizer\OptimizerUtils::getCriticalPages());
$images_count = \TenWebWpTransients\OptimizerTransients::get('two_images_count');
$compressed_iamges = (!empty($images_count) && isset($images_count['optimized_images_count'])) ? (int) $images_count['optimized_images_count'] : '';
$empty_images_count_transient = empty($images_count) ? 'two-settings-basic two_empty_images_count' : '';
$free_plan_limit = 6;

if ($compressed_pages >= $free_plan_limit) {
    $reached_the_limit = 'two-reached-limit';
    $limit_text = __('You have reached the Free plan limit.', 'tenweb-speed-optimizer');
} else {
    $reached_the_limit = '';
    $left_pages = $free_plan_limit - $compressed_pages;
    $single = __('You can optimize %d more page within%sthe Free Plan limit.', 'tenweb-speed-optimizer');
    $plural = __('You can optimize %d more pages within%sthe Free Plan limit.', 'tenweb-speed-optimizer');
    $limit_text = wp_sprintf(_n($single, $plural, $left_pages, 'photo-gallery'), $left_pages, '<br />');
}
?>
<script>
  jQuery(document).ready(function() {
    jQuery('.two-faq-item').on('click', function() {
      jQuery(this).toggleClass('active');
    });
    jQuery('.two-disconnect-link a').on('click', function() {
      jQuery('.two-disconnect-popup').appendTo('body').addClass('open');
      return false;
    });
    jQuery('.two-button-cancel, .two-close-img').on('click', function() {
      jQuery('.two-disconnect-popup').removeClass('open');
      return false;
    });
  });
</script>
<div class="two-container connected" dir="ltr">
  <?php include_once 'two_header.php';
        $banner = new \TenWebOptimizer\OptimizerBanner();
        $banner->NPSBannerPluginPage();
        ?>
  <div class="two-body-container">
    <?php
    global $TwoSettings;

    if (!TENWEB_SO_HOSTED_ON_10WEB && $TwoSettings->get_settings('cloudflare_cache_status') != 'on'
        && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web' && \TenWebOptimizer\OptimizerUtils::is_paid_user()) { ?>
        <div class="two-cdn-not-applied two-main-text">
            <?php echo wp_kses(
            __(
                '<b>Pro optimization hasn’t been applied yet.</b>
                You have upgraded to 10Web Booster Pro but haven’t enabled<br> the Pro optimization on your website. ',
                'tenweb-speed-optimizer'
            ),
            [ 'a' => [], 'br' => [], 'b' => [] ]
        )
                . '<a href="' . esc_url($db_cloudflare_page) . '">' . esc_html__('Enable CDN', 'tenweb-speed-optimizer')
                . '</a>' . esc_html__(' to enjoy the benefits.', 'tenweb-speed-optimizer'); ?>
        </div>
    <?php }

    if ($two_flow_finished) {
        ?>
      <div class="two-body">
        <div class="two-greeting">
          <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/waving_hand.png" alt="Hey" class="two-waving-hand"/>
          <?php if (TENWEB_SO_HOSTED_ON_10WEB) { ?>
            <?php _e('Hey there!', 'tenweb-speed-optimizer'); ?>
          <?php } else {?>
              <?php if (strtolower(TWO_SO_ORGANIZATION_NAME) != '10web') { ?>
                  <?php echo esc_html(sprintf(__('Hey %s!', 'tenweb-speed-optimizer'), $username, $two_plan_name)); ?>
              <?php } else { ?>
                  <?php echo esc_html(sprintf(__('Hey %s! You are on a %s.', 'tenweb-speed-optimizer'), $username, $two_plan_name)); ?>
              <?php }?>
          <?php } ?>
        </div>
        <div class="two-plugin-status">
          <?php if (TENWEB_SO_HOSTED_ON_10WEB) { ?>
              <?php _e(esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster is Active', 'tenweb-speed-optimizer'); ?>
          <?php } else {?>
              <?php _e(esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster is Active', 'tenweb-speed-optimizer'); ?>
          <?php } ?>
        </div>
        <div class="two-plugin-description">
          <?php echo esc_html($two_plan_description_1); ?>
          <br/>
          <?php echo esc_html($two_plan_description_2); ?>
        </div>
        <?php if (!TENWEB_SO_HOSTED_ON_10WEB && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web') { ?>
          <a href="<?php echo esc_url($two_manage_url); ?>" target="_blank"
             class="two-button two-button-green"><?php _e('MANAGE', 'tenweb-speed-optimizer'); ?></a>
        <?php } ?>
          <?php if (strtolower(TWO_SO_ORGANIZATION_NAME) !== '10web') { ?>
              <a href="#" target="_blank"
                 class="two-button two-button-green two_clear_cache two_cache_button">
                  <?php _e('Clear Cache', 'tenweb-speed-optimizer'); ?>
              </a>
          <?php } ?>
      </div>
      <?php
    } else {
        ?>
      <div class="two-body">
        <div class="two-not-finished-notice">
          <?php _e('Optimization not finished', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-plugin-status">
          <?php _e('Complete your website optimization.', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-plugin-description">
          <?php _e('Your website wasn’t optimized.', 'tenweb-speed-optimizer'); ?>
          <br/>
          <?php _e('Please <a href="' . esc_url(TENWEB_DASHBOARD) . '?flow_contact_us=1' . '">contact our support team</a> to complete the optimization.', 'tenweb-speed-optimizer'); ?>
          <br/>
          <?php _e('If you already have, we’ll be in touch soon.', 'tenweb-speed-optimizer'); ?>
        </div>
        <?php if (!TENWEB_SO_HOSTED_ON_10WEB) { ?>
          <a href="<?php echo esc_url($two_finish_opt_url); ?>" target="_blank" class="two-button two-button-green">
            <?php _e('FINISH OPTIMIZATION', 'tenweb-speed-optimizer'); ?>
          </a>
        <?php } ?>
      </div>
      <?php
    }

    if ((!TENWEB_SO_HOSTED_ON_10WEB && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web')) {
        if ($two_flow_finished) {
            $score = get_option('two-front-page-speed');

            $reanalyze_button_status_previous = false;
            $reanalyze_button_status_current = false;

            if (!empty($score)) {
                if (isset($score['current_score']) && isset($score['current_score']['status'])
              && $score['current_score']['status'] == 'inprogress') {
                    $reanalyze_button_status_current = true;
                }

                if (isset($score['previous_score']) && isset($score['previous_score']['status'])
              && $score['previous_score']['status'] == 'inprogress') {
                    $reanalyze_button_status_previous = true;
                }
            }
            $post_id = 'front_page'; // phpcs:ignore ?>
      <div class="two-optimized-homepage-and-available-pro-container two-any-reanalyzing-score-section <?php echo esc_attr($full_cont); ?>" data-id="<?php echo esc_attr($post_id); ?>">
        <div class="two-section-with-border two-optimized-homepage-container <?php echo esc_attr($full_cont); ?>">
          <div class="two-optimized-homepage-header">
            <p class="two-settings_title">
              <?php _e('Your optimized homepage score:', 'tenweb-speed-optimizer'); ?>
            </p>
            <p class="two-settings_title two-cache-link two_clear_cache">
              <?php _e('Clear cache', 'tenweb-speed-optimizer'); ?>
            </p>
          </div>
        <div class="two-homepage-scores two_score_block">
            <div class="two_score_block_left">
                <p class="two_score_block_title"><?php _e('Before optimization', 'tenweb-speed-optimizer'); ?></p>
                <?php if (empty($score) || !isset($score['previous_score'])
                    || !isset($score['previous_score']['desktop_score']) || $reanalyze_button_status_previous) {
                $no_old_scores = 'two-no-scores';
            } ?>
                <div class="two_score_container_both two-old-scores <?php echo isset($no_old_scores) ? esc_html($no_old_scores) : ''; ?>"
                     data-no-score-for="<?php echo $reanalyze_button_status_current ? esc_attr($post_id) : ''; ?>">
                    <div class="two_score_container two_score_container_mobile_old">
                        <div class="two-score-circle two_circle_with_bg" data-score="<?php echo isset($no_old_scores) ? '' : (int) $score['previous_score']['mobile_score']; ?>" data-size="40"
                             data-thickness="2" data-id="mobile">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($score['previous_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <div class="two_score_container two_score_container_desktop_old">
                        <div class="two-score-circle two_circle_with_bg" data-score="<?php echo isset($no_old_scores) ? '' : (int) $score['previous_score']['desktop_score']; ?>" data-size="40"
                             data-thickness="2" data-id="desktop">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($score['previous_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
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
                <p class="two_score_block_title"><?php echo sprintf(__('After %s optimization', 'tenweb-speed-optimizer'), esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster'); ?></p>
                <?php if (empty($score) || !isset($score['current_score'])
                    || !isset($score['current_score']['desktop_score']) || $reanalyze_button_status_current) {
                $no_new_scores = 'two-no-scores';
            } ?>
                <div class="two_score_container_both two-new-scores <?php echo isset($no_new_scores) ? esc_html($no_new_scores) : ''; ?>"
                     data-no-score-for="<?php echo $reanalyze_button_status_current ? esc_attr($post_id) : ''; ?>">
                    <div class="two_score_container two_score_container_mobile">
                        <div class="two-score-circle two_circle_with_bg" data-score="<?php echo isset($no_new_scores) ? '' : (int) $score['current_score']['mobile_score']; ?>" data-size="40"
                             data-thickness="2" data-id="mobile">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($score['current_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                        </div>
                    </div>
                    <div class="two_score_container two_score_container_desktop">
                        <div class="two-score-circle two_circle_with_bg" data-score="<?php echo isset($no_new_scores) ? '' : (int) $score['current_score']['desktop_score']; ?>" data-size="40"
                             data-thickness="2" data-id="desktop">
                            <span class="two-score-circle-animated"></span>
                        </div>
                        <div class="two_score_info">
                            <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                            <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($score['current_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
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
        <div class="two_reanalyze_container">
            <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
            <a onclick="two_reanalyze_score(this)" data-from-wp-admin="1" data-post_id="front_page"
               data-initiator="admin-bar" class="two_reanalyze_button">
                <?php $reanalyze_button_status_current ? _e('Reanalyzing...', 'tenweb-speed-optimizer') : _e('Reanalyze', 'tenweb-speed-optimizer'); ?>
            </a>
        </div>
          <?php
          if (!\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
              ?>
          <div class="two-optimized-homepage-notice <?php echo esc_attr($reached_the_limit); ?>">
              <div>
            <?php echo wp_kses_post($limit_text); ?>
              </div>
              <?php if (!\TenWebOptimizer\OptimizerUtils::is_paid_user() && $reached_the_limit == 'two-reached-limit') { ?>
                      <div><?php _e('6 of 6', 'tenweb-speed-optimizer'); ?></div>
                  <div class="two-line_container"></div>
              <?php } ?>
          </div>
            <?php
          } ?>
          <div class="two-optimiziation-info">
            <div class="two-optimized-page">
              <p class="two-settings_title two-title-with-dot">
                <?php _e('Optimized pages', 'tenweb-speed-optimizer'); ?>
              </p>
              <p class="two-settings_title">
                <?php
                $terms_count = (int) get_terms(['fields' => 'count', 'hide_empty' => false]);
            $total_pages = wp_count_posts('page')->publish + wp_count_posts('post')->publish + $terms_count;

            if (\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
                echo sprintf(__('%d of %d', 'tenweb-speed-optimizer'), esc_html($total_pages), esc_html($total_pages));
            } else {
                echo sprintf(__('%d of %d', 'tenweb-speed-optimizer'), esc_html($compressed_pages), esc_html($total_pages));
            } ?>
              </p>
            </div>
            <div class="two-optimized-images">
              <p class="two-settings_title two-title-with-dot">
                <?php _e('Optimized images', 'tenweb-speed-optimizer'); ?>
              </p>
              <p class="two-settings_title <?php echo esc_attr($empty_images_count_transient); ?>">
                <?php echo esc_html($compressed_iamges); ?>
              </p>
            </div>
          </div>
        </div>
        <?php
       if (!\TenWebOptimizer\OptimizerUtils::is_paid_user()) {
           $current_ts = time();
           $deadline_ts = mktime(0, 0, 0, 11, 29, 2022);
           $black_friday_on = true;

           if ($current_ts > $deadline_ts) {
               $black_friday_on = false;
           }

           if ($black_friday_on) {
               $black_friday_upgrade_button = $two_upgrade_link_pricing . '?two_comes_from=MainPageUpgradeButton';
               $black_friday_total_pages = (int) $total_pages;
               $black_friday_total_images = (int) $images_count['total_images_count']; ?>
            <div class="two-section-with-border two-get-pro-container two_black_friday_offer">
                <?php require __DIR__ . '/two_black_friday.php'; ?>
            </div>
        <?php
           } else { ?>
            <div class="two-section-with-border two-get-pro-container">
              <p class="two-settings_title">
                  <?php _e('Achieve more with Booster Pro', 'tenweb-speed-optimizer'); ?>
              </p>
              <div class="two-available-pro-listing">
                  <ul class="two-available-pro-list">
                      <li class="two-available-pro-list-each-diamond two-settings_title">
                          <?php _e('Full frontend optimization', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php $total_images_count = (is_array($images_count) && isset($images_count['total_images_count'])) ? (int) $images_count['total_images_count'] : 0; ?>
                          <?php echo esc_html(sprintf(__('%s pages and all %s images', 'tenweb-speed-optimizer'), (int) $total_pages, $total_images_count)); ?>
                      </li>
                      <li class="two-available-pro-list-each-diamond two-settings_title">
                          <?php _e('Optimization of unlimited new pages and images', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-diamond two-settings_title">
                          <?php _e('Pro optimization with Cloudflare CDN', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('50% faster load times', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('30% higher PageSpeed score', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('275 caching locations worldwide', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('DDoS protection and WAF', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('Full page caching', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-point two-settings_title">
                          <?php _e('SSL certificate', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-diamond two-settings_title">
                          <?php _e('Automatic caching and cache warmup', 'tenweb-speed-optimizer'); ?>
                      </li>
                      <li class="two-available-pro-list-each-diamond two-settings_title">
                          <?php _e('24/7 priority customer support', 'tenweb-speed-optimizer'); ?>
                      </li>
                  </ul>
              </div>
              <div class="two-available-pro-button-container">
                <a href="<?php echo esc_url($two_upgrade_link) . '&two_comes_from=MainPageUpgradeButton'; ?>"
                   class="two-button two-button-green two-available-pro-button"><?php _e('Upgrade', 'tenweb-speed-optimizer'); ?></a>
              </div>
            </div>
            <?php
            }
       } ?>
      </div>
      <?php
        } ?>
      <div class="two-disconnect-link">
        <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/check_solid.svg" alt="Connected" class="two-connected-img" />
        <b><?php _e('Site is connected', 'tenweb-speed-optimizer'); ?></b>
        <a href="<?php echo esc_url($two_disconnect_link); ?>"><?php _e('Disconnect from ' . esc_html(TWO_SO_ORGANIZATION_NAME), 'tenweb-speed-optimizer'); ?></a>
      </div>
      <div class="two-wp-link">
        <b><?php _e('Have a question?', 'tenweb-speed-optimizer'); ?></b>
        <span><?php echo esc_html($two_contact_text); ?> <a href="<?php echo esc_url($two_contact_link); ?>" target="_blank"><?php echo esc_html($two_contact_link_text); ?></a></span>
      </div>
        <?php
        $image_optimzier_active = true;
        $disconnect_title = __('Disconnect ' . TWO_SO_ORGANIZATION_NAME . ' Booster', 'tenweb-speed-optimizer');
        $disconnect_desc = __('Disconnecting a website from 10Web will rollback all optimization <br>
                            and caching configurations and negatively affect your PageSpeed.', 'tenweb-speed-optimizer');

        if (is_plugin_active('image-optimizer-wd/tenweb-image-optimizer.php')) {
            $image_optimzier_active = false;
            $disconnect_title = __('Disconnect website from ' . TWO_SO_ORGANIZATION_NAME, 'tenweb-speed-optimizer');
            $disconnect_desc = __('If you disconnect your website from 10Web, you will lose both the IO <br>
                            and Speed Booster plugins, resulting in the loss of your website optimization. <br>
              If you no longer want to use this plugin, deactivate it from the plugins list.', 'tenweb-speed-optimizer');
        } ?>
      <div class="two-disconnect-popup">
        <div class="two-disconnect-popup-body">
          <div class="two-disconnect-popup-title">
            <?php echo esc_html($disconnect_title); ?>
          </div>
          <div class="two-disconnect-popup-content">
            <p>
              <?php echo wp_kses_post($disconnect_desc); ?>
            </p>
              <?php
              if ($image_optimzier_active) {
                  ?>
            <p>
              <?php _e('By disconnecting you will revert the following:', 'tenweb-speed-optimizer'); ?>
            </p>
            <div class="two-disconnect-popup-list">
              <p>
                <?php _e('PageSpeed score', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Improved Core Web Vitals', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Image optimization', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Caching', 'tenweb-speed-optimizer'); ?>
              </p>
            </div>
              <?php
              } ?>
          </div>
          <div class="two-disconnect-popup-button-container">
            <a href="#" class="two-button two-disconnect-popup-button two-button-cancel"><?php _e('Cancel', 'tenweb-speed-optimizer'); ?></a>
            <a href="<?php echo esc_url($two_disconnect_link); ?>" class="two-button two-disconnect-popup-button two-button-disconnect"><?php _e('Disconnect', 'tenweb-speed-optimizer'); ?></a>
          </div>
          <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/close.svg" alt="Close" class="two-close-img" />
        </div>
      </div>
      <?php
    }
    ?>
  </div>
</div>
