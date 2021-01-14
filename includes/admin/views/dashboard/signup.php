<?php
/**
 * Signup content HTML
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/** Dashboard class containing functions used by this view. @var PureClarity_Dashboard_Page $this */

?>

<div id="pc-signup-col2">
	<div id="pc-waiting" <?php if ( self::STATE_WAITING !== $this->get_state_name() ) : ?>
		style="display:none"
	<?php endif; ?>>
		<div id="pc-sign-up-waiting" class="pc-box">
			<div class="pc-box-title-bar"><h3><?php esc_html_e( 'Setting up your PureClarity account', 'pureclarity' ); ?></h3></div>
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
	</div>
<?php if ( self::STATE_WAITING !== $this->get_state_name() ) : ?>
	<div id="pc-sign-up" class="pc-box pc-signup-boxes">
		<div class="pc-box-title-bar">
			<h3><?php esc_html_e( 'Sign up for a 30-day free trial', 'pureclarity' ); ?></h3>
		</div>
		<div class="pc-box-content">
			<p class="sign-up-subheading"><strong><?php esc_html_e( 'Try for Free today:', 'pureclarity' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( 'Get up and running in minutes', 'pureclarity' ); ?></li>
				<li><?php esc_html_e( 'No credit card or contract required', 'pureclarity' ); ?></li>
				<li><?php esc_html_e( 'Access to all PureClarity features', 'pureclarity' ); ?></li>
				<li><?php esc_html_e( 'Full support from our dedicated team', 'pureclarity' ); ?></li>
			</ul>
			<p class="sign-up-instruction">
				<?php esc_html_e( 'Fill out the form below to get started. We\'ll use these details to create your PureClarity account.', 'pureclarity' ); ?>
				<a id="pc-link-account-button" href="#TB_inline?&width=500&height=450&inlineId=pc-link-account-form-holder" class="thickbox" ><?php esc_html_e( 'Already have an account?', 'pureclarity' ); ?></a>
			</p>
				<div id="pc-sign-up-form-content">
					<form id="pc-sign-up-form" method="post" action="">
						<?php wp_nonce_field( 'pureclarity_signup_submit' ); ?>
						<input type="hidden" name="action" value="pureclarity_signup_submit" />
						<div class="left">
							<h3><?php esc_html_e( 'About you', 'pureclarity' ); ?></h3>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-firstname">
									<?php esc_html_e( 'First Name', 'pureclarity' ); ?>
								</label>
								<input type="text" name="first_name" id="pc-sign-up-firstname"/>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-lastname">
									<?php esc_html_e( 'Last Name', 'pureclarity' ); ?>
								</label>
								<input type="text" name="last_name" id="pc-sign-up-lastname"/>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-email"><?php esc_html_e( 'Email', 'pureclarity' ); ?></label>
								<input type="text" name="email" id="pc-sign-up-email" />
							</p>
							<p class="pc-field">
								<label class="label-nr" for="pc-sign-up-phone"><?php esc_html_e( 'Phone Number', 'pureclarity' ); ?></label>
								<input type="text" name="phone" id="pc-sign-up-phone" />
								<span class="pc-tooltip" id="pc-sign-up-phone-tooltip">?
									<span class="pc-tooltip-text" id="pc-sign-up-phone-tooltip">
									<?php esc_html_e( 'If you provide a phone number we will use it to contact you during your free trial and for support. It will not be used for sales calls.', 'pureclarity' ); ?>
									</span>
								</span>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-company"><?php esc_html_e( 'Company', 'pureclarity' ); ?></label>
								<input type="text" name="company" id="pc-sign-up-company" />
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-password"><?php esc_html_e( 'Password', 'pureclarity' ); ?></label>
								<input type="password" name="password" id="pc-sign-up-password" />
								<span class="pc-error-message" id="pc-sign-up-password-error"><?php esc_html_e( 'Password not strong enough, must contain 1 lowercase letter, 1 uppercase letter, 1 number and be 8 characters or longer', 'pureclarity' ); ?></span>
							</p>
						</div>
						<div class="right">
							<h3><?php esc_html_e( 'About your site', 'pureclarity' ); ?></h3>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-store-name">
									<?php esc_html_e( 'Store Name', 'pureclarity' ); ?>
								</label>
								<input type="text" name="store_name" id="pc-sign-up-store-name" value="<?php echo esc_attr( $this->get_store_name() ); ?>" />
								<span class="pc-error-message" id="pc-sign-up-store-name-error"><?php esc_html_e( 'Please enter a valid store name', 'pureclarity' ); ?></span>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-store-url"><?php esc_html_e( 'URL', 'pureclarity' ); ?></label>
								<input type="text" name="url" id="pc-sign-up-store-url" value="<?php echo esc_attr( $this->get_store_url() ); ?>"/>
								<span class="pc-error-message" id="pc-sign-up-store-url-error"><?php esc_html_e( 'Please enter a valid URL', 'pureclarity' ); ?></span>
							</p>
							<p class="details">
								<span class="label"><?php esc_html_e( 'Currency', 'pureclarity' ); ?></span>
								<span class="value" id="pc-sign-up-store-currency">
							<?php echo esc_html( $this->get_store_currency() ); ?>
						</span>
								<input type="hidden" name="currency" id="pc-sign-up-currency" value="<?php echo esc_attr( $this->get_store_currency() ); ?>"/>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-timezone"><?php esc_html_e( 'Timezone', 'pureclarity' ); ?></label>
								<select name="timezone" id="pc-sign-up-timezone">
									<?php foreach ( $this->get_pureclarity_timezones() as $timezone ) : ?>
										<option value="<?php echo esc_attr( $timezone['value'] ); ?>"
											<?php if ( 'Europe/London' === $timezone['value'] ) : ?>
												selected="selected"
											<?php endif; ?>>
											<?php echo esc_html( $timezone['label'] ); ?>
										</option>
									<?php endforeach ?>
								</select>
								<span class="pc-error-message" id="pc-sign-up-timezone-error"><?php esc_html_e( 'Please choose a timezone', 'pureclarity' ); ?></span>
							</p>
							<p class="pc-field">
								<label class="label" for="pc-sign-up-region"><?php esc_html_e( 'Region' ); ?></label>
								<select name="region" id="pc-sign-up-region">
									<?php foreach ( $this->get_pureclarity_regions() as $region ) : ?>
										<option value="<?php echo esc_attr( $region['value'] ); ?>">
											<?php echo esc_html( $region['label'] ); ?>
										</option>
									<?php endforeach ?>
								</select>
								<span class="pc-error-message" id="pc-sign-up-region-error"><?php esc_html_e( 'Please choose a Region', 'pureclarity' ); ?></span>
							</p>
						</div>
						<div id="pc-sign-up-response-holder">&nbsp;</div>
						<div class="pc-sign-up-submit-button">
							<button id="pc-sign-up-submit-button" type="button" title="<?php esc_html_e( 'Sign up', 'pureclarity' ); ?>"
									class="action-default scalable primary thickbox">
								<?php esc_html_e( 'Sign up', 'pureclarity' ); ?>
							</button>
						</div>
					</form>
			</div>
		</div>
	</div>
</div>
<div id="pc-link-account-form-holder">
	<div id="pc-link-account-form-header">
		<h2><?php esc_html_e( 'Account Setup', 'pureclarity' ); ?></h2>
	</div>
	<div id="pc-link-account-form-content">
		<form id="pc-link-account-form" method="post" action="">
			<?php wp_nonce_field( 'pureclarity_link_account' ); ?>
			<input type="hidden" name="action" value="pureclarity_link_account" />
			<p><?php esc_html_e( 'Enter your account details to get started', 'pureclarity' ); ?></p>
			<div id="pc-account-details">
				<p class="pc-field">
					<label for="pc-details-access-key"><?php esc_html_e( 'Access Key', 'pureclarity' ); ?></label>
					<input type="text" name="access_key" id="pc-details-access-key"/>
				</p>
				<p class="pc-field">
					<label for="pc-details-secret-key"><?php esc_html_e( 'Secret Key', 'pureclarity' ); ?></label>
					<input type="text" name="secret_key" id="pc-details-secret-key"/>
				</p>
				<p class="pc-field">
					<label for="pc-details-region"><?php esc_html_e( 'Region', 'pureclarity' ); ?></label>
					<select name="region" id="pc-details-region">
						<?php foreach ( $this->get_pureclarity_regions() as $region ) : ?>
							<option value="<?php echo esc_attr( $region['value'] ); ?>">
								<?php echo esc_html( $region['label'] ); ?>
							</option>
						<?php endforeach ?>
					</select>
				</p>
			</div>
			<div id="pc-link-account-response-holder">&nbsp;</div>
			<div class="pc-link-account-submit-button">
				<button id="pc-link-account-submit-button" type="button" title="<?php esc_html_e( 'Sign up', 'pureclarity' ); ?>"
						class="thickbox">
					<?php esc_html_e( 'Link account', 'pureclarity' ); ?>
				</button>
			</div>
		</form>
	</div>
	<?php
endif;
?>
</div>
<div id="pc-signup-col1">
	<div id="pc-marketing-info" class="pc-box pc-signup-boxes">
		<div class="pc-box-content">
			<p><?php esc_html_e( 'With our leading eCommerce personalisation platform, you can enjoy:' ); ?></p>
			<div class="pc_stat_donuts">
				<div class="pc_stat_donut">
					<svg class="pc_donut" width="120" height="120" xmlns="http://www.w3.org/2000/svg">
						<g>
							<title>26.6% Increase in revenue</title>
							<circle class="pc_donut_base" r="42" cy="60" cx="60" stroke-width="25" stroke="#e9f1f4" fill="transparent" stroke-dasharray="0" />
							<circle class="pc_donut_animation" r="42" cy="60" cx="60" stroke-width="25" stroke="#c4d95a" fill="transparent" stroke-dasharray="70.195,263.89" />
							<text class="pc_donut_text" x="60" y="-60" alignment-baseline="central" text-anchor="middle" font-size="19" font-weight="bold">26.6%</text>
						</g>
					</svg>
					<span><?php esc_html_e( 'Increase in', 'pureclarity' ); ?><br /><?php esc_html_e( 'revenue', 'pureclarity' ); ?></span>
				</div>
				<div class="pc_stat_donut">
					<svg class="pc_donut" width="120" height="120" xmlns="http://www.w3.org/2000/svg">
						<g>
							<title>71.5% Increase in conversion</title>
							<circle class="pc_donut_base" r="42" cy="60" cx="60" stroke-width="25" stroke="#e9f1f4" fill="transparent" stroke-dasharray="0" />
							<circle class="pc_donut_animation" r="42" cy="60" cx="60" stroke-width="25" stroke="#68b8db" fill="transparent" stroke-dasharray="188.68,263.89" />
							<text class="pc_donut_text" x="60" y="-60" alignment-baseline="central" text-anchor="middle" font-size="19" font-weight="bold">71.5%</text>
						</g>
					</svg>
					<span><?php esc_html_e( 'Increase in', 'pureclarity' ); ?><br /><?php esc_html_e( 'conversion', 'pureclarity' ); ?></span>
				</div>
				<div class="pc_stat_donut">
					<svg class="pc_donut" width="120" height="120" xmlns="http://www.w3.org/2000/svg">
						<g>
							<title>15.6% Increase in average order value</title>
							<circle class="pc_donut_base" r="42" cy="60" cx="60" stroke-width="25" stroke="#e9f1f4" fill="transparent" stroke-dasharray="0" />
							<circle class="pc_donut_animation" r="42" cy="60" cx="60" stroke-width="25" stroke="#e3d246" fill="transparent" stroke-dasharray="41.167,263.89" />
							<text class="pc_donut_text" x="60" y="-60" alignment-baseline="central" text-anchor="middle" font-size="19" font-weight="bold">15.6%</text>
						</g>
					</svg>
					<span><?php esc_html_e( 'Increase in', 'pureclarity' ); ?><br /><?php esc_html_e( 'average order value', 'pureclarity' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div id="pc-features" class="pc-box" style="overflow: hidden">
		<div class="pc-box-content">
			<p><?php esc_html_e( 'You\'ll also get access to these fantastic features:', 'pureclarity' ); ?></p>
			<div id="pc-features-list">
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-personalised-content.jpg' ); ?>" alt="<?php esc_attr_e( 'Personalised content', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Personalised content', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Display personalised banners, images & text to engage with visitors, turn them into buyers and incentivise returning customers.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-intelligent-recs.jpg' ); ?>" alt="<?php esc_attr_e( 'Intelligent recommendations', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Intelligent recommendations', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Use powerful personalised recommendations to upsell and cross-sell products at the right time.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-search.jpg' ); ?>" alt="<?php esc_attr_e( 'Personalisation within search', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Personalisation within search', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Help customers find what they’re looking for by displaying relevant products as they search.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-email.jpg' ); ?>" alt="<?php esc_attr_e( 'Personalisation within email', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Personalisation within email', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Use personalised email campaigns to entice customers back based on products they’ve viewed and bought in the past.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-chat.jpg' ); ?>" alt="<?php esc_attr_e( 'Live chat', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Live chat', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Use a combination of real-time information about the customers current behaviours so you can help them find exactly what they want.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-popups.jpg' ); ?>" alt="<?php esc_attr_e( 'Personalised pop-ups', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Personalised pop-ups', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Keep your customers informed of your latest products, news and offers with personalised email capture pop-ups.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-segmentation.jpg' ); ?>" alt="<?php esc_attr_e( 'Audience segmentation', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Audience segmentation', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Use pre-built and custom made conditions to target visitors using powerful, goal orientated marketing campaigns.', 'pureclarity' ); ?></p>
				</div>
				<div class="pc-feature">
					<img src="<?php echo esc_attr( PURECLARITY_BASE_URL . '/admin/images/features-analytics.jpg' ); ?>" alt="<?php esc_attr_e( 'Insights & analytics', 'pureclarity' ); ?>"/>
					<h3><?php esc_html_e( 'Insights & analytics', 'pureclarity' ); ?></h3>
					<p><?php esc_html_e( 'Gain deeper insights into visitor behaviour and learn how to maximise your web stores potential in order to increase revenue.', 'pureclarity' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
