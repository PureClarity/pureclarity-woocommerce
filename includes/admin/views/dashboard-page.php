<?php
/**
 * Dashboard page base HTML.
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */

?>
<div id="pc-dashboard">
	<?php if ( $this->show_welcome_banner() ) : ?>
		<div id="pc-banner-welcome" class="pc-banner">
			<h3><?php esc_html_e( 'Thanks for signing up for a free trial. Your site data is now being synced to PureClarity.', 'pureclarity' ); ?></h3>
		</div>
	<?php endif; ?>
	<?php if ( $this->show_manual_welcome_banner() ) : ?>
		<div id="pc-banner-welcome" class="pc-banner">
			<h3><?php esc_html_e( 'Your PureClarity account is now linked to this store. Your site data is now being synced to PureClarity.', 'pureclarity' ); ?></h3>
		</div>
	<?php endif; ?>
	<div id="pc-banner-getting-started" class="pc-banner"
			<?php if ( ! $this->show_getting_started_banner() ) : ?>
				style="display:none"
			<?php endif; ?>
	>
		<h3><?php esc_html_e( 'Your site data has been sent to PureClarity. Use the panels below to get started.', 'pureclarity' ); ?></h3>
	</div>
	<input type="hidden" id="pc-current-state" value="<?php echo esc_attr( $this->get_state_name() ); ?>"/>
	<?php if ( $this->is_not_configured() || $this->is_waiting() ) : ?>
		<?php $this->get_signup_content(); ?>
	<?php else : ?>
		<?php $this->get_configured_content(); ?>
	<?php endif; ?>
</div>
