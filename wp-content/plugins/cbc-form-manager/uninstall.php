<?php
// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Only delete data if the site owner explicitly enabled it.
$should_delete = get_option('cbc_fm_delete_data_on_uninstall');
if ($should_delete !== 'yes') {
    return;
}

// Delete all cbc_form_submission posts and their meta
$posts = get_posts([
    'post_type' => 'cbc_form_submission',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'fields' => 'ids',
]);

if (!empty($posts) && is_array($posts)) {
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
}

delete_option('cbc_fm_delete_data_on_uninstall');

