<?php
/**
 * PureClarity_Feed class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles feed generation & sending
 */
class PureClarity_Feed {

	/**
	 * PureClarity Plugin class
	 *
	 * @var PureClarity_Plugin $plugin
	 */
	private $plugin;

	/**
	 * Product tags map
	 *
	 * @var string[] $settings
	 */
	private $product_tags_map;

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Unique ID for feed being generated
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $unique_id;

	const PAGE_SIZE       = 100;
	const GATEWAY_TIMEOUT = 504;

	/**
	 * Builds class dependencies & includes http class
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin   = $plugin;
		$this->settings = $plugin->get_settings();

		if ( ! class_exists( 'WP_Http' ) ) {
			include_once ABSPATH . WPINC . '/class-http.php';
		}
	}

	/**
	 * Gets the total number of pages for the feed
	 *
	 * @param string $type - feed type.
	 */
	public function get_total_pages( $type ) {
		$items = $this->get_total_items( $type );
		return (int) ceil( $items / self::PAGE_SIZE );
	}

	/**
	 * Gets the total number of items for the feed
	 *
	 * @param string $type - feed type.
	 */
	public function get_total_items( $type ) {
		switch ( $type ) {
			case 'product':
				$query = new WP_Query(
					array(
						'post_type'        => $type,
						'post_status'      => 'publish',
						'suppress_filters' => true,
					)
				);
				return (int) $query->found_posts;
			case 'category':
				return 1;
			case 'user':
				return $this->get_users_count();
			case 'order':
				return $this->get_order_count();
		}
		return 0;
	}

	/**
	 * Sends a http request to start the feed
	 *
	 * @param string $type - feed type.
	 */
	public function start_feed( $type ) {
		$url  = $this->settings->get_feed_baseurl() . 'feed-create';
		$body = null;
		switch ( $type ) {
			case 'product':
				$this->load_product_tags_map();
				$body = $this->get_request_body( $type, '{ "Version": 2, "Products": [' );
				break;
			case 'category':
				$body = $this->get_request_body( $type, '{ "Version": 2, "Categories": [' );
				break;
			case 'user':
				$body = $this->get_request_body( $type, '{ "Version": 2, "Users": [' );
				break;
			case 'order':
				$body = $this->get_request_body( $type, 'OrderId,UserId,Email,DateTimeStamp,ProdCode,Quantity,UnitPrice' );
				break;
		}
		$this->http_post( $url, $body );
	}

	/**
	 * Sends a http request for feed data
	 *
	 * @param string $type - feed type.
	 * @param array  $data - data to send.
	 */
	public function send_data( $type, $data ) {
		$url  = $this->settings->get_feed_baseurl() . 'feed-append';
		$body = $this->get_request_body( $type, $data );
		$this->http_post( $url, $body );
	}

	/**
	 * Sends a http request to end the feed
	 *
	 * @param string $type - feed type.
	 */
	public function end_feed( $type ) {
		$url  = $this->settings->get_feed_baseurl() . 'feed-close';
		$body = $this->get_request_body( $type, ( 'order' === $type ? '' : ']}' ) );
		$this->http_post( $url, $body );
	}

	/**
	 * Sends a HTTP POST request
	 *
	 * @param string  $url - url to POST to.
	 * @param array   $body - body data.
	 * @param boolean $check_ok - whether to verify response.
	 * @param integer $timeout - timeout for post.
	 *
	 * @throws Exception - if varous erros occur.
	 */
	public function http_post( $url, $body, $check_ok = true, $timeout = 0 ) {
		$request = new WP_Http();
		for ( $x = 0; $x <= 5; $x++ ) {

			$args = array(
				'method' => 'POST',
				'body'   => $body,
			);

			if ( 0 !== $timeout ) {
				$args['timeout'] = $timeout;
			}

			$response = $request->request(
				$url,
				$args
			);

			if ( $response instanceof WP_Error ) {
				$error_message = '';
				foreach ( $response->get_error_codes() as $code ) {
					$error_message .= $code . ' - ' . $response->get_error_message( $code ) . '|';
				}
				throw new Exception( "Couldn't upload data to the PureClarity server, response errors: " . $error_message );
			}

			if ( $response['response'] && self::GATEWAY_TIMEOUT !== $response['response']['code'] ) {
				break;
			}
			error_log( "PureClarity 504 (Gateway Timeout) Error, retrying. Error: Couldn't upload data to the PureClarity server: " . wp_json_encode( $response ) );
		}
		if ( ! empty( $response->errors ) ) {
			throw new Exception( "Couldn't upload data to the PureClarity server, response errors: " . wp_json_encode( $response->errors ) );
		}
		if ( $check_ok && 'OK' !== $response['body'] ) {
			throw new Exception( "Couldn't upload data to the PureClarity server, response: " . wp_json_encode( $response ) );
		}
	}

	/**
	 * Builds standard data to send for feeds, with custom data added
	 *
	 * @param string $type - feed type.
	 * @param array  $data - custom data to send.
	 */
	public function get_request_body( $type, $data ) {
		$request = array(
			'accessKey' => $this->settings->get_access_key(),
			'secretKey' => $this->settings->get_secret_key(),
			'feedName'  => $type . '-' . $this->get_unique_id(),
		);
		if ( ! empty( $data ) ) {
			$request['payLoad'] = $data;
		}
		return $request;
	}

	/**
	 * Generates a uniqueid for the feed
	 */
	public function get_unique_id() {
		if ( is_null( $this->unique_id ) ) {
			$this->unique_id = uniqid();
		}
		return $this->unique_id;
	}

	/**
	 * Manually set a uniqueid for the feed
	 *
	 * @param string $unique_id - unique id for feed.
	 */
	public function set_unique_id( $unique_id ) {
		$this->unique_id = $unique_id;
	}

	/**
	 * Builds items for the given page number & feed type
	 *
	 * @param string  $type - feed type.
	 * @param integer $current_page - current page number.
	 */
	public function build_items( $type, $current_page ) {
		$items = array();
		switch ( $type ) {
			case 'product':
				$items = $this->get_products( $current_page, self::PAGE_SIZE );
				break;
			case 'category':
				$items = $this->get_categories();
				break;
			case 'user':
				$items = $this->get_users( $current_page, self::PAGE_SIZE );
				break;
			case 'order':
				$items = $this->get_orders( $current_page, self::PAGE_SIZE );
				break;
		}
		return $items;
	}

	/**
	 * Gets the required page of product data
	 *
	 * @param integer $current_page - current page number.
	 * @param integer $page_size - current page size.
	 */
	public function get_products( $current_page, $page_size ) {
		$query = new WP_Query(
			array(
				'post_type'        => 'product',
				'posts_per_page'   => $page_size,
				'post_status'      => 'publish',
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'paged'            => $current_page,
				'suppress_filters' => true,
			)
		);

		$first = false;
		if ( 1 === $current_page || 1 === $page_size ) {
			$first = true;
		}

		$products = '';
		while ( $query->have_posts() ) {
			$query->the_post();
			global $product;
			$product_data = $this->get_product_data( $product );
			if ( ! empty( $product_data ) ) {
				if ( $first ) {
					$first = false;
				} else {
					$products .= ',';
				}
				$products .= wp_json_encode( $product_data );
			}
		}

		return $products;
	}

	/**
	 * Genrates feed data for an individual product
	 *
	 * @param WC_Product $product - product to generate feed data for.
	 * @param boolean    $log_error - whether to log errors.
	 */
	public function get_product_data( $product, $log_error = true ) {
		if ( $product->get_catalog_visibility() === 'hidden' ) {
			if ( $log_error ) {
				error_log( 'PureClarity: Product ' . $product->get_id() . ' excluded from the feed. Reason: Catalog visibility = hidden.' );
			}
			return null;
		}

		$product_url = $this->remove_url_protocol(
			get_permalink( $product->get_id() )
		);

		$image_url = '';
		if ( ! empty( $product->get_image_id() ) ) {
			$image_url = $this->remove_url_protocol(
				wp_get_attachment_url( $product->get_image_id() )
			);
		}

		$category_ids = array();
		foreach ( $product->get_category_ids() as $category_id ) {
			$category_ids[] = (string) $category_id;
		}

		$product_data = array(
			'Id'          => (string) $product->get_id(),
			'Sku'         => $product->get_sku(),
			'Title'       => $product->get_title(),
			'Description' => $product->get_description() . ' ' . $product->get_short_description(),
			'Categories'  => $category_ids,
			'InStock'     => $product->get_stock_status() === 'instock',
			'Link'        => $product_url,
			'Image'       => $image_url,
			'ProductType' => $product->get_type(),
		);

		if ( $product->get_type() === 'external' && ! empty( $product->get_button_text() ) ) {
			$product_data['ButtonText'] = $product->get_button_text();
		}

		$all_image_urls = array();
		foreach ( $product->get_gallery_image_ids() as $attachment_id ) {
			$all_image_urls[] = $this->remove_url_protocol(
				wp_get_attachment_url( $attachment_id )
			);
		}
		if ( count( $all_image_urls ) > 0 ) {
			$product_data['AllImages'] = $all_image_urls;
		}

		if ( ! empty( $product->get_stock_quantity() ) ) {
			$product_data['StockQty'] = $product->get_stock_quantity();
		}

		if ( $product->get_catalog_visibility() === 'catalog' ) {
			$product_data['ExcludeFromSearch'] = true;
		}

		if ( $product->get_catalog_visibility() === 'search' ) {
			$product_data['ExcludeFromProductListing'] = true;
		}

		if ( ! empty( $product->get_date_on_sale_from() ) ) {
			$product_data['SalePriceStartDate'] = (string) $product->get_date_on_sale_from( 'c' );
		}

		if ( ! empty( $product->get_date_on_sale_to() ) ) {
			$product_data['SalePriceEndDate'] = (string) $product->get_date_on_sale_to( 'c' );
		}

		$this->set_search_tags( $product_data, $product );
		$this->set_basic_attributes( $product_data, $product );
		$this->set_product_price( $product_data, $product );
		$this->add_variant_info( $product_data, $product );
		$this->add_child_products( $product_data, $product );

		// Check is valid.
		$error = array();
		if ( ! array_key_exists( 'Prices', $product_data )
				|| ( is_array( $product_data['Prices'] ) && count( $product_data['Prices'] ) === 0 )
			) {
				$error[] = 'Prices';
		}
		if ( ! array_key_exists( 'Sku', $product_data ) || empty( $product_data['Sku'] ) ) {
			$error[] = 'Sku';
		}
		if ( ! array_key_exists( 'Title', $product_data ) || empty( $product_data['Title'] ) ) {
			$error[] = 'Title';
		}

		if ( count( $error ) > 0 ) {
			if ( $log_error ) {
				error_log( 'PureClarity: Product ' . $product->get_id() . ' excluded from the feed. Reason: Missing required fields = ' . implode( ', ', $error ) );
			}
			return null;
		}

		return $product_data;
	}

	/**
	 * Adds data to the provided array, checkign for existing keys & merging data if needed
	 *
	 * @param string $key - key to add to.
	 * @param array  $json - existing data array.
	 * @param mixed  $value - value to add.
	 */
	private function add_to_array( $key, &$json, $value ) {
		if ( ! empty( $value ) ) {
			if ( ! array_key_exists( $key, $json ) ) {
				$json[ $key ] = array();
			}
			if ( ! in_array( $value, $json[ $key ], true ) ) {
				$json[ $key ][] = $value;
			}
		}
	}

	/**
	 * Adds variant info to data array
	 *
	 * @param array      $json - existing data array.
	 * @param WC_Product $product - product to process.
	 */
	private function add_variant_info( &$json, &$product ) {

		if ( 'variable' !== $product->get_type() ) {
			return;
		}

		foreach ( $product->get_available_variations() as $variant ) {

			$this->add_to_array( 'AssociatedSkus', $json, $variant['sku'] );

			$price         = $variant['display_price'] . ' ' . get_woocommerce_currency();
			$regular_price = $variant['display_regular_price'] . ' ' . get_woocommerce_currency();

			if ( $regular_price !== $price ) {
				$this->add_to_array( 'Prices', $json, $regular_price );
				$this->add_to_array( 'SalePrices', $json, $price );
			} else {
				$this->add_to_array( 'Prices', $json, $price );
			}

			foreach ( $product->get_attributes() as $key => $attribute ) {
				$attribute = $variant['attributes'][ 'attribute_' . $key ];
				$this->add_to_array( $key, $json, $attribute );
			}
		}
	}

	/**
	 * Adds child product info to data array
	 *
	 * @param array      $json - existing data array.
	 * @param WC_Product $product - product to process.
	 */
	private function add_child_products( &$json, &$product ) {

		if ( 'grouped' === $product->get_type() ) {
			return;
		}

		foreach ( $product->get_children() as $child_id ) {
			$child_product = wc_get_product( $child_id );
			if ( ! empty( $child_product ) && $this->product_is_visible( $child_product ) ) {
				$this->add_to_array( 'AssociatedIds', $json, $child_product->get_id() );
				$this->add_to_array( 'AssociatedSkus', $json, $child_product->get_sku() );
				$this->add_to_array( 'AssociatedTitles', $json, $child_product->get_title() );
				$this->set_search_tags( $json, $child_product );
				$this->set_product_price( $json, $child_product );
				$this->add_variant_info( $json, $child_product );
				$this->add_child_products( $json, $child_product );
			}
		}
	}

	/**
	 * Checks if product is visible
	 *
	 * @param WC_Product $product - product to process.
	 */
	private function product_is_visible( $product ) {
		return 'hidden' !== $product->get_catalog_visibility() && 'publish' === $product->get_status();
	}

	/**
	 * Sets search tags for a product on data array
	 *
	 * @param array      $json - existing data array.
	 * @param WC_Product $product - product to process.
	 */
	private function set_search_tags( &$json, &$product ) {
		foreach ( $product->get_tag_ids() as $tag_id ) {
			if ( array_key_exists( $tag_id, $this->product_tags_map ) ) {
				$this->add_to_array( 'SearchTags', $json, $this->product_tags_map[ $tag_id ] );
			}
		}
	}

	/**
	 * Sets prices for a product on data array
	 *
	 * @param array      $json - existing data array.
	 * @param WC_Product $product - product to process.
	 */
	private function set_product_price( &$json, &$product ) {
		if ( $product->get_regular_price() ) {
			$price = $product->get_regular_price() . ' ' . get_woocommerce_currency();
			$this->add_to_array( 'Prices', $json, $price );
		}

		if ( $product->is_on_sale() || $this->product_has_future_sale( $product ) ) {
			if ( ! empty( $product->get_sale_price() ) ) {
				$sales_price = $product->get_sale_price() . ' ' . get_woocommerce_currency();
				$this->add_to_array( 'SalePrices', $json, $sales_price );
			}
		}
	}

	/**
	 * Checks if product is going to be on sale
	 *
	 * @param WC_Product $product - product to process.
	 */
	private function product_has_future_sale( $product ) {
		$sale_date = $product->get_date_on_sale_from();
		if ( ! empty( $sale_date ) ) {
			return ( $product->get_date_on_sale_from( 'view' )->getTimestamp() > current_time( 'timestamp', true ) );
		}
		return false;
	}

	/**
	 * Sets base product attributes on data array
	 *
	 * @param array      $json - existing data array.
	 * @param WC_Product $product - product to process.
	 */
	private function set_basic_attributes( &$json, &$product ) {
		$this->add_to_array( 'Weight', $json, $product->get_weight() );
		$this->add_to_array( 'Length', $json, $product->get_length() );
		$this->add_to_array( 'Width', $json, $product->get_width() );
		$this->add_to_array( 'Height', $json, $product->get_height() );
	}

	/**
	 * Loads all product tags
	 */
	public function load_product_tags_map() {
		$this->product_tags_map = [];
		$terms                  = get_terms( 'product_tag' );
		foreach ( $terms as $term ) {
			$this->product_tags_map[ $term->term_id ] = $term->name;
		}
	}

	/**
	 * Gets category data for feed
	 */
	public function get_categories() {
		$json       = '';
		$categories = get_terms(
			'product_cat',
			array(
				'hide_empty' => 0,
			)
		);

		// add into data the new root category!
		$data  = array(
			'Id'                      => '-1',
			'DisplayName'             => 'Shop',
			'Link'                    => '/?post_type=product',
			'ExcludeFromRecommenders' => true,
			'Description'             => 'All products on the site',
		);
		$json .= wp_json_encode( $data );

		foreach ( $categories as $category ) {
			$url = $this->remove_url_protocol(
				get_term_link( $category->term_id, 'product_cat' )
			);

			$data = array(
				'Id'          => (string) $category->term_id,
				'DisplayName' => $category->name,
				'Link'        => $url,
			);

			// If category is a root category (has no parent), add to new Shop category so that we can search in Shop for all products.
			$data['ParentIds'] = [ ( ! empty( $category->parent ) && $category->parent > 0 ) ? (string) $category->parent : '-1' ];

			$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
			if ( ! empty( $thumbnail_id ) ) {
				$image_url = wp_get_attachment_url( $thumbnail_id );
				if ( ! empty( $image_url ) ) {
					$data['Image'] = $this->remove_url_protocol( $image_url );
				}
			}
			$json .= ',' . wp_json_encode( $data );
		}
		return $json;
	}

	/**
	 * Gets count of all users
	 */
	public function get_users_count() {
		$args = array(
			'order'   => 'ASC',
			'orderby' => 'ID',
		);

		$users = new WP_User_Query( $args );
		return $users->get_total();
	}

	/**
	 * Gets the required page of users data
	 *
	 * @param integer $current_page - current page number.
	 * @param integer $page_size - current page size.
	 */
	public function get_users( $current_page, $page_size ) {

		$args = array(
			'order'   => 'ASC',
			'orderby' => 'ID',
			'offset'  => $page_size * ( $current_page - 1 ),
			'number'  => $page_size,
		);

		$users = new WP_User_Query( $args );
		$first = ( 1 === $current_page || 1 === $page_size );
		$items = '';
		foreach ( $users->get_results() as $user ) {

			$data = $this->parse_user( $user->ID );

			if ( ! empty( $data ) ) {

				if ( $first ) {
					$first = false;
				} else {
					$items .= ',';
				}

				$items .= wp_json_encode( $data );
			}
		}
		return $items;
	}

	/**
	 * Gets roles for provided user id
	 *
	 * @param integer $user_id - user id to process.
	 */
	public function get_roles( $user_id ) {
		$user_roles = get_user_meta( $user_id, 'wp_capabilities' );
		return array_keys( $user_roles[0] );
	}

	/**
	 * Processes a user for the feed
	 *
	 * @param integer $user_id - user id to process.
	 */
	public function parse_user( $user_id ) {
		$customer = new WC_Customer( $user_id );

		if ( ! empty( $customer ) && $customer->get_id() > 0 ) {
			$data = array(
				'UserId'    => $customer->get_id(),
				'Email'     => $customer->get_email(),
				'FirstName' => $customer->get_first_name(),
				'LastName'  => $customer->get_last_name(),
				'Roles'     => $this->get_roles( $user_id ),
			);

			if ( method_exists( $customer, 'get_billing' ) ) { // doesn't in earlier WC versions.
				$billing = $customer->get_billing();
				if ( ! empty( $billing ) ) {
					if ( ! empty( $billing['city'] ) ) {
						$data['City'] = $billing['city'];
					}
					if ( ! empty( $billing['state'] ) ) {
						$data['State'] = $billing['state'];
					}
					if ( ! empty( $billing['country'] ) ) {
						$data['Country'] = $billing['country'];
					}
				}
			} else {
				if ( method_exists( $customer, 'get_billing_city' ) ) {
					$data['City'] = $customer->get_billing_city();
				}
				if ( method_exists( $customer, 'get_billing_state' ) ) {
					$data['State'] = $customer->get_billing_state();
				}
				if ( method_exists( $customer, 'get_billing_country' ) ) {
					$data['Country'] = $customer->get_billing_country();
				}
			}
			return $data;
		}
		return null;
	}

	/**
	 * Gets count of all orders in last 12 months
	 */
	public function get_order_count() {
		$args = array(
			'status'       => 'completed',
			'type'         => 'shop_order',
			'date_created' => '>' . date( 'Y-m-d', strtotime( '-12 month' ) ),
			'paginate'     => true,
		);

		$results = wc_get_orders( $args );

		return $results->total;
	}

	/**
	 * Gets the required page of order data
	 *
	 * @param integer $current_page - current page number.
	 * @param integer $page_size - current page size.
	 */
	public function get_orders( $current_page, $page_size ) {
		$args = array(
			'limit'        => $page_size,
			'offset'       => $page_size * ( $current_page - 1 ),
			'orderby'      => 'date_created',
			'order'        => 'DESC',
			'status'       => 'completed',
			'type'         => 'shop_order',
			'date_created' => '>' . date( 'Y-m-d', strtotime( '-12 month' ) ),
		);

		$orders = new WC_Order_Query( $args );

		$items = '';
		foreach ( $orders->get_orders() as $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $order->get_product_from_item( $item );
				if ( is_object( $product ) ) {
					$items        .= PHP_EOL;
					$items        .= $order->get_id() . ',';
					$customer_id   = $order->get_customer_id();
					$items        .= $customer_id . ',';
					$customer_data = get_userdata( $customer_id );
					if ( $customer_data ) {
						$items .= $customer_data->user_email;
					}
					$items .= ',';
					$items .= (string) $order->get_date_created( 'c' ) . ',';
					$items .= $item->get_product_id() . ',';
					$items .= $item['qty'] . ',';
					$items .= wc_format_decimal(
						$order->get_item_total( $item, false, false )
					);
				}
			}
		}

		return $items;
	}

	/**
	 * Sends a product delta to PureClarity
	 *
	 * @param array $products - products to add/update.
	 * @param array $products_to_delete - products to delete.
	 */
	public function send_product_delta( $products, $products_to_delete ) {
		$request = array(
			'AppKey'         => $this->settings->get_access_key(),
			'Secret'         => $this->settings->get_secret_key(),
			'Products'       => $products,
			'DeleteProducts' => $products_to_delete,
			'Format'         => 'pureclarity_json',
		);

		$this->http_post( $this->settings->get_delta_url(), $request, false, 5 );

	}

	/**
	 * Sends a user delta to PureClarity
	 *
	 * @param array $users - users to add/update.
	 * @param array $deletes - users to delete.
	 */
	public function send_user_delta( $users, $deletes ) {

		$request = array(
			'AppKey'      => $this->settings->get_access_key(),
			'Secret'      => $this->settings->get_secret_key(),
			'Users'       => $users,
			'DeleteUsers' => $deletes,
			'Format'      => 'pureclarity_json',
		);

		$this->http_post( $this->settings->get_delta_url(), $request, false, 5 );

	}

	/**
	 * Removes protocl from the provided url
	 *
	 * @param string $url - url to process.
	 */
	public function remove_url_protocol( $url ) {
		return empty( $url ) ? $url : str_replace(
			array(
				'https:',
				'http:',
			),
			'',
			$url
		);
	}

}