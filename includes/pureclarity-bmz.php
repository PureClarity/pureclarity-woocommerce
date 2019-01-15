<?php

class PureClarity_Bmz {
    
    private $currentCategoryId;
    private $currentProduct;
    private $plugin;
    private $settings;
    private $state;

    public function __construct( &$plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->state = $plugin->get_state();
        add_shortcode( 'pureclarity-bmz', array( $this, 'pureclarity_render_bmz' ) );
        add_action( 'template_redirect', array( $this, 'render_bmzs' ), 10, 1 );
    }

    public function render_bmzs() {

        if ( ! $this->settings->is_merch_enabled() ) return;
        
        $this->currentProduct = $this->state->get_product();
        $this->currentCategoryId = $this->state->get_category_id();

        // Homepage and Order Received Page BMZs
        if ( is_front_page() && $this->settings->is_bmz_on_home_page() ) {
            add_filter( 'the_content', array( 
                    $this, 
                    'front_page' 
                ) 
            );
        }

        // Category Page BMZs (Only add if PC is not controlling prod lists, otherwise we'll it through JS)
        if ( 
                ( is_product_category() || is_shop() ) 
                && $this->settings->is_bmz_on_category_page() 
                && ! $this->settings->is_prod_enabled()  
        ) {
            add_action( 'woocommerce_before_main_content', array( 
                    $this, 
                    'cat_page_1'
                ), 10 );
            add_action( 'woocommerce_after_main_content', array( 
                    $this, 
                    'cat_page_2'
                ), 10 );
        }

        // Search Results BMZs (Only add if PC is not controlling search, otherwise we'll it through JS)
        if ( 
            is_search() 
            && $this->settings->is_bmz_on_search_page() 
            && ! $this->settings->is_search_enabled() 
        ) {
            add_action( 'woocommerce_before_main_content', array( 
                $this, 
                'search_page_1'
            ), 10 );
            add_action( 'woocommerce_after_main_content', array( 
                $this, 
                'search_page_2'
            ), 10 );
        }

        // Product Page BMZs
        if ( is_product() && $this->settings->is_bmz_on_product_page() ) {
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
            add_action( 'woocommerce_before_single_product', array( 
                $this, 
                'product_page_1'
            ), 10 );
            add_action( 'woocommerce_product_meta_end', array( 
                $this, 
                'product_page_2'
            ), 10 );
            add_action( 'woocommerce_after_single_product_summary', array( 
                $this, 
                'product_page_3'
            ), 10 );
            add_action( 'woocommerce_after_single_product', array( 
                $this, 
                'product_page_4'
            ), 10);
        }

        // Cart Page BMZs
        if ( is_cart() && $this->settings->is_bmz_on_basket_page() ) {
            add_action( 'woocommerce_before_cart', array( 
                $this, 
                'cart_page_1'
            ), 10 );
            add_action( 'woocommerce_after_cart', array( 
                $this, 
                'cart_page_2'
            ), 10 );
        }
        
        // Order Received Page BMZs
        if ( is_order_received_page() && $this->settings->is_bmz_on_checkout_page() ) {
            add_filter( 'the_content', array( 
                    $this, 
                    'order_received_page' 
                ) 
            );
        }
        
    }

    public function front_page( $content ) {
        return "[pureclarity-bmz id='HP-01' bottom='10']" . $content . "[pureclarity-bmz id='HP-02' top='10'][pureclarity-bmz id='HP-03' top='10'][pureclarity-bmz id='HP-04' top='10']";
    }

    public function order_received_page( $content ) {
        return "[pureclarity-bmz id='OC-01' bottom='10']" . $content . "[pureclarity-bmz id='OC-02' top='10']";
    }

    public function product_page_1() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PP-01',
                'bottom' => '10',
                'echo' => true
            )
        );
    }

    public function product_page_2() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PP-02',
                'top' => '10',
                'echo' => true
            )
        );
    }

    public function product_page_3() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PP-03', 
                'bottom' => '10', 
                'echo' => true
            )
        );
    }

    public function product_page_4() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PP-04', 
                'top' => '10', 
                'echo' => true
            )
        );
    }

    public function cat_page_1() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PL-01', 
                'bottom' => '10', 
                'echo' => true
            )
        );
    }

    public function cat_page_2() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'PL-02', 
                'top' => '10', 
                'bottom' => '10', 
                'echo' => true
            )
        );
    }

    public function search_page_1() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'SR-01',
                'top' => '10',
                'bottom' => '10',
                'echo' => true
            )
        );
    }

    public function search_page_2() {
        return $this->pureclarity_render_bmz( array(
                'id' => 'SR-02',
                'top' => '10',
                'bottom' => '10',
                'echo' => true
            )
        );
    }

    public function cart_page_1() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'BP-01',
                'bottom' => '10',
                'echo' => true
            )
        );
    }

    public function cart_page_2() {
        return $this->pureclarity_render_bmz( array( 
                'id' => 'BP-02',
                'top' => '10',
                'echo' => true
            )
        );
    }

    public function pureclarity_render_bmz( $atts, $content = null) {

        $arguments = shortcode_atts( 
            array( 
                'id' => null, 
                'top' => null, 
                'bottom' => null, 
                'echo' => false, 
                "class" => null 
            ), 
            $atts 
        );
        if ( 
            $this->settings->is_pureclarity_enabled() 
                && $this->settings->is_merch_enabled() 
                && ! empty( $arguments['id'] ) 
            ) {
            
            $html = ( ! empty($content) ? $content : "" );

            if ( $this->settings->is_bmz_debug_enabled() && $html == "" ){
                $html = "PURECLARITY BMZ: " . $arguments['id'];
            }

            $class = "pureclarity_bmz";
            if ( $this->settings->is_bmz_debug_enabled() ) {
                $class .= " pureclarity_debug";
            }
            $class .= " pureclarity_bmz_" . $arguments['id'];
            if ( ! empty( $arguments['class'] ) ) {
                $class .= ' ' . $arguments['class'];
            }

            $style = "";
            if ( ! empty( $arguments['top'] ) ) {
                $style .= "margin-top:" . $arguments['top'] . 'px;';
            }

            if ( ! empty( $arguments['bottom'] ) ) {
                $style .= "margin-bottom:" . $arguments['bottom'] . 'px;';
            }

            $data = "";
            if ( ! empty( $this->currentProduct ) ) {
                $data = "id:" . $this->currentProduct['id'];
            }
            elseif ( ! empty( $this->currentCategoryId ) ) {
                $data = "categoryid:" . $this->currentCategoryId;
            }

            $bmz = "<div class='" . $class . "' style='" . $style . "' data-pureclarity='bmz:" . $arguments['id'] . ";" . $data . "'>" . $html . "</div><div class='pureclarity_bmz_clearfix'></div>";
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