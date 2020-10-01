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
<div id="pureclarity-headline-stats" class="pureclarity-info-box pc-col-box">
	<div class="pureclarity-info-title">
		<h2>Performance</h2>
	</div>
	<div class="pureclarity-info-content">
		<?php foreach ( $dashboard['Stats'] as $stat_type => $stat ) : ?>
			<div id="pureclarity-headline-stat-' . $type . '" class="pureclarity-headline-stat">
				<h2 id="pureclarity-headline-stat-' . $type . '-title" class="pureclarity-headline-stat-subtitle"><?php echo $this->get_stat_title($stat_type); ?></h2>
				<?php foreach ( $this->stats_to_show as $key => $label ) : ?>
					<?php if ( isset( $stat[ $key ] ) ) : ?>
						<p class="pureclarity-headline-stat-row">
							<span class="pureclarity-stat-label"><?php echo $label; ?></span>:
							<span class="pureclarity-stat-value"><?php echo $this->get_stat_display($key, $stat[ $key ]); ?></span>
						</p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<div class="pureclarity-clearfix"></div>
		<p>PureClarity provides rich analytics and insights into your store. Here is a <strong>real time summary</strong> for today and the last 30 days. Use the link above to view the full range of analytics in the PureClarity Admin.</p>
	</div>
</div>
<style>


	.pureclarity-clearfix {
		clear:both;
	}
</style>