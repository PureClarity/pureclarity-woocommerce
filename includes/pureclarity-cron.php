<?php 


class PureClarity_Cron {

    private $plugin;
    private $setting;
    private $feed;

    public function __construct( $plugin ) {

        $this->plugin = $plugin;
        $this->settings = $plugin->get_settings();
        $this->feed = $plugin->get_feed();

        if ($this->settings->get_deltas_enabled()) {
            $this->process_products();
            $this->process_categories();
            //$this->process_users();
        }

        
    }

    public function process_products() {

        try {

            if ( ! $this->settings->get_prodfeed_run() ) {
                return;
            }

            $prodDeltas = $this->settings->get_prod_deltas();
            if (sizeof($prodDeltas) > 0) {

                $products = array();
                $deletes = array();

                $totalpacket = 0;
                $count = 0;

                foreach($prodDeltas as $id => $size ) {

                    if ($totalpacket >= 250000 || $count > 100) break;

                    $this->settings->remove_prod_delta( $id );
                    if ($size > -1) {
                        $meta_value = get_post_meta($id, 'pc_delta');
                        if ( ! empty( $meta_value ) && is_array( $meta_value ) && sizeof($meta_value) > 0) {
                            delete_post_meta($id, 'pc_delta');
                            $product = json_decode($meta_value[0]);
                            if ( ! empty($product ) ) {
                                $totalpacket += $size;
                                $products[] = $product;
                            }
                        }
                    } else {
                        $totalpacket += strlen($id);
                        $deletes[] = (string)$id;
                    }

                    $count += 1;
                }

                if (sizeof( $products ) > 0 || sizeof( $deletes ) > 0 ){
                    $this->feed->send_product_delta( $products, $deletes);
                }
            }
        } catch ( \Exception $exception ) {
            error_log("PureClarity: An Error occured updating product deltas: " . $exception->getMessage() );
        }
    }


    public function process_categories() {

        if ( ! $this->settings->get_catfeed_run() ) {
            return;
        }
        
        if ( !empty($this->settings->get_category_feed_required()) ) {

            $this->settings->clear_category_feed_required();

            $data = $this->feed->build_items( "category", 1 );
            if ( !empty($data) ) {
                try {
                    $this->feed->start_feed( "category" );
                    $this->feed->send_data( "category", $data );
                    $this->feed->end_feed( "category" );
                } catch ( \Exception $exception ) {
                    error_log("PureClarity: An Error occured updating categories: " . $exception->getMessage() );
                }
            }

        }
    }


    public function process_users() {

        if ( ! $this->settings->get_userfeed_run() ) {
            return;
        }
        
        if ( !empty($this->settings->get_user_feed_required()) ) {

            $this->settings->clear_user_feed_required();

            if ( $count > 0 ) {
                try {
                    


                } catch ( \Exception $exception ) {
                    error_log("PureClarity: An Error occured updating users: " . $exception->getMessage() );
                }
            }

        }
    }
    



}