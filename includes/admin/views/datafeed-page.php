<?php
/**
 * Data Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

$running_feed = __( 'Running Feed' ) . '...';
$click_button = __( 'Click button to run the %s data feed and submit it to PureClarity', 'pureclarity' );
?>
<style>
	.pureclarity-message { 
		display: none;
		color: #0085ba;
		font-weight: bold;
	}
</style>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice">
		<p><?php _e( 'Click the buttons below to manually run data feeds and submit them to PureClarity.', 'pureclarity' ); ?></p>
	</div>

	<div id="product-container" class="<?php echo $this->settings->is_product_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="product-heading">
			<strong><?php _e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_product_feed_sent() ? _e( 'Product feed has been previously submitted.', 'pureclarity' ) : _e( 'Product feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				printf(
					$click_button,
					'PRODUCT'
				);
				?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-product-datafeed" value="<?php _e( 'Run Product Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-product-message" class="pureclarity-message">
			<?php echo $running_feed; ?>
		</p>
	</div>

	<div id="category-container" class="<?php echo $this->settings->is_category_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="category-heading">
			<strong><?php _e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_category_feed_sent() ? _e( 'Category feed has been previously submitted.', 'pureclarity' ) : _e( 'Category feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				printf(
					$click_button,
					'CATEGORY'
				);
				?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-category-datafeed" value="<?php _e( 'Run Category Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-category-message" class="pureclarity-message">
			<?php echo $running_feed; ?>
		</p>
	</div>

	<!-- <div id="brand-container" class="<?php echo $this->settings->is_brand_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="brand-heading">
			<strong><?php _e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_brand_feed_sent() ? _e( 'Brand feed has been previously submitted.', 'pureclarity' ) : _e( 'Brand feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				printf(
					$click_button,
					'BRAND'
				);
				?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-brand-datafeed" value="<?php _e( 'Run Brand Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-brand-message" class="pureclarity-message">
			<?php echo $running_feed; ?>
		</p>
	</div> -->

	<div id="user-container" class="<?php echo $this->settings->is_user_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="user-heading">
			<strong><?php _e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_user_feed_sent() ? _e( 'User feed has been previously submitted.', 'pureclarity' ) : _e( 'User feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				printf(
					$click_button,
					'USER'
				);
				?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-user-datafeed" value="<?php _e( 'Run User Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-user-message" class="pureclarity-message">
			<?php echo $running_feed; ?>
		</p>
	</div>

	<div id="order-container" class="<?php echo $this->settings->is_order_feed_sent() ? 'updated' : 'error'; ?>">
		<p id="order-heading">
			<strong><?php _e( 'Note', 'pureclarity' ); ?>: <?php echo $this->settings->is_order_feed_sent() ? _e( 'A feed has been successfully submitted!', 'pureclarity' ) : _e( 'Order feed has never been run.', 'pureclarity' ); ?></strong>
		</p>
		<p>
			<?php
				printf(
					$click_button,
					'ORDER'
				);
				?>
		</p>
		<p>
			<input type="button" class="button button-primary pureclarity-buttons pureclarity-order-datafeed" value="<?php _e( 'Run Order Feed', 'pureclarity' ); ?>"/>
		</p>
		<p id="pureclarity-order-message" class="pureclarity-message">
			<?php echo $running_feed; ?>
		</p>
	</div>
	
</div>
