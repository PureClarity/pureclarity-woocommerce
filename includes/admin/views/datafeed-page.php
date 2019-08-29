<?php
/**
 * Data Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

?>
<style>
	.pureclarity-message { 
		display: none;
		color: #0085ba;
		font-weight: bold;
	}
</style>

<div class="wrap">

	<?php wp_nonce_field( 'pureclarity-submit-data-feed', 'pureclarity-feed-runner-nonce' ); ?>

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice">
		<p><?php esc_html_e( 'Click the buttons below to manually run data feeds and submit them to PureClarity.', 'pureclarity' ); ?></p>
	</div>

	<div id="product-container" class="<?php echo $this->settings->is_product_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="product-heading">
			<strong><?php esc_html_e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_product_feed_sent() ? esc_html_e( 'Product feed has been previously submitted.', 'pureclarity' ) : esc_html_e( 'Product feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				echo esc_html_e( 'Click button to run the PRODUCT data feed and submit it to PureClarity', 'pureclarity' );
			?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-product-datafeed" value="<?php esc_html_e( 'Run Product Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-product-message" class="pureclarity-message">
			<?php echo esc_html_e( 'Running Feed...', 'pureclarity' ); ?>
		</p>
	</div>

	<div id="category-container" class="<?php echo $this->settings->is_category_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="category-heading">
			<strong><?php esc_html_e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_category_feed_sent() ? esc_html_e( 'Category feed has been previously submitted.', 'pureclarity' ) : esc_html_e( 'Category feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				echo esc_html_e( 'Click button to run the CATEGORY data feed and submit it to PureClarity', 'pureclarity' );
			?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-category-datafeed" value="<?php esc_html_e( 'Run Category Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-category-message" class="pureclarity-message">
			<?php echo esc_html_e( 'Running Feed...', 'pureclarity' ); ?>
		</p>
	</div>

	<div id="user-container" class="<?php echo $this->settings->is_user_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="user-heading">
			<strong><?php esc_html_e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_user_feed_sent() ? esc_html_e( 'User feed has been previously submitted.', 'pureclarity' ) : esc_html_e( 'User feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				echo esc_html_e( 'Click button to run the USER data feed and submit it to PureClarity', 'pureclarity' );
			?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-user-datafeed" value="<?php esc_html_e( 'Run User Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-user-message" class="pureclarity-message">
			<?php echo esc_html_e( 'Running Feed...', 'pureclarity' ); ?>
		</p>
	</div>

	<div id="order-container" class="<?php echo $this->settings->is_order_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="order-heading">
			<strong><?php esc_html_e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_order_feed_sent() ? esc_html_e( 'A feed has been successfully submitted!', 'pureclarity' ) : esc_html_e( 'Order feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				echo esc_html_e( 'Click button to run the ORDER data feed and submit it to PureClarity', 'pureclarity' );
			?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-order-datafeed" value="<?php esc_html_e( 'Run Order Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-order-message" class="pureclarity-message">
			<?php echo esc_html_e( 'Running Feed...', 'pureclarity' ); ?>
		</p>
	</div>
</div>
