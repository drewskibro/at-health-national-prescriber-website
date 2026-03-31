<?php
/**
 * ACF Field Group Definitions for AT Health
 *
 * Field series organisation:
 *   A1-A9:  Global Options (branding, contact, compliance, social, navigation)
 *   B1-B13: Home Page sections
 *   C1-C3:  Blog Post fields
 *   D1-D11: Mounjaro page
 *   E1-E11: Wegovy page
 *   F1-F8:  Treatments page
 *   G1-G8:  Eligibility page
 *   H1-H8:  Switching Providers page
 *   I1-I6:  About page
 *   J1-J5:  Contact page
 *   K1-K5:  Customer Care page
 *   L1-L5:  Health Hub page
 *   M1-M5:  Reorder page
 *   N1-N3:  Terms page
 *
 * All field keys use the pattern: field_ah_[context]_[name]
 * All field names match the ah_field() / ah_option() calls in templates.
 *
 * IMPORTANT: Image fields must use return_format => 'id', never 'url'.
 * IMPORTANT: Never use empty() to check ACF values — use === null || === ''.
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
}

// ═══════════════════════════════════════════════
// A-SERIES: GLOBAL OPTIONS
// ═══════════════════════════════════════════════

// A1: Branding
acf_add_local_field_group( array(
    'key'      => 'group_ah_a1_branding',
    'title'    => 'A1 — Branding',
    'fields'   => array(
        array( 'key' => 'field_ah_site_logo', 'label' => 'Site Logo', 'name' => 'site_logo', 'type' => 'image', 'return_format' => 'id', 'instructions' => 'Upload the AT Health logo. Recommended: SVG or PNG with transparency.' ),
        array( 'key' => 'field_ah_company_name', 'label' => 'Company Name', 'name' => 'company_name', 'type' => 'text', 'default_value' => 'AT Health' ),
        array( 'key' => 'field_ah_company_legal_name', 'label' => 'Company Legal Name', 'name' => 'company_legal_name', 'type' => 'text', 'default_value' => 'AT Health Ltd' ),
        array( 'key' => 'field_ah_company_registration', 'label' => 'Company Registration Text', 'name' => 'company_registration', 'type' => 'text', 'default_value' => 'Company registered in England & Wales.' ),
        array( 'key' => 'field_ah_footer_tagline', 'label' => 'Footer Tagline', 'name' => 'footer_tagline', 'type' => 'text', 'default_value' => 'Medical Weight Loss, Delivered' ),
    ),
    'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ah-settings-branding' ) ) ),
) );

// A2: Contact & Details
acf_add_local_field_group( array(
    'key'      => 'group_ah_a2_contact',
    'title'    => 'A2 — Contact & Details',
    'fields'   => array(
        array( 'key' => 'field_ah_phone_number', 'label' => 'Phone Number', 'name' => 'phone_number', 'type' => 'text', 'default_value' => '0161 336 2548' ),
        array( 'key' => 'field_ah_phone_hours', 'label' => 'Phone Hours', 'name' => 'phone_hours', 'type' => 'text', 'default_value' => 'Mon–Fri, 9am–6pm' ),
        array( 'key' => 'field_ah_email_address', 'label' => 'Email Address', 'name' => 'email_address', 'type' => 'email', 'default_value' => 'ahmed@at-health.co.uk' ),
        array( 'key' => 'field_ah_email_response_time', 'label' => 'Email Response Time', 'name' => 'email_response_time', 'type' => 'text', 'default_value' => 'Reply within 4 hours' ),
        array( 'key' => 'field_ah_eligibility_url', 'label' => 'Eligibility / Booking URL', 'name' => 'eligibility_url', 'type' => 'url', 'instructions' => 'The main CTA link used across the site.' ),
    ),
    'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ah-settings-contact' ) ) ),
) );

// A3: Compliance
acf_add_local_field_group( array(
    'key'      => 'group_ah_a3_compliance',
    'title'    => 'A3 — Registration & Compliance',
    'fields'   => array(
        array( 'key' => 'field_ah_gphc_number', 'label' => 'GPhC Registration Number', 'name' => 'gphc_number', 'type' => 'text', 'default_value' => '2081354' ),
        array( 'key' => 'field_ah_superintendent', 'label' => 'Superintendent Pharmacist', 'name' => 'superintendent', 'type' => 'text', 'default_value' => 'Ms. Simona Pantaziu' ),
        array( 'key' => 'field_ah_company_number', 'label' => 'Company Number', 'name' => 'company_number', 'type' => 'text', 'default_value' => '08563110' ),
        array( 'key' => 'field_ah_registered_name', 'label' => 'Registered Business Name', 'name' => 'registered_name', 'type' => 'text', 'default_value' => 'Prescription Point Ltd' ),
        array( 'key' => 'field_ah_registered_address', 'label' => 'Registered Address', 'name' => 'registered_address', 'type' => 'textarea', 'default_value' => '14-16 Ashton Road, Denton, Manchester M34 3EX', 'rows' => 3 ),
        array( 'key' => 'field_ah_trust_badge_1', 'label' => 'Trust Badge 1', 'name' => 'trust_badge_1', 'type' => 'text', 'default_value' => 'GPhC & MHRA Regulated' ),
        array( 'key' => 'field_ah_trust_badge_2', 'label' => 'Trust Badge 2', 'name' => 'trust_badge_2', 'type' => 'text', 'default_value' => '4.9/5 from 10,000+ patients' ),
        array( 'key' => 'field_ah_trust_badge_3', 'label' => 'Trust Badge 3', 'name' => 'trust_badge_3', 'type' => 'text', 'default_value' => '256-bit SSL Encrypted' ),
        array( 'key' => 'field_ah_trust_badge_4', 'label' => 'Trust Badge 4', 'name' => 'trust_badge_4', 'type' => 'text', 'default_value' => 'Tracked 48h Delivery' ),
    ),
    'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ah-settings-compliance' ) ) ),
) );

// A4: Social Media
acf_add_local_field_group( array(
    'key'      => 'group_ah_a4_social',
    'title'    => 'A4 — Social Media',
    'fields'   => array(
        array( 'key' => 'field_ah_facebook_url', 'label' => 'Facebook URL', 'name' => 'facebook_url', 'type' => 'url' ),
        array( 'key' => 'field_ah_instagram_url', 'label' => 'Instagram URL', 'name' => 'instagram_url', 'type' => 'url' ),
        array( 'key' => 'field_ah_twitter_url', 'label' => 'Twitter/X URL', 'name' => 'twitter_url', 'type' => 'url' ),
        array( 'key' => 'field_ah_linkedin_url', 'label' => 'LinkedIn URL', 'name' => 'linkedin_url', 'type' => 'url' ),
    ),
    'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ah-settings-social' ) ) ),
) );

// A5: Navigation & CTAs
acf_add_local_field_group( array(
    'key'      => 'group_ah_a5_navigation',
    'title'    => 'A5 — Navigation & CTAs',
    'fields'   => array(
        array( 'key' => 'field_ah_top_banner_text', 'label' => 'Top Banner Text', 'name' => 'top_banner_text', 'type' => 'text', 'default_value' => 'Switch to Wegovy and save up to 27%' ),
        array( 'key' => 'field_ah_top_banner_link_text', 'label' => 'Top Banner Link Text', 'name' => 'top_banner_link_text', 'type' => 'text', 'default_value' => 'Check eligibility' ),
        array( 'key' => 'field_ah_nav_cta_text', 'label' => 'Nav CTA Button Text', 'name' => 'nav_cta_text', 'type' => 'text', 'default_value' => 'Start Journey →' ),
        array( 'key' => 'field_ah_footer_cta_text', 'label' => 'Footer CTA Text', 'name' => 'footer_cta_text', 'type' => 'text', 'default_value' => 'Start Your Journey' ),
        array( 'key' => 'field_ah_privacy_url', 'label' => 'Privacy Policy URL', 'name' => 'privacy_url', 'type' => 'url' ),
        array( 'key' => 'field_ah_cookies_url', 'label' => 'Cookies Policy URL', 'name' => 'cookies_url', 'type' => 'url' ),
        array( 'key' => 'field_ah_accessibility_url', 'label' => 'Accessibility URL', 'name' => 'accessibility_url', 'type' => 'url' ),
    ),
    'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ah-settings-navigation' ) ) ),
) );

// ═══════════════════════════════════════════════
// B-SERIES: HOME PAGE
// ═══════════════════════════════════════════════

acf_add_local_field_group( array(
    'key'      => 'group_ah_b1_home_hero',
    'title'    => 'B1 — Home: Hero Section',
    'fields'   => array(
        array( 'key' => 'field_ah_hero_eyebrow', 'label' => 'Eyebrow Text', 'name' => 'hero_eyebrow', 'type' => 'text', 'default_value' => 'Clinically Proven Weight Loss' ),
        array( 'key' => 'field_ah_hero_title', 'label' => 'Headline (HTML allowed)', 'name' => 'hero_title', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'HTML allowed for colour spans.' ),
        array( 'key' => 'field_ah_hero_subtitle', 'label' => 'Subtitle', 'name' => 'hero_subtitle', 'type' => 'textarea', 'rows' => 3 ),
        array( 'key' => 'field_ah_hero_cta_text', 'label' => 'CTA Button Text', 'name' => 'hero_cta_text', 'type' => 'text', 'default_value' => 'Start Your Journey' ),
        array( 'key' => 'field_ah_hero_cta_url', 'label' => 'CTA Button URL', 'name' => 'hero_cta_url', 'type' => 'url', 'instructions' => 'Leave blank to use default eligibility URL.' ),
        array( 'key' => 'field_ah_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'id' ),
        array( 'key' => 'field_ah_hero_image_alt', 'label' => 'Hero Image Alt Text', 'name' => 'hero_image_alt', 'type' => 'text' ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-home.php' ) ) ),
) );

acf_add_local_field_group( array(
    'key'      => 'group_ah_b2_home_hiw',
    'title'    => 'B2 — Home: How It Works',
    'fields'   => array(
        array( 'key' => 'field_ah_hiw_eyebrow', 'label' => 'Eyebrow', 'name' => 'hiw_eyebrow', 'type' => 'text', 'default_value' => 'Proven Process' ),
        array( 'key' => 'field_ah_hiw_title', 'label' => 'Title (HTML allowed)', 'name' => 'hiw_title', 'type' => 'textarea', 'rows' => 2 ),
        array( 'key' => 'field_ah_hiw_subtitle', 'label' => 'Subtitle', 'name' => 'hiw_subtitle', 'type' => 'textarea', 'rows' => 2 ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-home.php' ) ) ),
) );

acf_add_local_field_group( array(
    'key'      => 'group_ah_b3_home_testimonials',
    'title'    => 'B3 — Home: Testimonials',
    'fields'   => array(
        array( 'key' => 'field_ah_testimonials_badge', 'label' => 'Trust Badge Text', 'name' => 'testimonials_badge', 'type' => 'text', 'default_value' => 'Rated Excellent 4.9/5 by 10,000+ patients' ),
        array( 'key' => 'field_ah_testimonials_title', 'label' => 'Section Title', 'name' => 'testimonials_title', 'type' => 'text', 'default_value' => 'Life-Changing Results' ),
        array(
            'key'        => 'field_ah_testimonials_items',
            'label'      => 'Testimonials',
            'name'       => 'testimonials_items',
            'type'       => 'repeater',
            'min'        => 0,
            'max'        => 8,
            'layout'     => 'block',
            'sub_fields' => array(
                array( 'key' => 'field_ah_testimonial_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
                array( 'key' => 'field_ah_testimonial_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text', 'default_value' => 'Verified Patient' ),
                array( 'key' => 'field_ah_testimonial_text', 'label' => 'Testimonial Text', 'name' => 'text', 'type' => 'textarea', 'rows' => 3 ),
            ),
        ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-home.php' ) ) ),
) );

acf_add_local_field_group( array(
    'key'      => 'group_ah_b4_home_faq',
    'title'    => 'B4 — Home: FAQ',
    'fields'   => array(
        array( 'key' => 'field_ah_faq_eyebrow', 'label' => 'Eyebrow', 'name' => 'faq_eyebrow', 'type' => 'text', 'default_value' => 'Common Questions' ),
        array( 'key' => 'field_ah_faq_title', 'label' => 'Title', 'name' => 'faq_title', 'type' => 'text', 'default_value' => "Got Questions? We've Got Answers" ),
        array(
            'key'        => 'field_ah_faq_items',
            'label'      => 'FAQ Items',
            'name'       => 'faq_items',
            'type'       => 'repeater',
            'min'        => 0,
            'max'        => 12,
            'layout'     => 'block',
            'sub_fields' => array(
                array( 'key' => 'field_ah_faq_question', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ),
                array( 'key' => 'field_ah_faq_answer', 'label' => 'Answer', 'name' => 'answer', 'type' => 'textarea', 'rows' => 4 ),
            ),
        ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-home.php' ) ) ),
) );

// ═══════════════════════════════════════════════
// D-SERIES: MOUNJARO PAGE
// ═══════════════════════════════════════════════

acf_add_local_field_group( array(
    'key'      => 'group_ah_d1_mounjaro',
    'title'    => 'D1 — Mounjaro: All Fields',
    'fields'   => array(
        array( 'key' => 'field_ah_mj_eyebrow', 'label' => 'Eyebrow', 'name' => 'mj_eyebrow', 'type' => 'text', 'default_value' => 'Tirzepatide · Once Weekly' ),
        array( 'key' => 'field_ah_mj_rating_text', 'label' => 'Rating Text', 'name' => 'mj_rating_text', 'type' => 'text', 'default_value' => '4.9 · 2,847 reviews' ),
        array( 'key' => 'field_ah_mj_title', 'label' => 'Page Title', 'name' => 'mj_title', 'type' => 'text', 'default_value' => 'Mounjaro' ),
        array( 'key' => 'field_ah_mj_description', 'label' => 'Description', 'name' => 'mj_description', 'type' => 'textarea', 'rows' => 4 ),
        array( 'key' => 'field_ah_mj_product_image', 'label' => 'Product Image', 'name' => 'mj_product_image', 'type' => 'image', 'return_format' => 'id' ),
        array( 'key' => 'field_ah_mj_price', 'label' => 'Price (number only)', 'name' => 'mj_price', 'type' => 'text', 'default_value' => '199' ),
        array( 'key' => 'field_ah_mj_cta_text', 'label' => 'CTA Text', 'name' => 'mj_cta_text', 'type' => 'text', 'default_value' => 'Start Journey →' ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-mounjaro.php' ) ) ),
) );

// ═══════════════════════════════════════════════
// E-SERIES: WEGOVY PAGE
// ═══════════════════════════════════════════════

acf_add_local_field_group( array(
    'key'      => 'group_ah_e1_wegovy',
    'title'    => 'E1 — Wegovy: All Fields',
    'fields'   => array(
        array( 'key' => 'field_ah_wg_eyebrow', 'label' => 'Eyebrow', 'name' => 'wg_eyebrow', 'type' => 'text', 'default_value' => 'Semaglutide · Once Weekly' ),
        array( 'key' => 'field_ah_wg_rating_text', 'label' => 'Rating Text', 'name' => 'wg_rating_text', 'type' => 'text', 'default_value' => '4.9 · 3,124 reviews' ),
        array( 'key' => 'field_ah_wg_title', 'label' => 'Page Title', 'name' => 'wg_title', 'type' => 'text', 'default_value' => 'Wegovy' ),
        array( 'key' => 'field_ah_wg_description', 'label' => 'Description', 'name' => 'wg_description', 'type' => 'textarea', 'rows' => 4 ),
        array( 'key' => 'field_ah_wg_product_image', 'label' => 'Product Image', 'name' => 'wg_product_image', 'type' => 'image', 'return_format' => 'id' ),
        array( 'key' => 'field_ah_wg_price', 'label' => 'Price (number only)', 'name' => 'wg_price', 'type' => 'text', 'default_value' => '179' ),
        array( 'key' => 'field_ah_wg_cta_text', 'label' => 'CTA Text', 'name' => 'wg_cta_text', 'type' => 'text', 'default_value' => 'Start Journey →' ),
    ),
    'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/page-wegovy.php' ) ) ),
) );
