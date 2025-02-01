<?php

/**
 *  Review request
 * 
 *  @since  1.4.5   Showing review banner based on created coupon count
 *
 * @package  Wt_Smart_Coupon  
 */


if (!defined('ABSPATH')) {
    exit;
}
class Wt_Smart_Coupon_Review_Request
{
    /**
     * config options 
     */
    private $plugin_title               =   "Smart Coupons for WooCommerce";
    private $review_url                 =   "https://wordpress.org/support/plugin/wt-smart-coupons-for-woocommerce/reviews/#new-post";
    private $plugin_prefix              =   "wt_smart_coupon"; /* must be unique name */
    private $days_to_show_banner        =   10; /* when did the banner to show */
    private $remind_days                =   10; /* remind interval in days */
    private $webtoffee_logo_url         =   '';



    private $start_date                 =   0; /* banner to show count start date. plugin installed date, remind me later added date */
    private $current_banner_state       =   2; /* 1: active, 2: waiting to show(first after installation), 3: closed by user, 4: user done the review, 5:remind me later, 6:not interested to review */
    private $banner_state_option_name   =   ''; /* WP option name to save banner state */
    private $start_date_option_name     =   ''; /* WP option name to save start date */
    private $banner_css_class           =   ''; /* CSS class name for Banner HTML element. */
    private $banner_message             =   ''; /* Banner message. */
    private $later_btn_text             =   ''; /* Remind me later button text */
    private $never_btn_text             =   ''; /* Never review button text. */
    private $review_btn_text            =   ''; /* Review now button text. */
    private $ajax_action_name           =   ''; /* Name of ajax action to save banner state. */
    private $allowed_action_type_arr    = array(
        'later', /* remind me later */
        'never', /* never */
        'review', /* review now */
        'closed', /* not interested */
    );

    private $created_count = 0;
    private $required_created_count = 100;


    public function __construct()
    {
        //Set config vars
        $this->set_vars();

        register_activation_hook(WT_SMARTCOUPON_FILE_NAME , array($this, 'on_activate'));
        register_deactivation_hook(WT_SMARTCOUPON_FILE_NAME, array($this, 'on_deactivate'));

        if($this->check_condition()) /* checks the banner is active now */
        {
            $this->banner_message = sprintf(__("Hey, we at %sWebToffee%s would like to thank you for using our Smart Coupon for WooCommerce plugin. %s We would really appreciate if you could take a moment to drop a quick review that will inspire us to keep going.", 'wt-smart-coupons-for-woocommerce'), '<b>', '</b>', '<br />');

            if($this->created_count > $this->required_created_count)
            {
                $this->banner_message = sprintf(__('%s Wow%s, you have created more than %s coupons with our %s Smart Coupon for WooCommerce plugin! %s That’s awesome! We’d love it if you take a moment to rate us and help spread the word.', 'wt-smart-coupons-for-woocommerce'), '<span>', '</span>', '<b>'.absint($this->required_created_count).'</b>', '<b>', '</b><br />');
            }

            /* button texts */
            $this->later_btn_text   = __("Remind me later", 'wt-smart-coupons-for-woocommerce');
            $this->never_btn_text   = __("Not interested", 'wt-smart-coupons-for-woocommerce');
            $this->review_btn_text  = __("Rate us now", 'wt-smart-coupons-for-woocommerce');

            add_action('admin_notices', array($this, 'show_banner')); /* show banner */
            add_action('admin_print_footer_scripts', array($this, 'add_banner_scripts')); /* add banner scripts */
            add_action('wp_ajax_' . $this->ajax_action_name, array($this, 'process_user_action')); /* process banner user action */
        }
    }

    /**
     *	Set config vars
     */
    public function set_vars()
    {
        $this->ajax_action_name             =   $this->plugin_prefix . '_process_user_review_action';
        $this->banner_state_option_name     =   $this->plugin_prefix . "_review_request";
        $this->start_date_option_name       =   $this->plugin_prefix . "_start_date";
        $this->banner_css_class             =   $this->plugin_prefix . "_review_request";

        $this->start_date                   =   absint(get_option($this->start_date_option_name));
        $banner_state                       =   absint(get_option($this->banner_state_option_name));
        $this->current_banner_state         =   ($banner_state == 0 ? $this->current_banner_state : $banner_state);
        $this->webtoffee_logo_url           =    WT_SMARTCOUPON_MAIN_URL . 'admin/images/review_banner_bg.webp';

        $this->created_count = (int) get_option('wt_sc_coupons_created', 0);
    }

    /**
     *	Actions on plugin activation
     *	Saves activation date
     */
    public function on_activate()
    {         
        if(0 === $this->start_date)
        {
            $this->reset_start_date();
        }
    }

    /**
     *	Actions on plugin deactivation
     *	Removes activation date
     */
    public function on_deactivate()
    {   
        delete_option($this->start_date_option_name);
    }

    /**
     *	Reset the start date. 
     */
    private function reset_start_date()
    {   
        update_option($this->start_date_option_name, time());
    }

    /**
     *	Update the banner state 
     */
    private function update_banner_state($val)
    {
        update_option($this->banner_state_option_name, $val);
    }

    /**
     *	Prints the banner 
     */
    public function show_banner()
    {
        $this->update_banner_state(1); /* update banner active state */
    ?>
        <div class="<?php echo esc_attr($this->banner_css_class); ?> notice-info notice is-dismissible">
            <p>
                <?php echo wp_kses_post($this->banner_message); ?>
            </p>
            <p>
                <a class="button button-primary" data-type="review"><?php echo esc_html($this->review_btn_text); ?></a>
                <a class="button button-secondary" style="color:#333; border-color:#ccc; background:#efefef;" data-type="later"><?php echo esc_html($this->later_btn_text); ?></a>
            </p>
            <div class="wt-smart-coupon-review-footer" style="position:absolute;right:0px; bottom:0px;">
                <span class="wt-smart-coupon-footer-icon" style="position:absolute;right:0px; bottom:0px;">
                    <img src="<?php echo esc_attr($this->webtoffee_logo_url); ?>" style="max-height:85px; margin-bottom:0px; float:right;">
                </span>
            </div>
        </div>
    <?php
    }

    /**
     *	Ajax hook to process user action on the banner
     */
    public function process_user_action()
    {
        check_ajax_referer($this->plugin_prefix);
        
        if(isset($_POST['wt_review_action_type']))
        {
            $action_type = sanitize_text_field($_POST['wt_review_action_type']);

            /* current action is in allowed action list */
            if(in_array($action_type, $this->allowed_action_type_arr))
            {
                if('closed' === $action_type)
                {
                    $new_banner_state = ($this->required_created_count < $this->created_count ? 6 : 3); //never:6, not now:3

                }elseif('never' === $action_type)
                {
                    $new_banner_state = 6;

                }elseif('review' === $action_type)
                {
                    $new_banner_state = 4;
                }else
                {
                    /* reset start date to current date */
                    $this->reset_start_date();
                    $new_banner_state = 5; /* remind me later */
                }
                $this->update_banner_state($new_banner_state);
            }
        }
        exit();
    }

    /**
     *	Add banner JS to admin footer
     */
    public function add_banner_scripts()
    {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce($this->plugin_prefix);
        ?>
        <style type="text/css">
            .wt_smart_coupon_review_request{ border-left-color:#FFE500; background:linear-gradient(to right, #fcf5bc, #fff) #e8e0a6; padding-right:0px; padding-bottom:0px; }
            .wt_smart_coupon_review_request b{ color:#FF6636; font-size:14px; }
            .wt_smart_coupon_review_request span{ font-weight:bold; font-size:14px; }
            .wt_smart_coupon_review_request .button-primary{ background:#FFE500; border-color:#ccc; color:#000; }
        </style>
        <script type="text/javascript">
            (function($) {
                "use strict";

                /* prepare data object */
                var data_obj = {
                    _wpnonce: '<?php echo esc_html($nonce); ?>',
                    action: '<?php echo esc_html($this->ajax_action_name); ?>',
                    wt_review_action_type: ''
                };

                $(document).on('click', '.<?php echo esc_html($this->banner_css_class); ?> a.button', function(e) {
                    e.preventDefault();
                    var elm = $(this);
                    var btn_type = elm.attr('data-type');
                    if ('review' === btn_type) {
                        window.open('<?php echo esc_url($this->review_url); ?>');
                    }
                    elm.parents('.<?php echo esc_html($this->banner_css_class); ?>').hide();

                    data_obj['wt_review_action_type'] = btn_type;
                    $.ajax({
                        url: '<?php echo esc_url($ajax_url); ?>',
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.<?php echo esc_html($this->banner_css_class); ?> .notice-dismiss', function(e) {
                    e.preventDefault();
                    data_obj['wt_review_action_type'] = 'closed';
                    $.ajax({
                        url: '<?php echo esc_url($ajax_url); ?>',
                        data: data_obj,
                        type: 'POST',
                    });

                });

            })(jQuery)
        </script>
        <?php
    }

    /**
     *	Checks the condition to show the banner
     */
    private function check_condition()
    {      
        if(1 === $this->current_banner_state) /* currently showing then return true */
        {
            return true;
        }

        if(2 === $this->current_banner_state || 5 === $this->current_banner_state) /* only waiting/remind later state */ 
        {
            if (0 === $this->start_date) /* unable to get activated date */ 
            {
                /* set current date as activation date */
                $this->reset_start_date();
                return false;
            }

            $days = (2 === $this->current_banner_state ? $this->days_to_show_banner : $this->remind_days);

            $date_to_check = $this->start_date + (86400 * $days);

            if($date_to_check <= time()) /* time reached to show the banner */ 
            {
                return true;
            }else
            {
                return false;
            }
        }

        if(3 === $this->current_banner_state && $this->required_created_count < $this->created_count)
        {
            return true; 
        }

        return false;
    }
}
new Wt_Smart_Coupon_Review_Request();
