<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add HTML Structure
 * 
 * @since 1.0.0
 * @param $param.
 */
if ( ! class_exists('Qzorg_Category') ) {
    class Qzorg_Category
    {
        /**
         * @since 1.0.0
         * @param $param array()
         */

        public static function index( $params ) {
            global $wpdb;
            $table = $wpdb->prefix . "qzorg_categories";
            $category_name = isset($params['category_name_filter']) && "" !== $params['category_name_filter'] ? $params['category_name_filter'] : "";
            $q_results_obj = $wpdb->get_results("SELECT id, category_name, category_description FROM {$wpdb->prefix}qzorg_categories");
            if ( ! empty($category_name) ) {
                $q_results_obj = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, category_name, category_description 
                FROM {$wpdb->prefix}qzorg_categories 
                WHERE (category_name LIKE %s OR category_description LIKE %s)",
                    '%' . $wpdb->esc_like($category_name) . '%',
                    '%' . $wpdb->esc_like($category_name) . '%'
                ));
            }
            return $q_results_obj ? $q_results_obj : __('No categories found !', 'quiz-organizer');
        }

        /**
         * @since 1.0.0
         */

        public static function create() {
            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_categories";
            $insert      = $wpdb->insert(
                $table_name,
                array(
                    'category_name' => $category_name,
                    'category_type' => "quiz",
                    'created_at'    => current_time('mysql'),
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                ),
            );

            if ( false !== $insert ) {
                $last_id = $wpdb->insert_id;
                echo wp_json_encode(
                    array(
                        'success' => 1,
                        'message' => $last_id,
                    )
                );
            } else {
                do_action('qzorg_check_is_empty', $insert, __('Error to process your request !', 'quiz-organizer'));
            }
            die;
        }

        /**
         * @since 1.0.0
         */
        public static function edit( $id ) {
            global $wpdb;
            $q_results_obj = $wpdb->get_row($wpdb->prepare(
                "SELECT id, category_name, category_description 
            FROM {$wpdb->prefix}qzorg_categories 
            WHERE id = %d",
                $id
            ));
            return $q_results_obj ? $q_results_obj : __('categorY does not exists !', 'quiz-organizer');
        }

        /**
         * @since 1.0.0
         */
        public static function update( $id, $nonce ) {
            if ( isset($nonce) && wp_verify_nonce($nonce['nonce'], 'qzorg_create_update_category') ) {
                $category_name = isset($_POST['category_name']) ? sanitize_text_field(wp_unslash($_POST['category_name'])) : "";
                $category_description = isset($_POST['category_description']) ? sanitize_textarea_field(wp_unslash($_POST['category_description'])) : "";
                global $wpdb;
                $table_name = $wpdb->prefix . "qzorg_categories";
                if ( "" == $id ) {
                    $insert = $wpdb->insert(
                        $table_name,
                        array(
                            'category_name'        => $category_name,
                            'category_type'        => "quiz",
                            'category_description' => $category_description,
                            'created_at'           => current_time('mysql'),
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                        ),
                    );
                    if ( false === $insert ) {
                        wp_send_json_error([
                            'message' => __('Error to process your request !', 'quiz-organizer'),
                        ], 500);
                    } else {
                        wp_send_json_success([
                            'message' => __('Category Created !', 'quiz-organizer'),
                            'id'      => $wpdb->insert_id,
                        ], 200);
                    }
                } else {
                    if ( false === $wpdb->update(
                        $table_name,
                        array(
                            'category_name'        => $category_name,
                            'category_description' => $category_description,
                        ),
                        array(
                            'id' => $id,
                        ),
                    ) ) {
                        wp_send_json_error([
                            'message' => __('Error to process your request !', 'quiz-organizer'),
                        ], 500);
                    } else {
                        wp_send_json_success(__('Category Updated !', 'quiz-organizer'));
                    }
                }
            } else {
                wp_send_json_error([
                    'message' => __('Error nonce validation failed !', 'quiz-organizer'),
                ], 500);
            }
        }

        /**
         * @since 1.0.0
         */
        public static function destroy( $id ) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qzorg_categories";
            if ( empty($id) || false === $wpdb->delete(
                $table_name,
                array(
                    'id' => $id,
                ),
            ) ) {
                wp_send_json_error([
                    'message' => __('Category not exist !', 'quiz-organizer'),
                ], 404);
            } else {
                wp_send_json_success(__('Category Deleted !', 'quiz-organizer'));
            }
            die;
        }

    }

}