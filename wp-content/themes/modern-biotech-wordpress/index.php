<?php
/**
 * Index template.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="mbw-main" class="mbw-shell mbw-content">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('mbw-card'); ?>>
                <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                <p><?php echo esc_html(get_the_date()); ?></p>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <article class="mbw-card">
            <h1><?php esc_html_e('No content found', 'modern-biotech-wordpress'); ?></h1>
        </article>
    <?php endif; ?>
</main>
<?php
get_footer();
