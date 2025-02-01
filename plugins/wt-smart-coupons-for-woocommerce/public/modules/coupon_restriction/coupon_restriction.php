<?php
/**
 * Coupon usage restriction public section
 *
 * @link       
 * @since 1.4.0     
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists ( 'Wt_Smart_Coupon_Restriction' ) ) /* common module class not found so return */
{
    return;
}
class Wt_Smart_Coupon_Restriction_Public extends Wt_Smart_Coupon_Restriction
{
    public $module_base='coupon_restriction';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public static $bogo_applicable_count_session_id = 'wt_sc_bogo_applicable_count'; /* how much times the coupon passed the BOGO restriction condition */
    
    private $disqualified = array(); /* an array to store item_ids of disqualified products that doesn't satisty product quantity restriction[min or max] */

    /**
     *  Cart/Order items
     *  
     *  @since 1.4.4
     */
    private static $items = array();


    /**
     *  WC_Discounts Object
     *  
     *  @since 1.6.0
     */
    private static $discounts_obj = null;


    /**
     *  Coupon matching cart items array
     *  
     *  @since 1.6.0
     */
    public static $matching_items = array(); 


    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;
       
        add_filter('woocommerce_coupon_is_valid', array($this, 'wt_woocommerce_coupon_is_valid'), 10, 2);

        /**
         *  Exclude products that are not satisfying the coupon validation condition on `any(or)` product condition
         *  @since 1.4.1
         */
        add_filter('woocommerce_coupon_is_valid_for_product', array($this, 'exclude_disqualified_products'), 10, 4);
    }

    /**
     * Get Instance
    */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Restriction_Public();
        }
        return self::$instance;
    }

    /**
     *  Prepare min/max quantity data from restrction configuration data
     *  @since 1.4.0
     */
    private function process_qty_from_restriction_data($item_id, $type, $wt_sc_items_data)
    {
        $qty=absint(isset($wt_sc_items_data[$item_id][$type]) ? $wt_sc_items_data[$item_id][$type] : 0);
        return ($qty==0 && $type=='min' ? 1 : $qty);
    }

    public function get_individual_min_max_quantity_validation_message($item_name, $qty, $coupon_code, $type='no_valid_products')
    {
        if('min'==$type)
        {
            $msg=sprintf( __('The minimum quantity of %s for this coupon is %s.', 'wt-smart-coupons-for-woocommerce'), $item_name, $qty);
        }elseif('max'==$type)
        {
            $msg=sprintf( __('The maximum quantity of %s for this coupon is %s.', 'wt-smart-coupons-for-woocommerce'), $item_name, $qty);
        }else
        {
            $msg=__('Your cart does not meet the quantity eligibility criteria for this coupon.', 'wt-smart-coupons-for-woocommerce');
        }

        return apply_filters('wt_sc_alter_individual_min_max_quantity_validation_message', $msg, array('item_name'=>$item_name, 'quantity'=>$qty, 'type'=>$type, 'coupon_code'=>$coupon_code));
    }


    /**
    * 
    * @since    1.4.4   [Bug fix] When multiple coupon with same product restriction but different quantity restriction is used,
    *                   validation error occurring is fixed by taking each disqualified item (min and max)into 'disqualified' array based on individual coupon code.
    */
    private function individual_min_max_quantity_validation($coupon_code, $item_id, $wt_sc_items_data, $items_to_check_qty, $items_to_check_name, &$valid, $throw_exception=true)
    {
        $coupon_code=wc_format_coupon_code($coupon_code);
        
        /* min quantity */
        $min_qty=$this->process_qty_from_restriction_data($item_id, 'min', $wt_sc_items_data);
        if($min_qty>0 && $items_to_check_qty[$item_id]<$min_qty)
        {
            if(!isset($this->disqualified[$coupon_code]))
            {
                $this->disqualified[$coupon_code] = array();
            }

            $this->disqualified[$coupon_code][] = $item_id; //stores disqualified items below min qty restriction in an array
            $this->remove_bogo_applicable_count_session($coupon_code);
            $valid = false;
            if($throw_exception)
            {
                throw new Exception(
                    $this->get_individual_min_max_quantity_validation_message($items_to_check_name[$item_id], $min_qty, $coupon_code, 'min'), 110
                );
            }
        }

        /* max quantity */
        $max_qty=$this->process_qty_from_restriction_data($item_id, 'max', $wt_sc_items_data);
        if($max_qty>0 && $items_to_check_qty[$item_id]>$max_qty)
        {     
            if(!isset($this->disqualified[$coupon_code]))
            {
                $this->disqualified[$coupon_code] = array();
            }

            $this->disqualified[$coupon_code][] = $item_id; //stores disqualified item that exceeds max qty restriction in an array
            
            $this->remove_bogo_applicable_count_session($coupon_code);       
            $valid = false;                
            if($throw_exception)
            {
                throw new Exception(
                    $this->get_individual_min_max_quantity_validation_message($items_to_check_name[$item_id], $max_qty, $coupon_code, 'max'), 111
                );
            }
        }
    }

    /**
    * Get Quantity of matching products - Used for Coupon validation.
    * 
    * @since    1.4.3       [Bug fix] Incorrect quantity when excluded product/category exists
    * @since    1.4.4       Added compatibility for order items along with cart items
    * @since    1.6.0       Now uses the common function `get_matching_product_property`
    * @return   int         Total quantity of matching products
    */
    public function get_quantity_of_matching_product($coupon, $coupon_products, $coupon_categories, $coupon_exclude_products = array(), $coupon_exclude_categories = array(), $args = array())
    {
        $args['coupon_products']            = $coupon_products; 
        $args['coupon_categories']          = $coupon_categories; 
        $args['coupon_exclude_products']    = $coupon_exclude_products; 
        $args['coupon_exclude_categories']  = $coupon_exclude_categories; 
        $args['property']                   = 'quantity';

        return $this->get_matching_product_property($coupon, $args);
    }

    /**
     * Get sub total for mtching product - used for coupon validation
     * 
     *  @since  1.4.4    Added compatibility for order items along with cart items
     *  @since  1.6.0    Now uses the common function `get_matching_product_property`
     *  @return float    Subtotal of matching products
     */
    public function get_subtotal_of_matching_products($coupon, $coupon_products, $coupon_categories, $coupon_exclude_products = array(), $coupon_exclude_categories = array(), $args = array())
    {     
        $args['coupon_products']            = $coupon_products; 
        $args['coupon_categories']          = $coupon_categories; 
        $args['coupon_exclude_products']    = $coupon_exclude_products; 
        $args['coupon_exclude_categories']  = $coupon_exclude_categories;  
        $args['property']                   = 'subtotal';

        return $this->get_matching_product_property($coupon, $args);
    }

    /**
     *  Prepare term name for validation error message 
     */
    private function prepare_term_name_for_validation_error_message($category_id, &$items_to_check_name)
    {
        $items_to_check_name[$category_id]=__('the category', 'wt-smart-coupons-for-woocommerce');
        $term = get_term_by('id', $category_id, 'product_cat'); 
        if($term)
        {
            $items_to_check_name[$category_id].=" '".$term->name."'";
        }
    }

    /** 
     *  Remove BOGO applicable count session when the corresponding coupon was removed
     *  @since 1.4.0
     *  @param coupon code
     */
    public static function remove_bogo_applicable_count_session($coupon_code)
    {
        $coupon_code=wc_format_coupon_code($coupon_code);
        $bogo_applicable_count=self::get_bogo_applicable_count_session();
        if ( isset($bogo_applicable_count[$coupon_code]) && ! is_null( WC()->session ) ) {
            unset($bogo_applicable_count[$coupon_code]);
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }

    /**
     *  Get BOGO applicable count sessions if exists
     *  @since 1.4.0
     *  @return     array   Empty array if not exists, otherwise an array with the session info
     */
    public static function get_bogo_applicable_count_session() {
        return ( ! is_null( WC()->session ) ? WC()->session->get( self::$bogo_applicable_count_session_id ) : array() );
    }

    /**
     *  This function will take the minimum applicable count from all applicable count
     *  @since 1.4.0
     */
    public function prepare_final_applicable_count($coupon_code)
    {
        $coupon_code=wc_format_coupon_code($coupon_code);
        $bogo_applicable_count=self::get_bogo_applicable_count_session();
        
        if(isset($bogo_applicable_count[$coupon_code]) && ! is_null( WC()->session ) )
        {
            $bogo_applicable_count[$coupon_code]=min($bogo_applicable_count[$coupon_code]); 
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }


    public function set_applicable_count_by_subtotal($restriction_type, $coupon_code, $min_subtotal, $subtotal)
    {
        if ( ! is_null( WC()->session ) ) {
            $coupon_code=wc_format_coupon_code($coupon_code);
            $bogo_applicable_count=$this->get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type);

            $total_applicable_count=floor($subtotal/$min_subtotal);
            $bogo_applicable_count[$coupon_code][$restriction_type]=$total_applicable_count;
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }

    private function get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type)
    {
        $coupon_code=wc_format_coupon_code($coupon_code);
        $bogo_applicable_count=self::get_bogo_applicable_count_session();
        if(!isset($bogo_applicable_count[$coupon_code]))
        {
            $bogo_applicable_count[$coupon_code]=array();
        }

        if(!isset($bogo_applicable_count[$coupon_code][$restriction_type]))
        {
            $bogo_applicable_count[$coupon_code][$restriction_type]=0;
        }

        return $bogo_applicable_count;
    }

    public function set_applicable_count_by_global_qty($restriction_type, $coupon_code, $item_quantity, $wt_min_matching_product_qty)
    {   if ( ! is_null( WC()->session ) ) {
            $coupon_code=wc_format_coupon_code($coupon_code);
            $bogo_applicable_count=$this->get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type);
            $total_applicable_count=floor($item_quantity/$wt_min_matching_product_qty); 
            $bogo_applicable_count[$coupon_code][$restriction_type]+=$total_applicable_count;
        
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }


    /**
     *  Prepare an array with global individual matching quantity. Later we will take the minimum number from this array as eligible count
     */
    public function prepare_applicable_count_by_global_individual_qty($total_valid_arr, $item_quantity, $wt_min_matching_product_qty)
    {
        if($wt_min_matching_product_qty>0)
        {
            $total_valid_arr[]=floor($item_quantity/$wt_min_matching_product_qty); /* store the quantity to an array to find min qty */
        }
        return $total_valid_arr;
    }

    /**
     *  Find minimum value from global individual matching quantity array, This will be the eligibility count.
     */
    public function process_applicable_count_by_global_individual_qty($restriction_type, $coupon_code, $total_valid_arr)
    {
        if ( ! is_null( WC()->session ) ) {
            $coupon_code=wc_format_coupon_code($coupon_code);
            $bogo_applicable_count=$this->get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type);

            $bogo_applicable_count[$coupon_code][$restriction_type]=min($total_valid_arr);
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }

    public function set_applicable_count_by_qty($restriction_type, $coupon_code, $product_id, $items_to_check_qty, $wt_sc_products_data)
    {
        if ( ! is_null( WC()->session ) ) {
            $coupon_code=wc_format_coupon_code($coupon_code);
            $min_qty=$this->process_qty_from_restriction_data($product_id, 'min', $wt_sc_products_data);
            $cart_qty=$items_to_check_qty[$product_id];

            $bogo_applicable_count=$this->get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type);

            $total_applicable_count=floor($cart_qty/$min_qty);
            $bogo_applicable_count[$coupon_code][$restriction_type]+=$total_applicable_count;

            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }

    public function prepare_applicable_count_by_qty_for_and_condtion($total_valid_arr, $item_id, $wt_sc_items_data, $items_to_check_qty)
    {
        $min_qty=$this->process_qty_from_restriction_data($item_id, 'min', $wt_sc_items_data);
        if($min_qty>0)
        {
            $total_valid_arr[]=floor($items_to_check_qty[$item_id]/$min_qty); /* store the quantity to an array to find min qty */
        }
        return $total_valid_arr;
    }

    /**
     *  This function will process the applicable count if the product/category condition is `all from below`
     */
    public function process_applicable_count_by_qty_for_and_condition($restriction_type, $coupon_code, $total_valid_arr)
    {
        if ( ! is_null( WC()->session ) ) {
            $coupon_code=wc_format_coupon_code($coupon_code);
            $bogo_applicable_count=$this->get_applicable_qty_by_coupon_and_type($coupon_code, $restriction_type);

            $bogo_applicable_count[$coupon_code][$restriction_type]=min($total_valid_arr);
            WC()->session->set(self::$bogo_applicable_count_session_id, $bogo_applicable_count);
        }
    }

    /**
     *  @since 1.4.0
     *  @since 1.4.1    [Bug fix] Causing error on YITH POS custom add to cart
     *  @since 1.4.3    [Bug fix] Validation fails when global quantity restriction with exclude product/category exists
     *  @since 1.4.4    Added compatibility for backend coupon applying
     */
    public function wt_woocommerce_coupon_is_valid($valid, $coupon)
    {
        global $woocommerce;

        if(!$valid) //already invalid so no need to validate here.
        {
            return false;
        }

        if(is_admin()) //admin page
        {   
            $order_id = $this->is_order_edit_page(); //check order edit page or backend coupon applying via ajax
            
            if(false === $order_id) //not order edit page
            {
                return $valid;
            }

            $order = wc_get_order($order_id);

            if(!$order) //unable to get order object
            {
                return $valid;
            }

            /**
             *  Convert order items like cart items.
             */
            $items = Wt_Smart_Coupon_Common::convert_order_item_like_cart_item($order->get_items());

        }else
        {
            $cart = WC()->cart;

            if( is_null( $cart ) || empty( $cart->get_cart() ) )
            {
                return $valid;
            }

            $items = $cart->get_cart();
        }
         
        self::$items = $items; //store the value for sub functions. Eg: subtotal, quantity functions

        $applicable_count = 0; //how many times the validity conditions passed

        $coupon_id      = $coupon->get_id();
        $coupon_code    = wc_format_coupon_code($coupon->get_code());
        $wt_product_condition = $this->get_coupon_meta_value($coupon_id, '_wt_product_condition');        
        $use_individual_min_max = $this->get_coupon_meta_value($coupon_id, '_wt_use_individual_min_max');
        $wt_enable_product_category_restriction = $this->get_coupon_meta_value($coupon_id, '_wt_enable_product_category_restriction');
        
        $giveaway_obj = null;

        if(Wt_Smart_Coupon_Public::module_exists('giveaway_product'))
        {
            $giveaway_obj = Wt_Smart_Coupon_Giveaway_Product_Public::get_instance();
        }
        
        $coupon_products = array();
        $coupon_categories = array();
        $coupon_excluded_products = array();
        $coupon_excluded_categories = array();

        /**
         *  Clear applicable session data
         */
        $this->remove_bogo_applicable_count_session($coupon_code);

        if('yes'==$wt_enable_product_category_restriction) /* Product/category restriction enabled */
        {
            // Usage restriction "AND" for products       
            if('and'==$wt_product_condition || 'or'==$wt_product_condition)
            {
                $valid = true;
                $coupon_products = $coupon->get_product_ids() ;
                if(count($coupon_products)>0)
                {
                    $wt_sc_coupon_products = self::get_coupon_meta_value($coupon_id, '_wt_sc_coupon_products');
                    $wt_sc_products_data = self::prepare_items_data($coupon_products, $wt_sc_coupon_products);

                    $items_to_check = array();
                    $items_to_check_qty= array();
                    $items_to_check_name= array();
                    foreach($items as $item)
                    {
                        /* is free item check */
                        if(!is_null($giveaway_obj) && $giveaway_obj->is_a_free_item($item))
                        {
                            continue;
                        }

                        $product_name=(is_object($item['data']) && method_exists($item['data'], 'get_name') ?  ("'".$item['data']->get_name()."'")  : __('the product', 'wt-smart-coupons-for-woocommerce'));
                        array_push($items_to_check, $item['product_id']);
                        
                        if(isset($item['variation_id']) && $item['variation_id']>0) /* add variation id, if its a variable product */
                        {
                            array_push($items_to_check, $item['variation_id']);
                            $items_to_check_qty[$item['variation_id']] = $item['quantity'];
                            $items_to_check_name[$item['variation_id']] = $product_name;
                        }

                        $items_to_check_qty[$item['product_id']] = $item['quantity'];
                        $items_to_check_name[$item['product_id']] = $product_name;
                    }

                    /** 
                     * or condition, already validated by WC and here we are checking the min/max quantity, 
                     * If individual quantity validation enabled. 
                     * And also preparing eligibility count for both individual and non individual quantity restriction 
                     **/
                    if('or' === $wt_product_condition) /* or condition, already validated by WC and here we are checking the min/max quantity, If individual quantity validation enabled */
                    {
                        $valid_products=0;
                        
                        foreach($coupon_products as $product_id) /* loop through the coupon products */
                        {
                            if(in_array($product_id, $items_to_check) && isset($wt_sc_products_data[$product_id])) /* coupon product found, product min/max data available in meta value array */ 
                            {                               
                                if('yes' === $use_individual_min_max) /* individual quantity validation enabled */
                                {
                                    $valid=true; /* reset the valid value, may be the previous item is not a valid item */
                                    $this->individual_min_max_quantity_validation($coupon_code, $product_id, $wt_sc_products_data, $items_to_check_qty, $items_to_check_name, $valid, false);
                                    if($valid)
                                    {
                                        $valid_products++;
                                        $this->set_applicable_count_by_qty('product', $coupon_code, $product_id, $items_to_check_qty, $wt_sc_products_data);
                                    }
                                }else
                                {
                                    /* just for apply repeatedly functionality */
                                    $this->set_applicable_count_by_qty('product', $coupon_code, $product_id, $items_to_check_qty, $wt_sc_products_data);
                                }                               
                            }
                        }

                        if('yes' === $use_individual_min_max) /* individual quantity validation enabled */
                        {
                            if(0 === $valid_products) /* no products have valid quantity */
                            {
                                throw new Exception(
                                    $this->get_individual_min_max_quantity_validation_message('', '', $coupon_code), 112
                                );
                                $valid = false;
                            }else
                            {
                                $valid = true;
                            }
                        }

                    }else
                    {    
                        $total_valid_arr = array();           
                        foreach($coupon_products as $product_id)
                        {
                            if(!in_array($product_id, $items_to_check))
                            {
                                //clear coupon applicable session data
                                $this->remove_bogo_applicable_count_session($coupon_code);
                                
                                $valid = false;
                                break;

                            }else /* product found */
                            {
                                /* do quantity check for individual product */
                                if('yes' === $use_individual_min_max && isset($wt_sc_products_data[$product_id])) /* product min/max data available in meta value array */
                                {
                                    $this->individual_min_max_quantity_validation($coupon_code, $product_id, $wt_sc_products_data, $items_to_check_qty, $items_to_check_name, $valid);
                                    
                                    if(!$valid)
                                    {
                                        break;
                                    }else
                                    {
                                        $total_valid_arr = $this->prepare_applicable_count_by_qty_for_and_condtion($total_valid_arr, $product_id, $wt_sc_products_data, $items_to_check_qty);
                                    }
                                }
                            }
                        }

                        //condition is `and` so need to match all conditions
                        if($valid && 'yes' === $use_individual_min_max && !empty($total_valid_arr))
                        {
                            $this->process_applicable_count_by_qty_for_and_condition('product', $coupon_code, $total_valid_arr);
                        }
                    }
                        
                    if(!$valid)
                    {
                        throw new Exception(__('Sorry, this coupon is not applicable for selected products.', 'wt-smart-coupons-for-woocommerce' ), 109 );
                    }
                }
            }

            // Usage restriction "AND" for Categories.  Not for BOGO coupon type
            $wt_category_condition = get_post_meta($coupon_id, '_wt_category_condition', true);
            
            if(!$giveaway_obj->is_bogo($coupon) && ('and' === $wt_category_condition || 'or' === $wt_category_condition))
            {
                $valid = true;

                $coupon_categories = $coupon->get_product_categories();
                if(count($coupon_categories)>0)
                {

                    $wt_sc_coupon_categories = self::get_coupon_meta_value($coupon_id, '_wt_sc_coupon_categories');
                    $categories_data=self::prepare_items_data($coupon_categories, $wt_sc_coupon_categories);

                    $items_to_check = array();
                    $items_to_check_qty=array();
                    $items_to_check_name=array();
                    foreach($items as $item)
                    {                   
                        /* is free item check */
                        if(!is_null($giveaway_obj) && $giveaway_obj->is_a_free_item($item))
                        {
                            continue;
                        }

                        $product_cats = wc_get_product_cat_ids($item['product_id']);

                        $matching_cats=array_intersect($product_cats, $coupon_categories);
                        if(empty($matching_cats))
                        {
                            continue;
                        }
                        
                        /** prepare quantity */
                        foreach($matching_cats as $product_cat)
                        {
                            if(!isset($items_to_check_qty[$product_cat]))
                            {
                                $items_to_check_qty[$product_cat]=$item['quantity'];
                            }else{
                                $items_to_check_qty[$product_cat]+=$item['quantity'];
                            }
                        }
                        
                        $items_to_check = array_merge($items_to_check, $matching_cats);
                    }                   

                    if(empty($items_to_check)) /* no products from the given category in the cart */
                    {
                        //clear coupon applicable session data
                        $this->remove_bogo_applicable_count_session($coupon_code);
                        $valid=false;
                    }

                    if($valid)
                    {
                        /**
                         *  OR condition, already validated by WC, here we are checking the min/max quantity, if individual quantity validation enabled.
                         *  And also preparing eligibility count for both individual and non individual quantity restriction
                         */
                        if('or'==$wt_category_condition) 
                        {
                            foreach($coupon_categories as $category_id) /* loop through the coupon categories */
                            {
                                if(in_array($category_id, $items_to_check) && isset($categories_data[$category_id])) /* coupon category found, category min/max data available in meta value array */ 
                                {         
                                    if('yes'==$use_individual_min_max) /* individual quantity validation enabled */
                                    {
                                        /* prepare term name for error message */
                                        $this->prepare_term_name_for_validation_error_message($category_id, $items_to_check_name);

                                        $this->individual_min_max_quantity_validation($coupon_code, $category_id, $categories_data, $items_to_check_qty, $items_to_check_name, $valid);


                                        if(!$valid)
                                        {
                                           break;
                                        }
                                    }
                                    $this->set_applicable_count_by_qty('category', $coupon_code, $category_id, $items_to_check_qty, $categories_data);          
                                }
                            }

                        }else
                        {
                            $total_valid_arr=array();
                            foreach($coupon_categories as $category_id)
                            {              
                                if(!in_array($category_id, $items_to_check))
                                {
                                    //clear coupon applicable session data
                                    $this->remove_bogo_applicable_count_session($coupon_code);

                                    $valid = false;
                                    break;
                                }else  /* category found */
                                {
                                    /* do quantity check for individual category */
                                    if('yes'==$use_individual_min_max && isset($categories_data[$category_id])) /* category min/max data available in meta value array */
                                    {
                                        /* prepare term name for error message */
                                        $this->prepare_term_name_for_validation_error_message($category_id, $items_to_check_name);

                                        $this->individual_min_max_quantity_validation($coupon_code, $category_id, $categories_data, $items_to_check_qty, $items_to_check_name, $valid);

                                        if(!$valid)
                                        {
                                            break;
                                        }else
                                        {
                                            $total_valid_arr=$this->prepare_applicable_count_by_qty_for_and_condtion($total_valid_arr, $category_id, $categories_data, $items_to_check_qty);
                                        }
                                    }
                                }
                            }

                            //condition is `and` so need to match all conditions
                            if($valid && 'yes'==$use_individual_min_max && !empty($total_valid_arr))
                            {
                                $this->process_applicable_count_by_qty_for_and_condition('category', $coupon_code, $total_valid_arr);
                            }

                        }
                    }

                    if(!$valid)
                    {
                        throw new Exception(sprintf(__('Sorry, the coupon %s is not applicable for selected products.', 'wt-smart-coupons-for-woocommerce'), "<b>{$coupon_code}</b>"), 109);
                    }
                }
            }
           
            $coupon_products =  $coupon->get_product_ids();
            $coupon_excluded_products = $coupon->get_excluded_product_ids();

            if($giveaway_obj->is_bogo($coupon))
            {
                $coupon_categories = array();
                $coupon_excluded_categories = array();
            }else
            {
                $coupon_categories = $coupon->get_product_categories();
                $coupon_excluded_categories = $coupon->get_excluded_product_categories();                
            }
            
        }

        /**
         *  [Fix] Incorrect eligibility calculation when a user enabled `product and category restriction` and `individual quantity` but not added any products or category.
         *  
         *  @since 1.6.0 
         */
        if ( empty( $coupon_products ) && empty( $coupon_categories ) && 'yes' === $use_individual_min_max ) {
            $use_individual_min_max = 'no';
        }


        /**
         *  Quantity of matching Products
         */
        $wt_min_matching_product_qty = absint($this->get_coupon_meta_value($coupon_id, '_wt_min_matching_product_qty'));
        $wt_max_matching_product_qty = absint($this->get_coupon_meta_value($coupon_id, '_wt_max_matching_product_qty'));        
        $wt_min_matching_product_qty = (0 === $wt_min_matching_product_qty ? 1 : $wt_min_matching_product_qty);
        
        if($wt_min_matching_product_qty > 0 || $wt_max_matching_product_qty > 0)
        {
            if('no' === $wt_enable_product_category_restriction)
            {
                $quantity_of_matching_product = 0;
                $total_valid_arr = array();
                
                foreach($items as $item)
                {
                    /* is free item check */
                    if(!is_null($giveaway_obj) && $giveaway_obj->is_a_free_item($item))
                    {
                        continue;
                    }

                    if('yes' === $use_individual_min_max)
                    {
                        if($wt_min_matching_product_qty > 0 && $item['quantity'] < $wt_min_matching_product_qty)
                        {
                            $valid = false;
                            $this->remove_bogo_applicable_count_session($coupon_code);
                            throw new Exception(
                                $this->get_quantity_restriction_messages($coupon_code, $wt_min_matching_product_qty, false), 110
                            );
                            break;
                        }

                        if($wt_max_matching_product_qty > 0 && $item['quantity'] > $wt_max_matching_product_qty)
                        {            
                            $valid = false;
                            $this->remove_bogo_applicable_count_session($coupon_code);               
                            throw new Exception(
                                $this->get_quantity_restriction_messages($coupon_code, $wt_max_matching_product_qty, false, 'max'), 111
                            );
                            break;
                        }

                        //if code reached here, then it must be a valid product
                        $total_valid_arr=$this->prepare_applicable_count_by_global_individual_qty($total_valid_arr, $item['quantity'], $wt_min_matching_product_qty);

                    }else //global quantity, so calculate total quantity
                    {
                        $quantity_of_matching_product += $item['quantity'];
                    }
                }

                if('no'===$use_individual_min_max) //global quantity
                {
                    $valid=$this->validate_min_max_global_qty($coupon_code, $valid, $quantity_of_matching_product, $wt_min_matching_product_qty, $wt_max_matching_product_qty);
                    
                    if($valid)
                    {
                        $this->set_applicable_count_by_global_qty('quantity', $coupon_code, $quantity_of_matching_product, $wt_min_matching_product_qty);
                    }
                }else
                {
                    if($valid && !empty($total_valid_arr))
                    {   
                        $this->process_applicable_count_by_global_individual_qty('quantity', $coupon_code, $total_valid_arr);
                    }
                }

            }else
            {
                if('no' === $use_individual_min_max) /* Only check if global quantity check is enabled */
                {
                    $quantity_of_matching_product = $this->get_quantity_of_matching_product($coupon, $coupon_products, $coupon_categories, $coupon_excluded_products, $coupon_excluded_categories);
                    $valid = $this->validate_min_max_global_qty($coupon_code, $valid, $quantity_of_matching_product, $wt_min_matching_product_qty, $wt_max_matching_product_qty);
                    
                    if($valid)
                    {
                        $this->set_applicable_count_by_global_qty('quantity', $coupon_code, $quantity_of_matching_product, $wt_min_matching_product_qty);
                    }
                }
            }
        }        

        // Subtotal of matching products
        $wt_min_matching_product_subtotal = $this->get_coupon_meta_value($coupon_id, '_wt_min_matching_product_subtotal');
        $wt_max_matching_product_subtotal = $this->get_coupon_meta_value($coupon_id, '_wt_max_matching_product_subtotal');
        
        $subtotal_of_matching_product = $this->get_subtotal_of_matching_products($coupon, $coupon_products, $coupon_categories, $coupon_excluded_products, $coupon_excluded_categories);
        
        if($wt_min_matching_product_subtotal>0)
        {
            if($subtotal_of_matching_product<$wt_min_matching_product_subtotal)
            {
                if(in_array($coupon->get_code(), $woocommerce->cart->get_applied_coupons()))
                {
                    if($subtotal_of_matching_product < $wt_min_matching_product_subtotal )
                    {
                        $valid = false;
                        $this->remove_bogo_applicable_count_session($coupon_code);
                        throw new Exception(
                            sprintf( __('The minimum subtotal of matching products for this coupon is %s.', 'wt-smart-coupons-for-woocommerce' ), Wt_Smart_Coupon_Admin::get_formatted_price( $wt_min_matching_product_subtotal ) ),112
    
                        );
                    }
                }else
                {
                    $valid = false;
                    $this->remove_bogo_applicable_count_session($coupon_code);
                    throw new Exception(
                        sprintf( __('The minimum subtotal of matching products for this coupon is %s.', 'wt-smart-coupons-for-woocommerce' ), Wt_Smart_Coupon_Admin::get_formatted_price( $wt_min_matching_product_subtotal ) ),112

                    );
                }
            }

            //if code reached here, then it must be a valid product
            $this->set_applicable_count_by_subtotal('subtotal', $coupon_code, $wt_min_matching_product_subtotal, $subtotal_of_matching_product);

        }

        if($wt_max_matching_product_subtotal>0 && $subtotal_of_matching_product>$wt_max_matching_product_subtotal)
        {            
            $valid = false;
            $this->remove_bogo_applicable_count_session($coupon_code);               
            throw new Exception(
                sprintf( __( 'The maximum subtotal of matching products for this coupon is %s.', 'wt-smart-coupons-for-woocommerce' ), Wt_Smart_Coupon_Admin::get_formatted_price(  $wt_max_matching_product_subtotal ) ),113
            );
        }


        /* How many times the user can avail the coupon benefits */
        if($valid)
        {
            $this->prepare_final_applicable_count($coupon_code);
        }else
        {
            $this->remove_bogo_applicable_count_session($coupon_code);
        }

        return $valid;
    }


    private function validate_min_max_global_qty($coupon_code, $valid, $quantity_of_matching_product, $wt_min_matching_product_qty, $wt_max_matching_product_qty)
    {
        if($wt_min_matching_product_qty>0 && $quantity_of_matching_product<$wt_min_matching_product_qty)
        {
            $valid = false;
            
            //clear coupon applicable session data
            $this->remove_bogo_applicable_count_session($coupon_code);

            throw new Exception(
                $this->get_quantity_restriction_messages($coupon_code, $wt_min_matching_product_qty, true), 110
            );
        }
        if($wt_max_matching_product_qty>0 && $quantity_of_matching_product>$wt_max_matching_product_qty)
        {            
            $valid = false;

            //clear coupon applicable session data
            $this->remove_bogo_applicable_count_session($coupon_code);

            throw new Exception(
                $this->get_quantity_restriction_messages($coupon_code, $wt_max_matching_product_qty, true, 'max'), 111
            );
        }
        return $valid;
    }

    /**
     *  Exclude the products from applying discount that are not satisfying the min/max quatity restriction.
     *  This is applicable when product condition is `any(or)`
     * 
     *  @since    1.4.1
     *  @since    1.4.4     [Bug fix] When multiple coupons with same product restriction and different quantitiy restriction occurs,
     *                       items in disqualified array is checked for individual coupon code instead of one single array to avoid the product quantity eligibility issues.
     * 
     */
    public function exclude_disqualified_products($valid, $product, $coupon, $values)
    {
        $coupon_code = $coupon->get_code();

        if(isset($this->disqualified[$coupon_code]) && !empty($this->disqualified[$coupon_code])) //only proceeds if any item in the array of disqualified items is present in cart
        {
            $disqualified_products = $this->disqualified[$coupon_code];
            
            $product_id = ($product->get_parent_id()>0 ? $product->get_parent_id() : $product->get_id());
            $variation_id = ($product->get_parent_id()>0 ? $product->get_id() : 0);
            
            if(in_array($product_id, $disqualified_products) || in_array($variation_id, $disqualified_products))
            {
                $valid = false;
            }
        }

        return $valid;
    }

    private function get_quantity_restriction_messages($coupon_code, $qty, $is_global, $type='min')
    {
        $out='';
        if('min'==$type)
        {
            if($is_global)
            {
                $out=sprintf(__('Coupon applies to %s quantity. Please move eligible number of products in the cart to redeem the coupon.', 'wt-smart-coupons-for-woocommerce'), $qty);
            }else
            {
                $out=sprintf(__('Each eligible cart item requires minimum %s quantity, please add more items to cart to redeem the coupon.', 'wt-smart-coupons-for-woocommerce'), $qty);
            }
        }else
        {
            if($is_global)
            {
                $out=sprintf(__('The maximum quantity of matching products for this coupon is %s.', 'wt-smart-coupons-for-woocommerce'), $qty);
            }else
            {
                $out=sprintf(__('The maximum allowed quantity for each eligible item is %s', 'wt-smart-coupons-for-woocommerce'), $qty);
            }
        }

        return apply_filters('wt_sc_alter_quantity_restriction_messages', $out, $coupon_code, $qty, $is_global, $type); 
    }

    /**
     *  Checks current page is order edit page or backend coupon applying via ajax
     * 
     *  @since 1.4.4
     *  @return int|bool    Order id/true on success otherwise false
     */
    private function is_order_edit_page()
    {
        $basename = basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH));

        if('post.php' === $basename)
        {
            $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

            if(0 < $post_id && 'shop_order' === get_post_type($post_id))
            {
                return $post_id;
            }

        }elseif('post-new.php' === $basename)
        {
            $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';

            if('shop_order' === $post_type)
            {
                return true;   
            }
        }else
        {
            //ajax apply coupon
            $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';

            if('woocommerce_add_coupon_discount' === $action)
            {
                return isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
            }
        }

        return false;
    }


    /**
     *  Get properties like Subtotal, Quantity of matching products
     * 
     *  @since  1.6.0
     *  @param  WC_Coupon       $coupon     Coupon object
     *  @param  array           $args       Array of arguments. Eg: coupon_products, coupon_categories etc
     *  @return int|float       Currently there are only `subtotal` and `quantity` so the output values will be in integer or float
     */
    public function get_matching_product_property($coupon, $args)
    {
        $coupon_products            = (isset($args['coupon_products']) && is_array($args['coupon_products']) ? $args['coupon_products'] : array());
        $coupon_categories          = (isset($args['coupon_categories']) && is_array($args['coupon_categories']) ? $args['coupon_categories'] : array());      
        $coupon_exclude_products    = (isset($args['coupon_exclude_products']) && is_array($args['coupon_exclude_products']) ? $args['coupon_exclude_products'] : array());
        $coupon_exclude_categories  = (isset($args['coupon_exclude_categories']) && is_array($args['coupon_exclude_categories']) ? $args['coupon_exclude_categories'] : array());
        
        $is_product_restriction_enabled     = count($coupon_products) > 0;
        $is_category_restriction_enabled    = count($coupon_categories) > 0;

        $property = (isset($args['property']) && is_string($args['property']) ? $args['property'] : 'quantity'); //which property have to take. Eg: quantity, subtotal

        $items = self::$items;
        $total = 0; //the total property value to be returned

        $coupon_code = $coupon->get_code();
        $is_exclude_sale_items = $coupon->get_exclude_sale_items();
        $matching_items = isset(self::$matching_items[$coupon_code]) ? self::$matching_items[$coupon_code] : array();

        foreach($items as $item_key => $item)
        {
            // Skip giveaway products
            if ( isset( $item['free_product'] ) && "wt_give_away_product" === $item['free_product'] ) {
                continue;
            }

            // Skip sale items
            if ( $is_exclude_sale_items && $item['data']->is_on_sale() ) {
                continue;
            }

            $is_a_matching_product = false;

            if(!isset($matching_items[$item_key])) //not already validated
            {
                //product restriction
                if($is_product_restriction_enabled) 
                {
                    if(in_array($item['product_id'], $coupon_products) || in_array($item['variation_id'], $coupon_products))
                    {
                        $is_a_matching_product = true;
                    }
                }

                //category restriction
                if(!$is_a_matching_product && $is_category_restriction_enabled) 
                {
                    $product_cats = Wt_Smart_Coupon_Common::get_product_cat_ids($item['product_id']);

                    if(0 < count(array_intersect($coupon_categories, $product_cats)))
                    { 
                        if(0 === count(array_intersect($coupon_exclude_categories, $product_cats)))
                        {
                            $is_a_matching_product = true;
                        }     
                    }
                }

                if ( ! $is_product_restriction_enabled && ! $is_category_restriction_enabled )
                {
                    if(!empty($coupon_exclude_categories) || !empty($coupon_exclude_products)) //product/category exclude conditions are there
                    {
                        $product_cats = Wt_Smart_Coupon_Common::get_product_cat_ids($item['product_id']);

                        if(
                            !in_array( $item['product_id'], $coupon_exclude_products ) 
                            && !in_array( $item['variation_id'], $coupon_exclude_products ) 
                            && 0 === count( array_intersect( $coupon_exclude_categories, $product_cats ) )
                        ) //not included in product/category exclude conditions
                        {
                            $is_a_matching_product = true;
                        }

                    }else //no exclude conditions are there
                    {
                        $is_a_matching_product = true;
                    }
                }

                $matching_items[$item_key] = $is_a_matching_product;
            }else
            {
                $is_a_matching_product = $matching_items[$item_key]; //take the previously checked value
            }


            if($is_a_matching_product) //this product satisfies the matching criteria
            {
                if('subtotal' === $property)
                {
                    $total += ((float) $item['data']->get_price() * (int) $item['quantity']);

                }elseif('quantity' === $property)
                {
                    $total += $item['quantity'];
                }
            }
        }

        self::$matching_items[$coupon_code] = $matching_items;

        return $total;
    }
}
Wt_Smart_Coupon_Restriction_Public::get_instance();