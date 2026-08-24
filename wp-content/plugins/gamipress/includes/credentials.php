<?php
/**
 * Credentials
 *
 * @package     GamiPress\Credentials
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       7.9.8
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Encode credential ID
 *
 * @since  7.9.8
 *
 * @param int $id
 *
 * @return string
 */
function gamipress_encode_credential_id( $id = 0 ) {

    $number = (int) $id ^ (int) GAMIPRESS_CRED_KEY;

    if ( $number === 0 ) return '0';

    $result = '';
    while ($number > 0) {
        $result = GAMIPRESS_CRED_SALT[$number % 62] . $result;
        $number = intdiv( $number, 62 );
    }

    return $result;
}

/**
 * Decode credential ID
 *
 * @since  7.9.8
 *
 * @param string $encoded
 *
 * @return int
 */
function gamipress_decode_credential_id( $encoded = '' ) {

    $number = 0;

    for ( $i = 0; $i < strlen( $encoded ); $i++ )
        $number = $number * 62 + strpos( GAMIPRESS_CRED_SALT, $encoded[$i] );

    return (int) $number ^ (int) GAMIPRESS_CRED_KEY;
}

/**
 * Get the credential from the URL.
 *
 * @since  7.9.8
 *
 * @return bool|stdClass False or Queried user earning
 */
function gamipress_get_the_credential() {
    global $gamipress_current_credential;

    $credential_id = isset( $_GET[GAMIPRESS_CRED_VAR] ) ? $_GET[GAMIPRESS_CRED_VAR] : '';
    $credential_id = gamipress_decode_credential_id( $credential_id );

    if( $credential_id === 0 ) return false;

    if( $gamipress_current_credential ) {
        // Global credential already setup
        return $gamipress_current_credential;
    }

    // Query the credential
    ct_setup_table( 'gamipress_user_earnings' );
    $user_earning = ct_get_object( $credential_id );
    ct_reset_setup_table();

    if( ! $user_earning ) return false;

    // Bail if credential post does not match with the current post
    if( absint( $user_earning->post_id ) !== absint( get_the_ID() ) ) return false;

    $gamipress_current_credential = $user_earning;

    return $user_earning;
}


/**
 * Build a credential URL.
 *
 * @since  7.9.8
 *
 * @param int      $post_id        Achievement or Rank ID, also accepts a User Earning object.
 * @param int      $user_id        User ID.
 *
 * @return string                  Credential URL or post permalink.
 */
function gamipress_get_credential_url( $post_id = 0, $user_id = 0 ) {

    $user_earning = null;

    // Check if function called as gamipress_get_credential_url( $user_earning )
    if( gettype( $post_id ) === 'object' && property_exists( $post_id, 'user_earning_id' ) ) {
        $user_earning = $post_id;
    }

    if( $user_earning === null ) {
        $user_earning = gamipress_get_last_earning( array( 'post_id' => $post_id, 'user_id' => $user_id ) );
    } else {
        $post_id = $user_earning->post_id;
        $user_id = $user_earning->user_id;
    }

    $url = get_the_permalink( $post_id );

    if( $user_earning ) {
        $value = gamipress_encode_credential_id( absint( $user_earning->user_earning_id ) );

        $url = add_query_arg( array( GAMIPRESS_CRED_VAR => $value ), $url );
    }

    /**
     * Available filter to override the credential URL
     *
     * @since  7.9.8
     *
     * @param  string  $html            The credential URL
     * @param  int     $post_id         Achievement or Rank ID
     * @param  int     $user_id         User ID
     * @param stdClass $user_earning    User earning (the credential)
     *
     * @return string                   Credential URL
     */
    return apply_filters( 'gamipress_credential_url', $url, $post_id, $user_id, $user_earning );

}

/**
 * Credential link
 *
 * @since  7.9.8
 *
 * @param int       $post_id        Achievement or Rank ID.
 * @param int       $user_id        User ID.
 *
 * @return string                   HTML Markup.
 */
function gamipress_get_credential_link( $post_id = 0, $user_id = 0 ) {

    $user_earning = null;

    // Check if function called as gamipress_get_credential_link( $user_earning )
    if( gettype( $post_id ) === 'object' && property_exists( $post_id, 'user_earning_id' ) ) {
        $user_earning = $post_id;
    }

    if( $user_earning === null ) {
        $user_earning = gamipress_get_last_earning( array( 'post_id' => $post_id, 'user_id' => $user_id ) );
    } else {
        $post_id = $user_earning->post_id;
        $user_id = $user_earning->user_id;
    }

    $url = gamipress_get_credential_url( $user_earning );

    /**
     * Available filter to override the credential link label
     *
     * @since  7.9.8
     *
     * @param string   $label           The credential link label
     * @param int      $post_id         Achievement or Rank ID
     * @param int      $user_id         User ID
     * @param stdClass $user_earning    User earning (the credential)
     *
     * @return string                   The credential link label
     */
    $label = apply_filters( 'gamipress_credential_link_label', __( 'View Credential', 'gamipress' ), $post_id, $user_id, $user_earning );

    $html = '<a href="' . esc_attr( $url ) . '" class="gamipress-credential-link">'
            . $label
        . '</a>';

    /**
     * Available filter to override the credential link HTML
     *
     * @since  7.9.8
     *
     * @param  string  $html            The credential link HTML
     * @param  int     $post_id         Achievement or Rank ID
     * @param  int     $user_id         User ID
     * @param stdClass $user_earning    User earning (the credential)
     *
     * @return string                   HTML Markup
     */
    return apply_filters( 'gamipress_credential_link', $html, $post_id, $user_id, $user_earning );

}

/**
 * Credentials links HTML (for single templates)
 *
 * @since  7.9.8
 *
 * @param int       $post_id        Achievement or Rank ID.
 * @param array     $args           Template args
 *
 * @return string                   HTML Markup.
 */
function gamipress_get_credentials_links_html( $post_id = 0, $args = array() ) {

    $credential = gamipress_get_the_credential();
    $user_id = get_current_user_id();

    if( ! isset( $args['user_id'] ) ) {
        $args['user_id'] = get_current_user_id();
    }
    $viewing_user_id = absint( $args['user_id'] );

    $links = '';

    if( ! $credential ) {
        // Links when not viewing a credential

        $user_earning = gamipress_get_last_earning( array( 'post_id' => $post_id, 'user_id' => $user_id ) );
        
        // Only render credential link if user is viewing its own achievement/rank
        if( $user_id === $viewing_user_id ) {
            // Show credential link if user has earned it
            if( $user_earning )
                $links .= gamipress_get_credential_link( $user_earning );
        }

        /**
         * Available filter to add credentials links when viewing a post (achievement or rank)
         *
         * @since  7.9.8
         *
         * @param string   $html            The credential link HTML
         * @param int      $post_id         Achievement or Rank ID
         * @param int      $user_id         User ID
         * @param array    $args            Template args
         * @param stdClass $credential      User earning (the credential) if viewing a credential
         *
         * @return string                   HTML Markup
         */
        $links = apply_filters( 'gamipress_credentials_links_on_post', $links, $post_id, $user_id, $args, $credential );
            
    } else {

        /**
         * Available filter to add credentials links when viewing a credential
         *
         * @since  7.9.8
         *
         * @param string   $html            The credential link HTML
         * @param int      $post_id         Achievement or Rank ID
         * @param int      $user_id         User ID
         * @param array    $args            Template args
         * @param stdClass $credential      User earning (the credential) if viewing a credential
         *
         * @return string                   HTML Markup
         */
        $links = apply_filters( 'gamipress_credentials_links_on_credential', $links, $post_id, $user_id, $args, $credential );

    }

    /**
     * Available filter to add credentials links HTML
     *
     * @since  7.9.8
     *
     * @param string   $html            The credential link HTML
     * @param int      $post_id         Achievement or Rank ID
     * @param int      $user_id         User ID
     * @param array    $args            Template args
     * @param stdClass $credential      User earning (the credential) if viewing a credential
     *
     * @return string                   HTML Markup
     */
    $links = apply_filters( 'gamipress_credentials_links', $links, $post_id, $user_id, $args, $credential );

    return '<div class="gamipress-credentials-links">' . $links . '</div>';

}

/**
 * Credential text used on single templates
 *
 * @since  7.9.8
 *
 * @param  int $post_id Achievement or Rank ID.
 * @param  int $user_id User ID.
 *
 * @return string       HTML Markup.
 */
function gamipress_get_credential_text( $post_id = 0, $user_id = 0 ) {

    $html = '';
    $user_earning = null;

    // Check if function called as gamipress_get_credential_text( $user_earning )
    if( gettype( $post_id ) === 'object' && property_exists( $post_id, 'user_earning_id' ) ) {
        $user_earning = $post_id;
    }

    if( $user_earning === null ) {
        $user_earning = gamipress_get_last_earning( array( 'post_id' => $post_id, 'user_id' => $user_id ) );
    } else {
        $post_id = $user_earning->post_id;
        $user_id = $user_earning->user_id;
    }

    $user = get_user_by( 'id', $user_id );

    if ( $user ) {

        // User display
        $user_url = get_author_posts_url( $user->ID );

        $user_display = '<a href="' . esc_attr( $user_url ) . '">'
            . get_avatar( $user->ID )
            . esc_html( $user->display_name )
        . '</a>';

        /**
         * Available filter to override the credential text user display
         *
         * @since  7.9.8
         *
         * @param  string  $user_display    The user display.
         * @param  int     $post_id         Achievement or Rank ID.
         * @param  int     $user_id         User ID.
         * @param stdClass $user_earning    User earning (the credential)
         *
         * @return string
         */
        $user_display = apply_filters( 'gamipress_credential_text_user_display', $user_display, $post_id, $user_id, $user_earning );

        // Type display
        $post_type = gamipress_get_post_type( $post_id );
        if( gamipress_is_achievement( $post_type ) ) {
            $type_display = gamipress_get_achievement_type_singular( $post_type, true );
        } else {
            $type_display = gamipress_get_rank_type_singular( $post_type, true );
        }

        /**
         * Available filter to override the credential text type display
         *
         * @since  7.9.8
         *
         * @param  string  $type_display    The type display.
         * @param  int     $post_id         Achievement or Rank ID.
         * @param  int     $user_id         User ID.
         * @param stdClass $user_earning    User earning (the credential)
         *
         * @return string
         */
        $type_display = apply_filters( 'gamipress_credential_text_type_display', $type_display, $post_id, $user_id, $user_earning );

        // Date display
        $date_display = '<strong>' . date_i18n( get_option( 'date_format' ), strtotime( $user_earning->date ) ) . '</strong>';

        /**
         * Available filter to override the credential text date display
         *
         * @since  7.9.8
         *
         * @param  string  $date_display    The date display.
         * @param  int     $post_id         Achievement or Rank ID.
         * @param  int     $user_id         User ID.
         * @param stdClass $user_earning    User earning (the credential)
         *
         * @return string
         */
        $date_display = apply_filters( 'gamipress_credential_text_date_display', $date_display, $post_id, $user_id, $user_earning );

        // translators: %1$s: User display name %2$s: Achievement or rank type (Badge, Quest) %3$s: Earned date
        $credential_text = sprintf( __( '%1$s earned this %2$s on %3$s', 'gamipress' ),
            $user_display,
            $type_display,
            $date_display
        );

        /**
         * Available filter to override the credential text
         *
         * @since  7.9.8
         *
         * @param  string  $text            The credential text.
         * @param  int     $post_id         Achievement or Rank ID.
         * @param  int     $user_id         User ID.
         * @param stdClass $user_earning    User earning (the credential)
         *
         * @return string
         */
        $credential_text = apply_filters( 'gamipress_credential_text', $credential_text, $post_id, $user_id, $user_earning );

        $html .= '<div class="gamipress-credential-text"><p>' . $credential_text . '</p></div>';
    }

    /**
     * Available filter to override the credential text HTML
     *
     * @since  7.9.8
     *
     * @param  string  $html            The credential text HTML markup.
     * @param  int     $post_id         Achievement or Rank ID.
     * @param  int     $user_id         User ID.
     * @param stdClass $user_earning    User earning (the credential)
     *
     * @return string                   HTML Markup.
     */
    return apply_filters( 'gamipress_credential_text_html', $html, $post_id, $user_id, $user_earning );
}