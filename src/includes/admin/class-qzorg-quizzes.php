<?php
/**
 * Add HTML Structure
 * 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('Qzorg_Quzzes') ) {
    class Qzorg_Quzzes
    {
        private $db;

        public function __construct( ) {
            global $wpdb;
            $this->db = $wpdb;
            add_action( 'wp_ajax_qzorg_update_quiz_text', array( $this, 'qzorg_update_quiz_text' ) );
            add_action( 'wp_ajax_qzorg_delete_quiz_text', array( $this, 'qzorg_delete_quiz_text' ) );
            add_action( 'wp_ajax_qzorg_set_question_order', array( $this, 'qzorg_set_question_order' ) );
            add_action( 'wp_ajax_qzorg_update_questions_for_quiz', array( $this, 'qzorg_update_questions_for_quiz' ) );
            add_action( 'wp_ajax_qzorg_register_new_question', array( $this, 'qzorg_register_new_question' ) );
            add_action( 'wp_ajax_qzorg_delete_question', array( $this, 'qzorg_delete_question' ) );
            add_action( 'wp_ajax_qzorg_duplicate_question', array( $this, 'qzorg_duplicate_question' ) );
            add_action('wp_ajax_qzorg_q_image_upload', array( $this, 'qzorg_q_image_upload' ) );

        }
        
        /**
         * @since 1.0.0
         */
        function qzorg_update_quiz_text() {
            
            if ( (isset($_POST['data']['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['nonce'])), 'qzorg_update_quiz')) || (isset($_POST['data']['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['nonce'])), 'qzorg_register_quiz')) ) {

                global $Quiz_Organizer;
                $__d = array();
                $quiz_data = isset( $_POST['data']['data'] ) ? wp_kses_post(wp_unslash( $_POST['data']['data'])) : ""; 
                $quiz_data = json_decode($quiz_data, true);
                // $quiz_data Sanitization process here 
                $sanitized_loop = Qzorg_Defaults::qzorg_sanitize_text_field($quiz_data);
                foreach ( $sanitized_loop as $key => $value ) {
                    $__d[ $value['name'] ] = $value['value'];
                }
                if ( ! ( ! isset($__d['quiz_id'])) ) {
                    $Quiz_Organizer->modificationObj->qzorg_quiz_default_updatable($__d, $__d['quiz_id']);
                }elseif ( ! empty($__d) ) {
                    Qzorg_Modification::validate($__d);
                    $Quiz_Organizer->modificationObj->create();
                }
            }

        }

        function qzorg_delete_quiz_text() { 

            if ( (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'remove_quiz_nonce')) ) {
                $db = New Qzorg_Db();
                $db::process(isset( $_POST['data'] ) ? array_map('sanitize_text_field', wp_unslash($_POST['data'])) : array());
                $db->delete();
            } else {
                Qzorg_Defaults::get_nonce_validation_error();
            }
        }
        
        function qzorg_duplicate_question() {

            if ( (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'copy_question_nonce')) ) {
                global $Quiz_Organizer;
                $question_id = isset( $_POST['data'] ) ? sanitize_text_field(wp_unslash( $_POST['data'])) : 0;
                $quiz_id = isset( $_POST['quiz'] ) ? sanitize_text_field(wp_unslash( $_POST['quiz'])) : 0;
                $Quiz_Organizer->questionObj->qzorg_duplicate_question($question_id, $quiz_id);
            } else {
                Qzorg_Defaults::get_nonce_validation_error();
            }
            die;
        }

        function qzorg_set_question_order() {
            if ( (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'order_nonce')) ) {
                $db = new Qzorg_Db();
                $sanitized_order = array();
                $post_order = isset( $_POST['order'] ) ? sanitize_text_field(wp_unslash( $_POST['order'])) : array(); 
                $post_order = json_decode($post_order, true);
                // SANITIZE $post_order HERE USING FOREACH LOOP 
                foreach ( $post_order as $inner_array ) {
                    if ( is_array($inner_array) ) {
                        $sanitized_inner_array = array_map('sanitize_text_field', $inner_array);
                        $sanitized_order[] = $sanitized_inner_array;
                    }
                }
                $id = isset( $_POST['id'] ) ? sanitize_text_field(wp_unslash( $_POST['id'])) : 0; 
                $q_obj = $db->set_question_order($sanitized_order, $id);
            }
            die;
        }

                    
        function qzorg_update_questions_for_quiz() {
            if ( (isset($_POST['data']['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['nonce'])), 'qzorg_update_quiz')) ) {
                if ( isset($_POST['data']) ) {

                    $questions = isset( $_POST['data']['questions'] ) ? wp_kses_post(wp_unslash( $_POST['data']['questions'])) : ""; 
                    $questions = json_decode($questions, true);
                    $quiz_id = isset( $_POST['data']['quiz_id'] ) ? sanitize_text_field(wp_unslash( $_POST['data']['quiz_id'])) : ""; 
                    unset($_POST['data']['nonce']);
                    unset($_POST['data']['quiz_id']);
                    // $questions Sanitization process here 
                    $sanitized_questions = Qzorg_Defaults::qzorg_sanitize_text_field($questions, 1);
                    $db = new Qzorg_Db();
                    $db->update_quiz_qustions($sanitized_questions, $quiz_id);
                    
                }
            }
        }

        function qzorg_register_new_question() {

            if ( (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'new_question_nonce')) ) {
                global $Quiz_Organizer;
                $quiz_id = isset( $_POST['data'] ) ? sanitize_text_field(wp_unslash( $_POST['data'])) : ""; 
                $Quiz_Organizer->questionObj->create( $quiz_id );
            } else {
                Qzorg_Defaults::get_nonce_validation_error();
            }
        }

        function qzorg_delete_question() {

            if ( (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'remove_question_nonce')) ) {
                global $Quiz_Organizer;
                $question_id = isset( $_POST['data'] ) ? array_map('sanitize_text_field', wp_unslash( $_POST['data'])) : array(); 
                $Quiz_Organizer->questionObj->qzorg_delete_question( $question_id );
            } else {
                Qzorg_Defaults::get_nonce_validation_error();
            }
            die;
        }
                
        // AJAX callback for image upload
        function qzorg_q_image_upload() {
            check_ajax_referer('qzorg_image_nonce', 'security');
            $attachment_id = isset( $_POST['attachment_id'] ) ? sanitize_text_field(wp_unslash( intval($_POST['attachment_id']))) : "";
            $image_url = wp_get_attachment_image_src($attachment_id, 'full');
            wp_send_json_success(array( $image_url[0], $attachment_id ));
        }   

    }
}new Qzorg_Quzzes();