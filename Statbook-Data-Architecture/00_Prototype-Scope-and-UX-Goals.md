00_Brand-and-Visual-Reference

**md**  
\# Brand and Visual Reference  
<br/>Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Brand / Design Guidance  
Priority: High  
<br/>\## Purpose  
<br/>This document gives Codex and contributors a clear visual direction for the Bulldog Statbook prototype.  
<br/>\---  
<br/>\## Primary Brand Reference  
<br/>Use the public Bulldog site as the main visual reference:  
<br/>\*\*<https://www.bulldogstats.com\*\>*  
<br/>The prototype should feel like the product/software extension of that brand.  
<br/>\---  
<br/>\## Brand Identity  
<br/>\- Brand name: \*\*Bulldog\*\*  
\- Supporting line: \*\*Stats & Sports Innovation\*\*  
\- Product name: \*\*Bulldog Statbook\*\*  
<br/>The current brand system includes:  
\- strong wordmark  
\- bulldog illustration/logo treatment  
\- dark/navy framing  
\- Bulldog green accent  
\- white/light surfaces  
\- bold, confident headline typography  
\- clean, grid-forward composition  
<br/>\---  
<br/>\## Visual Personality  
<br/>The prototype should feel:  
<br/>\- bold  
\- modern  
\- practical  
\- structured  
\- analytical  
\- premium but restrained  
\- sports-operations focused  
<br/>The prototype should not feel:  
<br/>\- generic SaaS  
\- retro hockey UI  
\- fan-site themed  
\- neon  
\- overly playful  
\- cluttered  
\- gimmicky  
<br/>\---  
<br/>\## Product vs Marketing Distinction  
<br/>\### Marketing site  
The marketing site can be more brand-forward and statement-driven.  
<br/>\### Product prototype  
The product should be:  
\- more structured  
\- more functional  
\- more scannable  
\- more task-oriented  
\- more information-dense  
<br/>Do not copy the homepage layout directly.  
<br/>Instead, translate the brand language into a product UI system.  
<br/>\---  
<br/>\## Color Direction  
<br/>Use a palette aligned to bulldogstats.com.  
<br/>Suggested direction:  
<br/>\- deep navy / ink for primary framing  
\- Bulldog green for primary actions and emphasis  
\- white / off-white for surfaces  
\- cool neutral grays for borders and secondary text  
\- optional restrained blue-green support tone  
<br/>\### Suggested CSS tokens  
<br/>\`\`\`css  
:root {  
--bg: #f3f5f4;  
--surface: #ffffff;  
--surface-alt: #f7f8f8;  
<br/> --text: #0d1b26;  
--text-muted: #5f6d78;  
<br/> --border: #d7dede;  
--border-strong: #b9c4c4;  
<br/> --navy: #081722;  
--navy-2: #102533;  
<br/> --green: #007a43;  
--green-hover: #00663a;  
--green-soft: #e4f3eb;  
<br/> --blue-accent: #0f6f97;  
--blue-soft: #e7f2f7;  
<br/> --warning-soft: #fff2d9;  
--warning-text: #9a6700;  
<br/> --danger-soft: #fde8e8;  
--danger-text: #b42318;  
}

## Typography Direction

Use typography that feels:

- bold and high-confidence in headings
- clean and readable in body copy
- structured and editorial in hierarchy
- practical for a software product

Avoid novelty type or decorative mascot styling in the product UI.

## Logo Guidance

If the real Bulldog logo asset is available:

- use it directly in the prototype

If the logo asset is not available:

- use a clean text-based brand block
- do not invent a fake bulldog icon
- do not use clip-art or substitute mascot graphics

Suggested fallback brand treatment:

**BULLDOG** _Stats & Sports Innovation_

or

**Bulldog Statbook** _by Bulldog Stats & Sports Innovation_

## Most Important Design Priorities

1. strong app shell
2. serious dashboard hierarchy
3. excellent team-detail page
4. clean tables and badges
5. high-confidence typography
6. clear role/status signaling
7. coherence with bulldogstats.com

\---  
<br/>\# 5) \`00_Prototype-Asset-Usage-Guide.md\`  
<br/>\`\`\`md  
\# Prototype Asset Usage Guide  
<br/>Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Asset Guidance  
Priority: Medium  
<br/>\## Purpose  
<br/>This document explains how logos, screenshots, and related Bulldog brand assets should be used in the static prototype.  
<br/>\---  
<br/>\## Primary Rule  
<br/>Use real Bulldog assets if they are already present in the Codex project.  
<br/>Do not fabricate brand assets if the real ones are available.  
<br/>\---  
<br/>\## Allowed Asset Use  
<br/>The static prototype may use:  
<br/>\- official Bulldog logo files  
\- official wordmark exports  
\- approved screenshots from bulldogstats.com for reference only  
\- official color palette references  
\- official branding/identity files already stored in the project  
<br/>\---  
<br/>\## Not Allowed  
<br/>Do not use:  
<br/>\- fake bulldog mascot graphics  
\- stock dog icons  
\- clip-art replacements  
\- invented "similar" logos  
\- random sports illustrations that are not part of the brand system  
<br/>\---  
<br/>\## Recommended Usage Locations  
<br/>\### Logo / wordmark  
Use in:  
\- \`index.html\`  
\- sidebar/app shell  
\- possibly a compact top-left header treatment  
<br/>\### Screenshots  
Do not use homepage screenshots as page content inside the product prototype unless explicitly requested.  
They are reference material, not default UI content.  
<br/>\### Icons  
If no icon system exists, keep icon use minimal rather than adding a mismatched set.  
<br/>\---  
<br/>\## Fallback Rule  
<br/>If a needed asset is unavailable:  
<br/>\- use clean text-only brand treatment  
\- preserve spacing and hierarchy  
\- do not substitute unapproved imagery  
<br/>\---  
<br/>\## Prototype Goal Reminder  
<br/>The purpose of the prototype is to evaluate UX and product feel.  
Brand assets should support that, not overpower it.