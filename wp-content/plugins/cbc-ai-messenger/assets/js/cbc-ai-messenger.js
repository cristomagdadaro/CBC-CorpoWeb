(function ($) {
    // Utility: detect small screen
    function isMobile() {
        try {
            return window.innerWidth <= 768;
        } catch (e) {
            return false;
        }
    }

    // Message append logic
    function addMsg($panel, who, text) {
        var $log = $panel.find('.cbc-ai-log');
        var $convoLabel = $panel.find('.cbc-ai-convo-label');
        // Sanitize 'who' to prevent class injection
        var whoClass = who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot';
        var $div = $('<div/>').addClass('cbc-ai-msg ' + whoClass);

        if (who === 'user') {
            // Use .text() for user input to prevent self-XSS
            $div.text(text);
        } else {
            // **Vulnerability Point**: Using .html() trusts the server response.
            // This is safe ONLY if the server sanitizes all output.
            $div.html(text);
        }
        $log.append($div);
        $log.scrollTop($log[0].scrollHeight);
        $log.removeClass('hidden').addClass('block flex');
        $convoLabel.removeClass('hidden').addClass('block');
    }

    // HTML escaping utility
    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (s) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;'}[s]);
        });
    }

    // Backdrop helpers (mobile)
    function ensureBackdrop() {
        if ($('#cbc-ai-backdrop').length) return;
        $('body').append('<div id="cbc-ai-backdrop" class="fixed inset-0 bg-black/50 z-[9998]"></div>');
    }

    function removeBackdrop() {
        $('#cbc-ai-backdrop').remove();
    }

    // Fullscreen apply/remove on mobile
    function applyMobileFullscreen($container) {
        if (!$container.hasClass('cbc-ai-mobile-open')) {
            $container.addClass('cbc-ai-mobile-open');
            ensureBackdrop();
            try {
                document.body.classList.add('cbc-ai-scroll-locked');
            } catch (e) {
                console.error("Failed to lock scroll:", e);
            }
        }
    }

    function removeMobileFullscreen($container) {
        if ($container.hasClass('cbc-ai-mobile-open')) {
            $container.removeClass('cbc-ai-mobile-open');
            removeBackdrop();
            try {
                document.body.classList.remove('cbc-ai-scroll-locked');
            } catch (e) {
                console.error("Failed to unlock scroll:", e);
            }
        }
    }

    // Show / hide user info (name/email) once first submission succeeds
    function showUserInfo($panel, name, email) {
        var $userInfo = $panel.find('.cbc-ai-user-info');
        // Use escapeHtml to prevent XSS from stored user info
        $userInfo.html('<div class="flex items-center justify-between w-full"><div><strong>' + escapeHtml(name) + '</strong> <span class="text-xs text-gray-600">&lt;' + escapeHtml(email) + '&gt;</span></div><button type="button" class="cbc-ai-edit-user text-xs text-blue-600 underline">Change</button></div>');
        $userInfo.removeClass('hidden');
        $panel.find('.cbc-ai-contact-fields').addClass('hidden');
    }

    function hideUserInfo($panel) {
        var $ui = $panel.find('.cbc-ai-user-info');
        $ui.addClass('hidden').empty();
        $panel.find('.cbc-ai-contact-fields').removeClass('hidden');
    }

    // User info edit handler
    $(document).on('click', '.cbc-ai-edit-user', function() {
        var $panel = $(this).closest('#cbc-ai-chat-panel');
        hideUserInfo($panel);
    });

    function showFieldError($panel, message) {
        var $area = $panel.find('.cbc-ai-contact-fields');
        if (!$area.length) return;
        // Remove existing errors to prevent stacking
        $area.find('.cbc-ai-field-error').remove();
        var $err = $('<div/>').addClass('cbc-ai-field-error text-sm text-red-600 mt-1').text(message);
        $area.append($err);
        setTimeout(function () {
            $err.fadeOut(180, function () {
                $err.remove();
            });
        }, 2800);
    }

    // Toggle logic for the chat widget
    function initChatWidget() {
        var $container = $('#cbc-ai-chat-container');
        var $toggle = $('#cbc-ai-chat-toggle');
        var $panel = $('#cbc-ai-chat-panel');
        if (!$container.length || !$toggle.length || !$panel.length) return;

        var stateKey = 'cbc_ai_chat_collapsed';
        // Migrate legacy key if present
        try {
            var legacy = localStorage.getItem('cbc_ai_open');
            if (legacy !== null && localStorage.getItem(stateKey) === null) {
                localStorage.setItem(stateKey, legacy === '1' ? '0' : '1');
                localStorage.removeItem('cbc_ai_open'); // Clean up old key
            }
        } catch (e) {
            console.error("Could not access localStorage:", e);
        }

        var collapsed = false;
        try {
            collapsed = localStorage.getItem(stateKey) === '1';
        } catch (e) {
            // Default to collapsed if localStorage is inaccessible
            collapsed = true;
        }

        if (collapsed) {
            $container.addClass('collapsed');
            $toggle.attr('aria-expanded', 'false');
        } else {
            $toggle.attr('aria-expanded', 'true');
        }
        updateIcons($toggle, collapsed);

        if (!collapsed && isMobile()) {
            applyMobileFullscreen($container);
        }

        $toggle.on('click', function (e) {
            e.preventDefault();
            $container.toggleClass('collapsed');
            var isCollapsed = $container.hasClass('collapsed');
            try {
                localStorage.setItem(stateKey, isCollapsed ? '1' : '0');
            } catch (e) {
                console.error("Could not set localStorage item:", e);
            }
            $toggle.attr('aria-expanded', isCollapsed ? 'false' : 'true');
            updateIcons($toggle, isCollapsed);
            if (isCollapsed) {
                removeMobileFullscreen($container);
            } else {
                if (isMobile()) applyMobileFullscreen($container);
            }
        });

        $(document).on('click', '#cbc-ai-backdrop', function () {
            if (!$container.hasClass('collapsed')) {
                $toggle.trigger('click');
            }
        });
    }

    function updateIcons($toggle, isCollapsed) {
        var $arrow = $toggle.find('.cbc-ai-icon-expanded');
        var $robot = $toggle.find('.cbc-ai-icon-collapsed');
        if (isCollapsed) {
            $arrow.addClass('hidden');
            $robot.removeClass('hidden');
        } else {
            $robot.addClass('hidden');
            $arrow.removeClass('hidden');
        }
    }

    function setupResizeHandler() {
        var to = null;
        $(window).on('resize', function () {
            clearTimeout(to);
            to = setTimeout(function () {
                var $container = $('#cbc-ai-chat-container');
                if (!$container.length) return;
                if ($container.hasClass('collapsed')) {
                    removeMobileFullscreen($container);
                    return;
                }
                if (isMobile()) applyMobileFullscreen($container); else removeMobileFullscreen($container);
            }, 160);
        });
    }

    // Restore stored user info
    function restoreUserInfo() {
        try {
            var storedName = localStorage.getItem('cbc_ai_user_name');
            var storedEmail = localStorage.getItem('cbc_ai_user_email');
            var $panel = $('#cbc-ai-chat-panel');
            if ($panel.length && storedName && storedEmail) {
                $panel.find('.cbc-ai-input-name').val(storedName);
                $panel.find('.cbc-ai-input-email').val(storedEmail);
                showUserInfo($panel, storedName, storedEmail);
            }
        } catch (e) {
            console.error("Could not restore user info from localStorage:", e);
        }
    }

    // Dynamically load reCAPTCHA script
    function loadRecaptcha(siteKey, timeoutMs) {
        timeoutMs = timeoutMs || 8000;
        return new Promise(function (resolve, reject) {
            if (!siteKey) return reject(new Error('no-site-key'));
            if (window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
                return resolve(window.grecaptcha);
            }

            var existing = document.querySelector('script[src*="www.google.com/recaptcha/api.js"]');
            if (!existing) {
                var s = document.createElement('script');
                s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
                s.async = true; s.defer = true;
                s.onload = () => resolve(window.grecaptcha);
                s.onerror = () => reject(new Error('recaptcha-script-failed-to-load'));
                document.head.appendChild(s);
            }

            var waited = 0;
            var interval = 200;
            var iv = setInterval(function () {
                if (window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
                    clearInterval(iv);
                    resolve(window.grecaptcha);
                }
                waited += interval;
                if (waited >= timeoutMs) {
                    clearInterval(iv);
                    reject(new Error('grecaptcha-timeout'));
                }
            }, interval);
        });
    }

    // Form submission handler
    $(document).on('submit', '#cbc-ai-chat-panel .cbc-ai-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $panel = $('#cbc-ai-chat-panel');
        var $input = $form.find('.cbc-ai-input');
        var msg = ($input.val() || '').trim();
        if (!msg) return;

        var name = ($form.find('.cbc-ai-input-name').val() || '').trim();
        var email = ($form.find('.cbc-ai-input-email').val() || '').trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        var hasErrors = false;
        if (!name) {
            showFieldError($panel, 'Please enter your name.');
            hasErrors = true;
        }
        if (!email || !emailRegex.test(email)) {
            showFieldError($panel, 'Please enter a valid email address.');
            hasErrors = true;
        }
        if (hasErrors) return;

        addMsg($panel, 'user', msg);
        $input.val('');
        var $btn = $form.find('.cbc-ai-send');
        var oldBtnText = $btn.text();
        $btn.prop('disabled', true).text('Thinking…');

        var payload = {message: msg, name: name, email: email};

        function doSend(payloadToSend) {
            // Ensure CBCAI object and its properties exist
            if (typeof CBCAI === 'undefined' || !CBCAI.restUrl || !CBCAI.nonce) {
                addMsg($panel, 'bot', 'Configuration error. Cannot send message.');
                $btn.prop('disabled', false).text(oldBtnText);
                return;
            }

            fetch(CBCAI.restUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-WP-Nonce': CBCAI.nonce},
                body: JSON.stringify(payloadToSend)
            })
                .then(res => {
                    if (!res.ok) {
                        // Handle HTTP errors like 403, 500 etc.
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (data && data.reply) {
                        addMsg($panel, 'bot', data.reply);
                    } else if (data && data.error) {
                        addMsg($panel, 'bot', 'Error: ' + escapeHtml(data.error));
                    } else {
                        addMsg($panel, 'bot', 'Sorry, I could not generate a response right now.');
                    }
                    try {
                        localStorage.setItem('cbc_ai_user_name', name);
                        localStorage.setItem('cbc_ai_user_email', email);
                    } catch (err) {
                        console.error("Failed to save user info to localStorage:", err);
                    }
                    showUserInfo($panel, name, email);
                })
                .catch(function (err) {
                    console.error("Fetch error:", err);
                    addMsg($panel, 'bot', 'A network or server error occurred. Please try again.');
                })
                .finally(function () {
                    $btn.prop('disabled', false).text(oldBtnText);
                });
        }

        var siteKey = (typeof CBCAI !== 'undefined' && CBCAI.recaptchaSiteKey) ? CBCAI.recaptchaSiteKey : '';
        if (siteKey) {
            loadRecaptcha(siteKey, 9000).then(function (grecaptcha) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, {action: 'cbc_ai_ask'}).then(function (token) {
                        payload.recaptcha_token = token;
                        doSend(payload);
                    }).catch(function (err) {
                        console.error("reCAPTCHA execution failed:", err);
                        addMsg($panel, 'bot', 'Could not verify you are human. Please try again.');
                        $btn.prop('disabled', false).text(oldBtnText);
                    });
                });
            }).catch(function(err) {
                console.error("reCAPTCHA loading failed:", err);
                addMsg($panel, 'bot', 'Could not load security check. Please check your connection.');
                $btn.prop('disabled', false).text(oldBtnText);
            });
        } else {
            // Proceed without reCAPTCHA if no site key is provided
            doSend(payload);
        }
    });

    // Initialize everything when the DOM is ready
    $(document).ready(function () {
        initChatWidget();
        setupResizeHandler();
        restoreUserInfo();
    });

})(jQuery);