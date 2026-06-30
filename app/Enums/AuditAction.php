<?php

namespace App\Enums;

enum AuditAction: string
{
    // Authentication
    case Login           = 'auth.login';
    case Logout          = 'auth.logout';
    case PasswordReset   = 'auth.password_reset';
    case RegisteredUser  = 'auth.registered';

    // Users
    case UserCreated     = 'user.created';
    case UserUpdated     = 'user.updated';
    case UserDeleted     = 'user.deleted';

    // Roles
    case RoleAssigned    = 'role.assigned';
    case RoleRemoved     = 'role.removed';
    case RoleCreated     = 'role.created';
    case RoleUpdated     = 'role.updated';
    case RoleDeleted     = 'role.deleted';

    // Permissions
    case PermissionAssigned = 'permission.assigned';
    case PermissionRemoved  = 'permission.removed';

    // Resources
    case ResourceCreated  = 'resource.created';
    case ResourceUpdated  = 'resource.updated';
    case ResourceDeleted  = 'resource.deleted';
    case ResourceApproved = 'resource.approved';
    case ResourceRejected = 'resource.rejected';
    case ResourceSubmitted = 'resource.submitted'; // draft → pending

    // Access control
    case AccessDenied    = 'access.denied';

    /**
     * Human-readable label for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::Login             => 'Logged In',
            self::Logout            => 'Logged Out',
            self::PasswordReset     => 'Password Reset',
            self::RegisteredUser    => 'Registered',
            self::UserCreated       => 'User Created',
            self::UserUpdated       => 'User Updated',
            self::UserDeleted       => 'User Deleted',
            self::RoleAssigned      => 'Role Assigned',
            self::RoleRemoved       => 'Role Removed',
            self::RoleCreated       => 'Role Created',
            self::RoleUpdated       => 'Role Updated',
            self::RoleDeleted       => 'Role Deleted',
            self::PermissionAssigned => 'Permission Assigned',
            self::PermissionRemoved  => 'Permission Removed',
            self::ResourceCreated   => 'Resource Created',
            self::ResourceUpdated   => 'Resource Updated',
            self::ResourceDeleted   => 'Resource Deleted',
            self::ResourceApproved  => 'Resource Approved',
            self::ResourceRejected  => 'Resource Rejected',
            self::ResourceSubmitted => 'Resource Submitted',
            self::AccessDenied      => 'Access Denied',
        };
    }
}
