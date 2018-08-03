<?php

class PureClarity_Products_Watcher {

    private $plugin;
    private $settings;
    private $feed;
    private $state;

    public function __construct( &$plugin ) {


        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->feed = $plugin->get_feed();
        $this->state = $plugin->get_state();

        if ( ! $this->settings->get_pureclarity_enabled()) {
            return;
        }

        // Ensure we have session
        if (!session_id()) {
            session_start();
        }

        if ($this->settings->get_deltas_enabled()){
            // Watch for product changes
            add_action( 'save_post_product', array( $this, 'save_product' ), 10, 3 );
            add_action( 'added_post_meta',  array( $this, 'save_meta_item' ),  10, 4 );
            add_action( 'updated_post_meta',  array( $this, 'save_meta_item' ),  10, 4 );
            add_action( 'before_delete_post', array( $this, 'delete_item' ) );

            // Watch for category changes
            add_action( 'create_term', array( $this, 'save_term' ), 10, 3 );
            add_action( 'edit_term', array( $this, 'save_term' ), 10, 3 );
            add_action( 'delete_term', array( $this, 'save_term' ), 10, 3 );

            // Watch for User updates
            add_action( 'profile_update', array( $this, 'save_user' ) );
            add_action( 'user_register', array( $this, 'save_user' ) );
            add_action( 'delete_user', array( $this, 'delete_user' ) );
        }

        // Watch user login and logout
        add_action('wp_login', array( $this, 'user_login'), 10, 2);
        add_action('wp_logout', array( $this, 'user_logout'), 10, 2);

        // Watch cart updates
        add_action('woocommerce_add_to_cart', array( $this, 'set_cart'), 10, 1);
        add_action('woocommerce_update_cart_action_cart_updated', array( $this, 'set_cart'), 10, 1);
        add_action('woocommerce_cart_item_removed', array( $this, 'set_cart'), 10, 1);

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
                $this->settings->set_category_feed_required();
            }
            else {
                // Delete as delta
                $this->settings->set_category_feed_required();
            }
        }
    }

    public function save_user( $user_id ) {
        $data = $this->feed->parse_user( $user_id );
        if ( ! empty($data) ) {
            $json = json_encode($data);
            $this->settings->add_user_delta( $user_id, strlen($json) );
            update_user_meta($user_id, 'pc_delta', $json);
        }
    }

    public function delete_user( $user_id ) {
        $this->settings->add_user_delta_delete( $user_id );
    }

    public function save_meta_item( $meta_id, $post_id, $meta_key, $meta_value ) {
        if ( $meta_key != '_edit_lock' && $meta_key != 'pc_delta' ) {
            $post = get_post( $post_id );
            if ( ! empty($post) ) {
                $this->save_product( $post_id, $post, true );
            }
        }
    }

    public function save_product( $id, $post, $update  ) {

        if ($post->post_type == "product"){

            if ( ! current_user_can( 'edit_product', $id ) )
                return $id;
            
            if ($post->post_status == "publish") {
            
                $product = wc_get_product($id);    
                if ( ! empty($product) ){
                    
                    // Add as delta
                    $this->feed->loadProductTagsMap();
                    $data = $this->feed->parse_product( $product );
                    if ( ! empty($data) ) {
                        $json = json_encode($data);
                        $this->settings->add_prod_delta($id, strlen($json));
                        update_post_meta($id, 'pc_delta', $json);
                    }
                    else {
                        // Delete as delta
                        delete_post_meta($id, 'pc_delta');
                        $this->settings->add_prod_delta_delete($id);
                    }
                }
            }
            elseif ($post->post_status != "importing") {
                // Delete as delta
                delete_post_meta($id, 'pc_delta');
                $this->settings->add_prod_delta_delete($id);
            }
        }
        
    }

    public function delete_item( $id ) {
        $post = get_post( $id );
        if ($post->post_type == "product" && $post->post_status != "trash"){
            delete_post_meta($id, 'pc_delta');
            $this->settings->add_prod_delta_delete($id, true);
        }
    }

    public function user_login($user_login, $user) {
        if ( ! empty($user) ){
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

    public function set_cart( $update ) {
        
        try {

            $this->state->set_cart();

        } catch ( \Exception $exception ) {
            error_log("PureClarity: Can't build cart changes tracking event: " . $exception->getMessage() );
        }
    }
}