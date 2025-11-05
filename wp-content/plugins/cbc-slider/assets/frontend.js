(function () {
	function initSlider(root) {
		const viewport = root.querySelector('.cbc-slider__viewport');
		const track = root.querySelector('.cbc-slider__track');
		const slides = Array.from(root.querySelectorAll('.cbc-slider__slide'));
		const prevBtn = root.querySelector('.cbc-slider__prev');
		const nextBtn = root.querySelector('.cbc-slider__next');
		const dotsWrap = root.querySelector('.cbc-slider__dots');
		const autoplay = root.getAttribute('data-autoplay') === '1';
		const delay = parseInt(root.getAttribute('data-delay') || '5000', 10);
		const loop = root.getAttribute('data-loop') === '1';
		const pauseOnHover = root.getAttribute('data-pause') === '1';
		const showDots = root.getAttribute('data-dots') === '1';

		let index = 0;
		let timer = null;
		let isDragging = false;
		let startX = 0;
		let lastX = 0;
		let trackX = 0;

		function total() { return slides.length; }
		function clamp(i) { return Math.max(0, Math.min(i, total() - 1)); }
		function wrap(i) {
			if (loop) {
				if (i < 0) return total() - 1;
				if (i >= total()) return 0;
			}
			return clamp(i);
		}

		function getSlideWidth() {
			return viewport.getBoundingClientRect().width;
		}

		function updateTransform(animate = true) {
			const x = -index * getSlideWidth();
			track.style.transition = animate ? 'transform 320ms ease' : 'none';
			track.style.transform = 'translate3d(' + x + 'px,0,0)';
			updateAria();
			updateDots();
			handleVideoState();
		}

		function handleVideoState() {
			slides.forEach((s, i) => {
				const video = s.querySelector('video');
				if (!video) return;
				try {
					if (i === index) {
						if (video.autoplay || autoplay) video.play().catch(() => {});
					} else {
						video.pause();
					}
				} catch (e) {}
			});
		}

		function updateAria() {
			slides.forEach((s, i) => {
				if (i === index) {
					s.classList.add('is-active');
					s.setAttribute('aria-hidden', 'false');
				} else {
					s.classList.remove('is-active');
					s.setAttribute('aria-hidden', 'true');
				}
			});
		}

		function createDots() {
			if (!dotsWrap || !showDots) return;
			dotsWrap.innerHTML = '';
			for (let i = 0; i < total(); i++) {
				const b = document.createElement('button');
				b.type = 'button';
				b.className = 'cbc-slider__dot';
				b.setAttribute('role', 'tab');
				b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
				b.addEventListener('click', () => goTo(i));
				dotsWrap.appendChild(b);
			}
			updateDots();
		}

		function updateDots() {
			if (!dotsWrap || !showDots) return;
			Array.from(dotsWrap.children).forEach((b, i) => {
				if (i === index) {
					b.classList.add('is-active');
					b.setAttribute('aria-selected', 'true');
				} else {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				}
			});
		}

		function goTo(i) {
			index = wrap(i);
			updateTransform(true);
			restartAutoplay();
		}

		function next() { goTo(index + 1); }
		function prev() { goTo(index - 1); }

		function startAutoplay() {
			if (!autoplay || total() <= 1) return;
			stopAutoplay();
			timer = setInterval(() => {
				next();
			}, Math.max(1000, delay));
		}
		function stopAutoplay() { if (timer) { clearInterval(timer); timer = null; } }
		function restartAutoplay() { stopAutoplay(); startAutoplay(); }

		function onResize() { updateTransform(false); }

		function onKeydown(e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
			if (e.key === 'ArrowLeft') { e.preventDefault(); prev(); }
		}

		function onPointerDown(e) {
			isDragging = true;
			startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
			lastX = startX;
			track.style.transition = 'none';
			root.classList.add('is-dragging');
		}
		function onPointerMove(e) {
			if (!isDragging) return;
			const x = e.clientX || (e.touches && e.touches[0].clientX) || 0;
			lastX = x;
			const delta = x - startX;
			const base = -index * getSlideWidth();
			track.style.transform = 'translate3d(' + (base + delta) + 'px,0,0)';
		}
		function onPointerUp() {
			if (!isDragging) return;
			isDragging = false;
			root.classList.remove('is-dragging');
			const delta = lastX - startX;
			const threshold = Math.max(30, getSlideWidth() * 0.15);
			if (delta > threshold) { prev(); }
			else if (delta < -threshold) { next(); }
			else { updateTransform(true); }
		}

		// Wire events
		if (prevBtn) prevBtn.addEventListener('click', prev);
		if (nextBtn) nextBtn.addEventListener('click', next);
		viewport.addEventListener('keydown', onKeydown);
		window.addEventListener('resize', onResize);
		if (pauseOnHover) {
			root.addEventListener('mouseenter', stopAutoplay);
			root.addEventListener('mouseleave', startAutoplay);
		}
		// Touch/Mouse drag
		viewport.addEventListener('mousedown', onPointerDown);
		viewport.addEventListener('mousemove', onPointerMove);
		window.addEventListener('mouseup', onPointerUp);
		viewport.addEventListener('touchstart', onPointerDown, { passive: true });
		viewport.addEventListener('touchmove', onPointerMove, { passive: true });
		window.addEventListener('touchend', onPointerUp);

		createDots();
		updateTransform(false);
		startAutoplay();
	}

	document.addEventListener('DOMContentLoaded', function () {
		const sliders = document.querySelectorAll('.cbc-slider');
		sliders.forEach(initSlider);
	});
})();

