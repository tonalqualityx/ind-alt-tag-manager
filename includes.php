<?php
/**
 * Plugin includes loader.
 *
 * @package Ind_Alt_Tag_Manager
 */

defined( 'ABSPATH' ) || exit;

// for plugin updates must be first in includes
if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

require_once IND_ALT_TAG_MANAGER_ROOT_PATH . 'update-checker.php';
require_once IND_ALT_TAG_MANAGER_ROOT_PATH . 'includes/cache-clearer.php';
require_once IND_ALT_TAG_MANAGER_ROOT_PATH . 'admin/admin-setup.php';
require_once IND_ALT_TAG_MANAGER_ROOT_PATH . 'admin/settings-page.php';
