(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('cbc-quiz-app');
    if(!root || !window.cbcGames) return;

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

    // Fullscreen toggle
    const fsBtn = root.querySelector('#quiz-fullscreen-btn');
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

    let currentQuestions = [];
    let questionIndex = 0;
    let score = 0;

    function shuffleArray(array){
      for(let i=array.length-1;i>0;i--){
        const j = Math.floor(Math.random()*(i+1));
        [array[i], array[j]] = [array[j], array[i]];
      }
      return array;
    }

    function startGame(){
      // Reset
      questionIndex = 0; score = 0; currentQuestions = [];
      scoreText.textContent = 'Score: 0';
      feedbackText.textContent = '';
      optionsContainer.innerHTML = '';
      progressText.textContent = '';

      // Show quiz screen first so loading state is visible
      startScreen.classList.add('hidden'); endScreen.classList.add('hidden'); quizScreen.classList.remove('hidden');
      questionText.textContent = 'Loading questions…';

      fetch((window.cbcGames.apiBase || '') + 'quiz')
        .then(r=>r.json())
        .then(data=>{
          currentQuestions = Array.isArray(data.questions) ? data.questions : [];
          // Safety shuffle
          shuffleArray(currentQuestions);
          currentQuestions = currentQuestions.slice(0,10);

          if(currentQuestions.length === 0){
            questionText.textContent = '';
            optionsContainer.innerHTML = '<p style="color:#DC2626;">No questions available.</p>';
            return;
          }
          showQuestion();
        })
        .catch(()=>{
          questionText.textContent = '';
          optionsContainer.innerHTML = '<p style="color:#DC2626;">Failed to load questions. Please refresh.</p>';
        });
    }

    function showQuestion(){
      feedbackText.textContent = '';
      optionsContainer.innerHTML = '';

      if(questionIndex >= currentQuestions.length){
        endGame();
        return;
      }

      progressText.textContent = `Question ${questionIndex+1}/${currentQuestions.length}`;
      const q = currentQuestions[questionIndex];
      questionText.textContent = q.question;
      const shuffledOpts = shuffleArray([...(q.options||[])]);
      shuffledOpts.forEach(option => {
        const btn = document.createElement('button');
        btn.textContent = option;
        btn.className = 'btn-option w-full p-3 rounded-lg border-2 text-left transition-all duration-200';
        btn.addEventListener('click', ()=> selectAnswer(btn, option, q.answer));
        optionsContainer.appendChild(btn);
      });
    }

    function selectAnswer(button, selected, correct){
      const buttons = optionsContainer.querySelectorAll('button');
      buttons.forEach(b=> b.disabled = true);

      if(selected === correct){
        score++; scoreText.textContent = `Score: ${score}`;
        button.classList.add('correct');
        feedbackText.textContent = 'Correct!';
        feedbackText.style.color = '#16A34A';
      } else {
        button.classList.add('incorrect');
        feedbackText.textContent = `Wrong! The answer is ${correct}`;
        feedbackText.style.color = '#DC2626';
        buttons.forEach(b=>{ if(b.textContent === correct){ b.classList.add('correct'); }});
      }

      setTimeout(()=>{ questionIndex++; showQuestion(); }, 1200);
    }

    function endGame(){
      quizScreen.classList.add('hidden');
      endScreen.classList.remove('hidden');
      finalScoreText.textContent = `${score} / ${currentQuestions.length}`;
      if(score >= 8){
        resultMessage.textContent = 'Congratulations! You win a prize! Your knowledge is outstanding!';
        resultMessage.style.color = '#16A34A';
      } else {
        resultMessage.textContent = 'Great effort! Keep learning and try again to win the prize.';
        resultMessage.style.color = '#525252';
      }
    }

    startBtn?.addEventListener('click', startGame);
    restartBtn?.addEventListener('click', startGame);
  });
})();
