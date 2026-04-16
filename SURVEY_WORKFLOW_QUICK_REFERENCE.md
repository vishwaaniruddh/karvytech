# Survey Workflow - Quick Reference

## Survey States

| State | Description | Form Status | Visible Buttons |
|-------|-------------|-------------|-----------------|
| **not_started** | Initial state, survey not begun | Disabled (grayed out) | Start Survey |
| **in_progress** | Survey active, user filling form | Enabled | Save Draft, End & Preview |
| **ended** | Survey ended, in preview mode | Disabled (preview) | Edit Survey, Submit Survey |
| **submitted** | Survey submitted, awaiting approval | Redirects to view page | N/A |
| **approved** | Survey approved by admin | Locked (read-only) | Approved & Locked badge |
| **rejected** | Survey rejected, needs revision | Enabled | Save Draft, End & Preview |

---

## User Actions

### Starting a Survey
1. Navigate to survey page
2. See prominent "Start Survey" banner at top
3. Click green "Start Survey" button
4. Form becomes editable
5. Auto-save begins (every 30 seconds)

### Working on Survey
- Fill in fields normally
- Data auto-saves every 30 seconds
- Click "Save Draft" for manual save
- "Last saved" indicator shows save time
- Can close browser anytime - progress is saved

### Ending Survey
1. Click "End & Preview" button
2. Review all entered data
3. Choose:
   - "Edit Survey" to make changes
   - "Submit Survey" to finalize

### Submitting Survey
1. Confirm submission dialog
2. Survey is submitted
3. Redirects to sites list
4. Cannot edit after submission (unless rejected)

---

## Key Features

### Auto-Save
- Runs every 30 seconds when survey is in progress
- Silent (no popup)
- Updates "Last saved" indicator
- Stops when survey is ended or submitted

### Draft Persistence
- Close browser anytime
- Reopen same URL
- Survey resumes where you left off
- All data is preserved

### Preview Mode
- Review all answers before submitting
- Form is disabled (cannot edit)
- Can go back to edit if needed
- Must submit from preview mode

### Approval Lock
- Once approved, survey is locked
- No edits allowed
- Green "Approved" banner shows
- Only view mode available

---

## Visual Indicators

### Banners
- **Blue Banner** (top): Ready to start - shows before starting
- **Yellow Banner** (top): Preview mode - review before submit
- **Green Banner** (top): Approved - survey is locked

### Action Bar (bottom, fixed)
- Always visible at bottom of screen
- Shows relevant buttons based on state
- "Last saved" indicator on left
- Action buttons on right

### Form States
- **Disabled** (opacity 60%): Before starting
- **Enabled** (full opacity): During editing
- **Preview** (opacity 75%): During preview
- **Locked** (disabled): When approved

---

## Database Fields

| Field | Purpose |
|-------|---------|
| `survey_started_at` | When user clicked "Start Survey" |
| `survey_ended_at` | When user clicked "End & Preview" |
| `last_saved_at` | Last auto-save or manual save time |
| `submitted_date` | When user clicked "Submit Survey" |
| `is_draft` | 1 = draft, 0 = submitted |
| `approval_status` | null/pending/approved/rejected |
| `form_data` | JSON of all form field values |
| `site_master_data` | JSON of site information |

---

## API Endpoints

| Action | Method | Purpose |
|--------|--------|---------|
| `start` | POST | Start new survey or resume draft |
| `save_draft` | POST | Save progress (manual or auto) |
| `end_survey` | POST | Mark survey as ended (preview) |
| `submit` | POST | Final submission |
| `get_status` | GET | Check current survey state |

---

## Testing Checklist

- [ ] Start Survey button is clickable and prominent
- [ ] Form is disabled before starting
- [ ] Form enables after starting
- [ ] Auto-save works every 30 seconds
- [ ] Manual save works
- [ ] Data persists after closing browser
- [ ] Preview mode disables form
- [ ] Can edit from preview
- [ ] Submit works and redirects
- [ ] Approved surveys are locked
- [ ] Fixed action bar stays at bottom
- [ ] Floor replication works
- [ ] Default values appear

---

## Troubleshooting

### Form not disabled before starting?
Check: `surveyStatus === 'not_started'` and form has `pointer-events-none opacity-60`

### Auto-save not working?
Check: Console for errors, verify `autoSaveInterval` is set

### Data not persisting?
Check: `checkSurveyStatus()` is called in `mounted()`, database has `form_data`

### Buttons not showing?
Check: `surveyStatus` value, button visibility conditions

### Floor sections not appearing?
Check: "No of Floors" field value, `getRepeatCount()` function, console logs

---

## File Locations

- **Survey Form**: `admin/site-survey2.php`
- **API Endpoint**: `api/survey-progress.php`
- **Database Migration**: `database/migrations/add_survey_tracking_fields.sql`
- **Test Guide**: `SURVEY_TRACKING_TEST_GUIDE.md`
- **Implementation Doc**: `SURVEY_TRACKING_IMPLEMENTATION.md`
