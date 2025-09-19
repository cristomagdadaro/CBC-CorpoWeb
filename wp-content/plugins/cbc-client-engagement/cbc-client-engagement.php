<?php
/**
 * Plugin Name: CBC Client Engagement (Appointments & Feedback)
 * Description: Provides front-end forms for clients to book appointments and send feedback. Adds an admin panel to manage submissions.
 * Version: 1.0.0
 * Author: CBC Dev Team
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Client_Engagement {
    const APPOINTMENT_POST_TYPE = 'cbc_appointment';
    const FEEDBACK_POST_TYPE    = 'cbc_feedback';

    public function __construct() {
        // Register post types
        add_action('init', [$this, 'register_post_types']);

        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Shortcodes
        add_shortcode('cbc_appointment_form', [$this, 'render_appointment_form']);
        add_shortcode('cbc_feedback_form', [$this, 'render_feedback_form']);

        // Form handlers (admin-post)
        add_action('admin_post_nopriv_cbc_submit_appointment', [$this, 'handle_submit_appointment']);
        add_action('admin_post_cbc_submit_appointment',        [$this, 'handle_submit_appointment']);
        add_action('admin_post_nopriv_cbc_submit_feedback',    [$this, 'handle_submit_feedback']);
        add_action('admin_post_cbc_submit_feedback',           [$this, 'handle_submit_feedback']);

        // Admin menu
        add_action('admin_menu', [$this, 'register_admin_menu']);

        // Admin columns
        add_filter('manage_' . self::APPOINTMENT_POST_TYPE . '_posts_columns', [$this, 'appt_columns']);
        add_action('manage_' . self::APPOINTMENT_POST_TYPE . '_posts_custom_column', [$this, 'appt_column_content'], 10, 2);
        add_filter('manage_' . self::FEEDBACK_POST_TYPE . '_posts_columns', [$this, 'fb_columns']);
        add_action('manage_' . self::FEEDBACK_POST_TYPE . '_posts_custom_column', [$this, 'fb_column_content'], 10, 2);

        // Meta boxes
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        // Save disabled (we treat as submitted entries) but keep hook for possible future use
        add_action('save_post', [$this, 'prevent_unintended_save'], 10, 2);

        // Basic styles for forms
        add_action('wp_enqueue_scripts', function() {
            wp_register_style('cbc-client-engagement', plugins_url('css/forms.css', __FILE__), [], '1.0.0');
        });
    }

    public function activate() {
        $this->register_post_types();
        flush_rewrite_rules();
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
    }

    public function register_admin_menu() {
        $cap = 'edit_posts';
        add_menu_page(
            __('Client Engagement', 'cbc'),
            __('Client Engagement', 'cbc'),
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
    }

    public function render_dashboard_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $appt_count = wp_count_posts(self::APPOINTMENT_POST_TYPE);
        $fb_count   = wp_count_posts(self::FEEDBACK_POST_TYPE);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Client Engagement', 'cbc'); ?></h1>
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
            </div>
            <p style="margin-top:20px;">Embed forms using these shortcodes: <code>[cbc_appointment_form]</code> and <code>[cbc_feedback_form]</code>.</p>
        </div>
        <?php
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

    public function register_metaboxes() {
        add_meta_box('cbc_appt_details', __('Appointment Details', 'cbc'), [$this, 'render_appt_metabox'], self::APPOINTMENT_POST_TYPE, 'normal', 'high');
        add_meta_box('cbc_fb_details', __('Feedback Details', 'cbc'), [$this, 'render_fb_metabox'], self::FEEDBACK_POST_TYPE, 'normal', 'high');
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

    public function prevent_unintended_save($post_id, $post) {
        // We don't need to handle here; entries are read-only from admin by default.
        return;
    }
}

new CBC_Client_Engagement();
