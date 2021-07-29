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
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Session class
	 *
	 * @var PureClarity_Session $session
	 */
	private $session;

	/**
	 * PureClarity Delta class
	 *
	 * @var PureClarity_Delta $delta
	 */
	private $delta;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings $settings - PureClarity Settings class.
	 * @param PureClarity_Session  $session - PureClarity Session class.
	 * @param PureClarity_Delta    $delta - PureClarity Delta class.
	 */
	public function __construct(
		$settings,
		$session,
		$delta
	) {
		$this->settings = $settings;
		$this->session  = $session;
		$this->delta    = $delta;
	}

	/**
	 * Sets up watchers
	 */
	public function init() {
		if ( ! $this->settings->is_pureclarity_enabled() ) {
			return;
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
		add_action( 'profile_update', array( $this, 'trigger_user_delta' ) );
		add_action( 'user_register', array( $this, 'trigger_user_delta' ) );
		add_action( 'delete_user', array( $this, 'trigger_user_delta' ) );
	}

	/**
	 * Registers callback functions when users log in or out.
	 */
	private function register_user_session_listeners() {
		add_action( 'wp_login', array( $this, 'user_login' ), 15, 2 );
		add_action( 'wp_logout', array( $this, 'user_logout' ), 15, 2 );
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
		if ( 'product_cat' === $taxonomy ) {
			$this->settings->set_category_feed_required();
		}
	}

	/**
	 * Triggers delta for user
	 *
	 * @param integer $user_id - Id of user being added/edited/deleted.
	 */
	public function trigger_user_delta( $user_id ) {
		$this->delta->add_user_delta( $user_id );
	}

	/**
	 * Triggers delta for product save
	 *
	 * @param integer $id - Id of product being updated.
	 * @return mixed
	 */
	public function trigger_product_delta( $id ) {

		if ( ! current_user_can( 'edit_product', $id ) ) {
			return $id;
		}

		$this->delta->add_product_delta( $id );
	}

	/**
	 * Triggers delta for product delete
	 *
	 * @param integer $id - Id of product being deleted.
	 */
	public function delete_item( $id ) {
		$post = get_post( $id );
		if ( 'product' === $post->post_type && 'trash' === $post->post_status ) {
			$this->delta->add_product_delta( $id );
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
			// remove logout cookie if it's present (just in case user logged in immediately after logout).
			$secure = apply_filters( 'wc_session_use_secure_cookie', wc_site_is_https() && is_ssl() );
			wc_setcookie( 'pc_logout', '1', time() - YEAR_IN_SECONDS, $secure, true );
			$this->session->set_customer( $user->ID );
		}
	}

	/**
	 * Triggers user logout cookie update
	 */
	public function user_logout() {
		// add pc_logout cookie so that customer_logout can be triggered on next page load.
		$secure = apply_filters( 'wc_session_use_secure_cookie', wc_site_is_https() && is_ssl() );
		wc_setcookie( 'pc_logout', '1', time() + YEAR_IN_SECONDS, $secure, true );
		$this->session->clear_customer();
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
	 * Triggers cart update
	 *
	 * @param mixed $update - woocommerce event parameter (not used).
	 */
	public function set_cart( $update ) {
		try {
			$this->session->set_cart();
		} catch ( \Exception $exception ) {
			error_log( "PureClarity: Can't build cart changes tracking event: " . $exception->getMessage() );
		}
		return $update;
	}
}
