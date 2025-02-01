<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="options_group wt_sc_bogo_coupon_giveaway_tab_content">
    <fieldset class="form-field" style="padding-bottom:0px !important;">
        <legend><?php _e('Customer gets', 'wt-smart-coupons-for-woocommerce'); ?></legend>
        <select name="_wt_sc_bogo_customer_gets" class="wt_sc_form_toggle" wt_sc_form_toggle-target="_wt_sc_bogo_customer_gets">
            <?php
            foreach(self::customer_gets_data_arr() as $customer_get_k=>$customer_get_v)
            {
                ?>
                <option <?php echo ($customer_get_k=="" ? 'disabled="disabled"' : ''); ?> value="<?php echo esc_attr($customer_get_k);?>" <?php selected($customer_get_k, $bogo_customer_gets);?>><?php echo esc_html($customer_get_v);?></option>
                <?php
            }
            ?>
        </select>
        <?php
        foreach(self::customer_gets_help_arr() as $customer_get_k=>$customer_get_v)
        {
            ?>
            <p class="wt_sc_help_text wt_sc_conditional_help_text" style="margin:0px" data-sc-help-condition="[_wt_sc_bogo_customer_gets=<?php echo esc_attr($customer_get_k);?>]"><?php echo esc_html($customer_get_v);?></p>
            <?php
        }
        ?>
    </fieldset>

    <!-- Specific products -->
    <fieldset class="form-field wt_sc_bogo_products_fieldset wt_sc_coupon_fieldset" wt_sc_form_toggle-id="_wt_sc_bogo_customer_gets" wt_sc_form_toggle-val="specific_product" wt_sc_form_toggle-level="1">
        <legend><?php _e('Products', 'wt-smart-coupons-for-woocommerce'); ?></legend>
        <table class="wt_sc_coupon_meta_item_table" id="wt_sc_bogo_customer_gets_products">
            <thead>
                <tr>
                    <th><?php _e('Product', 'wt-smart-coupons-for-woocommerce');?></th>
                    <th><?php _e('Quantity', 'wt-smart-coupons-for-woocommerce');?></th>
                    <th colspan="2"><?php _e('Discount', 'wt-smart-coupons-for-woocommerce');?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(empty($bogo_products_data)) /* add a dummy item for first time use */
                {
                    $bogo_products_data=array();
                    $bogo_products_data['--']=$dummy_qty_price;
                }
                $field_index=0;
                foreach($bogo_products_data as $product_id=>$product_data)
                {
                    $product_title = '';
                    if('--' !== $product_id) /* not a dummy item */
                    {
                        $product = wc_get_product($product_id);
                        if ( ! is_object( $product ) ) {
                            $product_title = __( 'Product not found !!!', 'wt-smart-coupons-for-woocommerce' );
                        } else {
                            $product_title = wp_strip_all_tags( $product->get_formatted_name() );
                        }
                    }
                    ?>
                    <tr>
                        <td class="wt_sc_meta_item_tb_item">
                            <select class="wt_sc_product_search wt_sc_select2" data-default-val="" name="_wt_sc_bogo_free_product_ids[<?php echo esc_attr($field_index);?>]" data-placeholder="<?php esc_attr_e( 'Search for a product...', 'wt-smart-coupons-for-woocommerce' ); ?>">
                            <?php 
                            if ( '--' !== $product_id ) { /* not a dummy item */                          
                                echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . esc_html( $product_title ) . '</option>';
                            }
                            ?>
                            </select>
                        </td>
                        <td class="wt_sc_meta_item_tb_qty wt_sc_meta_item_tb_other">
                            <input type="number" name="_wt_sc_bogo_free_product_qty[<?php echo esc_attr($field_index);?>]" value="<?php echo esc_attr($product_data['qty']);?>" data-default-val="1" min="1" step="1">
                        </td>
                        <td class="wt_sc_meta_item_tb_price">
                            <input type="number" name="_wt_sc_bogo_free_product_price[<?php echo esc_attr($field_index);?>]" value="<?php echo esc_attr($product_data['price']);?>" data-default-val="100" min="0" step="any">
                        </td>
                        <td class="wt_sc_meta_item_tb_discount wt_sc_meta_item_tb_other" style="padding-left:0px;">
                            <select name="_wt_sc_bogo_free_product_price_type[<?php echo esc_attr($field_index);?>]" data-default-val="percent">
                                <option value="percent" <?php selected('percent', $product_data['price_type']);?>>%</option>
                                <option value="flat" <?php selected('flat', $product_data['price_type']);?>><?php echo esc_html(get_woocommerce_currency_symbol()); ?></option>                               
                            </select>
                        </td>
                        <td class="wt_sc_meta_item_tb_action">
                            <span class="dashicons dashicons-dismiss wt_sc_meta_item_tb_delete_row" title="<?php _e('Remove row', 'wt-smart-coupons-for-woocommerce');?>"></span>
                        </td>
                    </tr>
                    <?php 
                    $field_index++;
                }
                ?>
                <tr>
                    <td colspan="5" class="wt_sc_add_new_row_btn_td">
                        <button type="button" class="wt_sc_meta_item_tb_add_row" title="<?php _e('Add new row', 'wt-smart-coupons-for-woocommerce');?>">+</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <table style="background:#FFF4E8; width:calc(100% - 30px); margin-left: auto; margin-right: auto; padding:10px 15px; box-sizing:border-box; border-left:solid 3px #E97900; color:#575757; margin-bottom:20px; border-spacing:0px; border-collapse:collapse;">
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
</div>