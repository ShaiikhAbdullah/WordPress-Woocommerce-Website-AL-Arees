<?php
/**
 * Coupon usage restriction admin section
 *
 * @link       
 * @since 1.4.0    
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Wt_Smart_Coupon_Restriction')) /* common module class not found so return */
{
    return;
}
class Wt_Smart_Coupon_Restriction_Admin extends Wt_Smart_Coupon_Restriction
{
    public $module_base='coupon_restriction';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        add_action('woocommerce_coupon_options_usage_restriction', array($this, 'coupon_usage_restriction_fields'), 10, 1);

        add_action('woocommerce_process_shop_coupon_meta', array($this, 'process_shop_coupon_meta'), 10, 2);

        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'), 10, 0);
    }

    /**
     * Get Instance
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Restriction_Admin();
        }
        return self::$instance;
    }

    /**
     *  Process the meta data from product/category meta table. 
     *  This method will return an associative array with item id (Product/category id) as array key and min/max data array as value.
     *  @since 1.4.0 
     * 
     *  @param $id_key string item id POST array key
     *  @param $min_qty_key string min quantity POST array key
     *  @param $max_qty_key string max quantity POST array key
     *  
     *  @return $item_data  array  associative array with min/max quantity data. Empty array when no POST data exists
     */
    private function prepare_meta_data_from_post_data($id_key, $min_qty_key, $max_qty_key)
    {
        $item_ids=(isset($_POST[$id_key]) && is_array($_POST[$id_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$id_key], 'int_arr') : array());
        $item_min_qty=(isset($_POST[$min_qty_key]) && is_array($_POST[$min_qty_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$min_qty_key], 'text_arr') : array()); //use text_arr as validation type
        $item_max_qty=(isset($_POST[$max_qty_key]) && is_array($_POST[$max_qty_key]) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$max_qty_key], 'text_arr') : array());
        
        $item_data=array();
        foreach($item_ids as $i=>$item_id)
        {
            $item_data[$item_id]=array(
                'min'=>(isset($item_min_qty[$i]) ? $item_min_qty[$i] : ''),
                'max'=>(isset($item_max_qty[$i]) ? $item_max_qty[$i] : ''),
            );
        }

        return $item_data;
    }

    /**
     *  Update checkbox field data to postmeta.
     *  Checkbox fields have no post data when unchecked
     *  @since 1.4.0
     *   
     *  @param $post_id     int     post/coupon id
     *  @param $field_name     string     post meta/form field key name  
     */
    private function update_checkbox_field_data($post_id, $field_name)
    {
        if(isset($_POST[$field_name]) && $_POST[$field_name]=='yes')
        {
            update_post_meta($post_id, $field_name, 'yes');
        }else
        {
            update_post_meta($post_id, $field_name, 'no');
        }
    }

    /**
     *  Save coupon restrictions meta data
     * 
     *  @param $post_id     int     post/coupon id
     *  @param $post     object     post object
     */
    public function process_shop_coupon_meta($post_id, $post)
    {
        if(!current_user_can('manage_woocommerce')) 
        {
            wp_die(__('You do not have sufficient permission to perform this operation', 'wt-smart-coupons-for-woocommerce'));
        }

        /* product data */
        $wt_sc_coupon_products=$this->prepare_meta_data_from_post_data('_wt_sc_coupon_product_ids', '_wt_sc_coupon_product_min_qty', '_wt_sc_coupon_product_max_qty');
        update_post_meta($post_id, '_wt_sc_coupon_products', $wt_sc_coupon_products);

        /* category data */
        $wt_sc_coupon_categories=$this->prepare_meta_data_from_post_data('_wt_sc_coupon_category_ids', '_wt_sc_coupon_category_min_qty', '_wt_sc_coupon_category_max_qty');
        update_post_meta($post_id, '_wt_sc_coupon_categories', $wt_sc_coupon_categories);

        $this->update_checkbox_field_data($post_id, '_wt_use_individual_min_max');
        $this->update_checkbox_field_data($post_id, '_wt_enable_product_category_restriction');

        /* fields to skip from below meta data update loop. Because they are alreay updated. */
        $skip_post_arr=array(
            '_wt_sc_coupon_products', '_wt_sc_coupon_categories', '_wt_use_individual_min_max', '_wt_enable_product_category_restriction'
        ); 

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
     *  Extra fields on coupon usage restrictions tab. Coupon edit/add page
     *  @since 1.0.0
     * 
     *  @param $post_id     int     post/coupon id
     */
    public function coupon_usage_restriction_fields($post_id)
    {
        $coupon    = new WC_Coupon($post_id);

        /**
         *  Checkbox to enable/disable product/category restriction
         */
        $wt_enable_product_category_restriction =$this->get_coupon_meta_value($post_id, '_wt_enable_product_category_restriction');
        woocommerce_wp_checkbox(
            array(
                'id'            => '_wt_enable_product_category_restriction',
                'value'         => $wt_enable_product_category_restriction,
                'class'         => 'wt_enable_product_category_restriction',
                'label'         => __('Product/Category restrictions', 'wt-smart-coupons-for-woocommerce'),
                'description'   => __('Enable to apply coupon only if the cart satisfies the product or category restrictions.', 'wt-smart-coupons-for-woocommerce'),
            )
        );

        /**
         *  Checkbox to enable/disable individual min/max quantity restriction
         */
        $wt_use_individual_min_max =$this->get_coupon_meta_value($post_id, '_wt_use_individual_min_max');
        woocommerce_wp_checkbox(
            array(
                'id'            => '_wt_use_individual_min_max',
                'value'         => $wt_use_individual_min_max,
                'class'         => 'wt_use_individual_min_max',
                'label'         => __('Individual quantity restriction', 'wt-smart-coupons-for-woocommerce'),
                'description'   => __('Enable to set minimum and maximum quantity restrictions for individual product/category instead of entire cart.', 'wt-smart-coupons-for-woocommerce' ),
            )
        );

        /**
         *  Checkbox for Any/All product option
         */
        $wt_product_condition =$this->get_coupon_meta_value($post_id, '_wt_product_condition');
        woocommerce_wp_radio(
            array(
                'id'      => '_wt_product_condition',
                'value'     => $wt_product_condition,
                'class'     => 'wt_product_restrictions',
                'label'     => __('Product conditions:', 'wt-smart-coupons-for-woocommerce'),
                'options'   => array(
                        'or' => __('Any from below selection', 'wt-smart-coupons-for-woocommerce'),
                        'and' => __('All from below selection', 'wt-smart-coupons-for-woocommerce')
                    ),
                'description' => sprintf(__('%sAny:%s Applies coupon if any of the products from the below is available in the cart.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>').'<br />'.sprintf(__('%sAll:%s Applies coupon if the cart contains all of the listed products.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>'),
            )
        );


        /**
         *  Checkbox for Any/All category option
         */
        $wt_category_condition = $this->get_coupon_meta_value($post_id, '_wt_category_condition');
        woocommerce_wp_radio(
            array(
                'id'      => '_wt_category_condition',
                'value'     => $wt_category_condition,
                'class'     => 'wt_category_condition',
                'label'     => __('Category condition:', 'wt-smart-coupons-for-woocommerce'),
                'options'   => array
                    (
                        'or' => __('Any from below selection', 'wt-smart-coupons-for-woocommerce'),
                        'and' => __('All from below selection', 'wt-smart-coupons-for-woocommerce')
                    ),
                'description' => sprintf(__('%sAny:%s Applies coupon if the eligible quantity of products from any of the below selected categories are available in the cart.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>').'<br />'.sprintf(__('%sAll:%s Applies coupon if the cart contains the eligible quantity of products from all of the below listed categories.', 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>'),
            )
        );

        /** 
         * Product and category form fields 
         */
        include_once plugin_dir_path(__FILE__).'views/_usage_restrictions_tab_content.php';

        echo '<div class="options_group wt_sc_coupon_restriction_matching_products">';

            // Minimum quantity of matching products (product/category)
            woocommerce_wp_text_input(
                array(
                    'id'          => '_wt_min_matching_product_qty',
                    'label'       => __( 'Minimum quantity of matching products', 'wt-smart-coupons-for-woocommerce' ),
                    'placeholder' => __( 'No minimum', 'woocommerce' ),
                    'description' => __( 'Minimum quantity of the products that match the given product or category restrictions. If no product or category restrictions are specified, the total number of products is used.', 'wt-smart-coupons-for-woocommerce' ),
                    'data_type'   => 'decimal',
                    'desc_tip'    => true,
                )
            );

            // Maximum quantity of matching products (product/category)
            woocommerce_wp_text_input(
                array(
                    'id'          => '_wt_max_matching_product_qty',
                    'label'       => __( 'Maximum quantity of matching products', 'wt-smart-coupons-for-woocommerce' ),
                    'placeholder' => __( 'No maximum', 'woocommerce' ),
                    'description' => __( 'Maximum quantity of the products that match the given product or category restrictions. If no product or category restrictions are specified, the total number of products is used.', 'wt-smart-coupons-for-woocommerce' ),
                    'data_type'   => 'decimal',
                    'desc_tip'    => true,
                )
            );

            // Minimum subtotal of matching products (product/category)
            woocommerce_wp_text_input(
                array(
                    'id'          => '_wt_min_matching_product_subtotal',
                    'label'       => __( 'Minimum subtotal of matching products', 'wt-smart-coupons-for-woocommerce' ),
                    'placeholder' => __( 'No minimum', 'woocommerce' ),
                    'description' => __( 'Minimum price subtotal of the products that match the given product or category restrictions.', 'wt-smart-coupons-for-woocommerce' ),
                    'data_type'   => 'price',
                    'desc_tip'    => true,
                )
            );

            // Maximum subtotal of matching products (product/category)
            woocommerce_wp_text_input(
                array(
                    'id'          => '_wt_max_matching_product_subtotal',
                    'label'       => __( 'Maximum subtotal of matching products', 'wt-smart-coupons-for-woocommerce' ),
                    'placeholder' => __( 'No maximum', 'woocommerce' ),
                    'description' => __( 'Maximum price subtotal of the products that match the given product or category restrictions.', 'wt-smart-coupons-for-woocommerce' ),
                    'data_type'   => 'price',
                    'desc_tip'    => true,
                )
            );

        echo '</div>';
    }


    /**
     *  Enqueue Scripts and Styles
     *  @since 1.0.0
     * 
     */
    public function enqueue_scripts_styles()
    {
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        $screen_id_arr=array('shop_coupon', 'smart-coupons_page_wt-smart-coupon-for-woo_bulk_generate');
        
        /**
         *  Alter the screen ID list where the JS to enqueue
         *  @param $screen_id_arr   array     array of default screen id list
         */
        $screen_id_arr=apply_filters('wt_sc_coupon_restriction_admin_assets_screen_ids', $screen_id_arr);
        
        if(in_array($screen_id, $screen_id_arr))
        {
            wp_enqueue_script($this->module_id.'_coupon_edit', plugin_dir_url(__FILE__).'assets/js/main.js', array('jquery', WT_SC_PLUGIN_NAME), WEBTOFFEE_SMARTCOUPON_VERSION, false);
        }
    }

    public static function discount_type_help_arr($type = 'product')
    {
        switch ($type)
        {
            case 'product':
                $discount_type_help = array(
                    'percentage|fixed_product'      =>  __('Apply coupon only if the selected quantity of products are in the cart. Discounts will be given for those products and not the total cart amount.', 'wt-smart-coupons-for-woocommerce'),
                    'fixed_cart'                    =>  __('Applies coupon only if the selected quantity of products are in the cart. A discount will be given for the total cart amount.', 'wt-smart-coupons-for-woocommerce'),
                );
                break;

            case 'category':
                $discount_type_help = array(
                    'percentage|fixed_product'      =>  __('Apply coupon only if the selected quantity of products of the chosen category are in the cart. Discounts will be given for those products and not the total cart amount.', 'wt-smart-coupons-for-woocommerce'),
                    'fixed_cart'                    =>  __('Applies coupon only if the selected quantity of products of the chosen category are in the cart. A discount will be given for the total cart amount.', 'wt-smart-coupons-for-woocommerce'),
                );
                break;

            case 'exclude_product':
                $discount_type_help = array(
                    'percentage|fixed_product'      =>  __('If eligible products are in the cart along with excluded products, the coupon will be applied, but the discount will be limited to eligible products.', 'wt-smart-coupons-for-woocommerce'),
                    'fixed_cart'                    =>  __('The coupon is not applicable when the cart contains an excluded product.', 'wt-smart-coupons-for-woocommerce'),
                );
                break;

            case 'exclude_category':
                $discount_type_help = array(
                    'percentage|fixed_product'      =>  __('If eligible products are in the cart along with products from an excluded category, the coupon will be applied, but the discount will be limited to eligible products.', 'wt-smart-coupons-for-woocommerce'),
                    'fixed_cart'                    =>  __('The coupon is not applicable when the cart contains products from an excluded category.', 'wt-smart-coupons-for-woocommerce'),
                );
                break;
            
            default:
                $discount_type_help = array();
                break;
        }

        return apply_filters('wt_sc_intl_alter_discount_type_help_arr', $discount_type_help, $type);
    }
}
Wt_Smart_Coupon_Restriction_Admin::get_instance();