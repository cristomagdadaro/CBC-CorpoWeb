(function (_$) {
    // Lightweight DOM helpers without shadowing jQuery
    function qs(sel, ctx) {
        return (ctx || document).querySelector(sel);
    }

    function qsa(sel, ctx) {
        return Array.from((ctx || document).querySelectorAll(sel));
    }

    // Escape helpers for safe string interpolation
    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escAttr(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escTextarea(str) {
        // HTML-escape and also neutralize closing textarea tag
        return escHtml(str).replace(/<\/textarea>/gi, '&lt;/textarea&gt;');
    }

    function ensureStateShape(state) {
        const defaults = {
            slides: [],
            options: {
                autoplay: true,
                delay: 5000,
                loop: true,
                pauseOnHover: true,
                showArrows: true,
                showDots: true,
                objectFit: 'cover',
                aspectRatio: '16/9',
                height: '',
                heightSm: '',
                heightMd: '',
                heightLg: '',
                heightXl: '',
                videoMuted: true,
                videoLoop: false,
                videoControls: false,
            }
        };
        return Object.assign({}, defaults, state || {});
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('cbc-slider-admin-root');
        if (!root) return;
        const hidden = document.getElementById('cbc_slider_json');
        let state = ensureStateShape(JSON.parse(root.getAttribute('data-state') || '{}'));

        function saveState() {
            hidden.value = JSON.stringify(state);
        }

        function setOption(key, value) {
            state.options = Object.assign({}, state.options, {[key]: value});
            saveState();
        }

        function updateSlide(idx, patch) {
            const copy = state.slides.slice();
            copy[idx] = Object.assign({}, copy[idx] || {}, patch);
            state.slides = copy;
            saveState();
            render();
        }

        function removeSlide(idx) {
            state.slides.splice(idx, 1);
            saveState();
            render();
        }

        function moveSlide(idx, dir) {
            const to = idx + dir;
            if (to < 0 || to >= state.slides.length) return;
            const arr = state.slides;
            const tmp = arr[idx];
            arr[idx] = arr[to];
            arr[to] = tmp;
            saveState();
            render();
        }

        function mediaFrame(options) {
            return wp.media({
                title: options.title,
                button: {text: options.button || 'Select'},
                multiple: !!options.multiple,
                library: {type: options.types || undefined},
            });
        }

        function addFromMedia(types, multiple, typeOverride) {
            const frame = mediaFrame({title: 'Select media', button: 'Add', multiple, types});
            frame.on('select', function () {
                const selection = frame.state().get('selection');
                const items = [];
                selection.each(function (m) {
                    const media = m.toJSON();
                    const type = typeOverride || (media.type === 'video' ? 'video' : 'image');
                    const slide = {
                        type,
                        id: media.id || 0,
                        url: media.url || '',
                        alt: media.alt || '',
                        overlayHtml: '',
                        linkUrl: '',
                        linkTargetBlank: false
                    };
                    if (type === 'video') {
                        slide.posterId = 0;
                        slide.posterUrl = '';
                    }
                    items.push(slide);
                });
                state.slides = state.slides.concat(items);
                saveState();
                render();
            });
            frame.open();
        }

        function selectPoster(idx) {
            const frame = mediaFrame({
                title: 'Select poster image',
                button: 'Use poster',
                multiple: false,
                types: 'image'
            });
            frame.on('select', function () {
                const media = frame.state().get('selection').first().toJSON();
                updateSlide(idx, {posterId: media.id || 0, posterUrl: media.url || ''});
            });
            frame.open();
        }

        function replaceMedia(idx, types) {
            const frame = mediaFrame({title: 'Replace media', button: 'Replace', multiple: false, types});
            frame.on('select', function () {
                const media = frame.state().get('selection').first().toJSON();
                const type = media.type === 'video' ? 'video' : 'image';
                const patch = {type, id: media.id || 0, url: media.url || ''};
                if (type === 'image') {
                    patch.alt = media.alt || '';
                    patch.posterId = 0;
                    patch.posterUrl = '';
                }
                updateSlide(idx, patch);
            });
            frame.open();
        }

        function renderOptions() {
            const o = state.options;
            return `
				<div class="cbc-meta__section">
					<h3>Behavior</h3>
					<label><input type="checkbox" data-opt="autoplay" ${o.autoplay ? 'checked' : ''}/> Autoplay</label>
					<label>Delay (ms) <input type="number" min="1000" step="500" data-opt="delay" value="${escAttr(o.delay)}"></label>
					<label><input type="checkbox" data-opt="loop" ${o.loop ? 'checked' : ''}/> Loop</label>
					<label><input type="checkbox" data-opt="pauseOnHover" ${o.pauseOnHover ? 'checked' : ''}/> Pause on hover</label>
				</div>
				<div class="cbc-meta__section">
					<h3>Appearance</h3>
					<label>Object-fit
						<select data-opt="objectFit">
							${['cover', 'contain', 'fill', 'none', 'scale-down'].map(v => `<option value="${v}" ${o.objectFit === v ? 'selected' : ''}>${v}</option>`).join('')}
						</select>
					</label>
					<label>Aspect ratio
						<select data-opt="aspectRatio">
							${['16/9', '4/3', '1/1', '21/9', ''].map(v => `<option value="${v}" ${o.aspectRatio === v ? 'selected' : ''}>${v || 'Custom height(s)'}</option>`).join('')}
						</select>
					</label>
					<label>Base height <input type="text" placeholder="e.g., 480px or 60vh" data-opt="height" value="${escAttr(o.height || '')}"></label>
					<label>Small (≥640px) height <input type="text" data-opt="heightSm" value="${escAttr(o.heightSm || '')}"></label>
					<label>Medium (≥768px) height <input type="text" data-opt="heightMd" value="${escAttr(o.heightMd || '')}"></label>
					<label>Large (≥1024px) height <input type="text" data-opt="heightLg" value="${escAttr(o.heightLg || '')}"></label>
					<label>XL (≥1280px) height <input type="text" data-opt="heightXl" value="${escAttr(o.heightXl || '')}"></label>
					<label><input type="checkbox" data-opt="showArrows" ${o.showArrows ? 'checked' : ''}/> Show arrows</label>
					<label><input type="checkbox" data-opt="showDots" ${o.showDots ? 'checked' : ''}/> Show dots</label>
				</div>
				<div class="cbc-meta__section">
					<h3>Video</h3>
					<label><input type="checkbox" data-opt="videoMuted" ${o.videoMuted ? 'checked' : ''}/> Muted</label>
					<label><input type="checkbox" data-opt="videoLoop" ${o.videoLoop ? 'checked' : ''}/> Loop</label>
					<label><input type="checkbox" data-opt="videoControls" ${o.videoControls ? 'checked' : ''}/> Show native controls</label>
				</div>
			`;
        }

        function openOverlayEditor(idx, initialHtml) {
            const id = 'cbc_overlay_editor_' + idx;
            // Build modal DOM
            const modal = document.createElement('div');
            modal.className = 'cbc-modal';
            modal.innerHTML = `
				<div class="cbc-modal__backdrop"></div>
				<div class="cbc-modal__content" role="dialog" aria-modal="true" aria-labelledby="cbc-overlay-title-${idx}">
					<div class="cbc-modal__header">
						<h2 id="cbc-overlay-title-${idx}">Overlay editor</h2>
					</div>
					<div class="cbc-modal__body">
						<div id="wp-${id}-wrap" class="wp-core-ui wp-editor-wrap tmce-active">
							<div id="wp-${id}-editor-tools" class="wp-editor-tools hide-if-no-js">
								<div id="wp-${id}-media-buttons" class="wp-media-buttons">
									<button type="button" id="insert-media-button-${id}" class="button insert-media add_media" data-editor="${id}">
										<span class="wp-media-buttons-icon"></span> Add Media
									</button>
								</div>
							</div>
							<div id="wp-${id}-editor-container" class="wp-editor-container">
								<textarea id="${id}" class="wp-editor-area" rows="12"></textarea>
							</div>
						</div>
					</div>
					<div class="cbc-modal__footer">
						<button type="button" class="button button-primary" data-action="save">Save</button>
						<button type="button" class="button" data-action="cancel">Cancel</button>
					</div>
				</div>
			`;
            document.body.appendChild(modal);

            function destroy() {
                try {
                    if (window.wp && wp.editor && wp.editor.remove) {
                        wp.editor.remove(id);
                    }
                } catch (e) {
                }
                modal.parentNode && modal.parentNode.removeChild(modal);
            }

            // Initialize editor
            if (window.wp && wp.editor && wp.editor.initialize) {
                wp.editor.initialize(id, {
                    quicktags: true,
                    tinymce: {
                        wpautop: false,
                        branding: false,
                        toolbar1: 'formatselect,bold,italic,underline,link,unlink,alignleft,aligncenter,alignright,bullist,numlist,removeformat,code',
                        toolbar2: '',
                    },
                });
            }

            // Set initial content when TinyMCE is ready, else fallback to textarea
            (function waitForMCE(attempt) {
                attempt = attempt || 0;
                const ed = window.tinymce && tinymce.get(id);
                if (ed) {
                    ed.setContent(initialHtml || '');
                } else if (attempt < 20) {
                    setTimeout(function () {
                        waitForMCE(attempt + 1);
                    }, 100);
                } else {
                    const ta = document.getElementById(id);
                    if (ta) ta.value = initialHtml || '';
                }
            })();

            // Wire buttons
            modal.querySelector('[data-action="cancel"]').addEventListener('click', destroy);
            modal.querySelector('.cbc-modal__backdrop').addEventListener('click', destroy);
            modal.querySelector('[data-action="save"]').addEventListener('click', function () {
                let content = '';
                const ed = window.tinymce && tinymce.get(id);
                if (ed && !ed.isHidden()) {
                    content = ed.getContent();
                } else {
                    const ta = document.getElementById(id);
                    content = ta ? ta.value : '';
                }
                updateSlide(idx, {overlayHtml: content});
                destroy();
            });
        }

        function renderSlide(slide, idx) {
            const isVideo = slide.type === 'video';
            const thumb = isVideo ? (slide.posterUrl || slide.url) : slide.url;
            return `
				<div class="cbc-slide" data-idx="${idx}">
					<div class="cbc-slide__thumb">${thumb ? `<img src="${escAttr(thumb)}" alt="">` : '<div class="cbc-slide__thumb--empty">No media</div>'}</div>
					<div class="cbc-slide__form">
						<div class="cbc-row">
							<button type="button" class="button" data-action="replace" data-types="${isVideo ? 'image,video' : 'image,video'}">Replace Media</button>
							<button type="button" class="button" data-action="move" data-dir="-1">Move Up</button>
							<button type="button" class="button" data-action="move" data-dir="1">Move Down</button>
							<button type="button" class="button button-link-delete" data-action="remove">Remove</button>
						</div>
						<div class="cbc-row">
                            ${isVideo ? `
                                <label>Video URL <input type="text" data-field="url" value="${escAttr(slide.url || '')}" placeholder="Leave empty if selected from library"></label>
                                <div class="cbc-row">
                                    <button type="button" class="button" data-action="poster">Select Thumbnail</button>
                                    ${slide.posterUrl ? `<span class="cbc-note">Thumbnail already set</span>` : ''}
                                </div>
                            ` : `
                                <label>Alt text <input type="text" data-field="alt" value="${escAttr(slide.alt || '')}"></label>
                            `}
						</div>
						<div class="cbc-row">
							<button type="button" class="button" data-action="open-visual">Open Overlay HTML</button>
						</div>
						<div class="cbc-row">
							<div class="cbc-row">
                                <label for="slider-link-url-${idx}" style="flex:1">Link URL</label>
                                <input id="slider-link-url-${idx}" type="text" data-field="linkUrl" value="${escAttr(slide.linkUrl || '')}" placeholder="https://...">
                            </div>
                            <div class="cbc-row">
                                <label for="slider-open-new-tab-${idx}">Open in new tab</label>
							    <input id="slider-open-new-tab-${idx}" type="checkbox" data-field="linkTargetBlank" ${slide.linkTargetBlank ? 'checked' : ''}/> 
                            </div>
							
						</div>
					</div>
				</div>
			`;
        }

        function render() {
            root.innerHTML = `
				<div class="cbc-admin">
					<div class="cbc-admin__sidebar">${renderOptions()}</div>
					<div class="cbc-admin__main">
						<div class="cbc-toolbar">
							<button type="button" class="button button-primary" data-action="add-images">Add Image(s)</button>
							<button type="button" class="button" data-action="add-videos">Add Video(s)</button>
						</div>
						<div class="cbc-slides">
							${state.slides.length ? state.slides.map(renderSlide).join('') : '<div class="cbc-empty">No slides yet. Use the buttons above to add media.</div>'}
						</div>
					</div>
				</div>
			`;

            // Wire option change handlers
            qsa('[data-opt]', root).forEach(function (el) {
                el.addEventListener('change', function () {
                    const key = el.getAttribute('data-opt');
                    let value = el.type === 'checkbox' ? !!el.checked : el.value;
                    if (key === 'delay') value = parseInt(value || '0', 10) || 0;
                    setOption(key, value);
                });
            });

            // Toolbar buttons
            const addImagesBtn = root.querySelector('[data-action="add-images"]');
            if (addImagesBtn) addImagesBtn.addEventListener('click', function () {
                addFromMedia('image', true, 'image');
            });
            const addVideosBtn = root.querySelector('[data-action="add-videos"]');
            if (addVideosBtn) addVideosBtn.addEventListener('click', function () {
                addFromMedia('video', true, 'video');
            });

            // Slide controls
            qsa('.cbc-slide', root).forEach(function (card) {
                const idx = parseInt(card.getAttribute('data-idx'), 10);
                qsa('[data-field]', card).forEach(function (el) {
                    el.addEventListener('change', function () {
                        const key = el.getAttribute('data-field');
                        const patch = {};
                        if (el.type === 'checkbox') {
                            patch[key] = !!el.checked;
                        } else {
                            patch[key] = el.value;
                        }
                        updateSlide(idx, patch);
                    });
                });

                qsa('[data-action]', card).forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const action = btn.getAttribute('data-action');
                        if (action === 'replace') return replaceMedia(idx, btn.getAttribute('data-types').split(','));
                        if (action === 'remove') return removeSlide(idx);
                        if (action === 'move') return moveSlide(idx, parseInt(btn.getAttribute('data-dir'), 10));
                        if (action === 'poster') return selectPoster(idx);
                        if (action === 'open-visual') {
                            const current = (state.slides && state.slides[idx]) ? state.slides[idx].overlayHtml : '';
                            return openOverlayEditor(idx, current || '');
                        }
                    });
                });
            });
        }

        // initial
        saveState();
        render();
    });
})(window.jQuery);