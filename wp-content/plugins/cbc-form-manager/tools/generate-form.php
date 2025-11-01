<?php
// Lightweight CLI scaffolder for new form modules.
// Usage: php tools/generate-form.php <form_key> [title] [shortcode]

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$scriptDir = __DIR__;
$pluginDir = dirname($scriptDir);
$presentationDir = $pluginDir . DIRECTORY_SEPARATOR . 'presentation';

function abort($msg) {
    fwrite(STDERR, $msg . "\n");
    exit(1);
}

function ok($msg) {
    fwrite(STDOUT, $msg . "\n");
}

$args = $argv;
array_shift($args); // remove script name

if (count($args) < 1) {
    abort("Usage: php tools/generate-form.php <form_key> [title] [shortcode]");
}

$key = strtolower(trim($args[0]));
// Require starting with a letter to keep a valid PHP namespace segment
if (!preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
    abort("Invalid form key. Must start with a letter and contain only lowercase letters, numbers, and underscores.");
}

$title = $args[1] ?? ucwords(str_replace('_', ' ', $key));
$shortcode = isset($args[2]) ? strtolower(trim($args[2])) : $key;
if (!preg_match('/^[a-z][a-z0-9_]*$/', $shortcode)) {
    abort("Invalid shortcode. Must start with a letter and contain only lowercase letters, numbers, and underscores.");
}

$targetDir = $presentationDir . DIRECTORY_SEPARATOR . $key;
$assetsDir = $targetDir . DIRECTORY_SEPARATOR . 'assets';

if (is_dir($targetDir)) {
    abort("Form directory already exists: " . $targetDir);
}

// Create directories
if (!is_dir($presentationDir) && !mkdir($presentationDir, 0775, true)) {
    abort("Failed to create presentation directory: " . $presentationDir);
}
if (!mkdir($targetDir, 0775, true)) {
    abort("Failed to create form directory: " . $targetDir);
}
if (!mkdir($assetsDir, 0775, true)) {
    abort("Failed to create assets directory: " . $assetsDir);
}

// Escape title for single-quoted PHP string literal in generated code
$titleEsc = str_replace(["\\", "'"], ["\\\\", "\\'"], $title);

// Files content
$template = <<<'PHP'
<?php
namespace CbcFormManager\Presentation\{{KEY}};

use CbcFormManager\Application\FormModuleInterface;

if (!defined('ABSPATH')) { exit; }

class Form implements FormModuleInterface
{
    public function key(): string { return '{{KEY}}'; }
    public function shortcode(): string { return '{{SHORTCODE}}'; }
    public function title(): string { return __('{{TITLE}}', 'cbc-form-manager'); }

    public function fields(): array
    {
        return [
            'name'  => ['label' => __('Name', 'cbc-form-manager'),  'type' => 'text',  'required' => true],
            'email' => ['label' => __('Email', 'cbc-form-manager'), 'type' => 'email', 'required' => true],
            'message' => ['label' => __('Message', 'cbc-form-manager'), 'type' => 'textarea', 'required' => false],
        ];
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style('{{KEY}}', CBC_FM_PLUGIN_URL . 'presentation/{{KEY}}/assets/style.css', [], '1.0.0');
        wp_enqueue_script('{{KEY}}', CBC_FM_PLUGIN_URL . 'presentation/{{KEY}}/assets/script.js', ['jquery'], '1.0.0', true);
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
        <form class="cbc-form {{KEY}}" method="post" action="<?php echo esc_url($action); ?>">
            <input type="hidden" name="_cbc_form_key" value="<?php echo esc_attr(
                $this->key()
            ); ?>" />
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
                <label><?php echo esc_html($fields['message']['label']); ?></label>
                <textarea name="message" rows="5"><?php echo esc_textarea($old['message'] ?? ''); ?></textarea>
                <?php if (!empty($errors['message'])): ?><div class="cbc-form-error"><?php echo esc_html($errors['message']); ?></div><?php endif; ?>
            </div>

            <div class="cbc-form-actions">
                <button type="submit" class="button"><?php echo esc_html__('Submit', 'cbc-form-manager'); ?></button>
            </div>
        </form>
        <?php
        return (string)ob_get_clean();
    }
}
PHP;

$formPhp = strtr($template, [
    '{{KEY}}' => $key,
    '{{SHORTCODE}}' => $shortcode,
    '{{TITLE}}' => $titleEsc,
]);

$styleCss = <<<CSS
.cbc-form{max-width:640px;margin:1rem 0;padding:1rem;border:1px solid #e2e8f0;border-radius:6px;background:#fff}
.cbc-form-row{margin-bottom:12px}
.cbc-form-row label{display:block;font-weight:600;margin-bottom:6px}
.cbc-form-row input,.cbc-form-row textarea,.cbc-form-row select{width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px}
.cbc-form-error{color:#b91c1c;font-size:.9rem;margin-top:4px}
.cbc-form-alert{padding:10px 12px;border-radius:4px;margin-bottom:10px}
.cbc-form-alert-success{background:#ecfdf5;color:#065f46;border:1px solid #34d399}
.cbc-form-alert-danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.cbc-form-actions{margin-top:14px}
CSS;

$scriptJs = <<<JS
(function($){
    $(function(){
        // Add interactivity here if needed
    });
})(jQuery);
JS;

$indexPhp = "<?php\n// Silence is golden.\n";

// Write files with checks
$writes = [
    [$targetDir . DIRECTORY_SEPARATOR . 'Form.php', $formPhp],
    [$targetDir . DIRECTORY_SEPARATOR . 'index.php', $indexPhp],
    [$assetsDir . DIRECTORY_SEPARATOR . 'index.php', $indexPhp],
    [$assetsDir . DIRECTORY_SEPARATOR . 'style.css', $styleCss],
    [$assetsDir . DIRECTORY_SEPARATOR . 'script.js', $scriptJs],
];

foreach ($writes as [$path, $content]) {
    $bytes = @file_put_contents($path, $content);
    if ($bytes === false) {
        abort("Failed to write file: " . $path);
    }
}

ok("Form scaffold created at: " . $targetDir);
ok("Shortcode: [" . $shortcode . "]");
ok("Remember to customize fields() and render() as needed.");
