<?php
/**
 * Custom Functions
 *
 * @package PureClarity for WooCommerce
 * @since 2.3.0
 */

// Ensure path constant is set.
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	exit();
}


if ( false === function_exists( 'pureclarity_output_order_input' ) ) {
	/**
	 * Outputs a hidden input tag with order information in. It will be picked up by the PureClarity JS and sent to PureClarity.
	 *
	 * @param int $order_id Order ID - The ID of the order that needs to be sent to PureClarity.
	 */
	function pureclarity_output_order_input( $order_id ) {
		require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-class-loader.php';
		$loader = new PureClarity_Class_Loader();
		$pc_order = $loader->get_order();
		$pc_order->output_order_event_input( $order_id );
	}
}

if ( false === function_exists( 'pureclarity_db_check' ) ) {
	/**
	 * Checks to see if the database needs upgrading, if so, runs the upgrade
	 */
	function pureclarity_db_check() {
		$pc_db_version = (int) get_site_option( 'pureclarity_db_version' );
		if ( get_site_option( 'pureclarity_db_version' ) !== PURECLARITY_DB_VERSION ) {
			require_once PURECLARITY_INCLUDES_PATH . 'class-pureclarity-class-loader.php';
			$loader = new PureClarity_Class_Loader();
			$pc_db = $loader->get_database();
			$pc_db->update_db( $pc_db_version );
		}
	}
	add_action( 'plugins_loaded', 'pureclarity_db_check' );
}

if ( false === function_exists( 'pureclarity_action_link' ) ) {
	/**
	 * Update the plugin deactivate link to show a popup with a survey before deactivation of this plugin.
	 *
	 * @param mixed[] $plugin_actions - actions for this plugin.
	 * @param string  $plugin_file - plugin name.
	 *
	 * @return mixed[]
	 */
	function pureclarity_action_link( $plugin_actions, $plugin_file ) {
		if ( isset( $plugin_actions['deactivate'] ) && basename( plugin_dir_path( __FILE__ ) ) . '/pureclarity.php' === $plugin_file ) {
			$orig                          = $plugin_actions['deactivate'];
			$plugin_actions['deactivate']  = '<a href="#TB_inline?&width=600&height=600&inlineId=pureclarity_deactivate_feedback" class="thickbox" title="' . __( 'Deactivate PureClarity for WooCommerce', 'pureclarity' ) . '"> ' . __( 'Deactivate', 'pureclarity' ) . '</a>';
			$plugin_actions['deactivate'] .= '<div id="pureclarity_deactivate_feedback" style="display:none" xmlns="http://www.w3.org/1999/html">
			<div id="pureclarity_deactivate_feedback_form">
				' . wp_nonce_field( 'pureclarity_deactivate_feedback', 'pureclarity_deactivate_feedback_nonce' ) . '
				<p>' . __( 'We\'re sorry to see you go! If you have a moment, please could you let us know why you\'re deactivating PureClarity for WooCommerce?' ) . '</p>
				<p>
					<input type="radio" value="too_expensive" name="pureclarity_feedback_reason" id="pureclarity_feedback_too_expensive"/>
					<label for="pureclarity_feedback_too_expensive">' . __( 'Too expensive' ) . '</label>
				</p>
				<p>
					<input type="radio" value="better_plugin" name="pureclarity_feedback_reason" id="pureclarity_feedback_better_plugin"/>
					<label for="pureclarity_feedback_better_plugin">' . __( 'I found a better plugin (please specify below)' ) . '</label>
				</p>
				<p>
					<input type="radio" value="technical_issue" name="pureclarity_feedback_reason" id="pureclarity_feedback_technical_issue"/>
					<label for="pureclarity_feedback_technical_issue">' . __( 'This plugin broke my site' ) . '</label>
				</p>
				<p>
					<input type="radio" value="unexpected" name="pureclarity_feedback_reason" id="pureclarity_feedback_unexpected"/>
					<label for="pureclarity_feedback_unexpected">' . __( 'It did not do what I expected it to do' ) . '</label>
				</p>
				<p>
					<input type="radio" value="missing_features" name="pureclarity_feedback_reason" id="pureclarity_feedback_missing_features"/>
					<label for="pureclarity_feedback_missing_features">' . __( 'Missing features' ) . '</label>
				</p>
				<p>
					<input type="radio" value="other" name="pureclarity_feedback_reason" id="pureclarity_feedback_other"/>
					<label for="pureclarity_feedback_other">' . __( 'Other (please specify below)' ) . '</label>
				</p>
				<p>
					<label for="pureclarity_feedback_6">' . __( 'Any additional details you\'d like to provide?' ) . '</label><br />
					<textarea name="pureclarity_feedback_notes" id="pureclarity_feedback_notes" cols="50" rows="5"/></textarea>
				</p>
				' . $orig . '								
				<a id="cancel-deactivate-pureclarity-for-woocommerce">' . __( 'Cancel' ) . '</a>
			</div>
			</div>';
		}

		return $plugin_actions;
	}
	add_filter( 'plugin_action_links', 'pureclarity_action_link', 10, 2 );
}
