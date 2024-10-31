<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Manage Admin Database process
 * 
 * @since 2.1.0
 */
if ( ! class_exists('Qzorg_Db') ) {
    class Qzorg_Db
    {

        private static $delete = array();

        /**
         * @since 2.1.0
         * @return Question types list
         */

        protected static function get_questios( $title = "" ) {
            global $wpdb;
            
            $where = "";
            $offset = 0;
            $orderby = "question_id";
            $order = "desc";
            $per_page = intval($title['perpage']);
            
            if ( isset($title['filter']) && "" != $title['filter'] ) {
                $filter = '%'. $title['filter'] . '%';
                $where .= $wpdb->prepare(" AND question_title LIKE %s ", $filter);
            }
            
            $q_results_obj = $wpdb->get_results($wpdb->prepare(
                "SELECT question_id, quiz_id, question_title, question_tools FROM {$wpdb->prefix}qzorg_questions WHERE 1=1 {$where} AND question_title != '' ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ));

            if ( ! empty($q_results_obj) ) {
                $quiz_ids = array_unique(array_column($q_results_obj, 'quiz_id'));
                $quiz_name_array = array();
                foreach ( $quiz_ids as $key => $value ) {
                    $quiz_results_obj = $wpdb->get_row($wpdb->prepare(
                        "SELECT quiz_name FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                        $value
                    ));
                    $quiz_name_array[ $value ] = $quiz_results_obj->quiz_name;
                }
                foreach ( $q_results_obj as &$item ) {
                    $item->quiz_id = isset($quiz_name_array[ $item->quiz_id ]) ? $quiz_name_array[ $item->quiz_id ] : "-";
                    $item->question_title = htmlspecialchars(html_entity_decode($item->question_title, ENT_QUOTES), ENT_QUOTES);
                }
            }
            
            return $q_results_obj;
        }
        
        /**
         * @since  2.1.0
         */

        public static function process( $ids ) {
            self::$delete = $ids;
        }
        
        /**
         * @since  2.1.0
         */
        public function delete() {
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_quizzes";
            if ( ! empty(self::$delete) ) {
                foreach ( self::$delete as $key => $e_id ) {
                    if ( Qzorg_Questions::remove($e_id) ) {
                        $wpdb->delete(
                            $table,
                            array( 
                                'quiz_id' => $e_id, 
                            ),
                        );
                    }
                }
                wp_send_json_success([
                    'message' => __('Quiz Deleted!' , 'quiz-organizer'),
                ], 200);
            }
        }

        /**
         * @since 2.1.0
         * @param $id
         * @param Array $order 
         */

        public function set_question_order( $order = array(), $id = 0 ) {
            if ( 0 === $id ) { return; }
            global $wpdb;
            $quiz_obj = $wpdb->get_row( $wpdb->prepare(
                "SELECT other_tools FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                $id
            ) );
            $other_tools = maybe_unserialize($quiz_obj->other_tools);
            $other_tools['question_order'] = $order;
            $status = $wpdb->update( $wpdb->prefix."qzorg_quizzes", array(
                'other_tools' => maybe_serialize( $other_tools ),
            ), array( 'quiz_id' => $id ) );
            wp_send_json_success([]);
        }

        

        /**
         * Register new quiz
         * @since  2.1.0
         * @param $question_data, $quiz_id
         * @return wp_json_encode
         */
        public function update_quiz_qustions( $question_data, $quiz_id ) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_questions";
            if ( 0 == $quiz_id ) {
                echo wp_json_encode(
                    array(
                        'success' => 0,
                        'message' => __('Something wrong please reload the page !', 'quiz-organizer'),
                    )
                );
                die;
            }
            $tools = [];
            $tools['categories'] = isset($question_data['categories']) ? $question_data['categories'] : array();
            $tools['right_info_qzorgmessage'] = isset($question_data['right_info_qzorgmessage']) ? $question_data['right_info_qzorgmessage'] : "";
            $tools['wrong_info_qzorgmessage'] = isset($question_data['wrong_info_qzorgmessage']) ? $question_data['wrong_info_qzorgmessage'] : "";
            $tools['extra_info_qzorgmessage'] = isset($question_data['extra_info_qzorgmessage']) ? $question_data['extra_info_qzorgmessage'] : "";
            $tools['display_flex'] = "" == $question_data['display_flex'] ? "" : 1;
            $tools['required_question'] = "" == $question_data['required_question'] ? "" : 1;
            $wpdb->update(
                $table_name,
                array(
                    'quiz_id'         => $quiz_id,
                    'question_title'  => $question_data['question_title_qzorgmessage'],
                    'question_type'   => $question_data['question_type'],
                    'question_answer' => isset($question_data['answers']) ? maybe_serialize($question_data['answers']) : array(),
                    'question_tools'  => maybe_serialize($tools),
                    'question_image'  => $question_data['question_image'],
                    'updated_at'      => current_time('mysql'),
                ),
                array(
                    'question_id' => $question_data['question_id'],
                ),
            );
            echo wp_json_encode(
                array(
                    'success' => 1,
                    'message' => __('Question details saved!', 'quiz-organizer'),
                )
            );
            die;
        }

        

        /**
         * @since 1.0.0
         * @param $quiz_id
         * @uses Retrive quiz data
         */

        public function qzorg_get_quiz_data( $quiz_id = 0 ) {
            if ( 0 === $quiz_id ) { return; }
            global $wpdb;
            $q_results_obj = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                $quiz_id
            ));            
            if ( $q_results_obj ) {
                return $q_results_obj;
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

        

        /**
         * @since 1.0.0
         * @param $quiz
         * @return : Retuens questions list
         */

        public function questions( $quiz = 0 ) {
            if ( 0 === $quiz->quiz_id ) { return; }
            global $wpdb;
            $questions = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}qzorg_questions WHERE quiz_id = %d",
                $quiz->quiz_id
            ) );
            $quiz_obj = $wpdb->get_row( $wpdb->prepare(
                "SELECT other_tools, quiz_tools FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                $quiz->quiz_id
            ) );
            $unserialize = maybe_unserialize($quiz_obj->quiz_tools);
            $question_order = maybe_unserialize($quiz_obj->other_tools);
            $newOrder = $question_order['question_order'];
            if ( isset($unserialize['quizpage']['randomize_options']) && (3 == $unserialize['quizpage']['randomize_options'] || 1 == $unserialize['quizpage']['randomize_options']) ) { 
                $newOrder = Qzorg_Defaults::qzorg_random_q($newOrder);
            }
            $managedQuestions = $this->manage_question_order($questions, $newOrder);
            if ( isset($unserialize['quizpage']['category_wise_question']) && "yes" == $unserialize['quizpage']['category_wise_question'] ) {
                $managedQuestions = $this->category_wise($managedQuestions);
            }
            $overridemanagedQuestions = $unserialize['quizpage']['question_per_page'] ? $unserialize['quizpage']['question_per_page'] : 0;
            if ( "" != $overridemanagedQuestions && 0 != $overridemanagedQuestions && 0 < $overridemanagedQuestions ) {
                $managedQuestions = $this->per_page_order($managedQuestions, $unserialize['quizpage']['question_per_page']);
            }
            if ( $questions ) {
                return $managedQuestions;
            }
        }

        
        public function category_wise( $managedQuestions ) {
            $submerged = $counter = $questionIdAndTools = [];
            foreach ( $managedQuestions as $subArray ) {
                $counter[] = count($subArray);
                foreach ( $subArray as $item ) {
                    $submerged[ $item->question_id ] = $item;
                    $question_tools = maybe_unserialize($item->question_tools);
                    if ( ! empty($question_tools['categories']) && ! empty($question_tools['categories'][0]) ) {
                        $category = $question_tools['categories'][0];
                    }else {
                        $category = 0;
                    }
                    $questionIdAndTools[] = [
                        'question_id'    => $item->question_id,
                        'question_tools' => $category,
                    ];
                }
            }

            $merged = $groupedArray = [];
            $toolsZeroItems = [];

            foreach ( $questionIdAndTools as $item ) {
                $questionTools = $item['question_tools'];
                
                if ( 0 == $questionTools ) {
                    $toolsZeroItems[] = $item;
                } else {
                    if ( ! isset($groupedArray[ $questionTools ]) ) {
                        $groupedArray[ $questionTools ] = [];
                    }
                    $groupedArray[ $questionTools ][] = $item;
                }
            }
            
            $groupedArray = array_merge($groupedArray, [ 0 => $toolsZeroItems ]);

            foreach ( $groupedArray as $item ) {
                $merged = array_merge($merged, $item);
            }
            $finalarray = [];
            $current = 0;
            foreach ( $counter as $count ) {
                for ( $i = 0; $i < $count; $i++ ) { 
                    $finalarray[ $current ][] = $submerged[ $merged[0]['question_id'] ];
                    array_shift($merged);
                }
                $current++;
            }

            return $finalarray;
        }

        

        /**
         * @since 1.0.0
         * @param $originalArray question original array from database
         * @param $questionsPerPage parameter for show page wise queations
         * @return structure for create page order
         */

        function per_page_order( $originalArray, $questionsPerPage ) {
            
            $reIndexed = [];
            $pageLoop = 0;
            $qCounter = 0;

            foreach ( $originalArray as $page ) {
                foreach ( $page as $question ) {
                    $reIndexed[ $pageLoop ][] = $question;
                    $qCounter++;

                    if ( $qCounter >= $questionsPerPage ) {
                        $qCounter = 0;
                        $pageLoop++;
                    }
                }
            }
            return $reIndexed; 
        }
        

        /**
         * @since 1.0.0
         * @param Object $question
         * @param Bool $status  yes || no
         */

        public static function preview_question( $q, $status ) {
            $class = $str = "";
            $answer_text = htmlspecialchars($q['ans_text'], ENT_QUOTES);
            if ( "correct" == $q['answer'] ) {
                $classafter = "qzorg-correct-answer-after";
                $class = "qzorg-correct-r-answer";
            } elseif ( "incorrect" == $q['answer'] ) {
                $classafter = "qzorg-incorrect-answer-after";
                $class = "qzorg-incorrect-r-answer";
            } elseif ( "unanswered" == $q['answer'] ) {
                $classafter = "qzorg-unanswered-answer-after";
                $class = "qzorg-unanswered-r-answer";
                $answer_text = esc_html__('No response provided', 'quiz-organizer');
            }
            $str .= '<div class="'.esc_attr($classafter).' qzorg-wrapper-r-answer" data-id="'.esc_attr($q['question_id']).'">';
                $str .= '<div class="qzorg-r-question">'.htmlspecialchars($q['question_title'], ENT_QUOTES).'</div>';

                if ( isset($q['image']) && "" != $q['image'] ) {
                    $url = wp_get_attachment_image_src($q['image'], 'full')[0];
                    $str .= '<img class="qzorg-question-image" src="'.esc_url($url).'">';
                }
                
                if ( ! empty($q['answer_options']) ) {
                    $inner_class = "yes" == $status ? 'qzorg-correct-r-answer' : '';
                    foreach ( $q['answer_options'] as $ek => $ea ) {
                        $str .= '<div class="qzorg-quiz-r-answer-options '.(1 == $ea ? esc_attr($inner_class) : '').'">' . htmlspecialchars($ek, ENT_QUOTES) . '</div>';
                    }
                }

                $str .= '<div class="qzorg-r-by-user">'.esc_html__('Answer provided : ', 'quiz-organizer').'<span class="qzorg-r-user-answer '.esc_attr($class).'">'.$answer_text.'</span></div>';
                
            $str .= '</div>';
            return $str;
        }

    }
}