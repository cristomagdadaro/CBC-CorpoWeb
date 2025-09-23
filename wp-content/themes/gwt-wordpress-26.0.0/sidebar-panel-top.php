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
                <div class="flex flex-col md:flex-row gap-2 md:gap-5">
                    <!-- Video Wrapper -->
                    <div class="relative w-full md:w-1/2 aspect-video reveal-on-scroll-300 transition duration-700 ease-out will-change-transform opacity-100 translate-y-0">
                        <iframe title="Featured video" class="absolute top-0 left-0 w-full h-full border-none rounded-lg" src="https://www.facebook.com/plugins/video.php?height=314&amp;href=https%3A%2F%2Fwww.facebook.com%2FDACropBiotechCenter%2Fvideos%2F606440631062718%2F&amp;show_text=false&amp;width=560&amp;t=0" style="border:none;overflow:hidden;" allowfullscreen allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>

                    <!-- Text Section -->
                    <div class="relative flex flex-col md:w-1/2 justify-center backdrop-blur overflow-hidden reveal-on-scroll-500 transition duration-700 ease-out will-change-transform opacity-100 translate-y-0">
                        <h3 class="text-[#205F26] text-xl sm:text-2xl md:text-3xl backdrop-blur"><strong>DA-CROP BIOTECHNOLOGY CENTER</strong></h3>
                        <p class="leading-relaxed backdrop-blur">
                            DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program Office (DA-BPO).
                            Through DA-Administrative Order No. 26 Series of 2021, we are mandated to develop and apply modern
                            biotechnologies to boost the nation's agricultural productivity, build the skills of our research partners,
                            and foster a collaborative culture of knowledge-sharing to ensure a more food-secure and resilient Philippines.
                        </p>
                        <div id="particles-js-helix" class="absolute top-0 left-0 w-full min-h-[600vh] h-screen opacity-25"><canvas class="particles-js-canvas-el" style="width: 100%; height: 100%;" width="922" height="11280"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('Core Programs', ['id' => 'core_programs_header']); ?>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 justify-evenly pb-5">
                <div class="bg-white rounded-md overflow-hidden max-w-[300px] mx-auto reveal-on-scroll-300">
                    <div class="relative"> <img src="/wp-content/uploads/2025/09/Screenshot-2025-09-05-150724-768x445.png" alt="Technology Development and Innovation" class="w-full h-64 object-cover" /> </div>
                    <div class="-mt-10 mb-3 mx-4 bg-white shadow-lg rounded-md p-3 relative min-h-[240px]">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-3 h-[3.3rem]">TECHNOLOGY DEVELOPMENT AND INNOVATION</h3>
                        <p class="text-gray-800 text-center leading-1 mb-6 leading-[1rem] my-auto">We conduct and host cutting-edge crop biotechnology research and development (R&D) activities, including joint research projects, forming dedicated research teams, and implementing impactful research programs.</p>
                    </div>
                </div>
                <div class="bg-white rounded-md overflow-hidden max-w-[300px] mx-auto reveal-on-scroll-500">
                    <div class="relative"> <img src="/wp-content/uploads/2025/09/IMG_20240522_085745-768x576.jpg" alt="Technology Development and Innovation" class="w-full h-64 object-cover" /> </div>
                    <div class="-mt-10 mb-3 mx-4 bg-white shadow-lg rounded-md p-3 relative min-h-[240px]">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-3 h-[3.3rem]">R4D Biotechnology Capacity-Building Service</h3>
                        <p class="text-gray-800 text-center leading-1 mb-6 leading-[1rem] my-auto">We provide essential R&D-related services and training to DA agencies and our network. Our goal is to equip stakeholders with the skills to effectively apply modern crop biotechnology tools.</p>
                    </div>
                </div>
                <div class="bg-white rounded-md overflow-hidden max-w-[300px] mx-auto reveal-on-scroll-700">
                    <div class="relative"> <img src="/wp-content/uploads/2025/09/CBC04940-768x461.png" alt="Technology Development and Innovation" class="w-full h-64 object-cover" /> </div>
                    <div class="-mt-10 mb-3 mx-4 bg-white shadow-lg rounded-md p-3 relative min-h-[240px]">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-3 h-[3.3rem]">Partnership and Fund Generation</h3>
                        <p class="text-gray-800 text-center leading-1 mb-6 leading-[1rem] my-auto">We actively build and strengthen our connections within the R&D network. We also secure funding for projects from both local and international institutions, as well as public and private donors.</p>
                    </div>
                </div>
                <div class="bg-white rounded-md overflow-hidden max-w-[300px] mx-auto reveal-on-scroll-900">
                    <div class="relative"> <img src="/wp-content/uploads/2025/09/CBC06408-768x432.png" alt="Technology Development and Innovation" class="w-full h-64 object-cover" /> </div>
                    <div class="-mt-10 mb-3 mx-4 bg-white shadow-lg rounded-md p-3 relative min-h-[240px]">
                        <h3 class="text-[#1f5d2b] font-extrabold text-lg text-center mb-4 leading-[1.1rem] overflow-hidden text-ellipsis line-clamp-3 h-[3.3rem]">Technology Commercialization and Management</h3>
                        <p class="text-gray-800 text-center leading-1 mb-6 leading-[1rem] my-auto">We promote the commercialization and transfer of developed technologies. We also foster a culture of knowledge-sharing to ensure our network and stakeholders have easy access to a wealth of information.</p>
                    </div>
                </div>
            </div>

            <?php echo govph_section_header('Latest Stories', ['id' => 'latest_stories_header']); ?>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo do_shortcode('[gwt_latest_posts posts="6" excerptLength="25" show_date="1" show_image="1" show_author="0"] '); ?>

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