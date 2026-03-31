<?php
/**
 * 404 Error Page
 */
get_header();
?>

<main style="background: var(--ah-cream);">
    <section class="ah-container py-24 md:py-32 text-center">
        <h1 class="text-6xl md:text-7xl font-serif text-gray-900 mb-6">404</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-md mx-auto">
            Sorry, the page you're looking for doesn't exist or has been moved.
        </p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ah-btn-purple">
            Back to Homepage
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </section>
</main>

<?php get_footer(); ?>
