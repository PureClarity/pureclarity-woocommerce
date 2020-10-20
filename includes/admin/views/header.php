<?php
/**
 * Header bar HTML.
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */

?>
<div id="pc-title-bar">
	<div class="pureclarity-title-wrapper">
		<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/logo.png' ); ?>" alt="PureClarity" />
	</div>
	<div id="pc-title-bar-buttons">
		<p id="pc-button-settings" class="pc-topbar-button">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pureclarity-settings' ) ); ?>" title="<?php esc_html_e( 'Settings', 'pureclarity' ); ?>"><?php esc_html_e( 'Settings', 'pureclarity' ); ?></a>
		</p>
		<p id="pc-button-documentation" class="pc-topbar-button">
			<a href="https://www.pureclarity.com/docs/woocommerce/" target="_blank" title="<?php esc_html_e( 'Documentation', 'pureclarity' ); ?>"><?php esc_html_e( 'Documentation', 'pureclarity' ); ?></a>
		</p>
		<p id="pc-button-support" class="pc-topbar-button">
			<a href="mailto:support@pureclarity.com?subject=WooCommerce%202%20Support%20Issue&body=Plugin%20Version:%20<?php echo esc_attr( $this->get_plugin_version() ); ?>%0D%0AWooCommerce%20Version:%20<?php echo esc_attr( $this->get_woocommerce_version() ); ?>%0D%0AWordPress%20Version:%20<?php echo esc_attr( $this->get_wordpress_version() ); ?>%0D%0AStore Name: [PLEASE ENTER]%0D%0A" title="<?php esc_html_e( 'Support', 'pureclarity' ); ?>"><?php esc_html_e( 'Support', 'pureclarity' ); ?></a>
		</p>
	</div>
</div>
