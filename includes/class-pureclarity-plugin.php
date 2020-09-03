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
	 * PureClarity_Class_Loader class
	 *
	 * @var PureClarity_Class_Loader $loader
	 */
	private $loader;

	/**
	 * Sets up dependencies and adds some init actions
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 15 );
	}

	/**
	 * Sets up dependencies
	 */
	public function load_dependencies() {
		require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-class-loader.php';
		$this->loader = new PureClarity_Class_Loader();
	}

	/**
	 * Initializes the plugin
	 */
	public function init() {
		$this->load_dependencies();
		if ( is_admin() ) {
			$admin = $this->loader->get_admin();
			$admin->init();
		} elseif ( defined( 'DOING_CRON' ) ) {
			$cron = $this->loader->get_cron();
			$cron->init();
		} elseif ( ! wp_doing_ajax() ) {
			$public = $this->loader->get_public();
			$public->init();
		}

		$watcher = $this->loader->get_products_watcher();
		$watcher->init();
	}

}
