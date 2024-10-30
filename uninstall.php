<?php
/*
 * Uninstall plugin
 */

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

require_once __FILE__ . 'includes/class-iup-userId-manager.php';

delete_option( Ionic_User_Push_Admin::OPTION_NAME );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . Ionic_User_UserId_Manager::USER_ID_TABLE_NAME );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . Ionic_User_Scheduled_Manager::SCHEDULED_TABLE_NAME );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . Ionic_User_History_Manager::HISTORY_TABLE_NAME );