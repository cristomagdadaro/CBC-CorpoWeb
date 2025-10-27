<?php
/**
 * Plugin Name: CBC Media Archive (Photos & Videos)
 * Description: Separate Photos and Videos archive management with front-end display (grid/list). Photos support 300x300 grid, double‑click 50% zoom, and full-size download.
 * Version: 1.0.0
 * Author: Cristo Rey C. Magdadaro
 */

if (!defined('ABSPATH')) { exit; }

class CBC_Media_Archive {
    const PHOTO_CPT = 'cbc_photo';
    const VIDEO_CPT = 'cbc_video';

    const PHOTO_META = '_cbc_photo_attachment_id';
    const VIDEO_TYPE_META = '_cbc_video_type'; // url|file
    const VIDEO_URL_META  = '_cbc_video_url';
    const VIDEO_FILE_META = '_cbc_video_file_id';
    const VIDEO_POSTER_META = '_cbc_video_poster_id';

    public function __construct() {
        add_action('init', [$this, 'register_cpts']);
        add_action('after_setup_theme', [$this, 'register_image_sizes']);

        // Admin UI
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
        add_action('save_post', [$this, 'save_post_meta']);

        // Shortcodes
        add_shortcode('cbc_photos', [$this, 'shortcode_photos']);
        add_shortcode('cbc_videos', [$this, 'shortcode_videos']);

        // Activation hook
        register_activation_hook(__FILE__, [__CLASS__, 'on_activate']);
    }

    public function register_cpts() {
        // Parent menu: Archives
        $parent_slug = 'edit.php?post_type=' . self::PHOTO_CPT; // Photos will be first

	    // Videos (as a submenu under Photos/Archives)
	    register_post_type(self::VIDEO_CPT, [
		    'labels' => [
			    'name' => 'Videos',
			    'singular_name' => 'Video',
			    'add_new_item' => 'Add New Video',
			    'edit_item' => 'Edit Video',
			    'new_item' => 'New Video',
			    'view_item' => 'View Video',
			    'search_items' => 'Search Videos',
			    'not_found' => 'No videos found',
			    'menu_name' => 'Videos',
		    ],
		    'public' => true,
		    'menu_icon' => 'dashicons-video-alt3',
		    'show_in_menu' => 'edit.php?post_type=' . self::PHOTO_CPT,
		    'supports' => ['title','editor','thumbnail'],
		    'has_archive' => false,
		    'show_in_rest' => true,
	    ]);

        // Photos
        register_post_type(self::PHOTO_CPT, [
            'labels' => [
                'name' => 'Photos',
                'singular_name' => 'Photo',
                'add_new_item' => 'Add New Photo',
                'edit_item' => 'Edit Photo',
                'new_item' => 'New Photo',
                'view_item' => 'View Photo',
                'search_items' => 'Search Photos',
                'not_found' => 'No photos found',
                'menu_name' => 'Archives',
            ],
            'public' => true,
            'menu_icon' => 'dashicons-images-alt2',
            'show_in_menu' => true,
            'supports' => ['title','editor','thumbnail'],
            'has_archive' => false,
            'show_in_rest' => true,
        ]);
    }

    public function register_image_sizes() {
        if (function_exists('add_image_size')) {
            add_image_size('cbc_square_300', 300, 300, true);
        }
    }

    public function add_metaboxes() {
        add_meta_box('cbc_photo_meta', 'Photo File', [$this, 'render_photo_metabox'], self::PHOTO_CPT, 'normal', 'default');
        add_meta_box('cbc_video_meta', 'Video Source', [$this, 'render_video_metabox'], self::VIDEO_CPT, 'normal', 'default');
    }

    public function render_photo_metabox($post) {
        wp_nonce_field('cbc_photo_meta', 'cbc_photo_meta_nonce');
        $att_id = (int) get_post_meta($post->ID, self::PHOTO_META, true);
        $thumb = $att_id ? wp_get_attachment_image($att_id, 'medium', false, ['style' => 'max-width:100%;height:auto;border:1px solid #ddd;padding:4px;']) : '';
        echo '<p>Select or upload a single image for this photo item.</p>';
        echo '<div id="cbc-photo-preview">' . $thumb . '</div>';
        echo '<input type="hidden" id="cbc_photo_attachment_id" name="cbc_photo_attachment_id" value="' . esc_attr($att_id) . '">';
        echo '<p><button type="button" class="button" id="cbc-photo-select">Select Image</button> ';
        echo '<button type="button" class="button" id="cbc-photo-remove">Remove</button></p>';
        $this->print_media_selector_js('cbc-photo-select', 'cbc_photo_attachment_id', 'image', 'cbc-photo-preview', 'cbc-photo-remove');
    }

    public function render_video_metabox($post) {
        wp_nonce_field('cbc_video_meta', 'cbc_video_meta_nonce');
        $type  = get_post_meta($post->ID, self::VIDEO_TYPE_META, true) ?: 'url';
        $url   = get_post_meta($post->ID, self::VIDEO_URL_META, true);
        $file  = (int) get_post_meta($post->ID, self::VIDEO_FILE_META, true);
        $poster= (int) get_post_meta($post->ID, self::VIDEO_POSTER_META, true);

        echo '<p>Choose a video source: external URL (YouTube/Vimeo) or upload a video file.</p>';
        echo '<p>
                <label><input type="radio" name="cbc_video_type" value="url" ' . checked('url', $type, false) . '> URL</label>
                &nbsp;&nbsp;
                <label><input type="radio" name="cbc_video_type" value="file" ' . checked('file', $type, false) . '> File</label>
            </p>';
        echo '<p><label>Video URL<br><input type="url" name="cbc_video_url" value="' . esc_attr($url) . '" class="widefat"></label></p>';
        echo '<p><label>Video File</label><br>';
        echo '<input type="hidden" id="cbc_video_file_id" name="cbc_video_file_id" value="' . esc_attr($file) . '">';
        $file_label = $file ? esc_html(basename(get_attached_file($file))) : 'No file selected';
        echo '<span id="cbc-video-file-label">' . $file_label . '</span> ';
        echo '<button type="button" class="button" id="cbc-video-file-select">Select File</button> ';
        echo '<button type="button" class="button" id="cbc-video-file-remove">Remove</button></p>';
        
        echo '<p><label>Poster Image (optional)</label><br>';
        echo '<input type="hidden" id="cbc_video_poster_id" name="cbc_video_poster_id" value="' . esc_attr($poster) . '">';
        $poster_img = $poster ? wp_get_attachment_image($poster, 'medium', false, ['style'=>'max-width:100%;height:auto;border:1px solid #ddd;padding:4px;']) : '';
        echo '<div id="cbc-video-poster-preview">' . $poster_img . '</div>';
        echo '<button type="button" class="button" id="cbc-video-poster-select">Select Poster</button> ';
        echo '<button type="button" class="button" id="cbc-video-poster-remove">Remove</button></p>';

        $this->print_media_selector_js('cbc-video-file-select', 'cbc_video_file_id', 'video', null, 'cbc-video-file-remove', 'cbc-video-file-label');
        $this->print_media_selector_js('cbc-video-poster-select', 'cbc_video_poster_id', 'image', 'cbc-video-poster-preview', 'cbc-video-poster-remove');

        echo '<script>document.addEventListener("DOMContentLoaded",function(){
            function toggle(){
                var t = document.querySelector("input[name=cbc_video_type]:checked").value;
                var urlRow = document.querySelector("input[name=cbc_video_url]").closest("p");
                var fileRow = document.getElementById("cbc-video-file-select").closest("p");
                if(t==="url"){ urlRow.style.display="block"; fileRow.style.display="block"; }
            }
        });</script>';
    }

    private function print_media_selector_js($button_id, $input_id, $type='image', $preview_id=null, $remove_button_id=null, $label_id=null) {
        // Enqueue media scripts
        wp_enqueue_media();
        $choose = $type === 'video' ? 'Select video' : 'Select image';
        $multiple = 'false';
        echo '<script>(function(){
            var frame;
            function selectAttachment(att){
                var input = document.getElementById("' . esc_js($input_id) . '");
                input.value = att.id;
                ' . ($preview_id ? 'document.getElementById("'.esc_js($preview_id).'").innerHTML = att.sizes && att.sizes.medium ? "<img src=\\""+att.sizes.medium.url+"\\" style=\\"max-width:100%;height:auto;border:1px solid #ddd;padding:4px;\\">" : "<strong>Selected</strong>";' : '') . '
                ' . ($label_id ? 'document.getElementById("'.esc_js($label_id).'").textContent = att.filename || (att.url ? att.url.split("/").pop() : "Selected");' : '') . '
            }
            document.addEventListener("DOMContentLoaded", function(){
                var btn = document.getElementById("' . esc_js($button_id) . '");
                if(btn){ btn.addEventListener("click", function(e){
                    e.preventDefault();
                    if(frame){ frame.open(); return; }
                    frame = wp.media({ title: "' . esc_js($choose) . '", button: { text: "Use this" }, multiple: ' . $multiple . ', library: { type: "' . esc_js($type) . '" } });
                    frame.on("select", function(){ var att = frame.state().get("selection").first().toJSON(); selectAttachment(att); });
                    frame.open();
                }); }
                ' . ($remove_button_id ? 'var rbtn = document.getElementById("'.esc_js($remove_button_id).'"); if(rbtn){ rbtn.addEventListener("click", function(e){ e.preventDefault(); var input=document.getElementById("'.esc_js($input_id).'"); input.value=""; '.($preview_id?'document.getElementById("'.esc_js($preview_id).'").innerHTML="";':'').' '.($label_id?'document.getElementById("'.esc_js($label_id).'").textContent="No file selected";':'').' }); }' : '') . '
            });
        })();</script>';
    }

    public function save_post_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $post_type = get_post_type($post_id);
        if ($post_type === self::PHOTO_CPT) {
            if (!isset($_POST['cbc_photo_meta_nonce']) || !wp_verify_nonce($_POST['cbc_photo_meta_nonce'], 'cbc_photo_meta')) return;
            $att_id = isset($_POST['cbc_photo_attachment_id']) ? (int) $_POST['cbc_photo_attachment_id'] : 0;
            if ($att_id) {
                update_post_meta($post_id, self::PHOTO_META, $att_id);
                // Set featured image if empty
                if (!has_post_thumbnail($post_id)) {
                    set_post_thumbnail($post_id, $att_id);
                }
            } else {
                delete_post_meta($post_id, self::PHOTO_META);
            }
        } elseif ($post_type === self::VIDEO_CPT) {
            if (!isset($_POST['cbc_video_meta_nonce']) || !wp_verify_nonce($_POST['cbc_video_meta_nonce'], 'cbc_video_meta')) return;
            $type = isset($_POST['cbc_video_type']) && $_POST['cbc_video_type'] === 'file' ? 'file' : 'url';
            update_post_meta($post_id, self::VIDEO_TYPE_META, $type);
            $url = isset($_POST['cbc_video_url']) ? esc_url_raw($_POST['cbc_video_url']) : '';
            update_post_meta($post_id, self::VIDEO_URL_META, $url);
            $file_id = isset($_POST['cbc_video_file_id']) ? (int) $_POST['cbc_video_file_id'] : 0;
            if ($file_id) { update_post_meta($post_id, self::VIDEO_FILE_META, $file_id); } else { delete_post_meta($post_id, self::VIDEO_FILE_META); }
            $poster_id = isset($_POST['cbc_video_poster_id']) ? (int) $_POST['cbc_video_poster_id'] : 0;
            if ($poster_id) { update_post_meta($post_id, self::VIDEO_POSTER_META, $poster_id); } else { delete_post_meta($post_id, self::VIDEO_POSTER_META); }
            if ($poster_id && !has_post_thumbnail($post_id)) { set_post_thumbnail($post_id, $poster_id); }
        }
    }

    public function shortcode_photos($atts) {
        $atts = shortcode_atts([
            'view' => 'grid',
            'per_page' => 24,
        ], $atts, 'cbc_photos');
        $view = $atts['view'] === 'list' ? 'list' : 'grid';

        $q = new WP_Query([
            'post_type' => self::PHOTO_CPT,
            'posts_per_page' => intval($atts['per_page']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        ob_start();
        echo '<div class="cbc-photos-archive">';
        echo '<div class="flex justify-end gap-2 mb-3"><button class="cbc-toggle-view px-3 py-1 border rounded" data-view="grid">Grid</button><button class="cbc-toggle-view px-3 py-1 border rounded" data-view="list">List</button></div>';

        $container_class = $view === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3' : 'flex flex-col divide-y';
        echo '<div class="cbc-photos-container ' . esc_attr($container_class) . '" data-initial-view="' . esc_attr($view) . '">';
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $pid = get_the_ID();
                $att_id = (int) get_post_meta($pid, self::PHOTO_META, true);
                if (!$att_id) { continue; }
                $square = wp_get_attachment_image_src($att_id, 'cbc_square_300');
                $full   = wp_get_attachment_image_src($att_id, 'full');
                if (!$square || !$full) { continue; }
                $title = esc_attr(get_the_title());
                $desc  = esc_html(wp_strip_all_tags(get_the_excerpt() ?: ''));
                echo '<div class="cbc-photo-item group bg-white shadow hover:shadow-md transition rounded-md overflow-hidden relative flex items-center '.($view==='grid'?'flex-col':'gap-3').'" data-full="'.esc_url($full[0]).'" data-title="'.$title.'">';
	            echo '<div class="cbc-photo-title absolute top-3 left-0 z-[99] pl-4 py-1 text-[#006837] drop-shadow-md bg-gradient-to-r w-full from-white to-transparent whitespace-nowrap overflow-hidden overflow-ellipsis"><h3 class="font-bold text-sm">'.esc_html(get_the_title()).'</h3></div>';
                echo '<img src="'.esc_url($square[0]).'" alt="'.$title.'" class="aspect-[16/9] object-cover cursor-zoom-in select-none" draggable="false">';
                echo '<div class="w-fit mb-2 flex flex-col items-end absolute bottom-0 group-hover:right-0 right-[-10rem] duration-300 ease-in-out">';
                if ($desc) echo '<div class="text-xs text-gray-600">'.$desc.'</div>';
                echo '<a href="'.esc_url($full[0]).'" download class="inline-block text-xs px-2 py-1 border bg-white rounded-md hover:bg-gray-50 scale-75">Download Full Size</a>';
                echo '</div>';
                echo '</div>';
            }
            wp_reset_postdata();
        } else {
            echo '<div>No photos yet.</div>';
        }
        echo '</div>'; // container

        // Modal for 50% view
        echo '<div id="cbc-photo-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
                <div class="bg-white p-2 rounded shadow max-w-[95vw] max-h-[90vh] overflow-auto">
                    <div class="flex justify-between items-center mb-2">
                        <div id="cbc-photo-modal-title" class="font-semibold text-sm"></div>
                        <button id="cbc-photo-modal-close" class="px-2 py-1 border rounded">Close</button>
                    </div>
                    <div class="flex items-center justify-center"><img id="cbc-photo-modal-img" src="" alt="" style="width:auto;height:auto;max-width:100%;max-height:80vh" /></div>
                    <div class="mt-2 text-right"><a id="cbc-photo-modal-download" href="#" download class="px-3 py-1 border rounded inline-block">Download Full Size</a></div>
                </div>
            </div>';

        // Inline JS to handle view toggle and dblclick modal at 50% natural size
        echo '<script>(function(){
            function setView(container, view){
                container.className = container.className.replace(/cbc-photos-container[^\"]+\"/, "");
            }
            var root = document.currentScript.closest(".cbc-photos-archive");
            var container = root.querySelector(".cbc-photos-container");
            var buttons = root.querySelectorAll(".cbc-toggle-view");
            buttons.forEach(function(btn){ btn.addEventListener("click", function(){
                var view = btn.getAttribute("data-view");
                if(view==="grid"){
                    container.className = "cbc-photos-container grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3";
                    root.querySelectorAll(".cbc-photo-item").forEach(function(it){ it.classList.remove("flex-row","gap-3"); it.classList.add("flex","flex-col"); });
                } else {
                    container.className = "cbc-photos-container flex flex-col divide-y gap-3";
                    root.querySelectorAll(".cbc-photo-item").forEach(function(it){ it.classList.remove("flex","flex-col"); it.classList.add("flex","flex-row","gap-3"); });
                }
            }); });

            var modal = document.getElementById("cbc-photo-modal");
            var modalImg = document.getElementById("cbc-photo-modal-img");
            var modalTitle = document.getElementById("cbc-photo-modal-title");
            var modalDl = document.getElementById("cbc-photo-modal-download");
            var closeBtn = document.getElementById("cbc-photo-modal-close");
            closeBtn.addEventListener("click", function(){ modal.classList.add("hidden"); modalImg.src=""; });
            modal.addEventListener("click", function(e){ if(e.target===modal){ closeBtn.click(); } });

            
            root.querySelectorAll(".cbc-photo-item img").forEach(function(img) {
			    img.addEventListener("dblclick", function() {   
			        var parent = img.closest(".cbc-photo-item");
			        var full = parent.getAttribute("data-full");
			        if (full) {
			            window.open(full, "_blank");
			        }
			    });
			});

        })();</script>';

        echo '</div>'; // archive root
        return ob_get_clean();
    }

    public function shortcode_videos($atts) {
        $atts = shortcode_atts([
            'view' => 'grid',
            'per_page' => 24,
        ], $atts, 'cbc_videos');
        $view = $atts['view'] === 'list' ? 'list' : 'grid';

        $q = new WP_Query([
            'post_type' => self::VIDEO_CPT,
            'posts_per_page' => intval($atts['per_page']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        ob_start();
        $container_class = $view === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'flex flex-col divide-y';
        echo '<div class="cbc-videos-archive ' . esc_attr($container_class) . '">';
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $pid = get_the_ID();
                $type = get_post_meta($pid, self::VIDEO_TYPE_META, true) ?: 'url';
                $url  = get_post_meta($pid, self::VIDEO_URL_META, true);
                $file = (int) get_post_meta($pid, self::VIDEO_FILE_META, true);
                $poster = (int) get_post_meta($pid, self::VIDEO_POSTER_META, true);
                $title = esc_html(get_the_title());
                echo '<div class="bg-white rounded-md shadow relative">';
	            echo '<div class="absolute top-3 left-0 z-[99] pl-4 py-1 text-[#006837] drop-shadow-md backdrop-blur-md bg-gradient-to-r w-full from-white to-transparent whitespace-nowrap overflow-hidden overflow-ellipsis"><h3 class="font-bold text-sm">'.$title.'</h3></div>';
                echo '<div class="aspect-video w-full bg-black rounded-md overflow-hidden z-[10]">';
                if ($type === 'file' && $file) {
                    $src = wp_get_attachment_url($file);
                    $poster_url = $poster ? wp_get_attachment_url($poster) : '';
                    echo '<video controls preload="metadata" '.($poster_url?'poster="'.esc_url($poster_url).'"':'').' class="w-full h-full rounded-md"><source src="'.esc_url($src).'" type="'.esc_attr(get_post_mime_type($file)).'"></video>';
                } else if (!empty($url)) {
                    // Try oEmbed
                    $embed = wp_oembed_get($url, ['height' => 360]);
                    if ($embed) { echo $embed; }
                    else {
                        echo '<a href="'.esc_url($url).'" target="_blank" class="text-blue-600 underline">'.esc_html($url).'</a>';
                    }
                } else {
                    echo '<div class="text-white p-4">No video source.</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            wp_reset_postdata();
        } else {
            echo '<div>No videos yet.</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function on_activate() {
        // Ensure CPTs exist for rewrite flush
        $self = new self();
        $self->register_cpts();
        flush_rewrite_rules();

        // Create pages if not exist
        $pages = [
            'Photo Archive' => '[cbc_photos view="grid"]',
            'Video Archive' => '[cbc_videos view="grid"]',
        ];
        foreach ($pages as $title => $content) {
            $existing = get_page_by_title($title);
            if (!$existing) {
                wp_insert_post([
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ]);
            }
        }
    }
}

new CBC_Media_Archive();
