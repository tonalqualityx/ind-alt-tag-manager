<?php
/**
 * Uninstall handler for Indelible Alt Tag Manager.
 *
 * Cleans up all plugin transients when the plugin is deleted.
 *
 * @package Ind_Alt_Tag_Manager
 */

// Exit if not called by WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete known transients.
delete_transient( 'ind_alt_tag_count_no_alt' );
delete_transient( 'ind-alt-tag-warning' );
delete_transient( 'ind_alt_tag_manager_update_data' );

// Delete all page-specific transients.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, no caching API for bulk transient deletion.
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_ind_alt_tag_images_no_alt_page_%'
    )
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, no caching API for bulk transient deletion.
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_timeout_ind_alt_tag_images_no_alt_page_%'
    )
);
