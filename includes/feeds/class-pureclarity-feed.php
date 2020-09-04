<?php
/**
 * PureClarity_Feed class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

use PureClarity\Api\Feed\Type\Product;
use PureClarity\Api\Feed\Type\User;
use PureClarity\Api\Feed\Type\Order;
use PureClarity\Api\Feed\Type\Category;

/**
 * Handles feed generation & sending
 */
class PureClarity_Feed {

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
	 * PureClarity State Manager class
	 *
	 * @var PureClarity_State_Manager $state_manager
	 */
	private $state_manager;

	const PAGE_SIZE       = 100;
	const GATEWAY_TIMEOUT = 504;

	/**
	 * Builds class dependencies & includes http class
	 *
	 * @param PureClarity_Settings      $settings - PureClarity Settings class.
	 * @param PureClarity_State_Manager $state_manager - PureClarity State Manager class.
	 */
	public function __construct(
		$settings,
		$state_manager
	) {
		$this->settings      = $settings;
		$this->state_manager = $state_manager;
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
	 *
	 * @return int
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
	 * Runs an individual feed.
	 *
	 * @param string $type - Type of feed to run.
	 */
	public function run_feed( $type ) {
		try {
			$feed_class = $this->get_feed_class( $type );

			$total_pages_count = $this->get_total_pages( $type );

			if ( $total_pages_count > 0 ) {
				$feed_class->start();
				for ( $current_page = 1; $current_page <= $total_pages_count; $current_page++ ) {
					$data = $this->get_page_data( $type, $current_page );
					foreach ( $data as $row ) {
						$feed_class->append( $row );
					}
					$this->state_manager->set_state_value( $type . '_feed_progress', round( ( $total_pages_count / $current_page * 100 ) ) );
				}
				$feed_class->end();
			}

			$this->state_manager->set_state_value( $type . '_feed_last_run', time() );
		} catch ( \Exception $e ) {
			$this->state_manager->set_state_value( $type . '_feed_error', $e->getMessage() );
		}
	}

	/**
	 * Gets the PureClarity PHP SDK feed class.
	 *
	 * @param string $type - The type of feed we need to run.
	 *
	 * @return false|Category|Order|Product|User
	 */
	private function get_feed_class( $type ) {
		$access_key = $this->settings->get_access_key();
		$secret_key = $this->settings->get_secret_key();
		$region     = (int) $this->settings->get_region();

		switch ( $type ) {
			case 'product':
				$feed_class = new Product(
					$access_key,
					$secret_key,
					$region
				);
				break;
			case 'category':
				$feed_class = new Category(
					$access_key,
					$secret_key,
					$region
				);
				break;
			case 'user':
				$feed_class = new User(
					$access_key,
					$secret_key,
					$region
				);
				break;
			case 'order':
				$feed_class = new Order(
					$access_key,
					$secret_key,
					$region
				);
				break;
			default:
				$feed_class = false;
				break;
		}

		return $feed_class;
	}

	/**
	 * Gets the current page data.
	 *
	 * @param string  $type - The type of feed we need to run.
	 * @param integer $current_page - The page to get data for.
	 *
	 * @return mixed[]
	 */
	private function get_page_data( $type, $current_page ) {

		switch ( $type ) {
			case 'product':
				$this->load_product_tags_map();
				$data = $this->get_products( $current_page, self::PAGE_SIZE );
				break;
			case 'category':
				$data = $this->get_categories();
				break;
			case 'user':
				$data = $this->get_users( $current_page, self::PAGE_SIZE );
				break;
			case 'order':
				$data = $this->get_orders( $current_page, self::PAGE_SIZE );
				break;
			default:
				$data = array();
				break;
		}

		return $data;
	}

	/**
	 * Gets the required page of product data
	 *
	 * @param integer $current_page - current page number.
	 * @param integer $page_size - current page size.
	 *
	 * @return array
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

		$products = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			global $product;
			$product_data = $this->get_product_data( $product );
			if ( ! empty( $product_data ) ) {
				$products[] = $product_data;
			}
		}

		return $products;
	}

	/**
	 * Generates feed data for an individual product
	 *
	 * @param WC_Product $product - product to generate feed data for.
	 * @param boolean    $log_error - whether to log errors.
	 *
	 * @return array|null
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
		if ( null === $this->product_tags_map ) {
			$this->product_tags_map = array();
			$terms                  = get_terms( 'product_tag' );
			foreach ( $terms as $term ) {
				$this->product_tags_map[ $term->term_id ] = $term->name;
			}
		}
	}

	/**
	 * Gets category data for feed
	 */
	public function get_categories() {
		$categories = get_terms(
			'product_cat',
			array(
				'hide_empty' => 0,
			)
		);

		$category_data = array();

		// add into data the new root category!
		$data = array(
			'Id'                      => '-1',
			'DisplayName'             => 'Shop',
			'Link'                    => '/?post_type=product',
			'ExcludeFromRecommenders' => true,
			'Description'             => 'All products on the site',
			'Image'                   => '',
			'ParentIds'               => array(),
		);

		$category_data[] = $data;

		foreach ( $categories as $category ) {
			$url = $this->remove_url_protocol(
				get_term_link( $category->term_id, 'product_cat' )
			);

			$data = array(
				'Id'          => (string) $category->term_id,
				'DisplayName' => $category->name,
				'Link'        => $url,
				'Description' => '',
				'Image'       => '',
			);

			// If category is a root category (has no parent), add to new Shop category so that we can search in Shop for all products.
			$data['ParentIds'] = array( ( ! empty( $category->parent ) && $category->parent > 0 ) ? (string) $category->parent : '-1' );

			$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
			if ( ! empty( $thumbnail_id ) ) {
				$image_url = wp_get_attachment_url( $thumbnail_id );
				if ( ! empty( $image_url ) ) {
					$data['Image'] = $this->remove_url_protocol( $image_url );
				}
			}
			$category_data[] = $data;
		}
		return $category_data;
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

		$users     = new WP_User_Query( $args );
		$user_data = array();
		foreach ( $users->get_results() as $user ) {
			$data = $this->parse_user( $user->ID );
			if ( ! empty( $data ) ) {
				$user_data[] = $data;
			}
		}
		return $user_data;
	}

	/**
	 * Gets roles for provided user id
	 *
	 * @param integer $user_id - user id to process.
	 *
	 * @return array
	 */
	public function get_roles( $user_id ) {
		$user_roles = get_user_meta( $user_id, 'wp_capabilities' );
		return array_keys( $user_roles[0] );
	}

	/**
	 * Processes a user for the feed
	 *
	 * @param integer $user_id - user id to process.
	 *
	 * @return array|null
	 * @throws Exception - in WC_Customer - If customer cannot be read/found and $data is set.
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

		$orders     = new WC_Order_Query( $args );
		$order_data = array();
		foreach ( $orders->get_orders() as $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $order->get_product_from_item( $item );
				if ( is_object( $product ) ) {
					$customer_data = get_userdata( $order->get_user_id() );
					$order_data[]  = array(
						'OrderID'   => $order->get_id(),
						'UserId'    => $order->get_user_id() ? $order->get_user_id() : '',
						'Email'     => $order->get_user_id() ? $customer_data->user_email : $order->get_billing_email(),
						'DateTime'  => (string) $order->get_date_created( 'c' ),
						'ProdCode'  => $item->get_product_id(),
						'Quantity'  => $item['qty'],
						'UnitPrice' => wc_format_decimal( $order->get_item_total( $item, false, false ) ),
					);
				}
			}
		}

		return $order_data;
	}

	/**
	 * Removes protocl from the provided url
	 *
	 * @param string $url - url to process.
	 *
	 * @return mixed|string|string[]
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
