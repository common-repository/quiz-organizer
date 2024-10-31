<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Qzorg_Rest_Controller {

    private $loged_in; //bool
    private $user;
    private $allowed_roles = array( 'administrator' );

    public function __construct( $logged, $cur_user ) {
        $this->loged_in = $logged;
        $this->user = $cur_user;
        $this->namespace     = '/quizorganizer/v1';
        $this->resource_name = 'categories';
        $this->resource_quiz = 'quiz';
        $this->resource_question = 'question';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_categories' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );
        
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'edit_category' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_name . '(?:/(?P<id>\d+))?', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_category' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );
        
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/delete/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_category' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );
        
        register_rest_route( $this->namespace, '/' . $this->resource_quiz . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_all_quiz' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_quiz . '/index', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_list_quiz' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_question . '/index', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_question_quiz' ),
                'permission_callback' => array( $this, 'get_items_check_permissions' ),
            ),
        ) );
        
        
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_check_permissions( $request ) {
        if ( ! array_intersect($this->allowed_roles, $this->user->roles ) ) { 
            return new WP_Error( 'rest_forbidden', esc_html__( 'Permission Error.', 'quiz-organizer' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }

    /**
     * Grabs all the categories.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_categories( WP_REST_Request $request ) {
        $query = $request->get_params();
        $response = Qzorg_Category::index($query, $request);
        return rest_ensure_response( $response );
    }

    /**
     * Grabs single category.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function edit_category( WP_REST_Request $request ) {
        $query = $request->get_param( 'id' );
        $response = Qzorg_Category::edit($query, $request);
        return rest_ensure_response( $response );
    }

    /**
     * Update settings for the category.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function update_category( WP_REST_Request $request ) {
        $query = $request->get_param( 'id' );
        $request = $request->get_params();
        $response = Qzorg_Category::update($query, $request);
        return rest_ensure_response( $response );
    }
    
    /**
     * Remove the category
     *
     * @param WP_REST_Request $request Current request.
     */
    public function delete_category( WP_REST_Request $request ) {
        $query = $request->get_param( 'id' );
        $response = Qzorg_Category::destroy($query, $request);
        return rest_ensure_response( $response );
    }

    /**
     * Grabs all the related questions.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_all_quiz( WP_REST_Request $request ) {
        $query = $request->get_params();
        $response = Qzorg_Questions::index($query, $request);
        return rest_ensure_response( $response );
    }
    
    /**
     * Grabs all the related quizzes.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_list_quiz( WP_REST_Request $request ) {
        $query = $request->get_params();
        $response = Qzorg_Modification::index($query, $request);
        return rest_ensure_response( $response );
    }
    
    /**
     * Grabs all the related quizzes.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_question_quiz( WP_REST_Request $request ) {
        $query = $request->get_params();
        $response = Qzorg_Questions::index($query, $request);
        return rest_ensure_response( $response );
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code() {
        return 401;
    }

}

// Function to register our new routes from the controller.
function qzorg_register_my_rest_routes() {
    $controller = new Qzorg_Rest_Controller(is_user_logged_in(), wp_get_current_user());
    $controller->register_routes();
}

add_action( 'rest_api_init', 'qzorg_register_my_rest_routes' );
