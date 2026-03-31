<?php
/**
 * Template Name: Switching Providers
 * Description: Provider switching page with comparison table.
 */
get_header();
?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background:#fdf8f3;">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div data-reveal>
        <p class="text-purple-600 text-xs font-bold uppercase tracking-wider mb-4"><?php echo esc_html( ah_field( 'sw_eyebrow', 'Seamless Provider Switching' ) ); ?></p>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif text-gray-900 leading-tight mb-6">
          <?php echo wp_kses_post( ah_field( 'sw_title', 'Switch Your Provider<br>in Under <span style="color:#6366f1;">5 Minutes</span>' ) ); ?>
        </h1>
        <p class="text-lg text-gray-600 leading-relaxed mb-8 max-w-lg">
          <?php echo esc_html( ah_field( 'sw_subtitle', 'Already using Mounjaro or Wegovy? We make switching effortless. Save up to 27%, get better support, and never miss a dose. Over 2,400 patients switched in 2024.' ) ); ?>
        </p>
        <a href="<?php echo esc_url( ah_booking_url() ); ?>" class="ah-btn-purple">Start Your Switch <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
        <div class="flex flex-wrap gap-4 mt-6 text-sm text-gray-600">
          <?php foreach ( array( 'No prescription transfer', 'Zero gap in treatment', 'Same-day approval', 'Save up to £516/year' ) as $t ) : ?>
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg><span class="font-medium"><?php echo esc_html( $t ); ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div data-reveal="right" class="relative">
        <?php $sw_image = ah_field( 'sw_hero_image', '' ); ?>
        <?php if ( $sw_image ) : echo wp_get_attachment_image( $sw_image, 'hero-image', false, array( 'class' => 'w-full rounded-3xl shadow-2xl' ) ); else : ?>
        <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=1200&h=900&fit=crop" alt="Confident woman" class="w-full rounded-3xl shadow-2xl" />
        <?php endif; ?>
        <div class="absolute -bottom-4 -right-4 bg-white rounded-2xl shadow-xl p-4 border border-gray-100">
          <p class="text-2xl font-serif text-purple-700 font-bold">2,400+</p>
          <p class="text-xs text-gray-600">Patients switched in 2024</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Comparison -->
<section class="py-14 md:py-16" style="background: #f7f4f9;">
  <div class="ah-container-wide">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-serif text-gray-900 mb-4">Why patients switch to AT Health</h2>
    </div>
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm" data-reveal>
      <table class="tr-comparison-table">
        <thead><tr><th></th><th style="background: linear-gradient(160deg, #3a2878, #1b1250); color: #fff;">AT Health</th><th>Other Providers</th></tr></thead>
        <tbody>
          <tr><td class="font-semibold text-gray-900">Mounjaro (monthly)</td><td class="text-purple-700 font-bold">From £199</td><td class="text-gray-500">£249+</td></tr>
          <tr><td class="font-semibold text-gray-900">Wegovy (monthly)</td><td class="text-purple-700 font-bold">From £179</td><td class="text-gray-500">£229+</td></tr>
          <tr><td class="font-semibold text-gray-900">Hidden fees</td><td class="text-emerald-600 font-bold">None</td><td class="text-gray-500">£29–49</td></tr>
          <tr><td class="font-semibold text-gray-900">Response time</td><td class="text-purple-700 font-bold">Within 4 hours</td><td class="text-gray-500">24–72 hours</td></tr>
          <tr><td class="font-semibold text-gray-900">Monthly check-ins</td><td class="text-emerald-600 font-bold">Included</td><td class="text-gray-500">Extra cost</td></tr>
          <tr><td class="font-semibold text-gray-900">Delivery speed</td><td class="text-purple-700 font-bold">Within 48 hours</td><td class="text-gray-500">3–7 days</td></tr>
          <tr><td class="font-semibold text-gray-900">Cancel anytime</td><td class="text-emerald-600 font-bold">Always</td><td class="text-gray-500">Varies</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Stats Strip -->
<section class="py-12" style="background: linear-gradient(135deg, #2d1f6e 0%, #3a2878 50%, #2d1f6e 100%);">
  <div class="ah-container-wide">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <?php
      $stats = array(
          array( 'number' => '2,400+', 'label' => 'Patients Switched in 2024' ),
          array( 'number' => '27%', 'label' => 'Average Savings' ),
          array( 'number' => '4.9★', 'label' => 'Patient Rating' ),
          array( 'number' => '5 min', 'label' => 'Switch Time' ),
      );
      foreach ( $stats as $stat ) : ?>
      <div>
        <p class="text-3xl md:text-4xl font-serif text-white font-bold"><?php echo esc_html( $stat['number'] ); ?></p>
        <p class="text-sm text-purple-200 mt-1"><?php echo esc_html( $stat['label'] ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>
<?php get_footer(); ?>
