<?php
namespace App\Models;

use App\Database;
use PDO;

/**
 * Hilfsklasse für Status-Konstanten (Löst Magic Numbers Problem)
 * Wie Etiketten für verschiedene Zustände.
 */
class Status {
    const DRAFT = 1;
    const PENDING = 2;
    const APPROVED = 3;
    const REJECTED = 4;
}

class Bonus {

    /**
     * Erstellt eine neue Prämie und setzt den Startstatus auf 'PENDING' (Wartend).
     * Wie das Ausfüllen eines Antragsformulars und das Einwerfen in den Briefkasten des Chefs.
     */
    public static function create($assignmentId, $amount, $comment, $creatorUserId) {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // SQL Injection Schutz: Prepared Statements
            // Wie ein strenger Türsteher, der keine unerlaubten Zeichen oder Befehle in die Akte eintragen lässt.
            $stmt = $db->prepare("INSERT INTO bonuses (project_assignment_id, amount, comment, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$assignmentId, $amount, $comment, $creatorUserId]);
            $bonusId = $db->lastInsertId();

            // Nutzung der Konstante Status::PENDING statt der Zahl 2
            $stmtApp = $db->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
            $stmtApp->execute([$bonusId, $creatorUserId, Status::PENDING, "Prämie beantragt"]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Fehler beim Erstellen der Prämie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt Prämien für eine Zuweisung unter Nutzung der SQL-View.
     * Wie das Nachschlagen von Anträgen für ein bestimmtes Projekt.
     */
    public static function getForAssignment($assignmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT b.*, v.current_status 
            FROM bonuses b
            JOIN view_bonus_status v ON b.id = v.bonus_id
            WHERE b.project_assignment_id = ? AND b.deleted_at IS NULL
        ");
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    /**
     * Holt alle wartenden Prämien (DRAFT oder PENDING).
     * Wie ein Posteingangskorb auf dem Schreibtisch, in dem sämtliche noch nicht genehmigten Unterlagen liegen.
     */
    public static function getAllWithDetails() {
        $db = Database::getConnection();
        // Nutzung der Konstanten in der Query
        $sql = "
            SELECT b.id as bonus_id, b.amount, b.comment, b.created_at,
                   u.first_name, u.last_name, p.name as project_name,
                   req_u.first_name as req_first_name, req_u.last_name as req_last_name,
                   v.current_status, v.current_status_id
            FROM bonuses b
            JOIN view_bonus_status v ON b.id = v.bonus_id
            JOIN project_assignments pa ON b.project_assignment_id = pa.id
            JOIN users u ON pa.user_id = u.id
            JOIN projects p ON pa.project_id = p.id
            LEFT JOIN users req_u ON b.created_by = req_u.id
            WHERE b.deleted_at IS NULL 
            AND v.current_status_id IN (" . Status::DRAFT . ", " . Status::PENDING . ")
            ORDER BY b.created_at DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Holt ausschließlich die vollständig genehmigten Boni für die Auszahlung (HR-Liste).
     * Wie der Gang zum Buchhalter, der nur die Akten mit dem grünen "Genehmigt"-Stempel akzeptiert.
     */
    public static function getFullyApproved($limitToUserId = null, $startDate = null, $endDate = null) {
        $db = Database::getConnection();
        
        // Nutzung der Konstante Status::APPROVED statt der Zahl 3
        $sql = "SELECT b.*, u.first_name, u.last_name, p.name as project_name, v.last_update as approved_at
                FROM bonuses b 
                JOIN view_bonus_status v ON b.id = v.bonus_id
                JOIN project_assignments pa ON b.project_assignment_id = pa.id 
                JOIN users u ON pa.user_id = u.id 
                JOIN projects p ON pa.project_id = p.id 
                WHERE b.deleted_at IS NULL AND v.current_status_id = " . Status::APPROVED;
        
        $params = [];
        if ($limitToUserId) { 
            $sql .= " AND pa.user_id = :uid"; 
            $params['uid'] = $limitToUserId; 
        }
        if ($startDate) { 
            $sql .= " AND v.last_update >= :sd"; 
            $params['sd'] = $startDate . " 00:00:00"; 
        }
        if ($endDate) { 
            $sql .= " AND v.last_update <= :ed"; 
            $params['ed'] = $endDate . " 23:59:59"; 
        }
        
        $stmt = $db->prepare($sql . " ORDER BY v.last_update DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }



    /**
     * Verarbeitet die Genehmigung oder Ablehnung.
     * Wie das Stempeln eines Antrags mit "Genehmigt" oder "Abgelehnt".
     */
    public static function processApproval($bonusId, $userId, $isApproved, $comment = '') {
        $db = Database::getConnection();
        
        // Auswahl der Status-ID über Konstanten
        $statusId = $isApproved ? Status::APPROVED : Status::REJECTED;
        
        $stmt = $db->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$bonusId, $userId, $statusId, $comment]);
    }


}