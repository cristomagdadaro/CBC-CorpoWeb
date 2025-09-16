<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @package GWT
 * @since Government Website Template 2.0
 */

get_header(); 
include_once('inc/banner.php');
?>
<?php govph_displayoptions( 'govph_panel_top' ); ?>
<div class="container-main relative overflow-hidden" role="document">
    <div id="main-content" class="row">

        <div id="content" class="text-justify overflow-hidden gap-4 <?php govph_displayoptions( 'govph_content_position' ); ?>columns"
             style="display: flex; flex-direction: column; justify-content: space-between;"
            role="main">

            <h2 class="text-lg sm:text-xl text-white p-2 md:text-2xl bg-gradient-to-r from-[#1f5d2b] to-[#a2b917] lg:text-3xl text-left px-5"><strong>Videos</strong></h2>

            <?php
            $videos = [
                [
                    'src'   => 'https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2FDACropBiotechCenter%2Fvideos%2F1093880421705089%2F&show_text=false&width=560&t=0',
                    'title' => "Last Year's Accomplishments",
                    'description' => "Let's rewind and celebrate the breakthroughs of 2023 at the DA-Crop Biotechnology Center! Check out our recap video and stay tuned for even more exciting developments in the coming months!"
                ],
                [
                        'src'   => 'https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2FDACropBiotechCenter%2Fvideos%2F2629670060546947%2F&show_text=false&width=560&t=0',
                        'title' => 'Official Launch of the New DA-CBC Logo',
                        'description' => "This Labor Day, the DA-Crop Biotechnology Center is proud to reveal its new logo representing the tireless effort and commitment of our agricultural workers- whether in the fields or in the labs. The logo combines elements of DNA and crops, underscoring the valuable contributions of our farmers, laborers, and researchers who nurture the soil and guarantee bountiful crops. On this day, let us acknowledge their hard work and anticipate a tomorrow that values progress, with biotechnology at the forefront of enhancing agricultural efficiency and longevity. Join us in honoring the ethos of hard work and devotion!"
                ],
                [
                        'src'   => 'https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2FDACropBiotechCenter%2Fvideos%2F1092606405765452%2F&show_text=false&width=560&t=0',
                        'title' => 'DA-CBC: Cultivating not just crops, but also researchers!',
                        'description' => "Thirteen trainees from Benguet State University, Regional Crop Protection Center Region VII, Caraga State University, and Bureau of Soils and Water Management, recently completed the 4-day Training on Basic Molecular Biology Techniques for Bacterial Identification held at DA-CBC last October 28-31. The training, combining lectures and hands-on activities, provided participants with a comprehensive understanding of general laboratory procedures, microbial biotechnology, basic molecular biology techniques, and advanced bioinformatics analyses such as 16s rRNA Sequence Data Analysis, Diversity Analysis, and Metagenomics."
                ],
              /*[
                    'src' => "",
                    'title' => "",
                    'description' => ""
                ],*/
                // add more here...
            ];

            function limit_words( $string, $limit = 10 ) {
                $words = explode( ' ', $string );
                if ( count( $words ) > $limit ) {
                    return implode( ' ', array_slice( $words, 0, $limit ) ) . '...';
                } else {
                    return $string;
                }
            }
            ?>

            <div class="grid grid-cols-3 gap-2 lg:gap-5">
                <?php foreach ( $videos as $video ) : ?>
                    <div class="relative flex flex-col gap-2 w-auto h-fit reveal-on-scroll">
                        <div class="relative w-full shadow" style="padding-bottom: 56.25%;">
                            <iframe class="absolute top-0 left-0 w-full h-full border-none rounded"
                                    src="<?php echo esc_url( $video['src'] ); ?>"
                                    style="border:none;overflow:hidden; margin: 0 auto;"
                                    scrolling="no"
                                    frameborder="0"
                                    allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm whitespace-nowrap overflow-hidden overflow-ellipsis text-[#006837]"><?php echo esc_html( $video['title'] ); ?></h3>
                            <p class="text-sm leading-[1.1rem]"><?php echo esc_html( limit_words( $video['description'], 20 ) ); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
        <!-- start content -->


        <!-- end content -->
        <?php
				if(is_active_sidebar('left-sidebar')){
					govph_displayoptions( 'govph_sidebar_left' );
				}
				?>
        <?php
				if(is_active_sidebar('right-sidebar')){
					govph_displayoptions( 'govph_sidebar_right' );
				}
				?>

    </div>
</div>


<?php add_shortcode('custom_post_loop', 'custom_post_layout'); govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>