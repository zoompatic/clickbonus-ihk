<?php
// src/Database.php
namespace App;
use PDO;
use PDOException;

// Diese Klasse verwaltet die Verbindung zur Datenbank.
// Sie verwendet das Singleton-Muster, um sicherzustellen, dass nur eine Verbindung besteht.
// Wie ein Telefonbuch: Es gibt nur eines, und alle können es nutzen.

class Database {
    private static $instance = null;
    private $pdo;

    // Der Konstruktor ist private! Das verhindert, dass man die Klasse mit 'new' mehrfach aufruft.
    // Wie eine geheime Tür, die nur von innen geöffnet werden kann.
    private function __construct() {
        $host = \App\Config::get('DB_HOST', '127.0.0.1');
        $dbname = \App\Config::get('DB_NAME');
        $user = \App\Config::get('DB_USER', 'root');
        $pass = \App\Config::get('DB_PASS', '');
        $charset = \App\Config::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            // Hier wählen wir quasi die Nummer der Datenbank und warten, ob jemand abhebt.
            // Die ganzen "PDO::ATTR"-Befehle sind dabei nur Anweisungen, wie genau wir miteinander sprechen wollen (z.B. im Fehlerfall laut Bescheid sagen).
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Wenn niemand abhebt (Verbindung schlägt fehl), stoppen wir das gesamte System ('die') 
            // und hängen ein Schild auf, was genau schiefgelaufen ist.
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    // So holt sich die restliche App die Verbindung
    // Wie das Ausleihen eines Buches aus der Bibliothek.
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}