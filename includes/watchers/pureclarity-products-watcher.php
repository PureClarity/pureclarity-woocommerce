<?php

class PureClarity_Products_Watcher {

    private $plugin;
    private $settings;
    private $feed;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->feed = $plugin->get_feed();

        // Ensure we have session
        if (!session_id()) {
            session_start();
        }

        // Watch for product changes
        add_action( 'save_post', array( $this, 'save_item' ) );
        add_action( 'before_delete_post', array( $this, 'delete_item' ) );

        // Watch for category changes
        add_action( 'create_term', array( $this, 'save_term' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'save_term' ), 10, 3 );
        add_action( 'delete_term', array( $this, 'save_term' ), 10, 3 );

        // Watch user login and logout
        add_action('wp_login', array( $this, 'user_login'), 10, 2);
        add_action('wp_logout', array( $this, 'user_logout'), 10, 2);

        // Watch for orders
        if ( is_admin() ) {
            add_action( 'woocommerce_order_status_completed', array( $this, 'moto_order_placed'), 10, 1 );
        } else {
            add_action( 'woocommerce_order_status_processing', array( $this, 'order_placed'), 10, 1 );
        }

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
        if ($post->post_type == "product" && $this->settings->get_deltas_enabled()){
            if ($post->post_status == "publish") {
            
                $product = wc_get_product($id);    
                if ( ! empty($product) ){
                    // Add as delta
                    $this->feed->loadProductTagsMap();
                    $data = $this->feed->parse_product( $product );
                    if ( ! empty($data) ) {
                        $this->feed->send_product_delta( array($data), array());
                    }
                    else {
                        // Delete as delta
                        $this->feed->send_product_delta( array(), array($id));
                    }
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

    public function user_login($user_login, $user) {
        if ( ! empty($user) && in_array("customer", $user->roles )){
            $customer = new WC_Customer( $user->ID );
            if ($customer->get_id() > 0) {
                $_SESSION['pureclarity-customer'] = array(
                    'userid' => $customer->get_id(),
                    'email' => $customer->get_email(),
                    'firstname' => $customer->get_first_name(),
                    'lastname' => $customer->get_last_name()
                );
            }
        }
    }

    public function user_logout() {
        $_SESSION['pureclarity-logout'] = true;
    }

    public function moto_order_placed( $order_id ) {
        // Order is placed in the admin and is complete
    }

    public function order_placed( $order_id ) {
    
        $order = wc_get_order( $order_id );
        $customer = new WC_Customer( $order->get_user_id() );

        if ( ! empty( $order ) && ! empty( $customer ) ) {

            $dp = wc_get_price_decimals();

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
                        "qty" => $item['qty'],
                        "unitprice" => wc_format_decimal( $order->get_item_total( $item, false, false ), $dp )
                    );
                }
            }

            $data = array(
                "transaction" => $transaction,
                "items" => $orderItems
            );

            $_SESSION['pureclarity-order'] = $data;
        }
    }
}