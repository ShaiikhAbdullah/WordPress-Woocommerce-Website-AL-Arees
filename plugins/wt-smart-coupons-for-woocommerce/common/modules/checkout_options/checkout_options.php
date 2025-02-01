<?php
/**
 * Checkout options admin/public section
 *
 * @link       
 * @since 1.4.6    
 *
 * @package  Wt_Smart_Coupon  
 */

if(!defined('ABSPATH'))
{
    exit;
}

class Wt_Smart_Coupon_Checkout_Options
{
    public $module_base = 'checkout_options';
    public $module_id = '';
    public static $module_id_static = '';
    private static $instance = null;
    public static $meta_arr = array();

    public function __construct()
    {
        $this->module_id = Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static = $this->module_id;

        self::$meta_arr=array(
            '_wt_sc_shipping_methods' => array(
                'default'   => array(), /* default value */
                'type'      => 'text_arr', /* value type */
                'save_as'   => 'text', /* save as string */
            ),
            '_wt_sc_payment_methods' => array(
                'default'   => array(),
                'type'      => 'text_arr',
                'save_as'   => 'text',
            ),
            '_wt_sc_user_roles' => array(
                'default'   => array(),
                'type'      => 'text_arr',
                'save_as'   => 'text',
            ),
            '_wt_need_check_location_in' => array(
                'default' => 'billing',
                'type' => 'text',
            ),
            '_wt_coupon_available_location' => array(
                'default'   => array(),
                'type'      => 'text_arr',
                'save_as'   => 'text',
            ),
        );
    }

    /**
     *  Get Instance
     * 
     *  @since 1.4.6
     */
    public static function get_instance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new Wt_Smart_Coupon_Checkout_Options();
        }

        return self::$instance;
    }


    /**
     *  Get meta value, If meta not exists, will return default value
     *  
     *  @since  1.4.6
     *  @param  int     $post_id    Id of coupon 
     *  @param  string  $meta_key   Meta key
     *  @param  mixed   $default    Default value for meta(Optional). If not specified, then useses the already declared default value
     *  @return mixed   Value of the meta, Default value if no value exists
     */
    public static function get_coupon_meta_value($post_id, $meta_key, $default = '')
    {
        $default_vl=(isset(self::$meta_arr[$meta_key]) && isset(self::$meta_arr[$meta_key]['default']) ? self::$meta_arr[$meta_key]['default'] : $default);
        return (metadata_exists('post', $post_id, $meta_key) ? get_post_meta($post_id, $meta_key, true) : $default_vl);
    }


    /**
     *  Get meta value, If meta not exists, will return default value
     *  This function will process the meta value to an array
     *  
     *  @since  1.4.6
     *  @param  int     $post_id    Id of coupon 
     *  @param  string  $meta_key   Meta key
     *  @param  mixed   $default    Default value for meta(Optional). If not specified, then useses the already declared default value
     *  @return array   Value of the meta
     */
    public static function get_processed_coupon_meta_value($post_id, $meta_key, $default = '')
    {
        $meta_value = self::get_coupon_meta_value($post_id, $meta_key, $default);
        $meta_value = (is_string($meta_value) && $meta_value ? array_filter(explode(',', $meta_value)) : $meta_value);
        
        return (!is_array($meta_value) ? array() : $meta_value);
    }
}

Wt_Smart_Coupon_Checkout_Options::get_instance();
