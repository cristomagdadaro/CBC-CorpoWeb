<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>

<div class="relative overflow-hidden">
    <?php if (is_front_page()): ?>
        <!--<div id="particles-js-network" class="absolute top-0 left-0 w-full min-h-[600vh] h-screen "></div>-->
    <?php endif; ?>
<div id="panel-top" class="anchor relative overflow-hidden" role="complementary">
    <div class="row z-[99]">
        <?php if(is_front_page()): ?>
        <aside id="panel-top-1 z-0" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
            role="complementary">
            <?php do_action( 'before_sidebar' );
            $homepage_section_title_classes = 'section-title homepage-section-title text-center text-3xl lg:text-4xl font-extrabold text-[#1f5d2b]'; ?>
            <!-- Panel Top 1 - Hardcoded to avoid database malfunctioning-->
            <section class="px-4 pt-12 sm:px-6 md:pt-16 lg:px-8 lg:pt-16">
                <div class="mx-auto max-w-7xl">
                    <!-- Left Content (Text + Video) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 relative items-start gap-6 lg:gap-8 pb-12 md:pb-16 lg:pb-24">
                        <!-- Video Wrapper -->
                        <div class="relative w-full">
                            <div class="block md:hidden text-[#205F26] drop-shadow text-2xl sm:text-3xl font-extrabold text-center leading-tight !font-spartan my-3">
                                <span>DA-CROP BIOTECHNOLOGY CENTER</span>
                            </div>

                            <!-- Use responsive video container -->
                            <div class="relative w-full overflow-hidden rounded-lg">
                                <video
                                        class="w-full h-auto rounded-lg"
                                        controls
                                        playsinline
                                        preload="metadata"
                                >
                                    <source src="/wp-content/uploads/2025/09/DA-Crop-Biotechnology-Center-2021-1-1.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>

                        <!-- Text Section -->
                        <div class="relative flex h-full flex-col justify-center rounded-md bg-white/80 p-6 lg:p-8">
                            <div class="hidden md:block text-[#205F26] drop-shadow text-2xl sm:text-3xl font-extrabold text-center lg:text-left leading-tight !font-spartan">
                                DA-CROP BIOTECHNOLOGY CENTER
                            </div>
                            <p class="mt-3 text-justify md:text-left leading-relaxed">
                                DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program Office (DA-BPO).
                                Through DA-Administrative Order No. 26 Series of 2021, we are mandated to develop and apply modern
                                biotechnology to boost the nation's agricultural productivity, build the skills of our research partners,
                                and foster a collaborative culture of knowledge-sharing to ensure a more food-secure and resilient Philippines.
                            </p>
                            <div id="particles-js-helix" class="pointer-events-none absolute top-0 left-0 w-full h-full"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white px-4 py-12 sm:px-6 md:py-16 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <h2 class="<?php echo esc_attr($homepage_section_title_classes); ?>">Core Programs</h2>
                    <?php echo do_shortcode('[core_programs auto_advance="true" auto_interval="3500" show_arrows="true"]'); ?>
                </div>
            </section>

            <section class="px-4 py-12 sm:px-6 md:py-16 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <h2 class="<?php echo esc_attr($homepage_section_title_classes); ?>">Priority Commodities</h2>
                    <?php echo do_shortcode('[priority_commodities_carousel max_display="5" center_scale="1.30" auto_advance="true" auto_interval="2500" show_arrows="true" enable_blur="true"]'); ?>
                </div>
            </section>

            <?php echo do_shortcode('[our_impact map_svg_path="/wp-content/uploads/2026/04/phMap.png"]'); ?>

            <section class="px-4 py-12 sm:px-6 md:py-16 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-8 lg:gap-12 relative">

                        <div class="md:col-span-3">
                            <?php echo do_shortcode( '[gwt_calendar mode="grid" header="1" max="30"]' ); ?>
                        </div>

                        <div class="md:col-span-2">
                            <?php echo govph_section_header( 'News and Updates', [ 'id' => 'news_posts_header'] ); ?>

                            <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0 transition-opacity duration-500">
                                <?php echo do_shortcode('[gwt_latest_posts posts="5" excerpt_length="0" show_date="1" show_image="0" hover_image="1" show_author="0" post_layout="list"]'); ?>
                            </aside>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-4 py-12 sm:px-6 md:py-16 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <?php echo do_shortcode( '[cbc_web_apps]' ); ?>
                </div>
            </section>

            <div class="px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <?php dynamic_sidebar( 'panel-top-1' ); ?>
                </div>
            </div>
        </aside>
        <?php endif; ?>
        <?php if(is_active_sidebar('panel-top-1') || is_active_sidebar('panel-top-2') || is_active_sidebar('panel-top-3') || is_active_sidebar('panel-top-4')): ?>
            <?php if(is_active_sidebar('panel-top-2')): ?>
            <aside id="panel-top-2" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
                role="complementary">
                <?php do_action( 'before_sidebar' ); ?>
                <?php dynamic_sidebar( 'panel-top-2' ); ?>
            </aside>
            <?php endif; ?>
            <?php if(is_active_sidebar('panel-top-3')): ?>
            <aside id="panel-top-3" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
                role="complementary">
                <?php do_action( 'before_sidebar' ); ?>
                <?php dynamic_sidebar( 'panel-top-3' ); ?>
            </aside>
            <?php endif; ?>
            <?php if(is_active_sidebar('panel-top-4')): ?>
            <aside id="panel-top-4" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
                role="complementary">
                <?php do_action( 'before_sidebar' ); ?>
                <?php dynamic_sidebar( 'panel-top-4' ); ?>
            </aside>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
