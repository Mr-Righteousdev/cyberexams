# Roadmap: ExamShield

**Defined:** 2026-04-23
**Project:** ExamShield — Local Network Exam Platform

## Phase Overview

| Phase | Name | Requirements | Duration |
|-------|------|-------------|----------|
| Phase 1 | Foundation | 7 | 2 days |
| Phase 2 | Exam Builder | 14 | 3 days |
| Phase 3 | Exam Session Engine | 8 | 4 days |
| Phase 4 | Anti-Cheat Guards | 8 | 2 days |
| Phase 5 | Results & Admin | 7 | 3 days |
| Phase 6 | Polish & QA | 6 | 2 days |

**Total:** 6 phases, ~16 days

---

## Phase 1 — Foundation

**Goal:** Set up database schema, middleware, layouts, and core routing

### Requirements Covered

| ID | Requirement |
|----|------------|
| USER-01 | User role system (admin, student) |
| USER-02 | User can be activated/deactivated by admin |
| USER-03 | Student number field for users |
| STUD-01 | Admin can create student accounts |
| STUD-02 | Admin can bulk create students |
| STUD-03 | Admin can deactivate/reactivate students |
| STUD-04 | Admin can reset student passwords |
| MW-01 | Enforce local network access only |
| MW-02 | Role-based route access (admin vs student) |
| MW-03 | Ensure active student status before exam |

### Tasks

1. [X] Create migrations:
   - [X] Add `role` column to users (enum: admin, student)
   - [X] Add `student_number` column to users
   - [X] Add `is_active` column to users
   - [X] Create `exams` table
   - [X] Create `questions` table
   - [X] Create `options` table
   - [X] Create `exam_sessions` table
   - [X] Create `answers` table
   - [X] Create `activity_logs` table
2. [X] Run migrations
3. [X] Create models with relationships
4. [X] Create middleware:
   - [X] EnforceLocalNetwork
   - [X] CheckRole
   - [X] EnsureStudentActive
5. [X] Register middleware aliases
6. [X] Create route groups (admin, student)
7. [X] Create layouts:
   - [X] Admin layout with navigation
   - [X] Student exam layout (locked down)
8. [X] Create network blocked error page
9. [X] Create AdminSeeder
10. [ ] Test network guard from external IP

**Success Criteria:**
- [X] Database tables created
- [X] Admin can log in and access admin panel
- [X] Network guard blocks non-local IPs
- [X] Role-based access works

---

## Phase 2 — Exam Builder

**Goal:** Admin can create and manage exams with questions

### Requirements Covered

| ID | Requirement |
|----|------------|
| EXAM-01 | Admin can create exam with title, description, duration, instructions |
| EXAM-02 | Admin can set exam time window |
| EXAM-03 | Admin can enable/disable question shuffling |
| EXAM-04 | Admin can enable/disable option shufflings |
| EXAM-05 | Admin can publish/unpublish exams |
| EXAM-06 | Admin can set passing marks threshold |
| EXAM-07 | Admin can delete exams |
| QUES-01 | Admin can add MCQ questions |
| QUES-02 | Admin can add True/False questions |
| QUES-03 | Admin can add Short Answer questions |
| QUES-04 | Admin can add Code Snippet questions |
| QUES-05 | Admin can set marks per question |
| QUES-06 | Admin can reorder questions |
| QUES-07 | Admin can delete questions |

### Tasks

1. [X] Exam CRUD:
   - [X] Exam index (list published/unpublished)
   - [X] Exam create form
   - [X] Exam edit form
   - [X] Exam delete
2. [X] Exam publish/unpublish toggle
3. [X] Question CRUD per exam:
   - [X] Question create with type selector
   - [X] Question edit
   - [X] Question delete
4. [X] MCQ form:
   - [X] Question text field
   - [X] Dynamic option rows (min 2, max 6)
   - [X] Mark correct option
5. [X] True/False auto-options
6. [X] Short Answer form
7. [X] Code Snippet form:
   - [X] Code block textarea
   - [X] Language selector
   - [X] Expected answer or options
8. [X] Question ordering (up/down)
9. [ ] Question bank view
10. [X] Student management:
    - [X] Create single student
    - [X] Bulk create students
    - [X] Deactivate/reactivate
    - [X] Reset password
11. [ ] Exam preview (as student)

**Success Criteria:**
- [X] Admin can create full exam with all question types
- [X] Students can see published exams
- [X] Student accounts can be managed

---

## Phase 3 — Exam Session Engine

**Goal:** Student can take exam with auto-save, timer, and anti-cheat UI

### Requirements Covered

| ID | Requirement |
|----|------------|
| SESS-01 | Student can view available published exams |
| SESS-02 | Student sees instructions and duration before starting |
| SESS-03 | Session created on exam start with IP binding |
| SESS-04 | Student can navigate between questions |
| SESS-05 | Answers auto-save on selection/change |
| SESS-06 | Server-computed countdown timer with auto-submit |
| SESS-07 | Student can flag questions for review |
| SESS-08 | Prevent re-entry after submission |
| SESS-09 | Results page shows score, percentage, pass/fail |

### Tasks

1. [X] Student dashboard: available exams list
2. [X] Exam start page:
   - [X] Instructions display
   - [X] Duration info
   - [X] Confirm button
3. [X] ExamSession Livewire component:
   - [X] Session initialization (shuffle if enabled)
   - [X] Question navigation (prev/next + pills)
   - [X] Per-type rendering:
     - [X] MCQ radio buttons
     - [X] T/F toggle buttons
     - [X] Short answer textarea
     - [X] Code snippet + highlight.js
   - [X] Auto-save on change
   - [X] Server-computed timer
   - [X] Auto-submit at zero
   - [X] beforeunload warning
4. [X] IP verification on every request
5. [X] Block re-entry if submitted
6. [X] Results page:
   - [X] Score display
   - [X] Percentage
   - [X] Pass/fail status
   - [X] Per-question breakdown

**Plans:** 3 plans

Plans:
- [ ] 03-01-PLAN.md — Student dashboard and exam start flow
- [ ] 03-02-PLAN.md — Core ExamSession Livewire component with timer, navigation, auto-save
- [ ] 03-03-PLAN.md — Results page with GradingService and per-question breakdown

### Success Criteria

- [X] Student can complete full exam
- [X] Answers persist
- [X] Timer auto-submits at deadline
- [X] Results show correctly

---

## Phase 4 — Anti-Cheat Guards

**Goal:** Detect and log suspicious behavior, auto-submit on violations

### Requirements Covered

| ID | Requirement |
|----|------------|
| GUARD-01 | Detect and log tab/window switches |
| GUARD-02 | Block copy/paste attempts |
| GUARD-03 | Disable right-click context menu |
| GUARD-04 | Block keyboard shortcuts |
| GUARD-05 | Disable text selection on exam content |
| GUARD-06 | Detect IP changes mid-session |
| GUARD-07 | Auto-submit after 3 tab switches |
| GUARD-08 | Flag session based on violations |

### Tasks

1. [ ] Alpine.js guard component:
   - [ ] visibilitychange listener
   - [ ] Copy/paste block
   - [ ] Right-click disable
   - [ ] Keyboard shortcut block (Ctrl+U, F12, etc.)
   - [ ] Text selection disable (CSS)
2. [ ] Tab switch handling:
   - [ ] Log every switch
   - [ ] Show warning overlay
   - [ ] Auto-submit after 3 (configurable)
3. [ ] IP change detection:
   - [ ] Compare current IP to session IP
   - [ ] Flag session if changed
   - [ ] Log IP change event
4. [ ] Flagging logic:
   - [ ] Track violations
   - [ ] Update is_flagged
   - [ ] Store flag_reason
5. [ ] ActivityLog persistence

**Success Criteria:**
- [ ] All guards fire correctly
- [ ] Events logged to database
- [ ] Admin can see flagged sessions

**Plans:** 3 plans

Plans:
- [ ] 06-01-PLAN.md — Error pages, empty-state component, responsive layout audit
- [ ] 06-02-PLAN.md — Flash messages + loading states for all admin CRUD actions
- [ ] 06-03-PLAN.md — Security smoke tests (HTTP + Playwright) and security hook verification

---

## Phase 5 — Results & Admin Dashboard

**Goal:** Grading, manual review, export, analytics

### Requirements Covered

| ID | Requirement |
|----|------------|
| ADMIN-01 | Admin dashboard with exam overview |
| ADMIN-02 | Student account management |
| ADMIN-03 | Live monitor of active sessions |
| ADMIN-04 | View flagged sessions |
| GRAD-01 | Auto-grade MCQ/TF on submit |
| GRAD-02 | Leave short answers as "needs review" |
| GRAD-03 | Admin can view per-session results |
| GRAD-04 | Admin can manually grade short answers |
| GRAD-05 | Admin can view all results per exam |
| GRAD-06 | Export results to CSV |
| GRAD-07 | Basic analytics |

### Tasks

1. [X] GradingService implementation:
   - [X] Auto-grade MCQ/TF
   - [X] Leave short answer null
   - [X] Calculate percentage
   - [X] Determine pass/fail
2. [X] Per-session result view (admin):
   - [X] Student info
   - [X] Score/percentage
   - [X] Per-question answers
   - [X] Correct vs student answer
3. [X] Manual grading UI:
   - [X] List short answers needing review
   - [X] Grant marks form
4. [X] Results list per exam:
   - [X] All students
   - [X] Scores
   - [X] Flags
   - [X] Time taken
5. [X] CSV export
6. [X] Live monitor:
   - [X] Polling active sessions
   - [X] Questions answered
   - [X] Time remaining
   - [X] Flag status
7. [X] Basic analytics:
   - [X] Average score
   - [X] Pass rate
   - [X] Question difficulty (% correct)

**Success Criteria:**
- [X] Full grading workflow works
- [X] CSV exports correctly
- [X] Analytics display

**Plans:** 4 plans

Plans:
- [X] 05-01-PLAN.md — Dashboard, CSV export, analytics (ADMIN-01, GRAD-06, GRAD-07)
- [X] 05-02-PLAN.md — Per-session and per-exam results views (GRAD-03, GRAD-05)
- [X] 05-03-PLAN.md — Manual grading queue and workflow (GRAD-02, GRAD-04)
- [X] 05-04-PLAN.md — Live monitor, flagged sessions (ADMIN-03, ADMIN-04)

---

## Phase 6 — Polish & QA

**Goal:** Error handling, responsive UI, security verification

### Tasks

1. [X] Responsive UI pass:
   - [X] Test on laptop sizes
   - [X] Fix layout issues
2. [X] Error states:
   - [X] Exam expired
   - [X] Already submitted
   - [X] Network blocked
   - [X] Session not found
3. [X] Empty states:
   - [X] No exams published
   - [X] No results
4. [X] Flash messages for all admin actions
5. [X] Loading states (wire:loading)
6. [X] Security QA:
   - [X] Student cannot access admin routes
   - [X] Duplicate exam start blocked
   - [X] Auto-submit fires reliably
   - [X] IP binding works
   - [X] Network guard blocks external

### Requirements Covered

| ID | Requirement |
|----|------------|
| All prior | All v1 requirements should now work |

**Success Criteria:**
- [X] All phases complete
- [X] Security verification passes
- [X] Ready for production use

**Plans:** 3 plans

Plans:
- [ ] 06-01-PLAN.md — Error pages, empty-state component, responsive layout audit
- [ ] 06-02-PLAN.md — Flash messages + loading states for all admin CRUD actions
- [ ] 06-03-PLAN.md — Security smoke tests (HTTP + Playwright) and security hook verification

---

## Phase Dependencies

```
Phase 1 (Foundation)
    ↓
Phase 2 (Exam Builder)     ← Requires Phase 1
    ↓
Phase 3 (Exam Session)   ← Requires Phase 1, 2
    ↓
Phase 4 (Anti-Cheat)    ← Requires Phase 3
    ↓
Phase 5 (Results)       ← Requires Phase 3
    ↓
Phase 6 (Polish)        ← Requires all prior
```

---

## Next Step

Run `/gsd-plan-phase 1` to start Phase 1 execution.

---
*Roadmap defined: 2026-04-23*
*Last updated: 2026-04-23 after /gsd-new-project initialization*