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
	 * @var $currentCategoryId integer
	 */
	private $currentCategoryId;

	/**
	 * Current Product
	 *
	 * @since 2.0.0
	 * @var integer $currentCategoryId
	 */
	private $currentProduct;

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
	 * PureClarity State class
	 *
	 * @since 2.0.0
	 * @var PureClarity_State $state
	 */
	private $state;

	/**
	 * Builds class dependencies & sets up template codes
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin   = $plugin;
		$this->settings = $plugin->get_settings();
		$this->state    = $plugin->get_state();
		add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_bmz' ) );
		add_action( 'template_redirect', array( $this, 'render_bmzs' ), 10, 1 );
	}

	/**
	 * Sets up rendering of BMZs
	 */
	public function render_bmzs() {

		$this->currentProduct    = $this->state->get_product();
		$this->currentCategoryId = $this->state->get_category_id();

		// Homepage and Order Received Page BMZs.
		if ( is_front_page() && $this->settings->is_bmz_on_home_page() ) {
			add_filter(
				'the_content',
				array(
					$this,
					'front_page',
				)
			);
		}

		// Category Page BMZs.
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

		// Search Results BMZs.
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

		// Product Page BMZs.
		if ( is_product() && $this->settings->is_bmz_on_product_page() ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
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

		// Cart Page BMZs.
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
		}

		// Order Received Page BMZs.
		if ( is_order_received_page() && $this->settings->is_bmz_on_checkout_page() ) {
			add_filter(
				'the_content',
				array(
					$this,
					'order_received_page',
				)
			);
		}

	}

	/**
	 * Sets up front page content
	 *
	 * @param string $content - existing content.
	 */
	public function front_page( $content ) {
		return "[pureclarity-bmz id='HP-01' bottom='10']" . $content . "[pureclarity-bmz id='HP-02' top='10'][pureclarity-bmz id='HP-03' top='10'][pureclarity-bmz id='HP-04' top='10']";
	}

	/**
	 * Sets up order received page content
	 *
	 * @param string $content - existing content.
	 */
	public function order_received_page( $content ) {
		return "[pureclarity-bmz id='OC-01' bottom='10']" . $content . "[pureclarity-bmz id='OC-02' top='10']";
	}

	/**
	 * Sets up product page zone 1 content
	 */
	public function product_page_1() {
		return $this->pureclarity_render_bmz(
			array(
				'id'     => 'PP-01',
				'bottom' => '10',
				'echo'   => true,
			)
		);
	}

	/**
	 * Sets up product page zone 2 content
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
	 * Renders a zone
	 *
	 * @param array  $atts - zone attributes.
	 * @param string $content - custom content.
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

			if ( $this->settings->is_bmz_debug_enabled() && $html == '' ) {
				$html = 'PURECLARITY BMZ: ' . $arguments['id'];
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
			if ( ! empty( $this->currentProduct ) ) {
				$data = 'id:' . $this->currentProduct['id'];
			} elseif ( ! empty( $this->currentCategoryId ) ) {
				$data = 'categoryid:' . $this->currentCategoryId;
			}

			$bmz = "<div class='" . $class . "' style='" . $style . "' data-pureclarity='bmz:" . $arguments['id'] . ';' . $data . "'>" . $html . "</div><div class='pureclarity_bmz_clearfix'></div>";
			if ( $arguments['echo'] == true ) {
				echo $bmz;
			} else {
				return $bmz;
			}
		}

		return '';

	}

}
