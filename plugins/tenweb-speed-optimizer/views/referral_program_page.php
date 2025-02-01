<?php
wp_enqueue_style(
    'two_referral_program_page_css',
    TENWEB_SO_URL . '/assets/css/referral_program_page.css',
    ['two-open-sans'],
    TENWEB_SO_VERSION
);
wp_enqueue_script(
    'two_referral_program_js',
    TENWEB_SO_URL . '/assets/js/referral_program.js',
    ['jquery'],
    TENWEB_SO_VERSION
);

// red icon to be visible only until first visit of page
if (!get_option('two_referral_visited')) {
    update_option('two_referral_visited', 'two_referral_visited', false);
}

$referral_hash = get_site_option(TENWEB_PREFIX . '_client_referral_hash');
$link_to_dash = TENWEB_DASHBOARD . '/referral-system/program';
$referral_link = TENWEB_SITE_URL . '/page-speed-booster/?referral_hash=' . $referral_hash;
$share_text = urlencode('Boost your website\'s speed with 10Web PageSpeed Booster! Try it now for lightning-fast performance and receive a $20 credit.');
$referTo = [
    [
        'link' => 'https://twitter.com/share?text=' . $share_text . '&url=' . $referral_link,
        'logo' => TENWEB_SO_URL . '/assets/images/Twitter.svg',
        'title' => 'Twitter',
    ],
    [
        'link' => 'https://www.linkedin.com/sharing/share-offsite?mini=true&url=' . $referral_link . '&title=' . $share_text,
        'logo' => TENWEB_SO_URL . '/assets/images/linkedIn.svg',
        'title' => 'LinkedIn',
    ],
    [
        'link' => 'https://www.reddit.com/submit?title=' . $share_text
            . '&selftext=true&url=' . $referral_link,
        'logo' => TENWEB_SO_URL . '/assets/images/reddit-logo.svg',
        'title' => 'Reddit',
    ],
    [
        'link' => 'https://www.facebook.com/sharer.php?u=' . $referral_link . '&p[title]=' . $share_text,
        'logo' => TENWEB_SO_URL . '/assets/images/facebook.svg',
        'title' => 'Facebook',
    ],
];
$howItWorks = [
    [
        'img' => TENWEB_SO_URL . '/assets/images/invite.png',
        'css' => 'min-width: 198px;',
        'title' => __('Invite a friend to join 10Web', 'tenweb-speed-optimizer'),
        'desc' => __('Share the link on your social<br> media or send it to your friends.', 'tenweb-speed-optimizer'),
    ],
    [
        'img' => TENWEB_SO_URL . '/assets/images/gift.png',
        'title' => __('Gift them $20 credit', 'tenweb-speed-optimizer'),
        'desc' => __('They’ll get $20 credit<br> and an optimized website in minutes.', 'tenweb-speed-optimizer'),
    ],
    [
        'img' => TENWEB_SO_URL . '/assets/images/earn.png',
        'css' => 'min-width: 198px;',
        'title' => __('Earn from each referral', 'tenweb-speed-optimizer'),
        'desc' => __('You will receive a $20 credit<br> for the first referral and $10<br> for each additional one.', 'tenweb-speed-optimizer'),
    ],
];
?>
<div class="two-wp-container">
    <div class="two-container-with-border">
        <div class="two-referral-header">
            <div>
                <p class="two-page-main-title"><?php esc_html_e('Get a $20 credit for successful referrals you make.', 'tenweb-speed-optimizer'); ?></p>
                <p class="two-page-main-desc"><?php esc_html_e('The user who you refer will also receive $20 in 10Web credit.', 'tenweb-speed-optimizer'); ?></p>
            </div>
            <div>
                <a class="two-page-main-desc two-green-liner-button" href="<?php echo esc_url($link_to_dash); ?>"><?php esc_html_e('See my credit', 'tenweb-speed-optimizer'); ?></a>
            </div>
        </div>
        <div class="two-referral-works">
            <p class="two-page-main-desc two-page-subtitle"><?php
                echo wp_kses(
    __('Share the referral link with your community. <br>Give them $20 credit and a chance to improve their website score.', 'tenweb-speed-optimizer'),
    [ 'br' => [] ]
); ?>
            </p>
            <div class="two-referral-container">
                <p class="two-page-main-desc" id="two-referral-link"><?php echo esc_url($referral_link, 'tenweb-speed-optimizer'); ?></p>
                <p class="two-referral-copy two-page-main-desc"><?php esc_html_e('Copy', 'tenweb-speed-optimizer'); ?></p>
            </div>
            <div class="two-refer-links">
                <?php
                foreach ($referTo as $refer) { ?>
                    <a class="two-refer-to" href="<?php echo esc_url($refer['link']); ?>" target="_blank">
                        <img src="<?php echo esc_url($refer['logo']); ?>">
                        <?php echo esc_html($refer['title']); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="two-container-with-border">
        <p class="two-page-main-title">
            <?php esc_html_e('Here’s how our referral program works:', 'tenweb-speed-optimizer'); ?>
        </p>
        <div class="two-howitworks-container">
            <?php
            foreach ($howItWorks as $each) { ?>
                <div class="two-howitworks-each" <?php echo isset($each['css']) ? 'style="' . esc_attr($each['css']) . '"' : ''; ?>>
                    <img src="<?php echo esc_url($each['img']); ?>">
                    <p class="two-main-text two-page-small-subtitle"><?php echo esc_html($each['title']); ?></p>
                    <p class="two-main-text"><?php echo wp_kses(__($each['desc'], 'tenweb-speed-optimizer'), [ 'br' => [] ]); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
