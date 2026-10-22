<?php
/**
 * GamiPress AI Assistant
 *
 * @package     GamiPress\AI_Assistant
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Constants
define( 'GAMIPRESS_AI_ASSISTANT_VER', '1.0.0' );
define( 'GAMIPRESS_AI_ASSISTANT_FILE', __FILE__ );
define( 'GAMIPRESS_AI_ASSISTANT_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAMIPRESS_AI_ASSISTANT_URL', plugin_dir_url( __FILE__ ) );

// Classes
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'classes/gamipress-ai-assistant-ability.php';

// Includes
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/admin.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/ajax-functions.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/functions.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/help.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/scripts.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/templates.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/utilities.php';

// Abilities
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/award-points.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/deduct-points.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/create-points-type.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/add-points-awards.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/add-points-deducts.php';

require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/award-achievement.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/revoke-achievement.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/create-achievement.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/create-achievement-type.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/add-achievement-requirements.php';

require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/award-rank.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/revoke-rank.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/create-rank.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/create-rank-type.php';
require_once GAMIPRESS_AI_ASSISTANT_DIR . 'includes/abilities/add-rank-requirements.php';