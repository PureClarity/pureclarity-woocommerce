<?php
/**
 * Next Steps HTML
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */
?>
<div id="pureclarity-next-steps">
	<?php wp_nonce_field( 'pureclarity_complete_next_step', 'pureclarity-complete-next-step-nonce' ); ?>
	<?php foreach ( $dashboard['NextSteps'] as $step ) : ?>
		<div id="<?php echo esc_attr( $step['id'] ); ?>" class="pureclarity-info-box">
			<?php if ( isset( $step['title'] ) ) : ?>
				<div id="<?php echo esc_attr( $step['id'] ); ?>-title" class="pureclarity-info-title">
					<h2><?php echo esc_html( $step['title'] ); ?></h2>
				</div>
			<?php endif; ?>
			<div class="pureclarity-info-content">
				<?php if ( isset( $step['description'] ) ) : ?>
					<p id="<?php echo esc_attr( $step['id'] ); ?>-description" class="pureclarity-next-step-description"><?php echo $step['description']; ?></p>
				<?php endif; ?>

				<?php if ( isset( $step['vimeoLink'] ) ) : ?>
					<div class="pureclarity-next-step-vimeo"><iframe src="<?php echo esc_attr( $step['vimeoLink'] ); ?>?title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe></div>
				<?php endif; ?>

				<?php if ( isset( $step['customHTML'] ) ) : ?>
					<div class="pureclarity-next-step-customhtml"><?php echo $step['customHTML']; ?></div>
				<?php endif; ?>
				<?php if ( isset( $step['actions'] ) ) : ?>
					<div class="pureclarity-next-step-actions">
						<?php foreach ( $step['actions'] as $step_action ) : ?>
							<p class="pureclarity-next-step-action">
								<?php if ( isset( $step_action['actionLinkIsAdmin'] ) && $step_action['actionLinkIsAdmin'] ) : ?>
									<a href="<?php echo esc_attr( $this->get_admin_url() ); ?><?php echo esc_attr( $step_action['actionLink'] ); ?>" target="_blank" class="pc-button pc-action" id="<?php echo esc_attr( $step['id'] ); ?>"><?php echo esc_html( $step_action['actionText'] ); ?></a>
								<?php elseif ( isset( $step_action['actionLinkIsPlugin'] ) && $step_action['actionLinkIsPlugin'] ) : ?>
									<a href="<?php echo esc_url( admin_url( $step_action['actionLink'] ) ); ?>" target="_blank" class="pc-button pc-action" id="<?php echo esc_attr( $step['id'] ); ?>"><?php echo esc_html( $step_action['actionText'] ); ?></a>
								<?php else : ?>
									<a href="<?php echo esc_attr( $step_action['actionLink'] ); ?>" target="_blank" class="pc-button pc-action" id="<?php echo esc_attr( $step['id'] ); ?>"><?php echo esc_html( $step_action['actionText'] ); ?></a>
								<?php endif; ?>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
