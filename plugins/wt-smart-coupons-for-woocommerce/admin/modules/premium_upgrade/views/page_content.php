<?php
/**
 * Premium Upgrade Page Content
 *
 * @link       
 * @since 1.4.4   
 *
 * @package  Wt_Smart_Coupon  
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}
?>
<style type="text/css">
.wt_sc_upgrade_to_pro{ float:left; width:97%; margin:20px 1.5%; height:auto; padding:25px; box-sizing:border-box; background:url(<?php echo esc_attr($module_img_path);?>upgrade_bg.svg) no-repeat right bottom #fff; border-radius:4px; }  

.wt_sc_upgrade_to_pro_left{ float:left; width:63%; }
.wt_sc_pro_plugin_logo{ float:left; width:65px; } 
.wt_sc_pro_plugin_title_box{ float:left; margin-left:20px; margin-top:2px; width:calc(100% - 85px); }
.wt_sc_pro_plugin_title{ float:left; margin-top:0px; width:100%; font-size:20px; } 
.wt_sc_pro_plugin_rating{ float:left; margin-top:0px; width:100%; font-size:18px; } 
.wt_sc_pro_plugin_desc{ float:left; width:100%; font-size:15px; font-weight:200; margin-top:20px; line-height:24px; } 
.wt_sc_pro_plugin_features{float:left; width:100%; margin-top:20px; }
.wt_sc_pro_plugin_features ul{ list-style:none; margin:0px; }
.wt_sc_pro_plugin_features ul li{ float:left; font-size:14px; padding-bottom:2px; padding-right:10px;}
.wt_sc_pro_plugin_features ul li .dashicons{ color:#6abe45; margin-right:1px; margin-top:1px; font-size:16px; }
.wt_sc_upgrade_to_pro_btn{ display:inline-block; padding:14px 15px; color:#fff; background:#1da5f8; border-radius:6px; text-decoration:none; font-size:14px; margin-top:10px; }
.wt_sc_upgrade_to_pro_btn:hover{ color:#fff; text-decoration:none; background:#1da5f8; color:#fff; }

.wt_sc_upgrade_to_pro_right{ float:left; width:37%; padding-left:3%; box-sizing:border-box; }
.wt_sc_upgrade_to_pro_setup_video_box{ float:left; width:100%; box-sizing:border-box; padding:20px; background:#fff; box-shadow:0px 4px 28px rgba(227, 224, 249, 0.48); border-radius:2px; text-align:center; margin-top:20px; }
.wt_sc_upgrade_to_pro_setup_video_box h4{ font-size:14px; margin-top:0px; }

.wt_sc_middle_section{float:left; height:auto; width:97%; margin:20px 1.5%; margin-top:30px; }
.wt_sc_middle_section_box{ float:left; width:50%; }
.wt_sc_middle_section_box h3{ line-height:24px; }
.wt_sc_advantages{ float:right; width:calc(100% - 30px); background:#fff; min-height:58px; border-top-right-radius:4px; border-bottom-right-radius:4px; }
.wt_sc_advantages_box{float:left; width:50%; min-width:220px;}
.wt_sc_advantages_img{ float:left; margin-right:7px; }
.wt_sc_advantages_txt{ float:left; color:#606060; width:calc(45% - 65px); min-width:150px; line-height:17px; padding-top:10px; }

.wt_sc_other_addons{float:left; height:auto; width:97%; margin:20px 1.5%; margin-top:15px;}
.wt_sc_other_addons_title{float:left; width:100%; text-align:center; font-size:20px;}
.wt_sc_other_addons_container{ float:left; height:auto; width:100%; display:flex; justify-content:space-between; flex-wrap:wrap; margin-top:10px; }

.wt_sc_other_addons_box{ width:30%; box-sizing:border-box; min-width:363px; background:#fff; border-radius:4px; position:relative; margin-top:15px; padding-bottom:20px; }
.wt_sc_other_addons_title_box{ width:100%; box-sizing:border-box; padding:20px; float:left; height:auto; margin-top:0px; background:rgba(233, 245, 255, 0.67); color:#346591; }
.wt_sc_other_addons_title_box img{ float:left; width:55px; margin-right:15px;  }
.wt_sc_other_addons_title_box h3{ float:left; width:calc(100% - 70px); line-height:22px; margin:0px; font-size:18px; color:#346591; display:flex; justify-content:center; flex-direction:column; height:55px;}
.wt_sc_other_addons_desc{ width:100%; box-sizing:border-box; padding:20px; padding-bottom:10px; float:left; height:auto; font-size:14px; line-height:22px; min-height:90px; }
.wt_sc_other_addons_features{width:100%; box-sizing:border-box; padding:20px; padding-top:0px; float:left; height:auto; min-height:300px;}
.wt_sc_other_addons_features ul{ list-style:none; margin:0px; }
.wt_sc_other_addons_features li{ float:left; width:calc(100% - 23px); margin-left:23px; box-sizing:border-box; padding-left:23px; padding:7px 0px; }
.wt_sc_other_addons_features li .dashicons{ margin-left:-23px; float:left; color:#6abe45; }

.wt_sc_other_addons_visit_btn{ float:left; height:auto; width:100%; box-sizing:border-box; height:50px; line-height:50px; color:#fff; text-decoration:none; text-align:center; font-size:15px; background:#1DA5F8; }
.wt_sc_other_addons_visit_btn:hover{ color:#fff; text-decoration:none; }

.wt_sc_other_addons_video_title{ width:100%; box-sizing:border-box; padding:0px 20px; float:left; text-align:center; font-size:14px; margin-top:30px; }
.wt_sc_other_addons_video_box{ width:100%; box-sizing:border-box; padding:0px 20px; float:left; height:200px; }


@media only screen and (max-width:1150px) {
    .wt_sc_advantages{ border-radius:4px; }
    .wt_sc_advantages_box{width:100%; padding:4px; box-sizing:border-box;}
    .wt_sc_advantages_txt{ width:calc(100% - 65px); min-width:200px; margin-right:0px !important;}
    .wt_sc_advantages_img{ margin-left:0px !important; }
}

@media only screen and (max-width:960px) {
    .wt_sc_upgrade_to_pro_left{  margin-bottom:60px; }
    .wt_sc_upgrade_to_pro_left, .wt_sc_upgrade_to_pro_right{ float:left; width:100%; }
    .wt_sc_other_addons_box{ width:45%; }
    .wt_sc_middle_section{ margin-top:10px; }
    .wt_sc_advantages{ width:100%; }
    .wt_sc_middle_section_box{ float:left; width:100%; }
}
@media only screen and (max-width:600px) {
   .wt_sc_other_addons_box{ width:100%; min-width:0; }
}
</style>

<!-- Smart Coupon Premium -->
<div class="wt_sc_upgrade_to_pro">
    <div class="wt_sc_upgrade_to_pro_left">
        <img src="<?php echo esc_attr($module_img_path);?>plugin_pro_icon.svg" class="wt_sc_pro_plugin_logo">
        <div class="wt_sc_pro_plugin_title_box">
            <h3 class="wt_sc_pro_plugin_title"><?php _e('Smart Coupon for Woocommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            <div class="wt_sc_pro_plugin_rating">⭐️ ⭐️ ⭐️ ⭐️ ⭐️ 4.9</div>
        </div>
        <div class="wt_sc_pro_plugin_desc">
            <?php _e('Create coupons to offer discounts and free products to your customers with Smart Coupons for WooCommerce.', 'wt-smart-coupons-for-woocommerce'); ?>
        </div>
        <div class="wt_sc_pro_plugin_features">
            <ul>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'BOGO coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Coupons based on location','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Cart abandonment coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Product giveaway coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Store credits','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Purchase history-based coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Signup coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Bulk generate coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Banners with sales expiry timer','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Import or export coupons','wt-smart-coupons-for-woocommerce'); ?></li>
            </ul>
        </div>
        <a href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons" class="wt_sc_upgrade_to_pro_btn"><img src="<?php echo esc_attr($admin_img_path);?>pro_icon.svg"> <?php _e('Upgrade to Premium', 'wt-smart-coupons-for-woocommerce'); ?></a>
    </div>
    <div class="wt_sc_upgrade_to_pro_right">
        <div class="wt_sc_upgrade_to_pro_setup_video_box">
            <h4><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4> 
            <iframe src="//www.youtube.com/embed/IY4cmdUBw4A?rel=0" allowfullscreen="allowfullscreen" style="width:100%;" frameborder="0" align="middle"></iframe>
        </div> 
    </div> 
</div>

<div class="wt_sc_middle_section">
    <div class="wt_sc_middle_section_box">
        <h3 style="font-size:22px;"><?php _e('Additional Plugins to Market Your Products Better', 'wt-smart-coupons-for-woocommerce'); ?></h3>
    </div>
    <div class="wt_sc_middle_section_box">
        <div class="wt_sc_advantages">
            <div class="wt_sc_advantages_box">
                <img src="<?php echo esc_attr($module_img_path);?>30day.svg" class="wt_sc_advantages_img" style="margin-left:-30px;">
                <div class="wt_sc_advantages_txt" style="margin-right:4%;"><?php _e('30 Day 100% No Risk Money Back Guarantee', 'wt-smart-coupons-for-woocommerce'); ?></div>
            </div>
            <div class="wt_sc_advantages_box">
                <img src="<?php echo esc_attr($module_img_path);?>99_percent.svg" class="wt_sc_advantages_img">
                <div class="wt_sc_advantages_txt"><?php _e('Fast and Proirity Support with 99% Satisfaction Rating', 'wt-smart-coupons-for-woocommerce'); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="wt_sc_other_addons">

    <!-- Add on plugin details -->
    <div class="wt_sc_other_addons_container">
        
        <!-- URL Plugin -->
        <div class="wt_sc_other_addons_box">
            
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>url_coupon_icon.svg">
                <h3><?php _e('URL Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>

            <div class="wt_sc_other_addons_desc"><?php _e('Get sharable URLs and QR codes for your coupons!', 'wt-smart-coupons-for-woocommerce'); ?></div>

            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Create unique URLs for coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Generate QR codes for coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Auto-apply coupons on click','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Automatically add products to the cart','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Redirection users to specific pages','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Simple to use and easy to understand','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>

            <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=URL_Coupons"><?php _e('Get the plugin', 'wt-smart-coupons-for-woocommerce'); ?> → </a>

            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/80JyXvalx6E?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            
        </div>

        <!-- Gift card Plugin -->
        <div class="wt_sc_other_addons_box">
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>gift_cards_icon.svg">
                <h3><?php _e('WooCommerce Gift Cards', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>

            <div class="wt_sc_other_addons_desc"><?php _e('Create & manage gift cards for your store.', 'wt-smart-coupons-for-woocommerce'); ?></div>

            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Create gift cards products','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Email gift cards to customers','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('20+ predefined gift card templates','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Category-wise template listing','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Upload custom gift card templates','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Generate gift cards based on order status','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Refund to Store credit','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>
            <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Gift_Cards"><?php _e('Get the plugin', 'wt-smart-coupons-for-woocommerce'); ?> → </a>


            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/bKmGBG9U1uY?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            
        </div>

        <!-- Display Discount Plugin -->
        <div class="wt_sc_other_addons_box">
            
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>display_discounts_icon.svg">
                <h3><?php _e('Display Discounts for WooCommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>

            <div class="wt_sc_other_addons_desc"><?php _e('The best way to market your coupons in-house!', 'wt-smart-coupons-for-woocommerce'); ?></div>

            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'List discounts on WooCommerce product pages','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Add countdown timers to time-limited coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Multiple coupon display template','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Show restriction info within the coupon','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Limit the number of coupons to display','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>

            <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/display-woocommerce-discounts/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=Display_Discounts"><?php _e('Get the plugin', 'wt-smart-coupons-for-woocommerce'); ?> → </a>

            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/yJKUjqzdKUk?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            
        </div>
    </div>
</div>