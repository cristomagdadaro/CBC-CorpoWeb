<?php
namespace CBCGames\Domain\Scramble;

interface WordRepository
{
    /**
     * @return Word[]
     */
    public function all();
}
