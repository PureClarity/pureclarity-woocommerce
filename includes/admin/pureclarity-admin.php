<?php
/**
 * PureClarity_Admin class
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

/**
 * Handles admin display & actions code
 */
class PureClarity_Admin {

	const ADVANCED_OPTION_GROUP_NAME = 'pureclarity_advanced';
	const ADVANCED_SECTION_ID        = 'pureclarity_section_advanced';
	const ADVANCED_MENU_SLUG         = 'pureclarity-advanced';

	const DATAFEED_MENU_SLUG = 'pureclarity-datafeed';

	const SETTINGS_OPTION_GROUP_NAME = 'pureclarity_settings';
	const SETTINGS_SECTION_ID        = 'pureclarity_section_settings';
	const SETTINGS_SLUG              = 'pureclarity-settings';

	/**
	 * PureClarity Feed class
	 *
	 * @var PureClarity_Feed $feed
	 */
	private $feed;

	/**
	 * PureClarity Plugin class
	 *
	 * @var PureClarity_Plugin $plugin
	 */
	private $plugin;

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Builds class dependencies & sets up admin actions
	 *
	 * @param PureClarity_Plugin $plugin PureClarity Plugin class.
	 */
	public function __construct( &$plugin ) {

		$this->plugin   = $plugin;
		$this->settings = $this->plugin->get_settings();
		$this->feed     = $this->plugin->get_feed();

		add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		wp_register_script(
			'pureclarity-adminjs',
			plugin_dir_url( __FILE__ ) . 'js/pc-admin.js',
			array( 'jquery' ),
			PURECLARITY_VERSION
		);
		wp_enqueue_script( 'pureclarity-adminjs' );
		add_action(
			'wp_ajax_pureclarity_run_datafeed',
			array(
				$this,
				'run_data_feed',
			)
		);
	}

	/**
	 * Runs a chosen data feed
	 *
	 * @throws RuntimeException When an error occurs.
	 */
	public function run_data_feed() {
		try {
			session_write_close();

			if ( ! isset( $_POST['page'] ) ) {
				throw new RuntimeException( 'Page has not been set.' );
			}

			$type = $_POST['type'];
			if ( ! isset( $type ) ) {
				throw new RuntimeException( 'Type has not been set.' );
			}
			$acceptable_types = array(
				'product',
				'category',
				'brand',
				'user',
				'order',
			);
			if ( ! in_array( $type, $acceptable_types ) ) {
				throw new RuntimeException( 'Unknown type.' );
			}

			if ( isset( $_POST['feedname'] ) ) {
				$this->feed->set_unique_id( $_POST['feedname'] );
			}

			$current_page      = (int) $_POST['page'];
			$total_pages_count = $this->feed->get_total_pages( $type );

			if ( 1 === $current_page && $total_pages_count > 0 ) {
				$this->feed->start_feed( $type );
			}

			if ( $current_page <= $total_pages_count ) {
				$data = $this->feed->build_items( $type, $current_page );
				$this->feed->send_data( $type, $data );
			}

			$is_finished = ( $current_page >= $total_pages_count );

			if ( $is_finished && $total_pages_count > 0 ) {
				$this->feed->end_feed( $type );
				$this->settings->set_feed_type_sent( $type );
			}

			$response = array(
				'totalPagesCount' => $total_pages_count,
				'finished'        => $is_finished,
				'feedname'        => $this->feed->get_unique_id()(),
			);

			wp_send_json( $response );

		} catch ( \Exception $exception ) {
			error_log( "PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
			wp_send_json( array( 'error' => "An error occurred generating the {$type} feed. See error logs for more information." ) );
		}
	}

	/**
	 * Adds PureClarity menus
	 */
	public function add_menus() {
		add_menu_page(
			'PureClarity',
			'PureClarity',
			'manage_options',
			self::SETTINGS_SLUG,
			array( $this, 'settings_render' ),
			'data:image/svg+xml;base64,' . $this->pureclarity_svg()
		);

		$settings = __( 'Settings', 'pureclarity' );
		add_submenu_page(
			self::SETTINGS_SLUG,
			"PureClarity: {$settings}",
			$settings,
			'manage_options',
			self::SETTINGS_SLUG,
			array( $this, 'settings_render' )
		);

		add_submenu_page(
			self::SETTINGS_SLUG,
			'PureClarity: ' . __( 'Data Feed Management', 'pureclarity' ),
			__( 'Data Feeds', 'pureclarity' ),
			'manage_options',
			self::DATAFEED_MENU_SLUG,
			array( $this, 'datafeed_render' )
		);

		add_submenu_page(
			self::SETTINGS_SLUG,
			'PureClarity: ' . __( 'Advanced Settings', 'pureclarity' ),
			__( 'Advanced', 'pureclarity' ),
			'manage_options',
			self::ADVANCED_MENU_SLUG,
			array( $this, 'advanced_render' )
		);
	}

	/**
	 * PureClarity svg string
	 */
	public function pureclarity_svg() {
		return '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';
	}

	/**
	 * Renders settings page
	 */
	public function settings_render() {
		include_once 'views/settings-page.php';
	}

	/**
	 * Renders datafeed page
	 */
	public function datafeed_render() {
		include_once 'views/datafeed-page.php';
	}

	/**
	 * Renders advanced settings page
	 */
	public function advanced_render() {
		include_once 'views/advanced-page.php';
	}

	/**
	 * Adds settings sections
	 */
	public function add_settings() {
		$this->add_general_settings();
		// for data feed settings, see pureclarity/includes/admin/views/settings-page.php.
		$this->add_advanced_settings();
	}

	/**
	 * Adss the provided fields to the settings
	 *
	 * @param array  $fields - fields to add.
	 * @param string $slug - fields slug.
	 * @param string $section_id - fields section.
	 * @param string $group_name - settings group name.
	 */
	private function add_fields( $fields, $slug, $section_id, $group_name = null ) {
		foreach ( $fields as $field ) {
			$option_name = $field[0];
			$label       = $field[1];
			$callback    = $field[2];
			$is_checkbox = $field[3];
			add_settings_field(
				$option_name,
				__( $label, 'pureclarity' ),
				array( $this, $callback ),
				$slug,
				$section_id
			);
			if ( $group_name ) {
				register_setting( $group_name, $option_name, ( $is_checkbox ? 'sanitize_checkbox' : 'sanitize_callback' ) );
			}
		}
	}

	/**
	 * Adds the general settings scetion
	 */
	private function add_general_settings() {
		add_settings_section(
			self::SETTINGS_SECTION_ID,
			__( 'General Settings', 'pureclarity' ),
			array( $this, 'print_settings_section_text' ),
			self::SETTINGS_SLUG
		);
		$this->add_fields(
			$this->get_general_fields(),
			self::SETTINGS_SLUG,
			self::SETTINGS_SECTION_ID,
			self::SETTINGS_OPTION_GROUP_NAME
		);
	}

	/**
	 * Adds the advanced settings scetion
	 */
	private function add_advanced_settings() {
		add_settings_section(
			self::ADVANCED_SECTION_ID,
			null,
			array( $this, 'print_advanced_section_text' ),
			self::ADVANCED_MENU_SLUG
		);
		$this->add_fields(
			$this->get_advanced_fields(),
			self::ADVANCED_MENU_SLUG,
			self::ADVANCED_SECTION_ID,
			self::ADVANCED_OPTION_GROUP_NAME
		);
	}

	/**
	 * Generates the accesskey setting html
	 */
	public function accesskey_callback() {

		?>

		<input type="text" name="pureclarity_accesskey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_access_key() ); ?>" />
		<p class="description" id="home-description"><?php _e( 'Enter your Access Key', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the secretkey setting html
	 */
	public function secretkey_callback() {

		?>

		<input type="text" name="pureclarity_secretkey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_secret_key() ); ?>" />
		<p class="description" id="home-description"><?php _e( 'Enter your Secret Key', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the region setting html
	 */
	public function pureclarity_region_callback() {

		$regions = $this->settings->get_regions();
		$region  = $this->settings->get_region();

		?>

		<select id="pureclarity_region" name="pureclarity_region">
			<?php foreach ( $regions as $key => $url ) : ?>
				<option value="<?php echo $key; ?>" <?php echo ( $region === $key ? "selected='selected'" : '' ); ?>><?php _e( 'Region', 'pureclarity' ); ?> <?php echo $key; ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description" id="home-description"><?php _e( 'Select the Region Id supplied with your PureClarity credentials', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the plugin mode setting html
	 */
	public function pureclarity_mode_callback() {

		$mode     = $this->settings->get_pureclarity_mode();
		$selected = "selected='selected'";

		?>

		<select id="pureclarity_mode" name="pureclarity_mode">
			<option value="on" <?php echo ( 'on' === $mode ? $selected : '' ); ?> ><?php _e( 'On', 'pureclarity' ); ?></option>
			<option value="admin" <?php echo ( 'admin' === $mode ? $selected : '' ); ?>><?php _e( 'Admin only', 'pureclarity' ); ?></option>
			<option value="off" <?php echo ( 'off' === $mode ? $selected : '' ); ?>><?php _e( 'Off', 'pureclarity' ); ?></option>
		</select>
		<p class="description" id="home-description"><?php _e( "Set PureClarity Enable Mode. When the mode is set to 'Admin only' PureClarity only shows for administrators on the front end.", 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the deltas enabled setting html
	 */
	public function enabled_deltas_callback() {

		?>

		<input type="checkbox" id="checkbox_deltas"  name="pureclarity_deltas_enabled" class="regular-text" <?php echo ( $this->settings->is_deltas_enabled_admin() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Check to activate automatic data synchronisation', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz debug enabled setting html
	 */
	public function pureclarity_bmz_debug_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_debug"  name="pureclarity_bmz_debug" class="regular-text" <?php echo ( $this->settings->is_bmz_debug_enabled() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Check to activate debugging for PureClarity BMZs. They will show even if empty.', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on homepage enabled setting html
	 */
	public function pureclarity_add_bmz_homepage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_homepage"  name="pureclarity_add_bmz_homepage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_home_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Home page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on category page enabled setting html
	 */
	public function pureclarity_add_bmz_categorypage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_categorypage"  name="pureclarity_add_bmz_categorypage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_category_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Product Listing page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on search page enabled setting html
	 */
	public function pureclarity_add_bmz_searchpage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_searchpage"  name="pureclarity_add_bmz_searchpage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_search_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Search Results page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on product page enabled setting html
	 */
	public function pureclarity_add_bmz_productpage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_productpage"  name="pureclarity_add_bmz_productpage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_product_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Product page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on basket page enabled setting html
	 */
	public function pureclarity_add_bmz_basketpage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_basketpage"  name="pureclarity_add_bmz_basketpage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_basket_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Cart page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Generates the bmz on checkout page enabled setting html
	 */
	public function pureclarity_add_bmz_checkoutpage_callback() {

		?>

		<input type="checkbox" id="checkbox_bmz_checkoutpage"  name="pureclarity_add_bmz_checkoutpage" class="regular-text" <?php echo ( $this->settings->is_bmz_on_checkout_page() ? 'checked' : '' ); ?> />
		<p class="description" id="home-description"><?php _e( 'Auto-insert BMZs on Order Confirmation page', 'pureclarity' ); ?></p>

		<?php

	}

	/**
	 * Sanitizes a checkbox value
	 *
	 * @param string $value - setting to sanitize.
	 */
	public function sanitize_checkbox( $value ) {
		return ( 'on' === $value ? 'yes' : 'no' );
	}

	/**
	 * Generates html for top of settings page
	 */
	public function print_settings_section_text() {
		echo '<p>' . __( 'To get started with PureClarity, you will need a PureClarity account and to then enter your access credentials below.', 'pureclarity' ) . '</p>';
		$url  = 'https://www.pureclarity.com/free-trial/?source=woocommerce&medium=plugin&campaign=freetrial';
		$link = sprintf(
			wp_kses(    // sanitize result.
				__( "If you don't yet have an account, you can get started for free - <a href='%s' target='_blank'>register for your free trial today</a>.", 'pureclarity' ),
				array(      // permitted html.
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			),
			esc_url( $url )
		);
		echo $link;
	}

	/**
	 * Generates html for top of advanced page
	 */
	public function print_advanced_section_text() {
		$url  = 'https://support.pureclarity.com/hc/en-us/sections/360001594074-WooCommerce';
		$link = sprintf(
			wp_kses(    // sanitize result.
				__( "Configure advanced settings for PureClarity.  For more information, please see the <a href='%s' target='_blank'>PureClarity support documentation</a>.", 'pureclarity' ),
				array(      // permitted html.
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			),
			esc_url( $url )
		);
		echo $link;
	}

	/**
	 * Generates html for dependency notices
	 */
	public function display_dependency_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
                    <p>';

			printf(
				__( 'PureClarity requires the %s extension to be installed and enabled. Please contact your hosting provider.', 'pureclarity' ),
				'"cURL" PHP'
			);

			echo '</p>
				</div>';
		}

		$whitelist_admin_pages = array(
			'toplevel_page_pureclarity-settings',
			'pureclarity_page_pureclarity-advanced',
		);
		$admin_page            = get_current_screen();

		if ( in_array( $admin_page->base, $whitelist_admin_pages, true )
			&& isset( $_GET['settings-updated'] )
			&& $_GET['settings-updated'] ) :

			?>

			<div class="notice notice-success is-dismissible"> 
				<p><strong><?php _e( 'Settings saved.', 'pureclarity' ); ?></strong></p>
			</div>

			<?php

		endif;
	}

	/**
	 * Gets fields for the general settings page
	 */
	private function get_general_fields() {
		$access_key_field        = array(
			'pureclarity_accesskey',
			'Access Key',
			'accesskey_callback',
			false, // not a checkbox.
		);
		$secret_key_field        = array(
			'pureclarity_secretkey',
			'Secret Key',
			'secretkey_callback',
			false,
		);
		$region_field            = array(
			'pureclarity_region',
			'Region',
			'pureclarity_region_callback',
			false,
		);
		$mode_select             = array(
			'pureclarity_mode',
			'Enable Mode',
			'pureclarity_mode_callback',
			false,
		);
		$deltas_enabled_checkbox = array(
			'pureclarity_deltas_enabled',
			'Enable Data Sync',
			'enabled_deltas_callback',
			true,
		);
		return array(
			$access_key_field,
			$secret_key_field,
			$region_field,
			$mode_select,
			$deltas_enabled_checkbox,
		);
	}

	/**
	 * Gets fields for the advanced settings page
	 */
	private function get_advanced_fields() {
		$bmz_debug_checkbox             = array(
			'pureclarity_bmz_debug',
			'Enable BMZ Debugging',
			'pureclarity_bmz_debug_callback',
			true, // checkbox.
		);
		$add_bmz_homepage_checkbox      = array(
			'pureclarity_add_bmz_homepage',
			'Show Home Page BMZs',
			'pureclarity_add_bmz_homepage_callback',
			true,
		);
		$add_bmz_category_page_checkbox = array(
			'pureclarity_add_bmz_categorypage',
			'Show Product Listing BMZs',
			'pureclarity_add_bmz_categorypage_callback',
			true,
		);
		$add_bmz_search_page_checkbox   = array(
			'pureclarity_add_bmz_searchpage',
			'Show Search Results BMZs',
			'pureclarity_add_bmz_searchpage_callback',
			true,
		);
		$add_bmz_product_page_checkbox  = array(
			'pureclarity_add_bmz_productpage',
			'Show Product Page BMZs',
			'pureclarity_add_bmz_productpage_callback',
			true,
		);
		$add_bmz_basket_page_checkbox   = array(
			'pureclarity_add_bmz_basketpage',
			'Show Cart Page BMZs',
			'pureclarity_add_bmz_basketpage_callback',
			true,
		);
		$add_bmz_checkout_page_checkbox = array(
			'pureclarity_add_bmz_checkoutpage',
			'Show Order Confirmation BMZs',
			'pureclarity_add_bmz_checkoutpage_callback',
			true,
		);
		return array(
			$bmz_debug_checkbox,
			$add_bmz_homepage_checkbox,
			$add_bmz_category_page_checkbox,
			$add_bmz_search_page_checkbox,
			$add_bmz_product_page_checkbox,
			$add_bmz_basket_page_checkbox,
			$add_bmz_checkout_page_checkbox,
		);
	}
}
