<?php
/**
 * Single post template.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="mbw-main" class="mbw-shell mbw-content">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('mbw-card'); ?>>
            <h1><?php the_title(); ?></h1>
            <p><?php echo esc_html(get_the_date()); ?> | <?php the_author(); ?></p>
            <?php the_content(); ?>
        </article>
        <?php if (comments_open() || get_comments_number()) : ?>
            <?php comments_template(); ?>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();
