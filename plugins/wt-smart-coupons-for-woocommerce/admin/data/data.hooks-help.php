<?php 
/**
 * 	Hooks and example array
 * 	
 * 	@since 1.5.2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$hooks_category_labels = array(
	'coupon_style' => __('Coupon style', 'wt-smart-coupons-for-woocommerce'),
	'coupon_clone' => __('Coupon clone', 'wt-smart-coupons-for-woocommerce'),
	'coupon_message' => __('Coupon message', 'wt-smart-coupons-for-woocommerce'),
	'my_account' => __('My account', 'wt-smart-coupons-for-woocommerce'),
	'checkout' => __('Checkout page', 'wt-smart-coupons-for-woocommerce'),
	'others' => __('Others', 'wt-smart-coupons-for-woocommerce'),
);
$wf_filters_help_doc_lists=array(
	/**
	 *  Coupon style
	 */
	'coupon_style' => array(
		'wt_sc_alter_coupon_template_html' => array(
			'title' => __('Alter coupon template HTML.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('Alter the coupon block HTML before printing.', 'wt-smart-coupons-for-woocommerce'),
			'example' => 'add_filter( "wt_sc_alter_coupon_template_html", "wt_sc_customize_coupon_html", 10, 4 );
function wt_sc_customize_coupon_html( $html, $coupon_style, $coupon_type, $coupon ) {

	return \'<div class="wt_sc_single_coupon [wt_sc_single_coupon_class]" data-id="[wt_sc_coupon_id]" title="[wt_sc_single_coupon_title]">
    			<div class="wt_sc_coupon_content wt-coupon-content">
			        <div class="wt-coupon-amount">
			            <span class="wt_sc_coupon_amount amount">[wt_sc_coupon_amount]</span>
			        </div>
			        <div class="wt_sc_coupon_code wt-coupon-code"> 
			            <code>[wt_sc_coupon_code]</code>
			        </div>
    			</div>
		</div>\';
}',
		),
		'wt_sc_alter_coupon_html_placeholder_values' => array(
			'title' => __('Add value to custom placeholder.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('If you want to add any custom dynamic values to the coupon block. Add a placeholder to the coupon template first and assign the value to that placeholder via this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => 'add_filter( "wt_sc_alter_coupon_html_placeholder_values", "wt_sc_add_value_to_custom_placeholder" );
function wt_sc_add_value_to_custom_placeholder( $find_replace, $coupon, $coupon_type ) {

	// You have to add the [wt_sc_custom_placeholder] in the coupon template first.
	$find_replace["[wt_sc_custom_placeholder]"] = $coupon->get_code();

	return $find_replace;
}',
		),
		'wt_sc_alter_coupon_default_css' => array(
			'title' => __('Alter the coupon default style.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('If you want to customize the style, you can use this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => 'add_filter( "wt_sc_alter_coupon_default_css", "wt_sc_change_coupon_bg" );
function wt_sc_change_coupon_bg( $coupon_css ) {

	$coupon_css .= ".wt_sc_single_coupon{ background:red !important; }"; // change the background color to red.
	return $coupon_css;
}',
		),
		'wt_sc_alter_coupon_title_text' => array(
			'title' => __('Alter the coupon title.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('Alter the coupon title in the coupon block.', 'wt-smart-coupons-for-woocommerce'),
			'example' => 'add_filter( "wt_sc_alter_coupon_title_text", "wt_sc_alter_coupon_title_text", 10, 2 );
function wt_sc_alter_coupon_title_text( $label, $coupon ) {
	
	return $coupon->is_type( "wt_sc_bogo" ) ? __( "Buy one get one", "wt-smart-coupons-for-woocommerce" ) : $label; // Change the label in the coupon block when the coupon type is BOGO. 
}',
		),
		'wt_sc_alter_coupon_start_expiry_date_text' => array(
			'title' => __('Change the text of the coupon\'s start and expire dates.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default, "Start on" and "Expires on" will be the text if a coupon has a starting date and an expiration date; it can be changed by this hook.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_coupon_start_expiry_date_text", "wt_sc_change_coupon_start_expiry_date_text", 10, 3 );

function wt_sc_change_coupon_start_expiry_date_text( $date_text, $date, $type ){
	
	return ($type=="start_date" ? __("Coupon starts on ", "wt-smart-coupons-for-woocommerce") : __("Coupon expires on ", "wt-smart-coupons-for-woocommerce")). esc_html(date_i18n(get_option("date_format", "F j, Y"), $date));
}',
		),
	),

	/**
	 *  Coupon clone
	 */
	'coupon_clone' => array(
		'wt_smartcoupon_default_duplicate_coupon_status' => array(
			'title' => __('Set the cloned coupon status', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('Set the cloned coupon status.', 'wt-smart-coupons-for-woocommerce'),
			'example' => 'add_filter( "wt_smartcoupon_default_duplicate_coupon_status", "wt_sc_set_cloned_coupon_status_to_draft" );
function wt_sc_set_cloned_coupon_status_to_draft( $status ) {

	return "draft"; // Set the coupon status to draft.
}',
		),
	),

	/**
	 *  Coupon message
	 */
	'coupon_message' => array(
		'wt_smart_coupon_auto_coupon_message' => array(
			'title' => __('Change auto coupon message.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the coupon applied message when the coupon is applied automatically.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_smart_coupon_auto_coupon_message", "wt_sc_change_auto_coupon_message", 10, 1 );

function wt_sc_change_auto_coupon_message($coupon){
	
	return __("You got a coupon", "wt-smart-coupons-for-woocommerce") ; 
}',
		),
		'wt_sc_alter_user_role_validation_message' => array(
			'title' => __('Change user role validation message.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change user role invalid message when coupon applied by an invalid user.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_user_role_validation_message", "wt_sc_change_user_role_validation_message" );

function wt_sc_change_user_role_validation_message($message){
	return __("Your role does not qualify for this coupon.", "wt-smart-coupons-for-woocommerce");
}',
		),
		'wt_sc_alter_individual_min_max_quantity_validation_message' => array(
			'title' => __('Change the minimum and maximum individual quantity validation message.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the minimum and maximum quantity validation messages when enabling "Product/Category restrictions" and "Individual quantity restriction" in coupon settings.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_individual_min_max_quantity_validation_message", "wt_sc_change_individual_min_max_quantity_validation_message", 10, 2 );

function wt_sc_change_individual_min_max_quantity_validation_message( $msg, $array ){
	if("min"==$array["type"])
	{
		return sprintf( __("For this coupon minimum %s quantity of %s is required.", "wt-smart-coupons-for-woocommerce"), $array["quantity"], $array["item_name"]);
	}elseif("max"==$array["type"])
	{
		return sprintf( __("For this coupon maximum allowed quantity of %s is %s.", "wt-smart-coupons-for-woocommerce"), $array["item_name"], $array["quantity"]);
	}else
	{
		return __("Your cart does not meet the quantity eligibility criteria for this coupon.", "wt-smart-coupons-for-woocommerce");
	}
}',
		),
		'wt_sc_alter_quantity_restriction_messages' => array(
			'title' => __('Change quantity restriction message', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the minimum and maximum quantity validation messages when "Product/Category restrictions" is disabled. If “Individual quantity restriction” is disabled, the message in if($is_global) will appear.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_quantity_restriction_messages", "wt_sc_change_quantity_restriction_messages", 10, 5 );

function wt_sc_change_quantity_restriction_messages( $out, $coupon_code, $qty, $is_global, $type ){
	
	if("min"==$type)
		{
			if($is_global)
			{
				return sprintf(__("Coupon valid for %s items; ensure cart has required quantity to redeem.", "wt-smart-coupons-for-woocommerce"), $qty);
			}else
			{
				return sprintf(__("Coupon requires %s minimum eligible items per product; add more to redeem.", "wt-smart-coupons-for-woocommerce"), $qty);
			}
		}else
		{
			if($is_global)
			{
				return sprintf(__("This coupon can be applied to a maximum of %s eligible products.", "wt-smart-coupons-for-woocommerce"), $qty);
			}else
			{
				return sprintf(__("Each eligible item has a maximum allowable quantity of %s.", "wt-smart-coupons-for-woocommerce"), $qty);
			}
		}
}',
		),
		'wt_sc_alter_multiple_giveaway_added_msg' => array(
			'title' => __('Change message when multiple giveaway products added', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the message when more than one giveaway product is added to the cart.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter("wt_sc_alter_multiple_giveaway_added_msg", "wt_sc_change_multiple_giveaway_added_msg");

function wt_sc_change_multiple_giveaway_added_msg(){
	return __("Congratulations! Your cart is now filled with amazing giveaway items!", "wt-smart-coupons-for-woocommerce");
}',
		),
		'wt_smartcoupon_give_away_message' => array(
			'title' => __('Change message for choosing giveaway products', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('When a giveaway product is a variable product, a message with a product variation will show to choose. This hook will help to change the title.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_smartcoupon_give_away_message", "wt_sc_change_giveaway_message", 10, 3 );

function wt_sc_change_giveaway_message($message_html, $coupon_code, $coupon_id){
	return \'<h4 class="giveaway-title">\'. __("Congratulations! Choose your freebie:", "wt-smart-coupons-for-woocommerce") .\'<span class="coupon-code">[ \'.$coupon_code.\' ]</span></h4>\';
}',
		),
		'wt_smart_coupon_url_coupon_message' => array(
			'title' => __('Change url coupon message.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the coupon applied message when the coupon is applied by url.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter("wt_smart_coupon_url_coupon_message", "wt_sc_change_url_coupon_message");

function wt_sc_change_url_coupon_message($msg){
	
	if(WC()->cart->get_cart_contents_count()>0)
	{
		return __("You got a coupon","wt-smart-coupons-for-woocommerce");
	}else{
		$shop_page_url  = get_page_link(get_option("woocommerce_shop_page_id"));               
		return sprintf(__("Your cart is empty! Add %sproducts%s to avail the offer.", "wt-smart-coupons-for-woocommerce"), \'<a href="\'.esc_attr($shop_page_url).\'">\', \'</a>\');
	}
}',
		),
		
	),

	/**
	 *  My account
	 */
	'my_account' => array(
		'wt_sc_alter_myaccount_no_available_coupons_msg' => array(
			'title' => __('Message when no coupons available to show in my account coupons page.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the no coupons available message, use this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_myaccount_no_available_coupons_msg", "wt_sc_change_no_coupon_available_msg" );
function wt_sc_change_no_coupon_available_msg( $msg ) {
	
	return __( "No coupons found.", "wt-smart-coupons-for-woocommerce" ); // Add your custom message.
}'
		),
		'wt_sc_alter_myaccount_no_used_coupons_msg' => array(
			'title' => __('Message when no used coupons available to show in my account coupons page.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the no used coupons available message, use this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_myaccount_no_used_coupons_msg", "wt_sc_change_no_used_coupon_msg" );
function wt_sc_change_no_used_coupon_msg( $msg ) {
	
	return __( "No used coupons found.", "wt-smart-coupons-for-woocommerce" ); // Add your custom message.
}'
		),
		'wt_sc_alter_myaccount_no_expired_coupons_msg' => array(
			'title' => __('Message when no expired coupons available to show in my account coupons page.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('To change the no expired coupons available message, use this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_myaccount_no_expired_coupons_msg", "wt_sc_change_no_expired_coupon_msg" );
function wt_sc_change_no_expired_coupon_msg( $msg ) {
	
	return __( "No expired coupons found.", "wt-smart-coupons-for-woocommerce" ); // Add your custom message.
}'
		),
		'wt_sc_alter_available_coupons_sort_order' => array(
			'title' => __('Change the default sort order for my account coupons.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default the sort order is `Latest last`. You can use this filter to alter the sort order.', 'wt-smart-coupons-for-woocommerce') . '<br />' .
							__('Applicable values: ', 'wt-smart-coupons-for-woocommerce') . 'created_date:desc, created_date:asc, amount:desc, amount:asc',
			'example' => '
add_filter( "wt_sc_alter_available_coupons_sort_order", "wt_sc_change_my_coupons_default_order_latest_first" );
function wt_sc_change_my_coupons_default_order_latest_first( $default_order ) {
	
	return "created_date:desc";
}'
		),
		'wt_sc_my_account_available_coupons_per_page' => array(
			'title' => __('Change my account coupons display count per page.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default maximum 20 coupons will display in the my account page. You can change the count by using this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_my_account_available_coupons_per_page", "wt_sc_increase_my_account_coupons_count" );
function wt_sc_increase_my_account_coupons_count( $count ) {

	return 50; // Increase the count to 50
}'
		),
		'wt_sc_my_account_expired_coupons_per_page' => array(
			'title' => __('Change my account expired coupons display count.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default maximum 50 expired coupons will display in the my account page. You can change the count by using this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_my_account_expired_coupons_per_page", "wt_sc_change_my_account_expired_coupons_count" );
function wt_sc_change_my_account_expired_coupons_count( $count ) {

	return 100; // Change the count to 100
}'
		),
		'wt_smart_coupon_before_my_account_coupons' => array(
			'title' => __('Add heading before My coupons in my account.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('You can add a custom heading before my coupons in my account.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_action( "wt_smart_coupon_before_my_account_coupons", "wt_sc_add_heading_before_my_account_coupons" );
function wt_sc_add_heading_before_my_account_coupons(){

	echo "<p>".__( "Click on Available Coupons to apply", "wt-smart-coupons-for-woocommerce" )."</p>";
}'
		),
	), 

	/**
	 *  Checkout page
	 */
	'checkout' => array(
		'wt_sc_checkout_available_coupons_per_page' => array(
			'title' => __('Change checkout coupons display count.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default maximum 20 coupons will display in the checkout page. You can change the count by using this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_checkout_available_coupons_per_page", "wt_sc_increase_checkout_coupons_count" );
function wt_sc_increase_checkout_coupons_count( $count ) {

	return 50; // Increase the count to 50
}'
		),
		'wt_smart_coupon_before_checkout_coupons' => array(
			'title' => __('Add a heading before coupons on the checkout page.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('You can add a custom heading before coupons on the checkout page.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_action( "wt_smart_coupon_before_checkout_coupons", "wt_sc_add_heading_before_checkout_coupon" );
function wt_sc_add_heading_before_checkout_coupon(){

	echo "<p>".__( "Click coupons to apply", "wt-smart-coupons-for-woocommerce" )."</p>";
}'
		),
	),


	/**
	 *  Others
	 */
	'others'=>array(
		'wt_sc_cart_available_coupons_per_page' => array(
			'title' => __('Change cart coupons display count.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default maximum 20 coupons will display in the cart page. You can change the count by using this filter.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_cart_available_coupons_per_page", "wt_sc_increase_cart_coupons_count" );
function wt_sc_increase_cart_coupons_count( $count ) {

	return 50; // Increase the count to 50
}'
		),
		'wt_smartcoupon_max_auto_coupons_limit' => array(
			'title' => __('Change auto coupon apply count.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default, a maximum of 5 coupons will be applied automatically. You can change the count by using this filter. Note: Setting a high number may affect the performance of the website.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_smartcoupon_max_auto_coupons_limit", "wt_sc_increase_auto_coupons_limit" );
function wt_sc_increase_auto_coupons_limit( $count ) {

	return 10; // Increase the count to 10
}'
		),
		'wt_sc_enable_pagination_in_user_available_coupons' => array(
			'title' => __('Remove pagination after available coupon.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default, a next and prev button will be available after the available coupons. The count of available coupons will be basically 20, but it can be changed with the help of hooks.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_enable_pagination_in_user_available_coupons", "__return_false" );'
		),
		'wt_sc_alter_order_detail_giveaway_info_label' => array(
			'title' => __('Change giveaway product label in the order details.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default, "Free gift" is the label of the giveaway product in the order details, which can be changed by this hook.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_order_detail_giveaway_info_label", "wt_sc_change_order_detail_giveaway_product_label", 10, 4 );

function wt_sc_change_order_detail_giveaway_product_label( $label, $order_item, $order_item_id, $order ){
	return __("Gift Product:", "wt-smart-coupons-for-woocommerce");
}'
		),
		'wt_sc_alter_giveaway_cart_summary_label' => array(
			'title' => __('Change giveaway product label in the cart summary.', 'wt-smart-coupons-for-woocommerce'),
			'description' => __('By default, "Free gift" is the label of the giveaway product in the cart summary, which can be changed by this hook.', 'wt-smart-coupons-for-woocommerce'),
			'example' => '
add_filter( "wt_sc_alter_giveaway_cart_summary_label", "wt_sc_change_giveaway_cart_summary_label", 10, 2 );

function wt_sc_change_giveaway_cart_summary_label( $label, $cart_item ){
	return __("Gift Product:", "wt-smart-coupons-for-woocommerce");
}'
		),
	),
);