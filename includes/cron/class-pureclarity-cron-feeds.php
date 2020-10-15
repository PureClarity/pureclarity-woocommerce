<?php
/**
 * PureClarity_Cron_Feeds class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Feed\Feed;

/**
 * Handles Requested Feed related cron code
 */
class PureClarity_Cron_Feeds {

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Feed class
	 *
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

	/**
	 * PureClarity State Manager class
	 *
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
	 * Runs feeds that have been requested via admin.
	 */
	public function run_requested_feeds() {

		$feeds   = $this->state_manager->get_state_value( 'requested_feeds' );
		$running = $this->state_manager->get_state_value( 'requested_feeds_running' );

		if ( empty( $running ) && ! empty( $feeds ) ) {
			try {
				$requested_feeds = json_decode( $feeds );
				$this->state_manager->set_state_value( 'requested_feeds_running', '1' );

				foreach ( $requested_feeds as $type ) {
					$this->feed->run_feed( $type );
				}

				$this->set_banner_display();
			} catch ( \Exception $exception ) {
				error_log( "PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
				wp_send_json( array( 'error' => "An error occurred generating the {$type} feed. See error logs for more information." ) );
			}

			$this->state_manager->set_state_value( 'requested_feeds_running', '0' );
			$this->state_manager->set_state_value( 'requested_feeds', '' );
		}
	}

	/**
	 * Runs the nightly feed.
	 */
	public function run_nightly_feeds() {

		$running         = $this->state_manager->get_state_value( 'nightly_feeds_running' );
		$nightly_enabled = $this->settings->is_nightly_feed_enabled();

		if ( $nightly_enabled && empty( $running ) ) {
			try {
				$requested_feeds = array(
					Feed::FEED_TYPE_PRODUCT,
					Feed::FEED_TYPE_CATEGORY,
					Feed::FEED_TYPE_USER,
				);

				$this->state_manager->set_state_value( 'nightly_feeds_running', '1' );

				foreach ( $requested_feeds as $type ) {
					$this->feed->run_feed( $type );
				}

				$this->set_banner_display();
			} catch ( \Exception $exception ) {
				error_log( "PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
				wp_send_json( array( 'error' => "An error occurred generating the {$type} feed. See error logs for more information." ) );
			}

			$this->state_manager->set_state_value( 'nightly_feeds_running', '0' );
		}
	}

	/**
	 * Sorts out the state for the banner display.
	 */
	private function set_banner_display() {
		$show_banner        = $this->state_manager->get_state_value( 'show_welcome_banner' );
		$show_manual_banner = $this->state_manager->get_state_value( 'show_manual_welcome_banner' );
		if ( $show_banner || $show_manual_banner ) {
			$this->state_manager->set_state_value( 'show_welcome_banner', '0' );
			$this->state_manager->set_state_value( 'show_manual_welcome_banner', '0' );
			$this->state_manager->set_state_value( 'show_getting_started_banner', time() + 86400 );
		}
	}
}
