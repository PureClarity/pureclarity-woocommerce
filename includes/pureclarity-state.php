<?php
/**
 * PureClarity_State class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles session related code
 */
class PureClarity_State {

	/**
	 * Cart Data
	 *
	 * @var array $cart
	 */
	private $cart;

	/**
	 * Current Category ID
	 *
	 * @var integer $current_category_id
	 */
	private $current_category_id;

	/**
	 * Product Data
	 *
	 * @var array $current_product
	 */
	private $current_product;

	/**
	 * Customer Data
	 *
	 * @var array $customer
	 */
	private $customer;

	/**
	 * Flag for if event is a logout
	 *
	 * @var boolean $islogout
	 */
	private $islogout = false;

	/**
	 * Order Data
	 *
	 * @var array $order
	 */
	private $order;

	/**
	 * PureClarity Plugin class
	 *
	 * @var PureClarity_Plugin $plugin
	 */
	private $plugin;

	/**
	 * Builds class dependencies & starts session
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin = $plugin;
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Clears PureClarity customer data
	 */
	public function clear_customer() {
		$_SESSION['pureclarity-customer'] = null;
		$this->customer                   = null;
	}

	/**
	 * Sets PureClarity customer data
	 *
	 * @param integer $user_id - customer id.
	 */
	public function set_customer( $user_id ) {
		if ( ! empty( $user_id ) ) {
			$customer = new WC_Customer( $user_id );
			if ( $customer->get_id() > 0 ) {
				$data = array(
					'userid'    => $customer->get_id(),
					'email'     => $customer->get_email(),
					'firstname' => $customer->get_first_name(),
					'lastname'  => $customer->get_last_name(),
				);

				$this->customer                   = array(
					'id'   => time(),
					'data' => $data,
				);
				$_SESSION['pureclarity-customer'] = $this->customer;
				return $this->customer;
			}
		}
	}

	/**
	 * Gets PureClarity customer data
	 */
	public function get_customer() {

		if ( ! empty( $this->customer ) ) {
			return $this->customer;
		}

		if ( isset( $_SESSION['pureclarity-customer'] ) ) {
			$this->customer = $_SESSION['pureclarity-customer'];
			$isadmin        = current_user_can( 'administrator' );
			$adminset       = ! empty( $this->customer['data']['accid'] );
			if ( $this->customer['data']['userid'] == get_current_user_id() && $isadmin == $adminset ) {
				return $this->customer;
			}
		}

		return $this->set_customer( get_current_user_id() );
	}

	/**
	 * Gets PureClarity logout event data
	 */
	public function is_logout() {
		if ( $this->islogout ) {
			return true;
		}
		if ( isset( $_SESSION['pureclarity-logout'] ) ) {
			$this->islogout                 = $_SESSION['pureclarity-logout'];
			$_SESSION['pureclarity-logout'] = null;
			return $this->islogout;
		}
		return false;
	}

	/**
	 * Gets PureClarity product data
	 */
	public function get_product() {
		if ( ! empty( $this->current_product ) ) {
			return $this->current_product;
		}

		$product = $this->get_wc_product();
		if ( ! empty( $product ) ) {
			$data = array(
				'id'  => (string) $product->get_id(),
				'sku' => $product->get_sku(),
			);
			wp_reset_postdata();
			$this->current_product = $data;
			return $this->current_product;
		}
		return null;
	}

	/**
	 * Gets current product data
	 */
	public function get_wc_product() {
		if ( is_product() ) {
			global $product;
			if ( ! empty( $product ) ) {
				if ( ! is_object( $product ) ) {
					$product = wc_get_product( get_the_ID() );
					if ( empty( $product ) ) {
						return null;
					}
				}
				if ( $product->get_sku() ) {
					return $product;
				}
			}
		}
		return null;
	}

	/**
	 * Gets current category id
	 */
	public function get_category_id() {
		if ( ! empty( $this->current_category_id ) ) {
			return $this->current_category_id;
		}
		if ( is_product_category() ) {
			$category                  = get_queried_object();
			$this->current_category_id = $category->term_id;
			return $this->current_category_id;
		}
		return null;
	}

	/**
	 * Sets PureClarity cart data
	 */
	public function set_cart() {

		$cart_items = WC()->cart->get_cart();

		$items = array();

		if ( ! empty( $cart_items ) ) {

			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				$item    = array(
					'id'        => $cart_item['product_id'],
					'qty'       => $cart_item['quantity'],
					'unitprice' => get_post_meta( $cart_item['product_id'], '_price', true ),
				);
				$items[] = $item;
			}
		}

		$this->cart = array(
			'id'    => time(),
			'items' => $items,
		);

		$_SESSION['pureclarity-cart'] = $this->cart;

		return $this->cart;
	}

	/**
	 * Gets PureClarity cart data
	 */
	public function get_cart() {

		if ( $this->cart != null ) {
			return $this->cart;
		}

		if ( isset( $_SESSION['pureclarity-cart'] ) ) {
			$this->cart = $_SESSION['pureclarity-cart'];
			return $this->cart;
		}

		// must be new session.
		return $this->set_cart();
	}

	/**
	 * Gets PureClarity order data
	 */
	public function get_order() {
		if ( ! empty( $this->order ) ) {
			return $this->order;
		}
		if ( isset( $_SESSION['pureclarity-order'] ) ) {
			$this->order                   = $_SESSION['pureclarity-order'];
			$_SESSION['pureclarity-order'] = null;
			return $this->order;
		}
		return null;
	}

}
