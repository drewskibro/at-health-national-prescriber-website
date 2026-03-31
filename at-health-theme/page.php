<?php
/**
 * Default Page Template
 * Used for pages without a custom template assigned.
 */
get_header();
?>

<main class="ah-container py-16 md:py-24">
    <?php while ( have_posts() ) : the_post(); ?>
        <article>
            <h1 class="text-4xl md:text-5xl font-serif text-gray-900 mb-8"><?php the_title(); ?></h1>
            <div class="prose prose-lg max-w-none">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
