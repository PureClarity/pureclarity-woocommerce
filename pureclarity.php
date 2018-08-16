<?php
/**
* Plugin Name: 	PureClarity for WooCommerce
* Description: 	With PureClarity ecommerce personalization is simple! Provide the most powerful site search, recommend highly relevant and personalized products and retarget your customers with personalized emails.
* Plugin URI: 	https://www.pureclarity.com
* Version:		1.0.2
* Author:		PureClarity
* Author URI:	https://www.pureclarity.com
**/

// Abort if called directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set version and path constants
define( 'PURECLARITY_VERSION', '1.0.0' );
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	define( 'PURECLARITY_PATH', plugin_dir_path( __FILE__ ) );
}

// Ensure woocommerce is enabled
include_once(ABSPATH.'wp-admin/includes/plugin.php');
if ( ! is_plugin_active('woocommerce/woocommerce.php') ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error notice"><p>' . esc_html__( 'The PureClarity plugin requires WooCommerce to be enabled.', 'pureclarity' ) . '</p></div>';
	} );
}
else {

	// include classes
	require_once PURECLARITY_PATH . 'class-includes.php';

	// Create static instance
	$pureclarity = PureClarity_Plugin::getInstance();

}