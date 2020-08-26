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

	const SETTINGS_SLUG              = 'pureclarity-settings';

	/**
	 * PureClarity Settings Page class
	 *
	 * @var PureClarity_Settings_Page $settings_page
	 */
	private $settings_page;

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
		$this->settings_page  = new PureClarity_Settings_Page( $plugin->get_settings() );

		add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_init', array( $this->settings_page, 'add_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_js_and_css' ) );
	}

	/**
	 * Adds the PureClarity Admin JS & CSS to the page.
	 *
	 * @param string $hook - WP Hook that denotes what page is running.
	 */
	public function add_js_and_css( $hook ) {

		if ( strpos( $hook, 'pureclarity' ) !== false ) {

			wp_enqueue_style(
				'pureclarity-admin-styles',
				PURECLARITY_BASE_URL . 'admin/css/pc-admin.css',
				array(),
				PURECLARITY_VERSION
			);

			wp_register_script(
				'pureclarity-adminjs',
				PURECLARITY_BASE_URL . 'admin/js/pc-admin.js',
				array( 'jquery' ),
				PURECLARITY_VERSION,
				true
			);

			wp_enqueue_script( 'pureclarity-adminjs' );
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
			array( $this->settings_page, 'settings_render' )
		);
	}

	/**
	 * PureClarity svg string
	 */
	public function pureclarity_svg() {
		return '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';
	}

	/**
	 * Generates html for dependency notices
	 */
	public function display_dependency_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
                    <p>';

			echo esc_html_e( 'PureClarity requires the "cURL" PHP extension to be installed and enabled. Please contact your hosting provider.', 'pureclarity' );

			echo '</p>
				</div>';
		}

		$whitelist_admin_pages = array(
			'toplevel_page_pureclarity-settings',
			'pureclarity_page_pureclarity-advanced',
		);
		$admin_page            = get_current_screen();

		$updated = isset( $_GET['settings-updated'] ) ? sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) : false;

		if ( in_array( $admin_page->base, $whitelist_admin_pages, true ) && $updated ) :

			?>

			<div class="notice notice-success is-dismissible"> 
				<p><strong><?php esc_html_e( 'Settings saved.', 'pureclarity' ); ?></strong></p>
			</div>

			<?php

		endif;
	}
}
