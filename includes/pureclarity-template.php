<?php

class PureClarity_Template {

    private $plugin;
    private $bmz;

    public function __construct( &$plugin ) {

        $this->plugin = $plugin;        
        $this->bmz = $plugin->get_bmz();
        if ( ! is_ajax() ) {
            add_filter( 'wp_head', array( $this, 'render_pureclarity_json' ) );
        }
    }

    public function render_pureclarity_json() {

        $settings = $this->plugin->get_settings();
        $searchEnabled = $settings->get_search_enabled();
        $merchEnabled = $settings->get_merch_enabled();
        $prodListEnabled = $settings->get_prod_enabled();
        $shopEnabled = $settings->get_shop_enabled_admin();
        $searchResultsDOMSelector = $settings->get_search_result_element();
        $prodlistDOMSelector = $settings->get_prodlist_result_element();
        $shopDOMSelector = $settings->get_shop_selector();
        $enabled = ($searchEnabled || $merchEnabled ||  $prodListEnabled) && ($settings->get_accesskey() != "") &&  $settings->get_pureclarity_enabled();
        $state = $this->plugin->get_state();

        $searchBmz1 = null;
        $searchBmz2 = null;
        if ($settings->add_bmz_searchpage()){
            $searchBmz1 = $this->bmz->pureclarity_render_bmz( array( "id" => "SR-01", "bottom" => "10" ));
            $searchBmz2 = $this->bmz->pureclarity_render_bmz( array( "id" => "SR-02", "top" => "10" ));
        }

        $prodListBmz1 = null;
        $prodListBmz2 = null;
        if ($settings->add_bmz_categorypage()){
            $prodListBmz1 = $this->bmz->pureclarity_render_bmz( array( "id" => "PL-01", "bottom" => "10" ));
            $prodListBmz2 = $this->bmz->pureclarity_render_bmz( array( "id" => "PL-02", "top" => "10" ));
        }



        
        $config = array(
            'enabled' => $enabled,
            "product" => $state->get_product(),
            "categoryId" => is_shop()?"*":$state->get_category_id(),
            'autocomplete' => array(
                "enabled" => $searchEnabled,
                "searchSelector" => $settings->get_search_selector(),
                "shopSelector" => $settings->get_shop_selector()
            ),
            'search' => array(
                'do' => $searchEnabled && is_search(),
                "domSelector" => $searchResultsDOMSelector,
                'bmz1' => $searchBmz1,
                'bmz2' => $searchBmz2
            ),
            'merch' => array(
                'enabled' => $merchEnabled
            ),
            'prodlist' => array(
                'do' => $prodListEnabled && is_product_category(),
                "domSelector" => $prodlistDOMSelector,
                'bmz1' => $prodListBmz1,
                'bmz2' => $prodListBmz2
            ),
            'shop' => array(
                'do' => $shopEnabled && is_shop() && !is_search(),
                'domSelector' => $shopDOMSelector,
                'bmz1' => $prodListBmz1,
                'bmz2' => $prodListBmz2
            ),
            "tracking" => array(
                'accessKey' => $settings->get_accesskey(),
                "apiUrl" => $settings->get_api_url(),
                "customer" => $state->get_customer(),
                "islogout" => $state->is_logout(),
                "order" => $state->get_order(),
                "cart" => $state->get_cart()
            )
        );

        $style="";
         if ($enabled && (
             (is_search() && $searchEnabled) ||
             (is_product_category()  && $prodListEnabled) ||
             (is_shop() && $shopEnabled)
             )) {
        //if ($enabled && ((is_search() && $searchEnabled) || ((is_product_category()) && $prodListEnabled))) {
            $style = "<style type='text/css'>" . $searchResultsDOMSelector . " {display:none}</style>";
        }
        
        $script = '<script type="text/javascript">window.pureclarityConfig = ' . wp_json_encode( $config ) . '; </script>';

        echo $style . $script;
    }


}