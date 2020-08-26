<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** @var PureClarity_Dashboard_Page $this */

?>
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700" rel="stylesheet" type="text/css" media="not print" />
<h1 class="pureclarity-title-wrapper"><span class="pureclarity-title"><?php esc_html_e( 'PureClarity', 'pureclarity' ); ?></span></h1>
<div id="pc-dashboard">
	<input type="hidden" id="pc-current-state" value="<?php echo $this->get_state_name(); ?>"/>
	<?php if ( $this->is_not_configured() ) : ?>
		<?php $this->get_signup_content(); ?>
	<?php endif; ?>
	<div id="pc-waiting"
		<?php if ( $this->is_waiting() === false ) : ?>
			style="display:none"
		<?php endif; ?>>
		<?php $this->get_waiting_content(); ?>
	</div>
	<div id="pc-content"
		<?php if ( $this->is_not_configured() || $this->is_waiting() ) : ?>
			style="display:none"
		<?php endif; ?>>
		<?php $this->get_configured_content(); ?>
	</div>
	<div id="pc-clearfix"></div>
</div>
<div id="pc-version">
	<div id="pc-logo"><span>PureClarity</span></div>
	<div id="pc-version-number">
		Version: <?php echo $this->get_plugin_version(); ?>
	</div>
	<?php if ($this->is_up_to_date()): ?>
		<div id="pc-version-status" class="pc-up-to-date">(up to date)</div>
	<?php else: ?>
		<div id="pc-version-status" class="pc-out-of-date">
			(new version available - <?php echo $this->get_new_version(); ?>)<br />
			<a href="https://www.pureclarity.com/docs/woocommerce/#update" target="_blank">
				How to update
			</a>
		</div>
	<?php endif; ?>
</div>
