<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add HTML Structure
 * 
 * @since 1.0.0
 */
if ( ! class_exists('Qzorg_Phtml') ) {
    class Qzorg_Phtml
    {
        /**
         * @since 1.0.0
         * @uses Manage editor settings
         */

        public static function qzorg_editor_settings() {
            return array(
                'editor_height' => 175,
                'editor_class'  => 'qzorg-textarea',
                'media_buttons' => false,
                'tinymce'       => true,
                'teeny'         => false, // Set to false
            );
        }

        /**
         * @since 1.0.0
         * @uses To display user role
         */

        public static function qzorg_wp_user_role() {
            $roles = wp_get_current_user()->roles;

            if ( in_array('administrator', $roles, true) ) {
                return 'Administrator';
            }
            if ( in_array('editor', $roles, true) ) {
                return 'Editor';
            }
            if ( in_array('author', $roles, true) ) {
                return 'Author';
            }
            if ( in_array('contributor', $roles, true) ) {
                return 'Contributor';
            }
            if ( in_array('subscriber', $roles, true) ) {
                return 'Subscriber';
            }

            return '';
        }

        /**
         * @since 1.0.0
         * @uses Retrive user info weather login or not
         */

        public static function qzorg_get_current_user() {
            $user_info = array(
                'user_id'  => 0,
                'username' => '',
                'email'    => '',
            );

            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $user_info['user_id'] = $current_user->ID;
                $user_info['username'] = $current_user->user_login;
                $user_info['email'] = $current_user->user_email;
            }

            return $user_info;
        }

        /**
         * @since 1.0.0
         * @param $atts
         * @uses Functions lists
         */

        public static function qzorg_add_text_field( $atts ) {
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            $class = isset($atts['class']) ? $atts['class'] : "";
            $wrap = isset($atts['wrap']) ? $atts['wrap'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-text-wrapper <?php echo esc_attr($atts['name']); ?> <?php echo esc_attr($wrap); ?>" >
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner text-field">
                <input type="text" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" value="<?php echo esc_attr($fill); ?>" class="<?php echo esc_attr($class); ?> qzorg-global-input">
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_email_field( $atts ) {
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            $class = isset($atts['class']) ? $atts['class'] : "";
            $wrap = isset($atts['wrap']) ? $atts['wrap'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-email-wrapper <?php echo esc_attr($atts['name']); ?> <?php echo esc_attr($wrap); ?>" >
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner email-field">
                <input type="email" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" value="<?php echo esc_attr($fill); ?>" class="<?php echo esc_attr($class); ?> qzorg-global-email">
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_url_field( $atts ) {
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            $button = isset($atts['button']) ? $atts['button'] : "";
            $class = isset($atts['class']) ? $atts['class'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-url-wrapper <?php echo esc_attr($atts['name']); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner url-field">
                <input type="url" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" value="<?php echo esc_attr($fill); ?>" class="regular-text qzorg-global-url">
                <?php 
                if ( "yes" == $button ) { ?>
                    <button class="button <?php echo esc_attr($class); ?>"><?php echo esc_html($atts['button_text']); ?></button>
                <?php }
                if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_radio_field( $atts ) {
            $show = isset($atts['show']) ? $atts['show'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-radio-wrapper <?php echo esc_attr($atts['name']); ?>" style="display: <?php echo esc_attr($show); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner radio-field"> <?php
                foreach ( $atts['options'] as $key => $option ) : ?>
                <input type="radio" class="qzorg-global-radio" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name'] . '-' . $option['value']); ?>" value="<?php echo esc_attr($option['value']); ?>" <?php checked(true, (("" != $atts['fill']) && $option['value'] == $atts['fill'])); ?> >
                <label for="<?php echo esc_attr($atts['name'] . '-' . $option['value']); ?>"><?php echo esc_html($option['label']); ?></label>
            <?php endforeach; ?>
            <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_select_field( $atts ) {
            $wrap = isset($atts['wrap']) ? $atts['wrap'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-select-wrapper <?php echo esc_attr($atts['name']); ?> <?php echo esc_attr($wrap); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner select-field"> 
                <select name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" class="qzorg-global-select" >
                    <?php foreach ( $atts['options'] as $key => $option ) : ?>
                        <option value="<?php echo sanitize_key($option['value']); ?>" <?php selected($atts['fill'], $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_date_field( $atts ) {
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-date-wrapper <?php echo esc_attr($atts['name']); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner date-field">
                <input type="date" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" value="<?php if ( $fill ) {
                    echo esc_attr(gmdate('Y-m-d', strtotime($fill)));
                } ?>" class="regular-text qzorg-global-date">
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_number_field( $atts ) {
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            $min = isset($atts['min']) ? $atts['min'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-number-wrapper <?php echo esc_attr($atts['name']); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner number-field">
                <input type="number" min="<?php echo esc_attr($min); ?>" name="<?php echo esc_attr($atts['name']); ?>" id="<?php echo esc_attr($atts['name']); ?>" value="<?php echo esc_attr($fill); ?>" class="small-text qzorg-regular-number qzorg-global-number">
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_editor_field( $atts ) {

            $editor = self::qzorg_editor_settings();
            if ( isset($atts['editor_height']) ) {
                $editor['editor_height'] = $atts['editor_height'];
            }
            $fill = isset($atts['fill']) ? $atts['fill'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-editor-wrapper <?php echo esc_attr($atts['name']); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner editor-field">
                <?php wp_editor(stripslashes($fill), $atts['name'], $editor); ?>
                <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php
        }

        public static function qzorg_add_checkbox_field( $atts ) {
            $show = isset($atts['show']) ? $atts['show'] : "";
            ?>
        <div class="qzorg-field-wrapper qzorg-checkbox-wrapper <?php echo esc_attr($atts['name']); ?>">
            <div class="input-label">
                <label  for="<?php echo esc_attr($atts['name']); ?>"><?php echo esc_html($atts['label']); ?></label>
            </div>
            <div class="qzorg-field-inner checkbox-field"> <?php
                foreach ( $atts['options'] as $key => $option ) : ?>
                <input type="checkbox" name="<?php echo esc_attr($atts['name']); ?>" class="qzorg-global-checkbox" id="<?php echo esc_attr($atts['name'] . '-' . $option['value']); ?>" value="<?php echo esc_attr($option['value']); ?>" <?php checked(1, ( ! empty($atts['fill']) && $option['value'] == $atts['fill']), true); ?> >
                <label style="display: <?php echo esc_attr($show); ?>" for="<?php echo esc_attr($atts['name'] . '-' . $option['value']); ?>"><?php echo esc_html($option['label']); ?></label>
            <?php endforeach; ?>
            <?php if ( isset($atts['clue']) ) { ?>
                <div class="qzorg-field-clue">
                    <span><?php echo esc_html($atts['clue']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php }

        public static function qzorg_add_contact_custom_field( $atts ) {
            ?>
            <div class="qzorg-field-wrapper qzorg-text-wrapper disabled-overlay">
                <div class="input-label">
                    <label  for="qzorg_c_user_name"><?php echo esc_html("User name"); ?></label>
                </div>
                <div class="qzorg-field-inner text-field">
                    <input type="text" name="qzorg_c_user_name" id="qzorg_c_user_name" value="" class="regular-text qzorg-global-input" disabled>
                    <div class="qzorg-field-clue">
                        <span><?php esc_html_e("Username field for the contact form", 'quiz-organizer'); ?></span>
                    </div>
                </div>
            </div>

            <div class="qzorg-field-wrapper qzorg-text-wrapper disabled-overlay" >
                <div class="input-label">
                    <label  for="qzorg_c_user_email"><?php echo esc_html("User email"); ?></label>
                </div>
                <div class="qzorg-field-inner text-field">
                    <input type="email" name="qzorg_c_user_email" id="qzorg_c_user_email" value="" class="regular-text qzorg-global-email" disabled>
                    <div class="qzorg-field-clue">
                        <span><?php esc_html_e("Useremail field for the contact form", 'quiz-organizer'); ?></span>
                    </div>
                </div>
            </div>
        <?php
        }

        /**
         * @since 1.0.0
         * @return Question types list
         */

        public static function qzorg_question_types() {
            return array(
                "drop_down"      => __('Drop Down', 'quiz-organizer'),
                "radio"          => __('Radio Ans (Multiple Choice)', 'quiz-organizer'),
                "checkbox"       => __('Checkbox Ans (Multiple Response)', 'quiz-organizer'),
                "singlelinetext" => __('Single Line Text', 'quiz-organizer'),
                "paragraphtext"  => __('Short Text', 'quiz-organizer'),
                "number"         => __('Number', 'quiz-organizer'),
                "date"           => __('Date', 'quiz-organizer'),
            );
        }

        /**
         * @since 1.0.0
         * @return User Ip Address
         */

        public static function qzorg_set_user_ip() {

            global $Quiz_Organizer;
            if ( isset($Quiz_Organizer->globalSettings['stop_storing_ip_address']) && '1' == $Quiz_Organizer->globalSettings['stop_storing_ip_address'] ) {
                return "DISABLED";
            }

            $ipaddress = 'UNKNOWN';

            if ( getenv('HTTP_X_REAL_IP') ) {
                $ipaddress = getenv('HTTP_X_REAL_IP');
            } elseif ( getenv('HTTP_CLIENT_IP') ) {
                $ipaddress = getenv('HTTP_CLIENT_IP');
            } elseif ( getenv('HTTP_X_FORWARDED_FOR') ) {
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            } elseif ( getenv('HTTP_X_FORWARDED') ) {
                $ipaddress = getenv('HTTP_X_FORWARDED');
            } elseif ( getenv('HTTP_FORWARDED_FOR') ) {
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
            } elseif ( getenv('HTTP_FORWARDED') ) {
                $ipaddress = getenv('HTTP_FORWARDED');
            } elseif ( getenv('REMOTE_ADDR') ) {
                $ipaddress = getenv('REMOTE_ADDR');
            }

            return $ipaddress;
        }

    }
}