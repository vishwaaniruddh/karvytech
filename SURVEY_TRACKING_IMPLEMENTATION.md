# Survey Tracking Implementation Guide

## Overview
This document outlines the implementation of survey progress tracking with Start/End/Preview/Submit workflow.

## Database Changes

1. Run the migration: `database/migrations/add_survey_tracking_fields.sql`
   - Adds survey_started_at, survey_ended_at, is_draft, last_saved_at
   - Adds approval_status, approved_by, approved_at, rejection_reason

## API Endpoint

Created: `api/survey-progress.php`

Actions:
- `start` - Start new survey or resume draft
- `save_draft` - Auto-save progress
- `end_survey` - Mark as ended (ready for preview)
- `submit` - Final submission
- `get_status` - Get current survey status

## Frontend Changes Needed in admin/site-survey2.php

### 1. Add Vue Data Properties (in data() section):
```javascript
surveyResponseId: null,
surveyStatus: 'not_started', // not_started, in_progress, ended, submitted, approved
surveyStartedAt: null,
surveyEndedAt: null,
lastSavedAt: null,
isPreviewMode: false,
isLocked: false, // true if approved
autoSaveInterval: null,
```

### 2. Replace Submit Button Section with:
```html
<!-- Survey Control Buttons -->
<div class="flex justify-between items-center mt-8 p-6 bg-gray-50 rounded-lg border-t">
    <div class="flex items-center gap-4">
        <a href="sites/" class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            Cancel
        </a>
        
        <!-- Last Saved Indicator -->
        <span v-if="lastSavedAt" class="text-sm text-gray-500">
            Last saved: {{ formatTime(lastSavedAt) }}
        </span>
    </div>
    
    <div class="flex items-center gap-3">
        <!-- Start Survey Button -->
        <button v-if="surveyStatus === 'not_started'" 
                @click="startSurvey" 
                type="button"
                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
            </svg>
            Start Survey
        </button>
        
        <!-- Save Draft Button (shown during survey) -->
        <button v-if="surveyStatus === 'in_progress'" 
                @click="saveDraft" 
                type="button"
                :disabled="saving"
                class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"></path>
            </svg>
            {{ saving ? 'Saving...' : 'Save Draft' }}
        </button>
        
        <!-- End Survey Button -->
        <button v-if="surveyStatus === 'in_progress'" 
                @click="endSurvey" 
                type="button"
                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
            </svg>
            End & Preview
        </button>
        
        <!-- Edit Button (in preview mode) -->
        <button v-if="surveyStatus === 'ended' && !isLocked" 
                @click="backToEdit" 
                type="button"
                class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            Edit Survey
        </button>
        
        <!-- Submit Button (in preview mode) -->
        <button v-if="surveyStatus === 'ended' && !isLocked" 
                @click="submitSurvey" 
                type="button"
                :disabled="submitting"
                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors disabled:opacity-50">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            {{ submitting ? 'Submitting...' : 'Submit Survey' }}
        </button>
        
        <!-- Approved Badge -->
        <div v-if="isLocked" class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-md">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Approved & Locked
        </div>
    </div>
</div>
```

### 3. Add Methods:
```javascript
async startSurvey() {
    try {
        const response = await fetch('../api/survey-progress.php?action=start', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                delegation_id: '<?php echo $delegationId; ?>',
                survey_form_id: this.surveyFormId,
                site_id: '<?php echo $site['id']; ?>'
            })
        });
        
        const data = await response.json();
        if (data.success) {
            this.surveyResponseId = data.response_id;
            this.surveyStatus = 'in_progress';
            this.surveyStartedAt = data.started_at;
            
            // Start auto-save every 30 seconds
            this.autoSaveInterval = setInterval(() => this.saveDraft(true), 30000);
            
            alert(data.action === 'resumed' ? 'Resuming your draft survey' : 'Survey started!');
        }
    } catch (err) {
        console.error(err);
        alert('Failed to start survey');
    }
},

async saveDraft(isAutoSave = false) {
    if (!this.surveyResponseId) return;
    
    this.saving = true;
    try {
        const response = await fetch('../api/survey-progress.php?action=save_draft', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                response_id: this.surveyResponseId,
                form_data: JSON.stringify(this.formData),
                site_master: JSON.stringify({
                    site_id: '<?php echo htmlspecialchars($site['site_id'] ?? ''); ?>',
                    // ... other site master data
                })
            })
        });
        
        const data = await response.json();
        if (data.success) {
            this.lastSavedAt = data.saved_at;
            if (!isAutoSave) {
                alert('Draft saved successfully!');
            }
        }
    } catch (err) {
        console.error(err);
        if (!isAutoSave) {
            alert('Failed to save draft');
        }
    } finally {
        this.saving = false;
    }
},

async endSurvey() {
    if (!this.surveyResponseId) return;
    
    // Save current state
    await this.saveDraft(true);
    
    try {
        const response = await fetch('../api/survey-progress.php?action=end_survey', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                response_id: this.surveyResponseId,
                form_data: JSON.stringify(this.formData),
                site_master: JSON.stringify({/* site master data */})
            })
        });
        
        const data = await response.json();
        if (data.success) {
            this.surveyStatus = 'ended';
            this.surveyEndedAt = data.ended_at;
            this.isPreviewMode = true;
            
            // Stop auto-save
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
            }
            
            // Scroll to top to show preview
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (err) {
        console.error(err);
        alert('Failed to end survey');
    }
},

backToEdit() {
    this.surveyStatus = 'in_progress';
    this.isPreviewMode = false;
    
    // Restart auto-save
    this.autoSaveInterval = setInterval(() => this.saveDraft(true), 30000);
},

async submitSurvey() {
    if (!this.surveyResponseId) return;
    
    if (!confirm('Are you sure you want to submit this survey? You cannot edit it after submission.')) {
        return;
    }
    
    this.submitting = true;
    try {
        const response = await fetch('../api/survey-progress.php?action=submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                response_id: this.surveyResponseId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Survey submitted successfully!');
            window.location.href = 'sites/';
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error(err);
        alert('Failed to submit survey');
    } finally {
        this.submitting = false;
    }
},

formatTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    return date.toLocaleString();
}
```

### 4. Add mounted() hook to check existing status:
```javascript
async mounted() {
    await this.loadSurvey();
    await this.checkSurveyStatus();
},

async checkSurveyStatus() {
    try {
        const response = await fetch('../api/survey-progress.php?action=get_status&delegation_id=<?php echo $delegationId; ?>');
        const data = await response.json();
        
        if (data.success && data.survey) {
            const survey = data.survey;
            this.surveyResponseId = survey.id;
            
            if (survey.approval_status === 'approved') {
                this.isLocked = true;
                this.surveyStatus = 'approved';
                this.isPreviewMode = true;
            } else if (survey.is_draft == 1) {
                if (survey.survey_ended_at) {
                    this.surveyStatus = 'ended';
                    this.isPreviewMode = true;
                } else {
                    this.surveyStatus = 'in_progress';
                }
                
                // Load saved form data
                if (survey.form_data) {
                    this.formData = JSON.parse(survey.form_data);
                }
            }
            
            this.surveyStartedAt = survey.survey_started_at;
            this.surveyEndedAt = survey.survey_ended_at;
            this.lastSavedAt = survey.last_saved_at;
        }
    } catch (err) {
        console.error('Failed to check survey status:', err);
    }
}
```

### 5. Add Preview Mode Styling:
```html
<!-- Add to the form element -->
<form @submit.prevent="submitSurvey" enctype="multipart/form-data" :class="{'pointer-events-none opacity-75': isPreviewMode && !isLocked, 'pointer-events-none': isLocked}">
```

### 6. Add Preview Banner:
```html
<!-- Add after the Site Information section -->
<div v-if="isPreviewMode" class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <h3 class="text-lg font-semibold text-yellow-800">Preview Mode</h3>
            <p class="text-sm text-yellow-700">Review your answers below. Click "Edit Survey" to make changes or "Submit Survey" to finalize.</p>
        </div>
    </div>
</div>

<div v-if="isLocked" class="bg-green-50 border-l-4 border-green-400 p-6 mb-8">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <h3 class="text-lg font-semibold text-green-800">Survey Approved</h3>
            <p class="text-sm text-green-700">This survey has been approved and is now locked. No changes can be made.</p>
        </div>
    </div>
</div>
```

## Testing Steps

1. Run the SQL migration
2. Start a survey - should create draft
3. Fill some fields and save draft
4. Close browser and reopen - should resume draft
5. End survey - should show preview mode
6. Edit from preview - should allow changes
7. Submit - should finalize and redirect
8. Try to access again - should show view-only mode

## Notes

- Auto-save runs every 30 seconds when survey is in progress
- All data is preserved even if user closes browser
- Once approved, survey is completely locked
- Rejected surveys can be edited and resubmitted
