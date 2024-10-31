<?php
/**
 * @since 1.0.0
 * @uses USED VARIABLE LIST INTO RESULT PAGE OR EMAIL TEMPLATE
 */
class Qzorg_VariablesPage
{
    public function display_variables(){ ?>
        <h1>
            <?php esc_html_e('Extra variables page', 'quiz-organizer'); ?>
        </h1>
        <div class="wrap qzorg-custom">
            <form id="qzorg-question-form">
                <div class="qzorg-top-actions">
                    <div class="qzorg-quiz-filters">
                        <div class="qzorg-delete-multiple-quiz">
                        </div>
                        <div class="qzorg-quiz-filters-inner">
                            <div class="qzorg-filter-input">
                                <input class="qzorg-filter-variables qzorg-global-input" placeholder="<?php echo esc_attr('Type to search in') ?>" type="text" name="qzorg_filter_variables" id="qzorg_filter_variables" />
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <table class="wp-list-table widefat striped qzorg-variables-table">
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Variable', 'quiz-organizer'); ?></th>
                        <th scope="col"><?php esc_html_e('Scope', 'quiz-organizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>%%QUIZ_RESULTS_HERE%%</td>
                        <td><?php esc_html_e('Displays the results of the quiz', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%QUIZ_TITLE%%</td>
                        <td><?php esc_html_e('Shows the name of the quiz', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%QUIZ_SUBMISSION_DATE%%</td>
                        <td><?php esc_html_e('Displays the date when the quiz was submitted', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%USER_TOTAL_EARN_POINTS%%</td>
                        <td><?php esc_html_e('Shows the total points earned by the user', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%QUIZ_MAXIMUM_POINTS%%</td>
                        <td><?php esc_html_e('Indicates the highest achievable score in the quiz', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%CORRECT_QUESTION_COUNT%%</td>
                        <td><?php esc_html_e('Displays the count of questions answered correctly', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%INCORRECT_QUESTION_COUNT%%</td>
                        <td><?php esc_html_e('Displays the count of questions answered incorrectly', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%UNANSWERED_QUESTION_COUNT%%</td>
                        <td><?php esc_html_e('Indicates the number of questions not answered', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%TOTAL_TAKEN_QUESTION_COUNT%%</td>
                        <td><?php esc_html_e('Shows the count of questions attempted (excluding unanswered)', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%AVERAGE_POINTS_PER_QUESTION%%</td>
                        <td><?php esc_html_e('Presents the average points earned per question', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%DISPLAY_WP_USER_NAME%%</td>
                        <td><?php esc_html_e('Displays the user\'s WordPress profile username. If a guest, it remains blank', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%DISPLAY_WP_USER_EMIAL%%</td>
                        <td><?php esc_html_e('Shows the user\'s WordPress profile email. If a guest, it remains blank', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%QUIZ_CREATED_DATE%%</td>
                        <td><?php esc_html_e('Indicates the date when the quiz was created', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%DISPLAY_WP_ADMIN_EMIAL%%</td>
                        <td><?php esc_html_e('Displays the email of the WordPress admin', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%USER_IP_ADDR%%</td>
                        <td><?php esc_html_e('Shows the IP address of the user.', 'quiz-organizer'); ?></td>
                    </tr>
                    <tr>
                        <td>%%DISPLAY_WP_USER_ROLE%%</td>
                        <td><?php esc_html_e('Indicates the current role of the user (e.g., admin, editor, etc.)', 'quiz-organizer'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php }
}

function qzorg_variables_page() {

    if ( (isset($_GET['page']) && "qzorg_variables" == $_GET['page'] ) ) {
        $quizVariablesObj = new Qzorg_VariablesPage();
        $quizVariablesObj->display_variables();
    }

}