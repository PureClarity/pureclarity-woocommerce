<?php

class PureClarity_Products_Watcher {

    private $feed;
    private $plugin;
    private $settings;
    private $state;

    public function __construct( &$plugin ) {
        $this->plugin = $plugin;
        $this->feed = $plugin->get_feed();
        $this->settings = $plugin->get_settings();
        $this->state = $plugin->get_state();

        if ( ! $this->settings->is_pureclarity_enabled() ) {
            return;
        }

        if ( ! session_id() ) {
            session_start();
        }

        if ( $this->settings->is_deltas_enabled() ){
            $this->register_product_listeners();
            $this->register_category_listeners();
            $this->register_user_listeners();
        }
        $this->register_user_session_listeners();
        $this->register_cart_listeners();
        $this->register_order_listeners();

    }

    /**
     * Registers callback functions when product changes occur.
     */
    private function register_product_listeners() {
        
        // new / updated or un-trashed products
        add_action( 'woocommerce_new_product', array( $this, 'save_product_via_deltas' ), 10, 3 );
        add_action( 'woocommerce_update_product', array( $this, 'save_product_via_deltas' ), 10, 3 );
        add_action( 'untrashed_post', array( $this, 'save_product_via_deltas' ) );
        
        // trashed or deleted products
        add_action( 'trashed_post', array( $this, 'delete_item' ) );
        add_action( 'woocommerce_delete_product', array( $this, 'delete_item' ) , 10, 3 );
        add_action( 'woocommerce_trash_product', array( $this, 'delete_item' ) );
    }

    /**
     * Registers callback functions when category changes occur.
     */
    private function register_category_listeners() {
        add_action( 'create_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
        add_action( 'delete_term', array( $this, 'add_category_feed_to_deltas' ), 10, 3 );
    }

    /**
     * Registers callback functions when changes are made to user records.
     */
    private function register_user_listeners() {
        add_action( 'profile_update', array( $this, 'save_user_via_deltas' ) );
        add_action( 'user_register', array( $this, 'save_user_via_deltas' ) );
        add_action( 'delete_user', array( $this, 'delete_user_via_deltas' ) );
    }

    /**
     * Registers callback functions when users log in or out.
     */
    private function register_user_session_listeners() {
        add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );
        add_action( 'wp_logout', array( $this, 'user_logout' ), 10, 2 );
    }

   /**
     * Registers callback functions when cart changes occur.
     */
    private function register_cart_listeners() {
        add_action( 'woocommerce_add_to_cart', array( $this, 'set_cart' ), 10, 1 );
        add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'set_cart' ), 10, 1 );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'set_cart' ), 10, 1 );
    }

    private function register_order_listeners() {
        if ( is_admin() ) {
            add_action( 'woocommerce_order_status_completed', array( $this, 'moto_order_placed'), 10, 1 );
        } 
        else {
            add_action( 'woocommerce_order_status_processing', array( $this, 'order_placed'), 10, 1 );
            add_action( 'woocommerce_order_status_on-hold', array( $this, 'order_placed'), 10, 1 );
            add_action( 'woocommerce_order_status_pending', array( $this, 'order_placed'), 10, 1 );
        }
    }

    public function add_category_feed_to_deltas( $term_id, $tt_id, $taxonomy ) {
        if ( $taxonomy == 'product_cat' ) {
            $this->settings->set_category_feed_required();
        }
    }

    public function save_user_via_deltas( $user_id ) {
        $data = $this->feed->parse_user( $user_id );
        if ( ! empty($data) ) {
            $json = json_encode( $data );
            $this->settings->add_user_delta( $user_id, strlen($json) );
            update_user_meta( $user_id, 'pc_delta', $json );
        }
    }

    public function delete_user_via_deltas( $user_id ) {
        $this->settings->add_user_delta_delete( $user_id );
    }

    public function save_product_via_deltas( $id  ) {
        
        if ( ! current_user_can( 'edit_product', $id ) ) {
            return $id;
        }
        
        $product = wc_get_product($id);  
        $post = get_post( $id );
        
        if ( $post->post_status == "publish" ){
            // Add as delta
            $this->feed->loadProductTagsMap();
            $data = $this->feed->get_product_data( $product );
            
            if ( ! empty( $data ) ) {
                $json = json_encode( $data );
                $this->settings->add_product_delta( $id, strlen( $json ) );
                update_post_meta( $id, 'pc_delta', $json );
            } else {
                // Delete as delta
                delete_post_meta($id, 'pc_delta');
                $this->settings->add_product_delta_delete( $id );
            }
        } elseif ($post->post_status != "importing") {
            // Delete as delta
            delete_post_meta( $id, 'pc_delta' );
            $this->settings->add_product_delta_delete( $id );
        }
    }

    public function delete_item( $id ) {
        $post = get_post( $id );
        if ( $post->post_type == "product" && $post->post_status === 'trash' ){
            delete_post_meta( $id, 'pc_delta' );
            $this->settings->add_product_delta_delete( $id, true );
        }
    }

    public function user_login( $user_login, $user ) {
        if ( ! empty( $user ) ){
            $this->state->set_customer( $user->ID );
        }
    }

    public function user_logout() {
        $_SESSION['pureclarity-logout'] = true;
        $this->state->clear_customer();
    }

    public function moto_order_placed( $order_id ) {
        // Order is placed in the admin and is complete
    }

    public function order_placed( $order_id ) {
    
        $order = wc_get_order( $order_id );
        $customer = new WC_Customer( $order->get_user_id() );
        error_log( json_encode( $order ) );

        if ( ! empty( $order ) && ! empty( $customer ) ) {

            $transaction = array(
                "orderid" => $order->get_id(),
                "firstname" => $customer->get_first_name(),
                "lastname" => $customer->get_last_name(),
                "userid" => $order->get_user_id(),
                "ordertotal" => $order->get_total()
            );

            $orderItems = array();
            foreach ( $order->get_items() as $item_id => $item ) {
                $product = $order->get_product_from_item( $item );
                if ( is_object( $product ) ) {
                    $orderItems[] = array(
                        "orderid" => $order->get_id(),
                        "id" => $item->get_product_id(),
                        "qty" => $item[ 'qty' ],
                        "unitprice" => wc_format_decimal( $order->get_item_total( $item, false, false ) )
                    );
                }
            }

            $data = $transaction;
            $data['items'] = $orderItems;

            $_SESSION['pureclarity-order'] = $data;
        }
    }

    public function set_cart( $update ) {
        try {
            $this->state->set_cart();
        } catch ( \Exception $exception ) {
            error_log( "PureClarity: Can't build cart changes tracking event: " . $exception->getMessage() );
        }
    }
}