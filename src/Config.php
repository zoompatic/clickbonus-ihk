<?php
namespace App;

class Config {
    private static $env = [];

    /**
     * Lädt die .env Datei einmalig beim Systemstart
     */
    public static function load() {
        $path = __DIR__ . '/../.env';
        if (!file_exists($path)) {
            return; // Falls keine .env da ist, passiert nichts
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Kommentare ignorieren
            if (strpos(trim($line), '#') === 0) continue;
            
            // In Name=Wert teilen
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                self::$env[$name] = $value;
                // Auch für getenv() verfügbar machen
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Holt einen Wert aus der Konfiguration
     */
    public static function get($key, $default = null) {
        return self::$env[$key] ?? $default;
    }
}