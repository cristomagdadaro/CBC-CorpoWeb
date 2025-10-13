<?php
namespace CBCGames\Domain\Quiz;

interface QuestionRepository
{
    /**
     * @return Question[]
     */
    public function all();
}
