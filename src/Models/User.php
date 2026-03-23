<?php
namespace App\Models;

use App\Database;
use PDO;

// Diese Klasse handhabt alles rund um Benutzer, wie Anmeldung und Datenabruf.
// Wie ein Personalverzeichnis in einem Unternehmen.

class User {
    
    // Authentifiziert einen Benutzer mit E-Mail und Passwort.
    // Prüft, ob die Eingaben korrekt sind und ob der Benutzer die richtige Rolle hat.
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
        // Nur Manager und HR können sich anmelden.
        if ($user && (strtolower($user['role_name']) === 'mitarbeiter' || (int)$user['role_id'] === 4)) {
            return false;
        }

        // 2. BARRIERE: Nur wenn ein Passwort in der DB existiert und es übereinstimmt, geht es weiter
        // Wie das Überprüfen eines Schlosses mit dem richtigen Schlüssel.
        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            unset($user['password']); // Passwort aus dem Ergebnis entfernen, aus Sicherheitsgründen.
            return $user;
        }
        return false;
    }

    // Holt alle aktiven Benutzer aus der Datenbank.
    // Wie das Durchblättern eines Adressbuchs.
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