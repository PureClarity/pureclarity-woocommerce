<?php
    
class PureClarity_State {
    
    private $plugin;
    public $customer;
    public $islogout = false;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        if (!session_id()) {
            session_start();
        }
        add_action('wp_login', array( $this, 'user_login'), 10, 2);
        add_action('wp_logout', array( $this, 'user_logout'), 10, 2);
    }

    public function user_login($user_login, $user) {
        if ( ! empty($user) && in_array("customer", $user->roles )){
            $customer = new WC_Customer( $user->ID );
            if ($customer->get_id() > 0) {
                $this->customer = array(
                    'userid' => $customer->get_id(),
                    'email' => $customer->get_email(),
                    'firstname' => $customer->get_first_name(),
                    'lastname' => $customer->get_last_name()
                );
                $_SESSION['pureclarity-customer'] = $this->customer;
            }
        }
    }

    public function get_customer() {
        
        if ( ! empty($this->customer) ){
            return $this->customer;
        }

        if ( isset($_SESSION['pureclarity-customer']) ){
            $this->customer = $_SESSION['pureclarity-customer'];
            $_SESSION['pureclarity-customer'] = null;
            return $this->customer;
        }
        
        return null;
    }

    public function user_logout() {
        $this->islogout = true;
        $_SESSION['pureclarity-logout'] = true;
    }

    public function is_logout() {
        if ( $this->islogout) {
            return true;
        }
        if ( isset($_SESSION['pureclarity-logout']) ){
            $this->islogout = $_SESSION['pureclarity-logout'];
            $_SESSION['pureclarity-logout'] = null;
            return $this->islogout;
        }
        return false;
    }

    public function get_product() {
        $product = $this->get_wc_product();
        if (!empty($product)){
            $data = array(
                'id' => $product->get_id(),
                'sku' => $product->get_sku()
            );
            wp_reset_postdata();
            return $data;
        }
        return null;
    }


    public function get_wc_product() {
        if (is_product()){
            global $product;
            if (!empty($product)){
                if ( ! is_object( $product)) {
                    $product = wc_get_product( get_the_ID() );
                    if ( empty($product) )
                        return null;
                }
                if ($product->get_sku())
                    return $product;
            }
        }
        return null;
    }


    public function get_category_id() {
        $categoryId = null;
        if (is_product_category()) {
            $category = get_queried_object();
            return $category->term_id;
        }
        return null;
    }

}