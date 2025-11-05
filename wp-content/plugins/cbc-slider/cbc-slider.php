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
                'videoMuted' => true,
                'videoLoop' => false,
                'videoControls' => false,
            ),
        );
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
            'overlayPosition' => !empty($s['overlayPosition']) ? sanitize_html_class($s['overlayPosition']) : 'bottom-left',
            'overlayHtml' => !empty($s['overlayHtml']) ? cbc_slider_sanitize_overlay_html($s['overlayHtml']) : '',
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
    $overlay_position = isset($slide['overlayPosition']) ? sanitize_html_class($slide['overlayPosition']) : 'bottom-left';

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

    $overlay_html = $overlay ? '<div class="cbc-slider__overlay cbc-slider__overlay--' . $overlay_position . '">' . $overlay . '</div>' : '';

    return '<div class="cbc-slider__slide" role="group" aria-roledescription="slide" aria-label="' . esc_attr($index . ' ' . __('of', 'cbc-slider') . ' ' . $total) . '">
        <div class="cbc-slider__mediaWrap">' . $media_html . '</div>
        ' . $overlay_html . '
    </div>';
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

    $style_inline = '';
    if (!empty($options['height'])) {
        $style_inline = ' style="--cbc-slider-aspect: initial; height:' . esc_attr($options['height']) . ';"';
    } elseif (!empty($options['aspectRatio'])) {
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

    return '<div class="cbc-slider"' . $data_attrs . $style_inline . '>
        <div class="cbc-slider__viewport" tabindex="0" aria-roledescription="carousel" aria-label="' . esc_attr__('CBC Slider', 'cbc-slider') . '">
            <div class="cbc-slider__track">' . $slides_html . '</div>
        </div>
        ' . $arrows . $dots . '
    </div>';
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
 * Template tag to render a slider by ID.
 */
function cbc_slider_render($post_id) {
    echo cbc_slider_render_by_id($post_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
function cbc_slider_render_by_id($post_id) {
    $data = get_post_meta(intval($post_id), '_cbc_slider_data', true);
    return cbc_slider_render_from_data($data);
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

