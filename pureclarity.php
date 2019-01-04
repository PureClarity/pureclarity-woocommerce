<?php
/**
* Plugin Name: 	PureClarity for WooCommerce
* Description: 	The PureClarity ecommerce personalization plugin for WooCommerce is highly effective and easy to integrate.  Key features include the Advanced AI Recommendation Engine, Personalized Campaigns, Audience Segmentation, Personalization within Search and Email, and an in-depth analytics platform.
* Plugin URI: 	https://www.pureclarity.com
* Version:		1.0.4
* Author:		PureClarity
* Author URI:	https://www.pureclarity.com
**/

// Abort if called directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set version and path constants
define( 'PURECLARITY_VERSION', '1.0.4' );
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