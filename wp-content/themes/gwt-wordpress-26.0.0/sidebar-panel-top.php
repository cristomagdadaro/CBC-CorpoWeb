<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<?php if(is_active_sidebar('panel-top-1') || is_active_sidebar('panel-top-2') || is_active_sidebar('panel-top-3') || is_active_sidebar('panel-top-4')): ?>
<div id="panel-top" class="anchor relative overflow-hidden" role="complementary">
    <div id="particles-js-network" class="absolute top-0 left-0 w-full min-h-[600vh] h-screen "></div>
    <div class="row z-[99]">
        <?php if(is_active_sidebar('panel-top-1') && is_front_page()): ?>
        <aside id="panel-top-1 z-0" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
            role="complementary">
            <?php do_action( 'before_sidebar' );
            $spacer_height = '40px';
            ?>

            <!-- Panel Top 1 - Hardcoded to avoid database malfunctioning-->
            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <div class="widget widget_block">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-6 items-start">

                    <!-- Text Section -->
                    <div class="order-2 md:order-1 flex flex-col justify-center">
                        <h3 class="text-[#205F26] drop-shadow text-xl sm:text-2xl md:text-3xl font-extrabold text-center md:text-left leading-tight hidden sm:block">
                            DA-CROP BIOTECHNOLOGY CENTER
                        </h3>
                        <p class="leading-relaxed mt-2 text-justify md:text-left leading-tight sm:leading-relaxed">
                            DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program Office (DA-BPO).
                            Through DA-Administrative Order No. 26 Series of 2021, we are mandated to develop and apply modern
                            biotechnology to boost the nation's agricultural productivity, build the skills of our research partners,
                            and foster a collaborative culture of knowledge-sharing to ensure a more food-secure and resilient Philippines.
                        </p>
                    </div>

                    <!-- Video Wrapper -->
                    <div class="order-1 md:order-2 relative w-full aspect-video md:w-full md:h-full">
                        <h3 class="text-[#205F26] text-xl sm:text-2xl md:text-3xl font-bold text-center md:text-left my-2 leading-tight  sm:hidden block">
                            DA-CROP BIOTECHNOLOGY CENTER
                        </h3>
                        <iframe
                                title="Featured video"
                                class="w-full h-full border-none rounded-lg"
                                src="/wp-content/uploads/2025/09/DA-Crop-Biotechnology-Center-2021-1-1.mp4"
                                allowfullscreen
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                        </iframe>
                    </div>
                </div>
            </div>


            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('Core Programs', ['id' => 'core_programs_header']); ?>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <div class="sm:flex sm:flex-col md:grid md:grid-cols-2 lg:grid-cols-4 ap-3 lg:gap-6 pb-5">
                <div class="scale-[75%] md:scale-[85%] lg:scale-100 bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-48">
                        <img src="/wp-content/uploads/2025/09/Screenshot-2025-09-05-150724-768x445.png"
                             alt="Technology Development and Innovation"
                             class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 -mt-10 mx-4 bg-white rounded-md p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            TECHNOLOGY DEVELOPMENT AND INNOVATION
                        </h3>
                        <p class="text-gray-800 text-center leading-tight md:leading-relaxed">
                            We conduct and host cutting-edge crop biotechnology research and development (R&D) activities, including joint research projects, forming dedicated research teams, and implementing impactful research programs.
                        </p>
                    </div>
                </div>
                <div class="scale-[75%] md:scale-[85%] lg:scale-100 bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-48">
                        <img src="/wp-content/uploads/2025/09/IMG_20240522_085745-768x576.jpg"
                             alt="R4D Biotechnology Capacity-Building Service"
                             class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 -mt-10 mx-4 bg-white rounded-md p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            R4D Biotechnology Capacity-Building Service
                        </h3>
                        <p class="text-gray-800 text-center leading-tight md:leading-relaxed">
                            We provide essential R&D-related services and training to DA agencies and our network. Our goal is to equip stakeholders with the skills to effectively apply modern crop biotechnology tools.
                        </p>
                    </div>
                </div>
                <div class="scale-[75%] md:scale-[85%] lg:scale-100 bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-48">
                        <img src="/wp-content/uploads/2025/09/CBC04940-768x461.png"
                             alt="Partnership and Fund Generation"
                             class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 -mt-10 mx-4 bg-white rounded-md p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            Partnership and Fund Generation
                        </h3>
                        <p class="text-gray-800 text-center leading-tight md:leading-relaxed">
                            We actively build and strengthen our connections within the R&D network. We also secure funding for projects from both local and international institutions, as well as public and private donors.
                        </p>
                    </div>
                </div>
                <div class="scale-[75%] md:scale-[85%] lg:scale-100 bg-white reveal-on-scroll-100 opacity-0 rounded-md overflow-hidden shadow-lg flex flex-col h-auto w-full">
                    <div class="relative w-full h-48">
                        <img src="/wp-content/uploads/2025/09/CBC06408-768x432.png"
                             alt="Technology Commercialization and Management"
                             class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 -mt-10 mx-4 bg-white rounded-md p-4 relative">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-2 md:line-clamp-3 h-[3.3rem]">
                            Technology Commercialization and Management
                        </h3>
                        <p class="text-gray-800 text-center leading-tight md:leading-relaxed">
                            We promote the commercialization and transfer of developed technologies. We also foster a culture of knowledge-sharing to ensure our network and stakeholders have easy access to a wealth of information.
                        </p>
                    </div>
                </div>
            </div>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('Latest Stories', ['id' => 'latest_stories_header']); ?>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php
            // Use dynamic device-aware excerpt length (phone/tablet/desktop) via 'auto'
            echo do_shortcode('[gwt_latest_posts posts="7"  excerpt_length="auto" show_date="1" show_image="1" show_author="0"]');
            ?>


            <!-- insert the latest post template here -->
            <!--Panel Top 1 End -->

            <?php dynamic_sidebar( 'panel-top-1' ); ?>
        </aside>
        <?php endif; ?>
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
    </div>
</div>
<?php endif; ?>