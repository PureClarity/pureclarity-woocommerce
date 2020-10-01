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
<div id="pureclarity-next-steps">
	<?php foreach ( $dashboard['NextSteps'] as $step ) : ?>
		<div id="<?php echo esc_attr( $step['id'] ); ?>" class="pureclarity-info-box">
			<?php if ( isset( $step['title'] ) ) : ?>
				<div id="<?php echo esc_attr( $step['id'] ); ?>-title" class="pureclarity-info-title">
					<h2><?php esc_html_e( $step['title'], 'pureclarity' ); ?></h2>
				</div>
			<?php endif; ?>

			<div class="pureclarity-info-content">

				<?php if ( isset( $step['description'] ) ) : ?>
					<p id="<?php echo esc_attr( $step['id'] ); ?>-description" class="pureclarity-next-step-description"><?php esc_html_e( $step['description'], 'pureclarity' ); ?></p>
				<?php endif; ?>

				<?php if ( isset( $step['vimeoLink'] ) ) : ?>
					<div class="pureclarity-next-step-vimeo"><iframe src="<?php echo esc_attr( $step['vimeoLink'] ); ?>?title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe></div>
				<?php endif; ?>

				<?php if ( isset( $step['customHTML'] ) ) : ?>
					<div class="pureclarity-next-step-customhtml"><?php echo $step['customHTML']; ?></div>
				<?php endif; ?>
				<?php if ( isset( $step['actions'] ) ) : ?>
					<div class="pureclarity-next-step-actions">
						<?php foreach ( $step['actions'] as $action ) : ?>
							<p class="pureclarity-next-step-action">
								<?php if ( isset( $step['actionLinkIsAdmin'] ) && $action['actionLinkIsAdmin'] ) : ?>
									<a href="http://localhost:3014/<?php echo esc_attr($action['actionLink']); ?>" target="_blank" class="pc-button"><?php esc_html_e( $action['actionText'], 'pureclarity' ); ?></a>
								<?php else: ?>
									<a href="<?php echo esc_attr($action['actionLink']); ?>" target="_blank" class="pc-button"><?php esc_html_e( $action['actionText'], 'pureclarity' ); ?></a>
								<?php endif; ?>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
	<?php endforeach; ?>
</div>
