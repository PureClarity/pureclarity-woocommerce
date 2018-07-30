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
            return $bmz;
        }

        return "";

    }

}