<?php
namespace CbcFormManager\Domain\Repository;

use CbcFormManager\Domain\Entity\FormSubmission;

if (!defined('ABSPATH')) { exit; }

interface FormSubmissionRepository
{
    /**
     * Persist a form submission and return its identifier (e.g., WP post ID).
     */
    public function save(FormSubmission $submission): int;
}

