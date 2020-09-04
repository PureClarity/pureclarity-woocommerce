<?php
/**
 * PureClarity_Settings class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles config getting and setting
 */
class PureClarity_Settings {

	const OPTION_ACCESSKEY              = 'pureclarity_accesskey';
	const OPTION_SECRETKEY              = 'pureclarity_secretkey';
	const OPTION_REGION                 = 'pureclarity_region';
	const OPTION_MODE                   = 'pureclarity_mode';
	const OPTION_ZONE_DEBUG             = 'pureclarity_bmz_debug';
	const OPTION_DELTAS_ENABLED         = 'pureclarity_deltas_enabled';
	const OPTION_NIGHTLY_FEED_ENABLED   = 'pureclarity_nightly_feed_enabled';
	const OPTION_ZONE_HOMEPAGE          = 'pureclarity_add_bmz_homepage';
	const OPTION_ZONE_SEARCHPAGE        = 'pureclarity_add_bmz_searchpage';
	const OPTION_ZONE_CATEGORYPAGE      = 'pureclarity_add_bmz_categorypage';
	const OPTION_ZONE_PRODUCTPAGE       = 'pureclarity_add_bmz_productpage';
	const OPTION_ZONE_BASKETPAGE        = 'pureclarity_add_bmz_basketpage';
	const OPTION_ZONE_CHECKOUTPAGE      = 'pureclarity_add_bmz_checkoutpage';
	const OPTION_CATEGORY_FEED_REQUIRED = 'pureclarity_category_feed_required';

	/**
	 * PureClarity script url
	 *
	 * @var string $script_url
	 */
	public $script_url = '//pcs.pureclarity.net';

	/**
	 * PureClarity region for use in dropdowns
	 *
	 * @var array $regions
	 */
	private $display_regions = array(
		'1' => 'Europe',
		'4' => 'USA',
	);

	/**
	 * PureClarity region urls
	 *
	 * @var array $regions
	 */
	private $regions = array(
		'1'  => 'https://api-eu-w-1.pureclarity.net',
		'2'  => 'https://api-eu-w-2.pureclarity.net',
		'3'  => 'https://api-eu-c-1.pureclarity.net',
		'4'  => 'https://api-us-e-1.pureclarity.net',
		'5'  => 'https://api-us-e-2.pureclarity.net',
		'6'  => 'https://api-us-w-1.pureclarity.net',
		'7'  => 'https://api-us-w-2.pureclarity.net',
		'8'  => 'https://api-ap-s-1.pureclarity.net',
		'9'  => 'https://api-ap-ne-1.pureclarity.net',
		'10' => 'https://api-ap-ne-2.pureclarity.net',
		'11' => 'https://api-ap-se-1.pureclarity.net',
		'12' => 'https://api-ap-se-2.pureclarity.net',
		'13' => 'https://api-ca-c-1.pureclarity.net',
		'14' => 'https://api-sa-e-1.pureclarity.net',
	);

	/**
	 * PureClarity sftp region urls
	 *
	 * @var array $sftp_regions
	 */
	private $sftp_regions = array(
		'1'  => 'https://sftp-eu-w-1.pureclarity.net',
		'2'  => 'https://sftp-eu-w-2.pureclarity.net',
		'3'  => 'https://sftp-eu-c-1.pureclarity.net',
		'4'  => 'https://sftp-us-e-1.pureclarity.net',
		'5'  => 'https://sftp-us-e-2.pureclarity.net',
		'6'  => 'https://sftp-us-w-1.pureclarity.net',
		'7'  => 'https://sftp-us-w-2.pureclarity.net',
		'8'  => 'https://sftp-ap-s-1.pureclarity.net',
		'9'  => 'https://sftp-ap-ne-1.pureclarity.net',
		'10' => 'https://sftp-ap-ne-2.pureclarity.net',
		'11' => 'https://sftp-ap-se-1.pureclarity.net',
		'12' => 'https://sftp-ap-se-2.pureclarity.net',
		'13' => 'https://sftp-ca-c-1.pureclarity.net',
		'14' => 'https://sftp-sa-e-1.pureclarity.net',
	);

	/**
	 * Sets up PureClarity options with default values
	 */
	public function __construct() {
		add_option( 'pureclarity_accesskey', '' );
		add_option( 'pureclarity_secretkey', '' );
		add_option( 'pureclarity_region', '1' );
		add_option( 'pureclarity_mode', 'off' );
		add_option( 'pureclarity_bmz_debug', 'no' );
		add_option( 'pureclarity_deltas_enabled', 'no' );
		add_option( 'pureclarity_add_bmz_homepage', 'on' );
		add_option( 'pureclarity_add_bmz_searchpage', 'on' );
		add_option( 'pureclarity_add_bmz_categorypage', 'on' );
		add_option( 'pureclarity_add_bmz_productpage', 'on' );
		add_option( 'pureclarity_add_bmz_basketpage', 'on' );
		add_option( 'pureclarity_add_bmz_checkoutpage', 'on' );
		add_option( 'pureclarity_category_feed_required', '' );
	}

	/**
	 * Gets Access Key config value
	 *
	 * @return string
	 */
	public function get_access_key() {
		return (string) get_option( 'pureclarity_accesskey', '' );
	}

	/**
	 * Gets Secret Key config value
	 *
	 * @return string
	 */
	public function get_secret_key() {
		return (string) get_option( 'pureclarity_secretkey', '' );
	}

	/**
	 * Gets display friendly region list
	 *
	 * @return string[]
	 */
	public function get_display_regions() {
		return $this->display_regions;
	}

	/**
	 * Gets region urls
	 *
	 * @return string[]
	 */
	public function get_regions() {
		return $this->regions;
	}

	/**
	 * Gets region config value
	 *
	 * @return string
	 */
	public function get_region() {
		return (string) get_option( 'pureclarity_region', '1' );
	}

	/**
	 * Gets mode config value
	 *
	 * @return string
	 */
	public function get_pureclarity_mode() {
		return get_option( 'pureclarity_mode', 'off' );
	}

	/**
	 * Gets enabled config value
	 *
	 * @return string
	 */
	public function is_pureclarity_enabled() {
		switch ( $this->get_pureclarity_mode() ) {
			case 'on':
				return true;
			case 'admin':
				return current_user_can( 'administrator' ) || defined( 'DOING_CRON' );
		}
		return false;
	}

	/**
	 * Gets deltas enabled config value
	 *
	 * @return string
	 */
	public function is_deltas_enabled_admin() {
		return ( get_option( 'pureclarity_deltas_enabled', '' ) === 'on' );
	}

	/**
	 * Gets nightly feed enabled value
	 *
	 * @return string
	 */
	public function is_nightly_feed_enabled() {
		return ( get_option( self::OPTION_NIGHTLY_FEED_ENABLED, '' ) === 'on' );
	}

	/**
	 * Gets deltas enabled config value
	 *
	 * @return string
	 */
	public function is_deltas_enabled() {
		return $this->is_deltas_enabled_admin() && $this->is_pureclarity_enabled();
	}

	/**
	 * Gets bmz debug enabled config value
	 *
	 * @return string
	 */
	public function is_bmz_debug_enabled() {
		return ( get_option( 'pureclarity_bmz_debug', '' ) === 'on' );
	}

	/**
	 * Gets PureClarity API url
	 *
	 * @return string
	 */
	public function get_api_url() {
		$url = getenv( 'PURECLARITY_SCRIPT_URL' );
		if ( empty( $url ) ) {
			$url = $this->script_url . '/' . $this->get_access_key() . '/cs.js';
		} else {
			$url .= $this->get_access_key() . '/cs.js';
		}
		return $url;
	}

	/**
	 * Gets PureClarity feed url
	 *
	 * @return string
	 */
	public function get_feed_baseurl() {
		$url  = getenv( 'PURECLARITY_FEED_HOST' );
		$port = getenv( 'PURECLARITY_FEED_PORT' );
		if ( empty( $url ) ) {
			$url = $this->sftp_regions[ $this->get_region() ];
		} else {
			$url = 'http://' . $url;
		}
		if ( ! empty( $port ) ) {
			$url = $url . ':' . $port;
		}
		return $url . '/';
	}

	/**
	 * Gets PureClarity Delta feed url
	 */
	public function get_delta_url() {
		$url  = getenv( 'PURECLARITY_HOST' );
		if ( empty( $url ) ) {
			$url = $this->regions[ $this->get_region() ];
		}
		return $url . '/api/delta';
	}

	/**
	 * Returns whether BMZ should appear on homepage
	 *
	 * @return boolean
	 */
	public function is_bmz_on_home_page() {
		return ( get_option( 'pureclarity_add_bmz_homepage', '' ) === 'on' );
	}

	/**
	 * Returns whether BMZ should appear on category page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_category_page() {
		return ( get_option( 'pureclarity_add_bmz_categorypage', '' ) === 'on' );
	}

	/**
	 * Returns whether BMZ should appear on search page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_search_page() {
		return ( get_option( 'pureclarity_add_bmz_searchpage', '' ) === 'on' );
	}

	/**
	 * Returns whether BMZ should appear on product page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_product_page() {
		return ( get_option( 'pureclarity_add_bmz_productpage', '' ) === 'on' );
	}

	/**
	 * Returns whether BMZ should appear on basket page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_basket_page() {
		return ( get_option( 'pureclarity_add_bmz_basketpage', '' ) === 'on' );
	}

	/**
	 * Returns whether BMZ should appear on checkout page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_checkout_page() {
		return ( get_option( 'pureclarity_add_bmz_checkoutpage', '' ) === 'on' );
	}

	/**
	 * Updates pureclarity_category_feed_required option to time now
	 */
	public function set_category_feed_required() {
		update_option( 'pureclarity_category_feed_required', time() );
	}

	/**
	 * Updates pureclarity_category_feed_required option to empty
	 */
	public function clear_category_feed_required() {
		update_option( 'pureclarity_category_feed_required', '' );
	}

	/**
	 * Returns value for pureclarity_category_feed_required option
	 */
	public function get_category_feed_required() {
		return get_option( 'pureclarity_category_feed_required', '' );
	}
}
