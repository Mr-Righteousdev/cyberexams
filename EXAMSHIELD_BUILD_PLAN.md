# ExamShield — Build Plan
**Cybersecurity & Innovations Club — Exam Platform**
**Stack: Laravel · Livewire · Alpine.js · Tailwind CSS · MySQL**

---

## Table of Contents
1. [Project Overview](#1-project-overview)
2. [Tech Stack & Requirements](#2-tech-stack--requirements)
3. [Database Schema](#3-database-schema)
4. [Project File Structure](#4-project-file-structure)
5. [Routes Plan](#5-routes-plan)
6. [Middleware](#6-middleware)
7. [Models & Relationships](#7-models--relationships)
8. [Livewire Components](#8-livewire-components)
9. [Build Phases](#9-build-phases)
10. [Security Implementation Details](#10-security-implementation-details)
11. [Question Types Spec](#11-question-types-spec)
12. [Anti-Cheat Implementation](#12-anti-cheat-implementation)
13. [Grading Logic](#13-grading-logic)
14. [Environment & Setup](#14-environment--setup)

---

## 1. Project Overview

ExamShield is a local-network-only web examination platform for running secure, monitored exams within the club. It runs on localhost and students must be connected to the same router (local subnet) to access it. The admin creates and manages all exams. Students log in, write the exam within a time limit, and results are auto-graded and stored.

**Core principles:**
- Local network enforcement at the middleware level — no internet access bypasses it
- One exam session per student, IP-bound, non-resumable
- All anti-cheat guards fire on the client and are logged server-side
- Auto-grade objective questions; flag short answers for manual review

---

## 2. Tech Stack & Requirements

| Layer | Technology |
|---|---|
| Backend framework | Laravel 13 |
| Frontend reactivity | Livewire 4 |
| JS interactions | Alpine.js 3 |
| Styling | Tailwind CSS 4 |
| Database | MySQL  |
| Syntax highlighting | highlight.js (CDN) |
| Local dev server | `php artisan serve` on `0.0.0.0:8000` |

**PHP requirement:** PHP 8.2+
**Node requirement:** Node 18+ (for Vite/Tailwind build)

---

## 3. Database Schema

### 3.1 `users`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(255)
email           VARCHAR(255) UNIQUE
password        VARCHAR(255)
role            ENUM('admin', 'student') DEFAULT 'student'
student_number  VARCHAR(100) NULL
is_active       BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### 3.2 `exams`
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
title               VARCHAR(255)
description         TEXT NULL
instructions        TEXT NULL
duration_minutes    INT UNSIGNED        -- exam time limit
total_marks         INT UNSIGNED        -- computed or manually set
passing_marks       INT UNSIGNED NULL
starts_at           DATETIME NULL       -- NULL = open anytime
ends_at             DATETIME NULL       -- NULL = open anytime
shuffle_questions   BOOLEAN DEFAULT TRUE
shuffle_options     BOOLEAN DEFAULT TRUE
is_published        BOOLEAN DEFAULT FALSE
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### 3.3 `questions`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
exam_id         BIGINT UNSIGNED FK → exams.id CASCADE DELETE
type            ENUM('mcq', 'true_false', 'short_answer', 'code_snippet')
question_text   TEXT
code_block      TEXT NULL           -- for code_snippet type: the code to display
code_language   VARCHAR(50) NULL    -- e.g. 'python', 'javascript'
marks           INT UNSIGNED DEFAULT 1
order           INT UNSIGNED DEFAULT 0
explanation     TEXT NULL           -- shown after submission
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### 3.4 `options`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
question_id     BIGINT UNSIGNED FK → questions.id CASCADE DELETE
option_text     TEXT
is_correct      BOOLEAN DEFAULT FALSE
order           INT UNSIGNED DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP
```
> Used for `mcq` and `true_false` types only. `true_false` always has exactly 2 options: "True" and "False".

### 3.5 `exam_sessions`
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
exam_id             BIGINT UNSIGNED FK → exams.id CASCADE DELETE
user_id             BIGINT UNSIGNED FK → users.id CASCADE DELETE
ip_address          VARCHAR(45)         -- bound on session start
started_at          TIMESTAMP
submitted_at        TIMESTAMP NULL      -- NULL = still in progress
is_submitted        BOOLEAN DEFAULT FALSE
is_flagged          BOOLEAN DEFAULT FALSE
flag_reason         TEXT NULL           -- comma-separated flag codes
tab_switch_count    INT DEFAULT 0
score               DECIMAL(8,2) NULL   -- filled on submission
percentage          DECIMAL(5,2) NULL
passed              BOOLEAN NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### 3.6 `answers`
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
session_id          BIGINT UNSIGNED FK → exam_sessions.id CASCADE DELETE
question_id         BIGINT UNSIGNED FK → questions.id CASCADE DELETE
selected_option_id  BIGINT UNSIGNED NULL FK → options.id SET NULL
text_answer         TEXT NULL           -- for short_answer type
is_correct          BOOLEAN NULL        -- NULL = needs manual grading
marks_awarded       DECIMAL(5,2) NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### 3.7 `activity_logs`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
session_id      BIGINT UNSIGNED FK → exam_sessions.id CASCADE DELETE
event_type      ENUM('tab_switch', 'copy_attempt', 'paste_attempt', 'right_click', 'ip_change', 'devtools_open', 'focus_lost', 'auto_submit')
metadata        JSON NULL               -- extra context if needed
occurred_at     TIMESTAMP
```

---

## 4. Project File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ExamController.php
│   │   │   ├── QuestionController.php
│   │   │   ├── StudentController.php
│   │   │   └── ResultController.php
│   │   └── Student/
│   │       ├── DashboardController.php
│   │       └── ExamController.php
│   ├── Middleware/
│   │   ├── EnforceLocalNetwork.php      ← THE KEY GUARD
│   │   ├── EnsureStudentActive.php
│   │   └── PreventExamResume.php
│   └── Requests/
│       ├── StoreExamRequest.php
│       └── StoreQuestionRequest.php
├── Livewire/
│   ├── Admin/
│   │   ├── ExamBuilder.php
│   │   ├── QuestionManager.php
│   │   └── LiveMonitor.php
│   └── Student/
│       ├── ExamSession.php             ← CORE COMPONENT
│       └── ExamTimer.php
├── Models/
│   ├── User.php
│   ├── Exam.php
│   ├── Question.php
│   ├── Option.php
│   ├── ExamSession.php
│   ├── Answer.php
│   └── ActivityLog.php
├── Services/
│   ├── GradingService.php
│   ├── ExamSessionService.php
│   └── ActivityLogService.php
└── Policies/
    └── ExamPolicy.php

resources/views/
├── layouts/
│   ├── admin.blade.php
│   └── exam.blade.php                  ← locked-down layout for students
├── admin/
│   ├── dashboard.blade.php
│   ├── exams/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── students/
│   │   ├── index.blade.php
│   │   └── create.blade.php
│   └── results/
│       ├── index.blade.php
│       └── show.blade.php
└── student/
    ├── dashboard.blade.php
    ├── exam-start.blade.php
    ├── exam-session.blade.php          ← Livewire component view
    └── results.blade.php

database/migrations/
├── ..._create_users_table.php
├── ..._add_role_to_users_table.php
├── ..._create_exams_table.php
├── ..._create_questions_table.php
├── ..._create_options_table.php
├── ..._create_exam_sessions_table.php
├── ..._create_answers_table.php
└── ..._create_activity_logs_table.php

database/seeders/
├── AdminSeeder.php
└── SampleExamSeeder.php
```

---

## 5. Routes Plan

```php
// routes/web.php

// ── Public ─────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/blocked', fn() => view('errors.network-blocked'))->name('network.blocked');

// ── Authenticated (all routes inside local network middleware) ──────────────
Route::middleware(['auth', 'local.network'])->group(function () {

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard',                    [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('exams',                    Admin\ExamController::class);
        Route::post('exams/{exam}/publish',         [Admin\ExamController::class, 'publish'])->name('exams.publish');
        Route::post('exams/{exam}/unpublish',       [Admin\ExamController::class, 'unpublish'])->name('exams.unpublish');

        Route::resource('exams.questions',          Admin\QuestionController::class)->shallow();
        Route::post('questions/reorder',            [Admin\QuestionController::class, 'reorder'])->name('questions.reorder');

        Route::resource('students',                 Admin\StudentController::class)->only(['index','create','store','destroy']);
        Route::post('students/{user}/reset-password', [Admin\StudentController::class, 'resetPassword'])->name('students.reset-password');

        Route::get('results',                       [Admin\ResultController::class, 'index'])->name('results.index');
        Route::get('results/{session}',             [Admin\ResultController::class, 'show'])->name('results.show');
        Route::get('results/export/{exam}',         [Admin\ResultController::class, 'export'])->name('results.export');

        // Livewire real-time monitor
        Route::get('monitor/{exam}',                fn($exam) => view('admin.monitor', compact('exam')))->name('monitor');
    });

    // Student routes
    Route::middleware(['role:student', 'student.active'])->prefix('exam')->name('student.')->group(function () {
        Route::get('/dashboard',                    [Student\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/{exam}/start',                 [Student\ExamController::class, 'start'])->name('exam.start');
        Route::post('/{exam}/begin',                [Student\ExamController::class, 'begin'])->name('exam.begin');
        Route::get('/{session}/session',            [Student\ExamController::class, 'session'])->name('exam.session');
        Route::get('/results/{session}',            [Student\ExamController::class, 'results'])->name('exam.results');
    });
});
```

---

## 6. Middleware

### 6.1 `EnforceLocalNetwork` — the most important one
```php
// app/Http/Middleware/EnforceLocalNetwork.php

public function handle(Request $request, Closure $next): Response
{
    $ip = $request->ip();

    $localRanges = [
        '127.0.0.1',
        '::1',
        '192.168.',
        '10.',
        '172.16.', '172.17.', '172.18.', '172.19.',
        '172.20.', '172.21.', '172.22.', '172.23.',
        '172.24.', '172.25.', '172.26.', '172.27.',
        '172.28.', '172.29.', '172.30.', '172.31.',
    ];

    foreach ($localRanges as $range) {
        if (str_starts_with($ip, $range)) {
            return $next($request);
        }
    }

    return redirect()->route('network.blocked');
}
```

Register in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'local.network'  => \App\Http\Middleware\EnforceLocalNetwork::class,
        'role'           => \App\Http\Middleware\CheckRole::class,
        'student.active' => \App\Http\Middleware\EnsureStudentActive::class,
    ]);
})
```

### 6.2 `CheckRole`
```php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (auth()->user()?->role !== $role) {
        abort(403);
    }
    return $next($request);
}
```

### 6.3 `EnsureStudentActive`
Blocks deactivated students from accessing the platform mid-exam period.
```php
public function handle(Request $request, Closure $next): Response
{
    if (!auth()->user()->is_active) {
        auth()->logout();
        return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
    }
    return $next($request);
}
```

---

## 7. Models & Relationships

```php
// User
hasMany(ExamSession::class)

// Exam
hasMany(Question::class)->orderBy('order')
hasMany(ExamSession::class)

// Question
belongsTo(Exam::class)
hasMany(Option::class)->orderBy('order')

// Option
belongsTo(Question::class)

// ExamSession
belongsTo(User::class)
belongsTo(Exam::class)
hasMany(Answer::class)
hasMany(ActivityLog::class)

// Answer
belongsTo(ExamSession::class)
belongsTo(Question::class)
belongsTo(Option::class)

// ActivityLog
belongsTo(ExamSession::class)
```

**Key model casts:**
```php
// ExamSession
protected $casts = [
    'started_at'   => 'datetime',
    'submitted_at' => 'datetime',
    'is_submitted' => 'boolean',
    'is_flagged'   => 'boolean',
];

// Exam
protected $casts = [
    'starts_at'          => 'datetime',
    'ends_at'            => 'datetime',
    'shuffle_questions'  => 'boolean',
    'shuffle_options'    => 'boolean',
    'is_published'       => 'boolean',
];
```

---

## 8. Livewire Components

### 8.1 `ExamSession` — the heart of the student experience
**Properties:**
```php
public ExamSession $session;
public Exam $exam;
public Collection $questions;      // shuffled on mount, order locked in session
public int $currentIndex = 0;
public array $answers = [];        // [question_id => answer]
public int $secondsRemaining;
public bool $isSubmitted = false;
public bool $isFlagged = false;
```

**Key methods:**
```php
mount(ExamSession $session)     // load session, verify IP matches, load questions
saveAnswer(int $questionId, mixed $answer)   // persist to DB immediately
nextQuestion()
prevQuestion()
submit()                        // final submission, trigger GradingService
autoSubmit()                    // called when timer hits 0
logActivity(string $eventType)  // receives events from Alpine.js
```

**Polling:**
```php
// Auto-save every 30s and sync timer
#[On('tick')]
public function tick(): void
{
    $elapsed = now()->diffInSeconds($this->session->started_at);
    $this->secondsRemaining = max(0, ($this->exam->duration_minutes * 60) - $elapsed);

    if ($this->secondsRemaining <= 0) {
        $this->autoSubmit();
    }
}
```

### 8.2 `LiveMonitor` (Admin)
Shows real-time list of active sessions for an exam with:
- Student name + IP
- Questions answered / total
- Time remaining
- Flag status (red badge if flagged)
- Tab switch count

Uses Livewire polling every 10 seconds.

### 8.3 `ExamBuilder` (Admin)
Handles the drag-and-drop question reordering and inline question creation with live preview.

---

## 9. Build Phases

### Phase 1 — Foundation (Day 1–2)
- [ ] `laravel new examshield`
- [ ] Install Breeze (Blade stack): `php artisan breeze:install`
- [ ] Install Livewire: `composer require livewire/livewire`
- [ ] Configure MySQL in `.env`
- [ ] Create all migrations and run `php artisan migrate`
- [ ] Add `role` and `student_number` columns to users
- [ ] Create `AdminSeeder` (your admin account)
- [ ] Register all middleware aliases
- [ ] Build `EnforceLocalNetwork` middleware and test it
- [ ] Build `CheckRole` middleware
- [ ] Set up basic route groups
- [ ] Create admin layout (`layouts/admin.blade.php`) with Tailwind nav
- [ ] Create student exam layout (`layouts/exam.blade.php`) — stripped down, no nav links
- [ ] Create the `/blocked` network error page
- [ ] Serve on `0.0.0.0`: `php artisan serve --host=0.0.0.0 --port=8000`

**Phase 1 done when:** You can log in as admin from another device on the same network, and be blocked if you try from outside it.

---

### Phase 2 — Exam Builder (Day 3–5)
- [ ] `Exam` CRUD (index, create, edit, delete)
- [ ] Exam publish/unpublish toggle
- [ ] `Question` CRUD with question type selector
- [ ] MCQ question form: question text + dynamic option rows (min 2, max 6) + mark correct
- [ ] True/False question form: auto-creates two options
- [ ] Short answer question form: just question text + marks
- [ ] Code snippet question form: question text + code block textarea (language selector) + answer options or expected answer
- [ ] Question ordering (simple up/down arrows or drag-and-drop)
- [ ] Question bank view — all questions for an exam, with type badges
- [ ] Student management — create student accounts (bulk or one by one)
- [ ] Preview exam as student (read-only)

**Phase 2 done when:** You can build a complete Python basics exam end-to-end from the admin panel.

---

### Phase 3 — Exam Session Engine (Day 6–9)
- [ ] Student dashboard — shows available published exams
- [ ] Exam start page — instructions, duration, confirm button
- [ ] `begin()` method: create `ExamSession` record, bind IP, shuffle questions if enabled, redirect to session
- [ ] Build `ExamSession` Livewire component
  - [ ] Question navigation (prev/next + question number pills)
  - [ ] Per-type answer rendering: MCQ radio, T/F radio, short answer textarea, code snippet (highlight.js)
  - [ ] Auto-save answer on change (no submit button per question — seamless)
  - [ ] Live countdown timer (server-computed remaining time)
  - [ ] Auto-submit when timer hits 0
  - [ ] Prevent navigating away (beforeunload warning)
- [ ] Session IP verification on every Livewire request
- [ ] Block re-entry if session already submitted
- [ ] Results page post-submission (score, percentage, pass/fail, per-question breakdown)

**Phase 3 done when:** A student can fully write and submit an exam with all question types.

---

### Phase 4 — Anti-Cheat Guards (Day 10–11)
All guards use Alpine.js on the exam session page, emit events to Livewire via `$wire.call('logActivity', 'event_type')`.

- [ ] Tab/window switch detection (`visibilitychange` event)
  - [ ] Log every switch
  - [ ] Show warning overlay on return
  - [ ] Auto-submit after 3 switches (configurable)
- [ ] Copy attempt detection (`copy` event on document)
- [ ] Paste attempt detection (`paste` event on document)
- [ ] Right-click disable (`contextmenu` event, `preventDefault()`)
- [ ] Keyboard shortcut block (`Ctrl+C`, `Ctrl+V`, `Ctrl+U`, `F12`, `Ctrl+Shift+I`)
- [ ] Text selection disable on exam area (CSS `user-select: none` on question display)
- [ ] Single active session enforcement — check on every Livewire mount
- [ ] IP change detection — compare current IP to `session.ip_address` on every request
- [ ] Flagging logic — auto-flag session when threshold is crossed, update `is_flagged` and `flag_reason`

**Phase 4 done when:** All guards fire correctly and are logged. Admin can see flag status in real time.

---

### Phase 5 — Results & Admin Dashboard (Day 12–14)
- [ ] `GradingService` — auto-grade MCQ and T/F on submit, mark short answers as pending
- [ ] Per-session result view for admin — question by question, student's answer vs correct answer
- [ ] Manual grading UI for short_answer questions
- [ ] Results list for an exam — all students, scores, flags, time taken
- [ ] Admin live monitor — real-time active sessions view
- [ ] CSV export (exam results table → downloadable CSV)
- [ ] Basic analytics: average score, pass rate, question difficulty (% correct per question)

**Phase 5 done when:** Full exam cycle is complete — build, run, grade, export.

---

### Phase 6 — Polish & QA (Day 15–16)
- [ ] Responsive Tailwind UI pass — ensure it works on laptops of all sizes
- [ ] Error states — expired exam, already submitted, network blocked
- [ ] Empty states — no exams published yet, no results yet
- [ ] Flash messages for all admin actions
- [ ] Loading states on Livewire components (`wire:loading`)
- [ ] Final security QA checklist:
  - [ ] Can a student access admin routes? (should 403)
  - [ ] Can a student start the same exam twice? (should block)
  - [ ] Does auto-submit fire reliably at 0:00?
  - [ ] Does IP binding catch a spoofed request?
  - [ ] Does the network guard block non-local IPs?

---

## 10. Security Implementation Details

### Serving on local network
```bash
# In your terminal — students connect to YOUR_MACHINE_IP:8000
php artisan serve --host=0.0.0.0 --port=8000

# Find your local IP (share this with students)
# Windows: ipconfig | findstr IPv4
# Linux/Mac: hostname -I
```

### Timer — always server-computed
Never trust the client timer. Always calculate remaining time as:
```php
$remaining = ($exam->duration_minutes * 60) - now()->diffInSeconds($session->started_at);
```
The Livewire component polls the server every 60 seconds to re-sync, so a student manipulating browser JS only gets a cosmetic change — the server auto-submits at the real deadline.

### IP Binding
On `begin()`:
```php
$session = ExamSession::create([
    'exam_id'    => $exam->id,
    'user_id'    => auth()->id(),
    'ip_address' => $request->ip(),
    'started_at' => now(),
]);
```
On every Livewire request inside the session, add to `mount()` or a middleware:
```php
if ($request->ip() !== $this->session->ip_address) {
    $this->logActivity('ip_change');
    $this->isFlagged = true;
    $this->session->update(['is_flagged' => true, 'flag_reason' => 'IP address changed mid-session']);
}
```

### One active session per student per exam
In `begin()`, before creating a session:
```php
$existing = ExamSession::where('exam_id', $exam->id)
    ->where('user_id', auth()->id())
    ->first();

if ($existing) {
    if ($existing->is_submitted) {
        return redirect()->route('student.exam.results', $existing);
    }
    // Resume the existing session (or block — your choice)
    return redirect()->route('student.exam.session', $existing);
}
```

---

## 11. Question Types Spec

### MCQ
- 2–6 options, exactly one correct
- Rendered as radio buttons
- Options shuffled if `exam.shuffle_options = true`
- Auto-graded: full marks if correct option selected, 0 otherwise

### True / False
- Always exactly 2 options: "True" and "False"
- Rendered as two large toggle-style buttons
- Auto-graded

### Short Answer
- Student types a free-text response in a `<textarea>`
- NOT auto-graded — admin reviews and awards marks manually
- Admin sees student answer alongside the expected answer (stored in `questions.explanation` or a dedicated `expected_answer` column you can add)

### Code Snippet
- Admin pastes a block of code (Python etc.) in the question form
- Language is selected (python, javascript, etc.)
- Code is displayed to students using highlight.js in a read-only `<pre><code>` block with line numbers
- The actual question (e.g. "What is the output of this code?", "Identify the bug") is asked below the snippet
- Answer can be MCQ, T/F, or short answer — the `code_snippet` type just controls how the question is DISPLAYED, not how it is answered
- `type = 'code_snippet'` always pairs with options OR short answer field

---

## 12. Anti-Cheat Implementation

### Alpine.js guard component (attach to exam session page)
```html
<div
  x-data="examGuard()"
  x-init="init()"
  @visibilitychange.window="onVisibilityChange()"
  @copy.window.prevent="logEvent('copy_attempt')"
  @paste.window.prevent="logEvent('paste_attempt')"
  @contextmenu.window.prevent="logEvent('right_click')"
  @keydown.window="onKeyDown($event)"
>
  <!-- exam content here -->

  <!-- Tab switch warning overlay -->
  <div x-show="showWarning" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 text-center max-w-md">
      <h2 class="text-xl font-semibold text-red-600">Warning</h2>
      <p class="mt-2 text-gray-700">You switched tabs or windows. This has been recorded.</p>
      <p class="mt-1 text-sm text-gray-500">Switches: <span x-text="tabSwitchCount"></span> / 3</p>
      <button @click="showWarning = false" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg">
        Return to Exam
      </button>
    </div>
  </div>
</div>

<script>
function examGuard() {
  return {
    showWarning: false,
    tabSwitchCount: 0,

    init() {
      // Disable text selection on question area
      document.getElementById('question-display').style.userSelect = 'none';
    },

    onVisibilityChange() {
      if (document.hidden) return;
      this.tabSwitchCount++;
      this.showWarning = true;
      this.logEvent('tab_switch');
      if (this.tabSwitchCount >= 3) {
        this.logEvent('auto_submit');
        $wire.call('autoSubmit');
      }
    },

    onKeyDown(e) {
      const blocked = [
        e.ctrlKey && e.key === 'u',
        e.ctrlKey && e.key === 'c',
        e.ctrlKey && e.shiftKey && e.key === 'I',
        e.key === 'F12',
      ];
      if (blocked.some(Boolean)) {
        e.preventDefault();
        this.logEvent('copy_attempt');
      }
    },

    logEvent(type) {
      $wire.call('logActivity', type);
    }
  }
}
</script>
```

---

## 13. Grading Logic

### `GradingService`
```php
// Called on session submission

public function grade(ExamSession $session): void
{
    $totalMarks     = 0;
    $awardedMarks   = 0;

    foreach ($session->answers as $answer) {
        $question = $answer->question;
        $totalMarks += $question->marks;

        if (in_array($question->type, ['mcq', 'true_false'])) {
            $correct = $question->options()->where('is_correct', true)->first();
            $isCorrect = $answer->selected_option_id === $correct?->id;
            $marks = $isCorrect ? $question->marks : 0;

            $answer->update([
                'is_correct'    => $isCorrect,
                'marks_awarded' => $marks,
            ]);
            $awardedMarks += $marks;
        }

        // short_answer: left as null, awaits manual grading
    }

    $percentage = $totalMarks > 0 ? ($awardedMarks / $totalMarks) * 100 : 0;
    $passed     = $session->exam->passing_marks
                    ? $awardedMarks >= $session->exam->passing_marks
                    : $percentage >= 50;

    $session->update([
        'score'          => $awardedMarks,
        'percentage'     => round($percentage, 2),
        'passed'         => $passed,
        'is_submitted'   => true,
        'submitted_at'   => now(),
    ]);
}
```


---

## Quick Reference — Build Checklist

```
PHASE 1 — FOUNDATION
 [ ] Project setup + Breeze + Livewire
 [ ] Migrations + models
 [ ] Middleware (network guard, role, active)
 [ ] Route groups
 [ ] Admin + exam layouts
 [ ] Admin seeder
 [ ] Network blocked page
 [ ] Serve on 0.0.0.0

PHASE 2 — EXAM BUILDER
 [ ] Exam CRUD
 [ ] Question CRUD (all 4 types)
 [ ] Options management
 [ ] Question ordering
 [ ] Student account management
 [ ] Exam preview

PHASE 3 — EXAM SESSION
 [ ] Student dashboard
 [ ] Exam start + begin (create session)
 [ ] ExamSession Livewire component
 [ ] Per-type question rendering
 [ ] Auto-save answers
 [ ] Countdown timer (server-computed)
 [ ] Auto-submit on expiry
 [ ] Block re-entry post-submit
 [ ] Results page

PHASE 4 — ANTI-CHEAT
 [ ] Alpine.js guard component
 [ ] Tab switch detection + overlay + auto-submit at 3
 [ ] Copy/paste/right-click block
 [ ] Keyboard shortcut block
 [ ] Text selection disable
 [ ] Session IP binding + change detection
 [ ] Activity logging
 [ ] Flag threshold logic

PHASE 5 — RESULTS & ADMIN
 [ ] GradingService (auto-grade on submit)
 [ ] Per-session result view
 [ ] Manual grading for short answers
 [ ] Admin results list per exam
 [ ] Live monitor (Livewire polling)
 [ ] CSV export
 [ ] Basic analytics

PHASE 6 — POLISH
 [ ] Error states
 [ ] Empty states
 [ ] Loading states
 [ ] Flash messages
 [ ] Final security QA pass
```

---

*ExamShield — built for the Cybersecurity & Innovations Club*
*Stack: Laravel  · Livewire  · Alpine.js  · Tailwind CSS · MySQL*
