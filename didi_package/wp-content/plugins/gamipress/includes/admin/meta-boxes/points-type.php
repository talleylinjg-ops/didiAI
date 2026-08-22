<?php
/**
 * Points Type Meta Boxes
 *
 * @package     GamiPress\Admin\Meta_Boxes\Points_Type
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.4.7
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register points type meta boxes
 *
 * @since 1.0.0
 */
function gamipress_points_type_meta_boxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_';

    // Check if points types are public
    $public_points_type = apply_filters( 'gamipress_public_points_type', false );

    // Points Type Data
    gamipress_add_meta_box(
        'points-type-data',
        __( 'Points Type Data', 'gamipress' ),
        'points-type',
        array(
            'post_title' => array(
                'name' 	=> __( 'Singular Name', 'gamipress' ),
                'tooltip'   => __( 'The singular name for this points type.', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' 	=> 'text_medium',
            ),
            $prefix . 'plural_name' => array(
                'name' 	=> __( 'Plural Name', 'gamipress' ),
                'tooltip'   => __( 'The plural name for this points type.', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' 	=> 'text_medium',
            ),
            'post_name' => array(
                'name' 	=> __( 'Slug', 'gamipress' ),
                'desc' 	=>  (( $public_points_type ) ? '<span class="gamipress-permalink hide-if-no-js">' . site_url() . '/<strong class="gamipress-post-name"></strong>/</span><br>' : '' ),
                'tooltip'   => __( 'Slug is used for internal references, as some shortcode attributes, to completely differentiate this points type from any other (leave blank to automatically generate one).', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' 	=> 'text_medium',
                'attributes' => array(
                    'maxlength' => 20
                )
            ),
        ),
        array( 'priority' => 'high', )
    );

    // Points Display Options
    gamipress_add_meta_box(
        'points-display-options',
        __( 'Points Display Options', 'gamipress' ),
        'points-type',
        array(
            $prefix . 'label_position' => array(
                'name' => __( 'Label Position', 'gamipress' ),
                'tooltip'   => __( 'Location of the points type label.', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'select',
                'options_cb' => 'gamipress_options_cb_points_label_position',
                'default' => 'after'
            ),
            $prefix . 'thousands_separator' => array(
                'name' => __( 'Thousands Separator', 'gamipress' ),
                'tooltip'   => __( 'The symbol (usually , or .) to separate thousands.', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'text_small',
                'default' => ''
            ),
            $prefix . 'html_display' => array(
                'name' => __( 'HTML Display', 'gamipress' ),
                'tooltip'   => __( 'Set how this points type should get displayed when HTML is allowed.', 'gamipress' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'radio',
                'classes' => 'gamipress-switch',
                'options_cb' => 'gamipress_options_cb_points_html_display',
                'default' => 'label_image_after'
            ),
        ),
        array( 'context'  => 'side', )
    );

}
add_action( 'gamipress_init_points-type_meta_boxes', 'gamipress_points_type_meta_boxes' );

// Options callback to return the points label position options using the current points type label
function gamipress_options_cb_points_label_position( $field ) {

    $plural = gamipress_get_points_type_plural( $field->object_id, true );

    return array(
        'after' => sprintf( __( 'After (10 %s)', 'gamipress' ), $plural ),
        'before' => sprintf( __( 'Before (%s 10)', 'gamipress' ), $plural ),
    );

}

// Options callback for the points html display option
function gamipress_options_cb_points_html_display( $field ) {

    $plural = gamipress_get_points_type_plural( $field->object_id, true );
    $image = gamipress_get_points_type_thumbnail( $field->object_id, 'gamipress-points', 'gamipress-points-thumbnail gamipress-points-thumbnail-inline' );

    if( ! $image ) {
        $image = '<img src="' . GAMIPRESS_URL . 'assets/badges/points/gamipress-star.png' . '" class="gamipress-points-thumbnail gamipress-points-thumbnail-inline" />';
    }

    $image_label = $image . ' ' . $plural;

    return array(
        'label_image_after' => sprintf( __( '10 %s', 'gamipress' ), $image_label ),
        'image_after' => sprintf( __( '10 %s', 'gamipress' ), $image ),
        'label_after' => sprintf( __( '10 %s', 'gamipress' ), $plural ),
        'label_image_before' => sprintf( __( '%s 10', 'gamipress' ), $image_label ),
        'image_before' => sprintf( __( '%s 10', 'gamipress' ), $image ),
        'label_before' => sprintf( __( '%s 10', 'gamipress' ), $plural ),
    );

}