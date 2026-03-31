<instructions>
This file powers chat suggestion chips. Keep it focused and actionable.

# Be proactive
- Suggest ideas and things the user might want to add *soon*. 
- Important things the user might be overlooking (SEO, more features, bug fixes). 
- Look specifically for bugs and edge cases the user might be missing (e.g., what if no user has logged in).

# Rules
- Each task must be wrapped in a "<todo id="todo-id">" and "</todo>" tag pair.
- Inside each <todo> block:
  - First line: title (required)
  - Second line: description (optional)
- The id must be a short stable identifier for the task and must not change when you rewrite the title or description.
- You should proactively review this file after each response, even if the user did not explicitly ask, maintain it if there were meaningful changes (new requirement, task completion, reprioritization, or stale task cleanup).
- Think BIG: suggest ambitious features, UX improvements, technical enhancements, and creative possibilities.
- Balance quick wins with transformative ideas — include both incremental improvements and bold new features.
- Aim for 3-5 high-impact tasks that would genuinely excite the user.
- Tasks should be specific enough to act on, but visionary enough to inspire.
- Remove or rewrite stale tasks when completed, obsolete, or clearly lower-priority than current work.
- Re-rank by impact and user value, not just urgency.
- Draw inspiration from the project's existing features — what would make them 10x better?
- Don't be afraid to suggest features the user hasn't explicitly mentioned.
</instructions>

<todo id="hero-branded-photos">
Hero section — branded photography
Swap Unsplash placeholder photos for real AT Health branded photography once available. Layout is dialled in.
</todo>

<todo id="treatment-cards-photography">
Treatment Solutions card photography upgrade
Replace glassmorphic overlays with clean cards that lead with strong editorial photography and clear typography.
</todo>

<todo id="typography-scale">
Lock down typography hierarchy
Create one consistent section header pattern (eyebrow + heading + subline) and apply it uniformly across all sections. Strict type scale.
</todo>

<todo id="mobile-responsive-audit">
Mobile responsive polish pass
Audit all sections on small screens — hero collage, treatment grid aspect ratios, journey timeline layout, calculator form/results stacking.
</todo>

<todo id="faq-real-pricing">
FAQ pricing accuracy check
Verify the £149/month starting price in the FAQ is accurate — update if AT Health confirms different pricing tiers.
</todo>

<todo id="inner-pages-cleanup">
Apply premium design system to remaining inner pages
Reorder and terms pages still need the cream/lavender/dark-CTA uplift. About, eligibility, customer-care, health-hub, and contact are now done.
</todo>
