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
            'preferred_time' => ['label' => __('Preferred Time', 'cbc-form-manager'), 'type' => 'time', 'required' => true],
            'message' => ['label' => __('Message', 'cbc-form-manager'), 'type' => 'textarea', 'required' => false],
        ];
    }

    public function enqueue_assets(): void
    {
        $style_path = CBC_FM_PLUGIN_DIR . 'presentation/cbc_appointment_form/assets/style.css';
        $script_path = CBC_FM_PLUGIN_DIR . 'presentation/cbc_appointment_form/assets/script.js';
        $ver_style = file_exists($style_path) ? (string) @filemtime($style_path) : '1.0.2';
        $ver_script = file_exists($script_path) ? (string) @filemtime($script_path) : '1.0.2';

        //wp_enqueue_style('cbc-appointment-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_appointment_form/assets/style.css', [], $ver_style);
        wp_enqueue_script('cbc-appointment-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_appointment_form/assets/script.js', ['jquery'], $ver_script, true);
        // Provide AJAX URL and action to the script
        wp_localize_script('cbc-appointment-form', 'cbcAppointmentForm', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'action'   => 'cbc_form_submit',
        ]);
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
        <form class="cbc-form cbc-appointment-form max-w-lg mx-auto border rounded-md p-8 space-y-6" method="post" action="<?php echo esc_url($action); ?>" data-cbc-form-manager="1">
            <input type="hidden" name="_cbc_form_key" value="<?php echo esc_attr($this->key()); ?>" />
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>

            <?php if (!empty($errors['_global'])): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-md text-sm"><?php echo esc_html($errors['_global']); ?></div>
            <?php else: ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-md text-sm hidden"></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded-md text-sm"><?php echo esc_html($success); ?></div>
            <?php else: ?>
                <div class="bg-green-100 text-green-700 p-3 rounded-md text-sm hidden"></div>
            <?php endif; ?>

            <!-- Name -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('name')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['name']['label']); ?> *
                </label>
                <input id="<?php echo esc_attr($id('name')); ?>" type="text" name="name" value="<?php echo esc_attr($old['name'] ?? ''); ?>"
                       class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5" />
                <?php if (!empty($errors['name'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['name']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('email')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['email']['label']); ?> *
                </label>
                <input id="<?php echo esc_attr($id('email')); ?>" type="email" name="email" value="<?php echo esc_attr($old['email'] ?? ''); ?>"
                       class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5" />
                <?php if (!empty($errors['email'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['email']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('phone')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['phone']['label']); ?>
                </label>
                <input id="<?php echo esc_attr($id('phone')); ?>" type="text" name="phone" value="<?php echo esc_attr($old['phone'] ?? ''); ?>"
                       class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5" />
                <?php if (!empty($errors['phone'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['phone']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Preferred Date -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('preferred_date')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['preferred_date']['label']); ?> *
                </label>
                <input id="<?php echo esc_attr($id('preferred_date')); ?>" type="date" name="preferred_date" value="<?php echo esc_attr($old['preferred_date'] ?? ''); ?>"
                       class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5" />
                <?php if (!empty($errors['preferred_date'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['preferred_date']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Preferred Time -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('preferred_time')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['preferred_time']['label']); ?> *
                </label>
                <input id="<?php echo esc_attr($id('preferred_time')); ?>" type="time" name="preferred_time" placeholder="e.g., 14:00" value="<?php echo esc_attr($old['preferred_time'] ?? ''); ?>"
                       class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5" />
                <?php if (!empty($errors['preferred_time'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['preferred_time']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Message -->
            <div class="cbc-form-row">
                <label for="<?php echo esc_attr($id('message')); ?>" class="block text-gray-700 font-medium mb-1">
                    <?php echo esc_html($fields['message']['label']); ?>
                </label>
                <textarea id="<?php echo esc_attr($id('message')); ?>" name="message" rows="5"
                          class="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-100 rounded-md p-2.5"><?php echo esc_textarea($old['message'] ?? ''); ?></textarea>
                <?php if (!empty($errors['message'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo esc_html($errors['message']); ?></p>
                <?php endif; ?>
            </div>

            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="<?php echo esc_attr($id('website_url')); ?>"><?php echo esc_html__('Website', 'cbc-form-manager'); ?></label>
                <input id="<?php echo esc_attr($id('website_url')); ?>" type="text" name="cbc_website_url" value="" tabindex="-1" autocomplete="off" />
            </div>

            <?php if ( function_exists( 'cbc_recaptcha_field' ) ) : ?>
                <div class="cbc-form-row" style="margin: 15px 0;">
                    <?php cbc_recaptcha_field(); ?>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="pt-4 cbc-form-actions">
                <button type="submit" class="w-full bg-[#1f5d2b] hover:bg-[#a2b917] text-white font-semibold py-2.5 px-4 rounded-md transition duration-150 ease-in-out">
                    <?php echo esc_html__('Book Appointment', 'cbc-form-manager'); ?>
                </button>
            </div>
        </form>

        <?php
        return (string)ob_get_clean();
    }
}
