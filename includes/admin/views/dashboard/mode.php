<?php
/**
 * Display mode HTML.
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */
?>
<div class="pureclarity-info-box">
	<div class="pureclarity-info-title">
		<h2>
			<?php esc_html_e( 'Display Mode', 'pureclarity' ); ?>
		</h2>
	</div>
	<div class="pureclarity-info-content">
		<p>
			<strong><?php esc_html_e( 'Current Display Mode:', 'pureclarity' ); ?></strong>
			<?php if ( $this->get_mode() === self::MODE_LIVE ) : ?>
				<?php esc_html_e( 'Live', 'pureclarity' ); ?>
			<?php endif; ?>
			<?php if ( $this->get_mode() === self::MODE_ADMIN_ONLY ) : ?>
				<?php esc_html_e( 'Admin-only', 'pureclarity' ); ?>
			<?php endif; ?>
			<?php if ( $this->get_mode() === self::MODE_DISABLED ) : ?>
				<?php esc_html_e( 'Disabled', 'pureclarity' ); ?>
			<?php endif; ?>
		</p>
		<p>
			<?php if ( $this->get_mode() === self::MODE_LIVE ) : ?>
				<?php esc_html_e( 'In "Live" mode all users will see PureClarity recommenders. Use the buttons below to change the mode.', 'pureclarity' ); ?>
			<?php endif; ?>
			<?php if ( $this->get_mode() === self::MODE_ADMIN_ONLY ) : ?>
				<?php esc_html_e( '"Admin-only" mode means only logged in admin users will see PureClarity recommenders. Use the buttons below to change the mode.', 'pureclarity' ); ?>
			<?php endif; ?>
			<?php if ( $this->get_mode() === self::MODE_DISABLED ) : ?>
				<?php esc_html_e( '"Disabled" mode means no users will see PureClarity recommenders. Use the buttons below to change the mode.', 'pureclarity' ); ?>
			<?php endif; ?>
		</p>
		<p>
			<?php wp_nonce_field( 'pureclarity_switch_mode', 'pureclarity-switch-mode-nonce' ); ?>
			<?php if ( $this->get_mode() !== self::MODE_LIVE ) : ?>
			<button id="pc-mode-go-live-button" type="button"
					title="<?php esc_html_e( 'Live mode', 'pureclarity' ); ?>"
					class="pc-button">
				<?php esc_html_e( 'Live mode', 'pureclarity' ); ?>
			</button>
			<?php endif; ?>
			<?php if ( $this->get_mode() !== self::MODE_ADMIN_ONLY ) : ?>
			<button id="pc-mode-admin-only-button" type="button"
					title="<?php esc_html_e( 'Admin-only', 'pureclarity' ); ?>"
					class="pc-button">
				<?php esc_html_e( 'Admin-only', 'pureclarity' ); ?>
			</button>
			<?php endif; ?>
			<?php if ( $this->get_mode() !== self::MODE_DISABLED ) : ?>
			<button id="pc-mode-disabled-button" type="button"
					title="<?php esc_html_e( 'Disable', 'pureclarity' ); ?>"
					class="pc-button">
				<?php esc_html_e( 'Disable', 'pureclarity' ); ?>
			</button>
			<?php endif; ?>
		</p>
	</div>
</div>