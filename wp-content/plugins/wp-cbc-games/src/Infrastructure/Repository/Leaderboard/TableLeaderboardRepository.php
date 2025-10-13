<?php
namespace CBCGames\Infrastructure\Repository\Leaderboard;

if (!defined('ABSPATH')) { exit; }

class TableLeaderboardRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'cbc_games_leaderboard';
    }

    public function add(string $game, array $entry): array
    {
        global $wpdb;
        $game = sanitize_key($game);
        $name = mb_substr(sanitize_text_field($entry['name'] ?? ''), 0, 100);
        $agency = mb_substr(sanitize_text_field($entry['agency'] ?? ''), 0, 150);
        $age = isset($entry['age']) ? intval($entry['age']) : null;
        $score = isset($entry['score']) ? intval($entry['score']) : 0;
        $time_ms = isset($entry['time_ms']) ? max(0, intval($entry['time_ms'])) : null;
        $played_at = sanitize_text_field($entry['played_at'] ?? '');
        if (!$played_at || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $played_at)) {
            $played_at = current_time('Y-m-d');
        }
        $wpdb->insert(
            $this->table,
            [
                'game' => $game,
                'name' => $name,
                'agency' => $agency,
                'age' => $age,
                'score' => $score,
                'time_ms' => $time_ms,
                'played_at' => $played_at,
                'created_at' => current_time('mysql')
            ],
            ['%s','%s','%s','%d','%d','%d','%s','%s']
        );
        $id = $wpdb->insert_id;
        return [
            'id' => $id,
            'game' => $game,
            'name' => $name,
            'agency' => $agency,
            'age' => $age,
            'score' => $score,
            'time_ms' => $time_ms,
            'played_at' => $played_at,
            'created_at' => current_time('mysql'),
        ];
    }

    public function top(string $game, int $limit = 10): array
    {
        global $wpdb;
        $game = sanitize_key($game);
        $limit = max(1, min(100, intval($limit)));
        $sql = $wpdb->prepare(
            "SELECT name, agency, age, score, time_ms, played_at FROM {$this->table} WHERE game=%s ORDER BY score DESC, COALESCE(time_ms, 2147483647) ASC, created_at DESC LIMIT %d",
            $game,
            $limit
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if (!is_array($rows)) { $rows = []; }
        return $rows;
    }
}

