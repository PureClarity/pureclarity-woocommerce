<?php
/**
* Plugin Name: 	PureClarity for WooCommerce
* Description: 	Increase revenues by 26% in your WooCommerce store with AI-based real-time personalization. Integrates with PureClarity's multi-award winning ecommerce personalization software.
* Plugin URI: 	https://www.pureclarity.com
* Version:		2.0.1
* Author:		PureClarity
* Author URI:	https://www.pureclarity.com/?utm_source=marketplace&utm_medium=woocommerce&utm_campaign=aboutpureclarity
* Text Domain:  pureclarity
**/

// Abort if called directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set version and path constants
define( 'PURECLARITY_VERSION', '2.0.2' );
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	define( 'PURECLARITY_PATH', plugin_dir_path( __FILE__ ) );
}

// Ensure woocommerce is enabled
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! is_plugin_active('woocommerce/woocommerce.php') ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error notice"><p>' . __( 'The PureClarity plugin requires WooCommerce to be enabled.', 'pureclarity' ) . '</p></div>';
	} );
}
else {

	// include classes
	require_once PURECLARITY_PATH . 'class-includes.php';

	// Create static instance
	$pureclarity = PureClarity_Plugin::getInstance();

}