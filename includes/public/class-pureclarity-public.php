<?php
/**
 * PureClarity_Public class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
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
	 * PureClarity Configuration Display class
	 *
	 * @var PureClarity_Configuration_Display $configuration_display
	 */
	private $configuration_display;

	/**
	 * PureClarity Bmz class
	 *
	 * @var PureClarity_Bmz $bmz
	 */
	private $bmz;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings              $settings - PureClarity Settings class.
	 * @param PureClarity_Configuration_Display $configuration_display - PureClarity Configuration Display class.
	 * @param PureClarity_Bmz                   $bmz - PureClarity Zone class.
	 */
	public function __construct(
		$settings,
		$configuration_display,
		$bmz
	) {
		$this->settings              = $settings;
		$this->configuration_display = $configuration_display;
		$this->bmz                   = $bmz;
	}

	/**
	 * Initialises frontend if enabled
	 */
	public function init() {
		if ( $this->settings->is_pureclarity_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
			$this->configuration_display->init();
		}
		$this->bmz->init();
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
