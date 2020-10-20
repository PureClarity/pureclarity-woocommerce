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
	 * @param string $type - delta type.
	 * @return mixed[]
	 */
	public function get_deltas( $type ) {
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
	 * Adds a delta row for the given delta type and entity.
	 *
	 * @param string $type - delta type.
	 * @param string $id - id of entity.
	 */
	public function add_delta( $type, $id ) {

		$this->wpdb->replace(
			$this->table_name,
			array(
				'type' => $type,
				'id'   => $id,
			)
		);
	}

	/**
	 * Deletes a delta row for the given delta type and entity.
	 *
	 * @param string $type - delta type.
	 * @param string $id - id of entity.
	 */
	public function delete_delta( $type, $id ) {
		$this->wpdb->delete(
			$this->table_name,
			array(
				'type' => $type,
				'id'   => $id,
			)
		);
	}

}
