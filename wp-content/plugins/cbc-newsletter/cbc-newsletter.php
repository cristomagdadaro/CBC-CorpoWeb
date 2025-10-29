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
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
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
		$message = '<p style="color: green; text-align: center; font-weight: bolder; font-family: League Spartan">Thank you for subscribing!</p>';
	}

	ob_start();
	?>
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
		<?php echo $message; ?>
		<form method="post" action="">
			<?php
				wp_nonce_field('cbc_newsletter_subscribe_action', 'cbc_newsletter_nonce');
			?>
			<p class="text-sm leading-none text-center">Receive email updates whenever we post new biotechnology updates</p>
			<label class="text-sm" for="newsletter_email">Email:</label>
			<input type="email" name="newsletter_email" id="newsletter_email" required>
			<input type="submit" name="newsletter_subscribe" value="Subscribe">
		</form>
	</div>
	<?php
	return ob_get_clean();
}

// Handle subscription
add_action('init', 'cbc_newsletter_handle_subscription');

function cbc_newsletter_handle_subscription() {
	if (isset($_POST['newsletter_subscribe']) && check_admin_referer('cbc_newsletter_subscribe_action', 'cbc_newsletter_nonce')) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'newsletter_subscribers';
		// Sanitize and validate
		$email = sanitize_email($_POST['newsletter_email']);

		if (is_email($email)) {
			// Check if email already exists to prevent duplicate entries
			$exists = $wpdb->get_var($wpdb->prepare("SELECT email FROM $table_name WHERE email = %s", $email));

			if (!$exists) {
				// Insert the new subscriber
				$inserted = $wpdb->insert($table_name, array('email' => $email));

				if ($inserted) {
					// Redirect to the same page with a success flag after successful subscription
					wp_redirect(add_query_arg('cbc_subscribed', '1', wp_get_referer()));
					exit;
				}
			} else {
				// Optional: Redirect with a 'already subscribed' message
				// For simplicity, we'll just redirect to the success message for now
				wp_redirect(add_query_arg('cbc_subscribed', '1', wp_get_referer()));
				exit;
			}
		}
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

	?>
	<div class="wrap">
		<h1>CBC Newsletter Management</h1>
		<?php
		// Tab navigation
		echo '<h2 class="nav-tab-wrapper">';
		echo '<a href="?page=cbc-newsletter&tab=send" class="nav-tab ' . ($current_tab == 'send' ? 'nav-tab-active' : '') . '">Send Newsletter</a>';
		echo '<a href="?page=cbc-newsletter&tab=manage" class="nav-tab ' . ($current_tab == 'manage' ? 'nav-tab-active' : '') . '">Manage Subscribers</a>';
		echo '</h2>';

		// Content based on tab
		if ($current_tab == 'send') {
			cbc_newsletter_admin_send_page();
		} elseif ($current_tab == 'manage') {
			cbc_newsletter_admin_manage_page();
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
			cbc_newsletter_send_emails(sanitize_text_field($_POST['subject']), wp_kses_post($_POST['message'])); // Sanitize for email
			echo '<div class="notice notice-success"><p>Newsletter sent to all subscribers!</p></div>';
		} else {
			echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
		}
	}
	?>
	<h2>Send Email Update</h2>
	<form method="post" action="?page=cbc-newsletter&tab=send">
		<?php wp_nonce_field('cbc_send_newsletter_nonce'); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="subject">Subject:</label></th>
				<td><input type="text" name="subject" id="subject" required class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="message">Message:</label></th>
				<td><textarea name="message" id="message" rows="10" class="large-text" style="width: 100%;" required></textarea></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="send_newsletter" value="Send Newsletter" class="button button-primary button-large">
		</p>
	</form>
	<?php
}

function cbc_newsletter_send_emails($subject, $message) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_subscribers';
	// Retrieve only the email column
	$subscribers = $wpdb->get_results("SELECT email FROM $table_name");

	foreach ($subscribers as $subscriber) {
		// Send email
		wp_mail($subscriber->email, $subject, $message);
	}
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
?>