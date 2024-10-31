<?php
/** 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('Qzorg_Pdb') ) {
    class Qzorg_Pdb
    {
        private $db;

        public function __construct( ) {
            global $wpdb;
            $this->db = $wpdb;
        }

        /**
         * @since 1.0.0
         * @param $quiz_id
         * @return : Retuens questions list
         */

        public function qzorg_get_questionlist( $quiz_id = 0 ) {
            if ( 0 === $quiz_id ) { return; }
            global $wpdb;
            $questions = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}qzorg_questions WHERE quiz_id = %d",
                $quiz_id
            ) );
            $quiz_obj = $wpdb->get_row( $wpdb->prepare(
                "SELECT other_tools FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                $quiz_id
            ) );
            $order = maybe_unserialize($quiz_obj->other_tools);
            $newOrder = $order['question_order'];
            $sortedArray = $this->manage_question_order($questions, $newOrder);
            if ( $questions ) {
                return $sortedArray;
            }
        }

        
        /**
         * @since 1.0.0
         * @param $questions
         * @param $newOrder
         * @return : Replace question order
         */

        public function manage_question_order( $questions, $newOrder ) {

            $managedQuestions = array();

            foreach ( $newOrder as $ids ) {
                $temp = array();
                foreach ( $ids as $id ) {
                    foreach ( $questions as $object ) {
                        if ( $object->question_id == $id ) {
                            $temp[] = $object;
                            break;
                        }
                    }
                }
                $managedQuestions[] = $temp;
            }
            
            return $managedQuestions;
            
        } 

    }
}