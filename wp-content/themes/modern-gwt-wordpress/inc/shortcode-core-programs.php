<?php
/**
 * Core Programs Shortcode - Improved UI
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
                        'description' => 'Pioneering advanced research to develop breakthrough technologies and innovative solutions that address critical challenges.',
                        'icon' => 'lightbulb',
                        'link' => '/about-us/core-programs/technology-development-and-innovation/',
                ),
                array(
                        'title' => 'R4D Biotechnology Capacity-Building Service',
                        'image' => '/wp-content/uploads/2025/09/IMG_20240522_085745-768x576.jpg',
                        'description' => 'Empowering institutions nationwide through specialized training programs, hands-on workshops, and technical expertise development.',
                        'icon' => 'users',
                        'link' => '/about-us/core-programs/r4d-biotechnology-capacity-building-service/',
                ),
                array(
                        'title' => 'Partnership and Fund Generation',
                        'image' => '/wp-content/uploads/2025/09/CBC04940-768x461.png',
                        'description' => 'Building strategic alliances with industry leaders, government agencies, and investors to secure sustainable funding and maximize impact.',
                        'icon' => 'handshake',
                        'link' => '/about-us/core-programs/partnerships-and-fund-generation/',
                ),
                array(
                        'title' => 'Technology Commercialization and Management',
                        'image' => '/wp-content/uploads/2025/09/CBC06408-768x432.png',
                        'description' => 'Transforming innovative research into accessible products and services that improve lives and drive economic growth across the Philippines.',
                        'icon' => 'rocket',
                        'link' => '/about-us/core-programs/technology-commercialization-and-management/',
                ),
        );

        $programs = apply_filters('cbc_core_programs_data', $programs);

        if (empty($programs)) {
            return '<p class="text-center text-gray-500 py-8">No programs available.</p>';
        }

        ob_start();
        ?>
        <style>
            /* ============================================
               CORE PROGRAMS - IMPROVED UI
               ============================================ */

            .core-programs-section {
                padding: 4rem 1rem;
            }

            .core-programs-container {
                max-width: 1200px;
                margin: 0 auto;
            }

            /* Section Header */
            .core-programs-header {
                text-align: center;
                margin-bottom: 3rem;
            }

            .core-programs-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: #55A147;
                text-transform: uppercase;
                letter-spacing: 0.15em;
                margin-bottom: 0.5rem;
                display: block;
            }

            .core-programs-title {
                font-size: clamp(1.75rem, 4vw, 2.5rem);
                font-weight: 800;
                color: #1f5d2b;
                line-height: 1.1;
                letter-spacing: -0.02em;
                margin: 0;
            }

            /* Grid Layout - Desktop */
            .core-programs-grid {
                display: none;
            }

            @media (min-width: <?php echo esc_attr($atts['mobile_breakpoint']); ?>px) {
                .core-programs-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 1.5rem;
                }
            }

            @media (min-width: 1024px) {
                .core-programs-grid {
                    grid-template-columns: repeat(4, 1fr);
                    gap: 2rem;
                }
            }

            /* Grid Cards */
            .core-program-card {
                position: relative;
                background: #ffffff;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid #e2e8f0;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .core-program-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(31, 93, 43, 0.15), 0 10px 10px -5px rgba(31, 93, 43, 0.1);
                border-color: #55A147;
            }

            /* Image Container */
            .core-program-image-wrap {
                position: relative;
                width: 100%;
                height: 200px;
                overflow: hidden;
            }

            .core-program-image-wrap img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .core-program-card:hover .core-program-image-wrap img {
                transform: scale(1.1);
            }

            /* Image Overlay */
            .core-program-image-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, transparent 0%, rgba(31, 93, 43, 0.8) 100%);
                opacity: 0;
                transition: opacity 0.4s ease;
                display: flex;
                align-items: flex-end;
                padding: 1rem;
            }

            .core-program-card:hover .core-program-image-overlay {
                opacity: 1;
            }

            .core-program-overlay-text {
                color: white;
                font-size: 0.875rem;
                font-weight: 500;
                transform: translateY(10px);
                transition: transform 0.4s ease;
            }

            .core-program-card:hover .core-program-overlay-text {
                transform: translateY(0);
            }

            /* Content Area */
            .core-program-content {
                padding: 1.5rem;
                flex: 1;
                display: flex;
                flex-direction: column;
                background: white;
            }

            /* Icon */
            .core-program-icon {
                width: 3.5rem;
                height: 3.5rem;
                flex-shrink: 0;
                background: linear-gradient(135deg, #1f5d2b 0%, #55A147 100%);
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
            }

            .core-program-icon svg {
                width: 1.75rem;
                height: 1.75rem;
            }

            /* Title */
            .core-program-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: #1f5d2b;
                line-height: 1.3;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                min-height: 3.9em;
                display: flex;
                align-items: flex-start;
            }

            /* Description */
            .core-program-description {
                font-size: 0.875rem;
                color: #64748b;
                line-height: 1.6;
                margin: 0;
                flex: 1;
            }

            /* Learn More Link */
            .core-program-link {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin-top: 0.75rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: #1f5d2b;
                text-decoration: none;
                transition: gap 0.3s ease;
            }

            .core-program-card:hover .core-program-link {
                gap: 0.75rem;
            }

            .core-program-link svg {
                width: 1rem;
                height: 1rem;
                transition: transform 0.3s ease;
            }

            .core-program-card:hover .core-program-link svg {
                transform: translateX(4px);
            }

            /* ============================================
               CAROUSEL - MOBILE
               ============================================ */
            .core-programs-carousel-wrapper {
                position: relative;
                width: 100%;
                overflow: visible;
                padding: 1rem 0 2rem;
            }

            @media (min-width: <?php echo esc_attr($atts['mobile_breakpoint']); ?>px) {
                .core-programs-carousel-wrapper {
                    display: none;
                }
            }

            .core-programs-carousel {
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                perspective: 1300px;
                min-height: 380px;
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
                width: 280px;
                background: #ffffff;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                will-change: transform, opacity, filter;
                transition: transform 560ms cubic-bezier(.25, .9, .34, 1.31),
                opacity 560ms ease-out,
                filter 560ms ease-out,
                box-shadow 560ms ease-out;
                transform-origin: center center;
                pointer-events: none;
                border: 1px solid #e2e8f0;
            }

            .core-programs-card.active {
                box-shadow: 0 20px 25px -5px rgba(31, 93, 43, 0.2), 0 10px 10px -5px rgba(31, 93, 43, 0.1);
                pointer-events: auto;
            }

            .core-programs-card img {
                width: 100%;
                height: 160px;
                object-fit: cover;
            }

            .core-programs-card-content {
                padding: 1.25rem;
                position: relative;
            }

            .core-programs-card-title {
                color: #1f5d2b;
                font-weight: 700;
                font-size: 1rem;
                text-align: center;
                line-height: 1.3;
                margin: 0 0 0.5rem 0;
            }

            .core-programs-card-description {
                color: #64748b;
                text-align: center;
                line-height: 1.5;
                font-size: 0.875rem;
                margin: 0;
            }

            /* Carousel Arrows */
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
                background: rgba(31, 93, 43, 0.9);
                color: white;
                border: none;
                cursor: pointer;
                font-size: 1.25rem;
                line-height: 1;
                transition: all 0.2s ease;
                width: 2.5rem;
                height: 2.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .core-programs-arrow:hover {
                background: #1f5d2b;
                transform: scale(1.1);
            }

            .core-programs-arrow:focus {
                outline: 2px solid #55A147;
                outline-offset: 2px;
            }

            /* Carousel Dots */
            .core-programs-dots {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 1rem;
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
            }

            .core-programs-dot {
                width: 8px;
                height: 8px;
                border-radius: 9999px;
                background: #cbd5e1;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                padding: 0;
            }

            .core-programs-dot.active {
                background: #1f5d2b;
                width: 24px;
            }

            /* Reduced Motion */
            @media (prefers-reduced-motion: reduce) {
                .core-program-card,
                .core-programs-card,
                .core-programs-track {
                    transition: none !important;
                    animation: none !important;
                }
            }

            /* Mobile Optimizations */
            @media (max-width: 640px) {
                .core-programs-carousel {
                    min-height: 360px;
                }

                .core-programs-card {
                    width: 260px;
                }

                .core-programs-section {
                    padding: 3rem 0.75rem;
                }
            }

            /* Animation */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .core-programs-section.is-visible .core-program-card {
                opacity: 0;
                animation: fadeInUp 0.5s ease-out forwards;
            }

            .core-programs-section.is-visible .core-program-card:nth-child(1) { animation-delay: 0.1s; }
            .core-programs-section.is-visible .core-program-card:nth-child(2) { animation-delay: 0.2s; }
            .core-programs-section.is-visible .core-program-card:nth-child(3) { animation-delay: 0.3s; }
            .core-programs-section.is-visible .core-program-card:nth-child(4) { animation-delay: 0.4s; }
        </style>

        <section class="core-programs-section" aria-label="Core Programs">
            <div class="core-programs-container">
                <div class="core-programs-header">
                    <span class="core-programs-label">What We Do</span>
                    <h2 class="section-title">Core Programs</h2>
                </div>

                <!-- Grid Layout - Desktop -->
                <div class="core-programs-grid">
                    <?php foreach ($programs as $index => $program): ?>
                        <div class="core-program-card reveal-on-scroll" id="particles-js-flyAroundText">
                            <div class="core-program-image-wrap">
                                <img src="<?php echo esc_url($program['image']); ?>"
                                     alt="<?php echo esc_attr($program['title']); ?>"
                                     loading="lazy">
                                <div class="core-program-image-overlay">
                                    <div class="core-program-icon">
                                        <?php if ($program['icon'] === 'lightbulb'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                                            </svg>
                                        <?php elseif ($program['icon'] === 'users'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                            </svg>
                                        <?php elseif ($program['icon'] === 'handshake'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="core-program-content">
                                <div class="flex flex-col gap-2 justify-start mb-2">
                                    <h3 class="core-program-title"><?php echo esc_html($program['title']); ?></h3>
                                </div>
                                <p class="core-program-description"><?php echo esc_html($program['description']); ?></p>
                                <a href="<?php echo esc_url($program['link']); ?>" class="core-program-link">
                                    Learn More
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Layout - Mobile -->
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
                                            <?php echo esc_html($program['title']); ?>
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

                    <!-- Dots Indicator -->
                    <div class="core-programs-dots">
                        <?php foreach ($programs as $index => $program): ?>
                            <button class="core-programs-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>"
                                    aria-label="Go to program <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <script>
            (function () {
                // Intersection Observer for scroll animations
                const section = document.querySelector('.core-programs-section');
                if (section) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    observer.observe(section);
                }

                // Carousel functionality
                const wrappers = document.querySelectorAll('.core-programs-carousel-wrapper');
                if (!wrappers.length) return;

                wrappers.forEach(wrapper => {
                    const carousel = wrapper.querySelector('.core-programs-carousel');
                    const items = Array.from(wrapper.querySelectorAll('.core-programs-card'));
                    const dots = Array.from(wrapper.querySelectorAll('.core-programs-dot'));
                    const prevBtn = wrapper.querySelector('.core-programs-prev');
                    const nextBtn = wrapper.querySelector('.core-programs-next');

                    const autoAdvance = wrapper.dataset.autoAdvance === 'true';
                    const autoInterval = parseInt(wrapper.dataset.autoInterval) || 3000;

                    let currentIndex = 0;
                    let autoTimer = null;
                    let isPaused = false;

                    const itemCount = items.length;
                    const centerIndex = Math.floor(5 / 2);

                    function updateCarousel() {
                        items.forEach((item, idx) => {
                            const visualIndex = (idx - currentIndex + itemCount) % itemCount;
                            const offset = visualIndex - centerIndex;
                            const absOffset = Math.abs(offset);

                            const scale = offset === 0 ? 1.05 : Math.max(0.5, 0.9 - absOffset * 0.2);
                            const translateX = offset * 30;
                            const translateZ = -Math.min(absOffset * 80, 200);
                            const opacity = offset === 0 ? 1 : Math.max(0.2, 0.7 - absOffset * 0.2);

                            item.style.transform = `translate(-50%, -50%) translate3d(${translateX}vw, 0, ${translateZ}px) scale(${scale})`;
                            item.style.opacity = opacity;
                            item.style.zIndex = 100 - absOffset * 10;
                            item.style.display = visualIndex < 5 ? 'flex' : 'none';

                            item.classList.toggle('active', offset === 0);
                            item.setAttribute('aria-selected', offset === 0 ? 'true' : 'false');
                        });

                        // Update dots
                        dots.forEach((dot, idx) => {
                            dot.classList.toggle('active', idx === currentIndex);
                        });
                    }

                    function goTo(index) {
                        currentIndex = (index + itemCount) % itemCount;
                        updateCarousel();
                    }

                    function next() {
                        goTo(currentIndex + 1);
                    }

                    function prev() {
                        goTo(currentIndex - 1);
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
                    carousel.addEventListener('mouseenter', pause);
                    carousel.addEventListener('mouseleave', resume);

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

                    // Touch support
                    let touchStartX = 0;
                    carousel.addEventListener('touchstart', (e) => {
                        touchStartX = e.touches[0].clientX;
                        pause();
                    }, { passive: true });

                    carousel.addEventListener('touchend', (e) => {
                        const diff = touchStartX - e.changedTouches[0].clientX;
                        if (Math.abs(diff) > 50) {
                            diff > 0 ? next() : prev();
                        }
                    }, { passive: true });

                    // Dot navigation
                    dots.forEach((dot, idx) => {
                        dot.addEventListener('click', () => {
                            pause();
                            goTo(idx);
                        });
                    });

                    // Button navigation
                    if (prevBtn) prevBtn.addEventListener('click', () => { pause(); prev(); });
                    if (nextBtn) nextBtn.addEventListener('click', () => { pause(); next(); });

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