# Site Deletion Workflow - Quick Reference

## New Deletion Process (No Trash)

### Visual Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    ANY USER (Including Superadmin)          │
└─────────────────────────────────────────────────────────────┘
                              ↓
                    Clicks "Delete" button
                              ↓
                    ┌──────────────────┐
                    │ Confirmation     │
                    │ Dialog Shows:    │
                    │ "This will       │
                    │ create a request"│
                    └──────────────────┘
                              ↓
                    User clicks "Confirm"
                              ↓
                    ┌──────────────────┐
                    │ System creates   │
                    │ SuperadminRequest│
                    │ record           │
                    └──────────────────┘
                              ↓
                    ┌──────────────────┐
                    │ Site remains     │
                    │ ACTIVE           │
                    │ (no changes)     │
                    └──────────────────┘
                              ↓
                    ┌──────────────────┐
                    │ Success message: │
                    │ "Request created"│
                    └──────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                         SUPERADMIN                          │
└─────────────────────────────────────────────────────────────┘
                              ↓
                Goes to "Superadmin Actions"
                              ↓
                    ┌──────────────────┐
                    │ Views pending    │
                    │ deletion request │
                    └──────────────────┘
                              ↓
                    Reviews site details
                              ↓
                    ┌──────────────────┐
                    │ Decision:        │
                    │ Approve/Reject   │
                    └──────────────────┘
                              ↓
                ┌─────────────┴─────────────┐
                ↓                           ↓
        ┌──────────────┐          ┌──────────────┐
        │   APPROVE    │          │   REJECT     │
        └──────────────┘          └──────────────┘
                ↓                           ↓
    ┌──────────────────┐      ┌──────────────────┐
    │ permanentDelete()│      │ Site remains     │
    │ is called        │      │ active           │
    └──────────────────┘      └──────────────────┘
                ↓                           ↓
    ┌──────────────────┐      ┌──────────────────┐
    │ Delete related:  │      │ Requester gets   │
    │ - Installations  │      │ rejection notice │
    │ - Surveys        │      │ with reason      │
    │ - Delegations    │      └──────────────────┘
    │ - Materials      │
    └──────────────────┘
                ↓
    ┌──────────────────┐
    │ Delete site      │
    │ record           │
    └──────────────────┘
                ↓
    ┌──────────────────┐
    │ PERMANENTLY      │
    │ REMOVED          │
    │ (Cannot restore) │
    └──────────────────┘
                ↓
    ┌──────────────────┐
    │ Requester gets   │
    │ approval notice  │
    └──────────────────┘
```

## Key Points

### ✅ What Changed

| Before | After |
|--------|-------|
| Superadmin: Direct delete → Trash | Superadmin: Create request → Approve → Delete |
| Regular: Request → Trash | Regular: Request → Approve → Delete |
| Trash page exists | No trash page |
| Can restore deleted sites | Cannot restore (permanent) |
| Two-step deletion | One-step after approval |

### 🔒 Security Features

- ✅ All deletions require approval
- ✅ Full audit trail
- ✅ Requester tracked
- ✅ Approver tracked
- ✅ Timestamps recorded
- ✅ Remarks documented

### ⚠️ Important Notes

1. **Permanent Deletion**: Once approved, site is gone forever
2. **No Undo**: Cannot restore deleted sites
3. **Cascading Delete**: All related data is removed
4. **Active Until Approved**: Site works normally until approval
5. **Request Priority**: Superadmin requests marked as "high"

## User Messages

### When Creating Request

**All Users See:**
```
Title: Request Site Deletion

Message: This will create a deletion request for 
superadmin approval. The site will remain active 
until the request is approved.

Buttons: [Cancel] [Confirm]
```

**After Confirmation:**
```
✓ Success

Superadmin: "Site deletion request created 
successfully. You can review and approve it 
in Superadmin Actions."

Regular User: "Site deletion request submitted 
successfully. Awaiting superadmin approval."
```

### When Approving Request

**Superadmin Sees:**
```
✓ Success

"Site deletion approved and site has been 
permanently removed"
```

### When Rejecting Request

**Superadmin Sees:**
```
✓ Success

"Request rejected successfully"
```

## Database Records

### superadmin_requests Table

```sql
INSERT INTO superadmin_requests (
    request_type,
    request_title,
    request_description,
    requested_by,
    requested_by_name,
    requested_by_role,
    request_data,
    reference_id,
    reference_table,
    priority,
    status
) VALUES (
    'site_deletion',
    'Delete Site: SITE-001',
    'Request to delete site SITE-001 (Mumbai)',
    5,
    'john.doe',
    'admin',
    '{"site_id":123,"site_code":"SITE-001",...}',
    123,
    'sites',
    'medium',  -- or 'high' for superadmin
    'pending'
);
```

### After Approval

```sql
UPDATE superadmin_requests 
SET status = 'approved',
    reviewed_by = 1,
    reviewed_at = NOW(),
    remarks = 'Approved - duplicate entry'
WHERE id = 456;

-- Then site is permanently deleted
DELETE FROM sites WHERE id = 123;
-- (plus all related records)
```

## Quick Commands

### Check Pending Deletion Requests
```sql
SELECT * FROM superadmin_requests 
WHERE request_type = 'site_deletion' 
AND status = 'pending'
ORDER BY priority DESC, created_at ASC;
```

### Check Request History
```sql
SELECT 
    sr.*,
    u1.username as requester,
    u2.username as reviewer
FROM superadmin_requests sr
LEFT JOIN users u1 ON sr.requested_by = u1.id
LEFT JOIN users u2 ON sr.reviewed_by = u2.id
WHERE sr.request_type = 'site_deletion'
ORDER BY sr.created_at DESC;
```

### Find Sites Referenced in Requests
```sql
SELECT 
    sr.id as request_id,
    sr.status,
    s.site_id,
    s.location,
    sr.created_at as requested_at
FROM superadmin_requests sr
JOIN sites s ON sr.reference_id = s.id
WHERE sr.request_type = 'site_deletion'
AND sr.status = 'pending';
```

## Troubleshooting

### Issue: Request not showing in Superadmin Actions
**Check:**
1. Is user logged in as superadmin?
2. Is request status 'pending'?
3. Refresh the page
4. Check database: `SELECT * FROM superadmin_requests WHERE id = ?`

### Issue: Site not deleted after approval
**Check:**
1. Check error logs: `admin/logs/error.log`
2. Verify permanentDelete() method executed
3. Check for foreign key constraints
4. Verify transaction completed

### Issue: Cannot create deletion request
**Check:**
1. Does user have delete permission?
2. Is site ID valid?
3. Check browser console for errors
4. Verify API endpoint is accessible

## Best Practices

### For Users
1. ✅ Double-check site before requesting deletion
2. ✅ Provide clear reason in request (if field added)
3. ✅ Verify site is correct one
4. ✅ Check for active installations/surveys

### For Superadmin
1. ✅ Review site details before approving
2. ✅ Check for related data (installations, surveys)
3. ✅ Add remarks explaining decision
4. ✅ Verify requester's reason is valid
5. ✅ Consider impact on reports/analytics

### For Developers
1. ✅ Always use transactions for deletions
2. ✅ Log all deletion operations
3. ✅ Handle cascading deletes properly
4. ✅ Provide clear error messages
5. ✅ Test rollback scenarios

---

**Quick Reference Card**
- **All users**: Create request → Wait for approval
- **Superadmin**: Review → Approve/Reject
- **Approved**: Permanent deletion (no undo)
- **Rejected**: Site remains active
- **No trash**: Direct permanent deletion
