var gamipress_ai_assistant_history = [];
var gamipress_ai_assistant_md_parser;

(function ( $ ) {

    // Init Showdown JS
    gamipress_ai_assistant_md_parser = new showdown.Converter({
        smartIndentationFix: true,
        disableForced4SpacesIndentedSublists: true,
        simpleLineBreaks: true,
    });

    // Click on the assistant bubble
    $('body').on( 'click', '.gamipress-ai-assistant-bubble', function(e){
        e.preventDefault();

        var bubble = $(this);
        var assistant = bubble.closest('.gamipress-ai-assistant');
        var chat = assistant.find('.gamipress-ai-assistant-chat');
        var help = assistant.find('.gamipress-ai-assistant-help');

        // Toggle chat
        if( chat.data('status') === 'close' ) {
            chat.show();
            chat.data('status', 'open');
            bubble.removeClass('cmb-tooltip');

            // First open
            if( ! chat.find('.gamipress-ai-assistant-message').length )
                gamipress_ai_assistant_add_message( 'model', gamipress_ai_assistant.i18n.first_message );
        } else {
            chat.hide();
            chat.data('status', 'close');
            bubble.addClass('cmb-tooltip');

            // Close help if open
            if( help.data('status') === 'open' ) {
                help.hide();
                help.data('status', 'close');
            }
        }
    });

    // Click on chat close
    $('body').on( 'click', '.gamipress-ai-assistant-chat-close-button', function(e){
        e.preventDefault();

        var button = $(this);
        var assistant = button.closest('.gamipress-ai-assistant');
        var bubble = assistant.find('.gamipress-ai-assistant-bubble');

        bubble.trigger('click');
    });

    // Click on submit
    $('body').on( 'submit', '.gamipress-ai-assistant-chat-form', function(e){
        e.preventDefault();
        gamipress_ai_assistant_send_user_message();
    });

    // Click ENTER on input (Shift + ENTER grows the textarea)
    $('body').on( 'keypress', '.gamipress-ai-assistant-chat-input', function(e){
        var keycode = ( e.keyCode ? e.keyCode : e.which );
        var $this = $(this);

        if(  keycode === 13 && e.shiftKey )
            $this.attr('rows', parseInt( $(this).attr('rows') ) + 1 );

        if( keycode !== 13  ) return;
        if( e.shiftKey ) return;
        e.preventDefault();

        gamipress_ai_assistant_send_user_message();
    });

    // Change model select
    $('body').on( 'change', '.gamipress-ai-assistant-chat-model-input', function(e){
        e.preventDefault();

        var text = $(this).find('option:selected').text();
        var $aux = $('<select/>').append( $('<option/>').text( text ) );

        $(this).after( $aux );
        $(this).width( $aux.width() + 10 );
        $aux.remove();
    });

    // Click on help
    $('body').on( 'click', '.gamipress-ai-assistant-chat-help-button', function(e){
        e.preventDefault();

        var button = $(this);
        var assistant = button.closest('.gamipress-ai-assistant');
        var help = assistant.find('.gamipress-ai-assistant-help');

        if( help.data('status') === 'close' ) {
            help.show();
            help.data('status', 'open');
        } else {
            help.hide();
            help.data('status', 'close');
        }

    });

    // Click on send prompt
    $('body').on( 'click', '.gamipress-ai-assistant-send-prompt', function(e){
        e.preventDefault();

        var $this = $(this);
        var prompt = $this.data('prompt');

        var assistant = $('.gamipress-ai-assistant');
        var chat = assistant.find('.gamipress-ai-assistant-chat');
        var input = assistant.find('.gamipress-ai-assistant-chat-input');

        // Bail if input was disabled for something
        if( input.prop('disabled') ) return;

        if( chat.data('status') === 'close' ) {
            var bubble = assistant.find('.gamipress-ai-assistant-bubble');
            bubble.trigger('click');
        }

        input.val( prompt );

        if( $this.data('send') )
            gamipress_ai_assistant_send_user_message();

    });

    // Click on help ability (closed)
    $('body').on( 'click', '.gamipress-ai-assistant-help-ability-close .gamipress-ai-assistant-help-ability-label', function(e){
        e.preventDefault();

        var $this = $(this);
        var ability = $this.parent('.gamipress-ai-assistant-help-ability');
        var opened = $('.gamipress-ai-assistant-help-ability-open');

        if( opened ) {
            opened
                .removeClass('gamipress-ai-assistant-help-ability-open')
                .addClass('gamipress-ai-assistant-help-ability-close')
                .find('.gamipress-ai-assistant-help-ability-desc').slideUp('fast');
        }

        ability
            .removeClass('gamipress-ai-assistant-help-ability-close')
            .addClass('gamipress-ai-assistant-help-ability-open')
            .find('.gamipress-ai-assistant-help-ability-desc').slideDown('fast');


    });

    // Click on help ability (opened)
    $('body').on( 'click', '.gamipress-ai-assistant-help-ability-open .gamipress-ai-assistant-help-ability-label', function(e){
        e.preventDefault();

        var $this = $(this);
        var ability = $this.parent('.gamipress-ai-assistant-help-ability');

        ability
            .removeClass('gamipress-ai-assistant-help-ability-open')
            .addClass('gamipress-ai-assistant-help-ability-close')
            .find('.gamipress-ai-assistant-help-ability-desc').slideUp('fast');


    });

})( jQuery );

/**
 * Send user message to ajax
 *
 * @since 1.0.0
 */
function gamipress_ai_assistant_send_user_message() {

    var $ = $ || jQuery;
    var loading = $('.gamipress-ai-assistant-loading-message');

    // Bail if AI already loading
    if( loading.length ) return;

    var input = $('.gamipress-ai-assistant-chat-input');
    var model = $('.gamipress-ai-assistant-chat-model-input').val();

    if( input.prop('disabled') ) return;

    // Restore input height
    input.attr('rows', 1 );

    var prompt = input.val();

    // Prevent user HTML input
    prompt = prompt.replaceAll( '<', '&lt;' );
    prompt = prompt.replaceAll( '>', '&gt;' );

    if( prompt.length === 0 ) return;

    input.prop('disabled', true);

    gamipress_ai_assistant_set_face('loading');
    gamipress_ai_assistant_add_message( 'user', prompt );

    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'gamipress_ai_assistant_process_prompt',
            nonce: gamipress_ai_assistant.nonce,
            prompt: prompt,
            model: model,
            history: gamipress_ai_assistant_history,
        },
        success: function(r) {
            input.prop('disabled', false);

            gamipress_ai_assistant_set_face('talking');

            var chat_message = $('.gamipress-ai-assistant-message.gamipress-ai-assistant-loading-message');
            chat_message.removeClass('gamipress-ai-assistant-loading-message');

            // Add the original message to history
            gamipress_ai_assistant_history.push( {
                author: 'model',
                text: r.data
            } );

            // Parse any possible markdown
            var html = gamipress_ai_assistant_md_parser.makeHtml( r.data );

            // Remove "thinking" message
            chat_message.html( '' );

            if( ! r.success ) {
                chat_message.addClass('gamipress-ai-assistant-message-error');
                gamipress_ai_assistant_type( chat_message, html );
                return;
            }

            gamipress_ai_assistant_type( chat_message, html );

        },
        error: function(r) {
            input.prop('disabled', false);

            gamipress_ai_assistant_set_face('error');

            var chat_message = $('.gamipress-ai-assistant-message.gamipress-ai-assistant-loading-message');
            chat_message.removeClass('gamipress-ai-assistant-loading-message');

            chat_message.addClass('gamipress-ai-assistant-message-error');
            chat_message.html( gamipress_ai_assistant.i18n.error_message );

            console.log(r);
        }
    });

    gamipress_ai_assistant_history.push( {
        author: 'user',
        text: prompt
    } );

    input.val('');

    setTimeout( () => gamipress_ai_assistant_add_message( 'model',  gamipress_ai_assistant.i18n.loading ), 300)


}

/**
 * Add message to the chat history
 *
 * @since 1.0.0
 *
 * @param String author
 * @param String text
 */
function gamipress_ai_assistant_add_message( author, text ) {

    var $ = $ || jQuery;

    if( ! text.length ) return;

    var history =  $('.gamipress-ai-assistant-chat-history');
    var id = 'gamipress-ai-assistant-message-' + history.find('.gamipress-ai-assistant-message').length + 1;
    var css_class = 'gamipress-ai-assistant-message';

    if( text === gamipress_ai_assistant.i18n.loading ) {
        css_class += ' gamipress-ai-assistant-loading-message'
    }

    var type_text = '';

    if( author === 'model' ) {
        type_text = text;
        text = '';
    }

    history
        .append( '<div id="' + id + '" class="' + css_class + '" data-author="' + author + '">' + text + '</div>' );

    if( type_text.length )
        gamipress_ai_assistant_type( history.find('.gamipress-ai-assistant-message#' + id), type_text );
}

// Typing effect vars
var gamipress_ai_assistant_typing_text = '';
var gamipress_ai_assistant_typing_speed = 20;
var gamipress_ai_assistant_typing_in_html = false;

/**
 * Typing effect
 *
 * @since 1.0.0
 *
 * @param Object target
 * @param String text
 * @param Integer i
 * @param function callback
 */
function gamipress_ai_assistant_type( target, text, i = 0, callback ) {

    var $ = $ || jQuery;

    if( i === 0 ) {
        if( text !== gamipress_ai_assistant.i18n.loading )
            gamipress_ai_assistant_set_face('talking');

        // Save the original text and strip the text to be written in chat
        gamipress_ai_assistant_typing_text = text;

        var text_length = gamipress_ai_assistant_strip_html( text ).length;

        // Define type speed based on text length
        if( text_length > 500 ) gamipress_ai_assistant_typing_speed = 2;
        else if( text_length > 200 ) gamipress_ai_assistant_typing_speed = 5;
        else if( text_length > 100 ) gamipress_ai_assistant_typing_speed = 10;
        else gamipress_ai_assistant_typing_speed = 20;
    }

    var char = text.slice( 0, i );

    target[0].innerHTML = char;

    var chat_history = target.closest('.gamipress-ai-assistant-chat-history');
    chat_history[0].scrollTo(0, chat_history[0].scrollHeight);

    if ( i < text.length - 1 ) {
        var prev_char = char.slice(-1);

        // HTML start
        if( ! gamipress_ai_assistant_typing_in_html && prev_char === '<' )
            gamipress_ai_assistant_typing_in_html = true;

        // HTML end
        if( gamipress_ai_assistant_typing_in_html && prev_char === '>' )
            gamipress_ai_assistant_typing_in_html = false;

        if( gamipress_ai_assistant_typing_in_html )
            return gamipress_ai_assistant_type( target, text, i + 1 );

        setTimeout(() => gamipress_ai_assistant_type( target, text, i + 1, callback ), gamipress_ai_assistant_typing_speed );
    } else {
        target.html( gamipress_ai_assistant_typing_text );

        gamipress_ai_assistant_typing_in_html = false;

        if( text !== gamipress_ai_assistant.i18n.loading )
            gamipress_ai_assistant_set_face('idle');

        if (typeof callback == 'function') callback();
    }

}

/**
 * Strip HTML from text
 *
 * @since 1.0.0
 *
 * @param String text
 *
 * @returns {String}
 */
function gamipress_ai_assistant_strip_html( text ) {
    // Bail if text does not have any HTML
    if( ! gamipress_ai_assistant_has_html( text ) ) return text;

    var doc = new DOMParser().parseFromString(text, 'text/html');
    return doc.body.textContent || '';
}

/**
 * Check if text has HTML
 *
 * @since 1.0.0
 *
 * @param String text
 *
 * @returns {boolean}
 */
function gamipress_ai_assistant_has_html( text ) {
    return( /<\/?[a-z][\s\S]*>/i.test( text ) );
}

/**
 * Set face status
 *
 * @since 1.0.0
 *
 * @param String status
 */
function gamipress_ai_assistant_set_face( status ) {
    jQuery('.gamipress-ai-assistant-face').attr('data-status', status);
}