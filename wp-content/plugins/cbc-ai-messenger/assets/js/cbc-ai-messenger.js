(function($){
  function addMsg($box, who, text){
    var $log = $box.find('.cbc-ai-log');
    var $div = $('<div/>').addClass('cbc-ai-msg ' + (who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
    $div.html(text);
    $log.append($div);
    $log.scrollTop($log[0].scrollHeight);
  }

  function updateToggleAria($box, $btn, isOpen){
    var title = ($box.find('.cbc-ai-title').text() || 'CBC AI Assistant').trim();
    $btn.attr('aria-label', (isOpen ? 'Close ' : 'Open ') + title);
  }

  // Toggle open/close for floating widget using CSS classes
  $(document).on('click', '.cbc-ai-box .cbc-ai-toggle', function(){
    var $btn = $(this);
    var $box = $btn.closest('.cbc-ai-box');
    var isCollapsed = $box.hasClass('cbc-ai-collapsed');
    if (isCollapsed) {
      $box.removeClass('cbc-ai-collapsed').addClass('cbc-ai-open');
      $btn.attr('aria-expanded', 'true');
      $btn.removeClass('cbc-ai-toggle-open').addClass('cbc-ai-toggle-close');
      updateToggleAria($box, $btn, true);
      try { localStorage.setItem('cbc_ai_open', '1'); } catch(e) {}
    } else {
      $box.removeClass('cbc-ai-open').addClass('cbc-ai-collapsed');
      $btn.attr('aria-expanded', 'false');
      $btn.removeClass('cbc-ai-toggle-close').addClass('cbc-ai-toggle-open');
      updateToggleAria($box, $btn, false);
      try { localStorage.setItem('cbc_ai_open', '0'); } catch(e) {}
    }
  });

  // Restore open state if saved; apply to floating widgets
  $(function(){
    try {
      var open = localStorage.getItem('cbc_ai_open');
      $('.cbc-ai-box.cbc-ai-floating').each(function(){
        var $box = $(this);
        var $btn = $box.find('.cbc-ai-toggle');
        if (open === '1') {
          $box.removeClass('cbc-ai-collapsed').addClass('cbc-ai-open');
          $btn.attr('aria-expanded','true');
          $btn.removeClass('cbc-ai-toggle-open').addClass('cbc-ai-toggle-close');
          updateToggleAria($box, $btn, true);
        } else {
          $box.removeClass('cbc-ai-open').addClass('cbc-ai-collapsed');
          $btn.attr('aria-expanded','false');
          $btn.removeClass('cbc-ai-toggle-close').addClass('cbc-ai-toggle-open');
          updateToggleAria($box, $btn, false);
        }
      });
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

  // Allow submit with Enter key in textarea
  $(document).on('keydown', '.cbc-ai-box .cbc-ai-input', function(e){
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      $(this).closest('.cbc-ai-form').trigger('submit');
    }
  });

})(jQuery);
