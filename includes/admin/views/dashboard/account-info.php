<?php
/**
 * Account Status HTML.
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */

if ( isset( $dashboard['Account']['IsSignedUp'] ) && 0 === $dashboard['Account']['IsSignedUp'] ) : ?>
	<div id="pureclarity-account-info" class="pureclarity-info-box <?php echo esc_attr( $this->get_free_trial_class( $dashboard['Account']['DaysLeft'] ) ); ?>">
		<div class="pureclarity-info-title">
			<h2>
				<?php esc_html_e( 'Free Trial Status', 'pureclarity' ); ?>:
				<?php if ( $dashboard['Account']['DaysLeft'] > 0 ) : ?>
					<?php echo esc_html( $dashboard['Account']['DaysLeft'] ); ?> <?php esc_html_e( 'days left', 'pureclarity' ); ?>
				<?php elseif ( 0 === $dashboard['Account']['DaysLeft'] ) : ?>
					<?php esc_html_e( 'Expires today', 'pureclarity' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Expired', 'pureclarity' ); ?>:
				<?php endif; ?>
			</h2>
		</div>
		<div class="pureclarity-info-content">
			<p>
				<?php if ( $dashboard['Account']['DaysLeft'] > 0 ) : ?>
					<?php esc_html_e( 'You have', 'pureclarity' ); ?> <?php echo esc_html( $dashboard['Account']['DaysLeft'] ); ?> <?php esc_html_e( 'days left of your Free Trial. Activate your subscription now.', 'pureclarity' ); ?></p>
				<?php elseif ( 0 === $dashboard['Account']['DaysLeft'] ) : ?>
					<?php esc_html_e( 'Your free trial expires today. Activate your subscription now or your account will be suspended.', 'pureclarity' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Your free trial has expired and will be suspended. Activate your subscription now.', 'pureclarity' ); ?>
				<?php endif; ?>
			<p><?php esc_html_e( 'Ends', 'pureclarity' ); ?>: <?php echo esc_html( $this->get_date( $dashboard['Account']['FreeTrialEndDate'] ) ); ?></p>
			<p class="free-trial-signup"><a class="pc-button" href="<?php echo esc_url( $this->get_admin_url() ); ?>my-account/billing" target="_blank"><?php esc_html_e( 'Activate your subscription now', 'pureclarity' ); ?></a></p>
		</div>
	</div>
	<?php
endif;
