<?php
wp_enqueue_script('two_bf_countdown_js', TENWEB_SO_URL . '/assets/js/two_bf_countdown.js', ['jquery'], TENWEB_SO_VERSION);
?>
<div class="two_black_friday_countdown_container">
    <div class="two_black_friday_countdown">
        <div class="two_black_friday_countdown_numbers two_bfc_days">
            <span class="two_black_friday_countdown_each"></span>
            <span class="two_black_friday_countdown_each"></span>
        </div>
        <p class="two_black_friday_countdown_text">Days</p>
    </div>
    <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/conutdown_dots.png'); ?>" />
    <div class="two_black_friday_countdown">
        <div class="two_black_friday_countdown_numbers two_bfc_hours">
            <span class="two_black_friday_countdown_each"></span>
            <span class="two_black_friday_countdown_each"></span>
        </div>
        <p class="two_black_friday_countdown_text">Hours</p>
    </div>
    <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/conutdown_dots.png'); ?>" />
    <div class="two_black_friday_countdown">
        <div class="two_black_friday_countdown_numbers two_bfc_minutes">
            <span class="two_black_friday_countdown_each"></span>
            <span class="two_black_friday_countdown_each"></span>
        </div>
        <p class="two_black_friday_countdown_text">Minutes</p>
    </div>
</div>
<p class="two_pro_container_offer_title"><?php _e('30% lifetime discount this Black Friday', 'tenweb-speed-optimizer'); ?>
    <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/offer_underline.png'); ?>" /></p>
<p class="two_pro_container_title"><?php _e('Achieve more with 10Web Pro:', 'tenweb-speed-optimizer'); ?></p>
<p class="two_pro_option two_pro_bg_flash"><?php echo sprintf(__('Auto-optimize all %s pages and %s images', 'tenweb-speed-optimizer'), esc_html($black_friday_total_pages), esc_html($black_friday_total_images)); ?></p>
<p class="two_pro_option two_pro_bg_flash"><?php _e('Enable Cloudflare Enterprise CDN and get', 'tenweb-speed-optimizer'); ?></p>
<p class="two_pro_option"><?php _e('Up to 60% performance boost and <100ms TTFB', 'tenweb-speed-optimizer'); ?></p>
<p class="two_pro_option"><?php _e('Full page caching on edge network', 'tenweb-speed-optimizer'); ?></p>
<a href="<?php echo esc_url($black_friday_upgrade_button); ?>" target="_blank" class="two_add_page_button"><?php _e('Upgrade with discount', 'tenweb-speed-optimizer'); ?></a>
