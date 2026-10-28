<?php
/**
 * Help
 *
 * @package     GamiPress\AI_Assistant\Help
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render AI assistant help
 *
 * @since	    1.0.0
 */
function gamipress_ai_assistant_render_help() {
    $points_types = gamipress_get_points_types();
    $achievements_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();

    $achievement = gamipress_ai_assistant_get_random_post( array_keys( $achievements_types ) );
    $achievement_id = ( $achievement ? $achievement->ID : 123 );
    $achievement_title = ( $achievement ? $achievement->post_title : __( 'First Badge', 'gamipress' ) );
    $achievement_singular = ( $achievement && isset( $achievements_types[$achievement->post_type] ) ? $achievements_types[$achievement->post_type]['singular_name'] : __( 'Badge', 'gamipress' ) );

    $rank = gamipress_ai_assistant_get_random_post( array_keys( $rank_types ) );
    $rank_id = ( $rank ? $rank->ID : 123 );
    $rank_title = ( $rank ? $rank->post_title : __( 'Beginner', 'gamipress' ) );
    $rank_singular = ( $rank && isset( $rank_types[$rank->post_type] ) ? $rank_types[$rank->post_type]['singular_name'] : __( 'Rank', 'gamipress' ) );

    $user = wp_get_current_user();
    $user_first = $user->user_login;
    $user_email = $user->user_email;
    $user_id = $user->ID;

    $abilities = array(
        'suggest-gamification' => array(
            'label' => __( 'Suggest Configurations', 'gamipress' ),
            'icon' => 'gamipress',
            'desc' => __( 'I can suggest, design and create all gamification elements for GamiPress.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Suggest 5 badges based on %1$s activities and create them as %2$s', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plugins( 2 ),
                    gamipress_ai_assistant_get_random_plural( $achievements_types, __( 'badges', 'gamipress' ) ),
                ),
                sprintf( __( 'Generate a points type with 3 awards and 2 deducts based on %1$s activities', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plugins( 1 ),
                ),
                sprintf( __( 'Set up 5 progressive %1$s based on %2$s thresholds that increase by 100', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plural( $rank_types, __( 'ranks', 'gamipress' ) ),
                    gamipress_ai_assistant_get_random_singular( $points_types, __( 'point', 'gamipress' ) ),
                ),
            ),
        ),
        'create-types' => array(
            'label' => __( 'Create New Types', 'gamipress' ),
            'icon' => 'heart',
            'desc' => __( 'I\'m able to create new types (points, achievements and ranks).', 'gamipress' )
         . ' ' . __( 'For points types, I can also define points awards and deductions in the same prompt.', 'gamipress' ) ,
            'prompts' => array(
                sprintf( __( 'Add the "Points" points type with 3 awards based on %1$s', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plugins( 1 ),
                ),
                __( 'Create the "Badge" achievement type', 'gamipress' ),
                __( 'Add a rank type named "Rank"', 'gamipress' ),
            ),
        ),
        'create-achievements-ranks' => array(
            'label' => __( 'Set Up Achievements & Ranks', 'gamipress' ),
            'icon' => 'admin-tools',
            'desc' => __( 'I can design, create and configure new achievements & ranks for your site.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Create the %1$s "Early Bird" given for logging 5 times', 'gamipress' ),
                    gamipress_ai_assistant_get_random_singular( $achievements_types, __( 'badge', 'gamipress' ) ),
                ),
                sprintf( __( 'Create a %1$s for visiting the site 3 times and commenting on %2$s', 'gamipress' ),
                    gamipress_ai_assistant_get_random_singular( $rank_types, __( 'rank', 'gamipress' ) ),
                    __( 'Sample Page', 'gamipress' ),
                ),
            ),
        ),
        'create-points-awards-deducts' => array(
            'label' => __( 'Add Points Rules', 'gamipress' ),
            'icon' => 'flag',
            'desc' => __( 'I can add new rules to award and deduct points to your points types.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Add 2 awards based on %1$s to %2$s', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plugin(),
                    gamipress_ai_assistant_get_random_plural( $points_types, __( 'points', 'gamipress' ) ),
                ),
                sprintf( __( 'New rule: Deduct 25 %1$s for publishing a new post', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plural( $points_types, __( 'points', 'gamipress' ) ),
                    $user_first,
                ),
            ),
        ),
        'create-requirements' => array(
            'label' => __( 'Set Up Requirements', 'gamipress' ),
            'icon' => 'rank',
            'desc' => __( 'I can add new requirements to achievements and ranks.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Add a step to "%1$s" %2$s for log in 5 times', 'gamipress' ),
                    $achievement_title,
                    $achievement_singular,
                ),
                sprintf( __( 'New requirement to "%1$s" %2$s: Earn 100 %3$s', 'gamipress' ),
                    $rank_title,
                    $rank_singular,
                    gamipress_ai_assistant_get_random_plural( $points_types, __( 'points', 'gamipress' ) ),
                ),
                sprintf( __( 'Set up the %1$s "%2$s" with 3 steps based on %3$s', 'gamipress' ),
                    $achievement_singular,
                    $achievement_title,
                    gamipress_ai_assistant_get_random_plugin(),
                ),
            ),
        ),
        'award-deduct-points' => array(
            'label' => __( 'Award/Deduct Points', 'gamipress' ),
            'icon' => 'star-filled',
            'desc' => __( 'I can update your users balance and register the movement on user earnings.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Award 10 %1$s to %2$s for commenting', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plural( $points_types, __( 'points', 'gamipress' ) ),
                    $user_first,
                ),
                sprintf( __( 'Deduct 10 %1$s to %2$s without register on earnings', 'gamipress' ),
                    gamipress_ai_assistant_get_random_plural( $points_types, __( 'points', 'gamipress' ) ),
                    $user_first,
                ),
            ),
        ),
        'award-revoke-achievements-ranks' => array(
            'label' => __( 'Award/Revoke Achievements & Ranks', 'gamipress' ),
            'icon' => 'awards',
            'desc' => __( 'I can manage your users earnings easily.', 'gamipress' ),
            'prompts' => array(
                sprintf( __( 'Award the %1$s "%2$s" to %3$s', 'gamipress' ),
                    $achievement_singular,
                    $achievement_title,
                    $user_first,
                ),
                sprintf( __( 'Revoke the %1$s "%2$s" to %3$s', 'gamipress' ),
                    $rank_singular,
                    $rank_title,
                    $user_first,
                ),
            ),
        ),
    );

    $first_ability = array_keys( $abilities )[0];

    ?>

    <div class="gamipress-ai-assistant-help-presentation">
        <?php _e( 'I\'m an <strong>AI assistant</strong> specially designed to help you manage <strong>GamiPress</strong> using natural language.', 'gamipress' ); ?>
        <br>
        <br>
        <?php esc_html_e( 'Here\'s what I can do:', 'gamipress' ); ?>
    </div>

    <?php foreach( $abilities as $ability => $data ) : ?>
        <div class="gamipress-ai-assistant-help-ability gamipress-ai-assistant-help-ability-<?php echo esc_attr( $ability ); ?> <?php echo ( $ability === $first_ability ? 'gamipress-ai-assistant-help-ability-open' : 'gamipress-ai-assistant-help-ability-close' ); ?>">
            <div class="gamipress-ai-assistant-help-ability-label"><?php
                echo gamipress_dashicon( $data['icon'] )
                    . ' ' .  esc_html( $data['label'] )
                    . ' ' .  gamipress_dashicon( 'arrow-up-alt2' );
                ?></div>
            <div class="gamipress-ai-assistant-help-ability-desc" style="<?php echo ( $ability === $first_ability ? '' : 'display: none;' ); ?>">
                <div class="gamipress-ai-assistant-help-ability-desc-text"><?php echo esc_html( $data['desc'] ); ?></div>
                <strong><small><?php esc_html_e( 'Examples', 'gamipress' ); ?></small></strong>
                <div class="gamipress-ai-assistant-help-ability-prompts">
                    <?php foreach( $data['prompts'] as $prompt ) : ?>
                        <span class="gamipress-ai-assistant-prompt"><?php echo esc_html( $prompt ); ?></span>
                        <div class="gamipress-ai-assistant-send-prompt cmb-tooltip" data-prompt="<?php echo esc_attr( $prompt ); ?>">
                            <?php echo gamipress_dashicon( 'edit' ); ?>
                            <div class="cmb-tooltip-desc cmb-tooltip-top"><?php echo esc_html( __( 'Edit Prompt', 'gamipress' ) ); ?></div>
                        </div>
                        <div class="gamipress-ai-assistant-send-prompt cmb-tooltip" data-prompt="<?php echo esc_attr( $prompt ); ?>" data-send="true">
                            <?php echo gamipress_dashicon( 'share-alt2' ); ?>
                            <div class="cmb-tooltip-desc cmb-tooltip-top"><?php echo esc_html( __( 'Send to Assistant', 'gamipress' ) ); ?></div>
                        </div>
                        <br>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php
}

/**
 * Get a random singular name from GamiPress types
 *
 * @since   1.0.0
 *
 * @param array $types
 * @param string $default
 *
 * @return string
 */
function gamipress_ai_assistant_get_random_singular( $types, $default = '' ) {

    $rand = gamipress_assoc_array_rand( $types );
    if( is_array( $rand ) ) return $rand['singular_name'];

    return $default;
}

/**
 * Get a random plural name from GamiPress types
 *
 * @since   1.0.0
 *
 * @param array $types
 * @param string $default
 *
 * @return string
 */
function gamipress_ai_assistant_get_random_plural( $types, $default = '' ) {

    $rand = gamipress_assoc_array_rand( $types );
    if( is_array( $rand ) ) return $rand['plural_name'];

    return $default;
}

/**
 * Get a random post
 *
 * @since   1.0.0
 *
 * @param array $post_types
 *
 * @return Object|false
 */
function gamipress_ai_assistant_get_random_post( $post_types = array() ) {
    global $wpdb;

    $posts = GamiPress()->db->posts;

    $post_types_where = '';

    if( count( $post_types ) === 1 ) {
        $post_type = sanitize_key( $post_types[0] );
        $post_types_where = ' AND p.post_type = "' . $post_type . '"';
    } else if( count( $post_types ) ) {
        $post_types_where = ' AND p.post_type IN ( "' . implode( '", "', $post_types ) . '" )';
    }

    $post = $wpdb->get_row( "SELECT p.* 
            FROM {$posts} AS p
            WHERE p.post_status IN ( 'publish', 'draft' )
              {$post_types_where}
            ORDER BY rand()
            LIMIT 1"
    );

    return $post;
}

/**
 * Get a random plugin name
 *
 * @since   1.0.0
 *
 * @return string
 */
function gamipress_ai_assistant_get_random_plugin() {
    return gamipress_assoc_array_rand( gamipress_ai_assistant_get_available_plugins(), 'WordPress' );
}

/**
 * Get a list of random plugins names
 *
 * @since   1.0.0
 *
 * @return string
 */
function gamipress_ai_assistant_get_random_plugins( $desired = 3 ) {
    $available = gamipress_ai_assistant_get_available_plugins();

    if( $desired === 1 )
        return gamipress_ai_assistant_get_random_plugin();


    if( count( $available ) < $desired )
        return gamipress_join_words( $available );

    $results = array();

    for( $i = 0; $i < $desired; $i++ ) {
        $result = gamipress_assoc_array_rand( $available, 'WordPress' );

        $k = array_search( $result, $available );
        unset( $available[$k] );

        $results[] = $result;
    }

    return gamipress_join_words( array_unique( $results ) );
}

/**
 * Get available plugins names
 *
 * @since   1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_available_plugins() {

    $options = array();

//    if( class_exists( 'GamiPress' ) ) $options[] = 'GamiPress';
    if( class_exists( 'AutomatorWP' ) ) $options[] = 'AutomatorWP';
    if( class_exists( 'ShortLinksPro' ) ) $options[] = 'ShortLinks Pro';
    if( class_exists( 'BBForms' ) ) $options[] = 'BBForms';

    if( defined( 'BP_PLATFORM_VERSION' ) ) $options[] = 'BuddyBoss';
    if( class_exists( 'BuddyPress' ) && ! defined( 'BP_PLATFORM_VERSION' ) ) $options[] = 'BuddyPress';

    if( class_exists( 'SFWD_LMS' ) ) $options[] = 'LearnDash';
    if( class_exists( 'LearnPress' ) ) $options[] = 'LearnPress';
    if( class_exists( 'LifterLMS' ) ) $options[] = 'LifterLMS';
    if( function_exists( 'tutor' ) ) $options[] = 'Tutor LMS';
    if( class_exists( 'H5P_Plugin' ) ) $options[] = 'H5P';

    if( class_exists( 'Easy_Digital_Downloads' ) ) $options[] = 'Easy Digital Downloads';
    if( class_exists( 'SureCart' ) ) $options[] = 'SureCart';
    if( class_exists( 'WooCommerce' ) ) $options[] = 'WooCommerce';

    $options[] = 'WordPress';

    return $options;
}