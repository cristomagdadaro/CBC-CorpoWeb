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
  <?php
  $govph_preloader_options      = get_option( 'govph_options', array() );
  $preloader_logo_candidates    = array(
    array(
      'path' => trailingslashit( get_theme_root() ) . 'modern-gwt-wordpress/images/cbc-logo.png',
      'url'  => trailingslashit( get_theme_root_uri() ) . 'modern-gwt-wordpress/images/cbc-logo.png',
    ),
    array(
      'path' => trailingslashit( get_theme_root() ) . 'customcbc/images/cbc-logo.png',
      'url'  => trailingslashit( get_theme_root_uri() ) . 'customcbc/images/cbc-logo.png',
    ),
    array(
      'path' => WP_CONTENT_DIR . '/uploads/2024/06/DA-CBC-Logo-white-DA.png',
      'url'  => content_url( '/uploads/2024/06/DA-CBC-Logo-white-DA.png' ),
    ),
  );
  $preloader_logo_src = '';
  foreach ( $preloader_logo_candidates as $preloader_logo_candidate ) {
    if ( file_exists( $preloader_logo_candidate['path'] ) ) {
      $preloader_logo_src = esc_url( $preloader_logo_candidate['url'] );
      break;
    }
  }
  if ( empty( $preloader_logo_src ) && ! empty( $govph_preloader_options['govph_logo'] ) ) {
    $preloader_logo_src = esc_url( $govph_preloader_options['govph_logo'] );
  }
  if ( empty( $preloader_logo_src ) ) {
    $preloader_logo_src = esc_url( get_template_directory_uri() . '/images/logo-masthead-large.png' );
  }
  $preloader_logo_alt = ! empty( $govph_preloader_options['govph_agency_name'] )
    ? sprintf( '%s Logo', $govph_preloader_options['govph_agency_name'] )
    : 'DA-Crop Biotechnology Center Logo';
  ?>
  <link rel="preconnect" href="<?php echo esc_url( home_url( '/' ) ); ?>" crossorigin>
  <link rel="preload" as="image" href="<?php echo esc_url( $preloader_logo_src ); ?>" fetchpriority="high">
  <script type="text/javascript">document.documentElement.classList.add('cbc-preloader-active');</script>
  <style id="preloader-critical-css">
    html.cbc-preloader-active,
    html.cbc-preloader-active body {
      overflow: hidden;
    }

    #preloader {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      background: linear-gradient(135deg, #1a5c3a 0%, #4a8c3a 100%);
      z-index: 9999;
      opacity: 1;
      visibility: visible;
      overflow: hidden;
      transition: opacity 0.7s ease, visibility 0.7s ease;
    }

    #preloader::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at center, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0) 60%);
      opacity: 0.8;
    }

    #preloader.preloader-hidden {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
    }

    .preloader-content {
      position: relative;
      z-index: 1;
      width: min(100%, 34rem);
      text-align: center;
    }

    .preloader-logo-shell {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 1.1rem;
      border-radius: 999px;
      isolation: isolate;
    }

    .preloader-logo-shell::before,
    .preloader-logo-shell::after {
      content: "";
      position: absolute;
      inset: 10%;
      border-radius: 999px;
      pointer-events: none;
      opacity: 0;
      transform: scale(0.8);
    }

    .preloader-logo-shell::before {
      background: radial-gradient(circle, rgba(255, 214, 102, 0.32) 0%, rgba(255, 214, 102, 0.08) 42%, rgba(255, 214, 102, 0) 72%);
      filter: blur(10px);
    }

    .preloader-logo-shell::after {
      background: radial-gradient(circle, rgba(255, 255, 255, 0.24) 0%, rgba(196, 255, 216, 0.12) 38%, rgba(255, 255, 255, 0) 68%);
      filter: blur(18px);
    }

    .preloader-logo {
      width: 120px;
      height: auto;
      position: relative;
      z-index: 1;
      opacity: 0;
      transform: scale(0.8);
      filter: drop-shadow(0 18px 36px rgba(255, 214, 102, 0.22));
    }

    .preloader-tagline {
      margin: 1.25rem auto 0;
      max-width: 30rem;
      color: #ffffff;
      font-size: clamp(0.75rem, 1vw + 0.5rem, 0.95rem);
      font-weight: 600;
      letter-spacing: 0.28em;
      line-height: 1.7;
      text-transform: uppercase;
      opacity: 0;
      transform: translateY(20px);
      clip-path: inset(0 100% 0 0);
    }

    .loading-dots {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.65rem;
      margin: 1.45rem auto 0;
      padding: 0.45rem 0.9rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
      opacity: 0;
    }

    .loading-dot {
      width: 0.72rem;
      height: 0.72rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.34);
      transform: scale(0.82);
      transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .loading-dot.is-active,
    .loading-dot.is-complete {
      background: #ffe082;
      opacity: 1;
      transform: scale(1);
      box-shadow: 0 0 18px rgba(255, 224, 130, 0.6);
    }

    html.cbc-preloader-active .off-canvas-wrapper {
      opacity: 0.72;
      transform: none;
      filter: none;
      transition: opacity 0.95s ease;
      will-change: opacity;
    }

    html.cbc-preloader-complete .off-canvas-wrapper {
      opacity: 1;
      transform: none;
      filter: none;
    }

    html.cbc-preloader-active #floating-sidebar-container,
    html.cbc-preloader-active #back-to-top {
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
    }

    #preloader.preloader-started .preloader-logo-shell {
      animation: cbc-preloader-shell-float 3.2s ease-in-out 0.9s infinite;
    }

    #preloader.preloader-started .preloader-logo-shell::before {
      animation: cbc-preloader-shell-glow 1.8s ease-in-out 0.45s infinite alternate;
    }

    #preloader.preloader-started .preloader-logo-shell::after {
      animation: cbc-preloader-shell-glow-secondary 2.2s ease-in-out 0.7s infinite alternate;
    }

    #preloader.preloader-started .preloader-logo {
      animation:
        cbc-preloader-logo-reveal 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards,
        cbc-preloader-logo-glow 1.8s ease-in-out 0.55s infinite alternate;
    }

    #preloader.preloader-started .preloader-tagline {
      animation: cbc-preloader-tagline-reveal 0.7s ease 0.8s forwards;
    }

    #preloader.preloader-started .loading-dots {
      animation: cbc-preloader-fade-in 0.4s ease 1s forwards;
    }

    #preloader.preloader-started .loading-dot {
      animation: cbc-preloader-dot-idle 1.2s ease-in-out infinite;
    }

    #preloader.preloader-started .loading-dot:nth-child(2) {
      animation-delay: 0.12s;
    }

    #preloader.preloader-started .loading-dot:nth-child(3) {
      animation-delay: 0.24s;
    }

    #preloader.preloader-started .loading-dot:nth-child(4) {
      animation-delay: 0.36s;
    }

    @media screen and (min-width: 640px) {
      .preloader-logo {
        width: 150px;
      }
    }

    @media screen and (min-width: 1024px) {
      .preloader-logo {
        width: 180px;
      }
    }

    @keyframes cbc-preloader-logo-reveal {
      0% {
        opacity: 0;
        transform: scale(0.8);
      }
      55% {
        opacity: 1;
        transform: scale(1.06);
      }
      100% {
        opacity: 1;
        transform: scale(1);
      }
    }

    @keyframes cbc-preloader-logo-glow {
      0% {
        filter: drop-shadow(0 12px 24px rgba(255, 214, 102, 0.18));
      }
      100% {
        filter: drop-shadow(0 26px 50px rgba(255, 255, 255, 0.18)) drop-shadow(0 0 28px rgba(255, 224, 130, 0.55));
      }
    }

    @keyframes cbc-preloader-shell-glow {
      0% {
        opacity: 0.4;
        transform: scale(0.82);
      }
      100% {
        opacity: 1;
        transform: scale(1.12);
      }
    }

    @keyframes cbc-preloader-shell-glow-secondary {
      0% {
        opacity: 0.24;
        transform: scale(0.9);
      }
      100% {
        opacity: 0.7;
        transform: scale(1.2);
      }
    }

    @keyframes cbc-preloader-shell-float {
      0%,
      100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-4px);
      }
    }

    @keyframes cbc-preloader-tagline-reveal {
      0% {
        opacity: 0;
        transform: translateY(20px);
        clip-path: inset(0 100% 0 0);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
        clip-path: inset(0 0 0 0);
      }
    }

    @keyframes cbc-preloader-fade-in {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    @keyframes cbc-preloader-dot-idle {
      0%,
      100% {
        opacity: 0.45;
        transform: scale(0.82);
      }
      50% {
        opacity: 0.85;
        transform: scale(0.94);
      }
    }

    @media (prefers-reduced-motion: reduce) {
      #preloader,
      .preloader-logo-shell,
      .preloader-logo-shell::before,
      .preloader-logo-shell::after,
      .preloader-logo,
      .preloader-tagline,
      .loading-dots,
      .loading-dot,
      html.cbc-preloader-active .off-canvas-wrapper {
        animation: none !important;
        transition-duration: 0.01ms !important;
      }

      .preloader-logo,
      .preloader-tagline,
      .loading-dots,
      .loading-dot {
        opacity: 1;
        transform: none;
        clip-path: inset(0 0 0 0);
      }

      html.cbc-preloader-active .off-canvas-wrapper {
        opacity: 1;
        transform: none;
        filter: none;
      }
    }
  </style>
  <script id="preloader-critical-js" type="text/javascript">
  (function () {
    'use strict';

    var bootTime = Date.now();
    var minimumDuration = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 2500;
    var fadeDuration = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 700;
    var maxDuration = 8000;
    var started = false;
    var finished = false;

    function setProgress(overlay, value) {
      var dots = overlay ? overlay.querySelectorAll('.loading-dot') : [];
      var bounded = Math.max(0, Math.min(100, value));
      var activeDots = dots.length ? Math.max(1, Math.min(dots.length, Math.ceil((bounded / 100) * dots.length))) : 0;

      for (var i = 0; i < dots.length; i++) {
        dots[i].classList.toggle('is-active', i < activeDots);
        dots[i].classList.toggle('is-complete', bounded >= 100);
      }
    }

    function restoreFocus() {
      var target = document.querySelector('#main-content, main, [role="main"], #site-header a[rel="home"], body');
      if (!target || typeof target.focus !== 'function') {
        return;
      }

      var hadTabIndex = target.hasAttribute && target.hasAttribute('tabindex');
      if (!hadTabIndex && target !== document.body) {
        target.setAttribute('tabindex', '-1');
      }

      window.requestAnimationFrame(function () {
        try {
          target.focus({ preventScroll: true });
        } catch (error) {
          target.focus();
        }
      });
    }

    function initPreloader() {
      var overlay = document.getElementById('preloader');
      if (!overlay) {
        return;
      }

      var logoImg = document.getElementById('preloader-logo-img');
      var body = document.body;
      var trackableImages;
      var pendingImages;
      var loadedImages = 0;
      var totalPendingImages = 0;

      function startAnimations() {
        if (started) {
          return;
        }

        started = true;
        overlay.classList.add('preloader-started');
      }

      function syncProgressWithImages() {
        if (!totalPendingImages) {
          setProgress(overlay, 88);
          return;
        }

        setProgress(overlay, 35 + (loadedImages / totalPendingImages) * 53);
      }

      function hidePreloader() {
        if (finished) {
          return;
        }

        finished = true;
        setProgress(overlay, 100);
        overlay.classList.add('preloader-hidden');
        overlay.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('cbc-preloader-active');
        document.documentElement.classList.add('cbc-preloader-complete');
        if (body) {
          body.setAttribute('aria-busy', 'false');
        }
        restoreFocus();

        window.setTimeout(function () {
          if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
          }
        }, fadeDuration);
      }

      function finalizePreloader() {
        var remaining = Math.max(0, minimumDuration - (Date.now() - bootTime));
        window.setTimeout(hidePreloader, remaining);
      }

      if (body) {
        body.setAttribute('aria-busy', 'true');
      }
      setProgress(overlay, 8);

      if (logoImg) {
        if (logoImg.complete && logoImg.naturalHeight !== 0) {
          startAnimations();
        } else {
          logoImg.addEventListener('load', startAnimations, { once: true });
          logoImg.addEventListener('error', startAnimations, { once: true });
          window.setTimeout(startAnimations, 500);
        }
      } else {
        startAnimations();
      }

      trackableImages = Array.prototype.slice.call(document.images).filter(function (image) {
        return !overlay.contains(image);
      });
      pendingImages = trackableImages.filter(function (image) {
        return !image.complete;
      });
      totalPendingImages = pendingImages.length;

      pendingImages.forEach(function (image) {
        var handleImageSettled = function () {
          loadedImages += 1;
          syncProgressWithImages();
        };

        image.addEventListener('load', handleImageSettled, { once: true });
        image.addEventListener('error', handleImageSettled, { once: true });
      });

      setProgress(overlay, 30);
      syncProgressWithImages();

      if (document.readyState === 'complete') {
        finalizePreloader();
      } else {
        window.addEventListener('load', finalizePreloader, { once: true });
      }

      window.setTimeout(hidePreloader, maxDuration);
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initPreloader, { once: true });
    } else {
      initPreloader();
    }
  })();
  </script>
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
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
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

<div id="preloader" role="status" aria-live="polite" aria-hidden="false">
  <div class="preloader-content">
    <div class="preloader-logo-shell">
      <img src="<?php echo $preloader_logo_src; ?>"
         id="preloader-logo-img"
         alt="<?php echo esc_attr( $preloader_logo_alt ); ?>"
         class="preloader-logo"
         decoding="async"
         fetchpriority="high">
    </div>
    <p class="preloader-tagline">BIOTECH FOR BETTER CROP FOR BETTER LIVES</p>
    <div class="loading-dots" aria-hidden="true">
      <span class="loading-dot"></span>
      <span class="loading-dot"></span>
      <span class="loading-dot"></span>
      <span class="loading-dot"></span>
    </div>
  </div>
</div>

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

$site_header_classes = implode(
        ' ',
        array(
                'site-header',
                'z-50',
                'transition-all',
                'duration-500',
                is_front_page()
                        ? 'fixed top-0 left-0 right-0 site-header--overlay'
                        : 'relative site-header--with-offset',
        )
);
?>
<div class="off-canvas-wrapper overflow-hidden">
    <div class="off-canvas-wrapper-inner" data-off-canvas-wrapper>
        <header id="site-header" class="<?php echo esc_attr( $site_header_classes ); ?>">
            <div id="site-header-bar" class="site-header-bar bg-transparent transition-all duration-500">
                <div class="site-header__chrome relative isolate">
                    <div id="header-gradient-bg" class="absolute inset-0 z-0 w-full" style="background: linear-gradient(90deg, #1f5d2b 0%, #55A147 50%, #a2b917 100%); -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%); mask-image: linear-gradient(to bottom, black 10%, transparent 100%);"></div>
                    <div class="site-header__bar-inner relative z-10 mx-auto flex w-full max-w-[1280px] flex-col px-4 sm:px-6 lg:px-8">
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

                                    <div id="pst-container" class="site-header__time text-xs font-medium tracking-[0.2em] uppercase" style="display: none; font-size: 0.7rem !important;">
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

                        <div class="site-header__desktop-nav hidden items-center justify-between gap-6 transition-all duration-500 lg:flex z-10">
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