<?php
// config/config.php
// Dies ist ein Beispiel für die Konfigurationsdatei.
// Hier werden Datenbankeinstellungen gespeichert, aber in der echten Anwendung wird .env verwendet.
// Wie ein Musterformular, das man ausfüllt.
return [
    'db' => [
        'host'    => 'DEIN_HOST', // z.B. 'localhost'
        'dbname'  => 'DEIN_DBNAME',
        'user'    => 'DEIN_USER',
        'pass'    => '', // Standard bei XAMPP
        'charset' => 'utf8mb4'
    ]
];