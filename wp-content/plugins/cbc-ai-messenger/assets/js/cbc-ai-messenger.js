(function ($) {
    const CBCAIChat = {
        elements: {},
        isMobile: function () {
            try {
                return window.innerWidth <= 768;
            } catch (e) {
                return false;
            }
        },

        ui: {
            addMsg: function (who, text) {
                const { $log, $convoLabel } = CBCAIChat.elements;
                const $div = $('<div/>').addClass('cbc-ai-msg ' + (who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
                if (who === 'user') {
                    $div.text(text);
                } else {
                    $div.html(text);
                }
                $log.append($div);
                $log.scrollTop($log[0].scrollHeight);
                $log.removeClass('hidden').addClass('block flex');
                $convoLabel.removeClass('hidden').addClass('block');

                CBCAIChat.history.append({ who: who, text: text });
            },

            escapeHtml: function (str) {
                return String(str).replace(/[&<>"']/g, function (s) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;' }[s]);
                });
            },

            updateIcons: function (isCollapsed) {
                const toggle = CBCAIChat.elements.toggle;
                if (!toggle) return;
                const arrow = toggle.querySelector('.cbc-ai-icon-expanded');
                const robot = toggle.querySelector('.cbc-ai-icon-collapsed');

                if (isCollapsed) {
                    if (arrow) arrow.classList.add('hidden');
                    if (robot) robot.classList.remove('hidden');
                } else {
                    if (robot) robot.classList.add('hidden');
                    if (arrow) arrow.classList.remove('hidden');
                }
            },

            showFieldError: function (message) {
                const { $contactFields } = CBCAIChat.elements;
                if (!$contactFields.length) return;
                const $err = $('<div/>').addClass('cbc-ai-field-error text-sm text-red-600 mt-1').text(message);
                $contactFields.append($err);
                setTimeout(function () {
                    $err.fadeOut(180, function () { $err.remove(); });
                }, 2800);
            },

            mobile: {
                ensureBackdrop: function () {
                    if ($('#cbc-ai-backdrop').length) {
                        window.requestAnimationFrame(function () {
                            $('#cbc-ai-backdrop').addClass('is-visible');
                        });
                        return;
                    }
                    $('body').append('<div id="cbc-ai-backdrop" class="fixed inset-0 bg-black/50 z-[9998]"></div>');
                    window.requestAnimationFrame(function () {
                        $('#cbc-ai-backdrop').addClass('is-visible');
                    });
                },
                removeBackdrop: function () {
                    const $backdrop = $('#cbc-ai-backdrop');
                    if (!$backdrop.length) return;
                    $backdrop.removeClass('is-visible');
                    setTimeout(function () {
                        $('#cbc-ai-backdrop').remove();
                    }, 240);
                },
                applyFullscreen: function () {
                    const { container } = CBCAIChat.elements;
                    if (!container.classList.contains('cbc-ai-mobile-open')) {
                        container.classList.add('cbc-ai-mobile-open');
                        this.ensureBackdrop();
                        try { document.body.classList.add('cbc-ai-scroll-locked'); } catch (e) { }
                    }
                },
                removeFullscreen: function () {
                    const { container } = CBCAIChat.elements;
                    if (container.classList.contains('cbc-ai-mobile-open')) {
                        container.classList.remove('cbc-ai-mobile-open');
                        this.removeBackdrop();
                        try { document.body.classList.remove('cbc-ai-scroll-locked'); } catch (e) { }
                    }
                }
            }
        },

        history: {
            key: 'cbc_ai_chat_history',
            get: function () {
                try {
                    const stored = localStorage.getItem(this.key);
                    return stored ? JSON.parse(stored) : [];
                } catch (e) { return []; }
            },
            save: function (history) {
                try { localStorage.setItem(this.key, JSON.stringify(history)); } catch (e) { }
            },
            append: function (entry) {
                const history = this.get();
                history.push(entry);
                this.save(history);
            },
            clear: function () {
                try { localStorage.removeItem(this.key); } catch (e) { }
                const { $log, $convoLabel } = CBCAIChat.elements;
                $log.empty().addClass('hidden');
                $convoLabel.addClass('hidden');
            },
            restore: function () {
                const history = this.get();
                const { $log, $convoLabel } = CBCAIChat.elements;
                if (history.length > 0) {
                    history.forEach(item => {
                        const $div = $('<div/>').addClass('cbc-ai-msg ' + (item.who === 'user' ? 'cbc-ai-user' : 'cbc-ai-bot'));
                        if (item.who === 'user') {
                            $div.text(item.text);
                        } else {
                            $div.html(item.text);
                        }
                        $log.append($div);
                    });
                    $log.removeClass('hidden').addClass('block flex');
                    $convoLabel.removeClass('hidden').addClass('block');
                    $log.scrollTop($log[0].scrollHeight);
                }
            }
        },

        user: {
            nameKey: 'cbc_ai_user_name',
            emailKey: 'cbc_ai_user_email',
            getInfo: function () {
                try {
                    return {
                        name: localStorage.getItem(this.nameKey),
                        email: localStorage.getItem(this.emailKey)
                    };
                } catch (e) { return { name: null, email: null }; }
            },
            saveInfo: function (name, email) {
                try {
                    localStorage.setItem(this.nameKey, name);
                    localStorage.setItem(this.emailKey, email);
                } catch (e) { }
            },
            clearInfo: function () {
                try {
                    localStorage.removeItem(this.nameKey);
                    localStorage.removeItem(this.emailKey);
                } catch (e) { }
            },
            showInfo: function (name, email) {
                const { $userInfo, $contactFields } = CBCAIChat.elements;
                const html = `
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <strong>${CBCAIChat.ui.escapeHtml(name)}</strong>
                            <span class="text-xs text-gray-600">&lt;${CBCAIChat.ui.escapeHtml(email)}&gt;</span>
                        </div>
                        <button type="button" class="cbc-ai-edit-user text-xs text-blue-600 underline">Change</button>
                    </div>`;
                $userInfo.html(html).removeClass('hidden');
                $contactFields.addClass('hidden');
            },
            hideInfo: function () {
                const { $userInfo, $contactFields } = CBCAIChat.elements;
                $userInfo.addClass('hidden').empty();
                $contactFields.removeClass('hidden');
            },
            restoreInfo: function () {
                const { name, email } = this.getInfo();
                const { $nameInput, $emailInput } = CBCAIChat.elements;
                if (name && email) {
                    $nameInput.val(name);
                    $emailInput.val(email);
                    this.showInfo(name, email);
                }
            }
        },

        initToggle: function () {
            const { container, toggle } = this.elements;
            const stateKey = 'cbc_ai_chat_collapsed';

            try {
                const legacy = localStorage.getItem('cbc_ai_open');
                if (legacy !== null && localStorage.getItem(stateKey) === null) {
                    localStorage.setItem(stateKey, legacy === '1' ? '0' : '1');
                }
            } catch (e) { }

            let collapsed = false;
            try {
                collapsed = localStorage.getItem(stateKey) === '1';
            } catch (e) { }

            if (collapsed) {
                container.classList.add('collapsed');
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                if (this.isMobile()) {
                    this.ui.mobile.applyFullscreen();
                }
            }
            this.ui.updateIcons(collapsed);
        },

        initEventHandlers: function () {
            const { toggle, container, panel } = this.elements;

            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                container.classList.toggle('collapsed');
                const isCollapsed = container.classList.contains('collapsed');
                const isMobileView = this.isMobile();
                try {
                    localStorage.setItem('cbc_ai_chat_collapsed', isCollapsed ? '1' : '0');
                } catch (e) { }
                toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                this.ui.updateIcons(isCollapsed);

                if (isCollapsed) {
                    if (isMobileView && container.classList.contains('cbc-ai-mobile-open')) {
                        container.classList.add('cbc-ai-mobile-closing');
                        setTimeout(() => {
                            this.ui.mobile.removeFullscreen();
                            container.classList.remove('cbc-ai-mobile-closing');
                        }, 240);
                    } else {
                        this.ui.mobile.removeFullscreen();
                    }
                } else if (isMobileView) {
                    this.ui.mobile.applyFullscreen();
                }
            });

            document.addEventListener('click', (e) => {
                if (e.target.id === 'cbc-ai-backdrop' && !container.classList.contains('collapsed')) {
                    toggle.click();
                }
            });

            panel.addEventListener('click', (e) => {
                if (e.target.closest('.cbc-ai-clear-history')) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to clear the conversation history?')) {
                        this.history.clear();
                    }
                }
                if (e.target.closest('.cbc-ai-edit-user')) {
                    this.user.clearInfo();
                    this.user.hideInfo();
                }
            });

            this.elements.$form.on('submit', (e) => {
                e.preventDefault();
                const { $input, $nameInput, $emailInput } = this.elements;
                const msg = ($input.val() || '').trim();
                if (!msg) return;

                const name = ($nameInput.val() || '').trim();
                const email = ($emailInput.val() || '').trim();
                const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
                let invalid = false;
                if (!name) {
                    this.ui.showFieldError('Please enter your name.');
                    invalid = true;
                }
                if (!email || !emailRegex.test(email)) {
                    this.ui.showFieldError('Please enter a valid email address.');
                    invalid = true;
                }
                if (invalid) return;

                this.ui.addMsg('user', msg);
                $input.val('');
                const $btn = this.elements.$form.find('.cbc-ai-send');
                const oldText = $btn.text();
                $btn.prop('disabled', true).text('Thinking…');

                fetch(CBCAI.restUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CBCAI.nonce },
                    body: JSON.stringify({ message: msg, name: name, email: email })
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.reply) {
                        this.ui.addMsg('bot', data.reply);
                    } else if (data && data.error) {
                        this.ui.addMsg('bot', 'Error: ' + data.error);
                    } else {
                        this.ui.addMsg('bot', 'Sorry, I could not generate a response right now.');
                    }
                    this.user.saveInfo(name, email);
                    this.user.showInfo(name, email);
                })
                .catch(() => {
                    this.ui.addMsg('bot', 'Network error. Please try again.');
                })
                .finally(() => {
                    $btn.prop('disabled', false).text(oldText);
                });
            });

            let resizeTimeout;
            $(window).on('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if (container.classList.contains('collapsed')) {
                        this.ui.mobile.removeFullscreen();
                        return;
                    }
                    if (this.isMobile()) {
                        this.ui.mobile.applyFullscreen();
                    } else {
                        this.ui.mobile.removeFullscreen();
                    }
                }, 160);
            });
        },

        init: function () {
            this.elements = {
                container: document.getElementById('cbc-ai-chat-container'),
                toggle: document.getElementById('cbc-ai-chat-toggle'),
                panel: document.getElementById('cbc-ai-chat-panel'),
                $panel: $('#cbc-ai-chat-panel'), // for jQuery operations
                $form: $('#cbc-ai-chat-panel .cbc-ai-form'),
                $input: $('.cbc-ai-input'),
                $log: $('.cbc-ai-log'),
                $convoLabel: $('.cbc-ai-convo-label'),
                $contactFields: $('.cbc-ai-contact-fields'),
                $userInfo: $('.cbc-ai-user-info'),
                $nameInput: $('.cbc-ai-input-name'),
                $emailInput: $('.cbc-ai-input-email'),
            };

            if (!this.elements.container) return;

            this.initToggle();
            this.initEventHandlers();
            this.history.restore();
            this.user.restoreInfo();
        },
    };

    $(function () {
        CBCAIChat.init();
    });

})(jQuery);
