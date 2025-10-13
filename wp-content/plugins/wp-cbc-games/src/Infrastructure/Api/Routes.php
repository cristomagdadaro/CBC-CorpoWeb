<?php
namespace CBCGames\Infrastructure\Api;

use CBCGames\Application\Quiz\QuizService;
use CBCGames\Application\Scramble\ScrambleService;
use CBCGames\Infrastructure\Repository\Quiz\InMemoryQuestionRepository;
use CBCGames\Infrastructure\Repository\Scramble\InMemoryWordRepository;
use CBCGames\Infrastructure\Repository\Memory\PluginAssetImageRepository;
use CBCGames\Infrastructure\Repository\Leaderboard\TableLeaderboardRepository;

if (!defined('ABSPATH')) { exit; }

class Routes
{
    private array $allowedGames = ['quiz','memory','scramble'];

    public function register()
    {
        register_rest_route('cbc-games/v1', '/quiz', [
            'methods' => 'GET',
            'callback' => [$this, 'getQuiz'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('cbc-games/v1', '/scramble', [
            'methods' => 'GET',
            'callback' => [$this, 'getScramble'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('cbc-games/v1', '/memory', [
            'methods' => 'GET',
            'callback' => [$this, 'getMemory'],
            'permission_callback' => '__return_true',
        ]);

        // Leaderboard routes
        register_rest_route('cbc-games/v1', '/leaderboard/(?P<game>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getLeaderboard'],
            'permission_callback' => '__return_true',
            'args' => [
                'game' => ['required' => true],
                'limit' => ['required' => false],
            ],
        ]);
        register_rest_route('cbc-games/v1', '/leaderboard/(?P<game>[a-zA-Z0-9_-]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'postLeaderboard'],
            'permission_callback' => '__return_true',
            'args' => [ 'game' => ['required' => true] ],
        ]);
    }

    public function getQuiz($request)
    {
        $service = new QuizService(new InMemoryQuestionRepository());
        $questions = $service->tenRandom();
        $data = [];
        foreach ($questions as $q) {
            $data[] = [
                'question' => $q->text(),
                'options' => $q->options(),
                'answer'  => $q->answer(),
            ];
        }
        return rest_ensure_response(['questions' => $data]);
    }

    public function getScramble($request)
    {
        $service = new ScrambleService(new InMemoryWordRepository());
        $words = $service->pick(5);
        return rest_ensure_response(['words' => $words]);
    }

    public function getMemory($request)
    {
        $repo = new PluginAssetImageRepository();
        $images = $repo->all();
        return rest_ensure_response(['images' => $images]);
    }

    public function getLeaderboard($request)
    {
        $game = sanitize_key($request['game'] ?? '');
        if (!in_array($game, $this->allowedGames, true)) {
            return new \WP_Error('invalid_game', 'Invalid game', ['status' => 400]);
        }
        $limit = intval($request->get_param('limit') ?? 10);
        $repo = new TableLeaderboardRepository();
        $rows = $repo->top($game, $limit ?: 10);
        return rest_ensure_response(['leaderboard' => $rows]);
    }

    public function postLeaderboard($request)
    {
        $game = sanitize_key($request['game'] ?? '');
        if (!in_array($game, $this->allowedGames, true)) {
            return new \WP_Error('invalid_game', 'Invalid game', ['status' => 400]);
        }
        $params = $request->get_json_params();
        if (!is_array($params)) { $params = []; }

        // Honeypot field blocks simple bots
        $hp = isset($params['hp']) ? trim((string)$params['hp']) : '';
        if ($hp !== '') {
            return new \WP_Error('forbidden', 'Spam detected', ['status' => 403]);
        }

        $name = trim(sanitize_text_field($params['name'] ?? ''));
        $agency = trim(sanitize_text_field($params['agency'] ?? ''));
        $age = isset($params['age']) ? intval($params['age']) : null;
        $score = isset($params['score']) ? intval($params['score']) : 0;
        $time_ms = isset($params['time_ms']) ? intval($params['time_ms']) : null;
        $played_at = sanitize_text_field($params['played_at'] ?? '');

        // Basic required checks
        if ($name === '' || $agency === '') {
            return new \WP_Error('missing_fields', 'Name and Agency/School are required.', ['status' => 400]);
        }
        // Normalize lengths
        $name = mb_substr($name, 0, 100);
        $agency = mb_substr($agency, 0, 150);

        // Age bounds
        if (!is_null($age)) {
            if ($age < 1 || $age > 120) { $age = null; }
        }

        // Clamp score per game
        $maxScores = ['quiz' => 10, 'memory' => 8, 'scramble' => 5];
        $max = $maxScores[$game] ?? 10;
        if ($score < 0) { $score = 0; }
        if ($score > $max) { $score = $max; }

        // Clamp time_ms: 0 .. 24h
        if (!is_null($time_ms)) {
            if ($time_ms < 0) { $time_ms = 0; }
            $day = 24*60*60*1000; if ($time_ms > $day) { $time_ms = $day; }
        }

        // Validate date (YYYY-MM-DD) or default to today
        if (!$played_at || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $played_at)) {
            $played_at = current_time('Y-m-d');
        }

        $repo = new TableLeaderboardRepository();
        $saved = $repo->add($game, [
            'name' => $name,
            'agency' => $agency,
            'age' => $age,
            'score' => $score,
            'time_ms' => $time_ms,
            'played_at' => $played_at,
        ]);
        // Return updated top 10
        $top = $repo->top($game, 10);
        return rest_ensure_response(['saved' => $saved, 'leaderboard' => $top]);
    }
}
