<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE for license details.
 *
 * PureClarity_Feed_Status class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Feed status checker model - works out feed status from state data.
 */
class PureClarity_Feed_Status {

	/**
	 * Class to handle interaction with the pureclarity_state table
	 *
	 * @var PureClarity_State_Manager $state_manager
	 */
	private $state_manager;

	/**
	 * Object cache of feed status data.
	 *
	 * @var mixed[] $feed_status_data
	 */
	protected $feed_status_data;

	/**
	 * Object cache of feed errors.
	 *
	 * @var string[] $feed_errors
	 */
	protected $feed_errors;

	/**
	 * Object cache of feed progress data.
	 *
	 * @var mixed[] $progress_data
	 */
	protected $progress_data;

	/**
	 * Object cache of requested feed data.
	 *
	 * @var mixed[] $requested_feed_data
	 */
	protected $requested_feed_data;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_State_Manager $state_manager - PureClarity Settings class.
	 */
	public function __construct(
		$state_manager
	) {
		$this->state_manager = $state_manager;
	}

	/**
	 * Returns whether any of the feed types provided are currently in progress
	 *
	 * @param string[] $types - array of feed types to check.
	 *
	 * @return bool
	 */
	public function get_are_feeds_in_progress( array $types ) {
		$in_progress = false;
		foreach ( $types as $type ) {
			$status = $this->get_feed_status( $type );
			if ( true === $status['running'] ) {
				$in_progress = true;
			}
		}

		return $in_progress;
	}

	/**
	 * Returns the status of the given feed
	 *
	 * @param string $type - Type of feed to check the status of.
	 *
	 * @return mixed[]
	 */
	public function get_feed_status( $type ) {
		if ( ! isset( $this->feed_status_data[ $type ] ) ) {

			$status = array(
				'enabled' => true,
				'error'   => false,
				'running' => false,
				'class'   => 'pc-feed-not-sent',
				'label'   => __( 'Not Sent', 'pureclarity' ),
			);

			if ( false === $status['enabled'] ) {
				$this->feed_status_data[ $type ] = $status;
				return $this->feed_status_data[ $type ];
			}

			if ( $this->get_feed_error( $type ) ) {
				$status['error']                 = true;
				$status['label']                 = __( 'Error, please see logs for more information', 'pureclarity' );
				$status['class']                 = 'pc-feed-error';
				$this->feed_status_data[ $type ] = $status;

				return $this->feed_status_data[ $type ];
			}

			// check if it's been requested.
			$requested = $this->has_feed_been_requested( $type );

			if ( $requested ) {
				$status['running'] = true;
				$status['label']   = __( 'Waiting for feed run to start', 'pureclarity' );
				$status['class']   = 'pc-feed-waiting';
			}

			// check if it's been requested.
			$requested = $this->is_feed_waiting( $type );
			if ( $requested ) {
				$status['running'] = true;
				$status['label']   = __( 'Waiting for other feeds to finish', 'pureclarity' );
				$status['class']   = 'pc-feed-waiting';
			}

			if ( $status['running'] ) {
				// check if it's in progress.
				$progress = $this->feed_progress( $type );
				if ( ! empty( $progress ) ) {
					$status['running'] = true;
					$status['class']   = 'pc-feed-in-progress';
					$status['label']   = sprintf(
						/* translators: %d is replaced with progress number */
						__( 'In progress: %d', 'pureclarity' ),
						$progress
					) . '%';
				}
			}

			if ( true !== $status['running'] ) {
				// check it's last run date.
				$last_feed_date = $this->get_pureclarity_state( $type . '_feed_last_run' );

				if ( $last_feed_date ) {
					$status['label'] = sprintf(
						/* translators: %s is replaced with feeds last run date */
						__( 'Last sent: %s', 'pureclarity' ),
						date_i18n( 'd F, H:i:s', $last_feed_date )
					);
					$status['class'] = 'pc-feed-complete';
				}
			}

			$this->feed_status_data[ $type ] = $status;
		}

		return $this->feed_status_data[ $type ];
	}

	/**
	 * Checks the scheduled feed data and returns whether the given feed type is in it's data
	 *
	 * @param string $feed_type - Feed type to check.
	 *
	 * @return bool
	 */
	protected function has_feed_been_requested( $feed_type ) {
		$requested     = false;
		$schedule_data = $this->get_scheduled_feed_data();

		if ( ! empty( $schedule_data ) ) {
			$requested = in_array( $feed_type, $schedule_data, true );
		}

		return $requested;
	}

	/**
	 * Checks for & returns the <feed_type>_feed_error state row
	 *
	 * @param string $feed_type - Feed type to check.
	 *
	 * @return bool
	 */
	protected function get_feed_error( $feed_type ) {
		if ( null === $this->feed_errors || ! isset( $this->feed_errors[ $feed_type ] ) ) {
			$this->feed_errors[ $feed_type ] = $this->get_pureclarity_state( $feed_type . '_feed_error' );
		}

		return $this->feed_errors[ $feed_type ];
	}

	/**
	 * Checks for the running_feeds state row and returns whether the given feed type is in it's data
	 *
	 * @param string $feed_type - Feed type to check.
	 *
	 * @return bool
	 */
	protected function is_feed_waiting( $feed_type ) {
		$running = $this->get_pureclarity_state( 'running_feed' );

		return ! empty( $running ) && $running !== $feed_type;
	}

	/**
	 * Gets progress data from the state table
	 *
	 * @param string $feed_type - Feed type to get progress of.
	 *
	 * @return string
	 */
	protected function feed_progress( $feed_type ) {
		if ( ! isset( $this->progress_data[ $feed_type ] ) ) {
			$progress                          = $this->get_pureclarity_state( $feed_type . '_feed_progress' );
			$this->progress_data[ $feed_type ] = empty( $progress ) ? '0' : $progress;
		}

		return $this->progress_data[ $feed_type ];
	}

	/**
	 * Gets schedule data from the state table
	 *
	 * @return string[]
	 */
	protected function get_scheduled_feed_data() {
		if ( null === $this->requested_feed_data ) {
			$requested_feeds = $this->get_pureclarity_state( 'requested_feeds' );

			if ( ! empty( $requested_feeds ) ) {
				$this->requested_feed_data = json_decode( $requested_feeds, true );
			} else {
				$this->requested_feed_data = array();
			}
		}

		return $this->requested_feed_data;
	}

	/**
	 * Gets the data from the state table
	 *
	 * @param string $key - Key to get from the state table.
	 *
	 * @return string
	 */
	protected function get_pureclarity_state( $key ) {
		return $this->state_manager->get_state_value( $key );
	}

	/**
	 * Saves data to the state table
	 *
	 * @param string $key - Key to set value against in the state table.
	 * @param string $value - Value to set against the given key.
	 */
	protected function update_pureclarity_state( $key, $value ) {
		$this->state_manager->set_state_value( $key, $value );
	}
}
