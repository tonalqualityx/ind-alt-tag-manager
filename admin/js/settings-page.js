/**
 * Settings page JavaScript for Indelible Alt Tag Manager.
 *
 * Handles the manual update check functionality.
 *
 * @package Ind_Alt_Tag_Manager
 */

jQuery( document ).ready( function( $ ) {
    const i18n = ind_alt_tag_manager_settings_ajax.i18n;

    // Cache DOM elements
    const $checkBtn = $( '#ind-alt-check-updates-btn' );
    const $results = $( '#ind-alt-update-results' );
    const $spinner = $( '.ind-alt-spinner' );
    const $statusMessage = $( '#ind-alt-status-message' );
    const $updateAvailable = $( '#ind-alt-update-available' );
    const $upToDate = $( '#ind-alt-up-to-date' );
    const $rawResponse = $( '#ind-alt-raw-response' );
    const $lastCheckTime = $( '#ind-alt-last-check-time' );

    /**
     * Handle the "Check for Updates" button click.
     */
    $checkBtn.on( 'click', function() {
        // Reset UI state
        $results.removeClass( 'hidden' );
        $spinner.removeClass( 'hidden' );
        $updateAvailable.addClass( 'hidden' );
        $upToDate.addClass( 'hidden' );
        $statusMessage.text( i18n.checking );
        $checkBtn.prop( 'disabled', true ).text( i18n.checking );

        // Make AJAX request
        $.ajax({
            url: ind_alt_tag_manager_settings_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ind_alt_tag_manager_manual_update_check',
                nonce: ind_alt_tag_manager_settings_ajax.nonce,
            },
            dataType: 'json',
            timeout: 30000,
        })
        .done( function( response ) {
            $spinner.addClass( 'hidden' );
            $checkBtn.prop( 'disabled', false ).text( 'Check for Updates' );

            if ( response.success ) {
                const data = response.data;

                // Update last check time
                if ( $lastCheckTime.length ) {
                    $lastCheckTime.text( data.last_check );
                }

                // Display raw response for debugging
                $rawResponse.text( JSON.stringify( data.raw_response, null, 2 ) );

                // Show appropriate message based on update availability
                if ( data.update_available ) {
                    $statusMessage.text( i18n.updateAvailable );
                    $updateAvailable.removeClass( 'hidden' );
                    $( '#ind-alt-new-version' ).text( data.new_version );
                    $( '#ind-alt-current-version-display' ).text( data.current_version );
                } else {
                    $statusMessage.text( i18n.upToDate );
                    $upToDate.removeClass( 'hidden' );
                }
            } else {
                $statusMessage.text( response.data.message || i18n.errorGeneric );
                $rawResponse.text( JSON.stringify( response.data, null, 2 ) );
            }
        } )
        .fail( function( jqXHR, textStatus, errorThrown ) {
            $spinner.addClass( 'hidden' );
            $checkBtn.prop( 'disabled', false ).text( 'Check for Updates' );

            let errorMsg = i18n.errorGeneric;
            if ( textStatus === 'timeout' ) {
                errorMsg = 'Request timed out. Please try again.';
            } else if ( errorThrown ) {
                errorMsg = 'Error: ' + errorThrown;
            }

            $statusMessage.text( errorMsg );
            $rawResponse.text( 'Status: ' + textStatus + '\nError: ' + errorThrown + '\n\nResponse:\n' + JSON.stringify( jqXHR.responseText, null, 2 ) );
        } );
    } );

    /**
     * Toggle debug/raw response visibility.
     */
    $( '#ind-alt-toggle-debug' ).on( 'click', function() {
        $rawResponse.toggleClass( 'hidden' );
    } );
} );
