<?php
/**
 * PureClarity_Cron class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles cron scheduling
 */
class PureClarity_Cron {

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Delta Cron class
	 *
	 * @var PureClarity_Cron_Deltas $delta_cron
	 */
	private $delta_cron;

	/**
	 * PureClarity Delta Cron class
	 *
	 * @var PureClarity_Cron_Feeds $feeds_cron
	 */
	private $feeds_cron;

	/**
	 * PureClarity Signup Cron class
	 *
	 * @var PureClarity_Cron_Signup $signup_cron
	 */
	private $signup_cron;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings    $settings PureClarity Settings class.
	 * @param PureClarity_Cron_Deltas $delta_cron PureClarity Delta Cron class.
	 * @param PureClarity_Cron_Feeds  $feeds_cron PureClarity Feeds Cron class.
	 * @param PureClarity_Cron_Signup $signup_cron PureClarity Signup Cron class.
	 */
	public function __construct(
		$settings,
		$delta_cron,
		$feeds_cron,
		$signup_cron
	) {
		$this->settings    = $settings;
		$this->delta_cron  = $delta_cron;
		$this->feeds_cron  = $feeds_cron;
		$this->signup_cron = $signup_cron;
	}

	/**
	 * Initializes the class - adding the interval & schedule.
	 */
	public function init() {
		add_filter(
			'cron_schedules',
			array(
				$this,
				'add_cron_interval',
			)
		);

		$this->create_schedule();
	}

	/**
	 * Adds the PureClarity 1 minute interval
	 *
	 * @param array $schedules - existingt schedules.
	 * @return array
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['pureclarity_every_minute'] = array(
			'interval' => 60,
			'display'  => esc_html__( 'Every Minute' ),
		);

		return $schedules;
	}

	/**
	 * Schedules the feeds & delta tasks
	 */
	private function create_schedule() {
		$this->schedule_requested_feeds();
		$this->schedule_nightly_feeds();

		if ( ! $this->settings->get_access_key() && ! $this->settings->get_secret_key() ) {
			$this->schedule_signup_status();
		}

		if ( $this->settings->is_deltas_enabled() ) {
			$this->schedule_deltas();
		}
	}

	/**
	 * Schedules the requested feed run
	 */
	private function schedule_requested_feeds() {

		add_action(
			'pureclarity_requested_feeds_cron',
			array(
				$this->feeds_cron,
				'run_requested_feeds',
			)
		);

		if ( ! wp_next_scheduled( 'pureclarity_requested_feeds_cron' ) ) {
			wp_schedule_event(
				time(),
				'pureclarity_every_minute',
				'pureclarity_requested_feeds_cron'
			);
		}
	}

	/**
	 * Schedules the nightly feed run
	 */
	private function schedule_nightly_feeds() {

		add_action(
			'pureclarity_nightly_feeds_cron',
			array(
				$this->feeds_cron,
				'run_nightly_feeds',
			)
		);

		if ( ! wp_next_scheduled( 'pureclarity_nightly_feeds_cron' ) ) {
			$timezone_string = get_option( 'timezone_string' );
			if ( $timezone_string ) {
				$date = new DateTime( 'tomorrow 3am', new DateTimeZone( get_option( 'timezone_string' ) ) );
				$time = $date->getTimestamp();
			} else {
				$time = strtotime( 'tomorrow 3am' );
			}

			wp_schedule_event(
				$time,
				'daily',
				'pureclarity_nightly_feeds_cron'
			);
		}
	}

	/**
	 * Schedules the check signup task
	 */
	private function schedule_signup_status() {
		add_action(
			'pureclarity_check_signup_status_cron',
			array(
				$this->signup_cron,
				'check_signup_status',
			)
		);

		if ( ! wp_next_scheduled( 'pureclarity_check_signup_status_cron' ) ) {
			wp_schedule_event(
				time(),
				'pureclarity_every_minute',
				'pureclarity_check_signup_status_cron'
			);
		}
	}

	/**
	 * Schedules the delta task
	 */
	private function schedule_deltas() {
		add_action(
			'pureclarity_scheduled_deltas_cron',
			array(
				$this->delta_cron,
				'run_delta_schedule',
			)
		);

		if ( ! wp_next_scheduled( 'pureclarity_scheduled_deltas_cron' ) ) {
			wp_schedule_event(
				time(),
				'pureclarity_every_minute',
				'pureclarity_scheduled_deltas_cron'
			);
		}
	}
}
