<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\AI_Assistant\Ajax_Functions
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process an AI prompt
 *
 * @since   1.0.0
 */
function gamipress_ai_assistant_ajax_process_prompt() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( gamipress_get_manager_capability() ) )
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );

    // Check prompt
    $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( $_POST['prompt'] ) : '';

    if( empty( $prompt ) )
        wp_send_json_error( __( 'Please, type something.', 'gamipress' ) );

    // Check if supports AI
    $supports_ai = function_exists('wp_supports_ai') && (bool) call_user_func('wp_supports_ai');

    if( ! $supports_ai )
        wp_send_json_error( __( 'This assistant requires an AI connector in order to work, please configure at least one.', 'gamipress' ) );

    // Check model
    $model = isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : '';

    if( ! empty( $model ) ) {
        $models = gamipress_ai_assistant_get_models_ids();

        if( ! in_array( $model, $models ) )
            $model = '';
    }

    try {
        $user_message = \WordPress\AiClient\Messages\DTO\Message::fromArray( array(
            'role'  => 'user',
            'parts' => array(
                array(
                    'channel' => 'content',
                    'type'    => 'text',
                    'text'    => $prompt,
                ),
            ),
        ) );
    } catch ( \Exception $e ) {
        wp_send_json_error( __( 'Could not process your prompt.', 'gamipress' ) );
    }

    // Sanitize history
    $history = isset( $_POST['history'] ) && is_array( $_POST['history'] ) ? $_POST['history'] : array();
    $history_messages = array();

    foreach( $history as $prev ) {
        // Skip invalid entries
        if( ! isset( $prev['text'] ) || ! isset( $prev['author'] ) ) continue;

        $prev_role = sanitize_text_field( $prev['author'] );
        $prev_text = sanitize_text_field( $prev['text'] );

        if( empty( $prev_text ) ) continue;

        try {
            $history_messages[] = \WordPress\AiClient\Messages\DTO\Message::fromArray( array(
                'role'  => $prev_role === 'user' ? 'user' : 'model',
                'parts' => array(
                    array(
                        'channel' => 'content',
                        'type'    => 'text',
                        'text'    => $prev_text,
                    ),
                ),
            ) );
        } catch ( \Exception $e ) {
            // Do not report this error
            // wp_send_json_error( __( 'Could not process history messages.', 'gamipress' ) );
        }
    }

    $history_messages[] = $user_message;

    $abilities = gamipress_ai_assistant_get_abilities();

    $prompt = wp_ai_client_prompt()
        ->using_system_instruction( gamipress_ai_assistant_get_system_instructions() )
        ->with_history( ...$history_messages )
        ->using_abilities( ...$abilities );

    if( ! empty( $model ) )
        $prompt->using_model_preference( $model );

    $resolver = new WP_AI_Client_Ability_Function_Resolver( ...$abilities );

    // Bail if no models found
    if ( ! $prompt->is_supported_for_text_generation() )
        wp_send_json_error( sprintf( __( 'No models found that support %s for this prompt.', 'gamipress' ), __( 'text generation', 'gamipress' ) ) );

    $call = 0;
    /**
     * Filter available to modify the maximum ability calls
     *
     * @since 1.0.0
     *
     * @param integer $max_calls
     *
     * @return integer
     */
    $max_calls = apply_filters( 'gamipress_ai_assistant_max_ability_calls', 5 );
    $response = '';

    while ( $call < $max_calls ) {

        $result = $prompt->generate_result();

        // Bail if is a WP_Error
        if( is_wp_error( $result ) ) {

            // If not is the first call, and we have previous success, send previous messages
            if( $response !== '' )
                wp_send_json_success( $response );

            wp_send_json_error( $result->get_error_message() );

        }

        $message = $result->toMessage();

        if( $response !== '' )
            $response .= "\n";

        try {
            $response .= $result->toText();
        } catch ( \Exception $e ) {
            // Do not report this error
            // wp_send_json_error( array( 'message' => __( 'Could not process AI result.', 'gamipress' ) ) );
        }

        // Check if there are abilities to execute
        if ( $resolver->has_ability_calls( $message ) ) {
            $response_message = $resolver->execute_abilities( $message );

            $function_responses = gamipress_ai_assistant_get_all_function_responses( $response_message );

            $glue = count( $function_responses ) > 1 ? "\n" : ' ';

            foreach( $function_responses as $i => $function_response ) {

                $g = ( $i === 0 && strlen( $response ) === 0 ? '' : $glue );

                // Function response needs to match our output_schema ( array( 'success' => bool, 'message' => '' )
                if( isset( $function_response['message'] ) )
                    $response .= $g . $function_response['message'];
            }

            // For ability calls, after process the call rebuild the prompt
            $history_messages[] = $response_message;

            // Rebuild the prompt to call it again
            $prompt = wp_ai_client_prompt()
                ->using_system_instruction( gamipress_ai_assistant_get_system_instructions() )
                ->with_history( ...$history_messages )
                ->using_abilities( ...$abilities );

            if( ! empty( $model ) )
                $prompt->using_model_preference( $model );

            $call++;
            continue;
        }

        // Send back a successful response
        wp_send_json_success( $response );

    }

}
add_action( 'wp_ajax_gamipress_ai_assistant_process_prompt', 'gamipress_ai_assistant_ajax_process_prompt' );