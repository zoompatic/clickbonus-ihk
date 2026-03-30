<?php
// src/Models/Bonus.php
namespace App\Models;

use App\Database;
use PDO;

// Diese Hilfsklasse enthält Statuskonstanten für Prämien und Genehmigungen.
// Die numerischen Werte entsprechen den IDs in der Datenbanktabelle 'approval_statuses'.
class Status
{
    const PENDING  = 1; // Ausstehend: Prämie wurde beantragt und wartet auf Genehmigung.
    const APPROVED = 2; // Genehmigt: Prämie wurde final freigegeben.
    const REJECTED = 3; // Abgelehnt: Prämie wurde abgelehnt.
}

// Diese Klasse verwaltet alle Datenbankoperationen rund um Prämien.
// Sie deckt das Erstellen, Abrufen und Genehmigen von Prämien ab.
class Bonus
{
    // Erstellt eine neue Prämie und legt gleichzeitig den ersten Genehmigungseintrag an.
    // Beides wird in einer Transaktion ausgeführt, damit keine inkonsistenten Daten entstehen.
    public static function create($assignmentId, $amount, $comment, $creatorUserId)
    {
        $database = Database::getConnection();
        try {
            $database->beginTransaction();

            // Prämie in der Haupttabelle speichern.
            $statement = $database->prepare("INSERT INTO bonuses (project_assignment_id, amount, comment, created_by) VALUES (?, ?, ?, ?)");
            $statement->execute([$assignmentId, $amount, $comment, $creatorUserId]);
            $bonusId = $database->lastInsertId();

            // Ersten Genehmigungseintrag mit Status 'Ausstehend' anlegen.
            $approvalStatement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
            $approvalStatement->execute([$bonusId, $creatorUserId, Status::PENDING, "Prämie beantragt"]);

            $database->commit();
            return true;
        }
        catch (\Exception $error) {
            // Bei einem Fehler werden alle Änderungen rückgängig gemacht.
            $database->rollBack();
            error_log("Fehler beim Erstellen der Prämie: " . $error->getMessage());
            return false;
        }
    }

    // Holt eine einzelne Prämie anhand ihrer ID.
    // Wird z. B. vor der Statusprüfung beim Vier-Augen-Prinzip verwendet.
    public static function getById($id)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("SELECT * FROM bonuses WHERE id = ?");
        $statement->execute([$id]);
        return $statement->fetch();
    }

    // Holt alle Prämien für eine bestimmte Projektzuweisung (project_assignment).
    // Wird auf der Zuweisungsseite angezeigt, um den aktuellen Status je Mitarbeiter zu sehen.
    public static function getForAssignment($assignmentId)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("
            SELECT b.*, v.current_status 
            FROM bonuses b
            JOIN view_bonus_status v ON b.id = v.bonus_id
            WHERE b.project_assignment_id = ? AND b.deleted_at IS NULL
        ");
        $statement->execute([$assignmentId]);
        return $statement->fetchAll();
    }

    // Holt alle offenen Prämien (Status: Entwurf oder Ausstehend) mit allen relevanten Details.
    // Diese Methode wird für die Freigabe-Übersicht der Manager verwendet.
    public static function getAllWithDetails()
    {
        $database = Database::getConnection();
        $sql = "
            SELECT b.id as bonus_id, b.amount, b.comment, b.created_at,
                   u.first_name, u.last_name, p.name as project_name,
                   req_u.first_name as req_first_name, req_u.last_name as req_last_name,
                   v.current_status, v.current_status_id, b.created_by
            FROM bonuses b
            JOIN view_bonus_status v ON b.id = v.bonus_id
            JOIN project_assignments pa ON b.project_assignment_id = pa.id
            JOIN users u ON pa.user_id = u.id
            JOIN projects p ON pa.project_id = p.id
            LEFT JOIN users req_u ON b.created_by = req_u.id
            WHERE b.deleted_at IS NULL 
            AND v.current_status_id = " . Status::PENDING . "
            ORDER BY b.created_at DESC
        ";
        return $database->query($sql)->fetchAll();
    }

    // Holt alle final genehmigten Prämien für die HR-Auszahlungsliste.
    // Filter nach Benutzer und Zeitraum sind optional und werden nur bei Bedarf angewendet.
    public static function getFullyApproved($limitToUserId = null, $startDate = null, $endDate = null)
    {
        $database = Database::getConnection();

        $sql = "SELECT b.*, u.first_name, u.last_name, p.name as project_name, v.last_update as approved_at
                FROM bonuses b 
                JOIN view_bonus_status v ON b.id = v.bonus_id
                JOIN project_assignments pa ON b.project_assignment_id = pa.id 
                JOIN users u ON pa.user_id = u.id 
                JOIN projects p ON pa.project_id = p.id 
                WHERE b.deleted_at IS NULL AND v.current_status_id = " . Status::APPROVED;

        $params = [];

        // Optionaler Filter: nur Prämien eines bestimmten Benutzers.
        if ($limitToUserId) {
            $sql .= " AND pa.user_id = :uid";
            $params['uid'] = $limitToUserId;
        }
        // Optionaler Filter: nur Prämien ab einem bestimmten Datum.
        if ($startDate) {
            $sql .= " AND v.last_update >= :sd";
            $params['sd'] = $startDate . " 00:00:00";
        }
        // Optionaler Filter: nur Prämien bis zu einem bestimmten Datum.
        if ($endDate) {
            $sql .= " AND v.last_update <= :ed";
            $params['ed'] = $endDate . " 23:59:59";
        }

        $statement = $database->prepare($sql . " ORDER BY v.last_update DESC");
        $statement->execute($params);
        return $statement->fetchAll();
    }

    // Verarbeitet eine Genehmigung oder Ablehnung einer Prämie.
    // Ein neuer Eintrag in der 'approvals'-Tabelle wird mit dem entsprechenden Status angelegt.
    public static function processApproval($bonusId, $userId, $isApproved, $comment = '')
    {
        $database = Database::getConnection();

        // Statuswert anhand der Entscheidung des Benutzers auswählen.
        $statusId = $isApproved ? Status::APPROVED : Status::REJECTED;

        $statement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
        return $statement->execute([$bonusId, $userId, $statusId, $comment]);
    }
}