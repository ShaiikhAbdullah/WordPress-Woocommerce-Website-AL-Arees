<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 *  @since 1.4.4
 */
?>
<div class="wt-sc-tab-content" data-id="<?php echo esc_attr( $target_id );?>">
    
    <ul class="wt_sc_sub_tab"> 
        <li style="border-left:none; padding-left: 0px;" data-target="hooks"><a><?php _e('Hooks', 'wt-smart-coupons-for-woocommerce');?></a></li>
        <li data-target="help-links"><a><?php _e('Help Links', 'wt-smart-coupons-for-woocommerce'); ?></a></li> 
    </ul>

    <div class="wt_sc_sub_tab_container">
        <div class="wt_sc_sub_tab_content" data-id="hooks" style="display:block;">
            <?php include "_hooks_list.php"; ?>
        </div>
        <div class="wt_sc_sub_tab_content" data-id="help-links">
            <h3> <?php _e('Help links', 'wt-smart-coupons-for-woocommerce'); ?> </h3> 

            <?php $admin_img_path = WT_SMARTCOUPON_MAIN_URL . 'admin/images/'; ?>
            <ul class="wt-smartcoupon-help-links">
                <li>
                    <img src="<?php echo esc_url($admin_img_path);?>documentation.png">
                    <h3><?php _e('Documentation', 'wt-smart-coupons-for-woocommerce'); ?></h3>
                    <p><?php _e('Refer to our documentation(Pro version) to set up and get started', 'wt-smart-coupons-for-woocommerce'); ?></p>
                    <a target="_blank" href="https://www.webtoffee.com/category/documentation/smart-coupons-for-woocommerce/" class="button button-primary">
                        <?php _e('Documentation', 'wt-smart-coupons-for-woocommerce'); ?>        
                    </a>
                </li>
                <li>
                    <img src="<?php echo esc_url($admin_img_path);?>support.png">
                    <h3><?php _e('Help and Support', 'wt-smart-coupons-for-woocommerce'); ?></h3>
                    <p><?php _e('We would love to help you on any queries or issues.', 'wt-smart-coupons-for-woocommerce'); ?></p>
                    <a target="_blank" href="https://www.webtoffee.com/support/" class="button button-primary">
                        <?php _e('Contact Us', 'wt-smart-coupons-for-woocommerce'); ?>
                    </a>
                </li>               
            </ul>
        </div>
    </div>

</div>