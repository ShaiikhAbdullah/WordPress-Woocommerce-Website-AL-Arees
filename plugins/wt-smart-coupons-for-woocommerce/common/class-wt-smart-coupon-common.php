<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The public/admin-facing functionality of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.3.5
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/common
 */

if( ! class_exists ( 'Wt_Smart_Coupon_Common' ) ) {
    class Wt_Smart_Coupon_Common {

        /**
         * The ID of this plugin.
         *
         * @since    1.3.5
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.3.5
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /*
         * module list, Module folder and main file must be same as that of module name
         * Please check the `register_modules` method for more details
         */
        public static $modules=array(
            'coupon_category',
            'coupon_shortcode',
            'giveaway_product',
            'coupon_restriction',
            'checkout_options', /** @since 1.4.6 */
            'coupon_style', /** @since 1.4.7 */
            'auto_coupon', /** @since 1.7.0 */
        );

        public static $existing_modules=array();

        private static $instance = null;

        public static $lookup_table_allowed_meta_keys = array(
            '_wt_make_auto_coupon' => array('is_auto_coupon', '%d'), 
            '_wbte_sc_auto_coupon_priority' => array('auto_coupon_priority', '%d'), /** @since 1.7.0 */ 
            '_wc_make_coupon_available' => array(array('my_account_display', 'cart_display', 'checkout_display'), '%d'), 
            'customer_email' => array('email_restriction', '%s'), 
            '_wt_sc_user_roles' => array('user_roles', '%s'),
            '_wt_sc_exclude_user_roles' => array('exclude_user_roles', '%s'), 
            '_wt_coupon_expiry_in_days' => array('expiry', '%s'), 
            '_wt_coupon_enable_days' => array('expiry', '%s'), 
            '_wt_coupon_start_date' => array('expiry', '%s'), 
            'date_expires' => array('expiry', '%s'), 
            'discount_type' => array('discount_type', '%s'),
            'coupon_amount' => array('amount', '%f'), 
            'usage_limit' => array('usage_limit', '%d'),
            'usage_limit_per_user' => array('usage_limit_per_user', '%d'), 
            'usage_count' => array('usage_count', '%d'), 
            '_wt_gc_user_wallet_coupon' => array('is_wt_gc_wallet_coupon', '%d'),
        );

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.3.5
         * @param      string    $plugin_name       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct($plugin_name, $version) {

            $this->plugin_name = $plugin_name;
            $this->version = $version;
   
        }

        /**
         * Get Instance
         * @since 1.3.5
         */
        public static function get_instance($plugin_name, $version)
        {
            if(self::$instance==null)
            {
                self::$instance=new Wt_Smart_Coupon_Common($plugin_name, $version);
            }

            return self::$instance;
        }

        /**
         *  Registers modules    
         *  @since 1.3.5     
         */
        public function register_modules()
        {            
            Wt_Smart_Coupon::register_modules(self::$modules, 'wt_sc_common_modules', plugin_dir_path( __FILE__ ), self::$existing_modules);          
        }

        /**
         *  Check module enabled    
         *  @since 1.3.5     
         */
        public static function module_exists($module)
        {
            return in_array($module, self::$existing_modules);
        }


        /**
         *  Prepare WC_DateTime object from date
         *  @since  1.4.1
         *  @param  $date   WC_DateTime|String|Int  Date value 
         *  @return WC_DateTime|null 
         */
        public static function prepare_date_object($date)
        {
            try {
                if ( empty( $date ) ) {
                    return null;
                }

                if ( is_a( $date, 'WC_DateTime' ) ) {
                    $datetime = $date;
                } elseif ( is_numeric( $date ) ) {
                    // Timestamps are handled as UTC timestamps in all cases.
                    $datetime = new WC_DateTime( "@{$date}", new DateTimeZone( 'UTC' ) );
                } else {
                    // Strings are defined in local WP timezone. Convert to UTC.
                    if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $date, $date_bits ) ) {
                        $offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
                        $timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
                    } else {
                        $timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $date ) ) ) );
                    }
                    $datetime = new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );
                }

                // Set local timezone or offset.
                if ( get_option( 'timezone_string' ) ) {
                    $datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
                } else {
                    $datetime->set_utc_offset( wc_timezone_offset() );
                }

                return $datetime;

            } catch ( Exception $e ) {} // @codingStandardsIgnoreLine.

            return null;
            
        }

        /**
         *  Prepare timestamp from WC_DateTime object
         *  @since  1.4.1
         *  @param  $date   WC_DateTime  Date object 
         *  @param  $gmt   bool  return GMT timestamp (optional). Default:true
         *  @return int  timestamp 
         */
        public static function get_date_timestamp($date, $gmt=true)
        {
            $datetime=self::prepare_date_object($date);

            if($datetime && is_a($datetime, 'WC_DateTime'))
            {
                return ($gmt ? $datetime->getOffsetTimestamp() : $datetime->getTimestamp());
            }

            return 0;
        }

        /**
         *  @since 1.4.1
         *  Check the coupon exists
         */
        public static function is_coupon_exists($coupon)
        {
            global $wpdb;
            if(!is_null($wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type ='shop_coupon' AND post_status = 'publish' AND post_title = %s ", $coupon))))
            {
                return true;                
            }
            return false;
        }


        /**
         *  Insert existing coupon data to lookup table
         *  @since 1.4.3
         *  @since 1.4.4    Code updated to handle slow sites
         *                  New filter: wt_sc_lookup_table_migration_batch_limit
         *   @since 1.4.5   Duplicate removal added
         */
        public function update_existing_coupon_data_to_lookup_table()
        {
            if(get_option('wt_sc_coupon_lookup_updated'))
            {
                return; //update was already done.  
            }

            global $wpdb;
            $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();
            
            Wt_Smart_Coupon::install_lookup_table(); //this method will check and create lookup table, if not exists

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return.
            {
                return;
            }

            $last_updated_id = absint(get_option('wt_sc_coupon_lookup_migration_last_id', 0));

            /**
             *  Alter migration batch limit
             * 
             *  @since 1.4.4
             */
            $batch_limit = apply_filters('wt_sc_lookup_table_migration_batch_limit', 100);

            $results = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_status, p.post_date_gmt FROM {$wpdb->posts} AS p WHERE p.post_type = 'shop_coupon' AND p.ID > %d ORDER BY p.ID ASC LIMIT %d", $last_updated_id, $batch_limit), ARRAY_A);

            foreach($results as $result)
            {
                $this->update_data_to_lookup_table($result, true);
                $last_updated_id = $result['ID'];
            }

            //remove duplicates
            $wpdb->query("DELETE t1 FROM {$lookup_tb} t1 INNER JOIN {$lookup_tb} t2 WHERE t1.id < t2.id AND t1.coupon_id = t2.coupon_id");

            $results = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_status, p.post_date_gmt FROM {$wpdb->posts} AS p WHERE p.post_type = 'shop_coupon' AND p.ID > %d ORDER BY p.ID ASC LIMIT 1", $last_updated_id), ARRAY_A);

            if(empty($results)) //no more data. So update as completed
            {
                add_option('wt_sc_coupon_lookup_updated', time());
                delete_option('wt_sc_coupon_lookup_migration_last_id'); // migration completed, so not required anymore
            }else
            {
                update_option('wt_sc_coupon_lookup_migration_last_id', $last_updated_id); //update current batch last id
            }

        }

        /**
         *  Update lookup table on coupon object save
         *  @since  1.4.3
         */
        public function update_coupon_lookup_on_object_save($data_obj, $data_store)
        {
            if(!is_a($data_obj, 'WC_Coupon'))
            {
                return;
            }

            $this->check_and_update_coupon_lookup_table($data_obj->get_id());
        }

        /**
         *  Update lookup table on coupon meta data save
         *  @since  1.4.3
         */
        public function update_coupon_lookup_on_meta_save($post_id, $post)
        {
            $this->check_and_update_coupon_lookup_table($post_id);
        }

        /**
         *  Update lookup table on coupon usage count change
         *  @since  1.4.3
         */
        public function update_coupon_lookup_on_usage_count_change($coupon, $new_count, $used_by)
        {
            $post_id = $coupon->get_id();

            if($this->is_no_lookup_table_entry($post_id)) //no record in lookup table
            {
                $this->check_and_update_coupon_lookup_table($post_id, true); //second argument is true for force insert, so it will skip the update/insert check.
            }else
            {
                global $wpdb;
                $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();
                
                if(Wt_Smart_Coupon::is_table_exists($lookup_tb))
                {
                    $wpdb->update($lookup_tb, array('usage_count' => $new_count), array('coupon_id' => $post_id), array('%d'), array('%d'));
                }
            }
        }

        /**
         *  Update lookup table on post meta update
         *  @since  1.4.3
         */
        public function update_coupon_lookup_on_postmeta_change($meta_id, $object_id, $meta_key, $meta_value)
        { 
            if(in_array($meta_key, array_keys(self::$lookup_table_allowed_meta_keys)) && 'shop_coupon' === get_post_type($object_id))
            {
                $this->check_and_update_coupon_lookup_table($object_id, false, $meta_key);
            }
        }

        /**
         *  Update lookup table on post status update
         *  @since  1.4.3
         */
        public function update_coupon_lookup_on_post_status_change($new_status, $old_status, $post)
        { 
            if('shop_coupon' === get_post_type($post))
            {
                $post_id = $post->ID;

                if($this->is_no_lookup_table_entry($post_id)) //no record in lookup table
                {
                    $this->check_and_update_coupon_lookup_table($post_id, true); //second argument is true for force insert, so it will skip the update/insert check.
                }else
                {
                    global $wpdb;
                    $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

                    if(Wt_Smart_Coupon::is_table_exists($lookup_tb))
                    {
                        $wpdb->update($lookup_tb, array('post_status' => $new_status), array('coupon_id' => $post_id), array('%s'), array('%d'));
                    }
                }
            }
        }

        /**
         *  Insert/update coupon data to lookup table
         *  
         *  @since  1.4.3
         *  @since  1.4.4 Added already exists checking
         *  @param  array   $data_row   post data array from database
         *  @param  boolean   $insert   insert or update to existing
         *  @param  string   $meta_key   Any specific meta key
         */
        private function update_data_to_lookup_table($data_row, $insert = false, $meta_key = '')
        {
            global $wpdb;
            $lookup_tb  = Wt_Smart_Coupon::get_lookup_table_name();

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return.
            {
                return;
            }

            $coupon_id  = $data_row['ID'];
            $coupon     = new WC_Coupon($coupon_id);

            if($insert && !$this->is_no_lookup_table_entry($coupon_id)) //inserting and already data exists
            {
                return;
            }

            /**
             *  Update a specific meta key
             */
            if(!$insert && "" !== $meta_key && isset(self::$lookup_table_allowed_meta_keys[$meta_key])) //specific meta key update
            {
                if('_wc_make_coupon_available' === $meta_key)
                {
                    $coupon_loc = $this->get_meta_data_for_lookup_table($coupon, $coupon_id, $meta_key);
                    $update_data = array(
                        'my_account_display'    => absint(in_array('my_account', $coupon_loc)), 
                        'cart_display'          => absint(in_array('cart', $coupon_loc)),
                        'checkout_display'      => absint(in_array('checkout', $coupon_loc)), 
                    );

                    $update_data_format = array('%d', '%d', '%d');
                }else
                {
                    $update_data = array(
                        self::$lookup_table_allowed_meta_keys[$meta_key][0]    => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, $meta_key), 
                    );
                    $update_data_format = array(self::$lookup_table_allowed_meta_keys[$meta_key][1]);
                }

                $wpdb->update($lookup_tb, $update_data, array('coupon_id' => $coupon_id), $update_data_format, array('%d'));

                return;
            }


            $coupon_loc = $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wc_make_coupon_available');

            $data_arr = array(
                        'coupon_id'             => $coupon_id, 
                        'is_auto_coupon'        => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wt_make_auto_coupon'),
                        'auto_coupon_priority'  => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wbte_sc_auto_coupon_priority'),
                        'my_account_display'    => absint(in_array('my_account', $coupon_loc)), 
                        'cart_display'          => absint(in_array('cart', $coupon_loc)),
                        'checkout_display'      => absint(in_array('checkout', $coupon_loc)), 
                        'post_status'           => $data_row['post_status'], 
                        'email_restriction'     => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'customer_email'),
                        'user_roles'            => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wt_sc_user_roles'),
                        'exclude_user_roles'    => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wt_sc_exclude_user_roles'),
                        'expiry'                => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'expiry'), 
                        'discount_type'         => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'discount_type'), 
                        'amount'                => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'coupon_amount'),
                        'usage_limit'           => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'usage_limit'),
                        'usage_limit_per_user'  => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'usage_limit_per_user'),
                        'usage_count'           => $this->get_meta_data_for_lookup_table($coupon, $coupon_id, 'usage_count'),
                        'is_wt_gc_wallet_coupon'=> $this->get_meta_data_for_lookup_table($coupon, $coupon_id, '_wt_gc_user_wallet_coupon'),
                    );

            $data_format_arr =  array(
                '%d',   //coupon_id
                '%d',   //is_auto_coupon
                '%d',   //auto_coupon_priority
                '%d',   //my_account_display
                '%d',   //cart_display
                '%d',   //checkout_display
                '%s',   //post_status
                '%s',   //email_restriction
                '%s',   //user_roles
                '%s',   //exclude_user_roles
                '%s',   //expiry
                '%s',   //discount_type
                '%f',   //amount
                '%d',   //usage_limit
                '%d',   //usage_limit_per_user
                '%d',   //usage_count
                '%d',   //is_wt_gc_wallet_coupon         
            );
            
            if($insert)
            {
                if($this->is_no_lookup_table_entry($coupon_id))
                {
                    $wpdb->insert($lookup_tb, $data_arr, $data_format_arr);
                }
            }else
            {
                $wpdb->update($lookup_tb, $data_arr, array('coupon_id' => $coupon_id), $data_format_arr, array('%d'));
            }
        }

        private function get_meta_data_for_lookup_table($coupon, $coupon_id, $meta_key)
        {
            $out = '';
            switch ($meta_key) 
            {
                case '_wt_make_auto_coupon':
                case '_wbte_sc_auto_coupon_priority':
                case '_wt_gc_user_wallet_coupon':
                    $out = absint(get_post_meta($coupon_id, $meta_key, true));
                    break;
                
                case '_wc_make_coupon_available':
                    $out = explode(",", strval(get_post_meta($coupon_id, '_wc_make_coupon_available', true)));
                    break;
                
                case 'customer_email':
                    $out = maybe_serialize($coupon->get_email_restrictions());
                    break;
                
                case '_wt_sc_user_roles':             
                case '_wt_sc_exclude_user_roles':
                    $out = get_post_meta($coupon_id, $meta_key, true);
                    break;
                
                case 'discount_type':
                    $out = $coupon->get_discount_type();
                    break;
                
                case 'coupon_amount':
                    $out = $coupon->get_amount();
                    break;
                
                case 'usage_limit':
                    $out = $coupon->get_usage_limit();
                    break;
                
                case 'usage_limit_per_user':
                    $out = $coupon->get_usage_limit_per_user();
                    break;
                
                case 'usage_count':
                    $out = $coupon->get_usage_count();
                    break;
                
                case 'expiry': //not a meta key                   
                case '_wt_coupon_expiry_in_days':                   
                case '_wt_coupon_enable_days':                   
                case '_wt_coupon_start_date':                   
                case 'date_expires':                   
                    $coupon_expiry = '';
                    $coupon_expiry_days = (int) get_post_meta($coupon_id, '_wt_coupon_expiry_in_days', true);
                    $coupon_expiry_days_enabled = (bool) (get_post_meta($coupon_id, '_wt_coupon_enable_days', true));


                    if(true === $coupon_expiry_days_enabled && $coupon_expiry_days > 0)
                    {
                        $coupon_created = $coupon->get_date_created()->getOffsetTimestamp();
                        $start_date = Wt_Smart_Coupon_Public::get_coupon_start_date($coupon_id , true, true);
                        $base_date = (isset($start_date) && !empty($start_date) ? $start_date : $coupon_created);
                        $coupon_expiry_days = '+'.$coupon_expiry_days.' days';
                        $coupon_expiry = strtotime($coupon_expiry_days, $base_date);
                    }else{

                        $coupon_expiry_date = $coupon->get_date_expires();
                        if(isset($coupon_expiry_date) && $coupon_expiry_date !== null)
                        {
                           $coupon_expiry = $coupon_expiry_date->getOffsetTimestamp();
                        }
                    }
                    $out = ($coupon_expiry ? date('Y-m-d', $coupon_expiry) : '');
                    break;
                
                default:
                    $out = '';
                    break;
            }

            return $out;
        }

        /**
         *  Check and update coupon data to lookup table
         *  @since  1.4.3
         *  @param  int   $post_id   post id
         *  @param  boolean   $force_insert   Force insert. Without data exists check.
         *  @param  string   $meta_key   Any specific meta key
         */
        private function check_and_update_coupon_lookup_table($post_id, $force_insert = false, $meta_key = '')
        {
            global $wpdb;

            $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return.
            {
                return;
            }

            $post_row = $wpdb->get_row($wpdb->prepare("SELECT ID, post_status, post_date_gmt FROM {$wpdb->posts} WHERE ID=%d", absint($post_id)), ARRAY_A);

            $this->update_data_to_lookup_table($post_row, ($force_insert ? $force_insert : $this->is_no_lookup_table_entry($post_id)), $meta_key);
        }

        /**
         *  Check there is lookup table entry exists
         *  @since  1.4.3
         *  @param  int   $post_id   post id
         *  @return  boolean   true when no data exists in lookup table
         */
        private function is_no_lookup_table_entry($post_id)
        {
            global $wpdb;
            $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return true.
            {
                return true;
            }

            $row = $wpdb->get_row($wpdb->prepare("SELECT coupon_id FROM {$lookup_tb} WHERE coupon_id=%d", $post_id), ARRAY_A);
            return empty($row);
        }


        public function check_and_update_lookup_table()
        {
            if(Wt_Smart_Coupon::get_lookup_table_version() > Wt_Smart_Coupon::get_installed_lookup_table_version()) //new version available
            {
                Wt_Smart_Coupon::install_lookup_table();
            }
        }


        /**
         *  Convert order items like cart items. This will help us to give compatibility for coupons in both frontend and backend.
         *  
         *  @since 1.4.4
         *  @param $order_items  array      Order items array
         *  @return $cart_items  array      Processed order items, structure similar to cart items
         */
        public static function convert_order_item_like_cart_item($order_items)
        {
            $new_cart_items = array();

            foreach($order_items as $order_item_key => $order_item)
            {
                $_product = $order_item->get_product();

                $new_cart_items[$order_item_key] = array(
                    'key'               => $order_item_key,
                    'product_id'        => $order_item->get_product_id(),
                    'variation_id'      => $order_item->get_variation_id(),
                    'variation'         => array(),
                    'quantity'          => $order_item->get_quantity(),      
                    'line_tax_data'     => $order_item->get_taxes(),
                    'line_subtotal'     => $order_item->get_subtotal(),
                    'line_subtotal_tax' => $order_item->get_subtotal_tax(),
                    'line_total'        => $order_item->get_total(),
                    'line_tax'          => $order_item->get_total_tax(),
                    'data'              => $_product,
                    
                );


                /**
                 *  Variation product. So prepare variation data 
                 */
                if(0 < $order_item->get_variation_id())
                {
                    $attributes = $_product->get_attributes();

                    foreach($attributes as $attribute_key => $attribute_val)
                    {
                        $attributes['attribute_'.$attribute_key] = $attribute_val;
                        unset($attributes[$attribute_key]);
                    }

                    $new_cart_items[$order_item_key]['variation'] = $attributes;
                }


                /**
                 *  Check current item is a giveaway and add giveaway data
                 * 
                 */
                $is_free_item = wc_get_order_item_meta($order_item_key, 'free_product', true);

                if($is_free_item)
                {
                    $new_cart_items[$order_item_key]['free_product']        = $is_free_item;
                    $new_cart_items[$order_item_key]['free_gift_coupon']    = wc_get_order_item_meta($order_item_key, 'free_gift_coupon', true);
                    $new_cart_items[$order_item_key]['free_category']       = wc_get_order_item_meta($order_item_key, 'free_category', true);
                }          
            }

            return apply_filters('wt_sc_alter_converted_order_items', $new_cart_items, $order_items);
        }

        /**
         *  To get total records in lookup table. Using in lookup table migration message.
         *  
         *  @since 1.4.5
         *  @return int Total records in lookup table
         */
        public static function get_lookup_table_record_count()
        {
            global $wpdb;
            $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

            if(!Wt_Smart_Coupon::is_table_exists($lookup_tb))  //table not created so return zero.
            {
                return 0;
            }

            $row = $wpdb->get_row("SELECT COUNT(DISTINCT coupon_id) AS total_records FROM {$lookup_tb}", ARRAY_A);

            return absint(!empty($row) && isset($row['total_records']) ? $row['total_records'] : 0);
        }


        /**
         * Get orders based on the arguments provided
         * 
         * @since   1.5.1
         * @static
         * @param   array   $args     Query arguments for `wc_get_orders` function
         * @return  array   Orders
         */
        public static function get_orders($args)
        {
            return wc_get_orders($args);
        }


        /**
         *  Get category ids of a product
         * 
         * @since  1.6.0
         * @param  int      $product_id     Product ID.
         * @return int[]
         */
        public static function get_product_cat_ids( $product_id ) {
            if ( apply_filters( 'wt_sc_product_categories_with_ancestors', false, $product_id ) ) {
                return wc_get_product_cat_ids( $product_id );
            } else {
                return wc_get_product_term_ids( $product_id, 'product_cat' );
            }
        }


        /**
         *  Function to determine whether the coupon has been activated.
         *  In some cases the coupon was published but need to be activated to use. For example generated coupons.
         * 
         *  @since  1.7.0
         *  @param  WC_Coupon   $coupon     Coupon object.
         *  @return bool        Is actiavted or not.
         */
        public static function is_activated_coupon( $coupon ) {

            /**
             *  Is the current coupon is actiavted for use.
             *  
             *  @since  1.7.0
             *  @param  bool        Is actiavted or not.   
             *  @param  WC_Coupon   $coupon     Coupon object.   
             */
            return (bool) apply_filters( 'wbte_sc_is_coupon_activated', true, $coupon );
        }
    }
}