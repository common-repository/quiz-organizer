<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * To manage plugin installation, updates
 *
 * @since 1.0.0
 */
if ( ! class_exists('Qzorg_Beginning') ) {

    class Qzorg_Beginning
    {

        function __construct() {
            qzorg_inc_class('class-qzorg-db.php');
            add_action( 'admin_init', array( $this, 'qzorg_check_version_and_update' ) );
        }

        /**
         * Installs database tables
         *
         * @since 1.0.0
         */
        public static function install() {
            global $wpdb;
            $quiz_table = $wpdb->prefix . "qzorg_quizzes";
            $question_table = $wpdb->prefix . "qzorg_questions";
            $category_table = $wpdb->prefix . "qzorg_categories";
            $submission_table = $wpdb->prefix . "qzorg_submissions";
            $charset_collate = $wpdb->get_charset_collate();

            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}qzorg_quizzes'") != $quiz_table ) {
                
                $qz_sql = "CREATE TABLE $quiz_table (
                    quiz_id INT(10) NOT NULL AUTO_INCREMENT,
                    quiz_name TEXT NOT NULL,
                    quiz_visits INT NOT NULL,
                    quiz_attend INT NOT NULL,
                    quiz_type ENUM ('points', 'rightwronge', 'both') NOT NULL,
                    quiz_tools TEXT NOT NULL,
                    other_tools TEXT DEFAULT NULL,
                    author_id INT(10) NOT NULL,
                    login_require VARCHAR(10) NOT NULL,
                    shortcode VARCHAR(100) NOT NULL,
                    preview_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    updated_at DATETIME NOT NULL,
                    PRIMARY KEY (quiz_id)
                )$charset_collate;";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($qz_sql);
            }

            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}qzorg_questions'") != $question_table ) {
                $qn_sql = "CREATE TABLE $question_table (
                    question_id INT(10) NOT NULL AUTO_INCREMENT,
                    quiz_id INT(10) NOT NULL,
                    question_title TEXT NOT NULL,
                    question_answer TEXT NOT NULL,
                    question_type TEXT NOT NULL,
                    question_tools TEXT NOT NULL,
                    question_image TEXT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    updated_at DATETIME NOT NULL,
                    PRIMARY KEY (question_id)
                )$charset_collate;";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($qn_sql);
            }
            
            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}category_table'") != $category_table ) {
                $cy_sql = "CREATE TABLE $category_table (
                    id INT(10) NOT NULL AUTO_INCREMENT,
                    category_name VARCHAR(256) NOT NULL,
                    category_type VARCHAR(10) NOT NULL,
                    category_description TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY (id)
                )$charset_collate;";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($cy_sql);
            }

            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}qzorg_submissions'") != $submission_table ) {
                $sn_sql = "CREATE TABLE $submission_table (
                    submission_id INT(11) NOT NULL AUTO_INCREMENT,
                    quiz_id INT(11) NOT NULL,
                    user_id INT(11) NOT NULL DEFAULT 0,
                    user_ip VARCHAR(256) NOT NULL,
                    quiz_name TEXT NOT NULL,
                    quiz_type VARCHAR(100) NOT NULL,
                    user_name TEXT DEFAULT NULL,
                    user_email TEXT DEFAULT NULL,
                    duration VARCHAR(256) NULL,
                    totalpoints VARCHAR(256),
                    others MEDIUMTEXT,
                    redirect_url TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    deleted_at DATETIME,
                    PRIMARY KEY (submission_id)
                )$charset_collate;";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sn_sql);
            }

        }

        public function qzorg_check_version_and_update() {

            $global_option = get_option('qzorg_global_options');

            if ( empty($global_option) ) {
                add_option('qzorg_global_options', array(
                    'delete_data_on_plugin_deletion' => '0',
                    'stop_storing_ip_address'        => '0',
                    'default_question_type'          => 'drop_down',
                    'default_answer_fields'          => '1',
                ));
            }
            
            $current_version = get_option('qzorg_version', '1.0.0');
            
            if ( QZORG_VERSION !== $current_version ) {
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                
                update_option('qzorg_version', QZORG_VERSION);
                global $wpdb;
                $submissions_table = $wpdb->prefix . 'qzorg_submissions';
                
                if ( $wpdb->get_var("SHOW COLUMNS FROM $submissions_table LIKE 'unique_id'") != "unique_id" ) {
                    $wpdb->query("ALTER TABLE $submissions_table ADD COLUMN `unique_id` BIGINT DEFAULT NULL AFTER `redirect_url`"); 
                    $wpdb->query("UPDATE $submissions_table SET unique_id=0");
                }

                if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}qzorg_submissions'") != $submission_table ) {
                    $wpdb->query("ALTER TABLE {$wpdb->prefix}qzorg_submissions MODIFY `unique_id` TEXT DEFAULT NULL");
                }

            }
            
            // Execute IF CHANGE INTO DEFAULT ARRAY
            // Qzorg_Modification::default();
        }
    }
    new Qzorg_Beginning();
}