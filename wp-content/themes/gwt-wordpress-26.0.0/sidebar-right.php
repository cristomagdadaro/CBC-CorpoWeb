<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<aside id="sidebar-right" class="relative mt-5 sm:mt-0 <?php govph_displayoptions( 'govph_sidebar_position_right' ); ?>columns"
    role="complementary">
    <?php
    $swap = false;
    if ( is_front_page() ) :
        $swap = is_front_page();
        echo govph_section_header('Center Chief', ['id' => 'center_chief_header', 'swap'=>$swap]);
    ?>
       <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500">
            <div class="flex-col items-center gap-3 flex">
                <a href="/about-us/organizational-structure/dr-roel-r-suralta/" class="w-full h-[25rem] rounded-md overflow-hidden relative drop-shadow-lg">
                    <img decoding="async" id="tp-seal" src="/wp-content/uploads/2025/09/RRSuralta-500x749.jpg" alt="RRSuralta" title="RRSuralta" class="rounded-md">
                    <span class="absolute bottom-0 left-0 text-center w-full text-white text-lg py-2 select-none">2010-present</span>
                </a>
                <div class="flex flex-col">
                    <p class="sm:text-md text-sm text-justify"><strong>Dr. Roel R. Suralta</strong> is a distinguished Filipino agricultural scientist and NAST Academician, recognized for his pioneering research on root plasticity in rice. As <strong>Center Chief</strong> of the <a href="/">DA–Crop Biotechnology Center</a> at <a href="https://www.philrice.gov.ph/" target="_blank">PhilRice</a>, he leads innovations in climate-resilient crops and has received prestigious honors, including the Presidential Lingkod Bayan Award.</p>
                </div>
            </div>
        </aside>
    <?php endif; ?>
    <?php echo govph_section_header('Vision', ['id' => 'vision_header', 'swap'=>$swap]); ?>
        <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500">
            <div class="panel h-fit bg-transparent" style="max-height: 100vh;">
                <figure class="text-center"><blockquote><p>A prosperous, secure, and sustainable food future - one crop at a time.</p></blockquote></figure>
            </div>
        </aside>

    <?php echo govph_section_header('Mission', ['id' => 'mission_header', 'swap'=>$swap]); ?>
        <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500">
            <div class="panel h-fit bg-transparent" style="max-height: 100vh;">
                <figure class="text-center"><blockquote><p>To improve the productivity and competitiveness of priority commodities through providing biotechnology capacity building services, fostering collaboration within the agricultural research community, and driving the development and utilization of modern and inclusive biotechnology for crop improvement, contributing to a resilient and prosperous agricultural landscape.</p></blockquote></figure>
            </div>
        </aside>

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

        <aside class="widget callout border-none secondary widget_block">
            <div class="flex flex-col gap-1">
                <h3 class="font-bold my-1">Apps</h3>
                <div class="ml-2 flex items-center gap-2">
                    <a href="https://pin.philrice.gov.ph/projects/breedersmap-db">
                        <p class="block text-justify sm:text-md text-sm">Plant Breeders' Map Database</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="https://pin.philrice.gov.ph/projects/twg-db">
                        <p class="block text-justify sm:text-md text-sm">Biotech TWG Database</p>
                    </a>
                </div>
                <h3 class="font-bold my-1">Services</h3>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/">
                        <p class="block text-justify sm:text-md text-sm">Use Request Form</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="/">
                        <p class="block text-justify sm:text-md text-sm">Events Booking Form</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="http://192.168.36.71:5000/">
                        <p class="block text-justify sm:text-md text-sm">Synology BioNAS</p>
                    </a>
                </div>
                <div class="ml-2 flex items-center gap-2">
                    <a href="http://192.168.36.10/">
                        <p class="block text-justify sm:text-md text-sm">CrAPPs Center</p>
                    </a>
                </div>
            </div>
        </aside>


    <?php do_action( 'before_sidebar' ); ?>
    <?php if ( is_active_sidebar( 'right-sidebar' ) ) { dynamic_sidebar( 'right-sidebar' ); } ?>
</aside>
