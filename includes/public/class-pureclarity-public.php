<?php
/**
 * PureClarity_Public class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles public display & actions code
 */
class PureClarity_Public {

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * PureClarity Template class
	 *
	 * @var PureClarity_Template $template
	 */
	private $template;

	/**
	 * Builds class dependencies & sets up admin actions
	 *
	 * @param PureClarity_Settings $settings - PureClarity Settings class.
	 * @param PureClarity_Template $template - PureClarity Plugin class.
	 */
	public function __construct(
		$settings,
		$template
	) {
		$this->settings = $settings;
		$this->template = $template;
	}

	/**
	 * Initialises frontend if enabled
	 */
	public function init() {
		if ( $this->settings->is_pureclarity_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
			$this->template->init();
		}
	}

	/**
	 * Registers PureClarity CSS & JS
	 */
	public function register_assets() {
		wp_register_style( 'pureclarity-css', PURECLARITY_BASE_URL . 'public/css/pc.css', array(), PURECLARITY_VERSION, 'screen' );
		wp_enqueue_style( 'pureclarity-css' );

		wp_register_script( 'pureclarity-js', PURECLARITY_BASE_URL . 'public/js/pc.js', array( 'jquery', 'wp-util' ), PURECLARITY_VERSION, true );
		wp_enqueue_script( 'pureclarity-js' );
	}
}
