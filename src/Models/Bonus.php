<?php
// src/Models/Bonus.php
namespace App\Models;

use App\Database;
use PDO;

class Status
{
    const DRAFT = 1;
    const PENDING = 2;
    const APPROVED = 3;
    const REJECTED = 4;
}

class Bonus
{
    public static function create($assignmentId, $amount, $comment, $creatorUserId)
    {
        $database = Database::getConnection();
        try {
            $database->beginTransaction();

            $statement = $database->prepare("INSERT INTO bonuses (project_assignment_id, amount, comment, created_by) VALUES (?, ?, ?, ?)");
            $statement->execute([$assignmentId, $amount, $comment, $creatorUserId]);
            $bonusId = $database->lastInsertId();

            $approvalStatement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
            $approvalStatement->execute([$bonusId, $creatorUserId, Status::PENDING, $comment]);

            $database->commit();
            return true;
        }
        catch (\Exception $error) {
            $database->rollBack();
            error_log("Fehler beim Erstellen der Prämie: " . $error->getMessage());
            return false;
        }
    }

    public static function getById($id)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("SELECT * FROM bonuses WHERE id = ?");
        $statement->execute([$id]);
        return $statement->fetch();
    }

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
            AND v.current_status_id IN (" . Status::DRAFT . ", " . Status::PENDING . ")
            ORDER BY b.created_at DESC
        ";
        return $database->query($sql)->fetchAll();
    }

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

        $statement = $database->prepare($sql . " ORDER BY v.last_update DESC");
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function processApproval($bonusId, $userId, $isApproved, $comment = '')
    {
        $database = Database::getConnection();

        $statusId = $isApproved ? Status::APPROVED : Status::REJECTED;

        $statement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
        return $statement->execute([$bonusId, $userId, $statusId, $comment]);
    }
}