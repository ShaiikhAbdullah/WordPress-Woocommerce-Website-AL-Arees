<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.0.0
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin/partials
 */

$wt_sc_admin_view_path=plugin_dir_path(WT_SMARTCOUPON_FILE_NAME).'admin/views/';

?>
<div class="wrap">
    <h2 class="wp-heading-inline">
    <?php _e('Settings', 'wt-smart-coupons-for-woocommerce');?>: 
    <?php _e('Smart Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce');?>
    <a href="<?php echo esc_attr(admin_url('edit.php?post_type=shop_coupon'));?>" class="page-title-action" target="_blank"><?php _e('All coupons', 'wt-smart-coupons-for-woocommerce');?></a>
    <a href="<?php echo esc_attr(admin_url('post-new.php?post_type=shop_coupon'));?>" class="page-title-action" target="_blank"><?php _e('Add coupon', 'wt-smart-coupons-for-woocommerce');?></a>
    </h2>
    <div class="nav-tab-wrapper wp-clearfix wt-sc-tab-head">
        <?php
        $tab_head_arr=array( 
            'wt-sc-help'        =>  __('Help guide', 'wt-smart-coupons-for-woocommerce')
        );
        if(isset($_GET['debug']))
        {
            $tab_head_arr['wt-sc-debug']=__('Debug', 'wt-smart-coupons-for-woocommerce');
        }
        Wt_Smart_Coupon_Admin::generate_settings_tabhead($tab_head_arr);
        ?>
    </div>
    <div class="wt-sc-tab-container">
        
        <?php
        //inside the settings form
        $setting_views_a=array(       
            'wt-sc-help'=>'admin-help.php',        
        );

        //outside the settings form
        $setting_views_b=array(          
            'wt-sc-help'=>'admin-settings-help.php',           
        );
        if(isset($_GET['debug']))
        {
            $setting_views_b['wt-sc-debug']='admin-settings-debug.php';
        }
        ?>
        <form method="post" class="wt_sc_settings_form">
            <input type="hidden" value="main" class="wt_sc_settings_base" />
            <?php
            
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WT_SC_PLUGIN_NAME);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=$wt_sc_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }

            //settings form fields for module
            do_action('wt_sc_plugin_settings_form');
            ?>           
        </form>
        <?php
        foreach($setting_views_b as $target_id=>$value) 
        {
            $settings_view=$wt_sc_admin_view_path.$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php 
        //modules to hook outside settings form
        do_action('wt_sc_plugin_out_settings_form');
        ?> 
    </div>

    <?php 
    Wt_Smart_Coupon_Admin::admin_right_sidebar();
    ?>
</div>
<?php
do_action('wt_sc_plugin_after_settings_tab');
?>