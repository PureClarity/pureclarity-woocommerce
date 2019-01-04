<?php
	$runningFeed = __("Running Feed") . "...";
	$clickButton = __("Click this button to run the %s data feed and submit it to PureClarity", 'pureclarity');
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
		<p><?php _e('Click the buttons below to manually run data feeds and submit them to PureClarity.', 'pureclarity'); ?></p>
	</div>

	<div id="product-container" class="<?php echo $this->settings->get_prodfeed_run() ? "updated" : "error" ?>">
		<p id="product-heading"><strong><?php _e('Note', 'pureclarity'); ?>: <?php echo $this->settings->get_prodfeed_run() ? _e("A feed has been successfully submitted!", 'pureclarity') : _e("A Product Feed has never been run.", 'pureclarity'); ?></strong></p>
		<p>
			<?php
				printf(
				    $clickButton,
				    "PRODUCT"
				);
			?>
		</p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-product-datafeed" value="<?php _e("Run Product Feed", 'pureclarity'); ?>"/></p>
		<p id="pureclarity-product-message" class="pureclarity-message"><?php echo $runningFeed; ?></p>
	</div>

	<div id="category-container" class="<?php echo $this->settings->get_catfeed_run() ? "updated" : "error" ?>">
		<p id="category-heading"><strong><?php _e('Note', 'pureclarity'); ?>: <?php echo $this->settings->get_catfeed_run() ? _e("A feed has been successfully submitted!", 'pureclarity') : _e("A Category Feed has never been run.", 'pureclarity'); ?></strong></p>
		<p>
			<?php
				printf(
				    $clickButton,
				    "CATEGORY"
				);
			?>
		</p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-category-datafeed" value="<?php _e("Run Category Feed", 'pureclarity'); ?>"/></p>
		<p id="pureclarity-category-message" class="pureclarity-message"><?php echo $runningFeed; ?></p>
	</div>

	<!-- <div id="brand-container" class="<?php echo $this->settings->get_brandfeed_run() ? "updated" : "error" ?>">
		<p id="brand-heading"><strong><?php _e('Note', 'pureclarity'); ?>: <?php echo $this->settings->get_brandfeed_run() ? _e("A feed has been successfully submitted!", 'pureclarity') : _e("A Brand Feed has never been run.", 'pureclarity'); ?></strong></p>
		<p>
			<?php
				printf(
				    $clickButton,
				    "BRAND"
				);
			?>
		</p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-brand-datafeed" value="<?php _e("Run Brand Feed", 'pureclarity'); ?>"/></p>
		<p id="pureclarity-brand-message" class="pureclarity-message"><?php echo $runningFeed; ?></p>
	</div> -->

	<div id="user-container" class="<?php echo $this->settings->get_userfeed_run() ? "updated" : "error" ?>">
		<p id="user-heading"><strong><?php _e('Note', 'pureclarity'); ?>: <?php echo $this->settings->get_userfeed_run() ? _e("A feed has been successfully submitted!", 'pureclarity') : _e("A User Feed has never been run.", 'pureclarity'); ?></strong></p>
		<p>
			<?php
				printf(
				    $clickButton,
				    "USER"
				);
			?>
		</p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-user-datafeed" value="<?php _e("Run User Feed", 'pureclarity'); ?>"/></p>
		<p id="pureclarity-user-message" class="pureclarity-message"><?php echo $runningFeed; ?></p>
	</div>

	<div id="order-container" class="<?php echo $this->settings->get_orderfeed_run() ? "updated" : "error" ?>">
		<p id="order-heading"><strong><?php _e('Note', 'pureclarity'); ?>: <?php echo $this->settings->get_orderfeed_run() ? _e("A feed has been successfully submitted!", 'pureclarity') : _e("An Order Feed has never been run.", 'pureclarity'); ?></strong></p>
		<p>
			<?php
				printf(
				    $clickButton,
				    "ORDER"
				);
			?>
		</p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-order-datafeed" value="<?php _e("Run Order Feed", 'pureclarity'); ?>"/></p>
		<p id="pureclarity-order-message" class="pureclarity-message"><?php echo $runningFeed; ?></p>
	</div>
	
</div>