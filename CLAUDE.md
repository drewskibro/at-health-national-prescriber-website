# AT Health — National Prescriber Website

## Project Overview
Weight loss website for AT Health, built as a WordPress theme with ACF Pro.
Hosted on Kinsta. Deployed via GitHub Actions (SCP).

## Theme Architecture
- **Theme directory:** `at-health-theme/`
- **Prefix:** `ah_` (functions, fields, CSS classes)
- **ACF field key pattern:** `field_ah_[context]_[name]`
- **CSS class prefix per page:** `hp-` (home), `mj-` (mounjaro), `wg-` (wegovy), `tr-` (treatments), `el-` (eligibility), `sw-` (switching), `ab-` (about), `ct-` (contact), `cc-` (customer care), `hh-` (health hub), `ro-` (reorder), `tm-` (terms)

## Key Files
- `functions.php` — Theme setup, helpers, enqueue, includes
- `inc/acf-options.php` — Global settings pages (Branding, Contact, Compliance, Social, Nav)
- `inc/acf-fields.php` — All ACF field group definitions
- `header.php` / `footer.php` — Shared header and footer
- `page-templates/` — 12 custom page templates
- `template-parts/` — 4 shared section components
- `assets/css/globals.css` — CSS variables, base styles, shared components
- `assets/js/scroll-reveal.js` — Global scroll animations, FAQ accordion, count-up

## Critical Rules
1. **ACF helper functions use strict null checks:** `$value === null || $value === ''` — NEVER use `empty()` (breaks true_false fields where 0 = "No")
2. **ACF image fields:** Always `return_format => 'id'`, use `wp_get_attachment_image()` or `wp_get_attachment_url()`
3. **Escaping:** `esc_html()` for plain text, `wp_kses_post()` for HTML content, `esc_url()` for URLs, `esc_attr()` for attributes
4. **Gutenberg disabled** for all page templates (Classic Editor enforced)
5. **Cache busting:** `filemtime()` on `globals.css`, NOT `functions.php`
6. **Permalinks:** `/health-hub/%postname%/`

## Design System
- **Fonts:** DM Serif Display (headings), Inter (body)
- **Primary palette:** Purple `#a89dd6` / `#9b8fce` / `#8e88d0` / `#7d76ba`
- **Background:** Cream `#fdf8f3`, Lavender `#f7f4f9`, Dark `#0f1117`
- **Accent:** Indigo `#6366f1` (hero headlines)

## Deployment
- Push to `main` → GitHub Actions → SCP to Kinsta
- Required secrets: `KINSTA_SSH_HOST`, `KINSTA_SSH_USER`, `KINSTA_SSH_PASSWORD`, `KINSTA_SSH_PORT`
- No build tools — raw CSS/JS deployed as-is
- Never `git clone` on Kinsta

## Helper Functions
```php
ah_option('field_name', 'default')  // Global option field
ah_field('field_name', 'default')   // Page-level field
ah_company_name()                   // Returns 'AT Health'
ah_phone()                          // Returns phone number
ah_phone_link()                     // Returns tel: link
ah_email()                          // Returns email
ah_booking_url()                    // Returns eligibility/booking URL
ah_logo_url()                       // Returns logo URL (ACF > Customizer > SVG fallback)
```
