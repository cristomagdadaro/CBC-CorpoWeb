(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('cbc-scramble-app');
    if(!root || !window.cbcGames) return;

    const startScreen = root.querySelector('#start-screen');
    const gameScreen = root.querySelector('#game-screen');
    const endScreen = root.querySelector('#end-screen');
    const startBtn = root.querySelector('#start-btn');
    const restartBtn = root.querySelector('#restart-btn');
    const wordInfo = root.querySelector('#word-info');
    const timerDisplay = root.querySelector('#timer');
    const scrambledWordDisplay = root.querySelector('#scrambled-word');
    const guessInput = root.querySelector('#guess-input');
    const submitBtn = root.querySelector('#submit-btn');
    const skipBtn = root.querySelector('#skip-btn');
    const feedbackMessage = root.querySelector('#feedback-message');
    const endMessage = root.querySelector('#end-message');
    const finalScoreDisplay = root.querySelector('#final-score');
    const leaderboardForm = document.getElementById('scramble-leaderboard-form');
    const leaderboardBody = document.getElementById('scramble-leaderboard-body');

    // Fullscreen toggle
    const fsBtn = root.querySelector('#scramble-fullscreen-btn');
    const d = document;
    const requestFS = (el)=> (el.requestFullscreen||el.webkitRequestFullscreen||el.msRequestFullscreen||el.mozRequestFullScreen)?.call(el);
    const exitFS = ()=> (d.exitFullscreen||d.webkitExitFullscreen||d.msExitFullscreen||d.mozCancelFullScreen)?.call(d);
    const fsElement = ()=> d.fullscreenElement || d.webkitFullscreenElement || d.msFullscreenElement || d.mozFullScreenElement;
    function updateFsLabel(){ if(!fsBtn) return; fsBtn.textContent = fsElement()? 'Exit Fullscreen' : 'Fullscreen'; }
    if(fsBtn){
      if(!('fullscreenEnabled' in d) && !('webkitFullscreenEnabled' in d)){
        fsBtn.style.display = 'none';
      } else {
        fsBtn.addEventListener('click', function(){ fsElement()? exitFS() : requestFS(root); });
        d.addEventListener('fullscreenchange', updateFsLabel);
        d.addEventListener('webkitfullscreenchange', updateFsLabel);
        updateFsLabel();
      }
    }

    let currentWords = [];
    let wordIndex = 0; let score = 0; let currentCorrectWord=''; let countdownTimer;

    function today(){ const d=new Date(); const m=String(d.getMonth()+1).padStart(2,'0'); const day=String(d.getDate()).padStart(2,'0'); return `${d.getFullYear()}-${m}-${day}`; }

    function shuffleArray(array){
      for(let i=array.length-1;i>0;i--){
        const j = Math.floor(Math.random()*(i+1));
        [array[i], array[j]] = [array[j], array[i]];
      }
      return array;
    }

    function shuffleWord(word){
      const chars = word.split('');
      let shuffled = chars.sort(()=>Math.random()-0.5).join('');
      while(shuffled === word){
        shuffled = chars.sort(()=>Math.random()-0.5).join('');
      }
      return shuffled;
    }

    function scrambleWord(phrase){
      return phrase.split(' ').map(w=>shuffleWord(w)).join(' ').toUpperCase();
    }

    function startGame(){
      wordIndex=0; score=0; currentWords=[]; currentCorrectWord=''; clearInterval(countdownTimer);
      feedbackMessage.textContent='';

      // Show game screen first so loading message is visible
      startScreen.classList.add('hidden'); endScreen.classList.add('hidden'); gameScreen.classList.remove('hidden');
      wordInfo.textContent = '';
      scrambledWordDisplay.textContent = 'Loading words…';
      guessInput.value=''; guessInput.disabled = true;
      timerDisplay.textContent = '';

      // Load words from API
      fetch((window.cbcGames.apiBase||'') + 'scramble')
        .then(r=>r.json())
        .then(data=>{
          currentWords = Array.isArray(data.words) ? data.words : [];
          currentWords = currentWords.slice(0,5);
          if(currentWords.length === 0){
            scrambledWordDisplay.textContent = '';
            feedbackMessage.textContent = 'No words available.'; feedbackMessage.style.color = '#DC2626';
            return;
          }
          guessInput.disabled = false;
          loadNextWord();
        })
        .catch(()=>{
          scrambledWordDisplay.textContent = '';
          feedbackMessage.textContent = 'Failed to load words. Please refresh.'; feedbackMessage.style.color = '#DC2626';
        });
    }

    function loadNextWord(){
      if(wordIndex >= currentWords.length){ endGame(); return; }
      clearInterval(countdownTimer);
      guessInput.value=''; guessInput.disabled=false; feedbackMessage.textContent='';
      wordInfo.textContent = `Word ${wordIndex+1}/${currentWords.length}`;
      currentCorrectWord = currentWords[wordIndex];
      scrambledWordDisplay.textContent = scrambleWord(currentCorrectWord);
      startCountdown();
      guessInput.focus();
    }

    function startCountdown(){
      let timeLeft = 10; timerDisplay.textContent = `${timeLeft}s`;
      clearInterval(countdownTimer);
      countdownTimer = setInterval(()=>{
        timeLeft--; timerDisplay.textContent = `${timeLeft}s`;
        if(timeLeft<=0){
          clearInterval(countdownTimer);
          feedbackMessage.textContent = `Time's up! The word was: ${currentCorrectWord}`;
          feedbackMessage.style.color = '#DC2626';
          wordIndex++; setTimeout(loadNextWord, 1000);
        }
      }, 1000);
    }

    function checkGuess(){
      const guess = (guessInput.value||'').toUpperCase();
      const correct = (currentCorrectWord||'').toUpperCase();
      if(!guess) return;
      if(guess === correct){
        clearInterval(countdownTimer); score++;
        feedbackMessage.textContent = 'Correct!'; feedbackMessage.style.color = '#16A34A';
        guessInput.disabled = true; wordIndex++; setTimeout(loadNextWord, 800);
      } else {
        feedbackMessage.textContent = 'Incorrect, try again!'; feedbackMessage.style.color = '#DC2626';
      }
    }

    function skipWord(){
      clearInterval(countdownTimer);
      feedbackMessage.textContent = `Skipped. The word was: ${currentCorrectWord}`; feedbackMessage.style.color = '#4B5563';
      guessInput.disabled = true; wordIndex++; setTimeout(loadNextWord, 800);
    }

    function endGame(){
      clearInterval(countdownTimer);
      gameScreen.classList.add('hidden'); endScreen.classList.remove('hidden');
      endMessage.textContent = 'Game Over!'; finalScoreDisplay.textContent = `Your final score: ${score} / ${currentWords.length}`;
    }

    function renderLeaderboard(rows){
      if(!leaderboardBody) return;
      leaderboardBody.innerHTML = '';
      if(!Array.isArray(rows) || rows.length===0){
        const tr = document.createElement('tr');
        const td = document.createElement('td'); td.colSpan = 6; td.className='empty'; td.textContent='No scores yet.'; tr.appendChild(td);
        leaderboardBody.appendChild(tr); return;
      }
      rows.slice(0,10).forEach((r, i)=>{
        const tr = document.createElement('tr');
        const cells = [String(i+1), r.name||'', r.agency||'', (r.age??'')+'', String(r.score??0), r.played_at||''];
        cells.forEach(txt=>{ const td = document.createElement('td'); td.textContent = txt; tr.appendChild(td); });
        leaderboardBody.appendChild(tr);
      });
    }

    function loadLeaderboard(){
      fetch((window.cbcGames.apiBase||'') + 'leaderboard/scramble?limit=10')
        .then(r=>r.json()).then(data=> renderLeaderboard(data.leaderboard||[]))
        .catch(()=>{ /* keep */ });
    }

    function submitLeaderboard(evt){
      evt.preventDefault(); if(!leaderboardForm) return;
      const name = (document.getElementById('scramble-name')?.value||'').trim();
      const agency = (document.getElementById('scramble-agency')?.value||'').trim();
      const ageVal = document.getElementById('scramble-age')?.value; const age = ageVal? parseInt(ageVal,10) : null;
      let playedAt = (document.getElementById('scramble-played-at')?.value||'').trim(); if(!playedAt) playedAt = today();
      if(!name || !agency){ alert('Please enter your Name and Agency/School.'); return; }
      fetch((window.cbcGames.apiBase||'') + 'leaderboard/scramble', {
        method:'POST', headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ name, agency, age, score, played_at: playedAt })
      })
        .then(async r=>{ const data = await r.json(); if(!r.ok) throw new Error(data?.message||'Failed to submit'); return data; })
        .then(data=>{ renderLeaderboard(data.leaderboard||[]); leaderboardForm.reset(); document.getElementById('scramble-played-at').value = today(); })
        .catch(err=>{ alert(err.message||'Failed to submit score'); });
    }

    // Prefill date and load leaderboard on ready
    document.getElementById('scramble-played-at')?.setAttribute('value', today());
    loadLeaderboard();
    leaderboardForm?.addEventListener('submit', submitLeaderboard);

    startBtn?.addEventListener('click', startGame);
    restartBtn?.addEventListener('click', startGame);
    submitBtn?.addEventListener('click', checkGuess);
    skipBtn?.addEventListener('click', skipWord);
    guessInput?.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ checkGuess(); } });
  });
})();
