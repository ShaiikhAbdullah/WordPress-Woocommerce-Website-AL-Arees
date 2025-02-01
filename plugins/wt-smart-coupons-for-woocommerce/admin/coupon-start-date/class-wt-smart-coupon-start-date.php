<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * implement Start date option for coupon
 *
 * @link       http://www.webtoffee.com
 * @since      1.2.2
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin
 * 
 */

if( ! class_exists ( 'WT_smart_Coupon_Start_Date' ) ) {

    
	class WT_smart_Coupon_Start_Date {

        function __construct() {
            add_action('woocommerce_coupon_options', array( $this,'add_coupon_start_date_field' ) ,9,2);
            add_action('woocommerce_process_shop_coupon_meta', array( $this,'save_coupon_start_date' ), 11, 2);
			add_filter( 'woocommerce_coupon_is_valid',   array( $this,'wt_woocommerce_coupon_is_valid' ), 10, 2 );

        }


        function add_coupon_start_date_field( $coupon_id, $coupon ) {
            
            $start_date = get_post_meta($coupon_id , '_wt_coupon_start_date', true );;

            woocommerce_wp_text_input(
                array(
                    'id'                => '_wt_coupon_start_date',
                    'value'             => esc_attr( $start_date ),
                    'label'             => __( 'Coupon start date', 'wt-smart-coupons-for-woocommerce' ),
                    'placeholder'       => 'YYYY-MM-DD',
                    'description'       => '',
                    'class'             => 'date-picker',
                    'custom_attributes' => array(
                        'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
                    ),
                )
            );
        }


        function save_coupon_start_date($post_id, $post)
        {
            $start_date = (isset($_POST['_wt_coupon_start_date']) ? sanitize_text_field($_POST['_wt_coupon_start_date']) : '');
            update_post_meta($post_id, '_wt_coupon_start_date', $start_date);
        }


        function wt_woocommerce_coupon_is_valid($valid, $coupon)
        {
            $coupon_id = $coupon->get_id();
            
            if(!metadata_exists('post', $coupon_id, '_wt_coupon_start_date'))
            {
                return $valid;
            }

            $start_date = get_post_meta($coupon_id, '_wt_coupon_start_date', true);
            $start_date_timestamp = 0;

            if("" != $start_date)
            {
                $start_date_timestamp = Wt_Smart_Coupon_Common::get_date_timestamp($start_date, false);
            }
            
            if($start_date_timestamp>0 && time()<$start_date_timestamp)
            {
                $valid = false;
                if ( ! $valid ) {
                    throw new Exception(
                        sprintf(__( 'Sorry, this coupon is only available after %s', 'wt-smart-coupons-for-woocommerce'), $start_date),109
    
                    );
                }
            }

            return $valid;
        }
    }

    $coupon = new WT_smart_Coupon_Start_Date();
}