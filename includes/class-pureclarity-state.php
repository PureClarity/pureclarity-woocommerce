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
	private $islogout;

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

		// if not on the login page, check for the logout cookie, in order to see if we need to trigger customer_logout event.
		// cannot check on login page as our js isn't on it.
		if ( 'wp-login.php' !== $GLOBALS['pagenow'] ) {
			$this->is_logout();
		}
	}

	/**
	 * Clears PureClarity customer data
	 */
	public function clear_customer() {
		WC()->session->set( 'pureclarity-customer', null );
		$this->customer = null;
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

				$this->customer = array(
					'id'   => time(),
					'data' => $data,
				);
				WC()->session->set( 'pureclarity-customer', $this->customer );
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

		$customer = WC()->session->get( 'pureclarity-customer' );
		if ( $customer ) {
			$this->customer = $customer;
			if ( get_current_user_id() === $this->customer['data']['userid'] ) {
				return $this->customer;
			}
		}

		return $this->set_customer( get_current_user_id() );
	}

	/**
	 * Checks for logout cookie, and if present sets $this->islogout to true, for use later in config rendering
	 */
	public function is_logout() {
		if ( ! isset( $this->islogout ) ) {
			$this->islogout = isset( $_COOKIE['pc_logout'] ) ? (int) $_COOKIE['pc_logout'] : 0;
			if ( isset( $_COOKIE['pc_logout'] ) ) {
				unset( $_COOKIE['pc_logout'] );
				$secure = apply_filters( 'wc_session_use_secure_cookie', wc_site_is_https() && is_ssl() );
				wc_setcookie( 'pc_logout', '1', time() - YEAR_IN_SECONDS, $secure, true );
			}
		}
		return 1 === $this->islogout;
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
			$categories = $product->get_category_ids();
			$category   = ( count( $categories ) > 0 ) ? array_shift( $categories ) : null;
			$data       = array(
				'id'          => (string) $product->get_id(),
				'sku'         => $product->get_sku(),
				'category_id' => $category,
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

		$items      = array();
		$cart_items = WC()->cart->get_cart();
		$cart_id    = time();

		if ( empty( $cart_items ) && isset( $_COOKIE['pc_empty_cart'] ) ) {
			$cart_id = (int) $_COOKIE['pc_empty_cart'];
		}

		if ( ! empty( $cart_items ) ) {
			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				$item    = array(
					'id'        => $cart_item['product_id'],
					'qty'       => $cart_item['quantity'],
					'unitprice' => get_post_meta( $cart_item['product_id'], '_price', true ),
				);
				$items[] = $item;
			}

			if ( isset( $_COOKIE['pc_empty_cart'] ) ) {
				$secure = apply_filters( 'wc_session_use_secure_cookie', wc_site_is_https() && is_ssl() );
				wc_setcookie( 'pc_empty_cart', $cart_id, time() - YEAR_IN_SECONDS, $secure, true );
			}
		} else {
			$secure = apply_filters( 'wc_session_use_secure_cookie', wc_site_is_https() && is_ssl() );
			wc_setcookie( 'pc_empty_cart', $cart_id, time() + YEAR_IN_SECONDS, $secure, true );
		}

		$this->cart = array(
			'id'    => $cart_id,
			'items' => $items,
		);

		WC()->session->set( 'pureclarity-cart', $this->cart );

		return $this->cart;
	}

	/**
	 * Gets PureClarity cart data
	 */
	public function get_cart() {

		if ( null !== $this->cart ) {
			return $this->cart;
		}

		$cart = WC()->session->get( 'pureclarity-cart' );
		if ( isset( $cart ) ) {
			$this->cart = $cart;
			return $this->cart;
		}

		// must be new session.
		return $this->set_cart();
	}

	/**
	 * Gets PureClarity order data
	 *
	 * @return array|null
	 */
	public function get_order() {
		// Only do this on "Order-received" page.
		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			return null;
		}

		if ( ! empty( $this->order ) ) {
			return $this->order;
		}

		global $wp;
		$order_id = absint( $wp->query_vars['order-received'] );

		if ( $order_id ) {
			$pc_order    = new PureClarity_Order();
			$order_data  = $pc_order->get_order_info( $order_id );
			$this->order = $order_data;
		}

		return $this->order;
	}

	/**
	 * Works out what page type this page is, based on the target
	 *
	 * @return array
	 */
	public function get_page_view_context() {
		return $this->get_page_context( $this->get_page_type() );
	}

	/**
	 * Works out what page type this page is, based on WP functions
	 *
	 * @return string
	 */
	private function get_page_type() {
		$page_type = '';
		if ( is_front_page() ) {
			$page_type = 'homepage';
		} elseif ( is_search() ) {
			$page_type = 'search_results';
		} elseif ( is_product_category() || is_shop() ) {
			$page_type = 'product_listing_page';
		} elseif ( is_product() ) {
			$page_type = 'product_page';
		} elseif ( is_cart() ) {
			$page_type = 'basket_page';
		} elseif ( is_account_page() ) {
			$page_type = 'my_account';
		} elseif ( is_wc_endpoint_url( 'order-received' ) ) {
			$page_type = 'order_complete_page';
		} elseif ( is_checkout() ) {
			$page_type = '';
		} elseif ( is_page() ) {
			$page_type = 'content_page';
		}
		return $page_type;
	}

	/**
	 * Gets page-type specific context (e.g. product page - product ID)
	 *
	 * @param string $page_type Page Type - the page type currently being viewed.
	 *
	 * @return array
	 */
	protected function get_page_context( $page_type ) {
		$context = [];

		if ( $page_type ) {
			$context['page_type'] = $page_type;
		}

		switch ( $page_type ) {
			case 'category_listing_page':
			case 'product_listing_page':
				$category_id = $this->get_category_id();
				if ( $category_id ) {
					$context['category_id'] = $this->get_category_id();
				}
				break;
			case 'product_page':
				$product = $this->get_product();
				if ( is_array( $product ) ) {
					$context['product_id'] = $product['id'];
					if ( $product['category_id'] ) {
						$context['category_id'] = $product['category_id'];
					}
				}
				break;
		}

		return $context;
	}
}
