<?php

class PureClarity_Settings
{
    public $scriptUrl = '//pcs.pureclarity.net';

    public function __construct() {
		add_option( 'pureclarity_accesskey', '' );
        add_option( 'pureclarity_secretkey', '' );
        add_option( 'pureclarity_search_enabled', 'no' );
        add_option( 'pureclarity_merch_enabled', 'no' );
        add_option( 'pureclarity_prodlist_enabled', 'no' );
        add_option( 'pureclarity_prodfeed_run', '0' );
        add_option( 'pureclarity_catfeed_run', '0' );
        add_option( 'pureclarity_brandfeed_run', '0' );
        add_option( 'pureclarity_userfeed_run', '0' );
    }
    
    public function get_accesskey() {
        return (string) get_option( 'pureclarity_accesskey', '' );
    }
    
    public function get_secretkey() {
        return (string) get_option( 'pureclarity_secretkey', '' );
    }

    public function get_search_enabled() {
        return (string) get_option( 'pureclarity_search_enabled', '' );
    }

    public function get_merch_enabled() {
        return (string) get_option( 'pureclarity_merch_enabled', '' );
    }

    public function get_prod_enabled() {
        return (string) get_option( 'pureclarity_prodlist_enabled', '' );
    }

    public function get_api_url() {
        $url = getenv('PURECLARITY_SCRIPT_URL');
        if (empty($url)){
            $url = $this->scriptUrl . '/' . $this->get_accesskey() . '/cs.js'; ;
        }
        return $url;
    }

    public function get_feed_baseurl() {

        $url = "http://hostip"; //getenv('PURECLARITY_FEED_HOST');
        $port = getenv('PURECLARITY_FEED_PORT');
        if (empty($url)){
            $url = "https://sftp.pureclarity.net";
        }
        if (!empty($port)){
            $url = $url . ":" . $port;
        }

        return $url . "/";
    }

    public function get_search_selector() {
        return "woocommerce-product-search-field-0";
    }

    public function get_search_result_element() {
        return ".site-main";
    }

    public function get_prodfeed_run() {
        return get_option( 'pureclarity_prodfeed_run', '0' ) == '1';
    }

    public function get_catfeed_run() {
        return get_option( 'pureclarity_catfeed_run', '0' ) == '1';
    }

    public function get_brandfeed_run() {
        return get_option( 'pureclarity_brandfeed_run', '0' ) == '1';
    }

    public function get_userfeed_run() {
        return get_option( 'pureclarity_userfeed_run', '0' ) == '1';
    }
}