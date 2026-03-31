<?php
/**
 * Template Name: Health Hub
 * Description: Blog listing page with category filtering.
 */
get_header();

$current_cat = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background:#fdf8f3;">
  <div class="ah-container text-center" data-reveal>
    <div class="inline-flex items-center gap-2 bg-white border border-purple-200 rounded-full px-5 py-2 shadow-sm mb-6">
      <span class="text-purple-600 text-sm font-semibold">Health Hub</span>
    </div>
    <h1 class="text-4xl md:text-5xl font-serif text-gray-900 leading-tight mb-8">
      <?php echo wp_kses_post( ah_field( 'hh_title', 'Expert insights on weight loss,<br>heart health, and living your <span style="color:#8e88d0;">healthiest life</span>' ) ); ?>
    </h1>

    <!-- Category Filters -->
    <div class="hh-category-filter">
      <a href="<?php echo esc_url( get_permalink() ); ?>" class="hh-filter-btn <?php echo $current_cat === '' ? 'active' : ''; ?>">All Articles</a>
      <?php
      $categories = get_categories( array( 'hide_empty' => true ) );
      foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_permalink() ) ); ?>"
           class="hh-filter-btn <?php echo $current_cat === $cat->slug ? 'active' : ''; ?>">
          <?php echo esc_html( $cat->name ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Articles Grid -->
<section class="py-14 md:py-16" style="background: #f7f4f9;">
  <div class="ah-container-wide">
    <?php
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 12,
        'paged'          => max( 1, get_query_var( 'paged' ) ),
    );
    if ( $current_cat ) {
        $args['category_name'] = $current_cat;
    }
    $hub_query = new WP_Query( $args );

    if ( $hub_query->have_posts() ) : ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto" data-stagger>
      <?php $index = 0; while ( $hub_query->have_posts() ) : $hub_query->the_post(); ?>
      <article class="hh-article-card" data-reveal style="--stagger-index:<?php echo (int) $index; ?>">
        <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="block">
          <?php the_post_thumbnail( 'health-hub-card', array( 'class' => 'w-full h-48 object-cover' ) ); ?>
        </a>
        <?php endif; ?>
        <div class="p-6">
          <?php $cats = get_the_category(); if ( $cats ) : ?>
          <span class="text-xs font-bold uppercase tracking-wider text-purple-600 mb-2 block"><?php echo esc_html( $cats[0]->name ); ?></span>
          <?php endif; ?>
          <h2 class="text-xl font-serif text-gray-900 mb-3">
            <a href="<?php the_permalink(); ?>" class="hover:text-purple-600 transition-colors"><?php the_title(); ?></a>
          </h2>
          <p class="text-sm text-gray-600 leading-relaxed mb-4"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
          <div class="flex items-center justify-between">
            <a href="<?php the_permalink(); ?>" class="text-sm font-semibold text-purple-600 hover:text-purple-700 transition-colors">Read more &rarr;</a>
            <span class="text-xs text-gray-400"><?php echo esc_html( get_the_date() ); ?></span>
          </div>
        </div>
      </article>
      <?php $index++; endwhile; ?>
    </div>

    <!-- Pagination -->
    <div class="mt-12 flex justify-center">
      <?php
      echo paginate_links( array(
          'total'     => $hub_query->max_num_pages,
          'current'   => max( 1, get_query_var( 'paged' ) ),
          'prev_text' => '&larr; Previous',
          'next_text' => 'Next &rarr;',
      ) );
      ?>
    </div>
    <?php wp_reset_postdata(); else : ?>
    <p class="text-center text-gray-500 text-lg py-12">No articles found. Check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>
<?php get_footer(); ?>
