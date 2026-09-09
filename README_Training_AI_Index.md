# Training AI Index

Purpose: AI-readable export of the authoritative `Training_Database.xlsx` workbook.

## Source of truth

`Training_Database.xlsx` remains the authoritative master workbook. This package preserves the AI-relevant data as separate UTF-8 CSV files. Historical values, exercise names, dates, loads, RIR values, and notes are exported as recorded. No silent normalization or correction was performed.

## Included sheets

1. `Lookup_Exercises`: Exercise lookup/reference table. 98 data rows, 7 columns.
2. `tbl_ExerciseNameMap`: Primary historical/reference table. 8 data rows, 2 columns.
3. `Changelog`: Database change history. 3 data rows, 3 columns.
4. `tbl_Phases`: Primary historical/reference table. 15 data rows, 53 columns.
5. `tbl_Sessions`: Primary historical/reference table. 129 data rows, 14 columns.
6. `tbl_Exercises`: Primary historical/reference table. 2967 data rows, 18 columns.
7. `tbl_ExcerciseLibrary`: Primary historical/reference table. 102 data rows, 13 columns.
8. `tbl_Equipment`: Primary historical/reference table. 25 data rows, 13 columns.
9. `tbl_BodyMetrics`: Primary historical/reference table. 0 data rows, 28 columns.
10. `tbl_TrainingAssumptions`: Primary historical/reference table. 22 data rows, 10 columns.
11. `tbl_PRs`: Primary historical/reference table. 32 data rows, 15 columns.
12. `tbl_VolumeWeekly`: Primary historical/reference table. 31 data rows, 17 columns.
13. `tbl_VolumeMonthly`: Primary historical/reference table. 8 data rows, 20 columns.
14. `DataDictionary`: Schema/documentation. 12 data rows, 13 columns.

## Core relationship model

`tbl_Phases` → `tbl_Sessions` → `tbl_Exercises`

- `tbl_Phases` defines training phases.
- `tbl_Sessions` records workout/session-level information and links sessions to phases.
- `tbl_Exercises` records exercise/set-level results and links records to sessions and phases.
- `Lookup_Exercises` provides exercise reference/classification information.
- `tbl_PRs` provides recorded personal-record events.
- `tbl_VolumeWeekly` and `tbl_VolumeMonthly` provide derived volume summaries.
- `tbl_TrainingAssumptions` stores longitudinal training assumptions and supporting context.
- `tbl_BodyMetrics` stores body-metric data when populated.
- `tbl_ExerciseNameMap` supports exercise-name mapping.
- `tbl_ExcerciseLibrary` is retained exactly as named in the source workbook.
- `tbl_Equipment` contains equipment reference data.
- `DataDictionary` documents schema.
- `Changelog` documents database changes.

## AI interpretation rules

1. Use `tbl_Sessions` for session-level context.
2. Use `tbl_Exercises` for exercise/set performance.
3. Use `tbl_Phases` for phase context.
4. Use `Lookup_Exercises` for exercise reference/classification.
5. Use `tbl_TrainingAssumptions` for longitudinal hypotheses and documented assumptions.
6. Use `tbl_PRs` for recorded PR history.
7. Use weekly/monthly volume tables as derived summaries; use `tbl_Exercises` for event-level verification.
8. Treat Dashboard_ and WorkoutCards_ sheets as presentation layers unless display logic is specifically relevant.
9. Preserve source terminology. Do not silently rename historical exercise names.
10. If a field or table is empty, report it as unavailable rather than filling it from assumptions or outside knowledge.
11. Use IDs and table relationships where available rather than relying only on display names.

## Update procedure

When the master workbook changes materially, regenerate this package so the CSV exports remain synchronized with `Training_Database.xlsx`.
