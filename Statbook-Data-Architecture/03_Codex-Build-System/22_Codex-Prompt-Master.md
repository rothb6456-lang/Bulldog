Codex Prompt — Full Static Bulldog App Prototype

Build a complete static front-end prototype for the Bulldog app experience as a multi-page desktop-first web application.

PRIMARY GOAL

Create a visually polished placeholder product prototype that I can open in browser and evaluate from a UX/UI perspective before full backend implementation.

IMPORTANT CONTEXT

This is NOT the marketing website.

This is the product/application experience.

Use the existing public website as the visual brand reference:

<https://www.bulldogstats.com>

Brand details:

\- Brand name: Bulldog

\- Supporting line: Stats & Sports Innovation

\- The logo/wordmark is BULLDOG with the "O" replaced by a bulldog sketch/logo

\- If no actual logo asset file is provided, do NOT invent a fake bulldog logo and do NOT use clip-art

\- Instead, use a clean text-based brand treatment that feels consistent with the site, such as:

BULLDOG

Stats & Sports Innovation

\- Product name inside the app should be:

Bulldog Statbook

VISUAL DIRECTION

The app should feel like the product extension of bulldogstats.com, not a generic admin template.

Use bulldogstats.com as reference for:

\- bold, high-confidence typography

\- deep green + dark navy / near-black palette

\- white / off-white surfaces

\- clean spacing and premium restraint

\- coach-built, sports-operations tone

\- serious, modern, practical structure

The app should feel:

\- serious

\- modern

\- analytical

\- structured

\- coach/admin oriented

\- premium but restrained

\- sports-specific without feeling like a sports media site or fan site

Do NOT:

\- copy the marketing homepage layout directly

\- make it retro or hockey-themed

\- use generic startup gradients

\- use neon colors

\- use playful sports gimmicks

\- use mascot clip art

\- overload the pages with fake charts that do not help UX

DESKTOP EXPERIENCE PRIORITY

This prototype should primarily help evaluate the desktop experience.

Design desktop-first.

It should still collapse reasonably for tablet/mobile, but desktop quality is the priority.

TECHNICAL REQUIREMENTS

\- Static site only

\- Plain HTML, CSS, and minimal JavaScript only

\- No frameworks required

\- No backend required

\- No build step required

\- Must be easy to deploy to Cloudflare Pages

\- Use fake placeholder/sample data only

\- Keep code clean and easy to edit

OUTPUT FILES

Generate complete code for all of these files:

1\. index.html

2\. dashboard.html

3\. teams.html

4\. team-detail.html

5\. create-team.html

6\. player-identity.html

7\. guardian-relationships.html

8\. styles.css

9\. app.js

Also provide:

10\. a short README-style deployment note for Cloudflare Pages

INFORMATION ARCHITECTURE / PAGES

1\. index.html

Purpose:

\- login / entry page for the prototype

Requirements:

\- strong branded entry experience

\- show Bulldog Statbook name

\- supporting brand line: Stats & Sports Innovation

\- simple sign-in form mockup

\- button/link to enter prototype

\- should feel like product login, not marketing homepage

2\. dashboard.html

Purpose:

\- main landing screen after login

Requirements:

\- app shell with sidebar and/or top header

\- summary cards

\- "My Teams" preview

\- quick actions

\- recent identity/team context

\- strong page hierarchy

\- feel like a serious operations dashboard

3\. teams.html

Purpose:

\- list all teams where the user has a role

Requirements:

\- structured table or card-list view

\- clear "Create Team" CTA

\- show sample team rows with:

\- team name

\- sport

\- season

\- age group

\- role

\- status

\- rows should link to team-detail.html

4\. team-detail.html

Purpose:

\- strongest/most important screen in the whole prototype

Requirements:

\- strong team header

\- team summary / key stats

\- tabbed or sectioned content for:

\- Overview

\- Roster

\- Roles

\- show fake roster entries with:

\- player code

\- claim status

\- jersey number

\- positions

\- show fake team role entries with:

\- user/email

\- role type

\- status

\- page should feel like the working center of the application

\- make this the best-designed screen in the system

5\. create-team.html

Purpose:

\- form-style team creation flow

Requirements:

\- polished form layout

\- sample fields:

\- sport

\- team name

\- season label

\- age group

\- city

\- state/region

\- clear primary and secondary actions

\- should feel product-grade even though it is static

6\. player-identity.html

Purpose:

\- show a linked player identity record

Requirements:

\- player code

\- claim status

\- linked account summary

\- primary sport

\- identity status

\- should feel like an identity record page, not a profile fluff page

7\. guardian-relationships.html

Purpose:

\- show guardian-to-player relationship records

Requirements:

\- structured list or table

\- show sample rows with:

\- player code

\- relationship type

\- verification status

\- primary guardian yes/no

\- clean and serious layout

UI / LAYOUT REQUIREMENTS

App shell:

\- use a left sidebar for primary navigation

\- optional top header inside main content

\- sidebar should feel branded and product-grade

\- sidebar branding should reference Bulldog / Stats & Sports Innovation

\- active nav states should be visually clear

Navigation items:

\- Dashboard

\- Teams

\- Player Identity

\- Guardians

Design system requirements:

\- use cards, tables, badges, section headers, page headers, and clean spacing

\- badges should be visually distinct for:

\- Active

\- Pending

\- Claimed

\- Unclaimed

\- Team Admin

\- Coach

\- Viewer

\- buttons should have clear primary vs secondary treatment

\- tables should feel crisp and product-grade

\- forms should feel intentionally styled

\- typography should feel bold in headings and clean/readable in body text

BRAND / COLOR REQUIREMENTS

Use bulldogstats.com as the visual reference.

Suggested palette direction:

\- deep navy / near-black for primary framing and strong text

\- deep green as the primary action/accent color

\- white and off-white for content surfaces

\- cool neutral grays for borders and muted text

\- subtle blue-green support tones only if needed

Use a design-token style CSS approach with variables.

Suggested CSS token direction (you may refine these slightly if needed):

:root {

\--bg: #f3f5f4;

\--surface: #ffffff;

\--surface-alt: #f7f8f8;

\--text: #0d1b26;

\--text-muted: #5f6d78;

\--border: #d7dede;

\--border-strong: #b9c4c4;

\--navy: #081722;

\--navy-2: #102533;

\--green: #007a43;

\--green-hover: #00663a;

\--green-soft: #e4f3eb;

\--blue-accent: #0f6f97;

\--blue-soft: #e7f2f7;

\--warning-soft: #fff2d9;

\--warning-text: #9a6700;

\--danger-soft: #fde8e8;

\--danger-text: #b42318;

\--shadow-sm: 0 1px 2px rgba(8, 23, 34, 0.06);

\--shadow-md: 0 8px 24px rgba(8, 23, 34, 0.08);

\--radius-sm: 10px;

\--radius-md: 16px;

\--radius-lg: 20px;

}

TYPOGRAPHY DIRECTION

\- Headings should feel bold and high-confidence, similar in spirit to bulldogstats.com

\- Body text should be clean, readable, and product-oriented

\- Use system fonts if needed, or a sensible web-safe stack

\- Avoid decorative novelty fonts

\- Emphasize hierarchy and scanability

INTERACTION REQUIREMENTS

\- Minimal JS only

\- Use app.js for simple behavior such as:

\- tab switching on team-detail.html

\- optional sidebar/mobile nav toggle if helpful

\- No heavy interactivity needed

\- No fake auth logic needed

CONTENT / COPY DIRECTION

Use product-oriented copy, not marketing fluff.

Examples of tone:

\- Team operations overview

\- Roster identity and role management

\- Linked player record

\- Guardian relationship records

\- Active team context

Avoid overly generic copy like:

\- Welcome to our awesome platform

\- Manage everything in one place

unless it is made more specific and credible

RESPONSIVENESS

\- Desktop-first

\- Should still behave acceptably on tablet/mobile

\- Sidebar may collapse or stack on smaller screens

\- Tables may scroll horizontally if needed

\- Maintain readability and structure

QUALITY BAR

The result should feel:

\- more like real product software

\- less like a starter template

\- visually coherent across all pages

\- aligned with bulldogstats.com brand identity

\- polished enough for stakeholder review

DELIVERABLE FORMAT

Output the full code for every required file in clearly labeled sections, like:

\--- index.html ---

\[full code\]

\--- dashboard.html ---

\[full code\]

...etc.

Then finish with:

\- a short explanation of the design decisions

\- a short Cloudflare Pages deployment note

IMPORTANT FINAL REMINDER

This is a static UX prototype only.

Do not introduce Laravel, React, build tooling, or backend dependencies.

Keep everything simple, complete, and ready to drop into a folder and host on Cloudflare Pages.