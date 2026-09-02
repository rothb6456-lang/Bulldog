# Decisions Log

Version: v1.0    
Last Updated: 2026-08-18    
Status: Active    
Document Type: Decisions / Design Log    
Priority: Medium

## Purpose

This file records meaningful decisions made during the static prototype phase.

It should help preserve reasoning so that future contributors, Codex sessions, or implementation planning discussions understand why a decision was made.

---

## How To Use This File

Capture decisions when they affect:

- structure  
- scope  
- styling direction  
- page priorities  
- prototype constraints  
- asset use  
- implementation handoff readiness

Each decision should include:  
- what was decided  
- why it was decided  
- what impact it has

---

## Decision Log

### Decision 001  
**Topic:** Use a static prototype before Laravel implementation    
**Decision:** Prioritize a desktop-first static HTML/CSS/JS prototype before backend implementation work.    
**Why:** This reduces ambiguity, improves review quality, and creates a clearer visual/UX direction before development complexity increases.    
**Impact:** The current phase is prototype-first, not backend-first.

---

### Decision 002  
**Topic:** Make `team-detail.html` the hero page    
**Decision:** Treat `team-detail.html` as the most important working page in the prototype.    
**Why:** This page best demonstrates the value of the product by showing team structure, roster information, and operational actions in one place.    
**Impact:** Review quality will be heavily influenced by how strong this page feels.

---

### Decision 003  
**Topic:** Align the visual direction to bulldogstats.com    
**Decision:** Use bulldogstats.com as the primary brand and visual reference.    
**Why:** The prototype should feel like a credible Bulldog product extension rather than a generic admin template.    
**Impact:** Colors, tone, spacing, and overall UI confidence should align with Bulldog brand cues.

---

### Decision 004  
**Topic:** Prefer restrained branding over fake graphics    
**Decision:** Use real Bulldog assets when available, otherwise use text-based fallback branding rather than invented mascot graphics.    
**Why:** Fake or low-quality substitute graphics reduce credibility and make the prototype feel less trustworthy.    
**Impact:** Brand presence should come mostly from color, typography, structure, and careful asset use.

---

### Decision 005  
**Topic:** Keep the prototype desktop-first    
**Decision:** Optimize for desktop review rather than mobile-first responsiveness during this phase.    
**Why:** The product’s administrative workflows benefit most from desktop clarity, density, and hierarchy.    
**Impact:** Layout, tables, forms, and page composition should prioritize desktop usability.

---

### Decision 006  
**Topic:** Keep JavaScript minimal in the prototype    
**Decision:** Use only lightweight JavaScript needed to support basic static interactions or polish.    
**Why:** The goal is visual/UX validation, not full client logic.    
**Impact:** Most prototype value should come from HTML structure, CSS styling, and page composition.

---

### Decision 007  
**Topic:** Keep active guidance near the root    
**Decision:** Place the most important prototype guidance docs at the root of `Statbook-Data-Architecture`.    
**Why:** This improves discoverability for both humans and machine retrieval.    
**Impact:** Root-level governance docs should remain the fastest path to current prototype context.

---

### Decision 008  
**Topic:** Separate active docs from exports and archive materials    
**Decision:** Keep exports in `90_Exports/` and old or draft material in `99_Archive/`.    
**Why:** This reduces retrieval noise and keeps the active working set clean.    
**Impact:** Markdown remains the preferred active working format.

---

## Future Decisions To Record

Capture future decisions about:  
- component patterns  
- Blade/Laravel mapping  
- naming changes  
- page additions/removals  
- design system rules  
- asset approvals  
- review outcomes  