<?php

class PureClarity_Products_Watcher {

    private $plugin;
    private $settings;
    private $feed;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->feed = $plugin->get_feed();

        // Watch for product changes
        add_action( 'save_post', array( $this, 'save_item' ) );
        add_action( 'before_delete_post', array( $this, 'delete_item' ) );

        // Watch for category changes
        add_action( 'create_term', array( $this, 'save_term' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'save_term' ), 10, 3 );
        add_action( 'delete_term', array( $this, 'save_term' ), 10, 3 );

    }

    public function save_term( $term_id, $tt_id, $taxonomy ) {
        if ($taxonomy == 'product_cat') {
            $term = get_term($term_id);
            if ( ! empty($term) ) {
                // Add category as delta
            }
            else {
                // Delete as delta
            }
        }
    }

    public function save_item( $id ) {
        $post = get_post( $id );
        if ($post->post_type == "product"){
            if ($post->post_status == "publish") {
            
                $product = wc_get_product($id);    
                if ( ! empty($product) ){
                    // Add as delta
                    $this->feed->loadProductTagsMap();
                    $data = $this->feed->parse_product( $product );
                    $this->feed->send_product_delta( array($data), array());
                }
            }
            else{
                // Delete as delta
                $this->feed->send_product_delta( array(), array($id));
            }
        }
        
    }

    public function delete_item( $id ) {
        $post = get_post( $id );
        if ($post->post_type == "product"){
            $this->feed->send_product_delta( array(), array($id));
        }
    }


}