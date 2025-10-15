<?php
if ( is_home() || is_front_page() ) {
	$banner_class   = 'large-12';
	$banner_2_class = '';
	$banner_3_class = '';
	if ( is_active_sidebar( 'banner-section-1' ) && is_active_sidebar( 'banner-section-2' ) ) {
		$banner_class   = 'large-6 columns';
		$banner_2_class = 'large-3 columns';
		$banner_3_class = 'large-3 columns';
	} elseif ( is_active_sidebar( 'banner-section-1' ) && ! is_active_sidebar( 'banner-section-2' ) ) {
		$banner_class   = 'large-8 columns';
		$banner_2_class = 'large-4 columns';
	} elseif ( ! is_active_sidebar( 'banner-section-1' ) && is_active_sidebar( 'banner-section-2' ) ) {
		$banner_class   = 'large-8 columns';
		$banner_3_class = 'large-4 columns';
	}
	// Temporary commented as it need to show on mobile devices
// $banner_class .= ' hide-for-small-only';
}

$container_class = '';
$line_class      = '';
if ( ! ( is_home() || is_front_page() ) ) {
	$container_class = 'banner-pads';
} else {
	$line_class = "line";
}
?>
<!-- banner -->
 <?php if ( is_home() || is_front_page() ): ?>

<!-- Banner Loading Screen -->
<style>
.banner-loading-overlay {
    position: relative;
    min-height: 400px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
.banner-loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
}
.banner-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-top-color: #1f5d2b;
    border-radius: 50%;
    animation: banner-spin 0.8s linear infinite;
}
@keyframes banner-spin {
    to { transform: rotate(360deg); }
}
.banner-content-wrapper {
    opacity: 0;
    transition: opacity 0.5s ease-in;
}
.banner-content-wrapper.loaded {
    opacity: 1;
}
.banner-loading-overlay.loaded {
    background: transparent;
    min-height: auto;
}
.banner-loading-overlay.loaded .banner-loading-spinner {
    display: none;
}
/* Prevent image stacking during load */
#banner-slider img {
    display: block;
    max-width: 100%;
    height: auto;
}
#banner-slider .flexslider,
#banner-slider .slides {
    position: relative;
    min-height: 300px;
}
#banner-slider .slides > li {
    position: absolute;
    width: 100%;
    opacity: 0;
    transition: opacity 0.3s ease;
}
#banner-slider .slides > li.flex-active-slide {
    position: relative;
    opacity: 1;
    z-index: 2;
}
#banner-slider .slides > li:first-child {
    position: relative;
}
</style>

<div class="banner-loading-overlay" id="bannerLoadingOverlay">
    <div class="banner-loading-spinner">
        <div class="banner-spinner"></div>
    </div>

    <div class="container-banner p-0 banner-content-wrapper <?php echo $container_class; ?>" id="bannerContentWrapper">
        <?php govph_displayoptions( 'govph_slider_start' ); ?>

    <?php if ( $banner_slider = efs_get_slider() ): ?>
    <?php if ( govph_displayoptions( 'govph_slider_full' ) == 'active' ): ?>
        <!-- For GWT 26.0.0 remove class hide-for-small-only after large-12 on id="banner-slider" to show slider image on mobile devices -->
        <div id="banner-slider" class="large-12 reveal-on-scroll-100 opacity-0 ">
            <?php else: ?>
            <div id="banner-slider" class="<?php echo $banner_class ?>">
                <?php endif; ?>
                <?php echo $banner_slider ?>
            </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'banner-section-1' ) ): ?>
                <div id="banner-section-1" class="<?php echo $banner_2_class ?>">
                    <?php do_action( 'before_sidebar' ); ?>
                    <?php dynamic_sidebar( 'banner-section-1' ) ?>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'banner-section-2' ) ): ?>
                <div id="banner-section-2" class="<?php echo $banner_3_class ?>">
                    <?php do_action( 'before_sidebar' ); ?>
                    <?php dynamic_sidebar( 'banner-section-2' ) ?>
                </div>
            <?php endif; ?>

    </div>
</div>

<script>
(function() {
    'use strict';

    const bannerOverlay = document.getElementById('bannerLoadingOverlay');
    const bannerWrapper = document.getElementById('bannerContentWrapper');
    const bannerSlider = document.getElementById('banner-slider');

    if (!bannerSlider) {
        // No banner slider, hide loading immediately
        if (bannerOverlay) {
            bannerOverlay.classList.add('loaded');
        }
        if (bannerWrapper) {
            bannerWrapper.classList.add('loaded');
        }
        return;
    }

    // Get all images in the banner
    const bannerImages = bannerSlider.querySelectorAll('img');

    if (bannerImages.length === 0) {
        // No images, hide loading immediately
        bannerOverlay.classList.add('loaded');
        bannerWrapper.classList.add('loaded');
        return;
    }

    let loadedCount = 0;
    const totalImages = bannerImages.length;
    const minLoadTime = 300; // Minimum loading time in ms to prevent flash
    const startTime = Date.now();

    function checkAllLoaded() {
        loadedCount++;

        if (loadedCount >= totalImages) {
            const elapsed = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadTime - elapsed);

            setTimeout(() => {
                if (bannerOverlay) {
                    bannerOverlay.classList.add('loaded');
                }
                if (bannerWrapper) {
                    bannerWrapper.classList.add('loaded');
                }

                // Initialize flexslider if it exists
                if (typeof jQuery !== 'undefined' && jQuery.fn.flexslider) {
                    jQuery('#banner-slider .flexslider').flexslider('play');
                }
            }, remainingTime);
        }
    }

    // Set up lazy loading for each image
    bannerImages.forEach((img, index) => {
        // If image already loaded
        if (img.complete && img.naturalHeight !== 0) {
            checkAllLoaded();
            return;
        }

        // Create a new image to preload
        const preloadImg = new Image();

        preloadImg.onload = function() {
            checkAllLoaded();
        };

        preloadImg.onerror = function() {
            console.warn('Banner image failed to load:', img.src);
            checkAllLoaded(); // Count as loaded to prevent infinite loading
        };

        // Start loading
        if (img.dataset.src) {
            // Handle lazy-loaded images
            preloadImg.src = img.dataset.src;
            img.src = img.dataset.src;
        } else if (img.src) {
            // Handle regular images
            preloadImg.src = img.src;
        }
    });

    // Fallback timeout - force load after 10 seconds
    setTimeout(() => {
        if (!bannerWrapper.classList.contains('loaded')) {
            console.warn('Banner loading timeout - forcing display');
            bannerOverlay.classList.add('loaded');
            bannerWrapper.classList.add('loaded');
        }
    }, 10000);
})();
</script>

		<?php else: ?>
			<?php if ( is_404() ): ?>
				<?php govph_displayoptions( 'govph_banner_title_start' ); ?>
                <div class="large-9 columns container-main">
                    <header>
                        <h1 class="page-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'gwt_wp' ); ?></h1>
                    </header>
                </div>
				<?php govph_displayoptions( 'govph_banner_title_end' ); ?>
			<?php elseif ( is_search() ): ?>
				<?php govph_displayoptions( 'govph_banner_title_start' ); ?>
                <div class="large-9 columns container-main">
                    <header>
                        <h1 class="page-title">
							<?php printf( __( 'Search Results for: %s', 'gwt_wp' ), '<span>' . get_search_query() . '</span>' ); ?>
                        </h1>
                    </header>
                </div>
				<?php govph_displayoptions( 'govph_banner_title_end' ); ?>
			<?php elseif ( is_archive() ): ?>
				<?php govph_displayoptions( 'govph_banner_title_start' ); ?>
                <div class="large-9 columns container-main">

                </div>
				<?php govph_displayoptions( 'govph_banner_title_end' ); ?>
			<?php else: ?>
				<?php govph_displayoptions( 'govph_banner_title_start' ); ?>
                <!-- For Version 2 -->
                	<div class="hidden large-9 columns container-main">
                        <!-- Customize this at the top banner above the breadcrumbs; Remove hidden class to show on mobile devices -->
                    </div>
                <!-- End For Version 2-->
				<?php govph_displayoptions( 'govph_banner_title_end' ); ?>
			<?php endif ?>
		<?php endif ?>

		<?php govph_displayoptions( 'govph_slider_end' ); ?>

    </div>
    <?php
    $tempHeader = '';
    if ( is_front_page() ) :
        $tempHeader = 'Announcement';
    endif;
    echo do_shortcode( '[gwt_announcements header="'.$tempHeader.'" limit="5" layout="ticker" show_image="1" link_title="1" target="_blank"]' );
    ?>
		<!-- show breadcrumbs when not in home or front page -->
		<?php if ( ! ( is_home() || is_front_page() ) ):
		include_once( 'breadcrumbs.php' );
	endif; ?>
