<?php

class PureClarity_Template {

    private $plugin;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;        
        add_filter( 'wp_head', array( $this, 'render_pureclarity_json' ) );
    }

    public function render_pureclarity_json() {

        $settings = $this->plugin->get_settings();
        $searchEnabled = $settings->get_search_enabled();
        $merchEnabled = $settings->get_merch_enabled();
        $prodListEnabled = $settings->get_prod_enabled();
        $searchResultsDOMSelector = $settings->get_search_result_element();
        $enabled = ($searchEnabled || $merchEnabled ||  $prodListEnabled) && ($settings->get_accesskey() != "");
        $state = $this->plugin->get_state();

        $config = array(
            'enabled' => $enabled,
            'accessKey' => $settings->get_accesskey(),
            'searchEnabled' => $searchEnabled,
            'merchEnabled' => $merchEnabled,
            'prodListEnabled' => $prodListEnabled,
            "apiUrl" => $settings->get_api_url(),
            "searchSelector" => $settings->get_search_selector(),
            "isSearch" => is_search(),
            "product" => $state->get_product(),
            "isCategory" => is_product_category(),
            "categoryId" => $state->get_category_id(),
            "searchResultsDOMSelector" => $searchResultsDOMSelector,
            "customer" => $state->get_customer(),
            "islogout" => $state->is_logout()
        );

        $style="";
        if ($enabled && ((is_search() && $searchEnabled) || (is_product_category() && $prodListEnabled))) {
            $style = "<style type='text/css'>" . $searchResultsDOMSelector . " {display:none}</style>";
        }
        
        $script = '<script type="text/javascript">window.pureclarityConfig = ' . wp_json_encode( $config ) . '; </script>';

        echo $style . $script;
    }

    public function get_product() {
        if (is_product()){
            global $product;
            if (!empty($product)){
                if ( ! is_object( $product)) {
                    $product = wc_get_product( get_the_ID() );
                    if ( empty($product) )
                        return null;
                }
                if ($product->get_sku())
                    return $product;
            }
        }
        return null;
    }

}