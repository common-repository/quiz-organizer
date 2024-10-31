<?php
/**
 * @since 1.0.0
 * @return : Quiz modify page
 */
class Qzorg_ModificationPage {

    private $page = "";
    private $quiz_id = 0;

    public function __construct( $quiz_id, $page ) {
        $this->quiz_id = $quiz_id;
        $this->page = $page;
    }

    public function qzorg_display_modification_form(){ 
        $tabcurrent = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : "";
        $db = new Qzorg_Db();
        $shortcode = '[quiz-organizer id='.$this->quiz_id.']';
        $quiz_obj = $db->qzorg_get_quiz_data( $this->quiz_id );
        if ( $quiz_obj ) {
            ?>
            <div class="wrap qzorg-custom qzorg-unique-modify-page">
                <h1 style="display: none;" class="wp-heading-inline"><?php esc_html_e('Questions', 'quiz-organizer'); ?> ( <?php echo wp_kses_post($quiz_obj->quiz_name); ?> )</h1>
                <hr class="wp-header-end">
                <div class="qzorg-before-form-data">
                    <?php Qzorg_Defaults::get_header($this->page, $this->quiz_id, $tabcurrent); ?>
                </div>
                <?php 
                if ( "qzorgquestions" == $_GET['tab'] ) { 
                    wp_enqueue_media();
                    $localize_array = array(
                        'ql_nonce'          => wp_create_nonce('qzorg_ql_nonce'),
                        'is_true'           => __('True' , 'quiz-organizer'),
                        'ajax_url'          => admin_url('admin-ajax.php'),
                        'nonce'             => wp_create_nonce( 'order_nonce' ),
                        'remove_page'       => __('Remove Page' , 'quiz-organizer'),
                        'remove_question'   => wp_create_nonce('remove_question_nonce'),
                        'new_page'          => __('New Page' , 'quiz-organizer'),
                        'new_question'      => __('Here\'s your new question!' , 'quiz-organizer'),
                        'qzorg_image_nonce' => wp_create_nonce('qzorg_image_nonce'),
                        'check_if_correct'  => __('Check if correct' , 'quiz-organizer'),
                        'display_flex'      => __('Check if display answers horizontally' , 'quiz-organizer'),
                        'blank_title'       => __('Please set your question title.' , 'quiz-organizer'),
                        'require_status'    => __('Please select quiz status.' , 'quiz-organizer'),
                        'question_nonce'    => wp_create_nonce( 'new_question_nonce' ),
                        'copy_question'     => wp_create_nonce('copy_question_nonce'),
                        'root'              => esc_url_raw( rest_url() ),
                        'noncewp'           => wp_create_nonce( 'wp_rest' ),
                        'empty_questions'   => __('No questions Found !', 'quiz-organizer'),
                        'confirmation'      => __('You are about to permeanently delete this page question from this quiz. Are you sure to process this action?', 'quiz-organizer'),
                        'add_q_response'    => __('Question added successfully !', 'quiz-organizer'),
                    );
                    wp_localize_script( 'qzorg_adminjs', 'qzorgModify', $localize_array); 
                    $question_types = Qzorg_Phtml::qzorg_question_types();
                    $pdb = new Qzorg_Pdb();
                    ?>
                    <div class="qzorg-container-question-list">
                        <div><h3><?php echo wp_kses_post($quiz_obj->quiz_name); ?></h3></div>
                        <div>
                            <div class="qzorg-col-3">
                                <div class="qzorg-view-quiz"><span class="dashicons dashicons-shortcode"></span><?php echo esc_html( $shortcode ); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $questions_obj = $pdb->qzorg_get_questionlist( $this->quiz_id );
                    $categories_obj = $this->categories();
                    $this->questions($questions_obj, $categories_obj, $question_types );
                }elseif ( "quizsettings" == $_GET['tab'] ) {
                    $localize_array = array(
                        'is_true'           => __('True' , 'quiz-organizer'),
                        'ajax_url'          => admin_url('admin-ajax.php'),
                        'qzorg_image_nonce' => wp_create_nonce('qzorg_image_nonce'),
                    );
                    wp_localize_script( 'qzorg_adminjs', 'qzorgSettings', $localize_array); 
                    $quiz_tools = maybe_unserialize($quiz_obj->quiz_tools);
                    $quiz_tabs = apply_filters( 'qzorg_tags_custom_filter', Qzorg_Defaults::defaults());
                    $this->settings($quiz_tabs, $quiz_tools, $quiz_obj);
                } ?>
            </div>
        <?php 
            add_action( 'admin_footer', array( $this, 'template' ) );
        } else {
            Qzorg_Notification_Guide::qzorg_add_admin_notice("Quiz Does Not Exist.<a href='".QZORG_ADMIN_URL."admin.php?page=".QZORG_MENU."'>Return</a>", Qzorg_Notification_Guide::ADMIN_NOTICE_ERROR, false);
        }
    }
    /**
     * @version 1.0.0
     * Display question list
     */

    public function questions( $questions_obj, $categories_obj, $question_types ) { 
        ?>
        <form  method="POST" class="qzorg-modify-form accordion-body-form " >
            <div class="qzorg-wrapper-modify qzorg-page-wrapper">
                <?php wp_nonce_field('qzorg_update_quiz', 'qzorg_update_quiz_nonce'); ?>
                <input type="hidden" name="quiz_id" id="quiz_id" value="<?php echo esc_attr($this->quiz_id); ?>" class="regular-text">
                <?php  if ( $questions_obj ) {foreach ( $questions_obj as $each_key => $each_obj ) { if ( $each_obj ) { ?>
                <!-- In Page Loop -->
                <div class="qzorg-page" data-page="<?php echo esc_attr($each_key); ?>">
                <div class="qzorg-page-settings qzorg-page-settings-top">
                <strong><?php
                /* translators: %d is the page number */
                printf( esc_html__( 'Page No: %d', 'quiz-organizer' ), esc_attr( $each_key + 1 ) );
                ?></strong>
                </div>
                <div class="qzorg-accordion"  data-page="<?php echo esc_attr($each_key); ?>">
                    <?php
                        foreach ( $each_obj as $key => $question ) { 
                            $answers = maybe_unserialize($question->question_answer);
                            $tools = maybe_unserialize($question->question_tools);
                            $categories = ! empty($tools['categories']) ? $tools['categories'] : array();
                            ?>
                            <!-- In Question Loop -->
                            <div class="qzorg-accordion-parent" data-id="<?php echo esc_attr( $question->question_id ); ?>">
                                <div class="qzorg-accordion-header">
                                    <div class="qzorg-question-text">
                                        <span class="question-title-span"><?php echo esc_html($question->question_title); ?></span>
                                    </div>
                                    <div class="qzorg-settings">
                                        <button class="accordion-arrow"><span title="<?php esc_attr_e('Edit Question', 'quiz-organizer'); ?>" class="dashicons dashicons-admin-settings "></span></button>
                                        <button class="qzorg-duplicate-question"><span title="<?php esc_attr_e('Duplicate question', 'quiz-organizer'); ?>" class="dashicons dashicons-admin-page "></span></button>
                                        <button class="qzorg-question-delete"><span title="<?php esc_attr_e('Delete Question', 'quiz-organizer'); ?>" class="dashicons dashicons-trash"></span></button>
                                    </div>
                                </div>
                                <div class="qzorg-accordion-content" data-id="<?php echo esc_attr( $question->question_id ); ?>">
                                    <div class="qzorg-section qzorg-question-title"><input type="text" name="question_title" placeholder="<?php esc_attr_e('Your Question', 'quiz-organizer'); ?>" value="<?php echo esc_attr( $question->question_title ); ?>" class="regular-text qzorg-question-input question_title"></div>
                                    <div class="qzorg-section qzorg-selectable-field">
                                        <div class="qzorg-question-categories">
                                            <label for="question_type<?php echo esc_attr( $question->question_id ); ?>"><?php esc_html_e('Question Type', 'quiz-organizer'); ?></label>
                                            <select id="question_type<?php echo esc_attr( $question->question_id ); ?>" name="question_type" class="qzorg-question-type qzorg-select-box">
                                                <option value=""><?php esc_html_e( 'Question Type', 'quiz-organizer' ); ?></option>
                                                <?php
                                                    foreach ( $question_types as $key => $each_type ) { 
                                                        echo '<option '. selected( $key, $question->question_type, false) .' value="' . esc_attr( $key ) . '">' . esc_html( $each_type ) . '</option>';
                                                    } ?>
                                            </select>
                                        </div>
                                        <div class="qzorg-category-list">
                                            <label for="category_type<?php echo esc_attr( $question->question_id ); ?>" class="qzorg-category-label"><?php esc_html_e('Categories', 'quiz-organizer'); ?></label>
                                            <select id="category_type<?php echo esc_attr( $question->question_id ); ?>" name="category_type" class="question_category qzorg-select-box">
                                                <option value=""><?php esc_html_e( 'Select Category', 'quiz-organizer' ); ?></option>
                                                <?php
                                                if ( ! empty($categories_obj) ) {
                                                    foreach ( $categories_obj as $key => $each_type ) { 
                                                        echo '<option '. selected( 1, in_array($each_type->id, $categories, true), false) .' value="' . esc_attr( $each_type->id ) . '">' . esc_html( $each_type->category_name ) . '</option>';
                                                } 
}?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="qzorg-section qzorg-question-answers">
                                        <div class="qzorg-question-tabs">
                                            <div class="answer_list"> <?php 
                                                if ( ! empty( $answers) ) {
                                                    foreach ( $answers as $key => $each_answer ) { ?>
                                                        <div class="answer-fields">
                                                            <div class="qzorg-col que-col">
                                                                <div class="qzorg-ans-ans">
                                                                    <input name="answer" type="text" class="answer-input" value="<?php echo esc_attr( $each_answer['answer'] ); ?>" placeholder="<?php esc_attr_e('Your Answer', 'quiz-organizer'); ?>">
                                                                </div>
                                                                <div class="qzorg-ans-point">
                                                                    <input name="answer_point" type="number" class="answer-point" value="<?php echo $each_answer['points'] ? esc_attr( $each_answer['points']) : 0; ?>" placeholder="<?php esc_attr_e('Point(s)', 'quiz-organizer'); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="qzorg-col settings-col">
                                                                <div class="qzorg-ans-checkbox">
                                                                    <input name="is_correct" type="checkbox" class="answer-correct" <?php checked( $each_answer["is_correct"], 1 ); ?> value="<?php echo esc_attr( $each_answer['is_correct'] ); ?>">
                                                                </div>
                                                                <div class="qzorg-ans-remove">
                                                                    <span class="dashicons dashicons-trash qzorg-remove-answer"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } 
                                                } else { ?>
                                                    <div class="answer-fields">
                                                        <div class="qzorg-col que-col">
                                                            <div class="qzorg-ans-ans">
                                                                <input name="answer" type="text" class="answer-input" value="" placeholder="<?php esc_attr_e('Your Answer', 'quiz-organizer'); ?>">
                                                            </div>
                                                            <div class="qzorg-ans-point">
                                                                <input name="answer_point" type="number" class="answer-point" value="" placeholder="<?php esc_attr_e('Point(s)', 'quiz-organizer'); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="qzorg-col settings-col">
                                                            <div class="qzorg-ans-checkbox">
                                                                <input name="is_correct" type="checkbox" class="answer-correct" value="1">
                                                            </div>
                                                            <div class="qzorg-ans-remove">
                                                                <span class="dashicons dashicons-trash qzorg-remove-answer"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php } ?>
                                            </div>
                                        </div>
                                        <div class="qzorg-new-question-wrapper">
                                            <div class="new_answer">
                                                <button class="new-answer-button button" ><?php esc_html_e('(+) Add New Answer', 'quiz-organizer'); ?></button>
                                            </div>
                                        </div>
                                        <div class="other-setting-wrapper">
                                            <div class="image-upload-wrapper qzorg-left-part">
                                                <input type="hidden" class="qzorg-question-image" name="question_image" value="<?php echo esc_attr($question->question_image); ?>">
                                                <div class="qzorg-upload-div">
                                                    <img class="qzorg-image-preview" src="<?php echo esc_url( $this->imageurl($question->question_image)); ?>" alt="<?php echo esc_attr(__('Select Image', 'quiz-organizer')); ?>">
                                                    <div class=""><button class="qzorg-select-image-button button"><?php echo esc_html(__('Select Image', 'quiz-organizer')); ?></button></div>
                                                </div>
                                            </div>
                                            <div class="qzorg-right-part">
                                                <div class="qzorg-right-part-inner">
                                                    <label class="field-label"><?php esc_html_e('Is Required ?', 'quiz-organizer'); ?></label>
                                                    <input type="checkbox" name="required_question" value="1" <?php checked( true, (( isset($tools['required_question']) ) && "1" == $tools['required_question'])); ?> class="qzorg-required-question" />
                                                </div>
                                                <div class="qzorg-right-part-inner">
                                                    <label class="field-label"><?php esc_html_e('Check if display answer horizontally', 'quiz-organizer'); ?></label>
                                                    <input type="checkbox" name="display_flex" value="1" <?php checked( true, (( isset($tools['display_flex']) ) && "1" == $tools['display_flex'])); ?> class="qzorg-display-flex" />
                                                </div>
                                                <div class="qzorg-right-part-inner">
                                                    <label class="field-label"><?php esc_html_e('Right answer info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('Required to enable the Display instant answer option, This input will override default message from "Text" tab.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                                    <input name="right_info" type="text" value="<?php echo isset($tools['right_info_qzorgmessage']) ? esc_attr( $tools['right_info_qzorgmessage'] ) : ""; ?>" class="qzorg-right-info" placeholder="<?php esc_attr_e('Type right answer info', 'quiz-organizer'); ?>">
                                                </div>
                                                <div class="qzorg-right-part-inner">
                                                    <label class="field-label"><?php esc_html_e('Wrong answer info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('Required to enable the Display instant answer option, This input will override default message from "Text" tab.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                                    <input name="wrong_info" type="text" value="<?php echo isset($tools['wrong_info_qzorgmessage']) ? esc_attr( $tools['wrong_info_qzorgmessage'] ) : ""; ?>" class="qzorg-wrong-info" placeholder="<?php esc_attr_e('Type wrong answer info', 'quiz-organizer'); ?>">
                                                </div>
                                                <div class="qzorg-right-part-inner">
                                                    <label class="field-label"><?php esc_html_e('Question Additional Info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('This description will show below the question on quiz page.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                                    <input name="extra_info" type="text" value="<?php echo isset($tools['extra_info_qzorgmessage']) ? esc_attr( $tools['extra_info_qzorgmessage'] ) : ""; ?>" class="qzorg-extra-info" placeholder="<?php esc_attr_e('Additional Question info', 'quiz-organizer'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qzorg-save-question-wrapper">
                                            <div class="qzorg-save-question-info">
                                                <span class="qzorg-save-question-text"></span>
                                            </div>
                                            <div class="qzorg-save-question">
                                                <button type="submit" class="qzorg-save-single-question qzorg-global-submit"><?php echo esc_html(__( 'Save Question', 'quiz-organizer' )); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    ?>
                </div>
                <div class="qzorg-page-settings qzorg-page-settings-bottom">
                    <div>
                        <button class="qzorg-primary-bg qzorg-new-question qzorg-global-submit" ><?php esc_html_e('New Question', 'quiz-organizer'); ?></button>
                        <button class="qzorg-primary-bg qzorg-existing-question qzorg-global-submit" ><?php esc_html_e('Existing Question', 'quiz-organizer'); ?></button>
                    </div>
                </div>
                </div>
                <?php  } 
            }
                }else {
                    ?>
                    <div class="qzorg-page" data-page="0">
                        <div class="qzorg-page-settings qzorg-page-settings-top">
                            <strong><?php
                                /* translators: %d is the page number */
                                printf( esc_html__( 'Page No: %d', 'quiz-organizer' ), esc_attr( 1 ) ); ?>
                            </strong>
                        </div>
                        <div class="qzorg-accordion"  data-page="0">
                        </div>
                        <div class="qzorg-page-settings qzorg-page-settings-bottom">
                            <div>
                                <button class="qzorg-primary-bg qzorg-new-question qzorg-global-submit" ><?php esc_html_e('New Question', 'quiz-organizer'); ?></button>
                                <button class="qzorg-primary-bg qzorg-existing-question qzorg-global-submit" ><?php esc_html_e('Existing Question', 'quiz-organizer'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div id="no-results-message" style="display: none;">
                    <?php echo esc_html__('No results found.', 'quiz-organizer'); ?>
                </div>
            </div>
        </form>
        <?php
    }

    public function imageurl( $image_id = "" ) {
        if ( $image_id ) {
            $url = wp_get_attachment_image_src($image_id, 'thumbnail')[0] ;
        }else {
            $url = QZORG_IMAGE_URL.'/upload-img.png' ;
        }
        return $url;
    }
    /**
     * @since 1.0.0
     * @return : Retuens all categories
     */

    public function categories() {
        global $wpdb;
        $table = $wpdb->prefix . "qzorg_categories";
        $q_results_obj = $wpdb->get_results( "SELECT id, category_name, category_description FROM {$wpdb->prefix}qzorg_categories" );
        if ( $q_results_obj ) {
            return $q_results_obj;
        }
    }

    /**
     * @version 1.0.0
     * Display settings tabs
     */

    public function settings( $quiz_tabs, $quiz_tools, $quiz_obj ) {
        $shortcode = '[quiz-organizer id='.$this->quiz_id.']';
        ?>
        <div class="qzorg-container">
            
            <form  method="POST" class="qzorg-modify-form accordion-body-form quiz-update-setting-form" id="quiz-update-setting-form">
                <?php wp_nonce_field( 'qzorg_update_quiz','qzorg_update_quiz_nonce' ); ?>
                <div class="qzorg-tabs">
                    <a href="#" class="qzorg-tab-button active " data-tab="general"><?php esc_html_e('General', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="quizpage"><?php esc_html_e('Quiz Page', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="styles"><?php esc_html_e('Styles', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="emailconfiguration"><?php esc_html_e('Email Configuration', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="resultpage"><?php esc_html_e('Result Page', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="advance"><?php esc_html_e('Advance', 'quiz-organizer'); ?></a>
                    <a href="#" class="qzorg-tab-button " data-tab="othertext"><?php esc_html_e('Text', 'quiz-organizer'); ?></a>
                </div>
                <div class="qzorg-container-heading">
                    <div><h3><?php echo wp_kses_post($quiz_obj->quiz_name); ?></h3></div>
                    <div>
                        <div class="qzorg-col-3">
                            <div class="qzorg-wrapper-modify qzorg-wrapper-modify-actions"  style="margin-top: 0;">
                                <div class="qzorg-submit-button">
                                    <button class="qzorg-primary-bg qzorg-update-quiz qzorg-global-submit" type="submit"><?php esc_html_e('Update Settings', 'quiz-organizer'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="qzorg-tab-content-wrapper">
                    <?php $c = 0;
                    foreach ( $quiz_tabs as $tab ) { ?>
                        <div class="<?php echo esc_attr($tab); ?> qzorg-each-tab-content <?php echo 0 == $c ? "active" : ""; ?>" id="<?php echo esc_attr($tab); ?>">
                            <div class="qzorg-main-wrapper">
                                <div class="qzorg-wrapper-modify">
                                    <?php qzorg_register_setting_section(Qzorg_Defaults::defaults(), $tab, $quiz_tools); ?>
                                </div>
                            </div>
                        </div>
                    <?php $c++; } ?>
                </div>
                <div class="qzorg-wrapper-modify qzorg-wrapper-modify-actions">
                    <div class="qzorg-submit-button" style="float: right;">
                        <button class="qzorg-primary-bg qzorg-update-quiz qzorg-global-submit" type="submit"><?php esc_html_e('Update Settings', 'quiz-organizer'); ?></button>
                    </div>
                </div>
                <input type="hidden" name="quiz_id" value="<?php echo esc_attr(intval($this->quiz_id)); ?>" />
            </form>
        </div>
        <?php
    }

    /**
     * @version 1.0.0
     * Question template
     */
    
    public function template() {
        ?>
        <div class="qzorg-bpopup-wrapper" id="qzorg_questions_popup" style="display: none;">
            <div class="qzorg-modal-content" style="min-height: 500px;">
                <div class="qzorg-modal-top">
                    <div class="qzorg-modal-top-inside">
                        <h3 class="qzorg-q-modal-top-text"><?php esc_html_e( 'Import Questions', 'quiz-organizer' ); ?></h3><span class="qzorg-q-modal-response"></span>
                    </div>
                    <div class="qzorg-modal-top-close">
                        <span>X</span>
                    </div>
                </div>
                <div class="qzorg-modal-inner">
                    <div class="qzorg-quiz-filters">
                        <div>
                        <div class="qzorg-default-loader" style="display: none;">
                            <span class="spinner is-active"></span>
                        </div>
                        </div>                        
                        <div class="qzorg-quiz-filters-inner">
                            <div class="filter-drop">
                                <select name="qzorg_qpp" id="qzorg_qpp" class="qzorg-global-select">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                            <div class="qzorg-filter-input">
                                <input type="hidden" class="qzorg-question-page" />
                                <input class="qzorg-global-input qzorg-popup-filter-question" placeholder="<?php echo esc_attr('Filter questions') ?>" type="text" name="question_name" />
                            </div>
                        </div>
                    </div>
                    <table class="wp-list-table widefat striped qzorg-questions-table ">
                        <thead>
                            <tr>
                                <th width="80%" ><?php esc_html_e( 'Question Title', 'quiz-organizer' ); ?></th>
                                <th width="20%" ><?php esc_html_e( 'Action', 'quiz-organizer' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="the-list" class="questions-the-list"></tbody>
                    </table>
                </div>
                <div class="qzorg-modal-bottom">
                    <div class="qzorg-update-loader" style="display: none;">
                        <span class="spinner is-active"></span>
                    </div>
                    <!-- <button type="submit" class="qzorg-button update-category-btn qzorg-global-submit" value="Search Quizzes"><?php esc_html_e( 'Update', 'quiz-organizer' ); ?></button> -->
                </div>
            </div>
        </div>
        <script type="text/html" id="tmpl-qn">
            <% _.each(questions, function(data) { %>
                <tr>
                    <td class="row-title"><%=  data.question_title  %><br><span> >> <%=  data.quiz_id  %></span></td>
                    <td ><button class="button qzorg-question-add" data-id="<%=  data.question_id  %>" ><?php esc_html_e( 'Add', 'quiz-organizer' ); ?></button></td>
                </tr>
            <% }); %>
        </script>
        <script type="text/html" id="tmpl-new-page">
            <div class="qzorg-page" data-page="<%= id %>">
                <div class="qzorg-page-settings qzorg-page-settings-top">
                    <strong><span class="qzorg-drag-page-icon-inner ui-sortable-handle">☰</span><?php esc_html_e('Page No ', 'quiz-organizer'); ?><%= id %></strong>
                    <button class="qzorg-remove-page qzorg-global-submit"><?php esc_html_e('Remove Page', 'quiz-organizer'); ?></button>
                </div>
                <div class="qzorg-accordion" data-page="<%= id %>">
                </div>
                <div class="qzorg-page-settings qzorg-page-settings-bottom">  
                    <div>
                        <button class="qzorg-primary-bg qzorg-new-question qzorg-global-submit" ><?php esc_html_e('New Question', 'quiz-organizer'); ?></button>
                        <button class="qzorg-primary-bg qzorg-existing-question qzorg-global-submit" ><?php esc_html_e('Existing Question', 'quiz-organizer'); ?></button>
                    </div>
                    <button class="qzorg-primary-bg qzorg-new-page qzorg-global-submit">New Page</button>
                </div>
            </div>
        </script>
        <script type="text/html" id="tmpl-qzorg-new-question">
            <div class="qzorg-accordion-parent" data-id="<%= id %>">
                <div class="qzorg-accordion-header">
                    <div class="qzorg-question-text">
                        <span class="question-title-span"><%= ph %></span>
                    </div>
                    <div class="qzorg-settings">
                        <button class="accordion-arrow"><span title="<?php esc_attr_e('Edit Question', 'quiz-organizer'); ?>" class="dashicons dashicons-admin-settings"></span></button>
                        <button class="qzorg-duplicate-question"><span title="<?php esc_attr_e('Duplicate question', 'quiz-organizer'); ?>" class="dashicons dashicons-admin-page "></span></button>
                        <button class="qzorg-question-delete"><span title="<?php esc_attr_e('Delete Question', 'quiz-organizer'); ?>" class="dashicons dashicons-trash"></span></button>
                    </div>
                </div>
                <div class="qzorg-accordion-content" data-id="<%= id %>">
                    <div class="qzorg-section qzorg-question-title"><input type="text" name="question_title" placeholder="<?php esc_attr_e('Your Question', 'quiz-organizer'); ?>" value="<%= qtl %>" class="regular-text qzorg-question-input question_title"></div>
                    <div class="qzorg-section qzorg-selectable-field">
                        <div class="qzorg-question-categories">
                            <label for="question_type<%= id %>"><?php esc_html_e('Question Type', 'quiz-organizer'); ?></label>
                            <select id="question_type<%= id %>" name="question_type" class="qzorg-question-type qzorg-select-box">
                                <option value=""><?php esc_html_e( 'Question Type', 'quiz-organizer' ); ?></option>
                                <% _.each(qt, function(q, i) { %>
                                    <% if (typeof sqt !== 'undefined' && sqt == i) { %>
                                        <option value="<%= i %>" selected><%= q %></option>
                                    <% } else { %>
                                        <option value="<%= i %>"><%= q %></option>
                                    <% } %>
                                <% }); %>
                            </select>
                        </div>
                        <div class="qzorg-category-list">
                            <label class="qzorg-category-label"><?php esc_html_e('Categories', 'quiz-organizer'); ?></label>
                            <select name="category_type" class="question_category qzorg-select-box">
                                <option value=""><?php esc_html_e( 'Select Category', 'quiz-organizer' ); ?></option>
                                <% _.each(cs, function(c, d) { %>
                                    <% if (typeof qtls.categories !== 'undefined' && qtls.categories.includes(c.id)) { %>
                                        <option value="<%= c.id %>" selected><%= c.category_name %></option>
                                    <% } else { %>
                                        <option value="<%= c.id %>"><%= c.category_name %></option>
                                    <% } %>
                                <% }); %>
                            </select>
                        </div>
                    </div>
                    <div class="qzorg-section qzorg-question-answers">
                        <div class="qzorg-question-tabs">
                            <% if (typeof qa !== 'undefined' && typeof qa === 'object') { %>
                                <div class="answer_list">
                                    <% _.each(qa, function(x, y) { %>
                                        <div class="answer-fields">
                                            <div class="qzorg-col que-col">
                                                <div class="qzorg-ans-ans">
                                                    <input name="answer" type="text" class="answer-input" value="<%= x.answer %>" placeholder="<?php esc_attr_e('Your Answer', 'quiz-organizer'); ?>">
                                                </div>
                                                <div class="qzorg-ans-point">
                                                    <input name="answer_point" type="number" class="answer-point" value="<%= x.points %>" placeholder="<?php esc_attr_e('Point(s)', 'quiz-organizer'); ?>">
                                                </div>
                                            </div>
                                            <div class="qzorg-col settings-col">
                                                <div class="qzorg-ans-checkbox">
                                                    <input name="is_correct" type="checkbox" class="answer-correct" <% if (1 == x.is_correct) { %>checked<% } %> value="1">
                                                </div>
                                                <div class="qzorg-ans-remove">
                                                    <span class="dashicons dashicons-trash qzorg-remove-answer"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <% }); %>
                                </div>
                            <% } else { %>
                                <div class="answer_list">
                                    <% for (var i = 0; i < qa; i++) { %>
                                        <div class="answer-fields">
                                            <div class="qzorg-col que-col">
                                                <div class="qzorg-ans-ans">
                                                    <input name="answer" type="text" class="answer-input" value="" placeholder="<?php esc_attr_e('Your Answer', 'quiz-organizer'); ?>">
                                                </div>
                                                <div class="qzorg-ans-point">
                                                    <input name="answer_point" type="number" class="answer-point" value="" placeholder="<?php esc_attr_e('Point(s)', 'quiz-organizer'); ?>">
                                                </div>
                                            </div>
                                            <div class="qzorg-col settings-col">
                                                <div class="qzorg-ans-checkbox">
                                                    <input name="is_correct" type="checkbox" class="answer-correct" value="1">
                                                </div>
                                                <div class="qzorg-ans-remove">
                                                    <span class="dashicons dashicons-trash qzorg-remove-answer"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <% } %>
                                </div>
                            <% } %>
                        </div>
                        <div class="qzorg-new-question-wrapper">
                            <div class="new_answer">
                                <button class="new-answer-button button" ><?php esc_html_e('(+) Add New Answer', 'quiz-organizer'); ?></button>
                            </div>
                        </div>
                        <div class="other-setting-wrapper" >
                            <div class="image-upload-wrapper qzorg-left-part" >
                                <input type="hidden" class="qzorg-question-image" name="question_image" value="<%= qii %>">
                                <div class="qzorg-upload-div">
                                    <img class="qzorg-image-preview" src="<%= qiu %>" alt="<?php echo esc_attr(__('Select Image', 'quiz-organizer')); ?>">
                                    <div class=""><button class="qzorg-select-image-button button"><?php echo esc_html(__('Select Image', 'quiz-organizer')); ?></button></div>
                                </div>
                            </div>
                            <div class="qzorg-right-part">
                                <div class="qzorg-right-part-inner">
                                    <label class="field-label"><?php esc_html_e('Is Required ?', 'quiz-organizer'); ?></label>
                                    <input type="checkbox" name="required_question" value="yes" <% if ('1' == qtls.required_question) { %>checked<% } %> class="qzorg-required-question" />
                                </div>
                                <div class="qzorg-right-part-inner">
                                    <label class="field-label"><?php esc_html_e('Check if display answer horizontally', 'quiz-organizer'); ?></label>
                                    <input type="checkbox" name="display_flex" value="yes" <% if ('1' == qtls.display_flex) { %>checked<% } %> class="qzorg-display-flex" />
                                </div>
                                <div class="qzorg-right-part-inner">
                                    <label class="field-label"><?php esc_html_e('Right answer info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('Required to enable the Display instant answer option, This input will override default message from "Text" tab.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                    <input name="right_info" type="text" value="<%= qtls.right_info_qzorgmessage %>" class="qzorg-right-info" placeholder="<?php esc_attr_e('Type right answer info', 'quiz-organizer'); ?>">
                                </div>
                                <div class="qzorg-right-part-inner">
                                    <label class="field-label"><?php esc_html_e('Wrong answer info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('Required to enable the Display instant answer option, This input will override default message from "Text" tab.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                    <input name="wrong_info" type="text" value="<%= qtls.wrong_info_qzorgmessage %>" class="qzorg-wrong-info" placeholder="<?php esc_attr_e('Type wrong answer info', 'quiz-organizer'); ?>">
                                </div>
                                <div class="qzorg-right-part-inner">
                                    <label class="field-label"><?php esc_html_e('Question Additional Info', 'quiz-organizer'); ?><span title="<?php echo esc_attr__('This description will show below the question on quiz page.', 'quiz-organizer' ); ?>" class="dashicons dashicons-info"></span></label>
                                    <input name="extra_info" type="text" value="<%= qtls.extra_info_qzorgmessage %>" class="qzorg-extra-info" placeholder="<?php esc_attr_e('Additional Question info', 'quiz-organizer'); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="qzorg-save-question-wrapper">
                            <div class="qzorg-save-question-info">
                                <span class="qzorg-save-question-text"></span>
                            </div>
                            <div class="qzorg-save-question">
                                <button type="submit" class="qzorg-save-single-question qzorg-global-submit"><?php echo esc_html(__( 'Save Question', 'quiz-organizer' )); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </script>
        <script type="text/html" id="tmpl-qzorg-new-answer">
            <div class="answer-fields">
                <div class="qzorg-col que-col">
                    <div class="qzorg-ans-ans">
                        <input name="answer" type="text" class="answer-input" value="" placeholder="<?php esc_attr_e('Your Answer', 'quiz-organizer'); ?>">
                    </div>
                    <div class="qzorg-ans-point">
                        <input name="answer_point" type="number" class="answer-point" value="" placeholder="<?php esc_attr_e('Point(s)', 'quiz-organizer'); ?>">
                    </div>
                </div>
                <div class="qzorg-col settings-col">
                    <div class="qzorg-ans-checkbox">
                        <input name="is_correct" type="checkbox" class="answer-correct" value="1">
                    </div>
                    <div class="qzorg-ans-remove">
                        <span class="dashicons dashicons-trash qzorg-remove-answer"></span>
                    </div>
                </div>
            </div>
        </script>
        <?php 
    }

}

function qzorg_modify_quiz_page() {

    if ( (isset($_GET['page']) && isset($_GET['quizid'])) && "qzorg_modify_quiz" == $_GET['page'] && "" != $_GET['quizid'] ) {
        $pagenow = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : "";
        $quiz_id = isset($_GET['quizid']) ? sanitize_text_field(wp_unslash($_GET['quizid'])) : 0;
        $pageObj = new Qzorg_ModificationPage($quiz_id, $pagenow);
        $pageObj->qzorg_display_modification_form();
    }

    qzorg_show_notices();
}
?>