<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
$settings_button_title = isset($settings_button_title) && "" !== $settings_button_title ? $settings_button_title : __('Save settings', 'wt-smart-coupons-for-woocommerce');

/** 
*	left and right HTML for settings footer 
*/
$settings_footer_left = isset($settings_footer_left) ? $settings_footer_left : '';
$settings_footer_right = isset($settings_footer_right) ? $settings_footer_right : '';
?>
<div style="clear: both;"></div>
<div class="wt-sc-plugin-toolbar bottom">
    <div class="left">
    	<?php echo wp_kses_post($settings_footer_left);?>
    </div>
    <div class="right">
        <input type="submit" name="wt_sc_update_admin_settings_form" value="<?php echo esc_attr($settings_button_title); ?>" class="button button-primary" style="float:right;"/>
        <?php echo wp_kses_post($settings_footer_right);?>
        <span class="spinner" style="margin-top:11px;"></span>
    </div>
</div>