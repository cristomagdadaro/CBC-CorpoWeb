<?php
namespace CBCGames\Infrastructure\Repository\Scramble;

use CBCGames\Domain\Scramble\Word;
use CBCGames\Domain\Scramble\WordRepository;

class InMemoryWordRepository implements WordRepository
{
    private $words;

    public function __construct()
    {
        $list = [
            'GENOME','PLASMID','CRISPR','PROTEIN','HYBRID','GOLDEN RICE','BT CORN','BIOFORTIFY','TRANSGENIC','HERBICIDE','GENE','BIOTECH','AGRICULTURE','SELECTION','DIVERSITY'
        ];
        $this->words = [];
        foreach ($list as $w) {
            $this->words[] = new Word($w);
        }
    }

    public function all()
    {
        return $this->words;
    }
}
