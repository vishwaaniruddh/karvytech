# Superadmin Actions - Request Management System

## Overview
The Superadmin Actions module is a comprehensive request management and approval system designed exclusively for superadmin users. It provides a centralized dashboard for reviewing, approving, or rejecting various system requests that require superadmin authorization.

## Features

### 1. Dashboard with Statistics
- **Total Requests**: Overview of all requests in the system
- **Pending Requests**: Requests awaiting review
- **Approved Requests**: Successfully approved requests
- **Rejected Requests**: Declined requests with reasons
- **Urgent Pending**: High-priority requests needing immediate attention
- **High Priority**: Important requests requiring quick review

### 2. Request Management
- View all requests with detailed information
- Filter by status (pending, approved, rejected)
- Filter by priority (urgent, high, medium, low)
- Filter by request type
- Search functionality across requests
- Pagination for large datasets

### 3. Approval Workflow
- **Approve**: Accept requests with optional remarks
- **Reject**: Decline requests with mandatory rejection reason
- **View Details**: See complete request information including:
  - Request type and priority
  - Requester information
  - Request data (JSON format)
  - Review history (if already processed)

## Database Structure

### Table: `superadmin_requests`
```sql
- id: Primary key
- request_type: Type of request (site_deletion, user_creation, etc.)
- request_title: Short title
- request_description: Detailed description
- requested_by: User ID who made the request
- requested_by_name: Name of requester
- requested_by_role: Role of requester
- request_data: JSON data with request details
- reference_id: ID of related entity
- reference_table: Table name of related entity
- status: pending/approved/rejected
- priority: urgent/high/medium/low
- reviewed_by: Superadmin who reviewed
- reviewed_at: Review timestamp
- remarks: Superadmin comments
- created_at: Request creation time
- updated_at: Last update time
```

## Usage

### For Superadmin Users

1. **Access the Module**
   - Navigate to "Superadmin Actions" from the sidebar menu
   - Only visible to users with 'superadmin' role

2. **Review Requests**
   - View statistics at the top of the page
   - Use filters to find specific requests
   - Click "View" icon to see full details

3. **Approve a Request**
   - Click the green checkmark icon
   - Add optional remarks
   - Click "Approve Request"

4. **Reject a Request**
   - Click the red X icon
   - Provide mandatory rejection reason
   - Click "Reject Request"

### For Developers - Creating Requests

```php
require_once 'models/SuperadminRequest.php';

$requestModel = new SuperadminRequest();
$currentUser = Auth::getCurrentUser();

$requestId = $requestModel->createRequest([
    'request_type' => 'site_deletion',
    'request_title' => 'Delete Site #123',
    'request_description' => 'Request to permanently delete site due to...',
    'requested_by' => $currentUser['id'],
    'requested_by_name' => $currentUser['username'],
    'requested_by_role' => $currentUser['role'],
    'request_data' => json_encode([
        'site_id' => 123,
        'site_name' => 'Example Site',
        'reason' => 'Duplicate entry'
    ]),
    'reference_id' => 123,
    'reference_table' => 'sites',
    'priority' => 'high'
]);
```

## Request Types

Common request types include:
- `site_deletion`: Site deletion requests (soft delete after approval)
- `user_creation`: New user account creation
- `role_change`: User role modification
- `data_export`: Large data export requests
- `system_config`: System configuration changes
- `bulk_operation`: Bulk data operations

### Site Deletion Workflow

The site deletion feature implements a request-based approval system for ALL users:

**For All Users (Including Superadmin):**
1. When deleting a site from the Sites page, a deletion request is created
2. The site remains active until superadmin approval
3. User receives confirmation that request was submitted
4. Request appears in Superadmin Actions dashboard

**For Superadmin Users:**
1. Can view all deletion requests in Superadmin Actions
2. Can approve/reject deletion requests with remarks
3. Upon approval, site is permanently deleted (no trash/restore)
4. Upon rejection, site remains active and requester is notified

**Important Notes:**
- There is NO trash functionality - deletions are permanent after approval
- All users (including superadmin) must create a request to delete
- Sites cannot be restored once deletion is approved
- Permanent deletion removes all related data (surveys, delegations, installations)

## Priority Levels

- **Urgent**: Requires immediate attention (red badge)
- **High**: Important, needs quick review (orange badge)
- **Medium**: Standard priority (yellow badge)
- **Low**: Can be reviewed when convenient (gray badge)

## Security

- Only users with `role = 'superadmin'` can access this module
- All actions are logged with user ID and timestamp
- Rejection requires mandatory reason for audit trail
- Request data is stored in JSON format for flexibility

## Files

- `index.php`: Main dashboard page
- `requests-table.php`: Table component
- `process-request.php`: Approve/reject handler
- `view-request.php`: Request details viewer
- `models/SuperadminRequest.php`: Data model
- `database/migrations/create_superadmin_requests.sql`: Database schema

## Integration

To integrate with other modules:

1. Create a request when an action needs superadmin approval
2. Check request status before executing the action
3. Execute the action only if status is 'approved'
4. Handle rejection gracefully with user notification

## Future Enhancements

- Email notifications for new requests
- Bulk approve/reject functionality
- Request templates for common types
- Advanced filtering and sorting
- Export requests to CSV/PDF
- Request analytics and reporting
