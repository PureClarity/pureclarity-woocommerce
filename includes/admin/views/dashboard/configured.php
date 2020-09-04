<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Dashboard class containing functions used by this view.
 *
 * @var Pureclarity_Dashboard_Page $this
 */
?>
<div id="pc-thanks">
	<p><?php esc_html_e( 'Thank you for installing the PureClarity plugin for WooCommerce.', 'pureclarity' ); ?></p>
	<p><?php esc_html_e( 'Please follow the 3 steps below to get up and running with PureClarity.', 'pureclarity' ); ?></p>
</div>
<div id="pc-layer1">
	<div id="pc-step1" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Step 1', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'Style your templates using the Recommender Designer within the PureClarity Admin area', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<a class="regular-button" href="https://admin.pureclarity.com" target="_blank" title="<?php esc_html_e( 'Go to PureClarity Admin', 'pureclarity' ); ?>"><?php esc_html_e( 'Go to PureClarity Admin', 'pureclarity' ); ?></a>
			</p>
		</div>
	</div>
	<div id="pc-step2" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Step 2', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'By default, Zones are only shown to admin users on the frontend. When you are ready, press the "Go live" button to show Zones to all customers', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<button id="pc-go-live-button" type="button"
						title="<?php esc_html_e( 'Go Live', 'pureclarity' ); ?>"
						class="action-default scalable primary">
					<?php esc_html_e( 'Go Live', 'pureclarity' ); ?>
				</button>
			</p>
		</div>
	</div>
	<div id="pc-step3" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Step 3', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'Customize your Campaigns and Segments within the PureClarity Admin Area to optimize the performance of your Zones', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<a class="regular-button" href="https://admin.pureclarity.com" target="_blank" title="<?php esc_html_e( 'Go to PureClarity Admin', 'pureclarity' ); ?>"><?php esc_html_e( 'Go to PureClarity Admin', 'pureclarity' ); ?></a>
			</p>
		</div>
	</div>
</div>
<div id="pc-layer2">
	<div id="pc-feeds" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Data Feeds', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'Full data feeds are sent nightly to PureClarity to ensure data is up to date in our system, below is the status of each of the data feed types:', 'pureclarity' ); ?></p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Products', 'pureclarity' ); ?>:</span>
				<span id="pc-productFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_product_feed_status_class() ); ?>"></span>
				<span id="pc-productFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_product_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Categories', 'pureclarity' ); ?>:</span>
				<span id="pc-categoryFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_category_feed_status_class() ); ?>"></span>
				<span id="pc-categoryFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_category_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Users', 'pureclarity' ); ?>:</span>
				<span id="pc-userFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_user_feed_status_class() ); ?>"></span>
				<span id="pc-userFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_user_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Order History', 'pureclarity' ); ?>:</span>
				<span id="pc-ordersFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_orders_feed_status_class() ); ?>"></span>
				<span id="pc-ordersFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_orders_feed_status_label() ); ?></span>
			</p>
			<div id="pc-feeds-button" class="pc-feeds-button">
				<a href="#TB_inline?&width=600&height=500&inlineId=pc-feeds-modal-popup" id="pc-feeds-popup-button" class="thickbox" title="<?php esc_attr_e( 'Run Feeds Manually', 'pureclarity' ); ?>"><?php esc_html_e( 'Run Feeds Manually', 'pureclarity' ); ?></a>
			</div>
			<?php wp_nonce_field( 'pureclarity_feed_progress', 'pureclarity-feed-progress-nonce' ); ?>
			<input id="pc-feeds-in-progress" type="hidden" value="<?php echo $this->get_are_feeds_in_progress() ? 'true' : 'false'; ?>" />
			<div id="pc-feeds-modal-popup" style="display:none;">
				<div id="pc-feeds-modal-content">
					<p class="pc-bottom-buffer"><?php esc_html_e( 'Full data feeds will be sent nightly. If you need to send a full feed sooner, please use the form below.', 'pureclarity' ); ?></p>
					<p class="pc-bottom-buffer"><?php esc_html_e( 'Please select the data you would like to send to PureClarity:', 'pureclarity' ); ?></p>
					<div class="pc-feed-field">
						<label for="pc-chkProducts"><?php esc_html_e( 'Products', 'pureclarity' ); ?></label>
						<input id="pc-chkProducts" type="checkbox" checked="checked" />
					</div>
					<div class="pc-feed-field">
						<label for="pc-chkCategories"><?php esc_html_e( 'Categories', 'pureclarity' ); ?></label>
						<input id="pc-chkCategories" type="checkbox" checked="checked" />
					</div>
					<div class="pc-feed-field">
						<label for="pc-chkUsers"><?php esc_html_e( 'Users', 'pureclarity' ); ?></label>
						<input id="pc-chkUsers" type="checkbox" checked="checked" />
					</div>
					<div class="pc-feed-field">
						<label for="pc-chkOrders"><?php esc_html_e( 'Order History', 'pureclarity' ); ?></label>
						<input id="pc-chkOrders" type="checkbox" />
					</div>
					<p><?php esc_html_e( 'Note: Order history should only need to be sent on setup as real-time orders are sent to PureClarity', 'pureclarity' ); ?></p>
					<p class="pc-topbuffer"><?php esc_html_e( 'The chosen feeds will sent to PureClarity when the scheduled task runs, it can take up to one minute to start.', 'pureclarity' ); ?></p>
					<div id="pc-feed-outputContainer">
						<div id="pc-statusMessage" style="display:none"></div>
					</div>

					<button id="pc-feed-run-button" type="button" title="<?php esc_html_e( 'Sign up', 'pureclarity' ); ?>"
							class="action-default scalable primary thickbox">
						<?php esc_html_e( 'Sign up', 'pureclarity' ); ?>
					</button>
					<?php wp_nonce_field( 'pureclarity_request_feeds', 'pureclarity-request-feeds-nonce' ); ?>
				</div>
			</div>
		</div>
	</div>
	<div id="pc-configuration" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Advanced Configuration', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'You can enable admin only mode and more in the PureClarity plugin configuration', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<a class="regular-button" href="<?php echo esc_url( admin_url( 'pureclarity-settings' ) ); ?>" title="<?php esc_html_e( 'Configure', 'pureclarity' ); ?>"><?php esc_html_e( 'Configure', 'pureclarity' ); ?></a>
			</p>
			<p><?php esc_html_e( 'There are also extra options on products and categories to allow you to prevent them being included in data feeds or shown in recommenders. Navigate to Catalogue > Products (or Categories) and edit the relevant Product/Category to see the options available', 'pureclarity' ); ?></p>
		</div>
	</div>
	<div id="pc-help" class="pc-box">
		<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Help', 'pureclarity' ); ?></h3></div>
		<div class="pc-box-content">
			<p><?php esc_html_e( 'We have comprehensive documentation available which will help with any questions you may have', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<a class="regular-button" href="https://www.pureclarity.com/docs/woocommerce/" target="_blank" title="<?php esc_html_e( 'Access Documentation', 'pureclarity' ); ?>"><?php esc_html_e( 'Access Documentation', 'pureclarity' ); ?></a>
			</p>
			<p><?php esc_html_e( 'If you have any issues please contact our Support Team at support@pureclarity.com', 'pureclarity' ); ?></p>
			<p class="pc-button">
				<a class="regular-button" href="mailto:support@pureclarity.com?subject=WooCommerce%202%20Support%20Issue&body=Plugin%20Version:%20<?php echo esc_attr( $this->get_plugin_version() ); ?>%0D%0AWooCommerce%20Version:%20<?php echo esc_attr( $this->get_woocommerce_version() ); ?>%0D%0AWordPress%20Version:%20<?php echo esc_attr( $this->get_wordpress_version() ); ?>%0D%0AStore Name: [PLEASE ENTER]%0D%0A" title="<?php esc_html_e( 'Contact support', 'pureclarity' ); ?>"><?php esc_html_e( 'Contact support', 'pureclarity' ); ?></a>
			</p>
		</div>
	</div>
</div>
