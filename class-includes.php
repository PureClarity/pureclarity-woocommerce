<?php
/**
 * Includes used for classes
 *
 * @package PureClarity for WooCommerce
 * @since 2.0.0
 */

// Ensure path constant is set.
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	exit();
}

// Include required PureClarity classes.
require_once PURECLARITY_PATH . 'includes/class-pureclarity-plugin.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-settings.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-state.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-template.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-bmz.php';
require_once PURECLARITY_PATH . 'includes/feeds/class-pureclarity-feed.php';
require_once PURECLARITY_PATH . 'includes/watchers/class-pureclarity-products-watcher.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-cron.php';

// Add admin only classes.
if ( is_admin() ) {
	require_once PURECLARITY_PATH . 'includes/admin/class-pureclarity-admin.php';
}
