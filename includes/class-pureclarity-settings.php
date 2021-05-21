<?php
/**
 * PureClarity_Settings class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

use PureClarity\Api\Resource\Regions;

/**
 * Handles config getting pureclarity settings
 */
class PureClarity_Settings {

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
				return current_user_can( 'edit_pages' ) || defined( 'DOING_CRON' );
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
		return ( get_option( 'pureclarity_nightly_feed_enabled', '' ) === 'on' );
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
	 * Returns whether Zone should appear on homepage
	 *
	 * @return boolean
	 */
	public function is_bmz_on_home_page() {
		return ( get_option( 'pureclarity_add_bmz_homepage', '' ) === 'on' );
	}

	/**
	 * Returns whether Zone should appear on category page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_category_page() {
		return ( get_option( 'pureclarity_add_bmz_categorypage', '' ) === 'on' );
	}

	/**
	 * Returns whether Zone should appear on search page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_search_page() {
		return ( get_option( 'pureclarity_add_bmz_searchpage', '' ) === 'on' );
	}

	/**
	 * Returns whether Zone should appear on product page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_product_page() {
		return ( get_option( 'pureclarity_add_bmz_productpage', '' ) === 'on' );
	}

	/**
	 * Returns whether Zone should appear on basket page
	 *
	 * @return boolean
	 */
	public function is_bmz_on_basket_page() {
		return ( get_option( 'pureclarity_add_bmz_basketpage', '' ) === 'on' );
	}

	/**
	 * Returns whether Zone should appear on checkout page
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

	/**
	 * Returns whether feed logging is turned on
	 *
	 * @return boolean
	 */
	public function is_feed_logging_enabled() {
		return ( get_option( 'pureclarity_feed_debug_logging', 'no' ) === 'on' );
	}

	/**
	 * Returns whether product feed out of stock exclusion is turned on
	 *
	 * @return boolean
	 */
	public function is_product_feed_exclude_oos_enabled() {
		return ( get_option( 'pureclarity_product_feed_exclude_oos', 'no' ) === 'on' );
	}
}
