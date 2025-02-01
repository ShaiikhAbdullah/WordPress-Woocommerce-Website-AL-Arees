<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.0.0
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/public
 * @author     WebToffee <info@webtoffee.com>
 */
if( ! class_exists('Wt_Smart_Coupon_Public') ) {
    class Wt_Smart_Coupon_Public {

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;
    
        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         *  Module list, Module folder and main file must be same as that of module name
         *  Please check the `register_modules` method for more details
         *  @since 1.4.0
         */
        public static $modules=array(
            'giveaway_product',
            'coupon_restriction',
            'auto_coupon',
            'url_coupon',
            'checkout_options', /** @since 1.4.6 */
        );

        public static $existing_modules=array();

        private static $instance = null;
        
        private static $coupon_obj=null;

        protected $overwrite_coupon_message=array();
    
        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param      string    $plugin_name       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct($plugin_name, $version) {
    
            $this->plugin_name = $plugin_name;
            $this->version = $version; 
        }


        /**
         * Get Instance
         * @since 1.4.0
         */
        public static function get_instance($plugin_name, $version)
        {
            if(is_null(self::$instance))
            {
                self::$instance=new Wt_Smart_Coupon_Public($plugin_name, $version);
            }

            return self::$instance;
        }

        /**
         *  Register modules    
         *  @since 1.4.0    
         */
        public function register_modules()
        {            
            Wt_Smart_Coupon::register_modules(self::$modules, 'wt_sc_public_modules', plugin_dir_path( __FILE__ ), self::$existing_modules);          
        }

        /**
         *  Check module enabled    
         *  @since 1.4.0     
         */
        public static function module_exists($module)
        {
            return in_array($module, self::$existing_modules);
        }
        
    
        /**
         * Register the stylesheets for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {
    
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wt-smart-coupon-public.css', array(), $this->version, 'all');
        }
    
        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {
    
            $_nonces = array(
                'public' => wp_create_nonce( 'wt_smart_coupons_public' ),
                'apply_coupon' => wp_create_nonce( 'wt_smart_coupons_apply_coupon' ),
            );
            $params = array( 
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                'wc_ajax_url' => esc_url(home_url('/?wc-ajax=')),
                'nonces' => $_nonces,
                'labels' => array(
                    'please_wait'=>__('Please wait...', 'wt-smart-coupons-for-woocommerce'),
                    'choose_variation'=>__('Please choose a variation', 'wt-smart-coupons-for-woocommerce'),
                    'error'=>__('Error !!!', 'wt-smart-coupons-for-woocommerce'),
                ),
                'shipping_method' => array(),
                'payment_method' => '',
                'is_cart' => is_cart(),
            );

            if ( ! is_null( WC()->session ) ) {
                $params['shipping_method']  = is_array( WC()->session->get( 'chosen_shipping_methods' ) ) ? array_map( 'esc_html', WC()->session->get( 'chosen_shipping_methods' ) ) : array();
                $params['payment_method']   = esc_html( WC()->session->get( 'chosen_payment_method' ) );
            }

            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-smart-coupon-public.js', array('jquery'), $this->version, false);
            wp_localize_script($this->plugin_name,'WTSmartCouponOBJ', $params);
        }
    
        /**
         * Get formatted Meta values of a coupon.
         * @since 1.0.0
         */
        public static function get_coupon_meta_data( $coupon ) {

            if( !$coupon || !is_a ( $coupon,'WC_Coupon') ) {
                return;
            }

            $discount_types = wc_get_coupon_types();
            $coupon_data = array();
            $coupon_amount = $coupon->get_amount();
            
            switch( $coupon->get_discount_type() ) {
                case 'fixed_cart':
                    $coupon_data['coupon_type']     = __( 'Cart discount', 'wt-smart-coupons-for-woocommerce' );
                    $coupon_data['coupon_amount']   = Wt_Smart_Coupon_Admin::get_formatted_price( $coupon_amount ) ;
                    break;

                case 'fixed_product':
                    $coupon_data['coupon_type']     = __( 'Product discount', 'wt-smart-coupons-for-woocommerce' );
                    $coupon_data['coupon_amount']   = Wt_Smart_Coupon_Admin::get_formatted_price( $coupon_amount );
                    break;

                case 'percent_product':
                    $coupon_data['coupon_type']     = __( 'Product discount', 'wt-smart-coupons-for-woocommerce' );
                    $coupon_data['coupon_amount']   = $coupon_amount . '%';
                    break;

                case 'percent':
                    $coupon_data['coupon_type'] = __('Cart discount', 'wt-smart-coupons-for-woocommerce' );
                    $coupon_data['coupon_amount'] = $coupon_amount . '%';
                    break;
                case 'store_credit':
                    $coupon_data['coupon_type'] = $discount_types[ $coupon->get_discount_type() ];
                    $coupon_data['coupon_amount'] = Wt_Smart_Coupon_Admin::get_formatted_price( $coupon_amount );
                    break;

                default:

                    $coupon_data['coupon_type'] = $discount_types[ $coupon->get_discount_type() ];
                    $coupon_data['coupon_amount'] = $coupon_amount;
                    break;

            }

            if( 0 === $coupon_amount && $coupon->get_free_shipping() ) {
                $coupon_data['coupon_type'] = __('Free shipping','wt-smart-coupons-for-woocommerce');
                $coupon_data['coupon_amount'] = '';
            }

            
            $free_products  = get_post_meta( $coupon->get_id(), '_wt_free_product_ids', true );

            if( 0 === $coupon_amount && $free_products && is_string($free_products) &&  "" != trim($free_products))
            {              
                $coupon_data['coupon_type'] =  __('Free products', 'wt-smart-coupons-for-woocommerce');
                $coupon_data['coupon_amount'] = '';
            }


            $coupon_data['coupon_expires']      =  self::get_coupon_expires($coupon);
            $coupon_data['email_restriction']   = $coupon->get_email_restrictions();
            $coupon_data['coupon_id']           = $coupon->get_id();
            $coupon_data['start_date']   = self::get_coupon_start_date($coupon->get_id(), true, true);

            return apply_filters('wt_smart_coupon_meta_data', $coupon_data, $coupon);
        }

        public static function get_coupon_expires($coupon)
        {          
            $coupon_id = $coupon->get_id();
            $coupon_expiry = null;
            
            $coupon_expiry_date = $coupon->get_date_expires();
            
            if(isset($coupon_expiry_date)  &&  null !== $coupon_expiry_date)
            {
               $coupon_expiry = $coupon_expiry_date->getOffsetTimestamp();
            }

            return $coupon_expiry;
        }
        
        /**
         *  Get start date of a coupon.
         *  @since  1.2.5
         *  @since  1.4.1 Code updated
         *  @param      int             $coupon_id
         *  @param      bool            $timestamp     return timestamp or date string. (Optional) Default: false
         *  @return     string|int      timestamp or date string
         */
        public static function get_coupon_start_date($coupon_id, $timestamp = false, $offset_timestamp=false)
        {
            if(!metadata_exists('post', $coupon_id, '_wt_coupon_start_date'))
            {
                return ($timestamp ? 0 : '');
            }

            $start_date = get_post_meta($coupon_id, '_wt_coupon_start_date', true);

            return ($timestamp ? Wt_Smart_Coupon_Common::get_date_timestamp($start_date, $offset_timestamp) : $start_date);           
        }

        /**
         *  Get formatted Start/Expiry date of a coupon.
         *  @since 1.3.7
         */
        public static function get_coupon_start_expiry_date_texts($date, $type="start_date")
        {
            $date = intval($date);
            $days_diff = (($date - time())/(24*60*60));
            
            if($days_diff<0)
            {
                $date_text=("start_date" === $type ? '' : __('Expired', 'wt-smart-coupons-for-woocommerce'));
            }else
            {
                $date_text=($type=="start_date" ? __('Starts on ', 'wt-smart-coupons-for-woocommerce') : __('Expires on ', 'wt-smart-coupons-for-woocommerce')). esc_html(date_i18n(get_option('date_format', 'F j, Y'), $date)); 
                $date_text=apply_filters('wt_sc_alter_coupon_start_expiry_date_text', $date_text, $date, $type);
            }
            
            return $date_text;
        }

        /**
         *  Get all coupons used by a customer in previous orders.
         * 
         *  @since 1.0.0
         *  @since 1.5.1   Added HPOS Compatibility
         */
        public static function get_coupon_used_by_a_customer( $user,$coupon_code = '', $return = 'COUPONS' ) 
        {         
            global $current_user,$woocommerce,$wpdb;

            if( !$user ) {
                $user = wp_get_current_user();
            }
            $coupon_used = array();
            $customer_id = $user->ID;
            
            $customer_orders = Wt_Smart_Coupon_Common::get_orders(array( 
                'customer'      => $customer_id,
                'limit'         => -1,
            ));

            if ($customer_orders) :
                foreach ($customer_orders as $order) :
                    
                    if( Wt_Smart_Coupon::wt_sc_is_woocommerce_prior_to( '3.7' ) ) {
                        $coupons  = $order->get_used_coupons();
                    } else {
                        $coupons  = $order->get_coupon_codes();
                    }
                    if( $coupons ) {
                        $coupon_used = array_merge( $coupon_used, $coupons );
                    }
                endforeach;

                if( $return =='NO_OF_TIMES' && $coupon_code != '' ) {
                    $count_of_used = array_count_values($coupon_used);
                    
                    return isset( $count_of_used[ $coupon_code ] )? $count_of_used[ $coupon_code ] : 0 ;

                }
                return apply_filters('wt_smart_coupon_used_coupons',array_unique( $coupon_used ),$user );

            else :
                return false;
            endif;
        }
        
        /**
        * Check if coupon applicable to specific user roles
        *
        * @since  1.2.6
        * @access public
        * @return bool
        */
        public static function _wt_sc_check_valid_user_roles( $coupon_id ){
            $_wt_sc_user_roles         = get_post_meta($coupon_id, '_wt_sc_user_roles',true);
            if( isset( $_wt_sc_user_roles ) ) {
                if( ''!= $_wt_sc_user_roles && ! is_array( $_wt_sc_user_roles ) ) {
                    $_wt_sc_user_roles = explode(',',$_wt_sc_user_roles);
                } 
                $user = wp_get_current_user();
                if( isset( $user )) {
                    $user_roles = ( isset( $user->roles ) && is_array( $user->roles ) ) ? $user->roles : array();
                    if( !empty( $_wt_sc_user_roles )){
                        $roles = array_intersect($user_roles, $_wt_sc_user_roles);
                        if(empty($roles)){
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        public static function coupon_is_valid_for_displaying($coupon, $email, $user_id, $display_invalid_coupons, &$expired_coupon, &$expire_text)
        {
            $coupon_obj = new WC_Coupon( $coupon->ID );
            self::$coupon_obj=$coupon_obj;
            $start_text = '';
            $email_restriction = $coupon_obj->get_email_restrictions();

            // Check is coupon restricted for other Email.
            if(!empty($email_restriction) && !in_array($email, $email_restriction))
            {
                return false;
            }

            // Check is coupon restricted for the user roles.
            $coupon_id    = $coupon_obj->get_id();
            if(self::_wt_sc_check_valid_user_roles( $coupon_id ) === false )
            {
                return false;
            }

            // Check is Coupon Expired.
            $coupon_data  = self::get_coupon_meta_data( $coupon_obj );
            
            if(isset($coupon_data['coupon_expires']) && !is_null($coupon_data['coupon_expires']))
            {
                $expire_text = self::get_coupon_start_expiry_date_texts($coupon_data['coupon_expires'], "expiry_date");
                if(__('Expired', 'wt-smart-coupons-for-woocommerce') === $expire_text)
                {
                    array_push($expired_coupon, $coupon_obj->get_code());
                    return false;
                }
            }else
            {
                $expire_text = '';
            }

            // Check is usage limit per user is exeeded.               
            if($coupon_obj->get_usage_limit() > 0 && $coupon_obj->get_usage_count() >= $coupon_obj->get_usage_limit())
            {
                array_push($expired_coupon, $coupon_obj->get_code());
                return false;         
            }

            if($coupon_obj && $user_id && $coupon_obj->get_usage_limit_per_user()>0 && $coupon_obj->get_id() && $coupon_obj->get_data_store())
            {
                $data_store  = $coupon_obj->get_data_store();
                $usage_count = $data_store->get_usage_by_user_id( $coupon_obj, $user_id );
                if ( $usage_count >= $coupon_obj->get_usage_limit_per_user())
                {
                    array_push($expired_coupon, $coupon_obj->get_code() );
                    return false;
                }
            }

            if($display_invalid_coupons===false && $coupon_obj->is_valid()===false)
            {
                return false;
            }
            
            return true;
        }


        /**
         *  Get coupon html based on current style
         *  
         *  @since 1.1.0
         *  @since 1.4.7    Taking data from `Coupon_Style` module
         */
        public static function get_coupon_html($coupon, $coupon_data, $coupon_type = 'available_coupon', $include_css = false)
        {
            $coupon_html = '';

            if(Wt_Smart_Coupon_Common::module_exists('coupon_style'))
            {
                if("email_coupon" === $coupon_type) //strip the CSS from template and add it to email CSS
                {
                    /* forcefully include CSS along with HTML*/
                    $include_css = true;
                }

                $coupon_id = (isset($coupon_data['coupon_id']) ? absint($coupon_data['coupon_id']) : 0);
                $coupon = new WC_Coupon($coupon_id);
                $coupon_html = Wt_Smart_Coupon_Style::prepare_coupon_html($coupon, $coupon_data, $coupon_type, $include_css);              
            }

            return $coupon_html;
        }

        
        /**
         *  This function will print CSS for default coupon code block
         *  
         *  @since 1.4.7 
         */
        public static function print_coupon_default_css()
        {
            if(Wt_Smart_Coupon_Common::module_exists('coupon_style'))
            {
                echo '<style type="text/css">';
                echo wp_kses_post(Wt_Smart_Coupon_Style::get_coupon_default_css());
                echo '</style>';
            }
        }

        
        /**
         * Print coupon CSS
         * 
         *  @since 1.3.7
         */
        public static function print_coupon_css()
        {
            wc_deprecated_function('Wt_Smart_Coupon_Public::print_coupon_css', '1.4.7', 'Wt_Smart_Coupon_Public::print_coupon_default_css');

            Wt_Smart_Coupon_Public::print_coupon_default_css();
        }

        
        /**
         *  Display available coupons in checkout
         * 
         *  @since 1.3.7
         */
        public function display_available_coupon_in_checkout()
        {
            $offset=(isset($_GET['wt_sc_available_coupons_offset']) ? absint($_GET['wt_sc_available_coupons_offset']) : 0); 
            $limit=apply_filters('wt_sc_checkout_available_coupons_per_page', 20);
            
            do_action('wt_smart_coupon_before_checkout_coupons');
            $available_coupons = self::print_user_available_coupon('', 'checkout', $offset, $limit);
            do_action('wt_smart_coupon_after_checkout_coupons', array('available_coupons' => $available_coupons,));
        }

        /**
         * Action for displaying avalable coupon in cart page.
         * @since 1.4.3
         */
        public function display_available_coupon_in_cart()
        {
            $offset=(isset($_GET['wt_sc_available_coupons_offset']) ? absint($_GET['wt_sc_available_coupons_offset']) : 0);
            $limit=apply_filters('wt_sc_cart_available_coupons_per_page', 20);
            self::print_user_available_coupon('', 'cart', $offset, $limit);
        }

        /**
         *  Get user coupons
         *  @since 1.4.1
         *  @since 1.4.3    SQL query updated to take data from lookup table
         *  @since 1.4.4    Recursively fetching the coupon ids to reach the limit.
         *                  [Bug fix] Non existing post ids are in the list.
         */
        public static function get_user_coupons($user = '', $offset = 0, $limit = 30, $args = array())
        {
            global $wpdb;
            
            if(!$user)
            {
                $user= wp_get_current_user();
            }
            if($user)
            {
                $user_id = $user->ID;
                $email = $user->user_email;
            }else
            {
                return array();
            }

            $type=(isset($args['type']) ? $args['type'] : 'available_coupons');
            
            if(!in_array($type, array('available_coupons', 'expired_coupons', 'auto_coupons')))
            {
                return array(); /* not in the allowed type list */
            }

            $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return empty array.
            {
                return array();
            }

            $already_fetched_ids = isset($args['already_fetched_ids']) && is_array($args['already_fetched_ids']) ? $args['already_fetched_ids'] : array();
            $already_fetched_ids_count = count($already_fetched_ids);

            $sql = "SELECT coupon_id AS ID FROM {$lookup_tb} WHERE post_status = 'publish' AND is_wt_gc_wallet_coupon = 0";
            $sql_placeholder = array();

            if('available_coupons' == $type || 'expired_coupons' == $type)
            {
                $section=(isset($args['section']) ? $args['section'] : 'my_account');
                $allowed_sections = array('my_account', 'checkout', 'cart');

                if("" !== $section && in_array($section, $allowed_sections))
                {
                    $sql .= " AND {$section}_display = 1";
                }

            }else /* 'auto_coupons' */
            {
                $sql .= " AND is_auto_coupon = 1";
            }

            //usage count check
            $used_by_meta_sql = "(SELECT COUNT(pm.meta_id) FROM {$wpdb->postmeta} AS pm WHERE pm.post_id = coupon_id AND pm.meta_key = '_used_by' AND pm.meta_value = %d)";

            //email restriction
            if("" !== trim($email))
            {
                $sql .= " AND (email_restriction = 'a:0:{}' OR email_restriction LIKE %s OR email_restriction LIKE '%*%')";
                $sql_placeholder[] = '%'.$wpdb->esc_like($email).'%';
            }else
            {
                $sql .= " AND (email_restriction = 'a:0:{}' OR email_restriction = '')";
            }


            //user roles
            $user_roles = (isset($user->roles) && is_array($user->roles) ? $user->roles : array());

            /**
             *  Guest users do not have user roles, so add one to the array to filter user coupons.
             *  @since 1.7.0
             */
            if( empty( $user_roles ) && 0 === $user_id ){
                $user_roles = array( 'wbte_sc_guest' );
            }
            
            if(!empty($user_roles))
            {
                $role_sql_arr = array();
                foreach($user_roles as $k => $user_role)
                {
                    $role_sql_arr[] = "user_roles LIKE %s";
                    $user_roles[$k] = '%'.$wpdb->esc_like($user_role).'%';
                }
                $sql .= " AND (user_roles = '' OR " . $wpdb->prepare(implode(" OR ", $role_sql_arr), $user_roles) . ")";
            }
            
            $gmt_offset = wc_timezone_offset();
            
            if('available_coupons'==$type || 'auto_coupons'==$type)
            {
                //expiry date check
                $sql .= " AND (expiry = '' OR 
                    ((UNIX_TIMESTAMP(expiry) + TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP()))) - %d) >= UNIX_TIMESTAMP()
                )";
                $sql_placeholder[] = $gmt_offset;

                //store credit amount check
                $sql .= " AND ((discount_type = 'store_credit' AND amount > 0) OR discount_type != 'store_credit')";

                //usage limit check
                $sql .= " AND (usage_limit = 0 OR (usage_limit > 0 AND usage_limit > usage_count))";
                $sql .= " AND (usage_limit_per_user = 0 OR (usage_limit_per_user > 0 AND usage_limit_per_user > {$used_by_meta_sql}))";               
                $sql_placeholder[] = $user_id;

            }else /* 'expired_coupons' */
            {
                //expiry date check, store credit amount check, usage limit check
                $sql .= " AND (
                    (expiry != '' AND 
                        ((UNIX_TIMESTAMP(expiry) + TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP()))) - %d) < UNIX_TIMESTAMP()
                    ) OR 
                    (discount_type = 'store_credit' AND amount <= 0) OR 
                    ((usage_limit > 0 AND usage_limit <= usage_count) OR (usage_limit_per_user > 0 AND usage_limit_per_user <= {$used_by_meta_sql}))
                )";

                $sql_placeholder[] = $gmt_offset;
                $sql_placeholder[] = $user_id;
            }


            /* process order by data */
            $orderby_data = (isset($args['orderby']) ? $args['orderby'] : self::get_available_coupons_sort_order());
            $orderby_arr = explode(":", $orderby_data);

            $orderby_allowed = array('created_date' => 'id', 'amount' => 'amount');
            $orderby = (!isset($orderby_allowed[$orderby_arr[0]]) ? 'created_date' : $orderby_arr[0]);

            $orderby_order = strtolower(isset($orderby_arr[1]) ? $orderby_arr[1] : 'asc');
            $orderby_order = strtoupper(!in_array($orderby_order, array('asc', 'desc')) ? 'asc' : $orderby_order);

            $sql .= " ORDER  BY {$orderby_allowed[$orderby]} {$orderby_order} LIMIT %d, %d";
            $sql_placeholder[] = $offset;
            $sql_placeholder[] = $limit;


            $final_sql = $wpdb->prepare($sql, $sql_placeholder);

            $post_ids = $wpdb->get_col($final_sql);
            $post_ids = ($post_ids ? $post_ids : array());



            /**
             *  Filter the post_ids. In some cases lookup table was unable to record the post unavailability. So we need to do a further check here to evaluate this.
             * 
             *  @since 1.4.4
             */
            $fetched_post_ids = $post_ids; //this is using to check any data exists in DB
            $remaining_limit = $limit - $already_fetched_ids_count; //may be not the first iteration.
            $filtered_post_ids = array();

            foreach($post_ids as $post_id)
            {
                if('publish' === get_post_status($post_id))
                {
                    $filtered_post_ids[] = $post_id;

                    if(count($filtered_post_ids) === $remaining_limit) //may be not the first iteration.
                    {
                        break;
                    }
                }
            }

            $post_ids = array_merge($already_fetched_ids, $filtered_post_ids); //may be this not the first loop. So merge post ids from previous recrusion.


            /**
             *  Recrusively fetch the coupon ids.
             *  Here we are checking  not empty of $fetched_post_ids instead of $post_ids because we have to find data in the DB is finished or not. This is usefull when all of the post_ids are filtered by the above `foreach`.
             *  Here we are altered the offset, but not the limit by remaining count, Because it may increase the iteration count in some situations.
             *  Count of $fetched_post_ids and $limit is equal then assumes database have more records to fetch 
             * 
             *  @since 1.4.4
             */ 
            if(!empty($fetched_post_ids) && count($fetched_post_ids) === $limit && count($post_ids) < $limit)
            {
                $new_offset = $limit + $offset;
                $args['already_fetched_ids'] = $post_ids;
                       
                return self::get_user_coupons($user, $new_offset, $limit, $args);
            }
            

            return apply_filters('wt_sc_alter_user_coupons', $post_ids, $args);
        }

        /**
         *  Fix the flex item alignment issue
         *  @since 1.4.1
         */
        public static function add_hidden_coupon_boxes($count=2)
        {
            for($i=0; $i<$count; $i++)
            {
                echo '<div class="wt-sc-hidden-coupon-box"></div>';
            }
        }

        /**
         *  Print available coupon for a user
         *  @param $user        object  WP_User instance.
         *  @param $section     string  section to print 
         *  @param $offset      int     offset of records 
         *  @param $limit       int     max records 
         *  @param $print       bool    If false function will only return the data as array. Otherwise print the data along with return
         *  
         *  @return array    array of coupon objects. Empty array if no record exists
         */
        /**
         *  Print available coupon for a user
         *  @param $user        object  WP_User instance.
         *  @param $section     string  section to print 
         *  @param $offset      int     offset of records 
         *  @param $limit       int     max records 
         *  @param $print       bool    If false function will only return the data as array. Otherwise print the data along with return
         *  
         *  @return array    array of coupon objects. Empty array if no record exists
         */
        public static function print_user_available_coupon($user = '', $section = 'my_account', $offset = 0, $limit = 30, $print = true, $by_shortcode = false)
        {
            global $wpdb;
            if(!$user)
            {
                $user= wp_get_current_user();
            }
            if($user)
            {
                $user_id = $user->ID; 
                $email = $user->user_email;
            }else
            {
                return array();
            }
            
            $orderby=(isset($_GET['wt_sc_available_coupons_orderby']) ? sanitize_text_field($_GET['wt_sc_available_coupons_orderby']) : self::get_available_coupons_sort_order());
            
            $post_ids = self::get_user_coupons($user, $offset, $limit, array('type'=>'available_coupons', 'section' => $section, 'orderby' => $orderby, 'by_shortcode' => $by_shortcode));
            $out=array();
            
            if($print)
            {
                echo '<div class="wt_coupon_wrapper">';
                
                //inject CSS for coupon block
                self::print_coupon_default_css();
            }
            if(!empty($post_ids))
            {
                /**
                 *  Display cart valid coupons
                 */
                $display_invalid_coupons  = apply_filters('wt_smart_coupon_display_invalid_coupons', true, $section);
                $e = 0;

                foreach($post_ids as $post_id)
                {
                    $post = get_post($post_id); 
                    
                    $coupon_obj = new WC_Coupon($post->ID);
                    $coupon_data  = self::get_coupon_meta_data($coupon_obj);
                    $coupon_data['display_on_page'] = ($by_shortcode ? 'by_shortcode' : $section.'_page');

                    if(false === $display_invalid_coupons && false === $coupon_obj->is_valid())
                    {
                        continue;
                    }

                    // Limit to defined email addresses.
                    if(!self::is_coupon_emails_allowed(array($email), $coupon_obj))
                    {
                        continue;
                    }

                    /* alter coupon post object before printing */
                    $post=apply_filters('wt_alter_coupon_for_user_before_printing', $post, $user, $section);
                    
                    /* alter coupon data before printing */
                    $coupon_data = apply_filters('wt_alter_coupon_data_for_user_before_printing', $coupon_data, $post, $user, $section);

                    if($print)
                    {
                        if(0 === $e) //to print coupon CSS in first template, to avoid same multiple CSS blocks
                        {
                            echo self::get_coupon_html($post, $coupon_data, "available_coupon", true);
                        }else
                        {
                            echo self::get_coupon_html($post, $coupon_data);
                        }

                        $e++;
                    }

                    $out[]=$coupon_obj;
                }

                if($print)
                {
                    self::add_hidden_coupon_boxes();
                }

            }else
            {
                if($print && 'my_account'===$section)
                {
                    echo '<div class="wt_sc_myaccount_no_available_coupons">';
                        echo wp_kses_post( apply_filters('wt_sc_alter_myaccount_no_available_coupons_msg', __("Sorry, you don't have any available coupons", 'wt-smart-coupons-for-woocommerce')) );
                    echo '</div>';
                }
            }
            if($print)
            {
                echo '</div>';
            }

            if($print && apply_filters('wt_sc_enable_pagination_in_user_available_coupons', true, $section) && !empty($post_ids))
            {
            ?>
                <div class="wt_sc_pagination">
                    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                        <?php 
                        global $wp;
                        $current_url = home_url($wp->request);
                        
                        $url_params=!is_array($_GET) ? array() : wc_clean( wp_unslash( $_GET ) ); 
                        
                        /* previous link */
                        $prev_url='';
                        $prev_link_html='';
                        if($offset>0)
                        {
                            $new_offset=max(($offset-$limit), 0); /* lesser than zero is not allowed */
                            $post_ids = self::get_user_coupons($user, $new_offset, 1, array('type'=>'available_coupons', 'section'=>$section, 'by_shortcode' => $by_shortcode));
                            
                            if(!empty($post_ids)) /* show previous link */
                            {
                                if(0 === $new_offset)
                                {
                                    unset($url_params['wt_sc_available_coupons_offset']);   
                                }else{
                                    $url_params['wt_sc_available_coupons_offset']=$new_offset;
                                }

                                $prev_url=$current_url.'?'.build_query($url_params);
                                $prev_link_html='<a href="'.esc_attr($prev_url).'" class="wt_sc_pagination_previous woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button">'.__('Previous', 'wt-smart-coupons-for-woocommerce').'</a>';
                            }
                        }

                        echo wp_kses_post(apply_filters("wt_sc_alter_user_available_coupons_previous_link_html", $prev_link_html, $prev_url));

                        /* next link */
                        $next_url='';
                        $next_link_html='';
                        $new_offset=$offset+$limit;
                        $post_ids = self::get_user_coupons($user, $new_offset, 1, array('type'=>'available_coupons', 'section'=>$section, 'by_shortcode' => $by_shortcode));
                        if(!empty($post_ids)) /* show next link */ 
                        {   
                            $url_params['wt_sc_available_coupons_offset']=$new_offset;                  
                            $next_url=$current_url.'?'.build_query($url_params);
                            $next_link_html='&nbsp;<a href="'.esc_attr($next_url).'" class="wt_sc_pagination_next woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button">'.__('Next', 'wt-smart-coupons-for-woocommerce').'</a>';
                        }

                        echo wp_kses_post(apply_filters("wt_sc_alter_user_available_coupons_next_link_html", $next_link_html, $next_url));

                        ?>
                    </div>
                </div>
            <?php
            }

            return $out;
        }
        

        /**
         *  Get sort order for available coupons
         *  @since 1.4.1
         */
        public static function get_available_coupons_sort_order()
        {
            return apply_filters('wt_sc_alter_available_coupons_sort_order', 'created_date:asc');
        }

        /**
         * Overwrite the coupon added message
         */
        public function start_overwrite_coupon_success_message($coupon, $new_message = "")
        {
            $this->overwrite_coupon_message[$coupon] =  $new_message;
            add_filter('woocommerce_coupon_message', array( $this, 'overwrite_coupon_code_message'), 10, 3);
        }

        /**
         * Display the coupon message
         */
        public function overwrite_coupon_code_message($msg, $msg_code, $coupon)
        {
            if(isset($this->overwrite_coupon_message[$coupon->get_code()]))
            {
                $msg = $this->overwrite_coupon_message[$coupon->get_code()];
            }
            return $msg;
        }

        /**
         * Stop overwriting coupon
         */
        public function stop_overwrite_coupon_success_message()
        {
            remove_filter('woocommerce_coupon_message', array( $this, 'overwrite_coupon_code_message'), 10);
            $this->overwrite_coupon_message = array();
        }

        /**
         * Checks if the given email address(es) matches the ones specified on the coupon.
         * @since 1.4.3 [Bug fix] Email validation is not working properly when email has capital letters
         * @param array $check_emails Array of customer email addresses.
         * @param array $restrictions Array of allowed email addresses.
         * @return bool
         */
        public static function is_coupon_emails_allowed( $check_emails, $coupon_obj )
        {
            $restrictions = $coupon_obj->get_email_restrictions();
                    
            if(empty($restrictions))
            {
                return true;
            }

            foreach ( $check_emails as $check_email ) {
                
                $check_email = strtolower($check_email);

                // With a direct match we return true.
                if ( in_array( $check_email, $restrictions, true ) ) {
                    return true;
                }

                // Go through the allowed emails and return true if the email matches a wildcard.
                foreach ( $restrictions as $restriction ) {
                    // Convert to PHP-regex syntax.
                    $regex = '/^' . str_replace( '*', '(.+)?', $restriction ) . '$/';
                    preg_match( $regex, $check_email, $match );
                    if ( ! empty( $match ) ) {
                        return true;
                    }
                }
            }

            // No matches, this one isn't allowed.
            return false;
        }



        /**
         *  Ajax action function for applying coupon on coupon block click
         * 
         *  @since 1.4.8
         */
        public function apply_coupon()
        {
            check_ajax_referer('wt_smart_coupons_apply_coupon', '_wpnonce');
            $coupon_code = ( isset( $_POST['coupon_code'])  ?  Wt_Smart_Coupon_Security_Helper::sanitize_item( $_POST['coupon_code'] ) : false);
            $coupon_id = ( isset($_POST['coupon_id'])  ?  Wt_Smart_Coupon_Security_Helper::sanitize_item( $_POST['coupon_id'], 'absint' ) : 0);
            
            if(!$coupon_code && 0 === $coupon_id)
            {
                return;
            }

            $coupon_obj = new WC_Coupon($coupon_id);

            if(0 === $coupon_obj->get_id()) /* coupon not exists */
            {
                return;
            }

            $coupon_code = $coupon_obj->get_code();

            if(0 < WC()->cart->get_cart_contents_count())
            {  
                WC()->cart->add_discount($coupon_code);
            }else
            {
                $message = __('Coupon code applied successfully, Please add products into cart', 'wt-smart-coupons-for-woocommerce');
                $message = apply_filters('wt_smart_coupon_click_to_apply_coupon_message_cart_empty', $message);

                $this->start_overwrite_coupon_success_message($coupon_code, $message);       
                WC()->cart->add_discount($coupon_code);          
                $this->stop_overwrite_coupon_success_message();
            }
            
            wc_print_notices();
            die();
        }



        /**
         *  Display available coupon in `cart/checkout block`
         * 
         *  @since  1.6.0
         *  @param  string   $block_content      Block content HTML
         *  @param  array    $block              Block data array
         *  @return string   $block_content      Block content HTML
         */
        public function display_available_coupon_in_block_cart_checkout( $block_content, $block ) {
            
            if ( 'woocommerce/cart' === $block['blockName'] || 'woocommerce/checkout' === $block['blockName'] ) {
                
                // Prepare coupon list HTML
                ob_start();            
                ( 'woocommerce/cart' === $block['blockName'] ? $this->display_available_coupon_in_cart() : $this->display_available_coupon_in_checkout() ); 
                $coupons_html = ob_get_clean();
                $block_content = $coupons_html . $block_content; 

            }

            return $block_content;
        }


        
        /**
         *  Ajax callback to save checkout values for block checkout.
         * 
         *  @since 1.6.0
         */
        public function set_block_checkout_values() {

            // Nonce verification.
            $nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
            $nonce = ( is_array( $nonce ) ? reset( $nonce ) : $nonce );

            if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wt_smart_coupons_public' ) || is_null( WC()->session ) ) {
                exit();
            }

            // Set payment method
            $payment_method = ( isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '' );
            WC()->session->set( 'chosen_payment_method', $payment_method );

            
            //Set shipping
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            $posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();

            if ( is_array( $posted_shipping_methods ) ) {
                foreach ( $posted_shipping_methods as $i => $value ) {
                    $chosen_shipping_methods[ $i ] = $value;
                }
            }
            
            WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

            exit();
        }
    }
}