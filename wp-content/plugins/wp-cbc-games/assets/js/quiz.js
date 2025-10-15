(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('cbc-quiz-app');
        if (!root || !window.cbcGames) return;

        const startScreen = root.querySelector('#start-screen');
        const quizScreen = root.querySelector('#quiz-screen');
        const endScreen = root.querySelector('#end-screen');
        const startBtn = root.querySelector('#start-btn');
        const restartBtn = root.querySelector('#restart-btn');
        const questionText = root.querySelector('#question-text');
        const optionsContainer = root.querySelector('#options-container');
        const progressText = root.querySelector('#progress');
        const scoreText = root.querySelector('#score');
        const finalScoreText = root.querySelector('#final-score');
        const resultMessage = root.querySelector('#result-message');
        const feedbackText = root.querySelector('#feedback');
        const leaderboardForm = document.getElementById('quiz-leaderboard-form');
        const leaderboardBody = document.getElementById('quiz-leaderboard-body');

        // Audio controls
        const masterVolume = document.getElementById('cbc-quiz-master-volume');
        const muteMusic = document.getElementById('cbc-quiz-mute-bg-music');
        const muteSfx = document.getElementById('cbc-quiz-mute-sfx');

        // Sounds
        const bgMusic = document.getElementById('cbc-quiz-bg-music');
        const correctSound = document.getElementById('cbc-quiz-correct-sound');
        const wrongSound = document.getElementById('cbc-quiz-wrong-sound');
        const winSound = document.getElementById('cbc-quiz-win-sound');
        const loseSound = document.getElementById('cbc-quiz-lose-sound');

        const sfx = [correctSound, wrongSound, winSound, loseSound];

        function applyVolume() {
            const master = parseFloat(masterVolume.value) || 0.1;
            if (bgMusic) {
                bgMusic.volume = master;
                bgMusic.muted = muteMusic.checked;
            }
            sfx.forEach(s => {
                if (s) {
                    s.volume = master;
                    s.muted = muteSfx.checked;
                }
            });
        }

        masterVolume.addEventListener('input', applyVolume);
        muteMusic.addEventListener('change', applyVolume);
        muteSfx.addEventListener('change', applyVolume);

        // Fullscreen toggle
        const fsBtn = root.querySelector('#quiz-fullscreen-btn');
        const d = document;
        const requestFS = (el) => (el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen || el.mozRequestFullScreen)?.call(el);
        const exitFS = () => (d.exitFullscreen || d.webkitExitFullscreen || d.msExitFullscreen || d.mozCancelFullScreen)?.call(d);
        const fsElement = () => d.fullscreenElement || d.webkitFullscreenElement || d.msFullscreenElement || d.mozFullScreenElement;

        function updateFsLabel() {
            if (!fsBtn) return;
            fsBtn.textContent = fsElement() ? 'Exit Fullscreen' : 'Fullscreen';
        }

        if (fsBtn) {
            if (!('fullscreenEnabled' in d) && !('webkitFullscreenEnabled' in d)) {
                fsBtn.style.display = 'none';
            } else {
                fsBtn.addEventListener('click', function () {
                    fsElement() ? exitFS() : requestFS(root);
                });
                d.addEventListener('fullscreenchange', updateFsLabel);
                d.addEventListener('webkitfullscreenchange', updateFsLabel);
                updateFsLabel();
            }
        }

        let currentQuestions = [];
        let questionIndex = 0;
        let score = 0;
        let quizStartAt = 0; // performance.now()
        let quizEndMs = null; // number|null

        function fmtTime(ms) {
            if (ms == null) return '';
            const s = Math.floor(ms / 1000);
            const m = Math.floor(s / 60);
            const sec = String(s % 60).padStart(2, '0');
            return `${m}:${sec}`;
        }

        function today() {
            const d = new Date();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${m}-${day}`;
        }

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function renderLeaderboard(rows) {
            if (!leaderboardBody) return;
            leaderboardBody.innerHTML = '';
            if (!Array.isArray(rows) || rows.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 7;
                td.className = 'empty';
                td.textContent = 'No scores yet.';
                tr.appendChild(td);
                leaderboardBody.appendChild(tr);
                return;
            }
            rows.slice(0, 10).forEach((r, i) => {
                const tr = document.createElement('tr');
                const cells = [
                    String(i + 1), r.name || '', r.agency || '', (r.age ?? '') + '', String(r.score ?? 0), fmtTime(r.time_ms ?? null), r.played_at || ''
                ];
                cells.forEach(txt => {
                    const td = document.createElement('td');
                    td.textContent = txt;
                    tr.appendChild(td);
                });
                leaderboardBody.appendChild(tr);
            });
        }

        function loadLeaderboard() {
            fetch((window.cbcGames.apiBase || '') + 'leaderboard/quiz?limit=10')
                .then(r => r.json()).then(data => renderLeaderboard(data.leaderboard || []))
                .catch(() => { /* keep existing content */
                });
        }

        function submitLeaderboard(evt) {
            evt.preventDefault();
            if (!leaderboardForm) return;
            const name = (document.getElementById('quiz-name')?.value || '').trim();
            const agency = (document.getElementById('quiz-agency')?.value || '').trim();
            const ageVal = document.getElementById('quiz-age')?.value;
            const age = ageVal ? parseInt(ageVal, 10) : null;
            let playedAt = (document.getElementById('quiz-played-at')?.value || '').trim();
            if (!playedAt) playedAt = today();
            const hp = (document.getElementById('quiz-hp')?.value || '').trim();
            if (!name || !agency) {
                alert('Please enter your Name and Agency/School.');
                return;
            }
            const time_ms = quizEndMs ?? Math.round(performance.now() - (quizStartAt || performance.now()));
            fetch((window.cbcGames.apiBase || '') + 'leaderboard/quiz', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CBC-Nonce': (window.cbcGames?.nonce || '')},
                body: JSON.stringify({name, agency, age, score, time_ms, played_at: playedAt, hp})
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data?.message || 'Failed to submit');
                    return data;
                })
                .then(data => {
                    renderLeaderboard(data.leaderboard || []);
                    leaderboardForm.reset();
                    document.getElementById('quiz-played-at').value = today();
                })
                .catch(err => {
                    alert(err.message || 'Failed to submit score');
                });
        }

        function startGame() {
            // Reset
            questionIndex = 0;
            score = 0;
            currentQuestions = [];
            quizEndMs = null;
            quizStartAt = performance.now();
            scoreText.textContent = 'Score: 0';
            feedbackText.textContent = '';
            optionsContainer.innerHTML = '';
            progressText.textContent = '';

            bgMusic?.play();

            // Show quiz screen first so loading state is visible
            startScreen.classList.add('hidden');
            endScreen.classList.add('hidden');
            quizScreen.classList.remove('hidden');
            questionText.textContent = 'Loading questions…';

            fetch((window.cbcGames.apiBase || '') + 'quiz')
                .then(r => r.json())
                .then(data => {
                    currentQuestions = Array.isArray(data.questions) ? data.questions : [];
                    // Safety shuffle
                    shuffleArray(currentQuestions);
                    currentQuestions = currentQuestions.slice(0, 10);

                    if (currentQuestions.length === 0) {
                        questionText.textContent = '';
                        optionsContainer.innerHTML = '<p style="color:#DC2626;">No questions available.</p>';
                        return;
                    }
                    showQuestion();
                })
                .catch(() => {
                    questionText.textContent = '';
                    optionsContainer.innerHTML = '<p style="color:#DC2626;">Failed to load questions. Please refresh.</p>';
                });
        }

        function showQuestion() {
            feedbackText.textContent = '';
            optionsContainer.innerHTML = '';

            if (questionIndex >= currentQuestions.length) {
                endGame();
                return;
            }

            progressText.textContent = `Question ${questionIndex + 1}/${currentQuestions.length}`;
            const q = currentQuestions[questionIndex];
            questionText.textContent = q.question;
            const shuffledOpts = shuffleArray([...(q.options || [])]);
            shuffledOpts.forEach(option => {
                const btn = document.createElement('button');
                btn.textContent = option;
                btn.className = 'btn-option w-full p-3 rounded-lg border-2 text-left transition-all duration-200';
                btn.addEventListener('click', () => selectAnswer(btn, option, q.answer));
                optionsContainer.appendChild(btn);
            });
        }

        function selectAnswer(button, selected, correct) {
            const buttons = optionsContainer.querySelectorAll('button');
            buttons.forEach(b => b.disabled = true);

            if (selected === correct) {
                score++;
                scoreText.textContent = `Score: ${score}`;
                button.classList.add('correct');
                feedbackText.textContent = 'Correct!';
                feedbackText.style.color = '#16A34A';
                correctSound?.play();
            } else {
                button.classList.add('incorrect');
                feedbackText.textContent = `Wrong! The answer is ${correct}`;
                feedbackText.style.color = '#DC2626';
                wrongSound?.play();
                buttons.forEach(b => {
                    if (b.textContent === correct) b.classList.add('correct');
                });
            }

            questionIndex++;
            setTimeout(showQuestion, 1500);
        }

        function endGame() {
            bgMusic?.pause();
            if (bgMusic) {
                bgMusic.currentTime = 0;
            }
            quizScreen.classList.add('hidden');
            endScreen.classList.remove('hidden');
            finalScoreText.textContent = `${score} / ${currentQuestions.length}`;
            quizEndMs = Math.round(performance.now() - (quizStartAt || performance.now()));
            if (score > 7) {
                resultMessage.textContent = 'Congratulations! You win a prize! Your knowledge is outstanding!';
                resultMessage.style.color = '#16A34A';
                winSound?.play();
            } else {
                resultMessage.textContent = 'Great effort! Keep learning and try again to win the prize.';
                resultMessage.style.color = '#525252';
                loseSound?.play();
            }
        }

        // Prefill date and load leaderboard on ready
        document.getElementById('quiz-played-at').value = today();
        loadLeaderboard();
        applyVolume();

        startBtn.addEventListener('click', startGame);
        restartBtn.addEventListener('click', startGame);
        leaderboardForm.addEventListener('submit', submitLeaderboard);
    });
})();
