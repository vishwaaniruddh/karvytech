# Soft Delete Implementation for Sites

## Overview
This implementation adds soft delete functionality to the sites table, allowing sites to be "deleted" without permanently removing them from the database. Only superadmin users can permanently delete sites.

## Database Changes

### Migration File
Run the migration: `database/migrations/add_soft_delete_to_sites.sql`

This adds:
- `deleted_at` DATETIME column (NULL when not deleted)
- `deleted_by` INT column (references users.id)
- Index on `deleted_at` for performance

### SQL to Run
```sql
ALTER TABLE `sites` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_by`,
ADD COLUMN `deleted_by` INT NULL DEFAULT NULL AFTER `deleted_at`,
ADD INDEX `idx_deleted_at` (`deleted_at`);

ALTER TABLE `sites`
ADD CONSTRAINT `fk_sites_deleted_by` 
FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;
```

## Features

### 1. Soft Delete (All Admin Users)
- When a site is deleted, it's marked with `deleted_at` timestamp
- Site is hidden from normal queries
- All related data remains intact
- Can be restored later

### 2. Trash Management (Superadmin Only)
- Access via: `/admin/sites/trash.php`
- View all soft-deleted sites
- Restore sites back to active
- Permanently delete sites (hard delete)

### 3. Permanent Delete (Superadmin Only)
- Completely removes site and all related data:
  - Site record
  - Site delegations
  - Site surveys
  - Installation delegations
  - Installation materials
- Cannot be undone
- Requires superadmin role

## Files Modified/Created

### Models
- `models/Site.php`
  - `delete()` - Changed to soft delete
  - `permanentDelete()` - New method for hard delete
  - `restore()` - New method to restore deleted sites
  - `getTrashed()` - New method to get soft-deleted sites
  - `getAllWithPagination()` - Updated to exclude soft-deleted sites

### Controllers
- `controllers/SitesController.php`
  - `delete()` - Updated for soft delete
  - `permanentDelete()` - New method (superadmin only)
  - `restore()` - New method to restore sites

### Views
- `admin/sites/index.php` - Updated delete confirmation message
- `admin/sites/trash.php` - New trash management page (superadmin only)
- `admin/sites/delete.php` - Existing (now does soft delete)
- `admin/sites/restore.php` - New endpoint
- `admin/sites/permanent-delete.php` - New endpoint (superadmin only)

## User Roles

### Regular Admin
- Can soft delete sites (moves to trash)
- Cannot access trash page
- Cannot permanently delete

### Superadmin
- Can soft delete sites
- Can access trash page
- Can restore deleted sites
- Can permanently delete sites

## Usage

### For Regular Admins
1. Click delete button on a site
2. Confirm deletion
3. Site moves to trash (soft deleted)
4. Site disappears from active list

### For Superadmin
1. Access trash page via "Trash" button (red button in sites index)
2. View all deleted sites
3. Options:
   - **Restore**: Moves site back to active list
   - **Permanent Delete**: Completely removes site and all data (cannot be undone)

## Benefits

1. **Safety**: Accidental deletions can be recovered
2. **Audit Trail**: Track who deleted what and when
3. **Data Integrity**: Related data preserved until permanent deletion
4. **Compliance**: Meet data retention requirements
5. **Flexibility**: Superadmin can clean up when needed

## Important Notes

⚠️ **Permanent deletion is irreversible!**
- All site data is completely removed
- All related surveys, delegations, and installations are deleted
- No way to recover after permanent deletion
- Only superadmin can perform this action

## Testing Checklist

- [ ] Regular admin can soft delete sites
- [ ] Soft-deleted sites don't appear in normal site list
- [ ] Regular admin cannot access trash page
- [ ] Superadmin can access trash page
- [ ] Superadmin can restore deleted sites
- [ ] Restored sites appear in normal site list
- [ ] Superadmin can permanently delete sites
- [ ] Permanent deletion removes all related data
- [ ] Non-superadmin gets 403 error on permanent delete
