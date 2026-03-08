<?php
/**
 * Header template.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="mbw-skip-link" href="#mbw-main"><?php esc_html_e('Skip to content', 'modern-biotech-wordpress'); ?></a>

<header class="mbw-header" id="site-header">
    <div class="mbw-shell">
        <div class="mbw-nav-wrap">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="mbw-brand" aria-label="<?php esc_attr_e('Go to homepage', 'modern-biotech-wordpress'); ?>">
                <span class="mbw-brand-icon">DNA</span>
                <span class="mbw-brand-text">
                    <strong><?php bloginfo('name'); ?></strong>
                    <small><?php bloginfo('description'); ?></small>
                </span>
            </a>

            <button class="mbw-menu-toggle" id="mbw-menu-toggle" aria-expanded="false" aria-controls="mbw-mobile-menu">
                <span class="mbw-menu-open">Menu</span>
                <span class="mbw-menu-close">Close</span>
            </button>

            <nav class="mbw-nav" aria-label="<?php esc_attr_e('Primary Navigation', 'modern-biotech-wordpress'); ?>">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'mbw-nav-list',
                        'fallback_cb'    => false,
                    )
                );
                ?>
                <?php if (! has_nav_menu('primary')) : ?>
                    <ul class="mbw-nav-list">
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#programs">Programs</a></li>
                        <li><a href="#research">Research</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#experts">Experts</a></li>
                        <li><a href="#news">News</a></li>
                        <li><a href="#calendar">Calendar</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="mbw-mobile-menu" id="mbw-mobile-menu" hidden>
        <div class="mbw-shell">
            <ul class="mbw-mobile-list">
                <li><a href="#hero">Home</a></li>
                <li><a href="#programs">Programs</a></li>
                <li><a href="#research">Research</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#experts">Experts</a></li>
                <li><a href="#news">News</a></li>
                <li><a href="#calendar">Calendar</a></li>
                <li><a href="#subscribe">Subscribe</a></li>
            </ul>
        </div>
    </div>
</header>
