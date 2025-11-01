<?php
namespace CbcFormManager\Infrastructure\Repository;

use CbcFormManager\Domain\Entity\FormSubmission;
use CbcFormManager\Domain\Repository\FormSubmissionRepository;

if (!defined('ABSPATH')) { exit; }

class WpFormSubmissionRepository implements FormSubmissionRepository
{
    public function save(FormSubmission $submission): int
    {
        $title = sprintf('%s - %s', $submission->getFormKey(), current_time('mysql'));
        $postId = wp_insert_post([
            'post_type'   => 'cbc_form_submission',
            'post_status' => 'private',
            'post_title'  => $title,
            'post_content'=> '',
        ], true);

        if (is_wp_error($postId)) {
            // Log error if needed
            return 0;
        }

        $data = $submission->getData();
        update_post_meta($postId, '_cbc_form_key', $submission->getFormKey());
        update_post_meta($postId, '_cbc_form_data', wp_json_encode($data));
        update_post_meta($postId, '_cbc_form_user', $submission->getUserId());
        update_post_meta($postId, '_cbc_form_ip', $submission->getIp());
        update_post_meta($postId, '_cbc_form_user_agent', $submission->getUserAgent());
        update_post_meta($postId, '_cbc_form_created_at', $submission->getCreatedAt()->format('c'));

        do_action('cbc_form_manager/saved', $submission->getFormKey(), $postId, $data);

        return (int)$postId;
    }
}

