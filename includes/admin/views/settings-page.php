<?php
/**
 * Settings page html
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/** Settings page class containing functions used by this view. @var PureClarity_Settings_Page $this */
?>
<div id="pc-dashboard">
	<form method="post" action="options.php">
		<?php
			settings_fields( self::SETTINGS_OPTION_GROUP_NAME );
			do_settings_sections( self::SETTINGS_SLUG );
			submit_button();
		?>
	</form>
</div>
