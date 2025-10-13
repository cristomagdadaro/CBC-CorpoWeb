<?php if (!defined('ABSPATH')) { exit; } ?>
<div id="cbc-quiz-app" class="quiz-container w-full mx-auto rounded-xl shadow-lg p-10 md:p-8 text-center relative">
    <button id="quiz-fullscreen-btn" type="button" aria-label="Toggle fullscreen" class="btn-secondary absolute top-0 right-0 px-3 py-1 rounded">
        <span>
            Fullscreen
        </span>
    </button>

    <div id="start-screen">
        <h1 class="text-3xl md:text-4xl font-bold text-green-800 mb-2">DA-Crop Biotechnology Center</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-green-700 mb-4">Quiz Bee!</h2>
        <p class="text-green-600 mb-6">Test your knowledge about modern agriculture and the science behind our food. You'll face 10 questions.</p>
        <button id="start-btn" class="btn-primary font-bold p-3 rounded-lg text-lg transition-transform transform hover:scale-105">Start Quiz</button>
    </div>

    <div id="quiz-screen" class="hidden">
        <div class="flex justify-between items-center mb-4">
            <div id="progress" class="text-sm font-semibold text-green-700">Question 1/10</div>
            <div id="score" class="text-sm font-semibold text-green-700">Score: 0</div>
        </div>
        <div id="question-container" class="mb-6">
            <p id="question-text" class="text-xl md:text-2xl font-semibold text-gray-800"></p>
        </div>
        <div id="options-container" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
        <div id="feedback" class="mt-4 font-bold text-lg h-6"></div>
    </div>

    <div id="end-screen" class="hidden">
        <h2 class="text-3xl font-bold text-green-800 mb-4">Quiz Complete!</h2>
        <p class="text-2xl text-green-700 mb-4">Your Final Score:</p>
        <p id="final-score" class="text-5xl font-bold text-green-500 mb-6">0 / 10</p>
        <p id="result-message" class="text-xl font-semibold mb-6"></p>
        <button id="restart-btn" class="btn-primary font-bold p-3 rounded-lg text-lg transition-transform transform hover:scale-105">Play Again</button>
    </div>
</div>
