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

/**
 * Outputs a hidden input tag with order information in. It will be picked up by the PureClarity JS and sent to PureClarity.
 *
 * @param int $order_id Order ID - The ID of the order that needs to be sent to PureClarity.
 */
function pureclarity_output_order_input( $order_id ) {
	$pc_order = new PureClarity_Order();
	$pc_order->output_order_event_input( $order_id );
}
