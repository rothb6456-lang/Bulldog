00_Document-Priority-and-Source-of-Truth.md

**md**  
\# Document Priority and Source of Truth  
<br/>Version: v1.0  
Last Updated: 2026-08-18  
Status: Active  
Document Type: Governance / Priority  
Priority: Highest  
<br/>\## Purpose  
<br/>This document defines which materials should be treated as authoritative when Codex or human contributors encounter overlapping guidance.  
<br/>\---  
<br/>\## Highest Priority Documents  
<br/>These should be treated as primary source of truth:  
<br/>1\. \`00_Prototype-Scope-and-UX-Goals.md\`  
2\. \`01_Product-and-Planning/01_Technical-Blueprint.md\`  
3\. \`01_Product-and-Planning/02_Implementation-Backlog.md\`  
4\. \`01_Product-and-Planning/03_Database-Schema-Draft.md\`  
5\. \`01_Product-and-Planning/04_API-Endpoints-Plan.md\`  
<br/>\---  
<br/>\## Secondary Priority Documents  
<br/>These define implementation shape but should not override product truth:  
<br/>1\. \`02_Architecture-and-Stack/10_Laravel-Starter-Architecture.md\`  
2\. \`02_Architecture-and-Stack/11_Service-Skeletons.md\`  
3\. \`02_Architecture-and-Stack/12_Laravel-File-Stubs.md\`  
<br/>\---  
<br/>\## Tertiary Priority Documents  
<br/>These are Codex execution aids:  
<br/>1\. \`03_Codex-Build-System/20_Codex-Oriented-Architecture-and-Build-Guide.md\`  
2\. \`03_Codex-Build-System/21_Phase-1-2-Codex-Starter-Pack.md\`  
3\. \`03_Codex-Build-System/22_Codex-Prompt-Master.md\`  
<br/>\---  
<br/>\## Operational Documents  
<br/>These are important but not authoritative for product design:  
<br/>1\. \`04_Deployment-and-Ops/30_Laravel-Deployment-Checklist.md\`  
<br/>\---  
<br/>\## Static Prototype Priority  
<br/>For the static prototype specifically, the authoritative order is:  
<br/>1\. \`00_Prototype-Scope-and-UX-Goals.md\`  
2\. \`00_Brand-and-Visual-Reference.md\`  
3\. \`00_Prototype-Asset-Usage-Guide.md\`  
4\. \`00_Prototype-Review-Checklist.md\`  
5\. the static prototype prompt / refinement prompt  
6\. bulldogstats.com visual reference  
7\. implementation-oriented Laravel docs only where useful for information architecture  
<br/>\---  
<br/>\## Conflict Resolution Rules  
<br/>If documents conflict:  
<br/>\### Rule 1  
Product scope beats implementation convenience.  
<br/>\### Rule 2  
Static prototype goals beat Laravel implementation details during prototype work.  
<br/>\### Rule 3  
Technical blueprint beats prompt wording if a prompt drifts.  
<br/>\### Rule 4  
Brand reference from bulldogstats.com beats generic styling assumptions.  
<br/>\### Rule 5  
Archived files are never authoritative.  
<br/>\---  
<br/>\## Status Labels  
<br/>Use these labels consistently at the top of all documents:  
<br/>\- \`Status: Active\`  
\- \`Status: Draft\`  
\- \`Status: Supporting\`  
\- \`Status: Superseded\`  
\- \`Status: Archived\`  
<br/>\---  
<br/>\## Recommendation  
<br/>When producing code, Codex should explicitly confirm:  
\- what phase is being worked on  
\- whether the task is prototype or app implementation  
\- which documents are treated as source of truth