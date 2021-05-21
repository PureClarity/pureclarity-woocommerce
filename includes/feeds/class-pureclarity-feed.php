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

	/**
	 * Default Page Size.
	 */
	const PAGE_SIZE = 100;

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
	 * @return int
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
						'post_type'              => $type,
						'post_status'            => 'publish',
						'suppress_filters'       => true,
						'update_post_meta_cache' => false,
					)
				);
				return (int) $query->found_posts;
			case 'category':
				return 1;
			case 'user':
				return $this->get_users_count();
			case 'orders':
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
		$enable_cache = false;
		if ( ! wp_suspend_cache_addition() ) {
			$enable_cache = true;
			wp_suspend_cache_addition( true );
		}

		try {
			$feed_class = $this->get_feed_class( $type );

			$total_pages_count = $this->get_total_pages( $type );

			$this->log_debug( $type, 'Total pages of data found: ' . $total_pages_count );
			if ( $total_pages_count > 0 ) {
				$feed_class->start();
				for ( $current_page = 1; $current_page <= $total_pages_count; $current_page++ ) {
					$this->log_debug( $type, 'Processing page ' . $current_page . ' of ' . $total_pages_count );
					$data = $this->get_page_data( $type, $current_page );
					foreach ( $data as $row ) {
						$feed_class->append( $row );
					}
					$this->state_manager->set_state_value( $type . '_feed_progress', round( ( $current_page / $total_pages_count ) * 100 ) );
				}
				$feed_class->end();
			}

			$this->log_debug( $type, 'Feed finished' );

			$this->state_manager->set_state_value( $type . '_feed_last_run', time() );
		} catch ( \Exception $e ) {
			$this->log_error( $type, $e->getMessage() );
			$this->state_manager->set_state_value( $type . '_feed_error', $e->getMessage() );
		}

		if ( $enable_cache ) {
			wp_suspend_cache_addition( false );
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
			case 'orders':
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
			case 'orders':
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
				'post_type'              => 'product',
				'posts_per_page'         => $page_size,
				'post_status'            => 'publish',
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'paged'                  => $current_page,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
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

		$this->log_debug( 'product', 'Processing product ' . $product->get_id() );

		if ( $product->get_catalog_visibility() === 'hidden' ) {
			if ( $log_error ) {
				$this->log_debug( 'product', 'Product ' . $product->get_id() . ' excluded from the feed. Reason: Catalog visibility = hidden.' );
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
			'Title'       => $product->get_title(),
			'Description' => $product->get_description() . ' ' . $product->get_short_description(),
			'Categories'  => $category_ids,
			'InStock'     => $product->get_stock_status() === 'instock',
			'Link'        => $product_url,
			'Image'       => $image_url,
			'ProductType' => $product->get_type(),
		);

		if ( $product->get_sku() ) {
			$product_data['Sku'] = $product->get_sku();
		}


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

		if ( ! $product->is_in_stock() && $this->settings->is_product_feed_exclude_oos_enabled() ) {
			$product_data['ExcludeFromRecommenders'] = true;
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
		if ( ! array_key_exists( 'Title', $product_data ) || empty( $product_data['Title'] ) ) {
			$error[] = 'Title';
		}

		if ( count( $error ) > 0 ) {
			if ( $log_error ) {
				$this->log_debug( 'product', 'Product ' . $product->get_id() . ' excluded from the feed. Reason: Missing required fields = ' . implode( ', ', $error ) );
			}
			return null;
		}

		return apply_filters( 'pureclarity_feed_get_product_data', $product_data, $product );
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

			$this->add_to_array( 'AssociatedIds', $json, $variant['variation_id'] );
			if ( isset( $variant['sku'] ) && ! empty( $variant['sku'] ) ) {
				$this->add_to_array( 'AssociatedSkus', $json, $variant['sku'] );
			}

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
				if ( $child_product->get_sku() ) {
					$this->add_to_array( 'AssociatedSkus', $json, $child_product->get_sku() );
				}
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
	 * @return bool
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
	 * @return bool
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

			$this->log_debug( 'category', 'Processing category ' . $category->term_id );

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

			$category_data[] = apply_filters( 'pureclarity_feed_get_category_data', $data, $category );
		}
		return $category_data;
	}

	/**
	 * Gets count of all users
	 *
	 * @return int
	 */
	public function get_users_count() {
		$args = array(
			'order'                  => 'ASC',
			'orderby'                => 'ID',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => 'wc_last_active',
					'value'   => (string) strtotime( '-3 months' ),
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		);

		$users = new WP_User_Query( $args );
		return $users->get_total();
	}

	/**
	 * Gets the required page of users data
	 *
	 * @param integer $current_page - current page number.
	 * @param integer $page_size - current page size.
	 *
	 * @return array
	 * @throws Exception - in parse_user - If customer cannot be read/found and $data is set.
	 */
	public function get_users( $current_page, $page_size ) {

		add_action( 'pre_user_query', array( $this, 'user_query_add_billing_address' ) );

		$args = array(
			'order'                  => 'ASC',
			'orderby'                => 'ID',
			'offset'                 => $page_size * ( $current_page - 1 ),
			'number'                 => $page_size,
			'update_post_meta_cache' => false,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => 'wc_last_active',
					'value'   => (string) strtotime( '-3 months' ),
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		);

		$users     = new WP_User_Query( $args );
		$user_data = array();
		foreach ( $users->get_results() as $user ) {
			$data = $this->parse_user( $user );
			if ( ! empty( $data ) ) {
				$user_data[] = apply_filters( 'pureclarity_feed_get_user_data', $data, $user );
			}
		}

		remove_action( 'pre_user_query', array( $this, 'add_my_custom_queries' ) );

		return $user_data;
	}

	/**
	 * Adds billing address fields to user query.
	 *
	 * @param WP_User_Query $query - User Query to add to.
	 */
	public function user_query_add_billing_address( $query ) {
		global $wpdb;

		// Add the billing city / state/ country to the meta fields in our query.
		$query->query_fields .= ', billing_city.meta_value as billing_city';
		$query->query_fields .= ', billing_state.meta_value as billing_state';
		$query->query_fields .= ', billing_country.meta_value as billing_country';

		// add a left join to actually gather the billing address info for the users.
		$query->query_from .= " LEFT JOIN $wpdb->usermeta billing_city ON $wpdb->users.ID = "
							. "billing_city.user_id and billing_city.meta_key = 'billing_city'";

		$query->query_from .= " LEFT JOIN $wpdb->usermeta billing_state ON $wpdb->users.ID = "
							. "billing_state.user_id and billing_state.meta_key = 'billing_state'";
		$query->query_from .= " LEFT JOIN $wpdb->usermeta billing_country ON $wpdb->users.ID = "
							. "billing_country.user_id and billing_country.meta_key = 'billing_country'";
	}

	/**
	 * Processes a user for the feed
	 *
	 * @param Wp_User $user - user id to process.
	 *
	 * @return array|null
	 * @throws Exception - in WC_Customer - If customer cannot be read/found and $data is set.
	 */
	public function parse_user( $user ) {

		if ( is_object( $user ) ) {
			$this->log_debug( 'user', 'Processing user ' . $user->ID );

			$user_data = array(
				'UserId'    => $user->ID,
				'Email'     => $user->user_email,
				'FirstName' => $user->first_name,
				'LastName'  => $user->last_name,
				'Roles'     => $user->roles,
			);

			if ( $user->billing_city ) {
				$user_data['City'] = $user->billing_city;
			}

			if ( $user->billing_state ) {
				$user_data['State'] = $user->billing_state;
			}

			if ( $user->billing_country ) {
				$user_data['Country'] = $user->billing_country;
			}
		} else {
			$user_data = [];
		}

		return $user_data;
	}

	/**
	 * Gets count of all orders in last 12 months
	 *
	 * @return mixed
	 */
	public function get_order_count() {
		$args = array(
			'status'       => array( 'processing', 'completed' ),
			'type'         => 'shop_order',
			'date_created' => '>' . date( 'Y-m-d', strtotime( '-3 month' ) ),
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
	 *
	 * @return array
	 * @throws Exception - When WC_Data_Store validation fails.
	 */
	public function get_orders( $current_page, $page_size ) {

		global $wpdb;

		$order_data  = array();
		$page_offset = $page_size * ( $current_page - 1 );

		$result = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts 
		            WHERE post_type = 'shop_order'
           			AND post_status IN (%s,%s)
		            AND post_date > NOW() - INTERVAL 3 MONTH
					ORDER BY post_date DESC
					LIMIT %d
					OFFSET %d
		        ",
				array( 'wc-processing', 'wc-completed', $page_size, $page_offset )
			),
			ARRAY_A
		);

		foreach ( $result as $order_id ) {
			$order = wc_get_order( $order_id['ID'] );
			$this->log_debug( 'order', 'Processing order ' . $order_id['ID'] );
			/** WooCommerce Order Class. @var WC_Order $order */
			$order_lines = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				/** WooCommerce Order Item Class. @var WC_Order_Item $item */
				$email = $order->get_billing_email();
				if ( $order->get_user_id() ) {
					$user = get_userdata( $order->get_user_id() );
					if ( $user ) {
						$email = $user->user_email;
					}
				}
				$unit_price = $order->get_item_total( $item, false, false );
				$line_price = $order->get_item_subtotal( $item, true, false );
				if ( $unit_price > 0 && $line_price > 0 && $item->get_product_id() && ceil( $item['qty'] ) > 0 ) {
					$order_lines[] = array(
						'OrderID'   => $order->get_id(),
						'UserId'    => $order->get_user_id() ? $order->get_user_id() : '',
						'Email'     => $email,
						'DateTime'  => (string) $order->get_date_created( 'c' ),
						'ProdCode'  => $item->get_product_id(),
						'Quantity'  => ceil( $item['qty'] ),
						'UnitPrice' => wc_format_decimal( $unit_price ),
						'LinePrice' => wc_format_decimal( $line_price ),
					);
				} else {
					$this->log_debug( 'order', 'Skipping order item on order ' . $order->get_id() . ' due to missing unit price / product id / quantity' );
				}
			}
			if ( ! empty( $order_lines ) ) {
				$order_data[] = $order_lines;
			} else {
				$this->log_debug( 'order', 'Skipping order ' . $order->get_id() . ' due to no order items' );
			}
		}

		return $order_data;
	}

	/**
	 * Removes protocol from the provided url
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

	/**
	 * Logs an error using WooCommerce Logging.
	 *
	 * @param string $type - feed type.
	 * @param string $message - error message.
	 */
	private function log_error( $type, $message ) {
		$logger = wc_get_logger();
		if ( $logger ) {
			$logger->error( "PureClarity {$type} feed error: {$message}" );
		}
	}

	/**
	 * Logs an error using WooCommerce Logging.
	 *
	 * @param string $type - feed type.
	 * @param string $message - error message.
	 */
	private function log_debug( $type, $message ) {
		if ( $this->settings->is_feed_logging_enabled() ) {
			$logger = wc_get_logger();
			if ( $logger ) {
				$logger->debug( "PureClarity {$type} feed debug: {$message}" );
			}
		}
	}
}
