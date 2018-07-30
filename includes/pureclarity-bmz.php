<?php

class PureClarity_Bmz {

    private $plugin;
    private $settings;
    private $currentProduct;
    private $currentCategoryId;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->currentProduct = $this->plugin->get_state()->get_product();
        $this->currentCategoryId = $this->plugin->get_state()->get_category_id();
        add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_bmz') );

        // Product Page BMZs
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        add_action( 'woocommerce_before_single_product', array( $this, 'product_page_before_product'), 10);
        add_action( 'woocommerce_product_meta_end', array( $this, 'product_page_product_summary'), 10);
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'product_page_after_product_summary'), 10);
        add_action( 'woocommerce_after_single_product', array( $this, 'product_page_after_product'), 10);
        
    }

    public function product_page_before_product() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-01', 'bottom' => '10'));
    }

    public function product_page_product_summary() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-02', 'top' => '10' ));
    }

    public function product_page_after_product_summary() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-03', 'bottom' => '10' ));
    }

    public function product_page_after_product() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-04', 'top' => '10' ));
    }

    public function pureclarity_render_bmz( $atts, $content = null) {

        $arguments = shortcode_atts( array( 'id' => null, 'top' => null, 'bottom' => null ), $atts );
        if ( $this->settings->get_merch_enabled() && ! empty( $arguments['id'] )) {
            
            $html = ! empty($content) ? $content : "";

            $class = $this->settings->get_bmz_debug_enabled() ? "pureclarity_bmz pureclarity_debug" : "pureclarity_bmz";
            if ( $this->settings->get_bmz_debug_enabled() && $html == "" ){
                $html = "PURECLARITY BMZ: " . $arguments['id'];
            }

            $style = "";
            if ( ! empty( $arguments['top'] )) {
                $style .= "margin-top:" . $arguments['top'] . 'px;';
            }

            if ( ! empty( $arguments['bottom'] )) {
                $style .= "margin-bottom:" . $arguments['bottom'] . 'px;';
            }

            $data = "";
            if ( ! empty($this->currentProduct) ) {
                $data = "id:".$this->currentProduct['id'];
            }
            elseif ( ! empty($this->currentCategoryId) ) {
                $data = "categoryid:".$this->currentCategoryId;
            }

            $bmz = "<div class='" . $class . "' style='" . $style . "' data-pureclarity='bmz:" . $arguments['id'] . ";" . $data . "'>" . $html . "</div>";
            
            echo $bmz;
        }

        return "";

    }

}