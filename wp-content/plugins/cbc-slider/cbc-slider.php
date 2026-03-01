<?php
/**
 * Plugin Name: CBC Slider
 * Description: A robust slider that supports images, videos from the media library, and custom HTML overlays. Manage sliders in WP Admin and embed via shortcode.
 * Version: 1.1.0
 * Author: CBC
 * License: GPL-2.0-or-later
 * Text Domain: cbc-slider
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CBC_SLIDER_VERSION', '1.1.0');
define('CBC_SLIDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CBC_SLIDER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load translations
add_action('init', function() {
    load_plugin_textdomain('cbc-slider', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

/**
 * Register frontend assets (used by shortcode/template render)
 */
function cbc_slider_register_assets() {
    wp_register_script(
        'cbc-slider-frontend',
        CBC_SLIDER_PLUGIN_URL . 'assets/frontend.js',
        array(),
        CBC_SLIDER_VERSION,
        true
    );
    wp_register_style(
        'cbc-slider-style',
        CBC_SLIDER_PLUGIN_URL . 'assets/style.css',
        array(),
        CBC_SLIDER_VERSION
    );
}
add_action('init', 'cbc_slider_register_assets');

/**
 * Register a custom post type for sliders.
 */
function cbc_slider_register_cpt() {
    $labels = array(
        'name' => __('CBC Sliders', 'cbc-slider'),
        'singular_name' => __('CBC Slider', 'cbc-slider'),
        'add_new' => __('Add New', 'cbc-slider'),
        'add_new_item' => __('Add New Slider', 'cbc-slider'),
        'edit_item' => __('Edit Slider', 'cbc-slider'),
        'new_item' => __('New Slider', 'cbc-slider'),
        'view_item' => __('View Slider', 'cbc-slider'),
        'search_items' => __('Search Sliders', 'cbc-slider'),
        'not_found' => __('No sliders found', 'cbc-slider'),
        'not_found_in_trash' => __('No sliders found in Trash', 'cbc-slider'),
        'menu_name' => __('CBC Slider', 'cbc-slider'),
    );

    register_post_type('cbc_slider', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => array('title'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'has_archive' => false,
    ));
}
add_action('init', 'cbc_slider_register_cpt');

/**
 * Add meta box for slides/options.
 */
function cbc_slider_add_metaboxes() {
    add_meta_box(
        'cbc-slider-metabox',
        __('Slider Builder', 'cbc-slider'),
        'cbc_slider_metabox_render',
        'cbc_slider',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cbc_slider_add_metaboxes');

/**
 * Enqueue admin assets for the meta box.
 */
function cbc_slider_admin_assets($hook) {
    global $post;
    if (($hook === 'post.php' || $hook === 'post-new.php') && isset($post) && $post->post_type === 'cbc_slider') {
        wp_enqueue_media();
        // Enqueue classic editor (TinyMCE, QuickTags, media buttons) for overlay modal
        if (function_exists('wp_enqueue_editor')) { wp_enqueue_editor(); }
        wp_enqueue_style('cbc-slider-admin', CBC_SLIDER_PLUGIN_URL . 'assets/admin.css', array(), CBC_SLIDER_VERSION);
        wp_enqueue_script('cbc-slider-admin', CBC_SLIDER_PLUGIN_URL . 'assets/admin.js', array('jquery'), CBC_SLIDER_VERSION, true);
    }
}
add_action('admin_enqueue_scripts', 'cbc_slider_admin_assets');

/**
 * Render meta box UI container and seed data.
 */
function cbc_slider_metabox_render($post) {
    wp_nonce_field('cbc_slider_save', 'cbc_slider_nonce');
    $data = get_post_meta($post->ID, '_cbc_slider_data', true);
    if (empty($data) || !is_array($data)) {
        $data = array(
            'slides' => array(),
            'options' => array(
                'autoplay' => true,
                'delay' => 5000,
                'loop' => true,
                'pauseOnHover' => true,
                'showArrows' => true,
                'showDots' => true,
                'objectFit' => 'cover',
                'aspectRatio' => '16/9',
                'height' => '',
                'heightSm' => '',
                'heightMd' => '',
                'heightLg' => '',
                'heightXl' => '',
                'videoMuted' => true,
                'videoLoop' => false,
                'videoControls' => false,
            ),
        );
    } else {
        // Backfill new responsive height options if missing
        $defaults_new = array('heightSm' => '', 'heightMd' => '', 'heightLg' => '', 'heightXl' => '');
        if (!isset($data['options']) || !is_array($data['options'])) $data['options'] = array();
        $data['options'] = array_merge($defaults_new, $data['options']);
    }
    echo '<div id="cbc-slider-admin-root" data-state="' . esc_attr(wp_json_encode($data)) . '"></div>';
    echo '<textarea name="cbc_slider_json" id="cbc_slider_json" style="display:none;" aria-hidden="true">' . esc_textarea(wp_json_encode($data)) . '</textarea>';
}

/**
 * Sanitize full slider payload from admin.
 */
function cbc_slider_sanitize_payload($raw_json) {
    $payload = json_decode(wp_unslash($raw_json), true);
    if (!is_array($payload)) {
        return array('slides' => array(), 'options' => array());
    }

    // Options
    $opts = isset($payload['options']) && is_array($payload['options']) ? $payload['options'] : array();
    $options_clean = array(
        'autoplay' => !empty($opts['autoplay']),
        'delay' => isset($opts['delay']) ? intval($opts['delay']) : 5000,
        'loop' => !empty($opts['loop']),
        'pauseOnHover' => !empty($opts['pauseOnHover']),
        'showArrows' => !empty($opts['showArrows']),
        'showDots' => isset($opts['showDots']) ? (bool)$opts['showDots'] : true,
        'objectFit' => !empty($opts['objectFit']) ? sanitize_text_field($opts['objectFit']) : 'cover',
        'aspectRatio' => !empty($opts['aspectRatio']) ? sanitize_text_field($opts['aspectRatio']) : '16/9',
        'height' => !empty($opts['height']) ? sanitize_text_field($opts['height']) : '',
        'heightSm' => !empty($opts['heightSm']) ? sanitize_text_field($opts['heightSm']) : '',
        'heightMd' => !empty($opts['heightMd']) ? sanitize_text_field($opts['heightMd']) : '',
        'heightLg' => !empty($opts['heightLg']) ? sanitize_text_field($opts['heightLg']) : '',
        'heightXl' => !empty($opts['heightXl']) ? sanitize_text_field($opts['heightXl']) : '',
        'videoMuted' => isset($opts['videoMuted']) ? (bool)$opts['videoMuted'] : true,
        'videoLoop' => isset($opts['videoLoop']) ? (bool)$opts['videoLoop'] : false,
        'videoControls' => isset($opts['videoControls']) ? (bool)$opts['videoControls'] : false,
    );

    // Slides
    $slides_input = isset($payload['slides']) && is_array($payload['slides']) ? $payload['slides'] : array();
    $slides_clean = array();
    foreach ($slides_input as $s) {
        $type = (isset($s['type']) && $s['type'] === 'video') ? 'video' : 'image';
        $slide = array(
            'type' => $type,
            'id' => isset($s['id']) ? intval($s['id']) : 0,
            'url' => !empty($s['url']) ? esc_url_raw($s['url']) : '',
            'alt' => !empty($s['alt']) ? sanitize_text_field($s['alt']) : '',
            'overlayHtml' => !empty($s['overlayHtml']) ? cbc_slider_sanitize_overlay_html($s['overlayHtml']) : '',
            'linkUrl' => !empty($s['linkUrl']) ? esc_url_raw($s['linkUrl']) : '',
            'linkTargetBlank' => !empty($s['linkTargetBlank']) ? (bool)$s['linkTargetBlank'] : false,
        );
        if ($type === 'video') {
            $slide['posterId'] = isset($s['posterId']) ? intval($s['posterId']) : 0;
            $slide['posterUrl'] = !empty($s['posterUrl']) ? esc_url_raw($s['posterUrl']) : '';
        }
        $slides_clean[] = $slide;
    }

    // Final clean payload
    $clean = array(
        'slides' => $slides_clean,
        'options' => $options_clean,
    );

    return $clean;
}

/**
 * Save meta on post save.
 */
function cbc_slider_save_post($post_id) {
    if (!isset($_POST['cbc_slider_nonce']) || !wp_verify_nonce($_POST['cbc_slider_nonce'], 'cbc_slider_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['cbc_slider_json'])) {
        $clean = cbc_slider_sanitize_payload($_POST['cbc_slider_json']);
        update_post_meta($post_id, '_cbc_slider_data', $clean);
    }
}
add_action('save_post_cbc_slider', 'cbc_slider_save_post');

// ================= Existing sanitization and rendering helpers =================

/**
 * Allowed HTML for overlay content. Extends wp_kses_post to include class/style and data-* on common tags.
 */
function cbc_slider_get_allowed_overlay_html() {
    $allowed = wp_kses_allowed_html('post');

    $common_attr = array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true,
        'data-*' => true,
        'aria-*' => true,
    );

    $allowed['div'] = isset($allowed['div']) ? array_merge($allowed['div'], $common_attr) : $common_attr;
    $allowed['span'] = isset($allowed['span']) ? array_merge($allowed['span'], $common_attr) : $common_attr;

    for ($h = 1; $h <= 6; $h++) {
        $tag = 'h' . $h;
        $allowed[$tag] = isset($allowed[$tag]) ? array_merge($allowed[$tag], $common_attr) : $common_attr;
    }

    $allowed['a'] = isset($allowed['a']) ? array_merge($allowed['a'], $common_attr, array(
        'href' => true,
        'target' => true,
        'rel' => true,
        'download' => true,
    )) : array_merge($common_attr, array(
        'href' => true,
        'target' => true,
        'rel' => true,
        'download' => true,
    ));

    $allowed['ul'] = isset($allowed['ul']) ? array_merge($allowed['ul'], $common_attr) : $common_attr;
    $allowed['ol'] = isset($allowed['ol']) ? array_merge($allowed['ol'], $common_attr) : $common_attr;
    $allowed['li'] = isset($allowed['li']) ? array_merge($allowed['li'], $common_attr) : $common_attr;
    $allowed['p'] = isset($allowed['p']) ? array_merge($allowed['p'], $common_attr) : $common_attr;

    $img_attrs = array_merge($common_attr, array(
        'src' => true,
        'alt' => true,
        'title' => true,
        'width' => true,
        'height' => true,
        'srcset' => true,
        'sizes' => true,
        'loading' => true,
        'decoding' => true,
    ));
    $allowed['img'] = isset($allowed['img']) ? array_merge($allowed['img'], $img_attrs) : $img_attrs;

    return apply_filters('cbc_slider_allowed_overlay_html', $allowed);
}

/**
 * Sanitize overlay HTML.
 */
function cbc_slider_sanitize_overlay_html($html) {
    return wp_kses($html, cbc_slider_get_allowed_overlay_html());
}

/**
 * Build a single slide HTML.
 * @internal
 */
function cbc_slider_build_slide_html($slide, $options, $index, $total) {
    $type = isset($slide['type']) ? $slide['type'] : 'image';
    $overlay = isset($slide['overlayHtml']) ? cbc_slider_sanitize_overlay_html($slide['overlayHtml']) : '';

    $object_fit = !empty($options['objectFit']) ? esc_attr($options['objectFit']) : 'cover';

    $media_html = '';
    if ($type === 'video') {
        $vid_id = isset($slide['id']) ? intval($slide['id']) : 0;
        $src = $vid_id ? wp_get_attachment_url($vid_id) : (!empty($slide['url']) ? esc_url($slide['url']) : '');
        $poster = '';
        if (!empty($slide['posterId'])) {
            $poster_src = wp_get_attachment_image_src(intval($slide['posterId']), 'large');
            if (!empty($poster_src[0])) {
                $poster = ' poster="' . esc_url($poster_src[0]) . '"';
            }
        } elseif (!empty($slide['posterUrl'])) {
            $poster = ' poster="' . esc_url($slide['posterUrl']) . '"';
        }
        if ($src) {
            $attrs = ' playsinline preload="metadata"';
            if (!empty($options['videoMuted'])) { $attrs .= ' muted'; }
            if (!empty($options['videoLoop'])) { $attrs .= ' loop'; }
            if (!empty($options['videoControls'])) { $attrs .= ' controls'; }
            $media_html = '<video class="cbc-slider__video" style="object-fit:' . $object_fit . ';"' . $poster . $attrs . '>
                <source src="' . esc_url($src) . '" />
            </video>';
        }
    } else {
        $img_id = isset($slide['id']) ? intval($slide['id']) : 0;
        if ($img_id) {
            $media_html = wp_get_attachment_image(
                $img_id,
                'large',
                false,
                array(
                    'class' => 'cbc-slider__img',
                    'loading' => 'lazy',
                    'style' => 'object-fit:' . $object_fit . ';',
                    'alt' => isset($slide['alt']) ? esc_attr($slide['alt']) : ''
                )
            );
        } elseif (!empty($slide['url'])) {
            $alt = isset($slide['alt']) ? esc_attr($slide['alt']) : '';
            $media_html = '<img class="cbc-slider__img" src="' . esc_url($slide['url']) . '" alt="' . $alt . '" loading="lazy" style="object-fit:' . $object_fit . ';" />';
        }
    }

    // Wrap media with link if provided
    if (!empty($slide['linkUrl'])) {
        $target = !empty($slide['linkTargetBlank']) ? ' target="_blank" rel="noopener"' : '';
        $media_html = '<a class="cbc-slider__link" href="' . esc_url($slide['linkUrl']) . '"' . $target . '>' . $media_html . '</a>';
    }

    // Output overlay HTML directly so user can position/style via Tailwind
    $overlay_html = $overlay ? $overlay : '';

    return '<div class="cbc-slider__slide" role="group" aria-roledescription="slide" aria-label="' . esc_attr($index . ' ' . __('of', 'cbc-slider') . ' ' . $total) . '">' .
        '<div class="cbc-slider__mediaWrap">' . $media_html . '</div>' .
        $overlay_html .
    '</div>';
}

/**
 * Render slider HTML from data array.
 */
function cbc_slider_render_from_data($data) {
    if (empty($data) || empty($data['slides']) || !is_array($data['slides'])) return '';

    // Enqueue frontend assets only when rendering
    wp_enqueue_style('cbc-slider-style');
    wp_enqueue_script('cbc-slider-frontend');

    $slides = $data['slides'];
    $options = isset($data['options']) ? $data['options'] : array();

    $defaults = array(
        'autoplay' => true,
        'delay' => 5000,
        'loop' => true,
        'pauseOnHover' => true,
        'showArrows' => true,
        'showDots' => true,
        'objectFit' => 'cover',
        'aspectRatio' => '16/9',
        'height' => '',
        'heightSm' => '',
        'heightMd' => '',
        'heightLg' => '',
        'heightXl' => '',
        'videoMuted' => true,
        'videoLoop' => false,
        'videoControls' => false,
    );
    $options = wp_parse_args($options, $defaults);

    $total = count($slides);
    $data_attrs = sprintf(
        ' data-autoplay="%s" data-delay="%d" data-loop="%s" data-pause="%s" data-dots="%s"',
        !empty($options['autoplay']) ? '1' : '0',
        intval($options['delay']),
        !empty($options['loop']) ? '1' : '0',
        !empty($options['pauseOnHover']) ? '1' : '0',
        !empty($options['showDots']) ? '1' : '0'
    );

    // Inline style vars and class for custom heights
    $style_inline = '';
    $classes = 'cbc-slider';
    $has_custom_height = !empty($options['height']) || !empty($options['heightSm']) || !empty($options['heightMd']) || !empty($options['heightLg']) || !empty($options['heightXl']);
    if ($has_custom_height) {
        $classes .= ' cbc-slider--custom-height';
        $vars = array();
        if (!empty($options['height'])) { $vars[] = '--cbc-h-base:' . esc_attr($options['height']); }
        if (!empty($options['heightSm'])) { $vars[] = '--cbc-h-sm:' . esc_attr($options['heightSm']); }
        if (!empty($options['heightMd'])) { $vars[] = '--cbc-h-md:' . esc_attr($options['heightMd']); }
        if (!empty($options['heightLg'])) { $vars[] = '--cbc-h-lg:' . esc_attr($options['heightLg']); }
        if (!empty($options['heightXl'])) { $vars[] = '--cbc-h-xl:' . esc_attr($options['heightXl']); }
        if (!empty($vars)) {
            $style_inline = ' style="' . implode(';', $vars) . '"';
        }
    } else if (!empty($options['aspectRatio'])) {
        $style_inline = ' style="--cbc-slider-aspect: ' . esc_attr($options['aspectRatio']) . ';"';
    }

    $slides_html = '';
    $idx = 1;
    foreach ($slides as $slide) {
        $slides_html .= cbc_slider_build_slide_html($slide, $options, $idx, $total);
        $idx++;
    }

    $arrows = !empty($options['showArrows']) ? '<button class="cbc-slider__prev" aria-label="' . esc_attr__('Previous slide', 'cbc-slider') . '" type="button">&#10094;</button>' .
                                             '<button class="cbc-slider__next" aria-label="' . esc_attr__('Next slide', 'cbc-slider') . '" type="button">&#10095;</button>' : '';
    $dots = !empty($options['showDots']) ? '<div class="cbc-slider__dots" role="tablist" aria-label="' . esc_attr__('Slide navigation', 'cbc-slider') . '"></div>' : '';

    return '<div class="' . esc_attr($classes) . '"' . $data_attrs . $style_inline . '>
        <div class="cbc-slider__viewport" tabindex="0" aria-roledescription="carousel" aria-label="' . esc_attr__('CBC Slider', 'cbc-slider') . '">
            <div class="cbc-slider__track">' . $slides_html . '</div>
        </div>
        ' . $arrows . $dots . '
    </div>';
}

/**
 * Resolve a default slider ID. Prefers an overrideable option/filter, otherwise the latest published slider.
 */
function cbc_slider_get_default_id() {
    $explicit = (int) apply_filters('cbc_slider_default_id', (int) get_option('cbc_slider_default_id', 0));
    if ($explicit) {
        return $explicit;
    }

    $query = new WP_Query(array(
        'post_type' => 'cbc_slider',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'fields' => 'ids',
    ));

    $id = $query->have_posts() ? (int) $query->posts[0] : 0;
    wp_reset_postdata();

    return $id;
}

/**
 * Shortcode: [cbc_slider id="123"]
 */
function cbc_slider_shortcode($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts, 'cbc_slider');
    $post_id = intval($atts['id']);
    if (!$post_id) return '';
    $data = get_post_meta($post_id, '_cbc_slider_data', true);
    if (empty($data)) return '';
    return cbc_slider_render_from_data($data);
}
add_shortcode('cbc_slider', 'cbc_slider_shortcode');

/**
 * Shortcode: [cbc_slider_latest] renders the configured default slider or latest published.
 */
function cbc_slider_shortcode_latest($atts) {
    $post_id = cbc_slider_get_default_id();
    if (!$post_id) return '';
    return cbc_slider_render_by_id($post_id);
}
add_shortcode('cbc_slider_latest', 'cbc_slider_shortcode_latest');

/**
 * Template tag to render a slider by ID.
 */
function cbc_slider_render($post_id) {
    echo cbc_slider_render_by_id($post_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
function cbc_slider_render_by_id($post_id) {
    $post_id = intval($post_id);
    if (!$post_id) {
        return '';
    }
    $data = get_post_meta($post_id, '_cbc_slider_data', true);
    return cbc_slider_render_from_data($data);
}

/**
 * Template tag + helper to render the default/last slider without hardcoding IDs.
 */
function cbc_slider_render_default() {
    echo cbc_slider_render_default_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function cbc_slider_render_default_html() {
    $post_id = cbc_slider_get_default_id();
    if (!$post_id) {
        return '';
    }
    return cbc_slider_render_by_id($post_id);
}

/**
 * Admin columns: show ID and shortcode.
 */
function cbc_slider_admin_columns($columns) {
    $new = array();
    foreach ($columns as $key => $label) {
        if ($key === 'date') {
            $new['cbc_id'] = __('ID', 'cbc-slider');
            $new['cbc_shortcode'] = __('Shortcode', 'cbc-slider');
        }
        $new[$key] = $label;
    }
    if (!isset($new['cbc_id'])) {
        $new['cbc_id'] = __('ID', 'cbc-slider');
        $new['cbc_shortcode'] = __('Shortcode', 'cbc-slider');
    }
    return $new;
}
add_filter('manage_cbc_slider_posts_columns', 'cbc_slider_admin_columns');

function cbc_slider_admin_columns_content($column, $post_id) {
    if ($column === 'cbc_id') {
        echo intval($post_id);
    } elseif ($column === 'cbc_shortcode') {
        echo '<code>[cbc_slider id="' . intval($post_id) . '"]</code>';
    }
}
add_action('manage_cbc_slider_posts_custom_column', 'cbc_slider_admin_columns_content', 10, 2);

// ================= Plugin Usage Instructions =================

/**
 * Add "Usage Guide" link to plugin action links on plugins page.
 */
function cbc_slider_plugin_action_links($links) {
    $usage_link = '<a href="' . esc_url(admin_url('admin.php?page=cbc-slider-usage')) . '">' . __('Usage Guide', 'cbc-slider') . '</a>';
    array_unshift($links, $usage_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cbc_slider_plugin_action_links');

/**
 * Register admin menu page for usage guide.
 */
function cbc_slider_register_usage_page() {
    add_submenu_page(
        'edit.php?post_type=cbc_slider',
        __('CBC Slider - Usage Guide', 'cbc-slider'),
        __('How to Use', 'cbc-slider'),
        'manage_options',
        'cbc-slider-usage',
        'cbc_slider_render_usage_page'
    );
}
add_action('admin_menu', 'cbc_slider_register_usage_page');

/**
 * Render the usage guide page.
 */
function cbc_slider_render_usage_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.', 'cbc-slider'));
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('CBC Slider - Usage Guide', 'cbc-slider'); ?></h1>
        
        <div style="max-width: 800px; margin: 20px 0; background: #f1f1f1; padding: 20px; border-radius: 5px;">
            <h2><?php echo esc_html__('Quick Start', 'cbc-slider'); ?></h2>
            <ol>
                <li><strong><?php echo esc_html__('Create a Slider', 'cbc-slider'); ?>:</strong> Go to <a href="<?php echo esc_url(admin_url('post-new.php?post_type=cbc_slider')); ?>">CBC Slider → Add New</a></li>
                <li><strong><?php echo esc_html__('Add Slides', 'cbc-slider'); ?>:</strong> Click "Add Slide" to add images or videos from your media library</li>
                <li><strong><?php echo esc_html__('Configure Options', 'cbc-slider'); ?>:</strong> Set autoplay, animation delay, navigation, and responsive heights</li>
                <li><strong><?php echo esc_html__('Add Overlays', 'cbc-slider'); ?>:</strong> Optionally add custom HTML text, buttons, or content on top of slides</li>
                <li><strong><?php echo esc_html__('Publish & Embed', 'cbc-slider'); ?>:</strong> Publish your slider and use the shortcode shown in the list</li>
            </ol>
        </div>

        <h2><?php echo esc_html__('How to Use on Your Site', 'cbc-slider'); ?></h2>
        <p><?php echo esc_html__('Once you have created and published a slider, you can embed it in two ways:', 'cbc-slider'); ?></p>

        <h3><?php echo esc_html__('Option 1: Using the Shortcode', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Copy the shortcode for any slider from the CBC Slider list and paste it into any page or post:', 'cbc-slider'); ?></p>
        <pre style="background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>[cbc_slider id="123"]</code></pre>
        <p><em><?php echo esc_html__('Replace "123" with your slider\'s ID.', 'cbc-slider'); ?></em></p>

        <h3><?php echo esc_html__('Option 2: Using the Latest Slider Shortcode', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('This shortcode will automatically display your most recently published slider:', 'cbc-slider'); ?></p>
        <pre style="background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>[cbc_slider_latest]</code></pre>

        <h3><?php echo esc_html__('Option 3: Using in Theme Templates', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Add this code to your theme template files (e.g., front-page.php, index.php):', 'cbc-slider'); ?></p>
        <pre style="background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>&lt;?php cbc_slider_render(123); ?&gt;</code></pre>
        <p><em><?php echo esc_html__('Or use the default slider:', 'cbc-slider'); ?></em></p>
        <pre style="background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>&lt;?php cbc_slider_render_default(); ?&gt;</code></pre>

        <h2><?php echo esc_html__('Slider Configuration Options', 'cbc-slider'); ?></h2>
        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
            <tr style="background: #e9ecef;">
                <th style="border: 1px solid #ddd; padding: 10px; text-align: left;"><strong><?php echo esc_html__('Option', 'cbc-slider'); ?></strong></th>
                <th style="border: 1px solid #ddd; padding: 10px; text-align: left;"><strong><?php echo esc_html__('Description', 'cbc-slider'); ?></strong></th>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Autoplay', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Enable automatic slide transitions', 'cbc-slider'); ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Delay', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Time between slides in milliseconds (default: 5000ms = 5 seconds)', 'cbc-slider'); ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Loop', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Slider repeats after the last slide', 'cbc-slider'); ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Pause on Hover', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Autoplay pauses when user hovers over the slider', 'cbc-slider'); ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Show Arrows', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Display Previous/Next navigation buttons', 'cbc-slider'); ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Show Dots', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Display slide indicator dots at the bottom', 'cbc-slider'); ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Object Fit', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('How media fills the slide (cover, contain, fill, etc.)', 'cbc-slider'); ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Aspect Ratio', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Default slide dimensions (e.g., 16/9, 4/3, 1/1)', 'cbc-slider'); ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Responsive Heights', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Set custom heights for mobile (Sm), tablet (Md), desktop (Lg), and large screens (Xl)', 'cbc-slider'); ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="border: 1px solid #ddd; padding: 10px;"><strong><?php echo esc_html__('Video Settings', 'cbc-slider'); ?></strong></td>
                <td style="border: 1px solid #ddd; padding: 10px;"><?php echo esc_html__('Muted, loop, and controls for video slides', 'cbc-slider'); ?></td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Slide Types', 'cbc-slider'); ?></h2>
        <h3><?php echo esc_html__('Image Slides', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Upload images from your media library or direct URL. Supports responsive image formats.', 'cbc-slider'); ?></p>

        <h3><?php echo esc_html__('Video Slides', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Embed videos from your media library. Optionally set a poster image to display before playback.', 'cbc-slider'); ?></p>

        <h3><?php echo esc_html__('Slide Links', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Make slides clickable by adding a URL. Choose to open in the same window or new tab.', 'cbc-slider'); ?></p>

        <h3><?php echo esc_html__('Overlay Content', 'cbc-slider'); ?></h3>
        <p><?php echo esc_html__('Add custom HTML overlays to each slide for text, buttons, or interactive content using the built-in editor.', 'cbc-slider'); ?></p>

        <h2><?php echo esc_html__('Tips & Best Practices', 'cbc-slider'); ?></h2>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><?php echo esc_html__('Use high-quality images optimized for web (try Smush or similar plugins)', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('Set appropriate slide delays - too fast is distracting, too slow feels disconnected', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('Test responsive heights on mobile and tablet devices', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('For videos, always specify a poster image for better UX', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('Keep overlay text clear and readable with sufficient contrast', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('Use "object-fit: contain" for logos, "cover" for background photos', 'cbc-slider'); ?></li>
            <li><?php echo esc_html__('Ensure slide links have proper SEO attributes and are accessible', 'cbc-slider'); ?></li>
        </ul>

        <h2><?php echo esc_html__('Support', 'cbc-slider'); ?></h2>
        <p><?php echo esc_html__('For issues or feature requests, contact CBC support or visit the plugin documentation.', 'cbc-slider'); ?></p>

        <p style="margin-top: 30px; text-align: center; color: #666;">
            <em><?php echo esc_html__('CBC Slider v', 'cbc-slider') . esc_html(CBC_SLIDER_VERSION); ?></em>
        </p>
    </div>
    <?php
}

/**
 * Add "Usage Guide" row meta link on plugins page.
 */
function cbc_slider_plugin_row_meta($links, $file) {
    if (plugin_basename(__FILE__) === $file) {
        $usage_link = '<a href="' . esc_url(admin_url('admin.php?page=cbc-slider-usage')) . '" target="_blank">' . __('Documentation', 'cbc-slider') . '</a>';
        $links[] = $usage_link;
    }
    return $links;
}
add_filter('plugin_row_meta', 'cbc_slider_plugin_row_meta', 10, 2);

