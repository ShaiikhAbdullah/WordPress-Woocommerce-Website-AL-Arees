<?php
wp_enqueue_style('two_google-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap', [], TENWEB_SO_VERSION);
wp_enqueue_style('two_admin_css', TENWEB_SO_URL . '/assets/css/settings_basic.css', '', TENWEB_SO_VERSION);
wp_enqueue_style('two_white_label_css', TENWEB_SO_URL . '/assets/css/white_label.css', '', TENWEB_SO_VERSION);
wp_enqueue_script('two_white_label_js', TENWEB_SO_URL . '/assets/js/two_white_label.js', [ 'jquery' ], TENWEB_SO_VERSION);
wp_localize_script('two_white_label_js', 'two_admin_vars', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'ajaxnonce' => wp_create_nonce('two_ajax_nonce'),
]);

$company_name_setted = false;

if (get_option('two_so_organization_name') && get_option('two_so_organization_name') != '') {
    $company_name_setted = true;
    $company_name = get_option('two_so_organization_name');
}
$two_so_support_url = get_option('two_so_organization_support_url');

if (empty($two_so_support_url)) {
    $two_domain_id = get_site_option('tenweb_domain_id');
    $two_manage_url = trim(TENWEB_DASHBOARD, '/') . '/websites/' . $two_domain_id . '/booster/frontend' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
    $two_contact_link = $two_manage_url . '&open=livechat';
    $two_so_support_url = $two_contact_link;
}
?>
<div class="two-container connected">
  <div class="two-header">
        <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/10web_logo.svg" alt="10Web" class="two-header-img" />
    </div>
    <div class="two-body-container">
        <div class="two-body two-white-label">
            <div class="two-white-label-status">
                <div class="two-plugin-status">
                    <?php _e('White label', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-white-label-status-input">
                    <label class="switch">
                        <input type="checkbox" id="two-white-label-status" <?php echo $company_name_setted ? 'checked="1"' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            <div class="two-plugin-description">
                <?php _e('White label 10Web Booster in this website’s WordPress dashboard.', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-desc-block">
                <div class="two-plugin-description two-description-wl two-description-with-info">
                    <?php _e('Organization name', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-plugin-description two-description-wl two-description-with-info">
                    <?php _e('Your support URL', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-empty-desc"></div>
            </div>
            <div class="two-white-label-settup">
                <input type="text" id="two-company-name"
                       placeholder="<?php _e('Name of your company', 'tenweb-speed-optimizer'); ?>"
                    <?php echo $company_name_setted ? 'value="' . esc_attr($company_name) . '"' : ''; ?>>

                <input type="text" id="two-support-url"
                       placeholder="<?php _e('Your support URL', 'tenweb-speed-optimizer'); ?>"
                       value="<?php echo esc_attr($two_so_support_url); ?>"
                        >
                <button id="two-save-company-name" <?php echo $company_name_setted ? '' : 'disabled'; ?>>
                    <?php _e('Save', 'tenweb-speed-optimizer'); ?>
                </button>
            </div>
            <div class="two-plugin-description two-description-wl">
                <?php _e('The white label will be applied on:', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-white-label-apply-on">
                <div class="two-white-label-apply-on-each">
                    <?php _e('Plugin name', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-white-label-apply-on-each">
                    <?php _e('Active plugin list', 'tenweb-speed-optimizer'); ?>
                </div>
                <div class="two-white-label-apply-on-each">
                    <?php _e('Plugin settings in WP admin', 'tenweb-speed-optimizer'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
