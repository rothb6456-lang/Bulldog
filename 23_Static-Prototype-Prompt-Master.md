Below is a clean draft for **`23_Static-Prototype-Prompt-Master.md`** you can drop into your project.

```md
# Static Prototype Prompt Master

Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Codex Prompt Library  
Priority: High  
Related Documents:
- `00_README-Statbook-Data-Architecture.md`
- `00_Prototype-Scope-and-UX-Goals.md`
- `00_Brand-and-Visual-Reference.md`
- `00_Prototype-Asset-Usage-Guide.md`
- `00_Prototype-Review-Checklist.md`

## Purpose

This document stores the current clean prompts for generating and refining the **Bulldog Statbook static prototype**.

Use this document when the project is in **Static Prototype Mode**.

This file is intended to:
- reduce prompt drift
- keep Codex aligned with the Bulldog brand
- separate prototype work from Laravel implementation work
- provide a repeatable prompt set for regeneration and refinement

---

## Current Mode

The current active mode is:

# Static Prototype Mode

This means Codex should optimize for:
- visual quality
- desktop UX clarity
- information hierarchy
- brand alignment with bulldogstats.com
- clean multi-page navigation
- realistic product feel

Codex should **not** optimize for:
- backend integration
- Laravel implementation
- API logic
- real auth
- production data persistence

---

## Primary Brand Reference

Use the current Bulldog website as the main visual and tonal reference:

**https://www.bulldogstats.com**

Key brand characteristics:
- strong wordmark/logo system
- dark/navy and Bulldog green palette
- bold, high-confidence typography
- structured grid composition
- serious sports-operations tone
- premium but restrained visual language

The static prototype should feel like the **product extension** of bulldogstats.com.

---

## Prompt 1 — Full One-Shot Static Prototype Generation

Use this prompt in a fresh Codex session when generating the full static prototype from scratch.

```text
Build a complete static front-end prototype for the Bulldog app experience as a multi-page desktop-first web application.

PRIMARY GOAL
Create a visually polished placeholder product prototype that I can open in browser and evaluate from a UX/UI perspective before full backend implementation.

IMPORTANT CONTEXT
This is NOT the marketing website.
This is the product/application experience.

Use the existing public website as the visual brand reference:
https://www.bulldogstats.com

Brand details:
- Brand name: Bulldog
- Supporting line: Stats & Sports Innovation
- The logo/wordmark is BULLDOG with the “O” replaced by a bulldog sketch/logo
- If no actual logo asset file is provided, do NOT invent a fake bulldog logo and do NOT use clip-art
- Instead, use a clean text-based brand treatment that feels consistent with the site, such as:
  BULLDOG
  Stats & Sports Innovation
- Product name inside the app should be:
  Bulldog Statbook

VISUAL DIRECTION
The app should feel like the product extension of bulldogstats.com, not a generic admin template.

Use bulldogstats.com as reference for:
- bold, high-confidence typography
- deep green + dark navy / near-black palette
- white / off-white surfaces
- clean spacing and premium restraint
- coach-built, sports-operations tone
- serious, modern, practical structure

The app should feel:
- serious
- modern
- analytical
- structured
- coach/admin oriented
- premium but restrained
- sports-specific without feeling like a sports media site or fan site

Do NOT:
- copy the marketing homepage layout directly
- make it retro or hockey-themed
- use generic startup gradients
- use neon colors
- use playful sports gimmicks
- use mascot clip art
- overload the pages with fake charts that do not help UX

DESKTOP EXPERIENCE PRIORITY
This prototype should primarily help evaluate the desktop experience.
Design desktop-first.
It should still collapse reasonably for tablet/mobile, but desktop quality is the priority.

TECHNICAL REQUIREMENTS
- Static site only
- Plain HTML, CSS, and minimal JavaScript only
- No frameworks required
- No backend required
- No build step required
- Must be easy to deploy to Cloudflare Pages
- Use fake placeholder/sample data only
- Keep code clean and easy to edit

OUTPUT FILES
Generate complete code for all of these files:

1. index.html
2. dashboard.html
3. teams.html
4. team-detail.html
5. create-team.html
6. player-identity.html
7. guardian-relationships.html
8. styles.css
9. app.js

Also provide:
10. a short README-style deployment note for Cloudflare Pages

INFORMATION ARCHITECTURE / PAGES

1. index.html
Purpose:
- login / entry page for the prototype
Requirements:
- strong branded entry experience
- show Bulldog Statbook name
- supporting brand line: Stats & Sports Innovation
- simple sign-in form mockup
- button/link to enter prototype
- should feel like product login, not marketing homepage

2. dashboard.html
Purpose:
- main landing screen after login
Requirements:
- app shell with sidebar and/or top header
- summary cards
- “My Teams” preview
- quick actions
- recent identity/team context
- strong page hierarchy
- feel like a serious operations dashboard

3. teams.html
Purpose:
- list all teams where the user has a role
Requirements:
- structured table or card-list view
- clear “Create Team” CTA
- show sample team rows with:
  - team name
  - sport
  - season
  - age group
  - role
  - status
- rows should link to team-detail.html

4. team-detail.html
Purpose:
- strongest/most important screen in the whole prototype
Requirements:
- strong team header
- team summary / key stats
- tabbed or sectioned content for:
  - Overview
  - Roster
  - Roles
- show fake roster entries with:
  - player code
  - claim status
  - jersey number
  - positions
- show fake team role entries with:
  - user/email
  - role type
  - status
- page should feel like the working center of the application
- make this the best-designed screen in the system

5. create-team.html
Purpose:
- form-style team creation flow
Requirements:
- polished form layout
- sample fields:
  - sport
  - team name
  - season label
  - age group
  - city
  - state/region
- clear primary and secondary actions
- should feel product-grade even though it is static

6. player-identity.html
Purpose:
- show a linked player identity record
Requirements:
- player code
- claim status
- linked account summary
- primary sport
- identity status
- should feel like an identity record page, not a profile fluff page

7. guardian-relationships.html
Purpose:
- show guardian-to-player relationship records
Requirements:
- structured list or table
- show sample rows with:
  - player code
  - relationship type
  - verification status
  - primary guardian yes/no
- clean and serious layout

UI / LAYOUT REQUIREMENTS

App shell:
- use a left sidebar for primary navigation
- optional top header inside main content
- sidebar should feel branded and product-grade
- sidebar branding should reference Bulldog / Stats & Sports Innovation
- active nav states should be visually clear

Navigation items:
- Dashboard
- Teams
- Player Identity
- Guardians

Design system requirements:
- use cards, tables, badges, section headers, page headers, and clean spacing
- badges should be visually distinct for:
  - Active
  - Pending
  - Claimed
  - Unclaimed
  - Team Admin
  - Coach
  - Viewer
- buttons should have clear primary vs secondary treatment
- tables should feel crisp and product-grade
- forms should feel intentionally styled
- typography should feel bold in headings and clean/readable in body text

BRAND / COLOR REQUIREMENTS

Use bulldogstats.com as the visual reference.

Suggested palette direction:
- deep navy / near-black for primary framing and strong text
- deep green as the primary action/accent color
- white and off-white for content surfaces
- cool neutral grays for borders and muted text
- subtle blue-green support tones only if needed

Use a design-token style CSS approach with variables.

Suggested CSS token direction (you may refine these slightly if needed):

:root {
  --bg: #f3f5f4;
  --surface: #ffffff;
  --surface-alt: #f7f8f8;

  --text: #0d1b26;
  --text-muted: #5f6d78;

  --border: #d7dede;
  --border-strong: #b9c4c4;

  --navy: #081722;
  --navy-2: #102533;

  --green: #007a43;
  --green-hover: #00663a;
  --green-soft: #e4f3eb;

  --blue-accent: #0f6f97;
  --blue-soft: #e7f2f7;

  --warning-soft: #fff2d9;
  --warning-text: #9a6700;

  --danger-soft: #fde8e8;
  --danger-text: #b42318;

  --shadow-sm: 0 1px 2px rgba(8, 23, 34, 0.06);
  --shadow-md: 0 8px 24px rgba(8, 23, 34, 0.08);

  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 20px;
}

TYPOGRAPHY DIRECTION
- Headings should feel bold and high-confidence, similar in spirit to bulldogstats.com
- Body text should be clean, readable, and product-oriented
- Use system fonts if needed, or a sensible web-safe stack
- Avoid decorative novelty fonts
- Emphasize hierarchy and scanability

INTERACTION REQUIREMENTS
- Minimal JS only
- Use app.js for simple behavior such as:
  - tab switching on team-detail.html
  - optional sidebar/mobile nav toggle if helpful
- No heavy interactivity needed
- No fake auth logic needed

CONTENT / COPY DIRECTION
Use product-oriented copy, not marketing fluff.
Examples of tone:
- Team operations overview
- Roster identity and role management
- Linked player record
- Guardian relationship records
- Active team context

Avoid overly generic copy like:
- Welcome to our awesome platform
- Manage everything in one place
unless it is made more specific and credible

RESPONSIVENESS
- Desktop-first
- Should still behave acceptably on tablet/mobile
- Sidebar may collapse or stack on smaller screens
- Tables may scroll horizontally if needed
- Maintain readability and structure

QUALITY BAR
The result should feel:
- more like real product software
- less like a starter template
- visually coherent across all pages
- aligned with bulldogstats.com brand identity
- polished enough for stakeholder review

DELIVERABLE FORMAT
Output the full code for every required file in clearly labeled sections, like:

--- index.html ---
[full code]

--- dashboard.html ---
[full code]

...etc.

Then finish with:
- a short explanation of the design decisions
- a short Cloudflare Pages deployment note

IMPORTANT FINAL REMINDER
This is a static UX prototype only.
Do not introduce Laravel, React, build tooling, or backend dependencies.
Keep everything simple, complete, and ready to drop into a folder and host on Cloudflare Pages.
```

---

## Prompt 2 — Full Prototype Refinement Pass

Use this after Codex generates the first full prototype and you want a more polished second pass.

```text
Refine the existing static Bulldog Statbook prototype to better match the visual identity and tone of https://www.bulldogstats.com

This is a refinement pass, not a rebuild from scratch.

PRIMARY GOAL
Take the current multi-page static prototype and make it feel less like a generic admin template and more like a serious product experience that belongs to the Bulldog brand system.

BRAND REFERENCE
Use bulldogstats.com as the visual and tonal reference.

Key observed brand traits:
- bold condensed headline style
- strong dark ink / navy typography
- Bulldog green used with confidence
- light neutral backgrounds
- clean structure and spacing
- editorial energy with product restraint
- sports-focused without becoming gimmicky or loud

IMPORTANT
Do NOT redesign this into a marketing site.
Do NOT copy the homepage layout directly.
Do NOT introduce decorative sports graphics just for style.
Do NOT make it retro or hockey-themed.

Instead:
Translate the Bulldog website brand language into a product/app UI system.

REFINEMENT PRIORITIES

1. BRAND COHERENCE
- Make the prototype feel visually related to bulldogstats.com
- Strengthen the brand block in the sidebar/header/login screen
- Use a cleaner, more intentional Bulldog Statbook identity treatment
- If no true logo asset exists in the prototype, keep it text-based and tasteful

2. TYPOGRAPHY
- Improve the heading system so it feels bolder and more brand-aligned
- Use strong, condensed or high-impact headline styling where appropriate
- Keep body copy readable and product-oriented
- Improve hierarchy between page titles, section headers, labels, and table text

3. COLOR SYSTEM
- Refine the palette to better align with bulldogstats.com:
  - deep ink / near-black navy
  - Bulldog green as primary action/accent
  - light warm/cool neutral surfaces
  - subtle support tones only when needed
- Reduce any color usage that feels too generic SaaS
- Ensure buttons, nav states, badges, and tabs feel intentional

4. APP SHELL
- Improve the sidebar so it feels more premium and brand-specific
- Improve the top page headers so they feel more composed and less template-like
- Tighten spacing, alignment, and visual rhythm throughout the shell

5. TEAM-DETAIL PAGE
- Make team-detail.html the strongest page in the product
- Improve the hierarchy of:
  - team header
  - summary cards
  - overview / roster / roles sections
- Make roster and roles feel like real working product surfaces
- Improve tab styling and section transitions
- Make the page feel like the operational center of the app

6. TABLES / LISTS / DATA UI
- Improve tables so they feel sharper and more product-grade
- Better row spacing, column hierarchy, hover states, and header styling
- Reduce any “default HTML table” feeling
- Make list rows and cards feel more intentional and less generic

7. BADGES / STATES
- Improve badge styling for:
  - Active
  - Pending
  - Claimed
  - Unclaimed
  - Team Admin
  - Coach
  - Viewer
- Make the state system more elegant and more obviously designed
- Avoid toy-like colors or overly bright chip styles

8. BUTTONS / FORMS
- Make buttons feel more brand-consistent and stronger visually
- Improve primary and secondary action contrast
- Improve form styling in create-team.html
- Make fields feel more considered and less boilerplate

9. COPY TONE
- Tighten copy where needed so it sounds like serious product software
- Reduce filler phrases
- Use more specific labels and product-like wording
- Keep tone aligned with “Stats & Sports Innovation” and “Built for the Next Play”

10. CLEANUP
- Remove anything that feels placeholder-ish in a cheap way
- Keep placeholder/sample data, but present it more credibly
- Improve consistency across all pages
- Make spacing and component treatment more systematic

TECHNICAL CONSTRAINTS
- Keep this a static site only
- Plain HTML, CSS, minimal JS only
- No frameworks
- No backend
- No build tools
- Keep it deployable to Cloudflare Pages
- Edit the existing files rather than introducing unnecessary complexity

FILES TO REFINE
- index.html
- dashboard.html
- teams.html
- team-detail.html
- create-team.html
- player-identity.html
- guardian-relationships.html
- styles.css
- app.js

SPECIFIC VISUAL TARGET
The result should feel like:
“Bulldog Statbook is the product/software extension of bulldogstats.com.”

It should feel:
- bold
- clear
- serious
- product-grade
- brand-connected
- desktop-strong
- ready for stakeholder review

OUTPUT FORMAT
Return the updated full code for each changed file, clearly labeled.

Also include:
1. a short summary of the design improvements made
2. a short note on which changes most improved the desktop experience
```

---

## Prompt 3 — Team Detail Page Only Refinement

Use this if the full prototype is mostly fine, but `team-detail.html` still needs work.

```text
Refine only the team-detail.html experience in the static Bulldog Statbook prototype.

PRIMARY GOAL
Make team-detail.html feel like the strongest and most believable working screen in the product.

BRAND / VISUAL REFERENCE
Use https://www.bulldogstats.com as the brand and tone reference.

The page should feel like:
- the operational center of the app
- serious sports/team software
- brand-connected to Bulldog
- structured and easy to scan
- premium but restrained

Do NOT turn the page into a marketing hero layout.
Do NOT add unnecessary decorative sports graphics.
Do NOT introduce frameworks or backend dependencies.

FOCUS AREAS

1. TEAM HEADER
- strengthen hierarchy of team name, sport, season, age group, and location
- make header feel confident and product-grade
- improve action placement (example: edit team, add player)

2. SUMMARY BAND / STATS
- improve the summary cards or summary row
- make key team context more useful and visually balanced
- avoid generic stat-card styling

3. OVERVIEW / ROSTER / ROLES
- improve the distinction between these sections
- tab styling should feel deliberate and polished
- sections should be easier to scan quickly
- content areas should feel like real work surfaces

4. ROSTER SECTION
- improve row hierarchy for:
  - player code
  - claim status
  - jersey number
  - positions
- roster table/list should feel crisp and actionable
- make unclaimed vs claimed status especially clear

5. ROLES SECTION
- improve presentation of:
  - email / user
  - role type
  - status
- make team_admin vs coach vs viewer visually clear

6. DESKTOP UX
- optimize specifically for desktop readability and confidence
- improve spacing, density, and page rhythm
- make the layout feel intentional and not template-like

7. BRAND FIT
- use Bulldog green and dark/navy tones with restraint and confidence
- keep typography bold in headings
- preserve a product-like feel rather than a generic admin look

TECHNICAL REQUIREMENTS
- static HTML/CSS/minimal JS only
- update only what is necessary
- if styles.css or app.js need refinement for this page, include those changes too

OUTPUT FORMAT
Return:
1. updated team-detail.html
2. updated styles.css if needed
3. updated app.js if needed
4. a short explanation of what made the page stronger
```

---

## Prompt 4 — Micro-Refinement if the Prototype Still Feels Too Generic

Use this only if the second pass still feels too much like a starter template.

```text
Push the existing static Bulldog Statbook prototype further toward a distinctive Bulldog product experience.

Focus specifically on:
- reducing generic dashboard-template styling
- making typography more confident and brand-aligned
- making the dark/navy + green system feel more intentional
- improving composition, spacing, and structure
- making the product feel custom and designed, not assembled from defaults

Do not add unnecessary visual noise.
Do not make it flashy.
Do not make it look like a marketing site.

Instead:
- improve restraint
- improve confidence
- improve structure
- improve page hierarchy
- improve component consistency

Return only the files that need to change, plus a brief summary of how the prototype now better reflects the Bulldog brand.
```

---

## Recommended Prompt Order

Use prompts in this sequence:

1. **Prompt 1** — generate the full static prototype
2. review the prototype
3. **Prompt 2** — refine the full prototype
4. if needed, **Prompt 3** — strengthen team-detail only
5. if still needed, **Prompt 4** — remove remaining generic-template feel

---

## Prototype Review Workflow

After each pass, review the prototype using:

- `00_Prototype-Review-Checklist.md`
- `00_Brand-and-Visual-Reference.md`
- bulldogstats.com as the live visual reference

Priority review pages:
1. `team-detail.html`
2. `dashboard.html`
3. `index.html`
4. `teams.html`

---

## Notes for Asset Use

If Codex has access to real Bulldog assets:
- use the real wordmark/logo where appropriate

If not:
- use a clean text-only brand treatment
- do not invent substitute bulldog graphics

---

## Final Reminder

The static prototype is intended to help evaluate:
- desktop UX
- page hierarchy
- information design
- product tone
- brand alignment

It is not intended to:
- replace Laravel implementation planning
- solve backend architecture
- implement real auth or database behavior
```

