<?php
/**
 * Priority Commodities Carousel Shortcode
 * Converts Vue AdvancedCarousel component to WordPress shortcode
 *
 * Usage: [priority_commodities_carousel]
 */

if (!function_exists('cbc_priority_commodities_carousel_shortcode')) {
    function cbc_priority_commodities_carousel_shortcode($atts) {
        $atts = shortcode_atts(array(
            'max_display' => 5,
            'center_scale' => 1.30,
            'auto_advance' => 'true',
            'auto_interval' => 2500,
            'show_arrows' => 'true',
            'enable_blur' => 'true',
            'vertical_breakpoint' => 640,
        ), $atts, 'priority_commodities_carousel');

        // Define priority commodities data
        $commodities = array(
            array(
                'name' => 'Corn',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-corn.webp',
                'route' => home_url('/commodity/corn'),
            ),
            array(
                'name' => 'Rice',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-rice.webp',
                'route' => home_url('/commodity/rice'),
            ),
            array(
                'name' => 'Banana',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-banana.webp',
                'route' => home_url('/commodity/banana'),
            ),
            array(
                'name' => 'Coconut',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-coconut.webp',
                'route' => home_url('/commodity/coconut'),
            ),
            array(
                'name' => 'Cassava',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-cassava.webp',
                'route' => home_url('/commodity/cassava'),
            ),
            array(
                'name' => 'Sweet Potato',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-sweetpotato.webp',
                'route' => home_url('/commodity/sweet-potato'),
            ),
            array(
                'name' => 'Papaya',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-papaya.webp',
                'route' => home_url('/commodity/papaya'),
            ),
            array(
                'name' => 'Tomato',
                'image' => get_template_directory_uri() . '/assets/images/commodities/p-tomato.webp',
                'route' => home_url('/commodity/tomato'),
            ),
        );

        // Allow filtering of commodities data
        $commodities = apply_filters('cbc_priority_commodities_data', $commodities);

        if (empty($commodities)) {
            return '<p>No commodities available.</p>';
        }

        // Enqueue styles and scripts inline
        ob_start();
        ?>
        <style>
        .cbc-carousel-container {
            position: relative;
            width: 100%;
            overflow: visible;
            padding: 2rem 0;
        }
        .cbc-carousel {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1300px;
            min-height: 400px;
            user-select: none;
            overflow: visible;
        }
        .cbc-carousel-track {
            display: flex;
            gap: 0;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 100%;
            overflow: visible;
        }
        .cbc-carousel-item {
            position: absolute;
            left: 50%;
            top: 50%;
            overflow: hidden;
            border-radius: 0.75rem;
            box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.45);
            will-change: transform, opacity, filter;
            min-width: 9rem;
            transition: transform 560ms cubic-bezier(.25, .9, .34, 1.31),
                        opacity 560ms ease-out,
                        filter 560ms ease-out,
                        box-shadow 560ms ease-out;
            background: #1a1a1a;
            color: white;
            transform-origin: center center;
            pointer-events: none;
        }
        .cbc-carousel-item.active {
            box-shadow: 0 6px 28px -6px rgba(0, 0, 0, 0.65);
            pointer-events: auto;
        }
        .cbc-carousel-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 55%, rgba(255, 255, 255, 0.10), rgba(0, 0, 0, 0.55));
            opacity: 0;
            transition: opacity 400ms ease;
            pointer-events: none;
            mix-blend-mode: overlay;
        }
        .cbc-carousel-item.active::after {
            opacity: 0.55;
        }
        .cbc-carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .cbc-carousel-item-content {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            pointer-events: none;
        }
        .cbc-carousel-item-title {
            font-weight: 600;
            font-size: 1.125rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
            transition: all 300ms ease;
            padding: 0.5rem;
        }
        .cbc-carousel-item.active .cbc-carousel-item-title {
            transform: scale(1.1);
            font-weight: 700;
        }
        .cbc-carousel-item:not(.active) .cbc-carousel-item-title {
            opacity: 0.7;
        }
        .cbc-carousel-arrows {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0.5rem;
            pointer-events: none;
            z-index: 100;
        }
        .cbc-carousel-arrow {
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
        .cbc-carousel-arrow:hover {
            background: rgba(0, 0, 0, 0.6);
        }
        .cbc-carousel-arrow:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }
        @media (max-width: 640px) {
            .cbc-carousel {
                min-height: 300px;
            }
            .cbc-carousel-item-title {
                font-size: 0.875rem;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .cbc-carousel-item,
            .cbc-carousel-track {
                transition: none !important;
                animation: none !important;
            }
        }
        .cbc-carousel-loading {
            text-align: center;
            padding: 2rem;
        }
        .cbc-carousel-spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 3px solid rgba(255, 255, 255, 0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: cbc-spin 0.9s linear infinite;
            margin: 0 auto;
        }
        @keyframes cbc-spin {
            to { transform: rotate(360deg); }
        }
        </style>

        <div class="cbc-carousel-container
             data-max-display="<?php echo esc_attr($atts['max_display']); ?>"
             data-auto-advance="<?php echo esc_attr($atts['auto_advance']); ?>"
             data-auto-interval="<?php echo esc_attr($atts['auto_interval']); ?>"
             data-show-arrows="<?php echo esc_attr($atts['show_arrows']); ?>"
             data-enable-blur="<?php echo esc_attr($atts['enable_blur']); ?>"
             data-center-scale="<?php echo esc_attr($atts['center_scale']); ?>">

            <div class="cbc-carousel"
                 role="listbox"
                 aria-label="Priority Commodities Carousel"
                 tabindex="0">

                <div class="cbc-carousel-track">
                    <?php foreach ($commodities as $index => $commodity): ?>
                        <div class="cbc-carousel-item w-[12rem] h-[14rem] md:w-[22rem] md:h-[16rem] <?php echo $index === 0 ? 'active' : ''; ?>"
                             data-index="<?php echo $index; ?>"
                             data-name="<?php echo esc_attr($commodity['name']); ?>"
                             role="option"
                             aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                        >

                            <img src="<?php echo esc_url($commodity['image']); ?>"
                                 alt="<?php echo esc_attr($commodity['name']); ?>"
                                 loading="lazy">

                            <div class="cbc-carousel-item-content">
                                <span class="cbc-carousel-item-title">
                                    <?php echo esc_html($commodity['name']); ?>
                                </span>
                            </div>

                            <?php if (!empty($commodity['route'])): ?>
                                <span href="<?php echo esc_url($commodity['route']); ?>"
                                   class="absolute inset-0 z-40"
                                   aria-label="View <?php echo esc_attr($commodity['name']); ?> details"
                                   style="position: absolute; inset: 0; z-index: 40;"></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($atts['show_arrows'] === 'true'): ?>
                <div class="cbc-carousel-arrows">
                    <button type="button"
                            class="cbc-carousel-arrow cbc-carousel-prev"
                            aria-label="Previous commodity">
                        ‹
                    </button>
                    <button type="button"
                            class="cbc-carousel-arrow cbc-carousel-next"
                            aria-label="Next commodity">
                        ›
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        (function() {
            const containers = document.querySelectorAll('.cbc-carousel-container');

            containers.forEach(container => {
                const carousel = container.querySelector('.cbc-carousel');
                const track = container.querySelector('.cbc-carousel-track');
                const items = Array.from(container.querySelectorAll('.cbc-carousel-item'));
                const prevBtn = container.querySelector('.cbc-carousel-prev');
                const nextBtn = container.querySelector('.cbc-carousel-next');

                const maxDisplay = parseInt(container.dataset.maxDisplay) || 5;
                const autoAdvance = container.dataset.autoAdvance === 'true';
                const autoInterval = parseInt(container.dataset.autoInterval) || 2500;
                const enableBlur = container.dataset.enableBlur === 'true';
                const centerScale = parseFloat(container.dataset.centerScale) || 1.30;

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

                        // Calculate transforms
                        const baseScale = offset === 0
                            ? centerScale * 1.05
                            : (centerScale * 0.92) - absOffset * 0.35;
                        const scale = Math.max(0.45, baseScale);
                        const translateZ = -Math.min(absOffset * 60, 220);

                        // Calculate horizontal offset in viewport units for better centering
                        // When offset is 0, item should be perfectly centered
                        const translateX = offset * 25; // Use vw units for horizontal spacing

                        const opacity = offset === 0 ? 1 : Math.max(0.15, 1 - (absOffset * 0.65));
                        const brightness = offset === 0 ? 1.05 : Math.max(0.2, 0.55 - (absOffset - 1) * 0.18);
                        const saturation = offset === 0 ? 1.2 : 0.5;
                        const blur = enableBlur && absOffset > 0 ? Math.min(absOffset * 2, 4) : 0;

                        // Transform with proper centering: translate from center, then apply offset
                        item.style.transform = `translate(-50%, -50%) translate3d(${translateX}vw, 0, ${translateZ}px) scale(${scale})`;
                        item.style.opacity = opacity;
                        item.style.filter = `brightness(${brightness}) saturate(${saturation}) blur(${blur}px)`;
                        item.style.zIndex = 400 - absOffset * 12;

                        item.classList.toggle('active', offset === 0);
                        item.setAttribute('aria-selected', offset === 0 ? 'true' : 'false');

                        // Show only items within maxDisplay range
                        if (visualIndex < maxDisplay) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }

                function next() {
                    currentIndex = (currentIndex + 1) % itemCount;
                    updateCarousel();
                    triggerHapticFeedback();
                }

                function prev() {
                    currentIndex = (currentIndex - 1 + itemCount) % itemCount;
                    updateCarousel();
                    triggerHapticFeedback();
                }

                function triggerHapticFeedback() {
                    // Check if the Vibration API is supported
                    if ('vibrate' in navigator) {
                        // Medium-strong vibration pattern: vibrate for 40ms
                        // This provides a noticeable tactile response without being too aggressive
                        navigator.vibrate(40);
                    }
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
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    add_shortcode('priority_commodities_carousel', 'cbc_priority_commodities_carousel_shortcode');
}
