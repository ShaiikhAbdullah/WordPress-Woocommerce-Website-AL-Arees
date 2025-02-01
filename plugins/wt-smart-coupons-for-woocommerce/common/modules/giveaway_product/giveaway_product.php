<?php
/**
 * Giveaway product admin/public section
 *
 * @link       
 * @since 1.3.9  
 *
 * @package  Wt_Smart_Coupon
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Giveaway_Product
{
    public $module_base='giveaway_product';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public static $bogo_coupon_type_name='wt_sc_bogo'; /* bogo coupon type name */
    public static $meta_arr=array();
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        self::$meta_arr=array(
            '_wt_free_product_ids'=>array(
                'default'=>'', /* default value */
                'type'=>'text', /* value type */
            ),
            '_wt_sc_bogo_apply_frequency'=>array(
                'default'=>'once',
                'type'=>'text',
            ),
            '_wt_sc_bogo_customer_gets'=>array(
                'default'=>'specific_product', 
                'type'=>'text',
            ),
            '_wt_sc_bogo_product_condition'=>array(
                'default'=>'and', 
                'type'=>'text',
            ),
            '_wt_sc_bogo_free_products'=>array(
                'default'=>array(), 
                'type'=>'text_arr',
            ),
            'wt_apply_discount_before_tax_calculation'=>array(
                'default'=>true,
                'type'=>'boolean',
            ),
        );

        add_filter('woocommerce_coupon_discount_types', array($this, 'add_bogo_coupon_type'));
    }

    /**
     * Get Instance
    */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Giveaway_Product();
        }
        return self::$instance;
    }

    /**
     * Register BOGO coupon type
     * @since 1.3.9
     */
    public function add_bogo_coupon_type($discount_types)
    {
        $discount_types[self::$bogo_coupon_type_name] = __('BOGO (Buy X Get X/Y) offer', 'wt-smart-coupons-for-woocommerce');
        return $discount_types;
    }

    /**
     *  Prepare giveaway data for order detail section
     */
    public function prepare_giveaway_info_for_order($order_item_id, $order_item, $order)
    {
        if(wc_get_order_item_meta($order_item_id, 'free_product', true)=='wt_give_away_product')
        {
            $coupon_code = wc_get_order_item_meta($order_item_id,'free_gift_coupon',true);
            $coupon_id=wc_get_coupon_id_by_code($coupon_code) ;
            if($coupon_id)
            {
                $item_id = ($order_item['variation_id']>0 ? $order_item['variation_id'] : $order_item['product_id']);
                $product = wc_get_product($item_id);
                if(!$product instanceof WC_Product)
                {
                    return false;
                }
                $product_price = (float) self::get_product_price($product)*$order_item['quantity'];
                $giveaway_data=$this->get_product_giveaway_data($item_id, $coupon_code, $order_item);
                $discount= (float) self::get_available_discount_for_giveaway_product($product, $giveaway_data)*$order_item['quantity'];
                $sale_price_after_discount=($product_price - $discount);
                $value = '<del><span>'.Wt_Smart_Coupon_Admin::get_formatted_price( ( number_format((float) $product_price,2,'.','' ) ) ).'</span></del> <span>'.Wt_Smart_Coupon_Admin::get_formatted_price( ( number_format((float) $sale_price_after_discount,2,'.','' ) ) ).'</span>';
                return $value;
            }
        }
        return false;
    }

    public function get_product_giveaway_data($item_id, $coupon_code, $cart_item=array())
    {
        $coupon=new WC_Coupon($coupon_code);
        $coupon_id=$coupon->get_id();
        $product=wc_get_product($item_id);
        
        $product_id=$item_id;
        $variation_id=0;
        if($product->is_type("variation"))
        {
            $product_id=$product->get_parent_id();  
            $variation_id=$item_id;  
        }

        $bogo_customer_gets = $this->get_coupon_meta_value($coupon_id, '_wt_sc_bogo_customer_gets'); 
        if(self::is_bogo($coupon)) /* product specific discount options */
        {
            if('specific_product'==$bogo_customer_gets)
            {
                $bogo_products=self::get_all_bogo_giveaway_products($coupon_id);
                if($variation_id>0 && isset($bogo_products[$variation_id]))
                {
                    return $bogo_products[$variation_id];

                }elseif(isset($bogo_products[$product_id]))
                {
                    return $bogo_products[$product_id];
                }
            }
        }else
        {
            /* global discount options */
            return array(
                'qty'=>1, 
                'price'=>100, 
                'price_type'=>'percent',
            );
        }

        return self::get_dummy_qty_price();
    }

    /**
     * Is current coupon is BOGO.
     */
    public static function is_bogo($coupon)
    {
        return $coupon->is_type(self::$bogo_coupon_type_name);
    }

    public static function customer_gets_data_arr()
    {
        $customer_gets=array(
            'specific_product'=>__('Specific product', 'wt-smart-coupons-for-woocommerce'),
            'any_product_from_category'=>__('Any product from specific category (Pro)', 'wt-smart-coupons-for-woocommerce'),
            'any_product_from_store'=>__('Any product in store (Pro)', 'wt-smart-coupons-for-woocommerce'),
            'same_product_in_the_cart'=>__('Same product as in the cart (Pro)', 'wt-smart-coupons-for-woocommerce'),
        );

        return apply_filters('wt_sc_intl_alter_customer_gets_data_arr', $customer_gets);
    }

    public static function customer_gets_help_arr()
    {
        $customer_gets_help=array(
            'specific_product'=>__('Choose what the customers get for free or with a discount if the cart eligibility or conditions are met. Your customers will get below selected product/s for free or with a discount.', 'wt-smart-coupons-for-woocommerce'),
            'any_product_from_category'=>__('Choose what the customers get for free or with a discount if the cart eligibility or conditions are met. Your customers will get product/s from below selected category for free or with a discount.', 'wt-smart-coupons-for-woocommerce'),
            'any_product_from_store'=>__('Choose what the customers get for free or with a discount if the cart eligibility or conditions are met. Your customers will get any product/s from the store that are eligible for free or with a discount.', 'wt-smart-coupons-for-woocommerce'),
            'same_product_in_the_cart'=>__('Choose what the customers get for free or with a discount if the cart eligibility or conditions are met. Your customers will get the same product as in the cart that are configured in product restriction section.', 'wt-smart-coupons-for-woocommerce'),
            'any_product_from_category_in_the_cart'=>__('Choose what the customers get for free or with a discount if the cart eligibility or conditions are met. Your customers will get a product from the same category as in the cart that are configured in the category restriction section.', 'wt-smart-coupons-for-woocommerce'),
        );

        return apply_filters('wt_sc_intl_alter_customer_gets_help_arr', $customer_gets_help);
    }

    public static function get_dummy_qty_price()
    {
        return array('qty'=>1, 'price'=>100, 'price_type'=>'percent');
    }

    public static function prepare_items_data($item_ids, $wt_sc_items_data)
    {
        $dummy_qty_price=self::get_dummy_qty_price();
        $items_data=array();
        if(!empty($item_ids)) /* prepare dummy quantity and price data from default giveaway fields */
        {
            $qty_price_dummy=array_fill(0, count($item_ids), $dummy_qty_price);
            $items_data=array_combine($item_ids, $qty_price_dummy);
        }

        if(!empty($wt_sc_items_data)) /* meta data, merge with default giveaway product data */
        {
            foreach($items_data as $item_id=>$item_data)
            {
                $items_data[$item_id]=(isset($wt_sc_items_data[$item_id]) ? $wt_sc_items_data[$item_id] : $item_data);
            }
        }
        return $items_data;
    }

    /**
     *  @since 1.4.0
     *  Prepare meta value, If meta not exists, use default value
     */
    public static function get_coupon_meta_value($post_id, $meta_key, $default='')
    {
        $default_vl=(isset(self::$meta_arr[$meta_key]) && isset(self::$meta_arr[$meta_key]['default']) ? self::$meta_arr[$meta_key]['default'] : $default);
        return (metadata_exists('post', $post_id, $meta_key) ? get_post_meta($post_id, $meta_key, true) : $default_vl);
    }

    /**
     *  Get giveaway products id from coupon meta
     *  @return array
     */
    public static function get_giveaway_products($post_id)
    {
        $free_product_id_arr=array();
        try {
            
            $free_product_ids   = self::get_instance()->get_coupon_meta_value($post_id, '_wt_free_product_ids');      
            if($free_product_ids && is_string($free_product_ids))
            {
                $free_product_id_arr = explode(',', $free_product_ids);
            }

        } catch (Exception $exception) {
                
            $free_product_id_arr=array();
        }

        return $free_product_id_arr;
    }

    /**
     *  Checks all products are completely free
     *  Only for BOGO with specific product condition
     *  @since 1.4.0
     */
    public static function is_full_free_bogo($post_id)
    {
        $is_completely_free=true;
        $product_data_arr=self::get_all_bogo_giveaway_products($post_id);
        foreach($product_data_arr as $product_id=>$product_data)
        {
            $product = wc_get_product($product_id);
            if(!is_object($product))
            {
                continue; //product not exists, so skip
            }
            
            if('percent'==$product_data['price_type'])
            {
                if($product_data['price']=="" || $product_data['price']==100)
                {
                   continue; //current item is 100% free 
                }else
                {
                    $is_completely_free=false;
                    break; //current item is not completely free, so break the loop
                }
                
            }else //flat type price
            {
                if($product_data['price']==$product->get_price())
                {
                    continue; //current item is 100% free
                }else
                {
                    $is_completely_free=false;
                    break; //current item is not completely free, so break the loop
                }
            }
        }
        return $is_completely_free;
    }

    /**
     *  Function to check is 100% free giveaway item
     *  @since 1.4.0
     */
    public static function is_full_free_item($product, $giveaway_data)
    {
        $product_price=self::get_product_price($product);
        
        $discount = self::get_available_discount_for_giveaway_product($product, $giveaway_data);
        return ($discount===$product_price ? true : false);
    }

    /**
     * Function to get actual discount available for a giveaway item.
     * @since 1.4.0
     */
    public static function get_available_discount_for_giveaway_product($product, $giveaway_data)
    {     
        $product_price=self::get_product_price($product);
        $discount = $product_price;
        if(isset($giveaway_data['price']) && ''!=$giveaway_data['price'] && isset($giveaway_data['price_type']) && ''!=$giveaway_data['price_type'])
        {
            if('percent'==$giveaway_data['price_type'])
            {
                $discount=($product_price * $giveaway_data['price']/100);
            }else
            {
                $discount = min($giveaway_data['price'], $product_price);
            }            
        }

        return $discount;
    }

    public static function get_product_price($product)
    {
        if($product->is_on_sale())
        { 
            $product_price = $product->get_sale_price();
        } else {
            $product_price = $product->get_regular_price();
        }

        if("" === $product_price)
        {
            $product_price = $product->get_price();
        }

        $product_price = (float) $product_price;
        
        /**
         *  Alter product price of giveaway item
         * 
         *  @since 1.4.5
         *  @param $product_price   float           Price of the product
         *  @param $product         WC_Product      Product object
         */
        return apply_filters('wt_sc_alter_giveaway_product_price', $product_price, $product); 
    }

    public static function get_all_bogo_giveaway_products($post_id)
    {
        $free_product_id_arr=self::get_giveaway_products($post_id);
        $bogo_free_products = self::get_coupon_meta_value($post_id, '_wt_sc_bogo_free_products');
        return apply_filters('wt_sc_alter_bogo_giveaway_products', self::prepare_items_data($free_product_id_arr, $bogo_free_products), $post_id);
    }
}
Wt_Smart_Coupon_Giveaway_Product::get_instance();