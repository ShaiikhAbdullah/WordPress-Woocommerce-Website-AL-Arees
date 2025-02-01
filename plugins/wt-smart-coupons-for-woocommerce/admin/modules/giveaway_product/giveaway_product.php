<?php
/**
 * Giveaway products admin section
 *
 * @link       
 * @since 1.4.0    
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Wt_Smart_Coupon_Giveaway_Product')) /* common module class not found so return */
{
    return;
}
class Wt_Smart_Coupon_Giveaway_Product_Admin extends Wt_Smart_Coupon_Giveaway_Product
{
    public $module_base='giveaway_product';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 10, 0);

        /**
         *  Add fields on coupon general settings
         */
        add_action('woocommerce_coupon_options', array($this, 'add_general_settings_fields'), 6, 2);

        add_filter('woocommerce_coupon_data_tabs', array($this, 'add_give_way_coupon_data_tab'), 21, 1);
        add_action('woocommerce_coupon_data_panels', array($this, 'give_away_free_product_tab_content'), 10, 1);
        add_action('woocommerce_process_shop_coupon_meta', array($this, 'process_shop_coupon_meta_give_away'), 11, 2);
        add_action('wp_ajax_woocommerce_json_search_products_and_variations_without_parent', array($this, 'wt_products_and_variations_no_parent'));
      

        /* Giveaway details into order detail table */
        add_action('woocommerce_admin_order_totals_after_tax', array($this, 'add_giveaway_info_to_order_detail_table'));

        /**
         *  Help text for coupon restriction section
         *  @since  1.4.3
         */
        add_filter('wt_sc_intl_alter_discount_type_help_arr', array($this, 'add_discount_type_help_text'), 10, 2); 
    }

    /**
     * Get Instance
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Giveaway_Product_Admin();
        }
        return self::$instance;
    }

    /**
     *  Save giveaway related meta data
     */
    public function process_shop_coupon_meta_give_away($post_id, $post)
    {
        
        if(!current_user_can('manage_woocommerce')) 
        {
            wp_die(__('You do not have sufficient permission to perform this operation', 'wt-smart-coupons-for-woocommerce'));
        }

        $discount_type=sanitize_text_field($_POST['discount_type']);

        /* product data */
        $bogo_free_products=$this->prepare_meta_data_from_post_data('_wt_sc_bogo_free_product_ids', '_wt_sc_bogo_free_product_qty', '_wt_sc_bogo_free_product_price', '_wt_sc_bogo_free_product_price_type');
        update_post_meta($post_id, '_wt_sc_bogo_free_products', $bogo_free_products);

        
        if($discount_type===self::$bogo_coupon_type_name)
        {
            $wt_sc_bogo_free_categories=array();
            $wt_product_condition='and'; //all products option for BOGO
            $wt_category_condition='and'; 
            
            delete_post_meta($post_id, 'product_categories');
            delete_post_meta($post_id, 'exclude_product_categories');
            delete_post_meta($post_id, '_wt_category_condition');
        }else
        {
            $wt_sc_bogo_free_categories=$this->prepare_meta_data_from_post_data('_wt_sc_bogo_free_category_ids', '_wt_sc_bogo_free_category_qty', '_wt_sc_bogo_free_category_price', '_wt_sc_bogo_free_category_price_type');
            $wt_product_condition=sanitize_text_field($_POST['_wt_product_condition']);
            $wt_category_condition=sanitize_text_field($_POST['_wt_category_condition']);
        }
      
        update_post_meta($post_id, '_wt_sc_bogo_free_categories', $wt_sc_bogo_free_categories);       
        update_post_meta($post_id, '_wt_product_condition', $wt_product_condition);
        
        update_post_meta($post_id, '_wt_sc_bogo_customer_gets', 'specific_product'); //bogo customer gets
        update_post_meta($post_id, '_wt_sc_bogo_product_condition', 'and'); //bogo product condition
        


        // Giveaway free Products.
        if(isset($_POST['_wt_free_product_ids']) && $_POST['_wt_free_product_ids']!='')
        {
            $free_product_ids=Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_free_product_ids'], 'int_arr');
            update_post_meta($post_id, '_wt_free_product_ids', implode(',', $free_product_ids));
        }else
        {
            update_post_meta($post_id, '_wt_free_product_ids', '');
        }


        $skip_post_arr=array(
            '_wt_sc_bogo_free_products', '_wt_free_product_ids', '_wt_product_condition', '_wt_category_condition', '_wt_sc_bogo_customer_gets'
        ); /* fields that skip from below meta data update loop */
        
        foreach(self::$meta_arr as $mata_key=>$meta_info)
        {
            if(in_array($mata_key, $skip_post_arr))
            {
                continue; // already updated via above code block
            }
            if(isset($_POST[$mata_key]) && !empty($_POST[$mata_key]))
            {
                if(isset($meta_info['type']))
                {
                    if('absint'===$meta_info['type'])
                    {
                        $val=absint($_POST[$mata_key]);
                    }elseif('float'===$meta_info['type'])
                    {
                        $val=floatval($_POST[$mata_key]);
                    }elseif('boolean'===$meta_info['type'])
                    {
                        $val=boolval($_POST[$mata_key]);
                    }else
                    {
                        $val=sanitize_text_field($_POST[$mata_key]);
                    }
                }else{
                    $val=sanitize_text_field($_POST[$mata_key]);
                }
                update_post_meta($post_id, $mata_key, $val);

            }else
            {
                $default=(isset($meta_info['default']) ? $meta_info['default'] : '');
                update_post_meta($post_id, $mata_key, $default);
            }
        }
    }

    /**
     *  Enqueue Scripts and Styles
     */
    public function enqueue_scripts_styles()
    {
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        $screen_id_arr=array('shop_coupon', 'smart-coupons_page_wt-smart-coupon-for-woo_bulk_generate');
        $screen_id_arr=apply_filters('wt_sc_giveaway_admin_assets_screen_ids', $screen_id_arr);
        
        if(in_array($screen_id, $screen_id_arr))
        {
            wp_enqueue_style($this->module_id.'_coupon_edit', plugin_dir_url(__FILE__).'assets/css/main.css', array(), WEBTOFFEE_SMARTCOUPON_VERSION, 'all');
            
            wp_enqueue_script($this->module_id.'_coupon_edit', plugin_dir_url(__FILE__).'assets/js/main.js', array('jquery', WT_SC_PLUGIN_NAME), WEBTOFFEE_SMARTCOUPON_VERSION, false);

            $script_parameters=array(
                'bogo_coupon_type'=>self::$bogo_coupon_type_name,
                'msgs'=>array(  
                )
            );
            wp_localize_script($this->module_id.'_coupon_edit', 'wt_sc_giveaway_params', $script_parameters);
        }
    }

    public function add_general_settings_fields($post_id = 0, $coupon = null)
    {
        $wt_sc_bogo_apply_frequency = $this->get_coupon_meta_value($post_id, '_wt_sc_bogo_apply_frequency');
        echo '<div class="options_group" style="border:none;">';
        woocommerce_wp_radio(
            array(
                'id'      => '_wt_sc_bogo_apply_frequency',
                'value'     => $wt_sc_bogo_apply_frequency,
                'class'     => 'wt_sc_bogo_apply_frequency',
                'label'     => __('Number of times', 'wt-smart-coupons-for-woocommerce'),
                'options'   => array(
                        'once'      => __('Apply once', 'wt-smart-coupons-for-woocommerce'),
                        'repeat'    => __('Apply repeatedly', 'wt-smart-coupons-for-woocommerce'),
                    ),
                'description' => sprintf(__('%sApply once:%s If cart is eligible or conditions are met, coupon applies once. ie: If you set the coupon to offer Buy 2 Get 1, you get one free product. Moving more items to the cart will not make it eligible to get more free products.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>').'<br />'.sprintf(__('%sApply repeatedly:%s The coupon applies repeatedly whenever the cart is eligible or if conditions are met. ie: If you set the coupon to offer Buy 2 Get 1 then the coupon works repeatedly for Buy 4 Get 2 and so on.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>'),
                //'desc_tip'    => true,
            )
        );
        echo '</div>';
    }

    public function add_give_way_coupon_data_tab( $tabs )
    {
        $tabs['wt_give_away_free_product'] = array(
            'label'  => __('Giveaway products', 'wt-smart-coupons-for-woocommerce'),
            'target' => 'wt_give_away_free_products',
            'class'  => '',
        );

        return $tabs;
    }

    /**
     * Giveaway Product tab content
     */
    public function give_away_free_product_tab_content($post_id = 0)
    {
        $free_product_id_arr=self::get_giveaway_products($post_id);       

        $dummy_qty_price=self::get_dummy_qty_price();
        $bogo_customer_gets = $this->get_coupon_meta_value($post_id, '_wt_sc_bogo_customer_gets');
        $bogo_product_condition = $this->get_coupon_meta_value($post_id, '_wt_sc_bogo_product_condition');
        
        $bogo_free_products = $this->get_coupon_meta_value($post_id, '_wt_sc_bogo_free_products');
        $bogo_products_data=self::prepare_items_data($free_product_id_arr, $bogo_free_products);

        include_once plugin_dir_path(__FILE__).'views/giveaway_tab_content.php';       
    }

    /**
     *  Alter product search - exclude parent product from list (Only for non BOGO coupons)
     *  @since 1.2.4
     */
    public function wt_products_and_variations_no_parent()
    {
        check_ajax_referer( 'search-products', 'security' );
        if (!current_user_can('manage_woocommerce')) 
        {
            wp_die(__('You do not have sufficient permission to perform this operation', 'wt-smart-coupons-for-woocommerce'));
        }
        add_filter('woocommerce_json_search_found_products', array($this, 'exclude_parent_product_from_search'), 10, 1);
        
        WC_AJAX::json_search_products('', true);        
    }

    /**
     *  Exclude Parent Product from product search
     *  @since 1.2.4
     */
    public function exclude_parent_product_from_search($products)
    {
        foreach($products as $product_id =>$product)
        {
            $product_obj = wc_get_product($product_id);
            if($product_obj->has_child())
            {
                unset($products[$product_id]);
            }
        }
        return $products;
    }

    public function add_giveaway_info_to_order_detail_table($order_id)
    {
        $order=new WC_Order($order_id);
        $order_items = $order->get_items();
        foreach($order_items as $order_item_id=>$order_item)
        {
            $giveaway_info=$this->prepare_giveaway_info_for_order($order_item_id, $order_item, $order);
            if($giveaway_info)
            {
                $label_text=apply_filters('wt_sc_alter_order_detail_giveaway_info_label', __('Free gift:', 'wt-smart-coupons-for-woocommerce'), $order_item, $order_item_id, $order);
                ?>
                <tr>
                    <td class="label"><?php echo wp_kses_post($label_text); ?></td>
                    <td width="1%"></td>
                    <td class="total"><?php echo wp_kses_post($giveaway_info); // WPCS: XSS ok. ?></td>
                </tr>
                <?php
            }
        }
    }

    /**
     *  Help text for coupon restriction section
     *  @since  1.4.3
     *  @param  array   help text array
     *  @param  string  for which filed, default `product`. 
     *                  Possible values: product, exclude_product, category, exclude_category
     */
    public function add_discount_type_help_text($help_text_arr, $type = 'product')
    { 
        if('product' == $type)
        {
            $help_text_arr[self::$bogo_coupon_type_name] = __('Apply coupon only if the selected product quantity is in the cart. Discounts will be given for those products and not the total cart amount. For example, for setting up Buy X Get Y, choose the product/s X in this section.', 'wt-smart-coupons-for-woocommerce');
        
        }elseif('category' == $type)
        {
            $help_text_arr[self::$bogo_coupon_type_name] = __('Apply coupon only if the selected quantity of products of the chosen category are in the cart. Discounts will be given for those products and not the total cart amount.', 'wt-smart-coupons-for-woocommerce');
        
        }elseif('exclude_product' == $type || 'exclude_category' == $type)
        {
            $out = array();

            foreach($help_text_arr as $help_text_arr_key => $help_text_arr_val)
            {
                if(false !== stristr($help_text_arr_key, 'fixed_cart'))
                {
                    $out[$help_text_arr_key.'|'.self::$bogo_coupon_type_name] = $help_text_arr_val;
                }else
                {
                    $out[$help_text_arr_key] = $help_text_arr_val;
                }
            }

            $help_text_arr = $out;
        }

        return $help_text_arr;
    }

    private function prepare_meta_data_from_post_data($id_key, $qty_key, $price_key, $price_type_key)
    {
        $item_ids=(isset($_POST[$id_key]) && is_array($_POST[$id_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$id_key], 'int_arr') : array());
        $item_qty=(isset($_POST[$qty_key]) && is_array($_POST[$qty_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$qty_key], 'text_arr') : array()); //use text_arr as validation type
        $item_price=(isset($_POST[$price_key]) && is_array($_POST[$price_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$price_key], 'text_arr') : array());
        $item_price_type=(isset($_POST[$price_type_key]) && is_array($_POST[$price_type_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$price_type_key], 'text_arr') : array());
        
        $item_data=array();
        foreach($item_ids as $i=>$item_id)
        {
            $price_type=(isset($item_price_type[$i]) ? $item_price_type[$i] : '');
            $price=(isset($item_price[$i]) ? (float) $item_price[$i] : '');
            if('percent'===$price_type && ""!=$price)
            {
                $price=min($price, 100);
            }
            $item_data[$item_id]=array(
                'qty'=>(isset($item_qty[$i]) ? $item_qty[$i] : 1),
                'price'=>$price,
                'price_type'=>$price_type,
            );
        }

        return $item_data;
    }
}
Wt_Smart_Coupon_Giveaway_Product_Admin::get_instance();