<?php
/**
 * Plugin Name: Quiz Organizer
 * Plugin URI:  https://www.quizorganizer.com
 * Description: Simple way to create advanced quizzes and exams. With just a few simple steps.
 * Version:     2.9.1
 * Author:      Quiz Organizer
 * Author URI:  https://www.quizorganizer.com/?utm_source=wordpress.org&utm_medium=referral&utm_campaign=plugin_directory
 * Text Domain: quiz-organizer
 * Requires at least: 5.0
 * Requires PHP: 6.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'QZORG_FIRST_STEP', __FILE__ );
define( 'QZORG_VERSION', '2.9.1' );

require_once __DIR__ . '/defines.php';
require_once __DIR__ . '/src/qzorg-utility-function.php';
require_once __DIR__ . '/src/qzorg-packages.php';

global $Quiz_Organizer;
$Quiz_Organizer = new QZORG_Quiz_Organizer();

register_activation_hook( __FILE__, array( 'Qzorg_Beginning', 'install' ) );
