<?php
namespace CBCGames\Presentation\Shortcodes;

if (!defined('ABSPATH')) { exit; }

class RegisterShortcodes
{
    public function register()
    {
        add_shortcode('cbc_quiz', [$this, 'renderQuiz']);
        add_shortcode('cbc_memory', [$this, 'renderMemory']);
        add_shortcode('cbc_scramble', [$this, 'renderScramble']);
    }

    private function enqueueCommon()
    {
        wp_enqueue_style('cbc-games-styles');
        wp_enqueue_script('cbc-tailwind');
        // Localize base API URL and plugin URL
        wp_register_script('cbc-games-bootstrap', '', [], '1.0.0', true);
        wp_enqueue_script('cbc-games-bootstrap');
        wp_add_inline_script('cbc-games-bootstrap', sprintf(
            'window.cbcGames = { apiBase: %s, pluginUrl: %s };',
            json_encode(esc_url_raw(get_rest_url(null, 'cbc-games/v1/'))),
            json_encode(esc_url_raw(CBC_GAMES_URL))
        ));
    }

    public function renderQuiz($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-quiz-js', CBC_GAMES_URL . 'assets/js/quiz.js', ['cbc-games-bootstrap'], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/quiz.php';
        return ob_get_clean();
    }

    public function renderMemory($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-memory-js', CBC_GAMES_URL . 'assets/js/memory.js', ['cbc-games-bootstrap'], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/memory.php';
        return ob_get_clean();
    }

    public function renderScramble($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-scramble-js', CBC_GAMES_URL . 'assets/js/scramble.js', ['cbc-games-bootstrap'], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/scramble.php';
        return ob_get_clean();
    }
}
