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
			$this->process_products();
			$this->process_categories();
			$this->process_users();
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

				foreach ( $product_deltas as $id => $size ) {

					if ( $totalpacket >= 250000 || $count > 100 ) {
						break;
					}

					$this->settings->remove_product_delta( $id );
					if ( $size > -1 ) {
						$meta_value = get_post_meta( $id, 'pc_delta' );
						if ( ! empty( $meta_value )
							&& is_array( $meta_value )
							&& count( $meta_value ) > 0 ) {
							delete_post_meta( $id, 'pc_delta' );
							$product = json_decode( $meta_value[0] );
							if ( ! empty( $product ) ) {
								$totalpacket += $size;
								$products[]   = $product;
							}
						}
					} else {
						$totalpacket       += strlen( $id );
						$products_to_delete[] = (string) $id;
					}

					$count += 1;
				}

				if ( count( $products ) > 0 || count( $products_to_delete ) > 0 ) {
					$this->feed->send_product_delta( $products, $products_to_delete );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating product deltas: ' . $exception->getMessage() );
		}
	}

	/**
	 * Processes a category delta
	 */
	public function process_categories() {

		if ( ! $this->settings->is_category_feed_sent() ) {
			return;
		}

		if ( ! empty( $this->settings->get_category_feed_required() ) ) {

			$this->settings->clear_category_feed_required();

			$data = $this->feed->build_items( 'category', 1 );
			if ( ! empty( $data ) ) {
				try {
					$this->feed->start_feed( 'category' );
					$this->feed->send_data( 'category', $data );
					$this->feed->end_feed( 'category' );
				} catch ( \Exception $exception ) {
					error_log( 'PureClarity: An error occurred updating categories: ' . $exception->getMessage() );
				}
			}
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

				$users   = array();
				$deletes = array();

				$totalpacket = 0;
				$count       = 0;

				foreach ( $deltas as $id => $size ) {

					if ( $totalpacket >= 250000 || $count > 100 ) {
						break;
					}

					$this->settings->remove_user_delta( $id );
					if ( $size > -1 ) {
						$meta_value = get_user_meta( $id, 'pc_delta' );
						if ( ! empty( $meta_value ) && is_array( $meta_value ) && count( $meta_value ) > 0 ) {
							delete_user_meta( $id, 'pc_delta' );
							$user = json_decode( $meta_value[0] );
							if ( ! empty( $user ) ) {
								$totalpacket += $size;
								$users[]      = $user;
							}
						}
					} else {
						$totalpacket += strlen( $id );
						$deletes[]    = (string) $id;
					}

					$count += 1;
				}

				if ( count( $users ) > 0 || count( $deletes ) > 0 ) {
					$this->feed->send_user_delta( $users, $deletes );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating user deltas: ' . $exception->getMessage() );
		}
	}

}
