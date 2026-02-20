<?php
/**
 * Plugin Name: Indelible Alt Tag Manager
 * Plugin URI: https://becomeindelible.com
 * Description: This plugin finds missing alt tags and sets up an easy to use interface to update missing alt tags.
 * Version: 1.0.0
 * Author: Indelible Inc.
 * Author URI: https://becomeindelible.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ind-alt-tag-manager
 * Requires at least: 5.0
 * Requires PHP: 7.2
 *
 * @package Ind_Alt_Tag_Manager
 */

defined( 'ABSPATH' ) || exit;

define( 'IND_ALT_TAG_MANAGER_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'IND_ALT_TAG_MANAGER_SLUG', plugin_basename( IND_ALT_TAG_MANAGER_ROOT_PATH ) );
define( 'IND_ALT_TAG_MANAGER_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'IND_ALT_TAG_MANAGER_ROOT_FILE', __FILE__ );

// VERSIONS
if ( ! function_exists( 'get_plugin_data' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
define( 'IND_ALT_TAG_MANAGER_NAME', get_plugin_data( __FILE__ )['Name'] );
define( 'IND_ALT_TAG_MANAGER_VERSION', get_plugin_data( __FILE__ )['Version'] );

require_once IND_ALT_TAG_MANAGER_ROOT_PATH . 'includes.php';

register_activation_hook( __FILE__, 'ind_alt_tag_manager_activate' );
register_deactivation_hook( __FILE__, 'ind_alt_tag_manager_deactivate' );

/**
 * Plugin activation callback.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_activate() {
	ind_alt_tag_manager_schedule_update_check();
}

/**
 * Plugin deactivation callback.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_deactivate() {
	ind_alt_tag_manager_clear_update_schedule();
}

/**
 * Enqueue admin scripts and styles on the plugin admin page.
 *
 * @since 1.0.0
 * @param string $hook_suffix The current admin page hook suffix.
 * @return void
 */
function ind_alt_tag_manager_admin_scripts( $hook_suffix ) {
    // Only load on plugin's admin page
    if ( $hook_suffix !== 'toplevel_page_ind-alt-tag-manager-admin' ) {
        return;
    }

    wp_enqueue_style(
        'ind-alt-tag-manager-admin.css',
        IND_ALT_TAG_MANAGER_ROOT_URL . 'admin/css/style.min.css',
        array(),
        IND_ALT_TAG_MANAGER_VERSION
    );

    wp_enqueue_script(
        'ind-alt-tag-manager-adminjs',
        IND_ALT_TAG_MANAGER_ROOT_URL . 'admin/js/app.min.js',
        array( 'jquery' ),
        IND_ALT_TAG_MANAGER_VERSION,
        true
    );

    wp_localize_script( 'ind-alt-tag-manager-adminjs', 'ind_alt_tag_manager_admin_ajax', array(
        'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
        'ind_alt_tag_manager_admin_nonce' => wp_create_nonce( 'ind_alt_tag_manager_nonce' ),
        'pluginUrl'                     => IND_ALT_TAG_MANAGER_ROOT_URL,
        'i18n'                          => array(
            'saveFailed'  => __( 'Failed to save alt tag. Please try again.', 'ind-alt-tag-manager' ),
            'errorGeneric' => __( 'An error occurred. Please try again.', 'ind-alt-tag-manager' ),
            'noMoreImages' => __( 'No more images to load.', 'ind-alt-tag-manager' ),
            'loadError'    => __( 'An error occurred while loading more images. Please try again.', 'ind-alt-tag-manager' ),
            'loading'      => __( 'Loading...', 'ind-alt-tag-manager' ),
            'loadMore'     => __( 'Load More', 'ind-alt-tag-manager' ),
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'ind_alt_tag_manager_admin_scripts' );
