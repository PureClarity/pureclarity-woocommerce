<?php
/**
 * Feeds box & run request HTML.
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */
?>
	<div id="pc-step2" class="pureclarity-info-box pc-col-box">
		<div class="pureclarity-info-title">
			<h2><?php esc_html_e( 'Feeds', 'pureclarity' ); ?></h2>
		</div>
		<div class="pureclarity-info-content">
			<p><?php esc_html_e( 'Full data feeds are sent nightly to PureClarity to ensure data is up to date in our system, below is the status of each of the data feed types:', 'pureclarity' ); ?></p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Products', 'pureclarity' ); ?>:</span>
				<span id="pc-productFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_product_feed_status_class() ); ?>" title="<?php echo esc_html( $this->get_product_feed_status_label() ); ?>"></span>
				<span id="pc-productFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_product_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Categories', 'pureclarity' ); ?>:</span>
				<span id="pc-categoryFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_category_feed_status_class() ); ?>" title="<?php echo esc_html( $this->get_category_feed_status_label() ); ?>"></span>
				<span id="pc-categoryFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_category_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Users', 'pureclarity' ); ?>:</span>
				<span id="pc-userFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_user_feed_status_class() ); ?>" title="<?php echo esc_html( $this->get_user_feed_status_label() ); ?>"></span>
				<span id="pc-userFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_user_feed_status_label() ); ?></span>
			</p>
			<p class="pc-feed">
				<span class="pc-feedName"><?php esc_html_e( 'Order History', 'pureclarity' ); ?>:</span>
				<span id="pc-ordersFeedStatusClass" class="pc-feed-status-icon <?php echo esc_attr( $this->get_orders_feed_status_class() ); ?>" title="<?php echo esc_html( $this->get_orders_feed_status_label() ); ?>"></span>
				<span id="pc-ordersFeedStatusLabel" class="pc-feedStatus"><?php echo esc_html( $this->get_orders_feed_status_label() ); ?></span>
			</p>
			<div id="pc-feeds-button">
				<a href="#TB_inline?&width=600&height=500&inlineId=pc-feeds-modal-popup" id="pc-feeds-popup-button" class="pc-button thickbox" title="<?php esc_attr_e( 'Run Feeds Manually', 'pureclarity' ); ?>"><?php esc_html_e( 'Run feeds manually', 'pureclarity' ); ?></a>
			</div>
			<?php wp_nonce_field( 'pureclarity_feed_progress', 'pureclarity-feed-progress-nonce' ); ?>
			<input id="pc-feeds-in-progress" type="hidden" value="<?php echo $this->get_are_feeds_in_progress() ? 'true' : 'false'; ?>" />
			<input id="pc-feeds-label-base" type="hidden" value="<?php esc_attr_e( 'Waiting for feed run to start', 'pureclarity' ); ?>" />
			<input id="pc-feeds-button-not-enabled" type="hidden" value="<?php esc_attr_e( 'PureClarity not enabled', 'pureclarity' ); ?>" />
			<input id="pc-feeds-button-manually" type="hidden" value="<?php esc_attr_e( 'Run feeds manually', 'pureclarity' ); ?>" />
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
					<button id="pc-feed-run-button" type="button" title="<?php esc_html_e( 'Send feeds now', 'pureclarity' ); ?>"
							class="thickbox pc-button">
						<?php esc_html_e( 'Send feeds now', 'pureclarity' ); ?>
					</button>
					<?php wp_nonce_field( 'pureclarity_request_feeds', 'pureclarity-request-feeds-nonce' ); ?>
				</div>
			</div>
			<div class="pc-clearfix"></div>
		</div>
		<div class="pc-clearfix"></div>
	</div>
