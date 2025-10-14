<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>
<div id="cbc-quiz-app"
     class="quiz-container w-full mx-auto rounded-xl shadow-lg p-10 md:p-8 text-center relative flex flex-col justify-center">
    <div class="game-controls flex gap-4 items-center absolute top-0 left-0 p-2">
        <div class="control-group flex items-center gap-2">
            <label for="cbc-quiz-master-volume">Volume</label>
            <input type="range" id="cbc-quiz-master-volume" min="0" max="1" step="0.1" value="0.5" title="Master Volume">
        </div>
        <div class="control-group flex items-center">
            <input type="checkbox" id="cbc-quiz-mute-bg-music" class="form-checkbox !m-0" title="Mute background music">
            <label for="cbc-quiz-mute-bg-music">Mute Music</label>
        </div>
        <div class="control-group flex items-center">
            <input type="checkbox" id="cbc-quiz-mute-sfx" class="form-checkbox !m-0" title="Mute sound effects">
            <label for="cbc-quiz-mute-sfx">Mute SFX</label>
        </div>
    </div>
    <button id="quiz-fullscreen-btn" type="button" aria-label="Toggle fullscreen"
            class="btn-secondary absolute top-0 right-0 px-3 py-1 rounded">
            <span>
                Fullscreen
            </span>
    </button>

    <div id="start-screen">
        <h2 class="text-2xl md:text-3xl font-bold text-green-700 mb-4">Crop Biotech Quiz Bee!</h2>
        <p class="text-green-600 mb-6">Test your knowledge about modern agriculture and the science behind our food.
            You'll face 10 questions.</p>
        <button id="start-btn"
                class="btn-primary font-bold p-3 rounded-lg text-lg transition-transform transform hover:scale-105">
            Start Quiz
        </button>
    </div>

    <div id="quiz-screen" class="hidden flex flex-col gap-5">
        <div class="quiz-game-header flex justify-between items-center mb-4">
            <div id="progress" class="text-sm font-semibold text-green-700">Question 1/10</div>
            <div id="score" class="text-sm font-semibold text-green-700">Score: 0</div>
        </div>
        <div id="question-container" class="mb-6 h-fit">
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
        <button id="restart-btn"  class="btn-primary font-bold p-3 rounded-lg text-lg transition-transform transform hover:scale-105">Play Again</button>

        <div class="mt-4">
            <h3>Submit your Score!</h3>
            <form id="quiz-leaderboard-form" class="flex gap-3 items-center">
                <label for="quiz-hp"></label><input type="text" id="quiz-hp" name="hp" class="cbc-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
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
                    <button type="submit" class="btn-primary p-3 rounded-md">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<audio id="cbc-quiz-bg-music" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/bg-music.ogg' ); ?>" loop preload="auto"></audio>
<audio id="cbc-quiz-correct-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/correct.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-quiz-wrong-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/wrong.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-quiz-win-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/won.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-quiz-lose-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/lose.wav' ); ?>" preload="auto"></audio>

<div id="cbc-quiz-leaderboard" class="cbc-leaderboard">
    <h3>Leaderboard (Top 10)</h3>

    <div class="leaderboard-list">
        <table aria-label="Quiz leaderboard">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Agency/School</th>
                <th>Age</th>
                <th>Score</th>
                <th>Time</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody id="quiz-leaderboard-body">
            <tr>
                <td colspan="7" class="empty">Loading…</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
