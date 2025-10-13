<?php
namespace CBCGames\Infrastructure\Api;

use CBCGames\Application\Quiz\QuizService;
use CBCGames\Application\Scramble\ScrambleService;
use CBCGames\Infrastructure\Repository\Quiz\InMemoryQuestionRepository;
use CBCGames\Infrastructure\Repository\Scramble\InMemoryWordRepository;
use CBCGames\Infrastructure\Repository\Memory\PluginAssetImageRepository;

if (!defined('ABSPATH')) { exit; }

class Routes
{
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
}
