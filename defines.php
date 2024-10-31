<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'QZORG_ADMIN_URL', admin_url() );
define( 'QZORG_TXTDOMAIN', 'quiz-organizer' );
define( 'QZORG_MENU', 'quiz_organizer' );
define( 'QZORG_URL', 'https://www.quizorganizer.com' );
define( 'QZORG_PLUGIN_PATH', plugin_dir_path( QZORG_FIRST_STEP ) );
define( 'QZORG_PLUGIN_BASENAME', plugin_basename( QZORG_FIRST_STEP ) );
define( 'QZORG_PLUGIN_URL', plugin_dir_url( QZORG_FIRST_STEP ) );
define( 'QZORG_INCLUDE_PAGE', QZORG_PLUGIN_PATH . 'src/includes/admin' );
define( 'QZORG_INCLUDE_CLASS', QZORG_PLUGIN_PATH . 'src/includes/sets' );
define( 'QZORG_INCLUDE_SETS', QZORG_PLUGIN_PATH . 'src/includes/main' );
define( 'QZORG_CSS_URL', QZORG_PLUGIN_URL . 'assets/css' );
define( 'QZORG_IMAGE_URL', QZORG_PLUGIN_URL . 'assets/images' );
define( 'QZORG_PUBLIC_CSS_URL', QZORG_PLUGIN_URL . 'public/css' );
define( 'QZORG_JS_URL', QZORG_PLUGIN_URL . 'assets/js' );
define( 'QZORG_PUBLIC_JS_URL', QZORG_PLUGIN_URL . 'public/js' );
define( 'QZORG_IS_ADMIN', is_admin() );
