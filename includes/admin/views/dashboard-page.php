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
<div id="pc-dashboard">
	<input type="hidden" id="pc-current-state" value="<?php echo esc_attr( $this->get_state_name() ); ?>"/>
	<?php if ( $this->is_not_configured() || $this->is_waiting() ) : ?>
		<?php $this->get_signup_content(); ?>
	<?php else : ?>
		<?php $this->get_configured_content(); ?>
	<?php endif; ?>
</div>
