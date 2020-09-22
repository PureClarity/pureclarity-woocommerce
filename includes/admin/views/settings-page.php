<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

?>
<div id="pc-title-bar">
	<div class="pureclarity-title-wrapper">
		<img src="http://woocommerce.test/site2/wp-content/plugins/pureclarity/admin/css/images/logo.png" alt="PureClarity" />
	</div>
	<div id="pc-title-bar-buttons">
		<p id="pc-button-settings" class="pc-topbar-button">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pureclarity-settings' ) ); ?>" title="<?php esc_html_e( 'Settings', 'pureclarity' ); ?>"><?php esc_html_e( 'Settings', 'pureclarity' ); ?></a>
		</p>
		<p id="pc-button-documentation" class="pc-topbar-button">
			<a href="https://www.pureclarity.com/docs/woocommerce/" target="_blank" title="<?php esc_html_e( 'Documentation', 'pureclarity' ); ?>"><?php esc_html_e( 'Documentation', 'pureclarity' ); ?></a>
		</p>
		<p id="pc-button-support" class="pc-topbar-button">
			<a href=""><?php esc_html_e( 'Support', 'pureclarity' ); ?></a>
		</p>
	</div>
</div>
<div id="pc-dashboard">
	<form method="post" action="options.php">
		<?php
			settings_fields( self::SETTINGS_OPTION_GROUP_NAME ); // PureClarity_Admin.
			do_settings_sections( self::SETTINGS_SLUG );
			submit_button();
		?>
	</form>
</div>
