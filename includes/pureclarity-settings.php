<?php

class PureClarity_Settings
{
    public $scriptUrl = '//pcs.pureclarity.net';
    private $regions = array(  
        "1" => "https://api-eu-w-1.pureclarity.net",
        "2" => "https://api-eu-w-2.pureclarity.net",
        "3" => "https://api-eu-c-1.pureclarity.net",
        "4" => "https://api-us-e-1.pureclarity.net",
        "5" => "https://api-us-e-2.pureclarity.net",
        "6" => "https://api-us-w-1.pureclarity.net",
        "7" => "https://api-us-w-2.pureclarity.net",
        "8" => "https://api-ap-s-1.pureclarity.net",
        "9" => "https://api-ap-ne-1.pureclarity.net",
        "10" => "https://api-ap-ne-2.pureclarity.net",
        "11" => "https://api-ap-se-1.pureclarity.net",
        "12" => "https://api-ap-se-2.pureclarity.net",
        "13" => "https://api-ca-c-1.pureclarity.net",
        "14" => "https://api-sa-e-1.pureclarity.net"
    );

    private $sftpRegions = array( 
        "1" => "https://sftp-eu-w-1.pureclarity.net",
        "2" => "https://sftp-eu-w-2.pureclarity.net",
        "3" => "https://sftp-eu-c-1.pureclarity.net",
        "4" => "https://sftp-us-e-1.pureclarity.net",
        "5" => "https://sftp-us-e-2.pureclarity.net",
        "6" => "https://sftp-us-w-1.pureclarity.net",
        "7" => "https://sftp-us-w-2.pureclarity.net",
        "8" => "https://sftp-ap-s-1.pureclarity.net",
        "9" => "https://sftp-ap-ne-1.pureclarity.net",
        "10" => "https://sftp-ap-ne-2.pureclarity.net",
        "11" => "https://sftp-ap-se-1.pureclarity.net",
        "12" => "https://sftp-ap-se-2.pureclarity.net",
        "13" => "https://sftp-ca-c-1.pureclarity.net",
        "14" => "https://sftp-sa-e-1.pureclarity.net"
    );

    public function __construct() {
		add_option( 'pureclarity_accesskey', '' );
        add_option( 'pureclarity_secretkey', '' );
        add_option( 'pureclarity_region', '1' );
        add_option( 'pureclarity_mode', 'off' );
        // add_option( 'pureclarity_search_enabled', 'no' );
        // add_option( 'pureclarity_merch_enabled', 'no' );
        add_option( 'pureclarity_shop_enabled', 'no');
        // add_option( 'pureclarity_prodlist_enabled', 'no' );
        add_option( 'pureclarity_prodfeed_run', '0' );
        add_option( 'pureclarity_catfeed_run', '0' );
        add_option( 'pureclarity_brandfeed_run', '0' );    
        add_option( 'pureclarity_userfeed_run', '0' );
        add_option( 'pureclarity_orderfeed_run', '0' );
        add_option( 'pureclarity_bmz_debug', 'no' );
        add_option( 'pureclarity_deltas_enabled', 'no' );
        add_option( 'pureclarity_search_selector', '.search-field' );
        add_option( 'pureclarity_search_result_selector', '#main' );
        add_option( 'pureclarity_shop_result_selector', '#main' );
        add_option( 'pureclarity_prodlist_result_selector', '#main' );
        add_option( 'pureclarity_add_bmz_homepage', 'yes' );
        add_option( 'pureclarity_add_bmz_searchpage', 'yes' );
        add_option( 'pureclarity_add_bmz_categorypage', 'yes' );
        add_option( 'pureclarity_add_bmz_productpage', 'yes' );
        add_option( 'pureclarity_add_bmz_basketpage', 'yes' );
        add_option( 'pureclarity_add_bmz_checkoutpage', 'yes' );
        add_option( 'pureclarity_product_deltas', '{}' );
        add_option( 'pureclarity_category_feed_required', '' );
        add_option( 'pureclarity_user_deltas', '{}' );
    }
    
    public function get_accesskey() {
        return (string) get_option( 'pureclarity_accesskey', '' );
    }
    
    public function get_secretkey() {
        return (string) get_option( 'pureclarity_secretkey', '' );
    }

    public function get_regions() {
        return $this->regions;
    }

    public function get_region() {
        return (string) get_option( 'pureclarity_region', '1' );
    }

    public function get_pureclarity_mode() {
        return get_option( 'pureclarity_mode', 'off' );
    }

    public function is_pureclarity_enabled() {
        switch( $this->get_pureclarity_mode() ) {
            case "on":
                return true;
            case "admin":
                return current_user_can( 'administrator' );
        }
        return false;
    }

    public function is_search_enabled_admin() {
        return false;
        // return ( get_option( 'pureclarity_search_enabled', '' ) == "yes" );
    }

    public function is_merch_enabled_admin() {
        return true;
        // return ( get_option( 'pureclarity_merch_enabled', '' ) == "yes" );
    }

    public function is_prod_enabled_admin() {
        return false;
        // return ( get_option( 'pureclarity_prodlist_enabled', '' ) == "yes" );
    }

    public function is_deltas_enabled_admin() {
        return ( get_option( 'pureclarity_deltas_enabled', '' ) == "yes" );
    }

    public function is_search_enabled() {
        return false;
        // return $this->is_search_enabled_admin() && $this->is_pureclarity_enabled();
    }

    public function is_merch_enabled() {
        return true;
        // return $this->is_merch_enabled_admin() && $this->is_pureclarity_enabled();
    }

    public function is_prod_enabled() {
        return false;
        // return $this->is_prod_enabled_admin() && $this->is_pureclarity_enabled();
    }

    public function is_deltas_enabled() {
        return $this->is_deltas_enabled_admin() && $this->is_pureclarity_enabled();
    }

    public function is_bmz_debug_enabled() {
        return ( get_option( 'pureclarity_bmz_debug', '' ) == "yes" );
    }

    public function is_shop_enabled_admin() {
        return ( get_option( 'pureclarity_shop_enabled', '') == "yes" );
    }

    public function get_api_url() {
        $url = getenv( 'PURECLARITY_SCRIPT_URL' );
        if ( empty( $url ) ){
            $url = $this->scriptUrl . '/' . $this->get_accesskey() . '/cs.js';
        }
        return $url;
    }

    public function get_feed_baseurl() {
        $url = getenv( 'PURECLARITY_FEED_HOST' );
        $port = getenv( 'PURECLARITY_FEED_PORT' );
        if ( empty( $url ) ) {
            $url = $this->sftpRegions[ $this->get_region() ];
        }
        if ( ! empty( $port ) ){
            $url = $url . ":" . $port;
        }
        return $url . "/";
    }

    public function get_search_selector() {
        return (string) get_option( 'pureclarity_search_selector', '.search-field' );
    }

    public function get_shop_selector() {
        return (string) get_option( 'pureclarity_shop_selector', '#main' );
    }

    public function get_search_result_element() {
        return (string) get_option( 'pureclarity_search_result_selector', '#main' );
    }

    public function get_prodlist_result_element() {
        return (string) get_option( 'pureclarity_prodlist_selector', '#main' );
    }

    public function is_product_feed_sent() {
        return ( get_option( 'pureclarity_prodfeed_run', '0' ) == '1' );
    }

    public function is_category_feed_sent() {
        return ( get_option( 'pureclarity_catfeed_run', '0' ) == '1' );
    }

    public function is_brand_feed_sent() {
        return ( get_option( 'pureclarity_brandfeed_run', '0' ) == '1' );
    }

    public function is_user_feed_sent() {
        return ( get_option( 'pureclarity_userfeed_run', '0' ) == '1' );
    }

    public function is_order_feed_sent() {
        return ( get_option( 'pureclarity_orderfeed_run', '0' ) == '1' );
    }

    public function set_feed_type_sent( $type ) {
        $option = "";
        switch( $type ) {
            case "product":
                $option = 'pureclarity_prodfeed_run';
                break;
            case "category":
                $option = 'pureclarity_catfeed_run';
                break;
            case "brand":
                $option = 'pureclarity_brandfeed_run';
                break;
            case "user":
                $option = 'pureclarity_userfeed_run';
                break;
            case "order":
                $option = 'pureclarity_orderfeed_run';
                break;
        }
        if( ! empty( $option ) ) {
            update_option( $option, '1' );
        }
    }

    public function get_delta_url() {
        $url = getenv( 'PURECLARITY_API_ENDPOINT' );
        $port = getenv( 'PURECLARITY_API_PORT' );
        if ( empty( $url ) ) {
            $url = $this->regions[ $this->get_region() ];
        }
        if ( ! empty( $port ) ) {
            $url = $url . ":" . $port;
        }
        return $url . "/api/delta";
    }

    public function is_bmz_on_home_page() {
        return ( get_option( 'pureclarity_add_bmz_homepage', '' ) == "yes" );
    }

    public function is_bmz_on_category_page() {
        return ( get_option( 'pureclarity_add_bmz_categorypage', '' ) == "yes" );
    }

    public function is_bmz_on_search_page() {
        return ( get_option( 'pureclarity_add_bmz_searchpage', '' ) == "yes" );
    }

    public function is_bmz_on_product_page() {
        return ( get_option( 'pureclarity_add_bmz_productpage', '' ) == "yes" );
    }

    public function is_bmz_on_basket_page() {
        return ( get_option( 'pureclarity_add_bmz_basketpage', '' ) == "yes" );
    }

    public function is_bmz_on_checkout_page() {
        return ( get_option( 'pureclarity_add_bmz_checkoutpage', '' ) == "yes" );
    }
    
    public function add_prod_delta_delete( $id ) {
        $this->add_prod_delta( $id, -1 );
    }

    public function add_prod_delta( $id, $size ) {
        $deltas = $this->get_prod_deltas();
        if ( empty( $deltas ) ) {
            $deltas = array();
        }
        $json = json_encode( $deltas, true );
        $deltas[ $id ] = $size;
        update_option( 'pureclarity_product_deltas', json_encode( $deltas, true ) );
    }

    public function remove_prod_delta( $id ) {
        $deltas = $this->get_prod_deltas();
        if ( ! empty( $deltas ) && array_key_exists( $id, $deltas ) ) {
            unset( $deltas[ $id ] );
            update_option( 'pureclarity_product_deltas', json_encode( $deltas, true ) );
        }
    }

    public function get_prod_deltas() {
        $deltastring = get_option( 'pureclarity_product_deltas', '{}' );
        if ( ! empty( $deltastring ) ) {
            return json_decode( $deltastring, true );
        }
        return array();
    }

    public function set_category_feed_required() {
        update_option( 'pureclarity_category_feed_required', time() );
    }

    public function clear_category_feed_required() {
        update_option( 'pureclarity_category_feed_required', '' );
    }

    public function get_category_feed_required() {
        return get_option( 'pureclarity_category_feed_required', '' );
    }

    public function set_user_feed_required() {
        update_option( 'pureclarity_user_feed_required', time() );
    }

    public function clear_user_feed_required() {
        update_option( 'pureclarity_user_feed_required', '' );
    }
    
    public function add_user_delta_delete( $id ) {
        $this->add_user_delta( $id, -1 );
    }

    public function add_user_delta( $id, $size ) {
        $deltas = $this->get_user_deltas();
        if ( empty( $deltas ) ) {
            $deltas = array();
        }
        $json = json_encode( $deltas, true );
        $deltas[ $id ] = $size;
        update_option( 'pureclarity_user_deltas', json_encode( $deltas, true ) );
    }

    public function remove_user_delta( $id ) {
        $deltas = $this->get_user_deltas();
        if ( !empty($deltas) && array_key_exists( $id, $deltas ) ) {
            unset( $deltas[ $id ] );
            update_option( 'pureclarity_user_deltas', json_encode( $deltas, true ) );
        }
    }

    public function get_user_deltas() {
        $deltastring = get_option( 'pureclarity_user_deltas', '{}' );
        return ( ! empty( $deltastring ) ? json_decode( $deltastring, true ) : array() );
    }

}