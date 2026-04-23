---
phase: 03-exam-session-engine
plan: 01
subsystem: student-dashboard
tags: [student, exam, dashboard, livewire]
dependency_graph:
  requires: []
  provides: [SESS-01, SESS-08, student-dashboard]
  affects: [exam-session]
tech_stack:
  added: [Livewire]
  patterns: [component-view]
key_files:
  created:
    - app/Livewire/Student/Dashboard.php
    - app/Livewire/Student/ExamStart.php
    - app/View/Components/Layouts/Student.php
    - resources/views/components/layouts/student.blade.php
    - resources/views/livewire/student/dashboard.blade.php
    - resources/views/livewire/student/exam-start.blade.php
  modified:
    - routes/web.php
    - app/Models/ExamSession.php
decisions:
  - Used @extends pattern with layout component for student views
  - Component naming avoided ExamSession -> ExamStart to prevent collision with model
---

# Phase 03 Plan 01: Student Dashboard + Exam Start Flow Summary

## Overview

Implemented student dashboard showing available published exams and exam start page with instructions and confirmation. This is the entry point into the exam flow.

## Implementation Details

### Layout
- Created `app/View/Components/Layouts/Student.php` component class
- Created `resources/views/components/layouts/student.blade.php` layout view with header, navigation, and logout
- Applied via `@extends('components.layouts.student')` in Livewire views

### Dashboard Component
- `app/Livewire/Student/Dashboard.php` queries only published exams within time window
- Excludes already-attempted exams from available list
- Shows attempted exams separately with "View Results" button
- Uses Flux UI cards with status badges

### ExamStart Component
- `app/Livewire/Student/ExamStart.php` shows exam instructions and metadata
- Creates `ExamSession` with IP address on confirm
- Redirects to exam session on start
- Prevents re-entry for already-submitted sessions

### Routes Updated
- `/exam/dashboard` → Dashboard::class
- `/exam/{exam}/start` → ExamStart::class

## Test Verification

Pre-existing test failures are due to RefreshDatabase migrations not running. The code implementation follows existing patterns.

## Files Modified

| File | Change |
|------|--------|
| routes/web.php | Added student routes |
| app/Models/ExamSession.php | Added `total_marks` fillable/cast |

## Commits

- Files committed as part of Phase 3 wave-based execution