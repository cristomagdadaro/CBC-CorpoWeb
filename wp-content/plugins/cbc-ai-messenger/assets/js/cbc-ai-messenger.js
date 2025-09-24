(function($){
  function addMsg($box, who, text){
    var $log = $box.find('.cbc-ai-log');
    var $div = $('<div/>').addClass('cbc-ai-msg ' + (who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
    if (who === 'user') {
      // render as text to avoid HTML injection from user input
      $div.text(text);
    } else {
      // bot replies may contain basic HTML (related links), render as HTML
      $div.html(text);
    }
    $log.append($div);
    $log.scrollTop($log[0].scrollHeight);
  }

  function setAria($btn, title, isOpen){
    $btn.attr('aria-label', (isOpen ? 'Close ' : 'Open ') + (title || 'CBC AI Assistant'));
  }

  function isMobile() {
    try { return window.innerWidth <= 600; } catch(e) { return false; }
  }

  // Create backdrop and append to body when needed
  function createBackdrop(){
    if ($('#cbc-ai-backdrop').length) return;
    var $back = $('<div id="cbc-ai-backdrop" class="fixed inset-0 bg-black/50 z-[9999]"></div>');
    $('body').append($back);
    // clicking backdrop closes open widgets
    $back.on('click', function(){
      $('.cbc-ai-box.cbc-ai-open').each(function(){
        var $box = $(this);
        var $btn = $box.find('.cbc-ai-toggle');
        $btn.trigger('click');
      });
    });
  }
  function removeBackdrop(){
    $('#cbc-ai-backdrop').remove();
  }

  // Apply mobile fullscreen to the whole widget (.cbc-ai-box) so header is part of overlay
  function applyMobileFullscreen($box, $body, $header){
    // store original classes once so we can restore later
    if (!$box.attr('data-cbcai-orig-classes')) {
      $box.attr('data-cbcai-orig-classes', $box.attr('class'));
    }

    // Make box cover viewport and layout as column so header sits at top
    $box.addClass('fixed inset-0 z-[10000] flex flex-col items-stretch bg-white');
    // header should be full width and above body
    $header.addClass('w-full z-[10001]');
    // body should fill remaining space and be scrollable; add padding to avoid overlap
    $body.addClass('flex-1 overflow-auto p-4');
    // remove rounded corners for fullscreen
    $box.removeClass('rounded-md');
    $body.removeClass('md:rounded-md');

    // create backdrop and lock body scroll
    createBackdrop();
    try { document.body.classList.add('cbc-ai-scroll-locked'); } catch(e){}
  }
  function removeMobileFullscreen($box, $body, $header){
    // remove fullscreen classes we added
    $box.removeClass('inset-0 z-[10000] flex flex-col items-stretch bg-white');
    $header.removeClass('w-full z-[10001]');
    $body.removeClass('flex-1 overflow-auto p-4');
    // restore rounded corners if desired (keep as-is)

    // restore original classes if we saved them
    var orig = $box.attr('data-cbcai-orig-classes');
    if (orig) {
      $box.attr('class', orig);
      // ensure cbc-ai-box remains present
      if ($box.attr('class').indexOf('cbc-ai-box') === -1) {
        $box.addClass('cbc-ai-box');
      }
      $box.removeAttr('data-cbcai-orig-classes');
    }

    // remove backdrop and restore body scroll
    removeBackdrop();
    try { document.body.classList.remove('cbc-ai-scroll-locked'); } catch(e){}
  }

  // Animate opening: remove hidden then animate opacity/scale
  function animateOpen($box, $body, $title, $openIcon, $closeIcon, $btn){
    $box.removeClass('cbc-ai-collapsed').addClass('cbc-ai-open');
    $title.addClass('hidden'); // start collapsed behavior; we'll unhide after animation on desktop

    if (isMobile()) {
      // apply fullscreen to the box and body/header
      var $header = $box.find('.cbc-ai-header').first();
      applyMobileFullscreen($box, $body, $header);
    } else {
      removeMobileFullscreen($box, $body, $box.find('.cbc-ai-header'));
    }

    // prepare for animation
    $body.removeClass('hidden');
    $body.addClass('opacity-0 scale-95');
    // force reflow
    void $body[0].offsetWidth;
    // animate to visible
    $body.removeClass('opacity-0 scale-95').addClass('opacity-100 scale-100');

    // swap icons
    $openIcon.addClass('hidden');
    $closeIcon.removeClass('hidden');
    $btn.attr('aria-expanded','true');
    setAria($btn, $box.find('.cbc-ai-title').text(), true);
    try { localStorage.setItem('cbc_ai_open', '1'); } catch(e){}

    // After short delay, ensure title visible on desktop; on mobile show immediately
    if (isMobile()) {
      $title.removeClass('hidden');
    } else {
      setTimeout(function(){ $title.removeClass('hidden'); }, 50);
    }
  }

  // Animate close: animate opacity/scale then hide
  function animateClose($box, $body, $title, $openIcon, $closeIcon, $btn){
    // hide title quickly
    $title.addClass('hidden');
    // start closing animation
    $body.removeClass('opacity-100 scale-100').addClass('opacity-0 scale-95');
    // swap icons
    $closeIcon.addClass('hidden');
    $openIcon.removeClass('hidden');
    $btn.attr('aria-expanded','false');
    setAria($btn, $box.find('.cbc-ai-title').text(), false);
    try { localStorage.setItem('cbc_ai_open', '0'); } catch(e){}

    // after animation duration, hide and cleanup
    setTimeout(function(){
      $body.addClass('hidden');
      $body.removeClass('opacity-0 scale-95 opacity-100 scale-100');
      // remove mobile fullscreen classes from box/header/body
      var $header = $box.find('.cbc-ai-header').first();
      removeMobileFullscreen($box, $body, $header);
      $box.removeClass('cbc-ai-open').addClass('cbc-ai-collapsed');
    }, 50);
  }

  // Toggle open/close for floating widget using Tailwind classes + mobile fullscreen
  $(document).on('click', '.cbc-ai-box .cbc-ai-toggle', function(){
    var $btn = $(this);
    var $box = $btn.closest('.cbc-ai-box');
    var $body = $box.find('.cbc-ai-body').first();
    var $title = $box.find('.cbc-ai-title').first();
    var $openIcon = $btn.find('.cbc-ai-toggle-open-icon');
    var $closeIcon = $btn.find('.cbc-ai-toggle-close-icon');

    var isOpen = !$body.hasClass('hidden') && $body.css('display') !== 'none';
    if (!isOpen) {
      animateOpen($box, $body, $title, $openIcon, $closeIcon, $btn);
    } else {
      animateClose($box, $body, $title, $openIcon, $closeIcon, $btn);
    }
  });

  // Restore open state if saved; apply to floating widgets
  $(function(){
    try {
      var open = localStorage.getItem('cbc_ai_open');
      $('.cbc-ai-box.cbc-ai-floating').each(function(){
        var $box = $(this);
        var $btn = $box.find('.cbc-ai-toggle');
        var $body = $box.find('.cbc-ai-body');
        var $title = $box.find('.cbc-ai-title');
        var $openIcon = $btn.find('.cbc-ai-toggle-open-icon');
        var $closeIcon = $btn.find('.cbc-ai-toggle-close-icon');
        if (open === '1') {
          animateOpen($box, $body, $title, $openIcon, $closeIcon, $btn);
        } else {
          // ensure closed
          $body.addClass('hidden');
          $openIcon.removeClass('hidden');
          $closeIcon.addClass('hidden');
          $btn.attr('aria-expanded','false');
          $box.removeClass('cbc-ai-open').addClass('cbc-ai-collapsed');
          $title.addClass('hidden');
        }
      });
    } catch(e) {}
  });

  // Show user info at top of conversation and hide contact fields
  function showUserInfo($box, name, email){
    var $userInfo = $box.find('.cbc-ai-user-info');
    $userInfo.html('<div class="flex items-center justify-between w-full"><div><strong>' + escapeHtml(name) + '</strong> <span class="text-xs text-gray-600">&lt;' + escapeHtml(email) + '&gt;</span></div><button type="button" class="cbc-ai-edit-user text-xs text-blue-600 underline">Change</button></div>');
    $userInfo.removeClass('hidden');
    $box.find('.cbc-ai-contact-fields').addClass('hidden');
  }

  function hideUserInfo($box){
    var $userInfo = $box.find('.cbc-ai-user-info');
    $userInfo.addClass('hidden').empty();
    $box.find('.cbc-ai-contact-fields').removeClass('hidden');
  }

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, function(s){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[s];
    });
  }

  // Restore stored user info on load
  $(function(){
    try {
      var storedName = localStorage.getItem('cbc_ai_user_name');
      var storedEmail = localStorage.getItem('cbc_ai_user_email');
      $('.cbc-ai-box.cbc-ai-floating').each(function(){
        var $box = $(this);
        var $name = $box.find('.cbc-ai-input-name');
        var $email = $box.find('.cbc-ai-input-email');
        if (storedName && storedEmail) {
          $name.val(storedName);
          $email.val(storedEmail);
          showUserInfo($box, storedName, storedEmail);
        }
      });
    } catch(e) {}
  });

  // Edit user info handler (delegated)
  $(document).on('click', '.cbc-ai-box .cbc-ai-edit-user', function(){
    var $btn = $(this);
    var $box = $btn.closest('.cbc-ai-box');
    // clear stored info so user can re-enter
    try { localStorage.removeItem('cbc_ai_user_name'); localStorage.removeItem('cbc_ai_user_email'); } catch(e) {}
    hideUserInfo($box);
  });

  // Submit question
  $(document).on('submit', '.cbc-ai-box .cbc-ai-form', function(e){
    e.preventDefault();
    var $form = $(this);
    var $box = $form.closest('.cbc-ai-box');
    var $input = $form.find('.cbc-ai-input');
    var msg = ($input.val() || '').trim();
    if (!msg) { return; }

    var $nameField = $form.find('.cbc-ai-input-name');
    var $emailField = $form.find('.cbc-ai-input-email');
    var name = ($nameField.val() || '').trim();
    var email = ($emailField.val() || '').trim();

    // Validate name/email
    var invalid = false;
    // basic email regex
    var emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
    if (!name) {
      showFieldError($box, 'Please enter your name.'); invalid = true;
    }
    if (!email || !emailRegex.test(email)) {
      showFieldError($box, 'Please enter a valid email address.'); invalid = true;
    }
    if (invalid) return;

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

      // On first successful submission, hide contact fields and show user info
      try {
        localStorage.setItem('cbc_ai_user_name', name);
        localStorage.setItem('cbc_ai_user_email', email);
      } catch(e){}
      showUserInfo($box, name, email);
    })
    .catch(function(){
      addMsg($box, 'bot', 'Network error. Please try again.');
    })
    .finally(function(){
      $btn.prop('disabled', false).text(oldLabel);
    });
  });

  // show a temporary field-level error in the contact area
  function showFieldError($box, message){
    var $area = $box.find('.cbc-ai-contact-fields');
    if (!$area.length) return;
    var $err = $('<div/>').addClass('cbc-ai-field-error text-sm text-red-600 mt-1').text(message);
    $area.append($err);
    setTimeout(function(){ $err.fadeOut(200, function(){ $err.remove(); }); }, 3000);
  }

  // Debounced resize handler to adjust fullscreen behavior on orientation/size change
  (function(){
    var timeout = null;
    function onResize(){
      clearTimeout(timeout);
      timeout = setTimeout(function(){
        $('.cbc-ai-box.cbc-ai-open').each(function(){
          var $box = $(this);
          var $body = $box.find('.cbc-ai-body').first();
          var $header = $box.find('.cbc-ai-header').first();
          if (isMobile()) {
            // ensure fullscreen applied
            applyMobileFullscreen($box, $body, $header);
          } else {
            // ensure fullscreen removed
            removeMobileFullscreen($box, $body, $header);
          }
        });
      }, 150);
    }
    if (typeof window !== 'undefined') {
      $(window).on('resize', onResize);
    }
  })();

})(jQuery);
