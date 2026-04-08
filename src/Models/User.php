<?php
// src/Models/User.php
namespace App\Models;

use App\Database;
use App\Models\Role;
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

    public static function create($roleId, $firstName, $lastName, $email, $password)
    {
        $database = Database::getConnection();
        
        $hashedPassword = null;
        // Für Mitarbeiter (EMPLOYEE = 4) speichern wir kein Passwort, um Logins auf Datenbankebene sicher zu verhindern
        if ((int)$roleId !== Role::EMPLOYEE && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }

        $statement = $database->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, password)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $statement->execute([$roleId, $firstName, $lastName, $email, $hashedPassword]);
    }

    public static function getById($id)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $statement->execute([$id]);
        return $statement->fetch();
    }

    public static function update($id, $roleId, $firstName, $lastName, $email, $password)
    {
        $database = Database::getConnection();
        
        if ((int)$roleId === Role::EMPLOYEE) {
            $password = null;
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $statement = $database->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
            return $statement->execute([$roleId, $firstName, $lastName, $email, $hashedPassword, $id]);
        } else {
            if ((int)$roleId === Role::EMPLOYEE) {
                // Bei Mitarbeitern erzwingen wir ein leeres Passwort
                $statement = $database->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, email = ?, password = NULL WHERE id = ?");
                return $statement->execute([$roleId, $firstName, $lastName, $email, $id]);
            } else {
                // Bei anderen Rollen nur updaten, wenn passwort neu eingegeben wurde, andernfalls altes passwort behalten
                $statement = $database->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?");
                return $statement->execute([$roleId, $firstName, $lastName, $email, $id]);
            }
        }
    }

    public static function delete($id)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $statement->execute([$id]);
    }

    public static function verifyPasswordById($userId, $password)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("SELECT password FROM users WHERE id = ?");
        $statement->execute([$userId]);
        $user = $statement->fetch();
        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }

    public static function updatePassword($userId, $newPassword)
    {
        $database = Database::getConnection();
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $statement = $database->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $statement->execute([$hashedPassword, $userId]);
    }
}