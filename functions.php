<?php
/**
 * Custom Functions
 *
 * @package PureClarity for WooCommerce
 * @since 2.3.0
 */

// Ensure path constant is set.
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	exit();
}


if ( false === function_exists( 'pureclarity_output_order_input' ) ) {
	/**
	 * Outputs a hidden input tag with order information in. It will be picked up by the PureClarity JS and sent to PureClarity.
	 *
	 * @param int $order_id Order ID - The ID of the order that needs to be sent to PureClarity.
	 */
	function pureclarity_output_order_input( $order_id ) {
		require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-class-loader.php';
		$loader = new PureClarity_Class_Loader();
		$pc_order = $loader->get_order();
		$pc_order->output_order_event_input( $order_id );
	}
}

if ( false === function_exists( 'pureclarity_db_check' ) ) {
	/**
	 * Checks to see if the database needs upgrading, if so, runs the upgrade
	 */
	function pureclarity_db_check() {
		$pc_db_version = (int) get_site_option( 'pureclarity_db_version' );
		if ( get_site_option( 'pureclarity_db_version' ) !== PURECLARITY_DB_VERSION ) {
			require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-class-loader.php';
			$loader = new PureClarity_Class_Loader();
			$pc_db = $loader->get_database();
			$pc_db->update_db( $pc_db_version );
		}
	}
	add_action( 'plugins_loaded', 'pureclarity_db_check' );
}

