<?php
namespace CBCGames\Infrastructure\Repository\Memory;

use CBCGames\Domain\Memory\ImageRepository;

class PluginAssetImageRepository implements ImageRepository
{
    public function all()
    {
        $files = [
            'IMG_5854.png',
            'IMG_5855.png',
            'IMG_5856.png',
            'IMG_5857.png',
            'IMG_5858.png',
            'IMG_5859.png',
            'IMG_5861.png',
            'IMG_5862.png',
            'IMG_5863.png',
            'IMG_5864.png',
            'IMG_5865.png',
            'IMG_5866.png'
        ];
        $base = rtrim(CBC_GAMES_URL, '/');
        $base .= '/assets/images/memory/';
        $out = [];
        foreach ($files as $f) {
            $out[] = $base . $f;
        }
        return $out;
    }
}
