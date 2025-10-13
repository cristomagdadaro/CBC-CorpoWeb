<?php
namespace CBCGames\Domain\Quiz;

class Question
{
    private $question;
    private $options;
    private $answer;

    public function __construct($question, $options, $answer)
    {
        $this->question = $question;
        $this->options = $options;
        $this->answer = $answer;
    }

    public function text() { return $this->question; }
    public function options() { return $this->options; }
    public function answer() { return $this->answer; }
}
