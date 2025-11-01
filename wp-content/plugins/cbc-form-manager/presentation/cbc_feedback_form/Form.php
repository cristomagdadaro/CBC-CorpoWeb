<?php
namespace CbcFormManager\Presentation\cbc_feedback_form;

use CbcFormManager\Application\FormModuleInterface;

if (!defined('ABSPATH')) { exit; }

class Form implements FormModuleInterface
{
    public function key(): string { return 'cbc_feedback_form'; }
    public function shortcode(): string { return 'cbc_feedback_form'; }
    public function title(): string { return __('CBC Feedback Form', 'cbc-form-manager'); }

    public function fields(): array
    {
        return [
            'name' => ['label' => __('Your Name', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'email' => ['label' => __('Your Email (optional)', 'cbc-form-manager'), 'type' => 'email', 'required' => false],
            'rating' => ['label' => __('Rating', 'cbc-form-manager'), 'type' => 'select', 'required' => true, 'options' => [
                '5' => __('Excellent', 'cbc-form-manager'),
                '4' => __('Very Good', 'cbc-form-manager'),
                '3' => __('Good', 'cbc-form-manager'),
                '2' => __('Fair', 'cbc-form-manager'),
                '1' => __('Poor', 'cbc-form-manager'),
            ]],
            'comments' => ['label' => __('Comments', 'cbc-form-manager'), 'type' => 'textarea', 'required' => true],
        ];
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style('cbc-feedback-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_feedback_form/assets/style.css', [], '1.0.0');
        wp_enqueue_script('cbc-feedback-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_feedback_form/assets/script.js', ['jquery'], '1.0.0', true);
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

        ob_start();
        ?>
        <form class="cbc-form cbc-feedback-form" method="post" action="<?php echo esc_url($action); ?>">
            <input type="hidden" name="_cbc_form_key" value="<?php echo esc_attr($this->key()); ?>" />
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>

            <?php if (!empty($errors['_global'])): ?>
                <div class="cbc-form-alert cbc-form-alert-danger"><?php echo esc_html($errors['_global']); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="cbc-form-alert cbc-form-alert-success"><?php echo esc_html($success); ?></div>
            <?php endif; ?>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['name']['label']); ?> *</label>
                <input type="text" name="name" value="<?php echo esc_attr($old['name'] ?? ''); ?>" />
                <?php if (!empty($errors['name'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['name']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['email']['label']); ?></label>
                <input type="email" name="email" value="<?php echo esc_attr($old['email'] ?? ''); ?>" />
                <?php if (!empty($errors['email'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['email']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['rating']['label']); ?> *</label>
                <select name="rating">
                    <option value="">—</option>
                    <?php foreach ($fields['rating']['options'] as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected(($old['rating'] ?? ''), $val); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['rating'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['rating']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['comments']['label']); ?> *</label>
                <textarea name="comments" rows="5"><?php echo esc_textarea($old['comments'] ?? ''); ?></textarea>
                <?php if (!empty($errors['comments'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['comments']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-actions">
                <button type="submit" class="button"><?php echo esc_html__('Send Feedback', 'cbc-form-manager'); ?></button>
            </div>
        </form>
        <?php
        return (string)ob_get_clean();
    }
}

