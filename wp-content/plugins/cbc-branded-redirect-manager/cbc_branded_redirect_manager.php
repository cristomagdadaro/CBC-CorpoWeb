<?php
/**
 * Plugin Name: CBC Branded Redirect Manager
 * Description: Create and manage branded short links that redirect to external URLs and log clicks. Adds shortlinks at /go/{slug} and an admin UI to create/manage links.
 * Version: 1.0
 * Author: Cristo Rey C. Magdadaro
 * Text Domain: branded-redirect-manager
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

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_post_brm_save_link', array( $this, 'admin_save_link' ) );
        add_action( 'admin_post_brm_delete_link', array( $this, 'admin_delete_link' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }

    /* Activation - create DB table and flush rules */
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
            og_image VARCHAR(255) DEFAULT NULL;
            qr_code VARCHAR(255) DEFAULT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires DATETIME NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY(id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // flush rewrite rules
        $this->add_rewrite();
        flush_rewrite_rules();
    }

    public function deactivate() {
        // flush rewrite rules
        flush_rewrite_rules();
    }

    /* Add rewrite rule for /go/{slug} */
    public function add_rewrite() {
        add_rewrite_rule( '^go/([^/]+)/?$', 'index.php?brm_redirect=$matches[1]', 'top' );
        add_rewrite_tag( '%brm_redirect%', '([^&]+)' );
    }

    /* Handle front-end redirect */
    public function handle_redirect() {
        $slug = get_query_var( 'brm_redirect' );
        if ( empty( $slug ) ) {
            return;
        }

        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s AND status = 1", $slug ) );
        if ( ! $row ) {
            wp_redirect( home_url() );
            exit;
        }

        $option     = get_option( 'govph_options' );
        $logo_image = ( ! empty( $option['govph_logo'] ) ? $option['govph_logo'] : get_template_directory_uri() . '/images/logo-masthead-large.png' );

        // --- Check expiration ---
        if ( ! empty( $row->expires ) && $row->expires <= current_time( 'mysql' ) ) {
            status_header( 410 ); // 410 Gone (SEO-friendly for expired links)
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
                .footer {
                    margin-top: 16px;
                    font-size: 12px;
                    color: #777;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>
        </head>
        <body>
            <div class="cbc-card">
                <img src="' . esc_url( $logo_image ) . '" alt="DA-CBC Logo" class="cbc-logo">
                <h2>Link Expired</h2>
                <p>Sorry, this redirect link is no longer active.</p>
                <p>You can return to the <a href="' . esc_url( home_url() ) . '">main website</a>.</p>
            </div>
        </body>
        </html>';
            exit;
        }

        // Increment click count
        $wpdb->query( $wpdb->prepare( "UPDATE {$this->table} SET clicks = clicks + 1 WHERE id = %d", $row->id ) );

        // Prevent indexing
        header( 'X-Robots-Tag: noindex, nofollow', true );

        // --- Custom OG Meta ---
        $og_title       = ! empty( $row->og_title ) ? $row->og_title : 'Redirecting | DA-Crop Biotechnology Center';
        $og_description = ! empty( $row->og_description ) ? $row->og_description : 'Redirecting to a verified DA-CBC resource.';
        $og_image       = ! empty( $row->og_image ) ? $row->og_image : esc_html( $logo_image );

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
                .footer {
                    margin-top: 16px;
                    font-size: 12px;
                    color: #777;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>
        </head>
        <body>
            <div class="cbc-card">
                <img src="' . esc_url( $logo_image ) . '" alt="DA-CBC Logo" class="cbc-logo">
                <h2>Redirecting to External Link</h2>
                <p>If you’re not redirected, <a href="' . esc_url( $row->target_url ) . '">click here</a>.</p>
                <p>' . $row->clicks . ' link visits</p>
            </div>
        </body>
        </html>';

        // Uncomment this for live mode
        echo '<meta http-equiv="refresh" content="2;url=' . esc_attr( $row->target_url ) . '">';
        exit;
    }

    /* Admin menu and pages */
    public function admin_menu() {
        add_menu_page( 'Redirect Manager', 'Redirect Manager', 'manage_options', 'brm_redirects', array(
                $this,
                'admin_page_list'
        ), 'dashicons-admin-links', 58 );
        add_submenu_page( 'brm_redirects', 'Add New', 'Add New', 'manage_options', 'brm_add', array(
                $this,
                'admin_page_add'
        ) );
    }

    public function admin_assets( $hook ) {
        if ( strpos( $hook, 'brm' ) === false ) {
            return;
        }
        wp_enqueue_style( 'brm-admin', plugins_url( 'admin.css', __FILE__ ) );
    }

    /* Admin list page */
    public function admin_page_list() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY created DESC" );

        // Get the site URL for the shortlink base
        $base_url = site_url( '/go/' );
        ?>
        <div class="wrap">
            <h1>Redirect Manager<a href="<?php echo admin_url( 'admin.php?page=brm_add' ); ?>" class="page-title-action">Add New</a></h1>
            <table class="widefat fixed striped">
                <thead>
                <tr>
                    <th>Slug</th>
                    <th>Target URL</th>
                    <th>Clicks</th>
                    <th>Expires</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ( $rows ): foreach ( $rows as $r ):
                    // Construct the full short URL
                    $short_url = esc_url( $base_url . $r->slug );
                    ?>
                    <tr>
                        <td><code><?php echo esc_html( $r->slug ); ?></code></td>
                        <td style="max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html( $r->target_url ); ?></td>
                        <td><?php echo number_format_i18n( $r->clicks ); ?></td>
                        <td><?php echo $r->expires ? esc_html( $r->expires ) : '-'; ?></td>
                        <td style="display: flex; flex-direction: row; align-items: center; gap: 8px;">
                            <?php if ( ! empty( $r->qr_code ) ): ?>
                                <img src="<?php echo esc_url( $r->qr_code ); ?>" alt="QR Code" width="64" height="64">
                                <a href="<?php echo esc_url( $r->qr_code ); ?>" download class="button button-small">Download</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button button-small brm-copy-url" data-url="<?php echo $short_url; ?>">Copy
                            </button>
                            |
                            <a href="<?php echo $short_url; ?>" target="_blank">Visit</a>
                            |
                            <a href="<?php echo admin_url( 'admin.php?page=brm_add&edit=' . intval( $r->id ) ); ?>">Edit</a>
                            |
                            <form style="display:inline" method="post"
                                  action="<?php echo admin_url( 'admin-post.php' ); ?>">
                                <?php wp_nonce_field( 'brm_delete_' . $r->id ); ?>
                                <input type="hidden" name="action" value="brm_delete_link"/>
                                <input type="hidden" name="id" value="<?php echo intval( $r->id ); ?>"/>
                                <button class="button-link" onclick="return confirm('Delete this redirect?')">Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6">No redirects found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <script>
            // JavaScript for the Copy Button functionality
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.brm-copy-url').forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const urlToCopy = this.getAttribute('data-url');

                        // Use the modern clipboard API
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(urlToCopy).then(() => {
                                const originalText = this.textContent;
                                this.textContent = 'Copied!';
                                this.classList.add('copied');
                                setTimeout(() => {
                                    this.textContent = originalText;
                                    this.classList.remove('copied');
                                }, 1500);
                            }).catch(err => {
                                // Fallback for older browsers (or if permission is denied)
                                console.error('Could not copy text: ', err);
                                // Fallback: Create a temporary textarea
                                const tempInput = document.createElement('textarea');
                                tempInput.value = urlToCopy;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand('copy');
                                document.body.removeChild(tempInput);

                                const originalText = this.textContent;
                                this.textContent = 'Copied!';
                                this.classList.add('copied');
                                setTimeout(() => {
                                    this.textContent = originalText;
                                    this.classList.remove('copied');
                                }, 1500);
                            });
                        }
                    });
                });
            });
        </script>
        <style>
            /* Basic styling for the copied state */
            .brm-copy-url.copied {
                background: #46b450 !important; /* WordPress success color */
                border-color: #3b9944 !important;
                color: #fff !important;
            }
        </style>
        <?php
    } // End admin_page_list()

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
        ?>
        <div class="wrap">
            <h1><?php echo $edit ? 'Edit Redirect' : 'Add New Redirect'; ?></h1>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <?php wp_nonce_field( 'brm_save' ); ?>
                <input type="hidden" name="action" value="brm_save_link"/>
                <input type="hidden" name="id" value="<?php echo $edit ? intval( $edit->id ) : ''; ?>"/>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="brm_slug">Slug</label></th>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="text" name="slug" id="slug" value="<?php echo esc_attr( $edit->slug ?? '' ); ?>"
                                       placeholder="Auto-generated if empty"/>
                                <span>Optionally you can</span>
                                <button type="button" class="button" id="generate-slug-btn">Auto Generate</button>
                            </div>
                            <p class="description">Short unique identifier used in the URL:
                                <code><?php echo esc_html( site_url( '/go/' ) ); ?><span
                                            id="slug-preview"><?php echo $edit ? esc_html( $edit->slug ) : ''; ?></span></code>.
                                You customize this to your preference or auto generate with the button.
                            </p></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="brm_target">Target URL</label></th>
                        <td><input name="target_url" type="url" id="brm_target"
                                   value="<?php echo $edit ? esc_attr( $edit->target_url ) : ''; ?>"
                                   class="regular-text" placeholder="https://example.com/zoom/meeting..." required/>
                            <p class="description">Full destination URL to redirect to.</p></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="brm_expires">Expires</label></th>
                        <td><input name="expires" type="datetime-local" id="brm_expires"
                                   value="<?php echo $edit && $edit->expires ? date( 'Y-m-d\TH:i', strtotime( $edit->expires ) ) : ''; ?>"/>
                            <p class="description">Optional expiration date/time (local).</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="og_title">OG Title</label></th>
                        <td><textarea type="text" name="og_title" id="og_title"
                                      class="large-text"><?php echo esc_attr( $edit->og_title ?? '' ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="og_description">OG Description</label></th>
                        <td><textarea name="og_description" id="og_description" rows="3"
                                      class="large-text"><?php echo esc_textarea( $edit->og_description ?? '' ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="og_image">OG Image URL</label></th>
                        <td><input type="text" name="og_image" id="og_image"
                                   value="<?php echo esc_url( $edit->og_image ?? '' ); ?>" class="regular-text">
                            <p class="description">Paste full image URL</p></td>
                    </tr>
                    <tr>
                        <th scope="row">Status</th>
                        <td><select name="status">
                                <option value="1" <?php selected( $edit && $edit->status, 1 ); ?>>Active</option>
                                <option value="0" <?php selected( $edit && $edit->status, 0 ); ?>>Inactive</option>
                            </select></td>
                    </tr>
                </table>

                <?php submit_button( $edit ? 'Update Redirect' : 'Create Redirect' ); ?>
            </form>
        </div>
        <script>
            (function () {
                var slug = document.getElementById('brm_slug');
                var preview = document.getElementById('slug-preview');
                if (slug) slug.addEventListener('input', function () {
                    preview.textContent = slug.value;
                });
            })();
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const slugInput = document.querySelector(`input[name="slug"]`);
                const generateBtn = document.querySelector("#generate-slug-btn");

                function generateSlug(length = 9) {
                    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                    let result = "";
                    for (let i = 0; i < length; i++) {
                        result += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return result;
                }

                if (slugInput && generateBtn) {
                    generateBtn.addEventListener("click", (e) => {
                        e.preventDefault();
                        slugInput.value = generateSlug();

                        const preview = document.getElementById('slug-preview');
                        if (preview) {
                            preview.textContent = slugInput.value;
                        }
                    });
                }
            });
        </script>
        <?php
    }

    /* Save link handler */
    public function admin_save_link() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        check_admin_referer( 'brm_save' );

        global $wpdb;
        $id      = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $slug    = sanitize_title_with_dashes( wp_unslash( $_POST['slug'] ) );
        $target  = esc_url_raw( trim( wp_unslash( $_POST['target_url'] ) ) );
        $expires = ! empty( $_POST['expires'] ) ? date( 'Y-m-d H:i:s', strtotime( $_POST['expires'] ) ) : null;
        $status  = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;
        $og_title = sanitize_text_field( wp_unslash( $_POST['og_title'] ?? '' ) ); // Sanitize title
        $og_description = sanitize_textarea_field( wp_unslash( $_POST['og_description'] ?? '' ) ); // Sanitize description
        $og_image = esc_url_raw( trim( wp_unslash( $_POST['og_image'] ?? '' ) ) ); // Sanitize URL

        // Basic validation
        if ( empty( $slug ) || empty( $target ) ) {
            wp_die( 'Slug and target URL are required.' );
        }

        // Prevent open redirect to local admin pages or site URL (optional)
        $allowed_protocols = array( 'http', 'https' );
        $parsed            = wp_parse_url( $target );
        if ( ! $parsed || empty( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], $allowed_protocols ) ) {
            wp_die( 'Invalid target URL protocol. Use http or https.' );
        }

        // Prevent target to the same site path (you may choose to allow this)
        $site_host   = wp_parse_url( home_url(), PHP_URL_HOST );
        $target_host = wp_parse_url( $target, PHP_URL_HOST );
        if ( $target_host && $site_host && strtolower( $site_host ) === strtolower( $target_host ) ) {
            // disallow redirecting to same host to avoid loops (change if you prefer to allow)
            wp_die( 'Target URL must point to an external host.' );
        }

        if ( $id ) {
            $data = array(
                    'slug'           => $slug,
                    'target_url'     => $target,
                    'expires'        => $expires,
                    'status'         => $status,
                    'og_title'       => $og_title,
                    'og_description' => $og_description,
                    'og_image'       => $og_image,
            );

            $format = array( '%s', '%s', '%s', '%d', '%s', '%s', '%s' ); // Add '%s' for each new field

            $wpdb->update(
                    $this->table,
                    $data,
                    array( 'id' => $id ),
                    $format,
                    array( '%d' )
            );
        } else {
            $data = array(
                    'slug'           => $slug,
                    'target_url'     => $target,
                    'expires'        => $expires,
                    'status'         => $status,
                    'og_title'       => $og_title,
                    'og_description' => $og_description,
                    'og_image'       => $og_image,
            );

            $format = array( '%s', '%s', '%s', '%d', '%s', '%s', '%s' ); // Add '%s' for each new field

            $wpdb->insert( $this->table, $data, $format );
        }

        // --- Start QR Code Generation and Saving ---

        $qr_url = site_url('/go/' . $slug);
        $upload_dir = wp_upload_dir();

        // 1. Define the custom subdirectory path
        $qr_base_dir = trailingslashit($upload_dir['basedir']) . 'qr';
        $qr_base_url = trailingslashit($upload_dir['baseurl']) . 'qr';

        // 2. Create the directory if it doesn't exist
        if ( ! is_dir( $qr_base_dir ) ) {
            wp_mkdir_p( $qr_base_dir );
        }

        // 3. Define the file path and URL
        $qr_file = 'brm-qrcode-' . $slug . '.png';
        $qr_path = trailingslashit($qr_base_dir) . $qr_file;
        $qr_url_path = trailingslashit($qr_base_url) . $qr_file;

        // Generate QR using Google Chart API (or a library like PHP QR Code)
        // Note: Using quickchart.io as in your original code
        $qr_image = 'https://quickchart.io/chart?cht=qr&chs=500x500&chl=' . urlencode($qr_url) . '&choe=UTF-8';

        // 4. Save a copy locally
        $image_data = @file_get_contents($qr_image);
        if ($image_data) {
            file_put_contents($qr_path, $image_data);
        }

        // 5. Update QR code URL in DB
        if ($id) {
            $wpdb->update(
                    $this->table,
                    array('qr_code' => $qr_url_path),
                    array('id' => $id),
                    array('%s'),
                    array('%d')
            );
        } else {
            $last_id = $wpdb->insert_id;
            $wpdb->update(
                    $this->table,
                    array('qr_code' => $qr_url_path),
                    array('id' => $last_id),
                    array('%s'),
                    array('%d')
            );
        }
        // --- End QR Code Generation and Saving ---

        // redirect back to list
        wp_redirect( admin_url( 'admin.php?page=brm_redirects' ) );
        exit;
    }

    /* Delete link handler */
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

BRM_Plugin::instance();

// Add query var so WordPress populates it
function brm_query_vars( $vars ) {
    $vars[] = 'brm_redirect';

    return $vars;
}

add_filter( 'query_vars', 'brm_query_vars' );

?>
