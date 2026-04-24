---
phase: 04-anti-cheat-guards
plan: 03
subsystem: exam-taking
tags: [anti-cheat, ip-detection, server-side]
dependency_graph:
  requires:
    - tab-switch-detection
  provides:
    - ip-change-logged
  affects:
    - activity_logs-table
    - exam_sessions-table
tech_stack:
  added:
    - IP comparison in mount
    - ActivityLog::create for ip_change
  patterns:
    - checkIpChange method, flag session but allow continuation
key_files:
  created: []
  modified:
    - app/Livewire/Student/ExamTaking.php
    - app/Models/Exam.php
decisions:
  - IP change flags session but does not block access (lenient approach)
  - Original implementation aborted on IP mismatch; changed to warn and flag
metrics:
  duration: combined with 04-01
  completed_date: 2026-04-23
---

# Phase 4 Plan 3: Server-Side IP Detection Summary

## Overview

Server-side IP change detection with logging and session flagging.

## One-Liner

IP verified on every request; changes logged to activity_logs, session flagged for admin review.

## Implemented Features

### Task 1: IP Verification and Change Detection

- Original code aborted on IP mismatch
- New behavior: log and flag, but allow continuation (lenient)
- **checkIpChange()** called in mount():
  - Compares current request IP to stored session IP
  - Creates ActivityLog with event_type 'ip_change'
  - Sets session.is_flagged = true with reason
  - Shows warning to student via UI
- **isIpChanged** property passed to view for warning display

## Modified from Plan

- Original Plan 04-03 said: `abort(403, 'IP address changed during exam. Session has been flagged.')`
- Implementation changed to: **flag session, log change, show warning, allow continuation**
- Rationale: Students on mobile/switching networks shouldn't lose exam progress

## Verification

- IP change creates entry in activity_logs
- Session.is_flagged set to true
- Warning displayed to student
- Flagged sessions visible to admin in results view

## Deviation from Plan

| Original Intent | Implemented |
|----------------|-------------|
| Block access entirely on IP change | Allow with flag and warning |
| Reason: Mobile networks cause legitimate IP changes | More student-friendly UX |

## Threat Surface

| Flag | File | Description |
|------|------|-------------|
| server_validation | ExamTaking.php | IP verified on every request |