<?php
/**
 * Coupon style admin/public section
 *
 * @link       
 * @since 1.4.7     
 *
 * @package  Wt_Smart_Coupon
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Style 
{
    public $module_base = 'coupon_style';
    public $module_id = '';
    public static $module_id_static = '';
    private static $instance = null;
    const TO_HIDE_CSS = 'wt_sc_hidden';

    public function __construct()
    {
        $this->module_id = Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static = $this->module_id;

        add_filter('wt_sc_module_default_settings', array($this, 'default_settings'), 10, 2);
    }

    
    /**
     *  Get Instance
     * 
     *  @since 1.4.7
     */
    public static function get_instance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new Wt_Smart_Coupon_Style();
        }
        return self::$instance;
    }

    
    /**
     *  Default settings
     *  
     *  @since 1.4.7
     *  @param array     $settings      Settings array
     *  @param string    $base_id       Module Id
     *  @return array    Default settings of current module
    */
    public static function default_settings($settings, $base_id)
    {
        if($base_id !== self::$module_id_static)
        {
            return $settings;
        }
        
        return array(
            'available_coupon' => array(
                'style' => 'stitched_padding',
                'color' => array('#2890a8', '#ffffff', '#ffffff')
            ),
            'used_coupon'  => array(
                'style' => 'stitched_padding',
                'color' => array('#eeeeee', '#000000', '#000000')
            ),
            'expired_coupon' => array(
                'style' => 'stitched_padding',
                'color' => array('#f3dfdf', '#eccaca', '#eccaca')
            ),
        );  
    }

    
    /**
     *  Migrate old settings, If exists
     *  Settings before 1.4.7
     * 
     *  @since 1.4.7
     *  
     */
    protected static function migrate_settings()
    {
        $smart_coupon_option = get_option('wt_smart_coupon_options');
        
        if(isset($smart_coupon_option['wt_active_coupon_bg_color']) 
            || isset($smart_coupon_option['wt_active_coupon_border_color'])
            || isset($smart_coupon_option['wt_used_coupon_bg_color'])
            || isset($smart_coupon_option['wt_used_coupon_border_color'])
            || isset($smart_coupon_option['wt_expired_coupon_bg_color'])
            || isset($smart_coupon_option['wt_expired_coupon_border_color'])
        ) /* old data exists */
        {
            $default_settings = self::default_settings(array(), self::$module_id_static);

            $default_settings['available_coupon']['color'][0] = isset($smart_coupon_option['wt_active_coupon_bg_color']) ? $smart_coupon_option['wt_active_coupon_bg_color'] : $default_settings['available_coupon']['color'][0];
            $default_settings['available_coupon']['color'][1] = isset($smart_coupon_option['wt_active_coupon_border_color']) ? $smart_coupon_option['wt_active_coupon_border_color'] : $default_settings['available_coupon']['color'][1];
            $default_settings['available_coupon']['color'][2] = $default_settings['available_coupon']['color'][1];


            $default_settings['used_coupon']['color'][0] = isset($smart_coupon_option['wt_used_coupon_bg_color']) ? $smart_coupon_option['wt_used_coupon_bg_color'] : $default_settings['used_coupon']['color'][0];
            $default_settings['used_coupon']['color'][1] = isset($smart_coupon_option['wt_used_coupon_border_color']) ? $smart_coupon_option['wt_used_coupon_border_color'] : $default_settings['used_coupon']['color'][1];
            $default_settings['used_coupon']['color'][2] = $default_settings['used_coupon']['color'][1];

            $default_settings['expired_coupon']['color'][0] = isset($smart_coupon_option['wt_expired_coupon_bg_color']) ? $smart_coupon_option['wt_expired_coupon_bg_color'] : $default_settings['expired_coupon']['color'][0];
            $default_settings['expired_coupon']['color'][1] = isset($smart_coupon_option['wt_expired_coupon_border_color']) ? $smart_coupon_option['wt_expired_coupon_border_color'] : $default_settings['expired_coupon']['color'][1];
            $default_settings['expired_coupon']['color'][2] = $default_settings['expired_coupon']['color'][1];

            Wt_Smart_Coupon::update_settings($default_settings, self::$module_id_static);

            //remove old option
            unset($smart_coupon_option['wt_active_coupon_bg_color'], $smart_coupon_option['wt_active_coupon_border_color'], $smart_coupon_option['wt_used_coupon_bg_color'], $smart_coupon_option['wt_used_coupon_border_color'], $smart_coupon_option['wt_expired_coupon_bg_color'], $smart_coupon_option['wt_expired_coupon_border_color']);
            update_option('wt_smart_coupon_options', $smart_coupon_option);
        }
    }

    
    /**
     *  Get current coupon styles
     *  Format:
     *      array(
     *          'coupon_type' => array(
     *              'style' => stitched_padding, 
     *              'color' => array(color1, color2, color3), 
     *          )
     *      )
     *  
     *  @since 1.4.7
     *  @return array  Current coupon settings array
     */
    public static function get_current_coupon_style()
    {
        self::migrate_settings(); /* migrate old settings. If exists */

        return Wt_Smart_Coupon::get_settings(self::$module_id_static);
    }

    
    /**
     *  Get Coupon types
     *  
     *  @since 1.4.7
     *  @return array  Array of coupon types
     */
    public static function get_coupon_types()
    {
        $coupon_types = array(
            'available_coupon'  =>  __('Available coupon', 'wt-smart-coupons-for-woocommerce'),
            'used_coupon'       =>  __('Used coupon', 'wt-smart-coupons-for-woocommerce'),
            'expired_coupon'    =>  __('Expired coupon', 'wt-smart-coupons-for-woocommerce'),
        );

        return apply_filters('wt_coupon_types', $coupon_types);
    }

    
    /**
     *  Preset coupon styles
     *  Format: 
     *      array(
     *          template_slug => array(
     *              'name'  => Display name of the template,
     *              'html'  => Template HTML,
     *              'color' => Color array for the template,
     *          )
     *      )
     * 
     *  @since 1.4.7
     *  @return array   Array of coupon styles
     */
    public static function coupon_styles()
    {
        $coupon_styles = array(
            'stitched_padding'  => array(
                'name'          => __('Style 1', 'wt-smart-coupons-for-woocommerce'),
                'html'          => self::get_template_html_from_template_file('stitched_padding'),
                'color'         => array('#2890a8', '#ffffff', '#ffffff')
            ),
        );

        return apply_filters('wt_smart_coupon_styles', $coupon_styles);
    }


    /**
     *  This function will take HTML from template file
     *  Template file format: data.template_{template_slug}.php
     * 
     *  @since  1.4.7
     *  @param  string      $template_slug    Slug of the template. 
     *  @return  string      $html    Template HTML from the template file, If file exists, otherwise returns empty string 
     */
    public static function get_template_html_from_template_file($template_slug)
    {
        $html = '';
        $file = plugin_dir_path( __FILE__ ).'data/data.template_'.$template_slug.'.php';

        /**
         *  To override the template file 
         */
        $file = apply_filters('wt_sc_alter_coupon_template_file_path', $file, $template_slug);
        
        if(file_exists($file))
        {
            ob_start();
            include $file;
            $html = ob_get_clean();
        }

        return $html;
    }

    
    /**
     * Print coupon HTML
     *
     *  @since  1.4.7
     */
    public static function get_coupon_html($style_data, $coupon_data)
    {
        $coupon_id=(isset($coupon_data['coupon_id']) ? $coupon_data['coupon_id'] : 0);
        $coupon = new WC_Coupon($coupon_id);

        echo self::prepare_coupon_html($coupon, $coupon_data, "available_coupon", true, $style_data); //phpcs:ignore
    }

    
    /**
     *  Prepare coupon HTML
     *  
     *  @since  1.4.7
     *  @param  WC_Coupon   $coupon         Coupon object, Null for coupon preview
     *  @param  array       $coupon_data    Coupon info array
     *  @param  string      $coupon_type    Coupon type. Eg: available_coupon, expired etc. Default: available_coupon
     *  @param  bool        $include_css    Include CSS along with HTML. Default: false
     *  @param  array       $style_data     To print coupon HTML of a specific template. This will be using in admin preview section. Default: empty array
     *  @return  string     $html           Prepared HTML for printing
     */
    public static function prepare_coupon_html($coupon, $coupon_data, $coupon_type = "available_coupon", $include_css = false, $style_data = array())
    {
        $current_coupon_style = self::get_current_coupon_style();

        if(empty($style_data)) //no custom style provided
        {
            $coupon_style   = (isset($current_coupon_style[$coupon_type]) ? $current_coupon_style[$coupon_type] : $current_coupon_style['available_coupon']);
        }else
        {
            $coupon_style   = $style_data;
        }
        
        $style_name     = $coupon_style['style'];
        $colors         = $coupon_style['color']; 
          
        
        //take template html
        $html = self::get_template_html_from_template_file($style_name);
        $html = apply_filters('wt_sc_alter_coupon_template_html', $html, $coupon_style, $coupon_type, $coupon);

        if("" !== $html)
        {
            //Add translation for translatable strings
            $html = preg_replace_callback('/__\[(.*?)\]__/s', array(__CLASS__, 'add_translation_for_coupon_html'), $html);


            /**
             *  Placeholder data array
             *  Format: array([placeholder] => value)
             */
            $find_replace = array();

            //prepare single coupon CSS class, and title
            $find_replace['[wt_sc_single_coupon_class]'] = '';
            $find_replace['[wt_sc_single_coupon_title]'] = '';

            switch($coupon_type)
            {
                case 'expired_coupon' : 
                    $find_replace['[wt_sc_single_coupon_class]'] = ' used-coupon expired';
                    break;
                case 'used_coupon' :
                    $find_replace['[wt_sc_single_coupon_class]'] = ' used-coupon';      
                    break;
                default :
                    $find_replace['[wt_sc_single_coupon_class]'] = 'active-coupon';
                    $find_replace['[wt_sc_single_coupon_title]'] = esc_attr__('Click to apply coupon', 'wt-smart-coupons-for-woocommerce');                   
            }
            $find_replace['[wt_sc_single_coupon_class]'] .= (isset($coupon_data['display_on_page']) ? ' '.esc_attr($coupon_data['display_on_page']) : ''); // for apply on click.


            //add colors
            foreach($colors as $color_k => $color_v)
            {
                $find_replace['[wt_sc_color_'.$color_k.']'] = esc_attr($color_v);
            }

            //add coupon id
            $coupon_id = (isset($coupon_data['coupon_id']) ? $coupon_data['coupon_id'] : 0);
            $find_replace['[wt_sc_coupon_id]'] = esc_attr($coupon_id);

            $is_preview_mode = (isset($coupon_data['preview_mode']) && $coupon_data['preview_mode']);
           
            if($is_preview_mode) //in preview mode, add dummy values
            {
                //dummy texts for preview mode
                $find_replace['[wt_sc_coupon_start]']           = wp_kses_post(isset($coupon_data['coupon_start']) ? $coupon_data['coupon_start'] : '');
                $find_replace['[wt_sc_coupon_expiry]']          = wp_kses_post(isset($coupon_data['coupon_expiry']) ? $coupon_data['coupon_expiry'] : '');
                $find_replace['[wt_sc_credit_history]']         = '';
                $find_replace['[wt_sc_credit_history_url]']     = '';
                $find_replace['[wt_sc_coupon_desc]']            = wp_kses_post(isset($coupon_data['coupon_desc']) ? $coupon_data['coupon_desc'] : '');
                $find_replace['[wt_sc_coupon_desc_wrapper]']    = ("" !== $find_replace['[wt_sc_coupon_desc]'] ? '&nbsp;' : ''); //to toggle description box
                $find_replace['[wt_sc_coupon_amount]']          = wp_kses_post(wc_price((isset($coupon_data['coupon_amount']) ? absint($coupon_data['coupon_amount']) : 10)));
                $find_replace['[wt_sc_coupon_type]']            = wp_kses_post(isset($coupon_data['coupon_type']) ? $coupon_data['coupon_type'] : __('Cart discount', 'wt-smart-coupons-for-woocommerce'));
                $find_replace['[wt_sc_coupon_code]']            = esc_html(isset($coupon_data['coupon_code']) ? $coupon_data['coupon_code'] : 'XXX-XXX-XXX');

            }else
            {

                //Start date
                $find_replace['[wt_sc_coupon_start]'] = (isset($coupon_data['start_date']) && 0 < $coupon_data['start_date']) ? Wt_Smart_Coupon_Public::get_coupon_start_expiry_date_texts($coupon_data['start_date']) : '';

                //Expiry date
                $find_replace['[wt_sc_coupon_expiry]'] = (isset($coupon_data['coupon_expires']) && 0 < $coupon_data['coupon_expires']) ? Wt_Smart_Coupon_Public::get_coupon_start_expiry_date_texts($coupon_data['coupon_expires'], 'expiry_date') : '';

                //Description
                $find_replace['[wt_sc_coupon_desc]'] = wp_kses_post($coupon->get_description());
                $find_replace['[wt_sc_coupon_desc_wrapper]'] = ("" !== $find_replace['[wt_sc_coupon_desc]'] ? '&nbsp;' : ''); //to toggle the visibility
                
                //Code
                $find_replace['[wt_sc_coupon_code]'] = esc_html($coupon->get_code());

                //Coupon type
                $find_replace['[wt_sc_coupon_type]'] = esc_html($coupon_data['coupon_type']);

                //Amount
                $find_replace['[wt_sc_coupon_amount]'] = wp_kses_post($coupon_data['coupon_amount']);

                $find_replace['[wt_sc_credit_history_url]'] = '';
                $find_replace['[wt_sc_credit_history]'] = ''; //to hide credit history block
            }


            /**
             *  To remove `click to apply` title and pointer cursor from coupon block in email.
             * 
             *  @since 1.6.0
             */
            if('email_coupon' === $coupon_type)
            {
                $find_replace['[wt_sc_single_coupon_title]'] = '';
                $find_replace['[wt_sc_single_coupon_class]'] = '';
            }


            /** @since 1.6.0    Add coupon code for attribute. */
            $find_replace['[wt_sc_coupon_code_attr]'] = esc_attr($find_replace['[wt_sc_coupon_code]']);


            /* filter to alter/add placeholder values */
            $find_replace = apply_filters('wt_sc_alter_coupon_html_placeholder_values', $find_replace, $coupon, $coupon_type);

            $html = self::hide_empty_elements($find_replace, $html, $coupon_type);

            $refer_html = ''; //applicable for preview mode only

            if($is_preview_mode) //in preview mode we have to keep a reference copy for JS operations
            {
                $refer_find_replace = $find_replace;
                
                foreach($colors as $color_k => $color_v) //remove color placeholders
                {
                    unset($refer_find_replace['[wt_sc_color_'.$color_k.']']);
                }

                /* replace the placeholder with real values. Except color placeholders */
                $refer_html = '<div class="wt_sc_template_refer" data-color-config="'.esc_attr(implode('|', $colors)).'">'.str_replace(array_keys($refer_find_replace), array_values($refer_find_replace), $html).'</div>';
                
                // remove style blocks, because style blocks are already available along with main HTML
                $style_blocks = self::get_style_blocks($refer_html);
                $refer_html = self::remove_style_blocks($refer_html, $style_blocks);
            }

            /* replace the placeholder with real values */
            $html = str_replace(array_keys($find_replace), array_values($find_replace), $html);

            if(!$include_css) //remove CSS block(if exists) from HTML. Only remove first one if multiple exists
            {
                $style_blocks = self::get_style_blocks($html);
                $html = self::remove_style_blocks($html, $style_blocks);
            }

            $html .= $refer_html; //applicable for preview mode only
        }
        
        return apply_filters('wt_sc_alter_final_coupon_template_html', $html, $coupon_style, $coupon_type, $coupon); 
    }


    /**
     *  Hide empty(without value) elements in coupon HTML. 
     *  This function will add a CSS class with `display:none` property to the element. Class name: wt_sc_hidden
     *  
     *  @since  1.4.7
     *  @param  array       $find_replace   Placeholder and corresponding values
     *  @param  string      $html           Target HTML
     *  @param  string      $coupon_type    Coupon type. Eg: available_coupon, expired etc. Default: available_coupon
     *  @return  string     $html           Processed HTML
     */
    private static function hide_empty_elements($find_replace, $html, $coupon_type)
    {
        $elements_to_hide = array(
            'wt_sc_coupon_start', 'wt_sc_coupon_expiry', 'wt_sc_credit_history', 'wt_sc_coupon_desc_wrapper',
        ); 

        /** 
         * Alter the element list.
         * CSS class name of the elements to be hidden. The class name and placeholder name must be same.
         */
        $elements_to_hide = apply_filters('wt_sc_alter_hide_empty_elements', $elements_to_hide, $coupon_type);

        foreach($elements_to_hide as $key => $value)
        {
            $css_class = self::sanitize_css_class_name($value);
            
            if(isset($find_replace['['.$value.']']))
            {
                if("" === $find_replace['['.$value.']'])
                {
                    $html = self::add_css_class($css_class, $html, self::TO_HIDE_CSS);
                }
            }else
            {
                $find_replace['['.$value.']'] = '';
                $html = self::add_css_class($css_class, $html, self::TO_HIDE_CSS);
            }
        }

        return $html;
    }

    
    /**
     *  Add CSS class to an HTML dom element
     *  
     *  @since  1.4.7
     *  @param  string      $elm_class      Existing class name of the element for selecting element
     *  @param  string      $html           Target HTML
     *  @param  string      $new_class      New CSS class to append
     *  @return  string     $html           Processed HTML
     */
    public static function add_css_class($elm_class, $html, $new_class)
    {
        $match = self::get_element_by_class($elm_class,$html);
        
        if($match) //found
        { 
            $elm_class      = $match[1].$elm_class.$match[2];
            $new_class_arr  = self::filter_css_classes($elm_class.' '.$new_class);
            $new_class      = implode(" ", $new_class_arr);
            
            return str_replace($elm_class, $new_class, $html);
        }

        return $html;
    }

    /**
     *  Take the class attribute code section of an HTML element by class name.
     *  
     *  @since  1.4.7
     *  @param  string      $elm_class      Existing class name of the element for selecting element
     *  @param  string      $html           Target HTML
     *  @return  array|bool     If found, then return an array of matches otherwise false
     */
    public static function get_element_by_class($elm_class, $html)
    {
        $matches=array();
        $re = '/<[^>]*class\s*=\s*["\']([^"\']*)'.$elm_class.'(.*?[^"\']*)["\'][^>]*>/m';
        
        if(preg_match($re,$html,$matches))
        {
            if(0 < strlen($matches[1]) && " " !== substr($matches[1], -1)) //data before class name must be empty string, if exists
            {
                return false;
            }

            if(0 < strlen($matches[2]) && " " !== substr($matches[2], 0, 1)) //data after class name must be empty string, if exists
            {
                return false;
            }

            return $matches;

        }else
        {
            return false;
        }
    }


    /**
     *  Remove duplicate and empty class names from class attribute of an element
     *  
     *  @since  1.4.7
     *  @param  string      $class      Class attribute value of the element
     *  @return  array     Array of unique class names
     */
    private static function filter_css_classes($class)
    {
        $class_arr = explode(" ", $class);

        return array_unique(array_filter($class_arr));
    }

    
    /**
     *  Remove CSS class of an HTML dom element
     *  
     *  @since  1.4.7
     *  @param  string      $elm_class      Existing class name of the element for selecting element
     *  @param  string      $html           Target HTML
     *  @param  string      $remove_class   New CSS class to be removed
     *  @return  string     $html           Processed HTML
     */
    private static function remove_class($elm_class, $html, $remove_class)
    {
        $match = self::get_element_by_class($elm_class, $html);
        
        if($match) //found
        {
            $elm_class      = $match[1].$elm_class.$match[2];
            $new_class_arr  = self::filter_css_classes($elm_class);
            
            foreach(array_keys($new_class_arr,$remove_class) as $key)
            {
                unset($new_class_arr[$key]);
            }

            $new_class = implode(" ", $new_class_arr);

            return str_replace($elm_class, $new_class, $html);
        }
        return $html;
    }


    /**
     *  Sanitize CSS class name string. 
     *  
     *  @since  1.4.7
     *  @param  string      $str      Class name
     *  @return  string     Sanitized class name
     */
    private static function sanitize_css_class_name($str)
    {
        return preg_replace('/[^\-_a-zA-Z0-9]+/', '', $str);
    }


    /**
     *  Get style block from HTML
     * 
     *  @since    1.4.7 
     *  @param    string  $html      template html
     *  @return   array  $style_arr  style blocks
     */
    private static function get_style_blocks($html, $inner_content = false)
    {       
        $re = '/<style.*?>(.*?)<\/style>/sm';

        if(preg_match_all($re, $html, $style_arr)) //style exists
        {
            $style_arr = ($inner_content ? $style_arr[1] : $style_arr[0]);
        }else
        {
            $style_arr = array();
        }

        return $style_arr;
    }


    /**
     *  Remove style block from HTML
     * 
     *  @since    1.4.7 
     *  @param  string  $html       template html
     *  @param  array   $style_arr  style blocks
     *  @return string  $html       style removed html
     */
    public static function remove_style_blocks($html, $style_arr)
    { 
        return str_replace($style_arr, '', $html);
    }

    
    /**
     *  Translatable strings are written as __[string]__ in templates, so we need to process those strings. 
     *  This function act as a calback for translation conversion code
     * 
     *  @since    1.4.7 
     *  @param  array  $match       array of match, when searching for translatable strings in the template HTML
     *  @return string  Translation compatible string  
     */
    private static function add_translation_for_coupon_html($match)
    {
        return (is_array($match) && isset($match[1]) && "" !== trim($match[1]) ? __($match[1], 'wt-smart-coupons-for-woocommerce') : '');
    }

    
    /**
     *  This function will print CSS for default coupon code block
     * 
     *  @since    1.4.7  
     */
    public static function get_coupon_default_css()
    {
        $coupon_css = '.wt_sc_single_coupon{ display:inline-block; width:300px; max-width:100%; height:auto; padding:5px; text-align:center; background:#2890a8; color:#ffffff; position:relative; }
        .wt_sc_single_coupon .wt_sc_hidden{ display:none; }
        .wt_sc_single_coupon.active-coupon{ cursor:pointer; }
        .wt_sc_coupon_amount{ font-size:30px; margin-right:5px; line-height:22px; font-weight:500; }
        .wt_sc_coupon_type{ font-size:20px;  font-weight:500; line-height:22px; }
        .wt_sc_coupon_code{ float:left; width:100%; font-size:19px; line-height:22px; }
        .wt_sc_coupon_code code{ background:none; font-size:15px; opacity:0.7; display:inline-block; line-height:22px; max-width:100%; word-wrap:break-word; }
        .wt_sc_coupon_content{ padding:10px 0px; float:left; width:100%; }   
        .wt_sc_coupon_desc_wrapper:hover .wt_sc_coupon_desc{ display:block}
        .wt_sc_coupon_desc{position:absolute; top:-18px; background:#333; color:#fff; text-shadow:none; text-align:left; font-size:12px; width:200px; right:-220px; padding:10px 20px; z-index:100; border-radius:8px; display:none; }
        .wt_sc_coupon_desc ul{margin:0!important;text-align:left;list-style-type:disc}
        .wt_sc_coupon_desc ul li{padding:0;margin:0;width:100%;height:auto;min-height:auto;list-style-type:disc!important}
        .wt_sc_coupon_desc_wrapper i.info{position:absolute; top:6px; right:10px; font-size:13px; font-weight:700; border-radius:100%; width:20px; height:20px; background:#fff; text-shadow:none; color:#2890a8; font-style:normal; cursor:pointer; line-height:20px; box-shadow:1px 1px 4px #333; }

        .wt_sc_credit_history_url{font-size:13px;font-weight:700;border-radius:100%;width:20px;height:20px;text-shadow:none;font-style:normal;cursor:pointer;position:absolute;right:12px;bottom:10px;text-align:center;line-height:20px;text-decoration:none!important;background:#fff}
        .wt_sc_credit_history_url span{font:bold 14px/1 dashicons}
        a.wt_sc_credit_history_url span:before{ line-height:20px; color:#2890a8; }

        @media only screen and (max-width: 700px)  {
          .wt_sc_coupon_content{z-index: 5; }
          .wt_sc_single_coupon .wt_sc_coupon_desc{ z-index: 100; right:auto; top:30px; left:0px; }
        }';
        
        return apply_filters('wt_sc_alter_coupon_default_css', $coupon_css);
    }

}

Wt_Smart_Coupon_Style::get_instance();
