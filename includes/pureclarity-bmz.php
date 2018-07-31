<?php

class PureClarity_Bmz {

    private $plugin;
    private $settings;
    private $state;
    private $currentProduct;
    private $currentCategoryId;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->state = $plugin->get_state();
        add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_bmz') );
        add_action( 'template_redirect', array( $this, 'render_bmzs' ), 10, 1 );
    }

    public function render_bmzs() {

        if (!$this->settings->get_merch_enabled()) return;
        
        $this->currentProduct = $this->state->get_product();
        $this->currentCategoryId = $this->state->get_category_id();

        // Product Page BMZs
        if ( !empty($this->currentProduct) ) {
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
            add_action( 'woocommerce_before_single_product', array( $this, 'product_page_1'), 10);
            add_action( 'woocommerce_product_meta_end', array( $this, 'product_page_2'), 10);
            add_action( 'woocommerce_after_single_product_summary', array( $this, 'product_page_3'), 10);
            add_action( 'woocommerce_after_single_product', array( $this, 'product_page_4'), 10);
        }

        // Category Page BMZs
        if ( !empty($this->currentCategoryId) ) {
            add_action( 'woocommerce_before_main_content', array( $this, 'cat_page_1'), 10);
            add_action( 'woocommerce_after_main_content', array( $this, 'cat_page_2'), 10);
        }

        // Cart Page BMZs
        if ( is_cart() ) {
            add_action( 'woocommerce_before_cart', array( $this, 'cart_page_1'), 10);
            add_action( 'woocommerce_after_cart', array( $this, 'cart_page_2'), 10);
        }

        // Homepage and Order Received Page BMZs
        add_filter( 'the_content', array( $this, 'content_page' ) );
        
    }

    public function content_page( $content ) {

        if (is_front_page()) {
            return "[pureclarity-bmz id='HP-01' bottom='10']" . $content . "[pureclarity-bmz id='HP-02' top='10']";
        }

        if (is_order_received_page()) {
            return "[pureclarity-bmz id='OC-01' bottom='10']" . $content . "[pureclarity-bmz id='OC-02' top='10']";
        }

        return $content;
    }
    

    public function product_page_1() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-01', 'bottom' => '10', 'echo' => true ));
    }

    public function product_page_2() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-02', 'top' => '10', 'echo' => true ));
    }

    public function product_page_3() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-03', 'bottom' => '10', 'echo' => true ));
    }

    public function product_page_4() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PP-04', 'top' => '10', 'echo' => true ));
    }

    public function cat_page_1() {
        return $this->pureclarity_render_bmz(array( 'id' => 'PL-01', 'bottom' => '10', 'echo' => true ));
    }

    public function cat_page_2() {
        // return $this->pureclarity_render_bmz(array( 'id' => 'PL-02', 'top' => '10', 'echo' => true ));
    }

    public function cart_page_1() {
        return $this->pureclarity_render_bmz(array( 'id' => 'BP-01', 'bottom' => '10', 'echo' => true ));
    }

    public function cart_page_2() {
        return $this->pureclarity_render_bmz(array( 'id' => 'BP-02', 'top' => '10', 'echo' => true ));
    }


    public function pureclarity_render_bmz( $atts, $content = null) {

        $arguments = shortcode_atts( array( 'id' => null, 'top' => null, 'bottom' => null, 'echo' => false ), $atts );
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
            if ( $arguments['echo'] == true ) {
                echo $bmz;
            }
            else {
                return $bmz;
            }
        }

        return "";

    }

}