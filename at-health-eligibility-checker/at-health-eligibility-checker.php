<?php
/**
 * Plugin Name:       AT Health Eligibility Checker
 * Plugin URI:        https://athealth.co.uk/
 * Description:       Embeddable weight-loss eligibility questionnaire for AT Health. Adds the [at_health_eligibility_checker] shortcode.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            AT Health
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       at-health-eligibility-checker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AT_HEALTH_EC_VERSION', '0.1.0' );
define( 'AT_HEALTH_EC_PATH', plugin_dir_path( __FILE__ ) );
define( 'AT_HEALTH_EC_URL', plugin_dir_url( __FILE__ ) );
define( 'AT_HEALTH_EC_SHORTCODE', 'at_health_eligibility_checker' );

/**
 * Conditionally enqueue the Inter font, stylesheet, and script —
 * only on singular pages that actually contain the shortcode.
 *
 * Registering from `wp_enqueue_scripts` (rather than from inside the
 * shortcode callback) is important so the CSS lands in <head> and
 * avoids a flash of unstyled content.
 */
function at_health_ec_enqueue_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();
	if ( ! $post || ! has_shortcode( $post->post_content, AT_HEALTH_EC_SHORTCODE ) ) {
		return;
	}

	// Google Font: Inter.
	wp_enqueue_style(
		'at-health-ec-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	// Plugin stylesheet.
	$css_rel  = 'assets/css/eligibility-checker.css';
	$css_path = AT_HEALTH_EC_PATH . $css_rel;
	wp_enqueue_style(
		'at-health-ec-styles',
		AT_HEALTH_EC_URL . $css_rel,
		array( 'at-health-ec-inter' ),
		file_exists( $css_path ) ? filemtime( $css_path ) : AT_HEALTH_EC_VERSION
	);

	// Plugin script.
	$js_rel  = 'assets/js/eligibility-checker.js';
	$js_path = AT_HEALTH_EC_PATH . $js_rel;
	wp_enqueue_script(
		'at-health-ec-script',
		AT_HEALTH_EC_URL . $js_rel,
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : AT_HEALTH_EC_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'at_health_ec_enqueue_assets' );

/**
 * Shortcode: [at_health_eligibility_checker]
 *
 * Outputs the wrapper div that scopes all plugin CSS. The full
 * questionnaire markup is populated in Phase 3 of the build.
 */
function at_health_ec_render_shortcode() {
	ob_start();
	?>
	<div id="at-health-checker-root">
		<!-- Eligibility checker markup will be populated in Phase 3. -->
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( AT_HEALTH_EC_SHORTCODE, 'at_health_ec_render_shortcode' );
