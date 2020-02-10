<?php
/**
 * PureClarity_Order class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles order JS code on order-received page
 */
class PureClarity_Order {

	/**
	 * Sets up dependencies and adds some init actions
	 */
	public function __construct() {
		add_action(
			'woocommerce_order_details_before_order_table',
			array(
				$this,
				'order_event',
			),
			10
		);
	}

	/**
	 * Takes the current order and generates JSON for the order tracking event
	 *
	 * @param WC_Order $order - the order being displayed on the page.
	 * @throws Exception - if customer is not found.
	 */
	public function order_event( $order ) {
		$output   = '';
		$customer = new WC_Customer( $order->get_user_id() );

		if ( ! empty( $order ) && ! empty( $customer ) ) {

			$transaction = array(
				'orderid'    => $order->get_id(),
				'firstname'  => $customer->get_first_name(),
				'lastname'   => $customer->get_last_name(),
				'userid'     => $order->get_user_id(),
				'ordertotal' => $order->get_total(),
			);

			if ( empty( $transaction['userid'] ) ) {
				// guest order, so add billing email.
				$transaction['firstname'] = $order->get_billing_first_name();
				$transaction['lastname']  = $order->get_billing_last_name();
				$transaction['email']     = $order->get_billing_email();
			}

			$order_items = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				if ( is_object( $product ) ) {
					$order_items[] = array(
						'id'        => $item->get_product_id(),
						'qty'       => $item['qty'],
						'unitprice' => wc_format_decimal( $order->get_item_total( $item, false, false ) ),
					);
				}
			}

			$data          = $transaction;
			$data['items'] = $order_items;
			$data_string   = wp_json_encode( $data );
			$output        = '<input type="hidden" id="pc_order_info" value=" ' . htmlentities( $data_string, ENT_QUOTES, 'utf-8' ) . '">';
		}

		echo wp_kses(
			$output,
			array(
				'input' => array(
					'type'  => array(),
					'id'    => array(),
					'value' => array(),
				),
			)
		);
	}
}
