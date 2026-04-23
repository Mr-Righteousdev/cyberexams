# PROJECT.md

## What This Is

ExamShield is a local-network-only web examination platform for running secure, monitored exams within clubs or classes. The admin creates and manages all exams. Students log in, take the exam within a time limit, and results are auto-graded and stored. Runs on localhost/local subnet — no internet access required.

## Core Value

A secured, local exam environment where students can take proctored exams with anti-cheat guards, automatic grading for objective questions, and manual review workflow for short answers — all without internet dependency.

## Requirements

### Active

- [ ] Build Phase 1: Foundation (migrations, middleware, admin/student layouts)
- [ ] Build Phase 2: Exam Builder (exam CRUD, question CRUD, student management)
- [ ] Build Phase 3: Exam Session Engine (student exam flow, auto-save, timer)
- [ ] Build Phase 4: Anti-Cheat Guards (tab-switch, copy/paste, IP binding)
- [ ] Build Phase 5: Results & Admin Dashboard (grading, export, analytics)
- [ ] Build Phase 6: Polish & QA (responsive, error states, security QA)

### Out of Scope

- [Internet exam access] — Local-network-only by design
- [OAuth login] — Email/password via Fortify sufficient
- [Real-time video proctoring] — Too complex for club use
- [Mobile app] — Web-first, desktop/laptop primary

## Context

- **Starting point**: Laravel Livewire starter kit with Fortify authentication already installed
- **Tech stack**: Laravel 13, Livewire 4, Alpine.js, Tailwind CSS v4, MySQL
- **Existing code**: Authentication (login, register, password reset, 2FA), user settings (profile, appearance, security)
- **Database**: MySQL (already configured in codebase)

## Constraints

- **[Tech]**: Must enforce local network access only — no internet exposure
- **[Tech]**: Server-computed timer only — never trust client-side timing
- **[Security]**: IP-bound sessions — detect IP changes mid-exam
- **[Security]**: One session per student per exam — prevent duplicate attempts

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Use Laravel Livewire starter kit | Pre-built auth via Fortify, Livewire 4, Flux UI components | ✓ Good |
| Local network enforcement | Core requirement for secure club exams | ✓ Good |

---

## Current Phase

**Project phase:** Phase 6 — Polish & QA

**Current focus:** Completed

## State

| Phase | Goal | Status |
|-------|------|--------|
| 1 | Foundation | Not started |
| 2 | Exam Builder | Not started |
| 3 | Exam Session Engine | Not started |
| 4 | Anti-Cheat Guards | Not started |
| 5 | Results & Admin | Complete |
| 6 | Polish & QA | Complete |

## Recent Changes

- [2026-04-23] Project initialized via /gsd-new-project
- [2026-04-23] PROJECT.md, REQUIREMENTS.md, ROADMAP.md created
- [2026-04-23] 6-phase roadmap defined

## Dependencies

- **Phase 1** → Foundation: migrations, middleware, layouts
- **Phase 2** → Exam Builder: needs Phase 1
- **Phase 3** → Exam Session: needs Phase 1, 2
- **Phase 4** → Anti-Cheat: needs Phase 3
- **Phase 5** → Results: needs Phase 3
- **Phase 6** → Polish: needs all prior

---

*Last updated: 2026-04-23 after /gsd-new-project initialization*
*See: .planning/PROJECT.md (updated 2026-04-23)*
*See: .planning/REQUIREMENTS.md (updated 2026-04-23)*
*See: .planning/ROADMAP.md (updated 2026-04-23)*