<?php
/**
 * PureClarity_Feedback class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

use PureClarity\Api\Signup\Submit;
use PureClarity\Api\Signup\Status;

/**
 * Handles PureClarity Feedback on deactivation
 */
class PureClarity_Feedback {

	/**
	 * Handles getting PureClarity settings
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Settings $settings - PureClarity settings class.
	 */
	public function __construct(
		$settings
	) {
		$this->settings = $settings;
	}

	/**
	 * Adds PureClarity menus
	 */
	public function feedback_action() {

		check_admin_referer( 'pureclarity_deactivate_feedback', 'security' );

		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$notes  = isset( $_POST['notes'] ) ? sanitize_text_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! empty( $reason ) || ! empty( $notes ) ) {
			$feedback = new \PureClarity\Api\Feedback\Submit(
				$this->settings->get_access_key(),
				$this->settings->get_secret_key(),
				(int) $this->settings->get_region(),
				'Reason Chosen: ' . $reason . ' | Additional Comments: ' . $notes,
				'WooCommerce'
			);

			$feedback->request();
		}
	}
}