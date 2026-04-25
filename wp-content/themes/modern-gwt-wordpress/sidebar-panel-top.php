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
            <section class="px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl relative overflow-visible">
                    <div class="flex flex-col-reverse md:grid grid-cols-1 items-center gap-8 md:grid-cols-2 lg:gap-16">
                        <!-- LEFT: Video -->
                        <div class="relative w-full z-10">
                            <div class="relative w-full overflow-hidden rounded-xl aspect-video bg-[#EAF5EE] border border-[#D0EBD8]">
                                <iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2Freel%2F1530085758685692%2F&show_text=false&width=560&t=0" class="absolute inset-0 w-full h-full" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
                            </div>
                        </div>

                        <!-- RIGHT: Text -->
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-4 relative">
                                <span class="block w-full md:w-1/2 h-0.5 bg-[#1A7A42]"></span>
                                <span class="text-[#1A7A42] text-[10px] font-bold tracking-[2.5px] uppercase !font-spartan">
                                    About
                                </span>
                                <span class="block w-full md:w-1/2 h-0.5 bg-[#1A7A42]"></span>
                            </div>

                            <h2 id="particles-js-flyAroundText" data-particles-type="flyAroundText" class="section-title text-center md:text-left">
                                DA-Crop Biotechnology Center
                            </h2>

                            <p class="text-sm text-gray-600 leading-[1.85] text-justify md:text-left z-10 relative">
                                DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program
                                Office (DA-BPO). Through DA-Administrative Order No. 26 Series of 2021, we are mandated
                                to develop and apply modern biotechnology to boost the nation's agricultural productivity,
                                build the skills of our research partners, and foster a collaborative culture of
                                knowledge-sharing to ensure a more food-secure and resilient Philippines.
                            </p>

                            <img decoding="async" width="1024" height="201" src="https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-1024x201.webp" alt="DA-CBC Full Front View" class="wp-image-3298 w-full h-auto object-cover mt-4 mb-4 select-none pointer-events-none drop-shadow" srcset="https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-1024x201.webp 1024w, https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-300x59.webp 300w, https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-768x150.webp 768w, https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-1536x301.webp 1536w, https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-2048x401.webp 2048w, https://dacbc.philrice.gov.ph/wp-content/uploads/2025/10/front-view-500x98.webp 500w" sizes="(max-width: 1024px) 100vw, 1024px">

                            <div class="w-full h-0.5 bg-[#1A7A42] rounded-full"></div>
                        </div>
                    </div>

                </div>
            </section>

            <section class="px-4 py-12 sm:px-6 md:py-16 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
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
