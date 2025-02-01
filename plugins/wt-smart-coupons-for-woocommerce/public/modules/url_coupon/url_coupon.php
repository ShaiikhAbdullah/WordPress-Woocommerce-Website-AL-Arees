<?php
/**
 * Url Coupon public facing
 *
 * @link       
 * @since 2.0.1    
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Url_Coupon_Public
{
    public $module_base='url_coupon';
    public $module_id='';
    protected $overwrite_coupon_message=array();
    public static $module_id_static='';
    private static $instance = null;

    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        add_action('wp_loaded', array($this, 'apply_url_coupon'));

        add_action('woocommerce_after_calculate_totals', array($this, 'apply_coupon_from_cookie'), 1000);
    }

    /**
     * Get Instance
     * @since 2.0.1
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Url_Coupon_Public();
        }
        return self::$instance;
    }

    /**
     * Apply coupon by URL
     */
    public function apply_url_coupon()
    {
        if(!isset($_GET['wt_coupon']))
        {
            return;
        }
        if(isset($_GET['removed_item']))
        {
            return;
        }

        $coupon_code=Wt_Smart_Coupon_Security_Helper::sanitize_item($_GET['wt_coupon']);
   
        if(""!=$coupon_code && Wt_Smart_Coupon_Common::is_coupon_exists($coupon_code))
        {
            $coupon_code=wc_sanitize_coupon_code($coupon_code);           
            if(WC()->cart->get_cart_contents_count()>0)
            {
                $new_message    =__('Coupon code applied successfully','wt-smart-coupons-for-woocommerce');
                $new_message = apply_filters('wt_smart_coupon_url_coupon_message', $new_message);
                if(is_array(WC()->cart->get_applied_coupons()) && !in_array($coupon_code, WC()->cart->get_applied_coupons()))
                {
                    $smart_coupon_obj=Wt_Smart_Coupon::get_instance();
                    $smart_coupon_obj->plugin_public->start_overwrite_coupon_success_message($coupon_code, $new_message);
                    WC()->cart->add_discount($coupon_code);              
                    $smart_coupon_obj->plugin_public->stop_overwrite_coupon_success_message();
                }else
                {
                    delete_transient('wt_smart_url_coupon_pending_coupon');
                }
            }else
            {
                set_transient('wt_smart_url_coupon_pending_coupon', $coupon_code, 1800);
                
                $shop_page_url  = get_page_link(get_option('woocommerce_shop_page_id'));               
                $new_message    = sprintf(__('Oops your cart is empty! Add %sproducts%s to your cart to avail the offer.', 'wt-smart-coupons-for-woocommerce'), '<a href="'.esc_attr($shop_page_url).'">', '</a>');
                $new_message = apply_filters('wt_smart_coupon_url_coupon_message', $new_message);               
                wc_add_notice($new_message, 'error');
            }
        }
    }

    /**
     * Apply coupon from cookie if coupon is not applied when visit URL. If cart count is zero when visting the URL.
     */
    public function apply_coupon_from_cookie()
    {
        $coupon_to_apply = get_transient('wt_smart_url_coupon_pending_coupon');
        
        if(''==$coupon_to_apply)
        {
            return;
        }
        $coupon_to_apply=wc_sanitize_coupon_code($coupon_to_apply);
        $coupon_obj = new WC_Coupon($coupon_to_apply);

        if(!(in_array($coupon_to_apply, WC()->cart->get_applied_coupons())))
        {      
            $applied = WC()->cart->add_discount($coupon_to_apply);
            if($applied)
            {
                delete_transient('wt_smart_url_coupon_pending_coupon');
            }
        }else
        {
            delete_transient('wt_smart_url_coupon_pending_coupon');
        }
        
        if(WC()->cart->get_cart_contents_count()>0)
        { 
            delete_transient('wt_smart_url_coupon_pending_coupon');
        }

    }

}
Wt_Smart_Coupon_Url_Coupon_Public::get_instance();