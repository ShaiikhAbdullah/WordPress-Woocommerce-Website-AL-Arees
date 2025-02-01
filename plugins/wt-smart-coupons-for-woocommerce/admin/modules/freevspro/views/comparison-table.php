<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

$no_icon='<span class="dashicons dashicons-dismiss" style="color:#ea1515;"></span>&nbsp;';
$yes_icon='<span class="dashicons dashicons-yes-alt" style="color:#18c01d;"></span>&nbsp;';

global $wp_version;
if(version_compare($wp_version, '5.2.0')<0)
{
 	$yes_icon='<img src="'.plugin_dir_url(dirname(__FILE__)).'assets/images/tick_icon_green.png" style="float:left;" />&nbsp;';
}

$comparison_data=array(
	array(
		__('Coupon Management Features', 'wt-smart-coupons-for-woocommerce'),
		array(
			__('BOGO Coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Giveaway', 'wt-smart-coupons-for-woocommerce'),
			__('URL coupons', 'wt-smart-coupons-for-woocommerce'),
		),
		array(
			__('BOGO Coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Giveaway', 'wt-smart-coupons-for-woocommerce'),
			__('URL coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Purchase history-based coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Store credit', 'wt-smart-coupons-for-woocommerce'),
			__('Gift coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Sign-up coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Cart abandonment coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Combo coupons', 'wt-smart-coupons-for-woocommerce'),
		),
	),
	array(
		array(
			__('BOGO Coupon options', 'wt-smart-coupons-for-woocommerce'),
			__('The customers can buy one product and get:', 'wt-smart-coupons-for-woocommerce'),
		),
		array(
			__('Specific product', 'wt-smart-coupons-for-woocommerce'),
		),
		array(
			__('Specific product', 'wt-smart-coupons-for-woocommerce'),
			__('Any product from a specific category', 'wt-smart-coupons-for-woocommerce'),
			__('Any product in store', 'wt-smart-coupons-for-woocommerce'),
			__('Same product as in the cart', 'wt-smart-coupons-for-woocommerce'),
		),
	),
	array(
		__('Applicable coupon restrictions', 'wt-smart-coupons-for-woocommerce'),
		array(
			__('Shipping method', 'wt-smart-coupons-for-woocommerce'),
			__('Payment method', 'wt-smart-coupons-for-woocommerce'),
			__('User roles', 'wt-smart-coupons-for-woocommerce'),
			__('Product quantity', 'wt-smart-coupons-for-woocommerce'),
			__('Country', 'wt-smart-coupons-for-woocommerce'),
		),
		array(
			__('Shipping method', 'wt-smart-coupons-for-woocommerce'),
			__('Payment method', 'wt-smart-coupons-for-woocommerce'),
			__('User roles', 'wt-smart-coupons-for-woocommerce'),
			__('Exclude user roles', 'wt-smart-coupons-for-woocommerce'),
			__('Product quantity', 'wt-smart-coupons-for-woocommerce'),
			__('Country', 'wt-smart-coupons-for-woocommerce'),
			__('State', 'wt-smart-coupons-for-woocommerce'),
		),
	),
	array(
		__('Coupon Automation', 'wt-smart-coupons-for-woocommerce'),
		array(
			__('Apply coupon automatically', 'wt-smart-coupons-for-woocommerce'),
			__('Set a coupon start date', 'wt-smart-coupons-for-woocommerce'),
		),
		array(
			__('Apply coupon automatically', 'wt-smart-coupons-for-woocommerce'),
			__('Set a coupon start date', 'wt-smart-coupons-for-woocommerce'),
			__('Duplicate coupons', 'wt-smart-coupons-for-woocommerce'),
			__('Supports custom coupon code format (prefix, suffix, length)', 'wt-smart-coupons-for-woocommerce'),
		),
	),
	array(
		__('Advanced Coupon Display and Customization', 'wt-smart-coupons-for-woocommerce'),
		array(
			__('Coupon styling', 'wt-smart-coupons-for-woocommerce'),
			array(
				__('Coupon templates', 'wt-smart-coupons-for-woocommerce'),
				__('Standard', 'wt-smart-coupons-for-woocommerce'),
			),
		),
		array(
			__('Coupon styling', 'wt-smart-coupons-for-woocommerce'),
			array(
				__('Coupon templates', 'wt-smart-coupons-for-woocommerce'),
				__('Multiple options', 'wt-smart-coupons-for-woocommerce'),
			),
			__('Display count down discount sales banner', 'wt-smart-coupons-for-woocommerce'),
			__('Custom endpoints and endpoint title for coupon listing page', 'wt-smart-coupons-for-woocommerce'),
		),
	),
);

?>

<table style="width:100%; background: linear-gradient(to right, #fff, #F1FFF4); padding:37px 46px; border: 1px solid #6ABE45; border-radius: 10px 10px 0px 0px;">
	<tr>
		<td>
			<img src="<?php echo esc_url(WT_SMARTCOUPON_MAIN_URL . 'admin/modules/other_solutions/assets/images/smart-coupons-plugin.png');?>" style="float:left; width:51px;">
		</td>
		<td style="padding-left: 20px;">
			<p style="font-size:23px; font-weight:700;">⚡<?php esc_html_e('Supercharge your sales with', 'wt-smart-coupons-for-woocommerce'); ?> ✨ <?php esc_html_e('Smart Coupons for WooCommerce Pro!', 'wt-smart-coupons-for-woocommerce'); ?></p>
			<p><?php esc_html_e('Create offers on your store your customers can’t resist from irresistible BOGO deals to delightful giveaways.', 'wt-smart-coupons-for-woocommerce'); ?></p>
			<span style="color:#6ABE45;" class="dashicons dashicons-saved"></span><span style="color:#616161; font-size:14px;"><?php esc_html_e('99% Customer Satisfaction', 'wt-smart-coupons-for-woocommerce'); ?></span>&ensp;&ensp;<span style="color:#6ABE45;" class="dashicons dashicons-saved"></span><span style="color:#616161; font-size:14px;"><?php esc_html_e('30 Day money back guarantee', 'wt-smart-coupons-for-woocommerce'); ?></span>
		</td>
		<td>
			<a style="background:#4750CB; font-size:16px; font-weight:500; border-radius:11px; line-height:48px; width:203px; color:#fff; border:none; text-align: center;" class="button button-secondary" href="<?php echo esc_attr('https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_comparison&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION) ; ?>" target="_blank"><?php esc_html_e('Unlock pro features', 'wt-smart-coupons-for-woocommerce'); ?> <span class="dashicons dashicons-arrow-right-alt" style="line-height:48px;font-size:14px;"></span> </a>
		</td>
	</tr>
</table>
<table class="wt_smcpn_freevs_pro">
	<tr>
		<td style="width:400px;"><?php _e('FEATURES', 'wt-smart-coupons-for-woocommerce'); ?></td>
		<td><?php _e('FREE', 'wt-smart-coupons-for-woocommerce'); ?></td>
		<td><?php _e('PREMIUM', 'wt-smart-coupons-for-woocommerce'); ?>&nbsp;<span><img src="<?php echo esc_url(WT_SMARTCOUPON_MAIN_URL . 'images/crown.svg');?>" style="width:16px;"></span></td>
	</tr>
	<?php
	foreach ($comparison_data as $index_i => $val_arr)
	{
		?>
		<tr class="wt_sc_freevspro_table_hd_tr" data-index="<?php echo esc_attr($index_i);?>" data-state='visible'>
			<td colspan="3"><span class="wt_sc_freevspro_table_hd_tr_dashicon<?php echo esc_attr($index_i);?> dashicons dashicons-arrow-up-alt2"></span>&ensp;
				<?php
					if(!is_array($val_arr[0])){
						echo esc_html($val_arr[0]);
					}else{
						echo esc_html($val_arr[0][0]);
						echo wp_kses_post('<br><p style="font-size:15px; font-weight: 400; margin: 0px 0px 0px 30px;">' . $val_arr[0][1] . '</p>');
					}
				 ?>
			</td>
		</tr>
		
		<?php
			foreach($val_arr[2] as $index_j => $val){
				?>
					<tr class = "wt_sc_freevspro_table_body_tr wt_sc_freevspro_table_details_body<?php echo $index_i;?>" data-index="<?php echo $index_i;?>">
						<td>
							<?php 
								echo wp_kses_post( ! is_array($val) ? $val : $val[0] ) ;
							?>
						</td>
						<td>
							<?php
								if(!is_array($val)){
									if(in_array($val, $val_arr[1])){
										echo wp_kses_post( $yes_icon );
									}else{
										echo wp_kses_post( $no_icon );
									}
								}else{
									echo wp_kses_post( $comparison_data[$index_i][1][$index_j][1] );
								}
								
							?>
						</td>
						<td>
							<?php
									if ( ! is_array( $val ) ) {
										echo wp_kses_post( $yes_icon );
									}else{
										echo wp_kses_post( $val[1] );
									}
							?>
						</td>
					</tr>
				<?php
			}
		?>
		<?php
	}
	?>
</table>