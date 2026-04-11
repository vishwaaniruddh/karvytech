# Superadmin Actions - Quick Start Guide

## For Regular Users (Admin/Manager)

### How to Request Site Deletion

1. **Navigate to Sites Page**
   - Go to `Sites Management` from the sidebar

2. **Find the Site**
   - Use search or filters to locate the site
   - Click the red trash icon in the Actions column

3. **Confirm Request**
   - Read the confirmation message
   - Click "Confirm" to submit the request

4. **Wait for Approval**
   - Your request is now pending superadmin review
   - You'll see a success message
   - The site remains active until approved

5. **Check Request Status**
   - Contact your superadmin to check status
   - Superadmin will approve or reject with remarks

---

## For Superadmin Users

### Dashboard Overview

Access: `Superadmin Actions` from sidebar menu

**Statistics Cards:**
- Total Requests
- Pending (needs your attention)
- Approved
- Rejected
- Urgent (high priority)
- High Priority

### How to Review Requests

1. **View Pending Requests**
   - Look at the requests table
   - Yellow "Pending" badge indicates awaiting review

2. **View Request Details**
   - Click the eye icon (👁️) to view full details
   - See requester info, site details, and request data

3. **Approve a Request**
   - Click the green checkmark icon (✓)
   - Add optional remarks
   - Click "Approve Request"
   - Site will be moved to trash

4. **Reject a Request**
   - Click the red X icon (✗)
   - Add mandatory rejection reason
   - Click "Reject Request"
   - Site remains active

### How to Manage Deleted Sites

1. **Access Trash**
   - Click "View Trash" button in Superadmin Actions
   - Or go to Sites page and click "Trash" button

2. **Restore a Site**
   - Find the site in trash
   - Click "Restore" button
   - Site becomes active again

3. **Permanently Delete**
   - Click "Permanent Delete" button
   - Confirm the action
   - ⚠️ This cannot be undone!
   - All related data will be deleted

### Direct Site Deletion (Superadmin Only)

1. **Go to Sites Page**
   - Navigate to `Sites Management`

2. **Delete Site**
   - Click red trash icon
   - Confirm deletion
   - Site immediately moves to trash
   - No approval needed

### Filters and Search

**Filter by Status:**
- All Status
- Pending
- Approved
- Rejected

**Filter by Priority:**
- All Priority
- Urgent
- High
- Medium
- Low

**Filter by Type:**
- All Types
- Site Deletion
- (Other types as added)

**Search:**
- Search by request title
- Search by description
- Search by requester name

### Best Practices

✅ **Do:**
- Review requests promptly
- Add meaningful remarks when approving/rejecting
- Check site details before approving deletion
- Use trash for temporary deletion
- Restore sites if deleted by mistake

❌ **Don't:**
- Permanently delete without checking dependencies
- Approve without reviewing details
- Reject without providing reason
- Delete sites with active installations without verification

### Keyboard Shortcuts

- `Ctrl + F` - Focus search box
- `Esc` - Close modal
- `Enter` - Submit form (when focused)

### Common Scenarios

**Scenario 1: Duplicate Site**
```
Request: Delete duplicate site
Action: Approve with remark "Duplicate entry removed"
```

**Scenario 2: Wrong Site Deletion Request**
```
Request: Delete active site
Action: Reject with remark "Site has active installations"
```

**Scenario 3: Accidental Deletion**
```
Action: Go to Trash → Find site → Click Restore
```

**Scenario 4: Cleanup Old Sites**
```
Action: Review trash → Permanently delete old entries
```

### Need Help?

- Check the full README.md for detailed documentation
- Review SITE_DELETION_INTEGRATION.md for technical details
- Contact system administrator for access issues

---

## Quick Reference

| Action | User Role | Result |
|--------|-----------|--------|
| Delete Site | Admin/Manager | Creates request |
| Delete Site | Superadmin | Immediate soft delete |
| Approve Request | Superadmin | Site moved to trash |
| Reject Request | Superadmin | Site remains active |
| Restore Site | Superadmin | Site becomes active |
| Permanent Delete | Superadmin | Site deleted forever |

---

**Last Updated:** 2026-04-11
**Version:** 1.0
