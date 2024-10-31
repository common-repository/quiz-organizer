<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Class to manage all notification for this plugin.
*
* @since 1.0.0
*/

if ( ! class_exists( 'Qzorg_Notification_Guide' ) ) {

    class Qzorg_Notification_Guide {

        public $message;
        protected static $_notice_list = [];
        const ADMIN_NOTICE_SUCCESS = 'notice-success';
        const ADMIN_NOTICE_ERROR = 'notice-error';
        const ADMIN_NOTICE_INFO = 'notice-info';
        const ADMIN_NOTICE_WARNING = 'notice-warning';

        /**
         * [ Success, Error, Warning, Info ] message(s) to be displayed in.
         *
         * @param $message
         * @param MESSAGE-TYPE
         * @param $is_dismissible
         */

        public static function qzorg_add_admin_notice( $message, $class = self::ADMIN_NOTICE_INFO, $is_dismissible = true ) {

            self::$_notice_list[] = array(
                'message'        => $message,
                'class'          => $class,
                'is_dismissible' => (bool) $is_dismissible,
            );
            
        }
        
        /**
         * @since 1.0.0
         * @uses Display admin notice
         */

        public static function qzorg_display_admin_notices() {

            foreach ( (array) self::$_notice_list as $notice ) :
                $dismissible = $notice['is_dismissible'] ? 'is-dismissible' : ''; ?>
                <div class="notice quiz-organizer-notice <?php echo esc_attr( $notice['class'] ); ?> notice <?php echo esc_attr( $dismissible ); ?>">
                    <p>
                        <?php echo wp_kses_post( $notice['message'] ); ?>
                    </p>
                </div>
            <?php endforeach;
        }

    }

}