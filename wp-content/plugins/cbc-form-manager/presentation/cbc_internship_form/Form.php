<?php
namespace CbcFormManager\Presentation\cbc_internship_form;

use CbcFormManager\Application\FormModuleInterface;

if (!defined('ABSPATH')) { exit; }

class Form implements FormModuleInterface
{
    public function key(): string { return 'cbc_internship_form'; }
    public function shortcode(): string { return 'cbc_internship_form'; }
    public function title(): string { return __('CBC Internship Form', 'cbc-form-manager'); }

    public function fields(): array
    {
        return [
            'name' => ['label' => __('Full Name', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'email' => ['label' => __('Email', 'cbc-form-manager'), 'type' => 'email', 'required' => true],
            'university' => ['label' => __('University', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'program' => ['label' => __('Program', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'graduation_date' => ['label' => __('Expected Graduation Date', 'cbc-form-manager'), 'type' => 'date', 'required' => false],
            'portfolio_url' => ['label' => __('Portfolio URL (optional)', 'cbc-form-manager'), 'type' => 'url', 'required' => false],
            'message' => ['label' => __('Cover Letter / Message', 'cbc-form-manager'), 'type' => 'textarea', 'required' => false],
        ];
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style('cbc-internship-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_internship_form/assets/style.css', [], '1.0.0');
        wp_enqueue_script('cbc-internship-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_internship_form/assets/script.js', ['jquery'], '1.0.0', true);
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
        <form class="cbc-form cbc-internship-form" method="post" action="<?php echo esc_url($action); ?>">
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
                <label><?php echo esc_html($fields['email']['label']); ?> *</label>
                <input type="email" name="email" value="<?php echo esc_attr($old['email'] ?? ''); ?>" />
                <?php if (!empty($errors['email'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['email']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['university']['label']); ?> *</label>
                <input type="text" name="university" value="<?php echo esc_attr($old['university'] ?? ''); ?>" />
                <?php if (!empty($errors['university'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['university']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['program']['label']); ?> *</label>
                <input type="text" name="program" value="<?php echo esc_attr($old['program'] ?? ''); ?>" />
                <?php if (!empty($errors['program'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['program']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['graduation_date']['label']); ?></label>
                <input type="date" name="graduation_date" value="<?php echo esc_attr($old['graduation_date'] ?? ''); ?>" />
                <?php if (!empty($errors['graduation_date'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['graduation_date']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['portfolio_url']['label']); ?></label>
                <input type="url" name="portfolio_url" value="<?php echo esc_attr($old['portfolio_url'] ?? ''); ?>" />
                <?php if (!empty($errors['portfolio_url'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['portfolio_url']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label><?php echo esc_html($fields['message']['label']); ?></label>
                <textarea name="message" rows="6"><?php echo esc_textarea($old['message'] ?? ''); ?></textarea>
                <?php if (!empty($errors['message'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['message']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-actions">
                <button type="submit" class="button"><?php echo esc_html__('Apply for Internship', 'cbc-form-manager'); ?></button>
            </div>
        </form>
        <?php
        return (string)ob_get_clean();
    }
}

