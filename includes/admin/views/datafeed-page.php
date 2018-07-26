<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice">
		<p>Click the buttons below to manually create data feeds and submit them to PureClarity. <strong>After this has been done product data will keep itself up to date.</strong></p>
	</div>

	<div class="<?php echo $this->settings->get_prodfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_prodfeed_run() ? "Note: A feed has succesfully been run!" : "Note: A Product Feed has never been run." ?></strong></p>
		<p>Click this button to run the PRODUCT data feed and submit it to PureClarity</strong></p>
		<p><button name="pureclarity_product_feed" class="button button-primar pureclarity-product-datafeed">Run Product Feed</button></p>
	</div>

	<div class="<?php echo $this->settings->get_catfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_catfeed_run() ? "Note: A feed has succesfully been run!" : "Note: A Category Feed has never been run." ?></strong></p>
		<p>Click this button to run the CATEGORY data feed and submit it to PureClarity</strong></p>
		<p><button name="pureclarity_product_feed" class="button button-primar pureclarity-product-datafeed">Run Category Feed</button></p>
	</div>

	<div class="<?php echo $this->settings->get_brandfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_brandfeed_run() ? "Note: A feed has succesfully been run!" : "Note: A Brand Feed has never been run." ?></strong></p>
		<p>Click this button to run the BRAND data feed and submit it to PureClarity</strong></p>
		<p><button name="pureclarity_product_feed" class="button button-primar pureclarity-product-datafeed">Run Brand Feed</button></p>
	</div>

	<div class="<?php echo $this->settings->get_userfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_userfeed_run() ? "Note: A feed has succesfully been run!" : "Note: A User Feed has never been run." ?></strong></p>
		<p>Click this button to run the USER data feed and submit it to PureClarity</strong></p>
		<p><button name="pureclarity_product_feed" class="button button-primar pureclarity-product-datafeed">Run User Feed</button></p>
	</div>
	
</div>