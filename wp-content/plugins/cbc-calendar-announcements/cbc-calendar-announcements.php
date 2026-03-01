<?php
/**
 * Plugin Name: CBC Calendar & Announcements
 * Description: Provides admin settings for Announcements (with optional image), Events & Trainings, Holidays, and Calendar display mode. Migrates existing theme options.
 * Version: 2.0.0
 * Author: Cristo Rey C. Magdadaro
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Option name used by this plugin
const CBC_CA_OPT = 'cbc_calendar_options';
// DB version for custom table
const CBC_CA_DB_VERSION = '3';

function cbc_ca_table_name(){
    global $wpdb;
    return $wpdb->prefix . 'cbc_calendar_items';
}

function cbc_ca_db_ensure(){
    global $wpdb;
    $table = cbc_ca_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    // Optimized schema: unified columns only
    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        item_type varchar(20) NOT NULL,
        date_from date NULL,
        date_to date NULL,
        title_message text NULL,
        url text NULL,
        location text NULL,
        image text NULL,
        event_kind varchar(20) NULL,
        announce tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY item_type (item_type),
        KEY date_from (date_from),
        KEY date_to (date_to),
        KEY announce (announce)
    ) {$charset_collate};";
    dbDelta( $sql );

    // If upgrading from older schema, backfill unified columns then drop legacy columns
    $prev = get_option( 'cbc_ca_db_version' );
    if ( $prev && version_compare( (string)$prev, '3', '<' ) ){
        // Backfill unified columns from legacy data if still present
        // Note: Wrap in try/catch-like checks using information_schema to avoid errors if columns already dropped
        $has_col = function($col) use ($wpdb, $table){
            $db = DB_NAME; // WordPress DB
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            return (int)$wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME=%s", $db, $table, $col) );
        };
        if ( $has_col('item_date') ){
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->query( "UPDATE {$table} SET date_from = item_date WHERE (date_from IS NULL OR date_from = '0000-00-00') AND item_date IS NOT NULL" );
        }
        if ( $has_col('title') || $has_col('message') ){
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->query( "UPDATE {$table} SET title_message = COALESCE(NULLIF(title,''), NULLIF(message,'')) WHERE (title_message IS NULL OR title_message='')" );
        }
        if ( $has_col('image_url') ){
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->query( "UPDATE {$table} SET image = image_url WHERE (image IS NULL OR image='') AND image_url IS NOT NULL" );
        }
        // Drop legacy columns
        $drops = array('item_date','title','message','image_url','scope');
        foreach ($drops as $col){
            if ( $has_col($col) ){
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->query( "ALTER TABLE {$table} DROP COLUMN {$col}" );
            }
        }
    }

    update_option( 'cbc_ca_db_version', CBC_CA_DB_VERSION, false );
}

function cbc_ca_db_has_any_data(){
    global $wpdb;
    $table = cbc_ca_table_name();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    return $count > 0;
}

function cbc_ca_parse_lines($text){
    $lines = preg_split('/\r\n|\r|\n/', (string)$text);
    $out = array();
    $out['announcements']    = isset($input['announcements']) ? sanitize_textarea_field($input['announcements']) : '';
    $out['events']           = isset($input['events']) ? sanitize_textarea_field($input['events']) : '';
    $out['holidays']         = isset($input['holidays']) ? sanitize_textarea_field($input['holidays']) : '';
    $mode = isset($input['calendar_display']) ? (string)$input['calendar_display'] : 'grid';
    $out['calendar_display'] = in_array($mode, array('grid','list'), true) ? $mode : 'grid';
    return $out;
}

function cbc_ca_parse_bool_announce(&$parts){
    $announce = 0;
    foreach ($parts as $i => $p){
        if (strtolower(trim($p)) === 'announce'){
            $announce = 1;
            unset($parts[$i]);
        }
    }
    $parts = array_values($parts);
    return $announce;
}

// Insert helpers now also fill new unified columns
function cbc_ca_insert_announcements($items){
    global $wpdb; $table = cbc_ca_table_name();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query( $wpdb->prepare("DELETE FROM {$table} WHERE item_type = %s", 'announcement') );
    foreach ($items as $it){
        $title_message = isset($it['message']) ? $it['message'] : '';
        $data = array(
            'item_type'     => 'announcement',
            'title_message' => $title_message,
            'url'           => isset($it['url']) ? $it['url'] : null,
            'image'         => isset($it['image_url']) ? $it['image_url'] : (isset($it['image']) ? $it['image'] : null),
            'announce'      => 1,
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert($table, $data);
    }
}

function cbc_ca_insert_events($items){
    global $wpdb; $table = cbc_ca_table_name();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    $wpdb->query( "DELETE FROM {$table} WHERE item_type IN ('event','training')" );
    foreach ($items as $it){
        $type = (isset($it['event_kind']) && strtolower($it['event_kind']) === 'training') ? 'training' : 'event';
        $title_message = isset($it['title']) ? $it['title'] : '';
        $data = array(
            'item_type'     => $type,
            'date_from'     => isset($it['item_date']) ? $it['item_date'] : (isset($it['date_from']) ? $it['date_from'] : null),
            'date_to'       => isset($it['date_to']) ? $it['date_to'] : null,
            'title_message' => $title_message,
            'url'           => isset($it['url']) ? $it['url'] : null,
            'location'      => isset($it['location']) ? $it['location'] : null,
            'event_kind'    => isset($it['event_kind']) ? $it['event_kind'] : null,
            'announce'      => isset($it['announce']) ? (int)$it['announce'] : 0,
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert($table, $data);
    }
}

function cbc_ca_insert_holidays($items){
    global $wpdb; $table = cbc_ca_table_name();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query( $wpdb->prepare("DELETE FROM {$table} WHERE item_type = %s", 'holiday') );
    foreach ($items as $it){
        $title_message = isset($it['title']) ? $it['title'] : '';
        $data = array(
            'item_type'     => 'holiday',
            'date_from'     => isset($it['item_date']) ? $it['item_date'] : (isset($it['date_from']) ? $it['date_from'] : null),
            'date_to'       => isset($it['date_to']) ? $it['date_to'] : null,
            'title_message' => $title_message,
            'announce'      => isset($it['announce']) ? (int)$it['announce'] : 0,
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert($table, $data);
    }
}

function cbc_ca_migrate_text_to_db($annText, $evText, $holText){
    // Announcements
    $annRows = array();
    foreach (cbc_ca_parse_lines($annText) as $line){
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) === 0 || $parts[0] === '') continue;
        $annRows[] = array(
            'message'   => $parts[0],
            'url'       => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null,
            'image_url' => isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null,
        );
    }
    if (!empty($annRows)) cbc_ca_insert_announcements($annRows);

    // Events & Trainings
    $evRows = array();
    foreach (cbc_ca_parse_lines($evText) as $line){
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 2) continue;
        $announce = cbc_ca_parse_bool_announce($parts);
        $date = isset($parts[0]) ? $parts[0] : '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;
        $title = isset($parts[1]) ? $parts[1] : '';
        if ($title === '') continue;
        $url   = isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null;
        $kind  = isset($parts[3]) && in_array(strtolower($parts[3]), array('event','training'), true) ? strtolower($parts[3]) : null;
        $loc   = isset($parts[4]) && $parts[4] !== '' ? $parts[4] : null;
        $evRows[] = array(
            'item_date'  => $date,
            'title'      => $title,
            'url'        => $url,
            'location'   => $loc,
            'event_kind' => $kind,
            'announce'   => $announce,
        );
    }
    if (!empty($evRows)) cbc_ca_insert_events($evRows);

    // Holidays
    $holRows = array();
    foreach (cbc_ca_parse_lines($holText) as $line){
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 2) continue;
        $announce = cbc_ca_parse_bool_announce($parts);
        $date = isset($parts[0]) ? $parts[0] : '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;
        $name = isset($parts[1]) ? $parts[1] : '';
        if ($name === '') continue;
        $holRows[] = array(
            'item_date'  => $date,
            'title'      => $name,
            'announce'   => $announce,
        );
    }
    if (!empty($holRows)) cbc_ca_insert_holidays($holRows);
}

add_action('admin_init', function(){
    if ( get_option('cbc_ca_db_version') !== CBC_CA_DB_VERSION ){
        cbc_ca_db_ensure();
    }
});

function cbc_ca_get_text_from_db($type){
    global $wpdb; $table = cbc_ca_table_name();
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ){
        return '';
    }
    if ($type === 'announcement'){
        // Fetch explicit announcement items plus any other items flagged as announce=1
        // Order: announcements first, then others by date_from then id for stability
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $rows = $wpdb->get_results( "SELECT title_message, url, image, item_type, date_from, id\n FROM {$table}\n WHERE item_type = 'announcement'\n OR (announce = 1 AND item_type IN ('event','training','holiday','custom'))\n ORDER BY CASE WHEN item_type='announcement' THEN 0 ELSE 1 END, COALESCE(date_from,'9999-12-31') ASC, id ASC", ARRAY_A );
        $lines = array();
        foreach ($rows as $r){
            $msg = (string) ($r['title_message'] ?? '');
            if ($msg === '') continue;
            $parts = array($msg);
            if (!empty($r['url']))   $parts[] = $r['url'];
            if (!empty($r['image'])) $parts[] = $r['image'];
            $lines[] = implode('|', $parts);
        }
        return implode("\n", $lines);
    }
    if ($type === 'event'){
        // Include date_to so consumers can render ranges
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $rows = $wpdb->get_results( "SELECT date_from AS df, date_to AS dt, title_message, url, location, announce, event_kind, item_type FROM {$table} WHERE item_type IN ('event','training') ORDER BY df ASC, id ASC", ARRAY_A );
        $lines = array();
        foreach ($rows as $r){
            $df = $r['df'] ?? '';
            $t  = $r['title_message'] ?? '';
            if ($df === '' || $t === '') continue;
            $parts = array();
            $parts[] = $df;
            $dt = $r['dt'] ?? '';
            if ($dt !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt)) { $parts[] = $dt; }
            $parts[] = $t;
            $parts[] = !empty($r['url']) ? $r['url'] : '';
            $kind = strtolower(trim((string)($r['event_kind'] ?? '')));
            if ($kind === ''){ $kind = (strtolower((string)($r['item_type'] ?? '')) === 'training') ? 'training' : 'event'; }
            $parts[] = $kind;
            $parts[] = !empty($r['location']) ? $r['location'] : '';
            if ((int)$r['announce'] === 1) $parts[] = 'announce';
            $lines[] = implode('|', $parts);
        }
        return implode("\n", $lines);
    }
    if ($type === 'holiday'){
        // Include URL so holidays can be clickable in UI
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $rows = $wpdb->get_results( $wpdb->prepare("SELECT date_from AS df, title_message AS title, announce, url FROM {$table} WHERE item_type = %s ORDER BY df ASC, id ASC", 'holiday'), ARRAY_A );
        $lines = array();
        foreach ($rows as $r){
            $d = $r['df'] ?? '';
            $t = $r['title'] ?? '';
            if ($d === '' || $t === '') continue;
            $parts = array($d, $t);
            if ( ! empty($r['url']) ) { $parts[] = $r['url']; }
            if ( (int)$r['announce'] === 1 ) { $parts[] = 'announce'; }
            $lines[] = implode('|', $parts);
        }
        return implode("\n", $lines);
    }
    return '';
}

function cbc_ca_maybe_migrate_on_activation(){
    cbc_ca_db_ensure();
    if ( cbc_ca_db_has_any_data() ) return;
    $existing = get_option( CBC_CA_OPT ); if ( ! is_array( $existing ) ) { $existing = array(); }
    $annText = isset($existing['announcements']) ? (string)$existing['announcements'] : '';
    $evText  = isset($existing['events']) ? (string)$existing['events'] : '';
    $holText = isset($existing['holidays']) ? (string)$existing['holidays'] : '';
    if ($annText === '' && $evText === '' && $holText === ''){
        $theme = get_option( 'govph_options' ); if ( is_array( $theme ) ) {
            $annText = isset($theme['govph_announcements']) ? (string)$theme['govph_announcements'] : '';
            $evText  = isset($theme['govph_events']) ? (string)$theme['govph_events'] : '';
            $holText = isset($theme['govph_holidays']) ? (string)$theme['govph_holidays'] : '';
        }
    }
    if ($annText !== '' || $evText !== '' || $holText !== ''){ cbc_ca_migrate_text_to_db($annText, $evText, $holText); }
}

register_activation_hook( __FILE__, function(){
    cbc_ca_maybe_migrate_on_activation();
    $theme = get_option( 'govph_options' ); if ( is_array( $theme ) && isset($theme['govph_calendar_display']) ){
        $opts = get_option( CBC_CA_OPT ); if ( ! is_array($opts) ) $opts = array();
        if ( empty($opts['calendar_display']) ){
            $mode = (string) $theme['govph_calendar_display'];
            $opts['calendar_display'] = in_array($mode, array('grid','list'), true) ? $mode : 'grid';
            update_option( CBC_CA_OPT, $opts, false );
        }
    }
    if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
});

// Admin list + CRUD UI
add_action('admin_menu', function(){
    $hook = add_submenu_page(
        'options-general.php',
        'CBC Calendar Items',
        'CBC Calendar Items',
        'manage_options',
        'cbc-calendar-items',
        'cbc_ca_render_items_page'
    );
    if ( $hook ){
        add_action("load-$hook", 'cbc_ca_items_screen_options');
    }
});

// Hide/remove any old/duplicate "CBC Calendar" submenu under Settings
add_action('admin_menu', function(){
    global $submenu;
    $parent = 'options-general.php';
    // Attempt with common slugs first
    $candidate_slugs = array('cbc-calendar','cbc_calendar','cbc-calendar-settings','cbc-calendar-announcements','cbc_calendar_announcements');
    foreach ($candidate_slugs as $slug){ remove_submenu_page($parent, $slug); }
    // Fallback: remove by menu title text match
    if ( isset($submenu[$parent]) && is_array($submenu[$parent]) ){
        foreach ($submenu[$parent] as $idx => $item){
            // $item: array( [0] => menu title, [2] => slug )
            $title = isset($item[0]) ? wp_strip_all_tags($item[0]) : '';
            if ( stripos($title, 'CBC Calendar') === 0 && $item[2] !== 'cbc-calendar-items' ){
                unset($submenu[$parent][$idx]);
            }
        }
    }
}, 999);
// Extra-aggressive cleanup in case another plugin/theme registers later or under a different parent
add_action('admin_menu', function(){
    global $submenu;
    if ( ! is_array($submenu) ) return;
    foreach ($submenu as $parent => &$items){
        if ( ! is_array($items) ) continue;
        foreach ($items as $idx => $it){
            $title = isset($it[0]) ? wp_strip_all_tags($it[0]) : '';
            $slug  = isset($it[2]) ? (string)$it[2] : '';
            if ( $title === 'CBC Calendar' || in_array($slug, array('cbc-calendar','cbc_calendar','cbc-calendar-settings'), true) ){
                if ( $slug !== 'cbc-calendar-items' ){
                    unset($items[$idx]);
                }
            }
        }
    }
}, 99999);

// Early handler (legacy GET fallback): process DELETE action before any output to avoid headers already sent
add_action('admin_init', function(){
    if ( ! current_user_can('manage_options') ) return;

    // Handle GET-based delete with action=delete
    if ( isset($_GET['page']) && $_GET['page'] === 'cbc-calendar-items' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['_wpnonce']) ){
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ( $id > 0 && wp_verify_nonce( $_GET['_wpnonce'], 'cbc_ca_delete_item_'.$id ) ){
            global $wpdb; $table = cbc_ca_table_name();
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->delete( $table, array('id'=>$id), array('%d') );
            // Keep theme option text in sync
            if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
            wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','deleted'=>1), admin_url('options-general.php')) );
            exit;
        }
    }

    // Handle malformed GET request with action=cbc_ca_delete_item (should be action=delete or POST request)
    if ( isset($_GET['page']) && $_GET['page'] === 'cbc-calendar-items' && isset($_GET['action']) && $_GET['action'] === 'cbc_ca_delete_item' && isset($_GET['_wpnonce']) && isset($_GET['id']) ){
        $id = (int) $_GET['id'];
        if ( $id > 0 && wp_verify_nonce( $_GET['_wpnonce'], 'cbc_ca_delete_item' ) ){
            global $wpdb; $table = cbc_ca_table_name();
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->delete( $table, array('id'=>$id), array('%d') );
            // Keep theme option text in sync
            if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
            wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','deleted'=>1), admin_url('options-general.php')) );
            exit;
        }
    }
});

// Preferred POST-based delete handler to avoid header issues
add_action('admin_post_cbc_ca_delete_item', function(){
    if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
    check_admin_referer('cbc_ca_delete_item');
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ( $id > 0 ){
        global $wpdb; $table = cbc_ca_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->delete( $table, array('id'=>$id), array('%d') );
        if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
    }
    wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','deleted'=>1), admin_url('options-general.php')) );
    exit;
});

function cbc_ca_items_screen_options(){
    add_screen_option( 'per_page', array('label' => 'Items per page','default' => 20,'option' => 'cbc_ca_items_per_page') );
}

add_filter('set-screen-option', function($status, $option, $value){ return $option === 'cbc_ca_items_per_page' ? max(1,(int)$value) : $status; }, 10, 3);

if ( is_admin() && ! class_exists('CBC_CA_List_Table') ){
    if ( ! class_exists( 'WP_List_Table' ) ){
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class CBC_CA_List_Table extends WP_List_Table {
        public function __construct(){ parent::__construct( array('singular' => 'item','plural' => 'items','ajax' => false) ); }
        public function get_columns(){ return array(
            'cb'            => '<input type="checkbox" />',
            'item_type'     => 'Type',
            'date_from'     => 'Date From',
            'date_to'       => 'Date To',
            'title_message' => 'Title / Message',
            'url'           => 'URL',
            'location'      => 'Location',
            'announce'      => 'Announce',
            'image'         => 'Image',
            'created_at'    => 'Created',
        ); }
        protected function column_cb($item){ return sprintf('<input type="checkbox" name="items[]" value="%d" />', (int)$item['id']); }
        protected function get_sortable_columns(){ return array(
            'item_type'     => array('item_type', false),
            'date_from'     => array('date_from', true),
            'date_to'       => array('date_to', false),
            'title_message' => array('title_message', false),
            'announce'      => array('announce', false),
            'created_at'    => array('created_at', false),
        ); }
        public function get_views(){
            $base_url = add_query_arg(array('page' => 'cbc-calendar-items'), admin_url('options-general.php'));
            $current = isset($_GET['type']) ? sanitize_key($_GET['type']) : 'all';
            $counts = $this->get_type_counts(); $views = array();
            $types = array('all' => 'All','announcement' => 'Announcements','event' => 'Events','training' => 'Trainings','holiday' => 'Holidays','custom' => 'Custom');
            foreach ($types as $key => $label){ $url = $key==='all' ? $base_url : add_query_arg('type',$key,$base_url); $count = isset($counts[$key]) ? (int)$counts[$key] : 0; $views[$key] = sprintf('<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url($url), $current===$key?'current':'', esc_html($label), $count); }
            return $views;
        }
        private function get_type_counts(){ global $wpdb; $table = cbc_ca_table_name(); $counts = array('all'=>0,'announcement'=>0,'event'=>0,'training'=>0,'holiday'=>0,'custom'=>0); if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) return $counts; $rows = $wpdb->get_results("SELECT item_type, COUNT(*) AS c FROM {$table} GROUP BY item_type", ARRAY_A); $total=0; foreach ($rows as $r){ $type=$r['item_type']??''; $c=(int)($r['c']??0); if(isset($counts[$type])) $counts[$type]=$c; $total+=$c; } $counts['all']=$total; return $counts; }
        public function prepare_items(){
            $this->process_bulk_action();
            global $wpdb; $table = cbc_ca_table_name(); $columns=$this->get_columns(); $this->_column_headers = array($columns, array(), $this->get_sortable_columns()); $per_page=$this->get_items_per_page('cbc_ca_items_per_page',20); $current_page=$this->get_pagenum(); $offset=($current_page-1)*$per_page; $type = isset($_GET['type'])?sanitize_key($_GET['type']):'all'; $allowed_types=array('announcement','event','training','holiday','custom'); $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'date_from'; $order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : 'ASC'; if(!in_array($order,array('ASC','DESC'),true)) $order='ASC'; $orderby_map = array('item_type'=>'item_type','date_from'=>'date_from','date_to'=>'date_to','title_message'=>'title_message','announce'=>'announce','created_at'=>'created_at'); $orderby_sql = isset($orderby_map[$orderby]) ? $orderby_map[$orderby] : 'date_from'; $where='WHERE 1=1'; $params=array(); if(in_array($type,$allowed_types,true)){ $where.=' AND item_type = %s'; $params[]=$type; } $search = isset($_REQUEST['s'])?trim((string)$_REQUEST['s']):''; if($search!==''){ $like = '%' . $wpdb->esc_like($search) . '%'; $where .= ' AND (title_message LIKE %s OR location LIKE %s)'; array_push($params,$like,$like); } if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ){ $this->items=array(); $this->set_pagination_args(array('total_items'=>0,'per_page'=>$per_page,'total_pages'=>0)); return; } $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$table} {$where} ORDER BY {$orderby_sql} {$order} LIMIT %d OFFSET %d"; $params[]=(int)$per_page; $params[]=(int)$offset; $items = $wpdb->get_results( $wpdb->prepare($sql, $params), ARRAY_A ); $total = (int) $wpdb->get_var('SELECT FOUND_ROWS()'); $this->items = is_array($items)?$items:array(); $this->set_pagination_args(array('total_items'=>$total,'per_page'=>$per_page,'total_pages'=>$per_page?ceil($total/$per_page):0)); }
        protected function column_default($item, $column_name){
            switch($column_name){
                case 'item_type': return esc_html( ucfirst($item['item_type']) );
                case 'date_from': return esc_html($item['date_from'] ?? '');
                case 'date_to': return esc_html($item['date_to'] ?? '');
                case 'title_message':
                    $t = $item['title_message'] ?? '';
                    $edit_link = esc_url( wp_nonce_url( add_query_arg(array('page'=>'cbc-calendar-items','action'=>'edit','id'=>(int)$item['id']), admin_url('options-general.php')), 'cbc_ca_edit_item_'.(int)$item['id'] ) );
                    $out = '<strong><a href="'.$edit_link.'">'. esc_html($t) .'</a></strong>';
                    // POST-based delete form
                    $form = '<form method="post" action="'. esc_url( admin_url('admin-post.php') ) .'" style="display:inline">'
                          . wp_nonce_field('cbc_ca_delete_item', '_wpnonce', true, false)
                          . '<input type="hidden" name="action" value="cbc_ca_delete_item" />'
                          . '<input type="hidden" name="id" value="'. (int)$item['id'] .'" />'
                          . '<button type="submit" class="submitdelete" onclick="return confirm(\'Delete this item?\');" style="background:none;border:none;color:#b32d2e;cursor:pointer">Delete</button>'
                          . '</form>';
                    $actions = array(
                        'edit'   => '<a href="'.$edit_link.'">Edit</a>',
                        'delete' => $form,
                    );
                    return $out . $this->row_actions($actions);
                case 'url': if(!empty($item['url'])){ $u=esc_url($item['url']); return '<a href="'.$u.'" target="_blank" rel="noopener">'.$u.'</a>'; } return '';
                case 'location': return esc_html($item['location'] ?? '');
                case 'announce': return ((int)$item['announce']===1)?'<span class="dashicons dashicons-megaphone" title="Announced"></span> Yes':'No';
                case 'image': return esc_html($item['image'] ?? '');
                case 'created_at': return esc_html($item['created_at']);
            }
            return '';
        }
        protected function get_bulk_actions(){ return array('delete' => 'Delete'); }
        public function process_bulk_action(){
            if ( 'delete' === $this->current_action() ){
                if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
                $items = isset($_REQUEST['items']) ? array_map('intval', (array)$_REQUEST['items']) : array();
                if ( ! empty($items) ){
                    check_admin_referer('bulk-items');
                    global $wpdb; $table = cbc_ca_table_name();
                    foreach ($items as $id){
                        if ($id > 0){
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                            $wpdb->delete( $table, array('id'=>$id), array('%d') );
                        }
                    }
                    if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
                    wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','deleted'=>count($items)), admin_url('options-general.php')) );
                    exit;
                }
            }
        }
    }
}

// Handle Add/Edit save via admin-post to avoid header issues
add_action('admin_post_cbc_ca_save_item', function(){
    if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
    check_admin_referer('cbc_ca_save_item');

    $id         = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
    $item_type  = isset($_POST['item_type']) ? sanitize_key($_POST['item_type']) : 'event';
    $date_from  = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to    = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    $title      = isset($_POST['title_message']) ? sanitize_text_field($_POST['title_message']) : '';
    $url        = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    $location   = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
    $image      = isset($_POST['image']) ? esc_url_raw($_POST['image']) : '';
    $announce   = isset($_POST['announce']) ? 1 : 0;

    // Normalize
    $allowed_types = array('announcement','event','training','holiday','custom');
    if ( ! in_array($item_type, $allowed_types, true) ) $item_type = 'event';
    if ( $item_type === 'announcement' ) $announce = 1; // always announced
    $date_from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) ? $date_from : '';
    $date_to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to) ? $date_to : '';

    // Basic validation
    if ( $title === '' ){
        wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','action'=> ($id? 'edit':'add'), 'id'=>$id, 'error'=>'title'), admin_url('options-general.php')) );
        exit;
    }
    if ( $item_type !== 'announcement' && $date_from === '' ){
        wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','action'=> ($id? 'edit':'add'), 'id'=>$id, 'error'=>'date'), admin_url('options-general.php')) );
        exit;
    }

    global $wpdb; $table = cbc_ca_table_name();
    $data = array(
        'item_type'     => $item_type,
        'date_from'     => $date_from ?: null,
        'date_to'       => $date_to   ?: null,
        'title_message' => $title,
        'url'           => $url ?: null,
        'location'      => $location ?: null,
        'image'         => $image ?: null,
        'announce'      => (int)$announce,
        'updated_at'    => current_time('mysql'),
    );

    if ( $id > 0 ){
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->update($table, $data, array('id'=>$id), null, array('%d'));
        if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
        wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','updated'=>1), admin_url('options-general.php')) );
        exit;
    } else {
        $data['created_at'] = current_time('mysql');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert($table, $data);
        if ( function_exists('cbc_ca_sync_theme_options_from_db') ) { cbc_ca_sync_theme_options_from_db(); }
        wp_safe_redirect( add_query_arg(array('page'=>'cbc-calendar-items','added'=>1), admin_url('options-general.php')) );
        exit;
    }
});

function cbc_ca_render_items_page(){
    if ( ! current_user_can('manage_options') ) return;

    $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
    if ( $action === 'add' || $action === 'edit' ){
        $item = array('id'=>0,'item_type'=>'event','date_from'=>'','date_to'=>'','title_message'=>'','url'=>'','location'=>'','announce'=>0,'image'=>'');
        if ( $action === 'edit' ){
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ( $id > 0 ){
                global $wpdb; $table=cbc_ca_table_name();
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id), ARRAY_A );
                if ( $row ){
                    $item = array_merge($item, array(
                        'id'            => (int)$row['id'],
                        'item_type'     => (string)$row['item_type'],
                        'date_from'     => (string)($row['date_from'] ?? ''),
                        'date_to'       => (string)($row['date_to'] ?? ''),
                        'title_message' => (string)($row['title_message'] ?? ''),
                        'url'           => (string)($row['url'] ?? ''),
                        'location'      => (string)($row['location'] ?? ''),
                        'announce'      => (int)($row['announce'] ?? 0),
                        'image'         => (string)($row['image'] ?? ''),
                    ));
                }
            }
        }
        cbc_ca_render_item_form($item);
        return;
    }

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">CBC Calendar Items</h1> ';
    $add_url = esc_url( add_query_arg(array('page'=>'cbc-calendar-items','action'=>'add'), admin_url('options-general.php')) );
    echo '<a href="'.$add_url.'" class="page-title-action">Add New</a>';
    echo '<hr class="wp-header-end" />';

    if ( isset($_GET['updated']) ) echo '<div class="updated notice"><p>Item updated.</p></div>';
    if ( isset($_GET['added']) ) echo '<div class="updated notice"><p>Item added.</p></div>';
    if ( isset($_GET['deleted']) ) echo '<div class="updated notice"><p>Item deleted.</p></div>';
    if ( isset($_GET['error']) ){
        $err = sanitize_key($_GET['error']);
        if ($err==='title') echo '<div class="error notice"><p>Title / Message is required.</p></div>';
        if ($err==='date') echo '<div class="error notice"><p>Date From is required for this type.</p></div>';
    }

    $list_table = new CBC_CA_List_Table();
    $list_table->prepare_items();

    echo '<form method="post">';
    echo '<input type="hidden" name="page" value="cbc-calendar-items" />';
    if ( isset($_GET['type']) ){
        echo '<input type="hidden" name="type" value="' . esc_attr( sanitize_key($_GET['type']) ) . '" />';
    }
    $list_table->views();
    $list_table->search_box('Search items', 'cbc_ca_items');
    $list_table->display();
    echo '</form>';
    echo '</div>';
}

function cbc_ca_render_item_form($item){
    $is_edit = !empty($item['id']);
    $title = $is_edit ? 'Edit Item' : 'Add New Item';
    $nonce = wp_create_nonce( 'cbc_ca_save_item' );
    // Enqueue media for image picker
    if ( function_exists('wp_enqueue_media') ) { wp_enqueue_media(); }

    echo '<div class="wrap">';
    echo '<h1>'.$title.'</h1>';
    echo '<form method="post" action="'. esc_url( admin_url('admin-post.php') ) .'">';
    echo '<input type="hidden" name="action" value="cbc_ca_save_item" />';
    echo '<input type="hidden" name="id" value="'. (int)$item['id'] .'" />';
    echo '<input type="hidden" name="_wpnonce" value="'.$nonce.'" />';

    // Type
    $types = array('announcement'=>'Announcement','event'=>'Event','training'=>'Training','holiday'=>'Holiday','custom'=>'Custom');
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th><label for="cbc_type">Type</label></th><td><select name="item_type" id="cbc_type">';
    foreach ($types as $k=>$label){ $sel = selected($item['item_type'],$k,false); echo '<option value="'.esc_attr($k).'" '.$sel.'>'.esc_html($label).'</option>'; }
    echo '</select></td></tr>';

    echo '<tr><th><label for="cbc_date_from">Date From</label></th><td><input type="date" name="date_from" id="cbc_date_from" value="'. esc_attr($item['date_from']) .'" /></td></tr>';
    echo '<tr><th><label for="cbc_date_to">Date To</label></th><td><input type="date" name="date_to" id="cbc_date_to" value="'. esc_attr($item['date_to']) .'" /></td></tr>';
    echo '<tr><th><label for="cbc_title">Title / Message</label></th><td><input type="text" class="regular-text" name="title_message" id="cbc_title" value="'. esc_attr($item['title_message']) .'" required /></td></tr>';
    echo '<tr><th><label for="cbc_url">URL</label></th><td><input type="text" class="regular-text" name="url" id="cbc_url" value="'. esc_attr($item['url']) .'" /></td></tr>';
    echo '<tr><th><label for="cbc_location">Location</label></th><td><input type="text" class="regular-text" name="location" id="cbc_location" value="'. esc_attr($item['location']) .'" /></td></tr>';
    echo '<tr><th><label for="cbc_image">Image URL</label></th><td><input type="text" class="regular-text" name="image" id="cbc_image" value="'. esc_attr($item['image']) .'" />';
    echo ' <button type="button" class="button" id="cbc_image_select">Select Image</button>';
    echo ' <button type="button" class="button" id="cbc_image_clear">Clear</button>';
    echo '</td></tr>';

    $checked = (int)$item['announce'] === 1 ? 'checked' : '';
    echo '<tr><th>Announce</th><td><label><input type="checkbox" name="announce" value="1" '.$checked.' /> Include in announcements</label><p class="description">Announcements are always announced.</p></td></tr>';

    echo '</tbody></table>';
    submit_button( $is_edit ? 'Update Item' : 'Add Item' );
    echo ' <a class="button" href="'. esc_url( add_query_arg(array('page'=>'cbc-calendar-items'), admin_url('options-general.php')) ) .'">Cancel</a>';
    echo '</form>';

    // Inline JS for media picker
    echo '<script type="text/javascript">(function(){
        var btn = document.getElementById("cbc_image_select");
        var clr = document.getElementById("cbc_image_clear");
        var field = document.getElementById("cbc_image");
        if (!btn || !field) return;
        var frame;
        btn.addEventListener("click", function(e){
            e.preventDefault();
            if (frame){ frame.open(); return; }
            frame = wp.media({ title: "Select or Upload Image", button: { text: "Use this image" }, library: { type: ["image"] }, multiple: false });
            frame.on("select", function(){ var att = frame.state().get("selection").first().toJSON(); if (att && att.url){ field.value = att.url; } });
            frame.open();
        });
        if (clr){ clr.addEventListener("click", function(e){ e.preventDefault(); field.value = ""; }); }
    })();</script>';

    echo '</div>';
}

// Keep theme options (used by existing calendar grid) in sync with DB
function cbc_ca_sync_theme_options_from_db(){
    global $cbc_ca_syncing_from_db;
    $cbc_ca_syncing_from_db = true; // Signal to prevent merge filters

    $ann = cbc_ca_get_text_from_db('announcement');
    $ev  = cbc_ca_get_text_from_db('event');
    $hol = cbc_ca_get_text_from_db('holiday');

    // Update theme option array if it exists
    $theme = get_option('govph_options');
    if ( ! is_array($theme) ) { $theme = array(); }
    $theme['govph_announcements'] = $ann;
    $theme['govph_events'] = $ev;
    $theme['govph_holidays'] = $hol;
    update_option('govph_options', $theme, false);

    // Optionally mirror to this plugin's own option for reference
    $opts = get_option(CBC_CA_OPT); if (!is_array($opts)) $opts = array();
    $opts['announcements'] = $ann; $opts['events'] = $ev; $opts['holidays'] = $hol;
    update_option(CBC_CA_OPT, $opts, false);

    $cbc_ca_syncing_from_db = false; // Reset flag
}

// Expose data and settings to theme via filter used by shortcodes
if ( ! function_exists('cbc_ca_build_calendar_options') ){
    function cbc_ca_build_calendar_options(){
        // Pull text data from DB (or empty string if table absent)
        $ann = cbc_ca_get_text_from_db('announcement');
        $ev  = cbc_ca_get_text_from_db('event');
        $hol = cbc_ca_get_text_from_db('holiday');

        // Read plugin options and theme fallback
        $plug = get_option( CBC_CA_OPT ); if ( ! is_array($plug) ) $plug = array();
        $theme = get_option( 'govph_options' ); if ( ! is_array($theme) ) $theme = array();

        $display = 'grid';
        if ( ! empty($plug['calendar_display']) && in_array($plug['calendar_display'], array('grid','list'), true) ){
            $display = $plug['calendar_display'];
        } elseif ( ! empty($theme['govph_calendar_display']) && in_array($theme['govph_calendar_display'], array('grid','list'), true) ){
            $display = $theme['govph_calendar_display'];
        }

        // Toggle permissions (admin-configurable; default to enabled)
        $allow_view  = isset($plug['allow_toggle_view'])  ? (string)$plug['allow_toggle_view']  : ( isset($theme['allow_toggle_view'])  ? (string)$theme['allow_toggle_view']  : '1' );
        $allow_show  = isset($plug['allow_toggle_show'])  ? (string)$plug['allow_toggle_show']  : ( isset($theme['allow_toggle_show'])  ? (string)$theme['allow_toggle_show']  : '1' );
        $allow_range = isset($plug['allow_toggle_range']) ? (string)$plug['allow_toggle_range'] : ( isset($theme['allow_toggle_range']) ? (string)$theme['allow_toggle_range'] : '1' );

        return array(
            'announcements'      => (string)$ann,
            'events'             => (string)$ev,
            'holidays'           => (string)$hol,
            'calendar_display'   => $display,
            'allow_toggle_view'  => $allow_view,
            'allow_toggle_show'  => $allow_show,
            'allow_toggle_range' => $allow_range,
        );
    }
}

if ( ! function_exists('cbc_ca_filter_calendar_options') ){
    function cbc_ca_filter_calendar_options( $options ){
        $built = cbc_ca_build_calendar_options();
        if ( ! is_array( $options ) ) { return $built; }
        // Do not clobber existing keys if already set upstream; prefer plugin values otherwise
        foreach ($built as $k=>$v){ if ( ! array_key_exists($k, $options) || $options[$k] === '' || $options[$k] === null ){ $options[$k] = $v; } }
        return $options;
    }
    add_filter( 'cbc_calendar_options', 'cbc_ca_filter_calendar_options', 10, 1 );
}
