<?php
/**
 * Utilities
 *
 * @package     GamiPress\AI_Assistant\Utilities
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the list of available models
 *
 * @since	    1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_models() {

    $models = array();

    if( class_exists( 'WordPress\AiClient\AiClient' ) ) {
        $registry = WordPress\AiClient\AiClient::defaultRegistry();

        $requirements = new WordPress\AiClient\Providers\Models\DTO\ModelRequirements(
            array( WordPress\AiClient\Providers\Models\Enums\CapabilityEnum::textGeneration() ),
            array()
        );

        foreach( $registry->findModelsMetadataForSupport( $requirements ) as $result ) {
            $provider = $result->getProvider();
            $provider_id = $provider->getId();

            $models[$provider_id] = array(
                'name' => $provider->getName(),
                'models' => array(),
            );

            foreach( $result->getModels() as $model ) {
                $models[$provider_id]['models'][$model->getId()] = $model->getName();
            }
        }
    }

    return $models;

}

/**
 * Get models IDs
 *
 * @since	    1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_models_ids() {

    $providers = gamipress_ai_assistant_get_models();
    $models_ids = array();

        foreach( $providers as $provider ) {


            foreach( $provider['models'] as $id => $name ) {
                $models_ids[] = $id;
            }
        }


    return $models_ids;

}

/**
 * Get a function response from a message
 *
 * @since   1.0.0
 *
 * @param \WordPress\AiClient\Messages\DTO\Message $message
 *
 * @return mixed|false
 */
function gamipress_ai_assistant_get_function_response( $message ) {
    $array = $message->toArray();
    $response = false;

    if(
        isset( $array['parts'] )
        && isset( $array['parts'][0] )
        && isset( $array['parts'][0]['functionResponse'] )
        && isset( $array['parts'][0]['functionResponse']['response'] )
    ) {
        $response = $array['parts'][0]['functionResponse']['response'];
    }

    return $response;

}

/**
 * Get all function responses from a message
 *
 * @since   1.0.0
 *
 * @param \WordPress\AiClient\Messages\DTO\Message $message
 *
 * @return array
 */
function gamipress_ai_assistant_get_all_function_responses( $message ) {
    $array = $message->toArray();
    $responses = array();

    if( isset( $array['parts'] ) && is_array( $array['parts'] ) ) {
        foreach( $array['parts'] as $part ) {
            if( isset( $part['functionResponse'] )
                && isset( $part['functionResponse']['response'] ) ) {
                $responses[] = $part['functionResponse']['response'];
            }
        }
    }

    return $responses;

}