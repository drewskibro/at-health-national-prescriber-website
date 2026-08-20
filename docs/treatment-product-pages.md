# Treatment product pages — Mounjaro, Wegovy, Wegovy Tablets

How three prescription weight-loss treatments are presented and sold on one WordPress
site, and the conventions that let a fourth be added in an afternoon.

Written as a transferable pattern: the parts specific to Together Clinic are marked,
everything else is reusable on any multi-product clinical site.

---

## 1. The three products

| | Mounjaro | Wegovy | Wegovy Tablets |
|---|---|---|---|
| Molecule | Tirzepatide | Semaglutide | Semaglutide (oral) |
| Form | Once-weekly injection | Once-weekly injection | **Once-daily tablet** |
| Doses | 2.5 / 5 / 7.5 / 10 / 12.5 / 15 mg | 0.25 / 0.5 / 1 / 1.7 / 2.4 mg | 1.5 / 4 / 9 / 25 mg |
| Headline result | Up to 22.5% | Up to 20.7% | Up to 16.6% |
| Page slug | `/mounjaro/` | `/wegovy/` | `/wegovy-tablets/` |
| CSS prefix | `mj-` | `wg-` | `wt-` |
| ACF group | D1 / D2 | E1 / E2 | E3 / E4 |
| SKU prefix | `MJ-` | `WG-` | `WGT-` |

The third product is the interesting one: **same molecule as Wegovy, different form,
different titration, different price ladder.** That single fact drives most of the
design decisions below.

---

## 2. One page skeleton, three instances

Every treatment page is the same ordered set of sections. A patient who has read one
knows how to read the others, and a developer who has edited one can edit any.

```
Breadcrumb  (Home / Treatments / <product>)
Hero        two columns — copy + benefits left, image + price card right
[Bridge]    optional product-specific section  (tablets: "same active ingredient")
Dosing      numbered ladder, one step per dose strength
FAQ         accordion (shared JS, shared markup)
Dark CTA    closing call to action + trust row
```

Only the **bridge** section varies. Wegovy Tablets adds one, because its single
strongest selling point — *same proven medicine, no needle* — needs stating plainly
before the reader reaches dosing. The injection pages don't need it and don't have it.
Resist adding per-page sections beyond this: divergence in section order is what makes
a set of pages stop feeling like one product family.

### Files per page

```
page-templates/page-<slug>.php   the template   (Template Name: header drives the picker)
assets/css/<slug>.css            page-scoped styles, prefixed
inc/acf-fields.php               two field groups, registered in code
functions.php                    one line in the $page_assets enqueue map
```

Adding a treatment means creating two files and touching two. Nothing else.

---

## 3. The conventions that make this cheap to extend

### 3.1 Content defaults live in the template, not the database

Every field is read through a helper with a hard-coded fallback:

```php
<?php echo esc_html( ah_field( 'wt_title', 'Wegovy Tablets' ) ); ?>
```

So a page renders complete and correct the moment its template is assigned — before
anyone opens the ACF editor. Content editors override what they want to change and
ignore the rest. This removes the "new page looks broken until someone fills in
twenty fields" problem entirely, and it means the page is reviewable in a staging
environment with zero content entry.

The rule: **the default in the template is the real copy, not a placeholder.** If the
default is `Lorem ipsum` or `from £XX`, you have moved the work rather than removed it.

### 3.2 ACF field groups are registered in PHP, never drawn in the admin UI

```php
acf_add_local_field_group( [
    'key'      => 'group_ah_e3_wegovy_tablets',
    'title'    => 'E3 — Wegovy Tablets: All Fields',
    'fields'   => [ /* ... */ ],
    'location' => [ [ [ 'param' => 'page_template', 'operator' => '==',
                        'value' => 'page-templates/page-wegovy-tablets.php' ] ] ],
] );
```

Field groups built in the WordPress admin live in the **database**, so they do not
travel with a deploy — the template arrives on production and the editor has no fields.
Registering them in code on `acf/init` makes them part of the theme: they ship with the
files, appear automatically, and are reviewable in a pull request like any other code.

Groups are numbered (`D1/D2`, `E1/E2`, `E3/E4`) so the admin sidebar sorts into product
order rather than alphabetical soup, and split into "All Fields" (hero) and
"Dosing & FAQ" (the long repeaters) so neither box becomes unusable.

### 3.3 CSS is prefixed per page, structure is shared

Layout comes from utility classes shared across the site; only genuinely page-specific
rules get a file, and every selector in it carries the page prefix (`.wt-dosing-step`).
Two consequences: no page can break another, and deleting a product means deleting one
CSS file with confidence that nothing else referenced it.

### 3.4 Repeaters carry their own presentation data

The tablets dose repeater gained a `price` subfield the injection pages don't have,
because oral pricing differs per strength and patients ask for it on the page. The
template renders the price chip only when the field is populated:

```php
<?php if ( ! empty( $dose['price'] ) ) : ?>
  <span class="...">&pound;<?php echo esc_html( $dose['price'] ); ?> · 30 days</span>
<?php endif; ?>
```

Same repeater shape, one optional column. Cheaper than a second repeater type, and the
injection pages are unaffected.

---

## 4. Connecting pages to commerce — where the real risk lives

A marketing page that is wrong costs you a conversion. A **dose ladder** that is wrong
gets a patient the wrong medicine. The two halves of this system deserve very different
levels of paranoia.

### 4.1 Single source of truth for doses

```php
const LADDERS = [
    'wegovy'         => [ '0.25mg', '0.5mg', '1mg', '1.7mg', '2.4mg' ],
    'mounjaro'       => [ '2.5mg', '5mg', '7.5mg', '10mg', '12.5mg', '15mg' ],
    'wegovy-tablets' => [ '1.5mg', '4mg', '9mg', '25mg' ],
];
```

One constant drives the eligibility checker's dose options, the ±1 reorder escalation
gate, the switching matrix and the prescriber's proposed starting dose. The page
templates deliberately do **not** read from it — page copy is marketing content with its
own editorial cadence, and coupling the two would mean a copy tweak could alter clinical
logic. They are checked against each other by review, not by code.

### 4.2 The molecule-collision guard

This is the single most important lesson from adding the third product.

Wegovy Tablets and Wegovy share an active ingredient and a brand name. Any normaliser
doing **substring** matching — `strpos($treatment, 'wegovy') !== false` — will classify
`wegovy-tablets` as `wegovy` and hand it the injection ladder: a 0.25mg starting dose
for a product whose lowest strength is 1.5mg, and a 2.4mg ceiling for a product that
goes to 25mg. Both directions are clinically wrong.

The fix is exact-match aliasing with a fail-safe pass-through:

```php
const TREATMENT_ALIASES = [
    'wegovy'      => 'wegovy',
    'mounjaro'    => 'mounjaro',
    'semaglutide' => 'wegovy',   // legacy payloads only (injection era)
    'tirzepatide' => 'mounjaro', // legacy payloads only (injection era)
];

// Exact match only. Unknown values pass through unchanged and fail safe
// downstream: ladder() returns [], get_variation_id() returns 0 — nothing
// can be sold or dose-laddered against the wrong product.
return $aliases[ $treatment ] ?? $treatment;
```

`wegovy-tablets` is deliberately **not** an alias of `wegovy`. It is a distinct
treatment with its own identity end to end.

The same discipline extends to SKUs: oral strengths use a `WGT-` prefix, never `WG-`.
Had they shared a prefix, `WG-1.5` and `WG-4` would sit in the same namespace as the
injection SKUs and a fat-fingered product edit could silently repoint a variation at the
wrong form.

**Generalised rule:** when two products share a name, a molecule, or a numeric range,
identity matching must be exact and unknown values must fail closed. Fuzzy matching is
acceptable for search; never for dispensing.

### 4.3 Switching between forms is a supported journey

Patients moving from injections to tablets are a real and valuable segment, so it is
handled in three places rather than left implicit:

- an FAQ on the tablets page answering it directly,
- the eligibility checker asking current treatment and dose, and
- the switching matrix proposing a clinically appropriate tablet strength, flagged for
  prescriber review rather than auto-approved.

The system **proposes and flags; it never decides.** Every dose suggestion is a
recommendation on the prescriber's screen.

---

## 5. Site integration — don't orphan the page

A new product page that only exists at its own URL is invisible. Each treatment appears
in four places, and all four are updated in the same commit that creates the page:

1. **Header dropdown** — desktop and mobile navigation
2. **Footer** — treatments column
3. **Treatments index** — a card in the grid, with badge, headline stat, price and two
   CTAs (*Start Journey* → eligibility checker, *Learn More* → the page)
4. **Comparison table** — a column

The comparison table earned a new row when tablets arrived: **"How it's taken"**
(once-weekly injection vs once-daily tablet). A comparison table that omits the
differentiating attribute of your newest product is worse than no table — it implicitly
tells the reader the products are interchangeable.

Links resolve by slug (`get_page_by_path( 'wegovy-tablets' )`) rather than hard-coded
IDs, so the same code works across staging and production without a content migration.

---

## 6. Regulated-copy conventions (UK pharmacy)

Applies to any prescription-medicine site; adjust to local regulator.

- **Never assert an approval a product doesn't hold.** The injection pages carry FDA and
  MHRA badges; the tablets page carries **MHRA only**. The badge row is per-page data,
  not a shared partial, precisely so this cannot be inherited by accident.
- **Cite the trial behind a headline number.** "Up to 16.6% of body weight" is
  accompanied by the study, dose, duration and estimand (OASIS-4, oral semaglutide
  25 mg, 64 weeks, trial-product estimand). Marketing rounds numbers; regulators ask
  where they came from.
- **Don't invent a schedule you weren't given.** The tablets ladder labels read
  *Starting Dose / Step 2 / Step 3 / Maximum Dose* rather than *Month 2 / Month 3*,
  because the client's source copy specified strengths but not durations. Vague and
  true beats specific and unverified.
- **Keep dosing framed as clinician-led.** Every dosing section closes with a
  personalised-dosing note: the prescriber sets the dose, not the page.
- Copy is British English throughout.

---

## 7. Adding a fourth treatment — the checklist

1. Copy the nearest existing template to `page-templates/page-<slug>.php`; update the
   `Template Name` header, breadcrumb, prefix and every default string.
2. Add two ACF groups in `inc/acf-fields.php`, located to the new template.
3. Create `assets/css/<slug>.css`; add one line to the `$page_assets` map in
   `functions.php`.
4. Add the ladder to `TC_Dose_Ladder::LADDERS` and the SKUs to
   `expected_sku_map()` — **with a distinct SKU prefix.**
5. Confirm the treatment identifier is *not* a substring collision with an existing one;
   if the names overlap, verify the normaliser still matches exactly.
6. Wire nav, footer, treatments card and comparison column.
7. Create the WooCommerce products/variations for each strength.
8. In wp-admin: create the page, set the slug to match the nav links, assign the
   template, publish.
9. Walk the full patient journey end to end — checker → dose selection → payment →
   confirmation — and confirm the correct ladder and prices appear at every step.

Steps 1–6 are one commit. Steps 7–9 are the parts that need a human on the live site,
and step 9 is the one that actually proves it works.

---

## 8. What we'd keep, and what we'd do differently

**Keep:** defaults-in-template, ACF-in-code, per-page CSS prefixes, one ladder constant,
exact-match identity, updating all four navigation surfaces in the same commit as the
page.

**Do differently next time:** define the treatment identifier and SKU prefix *before*
writing the first product page. The molecule-collision risk was found and fixed while
adding the third product — it would have cost nothing to prevent at the first, and the
substring normaliser it replaced had been quietly correct only because no two products
had yet shared a name.
