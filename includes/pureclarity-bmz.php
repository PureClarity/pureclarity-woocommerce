<?php

class PureClarity_Bmz {

    private $plugin;
    private $settings;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
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
            if (! empty( $arguments['top'] )) {
                $style .= "margin-top:" . $arguments['top'] . 'px;';
            }

            if (! empty( $arguments['bottom'] )) {
                $style .= "margin-bottom:" . $arguments['bottom'] . 'px;';
            }

            $bmz = "<div class='" . $class . "' style='" . $style . "' data-pureclarity='bmz:" . $arguments['id'] . ";'>" . $html . "</div>";
            return $bmz;
        }

        return "";

    }

}