<?php
$two_domain_id = get_site_option('tenweb_domain_id');
$two_manage_url = trim(TENWEB_DASHBOARD, '/') . '/websites/' . $two_domain_id . '/booster/frontend' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_contact_link = $two_manage_url . '&open=livechat';
$two_disconnect_nonce = wp_create_nonce('two_disconnect_nonce');
$two_disconnect_button_text = __('Deactivate', 'tenweb-speed-optimizer');

$two_so_organization_name = get_option('two_so_organization_name');
$two_so_organization_support_url = get_option('two_so_organization_support_url');

if (!empty($two_so_organization_name) && !empty($two_so_organization_support_url)) {
    $two_contact_link = $two_so_organization_support_url;
}
$query_args = [
    'action' => 'deactivate',
    'plugin' => TENWEB_SO_BASENAME,
    '_wpnonce' => wp_create_nonce('deactivate-plugin_' . TENWEB_SO_BASENAME)
];

// disconnect plugin only when image-optimizer is not active, otherwise only deactivate it
if (!is_plugin_active('image-optimizer-wd/tenweb-image-optimizer.php')) {
    $query_args['two_disconnect'] = 1;
    $query_args['nonce'] = $two_disconnect_nonce;
    $two_disconnect_button_text = __('Disconnect & deactivate', 'tenweb-speed-optimizer');
}
define('TENWEB_SO_DISCONNECT_DEACTIVATE_URL', add_query_arg(
    $query_args,
    admin_url('plugins.php')
));
global $TwoSettings;

if ('on' == $TwoSettings->get_settings('two_test_mode')) {
    $two_popup_title = __('You’re about to deactivate ' . TWO_SO_ORGANIZATION_NAME . ' Booster', 'tenweb-speed-optimizer');
    $two_popup_p1 = __(TWO_SO_ORGANIZATION_NAME . ' Booster is currently working in Test mode.', 'tenweb-speed-optimizer');
    $two_popup_p2 = __('If you’re still having issues and no longer wish to optimize your website with ' . TWO_SO_ORGANIZATION_NAME . ' Booster, you can disconnect your site and deactivate the plugin.', 'tenweb-speed-optimizer');
    $two_button_text = __('STAY CONNECTED & RESOLVE', 'tenweb-speed-optimizer');
    $two_button_class = 'two-button-cancel';
} else {
    $two_popup_title = __('Try this before deactivating ' . TWO_SO_ORGANIZATION_NAME . ' Booster', 'tenweb-speed-optimizer');
    $two_popup_p1 = sprintf(__('If you’re having issues with your optimized website, deactivating ' . TWO_SO_ORGANIZATION_NAME . ' Booster is not the only solution. We recommend enabling Test mode and %s to resolve the issue.', 'tenweb-speed-optimizer'), '<a href="' . esc_url($two_contact_link) . '" target="_blank">' . __('contacting our team', 'tenweb-speed-optimizer') . '</a>');
    $two_popup_p2 = sprintf(__('Test mode temporarily disables ' . TWO_SO_ORGANIZATION_NAME . ' Booster for website visitors so you can perform various tests without affecting the live site. To preview optimized version in Test mode, append %s. Choose how you want to proceed.', 'tenweb-speed-optimizer'), '<b>?twbooster=1</b>');
    $two_button_text = __('Enable test mode', 'tenweb-speed-optimizer');
    $two_button_class = 'two-button-test-mode';
}
?>
    <style>
        .two-button {
            border-radius: 6px;
            padding: 10px 24px;
            text-align: center;
            text-decoration: none;
            width: 260px;
            font-size: 14px;
            line-height: 20px;
            font-weight: 600;
            text-transform: uppercase;
            color: #FFFFFF;
            display: inline-block;
            box-sizing: border-box;
            margin: 0 0 0 20px;
        }
        .two-button:hover {
            opacity: 0.6;
        }
        .two-deactivate-popup * {
            box-sizing: border-box;
            font-family: Open Sans, sans-serif;
        }
        .two-deactivate-popup {
            position: fixed;
            top: 0; right: 0; bottom: 0; left: 0;
            background-color: #323A4534;
            display: none;
            flex-direction: column;
            justify-content: center;
            z-index: 9999;
            color: #323A45;
            font-family: sans-serif;
        }
        .two-deactivate-popup.open {
            display: flex;
        }
        .two-deactivate-popup-body {
            width: 740px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 13px;
            display: flex;
            flex-direction: column;
            align-self: center;
            padding: 35px 50px;
            font-size: 16px;
            line-height: 24px;
            justify-content: space-between;
            position: relative;
        }
        .two-deactivate-popup-content {
            max-width: 600px;
            flex-grow: 1;
            margin: 10px 0 15px 0;
        }
        .two-deactivate-popup-content>p {
            font-size: 16px;
            line-height: 24px;
            margin: 0 0 15px 0;
        }
        .two-deactivate-popup-content>p a, .two-deactivate-popup-content>p a:hover {
            color: #2160B5;
        }
        .two-deactivate-popup-title {
            font-size: 24px;
            line-height: 34px;
            font-weight: 800;
        }
        .two-deactivate-popup-button, .two-deactivate-popup-button:hover {
            background-color: #22B339;
            color: #FFFFFF;
        }
        .two-button-disconnect, .two-button-disconnect:hover {
            background-color: #E6E7E8;
            color: #323A45;
        }
        .two-deactivate-popup-button-container {
            display: flex;
            flex-direction: row;
            justify-content: end;
        }
        .two-close-img {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
        }
        @media screen and (max-width: 767px){
            .two-deactivate-popup-body {
                width: 100%;
                height: auto;
                top: 35px;
                border-radius: 0;
                padding: 35px 20px;
            }
        }
    </style>
    <script>
        jQuery(document).ready(function() {
            jQuery('tr[data-plugin="tenweb-speed-optimizer/tenweb_speed_optimizer.php"] span.deactivate a').on('click', function() {
                jQuery('.two-deactivate-popup').appendTo('body').addClass('open');
                return false;
            });
            jQuery('.two-button-cancel, .two-close-img').on('click', function() {
                jQuery('.two-deactivate-popup').removeClass('open');
                return false;
            });
            jQuery('.two-button-test-mode').on('click', function() {
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        action: "two_update_setting",
                        nonce: "<?php echo esc_js(wp_create_nonce('two_ajax_nonce')); ?>",
                        name: "two_test_mode",
                        value: "on",
                    }
                }).done(function (data) {
                    if ( true == data.success ) {
                        window.location.href = "<?php echo esc_url($two_manage_url); ?>";
                    } else {
                        alert( 'Something went wrong. Could not enable test mode.' );
                    }
                });
                return false;
            });
        });
    </script>
    <div class="two-deactivate-popup">
        <div class="two-deactivate-popup-body">
            <div class="two-deactivate-popup-title">
                <?php echo esc_html($two_popup_title); ?>
            </div>
            <div class="two-deactivate-popup-content">
                <p>
                    <?php echo wp_kses_post($two_popup_p1); ?>
                </p>
                <p>
                    <?php echo wp_kses_post($two_popup_p2); ?>
                </p>
            </div>
            <div class="two-deactivate-popup-button-container">
                <a href="<?php echo esc_url(TENWEB_SO_DISCONNECT_DEACTIVATE_URL); ?>" class="two-button two-button-disconnect"><?php echo esc_html($two_disconnect_button_text); ?></a>
                <a href="#" class="two-button two-deactivate-popup-button <?php echo esc_attr($two_button_class); ?>"><?php echo esc_html($two_button_text); ?></a>
            </div>
            <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/close.svg" alt="Close" class="two-close-img" />
        </div>
    </div>
<?php
