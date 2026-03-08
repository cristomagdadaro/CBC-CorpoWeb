<?php
/**
 * Page template.
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
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
