<?php

require_once __DIR__ . '/class-iup-userId-manager.php';
require_once __DIR__ . '/class-iup-history-manager.php';
require_once __DIR__ . '/class-iup-scheduled-manager.php';
include_once __DIR__ . '/class-iup-send-push.php';

class Ionic_User_Push {

    public function plugin_activation() {
        $userIdManager = new Ionic_User_UserId_Manager();
        $userIdManager->create_user_id_table();

        $historyManager = new Ionic_User_History_Manager();
        $historyManager->create_push_history_table();

        $scheduledManager = new Ionic_User_Scheduled_Manager();
        $scheduledManager->create_scheduled_table();
    }

    /**
     * Store ionic user id to database and echo json result
     */
    public function process_parameter() {
        $params = $_REQUEST;

        if (isset($params['do-ionic-user-cron']) === true) {
            $sendPush = new Ionic_User_Send_Push();
            $sendPush->send_scheduled_push_notification();
            exit;
        }

        if (empty($params['ionic-user-id']) === false) {

            if (empty($params['action']) === true) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Missing action'
                ));
                exit;
            }

            $userIdManager = new Ionic_User_UserId_Manager();

            switch ($params['action']) {
                case 'store':
                    $result = $userIdManager->store_userId($params['ionic-user-id']);
                    self::echo_result($result, $params['action']);
                    exit;
                case 'delete':
                    $result = $userIdManager->delete_userId($params['ionic-user-id']);
                    self::echo_result($result, $params['action']);
                    exit;
                default:
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Not allowed action'
                    ));
                    exit;
            }
        }
    }

    /**
     * @param bool $result
     * @param string $action
     */
    private function echo_result($result, $action) {
        if ($result === false) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Error while do ' . $action
            ));
        } else {
            echo json_encode(array('success' => true));
        }
    }

}