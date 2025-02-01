<?php
function customer_care_html($main_class, $close_icon)
{?>
    <div class="<?php echo esc_attr($main_class); ?>">
    <div class="two-contact-care-popup">
        <?php echo $close_icon ? '<span class="two-contact-care-close"></span>' : ''; ?>
        <div class="two-contact-care-content-section">
            <div class="two-contact-care-wp-section">
                <div class="two-contact-care-title">
                    <?php _e('Get free and fast support<br>on WordPress.org', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-contact-care-description">
                    <p class="two-contact-care-content-text">
                        <?php _e(
    'If you’re having issues or need help with your website,<br>
                            the fastest way to get assistance is by creating a topic<br> on ',
    'tenweb-speed-optimizer'
); ?>
                        <a href="<?php echo esc_url('https://wordpress.org/support/plugin/tenweb-speed-optimizer/'); ?>">
                            <?php _e('WordPress.org.', 'tenweb-speed-optimizer'); ?>
                        </a>
                    </p>
                    <p class="two-contact-care-content-text">
                        <?php _e(
    'Our support team constantly monitors and resolves topics<br> within 24 hours 
to provide users with a smooth<br> optimization process.',
    'tenweb-speed-optimizer'
); ?>
                    </p>
                </div>
                <a target="_blank" class="two-contact-care-green-button"
                   href="<?php echo esc_url('https://wordpress.org/support/plugin/tenweb-speed-optimizer/'); ?>">
                    <?php _e('CREATE A TOPIC', 'tenweb-speed-optimizer'); ?>
                </a>
            </div>
            <div class="two-contact-care-pro-section">
                <div class="two-contact-care-booster-pro">
                    <p class="two-contact-care-content-text two-option-diamond">
                        <?php _e('Priority 24/7 live chat support is available to ', 'tenweb-speed-optimizer'); ?>
                        <br>
                        <a href="<?php echo esc_url(TENWEB_DASHBOARD . '/upgrade-plan'); ?>">
                            <?php echo esc_html__('Booster Pro', 'tenweb-speed-optimizer'); ?></a>
                        <?php _e(' users', 'tenweb-speed-optimizer'); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="two-contact-care-video-section">
            <video width="470" height="590" controls>
                <source src="<?php echo esc_url(TENWEB_SO_URL . '/assets/images/wp_care_popup.mp4'); ?> " type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>
</div>
<?php } ?>