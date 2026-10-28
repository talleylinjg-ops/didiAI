<?php
/**
 * AI Ability
 *
 * @package     GamiPress\Classes\AI_Ability
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability {

    public $integration = 'gamipress';
    public $ability = '';
    public $name = ''; // integration/ability
    public $label = ''; // Set on register
    public $description = ''; // Set on register
    public $category = 'gamipress';
    public $args = array();

    // Meta args
    public $readonly = false;
    public $destructive = false;
    public $idempotent = true;
    public $show_in_rest = true;

    public function __construct() {

        $this->hooks();

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_action( 'wp_abilities_api_init', array( $this, 'register_ability' ) );

    }

    /**
     * Register
     *
     * @since 1.0.0
     */
    public function register() {
        // Override
    }

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register_ability() {

        // Init the subclass vars
        $this->register();

        $this->name = $this->integration . '/' . $this->ability;

        $this->args = wp_parse_args( $this->args, array(
            'label'               => $this->label, // Override
            'description'         => $this->description, // Override
            'category'            => $this->category,
            'input_schema'        => $this->input_schema(), // Override
            'output_schema'       => $this->output_schema(),
            'execute_callback'    => array( $this, 'execute' ),
            'permission_callback' => array( $this, 'permission' ),
            'meta'                => $this->meta(),
        ) );

        wp_register_ability( $this->name, $this->args );

    }

    /**
     * Input schema
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function input_schema() {
        // Override
        return array();
    }

    /**
     * Output schema
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function output_schema() {
        return array(
            'type' => 'object',
            'properties' => array(
                // Default to match with expected in execute
                'success' => array( 'type' => 'boolean' ),
                'message' => array( 'type' => 'string' ),
            ),
            'required' => array( 'success', 'message' ),
            'additionalProperties' => true,
        );
    }

    /**
     * Meta
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function meta() {
        return array(
            'annotations'  => array(
                'readonly'    => $this->readonly,
                'destructive' => $this->destructive,
                'idempotent'  => $this->idempotent,
            ),
            'show_in_rest' => $this->show_in_rest,
        );
    }

    /**
     * Ability permission callback
     *
     * @since 1.0.0
     */
    public function permission() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Ability execute callback
     *
     * @since 1.0.0
     *
     * @param array $args
     *
     * @return array array( 'success' => true|false, 'message' => '' )
     */
    public function execute( $args ) {
        // Override
        return array();
    }

    /**
     * Returns a response following the ability output schema
     *
     * @since 1.0.0
     *
     * @param string    $message
     * @param bool      $success
     *
     * @return array
     */
    public function response( $message, $success = true ) {
        if( $success )
            return $this->response_success( $message );
        else
            return $this->response_error( $message );
    }

    /**
     * Returns a success response
     *
     * @since 1.0.0
     *
     * @param string $message
     *
     * @return array
     */
    public function response_success( $message ) {

        return array(
            'success' => true,
            'message' => $this->get_response_prefix( true ) . ' ' . $message,
        );

    }

    /**
     * Returns an error response
     *
     * @since 1.0.0
     *
     * @param string $message
     *
     * @return array
     */
    public function response_error( $message ) {

        return array(
            'success' => false,
            'message' => $this->get_response_prefix( false ) . ' ' . $message,
        );

    }

    public function is_response_error( $thing ) {

        if( is_array( $thing )
            && isset( $thing['message'] )
            && isset( $thing['success'] )
            && $thing['success'] === false
        ) {
            return true;
        }

        return false;

    }

    /**
     * Gets a random prefix
     *
     * @since 1.0.0
     *
     * @param bool $success
     *
     * @return string
     */
    public function get_response_prefix( $success = true ) {

        $prefixes = array(
            __( 'Success!', 'gamipress' ),
            __( 'Done!', 'gamipress' ),
            __( 'Ready!', 'gamipress' ),
            __( 'Complete!', 'gamipress' ),
            __( 'Completed!', 'gamipress' ),
            __( 'Finish!', 'gamipress' ),
            __( 'Finished!', 'gamipress' ),
            __( 'It\'s done!', 'gamipress' ),
            __( 'I\'ve finished.', 'gamipress' ),
        );

        if( ! $success ) {
            $prefixes = array(
                __( 'Oops!', 'gamipress' ),
                __( 'Failed!', 'gamipress' ),
                __( 'Couldn\'t complete.', 'gamipress' ),
                __( 'Something went wrong.', 'gamipress' ),
            );
        }

        $i = array_rand( $prefixes, 1 );

        return isset( $prefixes[$i] ) ? $prefixes[$i] : '';

    }

    public function parse_user_query_args( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'limit' => 3,
        ) );

        return $args;
    }

    /**
     * Search for users from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int $search Search term to find the user(s)
     * @param array      $args   Extra search args
     *
     * @return array
     */
    public function resolve_user_ids( $search = '', $args = array() ) {

        global $wpdb;

        if ( empty( $search ) ) return array();

        $args = $this->parse_user_query_args( $args );

        // Pull back the search string
        $search = esc_sql( $wpdb->esc_like( $search ) );
        $where = '';
        $from = "FROM {$wpdb->users} as u ";

        $where = "WHERE ( ";

        $where .= "u.user_login LIKE '%{$search}%' ";
        $where .= "OR u.display_name LIKE '%{$search}%' ";

        if( is_numeric( $search ) )
            $where .= "OR u.ID = '{$search}' ";

        if( strpos( $search, '@' ) !== false )
            $where .= "OR u.user_email LIKE '%{$search}%' ";

        $where .= " ) ";

        $limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 3;

        $user_ids = $wpdb->get_col(
            "SELECT u.ID
             {$from}
             {$where}
             LIMIT {$limit}",
        );

        return $user_ids;

    }

    /**
     * Get user from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int $search Search term to find the user
     *
     * @return WP_User|array    WP_User object or an error response array
     */
    public function get_user( $search = '' ) {

        // If search is a number, check if there is a user with that ID
        if( is_numeric( $search ) && absint( $search ) > 0 ) {
            $user = get_user_by( 'id', absint( $search ) );

            if( $user ) return $user;
        }

        // Ssearch the user by term
        $user_ids = $this->resolve_user_ids( $search );

        // Bail if can not find the user
        if( count( $user_ids ) === 0 ) {
            return $this->response_error( sprintf(
                __( 'I couldn\'t find the user "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different name, email or another identifying detail.', 'gamipress' ),
                $search
            ) );
        } else if( count( $user_ids ) > 1 ) {
            // Bail if more than 1 user found
            // IMPORTANT: use $this->get_users() if you need multiple users
            return $this->response_error( sprintf(
                __( 'I found several users matching with "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different name, email or another identifying detail.', 'gamipress' ),
                $search
            ) );
        }

        $user = get_user_by( 'id', absint( $user_ids[0] ) );

        if( ! $user ) {
            return $this->response_error( sprintf(
                __( 'I couldn\'t find the user "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different name, email or another identifying detail.', 'gamipress' ),
                $search
            ) );
        }

        return $user;

    }

    /**
     * Get users from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int $search Search term to find the users
     *
     * @return array    WP_User array or an error response array
     */
    public function get_users( $search = '' ) {

        $user_ids = $this->resolve_user_ids( $search );

        // Bail if can not find the user
        if( count( $user_ids ) === 0 ) {
            return $this->response_error( sprintf(
                __( 'I couldn\'t find any user by "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different name, email or another identifying detail.', 'gamipress' ),
                $search
            ) );
        }

        $users = array();

        foreach( $user_ids as $user_id ) {
            $user = get_user_by( 'id', absint( $user_id ) );

            if( $user ) $users[] = $user;
        }

        if( count( $users ) === 0 ) {
            return $this->response_error( sprintf(
                __( 'I couldn\'t find any user by "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different name, email or another identifying detail.', 'gamipress' ),
                $search
            ) );
        }

        return $users;

    }

    /**
     * Search for posts from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int    $search Search term to find the post(s)
     * @param array         $args   Extra search args
     *
     * @return array
     */
    public function resolve_post_ids( $search = '', $args = array() ) {

        global $wpdb;

        if ( empty( $search ) ) return array();

        $args = $this->parse_post_query_args( $args );

        // Pull back the search string
        $search = esc_sql( $wpdb->esc_like( $search ) );
        $from = "FROM {$wpdb->posts} as p ";

        $where = "WHERE ( ";

        // Search for title or slug
        $where .= "p.post_title LIKE '%{$search}%' ";
        $where .= "OR p.post_name LIKE '%{$search}%' ";

        // Search by ID
        if( is_numeric( $search ) )
            $where .= "OR p.ID = '{$search}' ";

        $where .= " ) ";

        // Status publish, private or draft (preventing in trash or pending)
        $where .= "AND p.post_status IN ( 'publish', 'private', 'draft' ) ";

        // Post type
        if( ! empty( $args['post_type'] ) ) {
            $post_type = esc_sql( $args['post_type'] );
            $where .= "AND p.post_type = '{$post_type}' ";
        }

        $limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 3;

        $post_ids = $wpdb->get_col(
            "SELECT p.ID
             {$from}
             {$where}
             LIMIT {$limit}",
        );

        return $post_ids;

    }

    public function parse_post_query_args( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'post_type' => '',
            'limit' => 3,
        ) );

        return $args;
    }

    /**
     * Get post from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int    $search Search term to find the user
     * @param array         $args   Extra search args
     *
     * @return WP_User|array    WP_User object or an error response array
     */
    public function get_post( $search = '', $args = array() ) {

        $args = $this->parse_post_query_args( $args );

        $post_ids = $this->resolve_post_ids( $search, $args );
        $post_type = false;

        if( isset( $args['post_type'] ) )
            $post_type = get_post_type_object( $args['post_type'] );

        if( ! $post_type )
            $post_type = get_post_type_object( 'post' );

        // Bail if can not find the post
        if( count( $post_ids ) === 0 ) {
            return $this->response_error( sprintf(
                // translators: %1$s: Post type singular %2$s: Search term
                __( 'I couldn\'t find the %1$s "%2$s".', 'gamipress' )
                . ' ' . __( 'Try providing a different title, slug or another identifying detail.', 'gamipress' ),
                $post_type->labels->singular_name,
                $search
            ) );
        } else if( count( $post_ids ) > 1 ) {
            // Bail if more than 1 post found
            return $this->response_error( sprintf(
                // translators: %1$s: Post type plural %2$s: Search term
                __( 'I found several %1$s matching with "%2$s".', 'gamipress' )
                . ' ' . __( 'Try providing a different title, slug or another identifying detail.', 'gamipress' ),
                $post_type->labels->name,
                $search
            ) );
        }

        $post = get_post( absint( $post_ids[0] ) );

        if( ! $post ) {
            return $this->response_error( sprintf(
                // translators: %1$s: Post type singular %2$s: Search term
                __( 'I couldn\'t find the %1$s "%2$s".', 'gamipress' )
                . ' ' . __( 'Try providing a different title, slug or another identifying detail.', 'gamipress' ),
                $post_type->labels->singular_name,
                $search
            ) );
        }

        return $post;

    }

    /**
     * Get post from a search term.
     *
     * @since 1.0.0
     *
     * @param string|int    $search Search term to find the user
     * @param array         $args   Extra search args
     *
     * @return array        WP_Post array or an error response array
     */
    public function get_posts( $search = '', $args = array() ) {

        $args = $this->parse_post_query_args( $args );

        $post_ids = $this->resolve_post_ids( $search, $args );
        $post_type = false;

        if( isset( $args['post_type'] ) ) {
            $post_type = get_post_type_object( $args['post_type'] );
        }

        if( ! $post_type ) {
            $post_type = get_post_type_object( 'post' );
        }

        // Bail if can not find the post
        if( count( $post_ids ) === 0 ) {
            return $this->response_error( sprintf(
                // translators: %1$s: Post type plural %2$s: Search term
                __( 'I couldn\'t find any %s "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different title, slug or another identifying detail.', 'gamipress' ),
                $post_type->labels->name,
                $search
            ) );
        }

        $posts = array();

        foreach( $post_ids as $post_id ) {
            $post = get_post( absint( $post_id ) );

            if( $post ) $posts[] = $post;
        }


        if( count( $posts ) === 0 ) {
            return $this->response_error( sprintf(
                // translators: %1$s: Post type plural %2$s: Search term
                __( 'I couldn\'t find any %s "%s".', 'gamipress' )
                . ' ' . __( 'Try providing a different title, slug or another identifying detail.', 'gamipress' ),
                $post_type->labels->name,
                $search
            ) );
        }

        return $posts;

    }

}