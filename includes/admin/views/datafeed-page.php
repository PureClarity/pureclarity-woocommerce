<style>
	.pureclarity-message {display:none; color:#0085ba; font-weight:bold; }
</style>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice">
		<p>Click the buttons below to manually create data feeds and submit them to PureClarity. <strong>After this has been done product data will keep itself up to date.</strong></p>
	</div>

	<div class="<?php echo $this->settings->get_prodfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_prodfeed_run() ? "Note: A feed has succesfully been submitted!" : "Note: A Product Feed has never been run." ?></strong></p>
		<p>Click this button to run the PRODUCT data feed and submit it to PureClarity</strong></p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-product-datafeed" value="Run Product Feed"/></p>
		<p id="pureclarity-product-message" class="pureclarity-message">Running Feed...</p>
	</div>

	<div class="<?php echo $this->settings->get_catfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_catfeed_run() ? "Note: A feed has succesfully been submitted!" : "Note: A Category Feed has never been run." ?></strong></p>
		<p>Click this button to run the CATEGORY data feed and submit it to PureClarity</strong></p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-category-datafeed" value="Run Category Feed"/></p>
		<p id="pureclarity-category-message" class="pureclarity-message">Running Feed...</p>
	</div>

	<!-- <div class="<?php echo $this->settings->get_brandfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_brandfeed_run() ? "Note: A feed has succesfully been submitted!" : "Note: A Brand Feed has never been run." ?></strong></p>
		<p>Click this button to run the BRAND data feed and submit it to PureClarity</strong></p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-brand-datafeed" value="Run Brand Feed"/></p>
		<p id="pureclarity-brand-message" class="pureclarity-message">Running Feed...</p>
	</div> -->

	<div class="<?php echo $this->settings->get_userfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_userfeed_run() ? "Note: A feed has succesfully been submitted!" : "Note: A User Feed has never been run." ?></strong></p>
		<p>Click this button to run the USER data feed and submit it to PureClarity</strong></p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-user-datafeed" value="Run User Feed"/></p>
		<p id="pureclarity-user-message" class="pureclarity-message">Running Feed...</p>
	</div>

	<div class="<?php echo $this->settings->get_userfeed_run() ? "updated" : "error" ?>">
		<p><strong><?php echo $this->settings->get_userfeed_run() ? "Note: A feed has succesfully been submitted!" : "Note: An Order Feed has never been run." ?></strong></p>
		<p>Click this button to run the ORDER data feed and submit it to PureClarity</strong></p>
		<p><input type="button" class="button button-primary pureclarity-buttons pureclarity-order-datafeed" value="Run Order Feed"/></p>
		<p id="pureclarity-order-message" class="pureclarity-message">Running Feed...</p>
	</div>
	
</div>