<?php
/**
 * PureClarity_Database class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Handles Database upgrades for PureClarity tables
 */
class PureClarity_Database {

	/**
	 * Checks to see if the database is up to date, if not then updates it
	 *
	 * @param string $current_db_version - the order id to get info for.
	 */
	public function update_db( $current_db_version ) {
		if ( $current_db_version < PURECLARITY_DB_VERSION ) {
			for ( $i = $current_db_version; $i < PURECLARITY_DB_VERSION; $i++ ) {
				$f = 'update_db_to_' . ( $i + 1 );
				if ( method_exists( $this, $f ) ) {
					$this->$f();
				}
			}
		}
	}

	/**
	 * Upgrades to version 1 of the PureClarity Database table structure, creating the delta & state tables & options
	 */
	public function update_db_to_1() {
		$this->create_options();
		$this->create_state_table();
		$this->create_delta_table();
		add_option( 'pureclarity_db_version', 1 );
	}

	/**
	 * Creates the pureclarity_state table
	 */
	public function create_options() {
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
		add_option( 'pureclarity_nightly_feed_enabled', 'no' );
		add_option( 'pureclarity_feed_debug_logging', 'no' );
		add_option( 'pureclarity_product_feed_exclude_oos', 'no' );
		add_option( 'pureclarity_env_subheading', '' );
		add_option( 'pureclarity_feeds_subheading', '' );
		add_option( 'pureclarity_zones_subheading', '' );

		delete_option( 'pureclarity_brandfeed_run' );
		delete_option( 'pureclarity_catfeed_run' );
		delete_option( 'pureclarity_db_version' );
		delete_option( 'pureclarity_delta_running' );
		delete_option( 'pureclarity_orderfeed_run' );
		delete_option( 'pureclarity_prodfeed_run' );
		delete_option( 'pureclarity_product_deltas' );
		delete_option( 'pureclarity_product_deltas' );
		delete_option( 'pureclarity_user_deltas' );
		delete_option( 'pureclarity_userfeed_run' );
	}

	/**
	 * Creates the pureclarity_state table
	 */
	public function create_state_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'pureclarity_state';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
  			`name` CHAR(35) NOT NULL,
  			`value` TEXT NOT NULL,
  			UNIQUE INDEX `pureclarity_unique_name` (`name` ASC));
		) $charset_collate COMMENT 'PureClarity State Table - Stores key information about the state of the PureClarity integration';";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Creates the pureclarity_state table
	 */
	public function create_delta_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'pureclarity_delta';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
  			`type` CHAR(12) NOT NULL,
  			`id` integer UNSIGNED NOT NULL,
  			UNIQUE INDEX `pureclarity_unique_name` (`type` ASC, `id` ASC));
		) $charset_collate COMMENT 'PureClarity Delta Table - Stores ids of enities that need to be sent ot PureClarity as deltas';";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

}
