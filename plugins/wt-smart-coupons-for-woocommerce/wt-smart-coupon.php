<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           Wt_Smart_Coupon
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Coupons For WooCommerce Coupons 
 * Plugin URI:        
 * Description:       Smart Coupons For WooCommerce Coupons plugin adds advanced coupon features to your store to strengthen your marketing efforts and boost sales.
 * Version:           1.7.1
 * Author:            WebToffee
 * Author URI:        https://www.webtoffee.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wt-smart-coupons-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to:   8.8
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
*   @since 1.3.5
*   Check pro version is there
*/
include_once(ABSPATH.'wp-admin/includes/plugin.php');
if(is_plugin_active('wt-smart-coupon-pro/wt-smart-coupon-pro.php'))
{
    return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

if (!defined('WEBTOFFEE_SMARTCOUPON_VERSION')) {
    define('WEBTOFFEE_SMARTCOUPON_VERSION', '1.7.1');
}

if (!defined('WT_SMARTCOUPON_FILE_NAME')) {
    define('WT_SMARTCOUPON_FILE_NAME', __FILE__);
}

if (!defined('WT_SMARTCOUPON_BASE_NAME')) {
    define('WT_SMARTCOUPON_BASE_NAME', plugin_basename(__FILE__));
}

if (!defined('WT_SMARTCOUPON_MAIN_PATH')) {
    define('WT_SMARTCOUPON_MAIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('WT_SMARTCOUPON_MAIN_URL')) {
    define('WT_SMARTCOUPON_MAIN_URL', plugin_dir_url(__FILE__));
}


if (!defined('WT_SMARTCOUPON_INSTALLED_VERSION')) { 
    define('WT_SMARTCOUPON_INSTALLED_VERSION', 'BASIC');
}

/**
 *  @since 1.3.9
 *  Changelog in plugins page
 */
function wt_sc_update_message( $data, $response )
{
    if ( isset( $data['upgrade_notice'] ) )
    {
        add_action('admin_print_footer_scripts', 'wt_sc_plugin_screen_update_notice_js');
        $msg = str_replace(array( '<p>', '</p>' ), array( '<div>', '</div>' ), $data['upgrade_notice']);
        echo '<style type="text/css">
        #wt-smart-coupons-for-woocommerce-update .update-message p:last-child{ display:none;}     
        #wt-smart-coupons-for-woocommerce-update ul{ list-style:disc; margin-left:30px;}
        .wt_sc_update_message{ padding-left:30px;}
        </style>
        <div class="update-message wt_sc_update_message">' . wp_kses_post( wpautop( $msg ) ) . '</div>';
    }
}

/**
 *  @since 1.3.9
 *  Javascript code for changelog in plugins page
 */
function wt_sc_plugin_screen_update_notice_js() 
{   
    global $pagenow;
    if('plugins.php' != $pagenow)
    {
        return;
    }
    ?>
    <script>
        ( function( $ ){
            var update_dv=$('#wt-smart-coupons-for-woocommerce-update');
            update_dv.find('.wt_sc_update_message').next('p').remove();
            update_dv.find('a.update-link:eq(0)').on('click', function(){
                $('.wt_sc_update_message').remove();
            });
        })( jQuery );
    </script>
    <?php
}
add_action('in_plugin_update_message-wt-smart-coupons-for-woocommerce/wt-smart-coupon.php', 'wt_sc_update_message', 10, 2 );



/** @since 1.3.5 */
if (!defined('WT_SC_PLUGIN_NAME'))
{
    define('WT_SC_PLUGIN_NAME', 'wt-smart-coupon-for-woo');
    define('WT_SC_PLUGIN_ID', 'wt_smart_coupon_for_woo');
    define('WT_SC_SETTINGS_FIELD', WT_SC_PLUGIN_NAME); /* option name to store settings */
}

/**
 * Check if WooCommerce is active
 */
if(!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !array_key_exists( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) ) ) 
{ 
	return;
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt-smart-coupon-activator.php
 */
function activate_wt_smart_coupon() {

    if (!class_exists( 'WooCommerce' )) {
        deactivate_plugins( WT_SMARTCOUPON_BASE_NAME );
		wp_die( __( "WooCommerce is required for this plugin to work properly. Please activate WooCommerce.", 'wt-smart-coupons-for-woocommerce' ), "", array( 'back_link' => 1 ) );
	}
    require_once plugin_dir_path(__FILE__) . 'includes/class-wt-smart-coupon-activator.php';
    Wt_Smart_Coupon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt-smart-coupon-deactivator.php
 */
function deactivate_wt_smart_coupon() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wt-smart-coupon-deactivator.php';
    Wt_Smart_Coupon_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wt_smart_coupon');
register_deactivation_hook(__FILE__, 'deactivate_wt_smart_coupon');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */


require_once plugin_dir_path(__FILE__) . 'includes/class-wt-smart-coupon.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-smartcoupon-uninstall-feedback.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wt_smart_coupon() 
{
    $plugin = Wt_Smart_Coupon::get_instance();
    $plugin->run();
}

include 'admin/class-wt-duplicate-coupon.php';
include 'admin/exclude-product/class-wt-exclude-product-for-coupon.php';
include 'admin/coupon-start-date/class-wt-smart-coupon-start-date.php'; 

include 'public/class-myaccount-smart-coupon.php';


/**
 *  Declare compatibility with custom order tables for WooCommerce.
 * 
 *  @since 1.4.5
 *  
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

run_wt_smart_coupon();