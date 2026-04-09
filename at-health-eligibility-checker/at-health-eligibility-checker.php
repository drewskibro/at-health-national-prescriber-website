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
 * Outputs the wrapper div that scopes all plugin CSS, followed by the
 * full questionnaire markup. Image URLs are injected via PHP so the
 * source `src/assets/...` paths are replaced with plugins_url() calls
 * pointing at assets/images/ inside this plugin folder.
 *
 * The render function is built up in micro-phases (3a-1 through 3b),
 * each replacing a single SCREENS_BLOCK_* placeholder with its group
 * of screens.
 */
function at_health_ec_render_shortcode() {
	$testimonial_img = esc_url( plugins_url( 'assets/images/testimonial.jpg', __FILE__ ) );

	ob_start();
	?>
	<div id="at-health-checker-root">
		<div class="header">
			<div class="header-title">AT Health</div>
		</div>

		<div class="main-content">
			<div class="progress-bar-container" id="progressBarContainer" style="display: none;">
				<div class="progress-bar">
					<div class="progress-bar-fill" id="progressBarFill"></div>
				</div>
			</div>

			<!-- Screen 1 -->
			<div id="screen-1" class="screen active">
				<div class="container">
					<h1 class="heading">Do you agree to the following?</h1>
					<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
						<p style="color: #374151; font-size: 15px; margin-bottom: 16px; line-height: 1.6;">&#9670; You are completing this consultation for yourself and to the best of your knowledge</p>
						<p style="color: #374151; font-size: 15px; margin-bottom: 16px; line-height: 1.6;">&#9670; You will disclose any medical conditions, serious illnesses or operations you have had</p>
						<p style="color: #374151; font-size: 15px; margin-bottom: 16px; line-height: 1.6;">&#9670; You will disclose any prescription medications you are currently taking and agree to use only one weight loss treatment at a time</p>
						<p style="color: #374151; font-size: 15px; margin-bottom: 16px; line-height: 1.6;">&#9670; You agree to our Terms &amp; Conditions, Terms of Sale, and confirm that you have read our Privacy Policy</p>
						<p style="color: #374151; font-size: 15px; line-height: 1.6;">&#9670; Your accurate and honest responses to this online questionnaire for weight loss treatment are crucial. <strong>Withholding or providing false information can severely harm your health and may result in life-threatening consequences.</strong></p>
					</div>
					<button class="button button-primary" onclick="agreeAndStart()">Agree and start consultation →</button>

					<div class="testimonial">
						<div class="testimonial-header">
							<img src="<?php echo $testimonial_img; ?>" alt="Tasmia Darr" class="testimonial-image">
							<div>
								<div class="testimonial-name">Tasmia Darr</div>
								<div class="stars">★★★★★</div>
							</div>
						</div>
						<div class="testimonial-text">
							"I cannot recommend AT Health highly enough. They've been absolutely fantastic! I'm currently on GLP-1 weight loss medication which is prescribed through their programme and the whole experience is brilliant. If you have been thinking about doing this, I would recommend working with AT Health instead of an online supplier. AT Health are reliable, approachable, knowledgeable and accessible. Every step of my journey has been so well supported and monitored — I'm so glad I went with them."
						</div>
					</div>
					<div class="reviews">
						<strong>Excellent ★★★★★</strong> 1,247 reviews
					</div>
				</div>
			</div>

			<!-- Screen 1b -->
			<div id="screen-1b" class="screen">
				<div class="container">
					<h1 class="heading">Let's save your assessment</h1>
					<p class="subheading">Enter your details so our clinicians can send your eligibility result and support you with the next steps if treatment is appropriate.</p>

					<div id="earlyFormError" class="error-message" style="display: none;"></div>

					<div class="input-group">
						<label class="label">First name</label>
						<input type="text" class="input" id="earlyFirstName" placeholder="e.g. Sarah">
					</div>

					<div class="input-group">
						<label class="label">Last name</label>
						<input type="text" class="input" id="earlyLastName" placeholder="e.g. Jones">
					</div>

					<div class="input-group">
						<label class="label">Email address</label>
						<input type="email" class="input" id="earlyEmail" placeholder="e.g. sarah@example.com">
					</div>

					<div class="input-group">
						<label class="label">Phone number</label>
						<input type="tel" class="input" id="earlyPhone" placeholder="07XXX XXXXXX">
					</div>

					<div class="shield-note">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10 0L2 3V9C2 14 6 18 10 20C14 18 18 14 18 9V3L10 0Z" fill="#8882c8"/>
						</svg>
						<div>Your details are kept confidential and used only for your clinical assessment and treatment support.</div>
					</div>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" onclick="saveEarlyDetails()">Continue</button>
					</div>
				</div>
			</div>

			<!-- Screen 2 -->
			<div id="screen-2" class="screen">
				<div class="container">
					<h1 class="heading">Are you currently using weight loss medication?</h1>

					<div class="card" onclick="selectNewUser()">
						<div style="font-size: 32px; margin-bottom: 8px;">✨</div>
						<div class="card-title">I'm new to treatment</div>
						<div class="card-subtitle">First time using Wegovy or Mounjaro</div>
					</div>

					<div class="card" onclick="selectSwitching()">
						<div style="font-size: 32px; margin-bottom: 8px;">🔄</div>
						<div class="card-title">Switching providers</div>
						<div class="card-subtitle">Currently using medication elsewhere</div>
					</div>

					<button class="button button-secondary" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 3 -->
			<div id="screen-3" class="screen">
				<div class="container">
					<h1 class="heading">Where do you currently get your weight loss medication?</h1>

					<div class="radio-group">
						<div class="radio-item" onclick="selectProvider('Boots')">
							<input type="radio" name="provider" class="radio-input" value="Boots">
							<label>Boots</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Lloyds Pharmacy')">
							<input type="radio" name="provider" class="radio-input" value="Lloyds Pharmacy">
							<label>Lloyds Pharmacy</label>
						</div>
						<div class="radio-item" onclick="selectProvider('ASDA')">
							<input type="radio" name="provider" class="radio-input" value="ASDA">
							<label>ASDA</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Juniper')">
							<input type="radio" name="provider" class="radio-input" value="Juniper">
							<label>Juniper</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Numan')">
							<input type="radio" name="provider" class="radio-input" value="Numan">
							<label>Numan</label>
						</div>
						<div class="radio-item" onclick="selectProvider('MedExpress')">
							<input type="radio" name="provider" class="radio-input" value="MedExpress">
							<label>MedExpress</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Simple Online Pharmacy')">
							<input type="radio" name="provider" class="radio-input" value="Simple Online Pharmacy">
							<label>Simple Online Pharmacy</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Other')">
							<input type="radio" name="provider" class="radio-input" value="Other">
							<label>Other</label>
						</div>
						<div class="radio-item" onclick="selectProvider('Prefer not to say')">
							<input type="radio" name="provider" class="radio-input" value="Prefer not to say">
							<label>Prefer not to say</label>
						</div>
					</div>

					<button class="button button-secondary" style="margin-top: 24px;" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 3a -->
			<div id="screen-3a" class="screen">
				<div class="container">
					<h1 class="heading">Which medication are you currently taking?</h1>

					<div class="card" onclick="selectCurrentMedication('wegovy')">
						<div class="card-title">Wegovy</div>
						<div class="card-subtitle">Semaglutide injection</div>
					</div>

					<div class="card" onclick="selectCurrentMedication('mounjaro')">
						<div class="card-title">Mounjaro</div>
						<div class="card-subtitle">Tirzepatide injection</div>
					</div>

					<button class="button button-secondary" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 3b -->
			<div id="screen-3b" class="screen">
				<div class="container">
					<h1 class="heading" id="screen3bHeading">What dose are you currently taking?</h1>

					<div class="radio-group" id="doseRadioGroup"></div>

					<button class="button button-secondary" style="margin-top: 24px;" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 4 -->
			<div id="screen-4" class="screen">
				<div class="container">
					<h1 class="heading">How old are you?</h1>

					<div class="radio-group">
						<div class="radio-item" onclick="selectAge('under18')">
							<input type="radio" name="age" class="radio-input" value="under18">
							<label>Under 18</label>
						</div>
						<div class="radio-item" onclick="selectAge('18-74')">
							<input type="radio" name="age" class="radio-input" value="18-74">
							<label>18 to 74</label>
						</div>
						<div class="radio-item" onclick="selectAge('75+')">
							<input type="radio" name="age" class="radio-input" value="75+">
							<label>75 or over</label>
						</div>
					</div>

					<button class="button button-secondary" style="margin-top: 24px;" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 5 -->
			<div id="screen-5" class="screen">
				<div class="container">
					<h1 class="heading">Which ethnicity are you?</h1>
					<p class="subheading">Healthy BMI ranges are different according to your ethnic background. Our clinicians will carefully evaluate your BMI and complete medical history to determine the most appropriate treatment.</p>

					<div class="radio-group">
						<div class="radio-item" onclick="selectEthnicity('Asian or Asian British')">
							<input type="radio" name="ethnicity" class="radio-input" value="Asian or Asian British">
							<label>Asian or Asian British</label>
						</div>
						<div class="radio-item" onclick="selectEthnicity('Black (Caribbean, African)')">
							<input type="radio" name="ethnicity" class="radio-input" value="Black (Caribbean, African)">
							<label>Black (Caribbean, African)</label>
						</div>
						<div class="radio-item" onclick="selectEthnicity('Mixed ethnicities')">
							<input type="radio" name="ethnicity" class="radio-input" value="Mixed ethnicities">
							<label>Mixed ethnicities</label>
						</div>
						<div class="radio-item" onclick="selectEthnicity('Other ethnic group')">
							<input type="radio" name="ethnicity" class="radio-input" value="Other ethnic group">
							<label>Other ethnic group</label>
						</div>
						<div class="radio-item" onclick="selectEthnicity('White')">
							<input type="radio" name="ethnicity" class="radio-input" value="White">
							<label>White</label>
						</div>
					</div>

					<button class="button button-secondary" style="margin-top: 24px;" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 6 -->
			<div id="screen-6" class="screen">
				<div class="container">
					<h1 class="heading">What sex were you assigned at birth?</h1>

					<div class="card" onclick="selectSex('male')">
						<div class="card-title">Male</div>
					</div>

					<div class="card" onclick="selectSex('female')">
						<div class="card-title">Female</div>
					</div>

					<button class="button button-secondary" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 6b -->
			<div id="screen-6b" class="screen">
				<div class="container">
					<h1 class="heading">A few important questions to keep you safe</h1>

					<div class="yes-no-group">
						<div class="yes-no-question">Are you currently pregnant?</div>
						<div class="yes-no-buttons">
							<button class="yes-no-button" id="pregnant-yes" onclick="setPregnant('yes')">Yes</button>
							<button class="yes-no-button" id="pregnant-no" onclick="setPregnant('no')">No</button>
						</div>
					</div>

					<div class="yes-no-group">
						<div class="yes-no-question">Are you currently breastfeeding?</div>
						<div class="yes-no-buttons">
							<button class="yes-no-button" id="breastfeeding-yes" onclick="setBreastfeeding('yes')">Yes</button>
							<button class="yes-no-button" id="breastfeeding-no" onclick="setBreastfeeding('no')">No</button>
						</div>
					</div>

					<div class="yes-no-group">
						<div class="yes-no-question">Are you trying to conceive?</div>
						<div class="yes-no-buttons">
							<button class="yes-no-button" id="conceive-yes" onclick="setConceive('yes')">Yes</button>
							<button class="yes-no-button" id="conceive-no" onclick="setConceive('no')">No</button>
						</div>
					</div>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" id="screen6bContinue" onclick="continueFrom6b()" disabled>Continue</button>
					</div>
				</div>
			</div>

			<!-- Screen 7 -->
			<div id="screen-7" class="screen">
				<div class="container">
					<h1 class="heading">What is your weight?</h1>

					<div class="toggle-group">
						<button class="toggle-button active" id="weight-kg-toggle" onclick="setWeightUnit('kg')">kg</button>
						<button class="toggle-button" id="weight-st-toggle" onclick="setWeightUnit('st')">st / lbs</button>
					</div>

					<div id="weightError" class="error-message" style="display: none;"></div>

					<div id="weightInputKg">
						<div class="input-group">
							<label class="label">Weight (kg)</label>
							<input type="number" class="input" id="weightKg" placeholder="Enter weight in kg" min="40" max="250">
						</div>
					</div>

					<div id="weightInputSt" style="display: none;">
						<div class="weight-inputs">
							<div class="input-group">
								<label class="label">Stone</label>
								<input type="number" class="input" id="weightStone" placeholder="st" min="6" max="40">
							</div>
							<div class="input-group">
								<label class="label">Pounds</label>
								<input type="number" class="input" id="weightPounds" placeholder="lbs" min="0" max="13">
							</div>
						</div>
					</div>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" onclick="saveWeight()">Next</button>
					</div>
				</div>
			</div>

			<!-- Screen 8 -->
			<div id="screen-8" class="screen">
				<div class="container">
					<h1 class="heading">What is your height?</h1>

					<div class="toggle-group">
						<button class="toggle-button active" id="height-cm-toggle" onclick="setHeightUnit('cm')">cm</button>
						<button class="toggle-button" id="height-ft-toggle" onclick="setHeightUnit('ft')">ft / in</button>
					</div>

					<div id="heightError" class="error-message" style="display: none;"></div>

					<div id="heightInputCm">
						<div class="input-group">
							<label class="label">Height (cm)</label>
							<input type="number" class="input" id="heightCm" placeholder="Enter height in cm" min="120" max="230">
						</div>
					</div>

					<div id="heightInputFt" style="display: none;">
						<div class="weight-inputs">
							<div class="input-group">
								<label class="label">Feet</label>
								<input type="number" class="input" id="heightFeet" placeholder="ft" min="4" max="7">
							</div>
							<div class="input-group">
								<label class="label">Inches</label>
								<input type="number" class="input" id="heightInches" placeholder="in" min="0" max="11">
							</div>
						</div>
					</div>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" onclick="saveHeight()">Next</button>
					</div>
				</div>
			</div>

			<!-- Screen 9 -->
			<div id="screen-9" class="screen">
				<div class="container">
					<h1 class="heading">Have you been diagnosed with diabetes?</h1>

					<div class="radio-group">
						<div class="radio-item" onclick="selectDiabetes('medication')">
							<input type="radio" name="diabetes" class="radio-input" value="medication">
							<label>I have diabetes and take medication for it</label>
						</div>
						<div class="radio-item" onclick="selectDiabetes('diet-controlled')">
							<input type="radio" name="diabetes" class="radio-input" value="diet-controlled">
							<label>I have diabetes and it's diet-controlled</label>
						</div>
						<div class="radio-item" onclick="selectDiabetes('family-history')">
							<input type="radio" name="diabetes" class="radio-input" value="family-history">
							<label>No, but there is history of diabetes in my family</label>
						</div>
						<div class="radio-item" onclick="selectDiabetes('pre-diabetes')">
							<input type="radio" name="diabetes" class="radio-input" value="pre-diabetes">
							<label>I have pre-diabetes</label>
						</div>
						<div class="radio-item" onclick="selectDiabetes('no')">
							<input type="radio" name="diabetes" class="radio-input" value="no">
							<label>I don't have diabetes</label>
						</div>
					</div>

					<button class="button button-secondary" style="margin-top: 24px;" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 10 -->
			<div id="screen-10" class="screen">
				<div class="container">
					<h1 class="heading">Do any of the following statements apply to you?</h1>
					<p class="subheading">Select all that apply</p>

					<div id="screen10Error" class="error-message" style="display: none;"></div>

					<div class="checkbox-group" id="conditions-checkbox-group">
						<div class="checkbox-item" onclick="toggleCondition('cancer')">
							<input type="checkbox" class="checkbox-input" id="condition-cancer" value="cancer">
							<label>I have or have had cancer</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('pancreatitis')">
							<input type="checkbox" class="checkbox-input" id="condition-pancreatitis" value="pancreatitis">
							<label>I have or have had pancreatitis</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('eating-disorder')">
							<input type="checkbox" class="checkbox-input" id="condition-eating-disorder" value="eating-disorder">
							<label>I have or have had an eating disorder</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('bariatric')">
							<input type="checkbox" class="checkbox-input" id="condition-bariatric" value="bariatric">
							<label>I have had bariatric (weight loss) surgery</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('thyroid')">
							<input type="checkbox" class="checkbox-input" id="condition-thyroid" value="thyroid">
							<label>I have a thyroid condition</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('kidney')">
							<input type="checkbox" class="checkbox-input" id="condition-kidney" value="kidney">
							<label>I have kidney problems</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('liver')">
							<input type="checkbox" class="checkbox-input" id="condition-liver" value="liver">
							<label>I have liver problems</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('gallbladder')">
							<input type="checkbox" class="checkbox-input" id="condition-gallbladder" value="gallbladder">
							<label>I have gallbladder problems</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('heart')">
							<input type="checkbox" class="checkbox-input" id="condition-heart" value="heart">
							<label>I have heart problems</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('retinopathy')">
							<input type="checkbox" class="checkbox-input" id="condition-retinopathy" value="retinopathy">
							<label>I have diabetic retinopathy</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('gastroparesis')">
							<input type="checkbox" class="checkbox-input" id="condition-gastroparesis" value="gastroparesis">
							<label>I have gastroparesis</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('medullary')">
							<input type="checkbox" class="checkbox-input" id="condition-medullary" value="medullary">
							<label>I have a family history of medullary thyroid carcinoma</label>
						</div>
						<div class="checkbox-item" onclick="toggleCondition('none')">
							<input type="checkbox" class="checkbox-input" id="condition-none" value="none">
							<label><strong>None of these apply</strong></label>
						</div>
					</div>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" onclick="continueFromScreen10()">Next</button>
					</div>
				</div>
			</div>

			<!-- Screen 10a -->
			<div id="screen-10a" class="screen">
				<div class="container">
					<h1 class="heading">Was your bariatric operation in the last 6 months?</h1>

					<div class="card" onclick="selectBariatricRecent('yes')">
						<div class="card-title">Yes</div>
					</div>

					<div class="card" onclick="selectBariatricRecent('no')">
						<div class="card-title">No</div>
					</div>

					<button class="button button-secondary" onclick="goBack()">Back</button>
				</div>
			</div>

			<!-- Screen 10b -->
			<div id="screen-10b" class="screen">
				<div class="container">
					<h1 class="heading">Please tell us further details:</h1>

					<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
						<ul style="list-style: decimal; padding-left: 24px; line-height: 2;">
							<li>What type of bariatric surgery did you have?</li>
							<li>When did you have the surgery?</li>
							<li>What was your weight before surgery?</li>
							<li>What is your current weight?</li>
							<li>Have you had any complications from the surgery?</li>
							<li>Why are you considering weight loss medication?</li>
						</ul>
					</div>

					<textarea class="textarea" id="bariatricDetails" placeholder="Please provide details for the questions above..."></textarea>

					<div class="button-group">
						<button class="button button-secondary" onclick="goBack()">Back</button>
						<button class="button button-primary" onclick="continueFromScreen10b()">Next</button>
					</div>
				</div>
			</div>

			<!-- SCREENS_BLOCK_D: screens 11, 11a, 12, 12a — added in phase 3a-4 -->
			<!-- SCREENS_BLOCK_E: screens 13, 13-weight, 14, 14a, 15, 15a, 15b — added in phase 3a-5 -->
			<!-- SCREENS_BLOCK_F: screens 16, 17, 18, 19, 20 — added in phase 3a-6 -->
			<!-- SCREENS_BLOCK_G: screen 21 + ineligible — added in phase 3b -->

		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( AT_HEALTH_EC_SHORTCODE, 'at_health_ec_render_shortcode' );
