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
                'map_svg_path' => get_template_directory() . '/assets/svgs/phMap.svg',
        ), $atts, 'our_impact');

        // Impact Statistics Data
        // source https://docs.google.com/spreadsheets/d/17EzyEyIvq8SEPTFwCOO2F47IBkqaNkSHmtepKmkS8uQ/edit?gid=1300839281#gid=1300839281
        $impact_stats = array(
                'research_projects' => array(
                        'number' => null,
                        'label' => 'Research Initiatives',
                        'sublabel' => 'Ongoing R&D Projects',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'Programs', 'value' => '3', 'color' => '#1f5d2b'),
                                array('label' => 'Projects', 'value' => '5', 'color' => '#55A147'),
                                array('label' => 'Project Sites', 'value' => '#', 'color' => '#D5DA65'),
                                array('label' => 'Crops', 'value' => '13', 'color' => '#D5DA65'),
                        )
                ),
                'breakthroughs' => array(
                        'number' => null,
                        'label' => 'Publications',
                        'sublabel' => 'Disseminating Scientific Knowledge',
                        'color' => '#55A147',
                        'breakdown' => array(
                                array('label' => 'Scientific Journal Articles', 'value' => '13', 'color' => '#1f5d2b'),
                                array('label' => 'Technical Reports', 'value' => '7', 'color' => '#55A147'),
                                array('label' => 'Policy Briefs', 'value' => '5', 'color' => '#D5DA65'),
                                array('label' => 'IP/Patents', 'value' => '2', 'color' => '#D5DA65'),
                        )
                ),
                'capacity_building' => array(
                        'number' => null,
                        'label' => 'Stakeholders Empowered',
                        'sublabel' => 'Through Training & Workshops',
                        'color' => '#55A147',
                        'breakdown' => array(
                                array('label' => 'Balik Scientist Program', 'value' => '3', 'color' => '#1f5d2b'),
                                array('label' => 'Researchers', 'value' => '81', 'color' => '#55A147'),
                                array('label' => 'Regulators', 'value' => '16', 'color' => '#D5DA65'),
                                array('label' => 'Educators', 'value' => '16', 'color' => '#1f5d2b'),
                                array('label' => 'Students', 'value' => '8', 'color' => '#55A147'),
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
                                array('label' => 'International', 'value' => '3', 'color' => '#8BC34A'),
                        )
                ),
                'iec_reach' => array(
                        'number' => null,
                        'label' => 'Public Engagement',
                        'sublabel' => 'Information & Education Campaigns',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'RCBS Participants', 'value' => '3,803', 'color' => '#1f5d2b'),
                                array('label' => 'Farmers Engaged', 'value' => '604', 'color' => '#1f5d2b'),
                                array('label' => 'IEC Distributed', 'value' => '#', 'color' => '#D5DA65'),
                                array('label' => 'SocMed Followers', 'value' => '7,115', 'color' => '#1f5d2b'),
                                array('label' => 'Visitors', 'value' => '1,236', 'color' => '#8BC34A'),
                        )
                ),
                'internship_program' => array(
                        'number' => null,
                        'label' => 'Next-Gen Scientists',
                        'sublabel' => 'Internship & Attachment Program',
                        'color' => '#FACD15',
                        'breakdown' => array(
                                array('label' => 'Senior High School', 'value' => '55', 'color' => '#1f5d2b'),
                                array('label' => 'Undergraduate', 'value' => '103', 'color' => '#55A147'),
                                array('label' => 'Graduate', 'value' => '9', 'color' => '#D5DA65'),
                                array('label' => 'PhD', 'value' => '3', 'color' => '#1f5d2b'),
                        )
                ),
                'thesis_support' => array(
                        'number' => null,
                        'label' => 'Thesis & Dissertations',
                        'sublabel' => 'Research Support & Mentorship',
                        'color' => '#D5DA65',
                        'breakdown' => array(
                                array('label' => 'Internship', 'value' => '75', 'color' => '#1f5d2b'),
                                array('label' => 'International Students', 'value' => '13', 'color' => '#55A147'),
                                array('label' => 'Local University Students', 'value' => '34', 'color' => '#1f5d2b'),
                                array('label' => 'Immersion', 'value' => '57', 'color' => '#1f5d2b'),
                        )
                ),
        );

        $impact_stats = apply_filters('cbc_impact_stats_data', $impact_stats);

        $commodity_image_base = trailingslashit(get_template_directory_uri()) . 'assets/images/commodities/';
        $impact_commodities = array(
                'cacao' => array('commodity' => 'Cacao', 'image' => $commodity_image_base . 'p-cacao.webp', 'color' => '#237823', 'province' => 'Bukidnon', 'projects' => '3'),
                'tomato' => array('commodity' => 'Tomato', 'image' => $commodity_image_base . 'p-tomato.webp', 'color' => '#e9422f', 'province' => 'Bukidnon', 'projects' => '2'),
                'garlic' => array('commodity' => 'Garlic', 'image' => $commodity_image_base . 'p-garlic.webp', 'color' => '#c7a55b', 'province' => 'Ilocos Norte', 'projects' => '1'),
                'mungbean' => array('commodity' => 'Mungbean', 'image' => $commodity_image_base . 'p-mungbean.webp', 'color' => '#7abf35', 'province' => 'Tarlac', 'projects' => '2'),
                'onion' => array('commodity' => 'Onion', 'image' => $commodity_image_base . 'p-onion.webp', 'color' => '#ba2f88', 'province' => 'Nueva Ecija', 'projects' => '2'),
                'eggplant' => array('commodity' => 'Eggplant', 'image' => $commodity_image_base . 'p-eggplant.webp', 'color' => '#6f2da8', 'province' => 'Pangasinan', 'projects' => '2'),
                'coffee' => array('commodity' => 'Coffee', 'image' => $commodity_image_base . 'p-coffee.webp', 'color' => '#6b4b2a', 'province' => 'Benguet', 'projects' => '1'),
                'sweetpotato' => array('commodity' => 'Sweet Potato', 'image' => $commodity_image_base . 'p-sweetpotato.webp', 'color' => '#d98b31', 'province' => 'Albay', 'projects' => '1'),
                'durian' => array('commodity' => 'Durian', 'image' => $commodity_image_base . 'p-durian.webp', 'color' => '#92a636', 'province' => 'Davao del Sur', 'projects' => '2'),
                'banana' => array('commodity' => 'Banana', 'image' => $commodity_image_base . 'p-banana.webp', 'color' => '#e6b72d', 'province' => 'Davao del Norte', 'projects' => '2'),
                'pineapple' => array('commodity' => 'Pineapple', 'image' => $commodity_image_base . 'p-pineapple.webp', 'color' => '#d8a321', 'province' => 'South Cotabato', 'projects' => '1'),
                'rubber' => array('commodity' => 'Rubber', 'image' => $commodity_image_base . 'p-rubber.webp', 'color' => '#3a5f2c', 'province' => 'North Cotabato', 'projects' => '1'),
        );

        $impact_commodities = apply_filters('cbc_impact_commodities_data', $impact_commodities);
        $province_commodities = array();

        foreach ($impact_commodities as $commodity_key => $commodity) {
            if (empty($commodity['province']) || empty($commodity['commodity'])) {
                continue;
            }

            $province_key = sanitize_title($commodity['province']);
            $commodity['key'] = $commodity_key;

            if (!isset($province_commodities[$province_key])) {
                $province_commodities[$province_key] = array(
                        'key' => $province_key,
                        'province' => $commodity['province'],
                        'color' => isset($commodity['color']) ? $commodity['color'] : '#237823',
                        'projects' => 0,
                        'commodities' => array(),
                );
            }

            $province_commodities[$province_key]['projects'] += isset($commodity['projects']) ? (int) $commodity['projects'] : 0;
            $province_commodities[$province_key]['commodities'][] = $commodity;
        }

        $province_commodities = array_values($province_commodities);

        foreach ($impact_stats as $skey => $sval) {
            if (( ! isset($sval['number']) || $sval['number'] === '' || is_null($sval['number']) )
                && isset($sval['breakdown']) && is_array($sval['breakdown'])) {
                $sum = 0;
                foreach ($sval['breakdown'] as $item) {
                    $v = isset($item['value']) ? $item['value'] : 0;
                    $num = (float) str_replace(',', '', $v);
                    $sum += $num;
                }

                $impact_stats[$skey]['number'] = number_format((int) round($sum));
            } elseif (isset($sval['number']) && is_numeric(str_replace(',', '', $sval['number']))) {
                $impact_stats[$skey]['number'] = number_format((int) str_replace(',', '', $sval['number']));
            }
        }

        // Auto-sort impact stats by numeric `number` descending (highest first).
        // This runs after numbers are calculated so it respects auto-computed totals.


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
                grid-template-columns: minmax(108px, 1.3fr) minmax(72px, 2fr) minmax(44px, auto);
                align-items: center;
                gap: 0.5rem;
                font-size: 0.75rem;
            }

            .breakdown-label {
                color: #64748b;
                font-size: 0.7rem;
                line-height: 1.2;
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
                padding: 1rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                min-height: 200px;
                position: relative;
                overflow: hidden;
            }

            .impact-map-title {
                color: #1f5d2b;
                font-size: 0.95rem;
                font-weight: 800;
                line-height: 1.15;
                margin: 0 0 0.75rem;
                text-align: center;
                text-transform: uppercase;
            }

            @media (min-width: 640px) {
                .impact-map-card {
                    grid-column: span 2;
                    min-height: 360px;
                }
            }

            @media (min-width: 1024px) {
                .impact-map-card {
                    grid-column: span 2;
                    grid-row: span 3;
                    min-height: 560px;
                }
            }

            .impact-map-wrapper {
                width: 100%;
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.75rem;
                position: relative;
            }

            .impact-map-figure {
                position: relative;
                min-height: 300px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1;
            }

            .impact-map-wrapper svg {
                max-width: 100%;
                width: auto;
                height: auto;
                filter: drop-shadow(0 4px 20px rgba(31, 93, 43, 0.15));
            }

            .impact-map-wrapper svg {
                max-height: 420px;
            }

            .impact-map-wrapper svg [id] {
                fill: #d7ddd6;
                stroke: #ffffff;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 0.35;
                transition: fill 0.25s ease, filter 0.25s ease, stroke 0.25s ease;
            }

            .impact-map-wrapper svg .impact-province-active {
                cursor: pointer;
            }

            .impact-map-wrapper svg .impact-province-active.is-hovered {
                filter: drop-shadow(0 0 3px rgba(31, 93, 43, 0.55));
                stroke: #f7c806;
                stroke-width: 0.75;
            }

            .impact-map-lines {
                filter: none;
                inset: 0;
                height: 100%;
                max-height: none;
                max-width: none;
                overflow: visible;
                pointer-events: none;
                position: absolute;
                width: 100%;
                z-index: 2;
            }

            .impact-map-line {
                fill: none;
                opacity: 0.62;
                stroke-linecap: round;
                stroke-width: 1.5;
                vector-effect: non-scaling-stroke;
            }

            .impact-map-line-dot {
                opacity: 0.9;
            }

            .impact-map-line.is-hovered,
            .impact-map-line-dot.is-hovered {
                opacity: 1;
            }

            .impact-map-list {
                display: grid;
                gap: 0.55rem;
                position: relative;
                width: 100%;
                z-index: 3;
            }

            .impact-map-pin {
                border-left: 4px solid var(--pin-color, #237823);
                background: #f8faf7;
                display: grid;
                grid-template-columns: 2.25rem minmax(0, 1fr);
                align-items: center;
                gap: 0.5rem;
                padding: 0.45rem 0.55rem;
                transition: background 0.2s ease, box-shadow 0.2s ease;
            }

            .impact-map-pin.is-hovered {
                background: #fff8d6;
                box-shadow: 0 4px 16px rgba(31, 93, 43, 0.12);
            }

            .impact-map-icons {
                display: flex;
                align-items: center;
                min-width: 0;
            }

            .impact-map-icons img {
                background: #ffffff;
                border: 1px solid rgba(31, 93, 43, 0.15);
                border-radius: 50%;
                height: 1.65rem;
                margin-left: -0.45rem;
                object-fit: contain;
                padding: 0.12rem;
                width: 1.65rem;
            }

            .impact-map-icons img:first-child {
                margin-left: 0;
            }

            .impact-map-province {
                color: #1f2937;
                font-size: 0.76rem;
                font-weight: 800;
                line-height: 1.1;
                text-transform: uppercase;
            }

            .impact-map-commodities {
                color: #64748b;
                font-size: 0.68rem;
                line-height: 1.25;
                margin-top: 0.12rem;
            }

            .impact-map-projects {
                color: #1f5d2b;
                font-size: 0.66rem;
                font-weight: 700;
                margin-top: 0.12rem;
            }

            .impact-map-unavailable {
                color: #64748b;
                font-size: 0.78rem;
                text-align: center;
            }

            @media (min-width: 768px) {
                .impact-map-wrapper {
                    grid-template-columns: minmax(220px, 0.95fr) minmax(230px, 1fr);
                    align-items: center;
                }

                .impact-map-list {
                    grid-template-columns: 1fr;
                }
            }

            @media (min-width: 1024px) {
                .impact-map-wrapper svg {
                    max-height: 500px;
                }
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
                    grid-template-columns: minmax(96px, 1.2fr) minmax(64px, 2fr) minmax(40px, auto);
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
                            <div class="impact-map-figure">
                                <?php
                                // Resolve the provided path to an absolute filesystem path.
                                // $atts['map_svg_path'] may be an absolute filesystem path, a site-relative path,
                                // or an absolute URL. Inline readable local SVGs so provinces can be colored.
                                $provided = isset($atts['map_svg_path']) ? $atts['map_svg_path'] : '';
                                $theme_map_path = get_template_directory() . '/assets/svgs/phMap.svg';

                                if ($provided && !preg_match('#^https?://#i', $provided) && file_exists($provided)) {
                                    $map_path = $provided;
                                } elseif ($provided && !preg_match('#^https?://#i', $provided)) {
                                    $map_path = ABSPATH . ltrim($provided, '/');
                                } else {
                                    $map_path = $theme_map_path;
                                }

                                $map_is_svg = $map_path && strtolower(pathinfo($map_path, PATHINFO_EXTENSION)) === 'svg';

                                if (!$map_is_svg || !is_readable($map_path)) {
                                    $map_path = $theme_map_path;
                                    $map_is_svg = is_readable($map_path) && strtolower(pathinfo($map_path, PATHINFO_EXTENSION)) === 'svg';
                                }

                                $max_inline_size = 1024 * 1024; // 1 MB

                                if ($map_is_svg && is_readable($map_path) && filesize($map_path) > 0 && filesize($map_path) <= $max_inline_size) {
                                    $svg = file_get_contents($map_path);
                                    $svg = preg_replace('#<script.*?>.*?</script>#is', '', $svg);

                                    $svg = preg_replace('/@click="[^"]*"/', '', $svg);
                                    $svg = preg_replace('/\s*ref="[^"]*"/', '', $svg);

                                    $svg = preg_replace('/:?id="getProvince\(\'([a-z0-9-]+)\'\)"/', 'id="$1"', $svg);

                                    $svg = preg_replace('/<svg\b/', '<svg class="impact-philippine-map" role="img" aria-label="Philippines commodity map"', $svg, 1);
                                    echo $svg;
                                } else {
                                    echo '<p class="impact-map-unavailable">Province SVG map is unavailable.</p>';
                                }
                                ?>
                            </div>
                            <div class="impact-map-list" aria-label="Province commodity list">
                                <?php foreach ($province_commodities as $province_item):
                                    $commodity_names = wp_list_pluck($province_item['commodities'], 'commodity');
                                    $projects_label = number_format((int) $province_item['projects']) . ' project' . ((int) $province_item['projects'] === 1 ? '' : 's');
                                    ?>
                                    <div class="impact-map-pin"
                                         data-province-key="<?php echo esc_attr($province_item['key']); ?>"
                                         data-color="<?php echo esc_attr($province_item['color']); ?>"
                                         data-count="<?php echo esc_attr(count($province_item['commodities'])); ?>"
                                         style="--pin-color: <?php echo esc_attr($province_item['color']); ?>">
                                        <div class="impact-map-icons" aria-hidden="true">
                                            <?php foreach (array_slice($province_item['commodities'], 0, 3) as $commodity): ?>
                                                <img src="<?php echo esc_url($commodity['image']); ?>" alt="">
                                            <?php endforeach; ?>
                                        </div>
                                        <div>
                                            <div class="impact-map-province"><?php echo esc_html($province_item['province']); ?></div>
                                            <div class="impact-map-commodities"><?php echo esc_html(implode(', ', $commodity_names)); ?></div>
                                            <div class="impact-map-projects"><?php echo esc_html($projects_label); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
                                $breakdown_values = array_map(
                                    static function ($item) {
                                        return (float) str_replace(',', '', $item['value']);
                                    },
                                    $stat['breakdown']
                                );
                                $max_breakdown_value = !empty($breakdown_values) ? max($breakdown_values) : 0;
                                ?>
                                <div class="impact-breakdown">
                                    <?php foreach ($stat['breakdown'] as $item):
                                        $item_total = (float) str_replace(',', '', $item['value']);
                                        $percentage = $stat_total > 0 ? ($item_total / $stat_total) * 100 : 0;
                                        $normalized_percentage = $max_breakdown_value > 0 ? ($item_total / $max_breakdown_value) * 100 : 0;
                                        $normalized_percentage = max($normalized_percentage, 12);
                                        $weighted_percentage = min(100, ($percentage * 0.35) + ($normalized_percentage * 0.65));
                                        ?>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label"><?php echo esc_html($item['label']); ?></span>
                                            <div class="breakdown-bar-bg">
                                                <div class="breakdown-bar-fill"
                                                     data-target-width="<?php echo esc_attr($weighted_percentage); ?>"
                                                     style="width: <?php echo esc_attr($weighted_percentage); ?>%; background: <?php echo esc_attr($item['color']); ?>"></div>
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

                function hexToRgba(hex, alpha) {
                    const cleanHex = String(hex || '#237823').replace('#', '');
                    const normalized = cleanHex.length === 3
                        ? cleanHex.split('').map((part) => part + part).join('')
                        : cleanHex;
                    const color = parseInt(normalized, 16);

                    if (Number.isNaN(color)) {
                        return `rgba(35, 120, 35, ${alpha})`;
                    }

                    const red = (color >> 16) & 255;
                    const green = (color >> 8) & 255;
                    const blue = color & 255;

                    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
                }

                function getProvincePaths(map, provinceKey) {
                    if (!map || !provinceKey) return [];

                    if (window.CSS && CSS.escape) {
                        return Array.from(map.querySelectorAll(`#${CSS.escape(provinceKey)}`));
                    }

                    return Array.from(map.querySelectorAll('[id]')).filter((item) => item.id === provinceKey);
                }

                function clamp(value, min, max) {
                    return Math.min(Math.max(value, min), max);
                }

                function getProvinceAnchor(elements, wrapperRect) {
                    const anchors = elements.map((element) => {
                        if (typeof element.getBBox !== 'function' || typeof element.getScreenCTM !== 'function') {
                            return null;
                        }

                        const box = element.getBBox();
                        const matrix = element.getScreenCTM();

                        if (!box || !matrix || !box.width || !box.height) {
                            return null;
                        }

                        const point = element.ownerSVGElement.createSVGPoint();
                        point.x = box.x + (box.width / 2);
                        point.y = box.y + (box.height / 2);

                        const transformed = point.matrixTransform(matrix);

                        return {
                            x: transformed.x - wrapperRect.left,
                            y: transformed.y - wrapperRect.top,
                            weight: box.width * box.height,
                        };
                    }).filter(Boolean);

                    if (!anchors.length) return null;

                    const totalWeight = anchors.reduce((sum, anchor) => sum + anchor.weight, 0);

                    return anchors.reduce((result, anchor) => {
                        return {
                            x: result.x + (anchor.x * anchor.weight / totalWeight),
                            y: result.y + (anchor.y * anchor.weight / totalWeight),
                        };
                    }, { x: 0, y: 0 });
                }

                function getPinAnchor(pinRect, provinceAnchor, wrapperRect) {
                    const localLeft = pinRect.left - wrapperRect.left;
                    const localRight = pinRect.right - wrapperRect.left;
                    const localTop = pinRect.top - wrapperRect.top;
                    const localBottom = pinRect.bottom - wrapperRect.top;
                    const localCenterX = localLeft + (pinRect.width / 2);
                    const localCenterY = localTop + (pinRect.height / 2);
                    const edgePadding = 6;

                    if (provinceAnchor.y < localTop) {
                        return {
                            x: clamp(provinceAnchor.x, localLeft + edgePadding, localRight - edgePadding),
                            y: localTop,
                        };
                    }

                    if (provinceAnchor.y > localBottom) {
                        return {
                            x: clamp(provinceAnchor.x, localLeft + edgePadding, localRight - edgePadding),
                            y: localBottom,
                        };
                    }

                    if (provinceAnchor.x < localCenterX) {
                        return {
                            x: localLeft,
                            y: clamp(provinceAnchor.y, localTop + edgePadding, localBottom - edgePadding),
                        };
                    }

                    return {
                        x: localRight,
                        y: clamp(provinceAnchor.y, localTop + edgePadding, localBottom - edgePadding),
                    };
                }

                function drawCommodityLines(section) {
                    const wrapper = section.querySelector('.impact-map-wrapper');
                    const map = section.querySelector('.impact-philippine-map');
                    const pins = Array.from(section.querySelectorAll('.impact-map-pin'));

                    if (!wrapper || !map || !pins.length) return;

                    wrapper.querySelectorAll('.impact-map-lines').forEach((existingLines) => existingLines.remove());

                    const wrapperRect = wrapper.getBoundingClientRect();
                    const lineLayer = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    lineLayer.setAttribute('class', 'impact-map-lines');
                    lineLayer.setAttribute('viewBox', `0 0 ${wrapperRect.width} ${wrapperRect.height}`);
                    lineLayer.setAttribute('preserveAspectRatio', 'none');
                    lineLayer.setAttribute('aria-hidden', 'true');

                    pins.forEach((pin) => {
                        const provinceKey = pin.dataset.provinceKey;
                        const provinceAnchor = getProvinceAnchor(getProvincePaths(map, provinceKey), wrapperRect);
                        const pinRect = pin.getBoundingClientRect();

                        if (!provinceAnchor || !pinRect.width || !pinRect.height) return;

                        const pinAnchor = getPinAnchor(pinRect, provinceAnchor, wrapperRect);
                        const x1 = provinceAnchor.x;
                        const y1 = provinceAnchor.y;
                        const x2 = pinAnchor.x;
                        const y2 = pinAnchor.y;
                        const color = pin.dataset.color || '#237823';

                        const pinTop = pinRect.top - wrapperRect.top;
                        const pinBottom = pinRect.bottom - wrapperRect.top;
                        const verticalApproach = Math.abs(y2 - pinTop) < 1 || Math.abs(y2 - pinBottom) < 1;
                        const linePoints = verticalApproach
                            ? `${x1.toFixed(2)},${y1.toFixed(2)} ${x1.toFixed(2)},${((y1 + y2) / 2).toFixed(2)} ${x2.toFixed(2)},${((y1 + y2) / 2).toFixed(2)} ${x2.toFixed(2)},${y2.toFixed(2)}`
                            : `${x1.toFixed(2)},${y1.toFixed(2)} ${((x1 + x2) / 2).toFixed(2)},${y1.toFixed(2)} ${((x1 + x2) / 2).toFixed(2)},${y2.toFixed(2)} ${x2.toFixed(2)},${y2.toFixed(2)}`;

                        const line = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
                        line.setAttribute('class', 'impact-map-line');
                        line.setAttribute('data-province-key', provinceKey);
                        line.setAttribute('points', linePoints);
                        line.setAttribute('stroke', color);

                        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                        dot.setAttribute('class', 'impact-map-line-dot');
                        dot.setAttribute('data-province-key', provinceKey);
                        dot.setAttribute('cx', x1.toFixed(2));
                        dot.setAttribute('cy', y1.toFixed(2));
                        dot.setAttribute('r', '3');
                        dot.setAttribute('fill', color);

                        lineLayer.appendChild(line);
                        lineLayer.appendChild(dot);
                    });

                    wrapper.prepend(lineLayer);
                }

                function initCommodityMaps() {
                    sections.forEach((section) => {
                        const map = section.querySelector('.impact-philippine-map');
                        const pins = Array.from(section.querySelectorAll('.impact-map-pin'));

                        if (!map || !pins.length) return;

                        const highestCount = pins.reduce((max, pin) => {
                            return Math.max(max, parseInt(pin.dataset.count || '1', 10));
                        }, 1);

                        function setHovered(provinceKey, isHovered) {
                            const pin = pins.find((item) => item.dataset.provinceKey === provinceKey);
                            if (pin) {
                                pin.classList.toggle('is-hovered', isHovered);
                            }

                            getProvincePaths(map, provinceKey).forEach((path) => {
                                path.classList.toggle('is-hovered', isHovered);
                            });

                            section.querySelectorAll(`.impact-map-line[data-province-key="${provinceKey}"], .impact-map-line-dot[data-province-key="${provinceKey}"]`).forEach((line) => {
                                line.classList.toggle('is-hovered', isHovered);
                            });
                        }

                        pins.forEach((pin) => {
                            const provinceKey = pin.dataset.provinceKey;
                            const color = pin.dataset.color || '#237823';
                            const count = parseInt(pin.dataset.count || '1', 10);
                            const fillAlpha = Math.max(0.35, count / highestCount);
                            const provincePaths = getProvincePaths(map, provinceKey);

                            provincePaths.forEach((path) => {
                                path.classList.add('impact-province-active');
                                path.style.fill = hexToRgba(color, fillAlpha);

                                path.addEventListener('mouseenter', () => setHovered(provinceKey, true));
                                path.addEventListener('mouseleave', () => setHovered(provinceKey, false));
                                path.addEventListener('focus', () => setHovered(provinceKey, true));
                                path.addEventListener('blur', () => setHovered(provinceKey, false));
                                path.setAttribute('tabindex', '0');
                            });

                            pin.addEventListener('mouseenter', () => setHovered(provinceKey, true));
                            pin.addEventListener('mouseleave', () => setHovered(provinceKey, false));
                        });

                        drawCommodityLines(section);
                    });
                }

                function redrawAllCommodityLines() {
                    sections.forEach(drawCommodityLines);
                }

                let mapLineResizeTimer;

                function scheduleCommodityLineRedraw() {
                    window.clearTimeout(mapLineResizeTimer);
                    mapLineResizeTimer = window.setTimeout(redrawAllCommodityLines, 120);
                }

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

                initCommodityMaps();
                window.addEventListener('load', redrawAllCommodityLines);
                window.addEventListener('resize', scheduleCommodityLineRedraw);

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
