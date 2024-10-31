<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add Defaults Fields
 * 
 * @since 1.0.0
 */
if ( ! class_exists('Qzorg_Defaults') ) {
    class Qzorg_Defaults
    {
        public static $defaults = [];

        /**
         * @since 1.0.0
         */
        public static function create( $parent, $sub ) {
            self::$defaults[ $parent ][] = $sub;
        }

        public static function init(){
            self::create('general', [
                'type'     => 'text',
                'name'     => 'quiz_name',
                'required' => 'true',
                'label'    => __('Quiz Name', 'quiz-organizer'),
                'clue'     => __('Quiz name is required.', 'quiz-organizer'),
                'class'    => 'regular-text',
            ]);
        
            self::create('general', [
                'type'     => 'radio',
                'name'     => 'quiz-type',
                'label'    => __('Quiz Type', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Points', 'quiz-organizer'),
                        'value' => 'points',
                    ),
                    array(
                        'label' => __('Right/Wrong', 'quiz-organizer'),
                        'value' => 'rightwronge',
                    ),
                    array(
                        'label' => __('Both', 'quiz-organizer'),
                        'value' => 'both',
                    ),
                ),
                'clue'     => __('Set the quiz type for your quiz as "both" by default.', 'quiz-organizer'),
                'fill'     => 'both',
                'show'     => 'none',
            ]);
        
            self::create('general', [
                'type'     => 'radio',
                'name'     => 'quiz-intro-page',
                'label'    => __('Display Quiz Intro Page', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('You can choose to show or hide the quiz introduction page, which will appear at the beginning of the quiz.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);    
        
            self::create('general', [
                'type'          => 'editor',
                'name'          => 'quiz_intro_section_qzorgmessage',
                'required'      => 'false',
                'editor_height' => 100,
                'label'         => __('Quiz introduction', 'quiz-organizer'),
                'clue'          => __('You can depict your quiz introduction briefly in this section.', 'quiz-organizer'),
                'fill'          => 'Greetings to the %%QUIZ_TITLE%%',
            ]);
        
            self::create('general', [
                'type'     => 'select',
                'name'     => 'display-page-no',
                'label'    => __('Display Page No.', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If "yes" is selected, the quiz page will display the page number.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('general', [
                'type'     => 'select',
                'name'     => 'show_question_number',
                'label'    => __('Display Question Number ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('You can use this option to for appearance of the question number.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('general', [
                'type'     => 'radio',
                'name'     => 'login-require',
                'label'    => __('Login Require ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('"If "yes" is selected, only logged-in users can take the quiz; otherwise, it\'s open to all."', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('general', [
                'type'          => 'editor',
                'name'          => 'login_require_qzorgmessage',
                'required'      => 'false',
                'label'         => __('Login Require Message', 'quiz-organizer'),
                'editor_height' => 100,
                'clue'          => __('Set a message for non-logged-in users.', 'quiz-organizer'),
                'fill'          => 'Login required for this quiz.',
            ]);
            
            self::create('quizpage', [
                'type'     => 'number',
                'name'     => 'question_per_page',
                'required' => 'false',
                'label'    => __('Question Per Page', 'quiz-organizer'),
                'clue'     => __('Customize the number of questions shown per page to override the default question order set in the All Questions tab; set to 0 to maintain the original order.', 'quiz-organizer'),
                'fill'     => 0,
                'min'      => 0,
            ]);
        
            self::create('quizpage', [
                'type'     => 'number',
                'name'     => 'quiz_duration',
                'required' => 'false',
                'label'    => __('Quiz Duration Minutes', 'quiz-organizer'),
                'clue'     => __('Set your quiz time in minutes. Set 0 for no time limit.', 'quiz-organizer'),
                'fill'     => 0,
                'min'      => 0,
            ]);
        
            self::create('quizpage', [
                'type'     => 'radio',
                'name'     => 'quiz-auto-submit',
                'label'    => __('Auto Submit Quiz On Time Over', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If this option is enable, the quiz will automatically submit when the time duration is over.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('quizpage', [
                'type'     => 'select',
                'name'     => 'instant-answer',
                'label'    => __('Display Instant Answer', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Enable', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('Disable', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('Instantly shows if answer is correct or not, along with a concise message.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('quizpage', [
                'type'     => 'radio',
                'name'     => 'submit-quiz-if-incorrect',
                'label'    => __('Submit Quiz If Incorrect Answer ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If the option "yes" is picked, the quiz will automatically submit whenever an incorrect answer is encountered. This capability is applicable to question types such as Drop Down, Radio Ans (Multiple Choice), and Checkbox Ans (Multiple Response).', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('quizpage', [
                'type'     => 'select',
                'name'     => 'randomize_options',
                'label'    => __('Randomize Options', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Default Order', 'quiz-organizer'),
                        'value' => 0,
                    ),
                    array(
                        'label' => __('Randomize Questions Order', 'quiz-organizer'),
                        'value' => 1,
                    ),
                    array(
                        'label' => __('Randomize Answers Order', 'quiz-organizer'),
                        'value' => 2,
                    ),
                    array(
                        'label' => __('Randomize Both', 'quiz-organizer'),
                        'value' => 3,
                    ),
                ),
                'clue'     => __('Select any option for randomize questions, answer or both from here ( Result page will show your default question answer order ).', 'quiz-organizer'),
                'fill'     => 0,
            ]);
        
            self::create('quizpage', [
                'type'     => 'radio',
                'name'     => 'display-progressbar',
                'label'    => __('Display Progressbar ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('When the quiz consists of multiple pages, this option will display a progress bar on the quiz page.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('quizpage', [
                'type'     => 'select',
                'name'     => 'display_cat_name',
                'label'    => __('Display Category Name ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('This option will present the category name that was picked within the question.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('quizpage', [
                'type'     => 'radio',
                'name'     => 'category_wise_question',
                'label'    => __('Display Category Wise Questions ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If you choose "yes", the quiz will display questions categorized accordingly.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('styles', [
                'type'     => 'text',
                'name'     => 'quiz_text_color',
                'required' => 'false',
                'label'    => __('Quiz Text Color', 'quiz-organizer'),
                'clue'     => __('Please set the text color for your quiz from here.', 'quiz-organizer'),
                'fill'     => '#202124',
                'class'    => 'qzorg-color-picker',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'text',
                'name'     => 'quiz_bg_color',
                'required' => 'false',
                'label'    => __('Quiz Background Color', 'quiz-organizer'),
                'clue'     => __('Please change the background color of your quiz.', 'quiz-organizer'),
                'fill'     => '#FFFFFF',
                'class'    => 'qzorg-color-picker',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'text',
                'name'     => 'question_text_color',
                'required' => 'false',
                'label'    => __('Question Text Color', 'quiz-organizer'),
                'clue'     => __('You can change the text color of the quiz question title from here.', 'quiz-organizer'),
                'fill'     => '#202124',
                'class'    => 'qzorg-color-picker',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'text',
                'name'     => 'button_text_color',
                'required' => 'false',
                'label'    => __('Button Text Color', 'quiz-organizer'),
                'clue'     => __('You can change the text color of your button from here.', 'quiz-organizer'),
                'fill'     => '#FFFFFF',
                'class'    => 'qzorg-color-picker',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'text',
                'name'     => 'button_bg_color',
                'required' => 'false',
                'label'    => __('Button Background Color', 'quiz-organizer'),
                'clue'     => __('Change the background color of your button from here.', 'quiz-organizer'),
                'fill'     => '#1a73e8',
                'class'    => 'qzorg-color-picker',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'select',
                'name'     => 'page-animation',
                'label'    => __('Quiz Page Animation', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('No Animation', 'quiz-organizer'),
                        'value' => 'default',
                    ), 
                    array(
                        'label' => __('Bounce In', 'quiz-organizer'),
                        'value' => 'bounce-in',
                    ),
                    array(
                        'label' => __('Fade In', 'quiz-organizer'),
                        'value' => 'fade-in',
                    ),
                    array(
                        'label' => __('Scale In', 'quiz-organizer'),
                        'value' => 'scale-in',
                    ),
                    array(
                        'label' => __('Flip In', 'quiz-organizer'),
                        'value' => 'flip-in',
                    ),
                    array(
                        'label' => __('Pulse In', 'quiz-organizer'),
                        'value' => 'pulse-in',
                    ),
                    array(
                        'label' => __('Elastic', 'quiz-organizer'),
                        'value' => 'elastic',
                    ),          
                ),
                'clue'     => __('This won\'t be effective for the quiz that consists of only one page.', 'quiz-organizer'),
                'fill'     => 'default',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'     => 'select',
                'name'     => 'quiz-animation',
                'label'    => __('Quiz Background Animation', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('No Animation', 'quiz-organizer'),
                        'value' => 'noanimation',
                    ),
                    array(
                        'label' => __('Right to Left', 'quiz-organizer'),
                        'value' => 'marqueerighttoleft',
                    ),
                    array(
                        'label' => __('Top to Bottom', 'quiz-organizer'),
                        'value' => 'marqueetoptobottom',
                    ),
                    array(
                        'label' => __('Bottom to Top', 'quiz-organizer'),
                        'value' => 'marqueebottomtotop',
                    ),
                    array(
                        'label' => __('Center Horizontal', 'quiz-organizer'),
                        'value' => 'marqueecenterhorizontal',
                    ),
                    array(
                        'label' => __('Center Vertical', 'quiz-organizer'),
                        'value' => 'marqueecentervertical',
                    ),
                    array(
                        'label' => __('Diagonal Top Left', 'quiz-organizer'),
                        'value' => 'marqueediagonaltopleft',
                    ),
                    array(
                        'label' => __('Diagonal Top Right', 'quiz-organizer'),
                        'value' => 'marqueediagonaltopright',
                    ),
                    array(
                        'label' => __('Diagonal Bottom Left', 'quiz-organizer'),
                        'value' => 'marqueediagonalbottomleft',
                    ),
                    array(
                        'label' => __('Diagonal Bottom Right', 'quiz-organizer'),
                        'value' => 'marqueediagonalbottomright',
                    ),
                    array(
                        'label' => __('Diagonal Center', 'quiz-organizer'),
                        'value' => 'marqueediagonalcenter',
                    ),     
                ),
                'clue'     => __('To apply this animation effect please select quiz "background image".', 'quiz-organizer'),
                'fill'     => 'noanimation',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('styles', [
                'type'        => 'url',
                'name'        => 'quiz_background_image',
                'required'    => 'false',
                'label'       => __('Quiz Background Image', 'quiz-organizer'),
                'clue'        => '',
                'fill'        => '',
                'button'      => 'yes',
                'button_text' => __('Select Image', 'quiz-organizer'),
                'class'       => 'quiz-backgroundimage',
            ]);
        
            self::create('resultpage', [
                'type'     => 'editor',
                'name'     => 'resultpage_qzorgmessage',
                'required' => 'false',
                'label'    => __('Set Result Page Content', 'quiz-organizer'),
                'clue'     => __('The content for your result page will appear on the result page.', 'quiz-organizer'),
                'fill'     => 'Thanks for submitting your quiz! Here are your results.
                %%QUIZ_RESULTS_HERE%%',
            ]);
        
            self::create('resultpage', [
                'type'     => 'radio',
                'name'     => 'display-answer-status',
                'label'    => __('Show Correct Answer', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('The correct answer will be distinguished through highlighting at quiz results.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('resultpage', [
                'type'     => 'radio',
                'name'     => 'display_restart_button',
                'label'    => __('Show Restart Button', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('Select whether you\'d like to show a "Restart Quiz" button after submitting the quiz.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('resultpage', [
                'type'     => 'url',
                'name'     => 'redirect_after_submit',
                'required' => 'false',
                'label'    => __('Redirect After Submit ?', 'quiz-organizer'),
                'clue'     => __('Redirect to the specified URL after submission. Leave it blank for no redirect.', 'quiz-organizer'),
                'fill'     => '',
            ]);
            
            self::create('resultpage', [
                'type'     => 'radio',
                'name'     => 'save_result_to_db',
                'label'    => __('Save Result To Database ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('Select your preference for storing data in the database: yes or no.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'text',
                'name'     => 'send_email_from_user',
                'required' => 'false',
                'label'    => __('Email From Name', 'quiz-organizer'),
                'clue'     => __('The default email from name for emails will be the site title.', 'quiz-organizer'),
                'fill'     => get_bloginfo('name'),
                'class'    => 'regular-text',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'email',
                'name'     => 'send_email_from',
                'required' => 'false',
                'label'    => __('Email From', 'quiz-organizer'),
                'clue'     => __('Email is required. By default email will be the site administrator email from the options table.', 'quiz-organizer'),
                'fill'     => get_option('admin_email'),
                'class'    => 'regular-text',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'text',
                'name'     => 'send_email_subject',
                'required' => 'false',
                'label'    => __('Email Subject', 'quiz-organizer'),
                'clue'     => __('Set subject for your email.', 'quiz-organizer'),
                'fill'     => 'Submission For %%QUIZ_TITLE%%',
                'class'    => 'regular-text',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'editor',
                'name'     => 'send_email_qzorgmessage',
                'required' => 'false',
                'label'    => __('Email Body', 'quiz-organizer'),
                'clue'     => __('Set your email content for the quiz that you want to share with the user.', 'quiz-organizer'),
                'fill'     => 'Thanks for submitting your quiz! Here are your results.
                %%QUIZ_RESULTS_HERE%%',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'radio',
                'name'     => 'send_email_to_users',
                'label'    => __('Send An Email To User ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If "Yes" is selected, then an email will be sent to the user.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('emailconfiguration', [
                'type'     => 'radio',
                'name'     => 'send_email_to_admin',
                'label'    => __('Send An Email To Admin ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If "Yes" is selected, then the WordPress administrator (from the options table) will receive this email.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'start_quiz_label',
                'required' => 'false',
                'label'    => __('Start Quiz', 'quiz-organizer'),
                'clue'     => __('Set the label of quiz "Start Quiz" button as "Start Quiz" by default.', 'quiz-organizer'),
                'fill'     => __('Start Quiz', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'submit_quiz_label',
                'required' => 'false',
                'label'    => __('Submit Quiz', 'quiz-organizer'),
                'clue'     => __('Set the label of quiz "Submit" button as "Submit" by default.Set the label of quiz "Submit" button as "Submit" by default.', 'quiz-organizer'),
                'fill'     => __('Submit', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'quiz_previous_page_label',
                'required' => 'false',
                'label'    => __('Previous Page', 'quiz-organizer'),
                'clue'     => __('Set the label of quiz "Previous" button as "Previous" by default.', 'quiz-organizer'),
                'fill'     => __('Previous', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'quiz_next_page_label',
                'required' => 'false',
                'label'    => __('Next Page', 'quiz-organizer'),
                'clue'     => __('Set the label of quiz "Next" button as "Next" by default.', 'quiz-organizer'),
                'fill'     => __('Next', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'restart_quiz_label',
                'required' => 'false',
                'label'    => __('Restart Quiz', 'quiz-organizer'),
                'clue'     => __('Set the label of quiz "Restart" button as "Restart" by default.', 'quiz-organizer'),
                'fill'     => __('Restart', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'required_field_text',
                'required' => 'false',
                'label'    => __('Required Field Text', 'quiz-organizer'),
                'clue'     => __('This text show if the question is required.', 'quiz-organizer'),
                'fill'     => __('Please fill the required field', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'right_answer_text',
                'required' => 'false',
                'label'    => __('Right Answer Text', 'quiz-organizer'),
                'clue'     => __('Display right answer feedback, requires instant answer option enabled.', 'quiz-organizer'),
                'fill'     => __('That\'s the right answer', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'wrong_answer_text',
                'required' => 'false',
                'label'    => __('Wrong Answer Text', 'quiz-organizer'),
                'clue'     => __('Display wrong answer feedback, requires instant answer option enabled.', 'quiz-organizer'),
                'fill'     => __('That\'s the wrong answer', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'expired_quiz_text',
                'required' => 'false',
                'label'    => __('Message For Expired Quiz', 'quiz-organizer'),
                'clue'     => __('This message will appear when the quiz end date is reached.', 'quiz-organizer'),
                'fill'     => __('The quiz has ended', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('othertext', [
                'type'     => 'text',
                'name'     => 'limit_submission_text',
                'required' => 'false',
                'label'    => __('Limied Submission Text', 'quiz-organizer'),
                'clue'     => __('Set message to users for limited submission.', 'quiz-organizer'),
                'fill'     => __('Quiz submission limit reached', 'quiz-organizer'),
                'class'    => 'regular-text',
                'wrap'     => 'qzorg-flex-wrap',
            ]);
        
            self::create('advance', [
                'type' => 'contact_custom',
                'name' => 'wrong_answer_text',
                'fill' => '',
            ]);
            
            self::create('advance', [
                'type'     => 'radio',
                'name'     => 'display_contact_form',
                'label'    => __('Display Contact Form ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('If "Yes" is selected, then this form will appear at the beginning of the quiz.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
            
            self::create('advance', [
                'type'     => 'select',
                'name'     => 'contact_form_to_show',
                'label'    => __('Contact Form To Show ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Only Logged In Users', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('For All Users', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('Select one option to show a contact form for all or logged-in users only.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('advance', [
                'type'     => 'select',
                'name'     => 'all_correct_answer',
                'label'    => __('Required All Correct Answers ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('This only applicable for Checkbox Ans (Multiple Response) question type.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('advance', [
                'type'     => 'select',
                'name'     => 'disable_answer_options',
                'label'    => __('Disable Answers After Selection ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('This only applicable for Radio Ans (Multiple Choice) question type.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('advance', [
                'type'     => 'radio',
                'name'     => 'add_answer_class',
                'label'    => __('Add Correct Incorrect Class ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('no', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('This option will add correct incorrect class, only applicable for Radio Ans (Multiple Choice) question type.', 'quiz-organizer'),
                'fill'     => 'no',
            ]);
        
            self::create('advance', [
                'type'     => 'select',
                'name'     => 'required_math_js',
                'label'    => __('Required Mathematical JS ?', 'quiz-organizer'),
                'required' => 'false',
                'options'  => array(
                    array(
                        'label' => __('Yes', 'quiz-organizer'),
                        'value' => 'yes',
                    ),
                    array(
                        'label' => __('No', 'quiz-organizer'),
                        'value' => 'no',
                    ),
                ),
                'clue'     => __('This option is use for create an equation with variables and mathematical expression questions.', 'quiz-organizer'),
                'fill'     => 'yes',
            ]);
        
            self::create('advance', [
                'type'     => 'number',
                'name'     => 'quiz_submission_limit',
                'required' => 'true',
                'label'    => __('Quiz Submission Limit', 'quiz-organizer'),
                'clue'     => __('When this quiz submission limits reached other users can not submit quiz default 0 for no limit.', 'quiz-organizer'),
                'fill'     => 0,
                'min'      => 0,
            ]);
        
            self::create('advance', [
                'type'     => 'number',
                'name'     => 'qzorg_decimal_places',
                'required' => 'false',
                'label'    => __('Allow Decimal Places', 'quiz-organizer'),
                'clue'     => __('This number will ues for rounds specified number of decimal places.', 'quiz-organizer'),
                'fill'     => 1,
                'min'      => 0,
            ]);
        
            self::create('advance', [
                'type'  => 'date',
                'name'  => 'quiz_start_date',
                'label' => __('Quiz Start Date', 'quiz-organizer'),
                'clue'  => __('Set the date to start the quiz, leave it blank to make the quiz available anytime. Leave blank for today.', 'quiz-organizer'),
                'fill'  => gmdate('d-m-Y'),
            ]);
        
            self::create('advance', [
                'type'  => 'date',
                'name'  => 'quiz_end_date',
                'label' => __('Quiz End Date', 'quiz-organizer'),
                'clue'  => __('Set the date to end the quiz, after end date quiz will not available.', 'quiz-organizer'),
                'fill'  => "",
            ]);
        }

        /**
         * @since 1.0.0
         */
        public static function defaults() {
            return self::$defaults;
        }

        /**
         * @since 1.0.0
         */
        public static function settings( $args, $callable ) {
            if ( empty($args) ) {
                return; 
            }
            if ( method_exists(Qzorg_Phtml::class, $callable) ) {
                Qzorg_Phtml::$callable($args);
            }
        }

        /**
         * @since 1.0.0
         */

        public static function get_nonce_validation_error() {
            wp_send_json_error([
                'message' => __('Nonce validation failed please reload the page !', 'quiz-organizer'),
            ]);
        }

        /**
         * To sanitize multidimensional array.
         *
         * This function takes an array as a reference and sanitizes array data within it.
         *
         * @param array &$array The array containing all fields to be sanitized.
         *
         * @return array The sanitized array.
         */
        public static function qzorg_sanitize_text_field( &$array, $single = 0 ) {

            if ( 1 == $single ) {
                foreach ( $array as $key => $value ) {
                    if ( '_qzorgmessage' == substr($key, -13) ) {
                        $array[ $key ] = htmlspecialchars_decode($array[ $key ], ENT_QUOTES);
                    } else {
                        if ( "answers" == $key ) {
                            if ( ! empty($array[ $key ]) ) {
                                foreach ( $array[ $key ] as $akey => $avalue ) {
                                    $array[ $key ][ $akey ]['answer'] = htmlspecialchars_decode($array[ $key ][ $akey ]['answer'], ENT_QUOTES);
                                }
                            }
                        }
                    }
                }
            }else {
                foreach ( $array as $key => $value ) {
                    if ( '_qzorgmessage' == substr($value['name'], -13) ) {
                        $array[ $key ]['value'] = wp_kses_post(htmlspecialchars_decode($array[ $key ]['value'], ENT_QUOTES));
                    }else {
                        $array[ $key ]['value'] = sanitize_text_field($array[ $key ]['value']);
                    }
                }
            }

            return $array;
        }

        /**
         * @since 1.0.0
         */

        public static function setup_email( $message, $subject ) {
            $return = '<html><head>';
            $return .= self::qzorg_set_css_style();
            $return .= '<title>' . $subject . '</title></head><body><div class="qzorg-max-width">';
            $return .= $message;
            $return .= '</div></body></html>';
            return $return;
        }

        public static function qzorg_set_css_style() {
            return "<style type='text/css'>.qzorg-correct-r-answer {
            color: green;
        }

        .qzorg-max-width {
            max-width: 100vw;
        }
        
        .qzorg-quiz-r-answer-options.qzorg-correct-r-answer {
            color: #00ff00;
            font-weight: 600;
        }
        
        .qzorg-r-user-answer.qzorg-correct-r-answer {
            color: #00ff00;
        }
        
        .qzorg-r-user-answer.qzorg-incorrect-r-answer {
            color: #ff0000;
        }
        
        .qzorg-r-user-answer.qzorg-unanswered-r-answer {
            color: #ff0000;
        }
        
        .qzorg-results-wrapper .qzorg-wrapper-r-answer {
            border-bottom: 1px solid #ccc;
            padding: 15px 5px 5px 5px;
            margin: 10px 0 10px 0;
        }
        
        .qzorg-results-wrapper .qzorg-wrapper-r-answer .qzorg-r-question {
            font-weight: 500;
        }
        
        .qzorg-results-wrapper .qzorg-wrapper-r-answer .qzorg-r-user-answer {
            margin-top: 5px;
        }
        
        .qzorg-r-question {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
            padding: 5px 0;
        }
        
        .qzorg-quiz-r-answer-options {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .qzorg-quiz-r-answer-options.qzorg-correct-r-answer {
            color: #00ff00;
            font-weight: 600;
        }
        
        .qzorg-quiz-r-answer-options {
            padding: 0 0 0 15px;
        }
        
        .qzorg-quiz-r-answer-options,
        .qzorg-r-user-answer {
            font-weight: normal;
        }
        
        .qzorg-r-by-user {
            margin-top: 10px;
            font-style: italic;
        }</style>";
        }

        public static function documentation( $source, $medium, $campaign, $path, $slug = "documentation" ) {
            $query_args = array(
                'utm_source'   => $source,
                'utm_medium'   => $medium,
                'utm_campaign' => $campaign,
            );
            return add_query_arg($query_args, QZORG_URL . '/'.$slug.'/' . $path);
        }

        public static function footer() {
            ?>
        <div class="qzorg-footer-text">
            <p><?php echo esc_html__('Thank you for using Quiz Organizer ', 'quiz-organizer'); ?></p>
            <a href="<?php echo esc_url(Qzorg_Defaults::documentation('core_plugin', 'admin_dashboard', 'documentation', 'quizorganizer')); ?>" title="<?php echo esc_attr(__('Documentation', 'quiz-organizer')); ?>" target="_blank"><?php echo esc_html(__('Documentation', 'quiz-organizer')); ?></a>.
        </div>
        <?php
        }

        public static function qzorg_round( $value, $point ) {
            return round($value, $point);
        }

        public static function qzorg_random( $array ) {
            $keys = array_keys($array);
            shuffle($keys);
            $new_array = [];
            foreach ( $keys as $key ) {
                $new_array[ $key ] = $array[ $key ];
            }
            $array = $new_array;
            return $array;
        }

        public static function qzorg_random_q( $array ) {
            $randomized = array();
            $break = array();
            $output = array();
            $currenIndex = 0;
            foreach ( $array as $key => $value ) {
                $break[] = count($value);
                $randomized = array_merge($randomized, $value);
            }
            shuffle($randomized);
            foreach ( $break as $key => $single ) {
                $outputInner = [];
                for ( $i = 0; $i < $single; $i++ ) {
                    $outputInner[] = $randomized[ $currenIndex ];
                    $currenIndex++;
                }
                $output[] = $outputInner;
            }
            return $output;
        }

        public static function get_default_colors() {
            return array(
                "quiz_text_color"     => '#202124',
                "quiz_bg_color"       => '#FFFFFF',
                "question_text_color" => '#202124',
                "button_text_color"   => '#FFFFFF',
                "button_bg_color"     => '#1a73e8',
            );
        }

        public static function get_unique_id() {
            $uniqid = uniqid("", true);
            $uniqueId = uniqid();
            $uniqueId = $uniqueId . substr($uniqid, 5, 8);
            return $uniqueId;
        }   

        public static function get_header( $pagenow, $quiz_id, $tabcurrent = "" ) {
            $shortcode = '[quiz-organizer id='.$quiz_id.']';
            $tabarray = array(
                "qzorgquestions" => __('All Questions', 'quiz-organizer'),
                "quizsettings"   => __('Quiz Settings', 'quiz-organizer'),
            ); ?>
            <div class="qzorg-upper-menu-wrapper">
                <div class="qzorg-site-logo qzorg-col-1">
                    <img src="<?php echo esc_url(QZORG_IMAGE_URL.'/logo.png'); ?>"> 
                </div>
                <div class="qzorg-upper-menu-left qzorg-col-2">
                    <ul class="qzorg-upper-menu"> <?php 
                    foreach ( $tabarray as $hkey => $htab ) {
                        $currenttab = $tabcurrent == $hkey ? "current" : "";
                        ?>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=' . $pagenow . '&quizid=' . $quiz_id . '&tab=' . $hkey)); ?>" class="<?php echo esc_attr($currenttab); ?>"><?php echo esc_html($htab); ?></a></li>
                    <?php } ?>
                    </ul>
                </div>
                <?php Qzorg_Defaults::doclink(Qzorg_Defaults::documentation('core_plugin', 'qzorg_modify_quiz', QZORG_VERSION, 'get-help', 'docs')); ?>
            </div> <?php 
        }

        public static function doclink( $link ) { ?>
            <div class="qzorg-upper-menu-docs">
                <div class="qzorg-view-docs"><a class="qzorg-visit-docs-url" target="_blank" href="<?php echo esc_url($link); ?>"><span class="dashicons dashicons-media-document"></span><?php esc_html_e( 'View Documentation', 'quiz-organizer' ); ?></a></div>
            </div>
        <?php 
        }

        public static function get_footer(){
            return "";
        }

        public function verify( $q ) {
            if ( isset($_POST[ $q->question_type . $q->question_id ]) ) {
                switch ( $q->question_type ) {
                    case "checkbox":
                        $ans_val = array_map('sanitize_text_field', wp_unslash($_POST[ $q->question_type . $q->question_id ]));
                        break;
                
                    case "singlelinetext":
                        $ans_val = sanitize_text_field(wp_unslash($_POST[ $q->question_type . $q->question_id ]));
                        break;
                
                    case "paragraphtext":
                        $ans_val = sanitize_text_field(wp_unslash($_POST[ $q->question_type . $q->question_id ]));
                        break;
                
                    case "date":
                        $ans_val = sanitize_text_field(wp_unslash($_POST[ $q->question_type . $q->question_id ]));
                        break;
                
                    default:
                        $ans_val = sanitize_text_field(wp_unslash($_POST[ $q->question_type . $q->question_id ]));
                        break;
                }
            } else {
                $ans_val = "";
            }
            return $ans_val;
        }

    }
}