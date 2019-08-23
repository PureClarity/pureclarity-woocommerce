<?php

class PureClarity_Cron {

	private $plugin;
	private $setting;
	private $feed;

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

	public function process_products() {

		try {

			if ( ! $this->settings->is_product_feed_sent() ) {
				return;
			}

			$productDeltas = $this->settings->get_product_deltas();
			if ( count( $productDeltas ) > 0 ) {

				$products         = array();
				$productsToDelete = array();

				$totalpacket = 0;
				$count       = 0;

				foreach ( $productDeltas as $id => $size ) {

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
						$productsToDelete[] = (string) $id;
					}

					$count += 1;
				}

				if ( count( $products ) > 0 || count( $productsToDelete ) > 0 ) {
					$this->feed->send_product_delta( $products, $productsToDelete );
				}
			}
		} catch ( \Exception $exception ) {
			error_log( 'PureClarity: An error occurred updating product deltas: ' . $exception->getMessage() );
		}
	}

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
