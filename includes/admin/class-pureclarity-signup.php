<?php
/**
 * PureClarity_Signup class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Signup\Submit;
use PureClarity\Api\Signup\Status;

/**
 * Handles PureClarity Signup
 */
class PureClarity_Signup {

	/**
	 * Class to handle interaction with the pureclarity_state table
	 *
	 * @var PureClarity_State_Manager $state_manager
	 */
	private $state_manager;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_State_Manager $state_manager - PureClarity state manager class.
	 */
	public function __construct(
		$state_manager
	) {
		$this->state_manager = $state_manager;
	}

	/**
	 * Submit signup action, outputs a json encoded result
	 */
	public function submit_signup_action() {

		check_admin_referer( 'pureclarity_signup_submit' );

		$params = array(
			'firstname'  => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'lastname'   => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '',
			'company'    => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
			'password'   => isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '',
			'url'        => isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '',
			'store_name' => isset( $_POST['store_name'] ) ? sanitize_text_field( wp_unslash( $_POST['store_name'] ) ) : '',
			'region'     => isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '',
			'currency'   => isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '',
			'timezone'   => isset( $_POST['timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) : '',
			'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'platform'   => 'woocommerce',
		);

		if ( '1' !== $this->get_pureclarity_state( 'signup_started' ) ) {
			$this->update_pureclarity_state( 'signup_started', '1' );
			$result = $this->submit_signup( $params );
			$response = array(
				'error'   => $result['errors'],
				'success' => empty( $result['errors'] ),
			);

			if ( $result['errors'] ) {
				$this->delete_pureclarity_state( 'signup_started' );
			}
		} else {
			$response = array(
				'error'   => false,
				'success' => true,
			);
		}

		wp_send_json( $response );
	}

	/**
	 * Sends the signup to PureClarity
	 *
	 * @param mixed[] $params - Signup data collected by the signup form.
	 *
	 * @return mixed[]
	 */
	public function submit_signup( $params ) {
		$signup = new Submit();
		$result = $signup->request( $params );

		if ( empty( $result['errors'] ) ) {
			$this->save_signup_data( $result['request_id'], $params );
		}

		return $result;
	}

	/**
	 * Checks the signup progress and processes if complete.
	 */
	public function signup_progress_action() {

		$response = $this->check_signup_status();

		$result = array(
			'error'   => '',
			'success' => false,
		);

		if ( true === $response['complete'] ) {
			$this->process_auto_signup( $response['response'] );
			$result['success'] = true;
		} elseif ( $response['errors'] ) {
			$result['error'] = implode( ' | ', $response['errors'] );
		}

		wp_send_json( $result );
	}

	/**
	 * Calls PureClarity to check the signup status
	 */
	public function check_signup_status() {

		$result = array(
			'error'    => '',
			'response' => array(),
			'complete' => false,
		);

		try {
			$signup_data = $this->get_signup_data();
			if ( ! empty( $signup_data ) ) {
				$status = new Status();
				$result = $status->request( $signup_data );
			}
		} catch ( Exception $e ) {
			$result['error'] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Submit link account details action, outputs a json encoded result
	 */
	public function link_account_action() {

		check_admin_referer( 'pureclarity_link_account' );

		$params = array(
			'access_key' => isset( $_POST['access_key'] ) ? sanitize_text_field( wp_unslash( $_POST['access_key'] ) ) : '',
			'secret_key' => isset( $_POST['secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['secret_key'] ) ) : '',
			'region'     => isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '',
		);

		$result = $this->process_manual_configure( $params );

		$response = array(
			'error'   => implode( ',', $result['errors'] ),
			'success' => empty( $result['error'] ),
		);

		wp_send_json( $response );
	}



	/**
	 * Processes a manual configuration from the dashboard page
	 *
	 * @param mixed[] $request_data - Data submitted by the user on the Dashboard.
	 *
	 * @return mixed[]
	 */
	public function process_manual_configure( $request_data ) {
		$result = array(
			'errors' => array(),
		);

		$result['errors'] = $this->validate_manual_configure( $request_data );

		if ( empty( $result['errors'] ) ) {
			try {
				$this->save_config( $request_data['access_key'], $request_data['secret_key'], $request_data['region'] );
				$this->update_pureclarity_state( 'show_manual_welcome_banner', '1' );
				$this->trigger_feeds();
			} catch ( \Exception $e ) {
				$result['errors'][] = 'Error processing request ' . $e->getMessage();
			}
		}

		return $result;
	}

	/**
	 * Validates the params in the manual configure request
	 *
	 * @param mixed[] $request_data - Data submitted by the user on the Dashboard.
	 * @return array
	 */
	protected function validate_manual_configure( $request_data ) {
		$errors = array();

		if ( ! isset( $request_data['access_key'] ) || empty( $request_data['access_key'] ) ) {
			$errors[] = 'Missing Access Key';
		}

		if ( ! isset( $request_data['secret_key'] ) || empty( $request_data['secret_key'] ) ) {
			$errors[] = 'Missing Secret Key';
		}

		if ( ! isset( $request_data['region'] ) || empty( $request_data['region'] ) ) {
			$errors[] = 'Missing Region';
		}

		return $errors;
	}

	/**
	 * Processes the signup request
	 *
	 * @param mixed[] $request_data - Data from the signup progress response from PureClarity.
	 *
	 * @return mixed[]
	 */
	public function process_auto_signup( $request_data ) {
		$result = array(
			'errors' => array(),
		);

		try {
			$signup_data = $this->get_signup_data();

			if ( ! empty( $signup_data ) ) {
				$this->save_config( $request_data['AccessKey'], $request_data['SecretKey'], $signup_data['region'] );
				$this->delete_pureclarity_state( 'signup_request' );
				$this->update_pureclarity_state( 'show_welcome_banner', '1' );
				$this->delete_pureclarity_state( 'signup_started' );
				$this->trigger_feeds();
			} else {
				$result['errors'][] = 'Error processing request';
			}
		} catch ( Exception $e ) {
			$result['errors'][] = 'Error processing request: ' . $e->getMessage();
		}

		return $result;
	}

	/**
	 * Saves the PureClarity credentials to the WordPress config
	 *
	 * @param string $access_key - Access Key for the PureClarity Account.
	 * @param string $secret_key - Secret Key for the PureClarity Account.
	 * @param string $region - Region the signup request was made for.
	 */
	protected function save_config( $access_key, $secret_key, $region ) {
		update_option( 'pureclarity_mode', 'admin' );
		update_option( 'pureclarity_accesskey', $access_key );
		update_option( 'pureclarity_secretkey', $secret_key );
		update_option( 'pureclarity_region', $region );
		update_option( 'pureclarity_nightly_feed_enabled', 'on' );
		update_option( 'pureclarity_deltas_enabled', 'on' );
	}

	/**
	 * Triggers a run of feeds needed after signup
	 */
	protected function trigger_feeds() {
		$feeds = array(
			'user',
			'category',
			'product',
			'orders',
		);

		$this->update_pureclarity_state( 'requested_feeds', wp_json_encode( $feeds ) );
	}

	/**
	 * Gets the request details from state table
	 *
	 * @return array|string
	 */
	protected function get_signup_data() {

		$request_data = $this->get_pureclarity_state( 'signup_request' );
		$request      = array();

		if ( ! empty( $request_data ) ) {
			$request = json_decode( $request_data, true );
		}
		return $request;
	}

	/**
	 * Saves the request details to state table
	 *
	 * @param string  $request_id - ID of the request (returned by PureClarity).
	 * @param mixed[] $params - the parameters used by the signup request.
	 */
	protected function save_signup_data( $request_id, $params ) {

		$signup_data = array(
			'id'     => $request_id,
			'region' => $params['region'],
		);

		$this->update_pureclarity_state( 'signup_request', wp_json_encode( $signup_data ) );
	}

	/**
	 * Gets the data from the state table
	 *
	 * @param string $key - Key to get from the state table.
	 *
	 * @return string
	 */
	protected function get_pureclarity_state( $key ) {
		return $this->state_manager->get_state_value( $key );
	}

	/**
	 * Saves data to the state table
	 *
	 * @param string $key - Key to set value against in the state table.
	 * @param string $value - Value to set against the given key.
	 */
	protected function update_pureclarity_state( $key, $value ) {
		$this->state_manager->set_state_value( $key, $value );
	}

	/**
	 * Removes data from the state table
	 *
	 * @param string $key - Key to set value against in the state table.
	 */
	protected function delete_pureclarity_state( $key ) {
		$this->state_manager->delete_state_value( $key );
	}
}
