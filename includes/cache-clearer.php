<?php
/**
 * Cache clearing integration for Indelible Alt Tag Manager.
 *
 * Handles cache clearing for various caching plugins and WordPress core caches.
 *
 * @package Ind_Alt_Tag_Manager
 * @since 0.1.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clear all relevant caches after an alt tag is updated.
 *
 * This function clears:
 * - Plugin's own transients
 * - WordPress object cache for the attachment
 * - Third-party caching plugin caches (if active)
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_all_caches( $attachment_id ) {
    // Clear plugin transients.
    ind_alt_tag_manager_delete_alt_tag_trans();

    // Clear WordPress object cache for this attachment.
    clean_post_cache( $attachment_id );

    // Clear third-party caching plugin caches.
    ind_alt_tag_manager_clear_third_party_caches( $attachment_id );
}

/**
 * Clear third-party caching plugin caches.
 *
 * Supports:
 * - Hummingbird (WPMU DEV)
 * - WP Rocket
 * - W3 Total Cache
 * - LiteSpeed Cache
 * - WP Super Cache
 * - SiteGround Optimizer
 * - Cloudflare (if plugin installed)
 * - Redis Object Cache
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_third_party_caches( $attachment_id ) {
    /**
     * Fires before third-party caches are cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_before_clear_caches', $attachment_id );

    // Hummingbird (WPMU DEV).
    ind_alt_tag_manager_clear_hummingbird_cache( $attachment_id );

    // WP Rocket.
    ind_alt_tag_manager_clear_wp_rocket_cache( $attachment_id );

    // W3 Total Cache.
    ind_alt_tag_manager_clear_w3_total_cache( $attachment_id );

    // LiteSpeed Cache.
    ind_alt_tag_manager_clear_litespeed_cache( $attachment_id );

    // WP Super Cache.
    ind_alt_tag_manager_clear_wp_super_cache( $attachment_id );

    // SiteGround Optimizer.
    ind_alt_tag_manager_clear_siteground_optimizer( $attachment_id );

    // Cloudflare plugin.
    ind_alt_tag_manager_clear_cloudflare_cache( $attachment_id );

    // Redis Object Cache.
    ind_alt_tag_manager_clear_redis_object_cache( $attachment_id );

    /**
     * Fires after third-party caches have been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_after_clear_caches', $attachment_id );
}

/**
 * Clear Hummingbird cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_hummingbird_cache( $attachment_id ) {
    // Check if Hummingbird is active.
    if ( ! class_exists( 'WP_Hummingbird' ) ) {
        return;
    }

    // Clear page cache.
    if ( function_exists( 'wphb_clear_page_cache' ) ) {
        wphb_clear_page_cache();
    }

    // Clear object cache.
    if ( function_exists( 'wphb_clear_object_cache' ) ) {
        wphb_clear_object_cache();
    }

    // Alternative: Use the core class methods if available.
    if ( function_exists( 'wphb_clear_cache' ) ) {
        wphb_clear_cache();
    }

    // Clear cache for specific attachment URL.
    $attachment_url = get_permalink( $attachment_id );
    if ( $attachment_url && function_exists( 'wphb_clear_url_cache' ) ) {
        wphb_clear_url_cache( $attachment_url );
    }

    /**
     * Fires after Hummingbird cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_hummingbird_cache', $attachment_id );
}

/**
 * Clear WP Rocket cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_wp_rocket_cache( $attachment_id ) {
    // Check if WP Rocket is active.
    if ( ! function_exists( 'rocket_clean_post' ) ) {
        return;
    }

    // Clear cache for the attachment post.
    rocket_clean_post( $attachment_id );

    // Clear homepage cache as well.
    if ( function_exists( 'rocket_clean_home' ) ) {
        rocket_clean_home();
    }

    /**
     * Fires after WP Rocket cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_wp_rocket_cache', $attachment_id );
}

/**
 * Clear W3 Total Cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_w3_total_cache( $attachment_id ) {
    // Check if W3 Total Cache is active.
    if ( ! defined( 'W3TC' ) ) {
        return;
    }

    // Clear post cache.
    if ( function_exists( 'w3tc_flush_post' ) ) {
        w3tc_flush_post( $attachment_id );
    }

    // Clear object cache.
    if ( function_exists( 'w3tc_flush_objectcache' ) ) {
        w3tc_flush_objectcache();
    }

    // Alternative: Use the global object.
    if ( isset( $GLOBALS['w3tc_object'] ) && method_exists( $GLOBALS['w3tc_object'], 'flush' ) ) {
        $GLOBALS['w3tc_object']->flush();
    }

    /**
     * Fires after W3 Total Cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_w3_total_cache', $attachment_id );
}

/**
 * Clear LiteSpeed Cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_litespeed_cache( $attachment_id ) {
    // Check if LiteSpeed Cache is active.
    if ( ! defined( 'LSCWP_V' ) ) {
        return;
    }

    // Clear post cache.
    if ( function_exists( 'litespeed_purge_single_post' ) ) {
        litespeed_purge_single_post( $attachment_id );
    }

    // Alternative: Use the purge API.
    if ( class_exists( 'LiteSpeed\Purge' ) && method_exists( 'LiteSpeed\Purge', 'add' ) ) {
        $attachment_url = get_permalink( $attachment_id );
        if ( $attachment_url ) {
            LiteSpeed\Purge::add( $attachment_url );
        }
    }

    /**
     * Fires after LiteSpeed Cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_litespeed_cache', $attachment_id );
}

/**
 * Clear WP Super Cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_wp_super_cache( $attachment_id ) {
    // Check if WP Super Cache is active.
    if ( ! function_exists( 'wp_cache_post_change' ) ) {
        return;
    }

    // Clear cache for the post.
    wp_cache_post_change( $attachment_id );

    // Alternative: Use the global wp_supercache_cache.
    global $file_prefix;
    if ( function_exists( 'wp_cache_clean_cache' ) && isset( $file_prefix ) ) {
        wp_cache_clean_cache( $file_prefix, true );
    }

    /**
     * Fires after WP Super Cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_wp_super_cache', $attachment_id );
}

/**
 * Clear SiteGround Optimizer cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_siteground_optimizer( $attachment_id ) {
    // Check if SiteGround Optimizer is active.
    if ( ! class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        return;
    }

    // Purge the cache.
    if ( class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        SiteGround_Optimizer\Supercacher\Supercacher::get_instance()->purge_cache();
    }

    /**
     * Fires after SiteGround Optimizer cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_siteground_optimizer_cache', $attachment_id );
}

/**
 * Clear Cloudflare cache (via Cloudflare plugin).
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_cloudflare_cache( $attachment_id ) {
    // Check if Cloudflare plugin is active.
    if ( ! class_exists( 'CF\WordPress\Hooks' ) ) {
        return;
    }

    // Clear post cache via Cloudflare.
    $cloudflare_hooks = new CF\WordPress\Hooks();
    if ( method_exists( $cloudflare_hooks, 'purgeCacheByRelevantURLs' ) ) {
        $attachment_url = get_permalink( $attachment_id );
        if ( $attachment_url ) {
            $cloudflare_hooks->purgeCacheByRelevantURLs( array( $attachment_url ) );
        }
    }

    /**
     * Fires after Cloudflare cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_cloudflare_cache', $attachment_id );
}

/**
 * Clear Redis Object Cache.
 *
 * @since 0.1.1
 * @param int $attachment_id The attachment ID that was updated.
 * @return void
 */
function ind_alt_tag_manager_clear_redis_object_cache( $attachment_id ) {
    // Check if Redis Object Cache is active.
    if ( ! function_exists( 'redis_object_cache' ) && ! class_exists( 'Redis_Object_Cache' ) ) {
        return;
    }

    // Flush object cache via Redis Object Cache.
    if ( function_exists( 'redis_object_cache' ) ) {
        $roc = redis_object_cache();
        if ( method_exists( $roc, 'flush' ) ) {
            $roc->flush();
        }
    }

    // Alternative: Use the global wp_cache.
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }

    /**
     * Fires after Redis Object Cache has been cleared.
     *
     * @since 0.1.1
     * @param int $attachment_id The attachment ID that was updated.
     */
    do_action( 'ind_alt_tag_manager_cleared_redis_object_cache', $attachment_id );
}

/**
 * List of supported caching plugins for documentation purposes.
 *
 * This function returns an array of supported caching plugins with their
 * detection methods and cache clearing functions.
 *
 * @since 0.1.1
 * @return array Array of caching plugin data.
 */
function ind_alt_tag_manager_get_supported_cache_plugins() {
    return array(
        'hummingbird'         => array(
            'name'            => 'Hummingbird (WPMU DEV)',
            'active_check'    => 'class_exists( "WP_Hummingbird" )',
            'clear_functions' => array(
                'wphb_clear_page_cache()',
                'wphb_clear_object_cache()',
                'wphb_clear_cache()',
            ),
            'documentation'   => 'https://wpmudev.com/docs/wpmu-dev-plugins/hummingbird/',
        ),
        'wp_rocket'           => array(
            'name'            => 'WP Rocket',
            'active_check'    => 'function_exists( "rocket_clean_post" )',
            'clear_functions' => array(
                'rocket_clean_post( $post_id )',
                'rocket_clean_home()',
            ),
            'documentation'   => 'https://docs.wp-rocket.me/article/93-rocketcleanpost',
        ),
        'w3_total_cache'      => array(
            'name'            => 'W3 Total Cache',
            'active_check'    => 'defined( "W3TC" )',
            'clear_functions' => array(
                'w3tc_flush_post( $post_id )',
                'w3tc_flush_objectcache()',
            ),
            'documentation'   => 'https://github.com/BoldGrid/w3-total-cache/wiki/FAQ:-Developers',
        ),
        'litespeed_cache'     => array(
            'name'            => 'LiteSpeed Cache',
            'active_check'    => 'defined( "LSCWP_V" )',
            'clear_functions' => array(
                'litespeed_purge_single_post( $post_id )',
                'LiteSpeed\\Purge::add( $url )',
            ),
            'documentation'   => 'https://docs.litespeedtech.com/lscache/lscwp/api/',
        ),
        'wp_super_cache'      => array(
            'name'            => 'WP Super Cache',
            'active_check'    => 'function_exists( "wp_cache_post_change" )',
            'clear_functions' => array(
                'wp_cache_post_change( $post_id )',
            ),
            'documentation'   => 'https://wordpress.org/support/plugin/wp-super-cache/',
        ),
        'siteground_optimizer' => array(
            'name'            => 'SiteGround Optimizer',
            'active_check'    => 'class_exists( "SiteGround_Optimizer\\Supercacher\\Supercacher" )',
            'clear_functions' => array(
                'SiteGround_Optimizer\\Supercacher\\Supercacher::get_instance()->purge_cache()',
            ),
            'documentation'   => 'https://www.siteground.com/kb/siteground-optimizer/',
        ),
        'cloudflare'          => array(
            'name'            => 'Cloudflare (Official Plugin)',
            'active_check'    => 'class_exists( "CF\\WordPress\\Hooks" )',
            'clear_functions' => array(
                'CF\\WordPress\\Hooks::purgeCacheByRelevantURLs( array( $url ) )',
            ),
            'documentation'   => 'https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/',
        ),
        'redis_object_cache'  => array(
            'name'            => 'Redis Object Cache',
            'active_check'    => 'function_exists( "redis_object_cache" )',
            'clear_functions' => array(
                'redis_object_cache()->flush()',
                'wp_cache_flush()',
            ),
            'documentation'   => 'https://github.com/rhubarbgroup/redis-object-cache/wiki',
        ),
    );
}
