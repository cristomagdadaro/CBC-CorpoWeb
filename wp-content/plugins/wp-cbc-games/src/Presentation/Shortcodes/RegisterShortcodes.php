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
        // Tailwind is optional; keep if theme provides it via this handle.
        if (wp_script_is('cbc-tailwind', 'registered')) {
            wp_enqueue_script('cbc-tailwind');
        }
        // cbc-games-bootstrap is registered globally in plugin bootstrap and enqueued there.
    }

    public function renderQuiz($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-quiz-js', CBC_GAMES_URL . 'assets/js/quiz.js', [], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/quiz.php';
        return ob_get_clean();
    }

    public function renderMemory($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-memory-js', CBC_GAMES_URL . 'assets/js/memory.js', [], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/memory.php';
        return ob_get_clean();
    }

    public function renderScramble($atts = [], $content = '')
    {
        $this->enqueueCommon();
        wp_enqueue_script('cbc-scramble-js', CBC_GAMES_URL . 'assets/js/scramble.js', [], '1.0.0', true);
        ob_start();
        include CBC_GAMES_PATH . 'views/scramble.php';
        return ob_get_clean();
    }
}
