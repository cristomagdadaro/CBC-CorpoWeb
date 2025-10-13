<?php
namespace CBCGames\Application\Scramble;

use CBCGames\Domain\Scramble\WordRepository;

class ScrambleService
{
    private $repo;

    public function __construct(WordRepository $repo)
    {
        $this->repo = $repo;
    }

    public function pick($count = 5)
    {
        $all = $this->repo->all();
        shuffle($all);
        $selected = array_slice($all, 0, $count);
        $out = [];
        foreach ($selected as $w) {
            $out[] = $w->value();
        }
        return $out;
    }
}
