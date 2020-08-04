<?php
/**
 * PureClarity_Template class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Renders template related code.
 */
class PureClarity_Template {

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
	 * PureClarity Bmz class
	 *
	 * @var PureClarity_Bmz $bmz
	 */
	private $bmz;

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
	 * Builds class dependencies & sets up watchers
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {
		$this->plugin = $plugin;
		$this->bmz    = $this->plugin->get_bmz();
		if ( ! is_ajax() ) {
			if ( ! defined( 'DOING_CRON' ) ) {
				add_filter(
					'wp_loaded',
					array(
						$this,
						'build_cart_config',
					)
				);
			}

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
			$pureclarity_settings = $this->get_pureclarity_plugin_settings();
			$pureclarity_session  = $this->get_pureclarity_plugin()->get_state();
			$this->config         = array(
				'enabled'    => $this->is_pureclarity_active(),
				'product'    => $pureclarity_session->get_product(),
				'categoryId' => ( is_shop() ? '*' : $pureclarity_session->get_category_id() ),
				'page_view'  => $pureclarity_session->get_page_view_context(),
				'tracking'   => array(
					'accessKey' => $pureclarity_settings->get_access_key(),
					'apiUrl'    => $pureclarity_settings->get_api_url(),
					'customer'  => $pureclarity_session->get_customer(),
					'islogout'  => $pureclarity_session->is_logout(),
					'order'     => $pureclarity_session->get_order(),
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
		$pureclarity_session = $this->get_pureclarity_plugin()->get_state();
		$this->cart_config   = $pureclarity_session->get_cart();
	}

	/**
	 * Returns whether PureClarity is active
	 *
	 * @return boolean
	 */
	private function is_pureclarity_active() {
		return ( $this->get_pureclarity_plugin_settings()->get_access_key() !== '' )
			&& $this->get_pureclarity_plugin_settings()->is_pureclarity_enabled();
	}

	/**
	 * Returns an instance of the PureClarity_Plugin class
	 *
	 * @return PureClarity_Plugin
	 */
	private function get_pureclarity_plugin() {
		return $this->plugin;
	}

	/**
	 * Returns the settings class
	 *
	 * @return PureClarity_Settings
	 */
	private function get_pureclarity_plugin_settings() {
		if ( ! isset( $this->settings ) ) {
			$this->settings = $this->get_pureclarity_plugin()->get_settings();
		}
		return $this->settings;
	}

}
