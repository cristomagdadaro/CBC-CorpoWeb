<?php
namespace CbcFormManager\Presentation\cbc_appointment_form;

use CbcFormManager\Application\FormModuleInterface;

if (!defined('ABSPATH')) { exit; }

class Form implements FormModuleInterface
{
    public function key(): string { return 'cbc_appointment_form'; }
    public function shortcode(): string { return 'cbc_appointment_form'; }
    public function title(): string { return __('CBC Appointment Form', 'cbc-form-manager'); }

    public function fields(): array
    {
        return [
            'name' => ['label' => __('Full Name', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'email' => ['label' => __('Email', 'cbc-form-manager'), 'type' => 'email', 'required' => true],
            'phone' => ['label' => __('Phone', 'cbc-form-manager'), 'type' => 'text', 'required' => false],
            'preferred_date' => ['label' => __('Preferred Date', 'cbc-form-manager'), 'type' => 'date', 'required' => true],
            'preferred_time' => ['label' => __('Preferred Time', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'message' => ['label' => __('Message', 'cbc-form-manager'), 'type' => 'textarea', 'required' => false],
        ];
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style('cbc-appointment-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_appointment_form/assets/style.css', [], '1.0.1');
        wp_enqueue_script('cbc-appointment-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_appointment_form/assets/script.js', ['jquery'], '1.0.0', true);
    }

    public function render(array $view = []): string
    {
        $fields = $this->fields();
        $errors = $view['errors'] ?? [];
        $old = $view['old'] ?? [];
        $success = $view['success'] ?? null;
        $action = $view['action'] ?? '';
        $nonce_action = $view['nonce_action'] ?? '';
        $nonce_name = $view['nonce_name'] ?? '_cbc_form_nonce';

        $id = function(string $name): string { return 'cbc_appointment_' . $name; };

        ob_start();
        ?>
        <form class="cbc-form cbc-appointment-form" method="post" action="<?php echo esc_url($action); ?>">
	        <h2>CBC  Form Manager</h2>
            <input type="hidden" name="_cbc_form_key" value="<?php echo esc_attr($this->key()); ?>" />
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>

            <?php if (!empty($errors['_global'])): ?>
                <div class="cbc-form-alert cbc-form-alert-danger"><?php echo esc_html($errors['_global']); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="cbc-form-alert cbc-form-alert-success"><?php echo esc_html($success); ?></div>
            <?php endif; ?>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('name')); ?>"><?php echo esc_html($fields['name']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('name')); ?>" type="text" name="name" value="<?php echo esc_attr($old['name'] ?? ''); ?>" />
                <?php if (!empty($errors['name'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['name']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('email')); ?>"><?php echo esc_html($fields['email']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('email')); ?>" type="email" name="email" value="<?php echo esc_attr($old['email'] ?? ''); ?>" />
                <?php if (!empty($errors['email'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['email']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('phone')); ?>"><?php echo esc_html($fields['phone']['label']); ?></label>
                <input id="<?php echo esc_attr($id('phone')); ?>" type="text" name="phone" value="<?php echo esc_attr($old['phone'] ?? ''); ?>" />
                <?php if (!empty($errors['phone'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['phone']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('preferred_date')); ?>"><?php echo esc_html($fields['preferred_date']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('preferred_date')); ?>" type="date" name="preferred_date" value="<?php echo esc_attr($old['preferred_date'] ?? ''); ?>" />
                <?php if (!empty($errors['preferred_date'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['preferred_date']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('preferred_time')); ?>"><?php echo esc_html($fields['preferred_time']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('preferred_time')); ?>" type="text" name="preferred_time" value="<?php echo esc_attr($old['preferred_time'] ?? ''); ?>" placeholder="e.g., 14:00" />
                <?php if (!empty($errors['preferred_time'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['preferred_time']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('message')); ?>"><?php echo esc_html($fields['message']['label']); ?></label>
                <textarea id="<?php echo esc_attr($id('message')); ?>" name="message" rows="5"><?php echo esc_textarea($old['message'] ?? ''); ?></textarea>
                <?php if (!empty($errors['message'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['message']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-actions">
                <button type="submit" class="button"><?php echo esc_html__('Book Appointment', 'cbc-form-manager'); ?></button>
            </div>
        </form>
        <?php
        return (string)ob_get_clean();
    }
}
