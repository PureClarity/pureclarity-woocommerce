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
		echo $script;
	}

	/**
	 * Gets PureClarity configuration
	 *
	 * @return array
	 */
	private function get_config() {
		$pureclarity_settings = $this->get_pureclarity_plugin_settings();
		$pureclarity_session  = $this->get_pureclarity_plugin()->get_state();
		return array(
			'enabled'    => $this->is_pureclarity_active(),
			'product'    => $pureclarity_session->get_product(),
			'categoryId' => ( is_shop() ? '*' : $pureclarity_session->get_category_id() ),
			'tracking'   => array(
				'accessKey' => $pureclarity_settings->get_access_key(),
				'apiUrl'    => $pureclarity_settings->get_api_url(),
				'customer'  => $pureclarity_session->get_customer(),
				'islogout'  => $pureclarity_session->is_logout(),
				'order'     => $pureclarity_session->get_order(),
				'cart'      => $pureclarity_session->get_cart(),
			),
		);
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
