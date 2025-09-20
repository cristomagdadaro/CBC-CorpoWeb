<?php
/**
 * Plugin Name: Developer Deck (Developer's Page)
 * Description: Simple developer dashboard to scan the codebase for features/shortcodes/CPTs/sidebars and to save developer notes and tips. Provides an admin UI and a shortcode [devs_developer_page] to render the Developer's Page.
 * Version: 0.1.0
 * Author: Dev Tools
 */

if (!defined('ABSPATH')) {
    exit;
}

class Developer_Deck {
    const OPTION_NOTES = 'developer_deck_notes';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_developer_deck_save', [$this, 'handle_save']);
        add_shortcode('devs_developer_page', [$this, 'shortcode_render']);
    }

    public function add_admin_menu() {
        add_management_page(
            __('Developer\'s Page', 'developer-deck'),
            __('Developer\'s Page', 'developer-deck'),
            'manage_options',
            'developer-deck',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $notes = get_option(self::OPTION_NOTES, []);

        echo '<div class="wrap"><h1>' . esc_html__('Developer\'s Page', 'developer-deck') . '</h1>';

        // Quick actions
        echo '<p>' . esc_html__('Use the Scan action to collect shortcodes, CPTs, sidebars, enqueues and other patterns from the codebase. Save any notes or tips below. Publish the Developer\'s Page using the [devs_developer_page] shortcode.', 'developer-deck') . '</p>';

        echo '<form method="post" style="margin-bottom:1rem;">';
        echo '<input type="hidden" name="action" value="developer_deck_scan" />';
        submit_button(__('Run Codebase Scan', 'developer-deck'), 'primary', 'developer_deck_scan');
        echo '</form>';

        // Handle scan action (simple nonce-less POST to same page)
        if (isset($_POST['developer_deck_scan'])) {
            echo '<h2>' . esc_html__('Scan Results', 'developer-deck') . '</h2>';
            $results = $this->scan_project();
            $this->render_scan_results($results);
        }

        // Notes editor
        echo '<h2>' . esc_html__('Developer Notes & Tips', 'developer-deck') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('developer_deck_save', 'developer_deck_nonce');
        echo '<input type="hidden" name="action" value="developer_deck_save" />';
        echo '<table class="form-table"><tr><th>' . esc_html__('Title', 'developer-deck') . '</th><td><input type="text" name="note_title" class="regular-text" /></td></tr>';
        echo '<tr><th>' . esc_html__('Content (Markdown/HTML)', 'developer-deck') . '</th><td><textarea name="note_content" rows="8" cols="80" class="large-text"></textarea></td></tr>';
        echo '<tr><th>' . esc_html__('Visible (front-end)', 'developer-deck') . '</th><td><label><input type="checkbox" name="note_public" value="1"> ' . esc_html__('Show on Developer\'s Page shortcode', 'developer-deck') . '</label></td></tr>';
        echo '</table>';
        submit_button(__('Save Note', 'developer-deck'));
        echo '</form>';

        // List existing notes
        if (!empty($notes) && is_array($notes)) {
            echo '<h3>' . esc_html__('Saved Notes', 'developer-deck') . '</h3>';
            echo '<ul>';
            foreach ($notes as $i => $n) {
                $title = isset($n['title']) ? $n['title'] : '(' . $i . ')';
                echo '<li><strong>' . esc_html($title) . '</strong> &nbsp; <small>' . esc_html(isset($n['public']) && $n['public'] ? 'public' : 'private') . '</small></li>';
            }
            echo '</ul>';
        }

        echo '<p style="margin-top:1rem;">' . esc_html__('Shortcode: use [devs_developer_page] on a page to show public notes and the last scan summary.', 'developer-deck') . '</p>';

        echo '</div>';
    }

    public function handle_save() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No.'));
        }
        if (!isset($_POST['developer_deck_nonce']) || !wp_verify_nonce($_POST['developer_deck_nonce'], 'developer_deck_save')) {
            wp_die(__('Invalid nonce.'));
        }
        $title = isset($_POST['note_title']) ? sanitize_text_field($_POST['note_title']) : '';
        $content = isset($_POST['note_content']) ? wp_kses_post($_POST['note_content']) : '';
        $public = isset($_POST['note_public']) && $_POST['note_public'] ? 1 : 0;

        $notes = get_option(self::OPTION_NOTES, []);
        $notes[] = ['title' => $title, 'content' => $content, 'public' => $public, 'created' => current_time('mysql')];
        update_option(self::OPTION_NOTES, $notes);

        wp_safe_redirect(admin_url('tools.php?page=developer-deck'));
        exit;
    }

    /**
     * Shortcode render: show public notes and last scan summary (one-off scan performed on request).
     */
    public function shortcode_render($atts = []) {
        $notes = get_option(self::OPTION_NOTES, []);
        $out = '<div class="developer-deck">';
        $out .= '<h2>Developer\'s Page</h2>';
        if (!empty($notes)) {
            foreach ($notes as $n) {
                if (empty($n['public'])) continue;
                $out .= '<article class="dev-note"><h3>' . esc_html($n['title']) . '</h3>';
                $out .= wp_kses_post(wpautop($n['content']));
                $out .= '</article>';
            }
        } else {
            $out .= '<p>No developer notes yet.</p>';
        }

        // Optionally run a lightweight scan (non-expensive)
        $scan = $this->scan_project(['limit_files' => 200]);
        $out .= $this->render_scan_results_html($scan);
        $out .= '</div>';
        return $out;
    }

    /**
     * Scan the project's wp-content folder for key patterns.
     * Returns an array with grouped results.
     */
    public function scan_project($opts = []) {
        $root = ABSPATH;
        $paths = [
            $root . 'wp-content/plugins',
            $root . 'wp-content/themes',
        ];

        $patterns = [
            'shortcodes' => '/add_shortcode\s*\(\s*["\']([a-z0-9_\-]+)["\']/',
            'post_types' => '/register_post_type\s*\(\s*["\']([a-z0-9_\-]+)["\']/',
            'taxonomies' => '/register_taxonomy\s*\(\s*["\']([a-z0-9_\-]+)["\']/',
            'sidebars'   => '/register_sidebar\s*\(/',
            'enqueues'   => '/wp_enqueue_(script|style)\s*\(/',
            'dynamic_sidebar' => '/dynamic_sidebar\s*\(/',
        ];

        $results = [];
        foreach ($patterns as $key => $re) {
            $results[$key] = [];
        }

        $fileCount = 0;
        foreach ($paths as $p) {
            if (!is_dir($p)) continue;
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p));
            foreach ($it as $file) {
                if (!$file->isFile()) continue;
                $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
                if (!in_array($ext, ['php','inc','tpl'])) continue;
                $fileCount++;
                if (isset($opts['limit_files']) && $fileCount > $opts['limit_files']) break 2;
                $content = @file_get_contents($file->getPathname());
                if ($content === false) continue;
                foreach ($patterns as $key => $re) {
                    if (preg_match_all($re, $content, $m)) {
                        foreach ($m[0] as $i => $match) {
                            $val = isset($m[1][$i]) ? $m[1][$i] : trim($match);
                            $results[$key][] = ['file' => str_replace(ABSPATH, '', $file->getPathname()), 'match' => $val];
                        }
                    }
                }
            }
        }

        return $results;
    }

    protected function render_scan_results($results) {
        echo '<div class="developer-deck-scan">';
        foreach ($results as $group => $items) {
            echo '<h3>' . esc_html(ucfirst(str_replace('_',' ',$group))) . ' (' . count($items) . ')</h3>';
            if (empty($items)) { echo '<p><em>None found.</em></p>'; continue; }
            echo '<ul style="max-height:240px; overflow:auto; background:#fff; padding:8px; border:1px solid #ddd;">';
            foreach ($items as $it) {
                echo '<li><code>' . esc_html($it['match']) . '</code> — <small>' . esc_html($it['file']) . '</small></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    protected function render_scan_results_html($results) {
        $out = '<section class="developer-deck-scan">';
        foreach ($results as $group => $items) {
            $out .= '<h3>' . esc_html(ucfirst(str_replace('_',' ',$group))) . ' (' . count($items) . ')</h3>';
            if (empty($items)) { $out .= '<p><em>None found.</em></p>'; continue; }
            $out .= '<ul style="max-height:240px; overflow:auto; background:#fff; padding:8px; border:1px solid #ddd;">';
            foreach ($items as $it) {
                $out .= '<li><code>' . esc_html($it['match']) . '</code> — <small>' . esc_html($it['file']) . '</small></li>';
            }
            $out .= '</ul>';
        }
        $out .= '</section>';
        return $out;
    }
}

new Developer_Deck();
