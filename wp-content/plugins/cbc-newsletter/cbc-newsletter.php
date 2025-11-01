<?php
/*
Plugin Name: CBC Newsletter
Description: A simple newsletter plugin for subscribing users and sending updates via email.
Version: 1.1
Author: Cristo Rey C. Magdadaro
*/

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Activation hook to create database table
register_activation_hook(__FILE__, 'cbc_newsletter_activate');

function cbc_newsletter_activate() {
	global $wpdb;
	$table_name_subscribers = $wpdb->prefix . 'newsletter_subscribers';
	$table_name_templates = $wpdb->prefix . 'newsletter_templates';
	$charset_collate = $wpdb->get_charset_collate();

	$sql_subscribers = "CREATE TABLE $table_name_subscribers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

	$sql_templates = "CREATE TABLE $table_name_templates (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		content text NOT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_subscribers);
	dbDelta($sql_templates);
}

// Deactivation hook (optional)
register_deactivation_hook(__FILE__, 'cbc_newsletter_deactivate');

function cbc_newsletter_deactivate() {
	// Optional cleanup
}

// Shortcode for subscription form
add_shortcode('newsletter_subscribe', 'cbc_newsletter_subscribe_form');

function cbc_newsletter_subscribe_form() {
	// Display a message if subscription was successful
	$message = '';
	if (isset($_GET['cbc_subscribed']) && $_GET['cbc_subscribed'] == '1') {
		$message = '<p style="color: green; text-align: center; font-weight: bolder; font-family: League Spartan,serif">Thank you for subscribing!</p>';
	}

	ob_start();
	?>
	<div id="cbc-newsletter-message"></div>
	<style>
        /* Simple styling for form alignment and button */
        .cbc-newsletter-form {
            max-width: 400px;
            padding: 15px;
            border-radius: 5px;
        }
        .cbc-newsletter-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .cbc-newsletter-form input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box; /* Crucial for width: 100% with padding */
        }
        /* Using a standard button class for a slightly better UI */
        .cbc-newsletter-form input[type="submit"] {
            cursor: pointer;
            padding: 8px 15px;
            background-color: #1f5d2b; /* WordPress primary color */
            color: white;
            border: none;
            border-radius: 3px;
        }
	</style>
	<div class="cbc-newsletter-form">
		<form id="cbc-newsletter-subscribe-form" method="post" action="">
			<?php
				wp_nonce_field('cbc_newsletter_subscribe_action', 'cbc_newsletter_nonce');
			?>
			<p class="text-sm leading-none text-center">Receive email updates whenever we post new biotechnology updates</p>
			<label class="text-sm" for="newsletter_email">Email:</label>
			<input type="email" name="newsletter_email" id="newsletter_email" required>
			<input type="submit" name="newsletter_subscribe" value="Subscribe">
		</form>
	</div>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('cbc-newsletter-subscribe-form');
		const messageDiv = document.getElementById('cbc-newsletter-message');

		form.addEventListener('submit', function(e) {
			e.preventDefault();

			const formData = new FormData(form);
			formData.append('action', 'cbc_subscribe_newsletter');

			fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					messageDiv.innerHTML = '<p style="color: green;">' + data.data.message + '</p>';
					form.reset();
				} else {
					messageDiv.innerHTML = '<p style="color: red;">' + data.data.message + '</p>';
				}
			})
			.catch(error => {
				messageDiv.innerHTML = '<p style="color: red;">An unexpected error occurred.</p>';
			});
		});
	});
	</script>
	<?php
	return ob_get_clean();
}

// Handle subscription via AJAX
add_action('wp_ajax_nopriv_cbc_subscribe_newsletter', 'cbc_newsletter_handle_ajax_subscription');
add_action('wp_ajax_cbc_subscribe_newsletter', 'cbc_newsletter_handle_ajax_subscription');

function cbc_newsletter_handle_ajax_subscription() {
	if (!check_ajax_referer('cbc_newsletter_subscribe_action', 'cbc_newsletter_nonce', false)) {
		wp_send_json_error(['message' => 'Security check failed.'], 403);
		return;
	}

	if (!isset($_POST['newsletter_email'])) {
		wp_send_json_error(['message' => 'Email is required.'], 400);
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	$email = sanitize_email($_POST['newsletter_email']);

	if (!is_email($email)) {
		wp_send_json_error(['message' => 'Invalid email address provided.'], 400);
		return;
	}

	$exists = $wpdb->get_var($wpdb->prepare("SELECT email FROM $table_name WHERE email = %s", $email));

	if ($exists) {
		wp_send_json_success(['message' => 'You are already subscribed. Thank you!']);
		return;
	}

	$inserted = $wpdb->insert($table_name, ['email' => $email]);

	if ($inserted) {
		wp_send_json_success(['message' => 'Thank you for subscribing!']);
	} else {
		wp_send_json_error(['message' => 'Could not subscribe. Please try again.'], 500);
	}
}


// --- Admin Menu and Pages ---

add_action('admin_menu', 'cbc_newsletter_admin_menu');

function cbc_newsletter_admin_menu() {
	add_menu_page('CBC Newsletter', 'Newsletter', 'manage_options', 'cbc-newsletter', 'cbc_newsletter_admin_page_router', 'dashicons-email', 6);
}

// Router function to handle different tabs/sections
function cbc_newsletter_admin_page_router() {
	// Current active tab, default to 'send'
	$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'send';

	// Handle subscriber deletion before displaying the page
	if ($current_tab === 'manage' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id']) && check_admin_referer('delete_subscriber_action_' . $_GET['id'])) {
		cbc_newsletter_delete_subscriber($_GET['id']);
	}

	// Handle template actions
	if ($current_tab === 'templates') {
		if (isset($_POST['save_template']) && check_admin_referer('cbc_save_template_nonce')) {
			$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
			$name = sanitize_text_field($_POST['template_name']);
			$content = wp_kses_post($_POST['template_content']);
			cbc_newsletter_save_template($template_id, $name, $content);
			// Redirect to avoid form resubmission
			wp_redirect(admin_url('admin.php?page=cbc-newsletter&tab=templates&template_saved=1'));
			exit;
		}
		if (isset($_GET['action']) && $_GET['action'] === 'delete_template' && isset($_GET['template_id']) && check_admin_referer('cbc_delete_template_' . $_GET['template_id'])) {
			cbc_newsletter_delete_template(intval($_GET['template_id']));
			wp_redirect(admin_url('admin.php?page=cbc-newsletter&tab=templates&template_deleted=1'));
			exit;
		}
	}

	?>
	<div class="wrap">
		<h1>CBC Newsletter Management</h1>
		<?php
		// Tab navigation
		echo '<h2 class="nav-tab-wrapper">';
		echo '<a href="?page=cbc-newsletter&tab=send" class="nav-tab ' . ($current_tab == 'send' ? 'nav-tab-active' : '') . '">Send Newsletter</a>';
		echo '<a href="?page=cbc-newsletter&tab=manage" class="nav-tab ' . ($current_tab == 'manage' ? 'nav-tab-active' : '') . '">Manage Subscribers</a>';
		echo '<a href="?page=cbc-newsletter&tab=templates" class="nav-tab ' . ($current_tab == 'templates' ? 'nav-tab-active' : '') . '">Templates</a>';
		echo '</h2>';

		// Content based on tab
		if ($current_tab == 'send') {
			cbc_newsletter_admin_send_page();
		} elseif ($current_tab == 'manage') {
			cbc_newsletter_admin_manage_page();
		} elseif ($current_tab == 'templates') {
			cbc_newsletter_admin_templates_page();
		}
		?>
	</div>
	<?php
}

// Newsletter Sending Page
function cbc_newsletter_admin_send_page() {
	if (isset($_POST['send_newsletter'])) {
		// Basic nonce check for form submission security
		if (wp_verify_nonce($_POST['_wpnonce'], 'cbc_send_newsletter_nonce')) {
			$subject = sanitize_text_field($_POST['subject']);
			$message = wp_kses_post($_POST['message']);

			// Set content type to HTML
			add_filter('wp_mail_content_type', function() {
				return 'text/html';
			});

			cbc_newsletter_send_emails($subject, $message);

			// Reset content type to avoid conflicts
			remove_filter('wp_mail_content_type', 'wpautop');

			echo '<div class="notice notice-success"><p>Newsletter sent to all subscribers!</p></div>';
		} else {
			echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
		}
	}

	global $wpdb;
	$templates_table = $wpdb->prefix . 'newsletter_templates';
	$templates = $wpdb->get_results("SELECT id, name FROM $templates_table ORDER BY name ASC");

	?>
	<h2>Send Email Update</h2>
	<form method="post" action="?page=cbc-newsletter&tab=send">
		<?php wp_nonce_field('cbc_send_newsletter_nonce'); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="template_select">Choose a Template:</label></th>
				<td>
					<select name="template_select" id="template_select">
						<option value="">-- Select a Template --</option>
						<?php foreach ($templates as $template): ?>
							<option value="<?php echo esc_attr($template->id); ?>"><?php echo esc_html($template->name); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">Selecting a template will load its content into the message box below.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="subject">Subject:</label></th>
				<td><input type="text" name="subject" id="subject" required class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="message">Message:</label></th>
				<td>
					<?php wp_editor('', 'message', ['textarea_rows' => 15, 'media_buttons' => true]); ?>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="send_newsletter" value="Send Newsletter" class="button button-primary button-large">
		</p>
	</form>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const templateSelect = document.getElementById('template_select');
		templateSelect.addEventListener('change', function() {
			const templateId = this.value;
			if (!templateId) {
				if (wp.editor.get('message')) {
					wp.editor.get('message').setContent('');
				}
				return;
			}

			// Fetch template content via AJAX
			const formData = new FormData();
			formData.append('action', 'cbc_get_template_content');
			formData.append('template_id', templateId);
			formData.append('_ajax_nonce', '<?php echo wp_create_nonce('cbc_get_template_content_nonce'); ?>');

			fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && wp.editor.get('message')) {
					wp.editor.get('message').setContent(data.data.content);
				} else {
					alert('Could not load template content.');
				}
			});
		});
	});
	</script>
	<?php
}

// AJAX handler to get template content
add_action('wp_ajax_cbc_get_template_content', function() {
	check_ajax_referer('cbc_get_template_content_nonce');

	$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
	if (!$template_id) {
		wp_send_json_error(null, 400);
	}

	global $wpdb;
	$templates_table = $wpdb->prefix . 'newsletter_templates';
	$content = $wpdb->get_var($wpdb->prepare("SELECT content FROM $templates_table WHERE id = %d", $template_id));

	if ($content !== null) {
		wp_send_json_success(['content' => $content]);
	} else {
		wp_send_json_error(null, 404);
	}
});


function cbc_newsletter_send_emails($subject, $message) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	$subscribers = $wpdb->get_results("SELECT email FROM $table_name");

	// Get the site logo to prepend to the email
	$logo_html = '';
	if (function_exists('get_custom_logo') && has_custom_logo()) {
		$custom_logo_id = get_theme_mod('custom_logo');
		$logo_image_data = wp_get_attachment_image_src($custom_logo_id, 'full');
		if ($logo_image_data) {
			$logo_url = $logo_image_data[0];
			$logo_html = '<div style="text-align:center; padding: 20px 0;"><img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" style="max-width:150px; height:auto;"></div>';
		}
	}

	// Combine the logo, message
	$full_message = $logo_html . $message;

	// Set the From Name and From Email to make emails look professional
	$website_name = get_bloginfo('name');
	$domain = wp_parse_url(get_home_url(), PHP_URL_HOST);
	$from_email = 'noreply@' . $domain;

	$from_name_filter = function() use ($website_name) {
		return $website_name;
	};
	$from_email_filter = function() use ($from_email) {
		return $from_email;
	};

	add_filter('wp_mail_from_name', $from_name_filter);
	add_filter('wp_mail_from', $from_email_filter);

	foreach ($subscribers as $subscriber) {
		wp_mail($subscriber->email, $subject, $full_message);
	}

	// Remove the filters immediately after sending to avoid conflicts
	remove_filter('wp_mail_from_name', $from_name_filter);
	remove_filter('wp_mail_from', $from_email_filter);
}

// --- Subscriber Management Page ---

function cbc_newsletter_admin_manage_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	// Fetch all subscribers, ordered by subscription date
	$subscribers = $wpdb->get_results("SELECT id, email, subscribed_at FROM $table_name ORDER BY subscribed_at DESC");

	?>
	<h2>Manage Subscribers (<?php echo count($subscribers); ?> Total)</h2>

	<?php if (count($subscribers) > 0): ?>
		<table class="wp-list-table widefat striped">
			<thead>
			<tr>
				<th style="width: 50px;">ID</th>
				<th>Email</th>
				<th style="width: 200px;">Subscribed At</th>
				<th style="width: 100px;">Action</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($subscribers as $subscriber):
				$delete_url = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'delete',
							'id' => $subscriber->id,
							'tab' => 'manage',
							'page' => 'cbc-newsletter'
						),
						admin_url('admin.php')
					),
					'delete_subscriber_action_' . $subscriber->id
				);
				?>
				<tr>
					<td><?php echo esc_html($subscriber->id); ?></td>
					<td><?php echo esc_html($subscriber->email); ?></td>
					<td><?php echo esc_html($subscriber->subscribed_at); ?></td>
					<td>
						<a href="<?php echo $delete_url; ?>"
						   class="button button-small"
						   onclick="return confirm('Are you sure you want to delete this subscriber?');">
							Delete
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>No subscribers found.</p>
	<?php endif;
}

function cbc_newsletter_delete_subscriber($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	// Use $wpdb->delete for safe deletion
	$deleted = $wpdb->delete($table_name, array('id' => $id), array('%d'));

	if ($deleted) {
		// Redirect back to the manage tab after deletion with a success message
		wp_redirect(add_query_arg('deleted', '1', admin_url('admin.php?page=cbc-newsletter&tab=manage')));
		exit;
	}
}

// --- Template Management Functions ---

function cbc_newsletter_admin_templates_page() {
	global $wpdb;
	$templates_table = $wpdb->prefix . 'newsletter_templates';

	$edit_template = null;
	if (isset($_GET['action']) && $_GET['action'] === 'edit_template' && isset($_GET['template_id'])) {
		$template_id = intval($_GET['template_id']);
		$edit_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $templates_table WHERE id = %d", $template_id));
	}

	$templates = $wpdb->get_results("SELECT * FROM $templates_table ORDER BY name ASC");
	?>
	<div class="wrap">
		<h2>Email Templates</h2>

		<div id="col-container" class="wp-clearfix">
			<div id="col-left">
				<div class="col-wrap">
					<h3><?php echo $edit_template ? 'Edit Template' : 'Add New Template'; ?></h3>
					<form method="post" action="?page=cbc-newsletter&tab=templates">
						<?php wp_nonce_field('cbc_save_template_nonce'); ?>
						<input type="hidden" name="template_id" value="<?php echo $edit_template ? esc_attr($edit_template->id) : '0'; ?>">

						<div class="form-field">
							<label for="template_name">Template Name</label>
							<input type="text" name="template_name" id="template_name" value="<?php echo $edit_template ? esc_attr($edit_template->name) : ''; ?>" required>
						</div>

						<div class="form-field">
							<label for="template_content">Template Content</label>
							<?php wp_editor($edit_template ? $edit_template->content : '', 'template_content', ['textarea_rows' => 20]); ?>
						</div>

						<p class="submit">
							<input type="submit" name="save_template" class="button button-primary" value="<?php echo $edit_template ? 'Update Template' : 'Add Template'; ?>">
							<?php if ($edit_template): ?>
								<a href="?page=cbc-newsletter&tab=templates" class="button">Cancel Edit</a>
							<?php endif; ?>
						</p>
					</form>
				</div>
			</div>
			<div id="col-right">
				<div class="col-wrap">
					<h3>Existing Templates</h3>
					<table class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th>Name</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if ($templates): ?>
								<?php foreach ($templates as $template): ?>
									<tr>
										<td><?php echo esc_html($template->name); ?></td>
										<td>
											<a href="?page=cbc-newsletter&tab=templates&action=edit_template&template_id=<?php echo $template->id; ?>">Edit</a> |
											<a href="<?php echo wp_nonce_url('?page=cbc-newsletter&tab=templates&action=delete_template&template_id=' . $template->id, 'cbc_delete_template_' . $template->id); ?>" onclick="return confirm('Are you sure?');" style="color: red;">Delete</a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="2">No templates found.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function cbc_newsletter_save_template($id, $name, $content) {
	global $wpdb;
	$templates_table = $wpdb->prefix . 'newsletter_templates';

	$data = ['name' => $name, 'content' => $content];
	$format = ['%s', '%s'];

	if ($id > 0) {
		$wpdb->update($templates_table, $data, ['id' => $id], $format, ['%d']);
	} else {
		$wpdb->insert($templates_table, $data, $format);
	}
}

function cbc_newsletter_delete_template($id) {
	global $wpdb;
	$templates_table = $wpdb->prefix . 'newsletter_templates';
	$wpdb->delete($templates_table, ['id' => $id], ['%d']);
}
?>

