<?php
/**
 * Our Impact Shortcode - Compact Bento Grid Layout
 * Displays impact statistics with Philippines map
 *
 * Usage: [our_impact]
 */

if (!function_exists('cbc_our_impact_shortcode')) {
    function cbc_our_impact_shortcode($atts) {
        $atts = shortcode_atts(array(
                'map_svg_path' => '/wp-content/uploads/2025/09/phMap.svg',
        ), $atts, 'our_impact');

        // Impact Statistics Data
        $impact_stats = array(
                'research_projects' => array(
                        'number' => null,
                        'label' => 'Active Research Initiatives',
                        'sublabel' => 'Ongoing R&D Projects',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'Programs', 'value' => '3', 'color' => '#1f5d2b'),
                                array('label' => 'Projects ', 'value' => '5', 'color' => '#55A147'),
                        )
                ),
                'capacity_building' => array(
                        'number' => null,
                        'label' => 'Stakeholders Empowered',
                        'sublabel' => 'Through Training & Workshops',
                        'color' => '#55A147',
                        'breakdown' => array(
                                array('label' => 'Researchers', 'value' => '81', 'color' => '#1f5d2b'),
                                array('label' => 'Regulators', 'value' => '16', 'color' => '#55A147'),
                                array('label' => 'Educators', 'value' => '16', 'color' => '#D5DA65'),
                                array('label' => 'Students', 'value' => '8', 'color' => '#D5DA65'),
                        )
                ),
                'partners_collaborators' => array(
                        'number' => null,
                        'label' => 'Partners and Collaborators',
                        'sublabel' => 'Academic, Government & Global Partners',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'SUCs', 'value' => '27', 'color' => '#1f5d2b'),
                                array('label' => 'DA Agencies', 'value' => '3', 'color' => '#D5DA65'),
                                array('label' => 'International', 'value' => '2', 'color' => '#8BC34A'),
                        )
                ),
                'iec_reach' => array(
                        'number' => null,
                        'label' => 'Public Engagement',
                        'sublabel' => 'Information & Education Campaigns',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'RCBS Conducted', 'value' => '6', 'color' => '#1f5d2b'),
                                array('label' => 'Knowledge Products', 'value' => '7', 'color' => '#D5DA65'),
                                array('label' => 'International Visitors', 'value' => '28', 'color' => '#8BC34A'),
                        )
                ),
                'internship_program' => array(
                        'number' => null,
                        'label' => 'Next-Gen Scientists',
                        'sublabel' => 'Internship & Attachment Program',
                        'color' => '#FACD15',
                        'breakdown' => array(
                                array('label' => 'High School', 'value' => '55', 'color' => '#1f5d2b'),
                                array('label' => 'Undergraduate', 'value' => '106', 'color' => '#55A147'),
                                array('label' => 'Graduate', 'value' => '6', 'color' => '#D5DA65'),
                                array('label' => 'PhD', 'value' => '3', 'color' => '#1f5d2b'),
                        )
                ),
                'thesis_support' => array(
                        'number' => null,
                        'label' => 'Thesis & Dissertations',
                        'sublabel' => 'Research Support & Mentorship',
                        'color' => '#D5DA65',
                        'breakdown' => array(
                                array('label' => 'Local University Students', 'value' => '34', 'color' => '#1f5d2b'),
                                array('label' => 'International Students', 'value' => '4', 'color' => '#55A147'),
                        )
                ),
        );

        $impact_stats = apply_filters('cbc_impact_stats_data', $impact_stats);

        foreach ($impact_stats as $skey => $sval) {
            if (( ! isset($sval['number']) || $sval['number'] === '' || is_null($sval['number']) )
                && isset($sval['breakdown']) && is_array($sval['breakdown'])) {
                $sum = 0;
                foreach ($sval['breakdown'] as $item) {
                    $v = isset($item['value']) ? $item['value'] : 0;
                    $num = (float) str_replace(',', '', $v);
                    $sum += $num;
                }

                $impact_stats[$skey]['number'] = (string) (int) round($sum);
            }
        }

        // Auto-sort impact stats by numeric `number` descending (highest first).
        // This runs after numbers are calculated so it respects auto-computed totals.
        if (!empty($impact_stats) && is_array($impact_stats)) {
            uasort($impact_stats, function ($a, $b) {
                $na = isset($a['number']) ? (float) str_replace(',', '', $a['number']) : 0.0;
                $nb = isset($b['number']) ? (float) str_replace(',', '', $b['number']) : 0.0;
                // Descending order: compare b to a
                if ($nb === $na) {
                    return 0;
                }
                return ($nb < $na) ? -1 : 1;
            });
        }

        ob_start();
        ?>
        <style>
            .our-impact-section {
                color: #1f2937;
                padding: 3rem 1rem;
                position: relative;
                overflow: hidden;
            }

            .our-impact-container {
                max-width: 80rem;
                margin: 0 auto;
            }

            .our-impact-header {
                text-align: center;
                margin-bottom: 0;
            }

            .our-impact-title {
                font-size: clamp(1.875rem, 4vw, 2.5rem);
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: -0.02em;
                margin: 0;
                color: #1f5d2b;
            }

            /* Bento Grid Layout */
            .impact-bento-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            @media (min-width: 640px) {
                .impact-bento-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 1rem;
                }
            }

            @media (min-width: 768px) {
                .our-impact-section {
                    padding: 4rem 1.5rem;
                }
            }

            @media (min-width: 1024px) {
                .our-impact-section {
                    padding: 6rem 2rem;
                }

                .impact-bento-grid {
                    grid-template-columns: repeat(3, 1fr);
                    grid-template-rows: auto auto;
                    gap: 1.25rem;
                }
            }

            /* Individual Impact Cards */
            .impact-card {
                background: #ffffff;
                padding: 1.25rem;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .impact-card:hover {
                background: #ffffff;
                box-shadow: 0 4px 20px -4px rgba(31, 93, 43, 0.15);
                transform: translateY(-2px);
            }

            /* Accent border on left */
            .impact-card::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                background: var(--card-color, #1f5d2b);
            }

            .impact-card-header {
                margin-bottom: 0.75rem;
            }

            .impact-stat-number {
                font-size: clamp(1.75rem, 3vw, 2.5rem);
                font-weight: 800;
                line-height: 1;
                margin-bottom: 0.25rem;
                font-family: 'Segoe UI', system-ui, sans-serif;
                color: var(--card-color, #1f5d2b);
            }

            .impact-stat-label {
                font-size: 0.875rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #1f2937;
                line-height: 1.2;
                margin-bottom: 0.15rem;
            }

            .impact-stat-sublabel {
                font-size: 0.7rem;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            /* Compact Breakdown Bars */
            .impact-breakdown {
                margin-top: 0.75rem;
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .breakdown-item {
                display: grid;
                grid-template-columns: auto 1fr auto;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.75rem;
            }

            .breakdown-label {
                color: #64748b;
                font-size: 0.7rem;
                white-space: nowrap;
                min-width: 70px;
            }

            .breakdown-bar-bg {
                height: 4px;
                background: #e2e8f0;
                overflow: hidden;
                min-width: 40px;
            }

            .breakdown-bar-fill {
                height: 100%;
                transition: width 0.9s ease-out;
            }

            .breakdown-value {
                color: #374151;
                font-weight: 600;
                font-size: 0.75rem;
                min-width: 35px;
                text-align: right;
            }

            /* Map Card - Featured */
            .impact-map-card {
                background: #ffffff;
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                position: relative;
                overflow: hidden;
                opacity: 50% !important;
            }

            @media (min-width: 640px) {
                .impact-map-card {
                    grid-column: span 2;
                    min-height: 250px;
                }
            }

            @media (min-width: 1024px) {
                .impact-map-card {
                    grid-column: span 1;
                    grid-row: span 3;
                    min-height: auto;
                }
            }

            .impact-map-wrapper {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .impact-map-wrapper svg,
            .impact-map-wrapper img {
                max-width: 100%;
                width: auto;
                height: auto;
                filter: drop-shadow(0 4px 20px rgba(31, 93, 43, 0.15));
            }

            .impact-map-caption {
                position: absolute;
                bottom: 1rem;
                left: 50%;
                transform: translateX(-50%);
                text-align: center;
                font-size: 0.875rem;
                color: #404040;
                background: rgba(255,255,255,0.9);
                padding: 0.25rem 0.75rem;
            }

            /* Animation */
            @keyframes countUp {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .our-impact-section.impact-is-ready .impact-card {
                opacity: 0;
                transform: translateY(20px);
            }

            .our-impact-section.impact-is-visible .impact-card {
                animation: countUp 0.5s ease-out forwards;
            }

            .our-impact-section.impact-is-visible .impact-card:nth-child(1) { animation-delay: 0.05s; }
            .our-impact-section.impact-is-visible .impact-card:nth-child(2) { animation-delay: 0.1s; }
            .our-impact-section.impact-is-visible .impact-card:nth-child(3) { animation-delay: 0.15s; }
            .our-impact-section.impact-is-visible .impact-card:nth-child(4) { animation-delay: 0.2s; }
            .our-impact-section.impact-is-visible .impact-card:nth-child(5) { animation-delay: 0.25s; }
            .our-impact-section.impact-is-visible .impact-card:nth-child(6) { animation-delay: 0.3s; }

            /* Mobile optimizations */
            @media (max-width: 639px) {
                .impact-card {
                    padding: 1rem;
                }

                .impact-breakdown {
                    gap: 0.35rem;
                }

                .breakdown-item {
                    gap: 0.35rem;
                }

                .impact-map-card {
                    min-height: 180px;
                    order: -1;
                }
            }
        </style>

        <section class="our-impact-section" aria-label="Our Impact Statistics">
            <div class="our-impact-container">
                <div class="our-impact-header">
                    <h2 class="our-impact-title section-title homepage-section-title text-center text-3xl lg:text-4xl font-extrabold text-[#1f5d2b]">OUR IMPACT</h2>
                </div>
                <div class="impact-bento-grid">
                    <!-- Map Card -->
                    <div class="impact-map-card rounded-lg">
                        <div class="impact-map-wrapper">
                            <?php
                            // Resolve the provided path to an absolute filesystem path.
                            // $atts['map_svg_path'] may be a site-relative path like '/wp-content/uploads/2025/09/phMap.svg'
                            // or an absolute URL. We want to safely inline SVGs from disk when appropriate.
                            $provided = isset($atts['map_svg_path']) ? $atts['map_svg_path'] : '';
                            $map_relative = ltrim($provided, '/');

                            // Filesystem path to the file (assume site root when a site-relative path is provided).
                            $map_path = ABSPATH . $map_relative;

                            // Build a safe URL for fallback display. If the provided value looks like an absolute URL, use it.
                            if (preg_match('#^https?://#i', $provided)) {
                                $map_src = esc_url($provided);
                            } else {
                                $map_src = esc_url( site_url( '/' ) . $map_relative );
                            }

                            // Only inline SVGs from disk and only when small enough and readable.
                            $map_is_svg = strtolower( pathinfo( $map_path, PATHINFO_EXTENSION ) ) === 'svg';
                            $max_inline_size = 200 * 1024; // 200 KB

                            if ( $map_is_svg && is_readable( $map_path ) && filesize( $map_path ) > 0 && filesize( $map_path ) <= $max_inline_size ) {
                                $svg = file_get_contents( $map_path );
                                // Minimal sanitization: remove <script>...</script> blocks to reduce injection risk.
                                $svg = preg_replace('#<script.*?>.*?</script>#is', '', $svg);
                                echo $svg;
                            } else {
                                // Fallback to an <img> tag pointing to the constructed URL. Escaped for safety.
                                echo '<img src="' . $map_src . '" alt="Philippines Map showing our nationwide impact">';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Stat Cards -->
                    <?php foreach ($impact_stats as $key => $stat):
                        $stat_numeric_value = (int) str_replace(',', '', $stat['number']);
                        ?>
                        <div class="impact-card shadow-lg rounded-lg bg-white" style="--card-color: <?php echo esc_attr($stat['color']); ?>">
                            <div class="impact-card-header">
                                <div class="impact-stat-number"
                                     data-count-target="<?php echo esc_attr($stat_numeric_value); ?>"
                                     data-count-final="<?php echo esc_attr($stat['number']); ?>"
                                     style="color: <?php echo esc_attr($stat['color']); ?>">
                                    <?php echo esc_html($stat['number']); ?>
                                </div>
                                <div class="impact-stat-label">
                                    <?php echo esc_html($stat['label']); ?>
                                </div>
                                <div class="impact-stat-sublabel">
                                    <?php echo esc_html($stat['sublabel']); ?>
                                </div>
                            </div>

                            <?php if (isset($stat['breakdown']) && !empty($stat['breakdown'])):
                                $stat_total = (float) str_replace(',', '', $stat['number']);
                                ?>
                                <div class="impact-breakdown">
                                    <?php foreach ($stat['breakdown'] as $item):
                                        $item_total = (float) str_replace(',', '', $item['value']);
                                        $percentage = $stat_total > 0 ? ($item_total / $stat_total) * 100 : 0;
                                        ?>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label"><?php echo esc_html($item['label']); ?></span>
                                            <div class="breakdown-bar-bg">
                                                <div class="breakdown-bar-fill"
                                                     data-target-width="<?php echo esc_attr($percentage); ?>"
                                                     style="width: <?php echo esc_attr($percentage); ?>%; background: <?php echo esc_attr($item['color']); ?>"></div>
                                            </div>
                                            <span class="breakdown-value"><?php echo esc_html($item['value']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <script>
            (function () {
                if (window.cbcOurImpactAnimatorInitialized) return;
                window.cbcOurImpactAnimatorInitialized = true;

                const sections = Array.from(document.querySelectorAll('.our-impact-section'));
                if (!sections.length) return;

                const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function formatNumber(value) {
                    return Number(value).toLocaleString('en-US');
                }

                function animateCounter(counter) {
                    const target = parseInt(counter.dataset.countTarget || '0', 10);
                    const finalText = counter.dataset.countFinal || formatNumber(target);
                    const duration = 850;
                    const startTime = performance.now();

                    if (!target || prefersReducedMotion) {
                        counter.textContent = finalText;
                        return;
                    }

                    counter.textContent = '0';

                    function update(now) {
                        const progress = Math.min((now - startTime) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 4);
                        const currentValue = Math.round(target * eased);

                        counter.textContent = formatNumber(currentValue);

                        if (progress < 1) {
                            window.requestAnimationFrame(update);
                        } else {
                            counter.textContent = finalText;
                        }
                    }

                    window.requestAnimationFrame(update);
                }

                function animateSection(section) {
                    if (section.dataset.impactAnimated === 'true') return;

                    section.dataset.impactAnimated = 'true';
                    section.classList.add('impact-is-visible');

                    section.querySelectorAll('.impact-stat-number').forEach(animateCounter);

                    section.querySelectorAll('.breakdown-bar-fill').forEach((bar) => {
                        const targetWidth = bar.dataset.targetWidth || '0';

                        if (prefersReducedMotion) {
                            bar.style.width = `${targetWidth}%`;
                            return;
                        }

                        window.requestAnimationFrame(() => {
                            bar.style.width = `${targetWidth}%`;
                        });
                    });
                }

                sections.forEach((section) => {
                    if (!prefersReducedMotion) {
                        section.classList.add('impact-is-ready');
                    }

                    section.querySelectorAll('.impact-stat-number').forEach((counter) => {
                        counter.dataset.countFinal = counter.dataset.countFinal || counter.textContent.trim();
                    });

                    if (!prefersReducedMotion) {
                        section.querySelectorAll('.breakdown-bar-fill').forEach((bar) => {
                            bar.style.width = '0%';
                        });
                    }
                });

                if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
                    sections.forEach(animateSection);
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            animateSection(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.2,
                    rootMargin: '0px 0px -5% 0px'
                });

                sections.forEach((section) => {
                    observer.observe(section);
                });
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    add_shortcode('our_impact', 'cbc_our_impact_shortcode');
}

