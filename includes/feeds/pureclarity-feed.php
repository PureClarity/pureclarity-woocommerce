<?php

class PureClarity_Feed {

    private $plugin;
    private $settings;
    private $productTagsMap;
    public $pageSize = 20;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();

        if( !class_exists( 'WP_Http' ) )
            include_once( ABSPATH . WPINC. '/class-http.php' );
    }

    public function get_total_pages( $type ) {
        $items = $this->get_total_items( $type );
        return (int) ceil( $items / $this->pageSize );
    }

    public function get_total_items( $type ) {

        switch($type){
            case "product":
                $query = new WP_Query(
                    array(
                        'post_type'        => $type,
                        'post_status'      => 'publish',
                        'suppress_filters' => true,
                    )
                );
                return (int) $query->found_posts;
            case "category":
                return 1;
            case "user":
                return $this->get_users_count();
            case "order":
                return $this->get_order_count();
        }   
        return 0;
    }

    public function start_feed( $type ) {
        $url = $this->settings->get_feed_baseurl() . "feed-create";
        $body;
        switch($type) {
            case "product":
                $this->loadProductTagsMap();
                $body = $this->get_request_body( $type, '{ "Version": 2, "Products": [' );
            break;
            case "category":
                $body = $this->get_request_body( $type, '{ "Version": 2, "Categories": [' );
            break;
            case "user":
                $body = $this->get_request_body( $type, '{ "Version": 2, "Users": [' );
            break;
            case "order":
                $body = $this->get_request_body( $type, "OrderId,UserId,Email,DateTimeStamp,ProdCode,Quantity,UnityPrice,LinePrice" );
            break;
        }
        $this->http_post( $url, $body );
    }

    public function send_data( $type, $data ){
        $url = $this->settings->get_feed_baseurl() . "feed-append";
        $body = $this->get_request_body( $type, $data );
        $this->http_post( $url, $body );
    }

    public function end_feed( $type ) {
        $url = $this->settings->get_feed_baseurl() . "feed-close";
        $body = $this->get_request_body( $type, "]}" );
        $this->http_post( $url, $body );
    }

    public function http_post( $url, $body, $checkOk = true ) {
        
        $request = new WP_Http;
        $response = $request->request( $url, array( 'method' => 'POST', 'body' => $body ) );
        if (!empty($response->errors)){
            throw new Exception("Couldn't upload data to the PureClarity server: " . wp_json_encode($response->errors));
        }
        if ($checkOk && $response['body'] != "OK"){
            throw new Exception("Couldn't upload data to the PureClarity server: " . $response['body']);
        }
    }

    public function get_request_body( $type, $data ) {
        $request = array(
            "accessKey"   => $this->settings->get_accesskey(),
            "secretKey"   => $this->settings->get_secretkey(),
            "feedName"    => $type
        );
        if ( ! empty($data) ){
            $request["payLoad"] = $data;
        }
        return $request;
    }

    public function build_items( $type, $currentPage ) {
        $items = array();
        switch($type) {
            case "product":
                $items = $this->get_products( $currentPage, $this->pageSize );
            break;
            case "category":
                $items = $this->get_categories();
            break;
            case "user":
                $items = $this->get_users( $currentPage, $this->pageSize );
            break;
            case "order":
                $items = $this->get_orders( $currentPage, $this->pageSize );
            break;
        }
        return $items;
    }

    public function get_products( $currentPage, $pageSize ) {
        $query = new WP_Query(
            array(
                'post_type'        => 'product',
                'posts_per_page'   => $pageSize,
                'post_status'      => 'publish',
                'orderby'          => 'ID',
                'order'            => 'ASC',
                'paged'            => $currentPage,
                'suppress_filters' => true,
            )
        );

        $first = false;
        if ($currentPage == 1 || $pageSize == 1)
            $first = true;

        $items = "";
        while ( $query->have_posts() ) {
            $query->the_post(); 
            global $product;
            $item = $this->parse_product($product);
            if (!empty($item)) {
                if ($first) {
                    $first = false;
                }
                else {
                    $items .= ",";
                }
                $items .= wp_json_encode($item);
            }
        }

        return $items;
    }

    public function parse_product( $product, $log_error = true ) {
        
        if ( $product->get_catalog_visibility() == "hidden"){
            if ($log_error) {
                error_log("PureClarity: Product " . $product->get_id() . " excluded from the feed. Reason: Catalog visibility = hidden.");
            }
            return null;
        }

        $productUrl = get_permalink( $product->get_id() );
        $productUrl = str_replace(array("https:", "http:"), "", $productUrl);

        $imageUrl = "";
        if (!empty($product->get_image_id())){
            $imageUrl = wp_get_attachment_url( $product->get_image_id() );
            $imageUrl = str_replace(array("https:", "http:"), "", $imageUrl);
        }

        $categoryIds = array();
        foreach($product->get_category_ids() as $categoryId){
            $categoryIds[] = (string) $categoryId;
        }

        $json = array(
            "Id" => (string) $product->get_id(),
            "Sku"   => $product->get_sku(),
            "Title" => $product->get_title(),
            "Description" => $product->get_description() . " " . $product->get_short_description(),
            "Categories" => $categoryIds,
            "InStock" => $product->get_stock_status() == "instock",
            "Link" => $productUrl,
            "Image" => $imageUrl,
            "ProductType" => $product->get_type()
        );

        if ($product->get_type() == 'external' && !empty( $product->get_button_text() )) {
            $json["ButtonText"] = $product->get_button_text();
        }

        $allImageUrls = array();
        foreach( $product->get_gallery_image_ids() as $attachmentId ) {
            $additionalImageUrl = wp_get_attachment_url( $attachmentId );
            $allImageUrls[] = str_replace(array("https:", "http:"), "", $additionalImageUrl);
        }
        if (sizeof($allImageUrls) >0) {
            $json["AllImages"] = $allImageUrls;
        }

        if (!empty($product->get_stock_quantity())){
            $json["StockQty"] = $product->get_stock_quantity();
        }

        if ($product->get_catalog_visibility() == "catalog") {
            $json['ExcludeFromSearch'] = true;
        }

        if ($product->get_catalog_visibility() == "search") {
            $json['ExcludeFromProductListing'] = true;
        }

        if (!empty($product->get_date_on_sale_from())){
            $json['SalePriceStartDate'] = (string) $product->get_date_on_sale_from("c");
        }

        if (!empty($product->get_date_on_sale_to())){
            $json['SalePriceEndDate'] = (string) $product->get_date_on_sale_to("c");
        }

        $this->set_search_tags( $json, $product );
        $this->set_basic_attributes( $json, $product );
        $this->set_product_price( $json, $product );
        $this->add_variant_info( $json, $product );
        $this->add_child_products( $json, $product);

        // Check is valid
        $error = array();
        if (!array_key_exists('Prices', $json) || (is_array($json['Prices']) && sizeof($json['Prices']) == 0)) {
            $error[] = 'Prices';
        }
        if (!array_key_exists('Sku', $json) || empty($json['Sku'])) {
            $error[] = 'Sku';
        }
        if (!array_key_exists('Title', $json) || empty($json['Title'])) {
            $error[] = 'Title';
        }
        
        if (sizeof($error) > 0) {
            if ($log_error) {
                error_log("PureClarity: Product " . $product->get_id() . " excluded from the feed. Reason: Missing required fields = " . implode(", ",  $error));
            }
            return null;
        }
        
        return $json;
    }

    private function add_to_array( $key, &$json, $value ) {
        if ( ! empty($value) ) {
            if (!array_key_exists($key, $json)) {
                $json[$key] = array();
            }
            if (!in_array($value, $json[$key])) {
                $json[$key][] = $value;
            }
        }
    }


    private function add_variant_info( &$json, &$product ) {
        
        if ($product->get_type() != 'variable') return;
        
        foreach($product->get_available_variations() as $variant) {
            
            $this->add_to_array("AssociatedSkus", $json, $variant['sku']);

            $price = $variant['display_price'] . ' ' . get_woocommerce_currency();
            $regularPrice = $variant['display_regular_price'] . ' ' . get_woocommerce_currency();

            if ($price != $regularPrice) {
                $this->add_to_array( "Prices", $json, $regularPrice );
                $this->add_to_array( "SalePrices", $json, $price );
            } else {
                $this->add_to_array( "Prices", $json, $price );
            }

            foreach($product->get_attributes() as $key => $attribute ) {
                $this->add_to_array($key, $json, $variant['attributes']['attribute_' . $key]);
            }
        }
    }

    
    private function add_child_products( &$json, &$product ) {

        if ($product->get_type() != 'grouped') return;

        foreach($product->get_children() as $childId) {
            $childProduct = wc_get_product($childId);
            if ( ! empty($childProduct) ) {

                if ( $childProduct->get_catalog_visibility() != "hidden" && $childProduct->get_status() == 'publish') {

                    $this->add_to_array("AssociatedIds", $json, $childProduct->get_id());
                    $this->add_to_array("AssociatedSkus", $json, $childProduct->get_sku());
                    $this->add_to_array("AssociatedTitles", $json, $childProduct->get_title());
                    $this->set_search_tags( $json, $childProduct );
                    $this->set_product_price( $json, $childProduct );
                    $this->add_variant_info( $json, $childProduct );
                    $this->add_child_products( $json, $childProduct );

                }
            }
        }
    }

    private function set_search_tags( &$json, &$product ) {
        
        foreach( $product->get_tag_ids() as $tagId) {
            if (array_key_exists($tagId, $this->productTagsMap)){
                $this->add_to_array( "SearchTags", $json, $this->productTagsMap[$tagId]);
            }
        }
    }

    private function set_product_price( &$json, &$product ) {
        
        if ($product->get_regular_price()) {
            $price = $product->get_regular_price() . ' ' . get_woocommerce_currency();
            $this->add_to_array("Prices", $json, $price);
        }

        if ($product->get_price() && $product->get_price() != $product->get_regular_price()) {
            $salesPrice = $product->get_price() . ' ' . get_woocommerce_currency();
            $this->add_to_array("SalePrices", $json, $salesPrice);
        }
    }

    private function set_basic_attributes( &$json, &$product ) {
        $this->add_to_array('Weight', $json, $product->get_weight());
        $this->add_to_array('Length', $json, $product->get_length());
        $this->add_to_array('Width', $json, $product->get_width());
        $this->add_to_array('Height', $json, $product->get_height());
    }



    public function loadProductTagsMap() {
        $this->productTagsMap = [];
        $terms = get_terms( 'product_tag' );
        foreach( $terms as $term ) {
            $this->productTagsMap[$term->term_id] = $term->name;
        }
    }

    public function get_categories() {
        $json = "";
        $categories = get_terms( 'product_cat', array( "hide_empty" => 0 ) );
        $first = true;
        foreach( $categories as $category ) {

            $url = get_term_link( $category->term_id, 'product_cat' );
            if (!empty($url)) {
                $url = str_replace(array("https:", "http:"), "", $url);
            }

            $data = array(
                "Id" => (string) $category->term_id,
                "DisplayName" => $category->name,
                "Link" => $url
            );

            if (!empty($category->parent) && $category->parent > 0)
                $data["ParentIds"] = [(string) $category->parent];

            $thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ); 
            if (!empty($thumbnail_id)){
                $imageUrl = wp_get_attachment_url( $thumbnail_id );
                if (!empty($imageUrl)){
                    $imageUrl = str_replace(array("https:", "http:"), "", $imageUrl);
                    $data['Image'] = $imageUrl;
                }   
            }
            
            if ($first) {
                $first = false;
            }
            else {
                $json .= ",";
            }
            $json .= wp_json_encode($data);
        }
        return $json;
    }

    public function get_users_count() {
        $args = array(
			'order'   => 'ASC',
            'orderby' => 'ID',
            'role' => 'Customer'
        );
        
        $users = new WP_User_Query( $args );
        return $users->get_total();
    }

    public function get_users( $currentPage, $pageSize ) {

        $offset = $pageSize * ( $currentPage - 1 );
        $args = array(
			'order'   => 'ASC',
            'orderby' => 'ID',
            'role' => 'Customer',
			'offset'  => $offset,
			'number'  => $pageSize,
        );
        
        $users = new WP_User_Query( $args );

        $first = false;
        if ($currentPage == 1 || $pageSize == 1)
            $first = true;

        $items = "";
        foreach($users->get_results() as $user) {
            $customer = new WC_Customer( $user->ID );
            if ($customer->get_id() > 0) {

                $data = array(
                    'UserId' => $customer->get_id(),
                    'Email' => $customer->get_email(),
                    'FirstName' => $customer->get_first_name(),
                    'LastName' => $customer->get_last_name()
                );

                $billing = $customer->get_billing();
                if (!empty($billing)) {
                    if (!empty($billing['city'])) {
                        $data['City'] = $billing['city'];
                    }
                    if (!empty($billing['state'])) {
                        $data['State'] = $billing['state'];
                    }
                    if (!empty($billing['country'])) {
                        $data['Country'] = $billing['country'];
                    }
                }

                if ($first) {
                    $first = false;
                }
                else {
                    $items .= ",";
                }

                $items .= wp_json_encode($data);
            }
        }
        return $items;
    }

    public function get_order_count() {
        $args = array(
            'status' => 'completed',
            'type' => 'shop_order',
            'date_created' => '>' . date('Y-m-d', strtotime("-6 month")),
            'paginate' => true
        );
        
        $results = wc_get_orders( $args );
        
        return $results->total;
    }

    public function get_orders( $currentPage, $pageSize ) {

        $args = array(
            'limit' => $pageSize,
            'paged' => $currentPage,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'completed',
            'type' => 'shop_order',
            'date_created' => '>' . date('Y-m-d', strtotime("-6 month"))
        );

        $dp = wc_get_price_decimals();
        
        $orders = new WC_Order_Query( $args );

        $items = "";
        foreach($orders->get_orders() as $order) {
            
            foreach ( $order->get_items() as $item_id => $item ) {
                $product      = $order->get_product_from_item( $item );
                $product_id   = 0;
                $variation_id = 0;
                $product_sku  = null;
                if ( is_object( $product ) ) {
                    $items .= PHP_EOL;
                    $product_id   = $item->get_product_id();
                    $variation_id = $item->get_variation_id();
                    $product_sku  = $product->get_sku();
                    $items .= $order->get_id() . ',';
                    $items .= $order->get_customer_id() . ',,';
                    $items .= (string) $order->get_date_created("c") . ',';
                    $items .= $product_id . ',';
                    $items .= $item['qty'] . ',';
                    $items .= wc_format_decimal( $order->get_item_total( $item, false, false ), $dp ) . ',';
                    $items .= wc_format_decimal( $order->get_line_total( $item, false, false ), $dp );
                }
            }            
        }

        return $items;
    }

    public function send_product_delta( $products, $deletes) {

        $request = array(
            'AppKey'            => $this->settings->get_accesskey(),
            'Secret'            => $this->settings->get_secretkey(),
            'Products'          => $products,
            'DeleteProducts'    => $deletes,
            'Format'            => 'pureclarity_json'
        );

        $url = $this->settings->get_delta_url();
        $this->http_post( $url, $request, false);

    }

}