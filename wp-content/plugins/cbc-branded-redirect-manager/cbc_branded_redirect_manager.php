<?php
/**
 * Plugin Name: CBC GoLink
 * Description: Create and manage branded short links that redirect to external URLs and log clicks. Adds shortlinks at /go/{slug} and an admin UI to create/manage links.
 * Version: 1.4
 * Author: Cristo Rey C. Magdadaro
 * Text Domain: cbc-golink
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BRM_Plugin {
    private $table;
    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'brm_redirects';

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'init', array( $this, 'add_rewrite' ) );
        add_action( 'template_redirect', array( $this, 'handle_redirect' ) );

        // Admin actions
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_ajax_brm_save_link', array( $this, 'handle_ajax_save_link' ) );
        // NOTE: wp_ajax_nopriv intentionally removed - only authenticated users + admin can create links
        add_action( 'wp_ajax_brm_regenerate_qr', array( $this, 'handle_ajax_regenerate_qr' ) );
        add_action( 'admin_post_brm_delete_link', array( $this, 'admin_delete_link' ) );

        // Assets
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );

        // Frontend Shortcode
        add_shortcode( 'brm_create_link_form', array( $this, 'create_link_form_shortcode' ) );
    }

    /**
     * Helper function to generate a cryptographically secure random slug.
     */
    private function generate_random_slug( $length = 9 ) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $result = "";
        for ( $i = 0; $i < $length; $i ++ ) {
            $result .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
        }
        return $result;
    }


    /* Activation - create DB table, set default options, and flush rules */
    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = "CREATE TABLE {$this->table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(191) NOT NULL,
            target_url TEXT NOT NULL,
            clicks BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            og_title VARCHAR(255) DEFAULT NULL,
            og_description TEXT DEFAULT NULL,
            og_image VARCHAR(255) DEFAULT NULL,
            qr_code VARCHAR(255) DEFAULT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires DATETIME NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            is_public TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        if ( ! get_option( 'brm_public_access' ) ) {
            add_option( 'brm_public_access', 'private' );
        }

        $this->add_rewrite();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    /* Add rewrite rule for /go/{slug} */
    public function add_rewrite() {
        add_rewrite_rule( '^go/([^/]+)/?$', 'index.php?brm_redirect=$matches[1]', 'top' );
        add_rewrite_tag( '%brm_redirect%', '([^&]+)' );
    }

    /* Handle front-end redirect (Styles reverted to original inline, as CSS files are generally not loaded on direct redirects) */
    public function handle_redirect() {
        $slug = get_query_var( 'brm_redirect' );
        if ( empty( $slug ) ) {
            return;
        }

        global $wpdb;
        // Try to get from cache first (1 hour TTL for performance)
        $cache_key = 'brm_redirect_' . md5( $slug );
        $row = get_transient( $cache_key );
        
        if ( $row === false ) {
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s AND status = 1", $slug ) );
            if ( $row ) {
                set_transient( $cache_key, $row, HOUR_IN_SECONDS );
            }
        }
        if ( ! $row ) {
            wp_redirect( home_url() );
            exit;
        }

        $option     = get_option( 'govph_options' );
        $logo_image = ( ! empty( $option['govph_logo'] ) ? $option['govph_logo'] : get_template_directory_uri() . '/images/logo-masthead-large.png' );

        // --- Check expiration ---
        if ( ! empty( $row->expires ) && $row->expires <= current_time( 'mysql' ) ) {
            status_header( 410 );
            header( 'X-Robots-Tag: noindex, nofollow', true );

            echo '<!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Link Expired | DA-Crop Biotechnology Center</title>
            <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: "League Spartan", sans-serif;
                    background: linear-gradient(135deg, #444, #777);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    color: #fff;
                }
                .cbc-card {
                    background: #fff;
                    color: #2b2b2b;
                    border-radius: 16px;
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    max-width: 520px;
                    padding: 32px 28px;
                    animation: fadeIn 0.6s ease;
                }
                .cbc-redirect-loader {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    margin: 18px 0 14px;
                }
                .cbc-loader-spinner {
                    width: 38px;
                    height: 38px;
                    border-radius: 50%;
                    border: 4px solid rgba(26, 78, 19, 0.18);
                    border-top-color: #1a4e13;
                    animation: brmSpin 0.9s linear infinite;
                }
                .cbc-loader-text {
                    font-size: 1rem;
                    font-weight: 600;
                    color: #1a4e13;
                    margin: 0;
                }
                .cbc-logo {
                    max-width: 120px;
                    margin-bottom: 16px;
                }
                h2 {
                    color: #a00;
                    font-weight: 700;
                    font-size: 1.5rem;
                    margin-bottom: 8px;
                }
                p {
                    font-size: 1rem;
                    color: #444;
                    margin: 8px 0;
                }
                a {
                    color: #1a4e13;
                    font-weight: 600;
                    text-decoration: none;
                }
                a:hover { text-decoration: underline; }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes brmSpin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="cbc-card">
                <img src="' . esc_url( $logo_image ) . '" alt="DA-CBC Logo" class="cbc-logo">
                <h2>Link Expired</h2>
                <div class="cbc-redirect-loader" aria-live="polite" aria-label="Redirect in progress">
                    <span class="cbc-loader-spinner" aria-hidden="true"></span>
                    <p class="cbc-loader-text">Redirecting in a few moments…</p>
                </div>
                <p>Sorry, this redirect link is no longer active.</p>
                <p>You can return to the <a href="' . esc_url( home_url() ) . '">main website</a>.</p>
            </div>
        </body>
        </html>';
            exit;
        }

        // Increment click count asynchronously via transient (batch updates - flush every 50 clicks for performance)
        $click_counter_key = 'brm_clicks_batch_' . $row->id;
        $click_batch = ( get_transient( $click_counter_key ) ?: 0 ) + 1;
        set_transient( $click_counter_key, $click_batch, 3600 );
        
        // Flush batch to DB when threshold reached
        if ( $click_batch >= 50 ) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$this->table} SET clicks = clicks + %d WHERE id = %d", $click_batch, $row->id ) );
            delete_transient( $click_counter_key );
        }

        // Prevent indexing
        header( 'X-Robots-Tag: noindex, nofollow', true );

        // --- Custom OG Meta ---
        $og_title       = ! empty( $row->og_title ) ? $row->og_title : 'Redirecting | DA-Crop Biotechnology Center';
        $og_description = ! empty( $row->og_description ) ? $row->og_description : 'Redirecting to a verified DA-CBC resource.';
        $og_image       = ! empty( $row->og_image ) ? $row->og_image : esc_url( $logo_image );

        status_header( 200 );
        echo '<!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>' . esc_html( $og_title ) . '</title>
            <meta property="og:title" content="' . esc_attr( $og_title ) . '">
            <meta property="og:description" content="' . esc_attr( $og_description ) . '">
            <meta property="og:image" content="' . esc_url( $og_image ) . '">
            <meta property="og:url" content="' . esc_url( home_url( '/go/' . $slug ) ) . '">
            <meta property="og:type" content="website">
            <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: "League Spartan", sans-serif;
                    background: linear-gradient(135deg, #2b7a0b, #79c143);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    color: #fff;
                }
                .cbc-card {
                    background: #fff;
                    color: #2b2b2b;
                    border-radius: 16px;
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    max-width: 520px;
                    padding: 32px 28px;
                    animation: fadeIn 0.6s ease;
                }
                .cbc-redirect-loader {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    margin: 18px 0 14px;
                }
                .cbc-loader-spinner {
                    width: 38px;
                    height: 38px;
                    border-radius: 50%;
                    border: 4px solid rgba(26, 78, 19, 0.18);
                    border-top-color: #1a4e13;
                    animation: brmSpin 0.9s linear infinite;
                }
                .cbc-loader-text {
                    font-size: 1rem;
                    font-weight: 600;
                    color: #1a4e13;
                    margin: 0;
                }
                .cbc-logo {
                    max-width: 120px;
                    margin-bottom: 16px;
                }
                h2 {
                    color: #1a4e13;
                    font-weight: 700;
                    font-size: 1.5rem;
                    margin-bottom: 8px;
                }
                p {
                    font-size: 1rem;
                    color: #444;
                    margin: 8px 0;
                }
                a {
                    color: #1a4e13;
                    font-weight: 600;
                    text-decoration: none;
                }
                a:hover { text-decoration: underline; }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes brmSpin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="cbc-card">
                <img src="' . esc_url( $logo_image ) . '" alt="DA-CBC Logo" class="cbc-logo">
                <h2>Redirecting to External Link</h2>
                <div class="cbc-redirect-loader" aria-live="polite" aria-label="Redirect in progress">
                    <span class="cbc-loader-spinner" aria-hidden="true"></span>
                    <p class="cbc-loader-text">Redirecting in a few moments…</p>
                </div>
                <p>If you’re not redirected, <a href="' . esc_url( $row->target_url ) . '">click here</a>.</p>
                <p>' . number_format_i18n( $row->clicks ) . ' link visits</p>
            </div>
        </body>
        </html>';

        echo '<meta http-equiv="refresh" content="2;url=' . esc_attr( $row->target_url ) . '">';
        exit;
    }

    /* Admin menu, pages, and settings */
    public function admin_menu() {
        add_menu_page( 'GoLink Manager', 'GoLink Manager', 'manage_options', 'brm_redirects', array(
                $this,
                'admin_page_list'
        ), 'dashicons-admin-links', 58 );
        add_submenu_page( 'brm_redirects', 'Add New', 'Add New', 'manage_options', 'brm_add', array(
                $this,
                'admin_page_add'
        ) );
        add_submenu_page( 'brm_redirects', 'Settings', 'Settings', 'manage_options', 'brm_settings', array(
                $this,
                'admin_page_settings'
        ) );
    }

    public function register_settings() {
        register_setting( 'brm_options_group', 'brm_public_access' );
        add_settings_section( 'brm_general_section', 'General Settings', array( $this, 'general_settings_section_callback' ), 'brm_settings_page' );
        add_settings_field( 'brm_public_access_field', 'Public Link Creation', array(
                $this,
                'public_access_field_callback'
        ), 'brm_settings_page', 'brm_general_section' );
    }

    public function general_settings_section_callback() {
        echo '<p class="description">Configure GoLink access behavior for public users.</p>';
    }

    public function public_access_field_callback() {
        $option = get_option( 'brm_public_access', 'private' );
        ?>
        <select name="brm_public_access">
            <option value="private" <?php selected( $option, 'private' ); ?>>Private (Admin only)</option>
            <option value="public" <?php selected( $option, 'public' ); ?>>Public (Allow frontend shortcode)</option>
        </select>
        <p class="description">Controls whether the `[brm_create_link_form]` shortcode will display the creation form.</p>
        <?php
    }

    public function admin_page_settings() {
        ?>
        <div class="wrap">
            <h1>GoLink Manager Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'brm_options_group' );
                do_settings_sections( 'brm_settings_page' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue Admin CSS and JS files.
     */
    public function admin_assets( $hook ) {
        // Only load assets on pages related to the BRM plugin (e.g., admin.php?page=brm_...)
        if ( strpos( $hook, 'brm' ) === false ) {
            return;
        }

        wp_enqueue_media();

        // 1. Enqueue CSS
        wp_enqueue_style( 'brm-admin-style', plugins_url( 'brm-admin.css', __FILE__ ), array(), '1.1' );

        // 2. Enqueue JavaScript for Admin functionality (Copy button, etc.)
        wp_enqueue_script(
                'brm-admin-script',
                plugins_url( 'brm-admin-script.js', __FILE__ ),
                array( 'jquery' ),
                '1.1',
                true
        );

        // 3. Enqueue JavaScript for AJAX form functionality
        wp_enqueue_script(
            'brm-ajax-form-script',
            plugins_url( 'brm-ajax-form-script.js', __FILE__ ),
            array( 'jquery' ),
            '1.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'brm-ajax-form-script',
            'brm_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'brm_save' ),
                'base_url' => site_url('/go/'),
            )
        );

        wp_localize_script(
            'brm-admin-script',
            'brm_admin_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'brm_regenerate_qr' ),
            )
        );

        wp_add_inline_script(
            'brm-admin-script',
            "jQuery(function($){\n\tvar frame;\n\tvar imageField = $('#og_image');\n\tvar previewWrap = $('#brm-og-image-preview');\n\tvar previewImg = $('#brm-og-image-preview img');\n\tfunction setPreview(url){\n\t\tif (!previewWrap.length) { return; }\n\t\tif (url) {\n\t\t\tpreviewImg.attr('src', url);\n\t\t\tpreviewWrap.removeClass('hidden').show();\n\t\t} else {\n\t\t\tpreviewImg.attr('src', '');\n\t\t\tpreviewWrap.addClass('hidden').hide();\n\t\t}\n\t}\n\t$(document).on('click', '#brm-og-image-select', function(e){\n\t\te.preventDefault();\n\t\tif (frame) {\n\t\t\tframe.open();\n\t\t\treturn;\n\t\t}\n\t\tframe = wp.media({\n\t\t\ttitle: 'Select OG Image',\n\t\t\tbutton: { text: 'Use this image' },\n\t\t\tmultiple: false,\n\t\t\tlibrary: { type: 'image' }\n\t\t});\n\t\tframe.on('select', function(){\n\t\t\tvar attachment = frame.state().get('selection').first().toJSON();\n\t\t\tif (attachment && attachment.url) {\n\t\t\t\timageField.val(attachment.url).trigger('change');\n\t\t\t\tsetPreview(attachment.url);\n\t\t\t}\n\t\t});\n\t\tframe.open();\n\t});\n\t$(document).on('click', '#brm-og-image-clear', function(e){\n\t\te.preventDefault();\n\t\timageField.val('').trigger('change');\n\t\tsetPreview('');\n\t});\n\tif (imageField.length) {\n\t\tsetPreview(imageField.val());\n\t\timageField.on('input change', function(){ setPreview($(this).val()); });\n\t}\n});",
            'after'
        );
    }

    /**
     * Enqueue Frontend CSS and JS files.
     */
    public function frontend_assets() {
        // 1. Enqueue CSS for the form
        wp_enqueue_style( 'brm-frontend-style', plugins_url( 'brm-frontend.css', __FILE__ ), array(), '1.1' );

        // 2. Enqueue JavaScript for AJAX form functionality
        wp_enqueue_script(
            'brm-ajax-form-script',
            plugins_url( 'brm-ajax-form-script.js', __FILE__ ),
            array( 'jquery' ),
            '1.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'brm-ajax-form-script',
            'brm_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'brm_save' ),
                'base_url' => site_url('/go/'),
            )
        );
    }

    /* Admin list page */
    public function admin_page_list() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        // Pagination: 50 links per page
        $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $per_page = 50;
        $offset = ( $paged - 1 ) * $per_page;
        
        // Total count for pagination
        $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table}" ) );
        $total_pages = max( 1, ceil( $total / $per_page ) );
        
        // Fetch paginated results
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} ORDER BY created DESC LIMIT %d OFFSET %d", $per_page, $offset ) );

        $base_url = site_url( '/go/' );
        ?>
        <div class="wrap brm-admin-list">
            <h1>GoLink Manager
                <a href="<?php echo admin_url( 'admin.php?page=brm_add' ); ?>"
                   class="page-title-action brm-add-button">
                    Add New
                </a>
            </h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th scope="col" class="brm-col-slug">Slug</th>
                    <th scope="col" class="brm-col-target">Target URL</th>
                    <th scope="col" class="brm-col-clicks">Clicks</th>
                    <th scope="col" class="brm-col-expires">Expires</th>
                    <th scope="col" class="brm-col-public">From Public</th>
                    <th scope="col" class="brm-col-qr">QR Code</th>
                    <th scope="col" class="brm-col-actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ( $rows ): foreach ( $rows as $r ):
                    $short_url = esc_url( $base_url . $r->slug );
                    ?>
                    <tr>
                        <td data-colname="Slug"><code class="brm-slug-code"><?php echo esc_html( $r->slug ); ?></code></td>
                        <td data-colname="Target URL" class="brm-target-url"><?php echo esc_html( $r->target_url ); ?></td>
                        <td data-colname="Clicks"><?php echo number_format_i18n( $r->clicks ); ?></td>
                        <td data-colname="Expires"><?php echo $r->expires ? esc_html( $r->expires ) : '-'; ?></td>
                        <td data-colname="From Public"><?php echo $r->is_public ? 'Yes' : 'No'; ?></td>
                        <td data-colname="QR Code" class="brm-qr-cell">
                            <?php if ( ! empty( $r->qr_code ) ): ?>
                                <img src="<?php echo esc_url( $r->qr_code ); ?>" alt="QR Code" class="brm-qr-image">
                                <a href="<?php echo esc_url( $r->qr_code ); ?>" download class="brm-download-qr">Download</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td data-colname="Actions" class="brm-actions-cell">
                            <button class="button button-small brm-copy-url" data-url="<?php echo $short_url; ?>">Copy
                            </button>
                            |
                            <a href="<?php echo $short_url; ?>" target="_blank" class="brm-action-visit">Visit</a>
                            |
                            <a href="<?php echo admin_url( 'admin.php?page=brm_add&edit=' . intval( $r->id ) ); ?>" class="brm-action-edit">Edit</a>
                            |
                            <button class="button-link brm-action-regenerate-qr" data-id="<?php echo intval($r->id); ?>">Regenerate QR</button>
                            |
                            <form class="brm-delete-form" method="post"
                                  action="<?php echo admin_url( 'admin-post.php' ); ?>">
                                <?php wp_nonce_field( 'brm_delete_' . $r->id ); ?>
                                <input type="hidden" name="action" value="brm_delete_link"/>
                                <input type="hidden" name="id" value="<?php echo intval( $r->id ); ?>"/>
                                <button class="button-link brm-action-delete" onclick="return confirm('Delete this redirect?')">Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="brm-no-redirects">No redirects found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ( $total_pages > 1 ): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => esc_html__( '&laquo; Previous', 'brm' ),
                            'next_text' => esc_html__( 'Next &raquo;', 'brm' ),
                            'total'     => $total_pages,
                            'current'   => $paged,
                        ) );
                        echo ' <span class="displaying-num">' . sprintf( esc_html__( '%d–%d of %d', 'brm' ), $offset + 1, min( $offset + $per_page, $total ), $total ) . '</span>';
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        // JS is handled by brm-admin-script.js
    }

    /* Admin add/edit page */
    public function admin_page_add() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        global $wpdb;
        $edit = null;
        if ( ! empty( $_GET['edit'] ) ) {
            $edit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", intval( $_GET['edit'] ) ) );
        }
        echo $this->get_link_creation_form( true, $edit );
    }

    /**
     * Reusable function to generate the link creation form (Admin or Public).
     *
     * @param bool $is_admin Whether the form is for the admin area (shows all fields).
     * @param object|null $edit Existing link object for editing.
     * @return string The HTML form.
     */
    private function get_link_creation_form( $is_admin, $edit = null ) {
        $current_user_is_logged_in = is_user_logged_in();
        $public_access_option = get_option( 'brm_public_access', 'private' );

        // If not admin AND public access is 'private', show message.
        if ( ! $is_admin && $public_access_option !== 'public' ) {
            return '<div class="brm-alert brm-alert-warning">GoLink is currently restricted.</div>';
        }

        // If admin and no permission, return.
        if ( $is_admin && ! current_user_can( 'manage_options' ) ) {
            return '<div class="brm-alert brm-alert-error">You do not have permission to access this page.</div>';
        }

        if ( ! function_exists( 'submit_button' ) ) {
            require_once ABSPATH . 'wp-admin/includes/template.php';
        }

        $is_edit  = $edit !== null;
        $title    = $is_edit ? 'Edit Redirect' : 'GoLink';
        $submit_btn_text = $is_edit ? 'Update GoLink' : 'Create GoLink';

        ob_start();
        ?>
        <div class="wrap brm-form-wrap overflow-x-auto <?php echo $is_admin ? 'brm-admin-form' : 'brm-public-form'; ?>">
            <h1><?php echo esc_html( $title ); ?></h1>
            <p>Shorten and customize your link using this service.</p>

            <div id="brm-form-feedback"></div>

            <?php if ( ! $is_admin && $current_user_is_logged_in ) : ?>
                <div class="brm-alert brm-alert-info">
                    **Note:** As a logged-in user, you are using the public form. Your link will be marked as a public submission.
                </div>
            <?php endif; ?>

            <form id="brm-link-form" class="brm-form">
                <input type="hidden" name="action" value="brm_save_link"/>
                <input type="hidden" name="id" value="<?php echo $is_edit ? intval( $edit->id ) : ''; ?>"/>
                <input type="hidden" name="is_public_submission" value="<?php echo $is_admin ? 0 : 1; ?>" />
                <?php wp_nonce_field( 'brm_save_link_nonce', 'brm_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="target_url">Target URL <span class="brm-required text-red-500">*</span></label></th>
                        <td>
                            <input name="target_url" type="url" id="target_url"
                                   value="<?php echo $is_edit ? esc_attr( $edit->target_url ) : ''; ?>"
                                   class="regular-text brm-input-url !m-0"
                                   placeholder="https://example.com/zoom/meeting..." required/>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="slug">Slug</label></th>
                        <td>
                            <div class="brm-slug-control flex items-center">
                                <input type="text" name="slug" id="slug"
                                       value="<?php echo esc_attr( $edit->slug ?? '' ); ?>"
                                       class="regular-text brm-input-slug !m-0"
                                       placeholder="Auto-generated if empty"/>
                                <button type="button" class="button" id="generate-slug-btn">
                                    Auto Generate
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description">GoLink Generated</label></th>
                        <td class="description">
                            <code class="text-wrap"><?php echo esc_html( site_url( '/go/' ) ); ?><span id="slug-preview" class="brm-slug-preview"><?php echo $edit ? esc_html( $edit->slug ) : ''; ?></span></code>
                        </td>
                    </tr>
                    <?php if ( $is_admin ) : // Admin-only fields ?>

                        <tr>
                            <th scope="row"><label for="expires">Expires</label></th>
                            <td>
                                <input name="expires" type="datetime-local" id="expires"
                                       value="<?php echo $is_edit && $edit->expires ? date( 'Y-m-d\TH:i', strtotime( $edit->expires ) ) : ''; ?>"
                                       class="brm-input-datetime"/>
                                <p class="description">Optional expiration date/time (local).</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select name="status" id="status" class="brm-input-select">
                                    <option value="1" <?php selected( $is_edit && $edit->status, 1 ); ?>>Active</option>
                                    <option value="0" <?php selected( $is_edit && $edit->status, 0 ); ?>>Inactive</option>
                                </select>
                            </td>
                        </tr>

                        <tr class="brm-og-meta-heading">
                            <td colspan="2"><h3>Open Graph (OG) Meta Data (Advanced)</h3></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="og_title">OG Title</label></th>
                            <td>
                                <textarea name="og_title" id="og_title" rows="2"
                                          class="large-text brm-input-textarea"><?php echo esc_attr( $edit->og_title ?? '' ); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="og_description">OG Description</label></th>
                            <td>
                                <textarea name="og_description" id="og_description" rows="3"
                                          class="large-text brm-input-textarea"><?php echo esc_textarea( $edit->og_description ?? '' ); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="og_image">OG Image URL</label></th>
                            <td>
                                <input type="text" name="og_image" id="og_image"
                                       value="<?php echo esc_url( $edit->og_image ?? '' ); ?>" class="regular-text brm-input-image-url">
                                <button type="button" class="button" id="brm-og-image-select">Select Image</button>
                                <button type="button" class="button" id="brm-og-image-clear">Clear</button>
                                <p class="description">Choose an image from the Media Library or paste a full image URL for social sharing.</p>
                                <div id="brm-og-image-preview" style="margin-top:12px;<?php echo empty( $edit->og_image ) ? 'display:none;' : ''; ?>">
                                    <img src="<?php echo esc_url( $edit->og_image ?? '' ); ?>" alt="OG image preview" style="max-width:180px;height:auto;border:1px solid #ddd;padding:4px;background:#fff;">
                                </div>
                            </td>
                        </tr>

                    <?php endif; // End admin-only fields ?>

                </table>

                <?php if ( ! $is_admin && function_exists( 'cbc_recaptcha_field' ) ) : ?>
                    <div style="margin: 20px 0; padding: 10px;">
                        <?php cbc_recaptcha_field(); ?>
                    </div>
                <?php endif; ?>

                <?php submit_button( $submit_btn_text, 'primary large brm-submit-button' ); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }


    /* Shortcode to display the public form */
    public function create_link_form_shortcode( $atts ) {
        return $this->get_link_creation_form( false );
    }

    /**
     * Unified AJAX handler for saving a link with rate limiting and nonce validation.
     */
    public function handle_ajax_save_link() {
        // Validate nonce
        if ( ! isset( $_POST['brm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brm_nonce'] ) ), 'brm_save_link_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security verification failed. Please refresh and try again.' ) );
        }

        $is_admin_submission = current_user_can( 'manage_options' );
        $is_public_submission_flag = ! empty( $_POST['is_public_submission'] ) ? intval( $_POST['is_public_submission'] ) : 0;
        $is_public_access_allowed = get_option( 'brm_public_access', 'private' ) === 'public';

        // reCAPTCHA verification for public submissions
        if ( ! $is_admin_submission && $is_public_submission_flag && function_exists( 'cbc_recaptcha_verify' ) ) {
            $recaptcha_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
            if ( ! cbc_recaptcha_verify( $recaptcha_token ) ) {
                wp_send_json_error( array( 'message' => 'reCAPTCHA verification failed. Please try again.' ) );
            }
        }

        // Rate limiting for public submissions (5 links per hour per user)
        if ( ! $is_admin_submission && is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $rate_limit_key = 'brm_public_rate_' . $user_id;
            $rate_count = get_transient( $rate_limit_key );
            if ( $rate_count >= 5 ) {
                wp_send_json_error( array( 'message' => 'Rate limit reached. You can create 5 links per hour.' ) );
            }
            set_transient( $rate_limit_key, ( $rate_count ?: 0 ) + 1, HOUR_IN_SECONDS );
        }

        if ( ! $is_admin_submission && ! $is_public_access_allowed ) {
            wp_send_json_error( array( 'message' => 'Public link creation is currently disabled.' ) );
        }

        global $wpdb;
        $id      = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $slug    = sanitize_title_with_dashes( wp_unslash( $_POST['slug'] ?? '' ) );
        $target  = esc_url_raw( trim( wp_unslash( $_POST['target_url'] ?? '' ) ) );

        if ( empty( $target ) ) {
            wp_send_json_error( array( 'message' => 'Target URL is required.' ) );
        }

        $allowed_protocols = array( 'http', 'https' );
        $parsed = wp_parse_url( $target );
        if ( ! $parsed || empty( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], $allowed_protocols ) ) {
            wp_send_json_error( array( 'message' => 'Invalid target URL protocol. Use http or https.' ) );
        }

        $site_host   = wp_parse_url( home_url(), PHP_URL_HOST );
        $target_host = wp_parse_url( $target, PHP_URL_HOST );
        if ( $target_host && $site_host && strtolower( $site_host ) === strtolower( $target_host ) ) {
            wp_send_json_error( array( 'message' => 'Target URL must point to an external host.' ) );
        }

        if ( empty( $id ) && empty( $slug ) ) {
            do {
                $slug = $this->generate_random_slug();
                $is_slug_unique = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE slug = %s", $slug ) ) === '0';
            } while ( ! $is_slug_unique );
        }

        if ( empty( $slug ) ) {
            wp_send_json_error( array( 'message' => 'Slug is required.' ) );
        }

        $existing_slug = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$this->table} WHERE slug = %s AND id != %d", $slug, $id ) );
        if ( $existing_slug ) {
            wp_send_json_error( array( 'message' => 'This slug is already in use. Please choose another.' ) );
        }

        $expires = $is_admin_submission && ! empty( $_POST['expires'] ) ? date( 'Y-m-d H:i:s', strtotime( $_POST['expires'] ) ) : null;
        $status  = $is_admin_submission && isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;
        $og_title = $is_admin_submission ? sanitize_text_field( wp_unslash( $_POST['og_title'] ?? '' ) ) : '';
        $og_description = $is_admin_submission ? sanitize_textarea_field( wp_unslash( $_POST['og_description'] ?? '' ) ) : '';
        $og_image = $is_admin_submission ? esc_url_raw( trim( wp_unslash( $_POST['og_image'] ?? '' ) ) ) : '';
        $is_public = $is_public_submission_flag && ! $is_admin_submission ? 1 : 0;

        $data = array(
            'slug'           => $slug,
            'target_url'     => $target,
            'expires'        => $expires,
            'status'         => $status,
            'og_title'       => $og_title,
            'og_description' => $og_description,
            'og_image'       => $og_image,
            'is_public'      => $is_public,
        );

        $format = array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d' );

        // Reset WordPress error state
        $wpdb->last_error = '';

        if ( $id ) {
            $result = $wpdb->update( $this->table, $data, array( 'id' => $id ), $format, array( '%d' ) );
        } else {
            $result = $wpdb->insert( $this->table, $data, $format );
            if ($result) {
                $id = $wpdb->insert_id;
            }
        }

        // After the database operation, check for errors.
        if ( $result === false || ! empty( $wpdb->last_error ) ) {
            // There was a database error.
            $db_error = $wpdb->last_error;
            error_log( 'BRM Plugin DB Error: ' . $db_error ); // Log the error
            wp_send_json_error( array( 'message' => 'A database error occurred. Please check the server logs.' ) );
        }

        // --- QR Code Generation ---
        $qr_url_path = $this->generate_and_save_qr_code( $slug, $id );
        if ( $qr_url_path ) {
            $data['qr_code'] = $qr_url_path;
        }

        $redirect_url = $is_admin_submission ? admin_url( 'admin.php?page=brm_redirects' ) : site_url( '/go/' . $slug );

        wp_send_json_success( array(
            'message' => 'Link saved successfully!',
            'redirect' => $redirect_url,
            'slug' => $slug,
            'full_url' => site_url('/go/' . $slug),
            'qr_code' => $qr_url_path,
        ) );
    }

    public function handle_ajax_regenerate_qr() {
        check_ajax_referer( 'brm_regenerate_qr', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ) );
        }

        $id = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }

        global $wpdb;
        $slug = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$this->table} WHERE id = %d", $id ) );

        if ( ! $slug ) {
            wp_send_json_error( array( 'message' => 'Link not found.' ) );
        }

        $qr_url_path = $this->generate_and_save_qr_code( $slug, $id );

        if ( $qr_url_path ) {
            wp_send_json_success( array(
                'message' => 'QR Code regenerated successfully.',
                'qr_code' => $qr_url_path,
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to generate QR code.' ) );
        }
    }

    private function generate_and_save_qr_code( $slug, $id ) {
        $qr_url = site_url('/go/' . $slug);
        $upload_dir = wp_upload_dir();
        $qr_base_dir = trailingslashit($upload_dir['basedir']) . 'qr';
        $qr_base_url = trailingslashit($upload_dir['baseurl']) . 'qr';

        if ( ! is_dir( $qr_base_dir ) ) {
            wp_mkdir_p( $qr_base_dir );
        }

        $qr_file = 'brm-qrcode-' . $slug . '.png';
        $qr_path = trailingslashit($qr_base_dir) . $qr_file;
        $qr_url_path = trailingslashit($qr_base_url) . $qr_file;

        $qr_image_url = 'https://quickchart.io/chart?cht=qr&chs=500x500&chl=' . urlencode($qr_url) . '&choe=UTF-8';
        $response = wp_remote_get( $qr_image_url, array( 'timeout' => 15, 'sslverify' => false ) );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $image_data = wp_remote_retrieve_body( $response );
            if ( $image_data && file_put_contents( $qr_path, $image_data ) ) {
                global $wpdb;
                $wpdb->update( $this->table, array('qr_code' => $qr_url_path), array('id' => $id), array('%s'), array('%d') );
                return $qr_url_path;
            }
        }
        return false;
    }

    /* Delete link handler (No change needed) */
    public function admin_delete_link() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        $id = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        check_admin_referer( 'brm_delete_' . $id );
        if ( $id ) {
            global $wpdb;
            $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
        }
        wp_redirect( admin_url( 'admin.php?page=brm_redirects' ) );
        exit;
    }
}

// Instantiate the class
BRM_Plugin::instance();

