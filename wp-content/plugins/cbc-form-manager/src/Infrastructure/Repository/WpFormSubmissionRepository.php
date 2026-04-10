<?php
namespace CbcFormManager\Infrastructure\Repository;

use CbcFormManager\Domain\Entity\FormSubmission;
use CbcFormManager\Domain\Repository\FormSubmissionRepository;

if (!defined('ABSPATH')) { exit; }

class WpFormSubmissionRepository implements FormSubmissionRepository
{
    public function save(FormSubmission $submission): int
    {
        $title = $this->buildTitle($submission);
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

    private function buildTitle(FormSubmission $submission): string
    {
        $data = $submission->getData();
        $fallback = sprintf('%s - %s', $submission->getFormKey(), current_time('mysql'));

        switch ($submission->getFormKey()) {
            case 'cbc_appointment_form':
                $name = sanitize_text_field((string) ($data['name'] ?? ''));
                $when = trim(sanitize_text_field((string) ($data['preferred_date'] ?? '')) . ' ' . sanitize_text_field((string) ($data['preferred_time'] ?? '')));
                return trim($name . ($when !== '' ? ' - ' . $when : '')) ?: $fallback;

            case 'cbc_feedback_form':
                $name = sanitize_text_field((string) ($data['name'] ?? ''));
                $rating = sanitize_text_field((string) ($data['rating'] ?? ''));
                return trim($name . ($rating !== '' ? ' - Rating: ' . $rating : '')) ?: $fallback;

            case 'cbc_internship_form':
                $name = sanitize_text_field((string) ($data['name'] ?? ''));
                $school = sanitize_text_field((string) ($data['university_school'] ?? ''));
                $program = sanitize_text_field((string) ($data['course_program'] ?? ''));
                $context = trim($school . ($program !== '' ? ' (' . $program . ')' : ''));
                return trim($name . ($context !== '' ? ' - ' . $context : '')) ?: $fallback;
        }

        return $fallback;
    }
}

