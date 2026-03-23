<?php
namespace App\Models;

use App\Database;
use PDO;

class User {
    
    public static function authenticate($email, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.role_id, u.first_name, u.last_name, u.email, u.password, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? AND u.deleted_at IS NULL
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 1. BARRIERE: Mitarbeiter dürfen sich nicht einloggen!
        if ($user && (strtolower($user['role_name']) === 'mitarbeiter' || (int)$user['role_id'] === 4)) {
            return false;
        }

        // 2. BARRIERE: Nur wenn ein Passwort in der DB existiert und es übereinstimmt, geht es weiter
        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']); 
            return $user;
        }
        return false;
    }

    public static function getAllActive() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.last_name ASC
        ");
        return $stmt->fetchAll();
    }


}