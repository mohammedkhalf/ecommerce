<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cowpay.me
 * @since             1.0.0
 * @package           WooCowpay
 *
 * @wordpress-plugin
 * Plugin Name:       Cowpay For WooCommerce
 * Plugin URI:        https://docs.cowpay.me/plugins/woocommerce/
 * Description:       Extends WooCommerce by Adding the Cowpay payment Gateway.
 * Version:           2.0.0
 * Author:            Cowpay, support@cowpay.me
 * Author URI:        https://cowpay.me/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woo-cowpay
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 5.8.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Define global constant variables for our plugin
 */
define( 'WOO_COWPAY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO_COWPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('WOO_COWPAY_VERSION', '2.0.0');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-cowpay-activator.php
 */
function activate_woo_cowpay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-cowpay-activator.php';
	WooCowpayActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-cowpay-deactivator.php
 */
function deactivate_woo_cowpay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-cowpay-deactivator.php';
	WooCowpayDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woo_cowpay');
register_deactivation_hook(__FILE__, 'deactivate_woo_cowpay');
add_action('wp_ajax_check_otp_response', "check_otp_response");
 function check_otp_response()
{
	$cc = new WC_Payment_Gateway_Cowpay_CC();

	echo json_encode($cc->check_otp_response());
	wp_die();
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woo-cowpay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
/**
 * public helper function that can check wordpress version and if woocommece is installed and it`s auto deactive anything is missing
 */
function requiremnts_check_list() {
	global $wp_version;
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );
	$require_wp = "3.5";

	if ( version_compare( $wp_version, $require_wp, "<" ) && is_plugin_active($plugin)) {
		deactivate_plugins( $plugin );
		wp_die( "<strong>".$plugin_data['Name']."</strong> ".__("requires",'woo-cowpay')." <strong>WordPress ".$require_wp."</strong> ".__("or higher, and has been deactivated! Please upgrade WordPress and try again.",'woo-cowpay')."<br /><br />".__("Back to the",'woo-cowpay')." WordPress <a href='".get_admin_url(null, 'plugins.php')."'>".__("Plugins page",'woo-cowpay')."</a>." );
	}elseif( is_plugin_active($plugin) && !is_plugin_active('woocommerce/woocommerce.php')) {
		deactivate_plugins( $plugin );
		wp_die( "<strong>".$plugin_data['Name']."</strong> ".__("requires",'woo-cowpay')." <strong>woocommece </strong> ".__("to be activated, and has been deactivated! Please upgrade WordPress and try again.",'woo-cowpay')."<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>".__("Plugins page","woo-cowpay")."</a>." );
	}
}
add_action( 'admin_init', 'requiremnts_check_list' );
function run_woo_cowpay()
{
	
	$plugin = new WooCowpay();
	requiremnts_check_list();
	$plugin->run();
}

/**
 * public helper function that render php views in cowpay plugin
 */
function woo_cowpay_view($name, array $args = array())
{
	$args = apply_filters('woo_cowpay_view_arguments', $args, $name);
	
	// define template file parameters as variables in the scope
	foreach ($args as $key => $val) {
		$$key = $val;
	}

	load_plugin_textdomain('woo-cowpay');

	$file = WOO_COWPAY_PLUGIN_DIR . 'views/' . $name . '.php';
	
	include($file);
} //? TODO: Where should we define this function.

run_woo_cowpay();