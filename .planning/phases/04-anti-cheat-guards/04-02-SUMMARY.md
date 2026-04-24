---
phase: 04-anti-cheat-guards
plan: 02
subsystem: exam-taking
tags: [anti-cheat, tab-switch, visibilitychange]
dependency_graph:
  requires:
    - frontend-guards
  provides:
    - tab-switch-tracking
  affects:
    - exam-session-table
    - activity_logs-table
tech_stack:
  added:
    - visibilitychange listener
    - tab-switch warning overlay
  patterns:
    - mount to check, handleTabSwitch method
key_files:
  created:
    - database/migrations/2026_04_23_035211_add_max_tab_switches_to_exams_table.php
  modified:
    - app/Livewire/Student/ExamTaking.php
    - resources/views/livewire/student/exam-taking.blade.php
decisions:
  - Warning shown after first tab switch, not before
  - Auto-submit after reaching configured threshold (default 3)
metrics:
  duration: combined with 04-01
  completed_date: 2026-04-23
---

# Phase 4 Plan 2: Tab Switch Detection Summary

## Overview

Tab/window switch detection via visibilitychange listener with warning overlay and auto-submit.

## One-Liner

Visibility change triggers counter; warning overlay on first switch, auto-submit after configured threshold.

## Implemented Features

### Task 1: Tab Switch Detection in Component

- **handleTabSwitch()** increments counter, logs to activity_logs
- **tab_switch_count** stored on ExamSession
- **max_tab_switches** configurable per exam (default 3)
- Warning shown after first switch (`showTabWarning`)
- Auto-submit triggers when threshold reached
- Session flagged with reason on auto-submit

### Task 2: visibilitychange Event in Blade

- **handleVisibilityChange()** in Alpine.js calls Livewire method when page hidden
- **Tab switch indicator** shows current count in header
- **Warning overlay** full-screen, amber colored
- **Dismiss button** allows student to continue

## Verification

- Leaving tab triggers handleTabSwitch
- Counter increments and displays in UI
- Warning overlay appears after first switch
- Auto-submit triggers after 3rd switch
- ActivityLog entries created for each switch

## Deviation

None - plan executed as written.

## Threat Surface

| Flag | File | Description |
|------|------|-------------|
| new_event_handlers | exam-taking.blade.php | visibilitychange listener |