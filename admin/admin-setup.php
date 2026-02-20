<?php
/**
 * Admin setup and AJAX handlers for Indelible Alt Tag Manager.
 *
 * @package Ind_Alt_Tag_Manager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the admin menu page.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_admin_panel() {
    add_menu_page(
        'Alt Tag Manager',
        'Alt Tag Manager',
        'upload_files',
        'ind-alt-tag-manager-admin',
        'ind_alt_tag_manager_admin_settings'
    );
}
add_action( 'admin_menu', 'ind_alt_tag_manager_admin_panel' );

/**
 * Render the admin settings page.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_admin_settings() {
    if ( ! current_user_can( 'upload_files' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ind-alt-tag-manager' ) );
    }

    ob_start();
    ?>
    <div class='wrap'>
        <h1 class='ind-alt-tag-manager-admin-main-headline'><?php echo esc_html( ucwords( IND_ALT_TAG_MANAGER_NAME ) ); ?> <?php esc_html_e( 'Settings Page', 'ind-alt-tag-manager' ); ?></h1>
        <h2 class='ind-alt-tag-headline'><?php esc_html_e( 'Images Missing Alt tags', 'ind-alt-tag-manager' ); ?></h2>

        <?php
        // Get total count (cached separately)
        $cache_key_count = 'ind_alt_tag_count_no_alt';
        $total_count     = get_transient( $cache_key_count );

        if ( false === $total_count ) {
            $total_count = ind_alt_tag_manager_get_images_without_alt_count();
            set_transient( $cache_key_count, $total_count, 1800 );
        }

        // Get first page of images (cached by page)
        $per_page  = 20;
        $page      = 1;
        $cache_key = 'ind_alt_tag_images_no_alt_page_' . $page;
        $images    = get_transient( $cache_key );

        if ( false === $images ) {
            $image_ids = ind_alt_tag_manager_get_images_without_alt( $page, $per_page );

            $images = array();
            foreach ( $image_ids as $id ) {
                $image_url  = wp_get_attachment_url( $id );
                $alt        = get_post_meta( $id, '_wp_attachment_image_alt', true );
                $images[ $id ] = array(
                    'url' => $image_url,
                    'alt' => $alt,
                );
            }

            set_transient( $cache_key, $images, 1800 );
        }
        ?>
        <?php // translators: %d: number of images missing alt tags. ?>
        <p class='alt-count'><?php printf( esc_html__( 'Total Missing Alt Tags: %d', 'ind-alt-tag-manager' ), absint( $total_count ) ); ?></p>
        <div class='ind-alt-tag-manager-container'>
        <?php
        if ( ! empty( $images ) ) {
            foreach ( $images as $key => $image ) {
                ?>
                <div class='ind-alt-tag-single'>
                    <img class='ind-alt-tag-img' src='<?php echo esc_url( $image['url'] ); ?>'/>
                    <label for='ind-alt-tag-alt<?php echo absint( $key ); ?>'><?php esc_html_e( 'Default Alt Tag:', 'ind-alt-tag-manager' ); ?>
                        <input type='text' id='ind-alt-tag-alt<?php echo absint( $key ); ?>' class='ind-alt-tag-alt'>
                    </label>
                    <button class='ind-alt-tag-save' data-id='<?php echo absint( $key ); ?>'><?php esc_html_e( 'Save', 'ind-alt-tag-manager' ); ?></button>
                </div>
                <?php
            }
        } else {
            ?>
            <div class='ind-alt-tag-congrats'>
                <h2><?php esc_html_e( 'Congratulations! You have cleaned up all of your alt tags!', 'ind-alt-tag-manager' ); ?></h2>
                <p><?php esc_html_e( 'Now you should probably check out lighthouse and do what that says.', 'ind-alt-tag-manager' ); ?></p>
                <img src='<?php echo esc_url( IND_ALT_TAG_MANAGER_ROOT_URL . 'images/checkmark.png' ); ?>' alt='<?php esc_attr_e( 'Success', 'ind-alt-tag-manager' ); ?>'>
            </div>
            <?php
        }
        ?>
        </div>
        <?php
        if ( $total_count > $per_page ) {
            ?>
            <button class='ind-alt-tag-more' data-page='1' data-per-page='<?php echo absint( $per_page ); ?>' data-total='<?php echo absint( $total_count ); ?>'><?php esc_html_e( 'Load More', 'ind-alt-tag-manager' ); ?></button>
            <?php
        }
        ?>
    </div>
    <?php
    // All content in the buffer is already escaped individually above.
    echo wp_kses_post( ob_get_clean() );
}

/**
 * Get image attachment IDs that are missing alt tags.
 *
 * @since 1.0.0
 * @param int $page     The page number to retrieve.
 * @param int $per_page Number of results per page.
 * @return array Array of attachment IDs.
 */
function ind_alt_tag_manager_get_images_without_alt( $page = 1, $per_page = 20 ) {
    $query_images_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'fields'         => 'ids',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to find images without alt tags.
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_wp_attachment_image_alt',
                'value'   => '',
                'compare' => '=',
            ),
            array(
                'key'     => '_wp_attachment_image_alt',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

    return get_posts( $query_images_args );
}

/**
 * Get the total count of images missing alt tags.
 *
 * Uses WP_Query with found_posts for efficiency instead of fetching all IDs.
 *
 * @since 1.0.0
 * @return int Total count of images without alt tags.
 */
function ind_alt_tag_manager_get_images_without_alt_count() {
    $query = new WP_Query( array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to find images without alt tags.
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_wp_attachment_image_alt',
                'value'   => '',
                'compare' => '=',
            ),
            array(
                'key'     => '_wp_attachment_image_alt',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );

    return $query->found_posts;
}

/**
 * Display admin notice when images are missing alt tags.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_admin_notices() {
    $trans = get_transient( 'ind-alt-tag-warning' );
    if ( ! $trans ) {
        $cache_key_count = 'ind_alt_tag_count_no_alt';
        $count           = get_transient( $cache_key_count );

        if ( false === $count ) {
            $count = ind_alt_tag_manager_get_images_without_alt_count();
            set_transient( $cache_key_count, $count, 1800 );
        }

        if ( $count > 0 ) {
            ob_start();
            ?>
            <div class='notice notice-warning is-dismissible'>
                <p><?php
                    printf(
                        // translators: %1$d: number of images missing alt tags, %2$s: link to Alt Tag Manager page.
                        esc_html__( 'There are %1$d images that don\'t have alt tags, you can fix this in the %2$s', 'ind-alt-tag-manager' ),
                        absint( $count ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=ind-alt-tag-manager-admin' ) ) . '">' . esc_html__( 'Alt Tag Manager', 'ind-alt-tag-manager' ) . '</a>'
                    );
                ?></p>
            </div>
            <?php
            echo wp_kses_post( ob_get_clean() );
        } else {
            set_transient( 'ind-alt-tag-warning', 'no alts', 1800 );
        }
    }
}
add_action( 'admin_notices', 'ind_alt_tag_manager_admin_notices' );

/**
 * Clear alt tag transients when a new file is uploaded.
 *
 * @since 1.0.0
 * @param array $file The file data array.
 * @return array The unmodified file data array.
 */
function ind_alt_tag_manager_upload( $file ) {
    ind_alt_tag_manager_delete_alt_tag_trans();
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'ind_alt_tag_manager_upload' );

/**
 * Clear cache when alt tag meta is updated.
 *
 * @since 1.0.0
 * @param int    $meta_id    The meta ID.
 * @param int    $object_id  The object ID.
 * @param string $meta_key   The meta key.
 * @param mixed  $_meta_value The meta value.
 * @return void
 */
function ind_alt_tag_manager_clear_cache_on_alt_update( $meta_id, $object_id, $meta_key, $_meta_value ) {
    if ( $meta_key === '_wp_attachment_image_alt' ) {
        if ( get_post_type( $object_id ) === 'attachment' ) {
            ind_alt_tag_manager_delete_alt_tag_trans();
        }
    }
}
add_action( 'updated_post_meta', 'ind_alt_tag_manager_clear_cache_on_alt_update', 10, 4 );

/**
 * Clear cache when alt tag meta is added.
 *
 * @since 1.0.0
 * @param int    $meta_id    The meta ID.
 * @param int    $object_id  The object ID.
 * @param string $meta_key   The meta key.
 * @param mixed  $_meta_value The meta value.
 * @return void
 */
function ind_alt_tag_manager_clear_cache_on_alt_add( $meta_id, $object_id, $meta_key, $_meta_value ) {
    if ( $meta_key === '_wp_attachment_image_alt' ) {
        if ( get_post_type( $object_id ) === 'attachment' ) {
            ind_alt_tag_manager_delete_alt_tag_trans();
        }
    }
}
add_action( 'added_post_meta', 'ind_alt_tag_manager_clear_cache_on_alt_add', 10, 4 );

/**
 * Clear cache when alt tag meta is deleted.
 *
 * @since 1.0.0
 * @param array  $meta_ids   The meta IDs.
 * @param int    $object_id  The object ID.
 * @param string $meta_key   The meta key.
 * @param mixed  $_meta_value The meta value.
 * @return void
 */
function ind_alt_tag_manager_clear_cache_on_alt_delete( $meta_ids, $object_id, $meta_key, $_meta_value ) {
    if ( $meta_key === '_wp_attachment_image_alt' ) {
        if ( get_post_type( $object_id ) === 'attachment' ) {
            ind_alt_tag_manager_delete_alt_tag_trans();
        }
    }
}
add_action( 'deleted_post_meta', 'ind_alt_tag_manager_clear_cache_on_alt_delete', 10, 4 );

/**
 * Delete all plugin transients related to alt tag data.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_delete_alt_tag_trans() {
    global $wpdb;

    delete_transient( 'ind-alt-tag-warning' );
    delete_transient( 'ind_alt_tag_count_no_alt' );

    // Delete all page-specific transients using SQL pattern matching.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient cleanup, no caching API available.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_ind_alt_tag_images_no_alt_page_%'
        )
    );

    // Also delete timeout transients.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient cleanup, no caching API available.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_ind_alt_tag_images_no_alt_page_%'
        )
    );
}

/**
 * AJAX handler to update an image's alt tag.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_update_alt_tag() {
    check_ajax_referer( 'ind_alt_tag_manager_nonce', 'nonce', true );

    if ( ! current_user_can( 'upload_files' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }

    if ( ! isset( $_POST['data'] ) || ! is_array( $_POST['data'] ) ) {
        wp_send_json_error( 'Invalid data format' );
    }

    $id  = absint( $_POST['data']['id'] ?? 0 );
    $alt = sanitize_text_field( wp_unslash( $_POST['data']['alt'] ?? '' ) );

    if ( empty( $id ) || get_post_type( $id ) !== 'attachment' ) {
        wp_send_json_error( 'Invalid attachment ID' );
    }

    // Check user can edit this specific attachment
    if ( ! current_user_can( 'edit_post', $id ) ) {
        wp_send_json_error( 'Cannot edit this attachment' );
    }

    $return = update_post_meta( $id, '_wp_attachment_image_alt', $alt );
    ind_alt_tag_manager_delete_alt_tag_trans();

    wp_send_json_success( array( 'updated' => $return ) );
}
add_action( 'wp_ajax_ind_alt_tag_manager_update_alt_tag', 'ind_alt_tag_manager_update_alt_tag' );

/**
 * AJAX handler to load more images for pagination.
 *
 * @since 1.0.0
 * @return void
 */
function ind_alt_tag_manager_load_more_images() {
    check_ajax_referer( 'ind_alt_tag_manager_nonce', 'nonce', true );

    if ( ! current_user_can( 'upload_files' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }

    if ( ! isset( $_POST['page'] ) || ! isset( $_POST['per_page'] ) ) {
        wp_send_json_error( 'Invalid data format' );
    }

    $page     = absint( $_POST['page'] ?? 1 );
    $per_page = absint( $_POST['per_page'] ?? 20 );

    if ( $page < 1 || $per_page < 1 || $per_page > 100 ) {
        wp_send_json_error( 'Invalid pagination parameters' );
    }

    // Check transient cache first
    $cache_key = 'ind_alt_tag_images_no_alt_page_' . $page;
    $images    = get_transient( $cache_key );

    if ( false === $images ) {
        $image_ids = ind_alt_tag_manager_get_images_without_alt( $page, $per_page );

        $images = array();
        foreach ( $image_ids as $id ) {
            $image_url     = wp_get_attachment_url( $id );
            $alt           = get_post_meta( $id, '_wp_attachment_image_alt', true );
            $images[ $id ] = array(
                'url' => $image_url,
                'alt' => $alt,
            );
        }

        set_transient( $cache_key, $images, 1800 );
    }

    // Build HTML for the images
    $html = '';
    if ( ! empty( $images ) ) {
        foreach ( $images as $key => $image ) {
            ob_start();
            ?>
            <div class='ind-alt-tag-single'>
                <img class='ind-alt-tag-img' src='<?php echo esc_url( $image['url'] ); ?>'/>
                <label for='ind-alt-tag-alt<?php echo absint( $key ); ?>'><?php esc_html_e( 'Default Alt Tag:', 'ind-alt-tag-manager' ); ?>
                    <input type='text' id='ind-alt-tag-alt<?php echo absint( $key ); ?>' class='ind-alt-tag-alt'>
                </label>
                <button class='ind-alt-tag-save' data-id='<?php echo absint( $key ); ?>'><?php esc_html_e( 'Save', 'ind-alt-tag-manager' ); ?></button>
            </div>
            <?php
            $html .= ob_get_clean();
        }
    }

    wp_send_json_success( array(
        'html'  => $html,
        'count' => count( $images ),
        'page'  => $page,
    ) );
}
add_action( 'wp_ajax_ind_alt_tag_manager_load_more_images', 'ind_alt_tag_manager_load_more_images' );
