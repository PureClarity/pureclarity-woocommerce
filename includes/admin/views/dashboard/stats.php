<?php
/**
 * Performance Stats HTML
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */
?>
<div id="pureclarity-headline-stats" class="pureclarity-info-box pc-col-box">
	<div class="pureclarity-info-title">
		<h2><?php esc_html_e( 'Performance', 'pureclarity' ); ?></h2>
	</div>
	<div class="pureclarity-info-content">
		<p class="pureclarity-stats-text">
			<?php esc_html_e( 'PureClarity provides rich analytics and insights into your store. Here is a ', 'pureclarity' ); ?><strong><?php esc_html_e( 'real time summary', 'pureclarity' ); ?></strong><?php esc_html_e( ' for today and the last 30 days.', 'pureclarity' ); ?>
		</p>
		<div class="pureclarity-headline-stats-tabs">
			<?php foreach ( $dashboard['Stats'] as $stat_type => $stat ) : ?>
				<span id="pureclarity-headline-stat-<?php echo esc_attr( $stat_type ); ?>"
					class="pureclarity-headline-stat-tab
					<?php if ( 'today' === $stat_type ) : ?>
						pureclarity-headline-stat-active
					<?php endif; ?>">
					<?php echo esc_html( $this->get_stat_title( $stat_type ) ); ?>
				</span>
			<?php endforeach; ?>
		</div>
		<?php foreach ( $dashboard['Stats'] as $stat_type => $stat ) : ?>
			<div id="pureclarity-headline-stat-<?php echo esc_attr( $stat_type ); ?>-content" class="pureclarity-headline-stat"
				<?php if ( 'today' !== $stat_type ) : ?>
					style="display:none"
				<?php endif; ?>>
				<?php if ( isset( $stat['RecommenderProductTotal'], $stat['RecommenderProductTotalDisplay'], $stat['OrderCount'], $stat['SalesTotalDisplay'] ) && $stat['RecommenderProductTotal'] > 0 ) : ?>
				<p class="pureclarity-headline-stat-rec-total">
					<?php esc_html_e( 'PureClarity Recommenders have made:', 'pureclarity' ); ?>
					<span class="pureclarity-rec-total"><?php echo esc_html( $stat['RecommenderProductTotalDisplay'] ); ?></span>
					<?php esc_html_e( 'From ', 'pureclarity' ); ?>
					<span class="pureclarity-sub-total"><?php echo esc_html( $stat['OrderCount'] ); ?></span>
					<?php esc_html_e( ' Orders Totalling ', 'pureclarity' ); ?>
					<span class="pureclarity-sub-total"><?php echo esc_html( $stat['SalesTotalDisplay'] ); ?></span>
				</p>
				<?php endif; ?>
				<?php foreach ( $this->stats_to_show as $key => $label ) : ?>
					<?php if ( isset( $stat[ $key ] ) ) : ?>
						<p class="pureclarity-headline-stat-row">
							<span class="pureclarity-stat-label"><?php echo esc_html( $label ); ?>:</span>
							<span class="pureclarity-stat-value"><?php echo esc_html( $this->get_stat_display( $key, $stat[ $key ] ) ); ?></span>
						</p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<div class="pureclarity-clearfix"></div>
		<p class="pureclarity-stats-button">
			<a class="pc-button" href="<?php echo esc_url( $this->get_admin_url() ); ?>analytics/site" target="_blank"><?php esc_html_e( 'View the full range of analytics in the PureClarity Admin', 'pureclarity' ); ?></a>
		</p>
	</div>
</div>
