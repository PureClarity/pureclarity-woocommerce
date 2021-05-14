<?php
/**
 * PureClarity_Order class
 *
 * @package PureClarity for WooCommerce
 * @since 2.1.1
 */

/**
 * Handles order JS code on order-received page
 */
class PureClarity_Order {

	/**
	 * Generates the order data for the given order ID
	 *
	 * @param int $order_id - the order id to get info for.
	 * @return array|null
	 */
	public function get_order_info( $order_id ) {
		$order_info = null;

		try {
			$order = wc_get_order( $order_id );
			if ( ! empty( $order ) ) {
				$customer = new WC_Customer( $order->get_user_id() );
				if ( ! empty( $customer ) ) {
					$order_info = array(
						'orderid'    => $order->get_id(),
						'firstname'  => $order->get_user_id() ? $customer->get_first_name() : $order->get_billing_first_name(),
						'lastname'   => $order->get_user_id() ? $customer->get_last_name() : $order->get_billing_last_name(),
						'userid'     => $order->get_user_id() ? $order->get_user_id() : '',
						'ordertotal' => $order->get_total(),
						'email'      => $order->get_user_id() ? $customer->get_email() : $order->get_billing_email(),
					);

					$order_info['items'] = array();
					foreach ( $order->get_items() as $item_id => $item ) {
						$product = $item->get_product();
						if ( is_object( $product ) ) {
							$order_info['items'][] = array(
								'id'        => $item->get_product_id(),
								'qty'       => ceil( $item['qty'] ),
								'unitprice' => wc_format_decimal( $order->get_item_total( $item, false, false ) ),
							);
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			error_log( 'PureClarity: An error occurred trying to output order info: ' . $e->getMessage() );
		}

		return $order_info;
	}

	/**
	 * Generates the order data for the given order ID
	 *
	 * @param int $order_id - the order id to get info for.
	 */
	public function output_order_event_input( $order_id ) {
		$data        = $this->get_order_info( $order_id );
		$data_string = wp_json_encode( $data );
		$output      = '<input type="hidden" id="pc_order_info" value=" ' . htmlentities( $data_string, ENT_QUOTES, 'utf-8' ) . '">';

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
