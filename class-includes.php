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
require_once PURECLARITY_PATH . 'includes/class-pureclarity-order.php';
require_once PURECLARITY_PATH . 'includes/feeds/class-pureclarity-feed.php';
require_once PURECLARITY_PATH . 'includes/feeds/class-pureclarity-feed-status.php';
require_once PURECLARITY_PATH . 'includes/watchers/class-pureclarity-products-watcher.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-delta.php';
require_once PURECLARITY_PATH . 'includes/class-pureclarity-cron.php';

require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'class-pureclarity-cron-feeds.php';
require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'class-pureclarity-cron-deltas.php';
require_once PURECLARITY_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'php-sdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'data-managers' . DIRECTORY_SEPARATOR . 'class-pureclarity-state-manager.php';
require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'data-managers' . DIRECTORY_SEPARATOR . 'class-pureclarity-delta-manager.php';
// Add admin only classes.
if ( is_admin() ) {
	require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-pureclarity-signup.php';
	require_once PURECLARITY_PATH . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-pureclarity-feeds.php';
	require_once PURECLARITY_PATH . 'includes/admin/class-pureclarity-admin.php';
	require_once PURECLARITY_PATH . 'includes/admin/class-pureclarity-dashboard-page.php';
	require_once PURECLARITY_PATH . 'includes/admin/class-pureclarity-settings-page.php';
}
