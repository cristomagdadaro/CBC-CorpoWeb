<?php if (!defined('ABSPATH')) { exit; } ?>
<div id="cbc-memory-app" class="game-container w-full h-full mx-auto relative p-10 bg-white rounded-xl shadow">
    <button id="memory-fullscreen-btn" type="button" aria-label="Toggle fullscreen" class="btn-secondary absolute top-0 right-0 px-3 py-1 rounded">
        <span>
            Fullscreen
        </span>
    </button>

    <div id="start-screen">
        <h1 class="text-3xl font-bold text-green-800 mb-2">Crop Biotech Memory</h1>
        <p class="text-green-600 mb-6">Match 8 pairs of images in 60 seconds to win!</p>
        <button id="start-btn" class="btn-primary text-white font-bold p-3 rounded-lg text-lg mx-auto">Start Game</button>
    </div>

    <div id="game-screen" class="hidden relative">
        <div class="flex justify-between items-center mb-4 text-green-700">
            <div id="timer" class="text-xl font-semibold">Time: 60s</div>
            <div id="matches" class="text-xl font-semibold">Matches: 0 / 8</div>
        </div>
        <div id="game-grid"></div>
        <p id="game-message" class="mt-4 text-lg font-semibold h-6"></p>
    </div>

    <div id="end-screen" class="hidden text-center">
        <h2 id="end-message" class="text-3xl font-bold text-green-800 mb-4"></h2>
        <p id="final-stats" class="text-lg text-green-700 mb-6"></p>
        <button id="restart-btn" class="btn-primary text-white font-bold p-3 rounded-lg text-lg">Play Again</button>
    </div>
</div>
