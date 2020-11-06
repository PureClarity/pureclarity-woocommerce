<?php
/**
 * PureClarity_Cron_Signup class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.2
 */

/**
 * Handles Signup status check related cron code
 */
class PureClarity_Cron_Signup {

	/**
	 * PureClarity Signup class
	 *
	 * @var PureClarity_Signup $signup
	 */
	private $signup;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Signup $signup - PureClarity Signup class.
	 */
	public function __construct(
		$signup
	) {
		$this->signup = $signup;
	}

	/**
	 * Runs check on signup status and configures plugin if complete.
	 */
	public function check_signup_status() {

		$response = $this->signup->check_signup_status();

		if ( true === $response['complete'] ) {
			$this->signup->process_auto_signup( $response['response'] );
		}
	}
}
