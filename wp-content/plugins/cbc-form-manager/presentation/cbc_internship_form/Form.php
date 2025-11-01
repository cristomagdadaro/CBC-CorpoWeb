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
            'phone' => ['label' => __('Phone', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'university_school' => ['label' => __('University / School', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'course_program' => ['label' => __('Course / Program', 'cbc-form-manager'), 'type' => 'text', 'required' => true],
            'year_level' => ['label' => __('Year Level', 'cbc-form-manager'), 'type' => 'select', 'required' => true, 'options' => [
                '1' => __('1st Year', 'cbc-form-manager'),
                '2' => __('2nd Year', 'cbc-form-manager'),
                '3' => __('3rd Year', 'cbc-form-manager'),
                '4' => __('4th Year', 'cbc-form-manager'),
                '5' => __('5th Year', 'cbc-form-manager'),
                'G' => __('Graduate', 'cbc-form-manager'),
            ]],
            'letter_of_intent' => ['label' => __('Letter of Intent (PDF)', 'cbc-form-manager'), 'type' => 'file', 'required' => true],
        ];
    }

    public function enqueue_assets(): void
    {
        //wp_enqueue_style('cbc-internship-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_internship_form/assets/style.css', [], '1.0.1');
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

        $hasFile = true; // this form includes a file upload
        $id = function(string $name): string { return 'cbc_internship_' . $name; };

        ob_start();
        ?>
        <form class="cbc-form cbc-internship-form max-w-lg mx-auto border rounded-md p-8 space-y-6" method="post" action="<?php echo esc_url($action); ?>" <?php echo $hasFile ? 'enctype="multipart/form-data"' : ''; ?>>
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
                <label for="<?php echo esc_attr($id('phone')); ?>"><?php echo esc_html($fields['phone']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('phone')); ?>" type="text" name="phone" value="<?php echo esc_attr($old['phone'] ?? ''); ?>" />
                <?php if (!empty($errors['phone'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['phone']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('university_school')); ?>"><?php echo esc_html($fields['university_school']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('university_school')); ?>" type="text" name="university_school" value="<?php echo esc_attr($old['university_school'] ?? ''); ?>" />
                <?php if (!empty($errors['university_school'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['university_school']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('course_program')); ?>"><?php echo esc_html($fields['course_program']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('course_program')); ?>" type="text" name="course_program" value="<?php echo esc_attr($old['course_program'] ?? ''); ?>" />
                <?php if (!empty($errors['course_program'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['course_program']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('year_level')); ?>"><?php echo esc_html($fields['year_level']['label']); ?> *</label>
                <select id="<?php echo esc_attr($id('year_level')); ?>" name="year_level">
                    <option value="">—</option>
                    <?php foreach ($fields['year_level']['options'] as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected(($old['year_level'] ?? ''), $val); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['year_level'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['year_level']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('letter_of_intent')); ?>"><?php echo esc_html($fields['letter_of_intent']['label']); ?> *</label>
                <input id="<?php echo esc_attr($id('letter_of_intent')); ?>" type="file" name="letter_of_intent" accept="application/pdf" />
                <?php if (!empty($errors['letter_of_intent'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['letter_of_intent']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-actions">
                <button type="submit" class="w-full bg-[#1f5d2b] hover:bg-[#a2b917] text-white font-semibold py-2.5 px-4 rounded-md transition duration-150 ease-in-out">
                    <?php echo esc_html__('Apply for Internship', 'cbc-form-manager'); ?>
                </button>
            </div>
        </form>
        <?php
        return (string)ob_get_clean();
    }
}
