<?php
// src/Database.php
namespace App;
use PDO;
use PDOException;

// Diese Klasse verwaltet die Verbindung zur Datenbank.
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = \App\Config::get('DB_HOST', '127.0.0.1');
        $dbname = \App\Config::get('DB_NAME');
        $user = \App\Config::get('DB_USER', 'root');
        $password = \App\Config::get('DB_PASS', '');
        $charset = \App\Config::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        catch (PDOException $error) {
            die("Datenbankverbindung fehlgeschlagen: " . $error->getMessage());
        }
    }

    public static function getConnection()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}