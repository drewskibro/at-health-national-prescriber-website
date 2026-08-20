# Together Clinic — project documentation

Read in this order:

1. **`BUILD-BRIEF-v3.md`** — the current plan. One plugin, one entry point, review-first / pay-after. Staged phases, one PR each, with paste-ready prompts. **Start here.**
2. **`eligibility-review.md`** — the code review both briefs are grounded in. Every phase's "read this first". Line references are from Eligibility v1.1.5 / Reorder v1.0.6; re-verify against current code before editing.
3. **`treatment-product-pages.md`** — how the three treatment pages (Mounjaro, Wegovy, Wegovy Tablets) are built and wired into commerce, written as a reusable pattern. Read before adding a fourth treatment.
4. **`BUILD-BRIEF-v2.md`** — superseded by v3. Kept as the record of the clinical decisions (dose ladders, ±1 rule, switching matrix — all still binding, restated in v3 §3) and of the authorise/capture payment model v3 replaced.

## Current state

- The plugins live in this repo under `wp-content/plugins/` and deploy to Kinsta automatically on merge to `main` (GitHub Actions; the run's *Verify deployment* step prints the live plugin versions).
- Complete: v2 Phase 1 (housekeeping, 1.1.6/1.0.7) → v3 Phases 1a+1b (prescriber review gate + pay-link lifecycle, 1.2.0/1.1.0) → v3 Phase 2 (dose module: canonical ladder, reorder ±1 gate, switching matrix, 1.3.0/1.2.0) → v3 Phase 3 (plugins merged: the reorder plugin is now the `reorder/` module inside Together Clinic Eligibility Checker **2.0.0**; the standalone plugin is a self-deactivating shell).
- Complete: **v3 Phase 2.5 — authorise-at-submission** (2.1.0–2.1.2). The owner's Stripe account is connected with manual capture enabled, so the patient's card is authorised (held, not charged) immediately after the assessment; prescriber approval captures, rejection voids the hold. Two status allow-lists must both be filtered for a custom status — `woocommerce_valid_order_statuses_for_payment` (payability) *and* `..._for_payment_complete` (finalisation); missing the second silently skipped finalisation and left a "Pay" button on the thank-you page (fixed in 2.1.2). The emailed pay link remains as the fallback lane if a capture fails.
- Complete: **patient ID capture** (2.2.0, `class-tc-secure-docs.php`). Fail-closed secure storage per `CLAUDE.md` — no fallback location exists in the code. Documents are stored outside the web root, served only through a gated `admin-post.php` endpoint (nonce + `manage_woocommerce` + realpath containment), audit-logged on view, opaque 48-char filenames, filename-only in order meta. Storage lives at `/www/athealthweightloss_401/additional/secure-uploads/`, wired up by `TC_SECURE_DOC_DIR` in `wp-config.php`. Verified empirically: a planted file in that directory returns nginx 404 over HTTPS, and an uploaded document does not appear in the Media Library. The old `/upload-your-id/` page (standard media uploader → public Media Library) is retired.
- Complete: **Wegovy Tablets** (oral semaglutide) as a third treatment — see `treatment-product-pages.md`.
- Next up: v3 Phase 4 (screen-0 router); automated ID verification (Stripe Identity, provider-agnostic layer); Royal Mail Click & Drop (native WooCommerce integration — no development required; imports orders at `processing`, i.e. only post-approval); document retention/auto-deletion (`CLAUDE.md` §2 — currently a document is removed on replacement but not on order deletion).
- Before launch: switch Stripe out of test mode.

## Working rules

- One phase per session, one PR per phase, in v3's order.
- British English in all patient-facing copy.
- Bump the plugin version on every code PR (it is the cache-bust — load-bearing).
- Order meta via WC_Order CRUD only (HPOS-safe).
- After every merge, check the deploy run output to confirm the new version is live.
