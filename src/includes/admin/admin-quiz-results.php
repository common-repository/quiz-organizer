<?php
/**
 * @since 1.0.0
 * @uses View all quiz results.
 */


class Qzorg_QuizSubmissions extends WP_List_Table
{   
    private $db;
    public $prevRecordId = 0;
    public $nextRecordId = 0;
    public $submission_id = 0;

    public function __construct( ) {
        if ( ! current_user_can( 'edit_posts' ) ) { return; }
        global $wpdb;
        $this->db = $wpdb;
        parent::__construct(array(
            'singular' => 'qzorg_result',
            'plural'   => 'qzorg_results',
            'ajax'     => false,
        ));
    }

    public function qzorg_display_submissions() {     
        $quiz_title = $link = "";
        $prevLink = $nextLink = "#";
        $submissions = $this->db->prefix . "qzorg_submissions";
        $quiz_table = $this->db->prefix.'qzorg_quizzes';
        $this->submission_id = isset($_GET['submission_id']) ? intval(sanitize_text_field(wp_unslash($_GET['submission_id']))) : "";
        $q_results_obj = $this->db->get_row( "SELECT * FROM {$submissions} WHERE submission_id = {$this->submission_id}" );
        $user = $quiz = $others = array();
        $disabled = "disabled";
        if ( ! empty($q_results_obj) ) {
            $user['user_name'] = array( esc_html__('User Name', 'quiz-organizer'), $q_results_obj->user_name );
            $user['user_email'] = array( esc_html__('User Email', 'quiz-organizer'), $q_results_obj->user_email );
            $user['user_ip'] = array( esc_html__('User IP', 'quiz-organizer'), $q_results_obj->user_ip );
            $others = maybe_unserialize($q_results_obj->others);
            $q_quiz_single = $this->db->get_row( "SELECT * FROM {$quiz_table} WHERE quiz_id = {$q_results_obj->quiz_id} ORDER BY quiz_id ASC LIMIT 1" );
            $quiz_title = $q_quiz_single->quiz_name;
        }
        
        $sqlNext = $this->db->prepare("SELECT submission_id FROM {$submissions} WHERE submission_id > %d ORDER BY submission_id ASC LIMIT 1", $this->submission_id);
        $nextRecordId = $this->db->get_var($sqlNext);
        $sqlPrev = $this->db->prepare("SELECT submission_id FROM {$submissions} WHERE submission_id < %d ORDER BY submission_id DESC LIMIT 1", $this->submission_id);
        $prevRecordId = $this->db->get_var($sqlPrev);
        if ( $prevRecordId ) {
            $prevLink = esc_url(add_query_arg(array(
                'page'          => 'qzorg_results',
                'submission_id' => $prevRecordId,
            ), admin_url('admin.php')));
        }
        $disabled = "" == $prevRecordId ? "disabled" : "";
        $link .= "<a href='".esc_url($prevLink)."' class='button-primary ".esc_attr($disabled)."'>".__("Previous", 'quiz-organizer')."</a>";
        $link .= "<a href='?page=qzorg_results' class='button-primary'>".__("View All", 'quiz-organizer')."</a>";
        if ( $nextRecordId ) {
            $nextLink = esc_url(add_query_arg(array(
                'page'          => 'qzorg_results',
                'submission_id' => $nextRecordId,
            ), admin_url('admin.php')));
        }
        $disabled = "" == $nextRecordId ? "disabled" : "";
        $link .= "<a class='button-primary ".esc_attr($disabled)."' href='".esc_url($nextLink)."'>".__("Next", 'quiz-organizer')."</a>";
        if ( ! empty($q_results_obj) ) {
            $quiz['created_at'] = array( esc_html__('Submission Date', 'quiz-organizer'), $q_results_obj->created_at );
            $quiz['number_of_question'] = array( esc_html__('No of Questions', 'quiz-organizer'), count($others['questions']) );
            $quiz['correct_count'] = array( esc_html__('Correct Answers', 'quiz-organizer'), $others['correct_count'] );
            $quiz['incorrect_count'] = array( esc_html__('Incorrect Answers', 'quiz-organizer'), $others['incorrect_count'] );
            $quiz['quiz_maximum_points'] = array( esc_html__('Maximum Points', 'quiz-organizer'), $others['quiz_maximum_points'] );
            $quiz['total_points'] = array( esc_html__('Earned Points', 'quiz-organizer'), $others['total_points'] );
            $quiz['average_score'] = array( esc_html__('Score', 'quiz-organizer'), self::calculatePercentage($others['total_points'], $others['quiz_maximum_points']) );
        }

        $user = apply_filters('qzorg_update_user_info', $user);
        $quiz = apply_filters('qzorg_update_others_info', $quiz);
        
        ?>        
        <h1><?php esc_html_e( 'Quiz Results For', 'quiz-organizer' ); echo sprintf('<i> %1$s</i>', esc_html($quiz_title)); ?></h1>
            <div class="wrap qzorg-custom">
                <?php if ( ! empty($q_results_obj) ) { ?>
                <div class="qzorg-submission-status">
                    <div class="qzorg-status-wrap qzorg-user-info">
                        <div class="qzorg-user-heading"><h3><?php esc_html_e('User Overview', 'quiz-organizer'); ?></h3></div>
                        <div class="qzorg-user-details">
                            <ul>
                                <?php if ( ! empty($user) ) { 
                                    foreach ( $user as $ukey => $uvalue ) { ?>
                                        <li>
                                            <span class="qzorg-label"><?php echo esc_html($uvalue[0]); ?></span>
                                            <span class="qzorg-info"><?php echo esc_html($uvalue[1]); ?></span>
                                        </li>
                                    <?php } 
                                } ?>
                            </ul>
                        </div>
                        <div class="qzorg-user-duration">
                        <?php echo wp_kses_post(self::calculateTimeComponents($q_results_obj->duration) ); ?>
                        </div>
                    </div>
                    <div class="qzorg-status-wrap qzorg-quiz-info">
                        <div class="qzorg-quiz-heading"><h3><?php esc_html_e('Quiz Status', 'quiz-organizer'); ?></h3></div>
                        <div class="qzorg-quiz-details">
                            <ul>
                                <?php if ( ! empty($quiz) ) { 
                                    foreach ( $quiz as $qkey => $qvalue ) { ?>
                                        <li>
                                            <span class="qzorg-label"><?php echo esc_html($qvalue[0]); ?></span>
                                            <span class="qzorg-info"><?php echo esc_html($qvalue[1]); ?></span>
                                        </li>
                                    <?php } 
                                } ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="qzorg-pagination-result">
                    <?php echo wp_kses_post($link); ?>
                </div>
                <table class="wp-list-table widefat striped qzorg-submissions-table">
                    <tbody>
                        <?php if ( ! empty($q_results_obj) ) { ?>
                            <tr>
                                <th scope="col"></th>
                            </tr>
                            <?php
                            $unserializequiz = maybe_unserialize($q_quiz_single->quiz_tools);
                            $status = $unserializequiz['resultpage']['display-answer-status'];                            
                            $unserialize = maybe_unserialize($q_results_obj->others);
                            foreach ( $unserialize['questions'] as $key => $q ) { ?>
                                <tr>
                                    <td><?php echo wp_kses_post(Qzorg_Db::preview_question($q, 'yes')); ?></td>
                                </tr>
                            <?php }
                        } else {
                            ?>
                            <tr>
                                <td><?php esc_html_e('No quiz results found.', 'quiz-organizer'); ?></td>
                            </tr>
                            <?php
                        } ?>
                    </tbody>
                </table>
                <div class="qzorg-pagination-result">
                    <?php echo wp_kses_post($link); ?>
                </div>
            </div>
        <?php
    }

    public static function calculatePercentage( $earnedMarks = 0, $totalMarks = 0 ) {
        if ( 0 >= $totalMarks || 0 >= $earnedMarks ) {
            return 0;
        } else {
            $percentage = ($earnedMarks / $totalMarks) * 100;
            return Qzorg_Defaults::qzorg_round($percentage, 3).'(%)';
        }
    }   
    
    public static function calculateTimeComponents( $time ) {
        
        list($hours, $minutes, $seconds) = explode(':', $time);
    
        $hours = (int)$hours;
        $minutes = (int)$minutes;
        $seconds = (int)$seconds;
    
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
        $calculatedHours = floor($totalSeconds / 3600);
        $calculatedMinutes = floor(($totalSeconds % 3600) / 60);
        $calculatedSeconds = $totalSeconds % 60;
    
        $time_text = ' hours:'. $calculatedHours. ' minutes:'. $calculatedMinutes. ' seconds:'. $calculatedSeconds;

        $timehtml = '<div class="display-duration">';
            $timehtml .= '<div class="time"><div class="span">'.__('Hours', 'quiz-organizer').'</div><div class="hours">'.$calculatedHours.'</div></div>';
            $timehtml .= '<div class="time"><div class="span">'.__('Minute', 'quiz-organizer').'</div><div class="minute">'.$calculatedMinutes.'</div></div>';
            $timehtml .= '<div class="time"><div class="span">'.__('Seconds', 'quiz-organizer').'</div><div class="second">'.$calculatedSeconds.'</div></div>';
        $timehtml .= '</div>';
        
        return $timehtml; 
    }

    public $quiz = "";
    public $quiz_id = "";
    public $submission_ids = array();

    public function column_title( $item ) {
        $actions = array(
            'edit'   => sprintf('<a href="?page=%s&submission_id=%s">%s</a>', 'qzorg_results', $item['submission_id'], __('View Result', 'quiz-organizer')),
            'delete' => sprintf('<a href="?page=%s&submission_id=%s">%s</a>', 'qzorg_results', $item['submission_id'], __('Delete Result', 'quiz-organizer')),
        );
        return sprintf('%1$s %2$s', $item['quiz_name'], $this->row_actions($actions));
    }

    protected function handle_row_actions( $item, $column_name, $primary ) {

        if ( $column_name === $primary ) {
            return $this->column_title($item);
        } else {
            return $item[ $column_name ];
        }

    }

    public function get_columns() {

        $columns = array( 
            'cb'            => '<input type="checkbox" />',
            'quiz_name'     => __('Title', 'quiz-organizer'),
            'user_name'     => __('Username', 'quiz-organizer'),
            'user_email'    => __('UserEmail', 'quiz-organizer'),
            'user_ip'       => __('User IP', 'quiz-organizer'),
            'created_at'    => __('Submited Date', 'quiz-organizer'),
            'submission_id' => __('ID', 'quiz-organizer'),
        );

        return $columns;
    }
    
    public function prepare_items() {

        $where = "";
        $quiz = "";
        $orderby = "submission_id";
        $order = "desc";
        if ( (isset($_POST['qzorg_search_filter_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['qzorg_search_filter_nonce'])), 'qzorg_search_filter')) ) {
            $this->quiz = isset($_POST['quiz_name']) ? sanitize_text_field(wp_unslash($_POST['quiz_name'])) : "";
            $this->submission_ids = isset( $_POST['submission_id'] ) ? array_map('sanitize_text_field', wp_unslash( $_POST['submission_id'])) : array();
            $quiz = '%' . $this->db->esc_like( $this->quiz ) . '%';
            $this->process_bulk_action();
        } 

        if ( isset($_GET['quizid']) && "" != $_GET['quizid'] ) {
            $this->quiz_id = isset($_GET['quizid']) ? intval(sanitize_text_field(wp_unslash($_GET['quizid']))) : "";
            $where .= $this->db->prepare(" AND quiz_id = %d ", $this->quiz_id);
        }
        
        $per_page = 10;
        $table_name = $this->db->prefix . 'qzorg_submissions';
        
        if ( ! empty($this->quiz) ) {
            $where .= $this->db->prepare(" AND (quiz_name LIKE '%s' OR user_name LIKE '%s' OR user_email LIKE '%s') ", $quiz, $quiz, $quiz);
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        $paged = $this->get_pagenum();
        $offset = ($paged - 1) * $per_page;

        $total_items = $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->db->prefix}qzorg_submissions WHERE 1=1 {$where}"
            )
        );
        
        $orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : $orderby;
        $order = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : $order;

        $this->items = $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}qzorg_submissions WHERE 1=1 {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

    }

    public function get_sortable_columns() {

        $sortable_columns = array(
            'quiz_name'  => array( 'quiz_name', false ), 
            'created_at' => array( 'created_at', false ),
        );
    
        return $sortable_columns;
    }

    public function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            ?>
            <div class="alignleft actions">
                <label for="quiz_name"></label>
                <input type="text" class="qzorg-result-filter-input qzorg-global-input" name="quiz_name" value="<?php echo esc_attr($this->quiz); ?>" placeholder="<?php echo esc_attr(__('Filter by Quiz Name, Usernamem, Email', 'quiz-organizer')); ?>">
                <input type="submit" class="button" value="Filter">
            </div>
            <?php
        }
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="submission_id[]" value="%s" />', $item['submission_id']
        );
    }

    public function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            if ( ! empty($this->submission_ids) ) {
                $ids = implode(',', $this->submission_ids);
                $this->db->query("DELETE FROM {$this->db->prefix}qzorg_submissions WHERE submission_id IN ($ids)");
            }
        }
    }

}

function qzorg_result_page() {
    if ( (isset($_GET['page']) && "qzorg_results" == $_GET['page'] ) ) {
        $quizSubmissionsObj = new Qzorg_QuizSubmissions();

        if ( isset($_GET['submission_id']) && "" != $_GET['submission_id'] ) {
            wp_enqueue_style( 'qzorg_adminresultcss', QZORG_CSS_URL.'/qzorg-admin-result-page.css', array(), gmdate('h-m') );
            $quizSubmissionsObj->qzorg_display_submissions();
        } else {
            $quizSubmissionsObj->prepare_items();
            ?>
            <div class="wrap qzorg-custom qzorg-result-list">
            <h2><?php esc_html_e( 'Quiz Results page', 'quiz-organizer' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field('qzorg_search_filter', 'qzorg_search_filter_nonce'); ?>
                <input type="hidden" name="page" value="qzorg_results">
                <?php $quizSubmissionsObj->display(); ?>
            </form>
        </div>
        <?php
        }
    }
}