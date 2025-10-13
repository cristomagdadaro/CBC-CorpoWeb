<?php if (!defined('ABSPATH')) { exit; } ?>
<div id="cbc-quiz-app" class="quiz-container w-full mx-auto rounded-xl shadow-lg p-10 md:p-8 text-center relative flex flex-col justify-center">
    <button id="quiz-fullscreen-btn" type="button" aria-label="Toggle fullscreen" class="btn-secondary absolute top-0 right-0 px-3 py-1 rounded">
        <span>
            Fullscreen
        </span>
    </button>

    <div id="start-screen">
        <h2 class="text-2xl md:text-3xl font-bold text-green-700 mb-4">Crop Biotech Quiz Bee!</h2>
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

<div id="cbc-quiz-leaderboard" class="cbc-leaderboard">
  <h3>Leaderboard (Top 10)</h3>
  <form id="quiz-leaderboard-form">
    <input type="text" id="quiz-hp" name="hp" class="cbc-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
    <div>
      <label for="quiz-name">Name <span class="text-red-500">*</span></label>
      <input id="quiz-name" name="name" type="text" required />
    </div>
    <div>
      <label for="quiz-agency">Agency/School <span class="text-red-500">*</span></label>
      <input id="quiz-agency" name="agency" type="text" required />
    </div>
    <div>
      <label for="quiz-age">Age</label>
      <input id="quiz-age" name="age" type="number" min="1" max="120" />
    </div>
    <div>
      <label for="quiz-played-at">Date</label>
      <input id="quiz-played-at" name="played_at" type="date" />
    </div>
    <div>
      <button type="submit" class="btn-primary">Submit Score</button>
    </div>
  </form>
  <div class="leaderboard-list">
    <table aria-label="Quiz leaderboard">
      <thead>
        <tr><th>#</th><th>Name</th><th>Agency/School</th><th>Age</th><th>Score</th><th>Time</th><th>Date</th></tr>
      </thead>
      <tbody id="quiz-leaderboard-body"><tr><td colspan="7" class="empty">Loading…</td></tr></tbody>
    </table>
  </div>
</div>
