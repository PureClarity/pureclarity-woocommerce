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

    public function __construct( &$plugin ) {

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
            session_write_close();
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
            error_log("PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
            wp_send_json( array( "error" => "An error occurred generating the {$type} feed. See error logs for more information.") );
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

        $settings = __('Settings', 'pureclarity');
        add_submenu_page(
            $this->settings_slug,
            "PureClarity: {$settings}",
            $settings,
            "manage_options",
            $this->settings_slug,
            array( $this, 'settings_render' )
        );

        add_submenu_page(
            $this->settings_slug,
            'PureClarity: ' . __('Data Feed Management', 'pureclarity'),
            __('Data Feeds', 'pureclarity'),
            "manage_options",
            $this->datafeed_slug,
            array( $this, 'datafeed_render' )
        );
    
        add_submenu_page(
            $this->settings_slug,
            'PureClarity: ' . __('Advanced Settings', 'pureclarity'),
            __('Advanced', 'pureclarity'),
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
			__("General Settings", 'pureclarity'),
			array( $this, 'print_settings_section_text' ),
			$this->settings_slug
        );
        
        add_settings_field(
			'pureclarity_accesskey',
			__('Access Key', 'pureclarity'),
			array( $this, 'accesskey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_secretkey',
			__('Secret Key', 'pureclarity'),
			array( $this, 'secretkey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_region',
			__('Region', 'pureclarity'),
			array( $this, 'pureclarity_region_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_mode',
			__('Enable Mode', 'pureclarity'),
			array( $this, 'pureclarity_mode_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

   //      add_settings_field(
			// 'pureclarity_search_enabled',
			// 'Enable Search',
			// array( $this, 'search_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

   //      add_settings_field(
			// 'pureclarity_prodlist_enabled',
			// 'Enable Product Listing',
			// array( $this, 'prodlist_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

   //      add_settings_field(
			// 'pureclarity_merch_enabled',
			// 'Enable Merchandizing',
			// array( $this, 'merch_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

        add_settings_field(
            'pureclarity_shop_enabled',
            __('Enable Shop', 'pureclarity'),
            array( $this, 'shop_enabled_callback' ),
            $this->settings_slug,
            $this->settings_section
        );

        add_settings_field(
			'pureclarity_deltas_enabled',
			__('Enable Delta Sync', 'pureclarity'),
			array( $this, 'enabled_deltas_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        register_setting( $this->settings_option_group, 'pureclarity_accesskey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_secretkey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_region', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_mode', 'sanitize_callback' );
        // register_setting( $this->settings_option_group, 'pureclarity_search_enabled', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->settings_option_group, 'pureclarity_merch_enabled', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->settings_option_group, 'pureclarity_prodlist_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_deltas_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_shop_enabled', array($this, 'sanitize_checkbox') );

        // Advanced Settings
        add_settings_section(
			$this->advanced_section,
			null,
			array( $this, 'print_advanced_section_text' ),
			$this->advanced_slug
        );

        add_settings_field(
			'pureclarity_bmz_debug',
			__('Enable BMZ Debugging', 'pureclarity'),
			array( $this, 'pureclarity_bmz_debug_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

   //      add_settings_field(
			// 'pureclarity_search_selector',
			// 'Autocomplete Input DOM Selector',
			// array( $this, 'searchselector_callback' ),
			// $this->advanced_slug,
			// $this->advanced_section
   //      );

   //      add_settings_field(
			// 'pureclarity_search_result_selector',
			// 'Search Results DOM Selector',
			// array( $this, 'searchresults_selector_callback' ),
			// $this->advanced_slug,
			// $this->advanced_section
   //      );

        // add_settings_field(
        //     'pureclarity_prodlist_result_selector',
        //     'Product List DOM Selector',
        //     array ($this, 'prodlist_selector_callback'),
        //     $this->advanced_slug,
        //     $this->advanced_section
        // );

        add_settings_field(
            'pureclarity_shop_selector',
            __('Shop DOM Selector', 'pureclarity'),
            array ($this, 'shop_selector_callback'),
            $this->advanced_slug,
            $this->advanced_section
        );


        add_settings_field(
			'pureclarity_add_bmz_homepage',
			__('Show Home Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_homepage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_categorypage',
			__('Show Product Listing BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_categorypage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_searchpage',
			__('Show Search Results BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_searchpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_productpage',
			__('Show Product Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_productpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_basketpage',
			__('Show Cart Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_basketpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_checkoutpage',
            __('Show Order Confirmation BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_checkoutpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        register_setting( $this->advanced_option_group, 'pureclarity_bmz_debug', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->advanced_option_group, 'pureclarity_search_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_shop_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_prodlist_selector', 'sanitize_callback' );
        // register_setting( $this->advanced_option_group, 'pureclarity_search_result_selector', 'sanitize_callback' );
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
			__('Run Product Feed', 'pureclarity'),
			array( $this, 'product_feed_callback' ),
			$this->datafeed_slug,
			$this->datafeed_section
        );

    }

    public function accesskey_callback() {
        ?>
		<input type="text" name="pureclarity_accesskey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_accesskey() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter your Access Key', 'pureclarity'); ?></p>
        <?php
    }
    
    public function secretkey_callback() {

        ?>
		<input type="text" name="pureclarity_secretkey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_secretkey() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter your Secret Key', 'pureclarity'); ?></p>
        <?php
    }

    public function pureclarity_region_callback() {

        $regions = $this->settings->get_regions();
        $region = $this->settings->get_region();

        ?>
        <select id="pureclarity_region" name="pureclarity_region">
            <?php foreach($regions as $key=>$url): ?>
                <option value="<?php echo $key ?>" <?php echo ( $region == $key ? "selected='selected'" : "" ); ?>><?php _e('Region', 'pureclarity'); ?> <?php echo $key ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description" id="home-description"><?php _e('Select the Region Id supplied with your PureClarity credentials', 'pureclarity'); ?></p>
        <?php
    }


    public function pureclarity_mode_callback() {

        $mode = $this->settings->get_pureclarity_mode();
        $selected = "selected='selected'";

        ?>

        <select id="pureclarity_mode" name="pureclarity_mode">
            <option value="on" <?php echo ( $mode == "on" ? $selected : "" ); ?> ><?php _e('On', 'pureclarity'); ?></option>
            <option value="admin" <?php echo ( $mode == "admin" ? $selected : "" ); ?>><?php _e('Admin only', 'pureclarity'); ?></option>
            <option value="off" <?php echo ( $mode=="off" ? $selected : "" ); ?>><?php _e('Off', 'pureclarity'); ?></option>
        </select>
        <p class="description" id="home-description"><?php _e("Set PureClarity Enable Mode. When the mode is set to 'Admin only' PureClarity only shows for administrators on the front end.", 'pureclarity'); ?></p>

        <?php
    }
    
    public function search_enabled_callback() {

        $enabled = $this->settings->get_search_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_search" name="pureclarity_search_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Search.', 'pureclarity'); ?></p>
        <?php

    }
    
    public function merch_enabled_callback() {

        $enabled = $this->settings->get_merch_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_merch" name="pureclarity_merch_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Merchandizing', 'pureclarity'); ?></p>

        <?php

    }
    
    public function prodlist_enabled_callback() {

        $enabled = $this->settings->get_prod_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_prodlist"  name="pureclarity_prodlist_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Product Listing', 'pureclarity'); ?></p>

        <?php

    }

    public function shop_enabled_callback() {

        $enabled = $this->settings->get_shop_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_shop"  name="pureclarity_shop_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity on the Shop page', 'pureclarity'); ?></p>

        <?php

    }

    public function enabled_deltas_callback() {

        $enabled = $this->settings->get_deltas_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_deltas"  name="pureclarity_deltas_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate automatic data synchronisation', 'pureclarity'); ?></p>

        <?php

    }

    public function pureclarity_bmz_debug_callback() {

        $enabled = $this->settings->get_bmz_debug_enabled();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_debug"  name="pureclarity_bmz_debug" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate debugging for PureClarity BMZs. They will show even if empty.', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_homepage_callback() {

        $enabled = $this->settings->add_bmz_homepage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_homepage"  name="pureclarity_add_bmz_homepage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Home Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_categorypage_callback() {

        $enabled = $this->settings->add_bmz_categorypage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_categorypage"  name="pureclarity_add_bmz_categorypage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Product Listing Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_searchpage_callback() {

        $enabled = $this->settings->add_bmz_searchpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_searchpage"  name="pureclarity_add_bmz_searchpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Search Results Listing Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_productpage_callback() {

        $enabled = $this->settings->add_bmz_productpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_productpage"  name="pureclarity_add_bmz_productpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Product Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_basketpage_callback() {

        $enabled = $this->settings->add_bmz_basketpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_basketpage"  name="pureclarity_add_bmz_basketpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Cart Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_checkoutpage_callback() {

        $enabled = $this->settings->add_bmz_checkoutpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_checkoutpage"  name="pureclarity_add_bmz_checkoutpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Order Confirmation Page', 'pureclarity'); ?></p>
        <?php

    }

    public function searchselector_callback() {
        ?>
		<input type="text" name="pureclarity_search_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_selector() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the autocomplete input box (default is .search-field).', 'pureclarity'); ?></p>
        <?php
    }


    public function searchresults_selector_callback() {
        ?>
		<input type="text" name="pureclarity_search_result_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_result_element() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where search results will be displayed (default is #main).', 'pureclarity'); ?></p>
        <?php
    }


    public function shop_selector_callback() {
        ?>
		<input type="text" name="pureclarity_shop_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_shop_selector() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where shop results will be displayed (default is #main).', 'pureclarity'); ?></p>
        <?php
    }

    public function prodlist_selector_callback() {
        ?>
		<input type="text" name="pureclarity_prodlist_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_prodlist_result_element() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where product list results will be displayed (default is #main)', 'pureclarity'); ?></p>
        <?php
    }
    
    public function sanitize_checkbox( $value ) {
		return ( $value == 'on' ? 'yes' : 'no' );
    }

    public function print_settings_section_text() {
        echo "<p>" . __('To get started with PureClarity, you will need a PureClarity account and to then enter your access credentials below.', 'pureclarity') . "</p>";
        $url = "https://www.pureclarity.com/free-trial/?source=woocommerce&medium=listing&campaign=freetrial";
        $link = sprintf( 
            wp_kses(    // sanitize result 
                __( "If you don't yet have an account, <a href='%s' target='_blank'>register for your free trial today</a>.", 'pureclarity' ), 
                array(      // permitted html
                    'a' => array( 
                        'href' => array(),
                        'target' => array() 
                        ) 
                    ) 
            ), 
            esc_url( $url ) 
        );
        echo $link;
    }

    public function print_advanced_section_text() {
        $url = "https://support.pureclarity.com/hc/en-us/sections/360001594074-WooCommerce";
        $link = sprintf( 
            wp_kses(    // sanitize result 
                __( "Configure advanced settings for PureClarity.  For more information, please see the <a href='%s' target='_blank'>PureClarity support documentation</a>.", 'pureclarity' ), 
                array(      // permitted html
                    'a' => array( 
                        'href' => array(),
                        'target' => array()
                        ) 
                    ) 
            ), 
            esc_url( $url ) 
        );
        echo $link;
    }

    public function display_dependency_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
                    <p>';

            printf(
                __("PureClarity requires the %s extension to be installed and enabled. Please contact your hosting provider.", 'pureclarity'),
                '"cURL" PHP'
            );

            echo '</p>
				</div>';
        }
        
        $whitelist_admin_pages = array( 'toplevel_page_pureclarity-settings', 'pureclarity_page_pureclarity-advanced' );
        $admin_page = get_current_screen();

        if( in_array( $admin_page->base, $whitelist_admin_pages ) 
            && isset( $_GET[ 'settings-updated' ] ) 
            &&  $_GET[ 'settings-updated' ] ):
        ?>
            <div class="notice notice-success is-dismissible"> 
                <p><strong><?php _e('Settings saved.', 'pureclarity'); ?></strong></p>
            </div>
        <?php
        endif;
	}

}