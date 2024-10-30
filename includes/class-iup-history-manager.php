<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class Ionic_User_History_Manager {

    const HISTORY_TABLE_NAME = 'iup_push_history';
    const HISTORY_FIELD_TEXT = 'text';
    const HISTORY_FIELD_RECIPIENTS = 'recipients';
    const HISTORY_FIELD_RESULT = 'result';
    const HISTORY_FIELD_DATE = 'send';

    public function create_push_history_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::HISTORY_TABLE_NAME;

        $sql = "CREATE TABLE $table_name (
          `" . self::HISTORY_FIELD_TEXT . "` text NOT NULL,
          `" . self::HISTORY_FIELD_RECIPIENTS . "` int(11) DEFAULT NULL,
          `" . self::HISTORY_FIELD_RESULT . "` text NOT NULL,
          `" . self::HISTORY_FIELD_DATE . "` datetime DEFAULT NULL
        );";

        dbDelta( $sql );
    }

    /**
     * @param string $text
     * @param int $recipients
     * @param stdClass $result
     */
    public function store_history($text, $recipients, stdClass $result) {
        global $wpdb;

        $sql = "
            INSERT INTO `{$wpdb->prefix}" . self::HISTORY_TABLE_NAME . "`
            (`" . self::HISTORY_FIELD_TEXT . "`, `" . self::HISTORY_FIELD_RECIPIENTS . "`, `" . self::HISTORY_FIELD_RESULT . "`, `" . self::HISTORY_FIELD_DATE . "`)
            VALUES (%s,%d,%s,%s)
        ";

        $sql = $wpdb->prepare($sql, $text, $recipients, json_encode($result), date('Y-m-d H:i:s'));
        $wpdb->query($sql);
    }

    /**
     * @param int $pagenum
     * @param int $limit
     * @return array
     */
    public function get_history_page_links($pagenum, $limit = 25) {
        global $wpdb;

        $offset = ( $pagenum - 1 ) * $limit;
        $total = $wpdb->get_var( "SELECT COUNT(`" . self::HISTORY_FIELD_TEXT . "`) FROM `{$wpdb->prefix}" . self::HISTORY_TABLE_NAME . "`" );
        $num_of_pages = ceil( $total / $limit );
        $results = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}" . self::HISTORY_TABLE_NAME . "` LIMIT $offset, $limit" );

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