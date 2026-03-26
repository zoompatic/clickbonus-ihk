<?php
// src/Database.php
namespace App;
use PDO;
use PDOException;

// Diese Klasse verwaltet die Verbindung zur Datenbank.
// Sie verwendet das Singleton-Muster, um sicherzustellen, dass nur eine Verbindung besteht.

class Database
{
    private static $instance = null;
    private $pdo;

    // Der Konstruktor ist private! Das verhindert, dass man die Klasse mit 'new' mehrfach aufruft.
    private function __construct()
    {
        $host = \App\Config::get('DB_HOST', '127.0.0.1');
        $dbname = \App\Config::get('DB_NAME');
        $user = \App\Config::get('DB_USER', 'root');
        $pass = \App\Config::get('DB_PASS', '');
        $charset = \App\Config::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            // Initialisierung der PDO-Verbindung mit Fehlermodus und Fetch-Einstellungen.
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        catch (PDOException $e) {
            // Wenn die Verbindung fehlschlägt, wird das System gestoppt.
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    // Bereitstellung der Datenbankverbindung.
    public static function getConnection()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}