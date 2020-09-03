<?php
/**
 * PureClarity_Configuration_Display class
 *
 * @package PureClarity for WooCommerce
 */

/**
 * Renders config json string for use with PureClarity Javascript
 */
class PureClarity_Configuration_Display {

	/**
	 * PureClarity config data
	 *
	 * @var mixed[] $config
	 */
	private $config;

	/**
	 * PureClarity cart config data
	 *
	 * @var mixed[] $cart_config
	 */
	private $cart_config;
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
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings $settings - PureClarity Settings class.
	 * @param PureClarity_State    $state - PureClarity State class.
	 */
	public function __construct(
		$settings,
		$state
	) {
		$this->settings = $settings;
		$this->state    = $state;
	}

	public function init() {
		if ( ! is_ajax() ) {
			add_filter(
				'wp_loaded',
				array(
					$this,
					'build_cart_config',
				)
			);

			add_filter(
				'wp_head',
				array(
					$this,
					'render_pureclarity_json',
				)
			);
		}
	}

	/**
	 * Renders configuration json
	 */
	public function render_pureclarity_json() {
		$script = '<script type="text/javascript">window.pureclarityConfig = ' . wp_json_encode( $this->get_config() ) . ';</script>';
		echo wp_kses(
			$script,
			array(
				'script' => array(
					'type' => array(),
				),
			)
		);
	}

	/**
	 * Gets PureClarity configuration
	 *
	 * @return array
	 */
	private function get_config() {
		if ( empty( $this->config ) ) {
			$this->config = array(
				'enabled'    => $this->is_pureclarity_active(),
				'product'    => $this->state->get_product(),
				'categoryId' => ( is_shop() ? '*' : $this->state->get_category_id() ),
				'page_view'  => $this->state->get_page_view_context(),
				'tracking'   => array(
					'accessKey' => $this->settings->get_access_key(),
					'apiUrl'    => $this->settings->get_api_url(),
					'customer'  => $this->state->get_customer(),
					'islogout'  => $this->state->is_logout(),
					'order'     => $this->state->get_order(),
					'cart'      => $this->cart_config,
				),
			);
		}
		return $this->config;
	}

	/**
	 * Gets PureClarity configuration
	 */
	public function build_cart_config() {
		$this->cart_config   = $this->state->get_cart();
	}

	/**
	 * Returns whether PureClarity is active
	 *
	 * @return boolean
	 */
	private function is_pureclarity_active() {
		return ( $this->settings->get_access_key() !== '' )
			&& $this->settings->is_pureclarity_enabled();
	}

}
