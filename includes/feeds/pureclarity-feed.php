<?php

class PureClarity_Feed {

    private $plugin;
    private $productTagsMap;
    private $settings;
    private $uniqueId;
    
    const PAGE_SIZE = 100;
    const GATEWAY_TIMEOUT = 504;

    public function __construct( &$plugin ) {
        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();

        if( ! class_exists( 'WP_Http' ) )
            include_once( ABSPATH . WPINC . '/class-http.php' );
    }

    public function get_total_pages( $type ) {
        $items = $this->get_total_items( $type );
        return (int) ceil( $items / self::PAGE_SIZE );
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
        $body = null;
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
                $body = $this->get_request_body( $type, "OrderId,UserId,Email,DateTimeStamp,ProdCode,Quantity,UnitPrice" );
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
        for ( $x = 0; $x <= 5; $x++ ) {
            $response = $request->request( $url, array( 
                    'method' => 'POST', 
                    'body' => $body 
                ) 
            );
            if( $response["response"] && $response["response"]["code"] != self::GATEWAY_TIMEOUT ) {
                break;
            }
            error_log( "PureClarity 504 (Gateway Timeout) Error, retrying. Error: Couldn't upload data to the PureClarity server: " . wp_json_encode( $response ) );
        }
        if ( ! empty( $response->errors ) ) {
            throw new Exception( "Couldn't upload data to the PureClarity server, response errors: " . wp_json_encode( $response->errors ) );
        }
        if ( $checkOk && $response['body'] != "OK" ) {
            throw new Exception( "Couldn't upload data to the PureClarity server, response: " . wp_json_encode( $response ) );
        }
    }

    public function get_request_body( $type, $data ) {
        $request = array(
            "accessKey"   => $this->settings->get_access_key(),
            "secretKey"   => $this->settings->get_secret_key(),
            "feedName"    => $type . "-" . $this->getUniqueId(),
        );
        if ( ! empty($data) ){
            $request["payLoad"] = $data;
        }
        return $request;
    }

    private function getUniqueId() {
        if( is_null( $this->uniqueId ) ){
            $this->uniqueId = uniqid();
        }
        return $this->uniqueId;
    }

    public function build_items( $type, $currentPage ) {
        $items = array();
        switch($type) {
            case "product":
                $items = $this->get_products( $currentPage, self::PAGE_SIZE );
                break;
            case "category":
                $items = $this->get_categories();
                break;
            case "user":
                $items = $this->get_users( $currentPage, self::PAGE_SIZE );
                break;
            case "order":
                $items = $this->get_orders( $currentPage, self::PAGE_SIZE );
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
        if ( $currentPage == 1 || $pageSize == 1 ) {
            $first = true;
        }

        $products = "";
        while ( $query->have_posts() ) { 
            $query->the_post();
            global $product;
            $product_data = $this->get_product_data( $product );
            if ( ! empty( $product_data ) ) {
                if ( $first ) {
                    $first = false;
                }
                else {
                    $products .= ",";
                }
                $products .= wp_json_encode( $product_data );
            }
        }

        return $products;
    }

    public function get_product_data( $product, $log_error = true ) {
        if ( $product->get_catalog_visibility() == "hidden" ) {
            if ( $log_error ) {
                error_log( "PureClarity: Product " . $product->get_id() . " excluded from the feed. Reason: Catalog visibility = hidden." );
            }
            return null;
        }

        $productUrl = $this->removeUrlProtocol( 
            get_permalink( $product->get_id() ) 
        );

        $imageUrl = "";
        if ( ! empty( $product->get_image_id() ) ) {
            $imageUrl = $this->removeUrlProtocol(
                wp_get_attachment_url( $product->get_image_id() )
            );
        }

        $categoryIds = array();
        foreach( $product->get_category_ids() as $categoryId ) {
            $categoryIds[] = (string) $categoryId;
        }

        $product_data = array(
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

        if ( $product->get_type() == 'external' && ! empty( $product->get_button_text() ) ) {
            $product_data["ButtonText"] = $product->get_button_text();
        }

        $allImageUrls = array();
        foreach( $product->get_gallery_image_ids() as $attachmentId ) {
            $allImageUrls[] = $this->removeUrlProtocol(
                wp_get_attachment_url( $attachmentId )
            );
        }
        if ( sizeof( $allImageUrls ) > 0 ) {
            $product_data["AllImages"] = $allImageUrls;
        }

        if ( ! empty( $product->get_stock_quantity() ) ) {
            $product_data["StockQty"] = $product->get_stock_quantity();
        }

        if ( $product->get_catalog_visibility() == "catalog" ) {
            $product_data['ExcludeFromSearch'] = true;
        }

        if ( $product->get_catalog_visibility() == "search" ) {
            $product_data['ExcludeFromProductListing'] = true;
        }

        if ( ! empty( $product->get_date_on_sale_from() ) ) {
            $product_data['SalePriceStartDate'] = (string) $product->get_date_on_sale_from( "c" );
        }

        if ( ! empty( $product->get_date_on_sale_to() ) ) {
            $product_data['SalePriceEndDate'] = (string) $product->get_date_on_sale_to( "c" );
        }

        $this->set_search_tags( $product_data, $product );
        $this->set_basic_attributes( $product_data, $product );
        $this->set_product_price( $product_data, $product );
        $this->add_variant_info( $product_data, $product );
        $this->add_child_products( $product_data, $product);

        // Check is valid
        $error = array();
        if ( ! array_key_exists( 'Prices', $product_data ) 
                || ( is_array( $product_data['Prices'] ) && sizeof( $product_data['Prices'] ) == 0 ) 
            ) {
                $error[] = 'Prices';
        }
        if ( ! array_key_exists( 'Sku', $product_data ) || empty( $product_data['Sku'] ) ) {
            $error[] = 'Sku';
        }
        if ( ! array_key_exists( 'Title', $product_data ) || empty( $product_data['Title'] ) ) {
            $error[] = 'Title';
        }
        
        if ( count( $error ) > 0 ) {
            if ( $log_error ) {
                error_log( "PureClarity: Product " . $product->get_id() . " excluded from the feed. Reason: Missing required fields = " . implode( ", ",  $error ) );
            }
            return null;
        }
        
        return $product_data;
    }

    private function add_to_array( $key, &$json, $value ) {
        if ( ! empty( $value ) ) {
            if ( ! array_key_exists( $key, $json ) ) {
                $json[$key] = array();
            }
            if ( ! in_array( $value, $json[$key] ) ) {
                $json[$key][] = $value;
            }
        }
    }

    private function add_variant_info( &$json, &$product ) {
        
        if ( $product->get_type() != 'variable' ) return;
        
        foreach( $product->get_available_variations() as $variant ) {
            
            $this->add_to_array( "AssociatedSkus", $json, $variant['sku'] );

            $price = $variant['display_price'] . ' ' . get_woocommerce_currency();
            $regularPrice = $variant['display_regular_price'] . ' ' . get_woocommerce_currency();

            if ( $price != $regularPrice ) {
                $this->add_to_array( "Prices", $json, $regularPrice );
                $this->add_to_array( "SalePrices", $json, $price );
            } 
            else {
                $this->add_to_array( "Prices", $json, $price );
            }

            foreach( $product->get_attributes() as $key => $attribute ) {
                $attribute = $variant['attributes']['attribute_' . $key];
                $this->add_to_array( $key, $json, $attribute );
            }
        }
    }
    
    private function add_child_products( &$json, &$product ) {

        if ( $product->get_type() != 'grouped' ) return;

        foreach( $product->get_children() as $childId ) {
            $childProduct = wc_get_product( $childId );
            if ( ! empty($childProduct) && $this->productIsVisible( $childProduct ) ) {
                $this->add_to_array( "AssociatedIds", $json, $childProduct->get_id() );
                $this->add_to_array( "AssociatedSkus", $json, $childProduct->get_sku() );
                $this->add_to_array( "AssociatedTitles", $json, $childProduct->get_title() );
                $this->set_search_tags( $json, $childProduct );
                $this->set_product_price( $json, $childProduct );
                $this->add_variant_info( $json, $childProduct );
                $this->add_child_products( $json, $childProduct );
            }
        }
    }

    private function productIsVisible( $product ) {
        return $product->get_catalog_visibility() != "hidden" && $product->get_status() == 'publish';
    }

    private function set_search_tags( &$json, &$product ) {
        foreach( $product->get_tag_ids() as $tagId ) {
            if ( array_key_exists( $tagId, $this->productTagsMap ) ) {
                $this->add_to_array( "SearchTags", $json, $this->productTagsMap[$tagId] );
            }
        }
    }

    private function set_product_price( &$json, &$product ) {
        if ( $product->get_regular_price() ) {
            $price = $product->get_regular_price() . ' ' . get_woocommerce_currency();
            $this->add_to_array( "Prices", $json, $price );
        }

        if ( $product->is_on_sale() || $this->product_has_future_sale( $product ) ) {
            if( ! empty( $product->get_sale_price() ) ) {
                $salesPrice = $product->get_sale_price() . ' ' . get_woocommerce_currency();
                $this->add_to_array( "SalePrices", $json, $salesPrice );
            }
        }
    }

    private function product_has_future_sale( $product ) {
        $saleDate = $product->get_date_on_sale_from();
        if( ! empty( $saleDate ) ) {
            return ( $product->get_date_on_sale_from( $context )->getTimestamp() > current_time( 'timestamp', true ) );
        }
        return false;
    }

    private function set_basic_attributes( &$json, &$product ) {
        $this->add_to_array( 'Weight', $json, $product->get_weight() );
        $this->add_to_array( 'Length', $json, $product->get_length() );
        $this->add_to_array( 'Width', $json, $product->get_width() );
        $this->add_to_array( 'Height', $json, $product->get_height() );
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
        $categories = get_terms( 'product_cat', array( 
                "hide_empty" => 0 
            ) 
        );

        //add into data the new root category!
        $data = array(
            "Id" => "-1",
            "DisplayName" => "Shop",
            "Link" => "/?post_type=product",
            "ExcludeFromRecommenders" => true,
            "Description" => "All products on the site"
        );
        $json .= wp_json_encode( $data );

        foreach( $categories as $category ) {
            $url = $this->removeUrlProtocol(
                get_term_link( $category->term_id, 'product_cat' )
            );

            $data = array(
                "Id" => (string) $category->term_id,
                "DisplayName" => $category->name,
                "Link" => $url
            );

            //If category is a root category (has no parent), add to new Shop category so that we can search in Shop for all products
            $data["ParentIds"] = [ ( ! empty( $category->parent ) && $category->parent > 0 ) ? (string) $category->parent : "-1" ];

            $thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ); 
            if ( ! empty( $thumbnail_id ) ) {
                $imageUrl = wp_get_attachment_url( $thumbnail_id );
                if ( ! empty( $imageUrl ) ) {
                    $data['Image'] = $this->removeUrlProtocol( $imageUrl );
                }   
            }
            $json .= "," . wp_json_encode( $data );
        }
        return $json;
    }

    public function get_users_count() {
        $args = array(
			'order'   => 'ASC',
            'orderby' => 'ID'
        );
        
        $users = new WP_User_Query( $args );
        return $users->get_total();
    }

    public function get_users( $currentPage, $pageSize ) {

        $args = array(
			'order'   => 'ASC',
            'orderby' => 'ID',
			'offset'  => $pageSize * ( $currentPage - 1 ),
			'number'  => $pageSize,
        );
        
        $users = new WP_User_Query( $args );
        $first = ( $currentPage == 1 || $pageSize == 1 );
        $items = "";
        foreach( $users->get_results() as $user ) {

            $data = $this->parse_user( $user->ID );

            if ( ! empty ($data) ) {

                if ( $first ) {
                    $first = false;
                }
                else {
                    $items .= ",";
                }

                $items .= wp_json_encode( $data );
            }
        }
        return $items;
    }

    public function get_roles( $userId ) {
        $user_roles = get_user_meta( $userId, 'wp_capabilities' );
        return array_keys( $user_roles[0] );
    }

    public function parse_user( $userId ) {
        $customer = new WC_Customer( $userId );

        if ( ! empty( $customer ) && $customer->get_id() > 0 ) {
            $data = array(
                'UserId' => $customer->get_id(),
                'Email' => $customer->get_email(),
                'FirstName' => $customer->get_first_name(),
                'LastName' => $customer->get_last_name(),
                'Roles' => $this->get_roles( $userId )
            );

            $billing = $customer->get_billing();
            if ( ! empty($billing) ) {
                if ( ! empty( $billing['city'] ) ) {
                    $data['City'] = $billing['city'];
                }
                if ( ! empty( $billing['state'] ) ) {
                    $data['State'] = $billing['state'];
                }
                if ( ! empty( $billing['country'] ) ) {
                    $data['Country'] = $billing['country'];
                }
            }
            
            return $data;
        }
        return null;
    }

    public function get_order_count() {
        $args = array(
            'status' => 'completed',
            'type' => 'shop_order',
            'date_created' => '>' . date( 'Y-m-d', strtotime( "-12 month" ) ),
            'paginate' => true
        );
        
        $results = wc_get_orders( $args );
        
        return $results->total;
    }

    public function get_orders( $currentPage, $pageSize ) {
        $args = array(
            'limit' => $pageSize,
            'offset' => $pageSize * ( $currentPage - 1 ),
            'orderby' => 'date_created',
            'order' => 'DESC',
            'status' => 'completed',
            'type' => 'shop_order',
            'date_created' => '>' . date( 'Y-m-d', strtotime( "-12 month" ) )
        );

        $orders = new WC_Order_Query( $args );

        $items = "";
        foreach( $orders->get_orders() as $order ) {
            foreach ( $order->get_items() as $item_id => $item ) {
                $product = $order->get_product_from_item( $item );
                if ( is_object( $product ) ) {
                    $items .= PHP_EOL;
                    $items .= $order->get_id() . ',';
                    $items .= $order->get_customer_id() . ',,';
                    $items .= (string) $order->get_date_created("c") . ',';
                    $items .= $item->get_product_id() . ',';
                    $items .= $item['qty'] . ',';
                    $items .= wc_format_decimal( 
                        $order->get_item_total( $item, false, false )
                    );
                }
            }            
        }

        return $items;
    }

    public function send_product_delta( $products, $productsToDelete ) {
        $request = array(
            'AppKey'            => $this->settings->get_access_key(),
            'Secret'            => $this->settings->get_secret_key(),
            'Products'          => $products,
            'DeleteProducts'    => $productsToDelete,
            'Format'            => 'pureclarity_json'
        );

        $this->http_post( $this->settings->get_delta_url(), $request, false );

    }

    public function send_user_delta( $users, $deletes ) {

        $request = array(
            'AppKey'            => $this->settings->get_access_key(),
            'Secret'            => $this->settings->get_secret_key(),
            'Users'             => $users,
            'DeleteUsers'       => $deletes,
            'Format'            => 'pureclarity_json'
        );

        $this->http_post( $this->settings->get_delta_url(), $request, false );

    }

    public function removeUrlProtocol( $url ) {
        return empty( $url ) ? $url : str_replace(
                array(
                        "https:", 
                        "http:"
                    ), 
                "", 
                $url
            );
    }

}
