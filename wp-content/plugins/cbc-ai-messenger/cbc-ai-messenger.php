<?php
/**
 * Plugin Name: CBC AI Messenger
 * Description: Adds an AI messaging feature using a public LLM provider (OpenAIOpenRoute,OpenRouter, or LM Studio). Provides a shortcode [cbc_ai_messenger] and records all Q&A in a custom post type.
 * Version: 1.0.1
 * Author: Cristo Rey C. Magdadaro
 * License: GPL2+
 */

if (!defined('ABSPATH')) { exit; }

// Option key
const CBC_AI_OPT = 'cbc_ai_settings';

function cbc_ai_get_log_capabilities(): array {
    return array(
        'edit_post'              => 'cbc_manage_ai_logs',
        'read_post'              => 'cbc_manage_ai_logs',
        'delete_post'            => 'cbc_manage_ai_logs',
        'edit_posts'             => 'cbc_manage_ai_logs',
        'edit_others_posts'      => 'cbc_manage_ai_logs',
        'publish_posts'          => 'cbc_manage_ai_logs',
        'read_private_posts'     => 'cbc_manage_ai_logs',
        'delete_posts'           => 'cbc_manage_ai_logs',
        'delete_private_posts'   => 'cbc_manage_ai_logs',
        'delete_published_posts' => 'cbc_manage_ai_logs',
        'delete_others_posts'    => 'cbc_manage_ai_logs',
        'edit_private_posts'     => 'cbc_manage_ai_logs',
        'edit_published_posts'   => 'cbc_manage_ai_logs',
        'create_posts'           => 'cbc_manage_ai_logs',
    );
}

function cbc_ai_register_capabilities(): void {
    $role = get_role('administrator');

    if (!$role) {
        return;
    }

    foreach (array_unique(array_values(cbc_ai_get_log_capabilities())) as $cap) {
        $role->add_cap($cap);
    }
}

add_action('init', 'cbc_ai_register_capabilities', 5);

// Register Custom Post Type for message logs
add_action('init', function(){
    register_post_type('cbc_ai_message', array(
        'labels' => array(
            'name' => 'AI Messages',
            'singular_name' => 'AI Message',
            'menu_name' => 'AI Messages',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 25,
        'menu_icon' => 'dashicons-format-chat',
        'supports' => array('title'),
        'capabilities' => cbc_ai_get_log_capabilities(),
        'map_meta_cap' => false,
    ));
});

register_activation_hook(__FILE__, function(){
    cbc_ai_register_capabilities();
    // Ensure CPT is registered on activation and flush rewrite
    do_action('init');
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function(){
    flush_rewrite_rules();
});

// Settings Page
add_action('admin_menu', function(){
    add_options_page(
        'CBC AI Messenger',
        'CBC AI Messenger',
        'manage_options',
        'cbc-ai-messenger',
        'cbc_ai_render_settings_page'
    );
});

add_action('admin_init', function(){
    register_setting('cbc_ai_group', CBC_AI_OPT, 'cbc_ai_sanitize_settings');

    add_settings_section('cbc_ai_main', '', '__return_null', 'cbc-ai-messenger');

    add_settings_field('cbc_ai_provider', 'Provider', 'cbc_ai_field_provider', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_provider'));
    add_settings_field('cbc_ai_field_api_url', 'API URL', 'cbc_ai_field_api_url', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_field_api_url'));
    add_settings_field('cbc_ai_api_key', 'API Key', 'cbc_ai_field_api_key', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_api_key'));
    add_settings_field('cbc_ai_openai_org', 'OpenAI Organization (optional)', 'cbc_ai_field_openai_org', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_openai_org'));
    add_settings_field('cbc_ai_model', 'Model', 'cbc_ai_field_model', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_model'));
    add_settings_field('cbc_ai_temperature', 'Temperature', 'cbc_ai_field_temperature', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_temperature'));
    add_settings_field('cbc_ai_top_p', 'Top P', 'cbc_ai_field_top_p', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_top_p'));
    add_settings_field('cbc_ai_frequency_penalty', 'Frequency Penalty', 'cbc_ai_field_frequency_penalty', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_frequency_penalty'));
    add_settings_field('cbc_ai_presence_penalty', 'Presence Penalty', 'cbc_ai_field_presence_penalty', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_presence_penalty'));
    add_settings_field('cbc_ai_max_tokens', 'Max Tokens', 'cbc_ai_field_max_tokens', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_max_tokens'));
    add_settings_field('cbc_ai_system_prompt', 'System Prompt', 'cbc_ai_field_system_prompt', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_system_prompt'));
    add_settings_field('cbc_ai_enforce_scope', 'Enforce Topic Scope', 'cbc_ai_field_enforce_scope', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_enforce_scope'));

    // New settings for site context
    add_settings_field('cbc_ai_include_site_context', 'Use Site Content (RAG)', 'cbc_ai_field_include_site_context', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_include_site_context'));
    add_settings_field('cbc_ai_site_context_types', 'Content Types', 'cbc_ai_field_site_context_types', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_site_context_types'));
    add_settings_field('cbc_ai_site_context_limit', 'Results Limit', 'cbc_ai_field_site_context_limit', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_site_context_limit'));
    add_settings_field('cbc_ai_site_context_chars', 'Context Char Budget', 'cbc_ai_field_site_context_chars', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_site_context_chars'));
    // New: Render in Footer toggle
    add_settings_field('cbc_ai_render_in_footer', 'Render in Footer', 'cbc_ai_field_render_in_footer', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_render_in_footer'));
});

function cbc_ai_default_settings(){
    return array(
        'provider' => 'openrouter', // openai|openrouter|lmstudio
        'api_url' => 'http://localhost:1234',
        'api_key' => '',
        'model' => 'openai/gpt-4o-mini',
        'temperature' => 0.2,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
        'max_tokens' => 1024,
        'system_prompt' => "You are the official AI assistant of the Department of Agriculture - Crop Biotechnology Center (DA-CBC), Philippines. " .
            "Respond professionally, clearly, and helpfully as a public-facing representative of DA-CBC. " .
            "Use institutional facts accurately: DA-CBC is a state-of-the-art research facility located inside the Philippine Rice Research Institute (PhilRice) compound in Brgy. Maligaya, Science City of Muñoz, Nueva Ecija 3119, Philippines; the center is headed by Director Dr. Roel R. Suralta. " .
            "Its mandate is to generate improved agricultural technologies, increase crop productivity, support food security, and develop climate-resilient crops to help attain UN SDG 2 (Zero Hunger). " .
            "Core research areas include plant/crop biotechnology, genomics, bioinformatics, computational breeding, molecular breeding, genetic engineering, germplasm enhancement, and tissue culturing. " .
            "Key crops include rice (including Golden Rice / Malusog 1 and high iron/zinc rice), corn, coconut, coffee, sugarcane, banana, abaca, cotton, and cassava. " .
            "DA-CBC also uses the OneCBC Portal for laboratory equipment logging, venue rentals, and experiment monitoring. " .
            "When uncertain, state uncertainty briefly and suggest contacting DA-CBC through official channels for confirmation.",
        'enforce_scope' => 1,
        'openai_org' => '',
        // New defaults for site context
        'include_site_context' => 1,
        'site_context_types' => 'post,page',
        'site_context_limit' => 5,
        'site_context_chars' => 2000,
        // New
        'render_in_footer' => 0,
    );
}

function cbc_ai_get_settings(): array {
    $opts = get_option(CBC_AI_OPT);
    if (!is_array($opts)) { $opts = array(); }
    return wp_parse_args($opts, cbc_ai_default_settings());
}

function cbc_ai_sanitize_settings($input): array {
    if (!is_array($input)) { $input = array(); }
    $out = cbc_ai_get_settings();
    $prov = $input['provider'] ?? '';
    $out['provider'] = in_array($prov, array('openai','openrouter','lmstudio'), true) ? $prov : $out['provider'];
    $out['api_url'] = esc_url_raw($input['api_url'] ?? $out['api_url']);
    $out['api_key'] = sanitize_text_field(trim((string)($input['api_key'] ?? $out['api_key'])));
    $out['openai_org'] = sanitize_text_field($input['openai_org'] ?? $out['openai_org']);
    $out['model'] = sanitize_text_field($input['model'] ?? $out['model']);
    $out['temperature'] = is_numeric($input['temperature'] ?? null) ? max(0, min(2, floatval($input['temperature']))) : $out['temperature'];
    $out['top_p'] = is_numeric($input['top_p'] ?? null) ? max(0, min(1, floatval($input['top_p']))) : $out['top_p'];
    $out['frequency_penalty'] = is_numeric($input['frequency_penalty'] ?? null) ? max(-2, min(2, floatval($input['frequency_penalty']))) : $out['frequency_penalty'];
    $out['presence_penalty'] = is_numeric($input['presence_penalty'] ?? null) ? max(-2, min(2, floatval($input['presence_penalty']))) : $out['presence_penalty'];
    $out['max_tokens'] = is_numeric($input['max_tokens'] ?? null) ? max(1, min(4096, intval($input['max_tokens']))) : $out['max_tokens'];
    $out['system_prompt'] = wp_kses_post($input['system_prompt'] ?? $out['system_prompt']);
    $out['enforce_scope'] = !empty($input['enforce_scope']) ? 1 : 0;
    // New sanitization for site context
    $out['include_site_context'] = !empty($input['include_site_context']) ? 1 : 0;
    $out['site_context_types'] = sanitize_text_field($input['site_context_types'] ?? $out['site_context_types']);
    $out['site_context_limit'] = is_numeric($input['site_context_limit'] ?? null) ? max(1, min(10, intval($input['site_context_limit']))) : $out['site_context_limit'];
    $out['site_context_chars'] = is_numeric($input['site_context_chars'] ?? null) ? max(500, min(8000, intval($input['site_context_chars']))) : $out['site_context_chars'];
    // New: footer toggle
    $out['render_in_footer'] = !empty($input['render_in_footer']) ? 1 : 0;
    return $out;
}

function cbc_ai_render_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1>CBC AI Messenger</h1>
        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
            <?php settings_fields('cbc_ai_group'); ?>
            <?php do_settings_sections('cbc-ai-messenger'); ?>
            <?php submit_button(); ?>
        </form>
        <p class="description">Use the shortcode <code>[cbc_ai_messenger]</code> to render the chat form on any page.</p>
    </div>
    <?php
}

function cbc_ai_field_provider($args): void {
    $opts = array('openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'lmstudio' => 'LM Studio');
    $val = cbc_ai_get_settings()['provider'] ?? '';
    $html = "<select id='" . esc_attr($args['label_for']) . "' name='" . esc_attr(CBC_AI_OPT) . "[provider]'>";
    foreach ($opts as $k => $v) {
        $html .= "<option value='" . esc_attr($k) . "'" . selected($val, $k, false) . ">" . esc_html($v) . "</option>";
    }
    $html .= "</select>";
    echo $html;
}

function cbc_ai_field_api_url($args): void {
    $val = cbc_ai_get_settings()['api_url'] ?? '';
    // Use esc_attr() for security and remove non-functional button
    echo "<div><input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[api_url]' value='" . esc_attr($val) . "' class='regular-text' />";
    echo "<p class='description'>Enter the base URL for local providers like LM Studio (e.g., <code>http://localhost:1234</code>). This is ignored for OpenAI and OpenRouter.</p></div>";
}

function cbc_ai_field_api_key($args): void {
    $val = cbc_ai_get_settings()['api_key'] ?? '';
    echo "<input type='password' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[api_key]' value='" . esc_attr($val) . "' class='regular-text' />";
}

function cbc_ai_field_openai_org($args): void {
    $val = cbc_ai_get_settings()['openai_org'] ?? '';
    echo "<input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[openai_org]' value='" . esc_attr($val) . "' class='regular-text' />";
}

function cbc_ai_field_model($args): void {
    $val = cbc_ai_get_settings()['model'] ?? '';
    echo "<input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[model]' value='" . esc_attr($val) . "' class='regular-text' />";
    echo "<p class='description'>e.g., openai/gpt-4o-mini, google/gemini-flash-1.5, anthropic/claude-3-haiku</p>";
}

function cbc_ai_field_temperature($args): void {
    $val = cbc_ai_get_settings()['temperature'] ?? 0.3;
    echo "<input type='number' step='0.1' min='0' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[temperature]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_top_p($args): void {
    $val = cbc_ai_get_settings()['top_p'] ?? 1;
    echo "<input type='number' step='0.1' min='0' max='1' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[top_p]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_frequency_penalty($args): void {
    $val = cbc_ai_get_settings()['frequency_penalty'] ?? 0;
    echo "<input type='number' step='0.1' min='-2' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[frequency_penalty]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_presence_penalty($args): void {
    $val = cbc_ai_get_settings()['presence_penalty'] ?? 0;
    echo "<input type='number' step='0.1' min='-2' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[presence_penalty]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_max_tokens($args): void {
    $val = cbc_ai_get_settings()['max_tokens'] ?? 1024;
    echo "<input type='number' step='1' min='1' max='4096' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[max_tokens]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_system_prompt($args): void {
    $val = cbc_ai_get_settings()['system_prompt'] ?? '';
    echo "<textarea id='{$args['label_for']}' name='" . CBC_AI_OPT . "[system_prompt]' rows='5' class='large-text'>" . esc_textarea($val) . "</textarea>";
}

function cbc_ai_field_enforce_scope($args): void {
    $val = cbc_ai_get_settings()['enforce_scope'] ?? 0;
    echo "<input type='checkbox' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[enforce_scope]' value='1' " . checked(1, $val, false) . " />";
}

function cbc_ai_field_include_site_context($args): void {
    $val = cbc_ai_get_settings()['include_site_context'] ?? 1;
    echo "<input type='checkbox' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[include_site_context]' value='1' " . checked(1, $val, false) . " />";
    echo "<p class='description'>Include top matching posts/pages as context so the model can answer questions about your site content.</p>";
}

function cbc_ai_field_site_context_types($args): void {
    $val = cbc_ai_get_settings()['site_context_types'] ?? 'post,page';
    echo "<input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[site_context_types]' value='" . esc_attr($val) . "' class='regular-text' />";
    echo "<p class='description'>Comma-separated post types to search, e.g., post,page</p>";
}

function cbc_ai_field_site_context_limit($args): void {
    $val = cbc_ai_get_settings()['site_context_limit'] ?? 5;
    echo "<input type='number' min='1' max='10' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[site_context_limit]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_site_context_chars($args): void{
    $val = cbc_ai_get_settings()['site_context_chars'] ?? 2000;
    echo "<input type='number' min='500' max='8000' step='100' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[site_context_chars]' value='" . esc_attr($val) . "' class='small-text' />";
}

// New render in footer field
function cbc_ai_field_render_in_footer($args): void{
    $val = cbc_ai_get_settings()['render_in_footer'] ?? 0;
    echo "<input type='checkbox' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[render_in_footer]' value='1' " . checked(1, $val, false) . " />";
    echo "<p class='description'>Outputs the floating chat in the site footer on pages that don’t already include the shortcode.</p>";
}

// Shortcode to render the messenger UI
add_shortcode('cbc_ai_messenger', function($atts){
    $atts = shortcode_atts(array(
        'placeholder' => 'Ask me about Golden Rice, tissue culturing, molecular breeding, or DA-CBC facilities...',
        'floating' => '1',
        'title' => 'Chatbot',
    ), $atts, 'cbc_ai_messenger');

    // Enqueue small fallback CSS to ensure mobile fullscreen + scroll-lock behaviors
    wp_enqueue_style('cbc-ai-fallback', plugins_url('assets/css/cbc-ai-fallback.css', __FILE__), array(), '1.2.0');
    
    // Enqueue main messenger CSS
    wp_enqueue_style('cbc-ai-messenger', plugins_url('assets/css/cbc-ai-messenger.css', __FILE__), array(), '1.5.0');

    // Determine reCAPTCHA site key early so we can enqueue reCAPTCHA before the messenger script
    // Skip reCAPTCHA entirely on local/development environments
    $recaptcha_site_key = '';
    if (!cbc_ai_is_local_or_dev()) {
        if (function_exists('cbc_ai_get_recaptcha_site_key')) {
            $recaptcha_site_key = cbc_ai_get_recaptcha_site_key();
        }
    }
    if ($recaptcha_site_key) {
        // Enqueue reCAPTCHA v3 loader with site key so grecaptcha becomes available
        wp_enqueue_script('cbc-ai-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($recaptcha_site_key), array(), null, true);
        // Enqueue messenger script and declare dependency on the reCAPTCHA loader so it prints after
        wp_enqueue_script('cbc-ai-messenger', plugins_url('assets/js/cbc-ai-messenger.js', __FILE__), array('jquery','cbc-ai-recaptcha'), '1.5.0', true);
    } else {
        // No reCAPTCHA: enqueue messenger normally
        wp_enqueue_script('cbc-ai-messenger', plugins_url('assets/js/cbc-ai-messenger.js', __FILE__), array('jquery'), '1.5.0', true);
    }

    wp_localize_script('cbc-ai-messenger', 'CBCAI', array(
        'restUrl' => esc_url_raw(rest_url('cbc-ai/v1/ask')),
        'nonce' => is_user_logged_in() ? wp_create_nonce('wp_rest') : '',
        'placeholder' => (string)$atts['placeholder'],
        'title' => (string)$atts['title'],
        'recaptchaSiteKey' => $recaptcha_site_key,
        'recaptchaAction' => 'cbc_ai_chat',
    ));

    ob_start();
    ?>
    <div id="cbc-ai-chat-container" aria-hidden="false">
        <div id="cbc-ai-chat-panel" role="complementary" class="cbc-ai-box cbc-ai-panel shadow-lg bg-white rounded-md flex flex-col">
            <div class="cbc-ai-header flex items-center justify-between text-white px-5 py-2">
                <div class="cbc-ai-title font-semibold mr-2"><?php echo esc_html($atts['title']); ?></div>
                <button type="button" class="cbc-ai-clear-history text-xs" title="Clear conversation history">Clear</button>
            </div>
            <div class="cbc-ai-body p-4 flex flex-col gap-2">
                <div class="cbc-ai-user-info w-full mb-1 hidden text-sm text-gray-700"></div>
                <span class="cbc-ai-convo-label hidden text-xs text-gray-400">Conversation:</span>
                <div class="cbc-ai-log hidden bg-gray-300 rounded p-2 my-1 min-h-[80px] max-h-64 overflow-auto" aria-live="polite"></div>
                <form class="cbc-ai-form flex flex-col gap-2 mt-1">
                    <div class="cbc-ai-contact-fields flex flex-col md:flex-row gap-2 w-full">
                        <input type="text" name="name" class="cbc-ai-input-name flex-1 border rounded px-4 py-3" placeholder="Your name" aria-label="Your name" />
                        <input type="email" name="email" class="cbc-ai-input-email flex-1 border rounded px-4 py-3" placeholder="Your email" aria-label="Your email" />
                        <label for="cbc-ai-website" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">Leave this field empty</label>
                        <input id="cbc-ai-website" type="text" name="website" class="cbc-ai-input-website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;" aria-hidden="true" />
                    </div>
                    <textarea name="message" class="cbc-ai-input border rounded px-4 py-3 min-h-fit" style="height: 100px;" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" aria-label="Your question"></textarea>
                    <div class="flex items-center justify-center">
                        <?php if (function_exists('cbc_recaptcha_field')) { cbc_recaptcha_field(); } ?>
                    </div>
                    <button type="submit" class="cbc-ai-send bg-green-700 hover:bg-green-800 text-white rounded px-4 py-2">Ask</button>
                </form>
                <div class="cbc-ai-note text-xs text-gray-500 mt-1">This AI Chatbot provides information limited to DA-CBC and its official website content. By using this service, you acknowledge that you have read and agreed to our <a href="/about-us/terms-and-conditions/">Terms and Conditions</a> and <a href="/about-us/privacy-policy/">Privacy Policy</a>.</div>
            </div>
        </div>
        <button id="cbc-ai-chat-toggle" aria-expanded="true" aria-controls="cbc-ai-chat-panel" class="cbc-ai-chat-toggle" title="Toggle AI Chat" type="button">
            <span class="cbc-ai-icon-expanded" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                </svg>
            </span>
            <span class="cbc-ai-icon-collapsed hidden" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" class="w-6 h-6">
                  <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg>
            </span>
            <span class="sr-only">Toggle AI Chat</span>
        </button>
    </div>
    <?php
    return ob_get_clean();
});

// REST API endpoint
add_action('rest_api_init', function(){
    register_rest_route('cbc-ai/v1', '/ask', array(
        'methods' => 'POST',
        'callback' => 'cbc_ai_rest_ask',
        'permission_callback' => 'cbc_ai_rest_permission',
        'args' => array(
            'message' => array('required' => true,'type' => 'string'),
            'name'    => array('required' => true,'type' => 'string'),
            'email'   => array('required' => true,'type' => 'string'),
        )
    ));
});

function cbc_ai_rest_permission( WP_REST_Request $request ) {
    if ('POST' !== strtoupper((string) $request->get_method())) {
        return new WP_Error('cbc_ai_method_not_allowed', __('Only POST requests are allowed for this endpoint.', 'cbc-ai-messenger'), array('status' => 405));
    }

    if (!cbc_ai_is_local_or_dev() && '' === cbc_ai_get_recaptcha_secret()) {
        return new WP_Error('cbc_ai_not_ready', __('The AI service is temporarily unavailable.', 'cbc-ai-messenger'), array('status' => 503));
    }

    return true;
}

/**
 * Check if the site is running in local/development environment.
 */
function cbc_ai_is_local_or_dev(): bool {
    // Check WordPress environment type (WordPress 5.5+)
    if (function_exists('wp_get_environment_type')) {
        $env = wp_get_environment_type();
        if (in_array($env, array('local', 'development'), true)) {
            return true;
        }
    }
    
    // Check WP_DEBUG constant
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return true;
    }
    
    // Check if localhost or 127.0.0.1
    $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
    if (stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false) {
        return true;
    }
    
    return false;
}

function cbc_ai_is_contact_request($text, &$type): bool {
    $text = strtolower((string)$text);
    $type = 'general';
    $address_k = array('address','location','where','located','reside','where is');
    $phone_k = array('phone','telephone','tel','contact number','phone number');
    $email_k = array('email','e-mail');
    foreach ($address_k as $k) { if (strpos($text, $k) !== false) { $type = 'address'; return true; } }
    foreach ($phone_k as $k)   { if (strpos($text, $k) !== false) { $type = 'phone';   return true; } }
    foreach ($email_k as $k)   { if (strpos($text, $k) !== false) { $type = 'email';   return true; } }
    $contact_k = array('contact','facebook','fb','how to contact','how can i contact','office','visit');
    foreach ($contact_k as $k) { if (strpos($text, $k) !== false) return true; }
    if (preg_match('/\bwhere\b.*\b(locate|located|is)\b/', $text)) { $type = 'address'; return true; }
    if (preg_match('/\bhow\b.*\b(contact|reach)\b/', $text)) return true;
    return false;
}

function cbc_ai_get_client_ip(): string {
    $candidates = array(
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['HTTP_CLIENT_IP'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    );

    foreach ($candidates as $candidate) {
        if (!$candidate) {
            continue;
        }

        foreach (array_map('trim', explode(',', (string) $candidate)) as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

function cbc_ai_anonymize_ip(string $ip): string {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        $parts[3] = '0';
        return implode('.', $parts);
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        $parts = array_pad($parts, 8, '0');
        $parts[4] = '0';
        $parts[5] = '0';
        $parts[6] = '0';
        $parts[7] = '0';
        return implode(':', $parts);
    }

    return '';
}

function cbc_ai_hash_value(string $value): string {
    if ($value === '') {
        return '';
    }

    return hash_hmac('sha256', $value, wp_salt('auth'));
}

function cbc_ai_check_rate_limits(string $ip, string $email) {
    $ip_hash = md5($ip);
    $last_key = 'cbc_ai_last_' . $ip_hash;
    $last = (int) get_transient($last_key);

    if ($last && (time() - $last < 30)) {
        return new WP_Error('cbc_ai_rate_limit_short', __('Please wait a little longer before asking another question.', 'cbc-ai-messenger'), array('status' => 429));
    }

    $hour_key = 'cbc_ai_hour_' . $ip_hash;
    $hour_count = (int) get_transient($hour_key);
    if ($hour_count >= 10) {
        return new WP_Error('cbc_ai_rate_limit_hour', __('You have reached the hourly question limit. Please try again later.', 'cbc-ai-messenger'), array('status' => 429));
    }

    $email_key = 'cbc_ai_email_day_' . md5(strtolower($email));
    $email_count = (int) get_transient($email_key);
    if ($email_count >= 20) {
        return new WP_Error('cbc_ai_rate_limit_day', __('You have reached the daily question limit for this email address.', 'cbc-ai-messenger'), array('status' => 429));
    }

    set_transient($last_key, time(), MINUTE_IN_SECONDS);
    set_transient($hour_key, $hour_count + 1, HOUR_IN_SECONDS);
    set_transient($email_key, $email_count + 1, DAY_IN_SECONDS);

    return true;
}

function cbc_ai_rest_ask( WP_REST_Request $req ): WP_REST_Response {
    $message = trim((string)$req->get_param('message'));
    $name = trim((string)$req->get_param('name'));
    $email = trim((string)$req->get_param('email'));
    $honeypot = trim((string)$req->get_param('website'));

    if ($honeypot !== '') {
        return new WP_REST_Response(array('error' => 'Request blocked.'), 403);
    }

    // Server-side validation: require name and valid email
    if ($name === '') { return new WP_REST_Response(array('error' => 'Name is required'), 400); }
    $email_s = sanitize_email($email);
    if ($email_s === '' || !is_email($email_s)) { return new WP_REST_Response(array('error' => 'A valid email is required'), 400); }
    if (!preg_match('/@gmail\.com$/i', $email_s)) { return new WP_REST_Response(array('error' => 'A valid Gmail address is required'), 400); }
    // normalize sanitized values
    $name = sanitize_text_field($name);
    $email = $email_s;

    if ($message === '') { return new WP_REST_Response(array('error' => 'Empty message'), 400); }
    if (mb_strlen($message) > 1200) { return new WP_REST_Response(array('error' => 'Message is too long.'), 400); }
    $message = sanitize_textarea_field($message);

    // --- reCAPTCHA verification (if secret configured) ---
    // Skip reCAPTCHA validation on local/development environments
    $is_local = cbc_ai_is_local_or_dev();
    $recaptcha_secret = ($is_local) ? '' : (function_exists('cbc_ai_get_recaptcha_secret') ? cbc_ai_get_recaptcha_secret() : '');
    $recaptcha_token = trim((string)$req->get_param('recaptcha_token'));
    // Prepare admin-only debug info (will be injected into responses for admins)
    $admin_debug = array(
        'recaptcha_secret_configured' => $recaptcha_secret !== '',
        'recaptcha_token_provided' => $recaptcha_token !== '',
        'is_local_dev' => $is_local,
    );
    if ($recaptcha_secret !== '') {
        if ($recaptcha_token === '') {
            // For debugging: allow administrators to bypass reCAPTCHA token requirement so they can test the flow.
            if (is_user_logged_in() && current_user_can('manage_options')) {
                error_log('cbc-ai-messenger: reCAPTCHA token missing but bypassed for admin ' . get_current_user_id());
            } else {
                $resp = new WP_REST_Response(array('error' => 'reCAPTCHA token missing'));
                if (is_user_logged_in() && current_user_can('manage_options')) {
                    $resp->set_data(array('error' => 'reCAPTCHA token missing', 'debug' => $admin_debug));
                }
                return $resp;
            }
        }
        $ip = cbc_ai_get_client_ip();
        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_token,
                'remoteip' => $ip,
            ),
            'timeout' => 15,
        ));
        if (is_wp_error($verify)) {
            return new WP_REST_Response(array('error' => 'reCAPTCHA verification failed'), 403);
        }
        $body = wp_remote_retrieve_body($verify);
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['success'])) {
            $resp = new WP_REST_Response(array('error' => 'reCAPTCHA validation failed'), 403);
            if (is_user_logged_in() && current_user_can('manage_options')) { $resp->set_data(array('error' => 'reCAPTCHA validation failed', 'debug' => $data)); }
            return $resp;
        }
        // If v3, optionally check score threshold
        if (isset($data['score']) && floatval($data['score']) < 0.45) {
            $resp = new WP_REST_Response(array('error' => 'reCAPTCHA score too low'), 403);
            if (is_user_logged_in() && current_user_can('manage_options')) { $resp->set_data(array('error' => 'reCAPTCHA score too low', 'debug' => $data)); }
            return $resp;
        }
    }

    $ip = cbc_ai_get_client_ip();
    $limit = cbc_ai_check_rate_limits($ip, $email);
    if (is_wp_error($limit)) {
        return new WP_REST_Response(array('error' => $limit->get_error_message()), 429);
    }

    $opts = cbc_ai_get_settings();

    // Minimal local reply for contact/info request
    $contact_type = '';
    if (cbc_ai_is_contact_request($message, $contact_type)) {
        $address_plain = "PhilRice Compound, Brgy. Maligaya, Science City of Muñoz, Nueva Ecija 3119, Philippines";
        $tel_plain = "+63 908 889 7135";
        $email_plain = "cropbiotechcenter@gmail.com";
        switch ($contact_type) {
            case 'address': $reply = $address_plain; break;
            case 'phone':   $reply = $tel_plain; break;
            case 'email':   $reply = $email_plain; break;
            default:        $reply = "Address: $address_plain\nPhone: $tel_plain\nEmail: $email_plain"; break;
        }
        $post_id = cbc_ai_log_message($message, $reply, array('provider' => 'local','model' => '','status' => 'local_contact'), $name, $email);
        return new WP_REST_Response(array('reply' => wp_kses_post(nl2br(esc_html($reply)))), 200);
    }

    // Let the model decide scope based on the system prompt; no keyword pre-blocking
    $api_key = cbc_ai_get_effective_api_key($opts);
    if ($api_key === '') {
        $reply = 'The AI service is not configured. Please contact the site administrator.';
        $post_id = cbc_ai_log_message($message, $reply, array('provider' => 'none','model' => '','status' => 'not_configured'), $name, $email);
        return new WP_REST_Response(array('reply' => $reply), 200);
    }

    $result = cbc_ai_call_provider($opts, $message, $api_key);
    if (is_wp_error($result)) {
        $reply = 'Sorry, I could not generate a response right now.';
        $error_data = $result->get_error_data();
        $post_id = cbc_ai_log_message($message, $reply, array('provider' => $opts['provider'],'model' => $opts['model'],'status' => 'error','error' => $result->get_error_message(),'error_data' => $error_data), $name, $email);
        $response = array('reply' => $reply);
        if (is_user_logged_in() && current_user_can('manage_options')) {
            $response['debug'] = array(
                'provider_error' => $result->get_error_message(),
                'provider_error_data' => $error_data,
                'provider' => $opts['provider'],
                'model' => $opts['model'],
            );
        }
        return new WP_REST_Response($response, 200);
    }

    $reply = (string)($result['reply'] ?? '');
    if ($reply === '') { $reply = 'I do not have an answer at the moment.'; }

    $post_id = cbc_ai_log_message($message, $reply, array('provider' => $opts['provider'],'model' => $opts['model'],'status' => 'ok','usage' => $result['usage'] ?? array()), $name, $email);
    $response_body = array('reply' => wp_kses_post($reply));
    if (is_user_logged_in() && current_user_can('manage_options')) { $response_body['debug'] = $admin_debug; }
    return new WP_REST_Response($response_body, 200);
}

function cbc_ai_log_message($question, $answer, $meta = array(), $name = '', $email = ''): WP_Error|int {
    $title = wp_trim_words($question, 10, '...');
    $post_id = wp_insert_post(array('post_type' => 'cbc_ai_message','post_status' => 'private','post_title' => $title,));
    if (!$post_id || is_wp_error($post_id)) { return 0; }
    update_post_meta($post_id, '_cbc_ai_question', wp_kses_post($question));
    update_post_meta($post_id, '_cbc_ai_answer', wp_kses_post($answer));
    update_post_meta($post_id, '_cbc_ai_meta', $meta);
    if (!empty($name)) update_post_meta($post_id, '_cbc_ai_name', sanitize_text_field($name));
    if (!empty($email)) update_post_meta($post_id, '_cbc_ai_email', sanitize_email($email));
    $client_ip = cbc_ai_get_client_ip();
    update_post_meta($post_id, '_cbc_ai_ip_prefix', cbc_ai_anonymize_ip($client_ip));
    update_post_meta($post_id, '_cbc_ai_ip_hash', cbc_ai_hash_value($client_ip));
    update_post_meta($post_id, '_cbc_ai_ua_hash', cbc_ai_hash_value((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')));
    if (is_user_logged_in()) update_post_meta($post_id, '_cbc_ai_user_id', get_current_user_id());
    return $post_id;
}

function cbc_ai_normalize_model($provider, $model): bool|string {
    $model = trim((string)$model);
    if ($provider === 'openai') {
        if (strpos($model, '/') !== false) { $parts = explode('/', $model, 2); $model = end($parts); }
    } else {
        if (strpos($model, '/') === false) {
            if (preg_match('/^(gpt-4|gpt-4o|gpt-3\.5|o[0-9]|text-|gpt-)/i', $model)) { $model = 'openai/' . $model; }
        }
    }
    return $model;
}

function cbc_ai_build_site_context($query, $opts): string {
    $query = trim((string)$query);
    if ($query === '') return '';

    $types_csv = (string)($opts['site_context_types'] ?? 'post,page');
    $types = array_filter(array_map('trim', explode(',', $types_csv)), function($t){ return $t !== ''; });
    if (empty($types)) { $types = array('post'); }

    $limit = max(1, min(10, intval($opts['site_context_limit'] ?? 5)));
    $budget = max(500, min(8000, intval($opts['site_context_chars'] ?? 2000)));

    $q = new WP_Query(array(
        's' => $query,
        'post_type' => $types,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'ignore_sticky_posts' => true,
    ));

    if (!$q->have_posts()) { $GLOBALS['cbc_ai_last_site_items'] = array(); return ''; }

    $ctx = array();
    $items = array();
    $total = 0;
    $i = 1;
    while ($q->have_posts()){
        $q->the_post();
        $title = get_the_title();
        $url = get_permalink();
        $date = get_the_date('Y-m-d');
        $content = get_the_excerpt();
        if (!$content) { $content = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 60, '...'); }
        $entry = $i . ". " . $title . " (" . $date . ")\n" .
                 "URL: " . $url . "\n" .
                 "Excerpt: " . $content;
        $len = strlen($entry) + 2;
        if ($total + $len > $budget) { break; }
        $ctx[] = $entry;
        $items[] = array('title' => $title, 'url' => $url, 'date' => $date);
        $total += $len;
        $i++;
    }
    wp_reset_postdata();

    $GLOBALS['cbc_ai_last_site_items'] = $items;

    if (empty($ctx)) return '';
    return "Site content snippets (use if relevant; cite URLs when helpful):\n" . implode("\n\n", $ctx);
}

function cbc_ai_call_provider($opts, $message, $api_key = ''){
    $system = (string)$opts['system_prompt'];
    $guard = "Scope policy: Answer only topics directly related to DA-CBC, crop biotechnology, Philippine agriculture, and DA-CBC-aligned scientific domains: plant/crop biotechnology, genomics, bioinformatics, computational breeding, molecular breeding, genetic engineering, germplasm enhancement, tissue culturing, and DA-CBC facilities/services such as the OneCBC Portal. " .
        "You may discuss DA-CBC key crops: rice (including Golden Rice / Malusog 1 and high iron/zinc rice), corn, coconut, coffee, sugarcane, banana, abaca, cotton, and cassava. " .
        "Politely decline requests outside scope, including non-agricultural topics, partisan politics, and general software coding help unrelated to agricultural bioinformatics/computational breeding. " .
        "For out-of-scope requests, provide a brief refusal and offer to help with an in-scope DA-CBC or crop biotechnology question.";

    $provider = $opts['provider'] ?? 'openrouter';
    $model = cbc_ai_normalize_model($provider, $opts['model'] ?? '');

    $messages = array(array('role' => 'system', 'content' => $system));
    if (!empty($opts['enforce_scope'])) { $messages[] = array('role' => 'system', 'content' => $guard); }

    $used_site_ctx = false;
    if (!empty($opts['include_site_context'])) {
        $ctx = cbc_ai_build_site_context($message, $opts);
        if ($ctx !== '') {
            $messages[] = array('role' => 'system', 'content' => $ctx);
            $used_site_ctx = true;
        }
    }

    $messages[] = array('role' => 'user', 'content' => $message);

    $body = array(
        'model' => (string)$model,
        'temperature' => floatval($opts['temperature']),
        'top_p' => floatval($opts['top_p']),
        'frequency_penalty' => floatval($opts['frequency_penalty']),
        'presence_penalty' => floatval($opts['presence_penalty']),
        'max_tokens' => intval($opts['max_tokens']),
        'messages' => $messages,
    );

    $headers = array('Content-Type' => 'application/json');

    if ($provider === 'openrouter') {
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $headers['Authorization'] = 'Bearer ' . $api_key;
        $headers['HTTP-Referer'] = home_url('/');
        $headers['X-Title'] = get_bloginfo('name');

    } elseif ($provider === 'lmstudio') {
        // Use the saved API URL or a sensible default for LM Studio
        $base_url = !empty($opts['api_url']) ? $opts['api_url'] : 'http://localhost:1234';
        $url = rtrim($base_url, '/') . '/v1/chat/completions';

        // LM Studio can use an API key (often 'lm-studio'), send if provided
        if ($api_key !== '') {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

    } else { // Default to OpenAI
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers['Authorization'] = 'Bearer ' . $api_key;
        $org = cbc_ai_get_effective_openai_org($opts);
        if ($org !== '') { $headers['OpenAI-Organization'] = $org; }
    }

    $request_args = array('headers' => $headers, 'body' => wp_json_encode($body), 'timeout' => 45);
    $response = wp_remote_post($url, $request_args);
    if (is_wp_error($response)) {
        return new WP_Error('cbc_ai_transport', $response->get_error_message(), array('provider' => $provider, 'model' => $model));
    }

    $code = wp_remote_retrieve_response_code($response);
    $raw = wp_remote_retrieve_body($response);
    $data = json_decode($raw, true);

    // OpenAI compatibility fallback: some deployments/models require max_completion_tokens.
    if ($provider === 'openai' && ($code === 400 || $code === 422) && is_array($data) && !empty($data['error']['message'])) {
        $provider_error = strtolower((string)$data['error']['message']);
        if (strpos($provider_error, 'max_tokens') !== false) {
            $retry_body = $body;
            $retry_body['max_completion_tokens'] = intval($opts['max_tokens']);
            unset($retry_body['max_tokens']);

            $retry_response = wp_remote_post($url, array('headers' => $headers, 'body' => wp_json_encode($retry_body), 'timeout' => 45));
            if (!is_wp_error($retry_response)) {
                $code = wp_remote_retrieve_response_code($retry_response);
                $raw = wp_remote_retrieve_body($retry_response);
                $data = json_decode($raw, true);
            }
        }
    }

    if ($code < 200 || $code >= 300 || !is_array($data)) {
        $provider_message = 'HTTP error from provider';
        if (is_array($data) && !empty($data['error'])) {
            if (is_string($data['error'])) {
                $provider_message = $data['error'];
            } elseif (is_array($data['error']) && !empty($data['error']['message'])) {
                $provider_message = (string)$data['error']['message'];
            }
        }
        return new WP_Error('cbc_ai_http', $provider_message, array('code' => $code, 'body' => $raw, 'provider' => $provider, 'model' => $model));
    }

    $reply = '';
    if (isset($data['choices'][0]['message']['content'])) { $reply = (string)$data['choices'][0]['message']['content']; }
    $usage = isset($data['usage']) ? $data['usage'] : array();

    // Append related links if we used site context
    if ($used_site_ctx && !empty($GLOBALS['cbc_ai_last_site_items']) && is_array($GLOBALS['cbc_ai_last_site_items'])) {
        $items = $GLOBALS['cbc_ai_last_site_items'];
        $links = '';
        foreach ($items as $it) {
            $url = esc_url($it['url']);
            $title = esc_html($it['title']);
            $links .= '<li><a href="' . $url . '" target="_blank" rel="noopener">' . $title . '</a></li>';
        }
        if ($links !== '') {
            $reply .= "\n\n<p><strong>Related links:</strong></p><ul>" . $links . "</ul>";
        }
    }

    return array('reply' => $reply, 'usage' => $usage);
}

// Admin columns and meta box
add_filter('manage_cbc_ai_message_posts_columns', function($cols){ $cols['question'] = 'Question'; $cols['answer'] = 'Answer'; $cols['status'] = 'Status'; return $cols; });
add_action('manage_cbc_ai_message_posts_custom_column', function($col, $post_id){
    if ($col === 'question') { echo esc_html(wp_trim_words((string)get_post_meta($post_id, '_cbc_ai_question', true), 20, '...')); }
    elseif ($col === 'answer') { echo esc_html(wp_trim_words((string)get_post_meta($post_id, '_cbc_ai_answer', true), 20, '...')); }
    elseif ($col === 'status') { $meta = get_post_meta($post_id, '_cbc_ai_meta', true); if (is_array($meta) && isset($meta['status'])) echo esc_html($meta['status']); }
}, 10, 2);
add_action('add_meta_boxes', function(){ add_meta_box('cbc_ai_details', 'Message Details', 'cbc_ai_render_metabox', 'cbc_ai_message', 'normal', 'default'); });
function cbc_ai_render_metabox($post){
    $q = (string)get_post_meta($post->ID, '_cbc_ai_question', true);
    $a = (string)get_post_meta($post->ID, '_cbc_ai_answer', true);
    $meta = get_post_meta($post->ID, '_cbc_ai_meta', true);
    $name = (string)get_post_meta($post->ID, '_cbc_ai_name', true);
    $email = (string)get_post_meta($post->ID, '_cbc_ai_email', true);
    $ip_prefix = (string)get_post_meta($post->ID, '_cbc_ai_ip_prefix', true);
    $ip_hash = (string)get_post_meta($post->ID, '_cbc_ai_ip_hash', true);
    $ua_hash = (string)get_post_meta($post->ID, '_cbc_ai_ua_hash', true);
    ?>
    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
        <div><strong>Question</strong><div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;">&nbsp;<?php echo esc_html($q); ?></div></div>
        <div><strong>Answer</strong><div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;">&nbsp;<?php echo wp_kses_post($a); ?></div></div>
        <div><strong>Meta</strong><pre style="max-height:240px;overflow:auto;border:1px solid #eee;padding:8px;background:#fafafa;"><?php echo esc_html(print_r(is_array($meta) ? $meta : array(), true)); ?></pre></div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div><strong>Name:</strong> <?php echo esc_html($name); ?></div>
            <div><strong>Email:</strong> <?php echo esc_html($email); ?></div>
            <div><strong>IP Prefix:</strong> <?php echo esc_html($ip_prefix ?: 'Not stored'); ?></div>
            <div><strong>IP Hash:</strong> <?php echo esc_html($ip_hash ?: 'Not stored'); ?></div>
            <div><strong>UA Hash:</strong> <?php echo esc_html($ua_hash ?: 'Not stored'); ?></div>
        </div>
    </div>
    <?php
}

// Footer injection for inline chat (floating="0") if enabled
add_action('wp_footer', function(){
    if (is_admin()) return;
    $opts = cbc_ai_get_settings();
    if (empty($opts['render_in_footer'])) return;

    global $post;
    if ($post instanceof WP_Post) {
        $content = (string)$post->post_content;
        if (has_shortcode($content, 'cbc_ai_messenger')) return; // avoid duplicate
    }

    echo do_shortcode('[cbc_ai_messenger]');
});

function cbc_ai_get_effective_api_key($opts): string {
    $prov = $opts['provider'] ?? 'openrouter';
    $key = trim((string)($opts['api_key'] ?? ''));
    if ($key !== '') return $key;
    if ($prov === 'openrouter') { $env = getenv('OPENROUTER_API_KEY'); if ($env) return trim((string)$env); if (defined('CBC_AI_OPENROUTER_API_KEY')) return (string)constant('CBC_AI_OPENROUTER_API_KEY'); }
    else { $env = getenv('OPENAI_API_KEY'); if ($env) return trim((string)$env); if (defined('CBC_AI_OPENAI_API_KEY')) return (string)constant('CBC_AI_OPENAI_API_KEY'); }
    return '';
}
function cbc_ai_get_effective_openai_org($opts): string {
    $org = trim((string)($opts['openai_org'] ?? ''));
    if ($org !== '') return $org;
    $env = getenv('OPENAI_ORGANIZATION'); if ($env) return trim((string)$env);
    if (defined('CBC_AI_OPENAI_ORG')) return (string)constant('CBC_AI_OPENAI_ORG');
    return '';
}

// Add reCAPTCHA helper getters near the end so secrets stay server-side
if (!function_exists('cbc_ai_get_recaptcha_site_key')) {
    function cbc_ai_get_recaptcha_site_key(){
        $k = '';
        if (defined('CBC_AI_RECAPTCHA_SITE_KEY')) $k = trim((string)CBC_AI_RECAPTCHA_SITE_KEY);
        if ($k === '') {
            $env = getenv('RECAPTCHA_SITE_KEY'); if ($env) $k = trim((string)$env);
        }
        return $k;
    }
}
if (!function_exists('cbc_ai_get_recaptcha_secret')) {
    function cbc_ai_get_recaptcha_secret(){
        $k = '';
        if (defined('CBC_AI_RECAPTCHA_SECRET')) $k = trim((string)CBC_AI_RECAPTCHA_SECRET);
        if ($k === '') {
            $env = getenv('RECAPTCHA_SECRET_KEY'); if ($env) $k = trim((string)$env);
        }
        return $k;
    }
}
