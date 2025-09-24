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
    add_settings_field('cbc_ai_max_tokens', 'Max Tokens', 'cbc_ai_field_max_tokens', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_max_tokens'));
    add_settings_field('cbc_ai_system_prompt', 'System Prompt', 'cbc_ai_field_system_prompt', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_system_prompt'));
    add_settings_field('cbc_ai_enforce_scope', 'Enforce Topic Scope', 'cbc_ai_field_enforce_scope', 'cbc-ai-messenger', 'cbc_ai_main', array('label_for' => 'cbc_ai_enforce_scope'));
});

function cbc_ai_default_settings(){
    return array(
        'provider' => 'openrouter', // openai|openrouter
        'api_key' => '',
        'model' => 'openai/gpt-4o-mini',
        'temperature' => 0.3,
        'max_tokens' => 512,
        'system_prompt' => "You are CBC AI Assistant. Only answer questions about: DA-Crop Biotechnology Center (DA-CBC), biotechnology, agriculture, genetic engineering, and biology. If a question is outside this scope, politely refuse and say you can only assist with these topics.",
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
    $out['max_tokens'] = is_numeric($input['max_tokens'] ?? null) ? max(1, min(4096, intval($input['max_tokens']))) : $out['max_tokens'];
    $out['system_prompt'] = wp_kses_post($input['system_prompt'] ?? $out['system_prompt']);
    $out['enforce_scope'] = !empty($input['enforce_scope']) ? 1 : 0;
    return $out;
}

function cbc_ai_render_settings_page(){
    if (!current_user_can('manage_options')) return;
    $opts = cbc_ai_get_settings();
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

function cbc_ai_field_provider(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_provider">Provider</label>
    <select id="cbc_ai_provider" name="<?php echo esc_attr(CBC_AI_OPT); ?>[provider]">
        <option value="openai" <?php selected($o['provider'],'openai'); ?>>OpenAI</option>
        <option value="openrouter" <?php selected($o['provider'],'openrouter'); ?>>OpenRouter</option>
    </select>
    <p class="description">Choose your LLM provider. Enter the corresponding API key below.</p>
    <?php
}

function cbc_ai_field_api_key(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_api_key">API Key</label>
    <input id="cbc_ai_api_key" type="password" style="width: 420px;" name="<?php echo esc_attr(CBC_AI_OPT); ?>[api_key]" value="<?php echo esc_attr($o['api_key']); ?>" />
    <p class="description">Store your API key securely here. Or leave blank to use environment/constant: OPENAI_API_KEY / OPENROUTER_API_KEY or CBC_AI_OPENAI_API_KEY / CBC_AI_OPENROUTER_API_KEY.</p>
    <?php
}

function cbc_ai_field_openai_org(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_openai_org">OpenAI Organization</label>
    <input id="cbc_ai_openai_org" type="text" style="width: 420px;" name="<?php echo esc_attr(CBC_AI_OPT); ?>[openai_org]" value="<?php echo esc_attr($o['openai_org']); ?>" />
    <p class="description">Optional. For OpenAI only. Or leave blank to use environment/constant: OPENAI_ORGANIZATION or CBC_AI_OPENAI_ORG.</p>
    <?php
}

function cbc_ai_field_model(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_model">Model</label>
    <input id="cbc_ai_model" type="text" style="width: 420px;" name="<?php echo esc_attr(CBC_AI_OPT); ?>[model]" value="<?php echo esc_attr($o['model']); ?>" />
    <p class="description">For OpenAI: e.g., gpt-4o-mini, gpt-4.1-mini. For OpenRouter: e.g., openai/gpt-4o-mini, meta-llama/llama-3.1-8b-instruct.</p>
    <?php
}

function cbc_ai_field_temperature(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_temperature">Temperature</label>
    <input id="cbc_ai_temperature" type="number" step="0.1" min="0" max="2" name="<?php echo esc_attr(CBC_AI_OPT); ?>[temperature]" value="<?php echo esc_attr($o['temperature']); ?>" />
    <?php
}

function cbc_ai_field_max_tokens(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_max_tokens">Max Tokens</label>
    <input id="cbc_ai_max_tokens" type="number" min="1" max="4096" name="<?php echo esc_attr(CBC_AI_OPT); ?>[max_tokens]" value="<?php echo esc_attr($o['max_tokens']); ?>" />
    <?php
}

function cbc_ai_field_system_prompt(){
    $o = cbc_ai_get_settings();
    ?>
    <label class="screen-reader-text" for="cbc_ai_system_prompt">System Prompt</label>
    <textarea id="cbc_ai_system_prompt" name="<?php echo esc_attr(CBC_AI_OPT); ?>[system_prompt]" rows="6" cols="80" style="width:100%;max-width:800px;"><?php echo esc_textarea($o['system_prompt']); ?></textarea>
    <?php
}

function cbc_ai_field_enforce_scope(){
    $o = cbc_ai_get_settings();
    ?>
    <label for="cbc_ai_enforce_scope"><input id="cbc_ai_enforce_scope" type="checkbox" name="<?php echo esc_attr(CBC_AI_OPT); ?>[enforce_scope]" value="1" <?php checked($o['enforce_scope'],1); ?>> Refuse questions outside allowed topics</label>
    <p class="description">If enabled, user messages that don't mention allowed topics will be declined even before contacting the LLM.</p>
    <?php
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
                <div class="cbc-ai-title cbc-ai-open text-center drop-shadow"><?php echo esc_html($atts['title']); ?></div>
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
            'message' => array(
                'required' => true,
                'type' => 'string',
            ),
            'name' => array(
                'required' => false,
                'type' => 'string',
            ),
            'email' => array(
                'required' => false,
                'type' => 'string',
            ),
        )
    ));
});

function cbc_ai_is_in_scope($text){
    $text = strtolower((string)$text);
    $keywords = array(
        'da-cbc','crop biotechnology center','biotechnology','agriculture','genetic engineering','biology','plant breeding','gmo','genetically modified','biosafety','molecular biology','agronomy','plant tissue culture','crispr','gene editing','transgenic','biotech','seed','variety','crop','rice','maize','corn','soy','banana','abaca','cassava'
    );
    foreach ($keywords as $k) {
        if (strpos($text, $k) !== false) return true;
    }
    return false;
}

function cbc_ai_rest_ask( WP_REST_Request $req ){
    $message = trim((string)$req->get_param('message'));
    $name = sanitize_text_field((string)$req->get_param('name'));
    $email = sanitize_email((string)$req->get_param('email'));

    if ($message === '') {
        return new WP_REST_Response(array('error' => 'Empty message'), 400);
    }

    // Simple rate limit: 1 request per 10 seconds per IP
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $key = 'cbc_ai_last_' . md5($ip);
    $last = get_transient($key);
    if ($last && (time() - intval($last) < 10)) {
        return new WP_REST_Response(array('error' => 'Please wait a few seconds before asking another question.'), 429);
    }
    set_transient($key, time(), 30);

    $opts = cbc_ai_get_settings();

    // Pre-enforce scope if enabled and clearly out of scope
    if (!empty($opts['enforce_scope']) && !cbc_ai_is_in_scope($message)) {
        $reply = 'I can help with questions about the DA-Crop Biotechnology Center, biotechnology, agriculture, genetic engineering, and biology. Please ask within those topics.';
        $post_id = cbc_ai_log_message($message, $reply, array(
            'provider' => 'blocked',
            'model' => '',
            'status' => 'out_of_scope',
        ), $name, $email);
        return new WP_REST_Response(array('reply' => $reply, 'id' => $post_id, 'status' => 'out_of_scope'), 200);
    }

    $api_key = cbc_ai_get_effective_api_key($opts);
    if ($api_key === '') {
        $reply = 'The AI service is not configured. Please contact the site administrator.';
        $post_id = cbc_ai_log_message($message, $reply, array(
            'provider' => 'none',
            'model' => '',
            'status' => 'not_configured',
        ), $name, $email);
        return new WP_REST_Response(array('reply' => $reply, 'id' => $post_id, 'status' => 'not_configured'), 200);
    }

    $result = cbc_ai_call_provider($opts, $message, $api_key);

    if (is_wp_error($result)) {
        $reply = 'Sorry, I could not generate a response right now.';
        $post_id = cbc_ai_log_message($message, $reply, array(
            'provider' => $opts['provider'],
            'model' => $opts['model'],
            'status' => 'error',
            'error' => $result->get_error_message(),
        ), $name, $email);
        return new WP_REST_Response(array('reply' => $reply, 'id' => $post_id, 'status' => 'error'), 200);
    }

    $reply = (string)($result['reply'] ?? '');
    if ($reply === '') { $reply = 'I do not have an answer at the moment.'; }

    $post_id = cbc_ai_log_message($message, $reply, array(
        'provider' => $opts['provider'],
        'model' => $opts['model'],
        'status' => 'ok',
        'usage' => $result['usage'] ?? array(),
    ), $name, $email);

    return new WP_REST_Response(array(
        'reply' => wp_kses_post($reply),
        'id' => $post_id,
        'status' => 'ok',
    ), 200);
}

function cbc_ai_log_message($question, $answer, $meta = array(), $name = '', $email = ''){
    $title = wp_trim_words($question, 10, '...');
    $post_id = wp_insert_post(array(
        'post_type' => 'cbc_ai_message',
        'post_status' => 'private',
        'post_title' => $title,
    ));
    if (!$post_id || is_wp_error($post_id)) { return 0; }

    update_post_meta($post_id, '_cbc_ai_question', wp_kses_post($question));
    update_post_meta($post_id, '_cbc_ai_answer', wp_kses_post($answer));
    update_post_meta($post_id, '_cbc_ai_meta', $meta);
    if (!empty($name)) update_post_meta($post_id, '_cbc_ai_name', sanitize_text_field($name));
    if (!empty($email)) update_post_meta($post_id, '_cbc_ai_email', sanitize_email($email));

    // Additional context
    update_post_meta($post_id, '_cbc_ai_ip', $_SERVER['REMOTE_ADDR'] ?? '');
    update_post_meta($post_id, '_cbc_ai_ua', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (is_user_logged_in()) update_post_meta($post_id, '_cbc_ai_user_id', get_current_user_id());

    return $post_id;
}

function cbc_ai_normalize_model($provider, $model){
    $model = trim((string)$model);
    if ($provider === 'openai') {
        // Accept either plain (gpt-4o-mini) or vendor-prefixed (openai/gpt-4o-mini)
        if (strpos($model, '/') !== false) {
            $parts = explode('/', $model, 2);
            $model = end($parts);
        }
    } else {
        // For OpenRouter, encourage vendor prefixed. If not provided, assume openai/* for popular models.
        if (strpos($model, '/') === false) {
            // Only prefix known OpenAI models conservatively
            if (preg_match('/^(gpt-4|gpt-4o|gpt-3\.5|o[0-9]|text-|gpt-)/i', $model)) {
                $model = 'openai/' . $model;
            }
        }
    }
    return $model;
}

function cbc_ai_call_provider($opts, $message, $api_key = ''){
    $system = (string)$opts['system_prompt'];
    $guard = "Always stay within DA-CBC, biotechnology, agriculture, genetic engineering, and biology. If asked outside scope, respond with a brief refusal and invite an in-scope question.";

    $provider = $opts['provider'] ?? 'openrouter';
    $model = cbc_ai_normalize_model($provider, $opts['model'] ?? '');

    $body = array(
        'model' => (string)$model,
        'temperature' => floatval($opts['temperature']),
        'max_tokens' => intval($opts['max_tokens']),
        'messages' => array(
            array('role' => 'system', 'content' => $system),
            array('role' => 'system', 'content' => $guard),
            array('role' => 'user', 'content' => $message),
        ),
    );

    $headers = array('Content-Type' => 'application/json');
    $url = '';
    if (($opts['provider'] ?? 'openrouter') === 'openrouter') {
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $headers['Authorization'] = 'Bearer ' . $api_key;
        $headers['HTTP-Referer'] = home_url('/');
        $headers['X-Title'] = get_bloginfo('name');
    } else {
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers['Authorization'] = 'Bearer ' . ($api_key !== '' ? $api_key : cbc_ai_get_effective_api_key($opts));
        $org = cbc_ai_get_effective_openai_org($opts);
        if ($org !== '') {
            $headers['OpenAI-Organization'] = $org;
        }
    }

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => wp_json_encode($body),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $raw = wp_remote_retrieve_body($response);
    $data = json_decode($raw, true);

    if ($code < 200 || $code >= 300 || !is_array($data)) {
        return new WP_Error('cbc_ai_http', 'HTTP error from provider', array('code' => $code, 'body' => $raw));
    }

    // Parse reply depending on provider (both follow OpenAI format)
    $reply = '';
    if (isset($data['choices'][0]['message']['content'])) {
        $reply = (string)$data['choices'][0]['message']['content'];
    }
    $usage = isset($data['usage']) ? $data['usage'] : array();

    return array('reply' => $reply, 'usage' => $usage);
}

// Admin columns to view details quickly
add_filter('manage_cbc_ai_message_posts_columns', function($cols){
    $cols['question'] = 'Question';
    $cols['answer'] = 'Answer';
    $cols['status'] = 'Status';
    return $cols;
});

add_action('manage_cbc_ai_message_posts_custom_column', function($col, $post_id){
    if ($col === 'question') {
        echo esc_html(wp_trim_words((string)get_post_meta($post_id, '_cbc_ai_question', true), 20));
    } elseif ($col === 'answer') {
        echo esc_html(wp_trim_words((string)get_post_meta($post_id, '_cbc_ai_answer', true), 20));
    } elseif ($col === 'status') {
        $meta = get_post_meta($post_id, '_cbc_ai_meta', true);
        if (is_array($meta) && isset($meta['status'])) echo esc_html($meta['status']);
    }
}, 10, 2);

// Simple row actions to view full content
add_filter('post_row_actions', function($actions, $post){
    if ($post->post_type === 'cbc_ai_message') {
        $view = add_query_arg(array('post' => $post->ID, 'action' => 'edit'), admin_url('post.php'));
        $actions['view_full'] = '<a href="' . esc_url($view) . '">View Details</a>';
    }
    return $actions;
}, 10, 2);

// Meta box to display full question/answer and meta
add_action('add_meta_boxes', function(){
    add_meta_box('cbc_ai_details', 'Message Details', 'cbc_ai_render_metabox', 'cbc_ai_message', 'normal', 'default');
});

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
        <div>
            <strong>Question</strong>
            <div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;"><?php echo esc_html($q); ?></div>
        </div>
        <div>
            <strong>Answer</strong>
            <div style="white-space:pre-wrap;border:1px solid #ddd;padding:8px;background:#fff;"><?php echo wp_kses_post($a); ?></div>
        </div>
        <div>
            <strong>Meta</strong>
            <pre style="max-height:240px;overflow:auto;border:1px solid #eee;padding:8px;background:#fafafa;"><?php echo esc_html(print_r(is_array($meta) ? $meta : array(), true)); ?></pre>
        </div>
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
    if ($prov === 'openrouter') {
        $env = getenv('OPENROUTER_API_KEY');
        if ($env) return trim((string)$env);
        if (defined('CBC_AI_OPENROUTER_API_KEY')) return (string)constant('CBC_AI_OPENROUTER_API_KEY');
    } else {
        $env = getenv('OPENAI_API_KEY');
        if ($env) return trim((string)$env);
        if (defined('CBC_AI_OPENAI_API_KEY')) return (string)constant('CBC_AI_OPENAI_API_KEY');
    }
    return '';
}

function cbc_ai_get_effective_openai_org($opts){
    $org = trim((string)($opts['openai_org'] ?? ''));
    if ($org !== '') return $org;
    $env = getenv('OPENAI_ORGANIZATION');
    if ($env) return trim((string)$env);
    if (defined('CBC_AI_OPENAI_ORG')) return (string)constant('CBC_AI_OPENAI_ORG');
    return '';
}
