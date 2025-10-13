<?php
namespace CBCGames\Domain\Memory;

interface ImageRepository
{
    /**
     * @return string[] List of image URLs or paths
     */
    public function all();
}
