<?php

class PureClarity_Admin
{
    private $settings_slug = 'pureclarity-settings';
    private $settings_section = 'pureclarity_section_settings';
    private $datafeed_slug = 'pureclarity-datafeed';
    private $datafeed_section = 'pureclarity_section_datafeed';
    private $settings_option_group = 'pureclarity_settings';
    private $datafeed_option_group = 'pureclarity_datafeed';
    private $plugin;
    private $settings;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        $this->settings = $this->plugin->get_settings();
        add_action( 'admin_menu', array( $this, 'add_menus' ) );
        add_action( 'admin_init', array( $this, 'add_settings' ) );
        wp_register_script( 'pureclarity-adminjs', plugin_dir_url( __FILE__ ) . 'js/pc-admin.js', array( 'jquery' ), PURECLARITY_VERSION );
        wp_enqueue_script(  'pureclarity-adminjs' );
        add_action( 'wp_ajax_pureclarity_run_datafeed', array( $this, 'run_data_feed' ) );
    }

    public function run_data_feed() {
        $response = array(
            'totalPagesCount' => 23,
            'finished'        => false,
        );

        wp_send_json( $response );
    }

    public function add_menus() {
        add_menu_page(
            'PureClarity',
            'PureClarity',
            'manage_options',
            $this->settings_slug,
            array( $this, 'settings_render' ),
            ''
        );

        add_submenu_page(
            $this->settings_slug,
            'PureClarity: Settings',
            'Settings',
            "manage_options",
            $this->settings_slug,
            array( $this, 'settings_render' )
        );

        add_submenu_page(
            $this->settings_slug,
            'PureClarity: Data Feed Management',
            'Data Feeds',
            "manage_options",
            $this->datafeed_slug,
            array( $this, 'datafeed_render' )
        );
    }

    public function settings_render() {
        include_once( 'views/settings-page.php' );
    }

    public function datafeed_render() {
        include_once( 'views/datafeed-page.php' );
    }

    public function add_settings() {

        add_settings_section(
			$this->settings_section,
			null,
			array( $this, 'print_settings_section_text' ),
			$this->settings_slug
        );
        
        add_settings_field(
			'pureclarity_accesskey',
			'Access Key',
			array( $this, 'accesskey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_secretkey',
			'Secret Key',
			array( $this, 'secretkey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_search_enabled',
			'Enable Search',
			array( $this, 'search_enabled_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_merch_enabled',
			'Enable Merchandizing',
			array( $this, 'merch_enabled_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_prodlist_enabled',
			'Enable Product Listing',
			array( $this, 'prodlist_enabled_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        register_setting( $this->settings_option_group, 'pureclarity_accesskey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_secretkey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_search_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_merch_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_prodlist_enabled', array( $this, 'sanitize_checkbox' ) );


        add_settings_section(
			$this->datafeed_section,
			null,
			array( $this, 'print_datafeed_section_text' ),
			$this->datafeed_slug
        );

        add_settings_field(
			'pureclarity_product_feed',
			'Run Product Feed',
			array( $this, 'product_feed_callback' ),
			$this->datafeed_slug,
			$this->datafeed_section
        );

    }

    public function accesskey_callback() {
        ?>
		<input type="text" name="pureclarity_accesskey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_accesskey() ); ?>" />
		<p class="description" id="home-description">Enter your Access Key</p>
        <?php
    }
    
    public function secretkey_callback() {

        ?>
		<input type="text" name="pureclarity_secretkey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_secretkey() ); ?>" />
		<p class="description" id="home-description">Enter your Secret Key</p>
        <?php
    }
    
    public function search_enabled_callback() {

        $enabled = $this->settings->get_search_enabled();
        // die($enabled);
        $checked = '';
        if ( $enabled == 'yes' ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_search" name="pureclarity_search_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Search</p>
        <?php

    }
    
    public function merch_enabled_callback() {

        $enabled = $this->settings->get_merch_enabled();
        $checked = '';
        if ( $enabled == 'yes' ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_merch" name="pureclarity_merch_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Merchandizing</p>
        <?php

    }
    
    public function prodlist_enabled_callback() {

        $enabled = $this->settings->get_prod_enabled();
        $checked = '';
        if ( $enabled == 'yes' ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_prodlist"  name="pureclarity_prodlist_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Product Listing</p>
        <?php

    }

    
    public function sanitize_checkbox( $value ) {
		return $value === 'on' ? 'yes' : 'no';
    }

    public function print_settings_section_text() {
		echo '<p>' . 'Configure PureClarity access credentials. You can find them in PureClarity Admin console.' . '</p>';
		echo '<p>' . 'Once you have input the credentials you can then run a data feed.' . '</p>';
		echo '<p>' . wp_kses_post( 'To create an account simply contact the ? <a href="https://www.pureclarity.com" target="_blank">PureClarity</a> team to get one set up and start your free trial today!' ) . '</p>';
    }
    
    public function print_datafeed_section_text() {
		echo '<p>Data Feeds etc.</p>';
		echo '<p>' . wp_kses_post( 'To create an account simply contact the ? <a href="https://www.pureclarity.com" target="_blank">PureClarity</a> team to get one set up and start your free trial today!' ) . '</p>';
	}

}