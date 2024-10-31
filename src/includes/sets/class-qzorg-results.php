<?php

if ( ! defined('ABSPATH') ) {
    exit;
}

/**
* Class to manage all shortcode.
* @since 1.0.0
*/
if ( ! class_exists('Qzorg_Results') ) {
    class Qzorg_Results
    {
        private $questions = [];
        private $f = 'get_';
        private $quiz_id = 0;
        private $quiz_name;
        private $quiz_obj;
        private $user;
        private $structure;
        private $matrix;
        private $tools;
        public $username = "";
        public $useremail = "";
        public $duration = 0;
        public $quiz_tools;
        public $correct_answer = "no";

        public function __construct() {
            $this->init();
        }

        private function init() {
            add_filter('before_qzorg_quiz_submission', array( $this, 'qzorg_update_submission' ), 10, 1);
        }

        /**
         * Assign quizId
         * @since  1.0.0
         * @param  int $id.
         * @return void
         */
        public function setQuizID( $id ) {
            $this->quiz_id = $id;
        }

        /**
         * Retrive quizId
         * @since  1.0.0
         * @return false|int
         */
        public function getQuizID() {
            return $this->quiz_id;
        }

        /**
         * @since 1.0.0
         */
        public function evaluate( $userAnswer ) {

            global $Quiz_Organizer, $ans_keys;

            if ( $userAnswer['qzorg_id'] == $userAnswer['spirit'] ) {
                $quiz_id = $userAnswer['qzorg_id'];
            }
            $final = array();
            $this->setQuizID($quiz_id);
            $this->structure = $Quiz_Organizer;
            $this->quiz_obj = $this->qzorg_get_quiz_result_info($this->getQuizID());
            $this->quiz_tools = maybe_unserialize($this->quiz_obj->quiz_tools);
            if ( isset($this->quiz_tools['advance']['all_correct_answer']) && "yes" == $this->quiz_tools['advance']['all_correct_answer'] ) {
                $this->correct_answer = "yes";
            }
            $this->username = isset($userAnswer['qzorg-username']) ? $userAnswer['qzorg-username'] : "";
            $this->useremail = isset($userAnswer['qzorg-useremail']) ? $userAnswer['qzorg-useremail'] : "";
            if ( isset($userAnswer['qzorgquiztimer']) && 'null' != $userAnswer['qzorgquiztimer'] ) {
                $this->duration = $userAnswer['qzorgquiztimer'];
            }
            $is_submit = apply_filters('before_qzorg_quiz_submission', $quiz_id);
            $question_obj = $Quiz_Organizer->helperObj->qzorg_result_question_list($quiz_id);
            if ( ! empty($question_obj) ) {
                foreach ( $question_obj as $key => $question ) {
                    $a = $question->question_type . $question->question_id;
                    if ( isset($question->question_title) && "" != $question->question_title ) {
                        $answer_options = maybe_unserialize($question->question_answer);
                        $final[ $question->question_id ]['question_id'] = $question->question_id;
                        $final[ $question->question_id ]['question_title'] = $question->question_title;
                        $final[ $question->question_id ]['image'] = $question->question_image;
                        $final[ $question->question_id ]['user_answer'] = $ans_keys[ $a ];
                        $obj = $this->f . $question->question_type;
                        $data = $this->$obj($ans_keys[ $a ], $answer_options);
                        $status = 'unanswered' != $data['status'] ? "taken" : "untaken";
                        $final[ $question->question_id ]['answer'] = $data['status'];
                        $final[ $question->question_id ]['status'] = $status;
                        $final[ $question->question_id ]['earn_points'] = $data['earn_points'];
                        $final[ $question->question_id ]['max_points'] = $data['max_points'];
                        $final[ $question->question_id ]['min_points'] = $data['min_points'];
                        $final[ $question->question_id ]['ans_text'] = $data['ans_text'];
                        $final[ $question->question_id ]['answer_options'] = ! empty($answer_options) ? array_column($answer_options, 'is_correct', 'answer') : [];
                    }
                }
                $this->questions = $final;
                return $this->calculate();
            }
            return array();
        }

        public function max_points( $array, $is_multi = 0 ) {
            $maxPoints = 0;
            $maxPointsArray = array_column(array_filter($array, function ( $item ) {
                return 1 == $item['is_correct'];
            }), 'points');

            if ( ! empty($maxPointsArray) ) {
                if ( 0 == $is_multi ) {
                    $maxPoints = max($maxPointsArray);
                } else {
                    $maxPoints = array_sum($maxPointsArray);
                }
            }
            return $maxPoints;
        }

        public function min_points( $array, $is_multi = 0 ) {
            return min(array_column($array, "points"));
        }

        protected function get_drop_down( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $answers[ $userop ]['answer'];
                    $points = $answers[ $userop ]['points'];
                    if ( $ans_key == $userop && 1 == $answers[ $userop ]['is_correct'] ) {
                        $status = 'correct';
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_radio( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $answers[ $userop ]['answer'];
                    $points = $answers[ $userop ]['points'];
                    if ( $ans_key == $userop && 1 == $answers[ $userop ]['is_correct'] ) {
                        $status = 'correct';
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_singlelinetext( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $userop;
                    if ( 0 == strcasecmp($userop, $ans_value['answer']) ) {
                        $points = $ans_value['points'];
                        if ( 1 == $ans_value['is_correct'] ) {
                            $status = 'correct';
                        }
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_paragraphtext( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $userop;
                    if ( 0 == strcasecmp($userop, $ans_value['answer']) ) {
                        $points = $ans_value['points'];
                        if ( 1 == $ans_value['is_correct'] ) {
                            $status = 'correct';
                        }
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_number( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $userop;
                    if ( $userop == $ans_value['answer'] ) {
                        $points = $ans_value['points'];
                        if ( 1 == $ans_value['is_correct'] ) {
                            $status = 'correct';
                        }
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_date( $userop, $answers ) {
            $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                foreach ( $answers as $ans_key => $ans_value ) {
                    $ans_text = $userop;
                    if ( strtotime($userop) === strtotime($ans_value['answer']) ) {
                        $points = $ans_value['points'];
                        $status = 'correct';
                    }
                }
            }
            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        protected function get_checkbox( $userop, $answers ) {
            $correct_answer = $total_answer = $points = 0;
            $ans_text = "";
            if ( ! isset($userop) || "" == $userop ) {
                $status = 'unanswered';
            } else {
                $status = 'incorrect';
                if ( "yes" == $this->correct_answer ) {
                    foreach ( $answers as $ans_key => $ans_value ) {
                        if ( 1 == $ans_value['is_correct'] ) {
                            $total_answer++;
                        }
                        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- Not using strict comparison for in_array; third argument should be set to true.
                        if ( in_array($ans_key, $userop) ) {
                            $ans_text = $answers[ $ans_key ]['answer'] . ' , ' . $ans_text;
                            if ( 1 == $ans_value['is_correct'] ) {
                                $correct_answer++;
                            } else {
                                $correct_answer--;
                            }
                            $points = $ans_value['points'] + $points;
                        }
                    }
                    if ( $total_answer == $correct_answer ) {
                        $status = 'correct';
                    }
                } else {
                    $count = 0;
                    foreach ( $answers as $ans_key => $ans_value ) {
                        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- Not using strict comparison for in_array; third argument should be set to true.
                        if ( in_array($ans_key, $userop) ) {
                            $coma = '';
                            if ( 0 < $count ) {
                                $coma = ', ';
                            }
                            $ans_text = $answers[ $ans_key ]['answer'] . $coma . $ans_text;
                            if ( 1 == $ans_value['is_correct'] ) {
                                $status = 'correct';
                            }
                            $points = $ans_value['points'] + $points;
                            $count++;
                        }
                    }
                }
            }

            return array(
                'status'      => $status,
                'earn_points' => $points,
                'max_points'  => $this->max_points($answers, 1),
                'min_points'  => $this->min_points($answers),
                'ans_text'    => $ans_text,
            );
        }

        /**
         * @since 1.0.0
         */
        public function calculate() {
            $matrixMetrics = $this->structure->helperObj->calculate($this->questions, $this->quiz_tools);
            global $wpdb;
            $table_submission = $wpdb->prefix . 'qzorg_submissions';
            $user_info = Qzorg_Phtml::qzorg_get_current_user();
            $this->user = $user_info;
            $this->user['username'] = $this->username;
            $this->user['email'] = $this->useremail;
            // unset($this->quiz_obj->quiz_tools);
            $empty = [];
            foreach ( $this->quiz_tools as $key => $value ) {
                foreach ( $value as $innerkey => $innervalue ) {
                    $empty[ $innerkey ] = $innervalue;
                }
            }
            // MERGE FULL QUIZ OBJ HERE
            $a = array_merge( (array) $this->quiz_obj, $empty);
            $this->tools = (object) $a;
            $matrixMetrics['questions'] = $this->questions;
            $get_unique_id = $matrixMetrics['get_unique_id'];
            unset($matrixMetrics['get_unique_id']);
            if ( isset($this->tools->save_result_to_db) && "yes" == $this->tools->save_result_to_db ) {
                $data = array(
                    'quiz_id'      => $this->getQuizID(),
                    'user_id'      => $user_info['user_id'],
                    'user_ip'      => Qzorg_Phtml::qzorg_set_user_ip(),
                    'quiz_name'    => $this->quiz_obj->quiz_name,
                    'quiz_type'    => $this->quiz_obj->quiz_type,
                    'user_name'    => $this->user['username'],
                    'user_email'   => $this->user['email'],
                    'duration'     => $this->quiz_duration(),
                    'totalpoints'  => $matrixMetrics['total_points'],
                    'others'       => maybe_serialize($matrixMetrics),
                    'redirect_url' => "",
                    'unique_id'    => $get_unique_id,
                    'deleted_at'   => null,
                );

                $wpdb->insert(
                    $table_submission,
                    $data,
                    array(
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                    ),
                );
            }
            $matrixMetrics['unique_id'] = $get_unique_id;
            $this->matrix = $matrixMetrics;
            return $this->result();
        }

        public function quiz_duration() {

            $hours = floor($this->duration / 3600);
            $minutes = floor(($this->duration % 3600) / 60);
            $remainingSeconds = $this->duration % 60;

            return sprintf("%02d:%02d:%02d", $hours, $minutes, $remainingSeconds);
        }

        
        /**
         * @since 1.0.0
         * @param $quiz_id
         * @return : Retuens quiz data for result page
         */

        public function qzorg_get_quiz_result_info( $quiz_id = 0 ) {
            if ( 0 === $quiz_id ) { return; }
            global $wpdb;
            $q_results_obj = $wpdb->get_row($wpdb->prepare(
                "SELECT quiz_name, quiz_type, quiz_tools, created_at FROM {$wpdb->prefix}qzorg_quizzes WHERE quiz_id = %d",
                $quiz_id
            ));
            if ( $q_results_obj ) {
                $quiz_tools = maybe_unserialize($q_results_obj->quiz_tools);
                if ( isset($quiz_tools['advance']['quiz_submission_limit']) && 0 < $quiz_tools['advance']['quiz_submission_limit'] ) {
                    $results_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) AS record_count FROM {$wpdb->prefix}qzorg_submissions WHERE quiz_id = %d",
                        $quiz_id
                    ));
                    if ( $results_count > $quiz_tools['advance']['quiz_submission_limit'] ) {
                        wp_send_json_error([
                            'message'   => $quiz_tools['othertext']['limit_submission_text'],
                            'overlimit' => 1,
                        ], 403);
                    }
                }
                return $q_results_obj;
            }
        }

        /**
         * @version 1.0.0
         * @uses ARRAY THAT IS USED IN EACH QUESTION
         * @param [question_id] => 1
         * @param [question_title] => First question
         * @param [user_answer] => 0
         * @param [answer] => correct
         * @param [status] => taken
         * @param [earn_points] => 20
         * @param [max_points] => 20
         * @param [ans_text] => Answer
         */

        private function result() {
            $result_page = $this->structure->helperObj->result($this->tools, $this->user, $this->structure);

            if ( "yes" == $this->tools->send_email_to_users || "yes" == $this->tools->send_email_to_admin ) {
                $email = $this->tools->send_email_qzorgmessage;
                $email = apply_filters('qzorg_static_results_after', $email, $this->tools, $this->matrix);
                $email = apply_filters('qzorg_results_page_after', $email, $this->tools, $this->user, $this->questions);
                $return = $this->email($email, $result_page);
            } else {
                $return = $result_page;
            }

            if ( isset($this->tools->redirect_after_submit) && "" != $this->tools->redirect_after_submit && wp_http_validate_url($this->tools->redirect_after_submit) ) {
                apply_filters('qzorg_after_db_submission', $this->tools);
            }
            return $return;
        }

        /**
         * @since 1.0.0
         */
        private function email( $message, $result_page ) {
            $attachments = [];
            $condition = 1;

            add_filter('wp_mail_content_type', 'qzorg_set_html_content_type');

            $from_email = get_option('admin_email');
            if ( $this->tools->send_email_from && is_email($this->tools->send_email_from) ) {
                $from_email = $this->tools->send_email_from;
            }
            $subject = apply_filters('qzorg_email_subject', $this->tools->send_email_subject, $this->questions, $this->tools, $this->user);
            $headers = 'From:' . $this->tools->send_email_from_user . ' <' . $from_email . '>' . "\r\n";
            $mailMessage = Qzorg_Defaults::setup_email($message, $subject);
            // VERIFY SEND EMAIL OPTION
            if ( "yes" == $this->tools->send_email_to_users ) {

                $to_email = "";
                $contact_email = $this->useremail; // SET EMIAL FROM CONTACT FORM
                if ( isset($contact_email) && "" !== trim($contact_email) && is_email($contact_email) ) {
                    $to_email = $contact_email; // contact email
                    wp_mail($to_email, $subject, $mailMessage, $headers, $attachments);
                }
            }

            if ( "yes" == $this->tools->send_email_to_admin ) {
                $admin_email = $from_email;
                if ( is_email($admin_email) ) {
                    wp_mail($admin_email, $subject, $mailMessage, $headers, $attachments);
                }
            }

            remove_filter('wp_mail_content_type', 'qzorg_set_html_content_type');

            return $result_page;
        }

        /**
         * @since 1.0.0
         */
        public function qzorg_update_submission( $id ) {
            global $wpdb;
            $results = $wpdb->query( $wpdb->prepare( 
                "UPDATE {$wpdb->prefix}qzorg_quizzes SET quiz_attend = quiz_attend + 1 WHERE quiz_id = %d", $this->getQuizID() ) 
            );
            return 1;
        }

    }
}
