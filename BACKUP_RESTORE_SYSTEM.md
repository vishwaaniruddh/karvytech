# Backup and Restore System Documentation

## Overview

The backup and restore system provides a safety net for permanently deleted data. When a site deletion request is approved by a superadmin, all related data is backed up as JSON before permanent deletion. This allows for data restoration if needed.

## How It Works

### 1. Backup Creation (Automatic)

When a superadmin approves a site deletion request:

1. **Before Deletion**: All related records are backed up to `deleted_data_backups` table
2. **JSON Storage**: Each record is stored as a complete JSON object
3. **Request Grouping**: All backups for a single deletion request share the same `request_id`
4. **Metadata Tracking**: Tracks who deleted, when, and from which table

### 2. Tables Backed Up

When a site is deleted, the following related tables are automatically backed up:

- `sites` - The main site record
- `site_delegations` - Site delegation records
- `site_surveys` - Legacy survey responses
- `dynamic_survey_responses` - Dynamic survey responses
- `installation_delegations` - Installation delegation records
- `installation_materials` - Materials for each installation
- `material_requests` - Material requests for the site

### 3. Restore Process

Superadmins can restore deleted data from the **Restore Data** page:

1. Navigate to: `Superadmin Actions` → `Restore Data` button
2. View all restorable backups grouped by deletion request
3. Click **Restore** button for a specific request
4. All related records are recreated in their original tables
5. Backups are marked as `restored` to prevent duplicate restoration

## Database Schema

### deleted_data_backups Table

```sql
CREATE TABLE deleted_data_backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,              -- Links to superadmin_requests
    table_name VARCHAR(100) NOT NULL,     -- Original table name
    record_id INT NOT NULL,               -- Original record ID
    data JSON NOT NULL,                   -- Complete record as JSON
    deleted_by INT NULL,                  -- User who approved deletion
    deleted_at TIMESTAMP,                 -- When deleted
    restored_at TIMESTAMP NULL,           -- When restored (if applicable)
    restored_by INT NULL,                 -- User who restored
    status ENUM('deleted', 'restored'),   -- Current status
    notes TEXT NULL                       -- Additional notes
);
```

## Usage Examples

### For Superadmins

#### Viewing Restorable Data

1. Go to `http://localhost/project/admin/superadmin/index.php`
2. Click the **Restore Data** button (green button with refresh icon)
3. View statistics:
   - Total Backups
   - Can Restore (not yet restored)
   - Already Restored
   - Total Requests
   - Tables Affected

#### Restoring Deleted Data

1. Find the deletion request you want to restore
2. Review the tables and backup count
3. Click the **Restore** button
4. Confirm the restoration
5. All related records will be recreated

### For Developers

#### Adding Backup for New Tables

If you add a new table with `site_id` foreign key, update `Site::permanentDelete()`:

```php
// Backup your_new_table
$stmt = $this->db->prepare("SELECT * FROM your_new_table WHERE site_id = ?");
$stmt->execute([$id]);
$records = $stmt->fetchAll();

foreach ($records as $record) {
    $backupModel->createBackup(
        $requestId,
        'your_new_table',
        $record['id'],
        $record,
        $deletedBy,
        'Your table backup description'
    );
}
```

And add the deletion:

```php
// Delete your_new_table for this site
$stmt = $this->db->prepare("DELETE FROM your_new_table WHERE site_id = ?");
$stmt->execute([$id]);
```

## API Reference

### DeletedDataBackup Model

#### createBackup($requestId, $tableName, $recordId, $data, $deletedBy, $notes = null)

Creates a backup of a single record.

**Parameters:**
- `$requestId` - Superadmin request ID
- `$tableName` - Name of the table
- `$recordId` - Original record ID
- `$data` - Complete record as array
- `$deletedBy` - User ID who approved deletion
- `$notes` - Optional notes

**Returns:** Backup ID

#### getBackupsByRequest($requestId)

Gets all backups for a specific deletion request.

**Returns:** Array of backup records

#### restoreBackup($backupId, $restoredBy)

Restores a single backup record.

**Returns:** Array with success status and restored ID

#### restoreAllByRequest($requestId, $restoredBy)

Restores all backups for a deletion request.

**Returns:** Array with success status, restored records, and errors

#### getStats()

Gets overall backup statistics.

**Returns:** Array with counts

#### getRestorableBackups($page = 1, $limit = 20)

Gets paginated list of restorable backups grouped by request.

**Returns:** Array with records, pagination info

## Security Considerations

1. **Superadmin Only**: Only superadmins can access restore functionality
2. **Audit Trail**: All restorations are logged with user ID and timestamp
3. **No Duplicate Restore**: Once restored, backups are marked and cannot be restored again
4. **Cascade Delete**: If a superadmin request is deleted, all its backups are also deleted

## Workflow Diagram

```
User Deletes Site
       ↓
Creates Superadmin Request
       ↓
Superadmin Reviews Request
       ↓
Approves Deletion
       ↓
System Creates Backups (JSON)
  - Sites
  - Delegations
  - Surveys
  - Installations
  - Materials
  - Requests
       ↓
Permanent Deletion Executed
       ↓
Backups Available for Restore
       ↓
Superadmin Can Restore Anytime
       ↓
All Records Recreated
```

## Files Modified/Created

### New Files
- `database/migrations/create_deleted_data_backups.sql` - Table creation
- `database/migrations/run_backup_migration.php` - Migration runner
- `models/DeletedDataBackup.php` - Backup model
- `admin/superadmin/restore-data.php` - Restore UI
- `BACKUP_RESTORE_SYSTEM.md` - This documentation

### Modified Files
- `models/Site.php` - Added backup logic to `permanentDelete()`
- `admin/superadmin/process-request.php` - Pass requestId and deletedBy to permanentDelete
- `admin/superadmin/index.php` - Added "Restore Data" button

## Testing Checklist

- [x] Database table created successfully
- [ ] Delete a site and verify backups are created
- [ ] Check backup JSON contains all data
- [ ] Verify all related tables are backed up
- [ ] Test restore functionality
- [ ] Verify restored records match original data
- [ ] Check that restored backups are marked as 'restored'
- [ ] Test pagination on restore page
- [ ] Verify superadmin-only access

## Future Enhancements

1. **Selective Restore**: Restore individual records instead of all at once
2. **Backup Preview**: View JSON data before restoring
3. **Backup Export**: Download backups as JSON files
4. **Retention Policy**: Auto-delete old backups after X days
5. **Restore Validation**: Check for conflicts before restoring
6. **Backup Compression**: Compress JSON data to save space
7. **Audit Log**: Detailed log of all restore operations

## Troubleshooting

### Table Not Found Error

If you see "Table 'deleted_data_backups' doesn't exist":

```bash
php database/migrations/run_backup_migration.php
```

### Foreign Key Constraint Error

Ensure `superadmin_requests` and `users` tables exist before running migration.

### JSON Encoding Error

Check that all data being backed up is JSON-serializable. Avoid resources or closures.

### Restore Fails

Check error logs in `admin/logs/error.log` for detailed error messages.

## Support

For issues or questions, contact the development team or check the project documentation.
