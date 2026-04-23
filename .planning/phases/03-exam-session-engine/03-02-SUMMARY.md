---
phase: 03-exam-session-engine
plan: 02
subsystem: exam-session
tags: [student, exam, livewire, timer, navigation]
dependency_graph:
  requires: [SESS-01, SESS-08]
  provides: [SESS-03, SESS-04, SESS-05, SESS-06, SESS-07, exam-session]
  affects: [results]
tech_stack:
  added: [Livewire, Alpine.js]
  patterns: [component-view, auto-save]
key_files:
  created:
    - app/Livewire/Student/ExamTaking.php
    - app/Livewire/Student/ExamSession.php (renamed to avoid collision)
    - resources/views/livewire/student/exam-taking.blade.php
    - database/migrations/2026_04_23_034017_add_flagged_questions_and_total_marks_to_exam_sessions_table.php
  modified:
    - routes/web.php
    - app/Models/ExamSession.php
decisions:
  - Renamed component to ExamTaking to avoid collision with ExamSession model
  - Added flagged_questions JSON column for question flagging
  - Added total_marks column for score tracking
---

# Phase 03 Plan 02: ExamSession Component Summary

## Overview

Implemented core exam session Livewire component with timer, navigation, auto-save, and question flagging. Renamed to `ExamTaking` to prevent naming collision with `ExamSession` model.

## Implementation Details

### ExamTaking Component
- `app/Livewire/Student/ExamTaking.php` handles full exam flow
- **IP verification (SESS-03):** Blocks access on IP mismatch
- **Timer (SESS-06):** Server-computed remainingSeconds from `started_at + duration_minutes`
- **Auto-submit:** Triggers when timer reaches zero
- **Navigation (SESS-04):** Prev/next buttons, question pills
- **Auto-save (SESS-05):** Saves answers on every change via `saveAnswer()`
- **Question flagging (SESS-07):** Toggle flag for review
- **Shuffle support:** Respects exam's `shuffle_questions` setting
- **Render types:** mcq, true_false, short_answer, code_snippet

### Blade View
- `resources/views/livewire/student/exam-taking.blade.php`
- Sticky header with countdown timer
- Question pills showing answered/flagged/current states
- Modal for submit confirmation

### Migration
- Added `flagged_questions` JSON column
- Added `total_marks` integer column

### Route
- `/exam/session/{session}` → ExamTaking::class

## Threat Mitigations

| Threat | Mitigation |
|--------|------------|
| T-03-02 | IP check on mount() |
| T-03-03 | Server-side timer (client only cosmetic) |
| T-03-04 | updateOrCreate with unique key |

## Test Verification

Pre-existing test failures are due to RefreshDatabase migrations not running.

## Files Created

| File | Purpose |
|------|---------|
| app/Livewire/Student/ExamTaking.php | Livewire component |
| resources/views/.../exam-taking.blade.php | Blade view |
| migration | Database schema update |

## Commits

- Files committed as part of Phase 3 wave-based execution