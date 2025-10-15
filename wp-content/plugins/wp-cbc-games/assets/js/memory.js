(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('cbc-memory-app');
        if (!root || !window.cbcGames) return;

        const startScreen = root.querySelector('#start-screen');
        const gameScreen = root.querySelector('#game-screen');
        const endScreen = root.querySelector('#end-screen');
        const startBtn = root.querySelector('#start-btn');
        const restartBtn = root.querySelector('#restart-btn');
        const gameGrid = root.querySelector('#game-grid');
        const timerDisplay = root.querySelector('#timer');
        const matchesDisplay = root.querySelector('#matches');
        const gameMessage = root.querySelector('#game-message');
        const endMessage = root.querySelector('#end-message');
        const finalStats = root.querySelector('#final-stats');
        const leaderboardForm = document.getElementById('memory-leaderboard-form');
        const leaderboardBody = document.getElementById('memory-leaderboard-body');

        // Audio controls
        const masterVolume = document.getElementById('cbc-memory-master-volume');
        const muteMusic = document.getElementById('cbc-memory-mute-bg-music');
        const muteSfx = document.getElementById('cbc-memory-mute-sfx');

        // Sounds
        const bgMusic = document.getElementById('cbc-memory-bg-music');
        const correctSound = document.getElementById('cbc-memory-correct-sound');
        const winSound = document.getElementById('cbc-memory-win-sound');
        const loseSound = document.getElementById('cbc-memory-lose-sound');
        const flipSound = document.getElementById('cbc-memory-flip-sound');

        const sfx = [correctSound, winSound, loseSound, flipSound];

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
        const fsBtn = root.querySelector('#memory-fullscreen-btn');
        const d = document;
        const requestFS = (el) => (el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen || el.mozRequestFullScreen)?.call(el);
        const exitFS = () => (d.exitFullscreen || d.webkitExitFullscreen || d.msExitFullscreen || d.mozCancelFullScreen)?.call(d);
        const fsElement = () => d.fullscreenElement || d.webkitFullscreenElement || d.msFullscreenElement || d.mozFullScreenElement;

        function updateFsLabel() {
            if (!fsBtn) return;
            fsBtn.textContent = fsElement() ? 'Exit Fullscreen' : 'Fullscreen';
        }

        // Calculate card size so all rows fit in fullscreen
        function getColumnCount() {
            const cs = getComputedStyle(gameGrid);
            const tmpl = cs.gridTemplateColumns || '';
            const match = tmpl.match(/repeat\((\d+)/);
            if (match) return parseInt(match[1], 10) || 4;
            // Fallback: count columns by splitting resolved values
            const parts = tmpl.split(' ').filter(Boolean);
            return parts.length || 4;
        }

        function adjustGridCardSize() {
            // Only apply when this root is the fullscreen element
            const isFs = fsElement && fsElement() === root;
            if (!isFs) {
                root.style.removeProperty('--cbc-card-size');
                return;
            }
            // Ensure cards exist
            const total = gameGrid.querySelectorAll('.card').length;
            if (!total) {
                return;
            }
            const cols = Math.max(1, getColumnCount());
            const rows = Math.ceil(total / cols);
            const rootRect = root.getBoundingClientRect();
            const gridRectTop = gameGrid.getBoundingClientRect().top - root.getBoundingClientRect().top;
            const rootCS = getComputedStyle(root);
            const bottomPad = parseFloat(rootCS.paddingBottom) || 0;
            let available = rootRect.height - gridRectTop - bottomPad;
            const gridCS = getComputedStyle(gameGrid);
            const rowGap = parseFloat(gridCS.rowGap) || 0;
            const size = Math.floor((available - rowGap * Math.max(0, rows - 1)) / rows);
            if (isFinite(size) && size > 20) {
                root.style.setProperty('--cbc-card-size', size + 'px');
            }
        }

        if (fsBtn) {
            if (!('fullscreenEnabled' in d) && !('webkitFullscreenEnabled' in d)) {
                fsBtn.style.display = 'none';
            } else {
                fsBtn.addEventListener('click', function () {
                    fsElement() ? exitFS() : requestFS(root);
                });
                d.addEventListener('fullscreenchange', function () {
                    updateFsLabel();
                    adjustGridCardSize();
                });
                d.addEventListener('webkitfullscreenchange', function () {
                    updateFsLabel();
                    adjustGridCardSize();
                });
                window.addEventListener('resize', adjustGridCardSize);
                updateFsLabel();
            }
        }

        let cards = [];
        let flippedCards = [];
        let matchedPairs = 0;
        let timer = 60;
        let countdown;
        let isProcessing = false;
        let memoryStartAt = 0; // performance.now()
        let memoryEndMs = null; // number|null

        function fmtTime(ms) {
            if (ms == null) return '';
            const s = Math.floor(ms / 1000);
            const m = Math.floor(s / 60);
            const sec = String(s % 60).padStart(2, '0');
            return `${m}:${sec}`;
        }

        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function createBoard(images) {
            gameGrid.innerHTML = '';
            const unique = images.slice(0, 8); // 8 pairs
            cards = shuffle([...unique, ...unique]);
            cards.forEach((image, index) => {
                const card = document.createElement('div');
                card.className = 'card';
                card.dataset.id = index;
                card.dataset.image = image;

                const inner = document.createElement('div');
                inner.className = 'card-inner';

                const front = document.createElement('div');
                front.className = 'card-face card-front';
                front.textContent = '?';

                const back = document.createElement('div');
                back.className = 'card-face card-back';
                const img = document.createElement('img');
                img.src = image;
                img.alt = 'Memory image';
                back.appendChild(img);

                inner.appendChild(front);
                inner.appendChild(back);
                card.appendChild(inner);
                card.addEventListener('click', flipCard);
                gameGrid.appendChild(card);
            });
            // After rendering the board, adjust size if fullscreen
            setTimeout(adjustGridCardSize, 0);
        }

        function startTimer() {
            clearInterval(countdown);
            timer = 60;
            timerDisplay.textContent = `Time: ${timer}s`;
            countdown = setInterval(() => {
                timer--;
                timerDisplay.textContent = `Time: ${timer}s`;
                if (timer <= 0) {
                    clearInterval(countdown);
                    if (matchedPairs < 8) {
                        endGame('lose');
                    }
                }
            }, 1000);
        }

        function flipCard() {
            if (isProcessing || flippedCards.length >= 2 || this.classList.contains('flipped') || this.classList.contains('matched')) return;
            this.classList.add('flipped');
            flippedCards.push(this);
            flipSound?.play();
            if (flippedCards.length === 2) {
                isProcessing = true;
                setTimeout(checkMatch, 600);
            }
        }

        function checkMatch() {
            const [c1, c2] = flippedCards;
            if (!c1 || !c2) {
                isProcessing = false;
                flippedCards = [];
                return;
            }
            const i1 = c1.dataset.image, i2 = c2.dataset.image;
            if (i1 === i2) {
                gameMessage.textContent = 'Match found!';
                // Update the front faces to show a check mark when returned to front
                const f1 = c1.querySelector('.card-front');
                const f2 = c2.querySelector('.card-front');
                if (f1) f1.textContent = '✔';
                if (f2) f2.textContent = '✔';
                c1.classList.add('matched');
                c2.classList.add('matched');
                matchedPairs++;
                matchesDisplay.textContent = `Matches: ${matchedPairs} / 8`;
                correctSound?.play();
                if (matchedPairs === 8) {
                    endGame('win');
                }
            } else {
                gameMessage.textContent = 'No match, try again!';
                c1.classList.remove('flipped');
                c2.classList.remove('flipped');
            }
            flippedCards = [];
            isProcessing = false;
        }

        function startGame() {
            bgMusic?.play();
            matchedPairs = 0;
            flippedCards = [];
            isProcessing = false;
            gameMessage.textContent = '';
            matchesDisplay.textContent = 'Matches: 0 / 8';
            memoryEndMs = null;
            memoryStartAt = performance.now();
            // show game screen first so loading state is visible
            startScreen.classList.add('hidden');
            endScreen.classList.add('hidden');
            gameScreen.classList.remove('hidden');
            // loading message
            gameGrid.innerHTML = '<p class="cbc-loading">Loading images…</p>';

            fetch((window.cbcGames.apiBase || '') + 'memory')
                .then(r => r.json())
                .then(data => {
                    const images = Array.isArray(data.images) ? data.images : [];
                    if (images.length < 8) {
                        gameGrid.innerHTML = '<p style="color:#DC2626;">Not enough images to play.</p>';
                        return;
                    }
                    createBoard(shuffle(images));
                    startTimer();
                })
                .catch(() => {
                    gameGrid.innerHTML = '<p style="color:#DC2626;">Failed to load images. Please refresh.</p>';
                });
        }

        function endGame(result) {
            bgMusic?.pause();
            if (bgMusic) {
                bgMusic.currentTime = 0;
            }
            clearInterval(countdown);
            memoryEndMs = Math.round(performance.now() - (memoryStartAt || performance.now()));
            // Clear fullscreen card sizing when leaving game screen
            root.style.removeProperty('--cbc-card-size');
            gameScreen.classList.add('hidden');
            endScreen.classList.remove('hidden');
            if (result === 'win') {
                endMessage.textContent = 'You Win!';
                endMessage.style.color = '#16A34A';
                finalStats.textContent = 'You matched all pairs in time!';
                winSound?.play();
            } else {
                endMessage.textContent = 'Game Over';
                endMessage.style.color = '#DC2626';
                finalStats.textContent = `Time ran out. You found ${matchedPairs} out of 8 matches.`;
                loseSound?.play();
            }
        }

        const today = () => {
            const d = new Date();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${m}-${day}`;
        };

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
                const cells = [String(i + 1), r.name || '', r.agency || '', (r.age ?? '') + '', String(r.score ?? 0), fmtTime(r.time_ms ?? null), r.played_at || ''];
                cells.forEach(txt => {
                    const td = document.createElement('td');
                    td.textContent = txt;
                    tr.appendChild(td);
                });
                leaderboardBody.appendChild(tr);
            });
        }

        function loadLeaderboard() {
            fetch((window.cbcGames.apiBase || '') + 'leaderboard/memory?limit=10')
                .then(r => r.json()).then(data => renderLeaderboard(data.leaderboard || []))
                .catch(() => { /* keep */
                });
        }

        function submitLeaderboard(evt) {
            evt.preventDefault();
            if (!leaderboardForm) return;
            const name = (document.getElementById('memory-name')?.value || '').trim();
            const agency = (document.getElementById('memory-agency')?.value || '').trim();
            const ageVal = document.getElementById('memory-age')?.value;
            const age = ageVal ? parseInt(ageVal, 10) : null;
            let playedAt = (document.getElementById('memory-played-at')?.value || '').trim();
            if (!playedAt) playedAt = today();
            const hp = (document.getElementById('memory-hp')?.value || '').trim();
            if (!name || !agency) {
                alert('Please enter your Name and Agency/School.');
                return;
            }
            // Use matchedPairs as score at time of submission
            const scoreToSubmit = matchedPairs;
            const time_ms = memoryEndMs ?? Math.round(performance.now() - (memoryStartAt || performance.now()));
            fetch((window.cbcGames.apiBase || '') + 'leaderboard/memory', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CBC-Nonce': (window.cbcGames?.nonce || '')},
                body: JSON.stringify({name, agency, age, score: scoreToSubmit, time_ms, played_at: playedAt, hp})
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data?.message || 'Failed to submit');
                    return data;
                })
                .then(data => {
                    renderLeaderboard(data.leaderboard || []);
                    leaderboardForm.reset();
                    document.getElementById('memory-played-at').value = today();
                })
                .catch(err => {
                    alert(err.message || 'Failed to submit score');
                });
        }

        // Initialize leaderboard
        document.getElementById('memory-played-at')?.setAttribute('value', today());
        loadLeaderboard();
        leaderboardForm?.addEventListener('submit', submitLeaderboard);
        applyVolume();

        startBtn?.addEventListener('click', startGame);
        restartBtn?.addEventListener('click', startGame);
    });
})();
