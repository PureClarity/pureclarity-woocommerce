<?php
/**
 * PureClarity_Delta_Manager class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Handles interaction with the pureclarity_delta table
 */
class PureClarity_Delta_Manager {

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
		$this->table_name = $wpdb->prefix . 'pureclarity_delta';

	}

	/**
	 * Gets the value for the given state name key
	 *
	 * @param string $type
	 * @return mixed[]
	 */
	public function get_deltas( $type, $website_id = '' ) {
		global $wpdb;
		$rows = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id FROM {$this->table_name} WHERE `type` = %s",
				$type
			),
			ARRAY_A
		);

		return $rows;
	}

	/**
	 * Gets the value for the given state name key
	 *
	 * @param string $type
	 * @param string $id
	 * @param string $website_id
	 */
	public function add_delta( $type, $id, $website_id = '' ) {

		$this->wpdb->replace(
			$this->table_name,
			array(
				'type'       => $type,
				'id'         => $id,
				'website_id' => $website_id,
			)
		);
	}

	/**
	 * Gets the value for the given state name key
	 *
	 * @param string $type
	 * @param string $id
	 * @param integer $website_id
	 */
	public function delete_delta( $type, $id, $website_id = 0 ) {
		$this->wpdb->delete(
			$this->table_name,
			array(
				'type'       => $type,
				'id'         => $id,
				'website_id' => $website_id,
			)
		);
	}

}
