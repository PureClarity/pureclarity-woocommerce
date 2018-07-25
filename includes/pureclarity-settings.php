<?php

class PureClarity_Settings
{
    public function __construct() {
		add_option( 'pureclarity_accesskey', '' );
        add_option( 'pureclarity_secretkey', '' );
        add_option( 'pureclarity_search_enabled', 'no' );
        add_option( 'pureclarity_merch_enabled', 'no' );
        add_option( 'pureclarity_prodlist_enabled', 'no' );
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
}