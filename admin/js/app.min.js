jQuery(document).ready(function( $ ) {
    const i18n = ind_alt_tag_manager_admin_ajax.i18n;

    $('body').on('click', '.ind-alt-tag-img', function(){
        let url = $(this).attr('src');
        let $img = $('<img>', {
            'class': 'ind-alt-modal-img',
            'src': url
        });
        ind_alt_tag_modal($img);
    });

    $('body').on('click', '#ind-alt-tag-manager-admin-update-form', function(e){
        e.preventDefault();
        let text = $(this).text();
        if(text === 'Close Form'){
            $(this).text('Update Form');
        }else{
            $(this).text('Close Form');
        }
        $('.ind-alt-tag-manager-update-form-container').slideToggle();
    });

    // Save on button click
    $('body').on('click', '.ind-alt-tag-save', function(){
        saveAltTag($(this));
    });

    // Save on Enter key press in input field
    $('body').on('keypress', '.ind-alt-tag-alt', function(e){
        if (e.which === 13) { // Enter key
            e.preventDefault();
            let id = $(this).attr('id').replace('ind-alt-tag-alt', '');
            $('.ind-alt-tag-save[data-id="' + id + '"]').trigger('click');
        }
    });

    // Function to save alt tag
    function saveAltTag($button){
        let id = $button.data('id');
        let alt = $('#ind-alt-tag-alt' + id).val();

        $.ajax({
            url: ind_alt_tag_manager_admin_ajax.ajaxurl,
            dataType: 'json',
            method: "POST",
            data: {
                action: 'ind_alt_tag_manager_update_alt_tag',
                data: {'id':id, 'alt':alt},
                nonce: ind_alt_tag_manager_admin_ajax.ind_alt_tag_manager_admin_nonce
            }
        }).done(function(response){
            if(response.success && response.data.updated){
                // Find the parent container and fade it out, then remove
                let $container = $button.closest('.ind-alt-tag-single');
                $container.fadeOut(400, function() {
                    $(this).remove();

                    // Check if container is now empty
                    if ($('.ind-alt-tag-single').length === 0) {
                        // Reload the page to show the "congratulations" message
                        location.reload();
                    }
                });
            }else{
                alert(i18n.saveFailed);
            }
        }).fail(function(){
            alert(i18n.errorGeneric);
        });
    }

    // load more - Server-side pagination
    $('body').on('click', '.ind-alt-tag-more', function(){
        let $button = $(this);
        let currentPage = $button.data('page');
        let perPage = $button.data('per-page');
        let total = $button.data('total');
        let nextPage = currentPage + 1;

        // Disable button during request
        $button.prop('disabled', true).text(i18n.loading);

        $.ajax({
            url: ind_alt_tag_manager_admin_ajax.ajaxurl,
            dataType: 'json',
            method: "POST",
            data: {
                action: 'ind_alt_tag_manager_load_more_images',
                page: nextPage,
                per_page: perPage,
                nonce: ind_alt_tag_manager_admin_ajax.ind_alt_tag_manager_admin_nonce
            }
        }).done(function(response){
            if(response.success && response.data.html){
                // Append new images to container
                $('.ind-alt-tag-manager-container').append(response.data.html);

                // Update button state
                $button.data('page', nextPage);
                $button.prop('disabled', false).text(i18n.loadMore);

                // Calculate if more pages exist
                let loadedCount = (nextPage * perPage);
                if(loadedCount >= total){
                    $button.remove();
                }
            }else{
                alert(i18n.noMoreImages);
                $button.remove();
            }
        }).fail(function(){
            alert(i18n.loadError);
            $button.prop('disabled', false).text(i18n.loadMore);
        });
    });

    // modal

    $('body').on('click', '.ind-alt-modal-close', function(){
        $('.ind-alt-modal-container').remove();
    });

    $('body').on('click', '.ind-alt-modal-container', function(e){
        if($(e.target).hasClass('ind-alt-modal-container')){
            $('.ind-alt-modal-container').remove();
        }
    });

    // Close modal on Escape key
    $(document).on('keydown', function(e){
        if(e.key === 'Escape'){
            $('.ind-alt-modal-container').remove();
        }
    });

    function ind_alt_tag_modal($content){
        let $modal = $('<div>', {'class': 'ind-alt-modal-container'})
            .append(
                $('<div>', {'class': 'ind-alt-modal-inside-container'})
                    .append($('<button>', {'class': 'ind-alt-modal-close', text: 'X'}))
                    .append($content)
            );
        $('body').append($modal);
    }

});
