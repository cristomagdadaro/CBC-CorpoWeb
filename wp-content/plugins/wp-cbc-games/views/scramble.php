<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>
<div id="cbc-scramble-app"
     class="game-container relative p-10 bg-white rounded-xl shadow w-full mx-auto text-center flex flex-col justify-center">
    <div class="game-controls flex gap-4 items-center absolute top-0 left-0 p-2">
        <div class="control-group flex items-center gap-2">
            <label for="cbc-scramble-master-volume">Volume</label>
            <input type="range" id="cbc-scramble-master-volume" min="0" max="1" step="0.1" value="0.5" title="Master Volume">
        </div>
        <div class="control-group flex items-center">
            <input type="checkbox" id="cbc-scramble-mute-bg-music" class="form-checkbox !m-0" title="Mute background music">
            <label for="cbc-scramble-mute-bg-music">Mute Music</label>
        </div>
        <div class="control-group flex items-center">
            <input type="checkbox" id="cbc-scramble-mute-sfx" class="form-checkbox !m-0" title="Mute sound effects">
            <label for="cbc-scramble-mute-sfx">Mute SFX</label>
        </div>
    </div>
    <button id="scramble-fullscreen-btn" type="button" aria-label="Toggle fullscreen"
            class="btn-secondary absolute top-0 right-0 px-3 py-1 rounded">
        <span>
            Fullscreen
        </span>
    </button>

    <div id="start-screen">
        <?php echo govph_section_header( 'Biotech Scramble', [ 'id' => 'scramble_game_header' ] ); ?>
        <p class="text-green-600 mb-6">Unscramble the letters to reveal key terms. You have 10 seconds per word!</p>
        <button id="start-btn" class="btn-primary font-bold p-3 rounded-lg text-lg">Start Game</button>
    </div>

    <div id="game-screen" class="hidden flex flex-col gap-5">
        <div class="flex justify-between items-center mb-4 text-green-700">
            <div id="word-info" class="text-lg font-semibold">Word 1/5</div>
            <div id="timer" class="text-xl font-bold">10s</div>
        </div>
        <?php echo govph_section_header( 'Popular Posts', [ 'id' => 'scrambled-word' ] ); ?>
        <label for="guess-input" id="guess-input-label" class="sr-only">Your guess</label>
        <input type="text" aria-labelledby="guess-input-label" aria-label="Your guess" id="guess-input"
               placeholder="Your guess here..."
               class="w-full p-3 rounded-lg border-2 border-green-300 text-center text-xl tracking-wider mb-4 transition-colors"/>
        <div class="flex justify-center gap-3">
            <button id="submit-btn" class="btn-primary font-bold p-3 rounded-lg text-lg">Submit</button>
            <button id="skip-btn" class="btn-secondary font-bold p-3 rounded-lg text-lg">Skip</button>
        </div>
        <p id="feedback-message" class="mt-4 text-lg font-semibold h-6"></p>
    </div>

    <div id="end-screen" class="hidden">
        <h2 id="end-message" class="text-3xl font-bold text-green-800 mb-4"></h2>
        <p id="final-score" class="text-xl text-green-700 mb-6">Your final score: 0 / 5</p>
        <button id="restart-btn" class="btn-primary font-bold p-3 rounded-lg text-lg">Play Again</button>
        <div class="mt-4">
            <h3>Submit your Score!</h3>
            <form id="scramble-leaderboard-form" class="flex gap-3 items-center">
                <input type="text" id="scramble-hp" name="hp" class="cbc-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
                <div>
                    <label for="scramble-name">Name <span class="text-red-500">*</span></label>
                    <input id="scramble-name" name="name" type="text" required />
                </div>
                <div>
                    <label for="scramble-agency">Agency/School <span class="text-red-500">*</span></label>
                    <input id="scramble-agency" name="agency" type="text" required />
                </div>
                <div>
                    <label for="scramble-age">Age</label>
                    <input id="scramble-age" name="age" type="number" min="1" max="120" />
                </div>
                <div>
                    <label for="scramble-played-at">Date</label>
                    <input id="scramble-played-at" name="played_at" type="date" />
                </div>
                <div>
                    <button type="submit" class="btn-primary p-3 rounded-md">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<audio id="cbc-scramble-bg-music" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/bg-music.ogg' ); ?>" loop preload="auto"></audio>
<audio id="cbc-scramble-correct-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/correct.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-scramble-wrong-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/wrong.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-scramble-win-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/won.wav' ); ?>" preload="auto"></audio>
<audio id="cbc-scramble-lose-sound" src="<?php echo esc_url( CBC_GAMES_URL . 'assets/sounds/lose.wav' ); ?>" preload="auto"></audio>

<div id="cbc-scramble-leaderboard" class="cbc-leaderboard">
    <h3>Leaderboard (Top 10)</h3>

    <div class="leaderboard-list">
        <table aria-label="Scramble leaderboard">
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
            <tbody id="scramble-leaderboard-body">
            <tr>
                <td colspan="7" class="empty">Loading…</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>