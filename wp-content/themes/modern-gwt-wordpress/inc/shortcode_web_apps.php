<?php
/**
 * CBC Web Apps Showcase Shortcode
 * Displays DA-CBC web applications as minimal cards
 * Matches mood board: #EAF5EE bg, white cards, 3px top accent, Syne typography
 *
 * Usage: [cbc_web_apps]
 */

if (!function_exists('cbc_web_apps_shortcode')) {
    function cbc_web_apps_shortcode($atts) {
        $atts = shortcode_atts(array(
                'show_descriptions' => 'true',
                'open_new_tab' => 'true',
        ), $atts, 'cbc_web_apps');

        // Web Apps Data
        $web_apps = array(
                array(
                        'title' => 'Plant Breeders & Innovators Network',
                        'category' => 'PIN SYSTEM',
                        'url' => 'https://pin.philrice.gov.ph/',
                        'description' => 'Comprehensive database for crop varieties, genetic research, and biotechnology approvals in the Philippines.',
                        'color' => '#1f5d2b',
                ),
                array(
                        'title' => 'Virtual Tour of Our Facilities',
                        'category' => 'CBC 360° TOUR',
                        'url' => 'https://cbc360tour.philrice.gov.ph/',
                        'description' => 'Explore our state-of-the-art crop biotechnology center in an immersive 360° virtual environment.',
                        'color' => '#55A147',
                ),
                array(
                        'title' => 'Proprietary Web Apps and Services',
                        'category' => 'ONE CBC PORTAL',
                        'url' => 'https://onecbc.philrice.gov.ph/',
                        'description' => 'Internal portal connecting CBC staff, collaborators, and partner institutions for seamless operations.',
                        'color' => '#8BC34A',
                ),
        );

        $web_apps = apply_filters('cbc_web_apps_data', $web_apps);

        $target_attr = $atts['open_new_tab'] === 'true' ? 'target="_blank" rel="noopener noreferrer"' : '';
        $show_desc = $atts['show_descriptions'] === 'true';

        ob_start();
        ?>
        <style>
            .cbc-webapps-container {
                max-width: 1200px;
                margin: 0 auto;
            }

            /* Header Section */
            .cbc-webapps-header {
                margin-bottom: 0;
                text-align: center;
            }

            .cbc-webapps-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: #1f5d2b;
                text-transform: uppercase;
                letter-spacing: 0.15em;
                margin-bottom: 0.75rem;
                display: block;
            }

            /* Cards Grid */
            .cbc-webapps-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            @media (min-width: 768px) {
                .cbc-webapps-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (min-width: 1024px) {
                .cbc-webapps-grid {
                    grid-template-columns: repeat(3, 1fr);
                    gap: 2rem;
                }
            }

            /* Individual Card */
            .cbc-webapp-card {
                position: relative;
                background: #ffffff;
                border-radius: 0.75rem;
                padding: 2rem;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
                border: 1px solid rgba(31, 93, 43, 0.08);
                height: 100%;
            }

            /* 3px Top Accent Border */
            .cbc-webapp-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: var(--app-color, #1f5d2b);
                border-radius: 0.75rem 0.75rem 0 0;
            }

            .cbc-webapp-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px -12px rgba(31, 93, 43, 0.15);
                border-color: rgba(31, 93, 43, 0.15);
            }

            /* Category Label */
            .cbc-webapp-category {
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--app-color, #1f5d2b);
                text-transform: uppercase;
                letter-spacing: 0.1em;
                margin-bottom: 1rem;
            }

            /* Main Title */
            .cbc-webapp-title {
                font-size: clamp(1.25rem, 2.5vw, 1.5rem);
                font-weight: 700;
                color: #0f172a;
                line-height: 1.2;
                margin: 0 0 1rem 0;
            }

            /* Description */
            .cbc-webapp-description {
                font-size: 0.9375rem;
                color: #64748b;
                line-height: 1.6;
                margin: 0 0 2rem 0;
                flex: 1;
            }

            /* Visit Link */
            .cbc-webapp-link {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--app-color, #1f5d2b);
                text-decoration: none;
                margin-top: auto;
                transition: gap 0.2s ease;
            }

            .cbc-webapp-card:hover .cbc-webapp-link {
                gap: 0.75rem;
            }

            .cbc-webapp-link-text {
                position: relative;
            }

            .cbc-webapp-link-text::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: var(--app-color, #1f5d2b);
                transition: width 0.3s ease;
            }

            .cbc-webapp-card:hover .cbc-webapp-link-text::after {
                width: 100%;
            }

            .cbc-webapp-arrow {
                width: 1rem;
                height: 1rem;
                transition: transform 0.2s ease;
            }

            .cbc-webapp-card:hover .cbc-webapp-arrow {
                transform: translateX(4px);
            }

            /* Compact Mode */
            .cbc-webapps-grid.compact .cbc-webapp-description {
                display: none;
            }

            .cbc-webapps-grid.compact .cbc-webapp-card {
                padding: 1.5rem;
            }

            /* Mobile */
            @media (max-width: 767px) {
                .cbc-webapp-card {
                    padding: 1.5rem;
                }
            }

            /* Animation */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .cbc-webapps-section.is-visible .cbc-webapp-card {
                opacity: 0;
                animation: fadeInUp 0.6s ease-out forwards;
            }

            .cbc-webapps-section.is-visible .cbc-webapp-card:nth-child(1) { animation-delay: 0.1s; }
            .cbc-webapps-section.is-visible .cbc-webapp-card:nth-child(2) { animation-delay: 0.2s; }
            .cbc-webapps-section.is-visible .cbc-webapp-card:nth-child(3) { animation-delay: 0.3s; }
        </style>

        <div class="cbc-webapps-section" aria-label="DA-CBC Web Applications">
            <div class="cbc-webapps-container">
                <div class="cbc-webapps-header">
                    <span class="cbc-webapps-label">Our Platforms</span>
                    <h2 class="section-title homepage-section-title text-center">Connected digital ecosystem</h2>
                </div>

                <div class="cbc-webapps-grid <?php echo $show_desc ? '' : 'compact'; ?>">
                    <?php foreach ($web_apps as $app): ?>
                        <a href="<?php echo esc_url($app['url']); ?>"
                           class="cbc-webapp-card"
                           style="--app-color: <?php echo esc_attr($app['color']); ?>"
                                <?php echo $target_attr; ?>
                           aria-label="Visit <?php echo esc_attr($app['title']); ?>">

                            <span class="cbc-webapp-category"><?php echo esc_html($app['category']); ?></span>

                            <h3 class="cbc-webapp-title"><?php echo esc_html($app['title']); ?></h3>

                            <?php if ($show_desc): ?>
                                <p class="cbc-webapp-description"><?php echo esc_html($app['description']); ?></p>
                            <?php endif; ?>

                            <span class="cbc-webapp-link">
                                <span class="cbc-webapp-link-text">Visit <?php echo esc_html(parse_url($app['url'], PHP_URL_HOST)); ?></span>
                                <svg class="cbc-webapp-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            (function() {
                const section = document.querySelector('.cbc-webapps-section');
                if (!section) return;

                const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
                    section.classList.add('is-visible');
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.15,
                    rootMargin: '0px 0px -50px 0px'
                });

                observer.observe(section);
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    add_shortcode('cbc_web_apps', 'cbc_web_apps_shortcode');
}