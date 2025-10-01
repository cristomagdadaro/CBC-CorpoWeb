<?php
/**
 * Plugin Name: CBC Client Engagement (Appointments & Feedback)
 * Description: Provides front-end forms for clients to book appointments and send feedback. Adds an admin panel to manage submissions.
 * Version: 1.0.0
 * Author: Cristo Rey C. Magdadaro
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Client_Engagement {
    const APPOINTMENT_POST_TYPE = 'cbc_appointment';
    const FEEDBACK_POST_TYPE    = 'cbc_feedback';
    const INTERNSHIP_POST_TYPE  = 'cbc_internship';

    public function __construct() {
        // Register post types
        add_action('init', [$this, 'register_post_types']);

        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Shortcodes
        add_shortcode('cbc_appointment_form', [$this, 'render_appointment_form']);
        add_shortcode('cbc_feedback_form', [$this, 'render_feedback_form']);
        add_shortcode('cbc_internship_form', [$this, 'render_internship_form']);
        // Combined page shortcode (renders all forms)
        add_shortcode('cbc_client_engagement_page', [$this, 'render_client_engagement_page']);
        // Events listing shortcodes
        add_shortcode('cbc_events_list', [$this, 'render_events_list']);
        add_shortcode('cbc_events_section', [$this, 'render_events_section']);
    // Reusable section header (shortcode)
    add_shortcode('govph_section_header', [$this, 'render_section_header_shortcode']);

        // Form handlers (admin-post)
        add_action('admin_post_nopriv_cbc_submit_appointment', [$this, 'handle_submit_appointment']);
        add_action('admin_post_cbc_submit_appointment',        [$this, 'handle_submit_appointment']);
        add_action('admin_post_nopriv_cbc_submit_feedback',    [$this, 'handle_submit_feedback']);
        add_action('admin_post_cbc_submit_feedback',           [$this, 'handle_submit_feedback']);
        add_action('admin_post_nopriv_cbc_submit_internship',  [$this, 'handle_submit_internship']);
        add_action('admin_post_cbc_submit_internship',         [$this, 'handle_submit_internship']);

        // Admin menu
        add_action('admin_menu', [$this, 'register_admin_menu']);

    // Handler for one-off admin bulk action to enable comments
    add_action('admin_post_cbc_enable_comments_run', [$this, 'handle_enable_comments_run']);

        // Admin columns
        add_filter('manage_' . self::APPOINTMENT_POST_TYPE . '_posts_columns', [$this, 'appt_columns']);
        add_action('manage_' . self::APPOINTMENT_POST_TYPE . '_posts_custom_column', [$this, 'appt_column_content'], 10, 2);
        add_filter('manage_' . self::FEEDBACK_POST_TYPE . '_posts_columns', [$this, 'fb_columns']);
        add_action('manage_' . self::FEEDBACK_POST_TYPE . '_posts_custom_column', [$this, 'fb_column_content'], 10, 2);
        add_filter('manage_' . self::INTERNSHIP_POST_TYPE . '_posts_columns', [$this, 'intern_columns']);
        add_action('manage_' . self::INTERNSHIP_POST_TYPE . '_posts_custom_column', [$this, 'intern_column_content'], 10, 2);

        // Meta boxes
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        // Save disabled (we treat as submitted entries) but keep hook for possible future use
        add_action('save_post', [$this, 'prevent_unintended_save'], 10, 2);

    // Ensure events have comments enabled by default on creation
    add_action('save_post', [$this, 'cbc_set_event_comments_default'], 20, 3);

        // Basic styles for forms
        add_action('wp_enqueue_scripts', function() {
            wp_register_style('cbc-client-engagement', plugins_url('css/forms.css', __FILE__), [], '1.0.0');
        });
    }

    public function activate() {
        $this->register_post_types();
        flush_rewrite_rules();
        // Ensure default event types exist
        if ( function_exists('wp_insert_term') ) {
            $terms = [
                'event'    => 'Event',
                'training' => 'Training',
                'seminar'  => 'Seminar',
                'holiday'  => 'Holiday',
            ];
            foreach ($terms as $slug => $name) {
                if (!term_exists($slug, 'cbc_event_type')) {
                    wp_insert_term($name, 'cbc_event_type', ['slug' => $slug]);
                }
            }
        }
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function register_post_types() {
        // Appointments
        register_post_type(self::APPOINTMENT_POST_TYPE, [
            'labels' => [
                'name'               => __('Appointments', 'cbc'),
                'singular_name'      => __('Appointment', 'cbc'),
                'menu_name'          => __('Appointments', 'cbc'),
                'add_new_item'       => __('Add Appointment', 'cbc'),
                'edit_item'          => __('View Appointment', 'cbc'),
                'view_item'          => __('View Appointment', 'cbc'),
                'search_items'       => __('Search Appointments', 'cbc'),
                'not_found'          => __('No appointments found', 'cbc'),
                'not_found_in_trash' => __('No appointments found in Trash', 'cbc'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // We will attach under our custom top-level menu
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title'],
        ]);

        // Feedback
        register_post_type(self::FEEDBACK_POST_TYPE, [
            'labels' => [
                'name'               => __('Feedback', 'cbc'),
                'singular_name'      => __('Feedback', 'cbc'),
                'menu_name'          => __('Feedback', 'cbc'),
                'add_new_item'       => __('Add Feedback', 'cbc'),
                'edit_item'          => __('View Feedback', 'cbc'),
                'view_item'          => __('View Feedback', 'cbc'),
                'search_items'       => __('Search Feedback', 'cbc'),
                'not_found'          => __('No feedback found', 'cbc'),
                'not_found_in_trash' => __('No feedback found in Trash', 'cbc'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title'],
        ]);

        // Internship Applications
        register_post_type(self::INTERNSHIP_POST_TYPE, [
            'labels' => [
                'name'               => __('Internship Applications', 'cbc'),
                'singular_name'      => __('Internship Application', 'cbc'),
                'menu_name'          => __('Internship', 'cbc'),
                'add_new_item'       => __('Add Application', 'cbc'),
                'edit_item'          => __('View Application', 'cbc'),
                'view_item'          => __('View Application', 'cbc'),
                'search_items'       => __('Search Applications', 'cbc'),
                'not_found'          => __('No applications found', 'cbc'),
                'not_found_in_trash' => __('No applications found in Trash', 'cbc'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title'],
        ]);

        // Events (for Event / Training / Seminar / Holiday)
        register_post_type('cbc_event', [
            'labels' => [
                'name'               => __('Events', 'cbc'),
                'singular_name'      => __('Event', 'cbc'),
                'menu_name'          => __('Events', 'cbc'),
                'add_new_item'       => __('Add Event', 'cbc'),
                'edit_item'          => __('Edit Event', 'cbc'),
                'view_item'          => __('View Event', 'cbc'),
                'search_items'       => __('Search Events', 'cbc'),
                'not_found'          => __('No events found', 'cbc'),
                'not_found_in_trash' => __('No events found in Trash', 'cbc'),
            ],
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title','editor','excerpt','custom-fields','comments'],
            // Allow block-based comments form to work via REST
            'show_in_rest'       => true,
            'has_archive'        => true,
        ]);

        // Event Type taxonomy (event, training, seminar, holiday)
        register_taxonomy('cbc_event_type', ['cbc_event'], [
            'labels' => [
                'name' => __('Event Types', 'cbc'),
                'singular_name' => __('Event Type', 'cbc'),
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
        ]);
    }

    public function register_admin_menu() {
        $cap = 'edit_posts';
        add_menu_page(
            __('Clients', 'cbc'),
            __('Clients', 'cbc'),
            $cap,
            'cbc-client-engagement',
            [$this, 'render_dashboard_page'],
            'dashicons-calendar-alt',
            26
        );

        // Submenu: Dashboard
        add_submenu_page('cbc-client-engagement', __('Dashboard', 'cbc'), __('Dashboard', 'cbc'), $cap, 'cbc-client-engagement', [$this, 'render_dashboard_page']);

        // Submenu: Appointments (link to CPT list)
        add_submenu_page('cbc-client-engagement', __('Appointments', 'cbc'), __('Appointments', 'cbc'), $cap, 'edit.php?post_type=' . self::APPOINTMENT_POST_TYPE);

        // Submenu: Feedback (link to CPT list)
        add_submenu_page('cbc-client-engagement', __('Feedback', 'cbc'), __('Feedback', 'cbc'), $cap, 'edit.php?post_type=' . self::FEEDBACK_POST_TYPE);

        // Submenu: Internship Applications (link to CPT list)
        add_submenu_page('cbc-client-engagement', __('Internship Applications', 'cbc'), __('Internship Applications', 'cbc'), $cap, 'edit.php?post_type=' . self::INTERNSHIP_POST_TYPE);

        // Submenu: Events
        add_submenu_page('cbc-client-engagement', __('Events', 'cbc'), __('Events', 'cbc'), $cap, 'edit.php?post_type=cbc_event');

        // Submenu: One-off tools
        add_submenu_page('cbc-client-engagement', __('Tools', 'cbc'), __('Tools', 'cbc'), $cap, 'cbc-client-engagement-tools', [$this, 'render_tools_page']);
    }

    /**
     * Ensure comments are enabled by default for newly created events.
     * Runs on save_post and only acts when a new post is inserted and it's a cbc_event.
     */
    public function cbc_set_event_comments_default($post_id, $post, $update) {
        // Only act for our events
        if ($post->post_type !== 'cbc_event') {
            return;
        }

        // Only act on first insert, not updates
        if ($update) {
            return;
        }

        // Skip autosaves and revisions
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Ensure comment status is open
        if ( 'open' !== $post->comment_status ) {
            // Use wp_update_post safely to avoid infinite loop: remove this action for update
            remove_action('save_post', [$this, 'cbc_set_event_comments_default'], 20);
            wp_update_post( [ 'ID' => $post_id, 'comment_status' => 'open' ] );
            // Re-add action
            add_action('save_post', [$this, 'cbc_set_event_comments_default'], 20, 3);
        }
    }

    public function render_dashboard_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $appt_count = wp_count_posts(self::APPOINTMENT_POST_TYPE);
        $fb_count   = wp_count_posts(self::FEEDBACK_POST_TYPE);
        $intern_count = wp_count_posts(self::INTERNSHIP_POST_TYPE);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Clients', 'cbc'); ?></h1>
            <p>Use the Appointments and Feedback submenus to manage entries. Below is a quick summary.</p>
            <div class="cbc-cards" style="display:flex; gap:20px; margin-top:20px;">
                <div class="card" style="padding:16px; border:1px solid #ddd; background:#fff; width:280px;">
                    <h2>Appointments</h2>
                    <p><strong>Total:</strong> <?php echo intval($appt_count->publish + $appt_count->draft + $appt_count->pending + $appt_count->private); ?></p>
                    <p><a class="button button-primary" href="<?php echo admin_url('edit.php?post_type=' . self::APPOINTMENT_POST_TYPE); ?>">Manage Appointments</a></p>
                </div>
                <div class="card" style="padding:16px; border:1px solid #ddd; background:#fff; width:280px;">
                    <h2>Feedback</h2>
                    <p><strong>Total:</strong> <?php echo intval($fb_count->publish + $fb_count->draft + $fb_count->pending + $fb_count->private); ?></p>
                    <p><a class="button button-primary" href="<?php echo admin_url('edit.php?post_type=' . self::FEEDBACK_POST_TYPE); ?>">Manage Feedback</a></p>
                </div>
                <div class="card" style="padding:16px; border:1px solid #ddd; background:#fff; width:280px;">
                    <h2>Internships</h2>
                    <p><strong>Total:</strong> <?php echo intval($fb_count->publish + $fb_count->draft + $fb_count->pending + $fb_count->private); ?></p>
                    <p><a class="button button-primary" href="<?php echo admin_url('edit.php?post_type=' . self::INTERNSHIP_POST_TYPE); ?>">Manage Interns</a></p>
                </div>
            </div>
            <p style="margin-top:20px;">Embed forms using these shortcodes: <code>[cbc_appointment_form]</code>, <code>[cbc_feedback_form]</code>, and <code>[cbc_internship_form]</code>. You can also use the combined shortcode <code>[cbc_client_engagement_page]</code> to render all forms on a single page.</p>
        </div>
        <?php
    }

    /**
     * Render tools page with a nonce-protected button to enable comments on Events.
     */
    public function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $nonce = wp_create_nonce('cbc_enable_comments');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Clients Tools', 'cbc'); ?></h1>
            <p>One-off administrative tools for the Client Engagement plugin.</p>
            <h2>Enable comments for Events</h2>
            <p>This action will update existing Event posts (<code>cbc_event</code>) to open their comments where they are not already open. The operation is run in batches to avoid timeouts.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="cbc_enable_comments_run" />
                <input type="hidden" name="cbc_enable_comments_nonce" value="<?php echo esc_attr($nonce); ?>" />
                <p><button type="submit" class="button button-primary" onclick="return confirm('Are you sure you want to enable comments for Event posts? This will update multiple posts.');">Enable comments for Events</button></p>
            </form>
            <?php if ( isset( $_GET['cbc_enable_result'] ) ) :
                $result = json_decode( wp_unslash( $_GET['cbc_enable_result'] ), true );
                if ( is_array( $result ) ) : ?>
                    <h3>Results</h3>
                    <p>Updated: <?php echo intval( $result['updated'] ?? 0 ); ?></p>
                    <p>Skipped (already open): <?php echo intval( $result['skipped'] ?? 0 ); ?></p>
                <?php endif; endif; ?>
        </div>
        <?php
    }

    /**
     * Handle the admin_post action to enable comments for events.
     */
    public function handle_enable_comments_run() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions', 'cbc' ) );
        }

        $nonce = isset( $_POST['cbc_enable_comments_nonce'] ) ? wp_unslash( $_POST['cbc_enable_comments_nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'cbc_enable_comments' ) ) {
            wp_die( __( 'Nonce verification failed', 'cbc' ) );
        }

        // Query events that are not open
        $args = [
            'post_type' => 'cbc_event',
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];
        $posts = get_posts( $args );
        $updated = 0;
        $skipped = 0;

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $pid ) {
                $post = get_post( $pid );
                if ( ! $post ) {
                    continue;
                }
                if ( 'open' === $post->comment_status ) {
                    $skipped++;
                    continue;
                }
                // Update safely
                wp_update_post( [ 'ID' => $pid, 'comment_status' => 'open' ] );
                $updated++;
            }
        }

        $result = wp_json_encode( [ 'updated' => $updated, 'skipped' => $skipped ] );
        $redirect = add_query_arg( 'cbc_enable_result', rawurlencode( $result ), admin_url( 'admin.php?page=cbc-client-engagement-tools' ) );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Combined shortcode that renders appointment, feedback, and internship forms stacked.
     * Usage: [cbc_client_engagement_page]
     */
    public function render_client_engagement_page($atts = []) {
        wp_enqueue_style('cbc-client-engagement');
        $out  = '<div class="cbc-client-engagement-page">';
        $out .= '<section class="cbc-section cbc-appointment">';
        $out .= '<h2>Book an Appointment</h2>';
        $out .= $this->render_appointment_form($atts);
        $out .= '</section>';

        $out .= '<section class="cbc-section cbc-feedback">';
        $out .= '<h2>Send Feedback</h2>';
        $out .= $this->render_feedback_form($atts);
        $out .= '</section>';

        $out .= '<section class="cbc-section cbc-internship">';
        $out .= '<h2>Internship Application</h2>';
        $out .= $this->render_internship_form($atts);
        $out .= '</section>';

        $out .= '</div>';
        return $out;
    }

    /**
     * Shortcode: [cbc_events_list type="training" limit="5"]
     * Renders a simple list of events filtered by event type (slug).
     */
    public function render_events_list($atts = []) {
        $defaults = ['type' => '', 'limit' => 5];
        $atts = shortcode_atts($defaults, $atts, 'cbc_events_list');
        $type = sanitize_text_field($atts['type']);
        $limit = intval($atts['limit']);

        $args = [
            'post_type' => 'cbc_event',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
        ];
        if ($type) {
            $args['tax_query'] = [[
                'taxonomy' => 'cbc_event_type',
                'field' => 'slug',
                'terms' => $type,
            ]];
        }

        $q = new WP_Query($args);
        ob_start();
        if ($q->have_posts()) {
            echo '<ul class="cbc-events-list">';
            while ($q->have_posts()) { $q->the_post();
                $date = get_post_meta(get_the_ID(), 'event_date', true);
                echo '<li><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a>' . ($date ? ' - <small>' . esc_html($date) . '</small>' : '') . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="no-events">No events found.</p>';
        }
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Shortcode: [cbc_events_section]
     * Renders grouped sections for Event / Training / Seminar / Holiday
     */
    public function render_events_section($atts = []) {
        $types = ['event' => 'Event', 'training' => 'Training', 'seminar' => 'Seminar', 'holiday' => 'Holiday'];
        $out = '<div class="cbc-events-sections">';
        foreach ($types as $slug => $label) {
            $out .= '<section class="cbc-events-group cbc-events-' . esc_attr($slug) . '">';
            $out .= '<h3>' . esc_html($label) . '</h3>';
            $out .= do_shortcode('[cbc_events_list type="' . esc_attr($slug) . '" limit="5"]');
            $out .= '</section>';
        }
        $out .= '</div>';
        return $out;
    }

    // Shortcode: Appointment form
    public function render_appointment_form($atts = []) {
        wp_enqueue_style('cbc-client-engagement');
        $defaults = [
            'redirect' => '', // Optional redirect after submit
        ];
        $atts = shortcode_atts($defaults, $atts, 'cbc_appointment_form');

        $errors = isset($_GET['cbc_err']) ? sanitize_text_field(wp_unslash($_GET['cbc_err'])) : '';
        $success = isset($_GET['cbc_ok']) ? sanitize_text_field(wp_unslash($_GET['cbc_ok'])) : '';
        $action_url = esc_url(admin_url('admin-post.php'));
        $redirect = esc_url_raw($atts['redirect']);

        ob_start();
        ?>
        <form class="cbc-form" method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="action" value="cbc_submit_appointment" />
            <?php wp_nonce_field('cbc_submit_appointment', 'cbc_nonce'); ?>
            <?php if ($redirect) : ?><input type="hidden" name="_redirect" value="<?php echo esc_attr($redirect); ?>" /><?php endif; ?>
            <div class="cbc-row">
                <label for="cbc_name">Full Name<span class="req">*</span></label>
                <input type="text" id="cbc_name" name="name" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_email">Email<span class="req">*</span></label>
                <input type="email" id="cbc_email" name="email" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_phone">Phone</label>
                <input type="text" id="cbc_phone" name="phone" />
            </div>
            <div class="cbc-row">
                <label for="cbc_date">Preferred Date<span class="req">*</span></label>
                <input type="date" id="cbc_date" name="date" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_time">Preferred Time<span class="req">*</span></label>
                <input type="time" id="cbc_time" name="time" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_message">Message</label>
                <textarea id="cbc_message" name="message" rows="4"></textarea>
            </div>
            <div class="cbc-row">
                <button type="submit">Book Appointment</button>
            </div>
            <?php if ($errors) : ?><p class="cbc-error"><?php echo esc_html($errors); ?></p><?php endif; ?>
            <?php if ($success) : ?><p class="cbc-success"><?php echo esc_html($success); ?></p><?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }

    // Shortcode: Feedback form
    public function render_feedback_form($atts = []) {
        wp_enqueue_style('cbc-client-engagement');
        $defaults = [
            'redirect' => '',
        ];
        $atts = shortcode_atts($defaults, $atts, 'cbc_feedback_form');

        $errors = isset($_GET['cbc_err']) ? sanitize_text_field(wp_unslash($_GET['cbc_err'])) : '';
        $success = isset($_GET['cbc_ok']) ? sanitize_text_field(wp_unslash($_GET['cbc_ok'])) : '';
        $action_url = esc_url(admin_url('admin-post.php'));
        $redirect = esc_url_raw($atts['redirect']);

        ob_start();
        ?>
        <form class="cbc-form" method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="action" value="cbc_submit_feedback" />
            <?php wp_nonce_field('cbc_submit_feedback', 'cbc_nonce'); ?>
            <?php if ($redirect) : ?><input type="hidden" name="_redirect" value="<?php echo esc_attr($redirect); ?>" /><?php endif; ?>
            <div class="cbc-row">
                <label for="cbc_fb_name">Full Name<span class="req">*</span></label>
                <input type="text" id="cbc_fb_name" name="name" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_fb_email">Email<span class="req">*</span></label>
                <input type="email" id="cbc_fb_email" name="email" required />
            </div>
            <div class="cbc-row">
                <label for="cbc_rating">Rating<span class="req">*</span></label>
                <select id="cbc_rating" name="rating" required>
                    <option value="">Select...</option>
                    <option value="5">Excellent</option>
                    <option value="4">Good</option>
                    <option value="3">Average</option>
                    <option value="2">Poor</option>
                    <option value="1">Very Poor</option>
                </select>
            </div>
            <div class="cbc-row">
                <label for="cbc_fb_message">Feedback</label>
                <textarea id="cbc_fb_message" name="message" rows="4"></textarea>
            </div>
            <div class="cbc-row">
                <button type="submit">Send Feedback</button>
            </div>
            <?php if ($errors) : ?><p class="cbc-error"><?php echo esc_html($errors); ?></p><?php endif; ?>
            <?php if ($success) : ?><p class="cbc-success"><?php echo esc_html($success); ?></p><?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }

    // Shortcode: Internship application form
    public function render_internship_form($atts = []) {
        wp_enqueue_style('cbc-client-engagement');
        $defaults = [ 'redirect' => '' ];
        $atts = shortcode_atts($defaults, $atts, 'cbc_internship_form');

        $errors = isset($_GET['cbc_err']) ? sanitize_text_field(wp_unslash($_GET['cbc_err'])) : '';
        $success = isset($_GET['cbc_ok']) ? sanitize_text_field(wp_unslash($_GET['cbc_ok'])) : '';
        $action_url = esc_url(admin_url('admin-post.php'));
        $redirect = esc_url_raw($atts['redirect']);

        ob_start();
        ?>
        <form class="cbc-form" method="post" enctype="multipart/form-data" action="<?php echo $action_url; ?>">
            <input type="hidden" name="action" value="cbc_submit_internship" />
            <?php wp_nonce_field('cbc_submit_internship', 'cbc_nonce'); ?>
            <?php if ($redirect) : ?><input type="hidden" name="_redirect" value="<?php echo esc_attr($redirect); ?>" /><?php endif; ?>
            <div class="cbc-row">
                <label for="intern_name">Full Name<span class="req">*</span></label>
                <input type="text" id="intern_name" name="name" required />
            </div>
            <div class="cbc-row">
                <label for="intern_email">Email<span class="req">*</span></label>
                <input type="email" id="intern_email" name="email" required />
            </div>
            <div class="cbc-row">
                <label for="intern_phone">Phone</label>
                <input type="text" id="intern_phone" name="phone" />
            </div>
            <div class="cbc-row">
                <label for="intern_school">School/University<span class="req">*</span></label>
                <input type="text" id="intern_school" name="school" required />
            </div>
            <div class="cbc-row">
                <label for="intern_program">Program / Course<span class="req">*</span></label>
                <input type="text" id="intern_program" name="program" required />
            </div>
            <div class="cbc-row">
                <label for="intern_year">Year Level<span class="req">*</span></label>
                <input type="text" id="intern_year" name="year_level" placeholder="e.g., 3rd Year" required />
            </div>
            <div class="cbc-row">
                <label for="intern_letter_file">Letter of Intent<span class="req">*</span></label>
                <input type="file" id="intern_letter_file" name="letter_file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required />
                <p class="description">Allowed file types: PDF, DOC, DOCX. Max 10 MB.</p>
            </div>
            <div class="cbc-row">
                <button type="submit">Submit Application</button>
            </div>
            <?php if ($errors) : ?><p class="cbc-error"><?php echo esc_html($errors); ?></p><?php endif; ?>
            <?php if ($success) : ?><p class="cbc-success"><?php echo esc_html($success); ?></p><?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }

    public function handle_submit_appointment() {
        if (!isset($_POST['cbc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cbc_nonce'])), 'cbc_submit_appointment')) {
            $this->redirect_with_message('Invalid request.', false);
        }

        $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $date    = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
        $time    = isset($_POST['time']) ? sanitize_text_field(wp_unslash($_POST['time'])) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

        if (empty($name) || empty($email) || empty($date) || empty($time)) {
            $this->redirect_with_message('Please fill in all required fields.', false);
        }

        $post_id = wp_insert_post([
            'post_type'   => self::APPOINTMENT_POST_TYPE,
            'post_title'  => $name . ' - ' . $date . ' ' . $time,
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) {
            $this->redirect_with_message('Could not save your appointment. Please try again later.', false);
        }

        update_post_meta($post_id, 'cbc_name', $name);
        update_post_meta($post_id, 'cbc_email', $email);
        update_post_meta($post_id, 'cbc_phone', $phone);
        update_post_meta($post_id, 'cbc_date', $date);
        update_post_meta($post_id, 'cbc_time', $time);
        update_post_meta($post_id, 'cbc_message', $message);

        $this->redirect_with_message('Thank you! Your appointment has been submitted.', true);
    }

    public function handle_submit_feedback() {
        if (!isset($_POST['cbc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cbc_nonce'])), 'cbc_submit_feedback')) {
            $this->redirect_with_message('Invalid request.', false);
        }

        $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $rating  = isset($_POST['rating']) ? intval(wp_unslash($_POST['rating'])) : 0;
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

        if (empty($name) || empty($email) || $rating < 1 || $rating > 5) {
            $this->redirect_with_message('Please fill in all required fields.', false);
        }

        $post_id = wp_insert_post([
            'post_type'   => self::FEEDBACK_POST_TYPE,
            'post_title'  => $name . ' - Rating: ' . $rating,
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) {
            $this->redirect_with_message('Could not save your feedback. Please try again later.', false);
        }

        update_post_meta($post_id, 'cbc_name', $name);
        update_post_meta($post_id, 'cbc_email', $email);
        update_post_meta($post_id, 'cbc_rating', $rating);
        update_post_meta($post_id, 'cbc_message', $message);

        $this->redirect_with_message('Thank you for your feedback!', true);
    }

    public function handle_submit_internship() {
        if (!isset($_POST['cbc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cbc_nonce'])), 'cbc_submit_internship')) {
            $this->redirect_with_message('Invalid request.', false);
        }
        $name       = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email      = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone      = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $school     = isset($_POST['school']) ? sanitize_text_field(wp_unslash($_POST['school'])) : '';
        $program    = isset($_POST['program']) ? sanitize_text_field(wp_unslash($_POST['program'])) : '';
        $year_level = isset($_POST['year_level']) ? sanitize_text_field(wp_unslash($_POST['year_level'])) : '';

        // Validate file upload for Letter of Intent
        if (!isset($_FILES['letter_file']) || empty($_FILES['letter_file']['name'])) {
            $this->redirect_with_message('Please upload your Letter of Intent (PDF/DOC/DOCX).', false);
        }
        $file = $_FILES['letter_file'];
        if (!empty($file['error'])) {
            $this->redirect_with_message('File upload error. Please try again.', false);
        }
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB
            $this->redirect_with_message('File too large. Maximum size is 10 MB.', false);
        }
        $allowed_exts = ['pdf','doc','docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts, true)) {
            $this->redirect_with_message('Invalid file type. Allowed: PDF, DOC, DOCX.', false);
        }

        // Handle upload
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $overrides = ['test_form' => false];
        $uploaded = wp_handle_upload($file, $overrides);
        if (isset($uploaded['error'])) {
            $this->redirect_with_message('Upload failed: ' . $uploaded['error'], false);
        }

        $title = $name . ' - ' . $school . ' (' . $program . ')';
        $post_id = wp_insert_post([
            'post_type'   => self::INTERNSHIP_POST_TYPE,
            'post_title'  => $title,
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) {
            // Cleanup uploaded file if post creation failed
            @unlink($uploaded['file']);
            $this->redirect_with_message('Could not save your application. Please try again later.', false);
        }

        // Insert as attachment to media library and attach to this post
        $filetype = wp_check_filetype(basename($uploaded['file']), null);
        $attachment = [
            'guid'           => $uploaded['url'],
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name(basename($uploaded['file'])),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];
        $attach_id = wp_insert_attachment($attachment, $uploaded['file'], $post_id);
        if (!is_wp_error($attach_id)) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }

        update_post_meta($post_id, 'cbc_name', $name);
        update_post_meta($post_id, 'cbc_email', $email);
        update_post_meta($post_id, 'cbc_phone', $phone);
        update_post_meta($post_id, 'cbc_school', $school);
        update_post_meta($post_id, 'cbc_program', $program);
        update_post_meta($post_id, 'cbc_year_level', $year_level);
        if (!is_wp_error($attach_id)) {
            update_post_meta($post_id, 'cbc_letter_file_id', intval($attach_id));
            update_post_meta($post_id, 'cbc_letter_file_url', esc_url_raw($uploaded['url']));
        }

        $this->redirect_with_message('Thank you! Your internship application has been submitted.', true);
    }

    private function redirect_with_message($message, $success) {
        $redirect = isset($_POST['_redirect']) ? esc_url_raw(wp_unslash($_POST['_redirect'])) : wp_get_referer();
        if (!$redirect) {
            $redirect = home_url('/');
        }
        $param = $success ? 'cbc_ok' : 'cbc_err';
        $url = add_query_arg([$param => rawurlencode($message)], $redirect);
        wp_safe_redirect($url);
        exit;
    }

    // Admin list columns for Appointments
    public function appt_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = __('Appointment', 'cbc');
        $new['cbc_date'] = __('Date', 'cbc');
        $new['cbc_time'] = __('Time', 'cbc');
        $new['cbc_name'] = __('Name', 'cbc');
        $new['cbc_email'] = __('Email', 'cbc');
        $new['date'] = $columns['date'];
        return $new;
    }

    public function appt_column_content($column, $post_id) {
        switch ($column) {
            case 'cbc_date':
                echo esc_html(get_post_meta($post_id, 'cbc_date', true));
                break;
            case 'cbc_time':
                echo esc_html(get_post_meta($post_id, 'cbc_time', true));
                break;
            case 'cbc_name':
                echo esc_html(get_post_meta($post_id, 'cbc_name', true));
                break;
            case 'cbc_email':
                echo esc_html(get_post_meta($post_id, 'cbc_email', true));
                break;
        }
    }

    // Admin list columns for Feedback
    public function fb_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = __('Feedback', 'cbc');
        $new['cbc_name'] = __('Name', 'cbc');
        $new['cbc_email'] = __('Email', 'cbc');
        $new['cbc_rating'] = __('Rating', 'cbc');
        $new['date'] = $columns['date'];
        return $new;
    }

    public function fb_column_content($column, $post_id) {
        switch ($column) {
            case 'cbc_name':
                echo esc_html(get_post_meta($post_id, 'cbc_name', true));
                break;
            case 'cbc_email':
                echo esc_html(get_post_meta($post_id, 'cbc_email', true));
                break;
            case 'cbc_rating':
                echo esc_html(get_post_meta($post_id, 'cbc_rating', true));
                break;
        }
    }

    // Admin list columns for Internship Applications
    public function intern_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = __('Application', 'cbc');
        $new['cbc_name'] = __('Name', 'cbc');
        $new['cbc_email'] = __('Email', 'cbc');
        $new['cbc_school'] = __('School', 'cbc');
        $new['cbc_program'] = __('Program', 'cbc');
        $new['cbc_year_level'] = __('Year Level', 'cbc');
        $new['date'] = $columns['date'];
        return $new;
    }

    public function intern_column_content($column, $post_id) {
        switch ($column) {
            case 'cbc_name':
                echo esc_html(get_post_meta($post_id, 'cbc_name', true));
                break;
            case 'cbc_email':
                echo esc_html(get_post_meta($post_id, 'cbc_email', true));
                break;
            case 'cbc_school':
                echo esc_html(get_post_meta($post_id, 'cbc_school', true));
                break;
            case 'cbc_program':
                echo esc_html(get_post_meta($post_id, 'cbc_program', true));
                break;
            case 'cbc_year_level':
                echo esc_html(get_post_meta($post_id, 'cbc_year_level', true));
                break;
        }
    }

    public function register_metaboxes() {
        add_meta_box('cbc_appt_details', __('Appointment Details', 'cbc'), [$this, 'render_appt_metabox'], self::APPOINTMENT_POST_TYPE, 'normal', 'high');
        add_meta_box('cbc_fb_details', __('Feedback Details', 'cbc'), [$this, 'render_fb_metabox'], self::FEEDBACK_POST_TYPE, 'normal', 'high');
        add_meta_box('cbc_intern_details', __('Internship Application Details', 'cbc'), [$this, 'render_intern_metabox'], self::INTERNSHIP_POST_TYPE, 'normal', 'high');
    }

    public function render_appt_metabox($post) {
        $fields = [
            'cbc_name' => 'Name',
            'cbc_email' => 'Email',
            'cbc_phone' => 'Phone',
            'cbc_date' => 'Date',
            'cbc_time' => 'Time',
            'cbc_message' => 'Message',
        ];
        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            $val = get_post_meta($post->ID, $key, true);
            echo '<tr><th style="width:150px;">' . esc_html($label) . '</th><td>' . nl2br(esc_html($val)) . '</td></tr>';
        }
        echo '</table>';
    }

    public function render_fb_metabox($post) {
        $fields = [
            'cbc_name' => 'Name',
            'cbc_email' => 'Email',
            'cbc_rating' => 'Rating',
            'cbc_message' => 'Message',
        ];
        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            $val = get_post_meta($post->ID, $key, true);
            echo '<tr><th style="width:150px;">' . esc_html($label) . '</th><td>' . nl2br(esc_html($val)) . '</td></tr>';
        }
        echo '</table>';
    }

    public function render_intern_metabox($post) {
        $fields = [
            'cbc_name' => 'Name',
            'cbc_email' => 'Email',
            'cbc_phone' => 'Phone',
            'cbc_school' => 'School/University',
            'cbc_program' => 'Program',
            'cbc_year_level' => 'Year Level',
        ];
        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            $val = get_post_meta($post->ID, $key, true);
            echo '<tr><th style="width:150px;">' . esc_html($label) . '</th><td>' . nl2br(esc_html($val)) . '</td></tr>';
        }
        // Letter of Intent (file, backward compatible)
        $file_url = get_post_meta($post->ID, 'cbc_letter_file_url', true);
        $file_id  = intval(get_post_meta($post->ID, 'cbc_letter_file_id', true));
        $legacy_text = get_post_meta($post->ID, 'cbc_letter', true);
        echo '<tr><th style="width:150px;">Letter of Intent</th><td>';
        if ($file_url) {
            $filename = basename(parse_url($file_url, PHP_URL_PATH));
            echo '<a href="' . esc_url($file_url) . '" target="_blank" rel="noopener">' . esc_html($filename) . '</a>';
            if ($file_id) {
                echo ' (Attachment ID: ' . intval($file_id) . ')';
            }
        } elseif (!empty($legacy_text)) {
            echo nl2br(esc_html($legacy_text));
        } else {
            echo '<em>No file uploaded.</em>';
        }
        echo '</td></tr>';
        echo '</table>';
    }

    public function prevent_unintended_save($post_id, $post) {
        // We don't need to handle here; entries are read-only from admin by default.
        return;
    }

    /**
     * Shortcode handler: [govph_section_header title="Latest Features" classes="..."]
     * Returns the header markup so it can be used in post content / block editor.
     */
    public function render_section_header_shortcode($atts = []) {
        $defaults = [
            'title'   => '',
            'classes' => 'text-lg sm:text-xl font-extrabold drop-shadow text-white p-2 md:text-2xl bg-gradient-to-r lg:text-3xl text-center px-5',
            // gradient customization: hex colors (with or without #) and swap flag
            'from'    => '#1f5d2b',
            'to'      => '#a2b917',
            'swap'    => '0',
            'tag'     => 'h2',
            'strong'  => '1',
            'id'      => '',
        ];
        $atts = shortcode_atts($defaults, $atts, 'govph_section_header');
        if (empty($atts['title'])) {
            return '';
        }

        return self::section_header_markup($atts['title'], [
            'classes' => $atts['classes'],
            'from'    => $atts['from'],
            'to'      => $atts['to'],
            'swap'    => $atts['swap'],
            'tag'     => $atts['tag'],
            'strong'  => $atts['strong'],
            'id'      => $atts['id'],
        ]);
    }

    /**
     * Return the section header markup. Can be called from templates:
     * echo CBC_Client_Engagement::section_header_markup('Latest Features', ['classes' => '...']);
     */
    public static function section_header_markup($title, $args = []) {
        $defaults = [
            'classes' => 'text-lg sm:text-xl font-extrabold  drop-shadow text-white p-2 md:text-2xl bg-gradient-to-r lg:text-3xl text-center px-5',
            'tag'     => 'h2',
            'strong'  => true,
            'id'      => '',
            // text-alignment: left|center|right (default: center)
            'text_alignment' => 'center',
            // gradient customization
            'from'    => '#1f5d2b',
            'to'      => '#a2b917',
            'swap'    => false,
        ];

        /**
         * Filter the default args for the section header markup.
         *
         * @param array $defaults Default args (classes, tag, strong, id)
         */
        $defaults = apply_filters('govph_section_header_args', $defaults);

        // Backwards-compat: allow only the classes string to be filtered easily
        $defaults['classes'] = apply_filters('govph_section_header_classes', $defaults['classes']);

        $args = wp_parse_args($args, $defaults);
        $tag = preg_replace('/[^a-z0-9_-]/i', '', $args['tag']);
        if (!$tag) {
            $tag = 'h2';
        }
        $classes = $args['classes'];
        // Handle gradient customization: remove any existing gradient tokens then append constructed gradient
        $from = isset($args['from']) ? trim((string)$args['from']) : '';
        $to = isset($args['to']) ? trim((string)$args['to']) : '';
        $swap = filter_var($args['swap'], FILTER_VALIDATE_BOOLEAN);
        if ($swap) {
            $tmp = $from; $from = $to; $to = $tmp;
        }
        if ($from !== '' && $to !== '') {
            // normalize hex (allow with or without #)
            $from_hex = ltrim(strtolower($from), '#');
            $to_hex = ltrim(strtolower($to), '#');
            // Remove tokens like bg-gradient-to-*, from[...], to[...] to avoid duplicates
            $classes = preg_replace('/\b(bg-gradient-to-(?:r|l|t|b|tr|tl|br|bl)|from\[[^]]+]|to\[[^]]+])\b/', '', $classes);
            // Collapse multiple spaces
            $classes = preg_replace('/\s+/', ' ', trim($classes));
            // Determine gradient direction based on swap flag
            $direction = $swap ? 'bg-gradient-to-l' : 'bg-gradient-to-r';
            $gradient_class = $direction . ' from-[#' . $from_hex . '] to-[#' . $to_hex . ']';
            $classes = trim($classes . ' ' . $gradient_class);
        }
        $id_attr = $args['id'] ? ' id="' . esc_attr($args['id']) . '"' : '';
        $strong = filter_var($args['strong'], FILTER_VALIDATE_BOOLEAN);
        $align = in_array($args['text_alignment'], ['left','center','right'], true) ? $args['text_alignment'] : 'center';
        $align_style = ' style="text-align:' . esc_attr($align) . ';"';

        $title_escaped = esc_html($title);
        $classes_attr = esc_attr($classes);

        $inner = $strong ? '<strong>' . $title_escaped . '</strong>' : $title_escaped;
    return '<' . $tag . $id_attr . ' class="' . $classes_attr . '"' . $align_style . '>' . $inner . '</' . $tag . '>';
    }
}

// Global helper for templates: returns the header markup so themes can call it directly.
if (!function_exists('govph_section_header')) {
    function govph_section_header($title, $args = []) {
        return CBC_Client_Engagement::section_header_markup($title, $args);
    }
}

new CBC_Client_Engagement();
