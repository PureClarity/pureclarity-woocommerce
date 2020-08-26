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
	 * Cache of "new version"
	 *
	 * @var string $new_version
	 */
	private $new_version;

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
	 * @param PureClarity_Settings $settings PureClarity Settings class.
	 */
	public function __construct( $settings ) {
		$this->settings      = $settings;
		$this->state_manager = new PureClarity_State_Manager();
		$this->feed_status   = new PureClarity_Feed_Status();
	}

	/**
	 * Renders settings page
	 */
	public function dashboard_render() {
		include_once 'views/dashboard-page.php';
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
		if ( function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
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
	 * Includes the waiting dashboard view file
	 */
	public function get_waiting_content() {
		include 'views/dashboard/waiting.php';
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
	 * Returns the class to use for the Brand feed status display
	 *
	 * @return string
	 */
	public function get_brand_feed_status_class() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_BRAND );
		return $feed['class'];
	}

	/**
	 * Returns the label to use for the Brand feed status display
	 *
	 * @return string
	 */
	public function get_brand_feed_status_label() {
		$feed = $this->feed_status->get_feed_status( Feed::FEED_TYPE_BRAND );
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
				Feed::FEED_TYPE_BRAND,
				Feed::FEED_TYPE_ORDER,
			)
		);
	}

}
