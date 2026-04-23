# Requirements: ExamShield

**Defined:** 2026-04-23
**Core Value:** A secured, local exam environment where students can take proctored exams with anti-cheat guards, automatic grading for objective questions, and manual review workflow for short answers — all without internet dependency.

## v1 Requirements

### Authentication & Users

- [ ] **AUTH-01**: Existing Fortify authentication (login, register, password reset)
- [ ] **AUTH-02**: Email verification after signup
- [ ] **AUTH-03**: Two-factor authentication support
- [ ] **USER-01**: User role system (admin, student)
- [ ] **USER-02**: User can be activated/deactivated by admin
- [ ] **USER-03**: Student number field for users (optional)

### Exams

- [ ] **EXAM-01**: Admin can create exam with title, description, duration, instructions
- [ ] **EXAM-02**: Admin can set exam time window (starts_at, ends_at)
- [ ] **EXAM-03**: Admin can enable/disable question shuffling
- [ ] **EXAM-04**: Admin can enable/disable option shufflings
- [ ] **EXAM-05**: Admin can publish/unpublish exams
- [ ] **EXAM-06**: Admin can set passing marks threshold
- [ ] **EXAM-07**: Admin can delete exams

### Questions

- [ ] **QUES-01**: Admin can add MCQ questions (2-6 options, one correct)
- [ ] **QUES-02**: Admin can add True/False questions
- [ ] **QUES-03**: Admin can add Short Answer questions (manual grading)
- [ ] **QUES-04**: Admin can add Code Snippet questions with syntax highlighting
- [ ] **QUES-05**: Admin can set marks per question
- [ ] **QUES-06**: Admin can reorder questions
- [ ] **QUES-07**: Admin can delete questions

### Exam Session

- [ ] **SESS-01**: Student can view available published exams
- [ ] **SESS-01**: Student sees instructions and duration before starting
- [ ] **SESS-02**: Session is created on exam start with IP binding
- [ ] **SESS-03**: Student can navigate between questions (prev/next)
- [ ] **SESS-04**: Answers auto-save on selection/change
- [ ] **SESS-05**: Server-computed countdown timer with auto-submit
- [ ] **SESS-06**: Student can flag questions for review
- [ ] **SESS-07**: Prevent re-entry after submission
- [ ] **SESS-08**: Results page shows score, percentage, pass/fail

### Anti-Cheat

- [ ] **GUARD-01**: Detect and log tab/window switches
- [ ] **GUARD-02**: Block copy/paste attempts
- [ ] **GUARD-03**: Disable right-click context menu
- [ ] **GUARD-04**: Block keyboard shortcuts (Ctrl+U, F12, etc.)
- [ ] **GUARD-05**: Disable text selection on exam content
- [ ] **GUARD-06**: Detect IP changes mid-session and flag
- [ ] **GUARD-07**: Auto-submit after 3 tab switches (configurable)
- [ ] **GUARD-08**: Flag session based on threshold violations

### Results & Grading

- [ ] **GRAD-01**: Auto-grade MCQ and True/False on submit
- [ ] **GRAD-02**: Leave short answers as "needs review"
- [ ] **GRAD-03**: Admin can view per-session results
- [ ] **GRAD-04**: Admin can manually grade short answers
- [ ] **GRAD-05**: Admin can view all results per exam
- [ ] **GRAD-06**: Export results to CSV
- [ ] **GRAD-07**: Basic analytics (avg score, pass rate, question difficulty)

### Admin Dashboard

- [ ] **ADMIN-01**: Admin dashboard with exam overview
- [ ] **ADMIN-02**: Student account management (create, delete)
- [ ] **ADMIN-03**: Live monitor of active sessions
- [ ] **ADMIN-04**: View flagged sessions

### Student Management

- [ ] **STUD-01**: Admin can create student accounts
- [ ] **STUD-02**: Admin can bulk create students
- [ ] **STUD-03**: Admin can deactivate/reactivate students
- [ ] **STUD-04**: Admin can reset student passwords

### Middleware & Security

- [ ] **MW-01**: Enforce local network access only
- [ ] **MW-02**: Role-based route access (admin vs student)
- [ ] **MW-03**: Ensure active student status before exam

## v2 Requirements

### Advanced Features

- **ADV-01**: Question bank for reusable questions across exams
- **ADV-02**: Random question selection from pool
- **ADV-03**: Random student assignment to different question sets
- **ADV-04**: Extended analytics (question difficulty analysis)
- **ADV-05**: Email notifications to students

### Exam Types

- **TYPE-01**: Practice mode (no time limit, unlimited attempts)
- **TYPE-02**: Timed practice with instant feedback

## Out of Scope

| Feature | Reason |
|---------|--------|
| Internet-based exams | Core value is local-only |
| OAuth/SSO login | Email/password sufficient |
| Real-time video proctoring | Too complex, high bandwidth |
| Payment processing | Free for club use |
| Mobile app | Web-first, desktop primary |
| Multiple simultaneous exams | One exam per session |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| AUTH-01 to AUTH-03 | Starter Kit | Complete |
| USER-01 to USER-03 | Phase 1 | Pending |
| EXAM-01 to EXAM-07 | Phase 2 | Pending |
| QUES-01 to QUES-07 | Phase 2 | Pending |
| SESS-01 to SESS-08 | Phase 3 | Pending |
| GUARD-01 to GUARD-08 | Phase 4 | Pending |
| GRAD-01 to GRAD-07 | Phase 5 | Pending |
| ADMIN-01 to ADMIN-04 | Phase 5 | Pending |
| STUD-01 to STUD-04 | Phase 2 | Pending |
| MW-01 to MW-03 | Phase 1 | Pending |
| ADV-01 to ADV-05 | v2 | Deferred |
| TYPE-01 to TYPE-02 | v2 | Deferred |

**Coverage:**
- v1 requirements: 42 total
- Mapped to phases: 42
- Unmapped: 0 ✓

---
*Requirements defined: 2026-04-23*
*Last updated: 2026-04-23 after /gsd-new-project initialization*