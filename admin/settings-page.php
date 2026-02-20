<?php
/**
 * Settings page for Indelible Alt Tag Manager.
 *
 * @package Ind_Alt_Tag_Manager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the settings submenu page under the existing Alt Tag Manager menu.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_settings_page_init() {
    add_submenu_page(
        'ind-alt-tag-manager-admin',
        __( 'Alt Tag Manager Settings', 'ind-alt-tag-manager' ),
        __( 'Settings', 'ind-alt-tag-manager' ),
        'manage_options',
        'ind-alt-tag-manager-settings',
        'ind_alt_tag_manager_render_settings_page'
    );
}
add_action( 'admin_menu', 'ind_alt_tag_manager_settings_page_init' );

/**
 * Enqueue scripts and styles for the settings page.
 *
 * @since 1.1.0
 * @param string $hook_suffix The current admin page hook suffix.
 * @return void
 */
function ind_alt_tag_manager_settings_scripts( $hook_suffix ) {
    if ( $hook_suffix !== 'alt-tag-manager_page_ind-alt-tag-manager-settings' ) {
        return;
    }

    wp_enqueue_style(
        'ind-alt-tag-manager-settings',
        IND_ALT_TAG_MANAGER_ROOT_URL . 'admin/css/settings-page.css',
        array(),
        IND_ALT_TAG_MANAGER_VERSION
    );

    wp_enqueue_script(
        'ind-alt-tag-manager-settings',
        IND_ALT_TAG_MANAGER_ROOT_URL . 'admin/js/settings-page.js',
        array( 'jquery' ),
        IND_ALT_TAG_MANAGER_VERSION,
        true
    );

    wp_localize_script( 'ind-alt-tag-manager-settings', 'ind_alt_tag_manager_settings_ajax', array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'ind_alt_tag_manager_settings_nonce' ),
        'i18n'      => array(
            'checking'      => __( 'Checking for updates...', 'ind-alt-tag-manager' ),
            'errorGeneric'  => __( 'An error occurred. Please try again.', 'ind-alt-tag-manager' ),
            'updateAvailable' => __( 'Update Available!', 'ind-alt-tag-manager' ),
            'upToDate'      => __( "You're up to date!", 'ind-alt-tag-manager' ),
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'ind_alt_tag_manager_settings_scripts' );

/**
 * Render the settings page content.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ind-alt-tag-manager' ) );
    }

    $current_version        = ind_alt_tag_manager_get_version();
    $last_check_transient   = get_transient( 'ind_alt_tag_manager_update_data' );
    $last_check_time        = get_option( 'ind_alt_tag_manager_last_manual_check' );
    $last_check_formatted   = $last_check_time ? human_time_diff( $last_check_time, current_time( 'timestamp' ) ) . ' ago' : __( 'Never', 'ind-alt-tag-manager' );

    // Determine update status from cached data (will be refreshed by AJAX)
    $update_available = false;
    $new_version      = '';
    if ( $last_check_transient && isset( $last_check_transient->version ) ) {
        $update_available = version_compare( $last_check_transient->version, $current_version, '>' );
        $new_version      = $last_check_transient->version;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <div class="ind-alt-tag-manager-settings-container">
            <!-- Version Info Card -->
            <div class="ind-alt-settings-card">
                <h2><?php esc_html_e( 'Plugin Information', 'ind-alt-tag-manager' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Current Version', 'ind-alt-tag-manager' ); ?></th>
                        <td>
                            <code class="ind-alt-version"><?php echo esc_html( $current_version ); ?></code>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Last Update Check', 'ind-alt-tag-manager' ); ?></th>
                        <td>
                            <span id="ind-alt-last-check-time"><?php echo esc_html( $last_check_formatted ); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Update Check Card -->
            <div class="ind-alt-settings-card">
                <h2><?php esc_html_e( 'Update Check', 'ind-alt-tag-manager' ); ?></h2>
                <p><?php esc_html_e( 'Manually check for updates to the Alt Tag Manager plugin.', 'ind-alt-tag-manager' ); ?></p>

                <div class="ind-alt-update-check-section">
                    <button type="button" class="button button-primary" id="ind-alt-check-updates-btn">
                        <?php esc_html_e( 'Check for Updates', 'ind-alt-tag-manager' ); ?>
                    </button>
                </div>

                <!-- Results Display Area -->
                <div id="ind-alt-update-results" class="ind-alt-update-results hidden">
                    <div class="ind-alt-results-status">
                        <span class="dashicons dashicons-update-alt ind-alt-spinner hidden"></span>
                        <span id="ind-alt-status-message"></span>
                    </div>

                    <div id="ind-alt-update-available" class="hidden">
                        <div class="notice notice-warning inline">
                            <p>
                                <strong><?php esc_html_e( 'Update Available!', 'ind-alt-tag-manager' ); ?></strong><br>
                                <?php
                                printf(
                                    /* translators: %1$s: Current version, %2$s: New version */
                                    esc_html__( 'Version %1$s is available (you have %2$s).', 'ind-alt-tag-manager' ),
                                    '<span id="ind-alt-new-version"></span>',
                                    '<span id="ind-alt-current-version-display"></span>'
                                );
                                ?>
                            </p>
                        </div>
                        <p>
                            <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-secondary">
                                <?php esc_html_e( 'Go to Plugins Page to Update', 'ind-alt-tag-manager' ); ?>
                            </a>
                        </p>
                    </div>

                    <div id="ind-alt-up-to-date" class="hidden">
                        <div class="notice notice-success inline">
                            <p>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <strong><?php esc_html_e( "You're up to date!", 'ind-alt-tag-manager' ); ?></strong><br>
                                <?php esc_html_e( 'You have the latest version of Alt Tag Manager.', 'ind-alt-tag-manager' ); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Raw Response Debug Area -->
                    <div class="ind-alt-debug-section">
                        <h4><?php esc_html_e( 'Debug Information', 'ind-alt-tag-manager' ); ?></h4>
                        <button type="button" class="button" id="ind-alt-toggle-debug">
                            <?php esc_html_e( 'Show/Hide Raw Response', 'ind-alt-tag-manager' ); ?>
                        </button>
                        <pre id="ind-alt-raw-response" class="hidden"></pre>
                    </div>
                </div>
            </div>

            <!-- API Info Card -->
            <div class="ind-alt-settings-card">
                <h2><?php esc_html_e( 'API Endpoint', 'ind-alt-tag-manager' ); ?></h2>
                <p><?php esc_html_e( 'The plugin checks for updates from the following endpoint:', 'ind-alt-tag-manager' ); ?></p>
                <code class="ind-alt-endpoint">https://plugins.becomeindelible.com/api/v1/indelible-alt-tags/check</code>
            </div>
        </div>
    </div>
    <?php
}

/**
 * AJAX handler for manual update check.
 *
 * @since 1.1.0
 * @return void
 */
function ind_alt_tag_manager_manual_update_check() {
    check_ajax_referer( 'ind_alt_tag_manager_settings_nonce', 'nonce', true );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Insufficient permissions.', 'ind-alt-tag-manager' ),
        ) );
    }

    // Record the check time
    update_option( 'ind_alt_tag_manager_last_manual_check', current_time( 'timestamp' ) );

    // Build the API URL
    $api_url = add_query_arg(
        array(
            'version'     => IND_ALT_TAG_MANAGER_VERSION,
            'site_url'    => get_site_url(),
            'wp_version'  => get_bloginfo( 'version' ),
            'php_version' => phpversion(),
        ),
        'https://plugins.becomeindelible.com/api/v1/indelible-alt-tags/check'
    );

    // Make the request (not cached)
    $response = wp_remote_get(
        $api_url,
        array( 'timeout' => 30 )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array(
            'message' => $response->get_error_message(),
            'raw'     => null,
        ) );
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $body          = wp_remote_retrieve_body( $response );
    $data          = json_decode( $body );

    // Store the response in transient for WordPress update checks
    if ( is_object( $data ) && ! empty( $data->version ) ) {
        set_transient( 'ind_alt_tag_manager_update_data', $data, DAY_IN_SECONDS );
    }

    // Determine update status
    $update_available = false;
    $new_version      = '';
    if ( is_object( $data ) && isset( $data->version ) ) {
        $update_available = version_compare( $data->version, IND_ALT_TAG_MANAGER_VERSION, '>' );
        $new_version      = $data->version;
    }

    wp_send_json_success( array(
        'update_available' => $update_available,
        'current_version'  => IND_ALT_TAG_MANAGER_VERSION,
        'new_version'      => $new_version,
        'response_code'    => $response_code,
        'raw_response'     => $data,
        'last_check'       => human_time_diff( current_time( 'timestamp' ), current_time( 'timestamp' ) ) . ' ago',
    ) );
}
add_action( 'wp_ajax_ind_alt_tag_manager_manual_update_check', 'ind_alt_tag_manager_manual_update_check' );
