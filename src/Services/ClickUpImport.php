<?php
// src/Services/ClickUpImport.php
namespace App\Services;

use App\Database;
use App\Config;
use Exception;

// Diese Klasse dient als Schnittstelle zwischen diesem System und ClickUp.
class ClickUpImport
{

    private $apiToken;
    private $listId;
    private $baseUrl = 'https://api.clickup.com/api/v2';

    public function __construct()
    {
        $this->apiToken = Config::get('CLICKUP_TOKEN');
        $this->listId = Config::get('CLICKUP_LIST_ID');
    }

    public function syncProjects()
    {
        if (!$this->apiToken || !$this->listId) {
            throw new Exception("Synchronisation nicht möglich: API-Token oder List-ID fehlen in der .env Datei.");
        }

        $url = $this->baseUrl . '/list/' . $this->listId . '/task?subtasks=false';

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, [
            "Authorization: " . $this->apiToken,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($curlHandle);
        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if ($httpCode !== 200) {
            throw new Exception("Fehler beim Abrufen der ClickUp-Daten. HTTP-Code: " . $httpCode . " (Bitte API-Token in der .env prüfen)");
        }

        $dataPackage = json_decode($response, true);
        $tasks = $dataPackage['tasks'] ?? [];

        $database = Database::getConnection();
        $syncedCount = 0;

        foreach ($tasks as $task) {
            $clickupId = !empty($task['custom_id']) ? $task['custom_id'] : $task['id'];
            $name = $task['name'];
            $status = $task['status']['status'] ?? 'unknown';
            $description = $task['description'] ?? '';

            $statement = $database->prepare("
                INSERT INTO projects (clickup_task_id, clickup_status, name, description, last_sync_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                clickup_status = VALUES(clickup_status), 
                name = VALUES(name), 
                description = VALUES(description),
                last_sync_at = NOW()
            ");

            $statement->execute([$clickupId, $status, $name, $description]);
            $syncedCount++;
        }

        return $syncedCount;
    }
}