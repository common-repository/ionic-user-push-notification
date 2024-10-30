<?php

class Ionic_User_Push_Admin {

    const OPTION_NAME = 'ionic_user_push';

    public function admin_menu() {
        add_options_page('Ionic User Push Notifications', 'Ionic User Push', 'manage_options', 'ionic-user-push', array('Ionic_User_Push_Admin', 'admin_menu_options'));
    }

    public function admin_menu_options () {
        if (!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }

        // Store / load data from / to options
        $storeData = self::store_option(self::OPTION_NAME, $_POST);
        $options = self::load_options(self::OPTION_NAME);
        $totalUserIds = Ionic_User_UserId_Manager::get_total_userIds();

        if (filter_input(INPUT_POST, 'store-scheduled-push')) {
            if (filter_input(INPUT_POST, 'scheduled-send-to-all')) {
                $userIds = 'all';
            } else {
                $userIds = filter_input(INPUT_POST, 'scheduled-user-ids');
            }

            $scheduledStoreReturn = Ionic_User_Scheduled_Manager::store_scheduled(
                filter_input(INPUT_POST, 'scheduled-date'),
                filter_input(INPUT_POST, 'scheduled-time'),
                filter_input(INPUT_POST, 'scheduled-text'),
                $userIds
            );

            if ( is_wp_error( $scheduledStoreReturn ) ) {
                $error = $scheduledStoreReturn->get_error_message();
            }
        }

        if (filter_input(INPUT_GET, 'deleteScheduledId')) {
            Ionic_User_Scheduled_Manager::delete_scheduled(filter_input(INPUT_GET, 'deleteScheduledId'));
        }

        if (filter_input(INPUT_POST, 'send-push')) {
            // Send push notification

            if (filter_input(INPUT_POST, 'send-to-all') !== null) {
                $userIds = Ionic_User_UserId_Manager::get_all_userIds();
            } else {
                $userIds = explode(';', filter_input(INPUT_POST, 'send-user-ids'));
            }

            $sendPushReturn = Ionic_User_Send_Push::send_push_notification(filter_input(INPUT_POST, 'send-text'), $userIds, $options);
            if ( is_wp_error( $sendPushReturn ) ) {
                $error = $sendPushReturn->get_error_message();
            }
        }

        $tab = $_REQUEST['tab'] ? $_REQUEST['tab'] : 'settings';

        if ($tab === 'userIds') {
            $pagenum = filter_input(INPUT_GET, 'pagenum') ? absint(filter_input(INPUT_GET, 'pagenum')) : 1;
            $userIdsPages = Ionic_User_UserId_Manager::get_userIds_page_links($pagenum);
        }

        if ($tab === 'scheduled') {
            $pagenum = filter_input(INPUT_GET, 'pagenum') ? absint(filter_input(INPUT_GET, 'pagenum')) : 1;
            $scheduledPages = Ionic_User_Scheduled_Manager::get_scheduled_page_links($pagenum);
        }

        if ($tab === 'history') {
            $pagenum = filter_input(INPUT_GET, 'pagenum') ? absint(filter_input(INPUT_GET, 'pagenum')) : 1;
            $historyPages = Ionic_User_History_Manager::get_history_page_links($pagenum);
        }

        $template = IUP_PLUGIN_DIR_PATH . 'assets/html/iup-admin-' . $tab . '.html';
        if (is_file($template) === true) {
            require $template;
        }
    }

    /**
     * @param string $option_name
     * @return mixed
     */
    private function load_options($option_name) {
        $option_string = get_option($option_name);

        if ($option_string === false) {
            return array();
        }

        return json_decode($option_string, true);
    }

    /**
     * @param string $option_name
     * @param array $post
     * @return bool
     */
    private function store_option($option_name, array $post) {
        $option = array();
        $storeData = false;

        $options = array('appId', 'privateApiKey', 'sendUpdatePost', 'sendNewPost');

        foreach ($options as $name) {
            if (isset($post[$name])) {
                $storeData = true;
                $option[$name] = esc_html($post[$name]);
            }
        }

        if ($storeData === true) {
            update_option($option_name, json_encode($option));
        }

        return $storeData;
    }
}