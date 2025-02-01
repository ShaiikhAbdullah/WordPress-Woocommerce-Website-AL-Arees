<?php

/**
 * 	Debug tab HTML
 * 	
 * 	@since 1.4.5
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
	<h3><?php _e('Debug','wt-smart-coupons-for-woocommerce');?></h3>
	<p><?php _e('Caution: Settings here are only for advanced users.', 'wt-smart-coupons-for-woocommerce');?></p>
	<form method="post" style="border-bottom:dashed 1px #ccc;">
		<?php
	    // Set nonce:
	    if(function_exists('wp_nonce_field'))
	    {
	        wp_nonce_field(WT_SC_PLUGIN_NAME);
	    }
	    ?>
		<table class="wt-sc-form-table">
			<?php
	        $wt_sc_public_modules = get_option('wt_sc_public_modules');
	        
	        if(false === $wt_sc_public_modules)
	        {
	            $wt_sc_public_modules=array();
	        }
	        ?>
	        <tr valign="top">
	            <th scope="row"><?php _e('Public modules', 'wt-smart-coupons-for-woocommerce');?></th>
	            <td>
	                <?php
	                foreach($wt_sc_public_modules as $k => $v)
	                {
	                    
	                    echo '<input type="checkbox" name="wt_sc_public_modules['.esc_attr($k).']" value="1" '.(1 == $v ? 'checked' : '').' /> ';
	                    echo esc_html($k);
	                    echo '<br />';
	                }
	                ?>
	            </td>
	        </tr>
			<?php
	        $wt_sc_common_modules = get_option('wt_sc_common_modules');
	        
	        if(false === $wt_sc_common_modules)
	        {
	            $wt_sc_common_modules = array();
	        }
	        ?>
	        <tr valign="top">
	            <th scope="row"><?php _e('Common modules', 'wt-smart-coupons-for-woocommerce');?></th>
	            <td>
	                <?php
	                foreach($wt_sc_common_modules as $k => $v)
	                {
	                    
	                    echo '<input type="checkbox" name="wt_sc_common_modules['.esc_attr($k).']" value="1" '.(1 == $v ? 'checked' : '').' /> ';
	                    echo esc_html($k);
	                    echo '<br />';
	                }
	                ?>
	            </td>
	        </tr>
	        <?php
	        $wt_sc_admin_modules = get_option('wt_sc_admin_modules');
	        
	        if(false === $wt_sc_admin_modules)
	        {
	            $wt_sc_admin_modules = array();
	        }

	        ?>
	        <tr valign="top">
	            <th scope="row"><?php _e('Admin modules', 'wt-smart-coupons-for-woocommerce');?></th>
	            <td>
	                <?php
	                foreach($wt_sc_admin_modules as $k=>$v)
	                {
	                    
	                    echo '<input type="checkbox" name="wt_sc_admin_modules['.esc_attr($k).']" value="1" '.(1 == $v ? 'checked' : '').' /> ';
	                    echo esc_html($k);
	                    echo '<br />';
	                }
	                ?>
	            </td>
	        </tr>
	        <tr valign="top">
	            <th scope="row">&nbsp;</th>
	            <td>
	                <input type="submit" name="wt_sc_admin_modules_btn" value="Save" class="button-primary">
	            </td>
	        </tr>	
		</table>
	</form>
<?php
//advanced settings form fields for module
do_action('wt_sc_module_settings_debug');
?>
</div>