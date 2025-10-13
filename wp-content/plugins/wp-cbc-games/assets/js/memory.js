(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('cbc-memory-app');
    if(!root || !window.cbcGames) return;

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

    // Fullscreen toggle
    const fsBtn = root.querySelector('#memory-fullscreen-btn');
    const d = document;
    const requestFS = (el)=> (el.requestFullscreen||el.webkitRequestFullscreen||el.msRequestFullscreen||el.mozRequestFullScreen)?.call(el);
    const exitFS = ()=> (d.exitFullscreen||d.webkitExitFullscreen||d.msExitFullscreen||d.mozCancelFullScreen)?.call(d);
    const fsElement = ()=> d.fullscreenElement || d.webkitFullscreenElement || d.msFullscreenElement || d.mozFullScreenElement;
    function updateFsLabel(){ if(!fsBtn) return; fsBtn.textContent = fsElement()? 'Exit Fullscreen' : 'Fullscreen'; }

    // Calculate card size so all rows fit in fullscreen
    function getColumnCount(){
      const cs = getComputedStyle(gameGrid);
      const tmpl = cs.gridTemplateColumns || '';
      const match = tmpl.match(/repeat\((\d+)/);
      if(match) return parseInt(match[1], 10) || 4;
      // Fallback: count columns by splitting resolved values
      const parts = tmpl.split(' ').filter(Boolean);
      return parts.length || 4;
    }
    function adjustGridCardSize(){
      // Only apply when this root is the fullscreen element
      const isFs = fsElement && fsElement() === root;
      if(!isFs){
        root.style.removeProperty('--cbc-card-size');
        return;
      }
      // Ensure cards exist
      const total = gameGrid.querySelectorAll('.card').length;
      if(!total){ return; }
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
      if(isFinite(size) && size > 20){
        root.style.setProperty('--cbc-card-size', size + 'px');
      }
    }

    if(fsBtn){
      if(!('fullscreenEnabled' in d) && !('webkitFullscreenEnabled' in d)){
        fsBtn.style.display = 'none';
      } else {
        fsBtn.addEventListener('click', function(){ fsElement()? exitFS() : requestFS(root); });
        d.addEventListener('fullscreenchange', function(){ updateFsLabel(); adjustGridCardSize(); });
        d.addEventListener('webkitfullscreenchange', function(){ updateFsLabel(); adjustGridCardSize(); });
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

    function shuffle(array){
      for(let i=array.length-1;i>0;i--){
        const j = Math.floor(Math.random()*(i+1));
        [array[i], array[j]] = [array[j], array[i]];
      }
      return array;
    }

    function createBoard(images){
      gameGrid.innerHTML='';
      const unique = images.slice(0,8); // 8 pairs
      cards = shuffle([...unique, ...unique]);
      cards.forEach((image, index)=>{
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

        inner.appendChild(front); inner.appendChild(back);
        card.appendChild(inner);
        card.addEventListener('click', flipCard);
        gameGrid.appendChild(card);
      });
      // After rendering the board, adjust size if fullscreen
      setTimeout(adjustGridCardSize, 0);
    }

    function startTimer(){
      clearInterval(countdown);
      timer = 60;
      timerDisplay.textContent = `Time: ${timer}s`;
      countdown = setInterval(()=>{
        timer--; timerDisplay.textContent = `Time: ${timer}s`;
        if(timer<=0){
          clearInterval(countdown);
          if(matchedPairs < 8){ endGame('lose'); }
        }
      }, 1000);
    }

    function flipCard(){
      if(isProcessing || flippedCards.length>=2 || this.classList.contains('flipped') || this.classList.contains('matched')) return;
      this.classList.add('flipped');
      flippedCards.push(this);
      if(flippedCards.length===2){
        isProcessing = true;
        setTimeout(checkMatch, 600);
      }
    }

    function checkMatch(){
      const [c1, c2] = flippedCards;
      if(!c1 || !c2){ isProcessing=false; flippedCards=[]; return; }
      const i1=c1.dataset.image, i2=c2.dataset.image;
      if(i1===i2){
        gameMessage.textContent = 'Match found!';
        // Update the front faces to show a check mark when returned to front
        const f1 = c1.querySelector('.card-front');
        const f2 = c2.querySelector('.card-front');
        if(f1) f1.textContent = '✔';
        if(f2) f2.textContent = '✔';
        c1.classList.add('matched'); c2.classList.add('matched');
        matchedPairs++; matchesDisplay.textContent = `Matches: ${matchedPairs} / 8`;
        if(matchedPairs===8){ endGame('win'); }
      } else {
        gameMessage.textContent = 'No match, try again!';
        c1.classList.remove('flipped'); c2.classList.remove('flipped');
      }
      flippedCards=[]; isProcessing=false;
    }

    function startGame(){
      matchedPairs=0; flippedCards=[]; isProcessing=false; gameMessage.textContent='';
      matchesDisplay.textContent = 'Matches: 0 / 8';
      // show game screen first so loading state is visible
      startScreen.classList.add('hidden'); endScreen.classList.add('hidden'); gameScreen.classList.remove('hidden');
      // loading message
      gameGrid.innerHTML = '<p class="cbc-loading">Loading images…</p>';

      fetch((window.cbcGames.apiBase||'') + 'memory')
        .then(r=>r.json())
        .then(data=>{
          const images = Array.isArray(data.images)?data.images:[];
          if(images.length<8){
            gameGrid.innerHTML = '<p style="color:#DC2626;">Not enough images to play.</p>';
            return;
          }
          createBoard(shuffle(images));
          startTimer();
        })
        .catch(()=>{
          gameGrid.innerHTML = '<p style="color:#DC2626;">Failed to load images. Please refresh.</p>';
        });
    }

    function endGame(result){
      clearInterval(countdown);
      // Clear fullscreen card sizing when leaving game screen
      root.style.removeProperty('--cbc-card-size');
      gameScreen.classList.add('hidden'); endScreen.classList.remove('hidden');
      if(result==='win'){
        endMessage.textContent = 'You Win!'; endMessage.style.color = '#16A34A';
        finalStats.textContent = 'You matched all pairs in time!';
      } else {
        endMessage.textContent = 'Game Over'; endMessage.style.color = '#DC2626';
        finalStats.textContent = `Time ran out. You found ${matchedPairs} out of 8 matches.`;
      }
    }

    startBtn?.addEventListener('click', startGame);
    restartBtn?.addEventListener('click', startGame);
  });
})();
