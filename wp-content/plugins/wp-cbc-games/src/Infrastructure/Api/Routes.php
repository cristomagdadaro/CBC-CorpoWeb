<?php
namespace CBCGames\Infrastructure\Api;

use CBCGames\Application\Quiz\QuizService;
use CBCGames\Application\Scramble\ScrambleService;
use CBCGames\Infrastructure\Repository\Quiz\InMemoryQuestionRepository;
use CBCGames\Infrastructure\Repository\Scramble\InMemoryWordRepository;
use CBCGames\Infrastructure\Repository\Memory\PluginAssetImageRepository;
use CBCGames\Infrastructure\Repository\Leaderboard\OptionLeaderboardRepository;

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
        $repo = new OptionLeaderboardRepository();
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
        $name = trim(sanitize_text_field($params['name'] ?? ''));
        $agency = trim(sanitize_text_field($params['agency'] ?? ''));
        $age = isset($params['age']) ? intval($params['age']) : null;
        $score = isset($params['score']) ? intval($params['score']) : 0;
        $played_at = sanitize_text_field($params['played_at'] ?? '');
        if ($name === '' || $agency === '') {
            return new \WP_Error('missing_fields', 'Name and Agency/School are required.', ['status' => 400]);
        }
        $repo = new OptionLeaderboardRepository();
        $saved = $repo->add($game, [
            'name' => $name,
            'agency' => $agency,
            'age' => $age,
            'score' => $score,
            'played_at' => $played_at,
        ]);
        // Return updated top 10
        $top = $repo->top($game, 10);
        return rest_ensure_response(['saved' => $saved, 'leaderboard' => $top]);
    }
}
