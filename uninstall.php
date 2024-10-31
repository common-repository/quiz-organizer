<?php
/**
 * This code will execute when the plugin is uninstalled.
 */

if ( ! defined('WP_UNINSTALL_PLUGIN')) exit;

// Check if the option for deleting data on plugin deletion is enabled
$global_options = get_option('qzorg_global_options', array());
if ( isset($global_options['delete_data_on_plugin_deletion']) && 1 == $global_options['delete_data_on_plugin_deletion'] ) {
    global $wpdb;

    $tables_to_drop = array( 'qzorg_quizzes', 'qzorg_questions', 'qzorg_categories', 'qzorg_submissions' );

    foreach ( $tables_to_drop as $each_table ) {
        $table_name = $wpdb->prefix . $each_table;
        $query = $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %s', $table_name));
    }
}
