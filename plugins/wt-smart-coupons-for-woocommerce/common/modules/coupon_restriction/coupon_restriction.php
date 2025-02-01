<?php
/**
 * Coupon usage restriction admin/public section
 *
 * @link       
 * @since 1.4.0     
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wt_Smart_Coupon_Restriction
{
    public $module_base='coupon_restriction';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public static $meta_arr=array();
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        self::$meta_arr=array(
            '_wt_category_condition'=>array(
                'default'=>'or', /* default value */
                'type'=>'text', /* value type */
            ),'_wt_enable_product_category_restriction'=>array(
                'default'=>'yes',
                'type'=>'text',
            ),'_wt_product_condition'=>array(
                'default'=>'or',
                'type'=>'text',
            ),'_wt_use_individual_min_max'=>array(
                'default'=>'no',
                'type'=>'text',
            ),'_wt_min_matching_product_qty'=>array(
                'default'=>'',
                'type'=>'absint',
            ),'_wt_max_matching_product_qty'=>array(
                'default'=>'',
                'type'=>'absint',
            ),'_wt_min_matching_product_subtotal'=>array(
                'default'=>'',
                'type'=>'float',
            ),'_wt_max_matching_product_subtotal'=>array(
                'default'=>'',
                'type'=>'float',
            ),'_wt_sc_coupon_products'=>array(
                'default'=>array(),
                'type'=>'text_arr',
            ),'_wt_sc_coupon_categories'=>array(
                'default'=>array(),
                'type'=>'text_arr',
            ),
        );
    }

    /**
     * Get Instance
    */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Restriction();
        }
        return self::$instance;
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

    public static function prepare_items_data($item_ids, $wt_sc_items_data)
    {
        $dummy_min_max=self::get_dummy_min_max();
        $items_data=array();
        if(!empty($item_ids)) /* prepare dummy min max data from WC default fields */
        {
            $min_max_dummy=array_fill(0, count($item_ids), $dummy_min_max);
            $items_data=array_combine($item_ids, $min_max_dummy);
        }

        if(!empty($wt_sc_items_data)) /* meta data, merge with WC default product data */
        {
            foreach($items_data as $item_id=>$item_data)
            {
                $items_data[$item_id]=(isset($wt_sc_items_data[$item_id]) ? $wt_sc_items_data[$item_id] : $item_data);
            }

        }

        return $items_data;
    }

    public static function get_dummy_min_max()
    {
        return array('min'=>'', 'max'=>'');
    }
}
Wt_Smart_Coupon_Restriction::get_instance();