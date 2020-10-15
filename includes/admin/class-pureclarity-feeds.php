<?php
/**
 * PureClarity_Feeds class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Feed\Feed;

/**
 * Handles Feed run request and progress checking.
 */
class PureClarity_Feeds {

	/**
	 * State manager class - interacts with the pureclarity_state table
	 *
	 * @var PureClarity_State_Manager
	 */
	private $state_manager;

	/**
	 * State manager class - deals with information around feed statuses
	 *
	 * @var PureClarity_Feed_Status $feed_status
	 */
	private $feed_status;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_State_Manager $state_manager - PureClarity state manager class.
	 * @param PureClarity_Feed_Status   $feed_status - PureClarity feed status class.
	 */
	public function __construct(
		$state_manager,
		$feed_status
	) {
		$this->state_manager = $state_manager;
		$this->feed_status   = $feed_status;
	}

	/**
	 * Runs a chosen data feed
	 *
	 * @throws RuntimeException When an error occurs.
	 */
	public function feed_progress_action() {

		check_ajax_referer( 'pureclarity_feed_progress', 'security' );

		$status = array(
			Feed::FEED_TYPE_PRODUCT  => $this->feed_status->get_feed_status( Feed::FEED_TYPE_PRODUCT ),
			Feed::FEED_TYPE_CATEGORY => $this->feed_status->get_feed_status( Feed::FEED_TYPE_CATEGORY ),
			Feed::FEED_TYPE_USER     => $this->feed_status->get_feed_status( Feed::FEED_TYPE_USER ),
			Feed::FEED_TYPE_ORDER    => $this->feed_status->get_feed_status( Feed::FEED_TYPE_ORDER ),
			'in_progress'            => $this->feed_status->get_are_feeds_in_progress(
				array(
					Feed::FEED_TYPE_PRODUCT,
					Feed::FEED_TYPE_CATEGORY,
					Feed::FEED_TYPE_USER,
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

			if ( isset( $_POST[ Feed::FEED_TYPE_PRODUCT ] ) && 'true' === $_POST[ Feed::FEED_TYPE_PRODUCT ] ) {
				$feed_types[] = Feed::FEED_TYPE_PRODUCT;
			}

			if ( isset( $_POST[ Feed::FEED_TYPE_CATEGORY ] ) && 'true' === $_POST[ Feed::FEED_TYPE_CATEGORY ] ) {
				$feed_types[] = Feed::FEED_TYPE_CATEGORY;
			}

			if ( isset( $_POST[ Feed::FEED_TYPE_USER ] ) && 'true' === $_POST[ Feed::FEED_TYPE_USER ] ) {
				$feed_types[] = Feed::FEED_TYPE_USER;
			}

			if ( isset( $_POST[ Feed::FEED_TYPE_ORDER ] ) && 'true' === $_POST[ Feed::FEED_TYPE_ORDER ] ) {
				$feed_types[] = Feed::FEED_TYPE_ORDER;
			}

			if ( empty( $feed_types ) ) {
				$error = __( 'Please choose one or more feeds to send to PureClarity', 'pureclarity' );
			} else {
				$this->state_manager->set_state_value( 'requested_feeds', wp_json_encode( $feed_types ) );
				$this->state_manager->set_state_value( 'requested_feeds_running', '0' );

				foreach ( $feed_types as $feed ) {
					$this->state_manager->set_state_value( $feed . '_feed_error', '' );
					$this->state_manager->set_state_value( $feed . '_feed_progress', '0' );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error trying to request feeds: ' . $exception->getMessage() );
			$error = __( 'PureClarity: An error trying to request feeds. See error logs for more information.', 'pureclarity' );
		}
		wp_send_json( array( 'error' => $error ) );
	}

}
