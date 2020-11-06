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
 * Handles admin dashboard page display & actions code
 */
class PureClarity_Dashboard_Page {

	/**
	 * Plugin states.
	 */
	const STATE_NOT_CONFIGURED = 'not_configured';
	const STATE_WAITING        = 'waiting';
	const STATE_CONFIGURED     = 'configured';

	/**
	 * Display modes.
	 */
	const MODE_LIVE       = 'live';
	const MODE_ADMIN_ONLY = 'admin_only';
	const MODE_DISABLED   = 'disabled';

	/**
	 * Stats to show array.
	 *
	 * @var string[] - array of stat keys to show in performance box.
	 */
	private $stats_to_show = array(
		'Impressions'                    => 'Impressions',
		'Sessions'                       => 'Sessions',
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
		'ConversionRate',
	);

	/**
	 * Flag whether to show welcome banner.
	 *
	 * @var boolean $show_welcome_banner
	 */
	private $show_welcome_banner;

	/**
	 * Flag whether to show the manual configuration welcome banner.
	 *
	 * @var boolean $show_manual_welcome_banner
	 */
	private $show_manual_welcome_banner;

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
	 * Feed status class - deals with information around feed statuses
	 *
	 * @var PureClarity_Feed_Status $feed_status
	 */
	private $feed_status;

	/**
	 * PureClarity Settings class - can get PureClarity settings.
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
	 * Renders display mode content.
	 */
	public function get_mode_content() {
		include_once 'views/dashboard/mode.php';
	}

	/**
	 * Renders feeds box content.
	 */
	public function get_feeds_content() {
		include_once 'views/dashboard/feeds.php';
	}

	/**
	 * Renders next steps content.
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

	/**
	 * Renders the stats box content.
	 */
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

	/**
	 * Formats a stats value for display.
	 *
	 * @param string $key - key of stat being displayed.
	 * @param string $value - value to be displayed.
	 *
	 * @return string
	 */
	public function get_stat_display( $key, $value ) {
		if ( in_array( $key, $this->stat_percentage, true ) ) {
			$value .= '%';
		}
		return $value;
	}

	/**
	 * Gets the column title for a given stat type.
	 *
	 * @param string $type - Stat type (date range).
	 *
	 * @return string
	 */
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
	 * Renders the Account status box content.
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
	 * Gets the PureClarity Admin page URL.
	 */
	public function get_admin_url() {
		$url = 'https://admin.pureclarity.com/';

		$admin = getenv( 'PURECLARITY_ADMIN' );
		if ( $admin ) {
			$url = $admin;
		}

		return $url;
	}

	/**
	 * Gets a formatted date.
	 *
	 * @param string $date - date to format.
	 *
	 * @return string
	 */
	public function get_date( $date ) {
		$time = strtotime( $date );
		return date_i18n( get_option( 'date_format' ), $time );
	}

	/**
	 * Gets the class for the free trial box based on days remaining.
	 *
	 * @param string $days_left - number of days left of the trial.
	 *
	 * @return string
	 */
	public function get_free_trial_class( $days_left ) {
		$class = '';

		if ( $days_left <= 4 && $days_left > 1 ) {
			$class = 'pc-ft-warning';
		} elseif ( $days_left <= 1 ) {
			$class = 'pc-ft-error';
		}

		return $class;
	}

	/**
	 * Gets dashboard information from PureClarity.
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
	 * Runs before admin notices action and hides them on our page.
	 */
	public static function inject_before_notices() {

		$whitelist_admin_pages = array(
			'toplevel_page_pureclarity-dashboard',
			'pureclarity_page_pureclarity-settings',
		);
		$admin_page            = get_current_screen();

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
	 * Returns the current PureClarity display mode.
	 *
	 * @return string
	 */
	public function get_mode() {
		$mode = $this->settings->get_pureclarity_mode();
		if ( 'on' === $mode ) {
			return self::MODE_LIVE;
		}

		if ( 'admin' === $mode ) {
			return self::MODE_ADMIN_ONLY;
		}

		return self::MODE_DISABLED;
	}

	/**
	 * Returns the current configuration state.
	 *
	 * @return string
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
	 * Returns whether the plugin is not configured.
	 *
	 * @return boolean
	 */
	public function is_not_configured() {
		return ( true === $this->get_is_not_configured() && false === $this->get_signup_started() );
	}

	/**
	 * Returns whether the plugin is waiting for a signup to finish.
	 *
	 * @return boolean
	 */
	public function is_waiting() {
		return ( true === $this->get_is_not_configured() && true === $this->get_signup_started() );
	}

	/**
	 * Returns whether to show the welcome banner.
	 *
	 * @return string
	 */
	public function show_welcome_banner() {
		if ( null === $this->show_welcome_banner ) {
			$show                      = $this->state_manager->get_state_value( 'show_welcome_banner' );
			$this->show_welcome_banner = ( false === empty( $show ) ) && $this->get_state_name() === self::STATE_CONFIGURED;
		}
		return $this->show_welcome_banner;
	}

	/**
	 * Returns whether to show the manual configuration welcome banner.
	 *
	 * @return string
	 */
	public function show_manual_welcome_banner() {
		if ( null === $this->show_manual_welcome_banner ) {
			$show                             = $this->state_manager->get_state_value( 'show_manual_welcome_banner' );
			$this->show_manual_welcome_banner = ( false === empty( $show ) ) && $this->get_state_name() === self::STATE_CONFIGURED;
		}
		return $this->show_manual_welcome_banner;
	}

	/**
	 * Returns whether to show the post-welcome banner.
	 *
	 * @return string
	 */
	public function show_getting_started_banner() {
		$show = $this->state_manager->get_state_value( 'show_getting_started_banner' );
		return $this->get_state_name() === self::STATE_CONFIGURED
				&& false === $this->show_welcome_banner()
				&& false === $this->show_manual_welcome_banner()
				&& ( false === empty( $show ) )
				&& time() < $show;
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
	 * Returns the current WordPress version
	 *
	 * @return string
	 */
	public function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * Returns the current WooCommerce version
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
			$signup_started       = $this->state_manager->get_state_value( 'signup_started' );
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
	 * Gets the store currency
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
