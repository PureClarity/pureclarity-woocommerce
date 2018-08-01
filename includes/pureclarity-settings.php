<?php

class PureClarity_Settings
{
    public $scriptUrl = '//pcs.pureclarity.net';

    public function __construct() {
		add_option( 'pureclarity_accesskey', '' );
        add_option( 'pureclarity_secretkey', '' );
        add_option( 'pureclarity_mode', 'off' );
        add_option( 'pureclarity_search_enabled', 'no' );
        add_option( 'pureclarity_merch_enabled', 'no' );
        add_option( 'pureclarity_prodlist_enabled', 'no' );
        add_option( 'pureclarity_prodfeed_run', '0' );
        add_option( 'pureclarity_catfeed_run', '0' );
        add_option( 'pureclarity_brandfeed_run', '0' );
        add_option( 'pureclarity_userfeed_run', '0' );
        add_option( 'pureclarity_orderfeed_run', '0' );
        add_option( 'pureclarity_bmz_debug', 'no' );
        add_option( 'pureclarity_deltas_enabled', 'no' );
        add_option( 'pureclarity_search_selector', '.search-field' );
        add_option( 'pureclarity_search_result_selector', '#main' );
        add_option( 'pureclarity_add_bmz_homepage', 'yes' );
        add_option( 'pureclarity_add_bmz_categorypage', 'yes' );
        add_option( 'pureclarity_add_bmz_productpage', 'yes' );
        add_option( 'pureclarity_add_bmz_basketpage', 'yes' );
        add_option( 'pureclarity_add_bmz_checkoutpage', 'yes' );
    }
    
    public function get_accesskey() {
        return (string) get_option( 'pureclarity_accesskey', '' );
    }
    
    public function get_secretkey() {
        return (string) get_option( 'pureclarity_secretkey', '' );
    }

    public function get_pureclarity_mode() {
        return get_option( 'pureclarity_mode', 'off' );
    }

    public function get_pureclarity_enabled() {
        switch($this->get_pureclarity_mode()) {
            case "on":
                return true;
                break;
            case "admin":
                return current_user_can('administrator');
                break;
        }
        return false;
    }

    public function get_search_enabled_admin() {
        return get_option( 'pureclarity_search_enabled', '' ) == "yes";
    }

    public function get_merch_enabled_admin() {
        return get_option( 'pureclarity_merch_enabled', '' ) == "yes";
    }

    public function get_prod_enabled_admin() {
        return get_option( 'pureclarity_prodlist_enabled', '' ) == "yes";
    }

    public function get_deltas_enabled_admin() {
        return get_option( 'pureclarity_deltas_enabled', '' ) == "yes";
    }
    

    public function get_search_enabled() {
        return get_option( 'pureclarity_search_enabled', '' ) == "yes" && $this->get_pureclarity_enabled();
    }

    public function get_merch_enabled() {
        return get_option( 'pureclarity_merch_enabled', '' ) == "yes" && $this->get_pureclarity_enabled();
    }

    public function get_prod_enabled() {
        return get_option( 'pureclarity_prodlist_enabled', '' ) == "yes" && $this->get_pureclarity_enabled();
    }

    public function get_deltas_enabled() {
        return get_option( 'pureclarity_deltas_enabled', '' ) == "yes" && $this->get_pureclarity_enabled();
    }

    public function get_bmz_debug_enabled() {
        return get_option( 'pureclarity_bmz_debug', '' ) == "yes";
    }

    public function get_api_url() {
        $url = getenv('PURECLARITY_SCRIPT_URL');
        if (empty($url)){
            $url = $this->scriptUrl . '/' . $this->get_accesskey() . '/cs.js'; ;
        }
        return $url;
    }

    public function get_feed_baseurl() {

        $url = getenv('PURECLARITY_FEED_HOST');
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
        return (string) get_option( 'pureclarity_search_selector', '.search-field' );
    }

    public function get_search_result_element() {
        return (string) get_option( 'pureclarity_search_result_selector', '.site-main' );
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

    public function get_orderfeed_run() {
        return get_option( 'pureclarity_orderfeed_run', '0' ) == '1';
    }

    public function update_prodfeed_run() {
        update_option( 'pureclarity_prodfeed_run', '1' );
    }

    public function update_catfeed_run() {
        update_option( 'pureclarity_catfeed_run', '1' );
    }

    public function update_brandfeed_run() {
        update_option( 'pureclarity_brandfeed_run', '1' );
    }

    public function update_userfeed_run() {
        update_option( 'pureclarity_userfeed_run', '1' );
    }

    public function update_orderfeed_run() {
        update_option( 'pureclarity_orderfeed_run', '1' );
    }

    public function get_delta_url() {
        $url = getenv('PURECLARITY_API_ENDPOINT');
        $port =  getenv('PURECLARITY_API_PORT');
        if (empty($url)){
            $url = "https://api.pureclarity.net";
        }
        if (!empty($port)){
            $url = $url . ":" . $port;
        }

        return $url . "/api/productdelta";
    }

    public function add_bmz_homepage() {
        return get_option( 'pureclarity_add_bmz_homepage', '' ) == "yes";
    }

    public function add_bmz_categorypage() {
        return get_option( 'pureclarity_add_bmz_categorypage', '' ) == "yes";
    }

    public function add_bmz_searchpage() {
        return get_option( 'pureclarity_add_bmz_searchpage', '' ) == "yes";
    }

    public function add_bmz_productpage() {
        return get_option( 'pureclarity_add_bmz_productpage', '' ) == "yes";
    }

    public function add_bmz_basketpage() {
        return get_option( 'pureclarity_add_bmz_basketpage', '' ) == "yes";
    }

    public function add_bmz_checkoutpage() {
        return get_option( 'pureclarity_add_bmz_checkoutpage', '' ) == "yes";
    }
    
}