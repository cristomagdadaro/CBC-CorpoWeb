<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<?php if(is_active_sidebar('panel-top-1') || is_active_sidebar('panel-top-2') || is_active_sidebar('panel-top-3') || is_active_sidebar('panel-top-4')): ?>
<div class="relative overflow-hidden">
    <?php if (is_front_page()): ?>
        <!--<div id="particles-js-network" class="absolute top-0 left-0 w-full min-h-[600vh] h-screen "></div>-->
    <?php endif; ?>
<div id="panel-top" class="anchor relative overflow-hidden" role="complementary">
    <div class="row z-[99]">
        <?php if(is_active_sidebar('panel-top-1') && is_front_page()): ?>
        <aside id="panel-top-1 z-0" class="<?php govph_displayoptions( 'govph_position_panel_top' ); ?>"
            role="complementary">
            <?php do_action( 'before_sidebar' );
            $spacer_height = '5rem'; ?>
            <!-- Panel Top 1 - Hardcoded to avoid database malfunctioning-->
            <div class="widget widget_block">
                <div style="height: 15px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>
            <!-- Left Content (Text + Video) -->
            <div class="grid grid-cols-1 md:grid-cols-2 relative gap-6">
                <!-- Video Wrapper -->
                <div class="relative w-full aspect-video h-full z-0 mt-4 md:mt-0">
                    <iframe
                            title="Featured video"
                            class="w-full h-full border-none rounded-lg"
                            src="/wp-content/uploads/2025/09/DA-Crop-Biotechnology-Center-2021-1-1.mp4"
                            allowfullscreen
                            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                    </iframe>
                </div>
                <!-- Text Section -->
                <div class="flex flex-col justify-center relative z-20 bg-white/80 p-2 md:p-0 rounded-md">
                    <h3 class="text-[#205F26] drop-shadow text-xl sm:text-2xl md:text-3xl font-extrabold text-center md:text-left leading-tight !font-spartan mt-3 sm:mt-0">
                        DA-CROP BIOTECHNOLOGY CENTER
                    </h3>
                    <p class="mt-2 text-justify md:text-left sm:leading-relaxed">
                        DA-CBC is one of the three biotechnology centers under the DA-Biotechnology Program Office (DA-BPO).
                        Through DA-Administrative Order No. 26 Series of 2021, we are mandated to develop and apply modern
                        biotechnology to boost the nation's agricultural productivity, build the skills of our research partners,
                        and foster a collaborative culture of knowledge-sharing to ensure a more food-secure and resilient Philippines.
                    </p>
                </div>
            </div>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('Core Programs', ['id' => 'core_programs_header']); ?>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo do_shortcode('[core_programs auto_advance="true" auto_interval="3500" show_arrows="true"]'); ?>


            <?php /*echo govph_section_header('News & Updates', ['id' => 'news_updates_header']); */?>
            <!--<h1 class="text-[#1f5d2b] font-extrabold p-1 md:p-2 bg-gradient-to-r text-center px-5 text-lg sm:text-xl md:text-2xl lg:text-3xl">
                News & Updates
            </h1>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            --><?php
/*            echo do_shortcode('[gwt_latest_posts posts="5"  excerpt_length="20" show_date="1" show_image="1" image_size="medium" show_author="0" post_layout="grid"]');
            */?>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('Priority Commodities', ['id' => 'priority_commodity_header']); ?>

            <?php echo do_shortcode('[priority_commodities_carousel max_display="5" center_scale="1.30" auto_advance="true" auto_interval="2500" show_arrows="true" enable_blur="true"]'); ?>

            <!--<div class="widget widget_block">
                <div style="height: <?php /*echo $spacer_height;*/?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>
-->
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