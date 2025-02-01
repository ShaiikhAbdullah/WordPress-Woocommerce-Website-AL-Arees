<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
$current_user = wp_get_current_user();
$user_id = $current_user->ID; 
$email = $current_user->user_email;
$printed_coupons=array(
    'available_coupons'=>array(),
    'used_coupons'=>array(),
    'expired_coupons'=>array(),
);

/**
 *  @since 1.3.5 Deprecated  
 */
do_action_deprecated('wt_smart_coupon_before_my_acocount_coupons', array(), '1.3.5', 'wt_smart_coupon_before_my_account_coupons');

do_action('wt_smart_coupon_before_my_account_coupons', $current_user);
?>
<div class="wt-mycoupons">
    <h4><?php _e('Available Coupons', 'wt-smart-coupons-for-woocommerce'); ?></h4>
    <div class="wt_sc_available_coupon_sort_by">
        <form>
            <span><?php _e('Sort by', 'wt-smart-coupons-for-woocommerce'); ?> </span>
            <?php $orderby=(isset($_GET['wt_sc_available_coupons_orderby']) ? sanitize_text_field($_GET['wt_sc_available_coupons_orderby']) : Wt_Smart_Coupon_Public::get_available_coupons_sort_order()); ?>
            <select name="wt_sc_available_coupons_orderby" onchange="this.form.submit()">
                <option value="created_date:desc" <?php selected("created_date:desc", $orderby); ?>><?php _e('Latest first', 'wt-smart-coupons-for-woocommerce'); ?></option>
                <option value="created_date:asc" <?php selected("created_date:asc", $orderby); ?>><?php _e('Latest last', 'wt-smart-coupons-for-woocommerce'); ?></option>
                <option value="amount:desc" <?php selected("amount:desc", $orderby); ?>><?php _e('Price high to low', 'wt-smart-coupons-for-woocommerce'); ?></option>
                <option value="amount:asc" <?php selected("amount:asc", $orderby); ?>><?php _e('Price low to high', 'wt-smart-coupons-for-woocommerce'); ?></option>
            </select>
        </form>
    </div>
    <?php
    $limit=apply_filters('wt_sc_my_account_available_coupons_per_page', 20);
    $offset=(isset($_GET['wt_sc_available_coupons_offset']) ? absint($_GET['wt_sc_available_coupons_offset']) : 0);
    $printed_available_coupons=Wt_Smart_Coupon_Public::print_user_available_coupon('', 'my_account', $offset, $limit);
    $printed_coupons['available_coupons']=$printed_available_coupons;
    ?>
</div>

<?php
do_action('wt_smart_coupon_after_my_coupons', $current_user, $printed_available_coupons);

$smart_coupon_options = Wt_Smart_Coupon::get_settings();

if(isset($smart_coupon_options['display_used_coupons_my_account']) && $smart_coupon_options['display_used_coupons_my_account'])
{
?>
    <div class="wt-used-coupons">
        <h4><?php _e("Used Coupons", "wt-smart-coupons-for-woocommerce"); ?></h4>
        <?php
        /**
         *  Display used coupons by the current user
         */       
        $used_coupons=Wt_Smart_Coupon_Public::get_coupon_used_by_a_customer($current_user);
        if(!empty($used_coupons))
        {
            $i = 0;

            foreach($used_coupons as $coupon)
            {
                $coupon_obj = new WC_Coupon($coupon);

                if(0 === $coupon_obj->get_id()) /* Coupon not exists */
                {
                    continue;
                }
                

                $coupon_post = get_post($coupon_obj->get_id()); /* post object */

                if(!is_a($coupon_post, 'WP_Post') || (isset($coupon_post->post_status) && 'publish' !== $coupon_post->post_status)) //post status is not published
                {
                    continue;
                }

                
                $coupon_data  = Wt_Smart_Coupon_Public::get_coupon_meta_data($coupon_obj);
                $coupon_data['display_on_page'] = 'my_account';
                
                if(0 === $i)
                {
                    echo '<div class="wt_coupon_wrapper">';
                }

                echo Wt_Smart_Coupon_Public::get_coupon_html($coupon_post, $coupon_data, 'used_coupon');

                $printed_coupons['used_coupons'][]=$coupon_obj;

                $i++;
            }
            if($i>0) /* close the coupon wrapper div */
            {
                Wt_Smart_Coupon_Public::add_hidden_coupon_boxes();
                echo '</div>';
            }
        }else
        {
            echo '<div class="wt_sc_myaccount_no_used_coupons">';
                echo wp_kses_post( apply_filters('wt_sc_alter_myaccount_no_used_coupons_msg', __("Sorry, you don't have any used coupons", 'wt-smart-coupons-for-woocommerce')));
            echo '</div>';
        }

        do_action('wt_smart_coupon_after_used_coupons');

        ?>
    </div>
<?php 
}

if(isset($smart_coupon_options['display_expired_coupons_my_account']) && $smart_coupon_options['display_expired_coupons_my_account'])
{
?>
    <div class="wt-expired-coupons">
        <h4><?php _e("Expired Coupons", "wt-smart-coupons-for-woocommerce"); ?></h4>
        <?php
        
        global $wpdb;
        $offset=0;
        $limit=apply_filters('wt_sc_my_account_expired_coupons_per_page', 50);
                  
        $post_ids=Wt_Smart_Coupon_Public::get_user_coupons($current_user, $offset, $limit, array('type'=>'expired_coupons'));

        if(!empty($post_ids))
        {
            echo '<div class="wt_coupon_wrapper">';
            foreach($post_ids as $post_id)
            {
                $post=get_post($post_id);
                $coupon_obj = new WC_Coupon($post->ID);
                $coupon_data  = Wt_Smart_Coupon_Public::get_coupon_meta_data($coupon_obj);
                $coupon_data['display_on_page'] = 'my_account_page';
                
                echo Wt_Smart_Coupon_Public::get_coupon_html($post, $coupon_data, 'expired_coupon');
                $printed_coupons['expired_coupons'][]=$coupon_obj;
            }

            Wt_Smart_Coupon_Public::add_hidden_coupon_boxes();
            echo '</div>';
        }else
        {
            echo '<div class="wt_sc_myaccount_no_expired_coupons">';
                echo wp_kses_post(apply_filters('wt_sc_alter_myaccount_no_expired_coupons_msg', __("Sorry, you don't have any expired coupons", 'wt-smart-coupons-for-woocommerce')));
            echo '</div>';
        }
    
        do_action('wt_smart_coupon_after_expired_coupons');

        ?>
    </div>
<?php
}

/**
 * @since 1.3.5
 */
do_action('wt_smart_coupon_after_my_account_coupons', $printed_coupons);