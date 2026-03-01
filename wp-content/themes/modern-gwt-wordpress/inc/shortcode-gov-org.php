<?php
/**
 * Shortcode for displaying the organizational chart.
 * Usage: [cbc_org_chart data='JSON_ENCODED_DATA']
*/

if (!function_exists('cbc_organizational_chart_shortcode')) {
	function cbc_organizational_chart_shortcode($atts) {
		$atts = shortcode_atts(array(
			'data' => [],
		), $atts, 'cbc_org_chart');

		$structure = cbc_org_chart_default_structure();

		ob_start();
		?>
		<div class="cbc-org-chart-container space-y-6 gap-6">
			<?php foreach ($structure as $section): ?>
            <div>
				<?php if (!empty($section['heading'])):
                    echo '<h1 class="text-lg font-bold text-center text-[#1f5d2b] flex flex-col items-center my-2">
                            <span class="block mx-auto w-full h-[3px] mb-2 bg-gradient-to-r from-[#1f5d2b] to-[#a2b917]"></span>
                            <span id="' . sanitize_title( $section['heading'] ) . '" class="transition duration-700 ease-out will-change-transform opacity-100 translate-y-0 mt-3">' . $section['heading'] . '</span>
                          </h1>';
					//echo govph_section_header( $section['heading'], [ 'id' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $section['heading']))) . '_header', 'classes' => 'text-sm font-extrabold  drop-shadow text-white p-2 bg-gradient-to-r text-center px-5' ] );
				endif; ?>

				<!-- CASE 1: Direct items -->
				<?php if (!empty($section['items']) && is_array($section['items'])): ?>
                <div class="flex flex-wrap justify-center gap-6">
                    <?php foreach ($section['items'] as $person): ?>
                        <?php echo cbc_org_chart_render_person($person); ?>
                    <?php endforeach; ?>
                </div>
				<?php endif; ?>

				<!-- CASE 2: Section has grouped subcategories -->
				<?php if (!empty($section['groups']) && is_array($section['groups'])): ?>
                <div class="space-y-6">
                    <?php foreach ($section['groups'] as $group): ?>
                        <?php if (!empty($group['label'])): ?>
                            <h3 class="text-lg font-semibold text-[#1f5d2b] text-center mb-4">
                                <?php echo esc_html($group['label']); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if (!empty($group['items']) && is_array($group['items'])): ?>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 justify-items-center">
                                <?php foreach ($group['items'] as $person): ?>
                                    <?php echo cbc_org_chart_render_person($person); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
				<?php endif; ?>
            </div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}


	function cbc_org_chart_render_person($person) {
		$name  = esc_html($person['name'] ?? '');
		$title = esc_html($person['title'] ?? '');
		$img   = esc_url($person['img'] ?? '');
		$alt   = esc_attr($person['alt'] ?? $name);
		$link  = esc_url($person['link'] ?? '');

		$hover_class = $link ? 'hover:scale-105 transition-transform duration-200' : '';

		$card  = "<div class='relative flex flex-col gap-2 w-48 h-64 rounded drop-shadow-md overflow-hidden $hover_class'>";
		$card .= "<img src='$img' alt='$alt' class='w-full h-full object-cover object-top rounded'>";
		$card .= "<div class='absolute bottom-0 w-full bg-gradient-to-t from-[#1f5d2b] to-transparent text-white p-2 text-xs line-clamp-2 whitespace-normal'>";
		$card .= "<p class='font-semibold text-center m-0 !leading-tight select-none'>$name</p>";
		$card .= "<p class='text-center italic m-0 text-[9px] !leading-tight min-h-[2.5em] max-h-[2.5em] select-none'>$title</p>";

		$card .= "</div></div>";

		if ($link) {
			return "<a href='$link' class='cbc-org-card block'>$card</a>";
		}

		return $card;
	}


	function cbc_org_chart_default_structure() {
		return array(
            array(
                'heading' => 'Office of the President',
                'items' => array(
                    array(
                        'name' => 'Ferdinand R. Marcos Jr.',
                        'title' => 'Republic of the Philippines President',
                        'img' => '/wp-content/uploads/2024/06/BBM-Profile-Pic2.webp',
                        'alt' => 'Ferdinand R. Marcos Jr.',
                        'link' => 'https://op-proper.gov.ph/',
                    ),
                ),
            ),
			array(
				'heading' => 'Office of the Secretary',
				'items' => array(
					array(
						'name' => 'Francisco Tiu Laurel Jr.',
						'title' => 'Secretary of Agriculture',
						'img' => '/wp-content/uploads/2025/10/Sec.-Laurel.png',
						'alt' => 'Francisco Tiu Laurel Jr.',
                        'link' => 'https://www.da.gov.ph/about-us/directory-of-officials/',
					),

				),
			),
            array(
                'heading' => 'Office of the Undersecretary for Policy, Planning, and Regulations',
                'items' => array(
                    array(
                        'name' => 'Atty. Asis G. Perez',
                        'title' => 'Undersecretary for Policy, Planning, and Regulations',
                        'img' => '/wp-content/uploads/2025/10/asis-perez.jpg',
                        'alt' => 'Atty. Asis G. Perez',
                        'link' => 'https://www.da.gov.ph/about-us/directory-of-officials/',
                    ),
                ),
            ),
            array(
                'heading' => 'Program Management',
                'items' => array(
                    array(
                        'name' => 'Paul C. Limson, DVM',
                        'title' => 'Biotechnology Program Director',
                        'img' => '/wp-content/uploads/2025/10/no-profile.jpg',
                        'alt' => 'Paul C. Limson, DVM',
                        'link' => 'https://bpo.da.gov.ph/',
                    ),
                ),
            ),
            array(
                'heading' => 'Center Management',
                'items' => array(
                    array(
                            'name' => 'Roel R. Suralta, PhD',
                            'title' => 'Center Chief, DA-CBC',
                            'img' => '/wp-content/uploads/2024/06/RRSuralta-scaled-e1717643185253.jpg',
                            'alt' => 'Roel R. Suralta, PhD',
                            'link' => '/about-us/organizational-structure/dr-roel-r-suralta/',
                    ),
                ),
            ),
			array(
				'heading' => 'Experts',
				'items' => array(
					array(
						'name' => 'Nonawin L. Agustin, PhD',
						'title' => 'Project Officer V',
						'img' => '/wp-content/uploads/2024/06/Agustin-500x736.png',
						'alt' => 'Nonawin L. Agustin, PhD',
						'link' => '/about-us/organizational-structure/dr-nonawin-l-agustin/',
					),
					array(
						'name' => 'Reynante L. Ordonio, PhD',
						'title' => 'Senior Science Research Specialist',
						'img' => '/wp-content/uploads/2024/06/Ordonio-500x736.png',
						'alt' => 'Reynante L. Ordonio, PhD',
						'link' => '/about-us/organizational-structure/dr-reynante-l-ordonio/',
					),
					array(
						'name' => 'Arlen Anglacer Dela Cruz, PhD',
						'title' => 'Supervising Science Research Specialist',
						'img' => '/wp-content/uploads/2024/06/Doc-Arlen-500x739.jpg',
						'alt' => 'Arlen Anglacer Dela Cruz, PhD',
						'link' => '/about-us/organizational-structure/dr-arlen-anglacer-dela-cruz/',
					),
                    array(
						'name' => 'Shyatesa C. Razo-Regino, PhD',
						'title' => 'Balik Scienctist Fellow',
						'img' => '/wp-content/uploads/2025/10/shaya-500x532.png',
						'alt' => 'Shyatesa C. Razo-Regino, PhD',
						'link' => '/about-us/organizational-structure/shyatesa-c-razo-regino/',
					),
				),
			),
			/*array(
				'heading' => 'R&D Management Team',
				'groups' => array(
					array(
						'label' => 'Technology Development and Innovation Group',
						'items' => array(
							array(
								'name' => 'Jayvee Garcia',
								'title' => 'Science Research Specialist I',
								'img' => '/wp-content/uploads/2025/09/Jayvee-500x500.png',
								'alt' => 'Jayvee Garcia',
							),
						),
					),
					array(
						'label' => 'R4D Biotechnology Capacity-Building Service Group',
						'items' => array(
                            array(
                                    'name' => 'Ephraim Dioeve Yarcia',
                                    'title' => 'Science Research Specialist I',
                                    'img' => '/wp-content/uploads/2025/09/Paem-500x500.png',
                                    'alt' => 'Ephraim Dioeve Yarcia',
                            ),
							array(
								'name' => 'Rebecca I. Santos',
								'title' => 'Science Research Specialist I',
								'img' => '/wp-content/uploads/2025/09/Becca-500x500.png',
								'alt' => 'Rebecca I. Santos',
							),
						),
					),
					array(
						'label' => 'Technology Commercialization and Management Group',
						'items' => array(
                            array(
                                    'name' => 'Benson Munar',
                                    'title' => 'Science Research Specialist I',
                                    'img' => '/wp-content/uploads/2025/09/Benson-500x500.png',
                                    'alt' => 'Benson Munar',
                            ),
							array(
								'name' => 'Cristo Rey C. Magdadaro',
								'title' => 'Science Research Specialist I',
								'img' => '/wp-content/uploads/2025/09/Cris-500x500.png',
								'alt' => 'Cristo Rey C. Magdadaro',
							),
                            array(
                                    'name' => 'Precious Mae Gabato',
                                    'title' => 'Science Research Specialist II',
                                    'img' => '/wp-content/uploads/2025/09/Precious-500x500.png',
                                    'alt' => 'Precious Mae Gabato',
                            ),
						),
					),
				),
			),
			array(
				'heading' => 'Roots Team',
				'items' => array(
					array(
						'name' => 'Nonawin L. Agustin, PhD',
						'title' => 'Project Officer V',
						'img' => '/wp-content/uploads/2024/06/Agustin-e1717643254281.png',
						'alt' => 'Nonawin L. Agustin, PhD',
						'link' => '/about-us/organizational-structure/dr-nonawin-l-agustin/',
					),
					array(
						'name' => 'Amabel Achuela',
						'title' => 'Science Research Specialist I',
						'img' => '/wp-content/uploads/2025/10/no-profile.jpg',
						'alt' => 'Amabel Achuela',
					),
					array(
						'name' => 'Aradel Mae Tanaid',
						'title' => 'Science Research Specialist I',
						'img' => '/wp-content/uploads/2025/10/no-profile.jpg',
						'alt' => 'Aradel Mae Tanaid',
					),
				),
			),*/
		);
	}

	add_shortcode('cbc_org_chart', 'cbc_organizational_chart_shortcode');
}