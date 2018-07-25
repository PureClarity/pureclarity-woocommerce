<?php

class PureClarity_Plugin 
{
    static $pureclarity;
    private $settings;
    private $template;

    public static function getInstance() {
        if ( self::$pureclarity === null ) {
            self::$pureclarity = new PureClarity_Plugin();
        }
        return self::$pureclarity;
    }


    public function __construct() {

        $this->settings = new PureClarity_Settings();
        $this->template = new PureClarity_Template( $this );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
        add_action( 'init', array( $this, 'init' ), 15 );
    }

    public function get_settings() {
        return $this->settings;
    }

    public function register_assets() {

    }

    public function init() {
        if ( is_admin() ) {
			new PureClarity_Admin( $this );
		}
    }

}