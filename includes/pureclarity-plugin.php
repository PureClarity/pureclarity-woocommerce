<?php

class PureClarity_Plugin 
{
    private $bmz;
    private $feed;
    static $pureclarity;
    private $settings;
    private $state;

    public function __construct() {

        $this->settings = new PureClarity_Settings();
        
        $this->feed = new PureClarity_Feed( $this );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
        add_action( 'init', array( $this, 'init' ), 15 );
    }

    public static function getInstance() {
        if ( self::$pureclarity === null ) {
            self::$pureclarity = new PureClarity_Plugin();
        }
        return self::$pureclarity;
    }

    public function get_settings() {
        return $this->settings;
    }

    public function get_feed() {
        return $this->feed;
    }

    public function get_state() {
        return $this->state;
    }

    public function get_bmz() {
        return $this->bmz;
    }

    public function register_assets() {
        wp_register_style( 'pureclarity-css', plugin_dir_url( __FILE__ ) . '../css/pc.css', array(), PURECLARITY_VERSION, 'screen' );
        wp_enqueue_style( 'pureclarity-css' );

        wp_register_script( 'pureclarity-js', plugin_dir_url( __FILE__ ) . '../js/pc.js', array( 'jquery', 'wp-util' ), PURECLARITY_VERSION );
        wp_enqueue_script(  'pureclarity-js' );
    }

    public function init() {
        if ( is_admin() ) {
			new PureClarity_Admin( $this );
		} 
        else {
            if( $this->settings->is_pureclarity_enabled() ) {
                add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
            }
            $this->state = new PureClarity_State( $this );
            $this->bmz = new PureClarity_Bmz( $this );
            new PureClarity_Template( $this );
        }
        new PureClarity_Products_Watcher( $this );
        new PureClarity_Cron( $this );
    }

}