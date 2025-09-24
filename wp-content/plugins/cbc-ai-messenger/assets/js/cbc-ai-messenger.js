(function($){
  function addMsg($box, who, text){
    var $log = $box.find('.cbc-ai-log');
    var $div = $('<div/>').addClass('cbc-ai-msg ' + (who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
    $div.text(text);
    $log.append($div);
    $log.scrollTop($log[0].scrollHeight);
  }

  // Toggle open/close for floating widget
  $(document).on('click', '.cbc-ai-box .cbc-ai-toggle', function(){
    var $btn = $(this);
    var $box = $btn.closest('.cbc-ai-box');
    var isCollapsed = $box.hasClass('cbc-ai-collapsed');
    if (isCollapsed) {
      $box.removeClass('cbc-ai-collapsed').addClass('cbc-ai-open');
      $btn.attr('aria-expanded', 'true').text('Close');
      try { localStorage.setItem('cbc_ai_open', '1'); } catch(e) {}
    } else {
      $box.removeClass('cbc-ai-open').addClass('cbc-ai-collapsed');
      $btn.attr('aria-expanded', 'false').text('Open');
      try { localStorage.setItem('cbc_ai_open', '0'); } catch(e) {}
    }
  });

  // Restore open state if saved
  $(function(){
    try {
      var open = localStorage.getItem('cbc_ai_open');
      if (open === '1') {
        $('.cbc-ai-box.cbc-ai-floating').removeClass('cbc-ai-collapsed').addClass('cbc-ai-open')
          .find('.cbc-ai-toggle').attr('aria-expanded','true').text('Close');
      }
    } catch(e) {}
  });

  // Submit question
  $(document).on('submit', '.cbc-ai-box .cbc-ai-form', function(e){
    e.preventDefault();
    var $form = $(this);
    var $box = $form.closest('.cbc-ai-box');
    var $input = $form.find('.cbc-ai-input');
    var msg = ($input.val() || '').trim();
    if (!msg) { return; }

    var name = ($form.find('.cbc-ai-input-name').val() || '').trim();
    var email = ($form.find('.cbc-ai-input-email').val() || '').trim();

    addMsg($box, 'user', msg);
    $input.val('');

    var $btn = $form.find('.cbc-ai-send');
    var oldLabel = $btn.text();
    $btn.prop('disabled', true).text('Thinking…');

    fetch(CBCAI.restUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': CBCAI.nonce
      },
      body: JSON.stringify({ message: msg, name: name, email: email })
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data && data.reply) {
        addMsg($box, 'bot', data.reply);
      } else if (data && data.error) {
        addMsg($box, 'bot', 'Error: ' + data.error);
      } else {
        addMsg($box, 'bot', 'Sorry, I could not generate a response right now.');
      }
    })
    .catch(function(){
      addMsg($box, 'bot', 'Network error. Please try again.');
    })
    .finally(function(){
      $btn.prop('disabled', false).text(oldLabel);
    });
  });
})(jQuery);
