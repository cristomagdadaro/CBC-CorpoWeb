<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BRM_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'redirect',
            'plural'   => 'redirects',
            'ajax'     => false
        ) );
    }

    public function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'slug'      => __( 'Slug', 'cbc-golink' ),
            'target'    => __( 'Target URL', 'cbc-golink' ),
            'clicks'    => __( 'Clicks', 'cbc-golink' ),
            'expires'   => __( 'Expires', 'cbc-golink' ),
            'type'      => __( 'Type', 'cbc-golink' ),
            'is_public' => __( 'From Public', 'cbc-golink' ),
            'qr_code'   => __( 'QR Code', 'cbc-golink' ),
            'actions'   => __( 'Actions', 'cbc-golink' )
        );
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'slug'    => array( 'slug', false ),
            'target'  => array( 'target_url', false ),
            'clicks'  => array( 'clicks', false ),
            'created' => array( 'created', true ),
            'expires' => array( 'expires', false )
        );
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        $actions = array(
            'delete' => __( 'Delete', 'cbc-golink' )
        );
        return $actions;
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            $ids = isset( $_REQUEST['redirect_id'] ) ? $_REQUEST['redirect_id'] : array();
            if ( ! empty( $ids ) ) {
                check_admin_referer( 'bulk-' . $this->_args['plural'] );
                global $wpdb;
                $table = $wpdb->prefix . 'brm_redirects';
                foreach ( $ids as $id ) {
                    $id = intval( $id );
                    $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
                }
            }
        }
    }

    public function prepare_items() {
        global $wpdb;
        $table = $wpdb->prefix . 'brm_redirects';

        $this->process_bulk_action();

        $per_page = 50;
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );

        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created';
        $order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';

        $valid_orderbys = array( 'slug', 'target_url', 'clicks', 'created', 'expires' );
        if ( ! in_array( $orderby, $valid_orderbys ) ) {
            $orderby = 'created';
        }
        $order = ( $order === 'ASC' ) ? 'ASC' : 'DESC';

        $offset = ( $current_page - 1 ) * $per_page;

        $query = "SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $data = $wpdb->get_results( $wpdb->prepare( $query, $per_page, $offset ) );

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'clicks':
                return number_format_i18n( $item->clicks );
            case 'expires':
                return $item->expires ? esc_html( $item->expires ) : '-';
            case 'type':
                return isset($item->redirect_type) && $item->redirect_type === '301' ? '301 Direct' : 'Splash Page';
            case 'is_public':
                return $item->is_public ? __( 'Yes', 'cbc-golink' ) : __( 'No', 'cbc-golink' );
            default:
                return print_r( $item, true );
        }
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="redirect_id[]" value="%d" />',
            $item->id
        );
    }

    public function column_slug( $item ) {
        $short_url = esc_url( site_url( '/go/' . $item->slug ) );
        $edit_link = admin_url( 'admin.php?page=brm_add&edit=' . intval( $item->id ) );
        $delete_nonce = wp_create_nonce( 'brm_delete_' . $item->id );
        
        $actions = array(
            'edit'   => sprintf( '<a href="%s">%s</a>', $edit_link, __( 'Edit', 'cbc-golink' ) ),
            'visit'  => sprintf( '<a href="%s" target="_blank">%s</a>', $short_url, __( 'Visit', 'cbc-golink' ) ),
            'delete' => sprintf( '<a href="%s" class="brm-action-delete" onclick="return confirm(\'Are you sure?\')">%s</a>', admin_url( 'admin-post.php?action=brm_delete_link&id=' . $item->id . '&_wpnonce=' . $delete_nonce ), __( 'Delete', 'cbc-golink' ) )
        );

        return sprintf( '%1$s %2$s',
            sprintf( '<code class="brm-slug-code">%s</code>', esc_html( $item->slug ) ),
            $this->row_actions( $actions )
        );
    }

    public function column_target( $item ) {
        return sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $item->target_url ) );
    }

    public function column_qr_code( $item ) {
        if ( ! empty( $item->qr_code ) ) {
            return sprintf( '<img src="%1$s" alt="QR Code" class="brm-qr-image" style="max-width:50px;height:auto;"><br><a href="%1$s" download class="brm-download-qr">%2$s</a>', esc_url( $item->qr_code ), __( 'Download', 'cbc-golink' ) );
        }
        return '-';
    }

    public function column_actions( $item ) {
        $short_url = esc_url( site_url( '/go/' . $item->slug ) );
        return sprintf(
            '<button class="button button-small brm-copy-url" data-url="%s">%s</button><br><button class="button-link brm-action-regenerate-qr" data-id="%d" style="margin-top:4px;">%s</button>',
            $short_url,
            __( 'Copy', 'cbc-golink' ),
            $item->id,
            __( 'Regen QR', 'cbc-golink' )
        );
    }
}
