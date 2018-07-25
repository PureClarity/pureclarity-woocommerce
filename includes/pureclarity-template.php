<?php

class PureClarity_Template {

    private $plugin;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        
        add_filter( 'wp_head', array( $this, 'render_pureclarity_json' ) );
    }

    public function render_pureclarity_json() {

        $settings = $this->plugin->get_settings();

        $config = array(
            'accessKey' => $settings->get_accesskey(),
            'searchEnabled' => $settings->get_search_enabled() == "yes",
            'merchEnabled' => $settings->get_merch_enabled() == "yes",
            'prodListEnabled' => $settings->get_prod_enabled() == "yes"
		);

        echo '<script type="text/javascript">var pureclarityConfig = ' . wp_json_encode( $config ) . '; console.log(pureclarityConfig);</script>';
    }

}