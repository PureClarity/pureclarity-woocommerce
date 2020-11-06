<?php
/**
 * PureClarity_Class_Loader class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Handles class loading for PureClarity classes
 */
class PureClarity_Class_Loader {

	/**
	 * PureClarity Public class
	 *
	 * @var PureClarity_Public $public
	 */
	private $public;

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
	 * PureClarity Session class
	 *
	 * @var PureClarity_Session $session
	 */
	private $session;

	/**
	 * PureClarity Order class
	 *
	 * @var PureClarity_Order $order
	 */
	private $order;

	/**
	 * PureClarity Delta class
	 *
	 * @var PureClarity_Delta $delta
	 */
	private $delta;

	/**
	 * PureClarity Cron class
	 *
	 * @var PureClarity_Cron $cron
	 */
	private $cron;

	/**
	 * PureClarity Cron Deltas class
	 *
	 * @var PureClarity_Cron_Deltas $cron_deltas
	 */
	private $cron_deltas;

	/**
	 * PureClarity Cron Feeds class
	 *
	 * @var PureClarity_Cron_Feeds $cron_feeds
	 */
	private $cron_feeds;

	/**
	 * PureClarity Cron Signup class
	 *
	 * @var PureClarity_Cron_Signup $cron_signup
	 */
	private $cron_signup;

	/**
	 * PureClarity Admin class
	 *
	 * @var PureClarity_Admin $admin
	 */
	private $admin;

	/**
	 * PureClarity Admin Dashboard Page class
	 *
	 * @var PureClarity_Dashboard_Page $admin_dashboard_page
	 */
	private $admin_dashboard_page;

	/**
	 * PureClarity Admin Settings Page class
	 *
	 * @var PureClarity_Settings_Page $admin_settings_page
	 */
	private $admin_settings_page;

	/**
	 * PureClarity Admin Signup class
	 *
	 * @var PureClarity_Signup $admin_signup
	 */
	private $admin_signup;

	/**
	 * PureClarity Admin Feedback class
	 *
	 * @var PureClarity_Feedback $admin_feedback
	 */
	private $admin_feedback;

	/**
	 * PureClarity Admin Feeds class
	 *
	 * @var PureClarity_Feeds $admin_feeds
	 */
	private $admin_feeds;

	/**
	 * PureClarity State Manager class
	 *
	 * @var PureClarity_State_Manager $state_manager
	 */
	private $state_manager;

	/**
	 * PureClarity Delta Manager class
	 *
	 * @var PureClarity_Delta_Manager $delta_manager
	 */
	private $delta_manager;

	/**
	 * PureClarity Admin Feeds class
	 *
	 * @var PureClarity_Feed_Status $feed_status
	 */
	private $feed_status;

	/**
	 * PureClarity Products Watcher class
	 *
	 * @var PureClarity_Products_Watcher $products_watcher
	 */
	private $products_watcher;

	/**
	 * PureClarity Database class
	 *
	 * @var PureClarity_Database $database
	 */
	private $database;

	/**
	 * Returns the PureClarity_Public class
	 *
	 * @return PureClarity_Public
	 */
	public function get_public() {
		if ( is_null( $this->public ) ) {
			$this->require_public_class( 'public' );
			$this->public = new PureClarity_Public(
				$this->get_settings(),
				$this->get_configuration_display(),
				$this->get_bmz()
			);
		}
		return $this->public;
	}

	/**
	 * Returns the PureClarity_Configuration_Display class
	 *
	 * @return PureClarity_Configuration_Display
	 */
	public function get_configuration_display() {
		if ( is_null( $this->configuration_display ) ) {
			$this->require_public_class( 'configuration-display' );
			$this->configuration_display = new PureClarity_Configuration_Display(
				$this->get_settings(),
				$this->get_state()
			);
		}
		return $this->configuration_display;
	}

	/**
	 * Returns the PureClarity_Settings class
	 *
	 * @return PureClarity_Settings
	 */
	public function get_settings() {
		if ( is_null( $this->settings ) ) {
			$this->require_class( 'settings' );
			$this->settings = new PureClarity_Settings();
		}
		return $this->settings;
	}

	/**
	 * Returns the PureClarity_Feed class
	 *
	 * @return PureClarity_Feed
	 */
	public function get_feed() {
		if ( is_null( $this->feed ) ) {
			$this->require_feeds_class( 'feed' );
			$this->feed = new PureClarity_Feed(
				$this->get_settings(),
				$this->get_state_manager()
			);
		}
		return $this->feed;
	}

	/**
	 * Returns the PureClarity_Session class
	 *
	 * @return PureClarity_Session
	 */
	public function get_state() {
		if ( is_null( $this->session ) ) {
			$this->require_class( 'session' );
			$this->session = new PureClarity_Session(
				$this->get_order()
			);
		}
		return $this->session;
	}

	/**
	 * Returns the PureClarity_Order class
	 *
	 * @return PureClarity_Order
	 */
	public function get_order() {
		if ( is_null( $this->order ) ) {
			$this->require_class( 'order' );
			$this->order = new PureClarity_Order();
		}
		return $this->order;
	}

	/**
	 * Returns the PureClarity_Delta class
	 *
	 * @return PureClarity_Delta
	 */
	public function get_delta() {
		if ( is_null( $this->delta ) ) {
			$this->require_class( 'delta' );
			$this->delta = new PureClarity_Delta(
				$this->get_delta_manager(),
				$this->get_state_manager()
			);
		}
		return $this->delta;
	}

	/**
	 * Returns the PureClarity_Bmz class
	 *
	 * @return PureClarity_Bmz
	 */
	public function get_bmz() {
		if ( is_null( $this->bmz ) ) {
			$this->require_public_class( 'bmz' );
			$this->bmz = new PureClarity_Bmz(
				$this->get_settings(),
				$this->get_state()
			);
		}
		return $this->bmz;
	}

	/**
	 * Returns the PureClarity_Cron class
	 *
	 * @return PureClarity_Cron
	 */
	public function get_cron() {
		if ( is_null( $this->cron ) ) {
			$this->require_class( 'cron' );
			$this->cron = new PureClarity_Cron(
				$this->get_settings(),
				$this->get_cron_deltas(),
				$this->get_cron_feeds(),
				$this->get_cron_signup()
			);
		}
		return $this->cron;
	}

	/**
	 * Returns the PureClarity_Cron_Deltas class
	 *
	 * @return PureClarity_Cron_Deltas
	 */
	public function get_cron_deltas() {
		if ( is_null( $this->cron_deltas ) ) {
			$this->require_cron_class( 'deltas' );
			$this->cron_deltas = new PureClarity_Cron_Deltas(
				$this->get_settings(),
				$this->get_feed(),
				$this->get_delta()
			);
		}
		return $this->cron_deltas;
	}

	/**
	 * Returns the PureClarity_Cron_Feeds class
	 *
	 * @return PureClarity_Cron_Feeds
	 */
	public function get_cron_feeds() {
		if ( is_null( $this->cron_feeds ) ) {
			$this->require_cron_class( 'feeds' );
			$this->cron_feeds = new PureClarity_Cron_Feeds(
				$this->get_settings(),
				$this->get_feed(),
				$this->get_state_manager()
			);
		}
		return $this->cron_feeds;
	}

	/**
	 * Returns the PureClarity_Cron_Signup class
	 *
	 * @return PureClarity_Cron_Signup
	 */
	public function get_cron_signup() {
		if ( is_null( $this->cron_signup ) ) {
			$this->require_cron_class( 'signup' );
			$this->cron_signup = new PureClarity_Cron_Signup(
				$this->get_admin_signup()
			);
		}
		return $this->cron_signup;
	}

	/**
	 * Returns the PureClarity_Admin class
	 *
	 * @return PureClarity_Admin
	 */
	public function get_admin() {
		if ( is_null( $this->admin ) ) {
			$this->require_admin_class( 'admin' );
			$this->admin = new PureClarity_Admin(
				$this->get_admin_dashboard_page(),
				$this->get_admin_settings_page(),
				$this->get_admin_feeds(),
				$this->get_admin_signup(),
				$this->get_admin_feedback(),
				$this->get_settings()
			);
		}
		return $this->admin;
	}

	/**
	 * Returns the PureClarity_Products_Watcher class
	 *
	 * @return PureClarity_Products_Watcher
	 */
	public function get_products_watcher() {
		if ( is_null( $this->products_watcher ) ) {
			$this->require_watcher_class( 'products-watcher' );
			$this->products_watcher = new PureClarity_Products_Watcher(
				$this->get_settings(),
				$this->get_state(),
				$this->get_delta()
			);
		}
		return $this->products_watcher;
	}

	/**
	 * Returns the PureClarity_Dashboard_Page class
	 *
	 * @return PureClarity_Dashboard_Page
	 */
	public function get_admin_dashboard_page() {
		if ( is_null( $this->admin_dashboard_page ) ) {
			$this->require_admin_class( 'dashboard-page' );
			$this->admin_dashboard_page = new PureClarity_Dashboard_Page(
				$this->get_settings(),
				$this->get_state_manager(),
				$this->get_feed_status()
			);
		}
		return $this->admin_dashboard_page;
	}

	/**
	 * Returns the PureClarity_Settings_Page class
	 *
	 * @return PureClarity_Settings_Page
	 */
	public function get_admin_settings_page() {
		if ( is_null( $this->admin_settings_page ) ) {
			$this->require_admin_class( 'settings-page' );
			$this->admin_settings_page = new PureClarity_Settings_Page(
				$this->get_settings()
			);
		}
		return $this->admin_settings_page;
	}

	/**
	 * Returns the PureClarity_Feeds class
	 *
	 * @return PureClarity_Feeds
	 */
	public function get_admin_feeds() {
		if ( is_null( $this->admin_feeds ) ) {
			$this->require_admin_class( 'feeds' );
			$this->admin_feeds = new PureClarity_Feeds(
				$this->get_state_manager(),
				$this->get_feed_status()
			);
		}
		return $this->admin_feeds;
	}

	/**
	 * Returns the PureClarity_Signup class
	 *
	 * @return PureClarity_Signup
	 */
	public function get_admin_signup() {
		if ( is_null( $this->admin_signup ) ) {
			$this->require_admin_class( 'signup' );
			$this->admin_signup = new PureClarity_Signup(
				$this->get_state_manager()
			);
		}
		return $this->admin_signup;
	}

	/**
	 * Returns the PureClarity_Feedback class
	 *
	 * @return PureClarity_Feedback
	 */
	public function get_admin_feedback() {
		if ( is_null( $this->admin_feedback ) ) {
			$this->require_admin_class( 'feedback' );
			$this->admin_feedback = new PureClarity_Feedback(
				$this->get_settings()
			);
		}
		return $this->admin_feedback;
	}

	/**
	 * Returns the PureClarity_State_Manager class
	 *
	 * @return PureClarity_State_Manager
	 */
	public function get_state_manager() {
		if ( is_null( $this->state_manager ) ) {
			$this->require_data_manager_class( 'state' );
			$this->state_manager = new PureClarity_State_Manager();
		}
		return $this->state_manager;
	}

	/**
	 * Returns the PureClarity_Delta_Manager class
	 *
	 * @return PureClarity_Delta_Manager
	 */
	public function get_delta_manager() {
		if ( is_null( $this->delta_manager ) ) {
			$this->require_data_manager_class( 'delta' );
			$this->delta_manager = new PureClarity_Delta_Manager();
		}
		return $this->delta_manager;
	}

	/**
	 * Returns the PureClarity_Feed_Status class
	 *
	 * @return PureClarity_Feed_Status
	 */
	public function get_feed_status() {
		if ( is_null( $this->feed_status ) ) {
			$this->require_feeds_class( 'feed-status' );
			$this->feed_status = new PureClarity_Feed_Status(
				$this->get_state_manager()
			);
		}
		return $this->feed_status;
	}

	/**
	 * Returns the PureClarity_Database class
	 *
	 * @return PureClarity_Database
	 */
	public function get_database() {
		if ( is_null( $this->database ) ) {
			$this->require_class( 'database' );
			$this->database = new PureClarity_Database();
		}
		return $this->database;
	}

	/**
	 * Requires a class from the given name & subfolder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 * @param string $subfolder - any subfolder to include in the require command.
	 */
	private function require_class( $class_name, $subfolder = '' ) {
		$path = PURECLARITY_INCLUDES_PATH;
		if ( $subfolder ) {
			$path .= $subfolder . DIRECTORY_SEPARATOR;
		}
		require_once $path . 'class-pureclarity-' . $class_name . '.php';
	}

	/**
	 * Requires a class in the includes/public folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_public_class( $class_name ) {
		$this->require_class( $class_name, 'public' );
	}

	/**
	 * Requires a class in the includes/feeds folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_feeds_class( $class_name ) {
		$this->require_class( $class_name, 'feeds' );
	}

	/**
	 * Requires a class in the includes/cron folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_cron_class( $class_name ) {
		$this->require_class( 'cron-' . $class_name, 'cron' );
	}

	/**
	 * Requires a class in the includes/data-managers folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_data_manager_class( $class_name ) {
		$this->require_class( $class_name . '-manager', 'data-managers' );
	}

	/**
	 * Requires a class in the includes/admin folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_admin_class( $class_name ) {
		$this->require_class( $class_name, 'admin' );
	}

	/**
	 * Requires a class in the includes/watchers folder
	 *
	 * @param string $class_name - the class name part of the filename to require.
	 */
	private function require_watcher_class( $class_name ) {
		$this->require_class( $class_name, 'watchers' );
	}
}
