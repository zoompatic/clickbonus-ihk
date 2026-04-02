<?php
// src/Models/User.php
namespace App\Models;

use App\Database;
use PDO;

// Diese Klasse verwaltet Benutzer, Anmeldung und Datenabruf.
class User
{

    public static function authenticate($email, $password)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("
            SELECT u.id, u.role_id, u.first_name, u.last_name, u.email, u.password, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? AND u.deleted_at IS NULL
        ");
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user && (strtolower($user['role_name']) === 'mitarbeiter' || (int)$user['role_id'] === 4)) {
            return false;
        }

        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public static function getAllActive()
    {
        $database = Database::getConnection();
        $statement = $database->query("
            SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.last_name ASC
        ");
        return $statement->fetchAll();
    }
}