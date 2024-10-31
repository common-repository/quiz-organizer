<?php

/**
 * @since 1.0.0
 * Create Quiz From Here
 */
class Qzorg_CreateQuiz
{
    public function qzorg_create_quiz_page() {
        $translation_array = array(
            'quiz_name' => __('Quiz name is required.', 'quiz-organizer'),
        );
        wp_localize_script('qzorg_adminquizjs', 'qzorgCreate', $translation_array);
        $qzorg_quiz_form = Qzorg_Defaults::defaults();
        $quiz_tabs = apply_filters('qzorg_tags_custom_filter', $qzorg_quiz_form);
        ?>
        <div class="wrap qzorg-custom">
            <h1 class="wp-heading-inline" style="display: none;"><?php esc_html_e('Create New Quiz', 'quiz-organizer'); ?></h1>
            <hr class="wp-header-end">
            <div class="qzorg-container">
                <form  method="POST" class="qzorg-modify-form accordion-body-form quiz-create-setting-form" id="quiz-create-setting-form">
                    <?php wp_nonce_field('qzorg_register_quiz', 'qzorg_register_quiz_nonce'); ?>
                    <div class="qzorg-tab-menu-wrapper">
                        <div class="qzorg-title-wrapper qzorg-create-quiz-wrapper">
                            <h1 class="wp-heading-inline"><?php esc_html_e('Create New Quiz', 'quiz-organizer'); ?></h1>
                            <hr class="wp-header-end">
                            <?php Qzorg_Defaults::doclink(Qzorg_Defaults::documentation('core_plugin', 'qzorg_create_quiz', QZORG_VERSION, 'create-quiz')); ?>
                        </div>
                        <div class="qzorg-tabs">
                            <a href="#" class="qzorg-tab-button active " data-tab="general"><?php esc_html_e('General', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="quizpage"><?php esc_html_e('Quiz Page', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="styles"><?php esc_html_e('Styles', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="emailconfiguration"><?php esc_html_e('Email Configuration', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="resultpage"><?php esc_html_e('Result Page', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="advance"><?php esc_html_e('Advance', 'quiz-organizer'); ?></a>
                            <a href="#" class="qzorg-tab-button " data-tab="othertext"><?php esc_html_e('Text', 'quiz-organizer'); ?></a>
                        </div>
                        <div class="qzorg-tab-content-wrapper">
                            <?php $c = 0;
                            foreach ( $quiz_tabs as $tab ) { ?>
                                <div class="<?php echo esc_attr($tab); ?> qzorg-each-tab-content <?php echo 0 == $c ? "active" : ""; ?>" id="<?php echo esc_attr($tab); ?>">
                                    <div class="qzorg-main-wrapper">
                                        <div class="qzorg-wrapper-modify">
                                            <?php qzorg_register_setting_section($qzorg_quiz_form, $tab); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php $c++;
                            } ?>
                        </div>
                    </div>
                    <div class="qzorg-wrapper-modify qzorg-wrapper-modify-actions">
                        <div class="qzorg-submit-button">
                            <button class="button-primary qzorg-create-quiz qzorg-global-submit" type="submit"><?php esc_html_e('Create Quiz', 'quiz-organizer'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php }
}

function qzorg_create_quiz_page() {

    if ( (isset($_GET['page'])) && "qzorg_create_quiz" == $_GET['page'] ) {
        $quizPageObj = new Qzorg_CreateQuiz();
        $quizPageObj->qzorg_create_quiz_page();
    }

    qzorg_show_notices();
}

