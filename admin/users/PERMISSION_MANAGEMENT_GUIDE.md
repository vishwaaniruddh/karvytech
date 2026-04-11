# User Permission Management Guide

## Overview
The enhanced user permission management system allows administrators to customize permissions for individual users beyond their assigned role's default permissions.

## Features

### 1. Role-Based Permissions
- Users inherit permissions from their assigned role
- Roles define the baseline permissions for all users with that role
- Green badges indicate permissions inherited from the role

### 2. User-Specific Permissions
- Administrators can grant additional permissions to individual users
- Administrators can revoke specific role permissions from individual users
- Custom permissions override role defaults

### 3. Permission Editor
- Visual interface for managing permissions
- Grouped by module for easy navigation
- Search functionality to quickly find permissions
- Bulk actions (Select All, Deselect All, Reset to Role Defaults)

## How to Use

### Assigning a Role

1. **Navigate to User Management**
   - Go to `Admin > Users`
   - Click on a user to edit

2. **Select Role**
   - Choose from available roles (Superadmin, Admin, Manager, Contractor, etc.)
   - Click "Assign Role" to save

3. **View Permissions**
   - The "Current Permissions" section shows all active permissions
   - Permissions are grouped by module
   - Total permission count is displayed

### Editing User Permissions

1. **Open Permission Editor**
   - Click "Edit Permissions" button in the Current Permissions section
   - A modal will open with all available permissions

2. **Understanding the Interface**
   - **Green badges**: Permissions from the user's role
   - **Gray badges**: Additional permissions not in the role
   - **Module checkboxes**: Select/deselect all permissions in a module
   - **Individual checkboxes**: Select specific permissions

3. **Making Changes**
   - Check boxes to grant permissions
   - Uncheck boxes to revoke permissions
   - Use "Select All" to grant all permissions
   - Use "Deselect All" to revoke all permissions
   - Use "Reset to Role Defaults" to restore role permissions

4. **Search Permissions**
   - Use the search box to filter permissions by name
   - Type keywords to quickly find specific permissions

5. **Save Changes**
   - Click "Save Changes" to apply modifications
   - Page will reload to show updated permissions

### Viewing Role Permissions

1. **In Role Selection**
   - Click "View Permissions" link next to any role
   - A modal shows all permissions for that role

2. **In Role Comparison Table**
   - Click on the permission count (e.g., "33 permissions")
   - A modal displays detailed permissions grouped by module

## Permission Types

### Module-Based Permissions
Permissions are organized by system modules:
- **Dashboard**: View dashboard, statistics
- **Sites**: Create, edit, delete, view sites
- **Users**: Manage users, assign roles
- **BOQ**: Manage Bill of Quantities
- **Inventory**: Stock management, dispatches
- **Reports**: Generate and export reports
- **Settings**: System configuration

### Action-Based Permissions
Each module has specific actions:
- **View**: Read-only access
- **Create**: Add new records
- **Edit**: Modify existing records
- **Delete**: Remove records
- **Export**: Download data
- **Approve**: Approval workflows

## Best Practices

### When to Use Custom Permissions

✅ **Good Use Cases:**
- Temporary elevated access for specific tasks
- Restricting access to sensitive features
- Granting limited access to contractors
- Testing new features with specific users

❌ **Avoid:**
- Making extensive custom permissions for many users (create a new role instead)
- Removing critical permissions that break functionality
- Granting superadmin-level access to non-superadmin users

### Security Considerations

1. **Principle of Least Privilege**
   - Grant only necessary permissions
   - Review and revoke unused permissions regularly

2. **Audit Trail**
   - All permission changes are logged
   - Track who granted permissions and when

3. **Role Hierarchy**
   - Superadmin has all permissions
   - Admin has most permissions
   - Manager has limited permissions
   - Contractor has minimal permissions

4. **Regular Reviews**
   - Periodically review user permissions
   - Remove custom permissions when no longer needed
   - Update roles instead of individual permissions when possible

## Technical Details

### Database Structure

#### user_permissions Table
```sql
- id: Primary key
- user_id: User receiving permission
- permission_id: Permission being granted/revoked
- granted_by: Admin who made the change
- granted_at: Timestamp
- notes: Reason for change
- status: active/inactive
```

### API Endpoints

#### Get All Permissions
```
GET /api/rbac/permissions.php?action=all
```

#### Get Role Permissions
```
GET /api/rbac/roles.php?action=permissions&role_id={id}
```

#### Get User Permissions
```
GET /api/rbac/users.php?action=permissions&user_id={id}
```

#### Update User Permissions
```
POST /api/rbac/users.php?action=update_permissions
Body: user_id, role_id, permissions[]
```

### Permission Resolution Logic

1. **Get Role Permissions**: Fetch all permissions assigned to the user's role
2. **Get User Permissions**: Fetch user-specific permission overrides
3. **Merge**: Combine role and user permissions
4. **Apply Overrides**: 
   - Active user permissions are added
   - Inactive user permissions are removed
5. **Return Final Set**: User's effective permissions

### Example Permission Flow

```
User: John Doe
Role: Manager (has 19 permissions)

Custom Permissions:
+ sites.delete (added)
+ reports.export (added)
- users.create (removed from role)

Final Permissions: 20
(19 from role - 1 removed + 2 added)
```

## Troubleshooting

### Permission Not Showing
- Ensure the permission is active in the database
- Check if the module is active
- Verify role assignment is correct
- Clear browser cache and reload

### Cannot Save Changes
- Check if you have admin privileges
- Verify database connection
- Check browser console for errors
- Ensure user has a role assigned

### Permissions Not Taking Effect
- Logout and login again
- Check if permission is marked as active
- Verify the permission is correctly assigned
- Check for conflicting inactive permissions

## FAQ

**Q: What happens if I remove all permissions from a user?**
A: The user will have no access to any features except login. They should have at least basic permissions.

**Q: Can I edit superadmin permissions?**
A: Superadmin permissions are system-level and cannot be modified. Superadmin always has full access.

**Q: How do I create a new permission?**
A: Permissions are defined in the database and code. Contact a developer to add new permissions.

**Q: Can users see their own permissions?**
A: Users can view their permissions in their profile page (if implemented).

**Q: What's the difference between role and user permissions?**
A: Role permissions are defaults for all users with that role. User permissions are customizations for specific users.

## Support

For technical issues or questions:
- Check the system logs: `admin/logs/error.log`
- Review database tables: `user_permissions`, `role_permissions`, `permissions`
- Contact system administrator

---

**Last Updated:** 2026-04-11
**Version:** 1.0
