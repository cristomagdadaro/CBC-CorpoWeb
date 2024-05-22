<?php 
if (!function_exists('newsmark_banner_tabbed_posts')):
    /**
     *
     * @since Newsmark 1.0.0
     *
     */
    function newsmark_banner_tabbed_posts()
    {
        if (is_front_page() || is_home()) {
        $number_of_posts = '2';
        $newsup_slider_category = newsup_get_option('select_recent_news_category');
        $newsup_all_posts_main = newsup_get_posts($number_of_posts, $newsup_slider_category);
        if ($newsup_all_posts_main->have_posts()) :
        while ($newsup_all_posts_main->have_posts()) : $newsup_all_posts_main->the_post();
        global $post;
        $newsup_url = newsup_get_freatured_image_url($post->ID, 'newsup-slider-full');
        ?>             
            <div class="mg-blog-post lg mins back-img mr-bot30" style="background-image: url('<?php echo esc_url($newsup_url); ?>');">
                <article class="bottom">
                    <div class="mg-blog-category"> <?php newsup_post_categories(); ?> </div>
                    <h4 class="title"> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <?php newsup_post_meta(); ?>
                </article>
            </div>      
                        

<?php
    endwhile;
endif;
wp_reset_postdata();
?>
        <?php }
    }
endif;

add_action('newsmark_action_banner_tabbed_posts', 'newsmark_banner_tabbed_posts', 10);

if (!function_exists('newsmark_banner_trending_posts')):
    /**
     *
     * @since Newsmark 1.0.0
     *
     */
    function newsmark_banner_trending_posts()
    {
        if (is_front_page() || is_home()) {
        $number_of_posts = '2';
        $newsup_slider_category = newsup_get_option('select_trending_news_category');
        $newsup_all_posts_main = newsup_get_posts($number_of_posts, $newsup_slider_category);
        if ($newsup_all_posts_main->have_posts()) :
        while ($newsup_all_posts_main->have_posts()) : $newsup_all_posts_main->the_post();
        global $post;
        $newsup_url = newsup_get_freatured_image_url($post->ID, 'newsup-slider-full');
        ?>             
            <div class="mg-blog-post lg mins back-img mr-bot30" style="background-image: url('<?php echo esc_url($newsup_url); ?>');">
                <article class="bottom">
                    <div class="mg-blog-category"> <?php newsup_post_categories(); ?> </div>
                    <h4 class="title"> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <?php newsup_post_meta(); ?>
                </article>
            </div>      
                        

<?php
    endwhile;
endif;
wp_reset_postdata();
?>
        <?php }
    }
endif;

add_action('newsmark_action_banner_trending_posts', 'newsmark_banner_trending_posts', 10);

//Front Page Banner
if (!function_exists('newsmark_front_page_banner_section')) :
    /**
     *
     * @since Newsmark
     *
     */
    function newsmark_front_page_banner_section()
    {
        if (is_front_page() || is_home()) {
        $newsup_enable_main_slider = newsup_get_option('show_main_news_section');
        $select_vertical_slider_news_category = newsup_get_option('select_vertical_slider_news_category');
        $vertical_slider_number_of_slides = newsup_get_option('vertical_slider_number_of_slides');
        $all_posts_vertical = newsup_get_posts($vertical_slider_number_of_slides, $select_vertical_slider_news_category);
        if ($newsup_enable_main_slider):  

            $main_banner_section_background_image = newsup_get_option('main_banner_section_background_image');
            $main_banner_section_background_image_url = wp_get_attachment_image_src($main_banner_section_background_image, 'full');
        if(!empty($main_banner_section_background_image)){ ?>
             <section class="mg-fea-area over" style="background-image:url('<?php echo esc_url($main_banner_section_background_image_url[0]); ?>');">
        <?php }else{ ?>
            <section class="mg-fea-area">
        <?php  } ?>
            <div class="overlay">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="trending-posts">
                                <?php do_action('newsmark_action_banner_trending_posts');?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="homemain" class="homemain owl-carousel">
                                <?php newsup_get_block('list', 'banner'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="recent-posts">
                                <?php do_action('newsmark_action_banner_tabbed_posts');?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--==/ Home Slider ==-->
        <?php endif; ?>
        <!-- end slider-section -->
        <?php }
    }
endif;
add_action('newsmark_action_front_page_main_section_1', 'newsmark_front_page_banner_section', 40);


if (!function_exists('newsmark_news_ticker_posts')):
    /**
     *
     * @since newsup 1.0.0
     *
     */
    function newsmark_news_ticker_posts()  { 
                $show_flash_news_section = newsup_get_option('show_flash_news_section');
            if ($show_flash_news_section){ ?>
            <div class="mg-latest-news-sec">
                <?php
                $category = newsup_get_option('select_flash_news_category');
                $number_of_posts = newsup_get_option('number_of_flash_news');
                $newsup_ticker_news_title = get_theme_mod('flash_news_title','Breaking');

                $all_posts = newsup_get_posts($number_of_posts, $category);
                $show_trending = true;
                $count = 1;
                ?>
                    <div class="mg-latest-news">
                         <div class="bn_title">
                            <h2 class="title">
                                <?php if (!empty($newsup_ticker_news_title)): ?>
                                    <?php echo esc_html($newsup_ticker_news_title); ?><span></span>
                                <?php endif; ?>
                            </h2>
                        </div>
                        <?php if(is_rtl()){ ?>
                        <div class="mg-latest-news-slider marquee" data-direction='right' dir="ltr">
                        <?php } else { ?>
                        <div class="mg-latest-news-slider marquee">
                        <?php } ?>
                            <?php
                            if ($all_posts->have_posts()) :
                                while ($all_posts->have_posts()) : $all_posts->the_post();
                                    ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <span><?php the_title(); ?></span>
                                     </a>
                                    <?php
                                    $count++;
                                endwhile;
                                endif;
                                wp_reset_postdata();
                                ?>
                        </div>
                    </div>
            </div>
        <?php 
        }
    }
endif;
add_action('newsmark_action_news_ticker_posts', 'newsmark_news_ticker_posts', 10);

if (!function_exists('newsmark_header_section')) :
/**
 *  Slider
 *
 * @since Newsup
 *
 */
function newsmark_header_section()
{
    
    $show_flash_news_section = esc_attr(get_theme_mod('show_flash_news_section','true'));
    $header_data_enable = esc_attr(get_theme_mod('header_data_enable','true'));
    $header_time_enable = esc_attr(get_theme_mod('header_time_enable','true'));
 if(($header_data_enable == true) || ($header_time_enable == true) || ($show_flash_news_section == true)) {
?>
<div class="mg-head-detail hidden-xs">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-9 col-xs-12">
                <?php do_action('newsmark_action_news_ticker_posts'); ?>
            </div>
            <div class="col-md-3 col-xs-12">
                <ul class="info-left float-md-right">
                    <?php newsup_date_display_type(); ?>
                </ul>
            </div>
    
        </div>
    </div>
</div>
<?php 
} }
endif;
add_action('newsmark_action_header_section', 'newsmark_header_section', 5);


if (!function_exists('newsmark_header_social_icon')) :
    /**
     *  Slider
     *
     * @since Newsup
     *
     */
    function newsmark_header_social_icon()
    {
        
        $header_social_icon_enable = esc_attr(get_theme_mod('header_social_icon_enable','true'));
                $newsup_header_fb_link = get_theme_mod('newsup_header_fb_link');
                $newsup_header_fb_target = esc_attr(get_theme_mod('newsup_header_fb_target','true'));
                $newsup_header_twt_link = get_theme_mod('newsup_header_twt_link');
                $newsup_header_twt_target = esc_attr(get_theme_mod('newsup_header_twt_target','true'));
                $newsup_header_lnkd_link = get_theme_mod('newsup_header_lnkd_link');
                $newsup_header_lnkd_target = esc_attr(get_theme_mod('newsup_header_lnkd_target','true'));
                $newsup_header_insta_link = get_theme_mod('newsup_header_insta_link');
                $newsup_insta_insta_target = esc_attr(get_theme_mod('newsup_insta_insta_target','true'));
                $newsup_header_youtube_link = get_theme_mod('newsup_header_youtube_link');
                $newsup_header_youtube_target = esc_attr(get_theme_mod('newsup_header_youtube_target','true'));
                $newsup_header_pintrest_link = get_theme_mod('newsup_header_pintrest_link');
                $newsup_header_pintrest_target = esc_attr(get_theme_mod('newsup_header_pintrest_target','true'));
                $newsup_header_telegram_link = get_theme_mod('newsup_header_tele_link');
                $newsup_header_tele_target = esc_attr(get_theme_mod('newsup_header_tele_target','true'));
                 
                if($header_social_icon_enable == true) { ?>
                    <ul class="mg-social info-right">
                        <?php if($newsup_header_fb_link !=''){ ?>
                        <li><a <?php if($newsup_header_fb_target) { ?> target="_blank" <?php } ?>href="<?php echo esc_url($newsup_header_fb_link); ?>">
                        <span class="icon-soci facebook"><i class="fab fa-facebook"></i></span> </a></li>
                        <?php } ?>
                        <?php if($newsup_header_twt_link !=''){ ?>
                        <li><a <?php if($newsup_header_twt_target) { ?>target="_blank" <?php } ?>href="<?php echo esc_url($newsup_header_twt_link);?>">
                        <span class="icon-soci x-twitter"><i class="fa-brands fa-x-twitter"></i></span></a></li>
                        <?php } ?>
                        <?php if($newsup_header_lnkd_link !=''){ ?>
                        <li><a <?php if($newsup_header_lnkd_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_lnkd_link); ?>">
                        <span class="icon-soci linkedin"><i class="fab fa-linkedin"></i></span></a></li>
                        <?php } ?>
                        <?php if($newsup_header_insta_link !=''){ ?>
                        <li><a <?php if($newsup_insta_insta_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_insta_link); ?>">
                        <span class="icon-soci instagram"><i class="fab fa-instagram"></i></span></a></li>
                        <?php } ?>
                        <?php if($newsup_header_youtube_link !=''){ ?>
                        <li><a <?php if($newsup_header_youtube_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_youtube_link); ?>">
                        <span class="icon-soci youtube"><i class="fab fa-youtube"></i></span></a></li>
                        <?php } ?>
                        <?php if($newsup_header_pintrest_link !=''){ ?>
                        <li><a <?php if($newsup_header_pintrest_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_pintrest_link); ?>">
                        <span class="icon-soci pinterest"><i class="fab fa-pinterest-p"></i></span></a></li>
                        <?php } ?> 
                        <?php if($newsup_header_telegram_link !=''){ ?>
                        <li><a <?php if($newsup_header_tele_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_telegram_link); ?>">
                        <span class="icon-soci telegram"><i class="fab fa-telegram"></i></span></a></li>
                        <?php } ?>
                    </ul>
                <?php }
    
    } 
endif;
add_action('newsmark_action_header_social_icon', 'newsmark_header_social_icon', 5);

if (!function_exists('newsmark_header_search_section')) :
/**
 *  Search
 *
 * @since Newsup
 *
 */
function newsmark_header_search_section() { 
    $header_search_enable = get_theme_mod('header_search_enable','true');
    if($header_search_enable == true) {
    ?>
    <div class="dropdown show mg-search-box pr-2">
        <a class="dropdown-toggle msearch ml-auto" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-search"></i>
        </a>
        <div class="dropdown-menu searchinner" aria-labelledby="dropdownMenuLink">
            <?php get_search_form(); ?>
        </div>
    </div>
    <?php } 
}
endif;
add_action('newsmark_action_header_search_section', 'newsmark_header_search_section', 5);

if (!function_exists('newsmark_header_subscribe_section')) :
    /**
     *  Subscribe
     *
     * @since Newsup
     *
     */
    function newsmark_header_subscribe_section() { 
        $header_subsc_enable = get_theme_mod('header_subsc_enable','true');
        $subsc_target = get_theme_mod('newsup_subsc_link_target','true');
        $newsup_subsc_link = get_theme_mod('newsup_subsc_link','#');
        if($header_subsc_enable == true) {
        ?>
          <a href="<?php echo esc_url($newsup_subsc_link); ?>" <?php if($subsc_target) { ?> target="_blank" <?php } ?>  class="btn-bell btn-theme mx-2">
            <i class="fa fa-bell"></i>
        </a>
      <?php }
    }
endif;
add_action('newsmark_action_header_subscribe_section', 'newsmark_header_subscribe_section', 5);  