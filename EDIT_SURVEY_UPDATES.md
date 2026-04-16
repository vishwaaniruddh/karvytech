# Edit Survey Page Updates

## Summary
Updated `shared/edit-survey2.php` to include the same tracking and floor replication features as `admin/site-survey2.php`.

---

## Features Added

### 1. Fixed Action Bar at Bottom
- Sticky bar that stays at bottom of screen
- Contains:
  - Cancel button (left)
  - "Last saved" indicator (center-left)
  - Save Draft button (right)
  - Update Survey button (right)
- White background with shadow and border
- z-index 50 to stay above content

### 2. Auto-Save Functionality
- Runs every 30 seconds automatically
- Silent operation (no popups)
- Updates "Last saved" indicator
- Console logging for debugging
- Uses same API endpoint as main survey page

### 3. Last Saved Indicator
- Shows relative time (e.g., "Just now", "2 minutes ago")
- Green checkmark icon
- Updates after each save

### 4. Manual Save Draft
- Button to save on demand
- Shows "Saving..." state while saving
- Toast notification on success/error
- Uses survey-progress API

### 5. Floor Replication Logic
- **getRepeatCount()** method:
  - Detects "Floor Wise Camera Details" section by title
  - Finds "No of Floors" field in "General Information" section
  - Returns count to replicate section
  - Returns 0 if no floors specified (hides section)
  
- **getFieldKey()** method:
  - Generates unique field keys for repeated sections
  - Format: `fieldId_repeatIndex` (e.g., `405_1`, `405_2`, `405_3`)
  - Returns plain fieldId for non-repeatable sections

- **Template Updates**:
  - Uses `v-for="rIndex in getRepeatCount(section)"` to repeat sections
  - Shows repeat index: "Floor Wise Camera Details ( #1 )"
  - Blue left border for repeatable sections
  - All field references use `getFieldKey(field.id, rIndex, section)`

### 6. Default Value Initialization
- Watch on formData to detect changes
- Initializes default values for repeated sections
- Runs when "No of Floors" changes
- Preserves existing values

### 7. Proper Spacing
- Content has `pb-32` class for bottom padding
- Spacer div at end prevents content from being hidden
- Fixed bar doesn't overlap form content

---

## Technical Implementation

### Vue.js Data Properties
```javascript
{
    sections: [...],           // Form structure
    formData: {...},          // Form field values
    files: {},                // File uploads
    submitting: false,        // Update in progress
    saving: false,            // Save in progress
    lastSavedAt: null,        // Last save timestamp
    autoSaveInterval: null,   // Auto-save timer
    showDeleteModal: false,   // Delete confirmation
    pendingDelete: {...},     // File to delete
    toast: {...}              // Toast notification
}
```

### Key Methods
- `getFieldKey(fieldId, rIndex, section)` - Generate unique field keys
- `getRepeatCount(section)` - Calculate section repetitions
- `saveDraft(isAutoSave)` - Save progress to database
- `formatTime(datetime)` - Format timestamps as relative time
- `updateSurvey()` - Final update submission

### Lifecycle Hooks
- `mounted()` - Start auto-save interval (30 seconds)
- `beforeUnmount()` - Clean up auto-save interval

### Watch
- `formData` (deep) - Initialize default values for repeated sections

---

## Template Structure

### Before (Simple Loop)
```html
<div v-for="section in sections">
    <div v-for="field in section.fields">
        <input v-model="formData[field.id]">
    </div>
</div>
```

### After (With Replication)
```html
<template v-for="section in sections">
    <div v-for="rIndex in getRepeatCount(section)">
        <h4>{{ section.title }} ( #{{ rIndex }} )</h4>
        <div v-for="field in section.fields">
            <input v-model="formData[getFieldKey(field.id, rIndex, section)]">
        </div>
    </div>
</template>
```

---

## How Floor Replication Works

### Step 1: User enters "No of Floors"
```
General Information
├── No of Floors: 3
```

### Step 2: getRepeatCount() detects change
```javascript
getRepeatCount(section) {
    if (section.title === 'floor wise camera details') {
        // Find "No of Floors" field
        const floorsField = findField('No of Floors');
        const count = parseInt(formData[floorsField.id]); // 3
        return count; // Returns 3
    }
}
```

### Step 3: Template repeats section 3 times
```
Floor Wise Camera Details ( #1 )
├── SLP Camera: 0
├── Analytical Camera: 0
├── Blind Spot: 0

Floor Wise Camera Details ( #2 )
├── SLP Camera: 0
├── Analytical Camera: 0
├── Blind Spot: 0

Floor Wise Camera Details ( #3 )
├── SLP Camera: 0
├── Analytical Camera: 0
├── Blind Spot: 0
```

### Step 4: Field keys are unique
```javascript
Floor 1: field_405_1, field_406_1, field_407_1
Floor 2: field_405_2, field_406_2, field_407_2
Floor 3: field_405_3, field_406_3, field_407_3
```

### Step 5: Data is saved with unique keys
```json
{
    "404": "3",           // No of Floors
    "405_1": "5",         // Floor 1 - SLP Camera
    "406_1": "3",         // Floor 1 - Analytical Camera
    "407_1": "2",         // Floor 1 - Blind Spot
    "405_2": "4",         // Floor 2 - SLP Camera
    "406_2": "2",         // Floor 2 - Analytical Camera
    "407_2": "1",         // Floor 2 - Blind Spot
    "405_3": "6",         // Floor 3 - SLP Camera
    "406_3": "4",         // Floor 3 - Analytical Camera
    "407_3": "3"          // Floor 3 - Blind Spot
}
```

---

## Testing Instructions

### Test 1: Floor Replication
1. Open edit page: `http://localhost/project/shared/edit-survey2.php?id=11`
2. Find "No of Floors" field
3. Enter "3"
4. Verify "Floor Wise Camera Details" appears 3 times
5. Each section shows "( #1 )", "( #2 )", "( #3 )"
6. Change to "5" - verify 5 sections appear
7. Change to "0" - verify sections disappear

### Test 2: Auto-Save
1. Open browser console (F12)
2. Edit some fields
3. Wait 30 seconds
4. Console shows: "Auto-saving draft..."
5. "Last saved" indicator updates
6. No popup appears (silent save)

### Test 3: Manual Save
1. Click "Save Draft" button
2. Button shows "Saving..."
3. Toast notification appears: "Draft saved successfully!"
4. "Last saved" indicator updates

### Test 4: Fixed Action Bar
1. Scroll down the page
2. Action bar stays at bottom of screen
3. Always visible
4. Content is not hidden behind bar

### Test 5: Data Persistence
1. Enter data in floor sections
2. Click "Save Draft"
3. Close browser
4. Reopen edit page
5. All data is preserved
6. Floor sections show correct values

---

## Differences from site-survey2.php

| Feature | site-survey2.php | edit-survey2.php |
|---------|------------------|------------------|
| Start Survey Banner | ✅ Yes | ❌ No (already started) |
| Preview Mode | ✅ Yes | ❌ No (direct edit) |
| Auto-Save | ✅ Yes | ✅ Yes |
| Fixed Action Bar | ✅ Yes | ✅ Yes |
| Floor Replication | ✅ Yes | ✅ Yes |
| Default Values | ✅ Yes | ✅ Yes |
| Submit Button | "Submit Survey" | "Update Survey" |

---

## Files Modified

1. `shared/edit-survey2.php` - Complete rewrite with new features

---

## API Endpoints Used

- `POST /api/survey-progress.php?action=save_draft`
  - Parameters: response_id, form_data, site_master
  - Returns: success, saved_at

---

## Console Debugging

Open browser console to see:

```
=== Edit Survey App Mounted ===
Auto-save started (every 30 seconds)
Floor Wise Camera Details - No of Floors value: 3 parsed: 3
Auto-saving draft...
Draft saved at: 2026-04-15 12:30:45
```

---

## Success Criteria

- ✅ Fixed action bar at bottom
- ✅ Auto-save every 30 seconds
- ✅ Last saved indicator
- ✅ Manual save draft button
- ✅ Floor replication based on "No of Floors"
- ✅ Unique field keys for repeated sections
- ✅ Default values initialized
- ✅ Data persists across sessions
- ✅ Toast notifications
- ✅ Proper spacing (no overlap)

---

## Known Limitations

1. File uploads are not saved in drafts (only on final update)
2. No conflict resolution for concurrent edits
3. Auto-save may fail silently if network is down

---

## Future Enhancements

1. Save file uploads in drafts
2. Show unsaved changes indicator
3. Warn before leaving page with unsaved changes
4. Conflict detection for concurrent edits
5. Offline mode with queue

---

The edit survey page now has feature parity with the main survey page for floor replication and auto-save functionality!
