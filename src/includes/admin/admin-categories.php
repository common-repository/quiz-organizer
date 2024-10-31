<?php
/**
 * @since 1.0.0
 * @uses Manage categories 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Qzorg_Categories{

    public function __construct() {
        add_action( 'admin_footer', array( $this, 'qzorg_categories_tmpl' ) );
    }

    function qzorg_categories_page() {
        wp_localize_script( 'qzorg_adminjs', 'WP_API_SETTINGS', array(
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'd'     => 'DELETE',
            'p'     => 'POST',
        ) );
        wp_localize_script( 'qzorg_adminjs', 'qa_cat_text', array(
            'empty_categories' => __('No categories Found !', 'quiz-organizer'),
            'new_category'     => __('Create', 'quiz-organizer'),
            'cat_nonce'        => wp_create_nonce( 'new_cat_nonce' ),
            'edit_category'    => __('Update', 'quiz-organizer'),
            'category_btn'     => __('Category', 'quiz-organizer'),
            'confirmation'     => __('You are about to permeanently delete this category from your site. This category will be remove from all related questions are you sure ?', 'quiz-organizer'),
        ) ); 
        ?>
        <div class="wrap qzorg-custom">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Manage Categories', 'quiz-organizer' ); ?></h1>
            <hr class="wp-header-end">
            <form id="qzorg-category-form">
                <div class="qzorg-top-actions">
                    <div class="qzorg-quiz-filters">
                        <div class="qzorg-delete-multiple-quiz">
                            <div class="category-actions"><button href="javascript:void(0);"class=" button qzorg-cat-new-btn"><?php esc_html_e( 'Add New', 'quiz-organizer' ); ?></button></div>
                        </div>
                        <div class="qzorg-quiz-filters-inner">
                            <div class="qzorg-filter-input">
                                <input class="qzorg-global-input" placeholder="<?php echo esc_attr('Filter by name or description') ?>" type="text" name="category_name" />
                            </div>
                            <div class="quiz-filter-submit">
                                <input type="submit" class="qzorg-filter-cat-btn qzorg-global-submit" name="qzorg_filter_cat_btn" />
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <table class="wp-list-table widefat striped qzorg-categories-table ">
                <thead>
                    <tr>
                        <th width="20%" class="manage-column row-title qzorg-category-name"><?php esc_html_e( 'Name', 'quiz-organizer' ); ?></th>
                        <th width="50%" class="manage-column row-title column-Description"><?php esc_html_e( 'Description', 'quiz-organizer' ); ?></th>
                        <th width="20%" class="edit-column row-title"><?php esc_html_e( 'Edit', 'quiz-organizer' ); ?></th>
                        <th width="10%" class="remove-column row-title"><?php esc_html_e( 'Remove', 'quiz-organizer' ); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list" class="categories-the-list"></tbody>
            </table>
            <div class="qzorg-default-loader" style="display: none;">
                <span class="spinner is-active"></span>
            </div>
        </div>
        <?php 
    }
    /**
     * To use Javascript templages by WordPress underscore.js
     * 
     * @since 1.0.0
     * @uses admin_footer hook to load in admin footer area
     */
    function qzorg_categories_tmpl() {
        ?>
        <div class="qzorg-bpopup-wrapper" id="qzorg_cat_update" style="display: none">
            <div class="qzorg-modal-content">
            <form id="qzorg_edit_category" method="POST">
                <?php wp_nonce_field('qzorg_create_update_category', 'qzorg_create_update_cat_nonce'); ?>
                <div class="qzorg-modal-top">
                    <div class="qzorg-modal-top-inside">
                        <h3 class="qzorg-cat-modal-top-text"><?php esc_html_e( 'Update Category', 'quiz-organizer' ); ?></h3>
                    </div>
                    <div class="qzorg-modal-top-close">
                        <span>X</span>
                    </div>
                </div>
                <div class="qzorg-modal-inner">
                    <input type="hidden" id="cat_id" name="cat_id" value="" type="text" >
                    <input id="category_name" class="qzorg-global-input" name="category_name" placeholder="<?php echo esc_attr('Category Name') ?>" value="" type="text" >
                    <textarea id="category_description" placeholder="<?php echo esc_attr('Category Description') ?>" rows="4" cols="50" name="category_description" class="category_name-description qzorg-global-textarea"></textarea>
                </div>
                <div class="qzorg-modal-bottom">
                    <div class="qzorg-update-loader" style="display: none;">
                        <span class="spinner is-active"></span>
                    </div>
                    <button type="submit" class="qzorg-button update-category-btn qzorg-global-submit" value="Search Quizzes"><?php esc_html_e( 'Update', 'quiz-organizer' ); ?></button>
                </div>
                </form>
            </div>
        </div>
        <script type="text/html" id="tmpl-cl">
            <% _.each(categories, function(data) { %>
                <tr>
                    <td class="category_name_<%= data.id %> qzorg-category-name" ><%=  data.category_name  %></td>
                    <td class="category_description_<%= data.id %> qzorg-category-description" ><%=  data.category_description  %></td>
                    <td class="category_edit row-title"><span data-id="<%=  data.id  %>" class="qzorg-category-edit"><?php esc_html_e( 'Quick Edit', 'quiz-organizer' ); ?></span></td>
                    <td class="category_remove row-title"><span data-id="<%=  data.id  %>" class="qzorg-category-remove"><?php esc_html_e( 'Remove', 'quiz-organizer' ); ?></span></td>
                </tr>
            <% }); %>
        </script>
        <script type="text/html" id="tmpl-cn">
            <tr>
                <td class="category_name_<%= id %> qzorg-category-name" ><%=  category_name  %></td>
                <td class="category_description_<%= id %> qzorg-category-description" ><%=  category_description  %></td>
                <td class="category_edit row-title"><span data-id="<%=  id  %>" class="qzorg-category-edit"><?php esc_html_e( 'Quick Edit', 'quiz-organizer' ); ?></span></td>
                <td class="category_remove row-title"><span data-id="<%=  id  %>" class="qzorg-category-remove"><?php esc_html_e( 'Remove', 'quiz-organizer' ); ?></span></td>
            </tr>
        </script>
        <?php 
    }

}

function qzorg_categories_page() {

    if ( (isset($_GET['page'])) && "qzorg_categories" == $_GET['page'] ) {
        $categoryObj = new Qzorg_Categories();
        $categoryObj->qzorg_categories_page();
    }

    qzorg_show_notices();
}