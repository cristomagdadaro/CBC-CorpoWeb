<?php
namespace CBCGames\Application\Quiz;

use CBCGames\Domain\Quiz\QuestionRepository;

class QuizService
{
    private $repo;

    public function __construct(QuestionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function tenRandom()
    {
        $all = $this->repo->all();
        shuffle($all);
        return array_slice($all, 0, 10);
    }
}
