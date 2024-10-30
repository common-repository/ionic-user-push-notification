<?php
/*
Plugin Name: Ionic User Push Notification
Plugin URI: https://github.com/rintynator/wp-ionic-user-push
Description: Send push notifications to ionic users
Version: 1.1.1
Author: Thorsten Rintelen
Author URI: http://www.clever-code.de
License: GPLv2
*/

/*
Copyright (C) 2016 Thorsten Rintelen (E-Mail: t.rintelen@clever-code.de)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'IUP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

require_once IUP_PLUGIN_DIR_PATH . 'includes/class-iup.php';
require_once IUP_PLUGIN_DIR_PATH . 'includes/class-iup-send-push.php';

register_activation_hook( __FILE__, array( 'Ionic_User_Push', 'plugin_activation' ) );

if ( is_admin() ) {
    require_once IUP_PLUGIN_DIR_PATH . 'includes/class-iup-admin.php';

    add_action('admin_menu', array( 'Ionic_User_Push_Admin', 'admin_menu' ));

    function load_custom_wp_admin_style() {
        wp_register_style( 'custom_wp_admin_css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );
    }
    add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
    add_action( 'transition_post_status', array( 'Ionic_User_Send_Push', 'send_push_for_new_post'), 2, 3);
} else {
    require_once IUP_PLUGIN_DIR_PATH . 'includes/class-iup-userId-manager.php';

    add_action( 'init', array( 'Ionic_User_Push', 'process_parameter') );
}
