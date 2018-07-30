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

        $categoryId = null;
        if (is_product_category()) {
            $category = get_queried_object();
            $categoryId = $category->term_id;
        }

        $config = array(
            'enabled' => $enabled,
            'accessKey' => $settings->get_accesskey(),
            'searchEnabled' => $searchEnabled,
            'merchEnabled' => $merchEnabled,
            'prodListEnabled' => $prodListEnabled,
            "apiUrl" => $settings->get_api_url(),
            "searchSelector" => $settings->get_search_selector(),
            "isSearch" => is_search(),
            "isCategory" => is_product_category(),
            "categoryId" => $categoryId,
            "searchResultsDOMSelector" => $searchResultsDOMSelector
        );
        
        $product = $this->get_product();
        if (!empty($product)){
            $config['product'] = array(
                'id' => $product->get_id(),
                'sku' => $product->get_sku()
            );
            wp_reset_postdata();
        }

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