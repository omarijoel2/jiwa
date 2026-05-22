<?php
/**
 * Classes/Permissions.php
 * 
 * Author: Timon
 * Date: 09/02/2025
 * 
 */
namespace App\Classes;

use App\Classes\Query;

class Permissions
{
    // Get all permissions assigned to a role
    public static function getRolePermissions($role_id)
    {
        return Query::query(
            "SELECT p.name FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id",
            ['role_id' => $role_id]
        );
    }

    // Get all custom permissions assigned to a user
    public static function getUserPermissions($user_id)
    {
        return Query::query(
            "SELECT p.name FROM permissions p
            JOIN user_permissions up ON p.id = up.permission_id
            WHERE up.user_id = :user_id",
            ['user_id' => $user_id]
        );
    }

    // Check if user has a specific permission
    public static function hasPermission($user_id, $permission)
    {
        $rolePermissions = self::getRolePermissions($user_id);
        $userPermissions = self::getUserPermissions($user_id);

        $allPermissions = array_merge(
            array_column($rolePermissions, 'name'),
            array_column($userPermissions, 'name')
        );

        return in_array($permission, $allPermissions);
    }

    // Assign a permission to a user
    public static function assignUserPermission($user_id, $permission_id)
    {
        return Query::insert(
            "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)",
            ['user_id' => $user_id, 'permission_id' => $permission_id]
        );
    }

    // Assign a permission to a role
    public static function assignRolePermission($role_id, $permission_id)
    {
        return Query::insert(
            "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)",
            ['role_id' => $role_id, 'permission_id' => $permission_id]
        );
    }
}
?>
