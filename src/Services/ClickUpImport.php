<?php
namespace App\Services;

use App\Database;
use App\Config; // NEU: Nutzt den zentralen Config-Helfer
use Exception;

class ClickUpImport {
    
    private $apiToken;
    private $listId;
    private $baseUrl = 'https://api.clickup.com/api/v2';

    /**
     * Konstruktor: Lädt die API-Daten sicher über die Config-Klasse (.env)
     */
    public function __construct() {
        /**
         * SICHERHEITS-UPGRADE (Antigravity 1.C):
         * Wir löschen das 'require' der config.php und ziehen die Daten
         * stattdessen sicher aus den Umgebungsvariablen.
         */
        $this->apiToken = Config::get('CLICKUP_TOKEN');
        $this->listId = Config::get('CLICKUP_LIST_ID');
    }

    /**
     * Holt die Tasks (Projekte) von ClickUp und speichert sie in der Datenbank
     */
    public function syncProjects() {
        // Sicherstellen, dass die Zugangsdaten überhaupt da sind
        if (!$this->apiToken || !$this->listId) {
            throw new Exception("Synchronisation nicht möglich: API-Token oder List-ID fehlen in der .env Datei.");
        }

        // 1. Daten von der API holen
        $url = $this->baseUrl . '/list/' . $this->listId . '/task?subtasks=false';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: " . $this->apiToken,
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Fehler beim Abrufen der ClickUp-Daten. HTTP-Code: " . $httpCode . " (Bitte API-Token in der .env prüfen)");
        }

        $data = json_decode($response, true);
        $tasks = $data['tasks'] ?? [];

        // 2. Daten in die Datenbank schreiben
        $db = Database::getConnection();
        $syncedCount = 0;

        foreach ($tasks as $task) {
            $clickupId = !empty($task['custom_id']) ? $task['custom_id'] : $task['id'];
            $name = $task['name'];
            $status = $task['status']['status'] ?? 'unknown';
            $description = $task['description'] ?? '';

            $stmt = $db->prepare("
                INSERT INTO projects (clickup_task_id, clickup_status, name, description, last_sync_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                clickup_status = VALUES(clickup_status), 
                name = VALUES(name), 
                description = VALUES(description),
                last_sync_at = NOW()
            ");
            
            $stmt->execute([$clickupId, $status, $name, $description]);
            $syncedCount++;
        }

        return $syncedCount;
    }
}