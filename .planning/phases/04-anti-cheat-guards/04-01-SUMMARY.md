---
phase: 04-anti-cheat-guards
plan: 01
subsystem: exam-taking
tags: [anti-cheat, guards, frontend, security]
dependency_graph:
  requires:
    - exam-session
  provides:
    - blocked-actions-logged
  affects:
    - exam-taking-blade
tech_stack:
  added:
    - Alpine.js event handlers
    - Tailwind select-none utility
  patterns:
    - x-on:event.window event delegation for copy/paste/keydown
key_files:
  created: []
  modified:
    - resources/views/livewire/student/exam-taking.blade.php
decisions:
  - Used x-on:event.window pattern for global event handling
  - Used Flux toast for blocked action feedback
  - select-none applied to entire exam container
metrics:
  duration: ~8 minutes
  completed_date: 2026-04-23
---

# Phase 4 Plan 1: Frontend Anti-Cheat Guards Summary

## Overview

Implemented frontend guards to prevent casual cheating through browser interactions during exams.

## One-Liner

Copy/paste, right-click, keyboard shortcuts blocked with notifications; text selection disabled via CSS.

## Implemented Features

### Task 1: Anti-Cheat Event Guards

- **copy, paste, cut** blocked via `x-on:copy.window.prevent`, `x-on:paste.window.prevent`, `x-on:cut.window.prevent`
- **right-click** blocked via `x-on:contextmenu.window.prevent`
- **keyboard shortcuts** blocked: Ctrl+U, Ctrl+S, Ctrl+P, Ctrl+Shift+I, F12, Escape
- Flux toast notifications shown on each blocked action
- Logged to ActivityLog via `handleBlockedAction()`

### Task 2: Text Selection Disabled

- Added `select-none` Tailwind utility to exam container
- CSS `user-select: none` prevents text highlighting/selection
- Answer inputs remain interactive (radio buttons, textareas still work)

## Verification

- Right-click shows blocked toast message
- Ctrl+C/Ctrl+V shows blocked toast message  
- Ctrl+U, Ctrl+S, F12 are blocked
- Cannot select text with mouse drag on question content
- Page fully functional for answering questions

## Deviation

None - plan executed as written.

## Threat Surface

| Flag | File | Description |
|------|------|-------------|
| new_event_handlers | exam-taking.blade.php | Global window event listeners for anti-cheat |