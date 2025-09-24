<?php
/**
 * Plugin Name: CBC AI Messenger
 * Description: Adds an AI messaging feature using a public LLM provider (OpenAI or OpenRouter). Provides a shortcode [cbc_ai_messenger] and records all Q&A in a custom post type.
 * Version: 1.0.1
 * Author: Cristo Rey C. Magdadaro
 * License: GPL2+
 */

if (!defined('ABSPATH')) { exit; }

// Option key
const CBC_AI_OPT = 'cbc_ai_settings';

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
        'capability_type' => 'post',
    ));
});

register_activation_hook(__FILE__, function(){
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
});

function cbc_ai_default_settings(){
    return array(
        'provider' => 'openrouter', // openai|openrouter
        'api_key' => '',
        'model' => 'openai/gpt-4o-mini',
        'temperature' => 0.2,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
        'max_tokens' => 1024,
        'system_prompt' => "You are CBC AI Assistant. Your primary role is to answer questions about DA-Crop Biotechnology Center (DA-CBC), including its projects, research, and initiatives. You should also be knowledgeable about general topics in biotechnology, agriculture, genetic engineering, and biology. When asked a question outside of this scope, politely decline and state that your expertise is limited to these topics. Your responses should be informative, accurate, and easy to understand for a general audience.",
        'enforce_scope' => 1,
        'openai_org' => '',
    );
}

function cbc_ai_get_settings(){
    $opts = get_option(CBC_AI_OPT);
    if (!is_array($opts)) { $opts = array(); }
    return wp_parse_args($opts, cbc_ai_default_settings());
}

function cbc_ai_sanitize_settings($input){
    if (!is_array($input)) { $input = array(); }
    $out = cbc_ai_get_settings();
    $prov = $input['provider'] ?? '';
    $out['provider'] = in_array($prov, array('openai','openrouter'), true) ? $prov : $out['provider'];
    $out['api_key'] = trim((string)($input['api_key'] ?? $out['api_key']));
    $out['openai_org'] = sanitize_text_field($input['openai_org'] ?? $out['openai_org']);
    $out['model'] = sanitize_text_field($input['model'] ?? $out['model']);
    $out['temperature'] = is_numeric($input['temperature'] ?? null) ? max(0, min(2, floatval($input['temperature']))) : $out['temperature'];
    $out['top_p'] = is_numeric($input['top_p'] ?? null) ? max(0, min(1, floatval($input['top_p']))) : $out['top_p'];
    $out['frequency_penalty'] = is_numeric($input['frequency_penalty'] ?? null) ? max(-2, min(2, floatval($input['frequency_penalty']))) : $out['frequency_penalty'];
    $out['presence_penalty'] = is_numeric($input['presence_penalty'] ?? null) ? max(-2, min(2, floatval($input['presence_penalty']))) : $out['presence_penalty'];
    $out['max_tokens'] = is_numeric($input['max_tokens'] ?? null) ? max(1, min(4096, intval($input['max_tokens']))) : $out['max_tokens'];
    $out['system_prompt'] = wp_kses_post($input['system_prompt'] ?? $out['system_prompt']);
    $out['enforce_scope'] = !empty($input['enforce_scope']) ? 1 : 0;
    return $out;
}

function cbc_ai_render_settings_page(){
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

function cbc_ai_field_provider($args){
    $opts = array('openai' => 'OpenAI', 'openrouter' => 'OpenRouter');
    $val = cbc_ai_get_settings()['provider'] ?? '';
    $html = "<select id='{$args['label_for']}' name='" . CBC_AI_OPT . "[provider]'>";
    foreach ($opts as $k => $v) {
        $html .= "<option value='{$k}'" . selected($val, $k, false) . ">{$v}</option>";
    }
    $html .= "</select>";
    echo $html;
}

function cbc_ai_field_api_key($args){
    $val = cbc_ai_get_settings()['api_key'] ?? '';
    echo "<input type='password' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[api_key]' value='" . esc_attr($val) . "' class='regular-text' />";
}

function cbc_ai_field_openai_org($args){
    $val = cbc_ai_get_settings()['openai_org'] ?? '';
    echo "<input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[openai_org]' value='" . esc_attr($val) . "' class='regular-text' />";
}

function cbc_ai_field_model($args){
    $val = cbc_ai_get_settings()['model'] ?? '';
    echo "<input type='text' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[model]' value='" . esc_attr($val) . "' class='regular-text' />";
    echo "<p class='description'>e.g., openai/gpt-4o-mini, google/gemini-flash-1.5, anthropic/claude-3-haiku</p>";
}

function cbc_ai_field_temperature($args){
    $val = cbc_ai_get_settings()['temperature'] ?? 0.3;
    echo "<input type='number' step='0.1' min='0' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[temperature]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_top_p($args){
    $val = cbc_ai_get_settings()['top_p'] ?? 1;
    echo "<input type='number' step='0.1' min='0' max='1' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[top_p]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_frequency_penalty($args){
    $val = cbc_ai_get_settings()['frequency_penalty'] ?? 0;
    echo "<input type='number' step='0.1' min='-2' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[frequency_penalty]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_presence_penalty($args){
    $val = cbc_ai_get_settings()['presence_penalty'] ?? 0;
    echo "<input type='number' step='0.1' min='-2' max='2' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[presence_penalty]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_max_tokens($args){
    $val = cbc_ai_get_settings()['max_tokens'] ?? 1024;
    echo "<input type='number' step='1' min='1' max='4096' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[max_tokens]' value='" . esc_attr($val) . "' class='small-text' />";
}

function cbc_ai_field_system_prompt($args){
    $val = cbc_ai_get_settings()['system_prompt'] ?? '';
    echo "<textarea id='{$args['label_for']}' name='" . CBC_AI_OPT . "[system_prompt]' rows='5' class='large-text'>" . esc_textarea($val) . "</textarea>";
}

function cbc_ai_field_enforce_scope($args){
    $val = cbc_ai_get_settings()['enforce_scope'] ?? 0;
    echo "<input type='checkbox' id='{$args['label_for']}' name='" . CBC_AI_OPT . "[enforce_scope]' value='1' " . checked(1, $val, false) . " />";
}

// Shortcode to render the messenger UI
add_shortcode('cbc_ai_messenger', function($atts){
    $atts = shortcode_atts(array(
        'placeholder' => 'Ask about DA-CBC, biotechnology, agriculture, genetic engineering, or biology...',
        'floating' => '1',
        'title' => 'DA-CBC Chatbot',
    ), $atts, 'cbc_ai_messenger');

    $floating = in_array(strtolower((string)$atts['floating']), array('1','true','yes','on'), true);

    // Enqueue assets
    wp_enqueue_script('cbc-ai-messenger', plugins_url('assets/js/cbc-ai-messenger.js', __FILE__), array('jquery'), '1.1.0', true);
    wp_localize_script('cbc-ai-messenger', 'CBCAI', array(
        'restUrl' => esc_url_raw(rest_url('cbc-ai/v1/ask')),
        'nonce' => wp_create_nonce('wp_rest'),
        'placeholder' => (string)$atts['placeholder'],
    ));
    wp_enqueue_style('cbc-ai-messenger', plugins_url('assets/css/cbc-ai-messenger.css', __FILE__), array(), '1.1.0');

    ob_start();
    ?>
    <div class="cbc-ai-box flex flex-col justify-end items-end drop-shadow-md <?php echo $floating ? ' cbc-ai-floating cbc-ai-collapsed' : ''; ?>">
        <?php if ($floating): ?>
            <div class="cbc-ai-header flex justify-center w-full">
                <div class="cbc-ai-title cbc-ai-open text-center drop-shadow flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-robot" viewBox="0 0 16 16">
                        <path d="M6 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5M3 8.062C3 6.76 4.235 5.765 5.53 5.886a26.6 26.6 0 0 0 4.94 0C11.765 5.765 13 6.76 13 8.062v1.157a.93.93 0 0 1-.765.935c-.845.147-2.34.346-4.235.346s-3.39-.2-4.235-.346A.93.93 0 0 1 3 9.219zm4.542-.827a.25.25 0 0 0-.217.068l-.92.9a25 25 0 0 1-1.871-.183.25.25 0 0 0-.068.495c.55.076 1.232.149 2.02.193a.25.25 0 0 0 .189-.071l.754-.736.847 1.71a.25.25 0 0 0 .404.062l.932-.97a25 25 0 0 0 1.922-.188.25.25 0 0 0-.068-.495c-.538.074-1.207.145-1.98.189a.25.25 0 0 0-.166.076l-.754.785-.842-1.7a.25.25 0 0 0-.182-.135"/>
                        <path d="M8.5 1.866a1 1 0 1 0-1 0V3h-2A4.5 4.5 0 0 0 1 7.5V8a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1v1a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1v-.5A4.5 4.5 0 0 0 10.5 3h-2zM14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5A3.5 3.5 0 0 1 5.5 4h5A3.5 3.5 0 0 1 14 7.5"/>
                    </svg>
                   <span><?php echo esc_html($atts['title']); ?></span>
                </div>
                <button type="button" class="cbc-ai-toggle cbc-ai-toggle-open flex items-center" aria-label="Open CBC Chatbot" aria-expanded="false">
                    <span class="cbc-ai-toggle-open-icon drop-shadow-md" aria-hidden="true">
                        <!-- bubble / chat icon (used when collapsed - open action) -->
                        <svg viewBox="0 0 16 16" fill="currentColor">
                          <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                        </svg>
                    </span>
                    <span class="cbc-ai-toggle-close-icon drop-shadow-md" aria-hidden="true">
                        <!-- close (X) icon (used when open - close action) -->
                        <svg viewBox="0 0 16 16" fill="currentColor">
                          <path d="M3.404 2.596a.5.5 0 0 1 .707 0L8 6.485l3.889-3.89a.5.5 0 1 1 .707.707L8.707 7.192l3.889 3.889a.5.5 0 0 1-.707.707L8 7.899l-3.889 3.889a.5.5 0 0 1-.707-.707L7.293 7.192 3.404 3.303a.5.5 0 0 1 0-.707z"/>
                        </svg>
                    </span>
                </button>
            </div>
            <div class="cbc-ai-body">
        <?php endif; ?>
        <span class="text-sm text-gray-500">Conversation:</span>
        <div class="cbc-ai-log shadow mb-3" aria-live="polite"></div>
        <form class="cbc-ai-form">
            <input type="text" name="name" class="cbc-ai-input-name" placeholder="Your name (optional)" aria-label="Your name" />
            <input type="email" name="email" class="cbc-ai-input-email" placeholder="Your email (optional)" aria-label="Your email" />
            <textarea name="message" class="cbc-ai-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" aria-label="Your question" ></textarea>
            <button type="submit" class="cbc-ai-send">Ask</button>
        </form>
        <div class="cbc-ai-note">Answers are limited to DA-CBC and related science topics.</div>
        <?php if ($floating): ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});

// REST API endpoint
add_action('rest_api_init', function(){
    register_rest_route('cbc-ai/v1', '/ask', array(
        'methods' => 'POST',
        'callback' => 'cbc_ai_rest_ask',
        'permission_callback' => '__return_true',
        'args' => array(
            'message' => array('required' => true,'type' => 'string'),
            'name'    => array('required' => false,'type' => 'string'),
            'email'   => array('required' => false,'type' => 'string'),
        )
    ));
});

function cbc_ai_is_contact_request($text, &$type){
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

function cbc_ai_rest_ask( WP_REST_Request $req ){
    $message = trim((string)$req->get_param('message'));
    $name = sanitize_text_field((string)$req->get_param('name'));
    $email = sanitize_email((string)$req->get_param('email'));

    if ($message === '') { return new WP_REST_Response(array('error' => 'Empty message'), 400); }

    // Simple rate limit: 1 request per 10 seconds per IP
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $key = 'cbc_ai_last_' . md5($ip);
    $last = get_transient($key);
    if ($last && (time() - intval($last) < 10)) { return new WP_REST_Response(array('error' => 'Please wait a few seconds before asking another question.'), 429); }
    set_transient($key, time(), 30);

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
        $post_id = cbc_ai_log_message($message, $reply, array('provider' => $opts['provider'],'model' => $opts['model'],'status' => 'error','error' => $result->get_error_message()), $name, $email);
        return new WP_REST_Response(array('reply' => $reply), 200);
    }

    $reply = (string)($result['reply'] ?? '');
    if ($reply === '') { $reply = 'I do not have an answer at the moment.'; }

    $post_id = cbc_ai_log_message($message, $reply, array('provider' => $opts['provider'],'model' => $opts['model'],'status' => 'ok','usage' => $result['usage'] ?? array()), $name, $email);
    return new WP_REST_Response(array('reply' => wp_kses_post($reply)), 200);
}

function cbc_ai_log_message($question, $answer, $meta = array(), $name = '', $email = ''){
    $title = wp_trim_words($question, 10, '...');
    $post_id = wp_insert_post(array('post_type' => 'cbc_ai_message','post_status' => 'private','post_title' => $title,));
    if (!$post_id || is_wp_error($post_id)) { return 0; }
    update_post_meta($post_id, '_cbc_ai_question', wp_kses_post($question));
    update_post_meta($post_id, '_cbc_ai_answer', wp_kses_post($answer));
    update_post_meta($post_id, '_cbc_ai_meta', $meta);
    if (!empty($name)) update_post_meta($post_id, '_cbc_ai_name', sanitize_text_field($name));
    if (!empty($email)) update_post_meta($post_id, '_cbc_ai_email', sanitize_email($email));
    update_post_meta($post_id, '_cbc_ai_ip', $_SERVER['REMOTE_ADDR'] ?? '');
    update_post_meta($post_id, '_cbc_ai_ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (is_user_logged_in()) update_post_meta($post_id, '_cbc_ai_user_id', get_current_user_id());
    return $post_id;
}

function cbc_ai_normalize_model($provider, $model){
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

function cbc_ai_call_provider($opts, $message, $api_key = ''){
    $system = (string)$opts['system_prompt'];
    $guard = "Always stay within DA-CBC, biotechnology, agriculture, genetic engineering, and biology. If asked outside scope, respond with a brief refusal and invite an in-scope question.";
    $provider = $opts['provider'] ?? 'openrouter';
    $model = cbc_ai_normalize_model($provider, $opts['model'] ?? '');
    $messages = array(array('role' => 'system', 'content' => $system));
    if (!empty($opts['enforce_scope'])) { $messages[] = array('role' => 'system', 'content' => $guard); }
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
    } else {
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers['Authorization'] = 'Bearer ' . $api_key;
        $org = cbc_ai_get_effective_openai_org($opts);
        if ($org !== '') { $headers['OpenAI-Organization'] = $org; }
    }

    $response = wp_remote_post($url, array('headers' => $headers,'body' => wp_json_encode($body),'timeout' => 45,));
    if (is_wp_error($response)) { return $response; }

    $code = wp_remote_retrieve_response_code($response);
    $raw = wp_remote_retrieve_body($response);
    $data = json_decode($raw, true);
    if ($code < 200 || $code >= 300 || !is_array($data)) { return new WP_Error('cbc_ai_http', 'HTTP error from provider', array('code' => $code, 'body' => $raw)); }

    $reply = '';
    if (isset($data['choices'][0]['message']['content'])) { $reply = (string)$data['choices'][0]['message']['content']; }
    $usage = isset($data['usage']) ? $data['usage'] : array();
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
    $ip = (string)get_post_meta($post->ID, '_cbc_ai_ip', true);
    $ua = (string)get_post_meta($post->ID, '_cbc_ai_ua', true);
    ?>
    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
        <div><strong>Question</strong><div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;">&nbsp;<?php echo esc_html($q); ?></div></div>
        <div><strong>Answer</strong><div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;">&nbsp;<?php echo wp_kses_post($a); ?></div></div>
        <div><strong>Meta</strong><pre style="max-height:240px;overflow:auto;border:1px solid #eee;padding:8px;background:#fafafa;"><?php echo esc_html(print_r(is_array($meta) ? $meta : array(), true)); ?></pre></div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div><strong>Name:</strong> <?php echo esc_html($name); ?></div>
            <div><strong>Email:</strong> <?php echo esc_html($email); ?></div>
            <div><strong>IP:</strong> <?php echo esc_html($ip); ?></div>
            <div><strong>UA:</strong> <?php echo esc_html($ua); ?></div>
        </div>
    </div>
    <?php
}

function cbc_ai_get_effective_api_key($opts){
    $prov = $opts['provider'] ?? 'openrouter';
    $key = trim((string)($opts['api_key'] ?? ''));
    if ($key !== '') return $key;
    if ($prov === 'openrouter') { $env = getenv('OPENROUTER_API_KEY'); if ($env) return trim((string)$env); if (defined('CBC_AI_OPENROUTER_API_KEY')) return (string)constant('CBC_AI_OPENROUTER_API_KEY'); }
    else { $env = getenv('OPENAI_API_KEY'); if ($env) return trim((string)$env); if (defined('CBC_AI_OPENAI_API_KEY')) return (string)constant('CBC_AI_OPENAI_API_KEY'); }
    return '';
}
function cbc_ai_get_effective_openai_org($opts){
    $org = trim((string)($opts['openai_org'] ?? ''));
    if ($org !== '') return $org;
    $env = getenv('OPENAI_ORGANIZATION'); if ($env) return trim((string)$env);
    if (defined('CBC_AI_OPENAI_ORG')) return (string)constant('CBC_AI_OPENAI_ORG');
    return '';
}

