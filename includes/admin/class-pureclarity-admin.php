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

	const SETTINGS_SLUG  = 'pureclarity-settings';
	const DASHBOARD_SLUG = 'pureclarity-dashboard';

	/**
	 * PureClarity Feed class
	 *
	 * @var PureClarity_Feeds $feeds
	 */
	private $feeds;

	/**
	 * PureClarity Dashboard Page class
	 *
	 * @var PureClarity_Dashboard_Page $dashboard_page
	 */
	private $dashboard_page;

	/**
	 * PureClarity Settings Page class
	 *
	 * @var PureClarity_Settings_Page $settings_page
	 */
	private $settings_page;

	/**
	 * PureClarity Settings Page class
	 *
	 * @var PureClarity_Signup $signup
	 */
	private $signup;

	/**
	 * PureClarity Feedback class
	 *
	 * @var PureClarity_Feedback $feedback
	 */
	private $feedback;

	/**
	 * PureClarity Settings class
	 *
	 * @var PureClarity_Settings $settings
	 */
	private $settings;

	/**
	 * Builds class dependencies
	 *
	 * @param PureClarity_Dashboard_Page $dashboard_page - Dashboard page actions & rendering.
	 * @param PureClarity_Settings_Page  $settings_page - Settings page actions & rendering.
	 * @param PureClarity_Feeds          $feeds - Feeds actions class.
	 * @param PureClarity_Signup         $signup - Signup actions class.
	 * @param PureClarity_Feedback       $feedback - Feedback actions class.
	 * @param PureClarity_Settings       $settings - Settings class.
	 */
	public function __construct(
		$dashboard_page,
		$settings_page,
		$feeds,
		$signup,
		$feedback,
		$settings
	) {
		$this->dashboard_page = $dashboard_page;
		$this->feeds          = $feeds;
		$this->signup         = $signup;
		$this->settings_page  = $settings_page;
		$this->feedback       = $feedback;
		$this->settings       = $settings;
	}

	/**
	 * Sets up all the default admin hooks.
	 */
	public function init() {
		add_action( 'admin_notices', array( $this->dashboard_page, 'inject_before_notices' ), -9999 );
		add_action( 'admin_notices', array( $this->dashboard_page, 'inject_after_notices' ), PHP_INT_MAX );
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_init', array( $this->settings_page, 'add_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_js_and_css' ) );
		$this->add_ajax_actions();
	}

	/**
	 * Adds the PureClarity Admin JS & CSS to the page.
	 *
	 * @param string $hook - WP Hook that denotes what page is running.
	 */
	public function add_js_and_css( $hook ) {

		wp_enqueue_style(
			'pureclarity-admin-menu-styles',
			PURECLARITY_BASE_URL . 'admin/css/pc-admin-menu.css',
			array(),
			PURECLARITY_VERSION
		);

		wp_register_script(
			'pureclarity-deactivate-js',
			PURECLARITY_BASE_URL . 'admin/js/pc-deactivate.js',
			array( 'jquery' ),
			PURECLARITY_VERSION,
			true
		);

		wp_enqueue_script( 'pureclarity-deactivate-js' );

		wp_enqueue_style(
			'pureclarity-admin-deactivate-styles',
			PURECLARITY_BASE_URL . 'admin/css/pc-deactivate.css',
			array(),
			PURECLARITY_VERSION
		);

		if ( strpos( $hook, 'pureclarity' ) !== false ) {

			wp_enqueue_style(
				'pureclarity-font',
				'https://fonts.googleapis.com/css?family=Lato:200,300,400,500,600,700,900',
				array(),
				PURECLARITY_VERSION
			);

			$state = $this->dashboard_page->get_state_name();

			if ( PureClarity_Dashboard_Page::STATE_CONFIGURED !== $state ) {
				wp_enqueue_style(
					'pureclarity-admin-slick',
					PURECLARITY_BASE_URL . 'admin/css/slick.css',
					array(),
					PURECLARITY_VERSION
				);

				wp_enqueue_style(
					'pureclarity-admin-slick-theme',
					PURECLARITY_BASE_URL . 'admin/css/slick-theme.css',
					array(),
					PURECLARITY_VERSION
				);

				wp_register_script(
					'pureclarity-signup-js',
					PURECLARITY_BASE_URL . 'admin/js/pc-signup.js',
					array( 'jquery' ),
					PURECLARITY_VERSION,
					true
				);

				wp_register_script(
					'pureclarity-slick',
					PURECLARITY_BASE_URL . 'admin/js/slick.min.js',
					array( 'jquery' ),
					PURECLARITY_VERSION,
					true
				);

				wp_enqueue_script( 'pureclarity-signup-js' );
				wp_enqueue_script( 'pureclarity-slick' );
			} else {
				wp_register_script(
					'pureclarity-admin-js',
					PURECLARITY_BASE_URL . 'admin/js/pc-admin.js',
					array( 'jquery' ),
					PURECLARITY_VERSION,
					true
				);
				wp_enqueue_script( 'pureclarity-admin-js' );
			}

			wp_enqueue_style(
				'pureclarity-admin-styles',
				PURECLARITY_BASE_URL . 'admin/css/pc-admin.css',
				array(),
				PURECLARITY_VERSION
			);
			add_thickbox();
		}
	}

	/**
	 * Adds the various admin ajax actions used by the plugin
	 */
	public function add_ajax_actions() {

		$state = $this->dashboard_page->get_state_name();

		if ( PureClarity_Dashboard_Page::STATE_CONFIGURED === $state ) {
			add_action(
				'wp_ajax_pureclarity_request_feeds',
				array(
					$this->feeds,
					'request_feeds_action',
				)
			);

			add_action(
				'wp_ajax_pureclarity_feed_progress',
				array(
					$this->feeds,
					'feed_progress_action',
				)
			);

			add_action(
				'wp_ajax_pureclarity_switch_mode',
				array(
					$this,
					'switch_mode_action',
				)
			);

			add_action(
				'wp_ajax_pureclarity_complete_next_step',
				array(
					$this,
					'complete_next_step_action',
				)
			);
		} else {
			add_action(
				'wp_ajax_pureclarity_signup_submit',
				array(
					$this->signup,
					'submit_signup_action',
				)
			);

			add_action(
				'wp_ajax_pureclarity_signup_progress',
				array(
					$this->signup,
					'signup_progress_action',
				)
			);

			add_action(
				'wp_ajax_pureclarity_link_account',
				array(
					$this->signup,
					'link_account_action',
				)
			);
		}

		add_action(
			'wp_ajax_pureclarity_deactivate_feedback',
			array(
				$this->feedback,
				'feedback_action',
			)
		);
	}


	/**
	 * Switches mode
	 */
	public function switch_mode_action() {

		check_admin_referer( 'pureclarity_switch_mode', 'security' );

		$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';

		if ( 'live' === $mode ) {
			update_option( 'pureclarity_mode', 'on' );
		} elseif ( 'admin' === $mode ) {
			update_option( 'pureclarity_mode', 'admin' );
		} elseif ( 'disabled' === $mode ) {
			update_option( 'pureclarity_mode', 'off' );
		}
	}

	/**
	 * Completes a next step
	 */
	public function complete_next_step_action() {

		check_admin_referer( 'pureclarity_complete_next_step', 'security' );

		$action_id = isset( $_POST['action_id'] ) ? sanitize_text_field( wp_unslash( $_POST['action_id'] ) ) : '';

		if ( $action_id ) {
			try {
				$feedback = new \PureClarity\Api\NextSteps\Complete(
					$this->settings->get_access_key(),
					$this->settings->get_secret_key(),
					(int) $this->settings->get_region()
				);

				$feedback->request();
			} catch ( \Exception $e ) {
				error_log( 'PureClarity error: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Adds PureClarity menus
	 */
	public function add_menus() {

		$dashboard = __( 'Dashboard', 'pureclarity' );
		add_menu_page(
			"PureClarity: {$dashboard}",
			'PureClarity',
			'manage_options',
			self::DASHBOARD_SLUG,
			array( $this->dashboard_page, 'dashboard_render' ),
			'data:image/png;base64,' . $this->pureclarity_svg()
		);

		add_submenu_page(
			self::DASHBOARD_SLUG,
			"PureClarity: {$dashboard}",
			$dashboard,
			'manage_options',
			self::DASHBOARD_SLUG,
			array( $this->dashboard_page, 'dashboard_render' )
		);

		$settings = __( 'Settings', 'pureclarity' );
		add_submenu_page(
			self::DASHBOARD_SLUG,
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
	 * Generates html for notices
	 */
	public function display_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
                    <p>';

			echo esc_html_e( 'PureClarity requires the "cURL" PHP extension to be installed and enabled. Please contact your hosting provider.', 'pureclarity' );

			echo '</p>
				</div>';
		}

		if ( PureClarity_Dashboard_Page::STATE_CONFIGURED !== $this->dashboard_page->get_state_name() ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php _e( 'Thanks for installing PureClarity for WooCommerce.', 'pureclarity' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pureclarity-dashboard' ) ); ?>" title="<?php esc_html_e( 'Click here to begin the setup process', 'pureclarity' ); ?>"><?php esc_html_e( 'Click here to begin the setup process', 'pureclarity' ); ?></a>
				</p>
			</div>
			<?php
		}
	}
}
