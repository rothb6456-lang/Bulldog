# Stats Prototype Expansion Brief

Version: v1.0    
Last Updated: 2026-08-18    
Status: Active    
Document Type: Prototype Expansion Brief    
Priority: Highest    
Audience:  
- Codex  
- prototype builders  
- future implementation planning chats  
- stakeholders reviewing next-phase prototype scope

Related Documents:  
- `00_Project-Handoff-Master.md`  
- `00_Document-Priority-and-Source-of-Truth.md`  
- `00_Prototype-Scope-and-UX-Goals.md`  
- `00_Brand-and-Visual-Reference.md`  
- `00_Prototype-Asset-Usage-Guide.md`  
- `00_Prototype-Review-Checklist.md`  
- `00_Prototype-Task-Definition.md`  
- `23_Static-Prototype-Prompt-Master.md`  
- `24_Static-Prototype-File-Map.md`  
- `25_Static-Prototype-Sample-Content.md`  
- `26_Static-Prototype-Acceptance-Criteria.md`

---

## Purpose

This document defines the next major prototype expansion for Bulldog Statbook.

The initial static prototype established:  
- the product shell  
- desktop-first information architecture  
- Bulldog-aligned visual direction  
- page structure  
- navigation  
- foundational administrative workflows

This expansion phase builds on that base to make the prototype feel significantly more like a usable product by introducing **stat tracking workflows**, **stat visibility**, and **connected data experiences** across players, teams, and events.

The purpose of this phase is to make the prototype strong enough to help determine:  
- what Bulldog Statbook should test in beta  
- what views and flows are most important  
- where real workflow complexity appears  
- how stats should connect to the rest of the product

---

## Executive Summary

Bulldog Statbook should now evolve from a strong UI prototype into a more operational product prototype.

The next version should show, in a credible desktop-first way:

- how stats are entered  
- how stats are associated to players  
- how stats roll up to teams  
- how staff/admin users review stat outcomes  
- how stat workflows connect to existing team and player workflows  
- how the product could be evaluated for beta readiness

This is still a static prototype, not a backend implementation.

However, the product illusion should become much stronger.

Users reviewing the prototype should be able to understand:  
- where stat entry begins  
- where stat history appears  
- how player and team views differ  
- what workflows feel ready  
- what workflows require beta validation

---

## Strategic Goal

The strategic goal of this expansion is:

> Transform the current Bulldog Statbook prototype into a richer working desktop prototype that clearly demonstrates stat entry, stat review, player-level performance visibility, team-level rollups, and beta-evaluation workflows.

---

## What This Expansion Should Achieve

This phase should make the prototype answer the following questions clearly:

### 1. How are stats entered?  
There should be a visible and believable workflow for entering or capturing stats.

### 2. How do stats connect to players?  
A reviewer should be able to see how an individual player’s profile or detail experience reflects tracked stats.

### 3. How do stats connect to teams?  
A reviewer should be able to see how team-level pages reflect aggregate or grouped stat information.

### 4. How are recent games/events represented?  
The prototype should imply or show event-based stat context such as:  
- game  
- event  
- session  
- matchup  
- recent activity

### 5. What should be beta tested?  
The prototype should become detailed enough that workflow, clarity, navigation, and information-density questions are visible.

---

## Scope of the Expansion

This phase expands the prototype in the following directions:

### A. Stat entry and capture  
The prototype should demonstrate how a user would enter or review stats for a game, event, or team session.

Possible patterns include:  
- dedicated stat-entry page  
- structured stat-entry form  
- game/event stat screen  
- table-based stat input  
- segmented or tabbed stat entry  
- quick actions from team detail

### B. Player stat visibility  
Player-related pages should make it clear how stats are viewed at the player level.

Possible elements include:  
- recent performance summary  
- stat totals  
- stat averages  
- categorical stat groups  
- trend or recent activity sections  
- linked game/event entries

### C. Team stat visibility  
Team pages should make team-level performance easier to understand.

Possible elements include:  
- team summary cards  
- aggregate stat panels  
- team-level breakdowns  
- recent game summaries  
- leaderboard patterns  
- player contribution summaries

### D. Dashboard evolution  
The dashboard should move beyond general overview and begin reflecting actual product activity around stats.

Possible elements include:  
- recent stat activity  
- team performance summaries  
- stat anomalies or highlights  
- quick paths into stat entry  
- pending review or incomplete entry states

### E. Workflow connectivity  
The product should feel operationally connected.

A reviewer should be able to move logically between:  
- dashboard  
- team detail  
- player detail  
- stat entry  
- stat review  
- summaries and drilldowns

---

## Prototype Philosophy for This Phase

This is still a **static prototype**, but it should now create a much stronger **functional illusion**.

That means:

- users should understand workflows without backend logic  
- data should appear coherent and intentional  
- interactions can be simulated with lightweight JavaScript  
- review value matters more than technical realism  
- product confidence matters more than decorative polish alone

The prototype should feel like:  
- a believable desktop application  
- a product with connected data  
- a system that is closer to beta planning

---

## Key Product Questions This Prototype Should Help Answer

This phase should help expose and answer questions such as:

- What is the cleanest path for entering stats?  
- Should stats entry be game-first, team-first, or player-first?  
- What stat views are most useful for coaches/admins?  
- How much detail belongs on team pages versus dedicated stats pages?  
- How easily can users move from overview to detail?  
- Where are validation, workflow support, and review states likely needed later?  
- What parts of the product need beta testing most urgently?

---

## High-Level UX Direction

The stats expansion should preserve and strengthen the established UX principles:

- desktop-first  
- clear hierarchy  
- serious product tone  
- review-ready clarity  
- coherent cross-page system  
- realistic sample content  
- restrained Bulldog branding  
- strong app-shell consistency  
- excellent readability for dense information

Stats-related UI should feel:  
- intentional  
- structured  
- data-forward  
- usable  
- product-grade

Not:  
- toy-like  
- over-decorated  
- generic dashboard-template driven  
- visually noisy

---

## Visual Direction

Continue aligning with bulldogstats.com and the broader Bulldog ecosystem.

The prototype should continue using:  
- dark/navy structural framing  
- Bulldog green for action/emphasis  
- strong headings and labels  
- consistent cards/tables/forms  
- product-grade density  
- restrained branding

Stats interfaces should especially emphasize:  
- clarity  
- grouping  
- comparability  
- scan-friendly table structure  
- strong summary modules  
- obvious action paths

---

## Core Experience Areas To Add or Deepen

The following areas should be added or significantly strengthened.

### 1. Stats Dashboard  
A stats-aware dashboard or dashboard section should help answer:  
- what happened recently  
- who performed well  
- what requires attention  
- where the user should go next

Possible modules:  
- recent stat entries  
- game summary cards  
- top player snapshots  
- team trends  
- pending/incomplete stat capture  
- quick-entry actions

### 2. Team-Level Stats  
Team detail or a dedicated team stats view should show:  
- team totals or summary metrics  
- recent game performance  
- player contribution snapshots  
- stat categories relevant to the sport/workflow  
- links to player or event detail

### 3. Player-Level Stats  
Player-related pages should now show:  
- individual stat summaries  
- recent entries  
- trend or history sections  
- role-aware or category-aware metrics  
- links back to team/event context

### 4. Game/Event Stat Entry  
There should be a credible way to show how stats are entered for a game or event.

This does not need full production complexity, but it should make the workflow feel tangible.

### 5. Stat Review / Drilldown  
The prototype should help a reviewer understand:  
- how recorded stats are reviewed  
- where discrepancies or updates might later occur  
- what drilldown pathways exist

---

## Suggested New or Expanded Pages

Codex may refine current pages and add new pages where helpful.

Strong candidates include:

- `stats-dashboard.html`  
- `team-stats.html`  
- `player-stats.html`  
- `game-stats-entry.html`  
- `stat-entry-review.html`

These are not mandatory file names, but the prototype should cover these experience areas in some form.

It is acceptable to implement some of this through:  
- dedicated pages  
- tabs  
- panels  
- linked states  
- richer sections within existing pages

Choose the structure that produces the strongest and most coherent desktop-first prototype.

---

## Existing Pages That Must Be Preserved and Deepened

The following existing pages remain important and should be refined rather than sidelined:

- `index.html`  
- `dashboard.html`  
- `teams.html`  
- `team-detail.html`  
- `create-team.html`  
- `player-identity.html`  
- `guardian-relationships.html`

These should feel more complete after the stats expansion, not less.

---

## Priority Page Guidance

### `team-detail.html`  
This page remains one of the most important pages in the product.

It should now help demonstrate:  
- team-level summaries  
- pathways to stats entry  
- player contribution visibility  
- recent performance context  
- clear operational actions

### Potential companion “hero” page  
If a dedicated stats page becomes the best way to explain the product, that is acceptable.

However:  
- `team-detail.html` must remain excellent  
- the prototype should not feel fragmented  
- the stats experience must connect clearly back to teams and players

---

## Functional Illusion Requirements

This phase should include enough lightweight interaction to make workflows feel believable.

Reasonable static-prototype interactions may include:  
- tab switching  
- stat category switching  
- filters  
- quick search  
- drilldown links  
- row expansion  
- summary toggles  
- active-state navigation  
- panel switching  
- form-state changes  
- simple fake workflow progression

Use JavaScript lightly but meaningfully.

The goal is not to build production logic.  
The goal is to make the product behavior understandable.

---

## Content Requirements

The prototype should use realistic, populated sample content.

It should not feel empty or placeholder-driven.

Use believable examples for:  
- teams  
- players  
- stat categories  
- games/events  
- recent activity  
- totals, averages, or summaries  
- rankings or highlights where useful  
- recent stat entry logs  
- performance panels

The data should support review conversations, not just fill space.

---

## Design System Expectations

The expanded prototype should maintain or improve the shared design system, including:

- app shell  
- sidebar/navigation  
- page headers  
- action bars  
- cards/panels  
- table styles  
- form styles  
- tabs/segmented controls  
- badges/status indicators  
- filter/search tools  
- summary modules  
- spacing rhythm  
- typography hierarchy

The new stats workflows should feel fully native to the system.

They should not look like a separate prototype pasted onto the old one.

---

## Beta-Planning Lens

This phase should explicitly help identify future beta-test concerns.

The prototype should make it easier to evaluate:

### Workflow clarity  
- Is stat entry easy to understand?  
- Is navigation between stat contexts clear?

### Information architecture  
- Are team and player stat views separated well enough?  
- Is the product too dense or not dense enough?

### Review usability  
- Can an admin or coach quickly interpret the data?  
- Are summaries and drilldowns balanced appropriately?

### Operational confidence  
- Does the product feel trustworthy enough for daily use?  
- Are the places that would need validation obvious?

### Missing workflow discovery  
- What additional pages, states, or constraints might be required before beta?

---

## Recommended Build Behavior for Codex

When implementing this phase, Codex should:

- extend the current prototype rather than restart it  
- preserve and improve strong existing patterns  
- make reasonable decisions without pausing for minor ambiguity  
- optimize for review usefulness over implementation realism  
- build enough detail to surface real product questions  
- avoid empty shell pages  
- avoid over-engineering

---

## Source-of-Truth Guidance

For this expansion phase, use the existing prototype and project documents as the source-of-truth base.

Primary references:  
- `00_Document-Priority-and-Source-of-Truth.md`  
- `26_Static-Prototype-Acceptance-Criteria.md`  
- `00_Prototype-Scope-and-UX-Goals.md`  
- `00_Brand-and-Visual-Reference.md`  
- `00_Prototype-Asset-Usage-Guide.md`  
- `24_Static-Prototype-File-Map.md`  
- `25_Static-Prototype-Sample-Content.md`  
- `23_Static-Prototype-Prompt-Master.md`  
- `00_Project-Handoff-Master.md`

Also use:  
- the current prototype files in `06_Static-Prototype/current/`  
- notes in `06_Static-Prototype/notes/`  
- supporting planning docs if useful for conceptual grounding

---

## Ambiguity Resolution

If ambiguity exists, choose the direction that best improves:

1. review readiness    
2. beta-planning usefulness    
3. product credibility    
4. clarity of stat-tracking workflows    
5. desktop usability    
6. team/player/stats connectedness    
7. Bulldog brand alignment    
8. cross-page consistency  

Do not pause for minor issues if a strong product-grade decision can be made.

---

## Success Criteria for This Phase

This prototype expansion is successful if a reviewer can clearly understand:

- where and how stats are entered  
- how stats show up for players  
- how stats show up for teams  
- how recent games/events connect to stat views  
- what the main operational workflows are  
- which areas should be tested in beta

It is especially successful if the prototype feels like:  
- a serious desktop product  
- a coherent Bulldog application  
- a realistic foundation for future implementation planning

---

## Final Instruction Summary

This is a prototype expansion phase, not a rewrite.

The goal is to turn the existing Bulldog Statbook prototype into a richer working desktop prototype that demonstrates stat workflows clearly enough to support beta planning and deeper product review.

Focus on:  
- connected data experiences  
- stat entry  
- stat review  
- player/team rollups  
- dashboard usefulness  
- strong operational navigation  
- review-ready polish  