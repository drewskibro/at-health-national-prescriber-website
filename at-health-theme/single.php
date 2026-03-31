<?php
/**
 * Single Blog Post Template
 * Health Hub articles with featured image, metadata, and clinical authority signals.
 */
get_header();
?>

<main style="background: var(--ah-cream);">
    <?php while ( have_posts() ) : the_post(); ?>

    <!-- Hero -->
    <section class="ah-container-wide pt-12 pb-8">
        <div class="max-w-3xl mx-auto">
            <!-- Category -->
            <?php
            $categories = get_the_category();
            if ( $categories ) : ?>
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-purple-600 mb-4">
                    <?php echo esc_html( $categories[0]->name ); ?>
                </span>
            <?php endif; ?>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-serif text-gray-900 leading-tight mb-6">
                <?php the_title(); ?>
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-8">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>
                <?php
                $reading_time = ah_field( 'reading_time', '' );
                if ( $reading_time ) : ?>
                    <span>&middot;</span>
                    <span><?php echo esc_html( $reading_time ); ?> min read</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Image -->
    <?php if ( has_post_thumbnail() ) : ?>
    <section class="ah-container-wide pb-8">
        <div class="max-w-4xl mx-auto">
            <div class="rounded-2xl overflow-hidden">
                <?php the_post_thumbnail( 'health-hub-featured', array(
                    'class' => 'w-full h-auto',
                    'loading' => 'eager',
                ) ); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Content -->
    <section class="ah-container-wide pb-16">
        <div class="max-w-3xl mx-auto">
            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                <?php the_content(); ?>
            </div>
        </div>
    </section>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
