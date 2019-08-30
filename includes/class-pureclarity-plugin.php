<?php
/**
 * PureClarity_Plugin class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles intiliazation code
 */
class PureClarity_Plugin {

	/**
	 * PureClarity Bmz class
	 *
	 * @var PureClarity_Bmz $bmz
	 */
	private $bmz;

	/**
	 * PureClarity Feed class
	 *
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

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
	 * Sets up dependencies and adds some init actions
	 */
	public function __construct() {

		$this->settings = new PureClarity_Settings();

		$this->feed = new PureClarity_Feed( $this );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'init', array( $this, 'init' ), 15 );
	}

	/**
	 * Returns the settings class
	 *
	 * @return PureClarity_Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Returns the feed class
	 *
	 * @return PureClarity_Feed
	 */
	public function get_feed() {
		return $this->feed;
	}

	/**
	 * Returns the state class
	 *
	 * @return PureClarity_State
	 */
	public function get_state() {
		return $this->state;
	}

	/**
	 * Returns the bmz class
	 *
	 * @return PureClarity_Bmz
	 */
	public function get_bmz() {
		return $this->bmz;
	}

	/**
	 * Registers PureClarity CSS & JS
	 */
	public function register_assets() {
		wp_register_style( 'pureclarity-css', plugin_dir_url( __FILE__ ) . '../css/pc.css', array(), PURECLARITY_VERSION, 'screen' );
		wp_enqueue_style( 'pureclarity-css' );

		wp_register_script( 'pureclarity-js', plugin_dir_url( __FILE__ ) . '../js/pc.js', array( 'jquery', 'wp-util' ), PURECLARITY_VERSION, true );
		wp_enqueue_script( 'pureclarity-js' );
	}

	/**
	 * Initializes the plugin
	 */
	public function init() {
		if ( is_admin() ) {
			new PureClarity_Admin( $this );
		} else {
			if ( $this->settings->is_pureclarity_enabled() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
			}
			$this->state = new PureClarity_State( $this );
			$this->bmz   = new PureClarity_Bmz( $this );
			new PureClarity_Template( $this );
		}
		new PureClarity_Products_Watcher( $this );
		new PureClarity_Cron( $this );
	}

}
