<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package GWT
 * @since Government Website Template 2.0
 */

get_header();
?>
<?php govph_displayoptions( 'govph_panel_top' ); ?>
<div id="main-content" class="row container-main">
    <div class="large-8 medium-8 columns">
        <div class="text-center max-w-lg mx-auto">
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-800">Oops! Page Not Found</h1>

            <div class="my-6">
                <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <p class="text-base text-gray-600 mb-6">
                It looks like the page you were looking for doesn't exist. It might have been moved or deleted.
            </p>

            <a href="<?php echo esc_url(home_url()); ?>" class="inline-block px-6 py-3 text-white font-semibold rounded-lg transition duration-300">
                Go to Home Page
            </a>

            <p class="text-gray-500 mt-4">
                Or, try searching for what you need:
            </p>
            <div class="mt-4">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>

    <?php
        if(is_active_sidebar('left-sidebar')){
            govph_displayoptions( 'govph_sidebar_left' );
        }

        govph_displayoptions( 'govph_sidebar_right' );
    ?>

</div>

<?php get_footer(); ?>