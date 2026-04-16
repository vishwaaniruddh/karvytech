# Survey Tracking Feature - Testing Guide

## Overview
This guide will help you test the complete survey tracking workflow including start/end/preview/submit functionality with auto-save and draft persistence.

## Prerequisites
- Database migration has been executed successfully
- Survey form with "General Information" and "Floor Wise Camera Details" sections exists
- Site with delegation ID is available for testing

## Test Scenarios

### 1. Initial Page Load (Not Started State)

**URL:** `http://localhost/project/admin/site-survey2.php?site_id=625`

**Expected Behavior:**
- ✅ Page loads successfully
- ✅ "Start Survey" banner appears at the top with:
  - Blue background with left border
  - Large green "Start Survey" button on the right
  - Clear instructions about auto-save
- ✅ Form fields are disabled (grayed out with reduced opacity)
- ✅ Fixed action bar at bottom shows "Start Survey" button
- ✅ User cannot input any data until clicking "Start Survey"

**Visual Check:**
- Banner should be prominent and eye-catching
- Button should be large and clearly clickable
- Form should appear disabled/locked

---

### 2. Starting the Survey

**Action:** Click the "Start Survey" button (either in banner or bottom bar)

**Expected Behavior:**
- ✅ Alert shows: "Survey started!" (or "Resuming your draft survey" if resuming)
- ✅ Banner disappears
- ✅ Form becomes enabled (full opacity, fields are editable)
- ✅ Bottom action bar updates to show:
  - "Save Draft" button (gray)
  - "End & Preview" button (blue)
  - "Last saved" indicator (appears after first auto-save)
- ✅ Console log shows: Survey started with response ID
- ✅ Auto-save starts (every 30 seconds)

**Database Check:**
```sql
SELECT id, survey_started_at, is_draft, survey_status 
FROM dynamic_survey_responses 
WHERE delegation_id = [YOUR_DELEGATION_ID]
ORDER BY id DESC LIMIT 1;
```
Should show: `is_draft = 1`, `survey_status = 'draft'`, `survey_started_at` has timestamp

---

### 3. Filling Out the Form

**Action:** Enter data in various fields

**Test Data:**
- General Information → No of Floors: 3
- Floor Wise Camera Details should replicate 3 times
- Fill in camera counts (should default to 0)
- Add some text fields, dates, etc.

**Expected Behavior:**
- ✅ "No of Floors" field triggers replication of "Floor Wise Camera Details"
- ✅ Each floor section shows "( #1 )", "( #2 )", "( #3 )"
- ✅ Camera fields (SLP Camera, Analytical Camera, Blind Spot) default to 0
- ✅ All fields are editable
- ✅ Data persists in Vue's formData object

---

### 4. Auto-Save Functionality

**Action:** Wait 30 seconds after entering data

**Expected Behavior:**
- ✅ Console log shows: "Auto-saving draft..." (every 30 seconds)
- ✅ "Last saved" indicator updates with timestamp
- ✅ No alert/popup (silent auto-save)
- ✅ Timestamp shows relative time (e.g., "Just now", "2 minutes ago")

**Database Check:**
```sql
SELECT last_saved_at, form_data 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
Should show: `last_saved_at` updates every 30 seconds, `form_data` contains JSON with your inputs

---

### 5. Manual Save Draft

**Action:** Click "Save Draft" button

**Expected Behavior:**
- ✅ Button shows "Saving..." briefly
- ✅ Alert shows: "Draft saved successfully!"
- ✅ "Last saved" indicator updates
- ✅ Data is persisted to database

---

### 6. Close Browser and Resume

**Action:** 
1. Close the browser tab (or navigate away)
2. Reopen the same URL: `http://localhost/project/admin/site-survey2.php?site_id=625`

**Expected Behavior:**
- ✅ Alert shows: "Resuming your draft survey"
- ✅ Form loads with all previously entered data
- ✅ Survey status is "in_progress"
- ✅ Auto-save resumes automatically
- ✅ "Last saved" shows previous save time
- ✅ All fields are editable

**This proves draft persistence works!**

---

### 7. End Survey (Enter Preview Mode)

**Action:** Click "End & Preview" button

**Expected Behavior:**
- ✅ Data is saved first (auto-save)
- ✅ Alert shows: "Survey ended. Please review your answers before submitting."
- ✅ Page scrolls to top
- ✅ Yellow "Preview Mode" banner appears
- ✅ Form becomes semi-transparent (opacity-75) and disabled
- ✅ Bottom action bar shows:
  - "Edit Survey" button (gray)
  - "Submit Survey" button (green)
- ✅ Auto-save stops
- ✅ User can review all entered data but cannot edit

**Database Check:**
```sql
SELECT survey_ended_at, is_draft 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
Should show: `survey_ended_at` has timestamp, `is_draft = 1` (still draft until submitted)

---

### 8. Edit from Preview Mode

**Action:** Click "Edit Survey" button

**Expected Behavior:**
- ✅ Preview banner disappears
- ✅ Form becomes editable again (full opacity)
- ✅ Bottom action bar returns to:
  - "Save Draft" button
  - "End & Preview" button
- ✅ Auto-save resumes
- ✅ Page scrolls to top
- ✅ User can make changes

---

### 9. Final Submission

**Action:** 
1. Click "End & Preview" again
2. Review the data
3. Click "Submit Survey" button

**Expected Behavior:**
- ✅ Confirmation dialog: "Are you sure you want to submit this survey? You cannot edit it after submission."
- ✅ If confirmed:
  - Button shows "Submitting..."
  - Alert shows: "Survey submitted successfully!"
  - Redirects to sites list page
- ✅ If cancelled: stays in preview mode

**Database Check:**
```sql
SELECT is_draft, survey_status, submitted_date 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
Should show: `is_draft = 0`, `survey_status = 'submitted'`, `submitted_date` has timestamp

---

### 10. Approved Survey (Locked State)

**Action:** 
1. Manually update database to simulate approval:
```sql
UPDATE dynamic_survey_responses 
SET approval_status = 'approved', 
    approved_at = NOW(), 
    approved_by = 1 
WHERE id = [RESPONSE_ID];
```
2. Reload the page

**Expected Behavior:**
- ✅ Green "Survey Approved" banner appears
- ✅ Form is completely disabled (pointer-events-none)
- ✅ Bottom action bar shows only "Approved & Locked" badge
- ✅ No edit or submit buttons visible
- ✅ User cannot make any changes

---

### 11. Rejected Survey (Can Edit Again)

**Action:**
1. Update database to simulate rejection:
```sql
UPDATE dynamic_survey_responses 
SET approval_status = 'rejected', 
    is_draft = 1,
    rejection_reason = 'Please update camera counts' 
WHERE id = [RESPONSE_ID];
```
2. Reload the page

**Expected Behavior:**
- ✅ Form loads in editable state
- ✅ User can make changes
- ✅ Can save, end, and resubmit

---

## Fixed Action Bar Testing

### Visual Checks:
- ✅ Action bar stays at bottom of screen when scrolling
- ✅ Has white background with top border and shadow
- ✅ Contains "Cancel" button on left
- ✅ Contains action buttons on right
- ✅ "Last saved" indicator appears between Cancel and action buttons
- ✅ Buttons change based on survey status
- ✅ Bar has z-index of 50 (appears above other content)
- ✅ Content has bottom padding (pb-32) so it's not hidden behind bar

### Responsive Check:
- ✅ Works on desktop (wide screen)
- ✅ Works on tablet (medium screen)
- ✅ Works on mobile (small screen)

---

## Console Debugging

Open browser console (F12) and check for:

1. **On page load:**
   ```
   Loaded sections: ["General Information", "Floor Wise Camera Details", ...]
   ```

2. **When changing No of Floors:**
   ```
   Floor Wise Camera Details - No of Floors value: 3 parsed: 3
   ```

3. **During auto-save:**
   ```
   Auto-saving draft...
   Draft saved at: [timestamp]
   ```

4. **On survey start:**
   ```
   Survey started with response ID: [id]
   ```

---

## Common Issues and Solutions

### Issue: Start Survey button not clickable
**Solution:** Check that the button has proper z-index and is not covered by another element. The banner button should be clearly visible and clickable.

### Issue: Form not disabled before starting
**Solution:** Check that `surveyStatus === 'not_started'` and the form has class `pointer-events-none opacity-60`

### Issue: Auto-save not working
**Solution:** 
- Check console for errors
- Verify `autoSaveInterval` is set
- Check that `saveDraft(true)` is being called every 30 seconds

### Issue: Data not persisting after browser close
**Solution:**
- Check that `checkSurveyStatus()` is called in `mounted()`
- Verify database has saved `form_data` JSON
- Check that saved data is being loaded into `formData` object

### Issue: Fixed buttons overlap content
**Solution:** Ensure form has `pb-32` class and there's a spacer div at the bottom

### Issue: Floor sections not replicating
**Solution:**
- Check that "No of Floors" field exists in "General Information" section
- Verify `getRepeatCount()` function is detecting the section by title
- Check console for debug logs

---

## Success Criteria

All tests pass if:
- ✅ User cannot input data before starting survey
- ✅ Start button is prominent and clickable
- ✅ Form enables after starting
- ✅ Auto-save works every 30 seconds
- ✅ Manual save works on demand
- ✅ Data persists after closing browser
- ✅ Preview mode disables form
- ✅ Can edit from preview mode
- ✅ Final submission works and redirects
- ✅ Approved surveys are locked
- ✅ Fixed action bar stays at bottom
- ✅ All buttons show/hide based on status
- ✅ Floor replication works based on "No of Floors"
- ✅ Default values (0) appear in camera fields

---

## Next Steps After Testing

If all tests pass:
1. Test with different survey forms
2. Test with multiple users simultaneously
3. Test with large amounts of data
4. Test file uploads in draft mode
5. Test network interruptions during auto-save
6. Performance test with many repeated sections

If tests fail:
1. Check browser console for errors
2. Check network tab for API call failures
3. Verify database schema is correct
4. Check PHP error logs
5. Verify all files are saved correctly
