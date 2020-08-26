<?php
/**
 * PureClarity_Signup class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Feed\Feed;

/**
 * Handles Feed Running
 */
class PureClarity_Feeds {

	/**
	 * Runs a chosen data feed
	 *
	 * @throws RuntimeException When an error occurs.
	 */
	public function feed_progress_action() {

		check_ajax_referer( 'pureclarity_feed_progress', 'security' );

		$feed_status = new PureClarity_Feed_Status();

		$status = array(
			Feed::FEED_TYPE_PRODUCT  => $feed_status->get_feed_status( Feed::FEED_TYPE_PRODUCT ),
			Feed::FEED_TYPE_CATEGORY => $feed_status->get_feed_status( Feed::FEED_TYPE_CATEGORY ),
			Feed::FEED_TYPE_USER     => $feed_status->get_feed_status( Feed::FEED_TYPE_USER ),
			Feed::FEED_TYPE_BRAND    => $feed_status->get_feed_status( Feed::FEED_TYPE_BRAND ),
			Feed::FEED_TYPE_ORDER    => $feed_status->get_feed_status( Feed::FEED_TYPE_ORDER ),
			'in_progress'            => $feed_status->get_are_feeds_in_progress(
				array(
					Feed::FEED_TYPE_PRODUCT,
					Feed::FEED_TYPE_CATEGORY,
					Feed::FEED_TYPE_USER,
					Feed::FEED_TYPE_BRAND,
					Feed::FEED_TYPE_ORDER,
				)
			),
		);

		wp_send_json( $status );
	}

	/**
	 * Runs the chosen data feeds
	 *
	 * @throws RuntimeException When an error occurs.
	 */
	public function request_feeds_action() {
		$error = false;
		try {
			check_ajax_referer( 'pureclarity_request_feeds', 'security' );
			$feed_types = array();

			if ( isset( $_POST['product'] ) ) {
				$feed_types[] = Feed::FEED_TYPE_PRODUCT;
			}

			if ( isset( $_POST['product'] ) ) {
				$feed_types[] = Feed::FEED_TYPE_CATEGORY;
			}

			if ( isset( $_POST['product'] ) ) {
				$feed_types[] = Feed::FEED_TYPE_USER;
			}

			if ( isset( $_POST['product'] ) ) {
				$feed_types[] = Feed::FEED_TYPE_BRAND;
			}

			if ( isset( $_POST['product'] ) ) {
				$feed_types[] = Feed::FEED_TYPE_ORDER;
			}

			if ( empty( $feed_types ) ) {
				$error = __( 'Please choose one or more feeds to send to PureClarity', 'pureclarity' );
			} else {
				$state_manager = new PureClarity_State_Manager();
				$state_manager->set_state_value( 'requested_feeds', wp_json_encode( $feed_types ) );

				foreach ( $feed_types as $feed ) {
					$state_manager->set_state_value( $feed . '_feed_error', '' );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error trying to request feeds: ' . $exception->getMessage() );
			$error = __( 'PureClarity: An error trying to request feeds. See error logs for more information.', 'pureclarity' );
		}
		wp_send_json( array( 'error' => $error ) );
	}

}
