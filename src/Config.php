<?php
// src/Config.php
namespace App;

// Diese Klasse lädt Konfigurationen aus einer .env-Datei.

class Config
{
    private static $env = [];

    // Lädt die .env Datei einmalig beim Systemstart
    public static function load()
    {
        $path = __DIR__ . '/../.env';
        if (!file_exists($path)) {
            return; // Falls keine .env da ist, passiert nichts
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Kommentare ignorieren: Wir überspringen Zeilen mit '#'
            if (strpos(trim($line), '#') === 0)
                continue;

            // Zeile in zwei Teile schneiden
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                self::$env[$name] = $value;

                // Setzt die Umgebungsvariablen.
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
    // Holt einen Wert aus der Konfiguration
    public static function get($key, $default = null)
    {
        return self::$env[$key] ?? $default;
    }
}