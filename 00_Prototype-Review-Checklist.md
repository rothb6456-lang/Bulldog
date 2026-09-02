00_Prototype-Task-Definition

This is useful if you want to explicitly separate **prototype work** from **Laravel app work**.

**md**  
\# Prototype Task Definition  
<br/>Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Task Framing  
Priority: High  
<br/>\## Current Task Mode  
<br/>The project is currently in:  
<br/>\# Static Prototype Mode  
<br/>This means the active task is to build or refine a \*\*static front-end product prototype\*\* for visual UX evaluation.  
<br/>\---  
<br/>\## What Codex Should Optimize For  
<br/>\- visual coherence  
\- navigation clarity  
\- brand alignment  
\- desktop usability  
\- information hierarchy  
\- structured sample data presentation  
<br/>\---  
<br/>\## What Codex Should Not Optimize For Right Now  
<br/>\- Laravel routing  
\- controller logic  
\- migrations  
\- backend auth  
\- API implementation  
\- production-ready data persistence  
<br/>\---  
<br/>\## Output Expectation  
<br/>Current output should primarily be:  
\- HTML  
\- CSS  
\- minimal JS  
\- static assets and layout refinements  
<br/>\---  
<br/>\## Transition Later  
<br/>After the prototype is approved, the next mode will be:  
<br/>\# Laravel Implementation Mode  
<br/>At that point, implementation documents will become more active than prototype-only instructions.

# What we bypassed but may still be required to finalize the prototype

For the **prototype specifically**, I do **not** think you need more backend documents right now.

But I do think you may still want these practical items added to the project if they don't already exist:

## A. Real logo asset exports

If Codex truly has access, great. If not, make sure you have:

- bulldog-wordmark.svg
- bulldog-wordmark-dark.svg
- bulldog-wordmark-light.svg
- favicon/app icon if available

These will improve the prototype significantly.

## B. Prototype asset folder

Recommended:

**text**  
prototype-assets/  
logos/  
screenshots/  
icons/  
notes/

## C. A short prototype-specific prompt file

If you want one clean file in the architecture folder:

### \`23_Static-Prototype-Prompt-Master.md\`

That can store:

- the one-shot build prompt
- the refinement prompt
- any micro-refinement prompts

This is optional, but very useful.

# Draft for \`23_Static-Prototype-Prompt-Master.md\`

**md**  
\# Static Prototype Prompt Master  
<br/>Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Codex Prompt  
Priority: High  
<br/>\## Purpose  
<br/>This document stores the current clean prompts for generating and refining the Bulldog Statbook static prototype.  
<br/>\## Included Prompts  
<br/>\- full one-shot prototype generation prompt  
\- refinement prompt  
\- optional team-detail-only refinement prompt  
<br/>\## Usage  
<br/>Use this file when working in static prototype mode.  
Prefer this file over older prompt fragments spread across chat history.

You can then paste your final one-shot prompt and refinement prompt into it.

# Minimum set I recommend you actually add now

If you want the leanest useful answer, add these now:

1. 00_README-Statbook-Data-Architecture.md
2. 00_Document-Priority-and-Source-of-Truth.md
3. 00_Prototype-Scope-and-UX-Goals.md
4. 00_Brand-and-Visual-Reference.md
5. 00_Prototype-Review-Checklist.md
6. 23_Static-Prototype-Prompt-Master.md

That's the best minimum package.