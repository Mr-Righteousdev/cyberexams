# Phase 7: Exam Import/Export

**Goal:** JSON-based import/export for exam questions with queue-based processing and stats overview

## Decisions (locked)

| Decision | Choice |
|----------|--------|
| Queue driver | Database queue (async) |
| Error handling | Skip failed questions, add the rest |
| Duplicate handling | Skip silently if question text exists |
| Max questions | No limit |
| Notifications | Laravel database notifications (bell icon) + toast |
| Stats page URL | `/admin/exams/{id}/stats` |

## JSON Format

```json
{
  "exam_title": "Midterm Exam",
  "version": 1,
  "questions": [
    {
      "type": "mcq",
      "question_text": "What is 2+2?",
      "marks": 5,
      "code_block": null,
      "language": null,
      "options": [
        { "option_text": "3", "is_correct": false },
        { "option_text": "4", "is_correct": true }
      ]
    },
    {
      "type": "true_false",
      "question_text": "The earth is flat.",
      "marks": 2,
      "options": [
        { "option_text": "True", "is_correct": false },
        { "option_text": "False", "is_correct": true }
      ]
    },
    {
      "type": "short_answer",
      "question_text": "What is the capital of France?",
      "marks": 5,
      "options": []
    },
    {
      "type": "code_snippet",
      "question_text": "Fix this function",
      "marks": 10,
      "code_block": "function add(a,b){return a-b}",
      "language": "javascript",
      "options": []
    }
  ]
}
```

## Requirements Covered

| ID | Requirement |
|----|------------|
| IMP-01 | Admin can view exam stats (attendance, question count) |
| IMP-02 | Admin can export exam questions to JSON |
| IMP-03 | Admin can import questions from JSON file |
| IMP-04 | Import queued with progress feedback |
| IMP-05 | Failed imports shown with error details |
| IMP-06 | Admin can download sample JSON template |

## Canonical refs

- `app/Models/Exam.php`
- `app/Models/Question.php`
- `app/Models/Option.php`
- `app/Models/Notification.php`
- `app/Livewire/Admin/QuestionIndex.php`
- `resources/views/livewire/admin/question-index.blade.php`
- `routes/web.php`