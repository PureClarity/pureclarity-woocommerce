<?php

class PureClarity_Feed {

    private $plugin;
    private $settings;
    private $pageSize = 10;

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
        $query = new WP_Query(
			array(
				'post_type'        => $type,
				'post_status'      => 'publish',
				'suppress_filters' => true,
			)
		);
		return (int) $query->found_posts;
    }

    public function start_feed( $type ) {
        $url = $this->settings->get_feed_baseurl() . "feed-create";
        $body;
        switch($type) {
            case "product":
                $body = $this->get_request_body( $type, '{ "Version": 2, "Products": [' );
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
            $items .= wp_json_encode($this->parse_product($product));
        }

        return $items;
    }

    public function parse_product( $product ) {

        error_log($product);

        $productUrl = get_permalink( $product->get_id() );
        $productUrl = str_replace(array("https:", "http:"), "", $productUrl);

        $imageUrl = "";
        if (!empty($product->get_image_id())){
            $imageUrl = wp_get_attachment_url( $product->get_image_id() );
            $imageUrl = str_replace(array("https:", "http:"), "", $imageUrl);
        }

	    $allImageUrls = array();
        foreach( $product->get_gallery_image_ids() as $attachmentId ) {
            $additionalImageUrl = wp_get_attachment_url( $attachmentId );
            $allImageUrls[] = str_replace(array("https:", "http:"), "", $additionalImageUrl);
	    }

        $json = array(
            "Sku"   => $product->get_sku(),
            "Title" => $product->get_title(),
            "Description" => $product->get_description() . " " . $product->get_short_description(),
            "Categories" => $product->get_category_ids(),
            "InStock" => $product->get_stock_status() == "instock",
            "Link" => $productUrl,
            "Prices" => [$product->get_regular_price() . ' ' . get_woocommerce_currency()],
            "SalesPrices" => [$product->get_price() . ' ' . get_woocommerce_currency()],
            "Image" => $imageUrl,
            "AllImages" => $allImageUrls
        );
        error_log(wp_json_encode($json));
        return $json;

    }

}