<?php
/**
 * PureClarity_Cron class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles cron related code
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
	 * PureClarity Feed class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

	/**
	 * Builds class dependencies & calls processing code
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin   = $plugin;
		$this->settings = $plugin->get_settings();
		$this->feed     = $plugin->get_feed();

		if ( $this->settings->is_deltas_enabled() ) {
			add_filter(
				'cron_schedules',
				array(
					$this,
					'add_cron_interval',
				)
			);
			$this->create_schedule();
		}
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
		add_action(
			'pureclarity_scheduled_deltas_cron',
			array(
				$this,
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

	/**
	 * Runs outstanding delta tasks
	 */
	public function run_delta_schedule() {
		if ( false === $this->settings->is_delta_running() ) {
			$this->settings->set_is_delta_running( '1' );
			$this->process_products();
			$this->process_categories();
			$this->process_users();
			$this->settings->set_is_delta_running( '0' );
		}
	}

	/**
	 * Processes a product delta
	 */
	public function process_products() {

		try {

			if ( ! $this->settings->is_product_feed_sent() ) {
				return;
			}

			$product_deltas = $this->settings->get_product_deltas();
			if ( count( $product_deltas ) > 0 ) {

				$products           = array();
				$products_to_delete = array();

				$totalpacket = 0;
				$count       = 0;

				$this->feed->load_product_tags_map();

				$processed_ids = array();

				foreach ( array_keys( $product_deltas ) as $id ) {

					if ( $totalpacket >= 250000 || $count > 100 ) {
						break;
					}

					$product = wc_get_product( $id );
					$post    = get_post( $id );

					if ( 'publish' === $post->post_status && false !== $product ) {
						$data = $this->feed->get_product_data( $product );
						if ( ! empty( $data ) ) {
							$products[]   = $data;
							$json         = wp_json_encode( $data );
							$totalpacket += strlen( $json );
						} else {
							$totalpacket         += strlen( $id );
							$products_to_delete[] = (string) $id;
						}
					} elseif ( 'importing' !== $post->post_status ) {
						$totalpacket         += strlen( $id );
						$products_to_delete[] = (string) $id;
					}

					$processed_ids[] = $id;
					$count++;
				}

				if ( count( $products ) > 0 || count( $products_to_delete ) > 0 ) {
					$this->feed->send_product_delta( $products, $products_to_delete );
				}

				$this->settings->remove_product_deltas( $processed_ids );
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating product deltas: ' . $exception->getMessage() );
		}
	}

	/**
	 * Processes a category delta
	 */
	public function process_categories() {
		try {
			if ( ! $this->settings->is_category_feed_sent() ) {
				return;
			}

			if ( ! empty( $this->settings->get_category_feed_required() ) ) {
				$this->settings->clear_category_feed_required();

				$data = $this->feed->build_items( 'category', 1 );
				if ( ! empty( $data ) ) {
					$this->feed->start_feed( 'category' );
					$this->feed->send_data( 'category', $data );
					$this->feed->end_feed( 'category' );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating categories: ' . $exception->getMessage() );
		}
	}

	/**
	 * Processes a user delta
	 */
	public function process_users() {

		try {
			if ( ! $this->settings->is_user_feed_sent() ) {
				return;
			}

			$deltas = $this->settings->get_user_deltas();
			if ( count( $deltas ) > 0 ) {

				$users         = array();
				$deletes       = array();
				$totalpacket   = 0;
				$count         = 0;
				$processed_ids = array();

				foreach ( array_keys( $deltas ) as $id ) {

					if ( $totalpacket >= 250000 || $count > 100 ) {
						break;
					}

					$user_data = $this->feed->parse_user( $id );
					if ( ! empty( $user_data ) ) {
						$json         = wp_json_encode( $user_data );
						$totalpacket += strlen( $json );
						$users[]      = $user_data;
					} else {
						$totalpacket += strlen( $id );
						$deletes[]    = (string) $id;
					}

					$processed_ids[] = $id;
					$count++;
				}

				if ( count( $users ) > 0 || count( $deletes ) > 0 ) {
					$this->feed->send_user_delta( $users, $deletes );
				}

				$this->settings->remove_user_deltas( $processed_ids );
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating user deltas: ' . $exception->getMessage() );
		}
	}

}
