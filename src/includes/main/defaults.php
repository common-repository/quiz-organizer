<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'qzorg_results_page_after', 'qzorg_quiz_result_here', 10, 4 );
add_filter( 'qzorg_after_db_submission', 'qzorg_quiz_redirect', 10, 1 );
add_filter( 'qzorg_results_page_after', 'qzorg_user_ip_address', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_quiz_name_here', 10, 4 );
add_filter( 'qzorg_email_subject', 'qzorg_quiz_name_here', 10, 4 );
add_filter( 'qzorg_quiz_intro_title', 'qzorg_quiz_name_intro', 10, 2 );
add_filter( 'qzorg_static_results_after', 'qzorg_user_total_points_here', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_quiz_maximum_points', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_quiz_average_points', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_correct_question_count', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_incorrect_question_count', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_unanswered_question_count', 10, 3 );
add_filter( 'qzorg_static_results_after', 'qzorg_total_taken_question_count', 10, 3 );
add_filter( 'qzorg_results_page_after', 'qzorg_quiz_submission_date', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_quiz_created_date', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_wp_user_name', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_wp_user_email', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_wp_user_role', 10, 4 );
add_filter( 'qzorg_results_page_after', 'qzorg_wp_admin_email', 10, 4 );
add_filter( 'qzorg_quiz_questions_after', 'qzorg_validate_quiz', 999, 4 );
add_filter( 'wp_mail_content_type', 'qzorg_set_html_content_type' );

function qzorg_quiz_result_here( $replacable, $quiz, $user, $questions = array() ) {

    if ( false !== strpos($replacable, "%%QUIZ_RESULTS_HERE%%") ) {
        $answer_status = maybe_unserialize($quiz->quiz_tools)['resultpage']['display-answer-status'] ? maybe_unserialize($quiz->quiz_tools)['resultpage']['display-answer-status'] : "yes";
        $str = "<div class='qzorg-results-wrapper'>";
        foreach ( $questions as $key => $q ) {
            $str .= Qzorg_Db::preview_question($q, $answer_status);
        }
        $str .= "</div>";
        $replacable = str_replace("%%QUIZ_RESULTS_HERE%%", $str, $replacable);
    }
    return $replacable;
}

function qzorg_quiz_redirect( $tools ) {
    if ( isset($tools->redirect_after_submit) && "" != $tools->redirect_after_submit && wp_http_validate_url($tools->redirect_after_submit) ) {
        wp_send_json_success([
            'message' => __('Success !' , 'quiz-organizer'),
            'url'     => $tools->redirect_after_submit, 
            'results' => ! empty($submission) ? 1 : 0,
            'display' => $submission,
        ], 200);
    }
}

function qzorg_user_ip_address( $replacable, $quiz, $user, $questions = array() ) {
    if ( false !== strpos($replacable, "%%USER_IP_ADDR%%") ) {
        $replacable = str_replace("%%USER_IP_ADDR%%", Qzorg_Phtml::qzorg_set_user_ip(), $replacable );
    }
    return $replacable;
}

function qzorg_quiz_name_here( $replacable, $quiz, $user, $questions = array() ) {
    return false !== strpos($replacable, "%%QUIZ_TITLE%%") ? str_replace("%%QUIZ_TITLE%%", $quiz->quiz_name, $replacable ) : $replacable;
}

function qzorg_quiz_name_intro( $replacable, $quiz ) { 
    return false !== strpos($replacable, "%%QUIZ_TITLE%%") ? str_replace("%%QUIZ_TITLE%%", $quiz->quiz_name, $replacable ) : $replacable;
}

function qzorg_wp_user_name( $replacable, $quiz, $user, $questions = array() ) {
    return false !== strpos($replacable, "%%DISPLAY_WP_USER_NAME%%") ? str_replace("%%DISPLAY_WP_USER_NAME%%", ! empty($user) ? $user['username'] : "", $replacable ) : $replacable;
}

function qzorg_wp_user_email( $replacable, $quiz, $user, $questions = array() ) {
    return false !== strpos($replacable, "%%DISPLAY_WP_USER_EMIAL%%") ? str_replace("%%DISPLAY_WP_USER_EMIAL%%", ! empty($user) ? $user['email'] : "", $replacable ) : $replacable;
}

function qzorg_wp_admin_email( $replacable, $quiz, $user, $questions = array() ) {
    return false !== strpos($replacable, "%%DISPLAY_WP_ADMIN_EMIAL%%") ? str_replace("%%DISPLAY_WP_ADMIN_EMIAL%%", get_option('admin_email'), $replacable ) : $replacable;
}

function qzorg_wp_user_role( $replacable, $quiz, $user, $questions = array() ) {
    return false !== strpos($replacable, "%%DISPLAY_WP_USER_ROLE%%") ? str_replace("%%DISPLAY_WP_USER_ROLE%%", Qzorg_Phtml::qzorg_wp_user_role(), $replacable ) : $replacable;
}

function qzorg_quiz_submission_date( $replacable, $quiz, $user, $questions = array() ) {
    if ( false !== strpos($replacable, "%%QUIZ_SUBMISSION_DATE%%") ) {
        $replacable = str_replace("%%QUIZ_SUBMISSION_DATE%%", gmdate('d-m-Y'), $replacable );
    }
    return $replacable;
}

function qzorg_quiz_created_date( $replacable, $quiz, $user, $questions = array() ) {
    if ( false !== strpos($replacable, "%%QUIZ_CREATED_DATE%%") ) {
        $replacable = str_replace("%%QUIZ_CREATED_DATE%%", gmdate("d-m-Y", strtotime($quiz->created_at) ), $replacable );
    }
    return $replacable;
}

function qzorg_user_total_points_here( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%USER_TOTAL_EARN_POINTS%%") ? str_replace("%%USER_TOTAL_EARN_POINTS%%", $questions['total_points'], $replacable ) : $replacable;
}

function qzorg_correct_question_count( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%CORRECT_QUESTION_COUNT%%") ? str_replace("%%CORRECT_QUESTION_COUNT%%", $questions['correct_count'], $replacable ) : $replacable;
}

function qzorg_incorrect_question_count( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%INCORRECT_QUESTION_COUNT%%") ? str_replace("%%INCORRECT_QUESTION_COUNT%%", $questions['incorrect_count'], $replacable ) : $replacable;
}

function qzorg_unanswered_question_count( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%UNANSWERED_QUESTION_COUNT%%") ? str_replace("%%UNANSWERED_QUESTION_COUNT%%", $questions['unanswered_count'], $replacable ) : $replacable;
}

function qzorg_total_taken_question_count( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%TOTAL_TAKEN_QUESTION_COUNT%%") ? str_replace("%%TOTAL_TAKEN_QUESTION_COUNT%%", $questions['incorrect_count'] + $questions['correct_count'], $replacable ) : $replacable;
}

function qzorg_quiz_maximum_points( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%QUIZ_MAXIMUM_POINTS%%") ? str_replace("%%QUIZ_MAXIMUM_POINTS%%", $questions['quiz_maximum_points'], $replacable ) : $replacable;
}

function qzorg_quiz_average_points( $replacable, $quiz, $questions = array() ) {
    return false !== strpos($replacable, "%%AVERAGE_POINTS_PER_QUESTION%%") ? str_replace("%%AVERAGE_POINTS_PER_QUESTION%%", $questions['average_points'], $replacable ) : $replacable;
}

function qzorg_validate_quiz( $quiz, $settings ) {
    
    unset($quiz->quiz_tools);
    unset($quiz->other_tools);
    unset($quiz->updated_at);
    unset($quiz->preview_id);
    unset($quiz->shortcode);
    unset($quiz->author_id);
    unset($settings['emailconfiguration']);
    foreach ( $settings as $bk => $bv ) {
        foreach ( $bv as $ck => $cv ) {
            $quiz->{str_replace('-', '_', $ck)} = $cv;
        }
    }
    wp_add_inline_script(
        'qzorg_quiz__js', 
        "var qzorg_quiz = window.qzorg_quiz || {}; var id = " . esc_js($quiz->quiz_id) . "; qzorg_quiz[id] = " . wp_json_encode($quiz) . ";window.qzorg_quiz = qzorg_quiz;",
        'before'
    );
    wp_add_inline_script(
        'qzorg_math__js', 
        "MathJax.startup.promise.then(() => {
            MathJax.tex = {
                inlineMath: [['$', '$']],
                displayMath: [['$$', '$$']],
                processEscapes: true,
                processEnvironments: true
            };
            MathJax.options = {
                ignoreHtmlClass: 'qzorg-question-title'
            };
        })",
        'after'
    );
    return $quiz;
}

function qzorg_get_default_key( $key ) {
    $return = array();
    foreach ( Qzorg_Defaults::defaults()[ $key ] as $dkey => $dvalue ) {
        $return[ $dvalue['name'] ] = isset($dvalue['fill']) ? $dvalue['fill'] : "";
    }
    return $return;
}

function qzorg_set_html_content_type() {
    return 'text/html';
}
