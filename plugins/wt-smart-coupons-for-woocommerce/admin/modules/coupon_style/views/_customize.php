<?php
/**
 *  @since 1.4.7
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$coupon_types = (isset($view_params['coupon_types']) ? $view_params['coupon_types'] : array());
$current_coupon_style = (isset($view_params['current_coupon_style']) ? $view_params['current_coupon_style'] : array());
$coupon_styles = (isset($view_params['coupon_styles']) ? $view_params['coupon_styles'] : array());
$coupon_data_dummy = (isset($view_params['coupon_data_dummy']) ? $view_params['coupon_data_dummy'] : array());
$general_settings = (isset($view_params['general_settings']) ? $view_params['general_settings'] : array());
?>
<style type="text/css">
.wt_sc_coupon_preview{ float:left; width:400px; }
.wt_sc_coupon_colors{ float:left; width:auto; padding:10px 0px; }
.wt_sc_coupon_color_form_element{ margin-right:20px;}
.wt_sc_coupon_color_form_element .wt_sc_color_picker{ width:80px; height:30px; border:solid 1px #e5e5e5; border-radius:3px; }
.wt_sc_coupon_color_form_element .wp-picker-holder{ position:absolute; z-index:100; }
.wt_sc_coupon_color_form_element_label{ display:block; margin-bottom:3px; }
.wt_sc_coupon_change_theme_link{cursor:pointer; font-size:80%; display:inline-block;}
.wt_sc_coupon_templates .wt_sc_popup_body{ padding:30px; }
.wt_sc_coupon_templates .wt_sc_single_template_box{ float:left; width:350px; min-height:150px; padding-top:10px; margin:10px; text-align:center; cursor:pointer;}
.wt_sc_coupon_templates .wt_sc_single_template_box:hover{ box-shadow:0px 0px 5px #ccc; }
.wt_sc_coupon_templates .wt_sc_single_template_box .wt-single-coupon{ float:none; margin:0px; display:inline-block; }
.wt_sc_coupon_templates .wt_sc_single_template_box .wt-single-coupon.stitched_padding{ margin-left:3px; margin-top:3px; }
.wt_sc_coupon_templates .wt_sc_single_template_box label{ float:left; width:100%; padding:5px 0px; }
.wt_sc_template_refer{ display:none; }
<?php 
//inject CSS for coupon block
echo wp_kses_post(Wt_Smart_Coupon_Style::get_coupon_default_css());
?>
</style>

<div class="wt-sc-inner-content">

    <form method="post" class="wt_sc_coupon_style_form">
        <input type="hidden" name="action" value="wt_sc_customize_save">
        <?php
        // Set nonce:
        if(function_exists('wp_nonce_field'))
        {
            wp_nonce_field(WT_SC_PLUGIN_NAME);
        }
        ?>
        <ul class="wt_sc_sub_tab"> 
            <?php 
                foreach($coupon_types as $type_key => $type_name )
                {
                   ?>
                   <li data-target="<?php echo esc_attr($type_key); ?>"><a><?php echo esc_html($type_name); ?></a></li>
                   <?php
                }
            ?>     
        </ul>   
        <div class="wt_sc_sub_tab_container" style="min-height:230px;">
            <?php
            foreach($coupon_types as $type_key => $type_name)
            { 
                $selected_template = isset($current_coupon_style[$type_key]) ? $current_coupon_style[$type_key] : array();
                $style_name = (isset($selected_template['style']) ? $selected_template['style'] : '');
                ?>
                <div class="wt_sc_sub_tab_content" data-id="<?php echo esc_attr($type_key); ?>">
                    <h3>
                        <?php echo esc_html($type_name);?> 
                    </h3>
                    <input type="hidden" class="wt_sc_selected_coupon_style_input" name="wt_coupon_styles[<?php echo esc_attr($type_key);?>][style]" value="<?php echo esc_attr($style_name); ?>">
                    <div class="wt_sc_coupon_preview" data-coupon_type="<?php echo esc_attr($type_key);?>">
                        <?php
                        if(!empty($selected_template))
                        {
                            echo Wt_Smart_Coupon_Style::prepare_coupon_html(new WC_Coupon(0), $coupon_data_dummy, $type_key, true); //phpcs:ignore
                        }
                        ?>
                    </div>

                    <div class="wt_sc_coupon_colors" data-coupon_type="<?php echo esc_attr($type_key);?>">
                        <?php
                        if(!empty($selected_template))
                        {
                           $template_color=(isset($selected_template['color']) ? $selected_template['color'] : array());
                           foreach($template_color as $k=>$color)
                           {
                               ?>
                                <div class="wt_sc_coupon_color_form_element">
                                    <input name="wt_coupon_styles[<?php echo esc_attr($type_key);?>][color][]" value="<?php echo esc_attr($color); ?>" class="wt_sc_color_picker wt_sc_coupon_color" data-style_type="<?php echo esc_attr($style_name); ?>" data-coupon_type="<?php echo esc_attr($type_key);?>" data-index="<?php echo esc_attr($k);?>" />
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <?php 
                    if('used_coupon' === $type_key || 'expired_coupon' === $type_key)
                    {
                        $field_key = ('used_coupon' === $type_key ? 'display_used_coupons_my_account' : 'display_expired_coupons_my_account');
                        $label = ('used_coupon' === $type_key ? __('Display used coupons in My account?', 'wt-smart-coupons-for-woocommerce') : __('Display expired coupons in My account?', 'wt-smart-coupons-for-woocommerce'));
                        $field_val = (bool) (isset($general_settings[$field_key]) ? $general_settings[$field_key] : false);
                    ?>
                        <div style="float:left; width:100%;">
                            <input type="checkbox" style="float:left; margin-top:3px; margin-right:10px;" name="<?php echo esc_attr($field_key);?>" <?php checked($field_val); ?>>
                            <label><?php echo esc_html($label); ?></label>
                        </div>
                    <?php 
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        Wt_Smart_Coupon_Admin::add_settings_footer();
        ?>
    </form>
</div>