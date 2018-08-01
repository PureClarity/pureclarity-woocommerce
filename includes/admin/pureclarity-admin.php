<?php

class PureClarity_Admin
{
    private $settings_slug = 'pureclarity-settings';
    private $settings_section = 'pureclarity_section_settings';
    private $datafeed_slug = 'pureclarity-datafeed';
    private $datafeed_section = 'pureclarity_section_datafeed';
    private $advanced_section = 'pureclarity_section_advanced';
    private $advanced_slug = 'pureclarity-advanced';
    private $settings_option_group = 'pureclarity_settings';
    private $datafeed_option_group = 'pureclarity_datafeed';
    private $advanced_option_group = 'pureclarity_advanced';
    private $plugin;
    private $settings;
    private $feed;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        $this->settings = $this->plugin->get_settings();
        $this->feed = $this->plugin->get_feed();

        add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
        add_action( 'admin_menu', array( $this, 'add_menus' ) );
        add_action( 'admin_init', array( $this, 'add_settings' ) );
        wp_register_script( 'pureclarity-adminjs', plugin_dir_url( __FILE__ ) . 'js/pc-admin.js', array( 'jquery' ), PURECLARITY_VERSION );
        wp_enqueue_script(  'pureclarity-adminjs' );
        add_action( 'wp_ajax_pureclarity_run_datafeed', array( $this, 'run_data_feed' ) );
    }


    public function run_data_feed() {
        try {

            if ( ! isset( $_POST['page'] ) ) {
				throw new RuntimeException( 'Page has not been set.' );
			}
            $currentPage = (int) $_POST['page'];
            
            if ( ! isset( $_POST['type'] ) ) {
				throw new RuntimeException( 'Type has not been set.' );
			}
            $type = $_POST['type'];
            
            if ($type !== "product" && $type !== "category" && $type !== "brand" && $type !== "user" && $type !== "order") {
				throw new RuntimeException( 'Unknown type.' );
            }
            
            $totalPagesCount = $this->feed->get_total_pages($type);

            if ( $currentPage ===1 ) {
                $this->feed->start_feed( $type );
            }

            if ( $currentPage <= $totalPagesCount || $totalPagesCount === 0 ) {
                $data = $this->feed->build_items( $type, $currentPage );
                $this->feed->send_data( $type, $data );
            }

            $finished = $currentPage >= $totalPagesCount;

            if ($finished && $totalPagesCount > 0) {
                $this->feed->end_feed( $type );
                $this->update_successfeed( $type );
            }

            $response = array(
                'totalPagesCount' => $totalPagesCount,
                'finished'        => $finished,
            );

            wp_send_json( $response );

        } catch ( \Exception $exception ) {
            error_log("PureClarity: An Error occured generating " . $type . " feed: " . $exception->getMessage() );
            wp_send_json( array( "error" => "An Error occured generating the " . $type . " feed. See error logs for more information.") );
        }
    }

    public function update_successfeed( $type ) {
        switch($type) {
            case "product":
                $this->settings->update_prodfeed_run();
            break;
            case "category":
                $this->settings->update_catfeed_run();
            break;
            case "brand":
                $this->settings->update_brandfeed_run();
            break;
            case "user":
                $this->settings->update_userfeed_run();
            break;
            case "order":
                $this->settings->update_orderfeed_run();
            break;
        }
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
    
        add_submenu_page(
            $this->settings_slug,
            'PureClarity: Advanced Settings',
            'Advanced',
            "manage_options",
            $this->advanced_slug,
            array( $this, 'advanced_render' )
        );
    }

    public function settings_render() {
        include_once( 'views/settings-page.php' );
    }

    public function datafeed_render() {
        include_once( 'views/datafeed-page.php' );
    }

    public function advanced_render() {
        include_once( 'views/advanced-page.php' );
    }

    public function add_settings() {

        // General Settings
        add_settings_section(
			$this->settings_section,
			"General Settings",
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
			'pureclarity_mode',
			'Enable Mode',
			array( $this, 'pureclarity_mode_callback' ),
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

        add_settings_field(
			'pureclarity_deltas_enabled',
			'Enable Product Delta Sync',
			array( $this, 'enabled_deltas_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        register_setting( $this->settings_option_group, 'pureclarity_accesskey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_secretkey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_mode', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_search_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_merch_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_prodlist_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_deltas_enabled', array( $this, 'sanitize_checkbox' ) );

        // Advanced Settings
        add_settings_section(
			$this->advanced_section,
			null,
			array( $this, 'print_advanced_section_text' ),
			$this->advanced_slug
        );

        add_settings_field(
			'pureclarity_bmz_debug',
			'Enable BMZ Debugging',
			array( $this, 'pureclarity_bmz_debug_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_search_selector',
			'Autocomplete Input DOM Selector',
			array( $this, 'searchselector_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_search_result_selector',
			'Search Results DOM Selector',
			array( $this, 'searchresults_selector_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_homepage',
			'Show Home Page BMZs',
			array( $this, 'pureclarity_add_bmz_homepage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_categorypage',
			'Show Product Listing BMZs',
			array( $this, 'pureclarity_add_bmz_categorypage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_searchpage',
			'Show Search Results BMZs',
			array( $this, 'pureclarity_add_bmz_searchpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_productpage',
			'Show Product Page BMZs',
			array( $this, 'pureclarity_add_bmz_productpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_basketpage',
			'Show Cart Page BMZs',
			array( $this, 'pureclarity_add_bmz_basketpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_checkoutpage',
            'Show Checkout Page BMZs',
			array( $this, 'pureclarity_add_bmz_checkoutpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        register_setting( $this->advanced_option_group, 'pureclarity_bmz_debug', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_search_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_search_result_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_homepage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_categorypage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_searchpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_productpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_basketpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_checkoutpage', array( $this, 'sanitize_checkbox' ) );

        // Data feed section
        add_settings_section(
			$this->datafeed_section,
			null,
			null,
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


    public function pureclarity_mode_callback() {

        $mode = $this->settings->get_pureclarity_mode();

        ?>
        <select id="pureclarity_mode" name="pureclarity_mode">
            <option  value="on" <?php echo $mode=="on"?"selected='selected'":"" ?> >On</option>
            <option  value="admin" <?php echo $mode=="admin"?"selected='selected'":"" ?>>Admin only</option>
            <option  value="off" <?php echo $mode=="off"?"selected='selected'":"" ?>>Off</option>
        </select>
        <p class="description" id="home-description">Set PureClarity Enable Mode. Then the mode is set to 'Admin only' PureClarity only shows for you.</p>
        <?php
    }
    
    public function search_enabled_callback() {

        $enabled = $this->settings->get_search_enabled_admin();
        // die($enabled);
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_search" name="pureclarity_search_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Search</p>
        <?php

    }
    
    public function merch_enabled_callback() {

        $enabled = $this->settings->get_merch_enabled_admin();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_merch" name="pureclarity_merch_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Merchandizing</p>
        <?php

    }
    
    public function prodlist_enabled_callback() {

        $enabled = $this->settings->get_prod_enabled_admin();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_prodlist"  name="pureclarity_prodlist_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate PureClarity Product Listing</p>
        <?php

    }

    public function enabled_deltas_callback() {

        $enabled = $this->settings->get_deltas_enabled_admin();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_deltas"  name="pureclarity_deltas_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate automatic data synchronisation</p>
        <?php

    }

    public function pureclarity_bmz_debug_callback() {

        $enabled = $this->settings->get_bmz_debug_enabled();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_debug"  name="pureclarity_bmz_debug" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Check to activate debugging for PureClarity BMZs. They will show even if empty.</p>
        <?php

    }

    public function pureclarity_add_bmz_homepage_callback() {

        $enabled = $this->settings->add_bmz_homepage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_homepage"  name="pureclarity_add_bmz_homepage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Home Page.</p>
        <?php

    }

    public function pureclarity_add_bmz_categorypage_callback() {

        $enabled = $this->settings->add_bmz_categorypage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_categorypage"  name="pureclarity_add_bmz_categorypage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Product Listing Page.</p>
        <?php

    }

    public function pureclarity_add_bmz_searchpage_callback() {

        $enabled = $this->settings->add_bmz_searchpage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_searchpage"  name="pureclarity_add_bmz_searchpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Search Results Listing Page.</p>
        <?php

    }

    public function pureclarity_add_bmz_productpage_callback() {

        $enabled = $this->settings->add_bmz_productpage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_productpage"  name="pureclarity_add_bmz_productpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Product Page.</p>
        <?php

    }

    public function pureclarity_add_bmz_basketpage_callback() {

        $enabled = $this->settings->add_bmz_basketpage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_basketpage"  name="pureclarity_add_bmz_basketpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Cart Page.</p>
        <?php

    }

    public function pureclarity_add_bmz_checkoutpage_callback() {

        $enabled = $this->settings->add_bmz_checkoutpage();
        $checked = '';
        if ( $enabled == true ) {
            $checked = 'checked';
        }

        ?>
		<input type="checkbox" id="checkbox_bmz_checkoutpage"  name="pureclarity_add_bmz_checkoutpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description">Auto insert BMZs on Checkout Complete Page.</p>
        <?php

    }


    public function searchselector_callback() {
        ?>
		<input type="text" name="pureclarity_search_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_selector() ); ?>" />
		<p class="description" id="home-description">Enter DOM selector for the autocomplete input box. (Default is .search-field)</p>
        <?php
    }

    public function searchresults_selector_callback() {
        ?>
		<input type="text" name="pureclarity_search_result_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_result_element() ); ?>" />
		<p class="description" id="home-description">Enter DOM selector for the main body where search results will be displayed. (Default is #main)</p>
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
    

    public function print_advanced_section_text() {
		echo '<p>Configure advanced settings for PureClarity. ' . wp_kses_post( 'For more information visit the <a href="https://support.pureclarity.com" target="_blank">PureClarity</a> support page.' ) . '</p>';
    }


    public function display_dependency_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
					  <p>PureClarity requires the "cURL" PHP extension to be installed and enabled. Please contact your hosting provider.</p>
				  </div>';
        }
        
        $whitelist_admin_pages = array( 'toplevel_page_pureclarity-settings', 'pureclarity_page_pureclarity-advanced' );
        $admin_page = get_current_screen();

        if( in_array( $admin_page->base, $whitelist_admin_pages ) && isset( $_GET[ 'settings-updated' ] ) &&  $_GET[ 'settings-updated' ] ):
        ?>
            <div class="notice notice-success is-dismissible"> 
                <p><strong>Settings saved.</strong></p>
            </div>
        <?php
        endif;
	}

}