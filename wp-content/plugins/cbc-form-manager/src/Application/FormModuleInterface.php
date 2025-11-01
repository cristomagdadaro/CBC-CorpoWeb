<?php
namespace CbcFormManager\Application;

if (!defined('ABSPATH')) { exit; }

interface FormModuleInterface
{
    public function key(): string;            // Unique key, e.g., cbc_appointment_form
    public function shortcode(): string;      // Shortcode tag
    public function title(): string;          // Human-readable name

    /**
     * Return field definitions.
     * Example: [
     *   'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
     *   'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
     * ]
     */
    public function fields(): array;

    /**
     * Enqueue required assets for this form.
     */
    public function enqueue_assets(): void;

    /**
     * Render the form HTML.
     * The $view array includes: 'errors' (array), 'old' (array), 'success' (string|null), 'action' (string), 'nonce_action' (string), 'nonce_name' (string)
     */
    public function render(array $view = []): string;
}

