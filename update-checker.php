<?php
/**
 * Custom update checker for self-hosted plugin updates.
 *
 * Handles cron scheduling, remote API fetching, and WordPress update integration.
 *
 * @package Ind_Alt_Tag_Manager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schedule a daily update check at 3am in the site's timezone.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_schedule_update_check() {
	$hook = 'ind_alt_tag_manager_update_check';

	// Don't double-schedule.
	if ( wp_next_scheduled( $hook ) ) {
		return;
	}

	$timezone  = wp_timezone();
	$next_3am  = new DateTime( 'today 3:00am', $timezone );

	// If 3am has already passed today, schedule for tomorrow.
	$now = new DateTime( 'now', $timezone );
	if ( $now > $next_3am ) {
		$next_3am->modify( '+1 day' );
	}

	wp_schedule_event( $next_3am->getTimestamp(), 'daily', $hook );
}

/**
 * Clear the scheduled update check cron event.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_clear_update_schedule() {
	wp_clear_scheduled_hook( 'ind_alt_tag_manager_update_check' );
}

/**
 * Fetch update data from the remote API and cache it in a transient.
 *
 * Hooked to the `ind_alt_tag_manager_update_check` cron event.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_fetch_update_data() {
	$response = wp_remote_get(
		'https://plugins.becomeindelible.com/api/v1/indelible-alt-tags/check',
		array( 'timeout' => 10 )
	);

	if ( is_wp_error( $response ) ) {
		return;
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );

	if ( ! is_object( $data ) || empty( $data->version ) ) {
		return;
	}

	set_transient( 'ind_alt_tag_manager_update_data', $data, DAY_IN_SECONDS );
}
add_action( 'ind_alt_tag_manager_update_check', 'ind_alt_tag_manager_fetch_update_data' );

/**
 * Inject update info into the WordPress update transient when a new version is available.
 *
 * @since 1.1.0
 * @param object $transient The update_plugins transient data.
 * @return object Modified transient data.
 */
function ind_alt_tag_manager_check_update( $transient ) {
	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}

	$update_data = get_transient( 'ind_alt_tag_manager_update_data' );

	if ( ! is_object( $update_data ) || empty( $update_data->version ) ) {
		return $transient;
	}

	if ( ! version_compare( $update_data->version, IND_ALT_TAG_MANAGER_VERSION, '>' ) ) {
		return $transient;
	}

	$plugin_basename = plugin_basename( IND_ALT_TAG_MANAGER_ROOT_FILE );

	$update              = new stdClass();
	$update->slug        = 'ind-alt-tag-manager';
	$update->plugin      = $plugin_basename;
	$update->new_version = $update_data->version;
	$update->package     = isset( $update_data->download_url ) ? $update_data->download_url : '';
	$update->icons       = isset( $update_data->icons ) ? (array) $update_data->icons : array();
	$update->banners     = isset( $update_data->banners ) ? (array) $update_data->banners : array();
	$update->requires    = isset( $update_data->requires ) ? $update_data->requires : '';
	$update->tested      = isset( $update_data->tested ) ? $update_data->tested : '';
	$update->requires_php = isset( $update_data->requires_php ) ? $update_data->requires_php : '';

	$transient->response[ $plugin_basename ] = $update;

	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'ind_alt_tag_manager_check_update' );

/**
 * Provide plugin information for the "View details" modal in the plugins list.
 *
 * @since 1.1.0
 * @param false|object|array $result The result object or array. Default false.
 * @param string             $action The type of information being requested.
 * @param object             $args   Plugin API arguments.
 * @return false|object Plugin info object or false to let WordPress handle it.
 */
function ind_alt_tag_manager_plugin_info( $result, $action, $args ) {
	if ( 'plugin_information' !== $action ) {
		return $result;
	}

	if ( ! isset( $args->slug ) || 'ind-alt-tag-manager' !== $args->slug ) {
		return $result;
	}

	$update_data = get_transient( 'ind_alt_tag_manager_update_data' );

	if ( ! is_object( $update_data ) ) {
		return $result;
	}

	$info              = new stdClass();
	$info->name        = isset( $update_data->name ) ? $update_data->name : 'Indelible Alt Tag Manager';
	$info->slug        = 'ind-alt-tag-manager';
	$info->version     = isset( $update_data->version ) ? $update_data->version : '';
	$info->author      = isset( $update_data->author ) ? $update_data->author : '';
	$info->homepage    = isset( $update_data->homepage ) ? $update_data->homepage : '';
	$info->requires    = isset( $update_data->requires ) ? $update_data->requires : '';
	$info->tested      = isset( $update_data->tested ) ? $update_data->tested : '';
	$info->requires_php = isset( $update_data->requires_php ) ? $update_data->requires_php : '';
	$info->download_link = isset( $update_data->download_url ) ? $update_data->download_url : '';
	$info->banners     = isset( $update_data->banners ) ? (array) $update_data->banners : array();
	$info->icons       = isset( $update_data->icons ) ? (array) $update_data->icons : array();

	$info->sections = array();
	if ( isset( $update_data->sections ) ) {
		foreach ( $update_data->sections as $key => $value ) {
			$info->sections[ $key ] = $value;
		}
	}

	return $info;
}
add_filter( 'plugins_api', 'ind_alt_tag_manager_plugin_info', 10, 3 );
