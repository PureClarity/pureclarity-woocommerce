<?php 

// Ensure path constant is set
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	exit();
}

// Include required PureClarity classes
require_once PURECLARITY_PATH . 'includes/pureclarity-plugin.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-settings.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-state.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-template.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-bmz.php';
require_once PURECLARITY_PATH . 'includes/feeds/pureclarity-feed.php';
require_once PURECLARITY_PATH . 'includes/watchers/pureclarity-products-watcher.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-cron.php';

// Add admin only classes
if ( is_admin() ) {
    require_once PURECLARITY_PATH . 'includes/admin/pureclarity-admin.php';
}