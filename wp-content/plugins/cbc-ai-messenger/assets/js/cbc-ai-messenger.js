(function($){
  // Utility: detect small screen
  function isMobile(){ try { return window.innerWidth <= 768; } catch(e){ return false; } }

  // Message append logic (unchanged core)
  function addMsg($panel, who, text){
    var $log = $panel.find('.cbc-ai-log');
    var $div = $('<div/>').addClass('cbc-ai-msg ' + (who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
    if (who === 'user') { $div.text(text); } else { $div.html(text); }
    $log.append($div); $log.scrollTop($log[0].scrollHeight);
  }

  function escapeHtml(str){ return String(str).replace(/[&<>"']/g, function(s){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]); }); }

  // Backdrop helpers (mobile)
  function ensureBackdrop(){ if ($('#cbc-ai-backdrop').length) return; $('body').append('<div id="cbc-ai-backdrop" class="fixed inset-0 bg-black/50 z-[9998]"></div>'); }
  function removeBackdrop(){ $('#cbc-ai-backdrop').remove(); }

  // Fullscreen apply/remove on mobile
  function applyMobileFullscreen($container, $panel){
    if (!$container.hasClass('cbc-ai-mobile-open')){
      $container.addClass('cbc-ai-mobile-open');
      ensureBackdrop();
      try { document.body.classList.add('cbc-ai-scroll-locked'); } catch(e){}
    }
  }
  function removeMobileFullscreen($container){
    if ($container.hasClass('cbc-ai-mobile-open')){
      $container.removeClass('cbc-ai-mobile-open');
      removeBackdrop();
      try { document.body.classList.remove('cbc-ai-scroll-locked'); } catch(e){}
    }
  }

  // Show / hide user info (name/email) once first submission succeeds
  function showUserInfo($panel, name, email){
    var $userInfo = $panel.find('.cbc-ai-user-info');
    $userInfo.html('<div class="flex items-center justify-between w-full"><div><strong>' + escapeHtml(name) + '</strong> <span class="text-xs text-gray-600">&lt;' + escapeHtml(email) + '&gt;</span></div><button type="button" class="cbc-ai-edit-user text-xs text-blue-600 underline">Change</button></div>');
    $userInfo.removeClass('hidden');
    $panel.find('.cbc-ai-contact-fields').addClass('hidden');
  }
  function hideUserInfo($panel){
    var $ui = $panel.find('.cbc-ai-user-info');
    $ui.addClass('hidden').empty();
    $panel.find('.cbc-ai-contact-fields').removeClass('hidden');
  }

  function showFieldError($panel, message){
    var $area = $panel.find('.cbc-ai-contact-fields'); if(!$area.length) return;
    var $err = $('<div/>').addClass('cbc-ai-field-error text-sm text-red-600 mt-1').text(message);
    $area.append($err); setTimeout(function(){ $err.fadeOut(180, function(){ $err.remove(); }); }, 2800);
  }

  // Toggle logic replicating floating-sidebar-container style
  function initChatWidget(){
    var $container = $('#cbc-ai-chat-container');
    var $toggle = $('#cbc-ai-chat-toggle');
    var $panel = $('#cbc-ai-chat-panel');
    if(!$container.length || !$toggle.length || !$panel.length) return;

    var stateKey = 'cbc_ai_chat_collapsed';
    // Migrate legacy key (cbc_ai_open) if present
    try {
      var legacy = localStorage.getItem('cbc_ai_open');
      if (legacy !== null && localStorage.getItem(stateKey) === null) {
        // legacy: '1' meant open previously; collapsed is inverse
        localStorage.setItem(stateKey, legacy === '1' ? '0' : '1');
      }
    } catch(e){}

    var collapsed = false;
    try { collapsed = localStorage.getItem(stateKey) === '1'; } catch(e){}
    if (collapsed){
      $container.addClass('collapsed');
      $toggle.attr('aria-expanded','false');
    } else {
      $toggle.attr('aria-expanded','true');
    }
    updateIcons($toggle, collapsed);

    // Apply mobile fullscreen if open on load
    if (!collapsed && isMobile()) { applyMobileFullscreen($container, $panel); }

    // Toggle click
    $toggle.on('click', function(e){
      e.preventDefault();
      $container.toggleClass('collapsed');
      var isCollapsed = $container.hasClass('collapsed');
      try { localStorage.setItem(stateKey, isCollapsed ? '1' : '0'); } catch(e){}
      $toggle.attr('aria-expanded', isCollapsed ? 'false' : 'true');
      updateIcons($toggle, isCollapsed);
      if (isCollapsed){
        removeMobileFullscreen($container);
      } else {
        if (isMobile()) applyMobileFullscreen($container, $panel);
      }
    });

    // Backdrop click closes on mobile
    $(document).on('click', '#cbc-ai-backdrop', function(){
      if (!$container.hasClass('collapsed')) { $toggle.trigger('click'); }
    });
  }

  function updateIcons($toggle, isCollapsed){
    var $arrow = $toggle.find('.cbc-ai-icon-expanded');
    var $robot = $toggle.find('.cbc-ai-icon-collapsed');
    if (isCollapsed){
      $arrow.addClass('hidden');
      $robot.removeClass('hidden');
    } else {
      $robot.addClass('hidden');
      $arrow.removeClass('hidden');
    }
  }

  // Resize handler to switch fullscreen state appropriately
  function setupResizeHandler(){
    var to=null; $(window).on('resize', function(){
      clearTimeout(to); to=setTimeout(function(){
        var $container = $('#cbc-ai-chat-container'); var $panel = $('#cbc-ai-chat-panel');
        if(!$container.length) return;
        if ($container.hasClass('collapsed')) { removeMobileFullscreen($container); return; }
        if (isMobile()) applyMobileFullscreen($container, $panel); else removeMobileFullscreen($container);
      }, 160);
    });
  }

  // Restore stored user info
  function restoreUserInfo(){
    try {
      var storedName = localStorage.getItem('cbc_ai_user_name');
      var storedEmail = localStorage.getItem('cbc_ai_user_email');
      var $panel = $('#cbc-ai-chat-panel');
      if ($panel.length && storedName && storedEmail){
        $panel.find('.cbc-ai-input-name').val(storedName);
        $panel.find('.cbc-ai-input-email').val(storedEmail);
        showUserInfo($panel, storedName, storedEmail);
      }
    } catch(e){}
  }

  // Form submission
  $(document).on('submit', '#cbc-ai-chat-panel .cbc-ai-form', function(e){
    e.preventDefault();
    var $form = $(this); var $panel = $('#cbc-ai-chat-panel');
    var $input = $form.find('.cbc-ai-input'); var msg = ($input.val()||'').trim(); if(!msg) return;
    var name = ($form.find('.cbc-ai-input-name').val()||'').trim();
    var email = ($form.find('.cbc-ai-input-email').val()||'').trim();
    var emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
    var invalid = false; if(!name){ showFieldError($panel,'Please enter your name.'); invalid=true; }
    if(!email || !emailRegex.test(email)){ showFieldError($panel,'Please enter a valid email address.'); invalid=true; }
    if(invalid) return;

    addMsg($panel,'user',msg); $input.val('');
    var $btn = $form.find('.cbc-ai-send'); var old = $btn.text(); $btn.prop('disabled', true).text('Thinking…');

    fetch(CBCAI.restUrl, { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':CBCAI.nonce}, body: JSON.stringify({message:msg,name:name,email:email}) })
      .then(r=>r.json())
      .then(function(data){
        if(data && data.reply){ addMsg($panel,'bot', data.reply); }
        else if(data && data.error){ addMsg($panel,'bot','Error: '+data.error); }
        else { addMsg($panel,'bot','Sorry, I could not generate a response right now.'); }
        try { localStorage.setItem('cbc_ai_user_name', name); localStorage.setItem('cbc_ai_user_email', email); } catch(e){}
        showUserInfo($panel, name, email);
      })
      .catch(function(){ addMsg($panel,'bot','Network error. Please try again.'); })
      .finally(function(){ $btn.prop('disabled', false).text(old); });
  });

  // Edit user info
  $(document).on('click', '#cbc-ai-chat-panel .cbc-ai-edit-user', function(){
    var $panel = $('#cbc-ai-chat-panel');
    try { localStorage.removeItem('cbc_ai_user_name'); localStorage.removeItem('cbc_ai_user_email'); } catch(e){}
    hideUserInfo($panel);
  });

  // Init on DOM ready
  $(function(){ initChatWidget(); setupResizeHandler(); restoreUserInfo(); });

})(jQuery);
