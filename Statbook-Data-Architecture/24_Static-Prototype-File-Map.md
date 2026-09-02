# `24_Static-Prototype-File-Map.md`

This file is meant to help Codex understand:
- what each file is for
- how pages connect
- which pages matter most
- what the user should feel on each page
- what level of visual attention each page deserves

---

```md
# Static Prototype File Map

Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Prototype File Map  
Priority: High  
Related Documents:
- `00_README-Statbook-Data-Architecture.md`
- `00_Prototype-Scope-and-UX-Goals.md`
- `00_Brand-and-Visual-Reference.md`
- `00_Prototype-Review-Checklist.md`
- `23_Static-Prototype-Prompt-Master.md`

## Purpose

This document maps the static Bulldog Statbook prototype files to their intended role in the product experience.

It exists to help Codex and contributors understand:

- what each page is supposed to accomplish
- how the pages link together
- which pages deserve the most design attention
- what kind of information hierarchy each page should support
- what tone and UX outcome each page should deliver

This file should be used during both:
- initial prototype generation
- refinement/polish passes

---

## Prototype Type

The current prototype is a:

# static multi-page desktop-first product prototype

It is intended to help evaluate:
- product hierarchy
- desktop usability
- page relationships
- visual tone
- brand alignment with bulldogstats.com

It is not intended to implement backend behavior.

---

## Prototype File List

The static prototype currently consists of these files:

1. `index.html`
2. `dashboard.html`
3. `teams.html`
4. `team-detail.html`
5. `create-team.html`
6. `player-identity.html`
7. `guardian-relationships.html`
8. `styles.css`
9. `app.js`

---

## Navigation Structure

The primary application navigation should support these top-level destinations:

- Dashboard
- Teams
- Player Identity
- Guardians

The most common path through the prototype should be:

1. `index.html`
2. `dashboard.html`
3. `teams.html`
4. `team-detail.html`

That is the core prototype journey.

---

## Page Priority Ranking

These pages are not equally important.

### Highest visual and UX priority
1. `team-detail.html`
2. `dashboard.html`
3. `index.html`

### Medium priority
4. `teams.html`
5. `create-team.html`

### Supporting priority
6. `player-identity.html`
7. `guardian-relationships.html`

### Shared system priority
8. `styles.css`
9. `app.js`

---

## File-by-File Intent

---

## 1. `index.html`

### Role
Prototype entry page / sign-in gateway.

### Purpose
This page should establish:
- first visual impression
- product identity
- confidence in the Bulldog brand connection
- transition into the app experience

### UX Goal
It should feel like:
- a real product entry point
- not a marketing landing page
- not a generic auth template

### Key content
- Bulldog Statbook product naming
- optional supporting line: Stats & Sports Innovation
- simple mock login form
- clear entry action into the prototype

### Visual priority
High

### Design notes
This page should borrow tone from bulldogstats.com:
- bold typography
- restrained color use
- strong brand block
- clean whitespace
- sharp action buttons

---

## 2. `dashboard.html`

### Role
Primary post-login landing page.

### Purpose
This page should provide:
- immediate orientation
- team context
- quick entry into the main work surfaces
- a sense of operational overview

### UX Goal
It should answer:
- Where am I?
- What can I do next?
- What team/work context matters most?

### Key content
- summary cards
- quick actions
- recent or active team preview
- navigation into teams and identity areas

### Visual priority
Very high

### Design notes
This page should feel:
- practical
- structured
- scannable
- credible
- more serious than flashy

Avoid:
- empty dashboard clichés
- fake analytics clutter
- decorative charts with no UX value

---

## 3. `teams.html`

### Role
Team list page.

### Purpose
This page should show:
- all teams where the user has a role
- team context at a glance
- a clear way to create a team
- a clear way to open team detail

### UX Goal
It should feel easy to scan and trustworthy.

### Key content
Each team entry should ideally show:
- team name
- sport
- season
- age group
- user role
- team status

### Visual priority
Medium-high

### Design notes
A clean table or structured card-list is appropriate.
This page should feel like a directory view, not a feature showcase.

---

## 4. `team-detail.html`

### Role
Primary working screen and most important page in the prototype.

### Purpose
This page should demonstrate the operational heart of Bulldog Statbook.

It must clearly communicate:
- team identity
- team context
- roster context
- role context
- information density
- work-surface confidence

### UX Goal
This should be the page where a reviewer says:
- “Yes, I can see how this product would actually be used.”

### Key content
- strong team header
- team summary band/cards
- overview section
- roster section
- roles section
- clear status and role signaling
- action buttons such as add player / edit team

### Visual priority
Highest

### Design notes
This is the most important page in the whole prototype.

It should:
- feel premium and product-grade
- be easy to scan on desktop
- have clear section hierarchy
- make roster and roles distinct
- feel like the operational center of the app

If one page gets the most refinement attention, it should be this one.

---

## 5. `create-team.html`

### Role
Static form mockup for team creation.

### Purpose
This page should test:
- form layout quality
- field hierarchy
- button treatment
- task flow confidence

### UX Goal
It should feel simple, clear, and product-grade.

### Key content
- sport
- team name
- season label
- age group
- city
- state/region
- cancel/create actions

### Visual priority
Medium

### Design notes
This page should not feel like a raw browser form.
It should feel like a real product workflow.

---

## 6. `player-identity.html`

### Role
Identity record page.

### Purpose
This page should communicate the distinction between:
- user account
- player identity
- claim status
- identity context

### UX Goal
It should feel like a structured record view, not a social profile.

### Key content
- player code
- claim status
- identity status
- primary sport
- linked account summary

### Visual priority
Supporting

### Design notes
This page matters because it reinforces one of the key product concepts:
`User` is not the same as `PlayerIdentity`.

The design should reflect seriousness and structure.

---

## 7. `guardian-relationships.html`

### Role
Guardian relationship records page.

### Purpose
This page should present:
- guardian-to-player relationships
- relationship type
- verification state
- primary guardian designation

### UX Goal
It should feel administrative, clear, and trustworthy.

### Key content
- player code
- relationship type
- verification status
- primary guardian yes/no

### Visual priority
Supporting

### Design notes
Keep this page clean and simple.
It is an important conceptual page, but not a visual hero page.

---

## 8. `styles.css`

### Role
Shared visual system for the static prototype.

### Purpose
This file should establish a coherent design system for:
- colors
- typography
- spacing
- layout
- cards
- tables
- badges
- buttons
- forms
- sidebar/navigation
- tabs

### UX Goal
The CSS should make the whole prototype feel like one product, not a set of disconnected pages.

### Priority
High

### Design notes
This is one of the most important files in the prototype.

It should:
- use CSS variables/tokens
- reflect bulldogstats.com brand direction
- support desktop-first layouts
- create strong consistency across all pages

---

## 9. `app.js`

### Role
Minimal shared behavior.

### Purpose
This file should only support lightweight UI interactions needed by the prototype.

### Appropriate uses
- tab switching on team-detail
- optional sidebar collapse/toggle on smaller screens
- minor interaction polish

### Not appropriate
- fake backend logic
- simulated data loading
- complex state management

### Priority
Low-medium

### Design notes
Keep JS minimal and clean.
The prototype is primarily about layout and UX, not interactivity depth.

---

## Page Relationship Map

### Core flow
`index.html`  
→ `dashboard.html`  
→ `teams.html`  
→ `team-detail.html`

### Secondary flow
`dashboard.html`  
→ `player-identity.html`

### Secondary flow
`dashboard.html`  
→ `guardian-relationships.html`

### Creation flow
`teams.html`  
→ `create-team.html`  
→ `team-detail.html`

---

## Desktop Experience Priorities

The prototype should optimize for desktop review first.

Desktop review should be strongest on:
- `dashboard.html`
- `teams.html`
- `team-detail.html`

Special attention should be paid to:
- sidebar proportion
- page header spacing
- table readability
- section hierarchy
- button alignment
- content density without clutter

---

## Visual Hierarchy Goals by Page

### index.html
Brand confidence first, form simplicity second.

### dashboard.html
Orientation first, actionability second.

### teams.html
Scanability first, navigation second.

### team-detail.html
Operational usability first, structure second, polish third.

### create-team.html
Clarity first, form rhythm second.

### player-identity.html
Record structure first, concept clarity second.

### guardian-relationships.html
Administrative clarity first, visual simplicity second.

---

## Brand Fit Reminder

All pages should feel like they belong to the Bulldog family and visually align with:

**https://www.bulldogstats.com**

The prototype should feel like:
- the product extension of the brand
- not a separate unrelated UI
- not a generic admin starter
- not a sports fan page

---

## Refinement Guidance

When refining the prototype, prioritize in this order:

1. `team-detail.html`
2. `styles.css`
3. `dashboard.html`
4. `index.html`
5. `teams.html`
6. `create-team.html`
7. supporting pages

---

## Success Condition

This file map is serving its purpose if Codex can use it to produce a prototype where:

- navigation feels seamless
- desktop hierarchy is strong
- team-detail feels like the main working screen
- all pages feel like one coherent product
- the prototype clearly belongs to the Bulldog brand
```

---

# My recommendation after this

You now have a very solid **prototype governance pack**.

The most useful files for Codex at this point are probably:

1. `00_README-Statbook-Data-Architecture.md`
2. `00_Prototype-Scope-and-UX-Goals.md`
3. `00_Brand-and-Visual-Reference.md`
4. `23_Static-Prototype-Prompt-Master.md`
5. `24_Static-Prototype-File-Map.md`
6. `00_Prototype-Review-Checklist.md`

That is enough to keep the prototype generation/refinement very disciplined.

---
