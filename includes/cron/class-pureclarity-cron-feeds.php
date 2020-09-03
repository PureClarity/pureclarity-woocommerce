<?php
/**
 * PureClarity_Cron class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

use PureClarity\Api\Feed\Feed;

/**
 * Handles Requested Feed related cron code
 */
class PureClarity_Cron_Feeds {

	/**
	 * PureClarity Settings class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Feed class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

	/**
	 * PureClarity State Manager class
	 *
	 * @since 2.0.0
	 * @var PureClarity_State_Manager $state_manager
	 */
	private $state_manager;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings      $settings - PureClarity Settings class.
	 * @param PureClarity_Feed          $feed - PureClarity Feed class.
	 * @param PureClarity_State_Manager $state_manager - PureClarity State Manager class.
	 */
	public function __construct(
		$settings,
		$feed,
		$state_manager
	) {
		$this->settings      = $settings;
		$this->feed          = $feed;
		$this->state_manager = $state_manager;
	}

	/**
	 * Runs outstanding delta tasks
	 */
	public function run_requested_feeds() {

		$feeds   = $this->state_manager->get_state_value( 'requested_feeds' );
		$running = $this->state_manager->get_state_value( 'requested_feeds_running' );

		if ( empty( $running ) && ! empty( $feeds ) ) {
			try {
				$requested_feeds = json_decode( $feeds );
				$this->state_manager->set_state_value( 'requested_feeds_running', '1' );

				foreach ( $requested_feeds as $type ) {
					$this->run_feed( $type );
				}
			} catch ( \Exception $exception ) {
				error_log( "PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
				wp_send_json( array( 'error' => "An error occurred generating the {$type} feed. See error logs for more information." ) );
			}

			$this->state_manager->set_state_value( 'requested_feeds_running', '0' );
			$this->state_manager->set_state_value( 'requested_feeds', '' );
		}
	}

	/**
	 * Runs outstanding delta tasks
	 */
	public function run_nightly_feeds() {

		$running         = $this->state_manager->get_state_value( 'nightly_feeds_running' );
		$nightly_enabled = $this->settings->is_nightly_feed_enabled();

		if ( $nightly_enabled && empty( $running ) ) {
			try {
				$requested_feeds = array(
					Feed::FEED_TYPE_PRODUCT,
					Feed::FEED_TYPE_BRAND,
					Feed::FEED_TYPE_CATEGORY,
					Feed::FEED_TYPE_USER,
				);

				$this->state_manager->set_state_value( 'nightly_feeds_running', '1' );

				foreach ( $requested_feeds as $type ) {
					$this->run_feed( $type );
				}
			} catch ( \Exception $exception ) {
				error_log( "PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
				wp_send_json( array( 'error' => "An error occurred generating the {$type} feed. See error logs for more information." ) );
			}

			$this->state_manager->set_state_value( 'nightly_feeds_running', '0' );
		}
	}

	/**
	 * Runs an individual feed.
	 *
	 * @param string $type - Type of feed to run.
	 */
	private function run_feed( $type ) {
		try {
			$total_pages_count = $this->feed->get_total_pages( $type );
			for ( $current_page = 1; $current_page <= $total_pages_count; $current_page++ ) {
				if ( 1 === $current_page && $total_pages_count > 0 ) {
					$this->feed->start_feed( $type );
				}

				if ( $current_page <= $total_pages_count ) {
					$data = $this->feed->build_items( $type, $current_page );
					$this->feed->send_data( $type, $data );
				}

				$is_finished = ( $current_page >= $total_pages_count );

				if ( $is_finished && $total_pages_count > 0 ) {
					$this->feed->end_feed( $type );
					$this->settings->set_feed_type_sent( $type );
				}

				$this->state_manager->set_state_value( $type . '_feed_progress', round( ( $total_pages_count / $current_page * 100 ) ) );
			}
			$this->state_manager->set_state_value( $type . '_feed_last_run', time() );
		} catch ( \Exception $e ) {
			$this->state_manager->set_state_value( $type . '_feed_error', $e->getMessage() );
		}
	}
}
