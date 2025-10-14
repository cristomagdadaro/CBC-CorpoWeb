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
                'description' => 'We conduct and host cutting-edge crop biotechnology research and development (R&D) activities, including joint research projects, forming dedicated research teams, and implementing impactful research programs.',
            ),
            array(
                'title' => 'R4D Biotechnology Capacity-Building Service',
                'image' => '/wp-content/uploads/2025/09/IMG_20240522_085745-768x576.jpg',
                'description' => 'We provide essential R&D-related services and training to DA agencies and our network. Our goal is to equip stakeholders with the skills to effectively apply modern crop biotechnology tools.',
            ),
            array(
                'title' => 'Partnership and Fund Generation',
                'image' => '/wp-content/uploads/2025/09/CBC04940-768x461.png',
                'description' => 'We actively build and strengthen our connections within the R&D network. We also secure funding for projects from both local and international institutions, as well as public and private donors.',
            ),
            array(
                'title' => 'Technology Commercialization and Management',
                'image' => '/wp-content/uploads/2025/09/CBC06408-768x432.png',
                'description' => 'We promote the commercialization and transfer of developed technologies. We also foster a culture of knowledge-sharing to ensure our network and stakeholders have easy access to a wealth of information.',
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
            overflow: hidden;
            padding: 1rem 0;
        }
        .core-programs-carousel {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1300px;
            min-height: 450px;
            user-select: none;
            overflow: hidden;
        }
        .core-programs-track {
            display: flex;
            gap: 0;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 100%;
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
            background: white;
            transform-origin: center center;
            width: 85vw;
            max-width: 20rem;
        }
        .core-programs-card.active {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }
        .core-programs-card-image {
            position: relative;
            width: 100%;
            height: 12rem;
        }
        .core-programs-card-image img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .core-programs-card-content {
            flex: 1;
            margin: -2.5rem 1rem 0 1rem;
            background: white;
            border-radius: 0.375rem;
            padding: 1rem;
            position: relative;
        }
        .core-programs-card-title {
            color: #1f5d2b;
            font-weight: 800;
            font-size: 1.125rem;
            text-align: center;
            margin-bottom: 1rem;
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
        </style>

        <div id="core_programs_list">
            <!-- Grid layout for desktop (md and above) -->
            <div class="core-programs-grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 pb-5">
                <?php foreach ($programs as $program): ?>
                <div class="bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-48">
                        <img src="<?php echo esc_url($program['image']); ?>"
                             alt="<?php echo esc_attr($program['title']); ?>"
                             class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 -mt-10 mx-4 bg-white rounded-md p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            <?php echo esc_html(strtoupper($program['title'])); ?>
                        </h3>
                        <p class="text-gray-800 text-center leading-tight md:leading-relaxed">
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
                             role="option"
                             aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">

                            <div class="core-programs-card-image">
                                <img src="<?php echo esc_url($program['image']); ?>"
                                     alt="<?php echo esc_attr($program['title']); ?>"
                                     loading="lazy">
                            </div>

                            <div class="core-programs-card-content">
                                <h3 class="core-programs-card-title">
                                    <?php echo esc_html(strtoupper($program['title'])); ?>
                                </h3>
                                <p class="core-programs-card-description">
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
        (function() {
            const wrapper = document.querySelector('.core-programs-carousel-wrapper');
            if (!wrapper) return;

            const carousel = wrapper.querySelector('.core-programs-carousel');
            const items = Array.from(wrapper.querySelectorAll('.core-programs-card'));
            const prevBtn = wrapper.querySelector('.core-programs-prev');
            const nextBtn = wrapper.querySelector('.core-programs-next');

            const autoAdvance = wrapper.dataset.autoAdvance === 'true';
            const autoInterval = parseInt(wrapper.dataset.autoInterval) || 3500;

            let currentIndex = 0;
            let autoTimer = null;
            let isPaused = false;

            const itemCount = items.length;
            const maxDisplay = 3; // Show 3 items at a time on mobile
            const centerIndex = 1; // Center item

            function updateCarousel() {
                items.forEach((item, idx) => {
                    const visualIndex = (idx - currentIndex + itemCount) % itemCount;
                    const offset = visualIndex - centerIndex;
                    const absOffset = Math.abs(offset);

                    // Calculate transforms
                    const scale = offset === 0 ? 1.0 : Math.max(0.7, 0.85 - absOffset * 0.15);
                    const translateZ = -Math.min(absOffset * 80, 180);
                    const translateX = offset * 90; // Horizontal spacing in vw

                    const opacity = offset === 0 ? 1 : Math.max(0.3, 0.6 - absOffset * 0.2);
                    const brightness = offset === 0 ? 1 : Math.max(0.6, 0.8 - absOffset * 0.1);

                    // Transform with proper centering
                    item.style.transform = `translate(-50%, -50%) translate3d(${translateX}vw, 0, ${translateZ}px) scale(${scale})`;
                    item.style.opacity = opacity;
                    item.style.filter = `brightness(${brightness})`;
                    item.style.zIndex = 100 - absOffset * 10;

                    item.classList.toggle('active', offset === 0);
                    item.setAttribute('aria-selected', offset === 0 ? 'true' : 'false');

                    // Show only items within maxDisplay range
                    if (visualIndex < maxDisplay) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
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

            // Event listeners
            if (prevBtn) prevBtn.addEventListener('click', () => { pause(); prev(); });
            if (nextBtn) nextBtn.addEventListener('click', () => { pause(); next(); });

            carousel.addEventListener('mouseenter', pause);
            carousel.addEventListener('mouseleave', resume);

            // Keyboard navigation
            carousel.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    pause();
                    prev();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    pause();
                    next();
                }
            });

            // Touch/swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            carousel.addEventListener('touchstart', (e) => {
                touchStartX = e.touches[0].clientX;
                pause();
            }, { passive: true });

            carousel.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].clientX;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        next();
                    } else {
                        prev();
                    }
                }
            }, { passive: true });

            // Mouse wheel
            carousel.addEventListener('wheel', (e) => {
                e.preventDefault();
                pause();
                if (e.deltaY < 0) {
                    next();
                } else {
                    prev();
                }
            }, { passive: false });

            // Initialize
            updateCarousel();
            if (autoAdvance) startAuto();
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    add_shortcode('core_programs', 'cbc_core_programs_shortcode');
}

