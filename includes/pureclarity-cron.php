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
                        $product = get_post_meta($id, 'pc_delta');
                        if ( ! empty( $product )) {
                            delete_post_meta($id, 'pc_delta');
                            $totalpacket += $size;
                            $products[] = $product;
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

        }

        
    }

    



}