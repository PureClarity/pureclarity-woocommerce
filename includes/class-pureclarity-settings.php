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

	const OPTION_NIGHTLY_FEED_ENABLED   = 'pureclarity_nightly_feed_enabled';

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
		add_option( 'pureclarity_prodfeed_run', '0' );
		add_option( 'pureclarity_catfeed_run', '0' );
		add_option( 'pureclarity_brandfeed_run', '0' );
		add_option( 'pureclarity_userfeed_run', '0' );
		add_option( 'pureclarity_orderfeed_run', '0' );
		add_option( 'pureclarity_bmz_debug', 'no' );
		add_option( 'pureclarity_deltas_enabled', 'no' );
		add_option( 'pureclarity_add_bmz_homepage', 'on' );
		add_option( 'pureclarity_add_bmz_searchpage', 'on' );
		add_option( 'pureclarity_add_bmz_categorypage', 'on' );
		add_option( 'pureclarity_add_bmz_productpage', 'on' );
		add_option( 'pureclarity_add_bmz_basketpage', 'on' );
		add_option( 'pureclarity_add_bmz_checkoutpage', 'on' );
		add_option( 'pureclarity_product_deltas', '{}' );
		add_option( 'pureclarity_category_feed_required', '' );
		add_option( 'pureclarity_user_deltas', '{}' );
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
				return current_user_can( 'administrator' );
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
	 * Gets product list dom selector config value
	 *
	 * @return string
	 */
	public function get_prodlist_result_element() {
		return (string) get_option( 'pureclarity_prodlist_selector', '#main' );
	}

	/**
	 * Gets whether product feed has been sent already
	 *
	 * @return string
	 */
	public function is_product_feed_sent() {
		return ( get_option( 'pureclarity_prodfeed_run', '0' ) === '1' );
	}

	/**
	 * Gets whether category feed has been sent already
	 *
	 * @return string
	 */
	public function is_category_feed_sent() {
		return ( get_option( 'pureclarity_catfeed_run', '0' ) === '1' );
	}

	/**
	 * Gets whether brand feed has been sent already
	 *
	 * @return string
	 */
	public function is_brand_feed_sent() {
		return ( get_option( 'pureclarity_brandfeed_run', '0' ) === '1' );
	}

	/**
	 * Gets whether user feed has been sent already
	 *
	 * @return string
	 */
	public function is_user_feed_sent() {
		return ( get_option( 'pureclarity_userfeed_run', '0' ) === '1' );
	}

	/**
	 * Gets whether order feed has been sent already
	 *
	 * @return string
	 */
	public function is_order_feed_sent() {
		return ( get_option( 'pureclarity_orderfeed_run', '0' ) === '1' );
	}

	/**
	 * Gets whether the delta process is running already
	 *
	 * @return boolean
	 */
	public function is_delta_running() {
		return ( get_option( 'pureclarity_delta_running', '0' ) === '1' );
	}

	/**
	 * Sets whether the delta process is running already
	 *
	 * @param string $running - new value for option ("1" or "0").
	 */
	public function set_is_delta_running( $running ) {
		update_option( 'pureclarity_delta_running', $running );
	}

	/**
	 * Saves config to say that a feed has been sent
	 *
	 * @param string $type - type of feed sent.
	 */
	public function set_feed_type_sent( $type ) {
		$option = '';
		switch ( $type ) {
			case 'product':
				$option = 'pureclarity_prodfeed_run';
				break;
			case 'category':
				$option = 'pureclarity_catfeed_run';
				break;
			case 'brand':
				$option = 'pureclarity_brandfeed_run';
				break;
			case 'user':
				$option = 'pureclarity_userfeed_run';
				break;
			case 'order':
				$option = 'pureclarity_orderfeed_run';
				break;
		}
		if ( ! empty( $option ) ) {
			update_option( $option, '1' );
		}
	}

	/**
	 * Gets PureClarity Delta feed url
	 */
	public function get_delta_url() {
		$url  = getenv( 'PURECLARITY_API_ENDPOINT' );
		$port = getenv( 'PURECLARITY_API_PORT' );
		if ( empty( $url ) ) {
			$url = $this->regions[ $this->get_region() ];
		}
		if ( ! empty( $port ) ) {
			$url = $url . ':' . $port;
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
	 * Adds a product to the delta
	 *
	 * @param integer $id - product id.
	 */
	public function add_product_delta( $id ) {
		$deltas = $this->get_product_deltas();
		if ( empty( $deltas ) ) {
			$deltas = array();
		}
		$deltas[ $id ] = true;
		update_option( 'pureclarity_product_deltas', wp_json_encode( $deltas, true ) );
	}

	/**
	 * Removes products from the delta
	 *
	 * @param integer[] $ids - product ids to remove from delta array.
	 */
	public function remove_product_deltas( $ids ) {
		$deltas = $this->get_product_deltas();
		if ( ! empty( $deltas ) ) {
			foreach ( $ids as $id ) {
				if ( isset( $deltas[ $id ] ) ) {
					unset( $deltas[ $id ] );
				}
			}

			update_option( 'pureclarity_product_deltas', wp_json_encode( $deltas, true ) );
		}
	}

	/**
	 * Returns product delta array
	 *
	 * @return array
	 */
	public function get_product_deltas() {
		$deltastring = get_option( 'pureclarity_product_deltas', '{}' );
		if ( ! empty( $deltastring ) ) {
			return json_decode( $deltastring, true );
		}
		return array();
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
	 * Updates pureclarity_user_feed_required option to time now
	 */
	public function set_user_feed_required() {
		update_option( 'pureclarity_user_feed_required', time() );
	}

	/**
	 * Updates pureclarity_user_feed_required option to time empty
	 */
	public function clear_user_feed_required() {
		update_option( 'pureclarity_user_feed_required', '' );
	}

	/**
	 * Adds a user to the delta
	 *
	 * @param integer $id - user id.
	 */
	public function add_user_delta( $id ) {
		$deltas = $this->get_user_deltas();
		if ( empty( $deltas ) ) {
			$deltas = array();
		}
		$deltas[ $id ] = true;
		update_option( 'pureclarity_user_deltas', wp_json_encode( $deltas, true ) );
	}

	/**
	 * Removes a user from the delta
	 *
	 * @param integer[] $ids - user ids to remove from deltas.
	 */
	public function remove_user_deltas( $ids ) {
		$deltas = $this->get_user_deltas();

		if ( ! empty( $deltas ) ) {
			foreach ( $ids as $id ) {
				if ( isset( $deltas[ $id ] ) ) {
					unset( $deltas[ $id ] );
				}
			}
			update_option( 'pureclarity_user_deltas', wp_json_encode( $deltas, true ) );
		}
	}

	/**
	 * Returns all user deltas
	 *
	 * @return array
	 */
	public function get_user_deltas() {
		$deltastring = get_option( 'pureclarity_user_deltas', '{}' );
		return ( ! empty( $deltastring ) ? json_decode( $deltastring, true ) : array() );
	}

}
