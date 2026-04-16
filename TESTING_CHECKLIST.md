# Survey Tracking - Testing Checklist

Use this checklist to verify all features are working correctly.

---

## Pre-Testing Setup

- [ ] Database migration has been executed successfully
- [ ] Survey form exists with "General Information" and "Floor Wise Camera Details" sections
- [ ] Test site with delegation ID is available (e.g., site_id=625)
- [ ] Browser console is open (F12) for debugging

---

## Test 1: Initial Page Load

**URL:** `http://localhost/project/admin/site-survey2.php?site_id=625`

### Visual Checks
- [ ] Page loads without errors
- [ ] Blue "Start Survey" banner appears at top of form
- [ ] Banner has:
  - [ ] Blue background with left border
  - [ ] Play icon on left
  - [ ] "Ready to Begin Survey" heading
  - [ ] Instructions about auto-save
  - [ ] Large green "Start Survey" button on right
- [ ] Form fields are grayed out (opacity 60%)
- [ ] Cannot click or type in any form field
- [ ] Fixed action bar at bottom shows:
  - [ ] "Cancel" button on left
  - [ ] "Start Survey" button on right
  - [ ] White background with shadow

### Console Checks
- [ ] No JavaScript errors
- [ ] Console shows: "Loaded sections: [...]"

### Database Check
```sql
SELECT * FROM dynamic_survey_responses 
WHERE delegation_id = [YOUR_DELEGATION_ID];
```
- [ ] No record exists OR existing record has `is_draft = 1`

**Status:** ✅ Pass / ❌ Fail

---

## Test 2: Start Survey

**Action:** Click "Start Survey" button (either in banner or bottom bar)

### Visual Checks
- [ ] Alert appears: "Survey started!" or "Resuming your draft survey"
- [ ] Blue banner disappears
- [ ] Form fields become enabled (full opacity, white background)
- [ ] Can click and type in form fields
- [ ] Bottom action bar updates to show:
  - [ ] "Cancel" button on left
  - [ ] "Save Draft" button (gray)
  - [ ] "End & Preview" button (blue)

### Console Checks
- [ ] Console shows: "Survey started with response ID: [id]"
- [ ] No errors

### Database Check
```sql
SELECT id, survey_started_at, is_draft, survey_status, last_saved_at
FROM dynamic_survey_responses 
WHERE delegation_id = [YOUR_DELEGATION_ID]
ORDER BY id DESC LIMIT 1;
```
- [ ] New record created with:
  - [ ] `survey_started_at` has timestamp
  - [ ] `is_draft = 1`
  - [ ] `survey_status = 'draft'`

**Status:** ✅ Pass / ❌ Fail

---

## Test 3: Form Functionality

**Action:** Fill in form fields

### Test Data
- [ ] General Information → No of Floors: Enter "3"
- [ ] Verify "Floor Wise Camera Details" section appears 3 times
- [ ] Each floor section shows "( #1 )", "( #2 )", "( #3 )"
- [ ] Camera fields default to "0":
  - [ ] SLP Camera: 0
  - [ ] Analytical Camera: 0
  - [ ] Blind Spot: 0
- [ ] Change some values (e.g., Floor 1 SLP Camera: 5)
- [ ] Fill in other fields (text, dates, etc.)

### Visual Checks
- [ ] Floor sections replicate correctly based on "No of Floors"
- [ ] Default values appear in camera fields
- [ ] Can edit all fields
- [ ] Changes are reflected immediately

### Console Checks
- [ ] Console shows: "Floor Wise Camera Details - No of Floors value: 3 parsed: 3"
- [ ] No errors about undefined fields

**Status:** ✅ Pass / ❌ Fail

---

## Test 4: Auto-Save

**Action:** Wait 30 seconds after entering data

### Visual Checks
- [ ] "Last saved" indicator appears in bottom action bar
- [ ] Shows relative time (e.g., "Just now", "30 seconds ago")
- [ ] No popup or alert (silent auto-save)

### Console Checks
- [ ] Every 30 seconds, console shows: "Auto-saving draft..."
- [ ] Console shows: "Draft saved at: [timestamp]"

### Database Check
```sql
SELECT last_saved_at, form_data 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
- [ ] `last_saved_at` updates every 30 seconds
- [ ] `form_data` contains JSON with your input values

**Wait for 3 auto-saves (90 seconds total)**
- [ ] Auto-save #1 at 30 seconds
- [ ] Auto-save #2 at 60 seconds
- [ ] Auto-save #3 at 90 seconds

**Status:** ✅ Pass / ❌ Fail

---

## Test 5: Manual Save Draft

**Action:** Click "Save Draft" button

### Visual Checks
- [ ] Button text changes to "Saving..." briefly
- [ ] Alert appears: "Draft saved successfully!"
- [ ] "Last saved" indicator updates to "Just now"

### Database Check
```sql
SELECT last_saved_at, form_data 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
- [ ] `last_saved_at` has current timestamp
- [ ] `form_data` contains all your input values

**Status:** ✅ Pass / ❌ Fail

---

## Test 6: Draft Persistence (Critical Test!)

**Action:** Close browser and reopen

### Steps
1. [ ] Note the current data in form
2. [ ] Close the browser tab completely
3. [ ] Wait 10 seconds
4. [ ] Reopen: `http://localhost/project/admin/site-survey2.php?site_id=625`

### Visual Checks
- [ ] Alert appears: "Resuming your draft survey"
- [ ] Form loads with all previously entered data
- [ ] "No of Floors" value is preserved
- [ ] Floor sections appear correctly
- [ ] Camera values are preserved
- [ ] All other fields have saved values
- [ ] Form is enabled (can edit)
- [ ] Bottom action bar shows "Save Draft" and "End & Preview"
- [ ] "Last saved" shows previous save time

### Console Checks
- [ ] Console shows: "Survey started with response ID: [id]"
- [ ] Console shows: "Loaded sections: [...]"
- [ ] Auto-save resumes (check after 30 seconds)

**This is the most important test - data must persist!**

**Status:** ✅ Pass / ❌ Fail

---

## Test 7: End Survey (Preview Mode)

**Action:** Click "End & Preview" button

### Visual Checks
- [ ] Alert appears: "Survey ended. Please review your answers before submitting."
- [ ] Page scrolls to top
- [ ] Yellow "Preview Mode" banner appears at top
- [ ] Banner says: "Review your answers below. Click 'Edit Survey' to make changes or 'Submit Survey' to finalize."
- [ ] Form fields are disabled (semi-transparent, opacity 75%)
- [ ] Cannot edit any fields
- [ ] Bottom action bar shows:
  - [ ] "Cancel" button on left
  - [ ] "Edit Survey" button (gray)
  - [ ] "Submit Survey" button (green)
- [ ] "Last saved" indicator still visible

### Console Checks
- [ ] Console shows: "Survey ended"
- [ ] No errors

### Database Check
```sql
SELECT survey_ended_at, is_draft 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
- [ ] `survey_ended_at` has timestamp
- [ ] `is_draft = 1` (still draft until submitted)

**Status:** ✅ Pass / ❌ Fail

---

## Test 8: Edit from Preview

**Action:** Click "Edit Survey" button

### Visual Checks
- [ ] Yellow banner disappears
- [ ] Form becomes enabled again (full opacity)
- [ ] Can edit all fields
- [ ] Bottom action bar returns to:
  - [ ] "Save Draft" button
  - [ ] "End & Preview" button
- [ ] Page scrolls to top

### Console Checks
- [ ] Console shows: "Returning to edit mode"
- [ ] Auto-save resumes (check after 30 seconds)

**Status:** ✅ Pass / ❌ Fail

---

## Test 9: Final Submission

**Action:** 
1. Click "End & Preview" again
2. Review data in preview mode
3. Click "Submit Survey" button

### Visual Checks
- [ ] Confirmation dialog appears: "Are you sure you want to submit this survey? You cannot edit it after submission."
- [ ] If "Cancel": stays in preview mode
- [ ] If "OK":
  - [ ] Button text changes to "Submitting..."
  - [ ] Alert appears: "Survey submitted successfully!"
  - [ ] Redirects to sites list page

### Database Check
```sql
SELECT is_draft, survey_status, submitted_date 
FROM dynamic_survey_responses 
WHERE id = [RESPONSE_ID];
```
- [ ] `is_draft = 0`
- [ ] `survey_status = 'submitted'`
- [ ] `submitted_date` has timestamp

**Status:** ✅ Pass / ❌ Fail

---

## Test 10: Approved Survey (Locked)

**Action:** Simulate approval and reload page

### Steps
1. [ ] Run SQL to approve:
```sql
UPDATE dynamic_survey_responses 
SET approval_status = 'approved', 
    approved_at = NOW(), 
    approved_by = 1 
WHERE id = [RESPONSE_ID];
```
2. [ ] Reload page: `http://localhost/project/admin/site-survey2.php?site_id=625`

### Visual Checks
- [ ] Green "Survey Approved" banner appears at top
- [ ] Banner says: "This survey has been approved and is now locked. No changes can be made."
- [ ] Form is completely disabled
- [ ] Cannot click or edit any fields
- [ ] Bottom action bar shows only:
  - [ ] "Cancel" button on left
  - [ ] "Approved & Locked" badge (green) on right
- [ ] No edit or submit buttons visible

**Status:** ✅ Pass / ❌ Fail

---

## Test 11: Rejected Survey

**Action:** Simulate rejection and reload page

### Steps
1. [ ] Run SQL to reject:
```sql
UPDATE dynamic_survey_responses 
SET approval_status = 'rejected', 
    is_draft = 1,
    rejection_reason = 'Please update camera counts for Floor 2' 
WHERE id = [RESPONSE_ID];
```
2. [ ] Reload page: `http://localhost/project/admin/site-survey2.php?site_id=625`

### Visual Checks
- [ ] Form loads in editable state
- [ ] Can make changes
- [ ] Bottom action bar shows:
  - [ ] "Save Draft" button
  - [ ] "End & Preview" button
- [ ] Can save, end, and resubmit

**Status:** ✅ Pass / ❌ Fail

---

## Test 12: Fixed Action Bar

**Action:** Scroll up and down the page

### Visual Checks
- [ ] Action bar stays fixed at bottom of screen
- [ ] Doesn't scroll with page content
- [ ] Always visible
- [ ] Has white background with shadow
- [ ] Buttons are always accessible
- [ ] Content has bottom padding so it's not hidden behind bar
- [ ] No overlap with form content

**Status:** ✅ Pass / ❌ Fail

---

## Test 13: Responsive Design

**Action:** Resize browser window

### Desktop (Wide Screen)
- [ ] Banner displays correctly
- [ ] Action bar buttons are side-by-side
- [ ] Form fields use correct widths (half, third, quarter)

### Tablet (Medium Screen)
- [ ] Layout adjusts appropriately
- [ ] Buttons may stack or resize
- [ ] Form remains usable

### Mobile (Small Screen)
- [ ] Banner is readable
- [ ] Buttons stack vertically if needed
- [ ] Form fields stack to full width
- [ ] All features remain accessible

**Status:** ✅ Pass / ❌ Fail

---

## Test 14: Error Handling

### Network Error During Auto-Save
**Action:** Disconnect network, wait 30 seconds

- [ ] Console shows error
- [ ] No alert popup (silent failure)
- [ ] User can continue working
- [ ] Reconnect network
- [ ] Next auto-save succeeds

### Network Error During Manual Save
**Action:** Disconnect network, click "Save Draft"

- [ ] Alert appears: "Failed to save draft"
- [ ] User can retry
- [ ] Data remains in form

### Network Error During Submit
**Action:** Disconnect network, try to submit

- [ ] Alert appears: "Failed to submit survey"
- [ ] User remains in preview mode
- [ ] Can retry after reconnecting

**Status:** ✅ Pass / ❌ Fail

---

## Test 15: Multiple Surveys

**Action:** Test with different sites

### Steps
1. [ ] Complete survey for site_id=625
2. [ ] Open new survey for site_id=626
3. [ ] Verify it's a fresh survey (not_started)
4. [ ] Start and fill in data
5. [ ] Verify both surveys are independent

**Status:** ✅ Pass / ❌ Fail

---

## Overall Test Results

### Critical Features (Must Pass)
- [ ] Test 1: Initial page load
- [ ] Test 2: Start survey
- [ ] Test 3: Form functionality
- [ ] Test 4: Auto-save
- [ ] Test 6: Draft persistence (CRITICAL!)
- [ ] Test 7: Preview mode
- [ ] Test 9: Final submission
- [ ] Test 10: Approved/locked state

### Important Features (Should Pass)
- [ ] Test 5: Manual save
- [ ] Test 8: Edit from preview
- [ ] Test 11: Rejected survey
- [ ] Test 12: Fixed action bar

### Nice-to-Have Features (Good to Pass)
- [ ] Test 13: Responsive design
- [ ] Test 14: Error handling
- [ ] Test 15: Multiple surveys

---

## Final Checklist

- [ ] All critical tests pass
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] Database updates correctly
- [ ] User experience is smooth
- [ ] Visual design is clean
- [ ] Performance is acceptable

---

## If Tests Fail

### Common Issues

**Start Survey button not clickable:**
- Check: Button has proper z-index
- Check: No overlapping elements
- Check: surveyStatus === 'not_started'

**Form not disabled before starting:**
- Check: Form has class `pointer-events-none opacity-60`
- Check: surveyStatus === 'not_started'

**Auto-save not working:**
- Check: Console for errors
- Check: autoSaveInterval is set
- Check: API endpoint is accessible

**Data not persisting:**
- Check: checkSurveyStatus() is called in mounted()
- Check: Database has form_data JSON
- Check: JSON.parse() succeeds

**Floor sections not appearing:**
- Check: "No of Floors" field value
- Check: getRepeatCount() function
- Check: Console logs for debug info

---

## Test Report Template

```
Date: _______________
Tester: _______________
Browser: _______________
Environment: _______________

Critical Tests: ___/8 passed
Important Tests: ___/4 passed
Nice-to-Have Tests: ___/3 passed

Total: ___/15 passed

Issues Found:
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

Notes:
_______________________________________________
_______________________________________________
_______________________________________________

Overall Status: ✅ PASS / ❌ FAIL / ⚠️ PARTIAL
```

---

## Next Steps After All Tests Pass

1. [ ] Test with real users
2. [ ] Test with production data
3. [ ] Performance testing with large forms
4. [ ] Security testing
5. [ ] Accessibility testing
6. [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
7. [ ] Mobile device testing
8. [ ] Load testing (multiple concurrent users)

---

**Good luck with testing! 🚀**
