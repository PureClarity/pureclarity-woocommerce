<?php
    
class PureClarity_State {
    
    private $plugin;
    public $customer;
    public $islogout = false;
    public $order;
    public $currentProduct;
    public $currentCategoryId;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        if (!session_id()) {
            session_start();
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
        if (! empty($this->currentProduct)){
            return $this->currentProduct;
        }

        $product = $this->get_wc_product();
        if (!empty($product)){
            $data = array(
                'id' => $product->get_id(),
                'sku' => $product->get_sku()
            );
            wp_reset_postdata();
            $this->currentProduct = $data;
            return $this->currentProduct;
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
                if ($product->get_sku()){
                    return $product;
                }
            }
        }
        return null;
    }

    public function get_category_id() {
        if (! empty($this->currentCategoryId)){
            return $this->currentCategoryId;
        }
        $categoryId = null;
        if (is_product_category()) {
            $category = get_queried_object();
            $this->currentCategoryId = $category->term_id;
            return $this->currentCategoryId;
        }
        return null;
    }

    public function get_order() {
        if ( ! empty($this->order) ) {
            return $this->order;
        }
        if ( isset($_SESSION['pureclarity-order']) ){
            $this->order = $_SESSION['pureclarity-order'];
            $_SESSION['pureclarity-order'] = null;
            return $this->order;
        }
        return null;
    }

}