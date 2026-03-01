<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js" lang="en">

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Tailwind CDN removed for production (supply-chain risk + dev-only). Use a locally-compiled Tailwind CSS build instead. -->
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
    <link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/favicon.ico' ); ?>">
	<?php wp_head(); ?>

    <style>
        .container-main a,
        .container-main a:active,
        .container-main a:visited,
        .anchor a,
        .anchor a:active,
        .anchor a:visited {
        <?php govph_displayoptions('govph_anchorcolor');
        ?>
        }

        .container-main a:focus,
        .container-main a:hover,
        .anchor a:focus,
        .anchor a:hover {
        <?php govph_displayoptions('govph_anchorcolor_hover');
        ?>
        }

        div .container-masthead {
        <?php govph_displayoptions('govph_header_setting');
        ?><?php govph_displayoptions('govph_background_header_size_setting');
        ?>
        }

        h1.logo a {
        <?php govph_displayoptions('govph_logo_setting');
        ?>
        }

        /* For Enable and Disable image logo to be center or left position */
        h1.logo {
        <?php govph_displayoptions('govph_logo_setting');
        ?>
        }

        /* End for Enable and Disable image logo to be center or left position */
        div.container-banner {
        <?php govph_displayoptions('govph_slider_setting');
        ?>
        }

        .banner-content,
        .orbit .orbit-bullets {
        <?php govph_displayoptions('govph_slider_fullwidth');
        ?>
        }

        #pst-container {
        <?php govph_displayoptions('govph_custom_pst');
        ?>
        }


        /* Start For Subject of Approval
		Start for custom menu color

		This is start for custom menu, font, hover colors

		Start for border menu color that shows dropdown menu you can add also for auxiliary  #aux-main .has-dropdown>a::after
		This should have no background color
		#main-nav .has-dropdown>a::after {

        <?php govph_displayoptions('govph_menu_font_setting');
				?>

			}

			This is for menu nav colors
			#main-nav {
				This is for auxialliary and topbar to have custom colors

        <?php govph_displayoptions('govph_menu_color_setting');
				?>

			}

			End for menu nav colors

			This is for menu font colors in steady
			and add this code if want to reflect in auxiliary


			#aux-main li:not(.has-form) a:not(.button),
			#aux-main li.active:not(.has-form) a:not(.button)


			#main-nav li:not(.has-form) a:not(.button),
			#main-nav li.active:not(.has-form) a:not(.button) {

        <?php govph_displayoptions('govph_menu_font_setting');
				?>

			}

			This is for color of submenus in main nav
			#main-nav li:not(.has-form) a:not(.button) {

        <?php govph_displayoptions('govph_menu_color_setting');
				?>

			}

			End for menu font colors in steady

			#main-nav li.current-menu-item:not(.has-form),
			#aux-main li.current-menu-item:not(.has-form) {

        <?php govph_displayoptions('govph_menu_color_setting');
				?>

			}

			Button menu color on steady
			#magnifier-button,
			#accessibility-button {

        <?php govph_displayoptions('govph_menu_font_accessibility_setting');
				?>

			}

			Button menu color on hover
			#magnifier-button:hover,
			#accessibility-button:hover {

        <?php govph_displayoptions('govph_menu_font_hover_setting');
				?>

			}

			This is menu color for hover
			#main-nav ul li:hover:not(.has-form)>a,
			#main-nav .dropdown li:not(.has-form) a:not(.button):hover,
			#main-nav .dropdown li:not(.has-form):hover>a:not(.button),
			#aux-main ul li:hover:not(.has-form)>a,
			#aux-main .dropdown li:not(.has-form) a:not(.button):hover,
			#aux-main .dropdown li:not(.has-form):hover>a:not(.button) {

        <?php govph_displayoptions('govph_menu_color_setting');
				?><?php govph_displayoptions('govph_menu_font_hover_setting');
				?>

			}

			End for menu color for hover

			#main-nav li.current-menu-item:not(.has-form) a:not(.button),
			#aux-main li.current-menu-item:not(.has-form) a:not(.button),
			#offCanvasRight li.current-menu-item:not(.has-form)>a:not(.button) {

        <?php govph_displayoptions('govph_menu_color_setting');
				?><?php govph_displayoptions('govph_menu_font_hover_setting');
				?>

			}

			End for Custom menu color
			End for Subject of Approval Approval */

        #panel-top {
        <?php govph_displayoptions('govph_custom_panel_top');
        ?>
        }

        #panel-bottom {
        <?php govph_displayoptions('govph_custom_panel_bottom');
        ?>
        }

        #sidebar-left .widget,
        #sidebar-right .widget,
        .callout.secondary {
        <?php govph_displayoptions('govph_widget_setting');
        ?>
        }

        .container-main .entry-title a {
        <?php govph_displayoptions('govph_headings_setting');
        ?>
        }

        .container-banner .entry-title {
        <?php govph_displayoptions('govph_inner_headings_setting');
        ?>
        }

        #footer {
        <?php govph_displayoptions('govph_custom_footer_background_color');
        ?>
        }
    </style>
    <script type="text/javascript" language="javascript">
        var template_directory = '<?php echo esc_js( get_template_directory_uri() ); ?>';
    </script>
</head>
<?php
error_log("FB_DEBUG UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? '[none]') . " -- REQUEST: " . ($_SERVER['REQUEST_URI'] ?? '[none]'));
?>
<body <?php body_class(); ?>>

<div id="accessibility-shortcuts">
    <ul>
        <li><a href="#" class="skips toggle-statement" title="Toggle Accessibility Statement" accesskey="0"
               data-toggle="a11y-modal">Toggle Accessibility Statement</a></li>
		<?php if ( $govph_acc_link_home = govph_displayoptions( 'govph_acc_link_home' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_home ); ?>" accesskey="h">Home</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_contact = govph_displayoptions( 'govph_acc_link_contact' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_contact ); ?>" accesskey="c">Contacts</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_feedback = govph_displayoptions( 'govph_acc_link_feedback' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_feedback ); ?>" accesskey="k">Feedback</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_faq = govph_displayoptions( 'govph_acc_link_faq' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_faq ); ?>" accesskey="q">FAQ</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_search = govph_displayoptions( 'govph_acc_link_search' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_search ); ?>" accesskey="s">Search</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_main_content = govph_displayoptions( 'govph_acc_link_main_content' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_main_content ); ?>" accesskey="R">Skip to Main Content</a></li>
		<?php endif; ?>
		<?php if ( $govph_acc_link_sitemap = govph_displayoptions( 'govph_acc_link_sitemap' ) ): ?>
            <li><a href="<?php echo esc_url( $govph_acc_link_sitemap ); ?>" accesskey="M">Sitemap</a></li>
		<?php endif; ?>
    </ul>
</div>

<div id="a11y-modal" class="reveal large" title="Accessibility Statement" data-reveal>
        <textarea rows="21" class="statement-textarea" readonly>
This website adopts the Web Content Accessibility Guidelines (WCAG 2.0) as the accessibility standard for all its related web development and services. WCAG 2.0 is also an international standard, ISO 40500. This certifies it as a stable and referenceable technical standard. 

WCAG 2.0 contains 12 guidelines organized under 4 principles: Perceivable, Operable, Understandable, and Robust (POUR for short). There are testable success criteria for each guideline. Compliance to these criteria is measured in three levels: A, AA, or AAA. A guide to understanding and implementing Web Content Accessibility Guidelines 2.0 is available at: https://www.w3.org/TR/UNDERSTANDING-WCAG20/

Accessibility Features

Shortcut Keys Combination Activation Combination keys used for each browser.

	Chrome for Linux press (Alt+Shift+shortcut_key) 
	Chrome for Windows press (Alt+shortcut_key) 
	For Firefox press (Alt+Shift+shortcut_key) 
	For Internet Explorer press (Alt+Shift+shortcut_key) then press (enter)
	On Mac OS press (Ctrl+Opt+shortcut_key)

	Accessibility Statement (Combination + 0): Statement page that will show the available accessibility keys. 
	Home Page (Combination + H): Accessibility key for redirecting to homepage. 
	Main Content (Combination + R): Shortcut for viewing the content section of the current page. 
	FAQ (Combination + Q): Shortcut for FAQ page. 
	Contact (Combination + C): Shortcut for contact page or form inquiries. 
	Feedback (Combination + K): Shortcut for feedback page. 
	Site Map (Combination + M): Shortcut for site map (footer agency) section of the page. 
	Search (Combination + S): Shortcut for search page. 

Press esc, or click the close the button to close this dialog box.
	</textarea>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<?php
	    $name_slogan_class   = 'large-12 ';
	    $ear_content_class   = '';
	    $ear_content_2_class = '';
	    if ( is_active_sidebar( 'ear-content-1' ) && is_active_sidebar( 'ear-content-2' ) ) {
		    $name_slogan_class   = 'large-6 ';
		    $ear_content_class   = 'large-3 ';
		    $ear_content_2_class = 'large-3 ';
	    } elseif ( is_active_sidebar( 'ear-content-1' ) && ! is_active_sidebar( 'ear-content-2' ) ) {
		    $name_slogan_class = 'large-9 ';
		    $ear_content_class = 'large-3 ';
	    } elseif ( ! is_active_sidebar( 'ear-content-1' ) && is_active_sidebar( 'ear-content-2' ) ) {
		    $name_slogan_class   = 'large-9 ';
		    $ear_content_2_class = 'large-3 ';
	    }
	    ?>

<div class="off-canvas-wrapper overflow-hidden">
    <div class="off-canvas-wrapper-inner" data-off-canvas-wrapper>
        <!-- off-canvas right menu -->
        <nav id="mySidenav" class="sidenav hide-for-large fixed top-0 left-0 z-[99] bg-[#006837]" style="z-index: 1001;">
            <div class="flex flex-row justify-between items-center px-1 w-full absolute top-0">
                <a href="javascript:void(0)" class="closebtn mt-2 !text-white" id="closeNav">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </a>
            </div>
            <div style="padding:10px;" class="list-item"><?php get_search_form(); ?></div>
            <ul class="!whitespace-nowrap overflow-hidden overflow-ellipsis" style="list-style: none; padding:10px;">
				<?php wp_nav_menu( array(
					'theme_location' => 'topbar_left',
					'items_wrap'     => '%3$s',
					'container'      => false,
					'walker'         => new Off_Canvass_Menu()
				) ); ?>
				<?php if(has_nav_menu('aux_nav')): ?>
                <li id="aux-offmenu" class="list-item">AUXILIARY MENU</li>
				<?php wp_nav_menu( array(
					'theme_location' => 'aux_nav',
					'items_wrap'     => '%3$s',
					'container'      => false,
					'fallback_cb'    => false,
					'walker'         => new Off_Canvass_Menu()
				) ); ?>
				<?php endif; ?>
            </ul>
            <ul class="flex items-center justify-evenly border-t p-2">
                <li class="opacity-75 font-semibold text-gray-300">Contact Us</li>
	            <?php wp_nav_menu( array(
		            'theme_location' => 'topbar_right',
		            'items_wrap'     => '%3$s',
		            'container'      => false,
		            'fallback_cb'    => false,
		            'walker'         => new Off_Canvass_Menu()
	            ) ); ?>
            </ul>
        </nav>

        <div class="min-w-full min-h-screen fixed top-0 left-0 hidden backdrop-blur-sm z-10" id="closeBtnOverlay" style="z-index: 1000;"></div>


        <!-- off-canvas title bar for 'small' screen -->
        <div class="py-5 md:hidden lg:hidden"></div>
        <div id="off-canvas-container" class="title-bar fixed top-0 columns md:hidden block py-1 bg-gradient-to-r from-[#1f5d2b] to-[#a2b917]">
            <div class="flex justify-between w-full drop-shadow">
                <div class="title-bar-left flex flex-row items-center w-full">
                    <!-- masthead -->
                    <header class="container-masthead border-none text-black w-full">
                        <div class="row p-0 mx-auto border-none w-full">
                            <h1 class="<?php echo esc_attr( $name_slogan_class ); ?> select-none w-full" draggable="false">
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                                   draggable="false"
                                   title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
                                   rel="home" class="w-full flex"></a>
                                <?php govph_displayoptions( 'govph_logo' ); ?>
                            </h1>
                        </div>
                    </header>
                    <!-- masthead -->
                </div>
                <div class="title-bar-right flex items-center justify-end h-full my-auto">
                    <span class="sr-only hidden">Menu</span>
                    <button style="cursor:pointer;" id="openNav" class="menu-icon text-white" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- "main-nav" top-bar menu for 'medium' and up -->
        <div id="main-nav">
            <div class="bg-gradient-to-r from-[#1f5d2b] to-[#a2b917] drop-shadow lg:flex md:flex hidden px-2 md:px-0">
                <div class="row flex items-center md:p-3 py-0 w-full">
                    <nav class="top-bar-left sm:block hidden w-full">
                        <!-- masthead -->
                        <header class="container-masthead">
                            <div class="row sm:py-0 py-2 mx-auto">
                                <h1 class="<?php echo esc_attr( $name_slogan_class ); ?> columns select-none w-full" draggable="false">
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                                       title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
                                       rel="home"><?php govph_displayoptions( 'govph_logo' ); ?></a>
                                </h1>

				                <?php if ( is_active_sidebar( 'ear-content-1' ) ): ?>
                                    <div class="<?php echo esc_attr( $ear_content_class ); ?> columns">
						                <?php do_action( 'before_sidebar' ); ?>
						                <?php dynamic_sidebar( 'ear-content-1' ) ?>
                                    </div>
				                <?php endif; ?>
                            </div>
                        </header>
                        <!-- masthead -->
                    </nav>
                    <nav class="top-bar-right sm:block hidden sm:flex sm:flex-col sm:gap-1 my-auto">
                        <!-- Philippine Standard Timeewe -->
		                <?php if ( is_active_sidebar( 'ear-content-2' ) ): ?>
                            <div class="<?php echo esc_attr( $ear_content_2_class ); ?> m-0">
				                <?php do_action( 'before_sidebar' ); ?>
				                <?php dynamic_sidebar( 'ear-content-2' ) ?>
                            </div>
			                <?php endif; ?>
                      <div id="pst-container" style="display: none; color: white !important; font-size: 0.7rem !important;">
                            <div>Philippine Standard Time</div>
                            <div id="pst-time" class="whitespace-nowrap"></div>
                        </div>
                        <div class="border-none">
                            <?php if ( govph_displayoptions( 'govph_disable_search' ) ): ?>
                                <div><?php get_search_form(); ?></div>
                            <?php endif ?>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="row hidden lg:block md:block">
                <div class="flex flex-row justify-between">
                    <ul class="dropdown menu flex w-full flex-row justify-between mt-2" data-dropdown-menu>
                       <!-- <li class=" nav-item">
                            <a href="https://www.gov.ph">GOVPH</a>
                        </li>-->

                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'topbar_left',
                                'items_wrap'     => '%3$s',
                                'container'      => false,
                                'fallback_cb'    => false,
                                'walker'         => new GWT_Walker_Nav_Menu()
                            )
                        );
                        ?>
                    </ul>
                    <ul class="dropdown menu flex flex-row justify-between" data-dropdown-menu>
                        <?php wp_nav_menu( array(
                            'theme_location' => 'topbar_right',
                            'items_wrap'     => '%3$s',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'walker'         => new GWT_Walker_Nav_Menu()
                        ) ); ?>
                    </ul>
                </div>
            </div>
        </div>
        <div id="auxiliary" class="show-for-large">
            <div class="row">
                <div class="small-12 large-12 columns toplayer">
                    <nav id="aux-main" class="nomargin show-for-medium-up" data-dropdown-content>
                        <ul class="dropdown menu" data-dropdown-menu>
						    <?php
						    wp_nav_menu(
							    array(
						    	'theme_location'  => 'aux_nav',
						    	'items_wrap' => '%3$s',
						    	'container' => false,
						    	'fallback_cb' => false,
						    	'walker' => new GWT_Walker_Nav_Menu()
						    )
						    );
						    ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- original content goes in this container -->
        <div class="off-canvas-content min-w-full" data-off-canvas-content>