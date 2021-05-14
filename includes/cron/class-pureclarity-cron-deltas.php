<?php
/**
 * PureClarity_Cron_Deltas class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Delta\Type\Product;
use PureClarity\Api\Delta\Type\User;

/**
 * Handles Delta related cron code
 */
class PureClarity_Cron_Deltas {

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
	 * PureClarity Delta class
	 *
	 * @var PureClarity_Delta $deltas
	 */
	private $deltas;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings $settings - PureClarity Settings class.
	 * @param PureClarity_Feed     $feed - PureClarity Feed class.
	 * @param PureClarity_Delta    $deltas - PureClarity Delta class.
	 */
	public function __construct(
		$settings,
		$feed,
		$deltas
	) {
		$this->settings = $settings;
		$this->feed     = $feed;
		$this->deltas   = $deltas;
	}

	/**
	 * Runs deltas
	 */
	public function run_delta_schedule() {
		if ( false === $this->deltas->is_delta_running() ) {
			$enable_cache = false;
			if ( ! wp_suspend_cache_addition() ) {
				$enable_cache = true;
				wp_suspend_cache_addition( true );
			}
			$this->deltas->set_is_delta_running( '1' );
			$this->process_products();
			$this->process_categories();
			$this->process_users();
			$this->deltas->set_is_delta_running( '0' );
			if ( $enable_cache ) {
				wp_suspend_cache_addition( false );
			}
		}
	}

	/**
	 * Processes a product delta
	 */
	public function process_products() {

		try {

			$product_deltas = $this->deltas->get_product_deltas();
			$this->log_debug( 'product', 'Checking for product deltas, products found: ' . count( $product_deltas ) );
			if ( count( $product_deltas ) > 0 ) {
				$this->log_debug( 'product', 'Starting delta process' );

				$this->feed->load_product_tags_map();

				$processed_ids = array();
				$delta_handler = new Product(
					$this->settings->get_access_key(),
					$this->settings->get_secret_key(),
					(int) $this->settings->get_region()
				);

				foreach ( $product_deltas as $product ) {
					$id      = $product['id'];
					$product = wc_get_product( $id );
					$post    = get_post( $id );

					if ( 'publish' === $post->post_status && false !== $product ) {
						$data = $this->feed->get_product_data( $product );
						if ( ! empty( $data ) ) {
							$this->log_debug( 'product', 'Sending delta update for product: ' . $id );
							$delta_handler->addData( $data );
						} else {
							$this->log_debug( 'product', 'Sending delete delta for product ' . $id );
							$delta_handler->addDelete( (string) $id );
						}
					} elseif ( 'importing' !== $post->post_status ) {
						$this->log_debug( 'product', 'Sending delete delta for product ' . $id );
						$delta_handler->addDelete( (string) $id );
					}

					$processed_ids[] = $id;
				}

				$delta_handler->send();
				$this->deltas->remove_product_deltas( $processed_ids );
				$this->log_debug( 'product', 'Process finished' );
			}
		} catch ( \Exception $exception ) {
			$this->log_error( 'product', $exception->getMessage() );
		}
	}

	/**
	 * Processes a category delta
	 */
	public function process_categories() {
		try {
			if ( ! empty( $this->settings->get_category_feed_required() ) ) {
				$this->log_debug( 'category', 'Starting category delta process' );
				$this->settings->clear_category_feed_required();
				$this->feed->run_feed( 'category' );
			}
		} catch ( \Exception $exception ) {
			$this->log_error( 'category', $exception->getMessage() );
		}
	}

	/**
	 * Processes a user delta
	 */
	public function process_users() {

		try {
			$deltas = $this->deltas->get_user_deltas();
			$this->log_debug( 'user', 'Checking for user deltas, users found: ' . count( $deltas ) );
			if ( count( $deltas ) > 0 ) {
				$this->log_debug( 'user', 'Starting delta process' );

				$processed_ids = array();

				$delta_handler = new User(
					$this->settings->get_access_key(),
					$this->settings->get_secret_key(),
					(int) $this->settings->get_region()
				);

				foreach ( $deltas as $user ) {
					$id = $user['id'];

					$user_data = $this->feed->parse_user( $id );
					if ( ! empty( $user_data ) ) {
						$this->log_debug( 'user', 'Sending update delta for user ' . $id );
						$delta_handler->addData( $user_data );
					} else {
						$this->log_debug( 'user', 'Sending delete delta for user ' . $id );
						$delta_handler->addDelete( (string) $id );
					}

					$processed_ids[] = $id;
				}

				$delta_handler->send();
				$this->deltas->remove_user_deltas( $processed_ids );
				$this->log_debug( 'user', 'Process finished' );
			}
		} catch ( \Exception $exception ) {
			$this->log_error( 'users', $exception->getMessage() );
		}
	}

	/**
	 * Logs an error using WooCommerce Logging.
	 *
	 * @param string $type - delta type.
	 * @param string $message - error message.
	 */
	private function log_error( $type, $message ) {
		$logger = wc_get_logger();
		if ( $logger ) {
			$logger->error( "PureClarity {$type} delta error: {$message}" );
		}
	}

	/**
	 * Logs an error using WooCommerce Logging.
	 *
	 * @param string $type - delta type.
	 * @param string $message - error message.
	 */
	private function log_debug( $type, $message ) {
		if ( $this->settings->is_feed_logging_enabled() ) {
			$logger = wc_get_logger();
			if ( $logger ) {
				$logger->debug( "PureClarity {$type} delta debug: {$message}" );
			}
		}
	}

}
