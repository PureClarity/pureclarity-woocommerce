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

		// this just schedules cron, needs to be always run to ensure scheduling happens.
		$cron = $this->loader->get_cron();
		$cron->init();

		// watchers always need to be run, so that we can pick up on any events in frontend / backend.
		$watcher = $this->loader->get_products_watcher();
		$watcher->init();

		if ( is_admin() ) {
			$admin = $this->loader->get_admin();
			$admin->init();
		} elseif ( ! defined( 'DOING_CRON' ) && ! wp_doing_ajax() && ! $this->is_rest_api_request() ) {
			$public = $this->loader->get_public();
			$public->init();
		}
	}

	/**
	 * Detects if this is a WordPress API request, repurposed from WooCommerce's own version of this function.
	 * See https://wordpress.stackexchange.com/a/356946
	 *
	 * @return false|mixed|void
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

		return apply_filters( 'pureclarity_is_rest_api_request', $is_rest_api_request );
	}
}
