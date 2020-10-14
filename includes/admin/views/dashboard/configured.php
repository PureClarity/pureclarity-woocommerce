<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Dashboard class containing functions used by this view.
 *
 * @var Pureclarity_Dashboard_Page $this
 */

?>
<div id="pc-col1">
	<?php $this->get_next_steps_content(); ?>
</div>
<div id="pc-col2">
	<?php if ( $this->get_are_feeds_in_progress() ) : ?>
		<?php $this->get_feeds_content(); ?>
	<?php endif; ?>
	<?php $this->get_stats_content(); ?>
	<?php $this->get_account_status_content(); ?>
	<div id="pc-step2" class="pureclarity-info-box ">
		<div class="pureclarity-info-title">
			<h2><?php esc_html_e( 'Review', 'pureclarity' ); ?></h2>
		</div>
		<div class="pureclarity-info-content">
			<p>
				Enjoying PureClarity? Give us a review on the <a href="https://wordpress.org/support/plugin/pureclarity-for-woocommerce/reviews/#new-post">WordPress plugin directory</a>
			</p>
		</div>
	</div>
	<?php if ( ! $this->get_are_feeds_in_progress() ) : ?>
		<?php $this->get_feeds_content(); ?>
	<?php endif; ?>
</div>
