<?php
/**
 * PureClarity_Dashboard_Page class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Feed\Feed;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Resource\Timezones;

/**
 * Handles admin display & actions code
 */
class PureClarity_Dashboard_Page {

	public const STATE_NOT_CONFIGURED = 'not_configured';
	public const STATE_WAITING        = 'waiting';
	public const STATE_CONFIGURED     = 'configured';

	/**
	 * Stats to show array.
	 *
	 * @var string[] - array of stat keys to show in performance box.
	 */
	private $stats_to_show = array(
		'Impressions'                    => 'Impressions',
		'Sessions'                       => 'Sessions',
		'ConvertedSessions'              => 'Converted Sessions',
		'ConversionRate'                 => 'Conversion Rate',
		'SalesTotalDisplay'              => 'Sales Total',
		'OrderCount'                     => 'Orders',
		'RecommenderProductTotalDisplay' => 'Recommender Product Total',
	);

	/**
	 * Stats that should be shown as percentages.
	 *
	 * @var string[] - array of stats that should be shown as percentages.
	 */
	private $stat_percentage = array(
		'ConvertedSessions',
		'ConversionRate',
	);

	/**
	 * Cache of "new version"
	 *
	 * @var string $new_version
	 */
	private $new_version;

	/**
	 * Dashboard info from PureClarity
	 *
	 * @var mixed[] $dashboard_info
	 */
	private $dashboard_info;

	/**
	 * Flag to denote if the plugin is configured or not.
	 *
	 * @var bool $is_not_configured
	 */
	private $is_not_configured;

	/**
	 * Flag to denote if a signup has started.
	 *
	 * @var bool $signup_started
	 */
	private $signup_started;

	/**
	 * State manager class - interacts with the pureclarity_state table
	 *
	 * @var PureClarity_State_Manager
	 */
	private $state_manager;

	/**
	 * State manager class - deals with information around feed statuses
	 *
	 * @var PureClarity_Feed_Status $feed_status
	 */
	private $feed_status;

	/**
	 * PureClarity Settings class.
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings      $settings - PureClarity Settings class.
	 * @param PureClarity_State_Manager $state_manager - PureClarity state manager class.
	 * @param PureClarity_Feed_Status   $feed_status - PureClarity feed status class.
	 */
	public function __construct(
		$settings,
		$state_manager,
		$feed_status
	) {
		$this->settings      = $settings;
		$this->state_manager = $state_manager;
		$this->feed_status   = $feed_status;
	}

	/**
	 * Renders settings page
	 */
	public function get_next_steps_content() {
		try {
			$dashboard = $this->get_dasboard_info();
			if ( isset( $dashboard['NextSteps'] ) ) {
				include_once 'views/dashboard/next-steps.php';
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

	}

	public function get_stats_content() {
		try {
			$dashboard = $this->get_dasboard_info();
			if ( isset( $dashboard['Stats'] ) ) {
				include_once 'views/dashboard/stats.php';
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	public function get_stat_display( $key, $value ) {
		if (in_array( $key, $this->stat_percentage )) {
			$value .= '%';
		}
		return $value;
	}

	private function get_stat_title( $type ) {
		$title = '';
		switch ( $type ) {
			case 'today':
				$title = 'Today';
				break;
			case 'last30days':
				$title = 'Last 30 days';
				break;
		}
		return $title;
	}

	/**
	 * Renders settings page
	 */
	public function get_account_status_content() {
		try {
			$dashboard = $this->get_dasboard_info();
			if ( isset( $dashboard['Account'] ) ) {
				include_once 'views/dashboard/account-info.php';
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * Renders settings page
	 */
	public function get_dasboard_info() {
		if ( null === $this->dashboard_info ) {
			try {
				$dashboard = new \PureClarity\Api\Info\Dashboard(
					$this->settings->get_access_key(),
					$this->settings->get_secret_key(),
					(int) $this->settings->get_region()
				);

				$r                    = $dashboard->request();
				$this->dashboard_info = json_decode( $r['body'], true );
			} catch ( \Exception $e ) {
				error_log( $e->getMessage() );
			}
		}

		return $this->dashboard_info;

	}

	/**
	 * Renders the Dashboard page content.
	 */
	public function dashboard_render() {
		include_once 'views/header.php';
		include_once 'views/dashboard-page.php';
	}

	/**
	 * Runs before admin notices action and hides them.
	 */
	public static function inject_before_notices() {

		$whitelist_admin_pages = array(
			'toplevel_page_pureclarity-dashboard',
			'pureclarity_page_pureclarity-settings',
		);
		$admin_page = get_current_screen();

		if ( in_array( $admin_page->base, $whitelist_admin_pages, true ) ) {
			// Wrap the notices in a hidden div to prevent flickering before
			// they are moved elsewhere in the page by WordPress Core.
			echo '<div style="display:none" id="wp__notice-list">';

			// Capture all notices and hide them. WordPress Core looks for
			// `.wp-header-end` and appends notices after it if found.
			// https://github.com/WordPress/WordPress/blob/f6a37e7d39e2534d05b9e542045174498edfe536/wp-admin/js/common.js#L737 .
			echo '<div class="wp-header-end" id="woocommerce-layout__notice-catcher"></div>';
		}
	}

	/**
	 * Runs after admin notices and closes div.
	 */
	public static function inject_after_notices() {

		$admin_page            = get_current_screen();
		$whitelist_admin_pages = array(
			'toplevel_page_pureclarity-dashboard',
			'pureclarity_page_pureclarity-settings',
		);

		if ( in_array( $admin_page->base, $whitelist_admin_pages, true ) ) {
			// Close the hidden div used to prevent notices from flickering before
			// they are inserted elsewhere in the page.
			echo '</div>';
		}
	}

	/**
	 * Returns whether the dashboard should show the not configured state
	 *
	 * @return boolean
	 */
	public function get_state_name() {
		if ( $this->is_not_configured() ) {
			return self::STATE_NOT_CONFIGURED;
		} elseif ( $this->is_waiting() ) {
			return self::STATE_WAITING;
		} else {
			return self::STATE_CONFIGURED;
		}
	}

	/**
	 * Returns whether the dashboard should show the not configured state
	 *
	 * @return boolean
	 */
	public function is_not_configured() {
		return ( true === $this->get_is_not_configured() && false === $this->get_signup_started() );
	}

	/**
	 * Returns whether the dashboard should show the waiting for sign up to finish state
	 *
	 * @return boolean
	 */
	public function is_waiting() {
		return ( true === $this->get_is_not_configured() && true === $this->get_signup_started() );
	}

	/**
	 * Returns whether the PureClarity module is up to date
	 *
	 * @return boolean
	 */
	public function is_up_to_date() {
		$new_version = $this->get_new_version();
		return ( '' === $new_version || version_compare( $new_version, PURECLARITY_VERSION, '<=' ) );
	}

	/**
	 * Returns the current plugin version
	 *
	 * @return string
	 */
	public function get_plugin_version() {
		return PURECLARITY_VERSION;
	}

	/**
	 * Returns the latest version of the plugin available
	 *
	 * @return bool
	 */
	public function get_new_version() {
		if ( null === $this->new_version ) {
			$this->new_version = $this->state_manager->get_state_value( 'new_version' );
		}

		return $this->new_version;
	}

	/**
	 * Returns the current WordPress version
	 *
	 * @return string
	 */
	public function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * Returns the current Woocommerce version
	 *
	 * @return string
	 */
	public function get_woocommerce_version() {
		$version = 'N/A';
		global $woocommerce;
		if ( $woocommerce && $woocommerce->version ) {
			$version = $woocommerce->version;
		}
		return $version;
	}

	/**
	 * Includes the signup dashboard view file
	 */
	public function get_signup_content() {
		include 'views/dashboard/signup.php';
	}

	/**
	 * Includes the configured dashboard view file
	 */
	public function get_configured_content() {
		include 'views/dashboard/configured.php';
	}

	/**
	 * Checks the pureclarity_state table to see if the module is already configured
	 *
	 * @return bool
	 */
	private function get_is_not_configured() {
		if ( null === $this->is_not_configured ) {
			$this->is_not_configured = empty( $this->settings->get_access_key() ) && empty( $this->settings->get_secret_key() );
		}

		return $this->is_not_configured;
	}

	/**
	 * Checks the pureclarity_state table to see if a signup has already been started
	 *
	 * @return bool
	 */
	private function get_signup_started() {
		if ( null === $this->signup_started ) {
			$signup_started       = $this->state_manager->get_state_value( 'signup_request' );
			$this->signup_started = ( false === empty( $signup_started ) );
		}

		return $this->signup_started;
	}

	/**
	 * Gets the stores' name
	 *
	 * @return string
	 */
	public function get_store_name() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Gets the current store URL
	 *
	 * @return string
	 */
	public function get_store_url() {
		return get_site_url();
	}

	/**
	 * Gets the stores' currency
	 *
	 * @return string
	 */
	public function get_store_currency() {
		return get_woocommerce_currency();
	}

	/**
	 * Gets an array of supported timezones from the PureClarity SDK
	 *
	 * @return array
	 */
	public function get_pureclarity_regions() {
		$region_class = new Regions();
		return $region_class->getRegionLabels();
	}

	/**
	 * Gets an array of supported timezones from the PureClarity SDK
	 *
	 * @return array
	 */
	public function get_pureclarity_timezones() {
		$timezones = new Timezones();
		return $timezones->getLabels();
	}

	/**
	 * Returns the class to use for the Product feed status display
	 *
	 * @return string
	 */
	public function get_product_feed_status_class() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_PRODUCT );
		return $feed['class'];
	}

	/**
	 * Returns the label to use for the Product feed status display
	 *
	 * @return string
	 */
	public function get_product_feed_status_label() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_PRODUCT );
		return $feed['label'];
	}

	/**
	 * Returns the class to use for the Category feed status display
	 *
	 * @return string
	 */
	public function get_category_feed_status_class() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_CATEGORY );
		return $feed['class'];
	}

	/**
	 * Returns the label to use for the Category feed status display
	 *
	 * @return string
	 */
	public function get_category_feed_status_label() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_CATEGORY );
		return $feed['label'];
	}

	/**
	 * Returns the class to use for the User feed status display
	 *
	 * @return string
	 */
	public function get_user_feed_status_class() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_USER );
		return $feed['class'];
	}

	/**
	 * Returns the label to use for the User feed status display
	 *
	 * @return string
	 */
	public function get_user_feed_status_label() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_USER );
		return $feed['label'];
	}

	/**
	 * Returns the class to use for the Order feed status display
	 *
	 * @return string
	 */
	public function get_orders_feed_status_class() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_ORDER );
		return $feed['class'];
	}

	/**
	 * Returns the label to use for the Order feed status display
	 *
	 * @return string
	 */
	public function get_orders_feed_status_label() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_ORDER );
		return $feed['label'];
	}

	/**
	 * Returns whether the PureClarity feeds are currently in progress
	 *
	 * @return bool
	 */
	public function get_are_feeds_in_progress() {
		return $this->feed_status->get_are_feeds_in_progress(
			array(
				Feed::FEED_TYPE_PRODUCT,
				Feed::FEED_TYPE_CATEGORY,
				Feed::FEED_TYPE_USER,
				Feed::FEED_TYPE_ORDER,
			)
		);
	}

}
