# Survey State Diagram

## State Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         SURVEY LIFECYCLE                             │
└─────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │ NOT_STARTED  │  ← Initial state when page loads
    │              │
    │ • Form: 🔒   │  (Disabled, grayed out)
    │ • Banner: 🔵 │  (Blue "Start Survey" banner)
    │ • Button: ▶️  │  (Green "Start Survey" button)
    └──────┬───────┘
           │
           │ User clicks "Start Survey"
           │
           ▼
    ┌──────────────┐
    │ IN_PROGRESS  │  ← Survey is active
    │              │
    │ • Form: ✏️   │  (Enabled, editable)
    │ • Auto-save  │  (Every 30 seconds)
    │ • Buttons:   │  (Save Draft, End & Preview)
    └──────┬───────┘
           │
           │ User clicks "End & Preview"
           │
           ▼
    ┌──────────────┐
    │   ENDED      │  ← Preview mode
    │              │
    │ • Form: 👁️   │  (Disabled, preview only)
    │ • Banner: 🟡 │  (Yellow "Preview Mode" banner)
    │ • Buttons:   │  (Edit Survey, Submit Survey)
    └──┬───────┬───┘
       │       │
       │       │ User clicks "Submit Survey"
       │       │
       │       ▼
       │    ┌──────────────┐
       │    │  SUBMITTED   │  ← Awaiting approval
       │    │              │
       │    │ • Redirect   │  (To sites list)
       │    │ • Status:    │  (Pending approval)
       │    └──────┬───────┘
       │           │
       │           │ Admin reviews
       │           │
       │      ┌────┴────┐
       │      │         │
       │      ▼         ▼
       │   ┌─────┐  ┌─────────┐
       │   │APPR │  │REJECTED │
       │   │OVED │  │         │
       │   │     │  │ • Back  │
       │   │ 🔒  │  │   to    │
       │   │     │  │ EDIT    │
       │   └─────┘  └────┬────┘
       │                 │
       │                 │
       │ User clicks     │
       │ "Edit Survey"   │
       │                 │
       └─────────────────┘
           (Returns to IN_PROGRESS)
```

---

## State Details

### 🔵 NOT_STARTED
**Visual:**
```
┌─────────────────────────────────────────────────────────┐
│ 🔵 Ready to Begin Survey                                │
│    Click "Start Survey" to begin. Your progress will    │
│    be automatically saved.                [Start Survey]│
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Form Fields (All Disabled - Grayed Out)                 │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ No of Floors: [___________] (disabled)              │ │
│ │ SLP Camera:   [___________] (disabled)              │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ [Cancel]                              [▶️ Start Survey] │
└─────────────────────────────────────────────────────────┘
```

**Database:**
- No record exists yet OR
- Record exists with `is_draft = 1` and `survey_started_at = NULL`

---

### ✏️ IN_PROGRESS
**Visual:**
```
┌─────────────────────────────────────────────────────────┐
│ Form Fields (All Enabled)                               │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ No of Floors: [____3______] ✓                       │ │
│ │ SLP Camera:   [____5______] ✓                       │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ [Cancel] ✓ Last saved: 2 minutes ago                   │
│                        [💾 Save Draft] [⏹️ End & Preview]│
└─────────────────────────────────────────────────────────┘
```

**Database:**
- `is_draft = 1`
- `survey_started_at` has timestamp
- `survey_ended_at = NULL`
- `form_data` contains JSON
- `last_saved_at` updates every 30 seconds

**Auto-Save:**
- Runs every 30 seconds
- Console: "Auto-saving draft..."
- Updates `last_saved_at`

---

### 👁️ ENDED (Preview Mode)
**Visual:**
```
┌─────────────────────────────────────────────────────────┐
│ 🟡 Preview Mode                                         │
│    Review your answers below. Click "Edit Survey" to    │
│    make changes or "Submit Survey" to finalize.         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Form Fields (Disabled - Preview Only)                   │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ No of Floors: [____3______] (preview)               │ │
│ │ SLP Camera:   [____5______] (preview)               │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ [Cancel]                                                │
│                      [✏️ Edit Survey] [✅ Submit Survey] │
└─────────────────────────────────────────────────────────┘
```

**Database:**
- `is_draft = 1`
- `survey_started_at` has timestamp
- `survey_ended_at` has timestamp
- `form_data` contains JSON

---

### ✅ SUBMITTED
**Visual:**
```
Alert: "Survey submitted successfully!"
Redirect to: sites/index.php
```

**Database:**
- `is_draft = 0`
- `survey_status = 'submitted'`
- `submitted_date` has timestamp
- `approval_status = 'pending'` or NULL

---

### 🔒 APPROVED
**Visual:**
```
┌─────────────────────────────────────────────────────────┐
│ 🟢 Survey Approved                                      │
│    This survey has been approved and is now locked.     │
│    No changes can be made.                              │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Form Fields (Completely Locked)                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ No of Floors: [____3______] (locked)                │ │
│ │ SLP Camera:   [____5______] (locked)                │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ [Cancel]                      [🔒 Approved & Locked]    │
└─────────────────────────────────────────────────────────┘
```

**Database:**
- `is_draft = 0`
- `approval_status = 'approved'`
- `approved_by` has user ID
- `approved_at` has timestamp

---

### ❌ REJECTED
**Visual:**
```
Same as IN_PROGRESS state
User can edit and resubmit
```

**Database:**
- `is_draft = 1` (reset to draft)
- `approval_status = 'rejected'`
- `rejection_reason` contains text

---

## Button Visibility Matrix

| State | Start Survey | Save Draft | End & Preview | Edit Survey | Submit Survey | Approved Badge |
|-------|-------------|------------|---------------|-------------|---------------|----------------|
| NOT_STARTED | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| IN_PROGRESS | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |
| ENDED | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ |
| SUBMITTED | (redirects) | ❌ | ❌ | ❌ | ❌ | ❌ |
| APPROVED | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| REJECTED | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## Form State Matrix

| State | Form Enabled | Opacity | Pointer Events | Can Edit |
|-------|-------------|---------|----------------|----------|
| NOT_STARTED | ❌ | 60% | none | ❌ |
| IN_PROGRESS | ✅ | 100% | auto | ✅ |
| ENDED | ❌ | 75% | none | ❌ |
| APPROVED | ❌ | 100% | none | ❌ |
| REJECTED | ✅ | 100% | auto | ✅ |

---

## Banner Visibility Matrix

| State | Blue Banner | Yellow Banner | Green Banner |
|-------|------------|---------------|--------------|
| NOT_STARTED | ✅ | ❌ | ❌ |
| IN_PROGRESS | ❌ | ❌ | ❌ |
| ENDED | ❌ | ✅ | ❌ |
| APPROVED | ❌ | ❌ | ✅ |
| REJECTED | ❌ | ❌ | ❌ |

---

## Auto-Save Behavior

```
┌─────────────────────────────────────────────────────────┐
│                    AUTO-SAVE LIFECYCLE                   │
└─────────────────────────────────────────────────────────┘

NOT_STARTED
    │
    │ Click "Start Survey"
    │
    ▼
IN_PROGRESS ──────────────────────────────────────────┐
    │                                                  │
    │ setInterval(() => saveDraft(true), 30000)       │
    │                                                  │
    ├──► [30s] ──► Auto-save ──► Update last_saved_at │
    │                                                  │
    ├──► [30s] ──► Auto-save ──► Update last_saved_at │
    │                                                  │
    ├──► [30s] ──► Auto-save ──► Update last_saved_at │
    │                                                  │
    │ Click "End & Preview"                           │
    │                                                  │
    ▼                                                  │
ENDED ◄────────────────────────────────────────────────┘
    │
    │ clearInterval(autoSaveInterval)
    │ Auto-save STOPS
    │
    ▼
(No more auto-saves)
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│                      DATA FLOW                           │
└─────────────────────────────────────────────────────────┘

User Input
    │
    ▼
┌─────────────┐
│ Vue formData│ (Reactive object)
└──────┬──────┘
       │
       │ Every 30 seconds OR manual save
       │
       ▼
┌─────────────┐
│ saveDraft() │ (Method)
└──────┬──────┘
       │
       │ POST request
       │
       ▼
┌─────────────────────┐
│ survey-progress.php │ (API)
└──────┬──────────────┘
       │
       │ JSON.stringify(formData)
       │
       ▼
┌─────────────────────────────┐
│ dynamic_survey_responses    │ (Database)
│ • form_data (JSON)          │
│ • last_saved_at (DATETIME)  │
└─────────────────────────────┘

On page reload:
┌─────────────────────────────┐
│ dynamic_survey_responses    │
└──────┬──────────────────────┘
       │
       │ SELECT form_data
       │
       ▼
┌─────────────────────┐
│ checkSurveyStatus() │ (Method)
└──────┬──────────────┘
       │
       │ JSON.parse(form_data)
       │
       ▼
┌─────────────┐
│ Vue formData│ (Restored)
└─────────────┘
```

---

## Timeline Example

```
User Session Timeline:

10:00 AM - User opens survey page (NOT_STARTED)
10:01 AM - User clicks "Start Survey" (IN_PROGRESS)
10:01 AM - Auto-save starts
10:01:30 - Auto-save #1
10:02:00 - Auto-save #2
10:02:30 - Auto-save #3
10:03 AM - User clicks "Save Draft" (manual save)
10:05 AM - User closes browser
         - (Data is saved in database)

11:00 AM - User reopens survey page
         - Alert: "Resuming your draft survey"
         - All data restored (IN_PROGRESS)
11:00:30 - Auto-save resumes
11:05 AM - User clicks "End & Preview" (ENDED)
         - Auto-save stops
11:06 AM - User reviews data
11:07 AM - User clicks "Submit Survey" (SUBMITTED)
         - Redirects to sites list

Later:
Admin approves survey (APPROVED)
User can view but not edit
```

---

## Error Handling

```
┌─────────────────────────────────────────────────────────┐
│                   ERROR SCENARIOS                        │
└─────────────────────────────────────────────────────────┘

Network Error During Auto-Save:
    │
    ├─► Console: "Failed to save draft"
    ├─► No alert (silent failure)
    ├─► User can continue working
    └─► Next auto-save will retry

Network Error During Manual Save:
    │
    ├─► Alert: "Failed to save draft"
    ├─► User can retry
    └─► Data remains in Vue formData

Network Error During Submit:
    │
    ├─► Alert: "Failed to submit survey"
    ├─► User remains in preview mode
    └─► Can retry submission

Survey Already Approved:
    │
    ├─► API returns error
    ├─► Alert: "Survey is already approved and cannot be modified"
    └─► Page reloads to show locked state
```

This diagram provides a complete visual reference for understanding the survey tracking system!
