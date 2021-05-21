<?php
/**
 * PureClarity_Settings_Page class
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Handles settings page display
 */
class PureClarity_Settings_Page {

	const SETTINGS_OPTION_GROUP_NAME = 'pureclarity_settings';
	const SETTINGS_SECTION_ID        = 'pureclarity_section_settings';
	const SETTINGS_SLUG              = 'pureclarity-settings';

	/**
	 * PureClarity Settings class.
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Builds class dependencies & sets up admin actions
	 *
	 * @param PureClarity_Settings $settings PureClarity Settings class.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Renders settings page
	 */
	public function settings_render() {
		include_once 'views/header.php';
		include_once 'views/settings-page.php';
	}

	/**
	 * Adds settings sections
	 */
	public function add_settings() {
		add_settings_section(
			self::SETTINGS_SECTION_ID,
			__( 'Settings', 'pureclarity' ),
			array( $this, 'print_settings_section_text' ),
			self::SETTINGS_SLUG
		);
		$this->add_fields();
	}

	/**
	 * Adss the provided fields to the settings
	 */
	private function add_fields() {

		foreach ( $this->get_general_fields() as $field ) {
			add_settings_field(
				$field['name'],
				$field['label'],
				array( $this, 'display_field' ),
				self::SETTINGS_SLUG,
				self::SETTINGS_SECTION_ID,
				$field
			);
			register_setting( self::SETTINGS_OPTION_GROUP_NAME, $field['name'], ( 'checkbox' === $field['type'] ? 'sanitize_checkbox' : 'sanitize_callback' ) );
		}
	}

	/**
	 * Gets fields for the general settings page
	 */
	private function get_general_fields() {

		$env_subheading = array(
			'name'  => 'pureclarity_env_subheading',
			'label' => 'Environment',
			'type'  => 'heading',
		);

		$access_key_field = array(
			'name'        => 'pureclarity_accesskey',
			'label_for'   => 'pureclarity_accesskey',
			'label'       => 'Access Key',
			'type'        => 'text',
			'description' => 'Enter your Access Key',
			'value_cb'    => 'get_access_key',
		);

		$secret_key_field = array(
			'name'        => 'pureclarity_secretkey',
			'label_for'   => 'pureclarity_secretkey',
			'label'       => 'Secret Key',
			'type'        => 'text',
			'description' => 'Enter your Secret Key',
			'value_cb'    => 'get_secret_key',
		);

		$region_field = array(
			'name'        => 'pureclarity_region',
			'label_for'   => 'pureclarity_region',
			'label'       => 'Region',
			'type'        => 'select',
			'description' => 'Select the region for your PureClarity enviroment',
			'value_cb'    => 'get_region',
			'options'     => $this->settings->get_display_regions(),
		);

		$mode_select = array(
			'name'        => 'pureclarity_mode',
			'label_for'   => 'pureclarity_mode',
			'label'       => 'Display Mode',
			'type'        => 'select',
			'description' => 'Set PureClarity Display Mode. When the mode is set to "Admin only" PureClarity only shows for administrators on the front end.',
			'value_cb'    => 'get_pureclarity_mode',
			'options'     => array(
				'on'    => 'On',
				'admin' => 'Admin only',
				'off'   => 'Off',
			),
		);

		$feeds_subheading = array(
			'name'  => 'pureclarity_feeds_subheading',
			'label' => 'Deltas & Feeds',
			'type'  => 'heading',
		);

		$deltas_enabled_checkbox = array(
			'name'        => 'pureclarity_deltas_enabled',
			'label_for'   => 'pureclarity_deltas_enabled',
			'label'       => 'Enable Deltas',
			'type'        => 'checkbox',
			'description' => 'Check to activate automatic data synchronisation',
			'value_cb'    => 'is_deltas_enabled_admin',
		);

		$nightly_feeds_enabled_checkbox = array(
			'name'        => 'pureclarity_nightly_feed_enabled',
			'label_for'   => 'pureclarity_nightly_feed_enabled',
			'label'       => 'Enable Nightly Feeds',
			'type'        => 'checkbox',
			'description' => 'Check to activate automatic nightly feeds, sent at 3am',
			'value_cb'    => 'is_nightly_feed_enabled',
		);

		$exclude_oos_enabled_checkbox = array(
			'name'        => 'pureclarity_product_feed_exclude_oos',
			'label_for'   => 'pureclarity_product_feed_exclude_oos',
			'label'       => 'Exclude Out Of Stock Products From Recommenders',
			'type'        => 'checkbox',
			'description' => 'Sets the "Exclude from recommenders" flag to true for out of stock products in feeds / deltas',
			'value_cb'    => 'is_product_feed_exclude_oos_enabled',
		);

		$feeds_logging_enabled_checkbox = array(
			'name'        => 'pureclarity_feed_debug_logging',
			'label_for'   => 'pureclarity_feed_debug_logging',
			'label'       => 'Enable Debug Logging',
			'type'        => 'checkbox',
			'description' => 'Check to enable extra logging for feeds & deltas, to show what items are being processed / skipped. Please only enable when troubleshooting feeds & deltas, as it can produce large log files.',
			'value_cb'    => 'is_feed_logging_enabled',
		);

		$zones_subheading = array(
			'name'  => 'pureclarity_zones_subheading',
			'label' => 'Zones',
			'type'  => 'heading',
		);

		$bmz_debug_checkbox = array(
			'name'        => 'pureclarity_bmz_debug',
			'label_for'   => 'pureclarity_bmz_debug',
			'label'       => 'Enable Zone Debugging',
			'type'        => 'checkbox',
			'description' => 'Check to activate debugging for PureClarity Zones. They will show even if empty.',
			'value_cb'    => 'is_bmz_debug_enabled',
		);

		$add_bmz_homepage_checkbox = array(
			'name'        => 'pureclarity_add_bmz_homepage',
			'label_for'   => 'pureclarity_add_bmz_homepage',
			'label'       => 'Show Home Page Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Home page',
			'value_cb'    => 'is_bmz_on_home_page',
		);

		$add_bmz_category_page_checkbox = array(
			'name'        => 'pureclarity_add_bmz_categorypage',
			'label_for'   => 'pureclarity_add_bmz_categorypage',
			'label'       => 'Show Product Listing Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Product Listing page',
			'value_cb'    => 'is_bmz_on_category_page',
		);

		$add_bmz_search_page_checkbox = array(
			'name'        => 'pureclarity_add_bmz_searchpage',
			'label_for'   => 'pureclarity_add_bmz_searchpage',
			'label'       => 'Show Search Results Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Search Results page',
			'value_cb'    => 'is_bmz_on_search_page',
		);

		$add_bmz_product_page_checkbox = array(
			'name'        => 'pureclarity_add_bmz_productpage',
			'label_for'   => 'pureclarity_add_bmz_productpage',
			'label'       => 'Show Product Page Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Product page',
			'value_cb'    => 'is_bmz_on_product_page',
		);

		$add_bmz_basket_page_checkbox = array(
			'name'        => 'pureclarity_add_bmz_basketpage',
			'label_for'   => 'pureclarity_add_bmz_basketpage',
			'label'       => 'Show Cart Page Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Cart page',
			'value_cb'    => 'is_bmz_on_basket_page',
		);

		$add_bmz_checkout_page_checkbox = array(
			'name'        => 'pureclarity_add_bmz_checkoutpage',
			'label_for'   => 'pureclarity_add_bmz_checkoutpage',
			'label'       => 'Show Order Confirmation Zones',
			'type'        => 'checkbox',
			'description' => 'Auto-insert Zones on Order Confirmation page',
			'value_cb'    => 'is_bmz_on_checkout_page',
		);

		return array(
			$env_subheading,
			$access_key_field,
			$secret_key_field,
			$region_field,
			$mode_select,
			$feeds_subheading,
			$deltas_enabled_checkbox,
			$nightly_feeds_enabled_checkbox,
			$exclude_oos_enabled_checkbox,
			$feeds_logging_enabled_checkbox,
			$zones_subheading,
			$bmz_debug_checkbox,
			$add_bmz_homepage_checkbox,
			$add_bmz_category_page_checkbox,
			$add_bmz_search_page_checkbox,
			$add_bmz_product_page_checkbox,
			$add_bmz_basket_page_checkbox,
			$add_bmz_checkout_page_checkbox,
		);
	}

	/**
	 * Generates field html for the different setting types.
	 *
	 * @param mixed[] $args - arguments to help determine which type of field to display.
	 */
	public function display_field( $args ) {

		if ( 'heading' !== $args['type'] ) {

			$value = call_user_func( array( $this->settings, $args['value_cb'] ) );

			switch ( $args['type'] ) {
				case 'text':
					?>
					<input type="text"
							name="<?php echo esc_attr( $args['name'] ); ?>"
							id="<?php echo esc_attr( $args['name'] ); ?>"
							class="regular-text"
							value="<?php echo esc_attr( $value ); ?>" />
					<?php
					break;
				case 'checkbox':
					?>
					<input type="checkbox"
							name="<?php echo esc_attr( $args['name'] ); ?>"
							id="<?php echo esc_attr( $args['name'] ); ?>"
							class="regular-text" <?php echo ( $value ? 'checked' : '' ); ?> />
					<?php
					break;
				case 'select':
					?>
					<select id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>">
						<?php foreach ( $args['options'] as $key => $label ) : ?>
							<option value="<?php echo esc_html( $key ); ?>" <?php echo ( $value === (string) $key ) ? "selected='selected'" : ''; ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
					break;
				default:
					break;
			}

			if ( isset( $args['description'] ) ) {
				?>
				<p class="description" id="home-description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php
			}
		}
	}

	/**
	 * Generates html for top of settings page
	 */
	public function print_settings_section_text() {
		$url = 'https://www.pureclarity.com/docs/woocommerce/';
		echo sprintf(
			wp_kses( // sanitize result.
				/* translators: %s is replaced with the url */
				__( "Configure settings for PureClarity. For more information, please see the <a href='%s' target='_blank'>PureClarity support documentation</a>.", 'pureclarity' ),
				array(      // permitted html.
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			),
			esc_url( $url )
		);
	}

	/**
	 * Returns the current plugin version
	 *
	 * @return string
	 */
	public function get_plugin_version() {
		return PURECLARITY_VERSION;
	}

	/**
	 * Returns the current Woocommerce version
	 *
	 * @return string
	 */
	public function get_woocommerce_version() {
		$version = 'N/A';
		global $woocommerce;
		if ( $woocommerce && $woocommerce->version ) {
			$version = $woocommerce->version;
		}
		return $version;
	}

	/**
	 * Returns the current WordPress version
	 *
	 * @return string
	 */
	public function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}
}
