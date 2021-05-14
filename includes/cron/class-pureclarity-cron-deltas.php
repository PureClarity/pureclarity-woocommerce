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
			wp_suspend_cache_addition( true );
			$this->deltas->set_is_delta_running( '1' );
			$this->process_products();
			$this->process_categories();
			$this->process_users();
			$this->deltas->set_is_delta_running( '0' );
			wp_suspend_cache_addition( false );
		}
	}

	/**
	 * Processes a product delta
	 */
	public function process_products() {

		try {

			$product_deltas = $this->deltas->get_product_deltas();
			if ( count( $product_deltas ) > 0 ) {

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
							$delta_handler->addData( $data );
						} else {
							$delta_handler->addDelete( (string) $id );
						}
					} elseif ( 'importing' !== $post->post_status ) {
						$delta_handler->addDelete( (string) $id );
					}

					$processed_ids[] = $id;
				}

				$delta_handler->send();
				$this->deltas->remove_product_deltas( $processed_ids );
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
			if ( ! empty( $this->settings->get_category_feed_required() ) ) {
				$this->settings->clear_category_feed_required();
				$this->feed->run_feed( 'category' );
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
			$deltas = $this->deltas->get_user_deltas();
			if ( count( $deltas ) > 0 ) {

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
						$delta_handler->addData( $user_data );
					} else {
						$delta_handler->addDelete( (string) $id );
					}

					$processed_ids[] = $id;
				}

				$delta_handler->send();
				$this->deltas->remove_user_deltas( $processed_ids );
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating user deltas: ' . $exception->getMessage() );
		}
	}

}
