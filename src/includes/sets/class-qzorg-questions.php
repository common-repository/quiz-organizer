<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Class to manage all shortcode.
* @since 1.0.0
*/
if ( ! class_exists('Qzorg_Questions') ) {
    class Qzorg_Questions extends Qzorg_Db
    {
        /**
        * Preparation for question structure
        * @since  1.0.0
        */

        public static function index( $request ) {
            $questions = self::get_questios($request);
            $categories = Qzorg_Category::index(0);
            if ( $questions ) {
                foreach ( $questions as $key => $question ) {
                    $questions[ $key ]->question_tools = maybe_unserialize($questions[ $key ]->question_tools);
                }
                return $questions;
            }
            return [];   
        }

        /**
         * Register new question
         * @since  1.0.0
         * @param $question_data
         * @return JSON
         */
        public function create( $question_data ) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_questions";
            $question = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
            $register      = $wpdb->insert(
                $table_name,
                array(
                    'quiz_id'         => $question_data,
                    'question_title'  => "",
                    'question_type'   => $question['default_question_type'],
                    'question_answer' => maybe_serialize(""),
                    'question_tools'  => maybe_serialize(array()),
                    'created_at'      => current_time('mysql'),
                    'updated_at'      => current_time('mysql'),
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ),
            );

            if ( false !== $register ) {
                global $Quiz_Organizer;
                $last_id = $wpdb->insert_id;
                echo wp_json_encode(
                    array(
                        'success'         => 1,
                        'id'              => $last_id,
                        'categories'      => $this->categories(),
                        'question_types'  => Qzorg_Phtml::qzorg_question_types(),
                        'image_url'       => $this->imageurl(),
                        'selected_q_type' => $question['default_question_type'],
                        'default_fields'  => $question['default_answer_fields'],
                    )
                );
            } else {
                do_action('qzorg_check_is_empty', $register, __('Error to process your request !', 'quiz-organizer'));
            }
            die;

        }

        /**
         * @since 1.0.0
         * @return : Retuens all categories
         */

        public function categories() {
            global $wpdb;
            $q_results_obj = $wpdb->get_results("SELECT id, category_name, category_description FROM {$wpdb->prefix}qzorg_categories");
            if ( $q_results_obj ) {
                return $q_results_obj;
            }
        }

        /**
         * Delete Question
         * @since  1.0.0
         * @param $question_id
         * @return JSON
         */
        public function qzorg_delete_question( $questions ) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_questions";
            foreach ( $questions as $q ) {
                $wpdb->delete(
                    $table_name,
                    array(
                        'question_id' => $q,
                    ),
                );
            }
            echo wp_json_encode(
                array(
                    'success' => 1,
                    'message' => __('Question deleted!', 'quiz-organizer'),
                )
            );
            die;
        }

        /**
         * Delete Question
         * @since  1.0.0
         * @param $question_id
         * @return JSON
         */
        public function qzorg_duplicate_question( $question, $quiz_id ) {

            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_questions";
            $single = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}qzorg_questions WHERE question_id = %d", $question));

            $register      = $wpdb->insert(
                $table_name,
                array(
                    'quiz_id'         => $quiz_id,
                    'question_title'  => $single->question_title,
                    'question_type'   => $single->question_type,
                    'question_answer' => $single->question_answer,
                    'question_tools'  => $single->question_tools,
                    'question_image'  => $single->question_image,
                    'created_at'      => current_time('mysql'),
                    'updated_at'      => current_time('mysql'),
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ),
            );

            if ( false !== $register ) {
                $last_id = $wpdb->insert_id;
                wp_send_json_success([
                    'success'         => 1,
                    'id'              => $last_id,
                    'question_title'  => htmlspecialchars(html_entity_decode($single->question_title, ENT_QUOTES), ENT_QUOTES),
                    'categories'      => $this->categories(),
                    'question_types'  => Qzorg_Phtml::qzorg_question_types(),
                    'question_answer' => maybe_unserialize($single->question_answer),
                    'question_tools'  => maybe_unserialize($single->question_tools),
                    'selected_q_type' => $single->question_type,
                    'question_image'  => $single->question_image,
                    'image_url'       => $this->imageurl($single->question_image),
                ], 200);
            } else {
                wp_send_json_error([
                    'message' => __('Error to process your request !', 'quiz-organizer'),
                ], 500);
            }
        }

        /**
         * RETURN IAMGE URL
         * @since  1.1.0
         * @param $image_id
         * @return imageUrl
         */

        public function imageurl( $image_id = "" ) {
            if ( $image_id ) {
                $url = wp_get_attachment_image_src($image_id, 'thumbnail')[0] ;
            } else {
                $url = QZORG_IMAGE_URL . '/upload-img.png' ;
            }
            return $url;
        }


        /**
         * DELETE QUESTIONS BY LOOP
         * @since  1.0.0
         * @param $quiz_id
         * @return bool
         */
        public static function remove( $quiz_id ) {
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_questions";
            $ids = $wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$wpdb->prefix}qzorg_questions WHERE quiz_id = %d", $quiz_id));
            if ( ! empty($ids) ) {
                foreach ( $ids as $id ) {
                    $delete = $wpdb->delete(
                        $table,
                        array(
                            'question_id' => $id,
                        ),
                    );
                }
            }
            return 1;
        }

    }
}