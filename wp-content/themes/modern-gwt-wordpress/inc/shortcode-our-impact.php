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
        // https://docs.google.com/spreadsheets/d/1IUsPVUw9smJfWQdl6TrTeuxhp30K6Ia2OWFeJ0l47aM/edit?gid=1339515758#gid=1339515758
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
                                array('label' => 'Scientific Journal Articles', 'value' => '134', 'color' => '#1f5d2b'),
                                array('label' => 'Abstracts', 'value' => '52', 'color' => '#D5DA65'),
                                array('label' => 'Book/Chapter Book/Handbook', 'value' => '8', 'color' => '#55A147'),
                                array('label' => 'IP/Patents/PVP', 'value' => '7', 'color' => '#D5DA65'),
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
                                array('label' => 'Staff', 'value' => '18', 'color' => '#55A147'),
                        )
                ),
                'partners_collaborators' => array(
                        'number' => null,
                        'label' => 'Manpower and Collaborators',
                        'sublabel' => 'Personnels, Academic, Government & Global Partners',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'SUCs', 'value' => '27', 'color' => '#1f5d2b'),
                                array('label' => 'DA Agencies', 'value' => '3', 'color' => '#D5DA65'),
                                array('label' => 'International', 'value' => '3', 'color' => '#8BC34A'),
                                array('label' => 'Scientists', 'value' => '4', 'color' => '#55A147'),
                                array('label' => 'Researchers', 'value' => '52', 'color' => '#1f5d2b'),
                        )
                ),
                'iec_reach' => array(
                        'number' => null,
                        'label' => 'Public Engagement',
                        'sublabel' => 'Information & Education Campaigns',
                        'color' => '#1f5d2b',
                        'breakdown' => array(
                                array('label' => 'Regional Crop Biotechnology Symposium Participants', 'value' => '3,803', 'color' => '#1f5d2b'),
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
                'coffee-cavite' => array('commodity' => 'Coffee', 'image' => $commodity_image_base . 'p-coffee.webp', 'color' => '#6f4e37', 'province' => 'Cavite', 'projects' => '5'),
                'coffee-benguet' => array('commodity' => 'Coffee', 'image' => $commodity_image_base . 'p-coffee.webp', 'color' => '#6f4e37', 'province' => 'Benguet', 'projects' => '4'),
                'cacao-benguet' => array('commodity' => 'Cacao', 'image' => $commodity_image_base . 'p-cacao.webp', 'color' => '#8b5a2b', 'province' => 'Benguet', 'projects' => '3'),
                'cacao-metro-manila' => array('commodity' => 'Cacao', 'image' => $commodity_image_base . 'p-cacao.webp', 'color' => '#8b5a2b', 'province' => 'Metro Manila', 'projects' => '3'),
                'cacao-camarines-sur' => array('commodity' => 'Cacao', 'image' => $commodity_image_base . 'p-cacao.webp', 'color' => '#8b5a2b', 'province' => 'Camarines Sur', 'projects' => '1'),
                'cacao-bohol' => array('commodity' => 'Cacao', 'image' => $commodity_image_base . 'p-cacao.webp', 'color' => '#8b5a2b', 'province' => 'Bohol', 'projects' => '1'),
                'pili-camarines-sur' => array('commodity' => 'Pili Nut', 'image' => $commodity_image_base . 'p-pili.webp', 'color' => '#237823', 'province' => 'Camarines Sur', 'projects' => '8'),
                'pili-laguna' => array('commodity' => 'Pili Nut', 'image' => $commodity_image_base . 'p-pili.webp', 'color' => '#237823', 'province' => 'Laguna', 'projects' => '7'),
                'pili-northern-samar' => array('commodity' => 'Pili Nut', 'image' => $commodity_image_base . 'p-pili.webp', 'color' => '#237823', 'province' => 'Northern Samar', 'projects' => '4'),
                'purpleyam-bohol' => array('commodity' => 'Purple Yam', 'image' => $commodity_image_base . 'p-purple-yam.webp', 'color' => '#6f2da8', 'province' => 'Bohol', 'projects' => '4'),
                'purpleyam-leyte' => array('commodity' => 'Purple Yam', 'image' => $commodity_image_base . 'p-purple-yam.webp', 'color' => '#6f2da8', 'province' => 'Leyte', 'projects' => '11'),
                'rice-nueva-ecija' => array('commodity' => 'Rice', 'image' => $commodity_image_base . 'p-rice.webp', 'color' => '#ca8a04', 'province' => 'Nueva Ecija', 'projects' => '1'),
                'rice-agusan-del-sur' => array('commodity' => 'Rice', 'image' => $commodity_image_base . 'p-rice.webp', 'color' => '#ca8a04', 'province' => 'Agusan del Sur', 'projects' => '1'),
                'rice-negros-occidental' => array('commodity' => 'Rice', 'image' => $commodity_image_base . 'p-rice.webp', 'color' => '#ca8a04', 'province' => 'Negros Occidental', 'projects' => '1'),
                'rubber-zamboanga-del-sur' => array('commodity' => 'Rubber', 'image' => $commodity_image_base . 'p-rubber.webp', 'color' => '#0891b2', 'province' => 'Zamboanga del Sur', 'projects' => '2'),
                'cassava-laguna' => array('commodity' => 'Cassava', 'image' => $commodity_image_base . 'p-cassava.webp', 'color' => '#0f766e', 'province' => 'Laguna', 'projects' => '2'),
        );

        $impact_commodities = apply_filters('cbc_impact_commodities_data', $impact_commodities);
        $impact_global_engagements = array(
                // Replace the sample training counts below with the latest engagement totals when available.
                'south-korea-training' => array('commodity' => 'Training', 'icon_label' => 'KR', 'color' => '#2563eb', 'country' => 'South Korea', 'map_key' => 'KR', 'projects' => '1', 'count_label' => 'training'),
                'japan-training' => array('commodity' => 'Training', 'icon_label' => 'JP', 'color' => '#dc2626', 'country' => 'Japan', 'map_key' => 'JP', 'projects' => '1', 'count_label' => 'training'),
                'thailand-training' => array('commodity' => 'Training', 'icon_label' => 'TH', 'color' => '#0f766e', 'country' => 'Thailand', 'map_key' => 'TH', 'projects' => '1', 'count_label' => 'training'),
                'malaysia-training' => array('commodity' => 'Training', 'icon_label' => 'MY', 'color' => '#0891b2', 'country' => 'Malaysia', 'map_key' => 'MY', 'projects' => '1', 'count_label' => 'training'),
                'vietnam-training' => array('commodity' => 'Training', 'icon_label' => 'VN', 'color' => '#ca8a04', 'country' => 'Vietnam', 'map_key' => 'VN', 'projects' => '1', 'count_label' => 'training'),
        );

        $impact_global_engagements = apply_filters('cbc_impact_global_engagements_data', $impact_global_engagements);

        $build_location_groups = static function ($items, $location_field) {
            $location_groups = array();

            foreach ($items as $item_key => $item) {
                if (empty($item[$location_field]) || empty($item['commodity'])) {
                    continue;
                }

                $location_name = $item[$location_field];
                $location_key = !empty($item['map_key']) ? $item['map_key'] : sanitize_title($location_name);
                $item['key'] = $item_key;

                if (!isset($location_groups[$location_key])) {
                    $location_groups[$location_key] = array(
                            'key' => $location_key,
                            'location' => $location_name,
                            'color' => isset($item['color']) ? $item['color'] : '#237823',
                            'projects' => 0,
                            'count_label' => isset($item['count_label']) ? $item['count_label'] : 'project',
                            'items' => array(),
                    );
                }

                $location_groups[$location_key]['projects'] += isset($item['projects']) ? (int) $item['projects'] : 0;
                $location_groups[$location_key]['items'][] = $item;
            }

            return array_values($location_groups);
        };

        $split_location_groups = static function ($location_groups) {
            $total_locations = count($location_groups);
            $midpoint = (int) ceil($total_locations / 2);

            return array(
                    array_slice($location_groups, 0, $midpoint),
                    array_slice($location_groups, $midpoint),
            );
        };

        $province_commodities = $build_location_groups($impact_commodities, 'province');
        $global_engagement_countries = $build_location_groups($impact_global_engagements, 'country');

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

        list($left_provinces, $right_provinces) = $split_location_groups($province_commodities);
        list($left_countries, $right_countries) = $split_location_groups($global_engagement_countries);

        $render_impact_pin = static function ($location_item, $side = 'left') {
            $commodity_names = array_values(array_unique(wp_list_pluck($location_item['items'], 'commodity')));
            $count_label = !empty($location_item['count_label']) ? $location_item['count_label'] : 'project';
            $projects_label = number_format((int) $location_item['projects']) . ' ' . $count_label . ((int) $location_item['projects'] === 1 ? '' : 's');
            ?>
            <div class="impact-map-pin impact-map-pin-<?php echo esc_attr($side); ?>"
                 data-location-key="<?php echo esc_attr($location_item['key']); ?>"
                 data-color="<?php echo esc_attr($location_item['color']); ?>"
                 data-count="<?php echo esc_attr(count($location_item['items'])); ?>"
                 style="--pin-color: <?php echo esc_attr($location_item['color']); ?>">

                <div class="impact-map-icons" aria-hidden="true">
                    <?php foreach (array_slice($location_item['items'], 0, 3) as $commodity): ?>
                        <?php if (!empty($commodity['image'])): ?>
                            <img src="<?php echo esc_url($commodity['image']); ?>" alt="">
                        <?php else: ?>
                            <span class="impact-map-icon-badge"><?php echo esc_html(!empty($commodity['icon_label']) ? $commodity['icon_label'] : strtoupper(substr($commodity['commodity'], 0, 2))); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="impact-map-content">
                    <div class="impact-map-province"><?php echo esc_html($location_item['location']); ?></div>
                    <div class="impact-map-commodities"><?php echo esc_html(implode(', ', $commodity_names)); ?></div>
                    <div class="impact-map-projects"><?php echo esc_html($projects_label); ?></div>
                </div>
            </div>
            <?php
        };

        $render_inline_map = static function ($provided_path, $fallback_path, $map_class, $aria_label, $normalize_province_ids = false) {
            $map_path = $fallback_path;

            if ($provided_path && !preg_match('#^https?://#i', $provided_path) && file_exists($provided_path)) {
                $map_path = $provided_path;
            } elseif ($provided_path && !preg_match('#^https?://#i', $provided_path)) {
                $possible_path = ABSPATH . ltrim($provided_path, '/');
                if (file_exists($possible_path)) {
                    $map_path = $possible_path;
                }
            }

            $map_is_svg = $map_path && strtolower(pathinfo($map_path, PATHINFO_EXTENSION)) === 'svg';

            if (!$map_is_svg || !is_readable($map_path)) {
                echo '<p class="impact-map-unavailable">Province SVG map is unavailable.</p>';
                return;
            }

            $max_inline_size = 1024 * 1024; // 1 MB

            if (filesize($map_path) <= 0 || filesize($map_path) > $max_inline_size) {
                echo '<p class="impact-map-unavailable">Province SVG map is unavailable.</p>';
                return;
            }

            $svg = file_get_contents($map_path);
            $svg = preg_replace('#<script.*?>.*?</script>#is', '', $svg);
            $svg = preg_replace('/@click="[^"]*"/', '', $svg);
            $svg = preg_replace('/\s*ref="[^"]*"/', '', $svg);

            if ($normalize_province_ids) {
                $svg = preg_replace('/:?id="getProvince\(\'([a-z0-9-]+)\'\)"/', 'id="$1"', $svg);
            }

            $svg = preg_replace('/<svg\b/', '<svg class="' . esc_attr($map_class) . '" role="img" aria-label="' . esc_attr($aria_label) . '"', $svg, 1);

            echo $svg;
        };

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
                padding: 1rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                min-height: 200px;
                position: relative;
                overflow: hidden;
                height: 100%;
            }

            .impact-map-carousel {
                width: 100%;
            }

            .impact-map-carousel-header {
                align-items: center;
                display: flex;
                gap: 0.75rem;
                justify-content: space-between;
                margin-bottom: 1rem;
                width: 100%;
            }

            .impact-map-carousel-kicker {
                color: #64748b;
                display: block;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .impact-map-carousel-title {
                color: #1f5d2b;
                font-size: 1rem;
                font-weight: 800;
                line-height: 1.15;
                margin-top: 0.18rem;
            }

            .impact-map-carousel-nav {
                display: inline-flex;
                gap: 0.4rem;
            }

            .impact-map-carousel-button {
                align-items: center;
                background: #f1f5f9;
                border: 1px solid rgba(31, 93, 43, 0.08);
                color: #1f5d2b;
                cursor: pointer;
                display: inline-flex;
                font-size: 1rem;
                height: 2rem;
                justify-content: center;
                line-height: 1;
                width: 2rem;
            }

            .impact-map-carousel-button:hover,
            .impact-map-carousel-button:focus-visible {
                background: #e2f1e0;
                outline: none;
            }

            .impact-map-slides {
                position: relative;
                width: 100%;
            }

            .impact-map-slide[hidden] {
                display: none;
            }

            .impact-map-pagination {
                display: flex;
                gap: 0.4rem;
                justify-content: center;
                margin-top: 0.85rem;
            }

            .impact-map-pagination button {
                background: #cbd5e1;
                border: 0;
                cursor: pointer;
                height: 0.55rem;
                padding: 0;
                width: 1.75rem;
            }

            .impact-map-pagination button.is-active {
                background: #1f5d2b;
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
                    grid-row: span 2;
                    min-height: 560px;
                    height: 100%;
                }
            }

            .impact-map-wrapper {
                width: 100%;
                display: grid;
                grid-template-columns: 0.5fr 2fr 0.5fr;
                gap: 1rem;
                position: relative;
                align-items: center;
            }

            .impact-map-figure {
                position: relative;
                min-height: 300px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1;
            }

            .world-impact-map-figure svg {
                max-height: 320px;
                width: 100%;
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
                background: #f8faf7;
                display: grid;
                align-items: center;
                gap: 0.65rem;
                padding: 0.45rem 0.65rem;
                transition: all 0.2s ease;
                border: none;
            }

            .impact-map-pin-left {
                grid-template-columns: 1fr 2.25rem;
                text-align: right;
                border-right: 4px solid var(--pin-color);
                border-left: none;
            }

            .impact-map-pin-left .impact-map-icons {
                order: 2;
                justify-content: flex-end;
            }

            .impact-map-pin-right {
                grid-template-columns: 2.25rem 1fr;
                text-align: left;
                border-left: 4px solid var(--pin-color, #237823);
                border-right: none;
            }

            .impact-map-pin-right .impact-map-icons {
                justify-content: flex-start;
            }

            .impact-map-pin-right .impact-map-icons img {
                margin-right: -0.45rem;
                margin-left: 0;
            }

            .impact-map-pin-left .impact-map-icons img {
                margin-left: 0;
                margin-right: -0.45rem;
                justify-content: start;
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
                width: auto;
                margin-left: -0.45rem;
                object-fit: contain;
                padding: 0.12rem;
            }

            .impact-map-icon-badge {
                align-items: center;
                background: #ffffff;
                border: 1px solid rgba(31, 93, 43, 0.15);
                border-radius: 999px;
                color: #1f5d2b;
                display: inline-flex;
                font-size: 0.58rem;
                font-weight: 800;
                height: 1.65rem;
                justify-content: center;
                margin-left: -0.45rem;
                min-width: 1.65rem;
                padding: 0 0.3rem;
            }

            .impact-map-icons img:first-child {
                margin-left: 0;
                margin-right: 0;
                z-index: 3;
            }

            .impact-map-icons > :first-child {
                margin-left: 0;
                margin-right: 0;
                z-index: 3;
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

            /* Reverse the pin alignment for the right side */
            .impact-map-list-right .impact-map-pin {
                border-left: 4px solid var(--pin-color, #237823);
                border-right: 0;
                text-align: left;
            }

            .impact-map-list-right .impact-map-icons {
                justify-content: flex-end;
            }

            .impact-map-list-right .impact-map-icons img {
                margin-left: 0;
                margin-right: -0.45rem;
            }

            .impact-map-list-right .impact-map-icons img:last-child {
                margin-right: 0;
            }

            @media (min-width: 768px) {
                .impact-map-wrapper {
                    grid-template-columns: 1fr 1.5fr 1fr;
                }
            }

            .impact-map-list {
                display: flex;
                flex-direction: column;
                gap: 0.55rem;
                z-index: 3;
            }

            @media (min-width: 1024px) {
                .impact-map-wrapper svg {
                    max-height: 500px;
                }

                .world-impact-map-figure svg {
                    max-height: 360px;
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

                .impact-map-carousel-header {
                    align-items: flex-start;
                    flex-direction: column;
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
                    <div class="impact-map-card rounded-lg select-none">
                        <div class="impact-map-carousel" data-impact-carousel>
                            <div class="impact-map-carousel-header">
                                <div>
                                    <span class="impact-map-carousel-kicker">Impact Maps</span>
                                    <div class="impact-map-carousel-title" data-impact-carousel-title>Philippine Commodity Footprint</div>
                                </div>
                                <div class="impact-map-carousel-nav">
                                    <button class="impact-map-carousel-button" type="button" data-impact-carousel-prev aria-label="Show previous impact map">&larr;</button>
                                    <button class="impact-map-carousel-button" type="button" data-impact-carousel-next aria-label="Show next impact map">&rarr;</button>
                                </div>
                            </div>

                            <div class="impact-map-slides">
                                <div class="impact-map-slide" data-impact-slide data-slide-title="Philippine Commodity Footprint">
                                    <div class="impact-map-wrapper">
                                        <div class="impact-map-list impact-map-list-left">
                                            <?php foreach ($left_provinces as $province_item): ?>
                                                <?php $render_impact_pin($province_item, 'left'); ?>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="impact-map-figure">
                                            <?php
                                            $theme_map_path = get_template_directory() . '/assets/svgs/phMap.svg';
                                            $provided = isset($atts['map_svg_path']) ? $atts['map_svg_path'] : '';
                                            $render_inline_map($provided, $theme_map_path, 'impact-location-map impact-philippine-map', 'Philippines commodity map', true);
                                            ?>
                                        </div>

                                        <div class="impact-map-list impact-map-list-right">
                                            <?php foreach ($right_provinces as $province_item): ?>
                                                <?php $render_impact_pin($province_item, 'right'); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="impact-map-slide" data-impact-slide data-slide-title="Global Training Engagements" hidden>
                                    <div class="impact-map-wrapper">
                                        <div class="impact-map-list impact-map-list-left">
                                            <?php foreach ($left_countries as $country_item): ?>
                                                <?php $render_impact_pin($country_item, 'left'); ?>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="impact-map-figure world-impact-map-figure">
                                            <?php
                                            $world_map_path = get_template_directory() . '/assets/svgs/worldMap.svg';
                                            $render_inline_map($world_map_path, $world_map_path, 'impact-location-map impact-world-map', 'World training engagement map', false);
                                            ?>
                                        </div>

                                        <div class="impact-map-list impact-map-list-right">
                                            <?php foreach ($right_countries as $country_item): ?>
                                                <?php $render_impact_pin($country_item, 'right'); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="impact-map-pagination" aria-label="Impact map pages">
                                <button type="button" class="is-active" data-impact-carousel-dot aria-label="Show Philippine impact map"></button>
                                <button type="button" data-impact-carousel-dot aria-label="Show world impact map"></button>
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

                function getMapPaths(map, locationKey) {
                    if (!map || !locationKey) return [];

                    if (window.CSS && CSS.escape) {
                        return Array.from(map.querySelectorAll(`#${CSS.escape(locationKey)}`));
                    }

                    return Array.from(map.querySelectorAll('[id]')).filter((item) => item.id === locationKey);
                }

                function clamp(value, min, max) {
                    return Math.min(Math.max(value, min), max);
                }

                function getProvinceAnchor(elements, wrapperRect) {
                    const anchors = elements.map((element) => {
                        // Use getBoundingClientRect to get exact screen pixels
                        // instead of SVG internal coordinates, ensuring perfect alignment.
                        const rect = element.getBoundingClientRect();

                        if (!rect || !rect.width || !rect.height) {
                            return null;
                        }

                        return {
                            x: rect.left + (rect.width / 2) - wrapperRect.left,
                            y: rect.top + (rect.height / 2) - wrapperRect.top,
                            weight: rect.width * rect.height,
                        };
                    }).filter(Boolean);

                    if (!anchors.length) return null;

                    const totalWeight = anchors.reduce((sum, anchor) => sum + anchor.weight, 0);

                    // Calculate the weighted center if a province has multiple path pieces (like islands)
                    return anchors.reduce((result, anchor) => {
                        return {
                            x: result.x + (anchor.x * anchor.weight / totalWeight),
                            y: result.y + (anchor.y * anchor.weight / totalWeight),
                        };
                    }, { x: 0, y: 0 });
                }

                function getPinAnchor(pin, pinRect, provinceAnchor, wrapperRect) {
                    // If it's in the left list, the 'inner' side facing the map is the RIGHT edge (pinRect.right)
                    // If it's in the right list, the 'inner' side facing the map is the LEFT edge (pinRect.left)
                    const isLeftList = pin.closest('.impact-map-list-left') !== null;

                    const localLeft = pinRect.left - wrapperRect.left;
                    const localRight = pinRect.right - wrapperRect.left;
                    const localTop = pinRect.top - wrapperRect.top;
                    const localBottom = pinRect.bottom - wrapperRect.top;

                    return {
                        x: isLeftList ? localRight : localLeft,
                        y: clamp(provinceAnchor.y, localTop + 5, localBottom - 5),
                    };
                }

                function drawCommodityLines(wrapper) {
                    const map = wrapper ? wrapper.querySelector('.impact-location-map') : null;
                    const pins = wrapper ? Array.from(wrapper.querySelectorAll('.impact-map-pin')) : [];

                    if (!wrapper || !map || !pins.length || wrapper.offsetParent === null) return;

                    // Toggle this or pass it as a parameter/data-attribute
                    const useZigZag = false;

                    wrapper.querySelectorAll('.impact-map-lines').forEach((existingLines) => existingLines.remove());

                    const wrapperRect = wrapper.getBoundingClientRect();
                    const lineLayer = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    lineLayer.setAttribute('class', 'impact-map-lines');
                    lineLayer.setAttribute('viewBox', `0 0 ${wrapperRect.width} ${wrapperRect.height}`);
                    lineLayer.setAttribute('preserveAspectRatio', 'none');
                    lineLayer.setAttribute('aria-hidden', 'true');

                    pins.forEach((pin) => {
                        const locationKey = pin.dataset.locationKey;
                        const provinceAnchor = getProvinceAnchor(getMapPaths(map, locationKey), wrapperRect);
                        const pinRect = pin.getBoundingClientRect();

                        if (!provinceAnchor || !pinRect.width || !pinRect.height) return;

                        const pinAnchor = getPinAnchor(pin, pinRect, provinceAnchor, wrapperRect);

                        const x1 = provinceAnchor.x;
                        const y1 = provinceAnchor.y;
                        const x2 = pinAnchor.x;
                        const y2 = pinAnchor.y;
                        const color = pin.dataset.color || '#237823';

                        let linePoints;

                        if (useZigZag) {
                            const pinTop = pinRect.top - wrapperRect.top;
                            const pinBottom = pinRect.bottom - wrapperRect.top;
                            const verticalApproach = Math.abs(y2 - pinTop) < 1 || Math.abs(y2 - pinBottom) < 1;

                            linePoints = verticalApproach
                                ? `${x1.toFixed(2)},${y1.toFixed(2)} ${x1.toFixed(2)},${((y1 + y2) / 2).toFixed(2)} ${x2.toFixed(2)},${((y1 + y2) / 2).toFixed(2)} ${x2.toFixed(2)},${y2.toFixed(2)}`
                                : `${x1.toFixed(2)},${y1.toFixed(2)} ${((x1 + x2) / 2).toFixed(2)},${y1.toFixed(2)} ${((x1 + x2) / 2).toFixed(2)},${y2.toFixed(2)} ${x2.toFixed(2)},${y2.toFixed(2)}`;
                        } else {
                            // Clean Direct Line
                            linePoints = `${x1.toFixed(2)},${y1.toFixed(2)} ${x2.toFixed(2)},${y2.toFixed(2)}`;
                        }

                        const line = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
                        line.setAttribute('class', 'impact-map-line');
                        line.setAttribute('points', linePoints);
                        line.setAttribute('stroke', color);
                        line.setAttribute('fill', 'none'); // Ensure polyline doesn't try to fill

                        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                        dot.setAttribute('class', 'impact-map-line-dot');
                        dot.setAttribute('data-location-key', locationKey);
                        dot.setAttribute('cx', x1.toFixed(2));
                        dot.setAttribute('cy', y1.toFixed(2));
                        dot.setAttribute('r', '3');
                        dot.setAttribute('fill', color);

                        line.setAttribute('data-location-key', locationKey);
                        lineLayer.appendChild(line);
                        lineLayer.appendChild(dot);
                    });

                    wrapper.prepend(lineLayer);
                }

                function initCommodityMaps() {
                    const wrappers = Array.from(document.querySelectorAll('.impact-map-wrapper'));

                    wrappers.forEach((wrapper) => {
                        const map = wrapper.querySelector('.impact-location-map');
                        const pins = Array.from(wrapper.querySelectorAll('.impact-map-pin'));

                        if (!map || !pins.length) return;

                        const highestCount = pins.reduce((max, pin) => {
                            return Math.max(max, parseInt(pin.dataset.count || '1', 10));
                        }, 1);

                        function setHovered(locationKey, isHovered) {
                            const pin = pins.find((item) => item.dataset.locationKey === locationKey);
                            if (pin) {
                                pin.classList.toggle('is-hovered', isHovered);
                            }

                            getMapPaths(map, locationKey).forEach((path) => {
                                path.classList.toggle('is-hovered', isHovered);
                            });

                            wrapper.querySelectorAll(`.impact-map-line[data-location-key="${locationKey}"], .impact-map-line-dot[data-location-key="${locationKey}"]`).forEach((line) => {
                                line.classList.toggle('is-hovered', isHovered);
                            });
                        }

                        pins.forEach((pin) => {
                            const locationKey = pin.dataset.locationKey;
                            const color = pin.dataset.color || '#237823';
                            const count = parseInt(pin.dataset.count || '1', 10);
                            const fillAlpha = Math.max(0.35, count / highestCount);
                            const provincePaths = getMapPaths(map, locationKey);

                            provincePaths.forEach((path) => {
                                path.classList.add('impact-province-active');
                                path.style.fill = hexToRgba(color, fillAlpha);

                                path.addEventListener('mouseenter', () => setHovered(locationKey, true));
                                path.addEventListener('mouseleave', () => setHovered(locationKey, false));
                                path.addEventListener('focus', () => setHovered(locationKey, true));
                                path.addEventListener('blur', () => setHovered(locationKey, false));
                                path.setAttribute('tabindex', '0');
                            });

                            pin.addEventListener('mouseenter', () => setHovered(locationKey, true));
                            pin.addEventListener('mouseleave', () => setHovered(locationKey, false));
                        });

                        drawCommodityLines(wrapper);
                    });
                }

                function redrawAllCommodityLines() {
                    document.querySelectorAll('.impact-map-wrapper').forEach(drawCommodityLines);
                }

                let mapLineResizeTimer;

                function scheduleCommodityLineRedraw() {
                    window.clearTimeout(mapLineResizeTimer);
                    mapLineResizeTimer = window.setTimeout(redrawAllCommodityLines, 120);
                }

                function initMapCarousels() {
                    const carousels = Array.from(document.querySelectorAll('[data-impact-carousel]'));

                    carousels.forEach((carousel) => {
                        if (carousel.dataset.carouselReady === 'true') return;

                        carousel.dataset.carouselReady = 'true';

                        const slides = Array.from(carousel.querySelectorAll('[data-impact-slide]'));
                        const dots = Array.from(carousel.querySelectorAll('[data-impact-carousel-dot]'));
                        const title = carousel.querySelector('[data-impact-carousel-title]');
                        const prevButton = carousel.querySelector('[data-impact-carousel-prev]');
                        const nextButton = carousel.querySelector('[data-impact-carousel-next]');

                        if (!slides.length) return;

                        let activeIndex = 0;

                        function setActiveSlide(index) {
                            activeIndex = (index + slides.length) % slides.length;

                            slides.forEach((slide, slideIndex) => {
                                const isActive = slideIndex === activeIndex;
                                slide.hidden = !isActive;
                            });

                            dots.forEach((dot, dotIndex) => {
                                const isActive = dotIndex === activeIndex;
                                dot.classList.toggle('is-active', isActive);
                                dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                            });

                            if (title) {
                                title.textContent = slides[activeIndex].dataset.slideTitle || '';
                            }

                            window.requestAnimationFrame(redrawAllCommodityLines);
                        }

                        if (prevButton) {
                            prevButton.addEventListener('click', () => setActiveSlide(activeIndex - 1));
                        }

                        if (nextButton) {
                            nextButton.addEventListener('click', () => setActiveSlide(activeIndex + 1));
                        }

                        dots.forEach((dot, dotIndex) => {
                            dot.addEventListener('click', () => setActiveSlide(dotIndex));
                        });

                        setActiveSlide(0);
                    });
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

                initMapCarousels();
                initCommodityMaps();
                setTimeout(redrawAllCommodityLines, 150);
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
