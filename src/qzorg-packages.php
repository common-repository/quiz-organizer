<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * @since 1.0.0
 * QZORG_Quiz_Organizer
 */
class QZORG_Quiz_Organizer {

    /**
	 * @since 1.0.0
	 * Current Details
	 * @var string
	 */
	public $globalSettings = array();
	/**
	 * @since 1.0.0
	 * [ Global Public Required Objects ]
	 * @uses As object
	 */
	public $notificationGuideObj, $modificationObj, $helperObj, $questionObj, $resultsObj, $defaultsObj;

	/**
	 * @since 1.0.0
	 * Initialize dependencies and hooks form constructor
	 */

	function __construct() {
		$this->init();
		$this->after_init();
		$this->plugins_loaded();

		add_filter('plugin_action_links_' . QZORG_PLUGIN_BASENAME, 'qzorg_add_action_link');
	}

	/**
	 * @since 1.0.0
	 * @uses init()
	 */

	private function init() {

		qzorg_inc_sets('class-qzorg-beginning.php');
		qzorg_inc_class('class-qzorg-phtml.php');

		if ( QZORG_IS_ADMIN ) {
			
			if ( ! class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}
			
			qzorg_inc_page( 'class-qzorg-pdb.php' );
			qzorg_inc_page( 'class-qzorg-quizzes.php' );
			qzorg_inc_page( 'admin-dashboard.php' );
			qzorg_inc_page( 'admin-create-quiz.php' );
			qzorg_inc_page( 'admin-modify-quiz.php' );
			qzorg_inc_page( 'admin-categories.php' );
			qzorg_inc_page( 'admin-variables.php' );
			qzorg_inc_page( 'admin-global-settings.php' );
			qzorg_inc_page( 'admin-quiz-results.php' );
		}

		qzorg_inc_class('class-qzorg-plug-shortcode.php');
		qzorg_inc_class('class-qzorg-helper.php');
		qzorg_inc_class('class-qzorg-defaults.php');
		qzorg_inc_class('class-qzorg-questions.php');
		qzorg_inc_class('class-qzorg-results.php');
		qzorg_inc_class('class-qzorg-notification-guide.php');
		qzorg_inc_class('class-qzorg-category.php');
		qzorg_inc_class('class-qzorg-modification.php');
		$this->helperObj = new Qzorg_Helper();
		$this->defaultsObj = new Qzorg_Defaults();
		$this->questionObj = new Qzorg_Questions();
		$this->resultsObj = new Qzorg_Results();
		$this->modificationObj = new Qzorg_Modification();
		qzorg_inc_sets('defaults.php');
		qzorg_inc_sets('api.php');

		$this->globalSettings = wp_parse_args( get_option('qzorg_global_options'), array(
            'delete_data_on_plugin_deletion' => '0',
            'stop_storing_ip_address'        => '0',
            'default_question_type'          => 'drop_down',
            'default_answer_fields'          => '1',
        ) );
	}

	/**
	 * @since 1.0.0
	 * @uses QZORG_Quiz_Organizer::after_init() Adds actions to hooks and filters
	 */

    private function after_init() {
        add_action( 'admin_menu', array( $this, 'qzorg_generate_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'qzorg_admin_styles_scripts' ), 10 );
    }
    
	/**
	 * Structure For Admin Menu [ To show menu and pages in plugin ]
	 * @since 1.0.0
	 */
	public function qzorg_generate_admin_menu() {
		if ( function_exists( 'add_menu_page' ) ) {
            add_menu_page( __( 'Quiz Organizer', 'quiz-organizer' ), __( 'Quiz Organizer', 'quiz-organizer' ), 'edit_posts', QZORG_MENU, 'qzorg_dashboard_page', 'dashicons-feedback', $this->set_menu_position() );
			add_submenu_page( QZORG_MENU, __( 'Quizzes', 'quiz-organizer' ), __( 'Quizzes', 'quiz-organizer' ), 'edit_posts', QZORG_MENU, 'qzorg_dashboard_page', 0 );
            add_submenu_page( QZORG_MENU, __( 'Categories', 'quiz-organizer' ), __( 'Categories', 'quiz-organizer' ), 'edit_posts', 'qzorg_categories', 'qzorg_categories_page', 2 );
            add_submenu_page( QZORG_MENU, __( 'Extra Variables', 'quiz-organizer' ), __( 'Extra Variables', 'quiz-organizer' ), 'edit_posts', 'qzorg_variables', 'qzorg_variables_page', 7 );
            add_submenu_page( QZORG_MENU, __( 'Results', 'quiz-organizer' ), __( 'Results', 'quiz-organizer' ), 'edit_posts', 'qzorg_results', 'qzorg_result_page', 8 );
			add_submenu_page( QZORG_MENU, __('Global Settings', 'quiz-organizer'), __('Global Settings', 'quiz-organizer'), 'manage_options', 'qzorg_global_settings', 'qzorg_global_settings_page' );
			add_submenu_page( null, __( 'create Quiz', 'quiz-organizer' ), __( 'create Quiz', 'quiz-organizer' ), 'edit_posts', 'qzorg_create_quiz', 'qzorg_create_quiz_page' );
			add_submenu_page( null, __( 'Modify Quiz', 'quiz-organizer' ), __( 'Modify Quiz', 'quiz-organizer' ), 'edit_posts', 'qzorg_modify_quiz', 'qzorg_modify_quiz_page' );
		}
	}
	
	/**
	 * @since 1.0.0
	 */

	public function set_menu_position() {
		global $menu;
		$current_position = 0;
		if ( is_array($menu) ) {
			foreach ( $menu as $item ) {
				$position = $item[0];
				if ( $position > $current_position ) {
					$current_position = $position;
				}
			}
		}
		$new_position = (int)$current_position + 25;
		return (int)$new_position;
	}

	public function qzorg_admin_styles_scripts( $atts ) {
		$gmdate = gmdate('h-m-s');
		// quiz list and dashboard.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_media();
		// wp_enqueue_editor();
		wp_enqueue_style( 'qzorg_admincss', QZORG_CSS_URL.'/qzorg-admin.css', array(), $gmdate );
		wp_enqueue_style( 'qzorg_adminselect2css', QZORG_CSS_URL.'/select2.min.css', array(), $gmdate );
		wp_enqueue_script( 'qzorg_adminjs', QZORG_JS_URL.'/qzorg-admin.js', array( 'wp-util', 'jquery', 'underscore' ), $gmdate, true );
		wp_enqueue_script( 'qzorg_adminselect2js', QZORG_JS_URL.'/select2.min.js', array( 'jquery' ), $gmdate, true );
		wp_enqueue_script( 'qzorg_bpopupminjs', QZORG_JS_URL.'/jquery.bpopup.min.js', array( 'jquery' ), $gmdate, true );
		wp_enqueue_script( 'qzorg_bpopupjs', QZORG_JS_URL.'/jquery.bpopup.js', array( 'jquery' ), $gmdate, true );
		// Add inline CSS to the custom CSS file
		if ( isset( $_GET['page'] ) && ( 'qzorg_create_quiz' == $_GET['page'] || 'qzorg_modify_quiz' == $_GET['page'] || 'quiz_organizer' == $_GET['page'] || 'qzorg_variables' == $_GET['page'] || 'qzorg_results' == $_GET['page']) ) {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'qzorg_adminquizcss', QZORG_CSS_URL.'/qzorg-admin-quiz.css', array(), $gmdate );
			wp_enqueue_script( 'qzorg_adminquizjs', QZORG_JS_URL.'/qzorg-admin-quiz.js', array( 'qzorg_adminjs' ), $gmdate, true );
			if ( 'quiz_organizer' == $_GET['page'] ) { $this->qzorg_inline_style(); }
		}

		if ( isset( $_GET['page'] ) && 'qzorg_categories' == $_GET['page'] ) {
			wp_enqueue_style( 'qzorg_categoriescss', QZORG_CSS_URL.'/qzorg-categories.css', array(), $gmdate );
			wp_enqueue_script( 'qzorg_categoriesjs', QZORG_JS_URL.'/qzorg-categories.js', array( 'jquery' ), $gmdate, true );
		}

        $localize_array = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        );
        
        wp_localize_script( 'qzorg_adminjs', 'qzorgAjax', $localize_array); 
	}

	private function plugins_loaded(){
		add_action( 'plugins_loaded', 'qzorg_quiz_setting' );
	}

	public function qzorg_inline_style() {
		$additionalcss = "
			#wpbody-content {
				width: initial;
			}
		";
    	wp_add_inline_style('qzorg_adminquizcss', $additionalcss);
	}

}
