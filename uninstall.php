<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

// SEND DELETE REQUEST

if ( ! defined( 'PURECLARITY_PATH' ) ) {
	define( 'PURECLARITY_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PURECLARITY_INCLUDES_PATH' ) ) {
	define( 'PURECLARITY_INCLUDES_PATH', PURECLARITY_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR );
}

require_once PURECLARITY_INCLUDES_PATH . 'php-sdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

$delete = new PureClarity\Api\Delete\Submit(
	get_option( 'pureclarity_accesskey' ),
	get_option( 'pureclarity_secretkey' ),
	get_option( 'pureclarity_region' ),
	'HERE IS SOME FEEDBACK!!!'
);

$delete->request();

// REMOVE DATABASE TABLES.

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pureclarity_state" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pureclarity_delta" );

// REMOVE OPTIONS.

$options = array(
	'pureclarity_accesskey',
	'pureclarity_secretkey',
	'pureclarity_region',
	'pureclarity_mode',
	'pureclarity_bmz_debug',
	'pureclarity_deltas_enabled',
	'pureclarity_add_bmz_homepage',
	'pureclarity_add_bmz_searchpage',
	'pureclarity_add_bmz_categorypage',
	'pureclarity_add_bmz_productpage',
	'pureclarity_add_bmz_basketpage',
	'pureclarity_add_bmz_checkoutpage',
	'pureclarity_category_feed_required',
	'pureclarity_brandfeed_run',
	'pureclarity_catfeed_run',
	'pureclarity_db_version',
	'pureclarity_delta_running',
	'pureclarity_orderfeed_run',
	'pureclarity_prodfeed_run',
	'pureclarity_product_deltas',
	'pureclarity_user_deltas',
	'pureclarity_userfeed_run',

);

$option_name = 'wporg_option';

foreach ( $options as $option_name ) {
	delete_option( $option_name );
}


