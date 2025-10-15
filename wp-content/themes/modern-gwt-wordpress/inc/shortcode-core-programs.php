<?php
/**
 * Core Programs Shortcode
 * Displays core programs as a grid on desktop and carousel on mobile
 *
 * Usage: [core_programs]
 */

if (!function_exists('cbc_core_programs_shortcode')) {
    function cbc_core_programs_shortcode($atts) {
        $atts = shortcode_atts(array(
            'auto_advance' => 'true',
            'auto_interval' => 3500,
            'show_arrows' => 'true',
            'mobile_breakpoint' => 768,
        ), $atts, 'core_programs');

        // Define core programs data
        $programs = array(
            array(
                'title' => 'Technology Development and Innovation',
                'image' => '/wp-content/uploads/2025/09/Screenshot-2025-09-05-150724-768x445.png',
                'description' => 'Conduct cutting-edge research ',
            ),
            array(
                'title' => 'R4D Biotechnology Capacity-Building Service',
                'image' => '/wp-content/uploads/2025/09/IMG_20240522_085745-768x576.jpg',
                'description' => 'Nationwide training and workshops',
            ),
            array(
                'title' => 'Partnership and Fund Generation',
                'image' => '/wp-content/uploads/2025/09/CBC04940-768x461.png',
                'description' => 'Collaborators for growth and impact',
            ),
            array(
                'title' => 'Technology Commercialization and Management',
                'image' => '/wp-content/uploads/2025/09/CBC06408-768x432.png',
                'description' => 'Bringing innovations to the Filipino people',
            ),
        );

        // Allow filtering of programs data
        $programs = apply_filters('cbc_core_programs_data', $programs);

        if (empty($programs)) {
            return '<p>No programs available.</p>';
        }

        ob_start();
        ?>
        <style>
        /* Grid layout for desktop */
        .core-programs-grid {
            display: none;
        }
        @media (min-width: <?php echo esc_attr($atts['mobile_breakpoint']); ?>px) {
            .core-programs-grid {
                display: grid;
            }
            .core-programs-carousel-wrapper {
                display: none;
            }
        }

        /* Carousel styles for mobile */
        .core-programs-carousel-wrapper {
            position: relative;
            width: 100%;
            overflow: visible;
            padding: 2rem 0;
        }
        .core-programs-carousel {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1300px;
            min-height: 300px;
            user-select: none;
            overflow: visible;
        }
        .core-programs-track {
            display: flex;
            gap: 0;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 100%;
            overflow: visible;
        }
        .core-programs-card {
            position: absolute;
	        display: flex;
	        flex-direction: column;
            left: 50%;
            top: 50%;
            overflow: hidden;
            border-radius: 0.375rem;
            box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.45);
            will-change: transform, opacity, filter;
            transition: transform 560ms cubic-bezier(.25, .9, .34, 1.31),
                        opacity 560ms ease-out,
                        filter 560ms ease-out,
                        box-shadow 560ms ease-out;
            background: #ffffff;
            transform-origin: center center;
            pointer-events: none;
        }
        .core-programs-card.active {
            box-shadow: 0 6px 28px -6px rgba(0, 0, 0, 0.65);
            pointer-events: auto;
        }
        .core-programs-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 55%, rgba(255, 255, 255, 0.10), rgba(0, 0, 0, 0.55));
            opacity: 0;
            transition: opacity 400ms ease;
            pointer-events: none;
            mix-blend-mode: overlay;
        }
        .core-programs-card.active::after {
            opacity: 0.55;
        }
        .core-programs-card-content {
            flex: 1;
            margin: -2.5rem 1rem 0 1rem;
            background: white;
            border-radius: 0.375rem 0.375rem 0 0;
            padding: 1rem;
            position: relative;
        }
        .core-programs-card-title {
            color: #1f5d2b;
            font-weight: 600;
            font-size: 1.125rem;
            text-align: center;
            line-height: 1.1rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            height: 3.3rem;
        }
        .core-programs-card-description {
            color: #1f2937;
            text-align: center;
            line-height: 1.25;
        }
        @media (min-width: 640px) {
            .core-programs-card-description {
                line-height: 1.625;
            }
        }
        .core-programs-arrows {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0.5rem;
            pointer-events: none;
            z-index: 100;
        }
        .core-programs-arrow {
            pointer-events: auto;
            padding: 0.5rem;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            transition: background 200ms ease;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .core-programs-arrow:hover {
            background: rgba(0, 0, 0, 0.6);
        }
        .core-programs-arrow:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }
        @media (prefers-reduced-motion: reduce) {
            .core-programs-card,
            .core-programs-track {
                transition: none !important;
                animation: none !important;
            }
        }
        @media (max-width: 640px) {
            .core-programs-carousel {
                min-height: 300px;
            }
            .core-programs-card-title {
                font-size: 0.875rem;
            }
        }
        </style>

        <div id="core_programs_list">
            <!-- Grid layout for desktop (md and above) -->
            <div class="core-programs-grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 py-6">
                <?php foreach ($programs as $program): ?>
                <div class="items-center bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-32">
                        <img src="<?php echo esc_url($program['image']); ?>"
                             alt="<?php echo esc_attr($program['title']); ?>"
                             class="absolute inset-0 w-full h-full object-cover object-center text-[#1f5d2b]" />
                    </div>
                    <div class="flex-1 bg-white p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            <?php echo esc_html(strtoupper($program['title'])); ?>
                        </h3>
                        <p class="hidden text-gray-800 text-center leading-tight md:leading-relaxed">
                            <?php echo esc_html($program['description']); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Carousel layout for mobile -->
            <div class="core-programs-carousel-wrapper"
                 data-auto-advance="<?php echo esc_attr($atts['auto_advance']); ?>"
                 data-auto-interval="<?php echo esc_attr($atts['auto_interval']); ?>"
                 data-show-arrows="<?php echo esc_attr($atts['show_arrows']); ?>">

                <div class="core-programs-carousel"
                     role="listbox"
                     aria-label="Core Programs Carousel"
                     tabindex="0">
                    <div class="core-programs-track">
                        <?php foreach ($programs as $index => $program): ?>
                        <div class="core-programs-card <?php echo $index === 0 ? 'active' : ''; ?>"
                             data-index="<?php echo $index; ?>"
                             data-name="<?php echo esc_attr($program['title']); ?>"
                             role="option"
                             aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">

                            <img src="<?php echo esc_url($program['image']); ?>"
                                 alt="<?php echo esc_attr($program['title']); ?>"
                                 loading="lazy">

                            <div class="core-programs-card-content">
                                <h3 class="core-programs-card-title">
                                    <?php echo esc_html(strtoupper($program['title'])); ?>
                                </h3>
                                <p class="hidden core-programs-card-description">
                                    <?php echo esc_html($program['description']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($atts['show_arrows'] === 'true'): ?>
                    <div class="core-programs-arrows">
                        <button type="button"
                                class="core-programs-arrow core-programs-prev"
                                aria-label="Previous program">
                            ‹
                        </button>
                        <button type="button"
                                class="core-programs-arrow core-programs-next"
                                aria-label="Next program">
                            ›
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const wrappers = document.querySelectorAll('.core-programs-carousel-wrapper');
                if (!wrappers.length) return;

                wrappers.forEach(wrapper => {
                    const carousel = wrapper.querySelector('.core-programs-carousel');
                    const items = Array.from(wrapper.querySelectorAll('.core-programs-card'));
                    const prevBtn = wrapper.querySelector('.core-programs-prev');
                    const nextBtn = wrapper.querySelector('.core-programs-next');

                    // Configurable dataset attributes (same pattern as first script)
                    const maxDisplay = parseInt(wrapper.dataset.maxDisplay) || 5;
                    const autoAdvance = wrapper.dataset.autoAdvance === 'true';
                    const autoInterval = parseInt(wrapper.dataset.autoInterval) || 3000;
                    const enableBlur = wrapper.dataset.enableBlur === 'true';
                    const centerScale = parseFloat(wrapper.dataset.centerScale) || 1.25;

                    let currentIndex = 0;
                    let autoTimer = null;
                    let isPaused = false;

                    const itemCount = items.length;
                    const centerIndex = Math.floor(maxDisplay / 2);

                    function updateCarousel() {
                        items.forEach((item, idx) => {
                            const visualIndex = (idx - currentIndex + itemCount) % itemCount;
                            const offset = visualIndex - centerIndex;
                            const absOffset = Math.abs(offset);

                            // Transform calculations (same logic as CBC carousel)
                            const baseScale = offset === 0
                                ? centerScale * 1.05
                                : (centerScale * 0.92) - absOffset * 0.35;
                            const scale = Math.max(0.45, baseScale);
                            const translateZ = -Math.min(absOffset * 60, 220);
                            const translateX = offset * 25; // vw units for spacing

                            const opacity = offset === 0 ? 1 : Math.max(0.15, 1 - (absOffset * 0.65));
                            const brightness = offset === 0 ? 1.05 : Math.max(0.2, 0.55 - (absOffset - 1) * 0.18);
                            const saturation = offset === 0 ? 1.2 : 0.5;
                            const blur = enableBlur && absOffset > 0 ? Math.min(absOffset * 2, 4) : 0;

                            // Apply transforms and effects
                            item.style.transform = `translate(-50%, -50%) translate3d(${translateX}vw, 0, ${translateZ}px) scale(${scale})`;
                            item.style.opacity = opacity;
                            item.style.filter = `brightness(${brightness}) saturate(${saturation}) blur(${blur}px)`;
                            item.style.zIndex = 400 - absOffset * 12;

                            item.classList.toggle('active', offset === 0);
                            item.setAttribute('aria-selected', offset === 0 ? 'true' : 'false');

                            // Show only items within maxDisplay range
                            item.style.display = visualIndex < maxDisplay ? 'flex' : 'none';
                        });
                    }

                    function next() {
                        currentIndex = (currentIndex + 1) % itemCount;
                        updateCarousel();
                    }

                    function prev() {
                        currentIndex = (currentIndex - 1 + itemCount) % itemCount;
                        updateCarousel();
                    }

                    function startAuto() {
                        if (!autoAdvance || autoTimer) return;
                        autoTimer = setInterval(() => {
                            if (!isPaused) next();
                        }, autoInterval);
                    }

                    function stopAuto() {
                        if (autoTimer) {
                            clearInterval(autoTimer);
                            autoTimer = null;
                        }
                    }

                    function pause() {
                        isPaused = true;
                        stopAuto();
                    }

                    function resume() {
                        isPaused = false;
                        startAuto();
                    }

                    // Hover pause
                    carousel.addEventListener('mouseenter', pause);
                    carousel.addEventListener('mouseleave', resume);

                    // Keyboard navigation
                    carousel.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowLeft') {
                            e.preventDefault(); pause(); prev();
                        } else if (e.key === 'ArrowRight') {
                            e.preventDefault(); pause(); next();
                        }
                    });

                    // Touch/swipe
                    let touchStartX = 0, touchEndX = 0;
                    carousel.addEventListener('touchstart', (e) => {
                        touchStartX = e.touches[0].clientX;
                        pause();
                    }, { passive: true });

                    carousel.addEventListener('touchend', (e) => {
                        touchEndX = e.changedTouches[0].clientX;
                        const diff = touchStartX - touchEndX;
                        if (Math.abs(diff) > 50) {
                            diff > 0 ? next() : prev();
                        }
                    }, { passive: true });

                    // Mouse wheel
                    carousel.addEventListener('wheel', (e) => {
                        e.preventDefault();
                        pause();
                        if (e.deltaY < 0) next();
                        else prev();
                    }, { passive: false });

                    // Initialize
                    updateCarousel();
                    if (autoAdvance) startAuto();
                });
            })();

        </script>
        <?php
        return ob_get_clean();
    }

    add_shortcode('core_programs', 'cbc_core_programs_shortcode');
}

