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
            $spacer_height = '25px'; ?>
            <!-- Panel Top 1 - Hardcoded to avoid database malfunctioning-->
            <div class="widget widget_block hidden md:block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo do_shortcode('[core_programs auto_advance="true" auto_interval="3500" show_arrows="true"]'); ?>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php echo govph_section_header('News & Updates', ['id' => 'news_updates_header']); ?>

            <div class="widget widget_block">
                <div style="height: 10px" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

            <?php
            // Use dynamic device-aware excerpt length (phone/tablet/desktop) via 'auto'
            echo do_shortcode('[gwt_latest_posts posts="5"  excerpt_length="0" show_date="1" show_image="1" image_size="medium" show_author="0" post_layout="grid"]');
            ?>

            <div class="widget widget_block">
                <div style="height: <?php echo $spacer_height;?>" aria-hidden="true" class="wp-block-spacer"></div>
            </div>

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