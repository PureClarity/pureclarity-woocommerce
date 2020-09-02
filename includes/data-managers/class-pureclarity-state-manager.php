<?php
/**
 * PureClarity_Database class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Handles interaction with the pureclarity_state table
 */
class PureClarity_State_Manager {

	/**
	 * WordPress Database class
	 *
	 * @var wpdb $wpdb
	 */
	private $wpdb;

	/**
	 * PureClarity State table name
	 *
	 * @var string $table_name
	 */
	private $table_name;

	/**
	 * PureClarity_Data_State constructor.
	 *
	 * Sets up dependencies for this class.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . 'pureclarity_state';

	}
	/**
	 * Gets the value for the given state name key
	 *
	 * @param string $name_key
	 * @return string
	 */
	public function get_state_value( $name_key ) {
		global $wpdb;
		$row = (array) $wpdb->get_row(
			$wpdb->prepare(
				"SELECT value FROM {$this->table_name} WHERE name = %s",
				$name_key
			)
		);

		$state = '';
		if ( ! empty( $row ) ) {
			$state = $row['value'];
		}

		return $state;
	}

	/**
	 * Gets the value for the given state name key
	 *
	 * @param string $name_key
	 * @param string $value
	 */
	public function set_state_value( $name_key, $value ) {
		$this->wpdb->replace(
			$this->table_name,
			array(
				'name'       => $name_key,
				'value'      => $value
			)
		);
	}

}
