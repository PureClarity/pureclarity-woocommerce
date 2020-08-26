<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

?>
<div id="pc-sign-up-waiting" class="pc-box">
	<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Setting up account', 'pureclarity' ); ?></h3></div>
	<div class="pc-box-content">
		<div class="pc-loader">
			<div class="pc-loader-outer"></div>
			<div class="pc-loader-middle"></div>
			<div class="pc-loader-inner"></div>
		</div>
		<p><?php esc_html_e( 'We\'ll have you up and running', 'pureclarity' ); ?></p>
		<p><?php esc_html_e( 'before you can say increased revenue', 'pureclarity' ); ?></p>
	</div>
</div>
<input type="hidden" id="pc-sign-up-waiting-call-url" value="" />

