<?php 
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_sc_giveaway_products_cart_page">
        <?php
        foreach($free_products as $coupon_code=>$free_product_items)
        {
            if(empty($free_product_items))
            {
                continue;
            }
            
            $message=__('Congratulations! Choose your freebie from below:', 'wt-smart-coupons-for-woocommerce');
            $coupon_id=wc_get_coupon_id_by_code($coupon_code);
            $message_html = '<h4 class="giveaway-title">'.$message.'<span class="coupon-code">[ '.$coupon_code.' ]</span></h4>';
            $message_html = apply_filters('wt_smartcoupon_give_away_message', $message_html, $coupon_code, $coupon_id);
            echo wp_kses_post($message_html);

            $single_add_to_cart=true;
            if(isset($add_to_cart_all[$coupon_id]) && $add_to_cart_all[$coupon_id]===true)
            {
                $single_add_to_cart=false;
            }
            ?>
            <ul class="woocommcerce wt_give_away_products" coupon="<?php echo esc_attr($coupon_id);?>">
                <?php 
                $total_purchasable=0;
                foreach($free_product_items as $product_id => $product_data)
                {
                    $_product = wc_get_product( $product_id );          
                    if ( ! $_product || ( $_product->get_stock_quantity() &&   $_product->get_stock_quantity() < 1 ) ) {
                       continue;
                    }

                    /* product image */
                    $image = wp_get_attachment_image_src( $_product->get_image_id(), 'woocommerce_thumbnail');            
                    if(!$image)
                    {                
                        $parent_product = wc_get_product( $_product->get_parent_id() );
                        if($parent_product)
                        {
                            $image = wp_get_attachment_image_src($parent_product->get_image_id(), 'woocommerce_thumbnail');
                        }
                    }

                    if(!$image) /* image not available so use placeholder image */
                    {
                        $dimensions = wc_get_image_size('woocommerce_thumbnail');                             
                        $image = array(wc_placeholder_img_src('woocommerce_thumbnail'), $dimensions['width'], $dimensions['height'], false);
                    }
                    $variation_attributes=array(); /* this applicable only for variable products */
                    $is_purchasable=$this->is_purchasable($_product, $variation_attributes);
                    if($is_purchasable)
                    {
                        $total_purchasable++;   
                    }
                    $temp_product_id = $product_id;
                    $variation_without_attributes = false;
                    if( $_product->is_type('variation') ){
                        foreach( $_product->get_variation_attributes() as $attribute_name => $options ){
                            if( '' === $options ){
                                $temp_product_id = $_product->get_parent_id();
                                $variation_without_attributes = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <li class="wt_get_away_product" title="<?php echo esc_attr($_product->get_name()); ?>" data-is_purchasable="<?php echo esc_attr($is_purchasable ? 1 : 0); ?>" product-id="<?php echo esc_attr( $temp_product_id ); ?>">
                        <div class="wt_product_image">
                            <?php
                            if($image && is_array($image) && isset($image[0]))
                            {
                                ?>
                                <img src="<?php echo esc_attr($image[0]); ?>" data-id="<?php echo esc_attr($product_id); ?>" />
                                <?php
                            }else
                            {
                                ?>
                                <div class="wt_sc_dummy_img"></div>
                                <?php
                            }
                            if($is_purchasable)
                            {
                            ?> 
                                <div class="wt_product_discount">
                                    <?php 
                                    if(!$_product->is_type('variable'))
                                    {
                                    ?>
                                        <div>
                                            <?php _e('Price: ', 'wt-smart-coupons-for-woocommerce'); ?>
                                            <?php echo wp_kses_post($_product->get_price_html()); ?>                        
                                        </div>
                                    <?php 
                                    }
                                    ?>
                                    <div>
                                        <?php _e('Discount: ', 'wt-smart-coupons-for-woocommerce'); ?> 
                                        <?php
                                        echo wp_kses_post($this->get_give_away_discount_text(0, $product_data));
                                        ?>
                                    </div>
                                </div>
                            <?php
                            }else
                            {
                                ?>
                                <p class="wt_sc_product_out_of_stock stock out-of-stock"><?php _e('Sorry! this product is not available for giveaway.', 'woocommerce'); ?></p>
                                <?php
                            } 
                            ?>                     
                        </div>
                        <div class="wt_product_other_info">
                            <a href="<?php echo esc_attr(get_post_permalink($product_id)); ?>">
                                <?php echo esc_html(wp_trim_words($_product->get_name(), 6)); ?>
                            </a>
                        </div>
                        <div class="wt_choose_button_box">
                        <?php 
                        if($_product->is_type('variable')) /* variation choosing option */
                        {
                            if($is_purchasable)
                            {
                                ?>
                                <table class="variations wt_variations" cellspacing="0">
                                    <tbody>
                                    <?php
                                    foreach($_product->get_variation_attributes() as $attribute_name => $options)
                                    { 
                                    ?>
                                        <tr>
                                            <td class="value">
                                                <label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo esc_html(wc_attribute_label($attribute_name)); ?></label>
                                                <?php
                                                    wc_dropdown_variation_attribute_options( 
                                                        array( 
                                                            'options'           => $options,
                                                            'attribute'         => $attribute_name,
                                                            'product'           => $_product,
                                                            'selected'          => $variation_attributes['attribute_'.sanitize_title($attribute_name)],
                                                            'class'             => 'wt_give_away_product_attr',
                                                            'show_option_none'  => false
                                                        ) 
                                                    );
                                                    
                                                ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php 
                                $selected_variation_id = $is_purchasable;
                                ?>
                                <input type="hidden" name="variation_id" value="<?php echo esc_attr($selected_variation_id); ?>" />
                                <input type="hidden" name="wt_product_id" value="<?php echo esc_attr($product_id); ?>" />
                                <input type="hidden" name="wt_variation_options" value='<?php echo esc_attr(json_encode($variation_attributes)); ?>' />
                                <?php
                            }
                        }
                        if( $_product->is_type( 'variation' ) && $variation_without_attributes ) /* variation product with no attribute */
                        {
                            if( $is_purchasable )
                            {
                                $variation_id   = $product_id;
                                $product_id     = $_product->get_parent_id();
                                ?>
                                <input type="hidden" name="variation_id" value="<?php echo esc_attr( $variation_id ); ?>">
                                <input type="hidden" name="wt_product_id" value="<?php echo esc_attr( $product_id ); ?>" />
                                <input type="hidden" name="wt_variation_options" value='<?php echo esc_attr( json_encode( $variation_attributes ) ); ?>' />
                                <?php
                                $parent_product = wc_get_product( $product_id );
                                $variation_attributes = $_product->get_variation_attributes();
                                foreach( $variation_attributes as $attribute_name => $options ){
                                    if( '' === $options ){
                                        $variation_attributes[$attribute_name] = explode( ', ', $parent_product->get_attribute( str_replace( 'attribute_', '', $attribute_name ) ) );
                                    }else{
                                        $variation_attributes[$attribute_name] = [$options];
                                    }
                                    
                                }
                                ?>
                                    <table class="variations wt_variations" cellspacing="0">
                                        <tbody>
                                        <?php
                                        foreach( $variation_attributes as $attribute_name => $options )
                                        { 
                                        ?>
                                            <tr>
                                                <td class="value">
                                                    <label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo esc_html( wc_attribute_label( str_replace( 'attribute_', '', $attribute_name ) ) ); ?></label>
                                                    <select id=<?php echo esc_attr( $attribute_name );?> class="wt_give_away_product_attr" name=<?php echo esc_attr( $attribute_name );?> data-attribute_name=<?php echo esc_attr( $attribute_name );?> data-show_option_none="no">
                                                    <option value=""><?php esc_html_e( 'Choose an option', 'wt-smart-coupons-for-woocommerce' ); ?></option>
                                                    <?php
                                                        foreach( $options as $key => $value )
                                                        {
                                                            ?>
                                                                <option value="<?php echo esc_attr( $value );?>"><?php echo esc_html( $value );?></option>
                                                            <?php
                                                        }
                                                        
                                                    ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                
                                <?php
                                if( $single_add_to_cart && $is_purchasable ) 
                                {
                                    ?>
                                        <button class="wt_choose_free_product button" prod-id="<?php echo esc_attr( $product_id ); ?>" variation="<?php echo esc_attr( $variation_id ); ?>" type="button"><?php echo __( 'Choose Product','wt-smart-coupons-for-woocommerce' ); ?></button>
                                    <?php
                                }
                            }
                        }
                        ?>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php 
            if(!$single_add_to_cart && $total_purchasable>0)
            {
                $button_label=($total_purchasable>1 ? __('Add all to cart', 'wt-smart-coupons-for-woocommerce') : __('Add to cart', 'wt-smart-coupons-for-woocommerce'));
                ?>
                <div class="wt_add_to_cart_all">
                    <button class="wt_add_to_cart_all_btn button" coupon-id="<?php echo esc_attr($coupon_id); ?>" type="button"><?php echo esc_html($button_label); ?></button>
                </div>
                <?php
            }
        }              
        ?>
</div>