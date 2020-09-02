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
	 * PureClarity Plugin class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Plugin $plugin
	 */
	private $plugin;

	/**
	 * PureClarity Settings class
	 *
	 * @since 2.0.0
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
	 * Builds class dependencies & calls processing code
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin     = $plugin;
		$this->settings   = $plugin->get_settings();
		$this->delta_cron = new PureClarity_Cron_Deltas( $plugin );
		$this->feeds_cron = new PureClarity_Cron_Feeds( $plugin );

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
	 * Schedules the delta task
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
	 * Schedules the delta task
	 */
	private function create_schedule() {
		$this->schedule_requested_feeds();

		if ( $this->settings->is_deltas_enabled() ) {
			$this->schedule_deltas();
		}
	}

	/**
	 * Schedules the delta task
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
