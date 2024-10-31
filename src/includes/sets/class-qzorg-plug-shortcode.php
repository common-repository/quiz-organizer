<?php

if ( ! defined('ABSPATH') ) {
    exit;
}

/**
* Class to manage shortcode.
*
* @since 1.0.0
*/
if ( ! class_exists('Qzorg_Plug_Shortcode') ) {
    class Qzorg_Plug_Shortcode
    {
        private $db;
        private $table_quiz = '';
        private $table_question = '';
        private $b = 'set_';
        private $questions = 0;
        private $validate = 0;
        public $page_no = "no";
        public $unserialize;
        public $question_no = "no";
        public $structure;
        public $category = "no";
        public $more_settings = [];
        private $quiz_id = 0;

        public function __construct() {
            global $wpdb, $Quiz_Organizer;
            $this->structure = $Quiz_Organizer;
            $this->db = $wpdb;
            $this->table_quiz = $wpdb->prefix . 'qzorg_quizzes';
            $this->table_question = $wpdb->prefix . 'qzorg_questions';
            $this->init();
        }

        /**
         * Initialize dependencies and hooks form constructor
         * @since 1.0.0
         * @uses init()
         * @return void
         */

        private function init() {
            add_shortcode('quiz-organizer', array( $this, 'qzorg_quiz_questions' ));
            add_filter('before_qzorg_quiz_questions', 'qzorg_validate_basic_settings', 10, 2);
            add_filter('qzorg_update_db_data', 'qzorg_change_visits', 10, 1);
            add_action('wp_ajax_submit_quiz_form', array( $this, 'submit_quiz_form' ));
            add_action('wp_ajax_nopriv_submit_quiz_form', array( $this, 'submit_quiz_form' ));
            add_action('wp_ajax_display_instant_results', array( $this, 'instant_result' ));
            add_action('wp_ajax_nopriv_display_instant_results', array( $this, 'instant_result' ));
        }

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
         * @return : Retuens instant result for question
         */

        public function instant_result() {
            if ( isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'instant-results-nonce') ) {
                $varify = isset($_POST['check']) ? sanitize_text_field(wp_unslash($_POST['check'])) : "";
                $d_message = isset($_POST['d_message']) ? sanitize_text_field(wp_unslash($_POST['d_message'])) : "";
                $question_id = isset($_POST['question_id']) ? sanitize_text_field(wp_unslash($_POST['question_id'])) : "";
                if ( 4 != $varify ) {
                    $userop = isset($_POST['userop']) ? sanitize_text_field(wp_unslash($_POST['userop'])) : "";
                }
                $answer_options = $this->qzorg_get_single_question_answers($question_id);
                $answer_array = maybe_unserialize($answer_options->question_answer);
                $question_tools = maybe_unserialize($answer_options->question_tools);
                $status = '';
                if ( 0 == $varify || 1 == $varify || 3 == $varify ) {
                    foreach ( $answer_array as $ans_key => $ans_value ) {
                        if ( (0 == $varify && $ans_key == $userop && 1 == $answer_array[ $userop ]['is_correct'])
                            || (1 == $varify && 0 == strcasecmp($userop, $ans_value['answer']) && 1 == $ans_value['is_correct'])
                            || (3 == $varify && $userop == $ans_value['answer'] && 1 == $ans_value['is_correct'])
                        ) {
                            $status = 'true';
                            break;
                        }
                    }
                } elseif ( 2 == $varify ) {
                    foreach ( $answer_array as $ans_key => $ans_value ) {
                        if ( strtotime($userop) === strtotime($ans_value['answer']) ) {
                            $status = 'true';
                            break;
                        }
                    }
                } elseif ( 4 == $varify ) {
                    $other = isset($_POST['other']) ? sanitize_text_field(wp_unslash($_POST['other'])) : "no";
                    $userop = isset( $_POST['userop'] ) ? array_map('sanitize_text_field', wp_unslash( $_POST['userop'])) : array(); 
                    if ( "yes" == $other ) {
                        $correct_answer = $total_answer = 0;
                        foreach ( $answer_array as $ans_key => $ans_value ) {
                            if ( 1 == $ans_value['is_correct'] ) {
                                $total_answer++;
                            }
                            // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- Not using strict comparison for in_array; third argument should be set to true.
                            if ( ! empty($userop) && in_array($ans_key, $userop) ) {
                                if ( 1 == $ans_value['is_correct'] ) {
                                    $correct_answer++;
                                } else {
                                    $correct_answer--;
                                }
                            }
                        }
                        if ( $total_answer == $correct_answer ) {
                            $status = 'true';
                        }
                    } else {
                        foreach ( $answer_array as $ans_key => $ans_value ) {
                            // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- Not using strict comparison for in_array; third argument should be set to true.
                            if ( ! empty($userop) && in_array($ans_key, $userop) ) {
                                if ( 1 == $ans_value['is_correct'] ) {
                                    $status = 'true';
                                    break;
                                }
                            }
                        }
                    }
                }
                if ( "" == $status ) {
                    $info = (isset($question_tools['wrong_info_qzorgmessage']) && "" != $question_tools['wrong_info_qzorgmessage']) ? $question_tools['wrong_info_qzorgmessage'] : $answer_options->othertext['wrong_answer_text'];
                    $status = "false";
                } else {
                    $info = (isset($question_tools['right_info_qzorgmessage']) && "" != $question_tools['right_info_qzorgmessage']) ? $question_tools['right_info_qzorgmessage'] : $answer_options->othertext['right_answer_text'];
                }
                wp_send_json_success([
                    'status' => $status,
                    'info'   => $info,
                ], 200);
            }
        }

        public function submit_quiz_form() {
            if ( ! isset($_POST['spirit']) || ! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'qzorg_form_quiz_' . intval($_POST['spirit'])) ) {
                wp_send_json_error([
                    'message' => __('Nonce Validation Fail !', 'quiz-organizer'),
                    'nonce'   => 1,
                ], 403);
            }
            $evaluation_array = array();
            $submission = array();
            $evaluation_array['qzorg_id'] = isset($_REQUEST['qzorg_id']) ? sanitize_text_field(wp_unslash($_REQUEST['qzorg_id'])) : "";
            $evaluation_array['spirit'] = isset($_POST['spirit']) ? sanitize_text_field(wp_unslash($_POST['spirit'])) : "";

            if ( $evaluation_array['qzorg_id'] != $evaluation_array['spirit'] ) {
                wp_send_json_error([
                    'message' => __('Something Wrong Please Try Again !', 'quiz-organizer'),
                ], 403);
            }

            $evaluation_array['qzorg-username'] = isset($_POST['qzorg-username']) ? sanitize_text_field(wp_unslash($_POST['qzorg-username'])) : "";
            $evaluation_array['qzorg-useremail'] = isset($_POST['qzorg-useremail']) ? sanitize_email(wp_unslash($_POST['qzorg-useremail'])) : "";
            $evaluation_array['quiz_time'] = isset($_POST['quiz_time']) ? sanitize_text_field(wp_unslash($_POST['quiz_time'])) : "";
            $evaluation_array['qzorgquiztimer'] = isset($_POST['qzorgquiztimer']) ? sanitize_text_field(wp_unslash($_POST['qzorgquiztimer'])) : "";
            
            global $Quiz_Organizer, $ans_keys;
            $ans_keys = [];
            $question_obj = $Quiz_Organizer->helperObj->qzorg_result_question_list($evaluation_array['spirit']);
            if ( ! empty($question_obj) ) {
                foreach ( $question_obj as $key => $question ) {
                    $ans_keys[ $question->question_type . $question->question_id ] = $Quiz_Organizer->defaultsObj->verify($question);
                }
                $submission = $Quiz_Organizer->resultsObj->evaluate($evaluation_array);
            }

            wp_send_json_success([
                'message' => __('Success !', 'quiz-organizer'),
                'results' => ! empty($submission) ? 1 : 0,
                'display' => $submission,
            ], 200);

        }

        /**
        * Quiz structure
        * @since  1.0.0
        * @param $atts [ shortcode parameters [ 0 ] ]
        * @return : Display Quiz
        */

        public function qzorg_quiz_questions( $atts ) {
            if ( ! ( ! empty($atts) && isset($atts['id'])) ) {
                return;
            }
            $default_args = shortcode_atts(array(
                'id'   => 0,
                'link' => false,
            ), $atts);
            $quiz_data = '';
            $custom_css = "";
            $spirit = $id = $atts['id'];
            $db = new Qzorg_Db();
            $quiz_obj = $db->qzorg_get_quiz_data($id);
            ob_start();
            if ( $quiz_obj ) {
                $this->setQuizID($id);
                $this->unserialize = $unserialize = maybe_unserialize($quiz_obj->quiz_tools);
                wp_enqueue_script('qzorg_quiz__js', QZORG_PUBLIC_JS_URL . '/qzorg-tmpl-quiz.js', array( 'jquery', 'backbone' ), gmdate('h-m-s'), true);
                if ( isset($unserialize['advance']['required_math_js']) && "yes" == $unserialize['advance']['required_math_js'] ) {
                    wp_enqueue_script('qzorg_math__js', QZORG_PUBLIC_JS_URL . '/qzorg-math.min.js', array(), gmdate('h-m-s'), false);
                }
                wp_enqueue_style('qzorg_quiz__css', QZORG_PUBLIC_CSS_URL . '/qzorg-tmpl-default.css', array(), gmdate('h-m-s'));
                $data = $unserialize['styles'];
                $data = wp_parse_args( $data, Qzorg_Defaults::get_default_colors() );
                $background_image = (isset($data['quiz_background_image']) && $data['quiz_background_image']) ? "background: url(".esc_url($data['quiz_background_image']).") repeat;" : "";
                $quiz_animation = (isset($data['quiz-animation']) && "noanimation" != $data['quiz-animation']) ? "animation: ".$data['quiz-animation']." 60s linear infinite;" : "";
                $custom_css .= "
                .quiz-organizer-wrapper.qzorg-quiz-wrapper".$id." {
                    background-color: ". $data['quiz_bg_color'] . ";
                    color: ". $data['quiz_text_color'] . ";
                    ".$background_image.$quiz_animation.";
                }
                .qzorg-question-wrapper .qzorg-q-title-".$id." {
                    color: ". $data['question_text_color'] . ";
                }
                .quiz-organizer-wrapper .qzorg-default-button-".$id." {
                    color: ". $data['button_text_color'] . ";
                    background-color: ". $data['button_bg_color'] . ";
                }";
                wp_add_inline_style('qzorg_quiz__css', $custom_css);
                wp_localize_script('qzorg_quiz__js', 'spirit_form', array(
                    'ajaxurl'        => admin_url('admin-ajax.php'),
                    'action'         => 'submit_quiz_form',
                    'instant_result' => 'display_instant_results',
                    'nonce'          => wp_create_nonce('instant-results-nonce'),
                ));
                if ( (isset($unserialize['advance']['quiz_end_date']) && "" != $unserialize['advance']['quiz_end_date']) && strtotime($unserialize['advance']['quiz_end_date']) <= strtotime(gmdate('d-m-Y')) ) {
                    $this->validate = 2;
                }
                if ( strtotime($unserialize['advance']['quiz_start_date']) > strtotime(gmdate('d-m-Y')) ) {
                    $this->validate = 1;
                }
                if ( isset($this->unserialize['general']['show_question_number']) && "yes" == $this->unserialize['general']['show_question_number'] ) {
                    $this->question_no = $this->unserialize['general']['show_question_number'];
                }
                if ( isset($this->unserialize['quizpage']['display_cat_name']) && "yes" == $this->unserialize['quizpage']['display_cat_name'] ) {
                    $this->category = $this->unserialize['quizpage']['display_cat_name'];
                }
                if ( apply_filters('before_qzorg_quiz_questions', $quiz_obj, $unserialize) && 0 == $this->validate ) { 
                    $this->questions = 0;
                    $quiz_data .= '<div class="quiz-organizer-wrapper qzorg-quiz-container qzorg-quiz-main qzorg-quiz-wrapper'.esc_attr($id).'" data-type="'.esc_attr($quiz_obj->quiz_type).'" data-spirit="'.esc_attr($spirit).'" >';
                        $quiz_data .= '<form id="qzorg-quiz-form'.esc_attr($id).'" methd="POST" class="qzorg-quiz-form qzorg-quiz-form'.esc_attr($id).'" data-id="'.esc_attr($spirit).'" >';
                            $quiz_data .= '<div class="qzorg-before-questions">';
                                $quiz_data .= $this->timer($id, $unserialize);
                                $quiz_data .= $this->progressbar($id, $unserialize);
                                $quiz_data .= $this->quiz_info($quiz_obj, $unserialize);
                            $quiz_data .= '</div>';
                            if ( $quiz_obj->questions ) {
                                foreach ( $quiz_obj->questions as $key => $q_obj ) { 
                                    $quiz_data .= '<div class="qzorg-page-container qzorg-page-'.esc_attr($key + 1).' qzorg-page-wrapper" data-id="'.esc_attr($key + 1).'" data-extra="'.esc_attr($id).'">';
                                        if ( "yes" == $this->page_no ) { 
                                            $quiz_data .= '<p class="qzorg-page-no">'.esc_html("Page : " . $key + 1 . "/" . count($quiz_obj->questions)).'</p>';
                                        }
                                        foreach ( $q_obj as $key => $e_q ) {
                                            $answers = maybe_unserialize($e_q->question_answer);
                                            if ( isset($this->unserialize['quizpage']['randomize_options']) && (3 == $this->unserialize['quizpage']['randomize_options'] || 2 == $this->unserialize['quizpage']['randomize_options']) ) {
                                                $answers = Qzorg_Defaults::qzorg_random($answers);
                                            }
                                            if ( "" != $e_q->question_title ) {
                                                $obj = $this->b . $e_q->question_type;
                                                $quiz_data .= $this->$obj($e_q->question_type, $e_q, $answers);
                                                $this->questions++;
                                            }
                                            $unserialize_data = maybe_unserialize($e_q->question_tools);
                                            $this->more_settings[ $e_q->question_id ][] = isset($unserialize_data['required_question']) ? $unserialize_data['required_question'] : 0;
                                        } 
                                    $quiz_data .= '</div>';
                                }
                                $quiz_data .= $this->pagination($quiz_obj, $unserialize);
                            }
                        $quiz_data .= '</form>';
                        $quiz_data .= '<div class="qzorg-quiz-default-loader-parent" style="display: none;">';
                            $quiz_data .= '<div class="qzorg-quiz-default-loader">';
                                $quiz_data .= '<span class="spinner is-active"></span>';
                            $quiz_data .= '</div>';
                        $quiz_data .= '</div>';
                        $quiz_data .= '<div class="display_results-'.esc_attr($id).'" ></div>';
                        $quiz_data .= '<div class="restart-quiz-button" style="display: none;">';
                            $quiz_data .= '<button class="qzorg-default-button-'.esc_attr($this->getQuizID()).' qzorg-pagination-button qzorg-restart-quiz">'.esc_html($unserialize['othertext']['restart_quiz_label']).'</button>';
                        $quiz_data .= '</div>';
                    $quiz_data .= '</div>';
                } else {
                    if ( 1 == $this->validate ) {
                        $quiz_data .= $this->start_date();
                    } elseif ( 2 == $this->validate ) {
                        $quiz_data .= $this->end_date();
                    } else {
                        $quiz_data .= $this->login($id, $unserialize);
                    }
                }
                $quiz_obj->more_settings = $this->more_settings;
                $quiz_obj = apply_filters('qzorg_quiz_questions_after', $quiz_obj, $unserialize);
                $quiz_obj->questions = $this->questions;
            } else {
                $quiz_data .= $this->page($id);
            }
            return $quiz_data .= ob_get_clean();
        }

        /**
         * @since 1.0.0
         * @return : displaty date message before start quiz if required.
         */

        public function start_date() {
            return '<div class="quiz-organizer-wrapper qzorg-quiz-container ">'.esc_html__('The quiz will start soon, so make sure you\'re ready and prepared to showcase your knowledge.', 'quiz-organizer').'</div>';
        }

        /**
         * @since 1.0.0
         * @return : displaty date message before start quiz if required.
         */

        public function end_date() {
            $end_date = '';
            if ( isset($this->unserialize['othertext']['expired_quiz_text']) ) {
                $end_date .= '<p>' . esc_html($this->unserialize['othertext']['expired_quiz_text']) . '</p>';
            }
            return $end_date;
        }

        /**
         * @since 1.0.0
         * @param $id
         * @param $unserialize
         * @return : displaty timer if required.
         */

        public function timer( $id, $unserialize ) {
            $timer = '';
            if ( isset($unserialize['quizpage']['quiz_duration']) && (0 != $unserialize['quizpage']['quiz_duration'] && 0 < $unserialize['quizpage']['quiz_duration']) ) {
                $timer .= '<div class="qzorg-quiz-timer timer-container">';
                $timer .= '<div class="timer" id="qzorgtimer'.esc_attr($id).'" data-timer-id="'.esc_attr($id).'">';
                $timer .= '<span class="qzorg-time-hour"></span>';
                $timer .= '<span class="qzorg-time-minute"></span>';
                $timer .= '<span class="qzorg-time-second"></span>';
                $timer .= '</div>';
                $timer .= '<input type="hidden" id="qzorgquizcountdown'.esc_attr($id).'" name="qzorgquizcountdown" value="">';
                $timer .= '</div>';
            }
            if ( isset($unserialize['general']['display-page-no']) && "yes" == $unserialize['general']['display-page-no'] ) {
                $this->page_no = "yes";
            }
            return $timer;
        }

        /**
         * @since 1.0.0
         * @param $quiz_obj
         * @param $unserialize
         * @return : displaty pagination if required.
         */

        public function pagination( $quiz_obj, $unserialize ) {
            $pagination = '<div class="qzorg-pagination-wrapper">';
            if ( 1 < count($quiz_obj->questions) || (isset($unserialize['general']['quiz-intro-page']) && "yes" == $unserialize['general']['quiz-intro-page']) ) { 
                $pagination .= '<button class="qzorg-previous-button qzorg-default-button-'.esc_attr($this->getQuizID()).' qzorg-pagination-button" style="display: none">'.esc_html($unserialize['othertext']['quiz_previous_page_label']).'</button>';
                $pagination .= '<button class="qzorg-next-button qzorg-default-button-'.esc_attr($this->getQuizID()).' qzorg-pagination-button" style="display: none">'.esc_html($unserialize['othertext']['quiz_next_page_label']).'</button>';
            }
            $pagination .= '<input type="hidden" id="qzorgquiztimer'.esc_attr($this->getQuizID()).'" name="qzorgquiztimer" value="">';
            $pagination .= wp_nonce_field('qzorg_form_quiz_' . intval($this->getQuizID()), 'qzorg_quizform_' . intval($this->getQuizID()));
            $pagination .= '<input type="hidden" name="qzorg_id" id="qzorg_id" value="'.esc_attr($this->getQuizID()).'">';
            $pagination .= $this->submit($quiz_obj, $unserialize);
            $pagination .= '</div>';
            return $pagination;
        }

        /**
         * @since 1.0.0
         * @param $id
         * @param $unserialize
         * @return : displaty progressbar if required.
         */

        public function progressbar( $id, $unserialize ) {
            $progress = '';
            if ( isset($unserialize['quizpage']['display-progressbar']) && "yes" == $unserialize['quizpage']['display-progressbar'] ) {
            $progress .= '<div class="qzorg-progress-bar-wrapper" data-id="'.esc_attr($id).'">';
            $progress .= '<div class="qzorg-progress-bar">';
            $progress .= '<div class="qzorg-progress-line" per="0%" style="width: 0;"></div>';
            $progress .= '</div>';
            $progress .= '</div>';
            }
            return $progress;
        }

        /**
         * @since 1.0.0
         * @param $quiz_obj
         * @param $unserialize
         * @return : displaty quiz_info if required.
         */

        private function quiz_info( $quiz_obj, $unserialize ) {
            $class_list = "qzorg-display-intro-page";
            $quiz_info = $extra_button = '';
            $display_intro = $display_contact = 0;
            if ( isset($unserialize['general']['quiz-intro-page']) && "yes" == $unserialize['general']['quiz-intro-page'] || isset($unserialize['advance']['display_contact_form']) && "yes" == $unserialize['advance']['display_contact_form'] ) {
                $class_list .= " qzorg-quiz-intro-wrapper";
                if ( isset($unserialize['advance']['display_contact_form']) && "yes" == $unserialize['advance']['display_contact_form'] ) {
                    $display_contact = 1;
                    if ( isset($unserialize['advance']['contact_form_to_show']) && "yes" == $unserialize['advance']['contact_form_to_show'] ) {
                        $user = Qzorg_Phtml::qzorg_get_current_user();
                        if ( 0 == $user['user_id'] && "" == $user['username'] ) {
                            $display_contact = 0;
                        }
                    }
                }
                if ( isset($unserialize['general']['quiz-intro-page']) && "yes" == $unserialize['general']['quiz-intro-page'] ) {
                    $display_intro = 1;
                }
                $button = $unserialize['othertext']['start_quiz_label'];
                $extra_button .= '<button class="qzorg-start-quiz-button qzorg-default-button-'.esc_attr($this->getQuizID()).'">' . esc_html($button) . '</button>';
            }
            if ( 1 == $display_contact || 1 == $display_intro ) { 
            $quiz_info .= '<div class="'.esc_attr($class_list).'">';
                $quiz_info .= '<div class="qzorg-quiz-intro">';
                    $quiz_info .= $this->quiz_intro($display_intro, $quiz_obj, $unserialize);
                    $quiz_info .= $this->contact($display_contact);
                    $quiz_info .= wp_kses_post($extra_button);
                $quiz_info .= '</div>';
            $quiz_info .= '</div>';
            }
            return $quiz_info;
        }

        private function quiz_intro( $display, $quiz_obj, $unserialize ) {
            $quiz_intro = '';
            if ( 1 == $display ) {
                $intro = apply_filters('qzorg_quiz_intro_title', $unserialize['general']['quiz_intro_section_qzorgmessage'], $quiz_obj);
                $quiz_intro = '<div class="qzorg-quiz-intro-inner">'.wp_kses_post($intro).'</div>';
            }
            return $quiz_intro;
        }

        private function contact( $display ) {
            $contact = '';
            if ( 1 == $display ) {
                $user_info = Qzorg_Phtml::qzorg_get_current_user(); 
                $contact .= '<div class="qzorg-contact-form ">';
                $contact .= '<div class="qzorg-form-group">';
                $contact .= '<label for="qzorg-username">Username:</label>';
                $contact .= '<input type="text" id="qzorg-username" name="qzorg-username" value="'.esc_attr($user_info['username']).'" >';
                $contact .= '</div>';
                $contact .= '<div class="qzorg-form-group">';
                $contact .= '<label for="qzorg-useremail">Email:</label>';
                $contact .= '<input type="email" id="qzorg-useremail" name="qzorg-useremail" value="'.esc_attr($user_info['email']).'">';
                $contact .= '</div>';
                $contact .= '</div>';
            }
            return $contact;
        }

        /**
         * @since 1.0.0
         * @param $url
         * @return : displaty image if required.
         */

        private function image( $url ) {
            $image = '';
            if ( $url ) { 
                $image = '<div class="qzorg-question-image-wrapper qzorg-single-solo"><img class="qzorg-question-image" src="'.esc_url(wp_get_attachment_image_src($url, 'full')[0]).'"></div>';
            }
            return $image;
        }

        /**
         * @since 1.0.0
         * @return : Retuens all categories
         */

        public function qzorg_get_category_title( $id ) {
            $category_name = "";
            if ( ! empty($id['categories']) && ! empty($id['categories'][0]) ) {
                $table = $this->db->prefix . "qzorg_categories";
                $q_cat_obj = $this->db->get_row("SELECT category_name FROM {$table} WHERE id = {$id['categories'][0]}");
                if ( $q_cat_obj ) {
                    $category_name = $q_cat_obj->category_name;
                }
            }
            return $category_name;
        }

        /**
         * @since 1.0.0
         * @param $type
         * @param $question
         * @param $answers
         * @uses Functions lists
         */

        private function set_drop_down( $type, $question, $answers ) {
            $dropdata = '';
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $dropdata .= '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $dropdata .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }
                $dropdata .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $dropdata .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $dropdata .= $this->image($question->question_image);
                $dropdata .= '<div class="qzorg-class-'.esc_attr($question->question_type).'">';
                    $dropdata .= '<select name="'.esc_attr($question->question_type . $question->question_id).'" class="qzorg-default-select qzorg-class-'.esc_attr($question->question_type).' qzorg-class-'.esc_attr($question->question_id).'">';  
                    $dropdata .= '<option value="">Select Answer</option>';
                        foreach ( $answers as $key => $e_ans ) { 
                            $dropdata .= '<option value="'.esc_attr($key).'">'.esc_attr($e_ans['answer']).'</option>';
                        } 
                    $dropdata .= '</select>';
                $dropdata .= '</div> ';
                $dropdata .= $this->question_message();
            $dropdata .= '</div>';
            return $dropdata;
        }

        private function set_checkbox( $type, $question, $answers ) {
            $return = '';
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $class = (isset($tools['display_flex'])) && "1" == $tools['display_flex'] ? 'qzorg-d-flex' : "";
            $return .= '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $return .= $this->image($question->question_image);
                $return .= '<div class=" qzorg-class-'.esc_attr($question->question_type).'-wrapper ">';
                if ( ! empty($answers) ) { 
                    $return .= '<div class="qzorg-checkbox-wrap '.esc_attr($class).'">';
                        foreach ( $answers as $key => $e_ans ) { 
                            $return .= '<div class="qzorg-checkbox-item"><input name="'.esc_attr($question->question_type . $question->question_id).'[]" id="'.esc_attr($question->question_type . $question->question_id . '-' . $key).'" type="checkbox" value="'.esc_attr($key).'" class="qzorg-default-input-checkbox qzorg-class-'.esc_attr($question->question_type).' qzorg-class-'.esc_attr($question->question_id).'">';
                            $return .= '<label class="qzorg-default-label" for="'.esc_attr($question->question_type . $question->question_id . '-' . $key).'">'.esc_html($e_ans['answer']).'</label></div>';
                        } 
                    $return .= '</div>';
                }
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function set_radio( $type, $question, $answers ) {
            $return = '';
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $class = (isset($tools['display_flex'])) && "1" == $tools['display_flex'] ? 'qzorg-d-flex' : "";
            $return .= '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }    
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                    $return .= $this->image($question->question_image);
                    $return .= '<div class="qzorg-class-'.esc_attr($question->question_type).'-wrapper ">';
                    if ( ! empty($answers) ) { 
                        $return .= '<div class="qzorg-radio-wrap '.esc_attr($class).'">';
                        foreach ( $answers as $key => $e_ans ) { 
                            $return .= '<div class="qzorg-radio-item">';
                            $return .= '<input name="'.esc_attr($question->question_type . $question->question_id).'" id="'.esc_attr($question->question_type . $question->question_id . '-' . $key).'" type="radio" value="'.esc_attr($key).'" class="qzorg-default-input-radio qzorg-class-'.esc_attr($question->question_type).' qzorg-class-'.esc_attr($question->question_id).'">'; 
                            $return .= '<label class="qzorg-default-label" for="'.esc_attr($question->question_type . $question->question_id . '-' . $key).'">'.esc_html($e_ans['answer']).'</label>';
                            $return .= '</div>';
                        }
                        $return .= '</div>';
                    } 
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function set_number( $type, $question, $answers ) {
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $return = '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }    
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $return .= $this->image($question->question_image);
                $return .= '<div class="qzorg-class-'.esc_attr($question->question_type).'">';
                    $return .= '<input class="qzorg-default-input-number" name="'.esc_attr($question->question_type . $question->question_id).'" placeholder="'.esc_attr__("Your Answer", "quiz-organizer").'" type="number" />';
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function set_date( $type, $question, $answers ) {
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $return = '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $return .= $this->image($question->question_image);
                $return .= '<div class="qzorg-class-'.esc_attr($question->question_type).'">';
                    $return .= '<input class="qzorg-default-input-date" name="'.esc_attr($question->question_type . $question->question_id).'" placeholder="'.esc_attr__("Your Answer", "quiz-organizer").'" type="date" />';
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function set_paragraphtext( $type, $question, $answers ) {
            $return = '';
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : ""; 
            $tools = maybe_unserialize($question->question_tools);
            $return = '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $return .= $this->image($question->question_image);
                $return .= '<div class="qzorg-class-'.esc_attr($question->question_type).'">';
                $return .= '<textarea class="qzorg-default-input-pragraph" name="'.esc_attr($question->question_type . $question->question_id).'" placeholder="'.esc_attr__("Your Answer", "quiz-organizer").'" ></textarea>';
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function set_singlelinetext( $type, $question, $answers ) {
            $count = "yes" == $this->question_no ? ($this->questions + 1) . ". " : "";
            $tools = maybe_unserialize($question->question_tools);
            $return = '<div class="qzorg-question-wrapper qzorg-class-'.esc_attr($question->question_id).'" data-id="'.esc_attr($question->question_id).'">';
                if ( "yes" == $this->category ) { 
                    $return .= '<div class="qzorg-question-cat-title">'.esc_html($this->qzorg_get_category_title(maybe_unserialize($question->question_tools))).'</div>'; 
                }
                $return .= '<div class="qzorg-question-title qzorg-q-title-'.esc_attr($this->getQuizID()).'">'.esc_html($count . $question->question_title).'</div>';
                if ( isset($tools['extra_info_qzorgmessage']) && "" != isset($tools['extra_info_qzorgmessage']) ) {
                    $return .= '<div class="qzorg-question-extra qzorg-q-extra-'.esc_attr($this->getQuizID()).'">'.$tools['extra_info_qzorgmessage'].'</div>';
                }
                $return .= $this->image($question->question_image);
                $return .= '<div class="qzorg-class-'.esc_attr($question->question_type).'">';
                $return .= '<input class="qzorg-default-input-text" name="'.esc_attr($question->question_type . $question->question_id).'" placeholder="'.esc_attr__("Your Answer", "quiz-organizer").'" type="text" />';
                $return .= '</div>';
                $return .= $this->question_message();
            $return .= '</div>';
            return $return;
        }

        private function submit( $qiuz, $tools ) {
            return '<input type="submit" class="qzorg-pagination-button qzorg-check-results qzorg-default-button-'.esc_attr($this->getQuizID()).' qzorg-submit-btn" style="display: none" value="'.esc_attr($tools['othertext']['submit_quiz_label']).'" />';
        }

        public function question_message() {
            $message = '';
            if ( isset($this->unserialize['quizpage']['instant-answer']) && "yes" == $this->unserialize['quizpage']['instant-answer'] ) {
                $message .= '<div class="qzorg-question-m"></div>';
            }
            return $message;
        }

        /**
         * @since 1.0.0
         * @param $url
         * @uses Quiz related errorcif any
         */

        public function page( $id ) {
            return '<div class="quiz-organizer-wrapper qzorg-quiz-container qzorg-quiz-wrapper'.esc_attr($id).'">'.esc_html_e('Something wrong while loading quiz!', 'quiz-organizer').'</div>';
        }

        /**
         * @since 1.0.0
         * @param $id
         * @param $unserialize
         * @uses Display login message based on quiz settings
         */

        public function login( $id, $unserialize ) {
            $login = '<div class="quiz-organizer-wrapper qzorg-quiz-container qzorg-quiz-wrapper'.esc_attr($id).'">';
            $login .= wp_kses_post($unserialize['general']['login_require_qzorgmessage']);
            $login .= '</div>';
            return $login;
        }

        public function qzorg_get_single_question_answers( $question_id = 0 ) {
            if ( 0 === $question_id ) {
                return;
            }
            $table = $this->db->prefix . "qzorg_questions";
            $question = $this->db->get_row("SELECT question_answer, question_tools, quiz_id FROM {$table} WHERE question_id = {$question_id}");
            $quiz = $this->db->get_row("SELECT quiz_tools FROM {$this->table_quiz} WHERE quiz_id = {$question->quiz_id}");
            $question->othertext = maybe_unserialize($quiz->quiz_tools)['othertext'];
            if ( $question ) {
                return $question;
            }
        }

    }
}
global $plugShortcodeObj;
$plugShortcodeObj = new Qzorg_Plug_Shortcode();

function qzorg_validate_basic_settings( $quiz_obj, $unserialize ) {
    $return = 1;
    if ( ! is_user_logged_in() ) {
        if ( "yes" == $quiz_obj->login_require ) {
            $return = 0;
        }
    } 
    if ( 1 == $return ) {
        return apply_filters( 'qzorg_update_db_data', $quiz_obj );
    }
    return $return;
}

function qzorg_change_visits( $quiz_obj ) {
    global $wpdb, $Quiz_Organizer;
    $db = new Qzorg_Db();
    $questions_obj = $db->questions( $quiz_obj );
    $quiz_obj->questions = $questions_obj;
    $results = $wpdb->update(
		$wpdb->prefix . 'qzorg_quizzes',
		array(
			'quiz_visits' => ++$quiz_obj->quiz_visits,
		),
		array( 'quiz_id' => $quiz_obj->quiz_id )
	);
	return 1;
}