<?php
/**
 * URL coupon
 *
 * @link       
 * @since 1.3.5    
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Url_Coupon_Admin
{
    public $module_base='url_coupon';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        /**
         *  Copy to clipboard and coupon URL help popup
         *  @since 1.4.1
         */
        add_action("admin_footer", array($this, "coupon_edit_page_section"));

        add_filter("wt_sc_plugin_settings_tabhead", array($this, 'settings_tabhead'),1);       
        add_filter("wt_sc_plugin_out_settings_form", array($this, 'out_settings_form'),1);
    }

    /**
     * Get Instance
     * @since 1.3.5
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Url_Coupon_Admin();
        }
        return self::$instance;
    }

    /**
     * Copy to clipboard and coupon URL help popup
     * @since 1.4.1
     */
    public function coupon_edit_page_section()
    {
        if(defined('WT_URL_COUPONS_PRO_VERSION'))
        {
           return;
        }
        $screen = get_current_screen();
        if(('post'===$screen->base && 'shop_coupon'===$screen->post_type) || (isset($_GET['page']) && WT_SC_PLUGIN_NAME==$_GET['page']) )
        {
            ?>
            <style type="text/css">
            .wt_sc_copy_to_clipboard{ position:absolute; right:0px;  background:#fff; border:solid 1px #2790c4; color:#2790c4;  border-radius:2px; padding:4px 7px; top:50%; margin-top:-31px; margin-right:3px; }
            .wt_sc_copy_to_clipboard .dashicons{ font-size:14px; line-height:21px; }
            .wt_sc_copy_to_clipboard:hover{ background:#2790c4; color:#fff; }
            .wt_sc_coupon_url_help_popup_link{ float:right; margin-top:5px; margin-right:3px; color:#007cba; text-decoration:underline; cursor:pointer; }
            .wt_sc_url_coupon_help .wt_sc_popup_body{ text-align:left; padding:20px; }
            .wt_sc_url_coupon_help .wt_sc_popup_body h2{ display:none; }
            .wt_sc_url_coupon_help .wt_section_title{ padding:0px 10px 30px 10px !important; box-shadow:none !important; }
            </style>
            <script type="text/javascript">
                function wt_sc_show_coupon_url_preview()
                {
                    var coupon_code_elm=jQuery('[name="post_title"]');
                    if(jQuery('[name="wt_sc_url_coupon_name"]').length>0)
                    {
                        coupon_code_elm=jQuery('[name="wt_sc_url_coupon_name"]');
                        var coupon_code=coupon_code_elm.find('option:selected').text().trim();
                    }else 
                    {   
                        if(coupon_code_elm.length==0)
                        {
                            return;
                        }
                        var coupon_code=coupon_code_elm.val().trim();
                    }

                    if(coupon_code!="")
                    {
                        var cart_url='<?php echo esc_url(wc_get_cart_url()); ?>';
                        var coupon_url=cart_url+'?wt_coupon='+coupon_code.toLowerCase();
                        jQuery('.wt_sc_url_preview').html(coupon_url);
                        jQuery('.wt_sc_copy_to_clipboard').show();
                    }else
                    {
                        jQuery('.wt_sc_copy_to_clipboard').hide();
                    }
                }
                jQuery(document).ready(function(){
                    
                    /* adding URL preview element(If not already added). This will be hidden and using for copy to clipboard functionality */
                    var url_coupon_preview=jQuery('.wt_sc_url_preview');
                    if(0==url_coupon_preview.length)
                    {
                        jQuery('.generate-coupon-code').after('<span class="wt_sc_url_preview" style="display:none;"></span>');
                    }

                    /* adding copy to clipboard element */
                    var post_title_input=jQuery('[name="post_title"]');
                    var copy_to_clipboard=jQuery('.wt_sc_copy_to_clipboard');
                    if(0==copy_to_clipboard.length && post_title_input.length>0)
                    {
                        jQuery('#titlewrap').css({'position':'relative'});
                        post_title_input.after('<span class="wt_sc_copy_to_clipboard" data-target="wt_sc_url_preview"><?php echo esc_html(__("Copy coupon URL", 'wt-smart-coupons-for-woocommerce')); ?><span class="dashicons dashicons-admin-page"></span></span>');
                    }

                    var generate_coupon_code=jQuery('a.generate-coupon-code');
                    if(generate_coupon_code.length>0)
                    {
                        generate_coupon_code.after('<span class="wt_sc_coupon_url_help_popup_link"><?php echo esc_html(__("How to use coupon URL?", 'wt-smart-coupons-for-woocommerce')); ?></span>');
                    }

                    jQuery('[name="post_title"]').on('input', function(){
                        wt_sc_show_coupon_url_preview();
                    });

                    jQuery('a.generate-coupon-code').on('click', function(){
                        setTimeout(function(){ wt_sc_show_coupon_url_preview(); }, 100);
                    });

                    wt_sc_show_coupon_url_preview();

                    jQuery('.wt_sc_coupon_url_help_popup_link').on('click', function(){
                        wt_sc_popup.showPopup(jQuery('.wt_sc_url_coupon_help'));
                    });
                });
            </script>
            <div class="wt_sc_url_coupon_help wt_sc_popup" style="width:850px;">
                <div class="wt_sc_popup_hd">
                    <div class="wt_sc_popup_title"><?php _e('How to use coupon URL', 'wt-smart-coupons-for-woocommerce');?></div>
                    <div class="wt_sc_popup_close">X</div>
                </div>
                <div class="wt_sc_popup_body">
                    <?php 
                    $image_path  = plugin_dir_url( __FILE__ ).'assets/images/';

                    $view_params = array(
                        'image_path' => $image_path,
                    );

                    include plugin_dir_path( __FILE__ ).'views/_tab_data.php';
                    ?>   
                </div>
            </div>
            <?php
        }
    }

    
    /**
     *  Tab head for plugin settings page
     *  
     *  @since 1.3.5
     *  
     */
    public function settings_tabhead($arr)
    {
        if(defined('WT_URL_COUPONS_PRO_VERSION'))
        {
           return $arr;
        }


        $added=0;
        $out_arr=array();
        foreach($arr as $k=>$v)
        {
            $out_arr[$k]=$v;
            if('wt-sc-layouts' === $k && 0 === $added) /* after help */
            {               
                $out_arr['wt-sc-'.$this->module_base]=__('URL coupon', 'wt-smart-coupons-for-woocommerce');
                $added=1;
            }
        }

        if(0 === $added)
        {
            $out_arr['wt-sc-'.$this->module_base]=__('URL coupon', 'wt-smart-coupons-for-woocommerce');
        }

        return $out_arr;
    }

    /**
     * @since 1.3.5
     * URL coupon tab content
     **/
    public function out_settings_form($args)
    {
        if(defined('WT_URL_COUPONS_PRO_VERSION'))
        {
           return;
        }

        $view_file = plugin_dir_path( __FILE__ ).'views/_tab_data.php';

        $image_path=plugin_dir_url( __FILE__ ).'assets/images/';

        $view_params=array(
            'image_path'=>$image_path,
        );

        Wt_Smart_Coupon_Admin::envelope_settings_tabcontent('wt-sc-'.$this->module_base, $view_file, '', $view_params, 0);

    }
}

Wt_Smart_Coupon_Url_Coupon_Admin::get_instance();