<?php
/**
 * PureClarity_Bmz class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles bmz rendering
 */
class PureClarity_Bmz {

	/**
	 * Current Category ID
	 *
	 * @since 2.0.0
	 * @var $current_category_id integer
	 */
	private $current_category_id;

	/**
	 * Current Product
	 *
	 * @since 2.0.0
	 * @var integer $current_product
	 */
	private $current_product;

	/**
	 * Whether the order received zones were added through the page content
	 *
	 * @var bool $order_received_zones_in_content
	 */
	private $order_received_zones_in_content = false;

	/**
	 * Whether the order received zones were added around the Order Confirmation blocks
	 *
	 * @var bool $order_received_zones_in_blocks
	 */
	private $order_received_zones_in_blocks = false;

	/**
	 * PureClarity Settings class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Session class
	 *
	 * @since 2.0.0
	 * @var PureClarity_Session $session
	 */
	private $session;

	/**
	 * Builds class dependencies & sets up template codes
	 *
	 * @param PureClarity_Settings $settings - PureClarity Settings class.
	 * @param PureClarity_Session  $session - PureClarity Session class.
	 */
	public function __construct(
		$settings,
		$session
	) {
		$this->settings = $settings;
		$this->session  = $session;
	}

	/**
	 * Initialisation - adds zone shortcode & template injection hooks.
	 */
	public function init() {
		if ( $this->settings->is_pureclarity_enabled() ) {
			add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_bmz' ) );
			add_action( 'template_redirect', array( $this, 'render_bmzs' ), 10, 1 );
		} else {
			add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_empty_bmz' ) );
		}
	}

	/**
	 * Sets up hooks for rendering of Zones
	 */
	public function render_bmzs() {

		$this->current_product     = $this->session->get_product();
		$this->current_category_id = $this->session->get_category_id();

		// Homepage and Order Received Page Zones.
		if ( is_front_page() && $this->settings->is_bmz_on_home_page() ) {
			add_filter(
				'the_content',
				array(
					$this,
					'front_page',
				)
			);
		}

		// Category Page Zones.
		if ( ( is_product_category() || ( is_shop() && ! is_search() ) )
				&& $this->settings->is_bmz_on_category_page()
		) {
			add_action(
				'woocommerce_before_main_content',
				array(
					$this,
					'cat_page_1',
				),
				10
			);
			add_action(
				'woocommerce_after_main_content',
				array(
					$this,
					'cat_page_2',
				),
				10
			);
		}

		// Search Results Zones.
		if ( is_search()
			&& $this->settings->is_bmz_on_search_page()
		) {
			add_action(
				'woocommerce_before_main_content',
				array(
					$this,
					'search_page_1',
				),
				10
			);
			add_action(
				'woocommerce_after_main_content',
				array(
					$this,
					'search_page_2',
				),
				10
			);
		}

		// Product Page Zones.
		if ( is_product() && $this->settings->is_bmz_on_product_page() ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
			// Block themes show related products with blocks instead of the action above.
			add_filter( 'render_block_woocommerce/product-collection', array( $this, 'remove_related_products_block' ), 10, 2 );
			add_filter( 'render_block_woocommerce/related-products', '__return_empty_string' );
			// In block themes, put zone 1 after the store notices, where the classic template has it.
			add_filter( 'render_block_woocommerce/store-notices', array( $this, 'product_page_1_block' ) );
			add_action(
				'woocommerce_before_single_product',
				array(
					$this,
					'product_page_1',
				),
				10
			);
			add_action(
				'woocommerce_product_meta_end',
				array(
					$this,
					'product_page_2',
				),
				10
			);
			add_action(
				'woocommerce_after_single_product_summary',
				array(
					$this,
					'product_page_3',
				),
				10
			);
			add_action(
				'woocommerce_after_single_product',
				array(
					$this,
					'product_page_4',
				),
				10
			);
		}

		// Cart Page Zones.
		if ( is_cart() && $this->settings->is_bmz_on_basket_page() ) {
			add_action(
				'woocommerce_before_cart',
				array(
					$this,
					'cart_page_1',
				),
				10
			);
			add_action(
				'woocommerce_after_cart',
				array(
					$this,
					'cart_page_2',
				),
				10
			);
			// The Cart block does not run the classic cart template hooks.
			add_filter(
				'render_block_woocommerce/cart',
				array(
					$this,
					'cart_block',
				)
			);
		}

		// Order Received Page Zones.
		if ( is_order_received_page() && $this->settings->is_bmz_on_checkout_page() ) {
			add_filter(
				'the_content',
				array(
					$this,
					'order_received_page',
				)
			);
			// Block themes render the order confirmation with blocks and no page content.
			add_filter( 'render_block_woocommerce/order-confirmation-status', array( $this, 'order_confirmation_block_1' ) );
			add_filter( 'render_block_woocommerce/order-confirmation-additional-information', array( $this, 'order_confirmation_block_2' ) );
		}

	}

	/**
	 * Sets up front page content
	 *
	 * @param string $content - existing content.
	 * @return string
	 */
	public function front_page( $content ) {
		return "[pureclarity-bmz id='HP-01' bottom='10']" . $content . "[pureclarity-bmz id='HP-02' top='10'][pureclarity-bmz id='HP-03' top='10'][pureclarity-bmz id='HP-04' top='10']";
	}

	/**
	 * Sets up order received page content
	 *
	 * @param string $content - existing content.
	 * @return string
	 */
	public function order_received_page( $content ) {
		if ( $this->order_received_zones_in_blocks ) {
			return $content;
		}
		$this->order_received_zones_in_content = true;
		return "[pureclarity-bmz id='OC-01' bottom='10']" . $content . "[pureclarity-bmz id='OC-02' top='10']";
	}

	/**
	 * Adds order received zone 1 before the Order Confirmation Status block
	 *
	 * @param string $block_content - rendered block.
	 * @return string
	 */
	public function order_confirmation_block_1( $block_content ) {
		if ( $this->order_received_zones_in_content ) {
			return $block_content;
		}
		$this->order_received_zones_in_blocks = true;
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'OC-01',
				'bottom' => '10',
			)
		) . $block_content;
	}

	/**
	 * Adds order received zone 2 after the Order Confirmation Additional Information block
	 *
	 * @param string $block_content - rendered block.
	 * @return string
	 */
	public function order_confirmation_block_2( $block_content ) {
		if ( $this->order_received_zones_in_content ) {
			return $block_content;
		}
		$this->order_received_zones_in_blocks = true;
		return $block_content . $this->pureclarity_render_bmz(
			array(
				'id'  => 'OC-02',
				'top' => '10',
			)
		);
	}

	/**
	 * Removes the related products Product Collection block, as the product page zones replace it
	 *
	 * @param string $block_content - rendered block.
	 * @param array  $block - parsed block.
	 * @return string
	 */
	public function remove_related_products_block( $block_content, $block ) {
		if ( isset( $block['attrs']['collection'] ) && 'woocommerce/product-collection/related' === $block['attrs']['collection'] ) {
			return '';
		}
		return $block_content;
	}

	/**
	 * Sets up product page zone 1 content
	 *
	 * @return string
	 */
	public function product_page_1() {
		// WooCommerce's block template compatibility layer fires this hook from a render_block filter on the
		// template's first block, which is the site header, so block themes get zone 1 from product_page_1_block.
		if ( doing_filter( 'render_block' ) ) {
			return '';
		}
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'PP-01',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Adds product page zone 1 after the Store Notices block
	 *
	 * @param string $block_content - rendered block.
	 * @return string
	 */
	public function product_page_1_block( $block_content ) {
		return $block_content . $this->pureclarity_render_bmz(
			array(
				'id'     => 'PP-01',
				'bottom' => '10',
			)
		);
	}

	/**
	 * Sets up product page zone 2 content
	 *
	 * @return string
	 */
	public function product_page_2() {
		return $this->pureclarity_render_bmz(
			array(
				'id'   => 'PP-02',
				'top'  => '10',
				'echo' => true,
			)
		);
	}

	/**
	 * Sets up product page zone 3 content
	 *
	 * @return string
	 */
	public function product_page_3() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'PP-03',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up product page zone 4 content
	 *
	 * @return string
	 */
	public function product_page_4() {
		return $this->pureclarity_render_bmz(
			array(
				'id'   => 'PP-04',
				'top'  => '10',
				'echo' => true,
			)
		);
	}

	/**
	 * Sets up category page zone 1 content
	 *
	 * @return string
	 */
	public function cat_page_1() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'PL-01',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up category page zone 2 content
	 *
	 * @return string
	 */
	public function cat_page_2() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'PL-02',
				'top'    => '10',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up search page zone 1 content
	 *
	 * @return string
	 */
	public function search_page_1() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'SR-01',
				'top'    => '10',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up search page zone 2 content
	 *
	 * @return string
	 */
	public function search_page_2() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'SR-02',
				'top'    => '10',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up cart page zone 1 content
	 *
	 * @return string
	 */
	public function cart_page_1() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'BP-01',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up cart page zone 2 content
	 *
	 * @return string
	 */
	public function cart_page_2() {
		return $this->pureclarity_render_bmz(
			array(
				'id'   => 'BP-02',
				'top'  => '10',
				'echo' => true,
			)
		);
	}

	/**
	 * Adds cart page zones around the Cart block
	 *
	 * @param string $block_content - rendered Cart block.
	 * @return string
	 */
	public function cart_block( $block_content ) {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'BP-01',
				'bottom' => '10',
			)
		) . $block_content . $this->pureclarity_render_bmz(
			array(
				'id'  => 'BP-02',
				'top' => '10',
			)
		);
	}

	/**
	 * Renders an empty zone (for when PC is disabled and shortcodes exist).
	 *
	 * @return string
	 */
	public function pureclarity_render_empty_bmz() {
		return '';
	}

	/**
	 * Renders a zone
	 *
	 * @param array       $atts - zone attributes.
	 * @param string|null $content - custom content.
	 *
	 * @return string
	 */
	public function pureclarity_render_bmz( $atts, $content = null ) {

		$arguments = shortcode_atts(
			array(
				'id'     => null,
				'top'    => null,
				'bottom' => null,
				'echo'   => false,
				'class'  => null,
			),
			$atts
		);
		if ( $this->settings->is_pureclarity_enabled()
				&& ! empty( $arguments['id'] )
			) {

			$html = ( ! empty( $content ) ? $content : '' );

			if ( $this->settings->is_bmz_debug_enabled() && '' === $html ) {
				$html = 'PURECLARITY Zone: ' . $arguments['id'];
			}

			$class = 'pureclarity_bmz';
			if ( $this->settings->is_bmz_debug_enabled() ) {
				$class .= ' pureclarity_debug';
			}
			$class .= ' pureclarity_bmz_' . $arguments['id'];
			if ( ! empty( $arguments['class'] ) ) {
				$class .= ' ' . $arguments['class'];
			}

			$style = '';
			if ( ! empty( $arguments['top'] ) ) {
				$style .= 'margin-top:' . $arguments['top'] . 'px;';
			}

			if ( ! empty( $arguments['bottom'] ) ) {
				$style .= 'margin-bottom:' . $arguments['bottom'] . 'px;';
			}

			$data = '';
			if ( ! empty( $this->current_product ) ) {
				$data = 'id:' . $this->current_product['id'];
			} elseif ( ! empty( $this->current_category_id ) ) {
				$data = 'categoryid:' . $this->current_category_id;
			}

			$bmz = "<div class='" . $class . "' style='" . $style . "' data-pureclarity='bmz:" . $arguments['id'] . ';' . $data . "'>" . $html . "</div><div class='pureclarity_bmz_clearfix'></div>";
			if ( true === $arguments['echo'] ) {
				echo wp_kses(
					$bmz,
					array(
						'div' => array(
							'class'            => array(),
							'style'            => array(),
							'data-pureclarity' => array(),
						),
					)
				);
			} else {
				return $bmz;
			}
		}

		return '';
	}
}
