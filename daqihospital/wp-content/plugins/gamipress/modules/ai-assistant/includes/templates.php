<?php
/**
 * Templates
 *
 * @package     GamiPress\AI_Assistant\Templates
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add custom footer to the admin dashboard
 *
 * @since	    1.0.0
 */
function gamipress_ai_assistant_admin_footer() {
    if( gamipress_ai_assistant_is_valid_page() ) gamipress_ai_assistant_render();
}
add_action( 'admin_footer', 'gamipress_ai_assistant_admin_footer' );

/**
 * Render AI assistant
 *
 * @since	    1.0.0
 */
function gamipress_ai_assistant_render() {

    // Bail if disabled
    if( (bool) gamipress_get_option( 'disable_ai_assistant', false ) ) {
        return false;
    }

    ?>

    <div class="gamipress-ai-assistant">

        <div class="gamipress-ai-assistant-help" data-status="close" style="display: none;">
            <?php gamipress_ai_assistant_render_help(); ?>
        </div>

        <div class="gamipress-ai-assistant-chat" data-status="close" style="display: none;">

            <div class="gamipress-ai-assistant-chat-top">
                <div class="gamipress-ai-assistant-chat-title">
                    <span>
                        <?php echo gamipress_dashicon( 'gamipress' ); ?>
                        <?php esc_html_e( 'GamiPress', 'gamipress' ); ?>
                    </span>
                    <?php esc_html_e( 'AI Assistant', 'gamipress' ); ?>
                </div>
                <div class="gamipress-ai-assistant-chat-help-button">
                    <span class="cmb-tooltip">
                        <span class="dashicons dashicons-cmb-tooltip"></span>
                        <span class="cmb-tooltip-desc cmb-tooltip-top"><?php esc_html_e( 'Click to get help', 'gamipress' ); ?></span>
                    </span>
                </div>

                <div class="gamipress-ai-assistant-chat-close-button">
                    <span class="cmb-tooltip">
                        <span class="dashicons dashicons-no-alt"></span>
                        <span class="cmb-tooltip-desc cmb-tooltip-top"><?php esc_html_e( 'Close', 'gamipress' ); ?></span>
                    </span>
                </div>
            </div>

            <div class="gamipress-ai-assistant-chat-history"></div>

            <div class="gamipress-ai-assistant-chat-bottom">
                <form action="" class="gamipress-ai-assistant-chat-form">
                    <textarea rows="1" id="gamipress-ai-assistant-chat-input" class="gamipress-ai-assistant-chat-input" placeholder="<?php esc_html_e( 'Ask anything...', 'gamipress' ); ?>"></textarea>
                    <button type="submit" class="gamipress-ai-assistant-chat-input-submit gamipress-ai-assistant-button gamipress-ai-assistant-button-primary">
                        <i class="gamipress-ai-assistant-icon gamipress-ai-assistant-icon-send"></i>
                    </button>
                    <?php gamipress_ai_assistant_render_model_select(); ?>
                </form>
            </div>

        </div>

        <div class="gamipress-ai-assistant-bubble cmb-tooltip">
            <div class="cmb-tooltip-desc cmb-tooltip-left"><?php esc_html_e( 'AI Assistant', 'gamipress' ); ?></div>
            <?php gamipress_ai_assistant_render_face(); ?>
        </div>

    </div>
    <?php
}

/**
 * Render models select
 *
 * @since	    1.0.0
 */
function gamipress_ai_assistant_render_model_select() {

    $providers = gamipress_ai_assistant_get_models();

    if( ! count( $providers ) ) return;

    ?>

    <select id="gamipress-ai-assistant-chat-model-input" class="gamipress-ai-assistant-chat-model-input">
        <option value=""><?php esc_html_e( 'Default Model', 'gamipress' ); ?></option>
        <?php foreach( $providers as $provider ) : ?>
            <optgroup label="<?php echo esc_attr( $provider['name'] ); ?>">
                <?php foreach( $provider['models'] as $model => $name ) : ?>
                    <option value="<?php echo esc_attr( $model ); ?>"><?php echo esc_html( $name ); ?></option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>

    <?php

}

/**
 * Render AI assistant face
 *
 * @since	    1.0.0
 */
function gamipress_ai_assistant_render_face() {
    $url = GAMIPRESS_AI_ASSISTANT_URL . 'assets/img/'; ?>

    <div class="gamipress-ai-assistant-face" data-status="idle">
        <span class="gamipress-ai-assistant-head">
           <img src="<?php echo esc_attr( $url . 'head.svg' ); ?>" alt="" data-key="head-01">
        </span>
        <span class="gamipress-ai-assistant-eyes">
            <img src="<?php echo esc_attr( $url . 'eyes-idle.svg' ); ?>" alt="" data-key="eyes-idle">
            <img src="<?php echo esc_attr( $url . 'eyes-happy.svg' ); ?>" alt="" data-key="eyes-happy">
            <img src="<?php echo esc_attr( $url . 'eyes-error.svg' ); ?>" alt="" data-key="eyes-error">
            <img src="<?php echo esc_attr( $url . 'eyes-loading.svg' ); ?>" alt="" data-key="eyes-loading">
        </span>
        <span class="gamipress-ai-assistant-mouth">
            <img src="<?php echo esc_attr( $url . 'mouth-idle.svg' ); ?>" alt="" data-key="mouth-idle">
            <img src="<?php echo esc_attr( $url . 'mouth-smile.svg' ); ?>" alt="" data-key="mouth-smile">
            <img src="<?php echo esc_attr( $url . 'mouth-happy.svg' ); ?>" alt="" data-key="mouth-happy">
            <img src="<?php echo esc_attr( $url . 'mouth-error.svg' ); ?>" alt="" data-key="mouth-error">
        </span>
    </div>

    <?php
}