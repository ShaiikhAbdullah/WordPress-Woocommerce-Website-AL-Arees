<?php

$two_php_not_compatible = defined('TWO_INCOMPATIBLE_ERROR') && TWO_INCOMPATIBLE_ERROR;
$two_connect_link_a = $two_php_not_compatible ? '' : \TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link();

$two_login_link = $two_php_not_compatible ? '' : \TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link(
    'sign-up',
    ['has_account' => '1']
);
$two_connection_error = defined('TWO_INCOMPATIBLE_WARNING') && TWO_INCOMPATIBLE_WARNING;

/* incompatibility check is just for case if user's php version is lower than needed
* and we don't want to require Transient class to avoid php errors
 */
if (defined('TWO_INCOMPATIBLE_ERROR') && TWO_INCOMPATIBLE_ERROR) {
    $status = 'sign_up'; // phpcs:ignore
} else {
    $subscription_id = \TenWebWpTransients\OptimizerTransients::get('tenweb_subscription_id');

    if ($subscription_id) {
        $status = 'connect'; // phpcs:ignore
    } else {
        $status = 'sign_up'; // phpcs:ignore
    }
}?>

<div class="two-container disconnected" dir="ltr">
    <?php
    include_once 'two_header.php';
    ?>
    <div class="two-body-container">
        <?php
        if ($two_php_not_compatible || $two_connection_error) {
            global $two_incompatible_errors;

            foreach ($two_incompatible_errors as $two_incompatible_error) {
                ?>
                <div class="two-error">
                    <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/error.svg" alt="Error" class="two-error-img" />
                    <b><?php echo esc_html($two_incompatible_error['title']); ?></b> <?php echo esc_html($two_incompatible_error['message']); ?>
                </div>
                <?php
            }
        }
        ?>
        <div class="two-body">
            <div class="two-greeting">
                <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/waving_hand.png" alt="Hey" class="two-waving-hand" />
                <?php _e('Hello!', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-plugin-status">
                <?php _e('Welcome to ' . esc_html(TWO_SO_ORGANIZATION_NAME) . ' Website Booster', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-plugin-description">
                <?php _e('Follow these steps to get started:', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-steps">
                <div class="two-step two-step-1">
                    <div class="two-step-check">
                        <div class="two-step-check-inner two-check"></div>
                    </div>
                    <div class="two-step-title">
                        <?php _e('Step 1', 'tenweb-speed-optimizer'); ?>
                    </div>
                    <div class="two-step-body">
                        <div class="two-step-header">
                            <?php _e('Connect your website to ' . esc_html(TWO_SO_ORGANIZATION_NAME), 'tenweb-speed-optimizer'); ?>
                        </div>
                        <div class="two-step-description">
                            <?php _e('Sign up and connect your website to ' . esc_html(TWO_SO_ORGANIZATION_NAME), 'tenweb-speed-optimizer'); ?>
                           <br>
                            <?php _e(' to enable the ' . esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster service.', 'tenweb-speed-optimizer'); ?>
                        </div>
                    </div>
                </div>
                <div class="two-step two-step-2">
                    <div class="two-step-check">
                        <div class="two-step-check-inner two-flash"></div>
                    </div>
                    <div class="two-step-title">
                        <?php _e('Step 2', 'tenweb-speed-optimizer'); ?>
                    </div>
                    <div class="two-step-body">
                        <div class="two-step-header">
                            <?php _e('Optimize your website’s frontend', 'tenweb-speed-optimizer'); ?>
                        </div>
                        <div class="two-step-description">
                            <?php _e('Automatically optimize the frontend of your site,', 'tenweb-speed-optimizer'); ?>
                            <br>
                            <?php _e('get a 90+ PageSpeed and pass Core Web Vitals.', 'tenweb-speed-optimizer'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($status == 'connect') {
                if (!empty(get_site_option(TENWEB_PREFIX . '_access_token'))) {?>
                    <a href="#"
                       class="two-button two-button-connect two-button-io-active-optimize" <?php disabled(!$two_login_link); ?>>
                        <?php _e('Optimize', 'tenweb-speed-optimizer'); ?>
                    </a>
                <?php } else { ?>
                    <a href="<?php echo esc_url($two_login_link); ?>"
                       class="two-button two-button-connect" <?php disabled(!$two_login_link); ?>>
                        <?php _e('CONNECT', 'tenweb-speed-optimizer'); ?>
                    </a>
                <?php }
            } elseif ($status == 'sign_up') {
                ?>
                    <a href="<?php echo esc_url($two_connect_link_a); ?>"
                       class="two-button two-button-connect" <?php disabled(!$two_connect_link_a); ?>>
                        <?php _e('SIGN UP & CONNECT', 'tenweb-speed-optimizer'); ?>
                    </a>
                <?php
            }
            ?>
        </div>
        <div class="two-image-container">
            <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/welcome_image.png" alt="Welcome to <?php echo esc_html(TWO_SO_ORGANIZATION_NAME); ?>" class="two-welcome-image" />
            <div class="two-image-description">
                <div class="two-image-description-header">
                    <?php _e('Access the benefits of ' . esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster', 'tenweb-speed-optimizer'); ?>
                </div>
                <ul class="two-image-description-list">
                    <li><?php _e('90+ PageSpeed score', 'tenweb-speed-optimizer'); ?></li>
                    <li><?php _e('Image optimization', 'tenweb-speed-optimizer'); ?></li>
                    <li><?php _e('Improved Core Web Vitals', 'tenweb-speed-optimizer'); ?></li>
                    <li><?php _e('Full caching', 'tenweb-speed-optimizer'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

