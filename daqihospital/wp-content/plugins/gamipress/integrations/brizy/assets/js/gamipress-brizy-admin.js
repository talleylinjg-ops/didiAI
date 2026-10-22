(function( $ ) {

    // Listen for our change to our trigger type selectors
    $('.requirements-list').on( 'change', '.select-trigger-type', function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).val();
        var form_selector = $(this).siblings('.select-brizy-form');

        if( trigger_type === 'gamipress_brizy_specific_new_form_submission'
            || trigger_type === 'gamipress_brizy_specific_field_value_submission' ) {
            form_selector.show();
        } else {
            form_selector.hide();
        }

        var field_name_input = $(this).siblings('.brizy-field-name');
        var field_value_input = $(this).siblings('.brizy-field-value');

        if( trigger_type === 'gamipress_brizy_field_value_submission'
            || trigger_type === 'gamipress_brizy_specific_field_value_submission' ) {
            field_name_input.show();
            field_value_input.show();
        } else {
            field_name_input.hide();
            field_value_input.hide();
        }

    });

    // Loop requirement list items to show/hide amount input on initial load
    $('.requirements-list li').each(function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).find('.select-trigger-type').val();
        var form_selector = $(this).find('.select-brizy-form');

        if( trigger_type === 'gamipress_brizy_specific_new_form_submission'
            || trigger_type === 'gamipress_brizy_specific_field_value_submission' ) {
            form_selector.show();
        } else {
            form_selector.hide();
        }

        var field_name_input = $(this).find('.brizy-field-name');
        var field_value_input = $(this).find('.brizy-field-value');

        if( trigger_type === 'gamipress_brizy_field_value_submission'
            || trigger_type === 'gamipress_brizy_specific_field_value_submission' ) {
            field_name_input.show();
            field_value_input.show();
        } else {
            field_name_input.hide();
            field_value_input.hide();
        }

    });

    $('.requirements-list').on( 'update_requirement_data', '.requirement-row', function(e, requirement_details, requirement) {

        if( requirement_details.trigger_type === 'gamipress_brizy_specific_new_form_submission'
            || requirement_details.trigger_type === 'gamipress_brizy_specific_field_value_submission') {
            requirement_details.brizy_form = requirement.find( '.select-brizy-form' ).val();
        }

        if( requirement_details.trigger_type === 'gamipress_brizy_field_value_submission'
            || requirement_details.trigger_type === 'gamipress_brizy_specific_field_value_submission' ) {
            requirement_details.brizy_field_name = requirement.find( '.brizy-field-name input' ).val();
            requirement_details.brizy_field_value = requirement.find( '.brizy-field-value input' ).val();
        }

    });

})( jQuery );