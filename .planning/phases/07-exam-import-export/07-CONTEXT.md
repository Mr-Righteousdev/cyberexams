# Phase 7: Exam Import/Export - Context

**Gathered:** 2026-04-23
**Status:** Ready for planning

<domain>
## Phase Boundary

JSON-based import/export for exam questions with queue-based processing and stats overview.

</domain>

<decisions>
## Implementation Decisions

### Import Behavior
- **D-01:** Queue driver = database queue (async processing)
- **D-02:** Error handling = skip failed questions, add the rest (partial import)
- **D-03:** Duplicate handling = skip silently if question text already exists (case-insensitive)
- **D-04:** Max questions per import = no limit
- **D-05:** Question ordering = append to end of existing questions

### Notifications
- **D-06:** Notification style = Laravel database notifications (bell icon in sidebar) + toast
- **D-07:** Import result shown in modal from notifications page

### Stats Page
- **D-08:** Stats page URL = `/admin/exams/{id}/stats`
- **D-09:** Stats include: question count by type, total/completed/active sessions, pass/fail count, average score, pass rate

### JSON Format
- **D-10:** JSON structure includes `exam_title`, `version: 1`, and full `questions` array
- **D-11:** Each question has: `type`, `question_text`, `marks`, `code_block`, `language`, `options[]`
- **D-12:** Options have: `option_text`, `is_correct`
- **D-13:** Sample template includes 1 example of each question type (MCQ, True/False, Short Answer, Code Snippet)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Models
- `app/Models/Exam.php` — Exam model with questions() and sessions() relationships
- `app/Models/Question.php` — Question model with type, marks, code_block, code_language, options()
- `app/Models/Option.php` — Option model with option_text, is_correct, order

### Existing Components
- `app/Livewire/Admin/QuestionIndex.php` — Existing question listing (reused patterns)
- `app/Livewire/Admin/Dashboard.php` — Stats cards pattern to follow
- `resources/views/livewire/admin/dashboard.blade.php` — Flux UI card patterns
- `resources/views/layouts/app/sidebar.blade.php` — Admin layout with notification sidebar item

### Routes
- `routes/web.php` — Admin routes structure (add stats, export, import, notifications routes)

</canonical_refs>

<codebase_context>
## Existing Code Insights

### Reusable Assets
- `App\Livewire\Admin\Dashboard` — Stats loading pattern (AnalyticsService, map to array)
- `resources/views/livewire/admin/dashboard.blade.php` — Flux card grid pattern (grid-cols-2 md:grid-cols-4)

### Established Patterns
- Livewire components for admin pages
- Flux UI components (flux:card, flux:badge, flux:table, flux:button)
- Route model binding for Exam, Question, Session models
- Notifications stored in database via ->notify()

### Integration Points
- Admin routes under `/admin/exams/{exam}/`
- Sidebar navigation links to new pages
- Notifications stored and retrieved via auth()->user()->notifications()

</codebase_context>

<specifics>
## Specific Ideas

- Stats page should be accessible from "Stats & Import/Export" link on question index page
- Import triggers a toast saying "Import started!" and user is notified via bell icon when done
- Failed questions modal shows question text, type, and error reason
- "View Exam Stats" button in modal links to the stats page

</specifics>

<deferred>
## Deferred Ideas

None

</deferred>

---

*Phase: 07-exam-import-export*
*Context gathered: 2026-04-23*