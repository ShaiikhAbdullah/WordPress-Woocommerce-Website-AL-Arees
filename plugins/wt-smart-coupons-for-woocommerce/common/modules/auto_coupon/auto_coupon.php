<?php
/**
 * Auto coupon public
 *
 * @link       
 * @since 1.7.0  
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wt_Smart_Coupon_Auto_Coupon_Common
{
    public $module_base = 'auto_coupon';
    public $module_id   = '';
    public static $module_id_static = '';
    private static $instance        = null;


    public function __construct() {
        $this->module_id = Wt_Smart_Coupon::get_module_id( $this->module_base );
        self::$module_id_static = $this->module_id;

        /**
         *  Add settings
         * 
         *  @since 1.7.0
         */
        add_filter( 'wt_sc_module_default_settings', array( $this, 'default_settings' ), 10, 2 );
    }


    /**
     *  Get Instance
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new Wt_Smart_Coupon_Auto_Coupon_Common();
        }
        return self::$instance;
    }


    /**
     *  Default settings
     *  
     *  @since  1.7.0
     *  @param  array       $settings   Settings array
     *  @param  string      $base_id    Module id
     *  @return array       Settings array
     */
    public function default_settings( $settings, $base_id ) {
        if ( $base_id !== $this->module_id ) {
            return $settings;
        }

        return array(
            'max_auto_coupons_to_apply' => '',
            'max_auto_coupons_to_check' => '',
        );
    }

    /**
     *  Get auto coupon apply limit.
     *  
     *  @since  1.7.0
     *  @param  int     $limit      Coupon limit, Optional, Default: 5
     *  @return int     $limit      Coupon limit
     */
    public function get_default_auto_coupon_limit( $limit = 5 ) {

        /**
         *  Filter to alter auto apply coupon apply limit.
         * 
         *  @param  int     $limit      Coupon limit
         */
        return (int) apply_filters( 'wt_smartcoupon_max_auto_coupons_limit', $limit );
    }

    
    /**
     *  Get total number of auto-apply coupons
     *  
     *  @since  1.7.0
     *  @return int     Total coupon count.
     */
    protected function get_total_auto_coupon_count() {
        
        global $wpdb;
        
        $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();
        $auto_coupon_count = $wpdb->get_var( "SELECT COUNT( coupon_id ) AS cnt FROM {$lookup_tb} WHERE post_status != 'trash' AND is_auto_coupon = 1" );
        
        return absint( is_null( $auto_coupon_count ) ? 0 : $auto_coupon_count );
    }

    /**
     *  Function to check specified coupon is an auto coupon
     * 
     *  @since  1.4.1
     *  @since  1.7.0   Method moved from public module to common module of auto coupon.
     * 
     *  @param  int|WC_Coupon   $coupon     Coupon id or WC_Coupon object  
     *  @return bool            True when coupon is an auto coupon. 
     */
    public static function is_auto_coupon( $coupon ){
        $coupon_id = ( is_object( $coupon ) ? $coupon->get_id() : $coupon );
        return wc_string_to_bool( get_post_meta( $coupon_id, '_wt_make_auto_coupon', true ) );
    }
}

Wt_Smart_Coupon_Auto_Coupon_Common::get_instance();