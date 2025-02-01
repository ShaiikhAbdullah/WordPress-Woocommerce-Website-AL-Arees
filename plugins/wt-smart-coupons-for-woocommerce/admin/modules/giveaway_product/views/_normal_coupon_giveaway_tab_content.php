<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="options_group wt_sc_normal_coupon_giveaway_tab_content">
    <p class="form-field"><label><?php _e('Free Product', 'wt-smart-coupons-for-woocommerce' ); ?></label>
        <select class="wc-product-search" style="width: 50%;" name="_wt_free_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product...', 'wt-smart-coupons-for-woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations_without_parent"  data-allow_clear="true">
            <?php
            if(!empty($free_product_id_arr))
            {
                foreach($free_product_id_arr as $product_id)
                {
                    $product = wc_get_product($product_id);
                    if(is_object($product))
                    {
                        echo '<option value="'.esc_attr($product_id).'"'.selected(true, true, false).'>'.wp_kses_post($product->get_formatted_name()) . '</option>';
                    }
                }
            }                   
            ?>
        </select><?php echo wc_help_tip( __("A single quantity of the specified free product is added to the customer's cart when the coupon is applied. However, the corresponding tax and shipping charges are not exempted.", 'wt-smart-coupons-for-woocommerce' ) ); ?>
        <table style="background:#FFF4E8; width:calc(100% - 30px); margin-left: auto; margin-right: auto; margin-top: 70px; padding:10px 15px; box-sizing:border-box; border-left:solid 3px #E97900; color:#575757; margin-bottom:20px; border-spacing:0px; border-collapse:collapse;">
            <tr>
                <td style="padding:15px; background:#FFF4E8;">
                    <div style="width:100%; font-size:16px; font-weight:bold; color:#E97900;"><span><img src="<?php echo esc_url(WT_SMARTCOUPON_MAIN_URL . 'admin/images/idea_bulb_orange.svg');?>" style="width:16px;"></span>&nbsp;<?php esc_html_e('Create better BOGO deals', 'wt-smart-coupons-for-woocommerce'); ?></div>
                    <div style="width:100%; font-size:14px; color:##555555;"><?php esc_html_e('Upgrade to premium and enjoy advanced BOGO features for giveaway products, such as a custom quantity counter, dynamic pricing discounts, and more.', 'wt-smart-coupons-for-woocommerce'); ?></div>
                </td>
                <td style="width:100px; vertical-align:middle; padding:15px 15px 15px 5px; background:#FFF4E8;">
                    <a style="background:#E97900; color:#fff; border:none;" class="button button-secondary" href="<?php echo esc_attr('https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_smart_coupon_giveaway&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION) ; ?>" target="_blank"><?php esc_html_e('Check out this plugin', 'wt-smart-coupons-for-woocommerce'); ?> <span class="dashicons dashicons-arrow-right-alt" style="margin-top:8px;font-size:14px;"></span> </a>
                </td>
            </tr>
        </table>
    </p>
</div>