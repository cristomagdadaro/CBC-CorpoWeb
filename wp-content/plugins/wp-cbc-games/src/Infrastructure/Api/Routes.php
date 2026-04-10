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
            'permission_callback' => [$this, 'allowAuthenticatedRead'],
        ]);

        register_rest_route('cbc-games/v1', '/scramble', [
            'methods' => 'GET',
            'callback' => [$this, 'getScramble'],
            'permission_callback' => [$this, 'allowAuthenticatedRead'],
        ]);

        register_rest_route('cbc-games/v1', '/memory', [
            'methods' => 'GET',
            'callback' => [$this, 'getMemory'],
            'permission_callback' => [$this, 'allowAuthenticatedRead'],
        ]);

        // Leaderboard routes
        register_rest_route('cbc-games/v1', '/leaderboard/(?P<game>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getLeaderboard'],
            'permission_callback' => [$this, 'allowAuthenticatedRead'],
            'args' => [
                'game' => ['required' => true],
                'limit' => ['required' => false],
            ],
        ]);
        register_rest_route('cbc-games/v1', '/leaderboard/(?P<game>[a-zA-Z0-9_-]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'postLeaderboard'],
            'permission_callback' => [$this, 'allowLeaderboardWrite'],
            'args' => [ 'game' => ['required' => true] ],
        ]);
    }

    public function allowAuthenticatedRead($request)
    {
        if (!is_user_logged_in()) {
            return new \WP_Error('cbc_games_login_required', 'This game is available only to logged-in users.', ['status' => 401]);
        }

        return true;
    }

    public function allowLeaderboardWrite($request)
    {
        if (!is_user_logged_in()) {
            return new \WP_Error('cbc_games_login_required', 'This game is available only to logged-in users.', ['status' => 401]);
        }

        $game = sanitize_key($request['game'] ?? '');
        if (!in_array($game, $this->allowedGames, true)) {
            return new \WP_Error('invalid_game', 'Invalid game', ['status' => 400]);
        }

        $nonce = (string) $request->get_header('X-CBC-Nonce');
        if ($nonce === '' || !wp_verify_nonce($nonce, 'cbc_games_nonce')) {
            return new \WP_Error('invalid_nonce', 'Invalid gameplay nonce', ['status' => 403]);
        }

        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        $token = isset($params['submission_token']) ? sanitize_text_field((string) $params['submission_token']) : '';
        if (!$this->validateSubmissionToken($token, $game)) {
            return new \WP_Error('invalid_submission_token', 'Invalid or expired gameplay token.', ['status' => 403]);
        }

        return true;
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
        return rest_ensure_response([
            'questions' => $data,
            'submissionToken' => $this->issueSubmissionToken('quiz'),
        ]);
    }

    public function getScramble($request)
    {
        $service = new ScrambleService(new InMemoryWordRepository());
        $words = $service->pick(5);
        return rest_ensure_response([
            'words' => $words,
            'submissionToken' => $this->issueSubmissionToken('scramble'),
        ]);
    }

    public function getMemory($request)
    {
        $repo = new PluginAssetImageRepository();
        $images = $repo->all();
        return rest_ensure_response([
            'images' => $images,
            'submissionToken' => $this->issueSubmissionToken('memory'),
        ]);
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

        $rate = $this->checkLeaderboardRateLimit($game);
        if (is_wp_error($rate)) {
            return $rate;
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

    private function clientIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            $_SERVER['HTTP_CLIENT_IP'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            if (!$candidate) {
                continue;
            }

            foreach (array_map('trim', explode(',', (string) $candidate)) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    private function issueSubmissionToken(string $game): string
    {
        $payload = [
            'game' => $game,
            'exp' => time() + (15 * MINUTE_IN_SECONDS),
            'ip' => $this->clientIp(),
            'ua' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120),
        ];
        $encoded = rtrim(strtr(base64_encode(wp_json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $encoded, wp_salt('auth'));

        return $encoded . '.' . $signature;
    }

    private function validateSubmissionToken(string $token, string $game): bool
    {
        if ($token === '' || strpos($token, '.') === false) {
            return false;
        }

        [$encoded, $signature] = explode('.', $token, 2);
        $expected = hash_hmac('sha256', $encoded, wp_salt('auth'));
        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $normalized = strtr($encoded, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $payloadJson = base64_decode($normalized, true);
        $payload = json_decode((string) $payloadJson, true);
        if (!is_array($payload)) {
            return false;
        }

        if (($payload['game'] ?? '') !== $game) {
            return false;
        }

        if ((int) ($payload['exp'] ?? 0) < time()) {
            return false;
        }

        if (($payload['ip'] ?? '') !== $this->clientIp()) {
            return false;
        }

        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120);

        return hash_equals((string) ($payload['ua'] ?? ''), $ua);
    }

    private function checkLeaderboardRateLimit(string $game)
    {
        $ipHash = md5($this->clientIp() . '|' . $game);
        $minuteKey = 'cbc_games_lb_min_' . $ipHash;
        $hourKey = 'cbc_games_lb_hour_' . $ipHash;

        $minuteCount = (int) get_transient($minuteKey);
        $hourCount = (int) get_transient($hourKey);

        if ($minuteCount >= 3) {
            return new \WP_Error('rate_limited', 'Too many score submissions. Please wait a minute before trying again.', ['status' => 429]);
        }

        if ($hourCount >= 15) {
            return new \WP_Error('rate_limited_hour', 'Too many score submissions from this connection. Please try again later.', ['status' => 429]);
        }

        set_transient($minuteKey, $minuteCount + 1, MINUTE_IN_SECONDS);
        set_transient($hourKey, $hourCount + 1, HOUR_IN_SECONDS);

        return true;
    }
}
