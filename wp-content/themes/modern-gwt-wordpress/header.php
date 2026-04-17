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
  $has_custom_image_logo = govph_displayoptions( 'govph_logo_enable' );
  $has_header_search     = govph_displayoptions( 'govph_disable_search' );
?>
<div class="off-canvas-wrapper overflow-hidden">
    <div class="off-canvas-wrapper-inner" data-off-canvas-wrapper>
        <header id="site-header" class="site-header fixed top-0 left-0 right-0 z-50 transition-all duration-500">
            <div id="site-header-bar" class="site-header-bar bg-transparent transition-all duration-500">
                <div class="mx-auto flex w-full max-w-[1280px] flex-col px-4 sm:px-6 lg:px-8">
                    <div class="site-header__row flex items-center justify-between gap-4 py-4 transition-all duration-500 lg:gap-8 lg:py-5">
                        <div class="min-w-0 flex-1">
                            <div class="site-branding flex min-w-0 items-center">
                                <?php if ( $has_custom_image_logo ) : ?>
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                                       class="inline-flex min-w-0 items-center"
                                       draggable="false"
                                       title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
                                       rel="home"
                                       aria-label="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
                                        <?php govph_displayoptions( 'govph_logo' ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php govph_displayoptions( 'govph_logo' ); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-end lg:gap-4 xl:gap-6">
                            <?php if ( is_active_sidebar( 'ear-content-1' ) ) : ?>
                                <div class="site-header__meta-panel max-w-[16rem] xl:max-w-[18rem]">
					                <?php do_action( 'before_sidebar' ); ?>
					                <?php dynamic_sidebar( 'ear-content-1' ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="site-header__meta-stack flex flex-col items-end gap-2 text-right">
                                <?php if ( is_active_sidebar( 'ear-content-2' ) ) : ?>
                                    <div class="site-header__meta-panel max-w-[16rem] xl:max-w-[18rem]">
					                    <?php do_action( 'before_sidebar' ); ?>
					                    <?php dynamic_sidebar( 'ear-content-2' ); ?>
                                    </div>
                                <?php endif; ?>

                                <div id="pst-container" class="site-header__time text-xs font-medium tracking-[0.2em] uppercase" style="display: none; color: white !important; font-size: 0.7rem !important;">
                                    <div>Philippine Standard Time</div>
                                    <div id="pst-time" class="whitespace-nowrap tracking-normal"></div>
                                </div>

                                <?php if ( $has_header_search ) : ?>
                                    <div class="site-header__search w-full max-w-xs xl:max-w-sm">
                                        <?php get_search_form(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button id="site-mobile-menu-button"
                                class="site-mobile-menu-button inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-3 text-white transition-all duration-500 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/60 focus:ring-offset-2 focus:ring-offset-transparent lg:hidden"
                                type="button"
                                aria-expanded="false"
                                aria-controls="site-mobile-menu-panel"
                                aria-label="Toggle navigation menu">
                            <span class="sr-only">Toggle navigation menu</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="site-header__desktop-nav hidden items-center justify-between gap-6 border-t border-white/10 transition-all duration-500 lg:flex">
                        <div class="flex min-w-0 flex-1 items-center gap-4 xl:gap-6">
                            <?php if ( has_nav_menu( 'topbar_left' ) ) : ?>
                                <nav class="gwt-desktop-nav gwt-desktop-nav--primary min-w-0 flex-1" aria-label="Primary navigation">
                                    <ul class="dropdown menu flex flex-wrap items-center gap-1 !my-0" data-dropdown-menu>
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
                                </nav>
                            <?php endif; ?>

                            <?php if ( has_nav_menu( 'aux_nav' ) ) : ?>
                                <nav class="gwt-desktop-nav gwt-desktop-nav--aux min-w-0" aria-label="Auxiliary navigation">
                                    <ul class="dropdown menu flex flex-wrap items-center gap-1 !my-0" data-dropdown-menu>
                                        <?php
                                        wp_nav_menu(
                                            array(
                                                'theme_location' => 'aux_nav',
                                                'items_wrap'     => '%3$s',
                                                'container'      => false,
                                                'fallback_cb'    => false,
                                                'walker'         => new GWT_Walker_Nav_Menu()
                                            )
                                        );
                                        ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>

                        <?php if ( has_nav_menu( 'topbar_right' ) ) : ?>
                            <nav class="gwt-desktop-nav gwt-desktop-nav--utility shrink-0" aria-label="Contact navigation">
                                <ul class="dropdown menu flex flex-wrap items-center justify-end gap-1 !my-0" data-dropdown-menu>
                                    <?php
                                    wp_nav_menu(
                                        array(
                                            'theme_location' => 'topbar_right',
                                            'items_wrap'     => '%3$s',
                                            'container'      => false,
                                            'fallback_cb'    => false,
                                            'walker'         => new GWT_Walker_Nav_Menu()
                                        )
                                    );
                                    ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="site-mobile-menu-panel"
                 class="site-mobile-menu-panel border-t border-slate-200/70 bg-white/95 opacity-0 shadow-lg backdrop-blur-xl transition-all duration-300 lg:hidden"
                 aria-hidden="true">
                <div class="mx-auto max-h-[calc(100vh-5rem)] w-full max-w-[1280px] overflow-y-auto px-4 pb-6 pt-4 sm:px-6">
                    <?php if ( $has_header_search ) : ?>
                        <div class="mb-4 rounded-[1.5rem] bg-slate-100/80 p-3">
                            <?php get_search_form(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <?php if ( has_nav_menu( 'topbar_left' ) ) : ?>
                            <nav aria-label="Mobile primary navigation" class="rounded-[1.75rem] border border-slate-200/70 bg-white px-2 py-2 shadow-sm">
                                <p class="px-4 pb-2 pt-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Navigation</p>
                                <ul class="site-mobile-menu-list space-y-1">
                                    <?php wp_nav_menu( array(
                                        'theme_location' => 'topbar_left',
                                        'items_wrap'     => '%3$s',
                                        'container'      => false,
                                        'fallback_cb'    => false,
                                        'walker'         => new Off_Canvass_Menu()
                                    ) ); ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                        <?php if ( has_nav_menu( 'aux_nav' ) ) : ?>
                            <nav aria-label="Mobile auxiliary navigation" class="rounded-[1.75rem] border border-slate-200/70 bg-white px-2 py-2 shadow-sm">
                                <p class="px-4 pb-2 pt-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Auxiliary</p>
                                <ul class="site-mobile-menu-list space-y-1">
                                    <?php wp_nav_menu( array(
                                        'theme_location' => 'aux_nav',
                                        'items_wrap'     => '%3$s',
                                        'container'      => false,
                                        'fallback_cb'    => false,
                                        'walker'         => new Off_Canvass_Menu()
                                    ) ); ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                        <?php if ( has_nav_menu( 'topbar_right' ) ) : ?>
                            <nav aria-label="Mobile contact navigation" class="rounded-[1.75rem] border border-slate-200/70 bg-white px-2 py-2 shadow-sm">
                                <p class="px-4 pb-2 pt-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Contact</p>
                                <ul class="site-mobile-menu-list space-y-1">
                                    <?php wp_nav_menu( array(
                                        'theme_location' => 'topbar_right',
                                        'items_wrap'     => '%3$s',
                                        'container'      => false,
                                        'fallback_cb'    => false,
                                        'walker'         => new Off_Canvass_Menu()
                                    ) ); ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'ear-content-1' ) || is_active_sidebar( 'ear-content-2' ) ) : ?>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <?php if ( is_active_sidebar( 'ear-content-1' ) ) : ?>
                                    <div class="rounded-[1.75rem] border border-slate-200/70 bg-white p-4 shadow-sm">
					                    <?php do_action( 'before_sidebar' ); ?>
					                    <?php dynamic_sidebar( 'ear-content-1' ); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( is_active_sidebar( 'ear-content-2' ) ) : ?>
                                    <div class="rounded-[1.75rem] border border-slate-200/70 bg-white p-4 shadow-sm">
					                    <?php do_action( 'before_sidebar' ); ?>
					                    <?php dynamic_sidebar( 'ear-content-2' ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>
        <!-- original content goes in this container -->
        <div class="off-canvas-content min-w-full" data-off-canvas-content>