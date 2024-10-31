<?php
/**
 * @since 1.0.0
 * @return Dashboard
 */


class Qzorg_Dashboard
{   
    public function __construct( ) {
        if ( ! current_user_can( 'edit_posts' ) ) { return; }
        add_action( 'admin_footer', array( $this, 'template' ) );
    }

    public function qzorg_display_dashboard(){ 
        wp_localize_script( 'qzorg_adminjs', 'WP_API_SETTINGS', array(
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ) );
        wp_localize_script( 'qzorg_adminjs', 'qa_quiz_text', array(
            'empty_quizzes' => __('No Quizzes Found !', 'quiz-organizer'),
            'new_quiz'      => __('Create', 'quiz-organizer'),
            'edit_quiz'     => __('Update', 'quiz-organizer'),
            'quiz_btn'      => __('Quiz', 'quiz-organizer'),
            'confirmation'  => __('You are about to permeanently delete this quiz from your site. This quiz will be remove from all related questions are you sure ?', 'quiz-organizer'),
            'shortcode_cpy' => __('shortcode cpoied!', 'quiz-organizer'),
            'remove_nonce'  => wp_create_nonce( 'remove_quiz_nonce' ),
            'error'         => __('Error nonce validation faild !', 'quiz-organizer'),
        ) );
        ?>
        <form id="quiz">
            <div class="wrap qzorg-custom qzorg-quiz-list">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Manage quizzes', 'quiz-organizer' ); ?></h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=qzorg_create_quiz')); ?>" class="page-title-action"><?php echo esc_html__('Add New', 'quiz-organizer'); ?></a>
                <hr class="wp-header-end">
                <?php self::top_actions(); ?>
                <?php self::quiz_table(); ?>
                <?php //Qzorg_Defaults::footer(); ?>
            </div>
        </form>
    <?php 
    }

    protected static function top_actions(){
        ?>
        <div class="qzorg-top-actions">
            <div class="qzorg-quiz-filters">
                <div class="qzorg-delete-multiple-quiz">
                    <button disabled title="<?php echo esc_attr(__( 'Select at-least one to enable', 'quiz-organizer' )); ?>" class="button qzorg-delete-multiple-quiz-btn qzorg-global-submit"><?php esc_html_e( 'Delete Multiple', 'quiz-organizer' ); ?></button>
                    <span class="spinner is-active qzorg-delete-spinner" style="display: none;"></span>
                </div>
                <div class="qzorg-quiz-filters-inner">
                    <div class="filter-drop">
                        <select name="qzorg_pp" id="qzorg_pp" class="qzorg-global-select">
                            <option value="0"><?php echo esc_html__('All', 'quiz-organizer'); ?></option>
                            <option value="100">100</option>
                            <option value="500">500</option>
                        </select>
                    </div>
                    <div class="qzorg-filter-input">
                        <input class="qzorg-global-input" placeholder="<?php echo esc_attr(__('Filter by quiz name', 'quiz-organizer' )) ?>" type="text" name="quiz_name" id="quiz_name" />
                    </div>
                    <div class="qzorg-filter-input">
                        <label for="qzorg_start_date" class="qzorg-date-label"><?php echo esc_html__('Start Date', 'quiz-organizer'); ?></label><br>
                        <input type="date" name="qzorg_start_date" class="qzorg-global-date" id="qzorg_start_date" />
                    </div>
                    <div class="qzorg-filter-input">
                        <label for="qzorg_end_date" class="qzorg-date-label"><?php echo esc_html__('End Date', 'quiz-organizer'); ?></label><br>
                        <input type="date" name="qzorg_end_date" class="qzorg-global-date" id="qzorg_end_date" />
                    </div>
                    <div class="qzorg-filter-input">
                        <input type="hidden" name="page" id="page" />
                    </div>
                    <div class="quiz-filter-submit">
                        <input type="submit" class="qzorg-filter-quiz-btn qzorg-global-submit" name="qzorg_filter_quiz_btn" />
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    protected static function quiz_table(){
        ?>
        <table class="wp-list-table widefat striped fixed qzorg-quizzes-table table-view-list posts">
            <thead>
                <tr>
                    <th width="10%" id="cb" class="manage-column column-cb check-column">
                        <label class="label-covers-full-cell" for="cb-select-all-1"></label>
                        <input id="cb-select-all-1" type="checkbox" class="qzorg-check-all-quiz qzorg-global-checkbox">
                    </th>
                    <th width="38%" class="manage-column qzorg-quiz-name"><?php esc_html_e( 'Title', 'quiz-organizer' ); ?></th>
                    <th width="17%" class="manage-column column-Description"><?php esc_html_e( 'Shortcode', 'quiz-organizer' ); ?></th>
                    <th width="8%" class="visits-column"><?php esc_html_e( 'Visit(s)', 'quiz-organizer' ); ?></th>
                    <th width="12%" class="attend-column"><?php esc_html_e( 'Submission(s)', 'quiz-organizer' ); ?></th>
                    <th width="18%" class="created_at-column"><?php esc_html_e( 'Created Date', 'quiz-organizer' ); ?></th>
                    <th width="7%" class="id-column"><?php esc_html_e( 'ID', 'quiz-organizer' ); ?></th>
                </tr>
            </thead>
            <tbody id="the-list" class="quizzes-the-list"></tbody>
        </table>
        <div class="qzorg-default-loader" style="display: none;">
            <span class="spinner is-active"></span>
        </div>
        <?php 
    }

    /**
     * To use Javascript templages by WordPress underscore.js
     * 
     * @since 1.0.0
     * @uses admin_footer hook to load in admin footer area
     */
    public function template() {
        ?>
        <script type="text/html" id="tmpl-quizzes-list">
            <% _.each(quizzes, function(data) { %>
                <tr class="qzorg-each-tr" data-id="<%= data.quiz_id %>">
                    <th scope="row" class="check-column"><label class="label-covers-full-cell" for="cb-select-<%= data.quiz_id %>"></label><input id="cb-select-<%= data.quiz_id %>" data-id="<%= data.quiz_id %>" type="checkbox" class="qzorg-quiz-checkbox qzorg-global-checkbox" ></th>
                    <td class="quiz_name_<%= data.quiz_id %> qzorg-quiz-name" >
                        <div class="qzorg-row-action">
                            <strong class="qzorg-row-title"><a href="<%= data.quiz_url %>"><%=  data.quiz_name  %></a></strong>
                            <div class="qzorg-row-sublinks">
                                <span><a href="<%= data.quiz_url %>"><?php esc_html_e( 'Edit', 'quiz-organizer' ); ?></a></span>
                                <span><a href="<%= data.result_url %>"><?php esc_html_e( 'View Results', 'quiz-organizer' ); ?></a></span>
                                <span><a class="qzorg-delete-quiz" href="javascript:void(0);"><?php esc_html_e( 'Delete', 'quiz-organizer' ); ?></a></span>
                            </div>
                        </div>
                    </td>
                    <td class="shortcode_<%= data.quiz_id %> qzorg-quiz-shortcode" ><span class="qzorg-quiz-shortcode-span"><%=  data.shortcode  %></span></td>
                    <td class="quiz_edit"><span class="qzorg-quiz-edit"><%=  data.quiz_visits  %></span></td>
                    <td class="quiz_attend"><span class="qzorg-quiz-attend"><%=  data.quiz_attend  %></span></td>
                    <td class="quiz_created_at"><span class="qzorg-quiz-created_at"><%=  data.author  %></span></td>
                    <td class="quiz_id"><span class="qzorg-quiz-quiz_id"><%=  data.quiz_id  %></span></td>
                </tr>
            <% }); %>
        </script>
        <?php 
    }

}

function qzorg_dashboard_page() {
    if ( (isset($_GET['page']) && "quiz_organizer" == $_GET['page'] ) ) {
        $dashboardObj = new Qzorg_Dashboard();
        $dashboardObj->qzorg_display_dashboard();
        // Qzorg_Modification::default();
    }
}
