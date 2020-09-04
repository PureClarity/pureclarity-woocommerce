<?php
/**
 * PureClarity_Settings class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

use PureClarity\Api\Resource\Regions;

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
	 * @var array $display_regions
	 */
	private $display_regions;

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
		if ( null === $this->display_regions ) {
			$this->display_regions = array();
			$region_class          = new Regions();
			$pc_regions            = $region_class->getRegionLabels();
			foreach ( $pc_regions as $region ) {
				$this->display_regions[ (string) $region['value'] ] = $region['label'];
			}
		}

		return $this->display_regions;
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
