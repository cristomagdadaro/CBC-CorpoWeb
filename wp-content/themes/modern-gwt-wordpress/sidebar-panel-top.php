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
            $spacer_height = '5rem'; ?>
            <!-- Panel Top 1 - Hardcoded to avoid database malfunctioning-->
            <?php echo do_shortcode('[cbc_spacer class="h-10 sm:h-16 lg:h-24"]'); ?>
            <!-- Left Content (Text + Video) -->
            <div class="grid grid-cols-1 md:grid-cols-2 relative gap-3 md:gap-6 items-start ">
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
                <div class="flex flex-col justify-center bg-white/80 h-full rounded-md relative">
                    <div class="hidden md:block text-[#205F26] drop-shadow text-2xl sm:text-3xl font-extrabold text-center lg:text-left leading-tight !font-spartan mt-3">
                        DA-CROP BIOTECHNOLOGY CENTER
                    </div>
                    <p class="mt-3 text-justify md:text-left leading-relaxed">
                        DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program Office (DA-BPO).
                        Through DA-Administrative Order No. 26 Series of 2021, we are mandated to develop and apply modern
                        biotechnology to boost the nation's agricultural productivity, build the skills of our research partners,
                        and foster a collaborative culture of knowledge-sharing to ensure a more food-secure and resilient Philippines.
                    </p>
                    <div id="particles-js-helix" class="absolute top-0 left-0 w-full h-full"></div>
                </div>
            </div>

            <?php echo do_shortcode('[cbc_spacer class="h-10 sm:h-16 lg:h-24"]'); ?>

            <?php echo govph_section_header('Core Programs', ['id' => 'core_programs_header']); ?>
            <?php echo do_shortcode('[core_programs auto_advance="true" auto_interval="3500" show_arrows="true"]'); ?>

            <?php echo do_shortcode('[cbc_spacer class="h-10 sm:h-16 lg:h-24"]'); ?>

            <?php echo govph_section_header('Priority Commodities', ['id' => 'priority_commodity_header']); ?>
            <?php echo do_shortcode('[priority_commodities_carousel max_display="5" center_scale="1.30" auto_advance="true" auto_interval="2500" show_arrows="true" enable_blur="true"]'); ?>

            <?php dynamic_sidebar( 'panel-top-1' ); ?>
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
