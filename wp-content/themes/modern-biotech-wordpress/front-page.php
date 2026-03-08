<?php
/**
 * Front page template.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$quick_links = array(
    array('title' => 'Lab Services', 'description' => 'Access seven modern labs for advanced biotech analysis.', 'href' => '#services'),
    array('title' => 'Research Projects', 'description' => 'Explore active and completed biotechnology studies.', 'href' => '#research'),
    array('title' => 'PIN Database', 'description' => 'Browse plant breeder and innovator records.', 'href' => '#research'),
    array('title' => 'Expert Directory', 'description' => 'Connect with researchers and technical specialists.', 'href' => '#experts'),
    array('title' => 'Events & Training', 'description' => 'See workshops, symposia, and upcoming seminars.', 'href' => '#calendar'),
    array('title' => 'Knowledge Products', 'description' => 'Access technology briefs and publications.', 'href' => '#news'),
);

$core_programs = array(
    array('title' => 'Technology Development and Innovation', 'description' => 'Developing high-impact biotechnology interventions for strategic crops.', 'stat' => '50+ Active Projects'),
    array('title' => 'R4D Capacity-Building', 'description' => 'Training researchers, extension workers, and partners nationwide.', 'stat' => '1000+ Trained'),
    array('title' => 'Partnership and Fund Generation', 'description' => 'Building local and international collaborations for R&D scale.', 'stat' => '25+ Partners'),
    array('title' => 'Technology Commercialization', 'description' => 'Accelerating transfer of validated biotech solutions to users.', 'stat' => '15 Technologies'),
);

$research_projects = array(
    array('title' => 'CRISPR-Based Rice Resistance to Bacterial Blight', 'crop' => 'Rice', 'tool' => 'CRISPR', 'status' => 'Ongoing', 'year' => '2023-2026', 'team' => 8, 'image' => 'rice.jpg'),
    array('title' => 'Bt Corn Efficacy Against Asian Corn Borer', 'crop' => 'Corn', 'tool' => 'Genetic Engineering', 'status' => 'Completed', 'year' => '2021-2024', 'team' => 12, 'image' => 'corn.jpg'),
    array('title' => 'Banana Fusarium Wilt Resistance Markers', 'crop' => 'Banana', 'tool' => 'Marker-Assisted Selection', 'status' => 'Ongoing', 'year' => '2022-2025', 'team' => 6, 'image' => 'banana.jpg'),
    array('title' => 'Coconut Embryo Culture Protocol Optimization', 'crop' => 'Coconut', 'tool' => 'Tissue Culture', 'status' => 'Ongoing', 'year' => '2023-2025', 'team' => 5, 'image' => 'coconut.jpg'),
    array('title' => 'Abaca Fiber Quality QTL Mapping', 'crop' => 'Abaca', 'tool' => 'Genomics', 'status' => 'Completed', 'year' => '2020-2023', 'team' => 7, 'image' => 'abaca.jpg'),
    array('title' => 'Rice Drought Tolerance Gene Discovery', 'crop' => 'Rice', 'tool' => 'Bioinformatics', 'status' => 'Ongoing', 'year' => '2024-2027', 'team' => 10, 'image' => 'lab-research.jpg'),
);

$laboratories = array(
    array('name' => 'Molecular Genetics', 'summary' => 'Genotyping, markers, PCR, and genomic characterization.', 'turnaround' => '3-5 business days'),
    array('name' => 'Genetic Transformation', 'summary' => 'Trait introduction and validation pipelines for priority crops.', 'turnaround' => '4-6 months'),
    array('name' => 'Tissue Culture', 'summary' => 'Micropropagation, embryo rescue, and culture protocol support.', 'turnaround' => '6-12 weeks'),
    array('name' => 'Systems Biology', 'summary' => 'Integrated transcriptomics, proteomics, and metabolomics.', 'turnaround' => '2-4 weeks'),
    array('name' => 'Microbial Biotechnology', 'summary' => 'PGPM screening and biofertilizer development services.', 'turnaround' => '2-3 weeks'),
    array('name' => 'Bioinformatics', 'summary' => 'Genome analysis, pipelines, and data systems engineering.', 'turnaround' => '1-2 weeks'),
    array('name' => 'Molecular Diagnostics', 'summary' => 'Pathogen, GMO, and variety detection diagnostics.', 'turnaround' => '2-5 business days'),
);

$scientists = array(
    array('name' => 'Dr. Roel R. Suralta', 'role' => 'Center Chief', 'specialization' => 'Crop Biotechnology', 'lab' => 'Administration', 'publications' => 45, 'projects' => 12),
    array('name' => 'Dr. Maria Santos', 'role' => 'Senior Researcher', 'specialization' => 'Molecular Genetics', 'lab' => 'Molecular Genetics', 'publications' => 32, 'projects' => 8),
    array('name' => 'Dr. Juan Reyes', 'role' => 'Research Specialist', 'specialization' => 'Genetic Engineering', 'lab' => 'Genetic Transformation', 'publications' => 28, 'projects' => 10),
    array('name' => 'Dr. Ana Cruz', 'role' => 'Research Scientist', 'specialization' => 'Molecular Breeding', 'lab' => 'Molecular Genetics', 'publications' => 25, 'projects' => 6),
    array('name' => 'Dr. Pedro Lim', 'role' => 'Senior Research Fellow', 'specialization' => 'Tissue Culture', 'lab' => 'Tissue Culture', 'publications' => 38, 'projects' => 9),
    array('name' => 'Dr. Elena Garcia', 'role' => 'Bioinformatics Specialist', 'specialization' => 'Computational Biology', 'lab' => 'Bioinformatics', 'publications' => 22, 'projects' => 15),
);

$events = array(
    array('title' => 'Biotechnology Training Workshop', 'date' => '2026-03-15', 'type' => 'Training', 'time' => '09:00 AM - 04:00 PM', 'location' => 'DA-CBC Training Room'),
    array('title' => 'PIN System User Training', 'date' => '2026-03-20', 'type' => 'Webinar', 'time' => '01:00 PM - 05:00 PM', 'location' => 'Virtual Meeting'),
    array('title' => 'Research Symposium 2026', 'date' => '2026-03-25', 'type' => 'Conference', 'time' => '08:00 AM - 05:00 PM', 'location' => 'Plenary Hall'),
);

$featured_image = get_template_directory_uri() . '/assets/img/hero-bg.jpg';
?>
<main id="mbw-main">
    <section class="mbw-hero" id="hero" style="--hero-image: url('<?php echo esc_url($featured_image); ?>');">
        <div class="mbw-shell">
            <div class="mbw-hero-content reveal">
                <p class="mbw-badge">DA - Crop Biotechnology Center</p>
                <h1>Biotech for <span>Better Crops</span><br>for Better Lives</h1>
                <p class="mbw-hero-lead">
                    We develop and apply modern biotechnology to increase agricultural productivity,
                    strengthen crop resilience, and support a food-secure Philippines.
                </p>
                <div class="mbw-hero-actions">
                    <a class="mbw-btn mbw-btn-accent" href="#research">Explore Our Research</a>
                    <a class="mbw-btn mbw-btn-ghost" href="#services">Laboratory Services</a>
                </div>
                <div class="mbw-stats">
                    <article><strong>7</strong><small>Laboratories</small></article>
                    <article><strong>50+</strong><small>Research Projects</small></article>
                    <article><strong>5</strong><small>Priority Crops</small></article>
                </div>
            </div>
        </div>
    </section>

    <section class="mbw-section mbw-quick" id="quick-access">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Service Hub</p>
                <h2>Quick Access</h2>
            </header>
            <div class="mbw-card-grid mbw-card-grid-3">
                <?php foreach ($quick_links as $link) : ?>
                    <a class="mbw-card reveal" href="<?php echo esc_url($link['href']); ?>">
                        <h3><?php echo esc_html($link['title']); ?></h3>
                        <p><?php echo esc_html($link['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section mbw-alt" id="programs">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Our Mission</p>
                <h2>Core Programs</h2>
            </header>
            <div class="mbw-card-grid mbw-card-grid-2">
                <?php foreach ($core_programs as $program) : ?>
                    <article class="mbw-card reveal">
                        <h3><?php echo esc_html($program['title']); ?></h3>
                        <p><?php echo esc_html($program['description']); ?></p>
                        <span class="mbw-chip"><?php echo esc_html($program['stat']); ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section" id="research">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Research Portfolio</p>
                <h2>Research Catalog</h2>
            </header>

            <div class="mbw-filters reveal">
                <input type="search" id="mbw-research-search" placeholder="Search projects by title, crop, or method">
                <select id="mbw-crop-filter">
                    <option value="">All Crops</option>
                    <option value="Rice">Rice</option>
                    <option value="Corn">Corn</option>
                    <option value="Banana">Banana</option>
                    <option value="Coconut">Coconut</option>
                    <option value="Abaca">Abaca</option>
                </select>
                <select id="mbw-tool-filter">
                    <option value="">All Tools</option>
                    <option value="CRISPR">CRISPR</option>
                    <option value="Genetic Engineering">Genetic Engineering</option>
                    <option value="Marker-Assisted Selection">Marker-Assisted Selection</option>
                    <option value="Tissue Culture">Tissue Culture</option>
                    <option value="Genomics">Genomics</option>
                    <option value="Bioinformatics">Bioinformatics</option>
                </select>
                <select id="mbw-status-filter">
                    <option value="">All Status</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div class="mbw-card-grid mbw-card-grid-3" id="mbw-research-grid">
                <?php foreach ($research_projects as $project) : ?>
                    <article
                        class="mbw-card mbw-project reveal"
                        data-title="<?php echo esc_attr(strtolower($project['title'])); ?>"
                        data-crop="<?php echo esc_attr($project['crop']); ?>"
                        data-tool="<?php echo esc_attr($project['tool']); ?>"
                        data-status="<?php echo esc_attr($project['status']); ?>"
                    >
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/' . $project['image']); ?>" alt="<?php echo esc_attr($project['title']); ?>">
                        <div class="mbw-project-body">
                            <h3><?php echo esc_html($project['title']); ?></h3>
                            <p>
                                <span class="mbw-chip"><?php echo esc_html($project['crop']); ?></span>
                                <span class="mbw-chip"><?php echo esc_html($project['tool']); ?></span>
                                <span class="mbw-chip"><?php echo esc_html($project['status']); ?></span>
                            </p>
                            <small><?php echo esc_html($project['year']); ?> | <?php echo esc_html((string) $project['team']); ?> researchers</small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section mbw-alt" id="services">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Facilities</p>
                <h2>Laboratory Services</h2>
            </header>
            <div class="mbw-card-grid mbw-card-grid-2">
                <?php foreach ($laboratories as $lab) : ?>
                    <article class="mbw-card reveal">
                        <h3><?php echo esc_html($lab['name']); ?></h3>
                        <p><?php echo esc_html($lab['summary']); ?></p>
                        <small>Turnaround: <?php echo esc_html($lab['turnaround']); ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section" id="experts">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Our Team</p>
                <h2>Scientist Directory</h2>
            </header>

            <div class="mbw-filters reveal">
                <input type="search" id="mbw-scientist-search" placeholder="Search by name or specialization">
                <select id="mbw-lab-filter">
                    <option value="">All Laboratories</option>
                    <option value="Administration">Administration</option>
                    <option value="Molecular Genetics">Molecular Genetics</option>
                    <option value="Genetic Transformation">Genetic Transformation</option>
                    <option value="Tissue Culture">Tissue Culture</option>
                    <option value="Bioinformatics">Bioinformatics</option>
                </select>
            </div>

            <div class="mbw-card-grid mbw-card-grid-3" id="mbw-scientist-grid">
                <?php foreach ($scientists as $scientist) : ?>
                    <article
                        class="mbw-card reveal"
                        data-name="<?php echo esc_attr(strtolower($scientist['name'])); ?>"
                        data-specialization="<?php echo esc_attr(strtolower($scientist['specialization'])); ?>"
                        data-lab="<?php echo esc_attr($scientist['lab']); ?>"
                    >
                        <h3><?php echo esc_html($scientist['name']); ?></h3>
                        <p><?php echo esc_html($scientist['role']); ?> | <?php echo esc_html($scientist['specialization']); ?></p>
                        <small><?php echo esc_html($scientist['lab']); ?> | <?php echo esc_html((string) $scientist['publications']); ?> publications | <?php echo esc_html((string) $scientist['projects']); ?> projects</small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section mbw-alt" id="news">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Latest Updates</p>
                <h2>News and Updates</h2>
            </header>
            <div class="mbw-card-grid mbw-card-grid-2">
                <?php
                $news_query = new WP_Query(
                    array(
                        'post_type'      => 'post',
                        'post_status'    => 'publish',
                        'posts_per_page' => 4,
                    )
                );
                ?>
                <?php if ($news_query->have_posts()) : ?>
                    <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                        <article class="mbw-card reveal">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p>
                            <small><?php echo esc_html(get_the_date()); ?> | <?php the_author(); ?></small>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <article class="mbw-card reveal">
                        <h3>Celebrating 4 years of progress at the DA-Crop Biotechnology Center</h3>
                        <p>Milestones in biotechnology facilities, partnerships, and farmer-focused innovation initiatives.</p>
                        <small>September 30, 2025</small>
                    </article>
                    <article class="mbw-card reveal">
                        <h3>PIN Database rolled out in Ilocos Region</h3>
                        <p>Expanded regional access to plant breeder and innovator network resources.</p>
                        <small>July 22, 2025</small>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="mbw-section" id="calendar">
        <div class="mbw-shell">
            <header class="mbw-section-head reveal">
                <p class="mbw-kicker">Schedule</p>
                <h2>Calendar of Events</h2>
            </header>
            <div class="mbw-card-grid mbw-card-grid-3">
                <?php foreach ($events as $event) : ?>
                    <article class="mbw-card reveal">
                        <h3><?php echo esc_html($event['title']); ?></h3>
                        <p><?php echo esc_html($event['location']); ?></p>
                        <small><?php echo esc_html($event['date']); ?> | <?php echo esc_html($event['time']); ?> | <?php echo esc_html($event['type']); ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mbw-subscribe" id="subscribe">
        <div class="mbw-shell">
            <div class="mbw-subscribe-box reveal">
                <p class="mbw-kicker">Subscribe Now</p>
                <h2>Receive biotech updates and event announcements.</h2>
                <form id="mbw-subscribe-form" action="#" method="post">
                    <label class="screen-reader-text" for="mbw-email">Email Address</label>
                    <input id="mbw-email" type="email" placeholder="Enter your email address" required>
                    <button type="submit" class="mbw-btn mbw-btn-accent">Subscribe</button>
                </form>
                <p class="mbw-subscribe-message" id="mbw-subscribe-message" aria-live="polite"></p>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();
