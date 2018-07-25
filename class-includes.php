<?php 

// Ensure path constant is set
if ( ! defined( 'PURECLARITY_PATH' ) ) {
	exit();
}

// Include required PureClarity classes
require_once PURECLARITY_PATH . 'includes/pureclarity-plugin.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-settings.php';
require_once PURECLARITY_PATH . 'includes/pureclarity-template.php';

// Add admin only classes
if ( is_admin() ) {
    require_once PURECLARITY_PATH . 'includes/admin/pureclarity-admin.php';
}