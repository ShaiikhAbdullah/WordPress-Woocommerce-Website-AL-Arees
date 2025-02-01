<?php
wp_enqueue_style('two_cdn_page_css', TENWEB_SO_URL . '/assets/css/cdn_page.css', ['two-open-sans'], TENWEB_SO_VERSION);
$is_paid_user = \TenWebOptimizer\OptimizerUtils::is_paid_user();
$cloudflare_cdn_enabled = false;

if ($is_paid_user) {
    global $TwoSettings;

    if ($TwoSettings->get_settings('cloudflare_cache_status') == 'on') {
        $cloudflare_cdn_enabled = true;
    }
}
$domain_id = (int) get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0);
$db_cloudflare_page = TENWEB_DASHBOARD . '/websites/' . $domain_id . '/booster/cloudflare';
$upgrade_link = TENWEB_DASHBOARD . '/websites/' . $domain_id . '/booster/pro/?two_comes_from=CloudflarePage';

$cloudflare_cdn_benefits = [
    [
        'title' => __('Enterprise CDN', 'tenweb-speed-optimizer'),
        'desc' => __('Get ultra-fast content delivery over Cloudflare’s<br> global edge network spread in over 275 cities.
                            <br>Remove latency and improve performance.', 'tenweb-speed-optimizer')
    ],
    [
        'title' => __('Full page cache', 'tenweb-speed-optimizer'),
        'desc' => __('Enable full page caching for static pages<br> to read entirely from the cache, improving
                            <br>server response time and loading.', 'tenweb-speed-optimizer')
    ],
    [
        'title' => __('Bot protection', 'tenweb-speed-optimizer'),
        'desc' => __('Cloudflare easily identifies and mitigates all automated traffic to protect your domain<br> from bots.', 'tenweb-speed-optimizer')
    ],
    [
        'title' => __('DDoS protection', 'tenweb-speed-optimizer'),
        'desc' => __('Cloudflare protection secures websites while<br> ensuring the performance of legitimate traffic
                            <br> is not compromised.', 'tenweb-speed-optimizer')
    ],
    [
        'title' => __('Mobile optimization with Mirage', 'tenweb-speed-optimizer'),
        'desc' => __('Mirage automatically resizes the images<br> depending on the device and connection<br> of your visitors.', 'tenweb-speed-optimizer')
    ],
    [
        'title' => __('Web application firewall (WAF)', 'tenweb-speed-optimizer'),
        'desc' => __('Monitor, filter and protect data through<br> Cloudflare’s WAF. Secure your websites<br>
                            from critical threats and vulnerabilities.', 'tenweb-speed-optimizer')
    ],
];
?>
<div class="two-wp-container">

<?php
if ($cloudflare_cdn_enabled) { ?>
    <div class="two-container-with-border two-cloudflare-enabled">
        <p class="two-cloudflare-active">
        <span class="two-cloudflare-active-text">
            <?php esc_html_e('Active', 'tenweb-speed-optimizer'); ?>
        </span>
            <span class="two-cloudflare-active-icon"></span>
        </p>
        <p class="two-page-main-title"><?php esc_html_e('Cloudflare Enterprise', 'tenweb-speed-optimizer'); ?></p>
        <p class="two-page-main-desc"><?php
            echo wp_kses(
    __('Cloudflare Enterprise CDN has successfully been enabled on your website.
                    <br>If you wish to manage your Pro optimization settings, you can do it directly
                    <br> from your 10Web Booster dashboard.', 'tenweb-speed-optimizer'),
    [ 'br' => [] ]
);
            ?>
        </p>
        <div class="two-button-container-right">
            <a class="two-green-button" href="<?php echo esc_url($db_cloudflare_page); ?>">
                <?php esc_html_e('Manage', 'tenweb-speed-optimizer'); ?>
            </a>
        </div>
    </div>
<?php } else { ?>
    <div class="two-container-with-border">
        <p class="two-page-main-title"><?php esc_html_e('Cloudflare Enterprise', 'tenweb-speed-optimizer'); ?></p>
        <p class="two-page-main-desc"><?php
            esc_html_e(
                'Enable Cloudflare Enterprise CDN to improve your website PageSpeed performance.',
                'tenweb-speed-optimizer'
            );
            ?>
        </p>
        <div class="two-cdn-tools">
            <p class="two-main-text">
                <b><?php esc_html_e('30%', 'tenweb-speed-optimizer'); ?></b>
                <?php esc_html_e('higher PageSpeed score', 'tenweb-speed-optimizer'); ?>
                <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/higher_PageSpeed score.svg'); ?>" alt="higher PageSpeed score">
            </p>
            <p class="two-main-text">
                <b><?php esc_html_e('50%', 'tenweb-speed-optimizer'); ?></b>
                <?php esc_html_e('faster load times', 'tenweb-speed-optimizer'); ?>
                <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/faster _oad_times.svg'); ?>" alt="faster load times">
            </p>
            <p class="two-main-text">
                <b><?php esc_html_e('275', 'tenweb-speed-optimizer'); ?></b>
                <?php esc_html_e('caching locations worldwide', 'tenweb-speed-optimizer'); ?>
                <img src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/caching_locations_worldwide.svg'); ?>" alt="caching locations worldwide">
            </p>
        </div>
        <?php
         if (\TenWebOptimizer\OptimizerUtils::is_paid_user()) { ?>
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
         <div class="two-button-container-right">
             <a class="two-green-button" href="<?php echo esc_url($db_cloudflare_page); ?>">
                 <?php esc_html_e('Enable', 'tenweb-speed-optimizer'); ?>
             </a>
         </div>
        <?php } else { ?>
             <div class="two-cdn-test-main-container">
                 <div class="two-cdn-test-head">
                     <p class="two-cdn-test-head-title">
                         <?php esc_html_e('Test the impact of CDN on your website right now', 'tenweb-speed-optimizer'); ?>
                     </p>
                     <p class="two-cdn-style-line"></p>
                 </div>
                 <div class="two-cdn-test-steps">
                     <p class="two-main-text"><?php echo wp_kses(__('We will create a copy of your<br> homepage in 2 minutes', 'tenweb-speed-optimizer'), [ 'br' => []]); ?></p>
                     <p class="two-main-text"><?php echo wp_kses(__('The copy will be stored<br> on our servers with CDN', 'tenweb-speed-optimizer'), [ 'br' => []]); ?></p>
                     <p class="two-main-text"><?php echo wp_kses(__('It will not affect the original<br> version and be deleted in 1 hour', 'tenweb-speed-optimizer'), [ 'br' => []]); ?></p>
                 </div>
                 <div class="two-button-container-center">
                     <a class="two-green-button two-cdn-test-button" href="<?php echo esc_url($db_cloudflare_page); ?>">
                         <?php esc_html_e('Test the CDN', 'tenweb-speed-optimizer'); ?>
                     </a>
                     <a class="two-grey-link" href="<?php echo esc_url($upgrade_link); ?>">
                         <?php esc_html_e('Or Upgrade', 'tenweb-speed-optimizer'); ?>
                     </a>
                 </div>
             </div>
        <?php } ?>
        <div class="two-cdn-benefits-main-container">
            <div class="two-cdn-benefits-head">
                <p class="two-cdn-benefits-head-title">
                    <?php esc_html_e('Benefits of Cloudflare Enterprise', 'tenweb-speed-optimizer'); ?>
                </p>
                <p class="two-cdn-style-line"></p>
            </div>
            <p class="two-page-main-desc"><?php
                echo wp_kses(
                    __('Enterprise is the highest plan of Cloudflare worth $5000/mo. 10Web partnered with Cloudflare to
                    <br>provide you with all of their benefits within Booster Pro. Read more about how enterprise compares
                     <br>to free and pro ', 'tenweb-speed-optimizer'),
                    [ 'br' => [], 'a' => [] ]
                )
                    . '<a target="_blank" href="https://www.cloudflare.com/plans/#overview">' . esc_html__('Cloudflare pricing plans.', 'tenweb-speed-optimizer')
                    . '</a>'; ?>
            </p>
            <div class="two-cdn-benefits">
            <?php
            foreach ($cloudflare_cdn_benefits as $benefit) { ?>
                <div class="two-cdn-each-benefit">
                    <p class="two-main-text two-each-benefit-title">
                        <?php echo esc_html($benefit['title']); ?>
                    </p>
                    <p class="two-main-text">
                        <?php echo wp_kses(
                        $benefit['desc'],
                        [ 'br' => [] ]
                    ); ?>
                    </p>
                </div>
            <?php }
            ?>
            </div>
        </div>
    </div>
<?php } ?>

</div>
