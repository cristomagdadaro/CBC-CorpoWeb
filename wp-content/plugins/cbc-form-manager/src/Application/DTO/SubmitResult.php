<?php
namespace CbcFormManager\Application\DTO;

if (!defined('ABSPATH')) { exit; }

class SubmitResult
{
    public bool $success;
    public array $errors;
    public ?int $savedId;

    public function __construct(bool $success, array $errors = [], ?int $savedId = null)
    {
        $this->success = $success;
        $this->errors = $errors;
        $this->savedId = $savedId;
    }
}

