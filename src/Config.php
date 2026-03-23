<?php
namespace App;

// Diese Klasse lädt Konfigurationen aus einer .env-Datei.
// .env ist wie ein Geheimfach, wo sensible Daten wie Passwörter gespeichert werden, ohne sie im Code zu zeigen.

class Config {
    private static $env = [];

    /**
     * Lädt die .env Datei einmalig beim Systemstart
     * Wie das Laden eines Konfigurationsprofils beim Start eines Spiels.
     */
    public static function load() {
        $path = __DIR__ . '/../.env';
        if (!file_exists($path)) {
            return; // Falls keine .env da ist, passiert nichts
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Kommentare ignorieren: Wir überspringen Lese-Notizen (Zeilen mit '#'), genau wie man durchgestrichene Zeilen auf einem Einkaufszettel ignoriert.
            if (strpos(trim($line), '#') === 0) continue;
            
            // Zeile in zwei Teile schneiden: Wir trennen das "Etikett" (den Namen) vom eigentlichen "Inhalt" (dem Wert), ähnlich wie bei einem Namensschild.
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                self::$env[$name] = $value;
                
                // Wir legen diese Information auch in das globale Systemgedächtnis (Umgebungsvariablen), 
                // damit jederzeit von überall darauf zugegriffen werden kann.
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Holt einen Wert aus der Konfiguration
     * Wie das Nachschlagen eines Wertes in einem Wörterbuch.
     */
    public static function get($key, $default = null) {
        return self::$env[$key] ?? $default;
    }
}