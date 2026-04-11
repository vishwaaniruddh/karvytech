# Site Deletion Integration with Superadmin Request System

## Overview
Site deletion has been integrated with the Superadmin Request Management System to provide a controlled approval workflow for non-superadmin users.

## Implementation Summary

### 1. Modified Files

#### `admin/sites/delete.php`
- Added role-based deletion logic
- **Superadmin**: Directly performs soft delete
- **Non-Superadmin**: Creates a deletion request for approval
- Integrated with `SuperadminRequest` model

#### `admin/sites/index.php`
- Updated `deleteSite()` JavaScript function
- Different confirmation messages based on user role
- Superadmin: "Move to Trash" (immediate action)
- Non-Superadmin: "Request Site Deletion" (creates request)

#### `admin/superadmin/process-request.php`
- Added site deletion approval handling
- When approving `site_deletion` request type:
  - Executes actual soft delete on the site
  - Updates request status to approved
  - Returns success message

#### `admin/superadmin/view-request.php`
- Enhanced display for site deletion requests
- Shows site details in user-friendly format:
  - Site Code
  - Location
  - Customer
  - Site ID
- Other request types show raw JSON data

#### `admin/superadmin/index.php`
- Added "View Trash" button in header
- Links to `../sites/trash.php`
- Only visible to superadmin users

#### `admin/superadmin/README.md`
- Added comprehensive documentation
- Documented site deletion workflow
- Explained two-tier approval system

### 2. Workflow

#### For Regular Admin/Manager Users:
```
1. Click Delete button on site
2. Confirm deletion request
3. System creates superadmin_request record
4. Site remains active
5. User sees: "Site deletion request submitted successfully. Awaiting superadmin approval."
6. Wait for superadmin approval
```

#### For Superadmin Users:
```
Option A - Direct Deletion:
1. Click Delete button on site
2. Confirm deletion
3. Site immediately soft deleted (moved to trash)
4. Can restore from Trash page

Option B - Approve Requests:
1. Go to Superadmin Actions
2. View pending site deletion requests
3. Review site details
4. Approve or Reject with remarks
5. On approval: Site is soft deleted
6. On rejection: Site remains active, requester notified
```

### 3. Database Structure

#### Request Data Format for Site Deletion:
```json
{
  "site_id": 123,
  "site_code": "SITE-001",
  "location": "Mumbai, Maharashtra",
  "customer": "ABC Bank"
}
```

#### Request Record Example:
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
  'Request to delete site SITE-001 (Mumbai, Maharashtra)',
  5,
  'john.doe',
  'admin',
  '{"site_id":123,"site_code":"SITE-001","location":"Mumbai, Maharashtra","customer":"ABC Bank"}',
  123,
  'sites',
  'medium',
  'pending'
);
```

### 4. Security Features

- **Role-Based Access Control**: Only superadmin can directly delete or approve deletions
- **Audit Trail**: All deletion requests logged with requester info
- **Soft Delete**: Sites moved to trash, not permanently deleted
- **Restoration**: Superadmin can restore accidentally deleted sites
- **Mandatory Remarks**: Rejection requires reason for transparency

### 5. User Experience

#### Non-Superadmin Delete Confirmation:
```
Title: Request Site Deletion
Message: This will create a deletion request for superadmin approval. 
         The site will not be deleted immediately.
```

#### Superadmin Delete Confirmation:
```
Title: Move to Trash
Message: Are you sure you want to move this site to trash? 
         You can restore it later from the trash page.
```

### 6. Integration Points

#### Creating a Site Deletion Request (Programmatically):
```php
require_once 'models/SuperadminRequest.php';
require_once 'models/Site.php';

$siteModel = new Site();
$site = $siteModel->find($siteId);

$requestModel = new SuperadminRequest();
$requestId = $requestModel->createRequest([
    'request_type' => 'site_deletion',
    'request_title' => 'Delete Site: ' . $site['site_id'],
    'request_description' => 'Request to delete site ' . $site['site_id'],
    'requested_by' => $currentUser['id'],
    'requested_by_name' => $currentUser['username'],
    'requested_by_role' => $currentUser['role'],
    'request_data' => json_encode([
        'site_id' => $site['id'],
        'site_code' => $site['site_id'],
        'location' => $site['location'],
        'customer' => $site['customer']
    ]),
    'reference_id' => $siteId,
    'reference_table' => 'sites',
    'priority' => 'medium'
]);
```

#### Approving a Site Deletion Request:
```php
// In process-request.php
if ($request['request_type'] === 'site_deletion' && $request['reference_id']) {
    $siteModel = new Site();
    $deleteSuccess = $siteModel->delete($request['reference_id']);
    
    if ($deleteSuccess) {
        $requestModel->approve($id, $currentUser['id'], $remarks);
    }
}
```

### 7. Testing Checklist

- [ ] Non-superadmin user can create deletion request
- [ ] Request appears in Superadmin Actions dashboard
- [ ] Superadmin can view request details
- [ ] Superadmin can approve request (site moves to trash)
- [ ] Superadmin can reject request (site remains active)
- [ ] Superadmin can directly delete sites
- [ ] Trash button only visible to superadmin
- [ ] Deleted sites can be restored from trash
- [ ] Permanent deletion works correctly

### 8. Future Enhancements

- Email notifications when request is approved/rejected
- Bulk deletion requests
- Scheduled deletion (delete after X days)
- Deletion reason field for requesters
- Auto-approval for certain conditions
- Deletion impact analysis (show related records)

## Conclusion

The site deletion integration provides a robust approval workflow that:
- Prevents unauthorized deletions
- Maintains audit trail
- Allows restoration of accidentally deleted sites
- Provides clear communication between users and superadmin
- Follows security best practices
