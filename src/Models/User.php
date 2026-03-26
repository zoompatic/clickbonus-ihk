<?php
// src/Models/User.php
namespace App\Models;

use App\Database;
use PDO;

// Diese Klasse verwaltet Benutzer, Anmeldung und Datenabruf.
class User
{

    // Authentifizierung eines Benutzers mit E-Mail und Passwort.
    // Prüfung, ob die Eingaben korrekt sind und ob der Benutzer die richtige Rolle hat.
    public static function authenticate($email, $password)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.role_id, u.first_name, u.last_name, u.email, u.password, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? AND u.deleted_at IS NULL
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Prüfung der Berechtigung: Nur Manager und HR können sich anmelden.
        if ($user && (strtolower($user['role_name']) === 'mitarbeiter' || (int)$user['role_id'] === 4)) {
            return false;
        }

        // Prüfung ob ein Passwort existiert und mit dem Hash übereinstimmt.
        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']); // Passwort aus dem Ergebnis entfernen, aus Sicherheitsgründen.
            return $user;
        }
        return false;
    }

    // Holt alle aktiven Benutzer aus der Datenbank.
    public static function getAllActive()
    {
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