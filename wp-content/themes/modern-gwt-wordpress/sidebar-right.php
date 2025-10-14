<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<aside id="sidebar-right"
       class="[&>aside:last-child]:hidden mt-5 sm:mt-0 <?php govph_displayoptions( 'govph_sidebar_position_right' ); ?>columns"
       role="complementary">
    <?php
    $swap = false;
    if ( is_front_page() ) :
        $swap = is_front_page();
        echo govph_section_header( 'Center Chief', [ 'id' => 'center_chief_header', 'swap' => $swap ] );
        ?>
        <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
            <div class="grid grid-cols-2 gap-3 items-center">
                <figure class="w-full h-full drop-shadow">
                    <img src="/wp-content/uploads/2025/09/RRSuralta-683x1024.png"
                         alt="Dr. Roel R. Suralta"
                         class="rounded-md w-full h-full object-cover object-center">
                </figure>
                <div class="w-full mt-4 md:mt-0">
                    <p class="text-sm">
                        <strong class="font-bold">Dr. Roel R. Suralta</strong> is a distinguished Filipino agricultural
                        scientist and NAST Academician, recognized for his pioneering research on root plasticity in
                        rice. As <strong class="font-bold">Center Chief</strong> of the <a href="http://192.168.36.77/"
                                                                                           class="text-blue-600 hover:underline">DA–Crop
                            Biotechnology Center</a> at <a href="https://www.philrice.gov.ph/" target="_blank"
                                                           rel="noreferrer noopener"
                                                           class="text-blue-600 hover:underline">PhilRice</a>, he leads
                        innovations in climate-resilient crops and has received prestigious honors, including the
                        Presidential Lingkod Bayan Award.
                    </p>
                </div>
            </div>
        </aside>
    <?php
    else:
    echo govph_section_header( 'Popular Posts', [ 'id' => 'popular_posts_header', 'swap' => $swap ] ); ?>
    <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
        <?php echo do_shortcode( '[pm_popular_posts cache_minutes="0" titles_only="1"]' ); ?>
    </aside>

    <aside class="widget callout border-none secondary widget_block">
        <div class="grid grid-cols-1 gap-1">
            <div>
                <h3 class="font-bold my-1">Apps</h3>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/projects/breedersmap-db">
                        <p class="block text-justify sm:text-md text-sm m-0">Plant Breeders' Map Database</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/projects/twg-db">
                        <p class="block text-justify sm:text-md text-sm m-0">Biotech TWG Database</p>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="font-bold my-1">Services</h3>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/">
                        <p class="block text-justify sm:text-md text-sm m-0">Use Request Form</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/">
                        <p class="block text-justify sm:text-md text-sm m-0">Events Booking Form</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="http://192.168.36.71:5000/">
                        <p class="block text-justify sm:text-md text-sm m-0">Synology BioNAS</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="http://192.168.36.10/">
                        <p class="block text-justify sm:text-md text-sm m-0">CrAPPs Center</p>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="font-bold my-1">Games</h3>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/games/biotech-quiz-bee-game/">
                        <p class="block text-justify sm:text-md text-sm m-0">Biotech Quiz Bee</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/games/scramble-game/">
                        <p class="block text-justify sm:text-md text-sm m-0">Scramble Game</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/games/crop-memory-game/">
                        <p class="block text-justify sm:text-md text-sm m-0">Crop Memory Game</p>
                    </a>
                </div>
            </div>
        </div>
    </aside>
    <?php endif; ?>

    <aside class="widget callout border-none secondary widget_block">
        <div class="flex flex-col items-center gap-2">
            <div class="grid grid-cols-2 gap-4 items-center">
                <a href="https://privacy.gov.ph/transparency-seal/" class="flex justify-center">
                    <img
                            decoding="async"
                            id="tp-seal"
                            src="/wp-content/themes/gwt-wordpress-26.0.0/images/transparency-seal-160x160.png"
                            alt="transparency seal logo"
                            title="Transparency Seal"
                            class="w-32 h-32 object-contain md:w-40 md:h-40"
                    >
                </a>
                <a href="https://www.foi.gov.ph/" class="flex justify-center">
                    <img
                            decoding="async"
                            id="foi-logo"
                            src="/wp-content/themes/gwt-wordpress-26.0.0/images/foi-logo-160x160.png"
                            alt="freedom of information logo"
                            title="Freedom of Information"
                            class="w-32 h-32 object-contain md:w-40 md:h-40"
                    >
                </a>
            </div>

        </div>
    </aside>

    <?php do_action( 'before_sidebar' ); ?>
    <?php if ( is_active_sidebar( 'right-sidebar' ) ) {
        dynamic_sidebar( 'right-sidebar' );
    } ?>
</aside>
