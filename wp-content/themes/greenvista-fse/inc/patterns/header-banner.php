<?php  
/**
 * Default Header Banner
 */
return array(
	'title'      => esc_html__( 'Header Banner', 'greenvista-fse' ),
	'categories' => array( 'greenvista-fse', 'Header Banner' ),
	'content'    => '<!-- wp:group {"className":"header-main-banner","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"},"blockGap":"0","margin":{"top":"0","bottom":"0"}},"dimensions":{"minHeight":"0px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group header-main-banner" style="min-height:0px;margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:cover {"url":"'.esc_url(get_template_directory_uri()).'/assets/images/header-banner.jpg","id":3139,"dimRatio":0,"customOverlayColor":"#f4a38a","isUserOverlayColor":false,"minHeight":650,"isDark":false,"className":"hdrbanner-BX","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}}} -->
<div class="wp-block-cover is-light hdrbanner-BX" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:650px"><img class="wp-block-cover__image-background wp-image-3139" alt="" src="'.esc_url(get_template_directory_uri()).'/assets/images/header-banner.jpg" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim" style="background-color:#f4a38a"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"bannerInfo","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"},"blockGap":"0","margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"1170px","justifyContent":"center"}} -->
<div class="wp-block-group bannerInfo" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:heading {"textAlign":"left","style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"fontSize":"60px","fontStyle":"normal","fontWeight":"700","lineHeight":"1.1"},"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}},"textColor":"foreground","fontFamily":"poppins"} -->
<h2 class="wp-block-heading has-text-align-left has-foreground-color has-text-color has-link-color has-poppins-font-family" style="margin-bottom:var(--wp--preset--spacing--60);font-size:60px;font-style:normal;font-weight:700;line-height:1.1">Best Gardening <br /> in the Town</h2>
<!-- /wp:heading -->

<!-- wp:buttons {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","justifyContent":"left"}} -->
<div class="wp-block-buttons"><!-- wp:button {"textAlign":"center","backgroundColor":"foreground","className":"is-style-fill","style":{"typography":{"fontSize":"18px","fontStyle":"lite","fontWeight":"500"},"spacing":{"padding":{"left":"var:preset|spacing|60","right":"var:preset|spacing|60","top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}},"border":{"radius":"50px"},"color":{"text":"#282828"}},"fontFamily":"inter"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-foreground-background-color has-text-color has-background has-inter-font-family has-text-align-center has-custom-font-size wp-element-button" href="#" style="border-radius:50px;color:#282828;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--60);font-size:18px;font-style:lite;font-weight:500">Discover More</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->',
);