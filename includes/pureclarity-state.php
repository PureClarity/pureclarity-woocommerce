<?php

    
class PureClarity_State {
    
    private $plugin;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
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