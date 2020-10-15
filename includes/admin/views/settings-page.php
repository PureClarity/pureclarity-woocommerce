<?php
/**
 * Settings page html
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

?>
<div id="pc-dashboard">
	<form method="post" action="options.php">
		<?php
			settings_fields( self::SETTINGS_OPTION_GROUP_NAME ); // PureClarity_Admin.
			do_settings_sections( self::SETTINGS_SLUG );
			submit_button();
		?>
	</form>
</div>
