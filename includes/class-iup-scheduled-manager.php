<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Ionic_User_Scheduled_Manager {

    const SCHEDULED_TABLE_NAME = 'iup_scheduled_push';
    const SCHEDULED_FIELD_ID = 'id';
    const SCHEDULED_FIELD_USER_IDS = 'userIds';
    const SCHEDULED_FIELD_TEXT = 'text';
    const SCHEDULED_FIELD_DATETIME = 'datetime';

    public function create_scheduled_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::SCHEDULED_TABLE_NAME;

        $sql = "CREATE TABLE $table_name (
          `" . self::SCHEDULED_FIELD_ID . "` int(11) NOT NULL AUTO_INCREMENT,
          `" . self::SCHEDULED_FIELD_USER_IDS . "` text NOT NULL,
          `" . self::SCHEDULED_FIELD_DATETIME . "` datetime DEFAULT NULL,
          `" . self::SCHEDULED_FIELD_TEXT . "` text DEFAULT NULL,
          PRIMARY KEY  (`" . self::SCHEDULED_FIELD_ID . "`)
        );";

        dbDelta( $sql );
    }

    /**
     * @param int $id
     * @return bool|int
     */
    public function delete_scheduled($id) {
        global $wpdb;

        $sql = "
            DELETE FROM `{$wpdb->prefix}" . self::SCHEDULED_TABLE_NAME . "`
            WHERE `" . self::SCHEDULED_FIELD_ID . "` = %d
        ";

        $sql = $wpdb->prepare($sql, $id);
        return $wpdb->query($sql);
    }

    /**
     * @return array
     */
    public function get_passed_scheduled() {
        global $wpdb;

        $result = $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}" . self::SCHEDULED_TABLE_NAME . "` WHERE `" . self::SCHEDULED_FIELD_DATETIME . "` < NOW()"
        );
        if ($result === null) {
            return array();
        }

        return $result;
    }

    /**
     * @param string $date
     * @param string $time
     * @param string $text
     * @param string $userIds
     * @return WP_Error|bool|int
     */
    public function store_scheduled($date, $time, $text, $userIds) {
        if (empty($text)) {
            return new WP_Error( 'broke', __( "Missing text to store scheduled push notification!", "menu" ) );
        }

        if (count($userIds) === 0 || empty($userIds[0]) === true) {
            return new WP_Error( 'broke', __( "Missing users ids to store scheduled push notification!", "menu" ) );
        }

        if (empty($date) || empty($time) ) {
            return new WP_Error( 'broke', __( "Missing date or time to store scheduled push notification!", "menu" ) );
        }

        global $wpdb;

        $sql = "
            INSERT INTO `{$wpdb->prefix}" . self::SCHEDULED_TABLE_NAME . "`
            (`" . self::SCHEDULED_FIELD_USER_IDS . "`, `" . self::SCHEDULED_FIELD_TEXT . "`, `" . self::SCHEDULED_FIELD_DATETIME . "`)
            VALUES (%s,%s,%s)
        ";

        $date = date('Y-m-d H:i:s', strtotime($date . ' ' . $time));
        $sql = $wpdb->prepare($sql, $userIds, $text , $date);
        return $wpdb->query($sql);
    }

    /**
     * @param int $pagenum
     * @param int $limit
     * @return array
     */
    public function get_scheduled_page_links($pagenum, $limit = 25) {
        global $wpdb;

        $offset = ( $pagenum - 1 ) * $limit;
        $total = $wpdb->get_var( "SELECT COUNT(`" . self::SCHEDULED_FIELD_ID . "`) FROM `{$wpdb->prefix}" . self::SCHEDULED_TABLE_NAME . "`" );
        $num_of_pages = ceil( $total / $limit );
        $results = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}" . self::SCHEDULED_TABLE_NAME . "` ORDER BY `" . self::SCHEDULED_FIELD_DATETIME . "` LIMIT $offset, $limit" );

        $paginate_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'text-domain' ),
            'next_text' => __( '&raquo;', 'text-domain' ),
            'total' => $num_of_pages,
            'current' => $pagenum
        ) );

        return array(
            'paginate_links' => $paginate_links,
            'results' => $results
        );
    }
}