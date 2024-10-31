<?php
/**
 * @since 1.0.0
 * @uses GLOBAL SETTINGS PAGE
 */

class Qzorg_Global_Settings
{
    public function display_settings(){ ?> 
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('qzorg_global_group');
                do_settings_sections('qzorg_global_options');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    
    static function qzorg_get_default_fields( $options ) {
        return wp_parse_args( $options, array(
            'delete_data_on_plugin_deletion' => '0',
            'stop_storing_ip_address'        => '0',
            'default_question_type'          => 'drop_down',
            'default_answer_fields'          => '1',
        ) );
    }
}

function qzorg_global_setting_initialize() {
    add_settings_section(
        'qzorg_global_setting_init',
        __('Global Settings', 'quiz-organizer'),
        'qzorg_global_setting_init_callback',
        'qzorg_global_options'
    );
    
    add_settings_field(
        'delete_data_on_plugin_deletion',
        __('Delete All data of Quiz Organizer on deletion?', 'quiz-organizer'),
        'qzorg_delete_data_on_plugin_deletion_callback',
        'qzorg_global_options',
        'qzorg_global_setting_init'
    );
    
    add_settings_field(
        'stop_storing_ip_address',
        __('Stop storing ip address?', 'quiz-organizer'),
        'qzorg_stop_storing_ip_address_callback',
        'qzorg_global_options',
        'qzorg_global_setting_init'
    );
    
    add_settings_field(
        'default_question_type',
        __('Default question type', 'quiz-organizer'),
        'qzorg_default_question_type_callback',
        'qzorg_global_options',
        'qzorg_global_setting_init'
    );
    
    add_settings_field(
        'default_answer_fields',
        __('Default number of answers', 'quiz-organizer'),
        'qzorg_default_answer_fields_callback',
        'qzorg_global_options',
        'qzorg_global_setting_init'
    );
    
    register_setting(
        'qzorg_global_group', 
        'qzorg_global_options'
    );
}
add_action('admin_init', 'qzorg_global_setting_initialize');

function qzorg_global_setting_init_callback() { ?>
    <?php echo '<p>' . esc_html__('Handle your global settings options from here :', 'quiz-organizer') . '</p>';
}

function qzorg_delete_data_on_plugin_deletion_callback() {
    $checkbox_value = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
    ?>
    <input type="checkbox" id="delete_data_on_plugin_deletion" name="qzorg_global_options[delete_data_on_plugin_deletion]" value="1" <?php checked(1, isset($checkbox_value['delete_data_on_plugin_deletion']) ? $checkbox_value['delete_data_on_plugin_deletion'] : 0); ?> />
    <?php
}

function qzorg_stop_storing_ip_address_callback() { 
    $checkbox_value = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
    ?>
    <input type="checkbox" id="stop_storing_ip_address" name="qzorg_global_options[stop_storing_ip_address]" value="1" <?php checked(1, isset($checkbox_value['stop_storing_ip_address']) ? $checkbox_value['stop_storing_ip_address'] : 0); ?> />
    <?php
}

function qzorg_default_question_type_callback() { 
    $question = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
    ?>
    <select id="qzorg_global_options[default_question_type]" name="qzorg_global_options[default_question_type]" >
        <option value="" disabled><?php esc_html_e( 'Default Question Type', 'quiz-organizer' ); ?></option>
        <?php
            foreach ( Qzorg_Phtml::qzorg_question_types() as $key => $each_type ) { 
                echo '<option '. selected( $key, $question['default_question_type'], false) .' value="' . esc_attr( $key ) . '">' . esc_html( $each_type ) . '</option>';
            } ?>
    </select>
    <?php
}

function qzorg_default_answer_fields_callback() {
    $default_answer = Qzorg_Global_Settings::qzorg_get_default_fields(get_option('qzorg_global_options'));
    $fields = ! $default_answer['default_answer_fields'] || 0 == $default_answer['default_answer_fields'] ? 1 : $default_answer['default_answer_fields'];
    ?>
    <input type="number" min="1" id="default_answer_fields" name="qzorg_global_options[default_answer_fields]" value="<?php echo esc_attr($fields); ?>" />
    <?php
}

function qzorg_global_settings_page() {
    if ( isset($_GET['page']) && 'qzorg_global_settings' == $_GET['page'] ) {
        $quizGlobalSettingsObj = new Qzorg_Global_Settings();
        $quizGlobalSettingsObj->display_settings();
    }
}