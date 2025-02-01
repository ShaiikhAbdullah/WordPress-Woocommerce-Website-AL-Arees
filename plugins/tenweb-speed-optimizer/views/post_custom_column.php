<?php

use TenWebOptimizer\OptimizerUtils;

/* Check home page */
if (get_option('page_on_front') == $post_id) {
    $post_id = 'front_page'; // phpcs:ignore
    $page_score = get_option('two-front-page-speed');
} else {
    $page_score = get_post_meta($post_id, 'two_page_speed', true);
}
$page_url = get_permalink($post_id);
// The page is optimized if there is a new score data after optimization in DB or is in progress if there is an old score data.
$status = 'optimized'; // phpcs:ignore
$critical_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

if (\TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . $post_id)) {
    $status = 'optimizing'; // phpcs:ignore
} elseif (!array_key_exists($post_id, $critical_pages)) {
    $status = 'notOptimized'; // phpcs:ignore
}

$disconnect = true;

if (OptimizerUtils::is_tenweb_booster_connected()) {
    $disconnect = false;
}

$reanalyze_button_status_previous = false;
$reanalyze_button_status_current = false;

if (!empty($page_score)) {
    if (isset($page_score['current_score']) && isset($page_score['current_score']['status'])
        && $page_score['current_score']['status'] == 'inprogress') {
        $reanalyze_button_status_current = true;
    }

    if (isset($page_score['previous_score']) && isset($page_score['previous_score']['status'])
        && $page_score['previous_score']['status'] == 'inprogress') {
        $reanalyze_button_status_previous = true;
    }
}

$improvement_sec = '';

if (!empty($page_score) && isset($page_score['current_score']) && isset($page_score['previous_score'])
    && isset($page_score['current_score']['desktop_score']) && isset($page_score['previous_score']['desktop_score'])
    && (int) $page_score['previous_score']['desktop_score'] !== 0 && (int) $page_score['previous_score']['mobile_score'] !== 0) {

    /* score improvement calculation */
    $desktopScoreImprove = (($page_score['current_score']['desktop_score']
                - $page_score['previous_score']['desktop_score']) / $page_score['previous_score']['desktop_score']) * 100;
    $mobileScoreImprove = (($page_score['current_score']['mobile_score']
                - $page_score['previous_score']['mobile_score']) / $page_score['previous_score']['mobile_score']) * 100;
    $maxScore = max($desktopScoreImprove, $mobileScoreImprove);
    $showImproveBadge = round($maxScore) > 20;
    $improvedPercent = round($maxScore) > 20 ? round($maxScore) : 0;

    if ($showImproveBadge) {
        $improvement_sec = '<p class="two_optimized_improvement">Improved<span>' . esc_html($improvedPercent) . '%' . '</span></p>';
    }
}

?>
<?php if (\TenWebOptimizer\OptimizerUrl::urlIsOptimizable($page_url)) { ?>
    <span class="two-wp-pages-list two-page-speed two-optimized <?php echo $status == 'optimized' ? '' : ' two-hidden'; ?>">
  <a><?php _e('Optimized', 'tenweb-speed-optimizer'); ?></a>
  <span data-post-id="<?php esc_attr_e($post_id); ?>" class="two-pages-list-reanalyzing <?php echo ($reanalyze_button_status_current || $reanalyze_button_status_previous) ? '' : 'two-hidden'; ?>">
      <span class="two-page-speed two-optimizing"></span>
      <?php esc_html_e('Reanalyzing score…', 'tenweb-speed-optimizer'); ?>
  </span>
  <span data-post-id="<?php esc_attr_e($post_id); ?>" class="two-wp-pages-list-tooltip two-optimized-see-more <?php echo ($reanalyze_button_status_current || $reanalyze_button_status_previous) ? 'two-hidden' : ''; ?>">
      <?php esc_html_e('See score', 'tenweb-speed-optimizer'); ?>
      <div class="two-score-container two-any-reanalyzing-score-section two-hidden" data-id="<?php echo esc_attr($post_id); ?>">
          <div class="two-score-container-header-part">
              <p class="two-score-container-title"><?php _e('Page Performance', 'tenweb-speed-optimizer'); ?></p>
              <?php echo wp_kses_post($improvement_sec); ?>
          </div>
          <div class="two_score_block">
              <div class="two_score_block_left">
                    <p class="two_score_block_title"><?php _e('Before optimization', 'tenweb-speed-optimizer'); ?></p>
                    <?php if (empty($page_score) || !isset($page_score['previous_score'])
                        || !isset($page_score['previous_score']['desktop_score']) || $reanalyze_button_status_previous) {
            $no_old_scores = 'two-no-scores';
        }?>
                    <div class="two_score_container_both two-old-scores <?php echo isset($no_old_scores) ? esc_html($no_old_scores) : ''; ?>"
                         data-no-score-for="<?php echo $reanalyze_button_status_previous ? esc_attr($post_id) : ''; ?>">
                        <div class="two_score_container two_score_container_mobile_old">
                            <div class="two-score-circle" data-score="<?php echo isset($no_old_scores) ? '' : (int) $page_score['previous_score']['mobile_score']; ?>" data-size="30"
                                 data-thickness="2" data-id="mobile">
                                <span class="two-score-circle-animated"></span>
                            </div>
                            <div class="two_score_info">
                                <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                                <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                    <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($page_score['previous_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                            </div>
                        </div>
                        <div class="two_score_container two_score_container_desktop_old">
                            <div class="two-score-circle" data-score="<?php echo isset($no_old_scores) ? '' : (int) $page_score['previous_score']['desktop_score']; ?>" data-size="30"
                                 data-thickness="2" data-id="desktop">
                                <span class="two-score-circle-animated"></span>
                            </div>
                            <div class="two_score_info">
                                <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                                <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                    <span class="two_load_time"><?php echo isset($no_old_scores) ? '' : esc_html($page_score['previous_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
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
                    <p class="two_score_block_title"><?php echo esc_html(sprintf(__('After %s optimization', 'tenweb-speed-optimizer'), TWO_SO_ORGANIZATION_NAME . ' Booster')); ?></p>
                    <?php if (empty($page_score) || !isset($page_score['current_score'])
                        || !isset($page_score['current_score']['desktop_score']) || $reanalyze_button_status_current) {
            $no_new_scores = 'two-no-scores';
        }?>
                    <div class="two_score_container_both two-new-scores <?php echo isset($no_new_scores) ? esc_html($no_new_scores) : ''; ?>"
                         data-no-score-for="<?php echo $reanalyze_button_status_current ? esc_attr($post_id) : ''; ?>" >
                        <div class="two_score_container two_score_container_mobile">
                            <div class="two-score-circle" data-score="<?php echo isset($no_new_scores) ? '' : (int) $page_score['current_score']['mobile_score']; ?>" data-size="30"
                                 data-thickness="2" data-id="mobile">
                                <span class="two-score-circle-animated"></span>
                            </div>
                            <div class="two_score_info">
                                <p><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></p>
                                <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                    <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($page_score['current_score']['mobile_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
                            </div>
                        </div>
                        <div class="two_score_container two_score_container_desktop">
                            <div class="two-score-circle" data-score="<?php echo isset($no_new_scores) ? '' : (int) $page_score['current_score']['desktop_score']; ?>" data-size="30"
                                 data-thickness="2" data-id="desktop">
                                <span class="two-score-circle-animated"></span>
                            </div>
                            <div class="two_score_info">
                                <p><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></p>
                                <p><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                                    <span class="two_load_time"><?php echo isset($no_new_scores) ? '' : esc_html($page_score['current_score']['desktop_tti'] . __('s', 'tenweb-speed-optimizer')); ?></span></p>
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
          <div class="two-score-container-header-part">
              <div><a class="two-score-container-title two-manage-link" target="_blank"
                      href="<?php echo esc_url(TENWEB_DASHBOARD . '/websites/' . get_site_option(TENWEB_PREFIX . '_domain_id') . '/booster/pages'); ?>">
                  <?php _e('Manage optimized pages', 'tenweb-speed-optimizer'); ?>
                  </a>
              </div>
              <div class="two_reanalyze_container">
                <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
                <a onclick="two_reanalyze_score(this)" data-from-wp-admin="1" data-post_id=<?php esc_attr_e($post_id); ?>
                   data-initiator="admin-bar" class="two_reanalyze_button">
                    <?php $reanalyze_button_status_current ? _e('Reanalyzing...', 'tenweb-speed-optimizer') : _e('Reanalyze', 'tenweb-speed-optimizer'); ?>
                </a>
            </div>
          </div>
      </div>
  </span>
</span>

    <span class="two-wp-pages-list two-page-speed two-notoptimized <?php echo $status == 'notOptimized' ? '' : ' two-hidden'; ?>">
  <a <?php echo $disconnect ? 'href="' . esc_url(get_admin_url()) . 'admin.php?page=two_settings_page"' : ''; ?>
          data-post-id="<?php echo esc_attr($post_id); ?>"
          data-initiator="post-list"><?php _e('Optimize now', 'tenweb-speed-optimizer'); ?>
  </a>
    <span class="two-score-container two-wp-pages-list-tooltip two-optimization-in-progress-tooltip two-hidden">
        <p class="two-optimization-in-progress-title"><?php _e('Optimization in progress', 'tenweb-speed-optimizer'); ?></p>
        <p class="two-optimization-in-progress-description">
            <?php _e('A different page is currently being optimized, please wait until the process is complete to optimize another one.', 'tenweb-speed-optimizer'); ?>
        </p>
        <p class="two-optimization-in-progress-close-container">
            <span class="two-optimization-in-progress-close"><?php _e('Got It', 'tenweb-speed-optimizer'); ?></span>
        </p>
    </span>
</span>

    <span class="two-wp-pages-list two-page-speed two-optimizing two-loading-bg
    <?php echo $status == 'optimizing' ? sanitize_html_class(' two_ongoing_optimization') : sanitize_html_class(' two-hidden'); ?>">
  <?php _e('Optimizing...', 'tenweb-speed-optimizer'); ?>
  <p class="two-description">
    <?php _e('Reload in 2 minutes to see the new score', 'tenweb-speed-optimizer'); ?>
  </p>
</span>
<?php }?>
