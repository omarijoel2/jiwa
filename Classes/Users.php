<?php
/**
 * Classes/Users.php
 * 
 * Author: Timon
 * Date: 09/02/2025
 * 
 */

namespace App\Classes;

use App\Classes\Query;

class Users
{
    // Get user by ID
    public static function getUserById($user_id)
    {
        return Query::fetchOne("SELECT *, (SELECT name FROM roles WHERE id = role_id) AS role_name FROM users WHERE id = :user_id", ['user_id' => $user_id]);
    }

    // Get user by email
    public static function getUserByEmail($email)
    {
        return Query::fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    // Validate user credentials (Login)
    public static function authenticate($email, $password)
    {
        $user = self::getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return "inactive";
        }

        return $user;
    }

    // Set remember me token
    public static function setRememberMeToken($user_id, $token)
    {
        return Query::updateDelete(
            "UPDATE users SET remember_token = :token WHERE id = :id",
            ['token' => $token, 'id' => $user_id]
        );
    }

    // Get user by remember token
    public static function getUserByRememberToken($token)
    {
        return Query::fetchOne("SELECT * FROM users WHERE remember_token = :token", ['token' => $token]);
    }

    // Create a new user
    public static function createUser($name, $email, $password, $role_id)
    {
        return Query::insert(
            "INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)",
            [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role_id' => $role_id
            ]
        );
    }

    // Deactivate user
    public static function deactivateUser($user_id)
    {
        return Query::updateDelete(
            "UPDATE users SET status = 'inactive' WHERE id = :id",
            ['id' => $user_id]
        );
    }

    // Activate user
    public static function activateUser($user_id)
    {
        return Query::updateDelete(
            "UPDATE users SET status = 'active' WHERE id = :id",
            ['id' => $user_id]
        );
    }

    public static function updateProfile($user_id, $name, $email) {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :user_id";
        return Query::updateDelete($sql, ['name' => $name, 'email' => $email, 'user_id' => $user_id]);
    }

    public static function verifyPassword($user_id, $current_password) {
        $user = Query::fetchOne("SELECT password FROM users WHERE id = :user_id", ['user_id' => $user_id]);
        
        if (!$user) {
            return false;
        }
    
        return password_verify($current_password, $user['password']);
    }

    public static function updatePassword($user_id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
        return Query::updateDelete($sql, ['password' => $hashed_password, 'user_id' => $user_id]);
    }
    
    
}
?>
