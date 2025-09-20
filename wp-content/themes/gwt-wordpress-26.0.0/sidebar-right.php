<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<?php if ( is_active_sidebar( 'right-sidebar' ) ) : ?>
<aside id="sidebar-right" class="relative mt-5 sm:mt-0 <?php govph_displayoptions( 'govph_sidebar_position_right' ); ?>columns"
    role="complementary">
    <?php if ( is_front_page() ) : 
        echo govph_section_header('Center Chief', ['id' => 'center_chief_header', 'swap'=>true]);
    ?>
        
       <aside id="block-165" class="widget callout border-none secondary widget_block reveal-on-scroll-500">
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

    <?php do_action( 'before_sidebar' ); ?>
    <?php dynamic_sidebar( 'right-sidebar' ) ?>
</aside>
<?php endif; ?>
