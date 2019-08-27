<?php
/**
 * PureClarity_Products_Watcher class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles action related to product, category & user data changes
 */
class PureClarity_Products_Watcher {

	/**
	 * PureClarity Feed class
	 *
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

	/**
	 * PureClarity Plugin class
	 *
	 * @var PureClarity_Plugin $plugin
	 */
	private $plugin;

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity State class
	 *
	 * @var PureClarity_State $state
	 */
	private $state;

	/**
	 * Builds class dependencies & sets up watchers
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin   = $plugin;
		$this->feed     = $plugin->get_feed();
		$this->settings = $plugin->get_settings();
		$this->state    = $plugin->get_state();

		if ( ! $this->settings->is_pureclarity_enabled() ) {
			return;
		}

		if ( ! session_id() ) {
			session_start();
		}

		if ( $this->settings->is_deltas_enabled() ) {
			$this->register_product_listeners();
			$this->register_category_listeners();
			$this->register_user_listeners();
		}
		$this->register_user_session_listeners();
		$this->register_cart_listeners();
		$this->register_order_listeners();

	}

	/**
	 * Registers callback functions when product changes occur.
	 */
	private function register_product_listeners() {

		// new / updated or un-trashed products.
		add_action( 'woocommerce_new_product', array( $this, 'trigger_product_delta' ), 10, 3 );
		add_action( 'woocommerce_update_product', array( $this, 'trigger_product_delta' ), 10, 3 );
		add_action( 'untrashed_post', array( $this, 'trigger_product_delta' ) );

		// trashed or deleted products.
		add_action( 'trashed_post', array( $this, 'delete_item' ) );
		add_action( 'woocommerce_delete_product', array( $this, 'delete_item' ), 10, 3 );
		add_action( 'woocommerce_trash_product', array( $this, 'delete_item' ) );
	}

	/**
	 * Registers callback functions when category changes occur.
	 */
	private function register_category_listeners() {
		add_action( 'create_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
		add_action( 'delete_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
	}

	/**
	 * Registers callback functions when changes are made to user records.
	 */
	private function register_user_listeners() {
		add_action( 'profile_update', array( $this, 'save_user_via_deltas' ) );
		add_action( 'user_register', array( $this, 'save_user_via_deltas' ) );
		add_action( 'delete_user', array( $this, 'delete_user_via_deltas' ) );
	}

	/**
	 * Registers callback functions when users log in or out.
	 */
	private function register_user_session_listeners() {
		add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'user_logout' ), 10, 2 );
	}

	/**
	 * Registers callback functions when cart changes occur.
	 */
	private function register_cart_listeners() {
		add_action( 'woocommerce_add_to_cart', array( $this, 'set_cart' ), 10, 1 );
		add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'set_cart' ), 10, 1 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'set_cart' ), 10, 1 );
	}

	/**
	 * Registers callback functions for when orders occur
	 */
	private function register_order_listeners() {
		if ( is_admin() ) {
			add_action( 'woocommerce_order_status_completed', array( $this, 'moto_order_placed' ), 10, 1 );
		} else {
			add_action( 'woocommerce_order_status_processing', array( $this, 'order_placed' ), 10, 1 );
			add_action( 'woocommerce_order_status_on-hold', array( $this, 'order_placed' ), 10, 1 );
			add_action( 'woocommerce_order_status_pending', array( $this, 'order_placed' ), 10, 1 );
		}
	}

	/**
	 * Adds a category to deltas if required
	 *
	 * @param mixed  $term_id - Term ID (not used).
	 * @param mixed  $tt_id - TT ID (not used).
	 * @param string $taxonomy - Taxonomy type.
	 * @return void
	 */
	public function add_category_feed_to_deltas( $term_id, $tt_id, $taxonomy ) {
		if ( $taxonomy == 'product_cat' ) {
			$this->settings->set_category_feed_required();
		}
	}

	/**
	 * Saves a user for deltas
	 *
	 * @param integer $user_id - Id of user being saved.
	 */
	public function save_user_via_deltas( $user_id ) {
		$this->settings->add_user_delta( $user_id, 1 );
	}

	/**
	 * Triggers delta for user delete
	 *
	 * @param integer $user_id - Id of user being deleted.
	 */
	public function delete_user_via_deltas( $user_id ) {
		$this->settings->add_user_delta( $user_id, -1 );
	}

	/**
	 * Triggers delta for product save
	 *
	 * @param integer $id - Id of product being updated.
	 */
	public function trigger_product_delta( $id ) {

		if ( ! current_user_can( 'edit_product', $id ) ) {
			return $id;
		}

		$this->settings->add_product_delta( $id );
	}

	/**
	 * Triggers delta for product delete
	 *
	 * @param integer $id - Id of product being deleted.
	 */
	public function delete_item( $id ) {
		$post = get_post( $id );
		if ( $post->post_type == 'product' && $post->post_status === 'trash' ) {
			$this->settings->add_product_delta( $id );
		}
	}

	/**
	 * Triggers user login session update
	 *
	 * @param string  $user_login - param passed by event (not used).
	 * @param WP_User $user - user that logged in.
	 * @return void
	 */
	public function user_login( $user_login, $user ) {
		if ( ! empty( $user ) ) {
			$this->state->set_customer( $user->ID );
		}
	}

	/**
	 * Triggers user logout session update
	 */
	public function user_logout() {
		$_SESSION['pureclarity-logout'] = true;
		$this->state->clear_customer();
	}

	/**
	 * Triggers MOTO order event
	 *
	 * @param integer $order_id - order id.
	 */
	public function moto_order_placed( $order_id ) {
		// Order is placed in the admin and is complete.
	}

	/**
	 * Triggers order placed event
	 *
	 * @param integer $order_id - order id.
	 */
	public function order_placed( $order_id ) {

		$order    = wc_get_order( $order_id );
		$customer = new WC_Customer( $order->get_user_id() );
		error_log( json_encode( $order ) );

		if ( ! empty( $order ) && ! empty( $customer ) ) {

			$transaction = array(
				'orderid'    => $order->get_id(),
				'firstname'  => $customer->get_first_name(),
				'lastname'   => $customer->get_last_name(),
				'userid'     => $order->get_user_id(),
				'ordertotal' => $order->get_total(),
			);

			$order_items = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $order->get_product_from_item( $item );
				if ( is_object( $product ) ) {
					$order_items[] = array(
						'orderid'   => $order->get_id(),
						'id'        => $item->get_product_id(),
						'qty'       => $item['qty'],
						'unitprice' => wc_format_decimal( $order->get_item_total( $item, false, false ) ),
					);
				}
			}

			$data          = $transaction;
			$data['items'] = $order_items;

			$_SESSION['pureclarity-order'] = $data;
		}
	}

	/**
	 * Triggers cart update
	 *
	 * @param mixed $update - woocommerce event parameter (not used).
	 */
	public function set_cart( $update ) {
		try {
			$this->state->set_cart();
		} catch ( \Exception $exception ) {
			error_log( "PureClarity: Can't build cart changes tracking event: " . $exception->getMessage() );
		}
	}
}
