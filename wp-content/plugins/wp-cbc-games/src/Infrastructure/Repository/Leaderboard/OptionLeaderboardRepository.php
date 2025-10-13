<?php
namespace CBCGames\Infrastructure\Repository\Leaderboard;

if (!defined('ABSPATH')) { exit; }

class OptionLeaderboardRepository
{
    private const OPTION_KEY = 'cbc_games_leaderboard';
    private const MAX_PER_GAME = 100; // keep storage bounded

    private function getAll(): array
    {
        $data = get_option(self::OPTION_KEY, []);
        return is_array($data) ? $data : [];
    }

    private function saveAll(array $all): void
    {
        update_option(self::OPTION_KEY, $all, false);
    }

    public function add(string $game, array $entry): array
    {
        $game = sanitize_key($game);
        $all = $this->getAll();
        if (!isset($all[$game]) || !is_array($all[$game])) {
            $all[$game] = [];
        }
        // sanitize entry
        $name = sanitize_text_field($entry['name'] ?? '');
        $agency = sanitize_text_field($entry['agency'] ?? '');
        $age = isset($entry['age']) ? intval($entry['age']) : null;
        $score = isset($entry['score']) ? intval($entry['score']) : 0;
        $played_at = sanitize_text_field($entry['played_at'] ?? '');
        if (!$played_at) { $played_at = current_time('Y-m-d'); }

        $row = [
            'name' => mb_substr($name, 0, 100),
            'agency' => mb_substr($agency, 0, 150),
            'age' => $age,
            'score' => $score,
            'played_at' => $played_at,
            'created_at' => current_time('mysql'),
        ];

        $all[$game][] = $row;
        // sort desc by score, then by created_at desc
        usort($all[$game], function($a, $b){
            if (($b['score'] ?? 0) !== ($a['score'] ?? 0)) {
                return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            }
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });
        // trim
        if (count($all[$game]) > self::MAX_PER_GAME) {
            $all[$game] = array_slice($all[$game], 0, self::MAX_PER_GAME);
        }
        $this->saveAll($all);
        return $row;
    }

    public function top(string $game, int $limit = 10): array
    {
        $game = sanitize_key($game);
        $all = $this->getAll();
        $rows = isset($all[$game]) && is_array($all[$game]) ? $all[$game] : [];
        // already sorted on write, but ensure sort
        usort($rows, function($a, $b){
            if (($b['score'] ?? 0) !== ($a['score'] ?? 0)) {
                return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            }
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });
        return array_slice($rows, 0, max(1, $limit));
    }
}

