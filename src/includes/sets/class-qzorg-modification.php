<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Class to manage all db modification.
*
* @since 1.0.0
*/

if ( ! class_exists( 'Qzorg_Modification' ) ) {

    class Qzorg_Modification {
        private static $tab_data = array();
        private static $replaceable = array();
        private $quiz_id = 0;
        private static $redirect = true;

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
        public function getQuizID(){
            return $this->quiz_id;
        }

        /**
         * @uses index
        */
        
        public static function index( $params ) { 

            global $wpdb;
            $qzorg_pp = isset($params['qzorg_pp']) && "" !== $params['qzorg_pp'] ? (int)$params['qzorg_pp'] : 1000;
            $quiz_name = isset($params['quiz_name']) && "" !== $params['quiz_name'] ? $params['quiz_name'] : "";
            $qzorg_start_date = isset($params['qzorg_start_date']) && "" !== $params['qzorg_start_date'] ? $params['qzorg_start_date'] : "";
            $qzorg_end_date = isset($params['qzorg_end_date']) && "" !== $params['qzorg_end_date'] ? $params['qzorg_end_date'] : "";
            $page = isset($params['page']) && "" !== $params['page'] ? (int)$params['page'] : 1;

            $offset = 1 < $page ? (($page - 1) * $qzorg_pp) : 0;

            $query = "SELECT quiz_id, quiz_name, shortcode, quiz_attend, quiz_visits, preview_id, author_id, created_at FROM {$wpdb->prefix}qzorg_quizzes WHERE 1=1";

            // Filter by quiz_name
            if ( ! empty($quiz_name) ) {
                $query .= $wpdb->prepare(" AND quiz_name LIKE %s", '%' . $wpdb->esc_like($quiz_name) . '%');
            }
        
            // Filter by dates
            if ( ! empty($qzorg_start_date) || ! empty($qzorg_end_date) ) {
                if ( ! empty($qzorg_start_date) && ! empty($qzorg_end_date) ) {
                    $query .= $wpdb->prepare(" AND created_at BETWEEN %s AND %s", $qzorg_start_date, $qzorg_end_date);
                }elseif ( ! empty($qzorg_start_date) && empty($qzorg_end_date) ) {
                    $query .= $wpdb->prepare( " AND created_at >= %s", $qzorg_start_date );
                }elseif ( empty($qzorg_start_date) && ! empty($qzorg_end_date) ) {
                    $query .= $wpdb->prepare( " AND created_at <= %s", $qzorg_end_date );
                }
            }
            
            $total_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ($query) AS count_query"));

            $total_count = $total_count ? $total_count : 0;
            // Calculate the total number of pages
            $qzorg_pp = 0 == $qzorg_pp ? $total_count : $qzorg_pp;

            $total_pages = 0 != $total_count ? ceil($total_count / $qzorg_pp) : 0;

            // Add the pagination clauses
            $query .= " LIMIT $qzorg_pp OFFSET $offset";

            // Retrieve the quizzes
            $quizzes = $wpdb->get_results($query);

            foreach ( $quizzes as $key => $value ) {
                $value->author = "";
                $value->quiz_url = esc_url(admin_url('admin.php?page=qzorg_modify_quiz&quizid=' . $value->quiz_id . '&tab=qzorgquestions'));
                $value->result_url = esc_url(admin_url('admin.php?page=qzorg_results&quizid=' . $value->quiz_id));
                $value->preview_url = "javascript:void(0);";
                $value->author .= '<b>' . esc_html__('Author', 'quiz-organizer') . '</b> : ' . get_user_by('ID', $value->author_id)->display_name."<br>";
                $value->author .= '<b>' . esc_html__('Date', 'quiz-organizer') . '</b> : ' . $value->created_at;
                $value->quiz_attend = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}qzorg_submissions WHERE quiz_id = %d",
                    $value->quiz_id
                ));
            }

            // Prepare the response data
            $data = array(
                'quizzes'     => $quizzes,
                'total_pages' => $total_pages,
            );

            // Add pagination links
            $base_url = get_rest_url(null, '/quizorganizer/v1/quiz/index');
            $pagination_links = array(
                'first' => $base_url . '?qzorg_pp=' . $qzorg_pp . '&page=1',
                'last'  => $base_url . '?qzorg_pp=' . $qzorg_pp . '&page=' . $total_pages,
                'prev'  => '',
                'next'  => '',
            );
        
            // Add previous page link if applicable
            if ( $page > 1 ) {
                $pagination_links['prev'] = $base_url . '?qzorg_pp=' . $qzorg_pp . '&page=' . ( $page - 1);
            }
        
            // Add next page link if applicable
            if ( $page < $total_pages ) {
                $pagination_links['next'] = $base_url . '?qzorg_pp=' . $qzorg_pp . '&page=' . ( $page + 1);
            }
        
            // Include pagination links in the response
            $data['pagination_links'] = $pagination_links;
        
            // Return the response
            return $data;
        }
        
        /**
         * @uses array to override
        */

        public static function qzorg_quiz_default_fillable( array $fillable = array() ) {
            $replaceable = self::$replaceable;
            foreach ( Qzorg_Defaults::defaults() as $keys => $tabs ) {
                foreach ( $tabs as $key => $tab ) {
                    $replaceable[ $tab['name'] ] = isset($replaceable[ $tab['name'] ]) && $replaceable[ $tab['name'] ] ? $replaceable[ $tab['name'] ] : $tab['fill'];
                    $fillable[ $keys ][ $tab['name'] ] = $replaceable[ $tab['name'] ];
                }
            }
            self::$tab_data = $fillable;
        }

        
        /**
         * @uses array to override
        */

        public function qzorg_quiz_default_updatable( $replaceable, $id ) {
            $tabwise = array();
            $this->setQuizID($id);
            foreach ( Qzorg_Defaults::defaults() as $keys => $tabs ) {
                foreach ( $tabs as $key => $tab ) {
                    $replaceable[ $tab['name'] ] = isset($replaceable[ $tab['name'] ]) && $replaceable[ $tab['name'] ] ? $replaceable[ $tab['name'] ] : $tab['fill'];
                    $tabwise[ $keys ][ $tab['name'] ] = $replaceable[ $tab['name'] ];
                }
            }
            self::$tab_data = $tabwise;
            $this->update();
        }

        /**
         * Register new quiz
         * @since  1.0.0
         * @return Redirect
         */
        public function create() {
            global $wpdb;
            $__data = self::$tab_data;
            if ( empty(self::$tab_data) ) { return; }
            $table_name = $wpdb->prefix . "qzorg_quizzes";
            $results      = $wpdb->insert(
                $table_name,
                array(
                    'quiz_name'     => $__data['general']['quiz_name'],
                    'quiz_visits'   => 0,
                    'quiz_attend'   => 0,
                    'quiz_type'     => $__data['general']['quiz-type'],
                    'quiz_tools'    => maybe_serialize( $__data ),
                    'author_id'     => wp_get_current_user()->ID,
                    'login_require' => $__data['general']['login-require'],
                    'shortcode'     => '[]',
                    'created_at'    => current_time( 'mysql' ),
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array(
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );

            if ( false !== $results ) {
                $this->setQuizID($wpdb->insert_id);
                $this->postable($__data['general']['quiz_name']);
            } else {
                echo wp_json_encode(
                    array(
                        'success' => 0,
                        'message' => __('Error to process your request !' , 'quiz-organizer'),
                    )
                );
                die;
            }
        }

        /**
         * Register new quiz
         * @since  1.0.0
         */

        public function postable( $quiz_name ) {
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_quizzes";
            $shortcode = "[quiz-organizer id=".$this->getQuizID()."]";
            $order = array();
            $preview_id = time();
            $question = $this->qzorg_register_new_question();
            $order['question_order'] = $question;
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}qzorg_quizzes SET shortcode = %s, preview_id = %d, other_tools = %s WHERE quiz_id = %d",
                    $shortcode,
                    $preview_id,
                    maybe_serialize($order),
                    $this->getQuizID()
                )
            );
            if ( true == self::$redirect ) {
                echo wp_json_encode(
                    array(
                        'success'  => 1,
                        'message'  => __('Quiz Created!', 'quiz-organizer'),
                        'redirect' => admin_url('admin.php?page=qzorg_modify_quiz&tab=qzorgquestions&quizid=' . $this->getQuizID()),
                    ) 
                );
                die;
            } else {
                if ( function_exists ('qzorg_after_quiz_process') ) {
                    qzorg_after_quiz_process($this->getQuizID());
                }
            }
        }
        
        /**
         * @since  1.0.0
         * @uses Validate quiz
         */

        public static function validate( $data, $redirect = true ) {
            $required_fields = array( 'quiz_name' );
            foreach ( $data as $key => $eachpost ) {
                if ( in_array( $key, $required_fields, true ) ) {
                    do_action('qzorg_check_is_empty', $eachpost, __('Please fill all required field !' , 'quiz-organizer'));
                }
            }
            self::$redirect = $redirect;
            self::$replaceable = $data;
            self::qzorg_quiz_default_fillable();
        }

        /**
         * @since  1.0.0
         * @uses Set default global settings from [ 0 ]
         */

        public static function default(){
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_quizzes";
            $q_results_obj = $wpdb->get_results( $wpdb->prepare(
                "SELECT quiz_id, quiz_tools, updated_at FROM %s",
                $table
            ) );
            foreach ( $q_results_obj as $qk => $qv ) {
                $tools = maybe_unserialize($qv->quiz_tools);
                foreach ( Qzorg_Defaults::defaults() as $keys => $tabs ) {
                    foreach ( $tabs as $key => $tab ) {
                        if ( ! isset($tools[ $keys ][ $tab['name'] ]) ) {
                            $tools[ $keys ][ $tab['name'] ] = $tab['fill'];
                        }
                    }
                }
                $wpdb->update( $table, array(
					'quiz_tools' => maybe_serialize( $tools ),
					'updated_at' => $qv->updated_at,
				), array( 'quiz_id' => $qv->quiz_id ) );
            }
        }

        /**
         * @since  1.0.0
         */

        public function update() {
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_quizzes";
            $__data = self::$tab_data;
            $results      = $wpdb->update(
                $table,
                array(
                    'quiz_name'     => $__data['general']['quiz_name'],
                    'quiz_type'     => $__data['general']['quiz-type'],
                    'quiz_tools'    => maybe_serialize( $__data ),
                    'login_require' => $__data['general']['login-require'],
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( 
                    'quiz_id' => $this->getQuizID(), 
                ),
            );
            if ( false !== $results ) {
                wp_send_json_success([
                    'message' => __('Quiz Updated!' , 'quiz-organizer'),
                    'quiz_id' => $this->getQuizID(),
                    'results' => ! empty($submission) ? 1 : 0,
                ], 200);
            } else {
                wp_send_json_error([
                    'message' => __('Error to process your request !' , 'quiz-organizer'),
                ], 500);
            }
            die();
        }
        

        /**
         * Register new question
         * @since  1.0.0
         * @return ID
         */

        private function qzorg_register_new_question() {
            if ( true == self::$redirect ) {
                global $wpdb;
                $question = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
                $table_name = $wpdb->prefix . "qzorg_questions";
                $register      = $wpdb->insert(
                    $table_name,
                    array(
                        'quiz_id'         => $this->getQuizID(),
                        'question_title'  => "",
                        'question_type'   => $question['default_question_type'],
                        'question_answer' => maybe_serialize(""),
                        'question_tools'  => maybe_serialize(array()),
                        'created_at'      => current_time( 'mysql' ),
                        'updated_at'      => current_time( 'mysql' ),
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
                return array( array( $wpdb->insert_id ) );
            } else {
                return array( array() );
            }
        }

    }
    
}