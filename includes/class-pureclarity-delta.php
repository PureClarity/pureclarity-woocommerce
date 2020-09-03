<?php
/**
 * PureClarity_Settings class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles db delta interactions
 */
class PureClarity_Delta {

	/**
	 * PureClarity Delta table manager
	 *
	 * @var string $delta_manager
	 */
	private $delta_manager;

	/**
	 * PureClarity Delta table manager
	 *
	 * @var string $state_manager
	 */
	private $state_manager;

	/**
	 * PureClarity_Delta  constructor, sets dependencies.
	 *
	 * @param PureClarity_Delta_Manager $delta_manager - Delta data manager class.
	 * @param PureClarity_State_Manager $state_manager - State data manager class.
	 */
	public function __construct(
		$delta_manager,
		$state_manager
	) {
		$this->delta_manager = $delta_manager;
		$this->state_manager = $state_manager;
	}

	/**
	 * Gets whether the delta process is running already
	 *
	 * @return boolean
	 */
	public function is_delta_running() {
		return ( $this->state_manager->get_state_value( 'delta_running' ) === '1' );
	}

	/**
	 * Sets whether the delta process is running already
	 *
	 * @param string $running - new value for option ("1" or "0").
	 */
	public function set_is_delta_running( $running ) {
		$this->state_manager->set_state_value( 'delta_running', $running );
	}

	/**
	 * Adds a product to the delta
	 *
	 * @param integer $id - product id.
	 */
	public function add_product_delta( $id ) {
		$this->delta_manager->add_delta( 'product', $id );
	}

	/**
	 * Removes products from the delta
	 *
	 * @param integer[] $ids - product ids to remove from delta array.
	 */
	public function remove_product_deltas( $ids ) {
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				$this->delta_manager->delete_delta( 'product', $id );
			}
		}
	}

	/**
	 * Returns product delta array
	 *
	 * @return array
	 */
	public function get_product_deltas() {
		return $this->delta_manager->get_deltas( 'product' );
	}

	/**
	 * Adds a user to the delta
	 *
	 * @param integer $id - user id.
	 */
	public function add_user_delta( $id ) {
		$this->delta_manager->add_delta( 'user', $id );
	}

	/**
	 * Removes a user from the delta
	 *
	 * @param integer[] $ids - user ids to remove from deltas.
	 */
	public function remove_user_deltas( $ids ) {
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				$this->delta_manager->delete_delta( 'user', $id );
			}
		}
	}

	/**
	 * Returns all user deltas
	 *
	 * @return array
	 */
	public function get_user_deltas() {
		return $this->delta_manager->get_deltas( 'user' );
	}

}
