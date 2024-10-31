<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Class for get all the related support.
* @since 1.0.0
*/

if ( ! class_exists( 'Qzorg_Helper' ) ) {

    class Qzorg_Helper {

        private $matrix;
        private $questions;
        
        /**
         * @since 1.0.0
         * @param $quiz_id
         * @return : Retuens questions list for result page
         */

        public function qzorg_result_question_list( $quiz_id = 0 ) {
            if ( 0 === $quiz_id ) { return; }
            global $wpdb;
            $q_results_obj = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}qzorg_questions WHERE quiz_id = %d",
                $quiz_id
            ) );

            if ( ! empty($q_results_obj) ) {
                $refreshOrder = [];
                $quiz_obj = $wpdb->get_row( $wpdb->prepare(
                    "SELECT other_tools, quiz_tools FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                    $quiz_id
                ) );
                $question_order = maybe_unserialize($quiz_obj->other_tools);
                $newOrder = $question_order['question_order'];

                foreach ( $newOrder as $subArray ) {
                    foreach ( $subArray as $element ) {
                        foreach ( $q_results_obj as $object ) {
                            if ( $object->question_id == $element ) {
                                $refreshOrder[] = $object;
                                break;
                            }
                        }
                    }
                }
            }
            
            if ( $refreshOrder ) {
                return $refreshOrder;
            } else {
                return $q_results_obj;
            }
        }

        public function calculate( $questions, $tools ) {
            
            $decimal = isset($tools['advance']['qzorg_decimal_places']) ? $tools['advance']['qzorg_decimal_places'] : 1;
            $quizMaximumPoints = 0;
            $takenCount = 0;
            $untakenCount = 0;
            $correctCount = 0;
            $incorrectCount = 0;
            $unansweredCount = 0;
            $singleQuestionMaxPoints = 0;
            $singleQuestionMinPoints = PHP_INT_MAX;
            $totalPoints = 0;
            $pointsByQuestion = [];
            $this->questions = $questions;
            foreach ( $questions as $key => $question ) {
                if ( 'taken' === $question['status'] ) {
                    $takenCount++;
                } else {
                    $untakenCount++;
                }
                
                if ( 'correct' === $question['answer'] ) {
                    $correctCount++;
                } elseif ( 'incorrect' === $question['answer'] ) {
                    $incorrectCount++;
                } elseif ( 'unanswered' === $question['answer'] ) {
                    $unansweredCount++;
                }
                
                $earnPoints = $question['earn_points'];
                if ( $earnPoints > $singleQuestionMaxPoints ) {
                    $singleQuestionMaxPoints = $earnPoints;
                }
                
                if ( $earnPoints < $singleQuestionMinPoints ) {
                    $singleQuestionMinPoints = $earnPoints;
                }
                
                $totalPoints += $earnPoints;
                $pointsByQuestion[ $question['question_id'] ] = [
                    'earnPoints'     => Qzorg_Defaults::qzorg_round($earnPoints, $decimal),
                    'minimum_points' => Qzorg_Defaults::qzorg_round($question['min_points'], $decimal),
                    'maximum_points' => Qzorg_Defaults::qzorg_round($question['max_points'], $decimal),
                ];
            }
            
            $averagePoints = ($takenCount > 0) ? round($totalPoints / count($questions), 2) : 0;
            if ( ! empty(array_column($questions, 'max_points')) ) {
                $quizMaximumPoints = array_sum(array_column($questions, 'max_points'));
            }
            $get_unique_id = Qzorg_Defaults::get_unique_id();
            $this->matrix = [
                'taken_count'                => $takenCount,
                'untaken_count'              => $untakenCount,
                'correct_count'              => $correctCount,
                'incorrect_count'            => $incorrectCount,
                'unanswered_count'           => $unansweredCount,
                'single_question_max_points' => Qzorg_Defaults::qzorg_round($singleQuestionMaxPoints, $decimal),
                'single_question_min_points' => Qzorg_Defaults::qzorg_round($singleQuestionMinPoints, $decimal),
                'average_points'             => Qzorg_Defaults::qzorg_round($averagePoints, $decimal),
                'total_points'               => Qzorg_Defaults::qzorg_round($totalPoints, $decimal),
                'points_by_question'         => $pointsByQuestion,
                'quiz_maximum_points'        => Qzorg_Defaults::qzorg_round($quizMaximumPoints, $decimal),
                'get_unique_id'              => $get_unique_id,
            ];

            return $this->matrix;
        }     

        /**
         * @since 1.0.0
         * @return : Retuens Result page
         */

        public function result( $quiz_obj, $user, $structure ) {
            $return = $quiz_obj->resultpage_qzorgmessage;
            $return = apply_filters( 'qzorg_static_results_after', $return, $quiz_obj, $this->matrix );
            $return = apply_filters( 'qzorg_results_page_after', $return, $quiz_obj, $user, $this->questions );
            return $return;
        }

    }

}