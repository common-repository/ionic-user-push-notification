<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Ionic_User_UserId_Manager {

    const USER_ID_TABLE_NAME = 'iup_user_ids';
    const USER_ID_FIELD_USER_ID = 'userId';
    const USER_ID_FIELD_LAST_TOUCHED = 'lastTouched';
    const USER_ID_FIELD_CREATED = 'created';

    public function create_user_id_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::USER_ID_TABLE_NAME;

        $sql = "CREATE TABLE $table_name (
          `" . self::USER_ID_FIELD_USER_ID . "` varchar(50) NOT NULL,
          `" . self::USER_ID_FIELD_CREATED . "` datetime DEFAULT NULL,
          `" . self::USER_ID_FIELD_LAST_TOUCHED . "` datetime DEFAULT NULL,
          PRIMARY KEY  (`" . self::USER_ID_FIELD_USER_ID . "`)
        );";

        dbDelta( $sql );
    }

    /**
     * @param string $userId
     * @return bool|int
     */
    public function delete_userId($userId) {
        global $wpdb;

        $sql = "
            DELETE FROM `{$wpdb->prefix}" . self::USER_ID_TABLE_NAME . "`
            WHERE `" . self::USER_ID_FIELD_USER_ID . "` = %s
        ";

        $sql = $wpdb->prepare($sql, $userId);
        return $wpdb->query($sql);
    }

    /**
     * @param string $userId
     * @return bool|int
     */
    public function store_userId($userId) {
        global $wpdb;

        $sql = "
            INSERT INTO `{$wpdb->prefix}" . self::USER_ID_TABLE_NAME . "`
            (`" . self::USER_ID_FIELD_USER_ID . "`, `" . self::USER_ID_FIELD_CREATED . "`, `" . self::USER_ID_FIELD_LAST_TOUCHED . "`)
            VALUES (%s,%s,%s)
            ON DUPLICATE KEY UPDATE `" . self::USER_ID_FIELD_LAST_TOUCHED . "` = %s
        ";

        $currentDate = date('Y-m-d H:i:s');
        $sql = $wpdb->prepare($sql, $userId, $currentDate, $currentDate, $currentDate, $currentDate);
        return $wpdb->query($sql);
    }

    /**
     * @return null|string
     */
    public function get_total_userIds() {
        global $wpdb;

        return $wpdb->get_var( "SELECT COUNT(`" . self::USER_ID_FIELD_USER_ID . "`) FROM `{$wpdb->prefix}" . self::USER_ID_TABLE_NAME . "`" );
    }

    /**
     * @return array
     */
    public function get_all_userIds() {
        global $wpdb;

        $result = $wpdb->get_results( "SELECT `" . self::USER_ID_FIELD_USER_ID . "` FROM `{$wpdb->prefix}" . self::USER_ID_TABLE_NAME . "`" );
        if ($result === null) {
            return array();
        }

        $userIds = array();
        foreach ($result as $row) {
            $userIds[] = $row->userId;
        }

        return $userIds;
    }

    /**
     * @param int $pagenum
     * @param int $limit
     * @return array
     */
    public function get_userIds_page_links($pagenum, $limit = 25) {
        global $wpdb;

        $offset = ( $pagenum - 1 ) * $limit;
        $total = self::get_total_userIds();
        $num_of_pages = ceil( $total / $limit );
        $results = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}" . self::USER_ID_TABLE_NAME . "` LIMIT $offset, $limit" );

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