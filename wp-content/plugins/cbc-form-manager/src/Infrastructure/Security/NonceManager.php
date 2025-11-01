<?php
namespace CbcFormManager\Infrastructure\Security;

if (!defined('ABSPATH')) { exit; }

class NonceManager
{
    public function field(string $action, string $name = '_cbc_form_nonce'): string
    {
        ob_start();
        wp_nonce_field($action, $name);
        return (string)ob_get_clean();
    }

    public function verify(string $action, string $name = '_cbc_form_nonce'): bool
    {
        if (!isset($_POST[$name])) {
            return false;
        }
        return (bool)wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$name])), $action);
    }
}

