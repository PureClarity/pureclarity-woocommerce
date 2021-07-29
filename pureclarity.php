<?php
/**
 * PureClarity for WooCommerce
 *
 * @package PureClarity for WooCommerce
 *
 * Plugin Name:  PureClarity for WooCommerce
 * Description:  Use PureClarity's wide range of ecommerce personalisation features to create engaging online shopping experiences for your customers. Drive revenue, average order value, conversion rate and customer loyalty.
 * Plugin URI:   https://www.pureclarity.com
 * Version:      3.3.1
 * Author:       PureClarity
 * Author URI:   https://www.pureclarity.com/?utm_source=marketplace&utm_medium=woocommerce&utm_campaign=aboutpureclarity
 * Text Domain:  pureclarity
 * WC tested up to: 5.2.2
 **/

// Abort if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set version and path constants.
define( 'PURECLARITY_VERSION', '3.3.1' );
define( 'PURECLARITY_DB_VERSION', 1 );

if ( ! defined( 'PURECLARITY_PATH' ) ) {
	define( 'PURECLARITY_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PURECLARITY_INCLUDES_PATH' ) ) {
	define( 'PURECLARITY_INCLUDES_PATH', PURECLARITY_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'PURECLARITY_BASE_URL' ) ) {
	define( 'PURECLARITY_BASE_URL', plugin_dir_url( __FILE__ ) );
}

// Ensure woocommerce is enabled.
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action(
		'admin_notices',
		function() {
			echo '<div class="error notice"><p>' . esc_html_e( 'The PureClarity plugin requires WooCommerce to be enabled.', 'pureclarity' ) . '</p></div>';
		}
	);
} else {
	require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-plugin.php';
	// include classes.
	require_once PURECLARITY_INCLUDES_PATH . 'php-sdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';
	require_once PURECLARITY_PATH . 'functions.php';
	$pureclarity = new PureClarity_Plugin();
}
