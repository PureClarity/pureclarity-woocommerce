<?php

class PureClarity_Feed {

    private $plugin;
    private $settings;
    private $pageSize = 10;
    private $productTagsMap;

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
        }   
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

    public function http_post( $url, $body ) {
        
        $request = new WP_Http;
        $response = $request->request( $url, array( 'method' => 'POST', 'body' => $body ) );
        if (!empty($response->errors)){
            throw new Exception("Couldn't upload data to the PureClarity server: " . wp_json_encode($response->errors));
        }
        if ($response['body'] != "OK"){
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
                $items = $this->get_categories( $currentPage, $this->pageSize );
            break;
        }
        return $items;
    }

    public function get_products( $currentPage, $pageSize ) {
        $query = new WP_Query(
            array(
                'post_type'        => 'product',
                'posts_per_page'   => $pageSize,
                'post_status'      => 'any',
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
            if ($first) {
                $first = false;
            }
            else {
                $items .= ",";
            }
            $item = $this->parse_product($product);
            if (!empty($item))
                $items .= wp_json_encode($item);
        }

        return $items;
    }

    public function parse_product( $product ) {
        
        if ( $product->get_catalog_visibility() == "hidden")
            return null;

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
            "Id" => $product->get_id(),
            "Sku"   => $product->get_sku(),
            "Title" => $product->get_title(),
            "Description" => $product->get_description() . " " . $product->get_short_description(),
            "Categories" => $categoryIds,
            "InStock" => $product->get_stock_status() == "instock",
            "Link" => $productUrl,
            "Image" => $imageUrl,
            "ProductType" => $product->get_type()
        );

        $searchTags = array();
        foreach( $product->get_tag_ids() as $tagId) {
            if (array_key_exists($tagId, $this->productTagsMap)){
                $searchTags[] = $this->productTagsMap[$tagId];
            }
        }
        if (sizeof($searchTags) >0) {
            $json["SearchTags"] = $searchTags;
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
            $json['SalesPriceStartDate'] = (string) $product->get_date_on_sale_from("c");
        }

        if (!empty($product->get_date_on_sale_to())){
            $json['SalesPriceEndDate'] = (string) $product->get_date_on_sale_to("c");
        }

        if (!empty($product->get_weight())) {
            $json['Weight'] = [$product->get_weight()];
        }

        if (!empty($product->get_length())) {
            $json['Length'] = [$product->get_length()];
        }

        if (!empty($product->get_width())) {
            $json['Width'] = [$product->get_width()];
        }

        if (!empty($product->get_height())) {
            $json['Height'] = [$product->get_height()];
        }

        $prices = array();
        if ($product->get_regular_price()) {
            $prices[] = $product->get_regular_price() . ' ' . get_woocommerce_currency();
        }
        $json["Prices"] = $prices;

        $salesPrices = array();
        if ($product->get_price()) {
            $salesPrices[] = $product->get_price() . ' ' . get_woocommerce_currency();
        }
        $json["SalesPrices"] = $salesPrices;

        if ($product->get_type() == 'variable') {

            $json["AssociatedSkus"] = array();

            foreach($product->get_attributes() as $key => $attribute ) {
                $json[$key] = array();
            }

            $available_variations = $product->get_available_variations();
            foreach($available_variations as $variant) {
                
                $json["AssociatedSkus"][] = $variant['sku'];
                $price = $variant['display_price'] . ' ' . get_woocommerce_currency();
                $regularPrice = $variant['display_regular_price'] . ' ' . get_woocommerce_currency();

                if ($price != $regularPrice) {
                    if (!in_array($regularPrice, $json["Prices"])){
                        $json["Prices"][] = $regularPrice;
                    }
                    if (!in_array($price, $json["SalesPrices"])){
                        $json["SalesPrices"][] = $price;
                    }
                } else {
                    if (!in_array($price, $json["Prices"])){
                        $json["Prices"][] = $price;
                    }
                }

                foreach($product->get_attributes() as $key => $attribute ) {
                    if (!empty($variant['attributes']['attribute_' . $key])) {
                        $attributeValue = $variant['attributes']['attribute_' . $key];
                        if (!in_array($attributeValue, $json[$key])){
                            $json[$key][] = $attributeValue;
                        }
                    }
                }
            }   
        }
        
        return $json;
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

}