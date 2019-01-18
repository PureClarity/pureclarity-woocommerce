<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form method="post" action="options.php">
		<?php
			settings_fields( self::SETTINGS_OPTION_GROUP_NAME ); // PureClarity_Admin
			do_settings_sections( self::SETTINGS_SLUG ); 
			submit_button();
		?>
	</form>
</div>