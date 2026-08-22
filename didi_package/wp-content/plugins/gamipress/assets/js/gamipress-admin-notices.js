(function ( $ ) {

    // Hide review notice
    $(document).on('click', '.gamipress-hide-review-notice', function(e) {

        e.preventDefault();

        // Hide the notice on success
        $('.gamipress-review-notice').slideUp('fast');

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'gamipress_hide_review_notice',
                nonce: gamipress_admin_notices.nonce,
            },
            success: function(response) {

            }
        });

    });

})( jQuery );