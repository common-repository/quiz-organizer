<?php

/**
 * To return path
 *
 * Returns path for specified file.
 *
 * @since   1.0.0
 *
 * @param   string $file The specified file.
 * @return  string
 */
function qzorg_generate_path( $file = '', $class = 0 ) {
	return 1 === $class ? QZORG_INCLUDE_CLASS . '/' . $file : QZORG_INCLUDE_PAGE . '/' . $file;
}

/**
 * To include page
 *
 * @since   1.0.0
 *
 * @param   string $file The specified file.
 * @return  void
 */
function qzorg_inc_page( $file = '' ) {
	$p = qzorg_generate_path( $file );
	if ( file_exists( $p ) ) {
		include_once $p;
	}
}


/**
 * To include class
 * @since   1.0.0
 * @param   string $file The specified file.
 * @return  void
 */
function qzorg_inc_class( $file = '' ) {
	$c = qzorg_generate_path( $file, 1 );
	if ( file_exists( $c ) ) {
		include_once $c;
	}
}


/**
 * To include sets
 *
 * @since   1.0.0
 *
 * @param   string $file The specified file.
 * @return  void
 */
function qzorg_inc_sets( $file = '' ) {
	$s = QZORG_INCLUDE_SETS . '/' . $file;
	if ( file_exists( $s ) ) {
		include_once $s;
	}
}


/**
 * @since   1.0.0
 *
 * @param $title for the section
 * @param $callback name
 * @param $fields section fields
 */

function qzorg_register_setting_section( $fields , $callable_type, $tools = array() ) {
	qzorg_quiz_settings_section($fields, $callable_type, $tools);
}

/**
 * @return Qzorg_Notification_Guide - Notices
 */

function qzorg_show_notices() {
	Qzorg_Notification_Guide::qzorg_display_admin_notices();
}


function qzorg_add_action_link( $links ) {
	$settings_link = '<a href="' . admin_url('admin.php?page=qzorg_global_settings') . '">Settings</a>';
	array_push($links, $settings_link);
	return $links;
}


function qzorg_quiz_setting() {
    Qzorg_Defaults::init();
}

/**
 * Filters
 */

function qzorg_get_tabs( $tab_field ) {
	return array_keys($tab_field);
}

add_filter( 'qzorg_tags_custom_filter', 'qzorg_get_tabs' );
add_action( 'qzorg_check_is_empty', 'qzorg_is_required',10,2);

function qzorg_is_required( $v, $m ) {
	if ( "" == $v ) {
		echo wp_json_encode(
			array(
				'success' => 0,
				'message' => $m,
			)
		);
		exit;
	}
}

/**
 * @since   1.0.0
 *
 * @param string $file The specified file.
 * @param $title for the section
 * @param $fields section fields
 */

function qzorg_quiz_settings_section( $fields, $type, $tools ) { ?>
	<?php
	foreach ( $fields[ $type ] as $field_key => $field_value ) {
		if ( ! empty($tools) ) {
			if ( ! isset($tools[ $type ][ $field_value['name'] ]) ) {
				$tools[ $type ] = qzorg_get_default_key($type);
			}
			$field_value['fill'] = $tools[ $type ][ $field_value['name'] ];
		}
		Qzorg_Defaults::settings($field_value, "qzorg_add_{$field_value['type']}_field");
	}
}