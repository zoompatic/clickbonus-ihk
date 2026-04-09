<?php
// src/Models/Bonus.php
namespace App\Models;

use App\Database;
use App\Models\Status;
use PDO;

// Diese Klasse verwaltet alle Datenbankoperationen rund um Prämien.
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

    public static function createManual($targetUserId, $amount, $comment, $creatorUserId)
    {
        $database = Database::getConnection();
        try {
            $database->beginTransaction();

            $statement = $database->prepare("INSERT INTO bonuses (target_user_id, amount, comment, created_by) VALUES (?, ?, ?, ?)");
            $statement->execute([$targetUserId, $amount, $comment, $creatorUserId]);
            $bonusId = $database->lastInsertId();

            $approvalStatement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
            $approvalStatement->execute([$bonusId, $creatorUserId, Status::PENDING, $comment]);

            $database->commit();
            return true;
        }
        catch (\Exception $error) {
            $database->rollBack();
            error_log("Fehler beim Erstellen der manuellen Prämie: " . $error->getMessage());
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

    public static function getAllWithDetails($managerId = null, $onlyManual = false)
    {
        $database = Database::getConnection();
        $sql = "
            SELECT b.id as bonus_id, b.amount, b.comment, b.created_at, b.project_assignment_id,
                   COALESCE(u.first_name, u_man.first_name) as first_name, 
                   COALESCE(u.last_name, u_man.last_name) as last_name, 
                   COALESCE(p.name, 'Manuelle Prämie') as project_name, p.id as project_id,
                   req_u.first_name as req_first_name, req_u.last_name as req_last_name, req_u.role_id as req_role_id,
                   v.current_status, v.current_status_id, b.created_by
            FROM bonuses b
            JOIN view_bonus_status v ON b.id = v.bonus_id
            LEFT JOIN project_assignments pa ON b.project_assignment_id = pa.id
            LEFT JOIN users u ON pa.user_id = u.id
            LEFT JOIN projects p ON pa.project_id = p.id
            LEFT JOIN users u_man ON b.target_user_id = u_man.id
            LEFT JOIN users req_u ON b.created_by = req_u.id
            WHERE b.deleted_at IS NULL 
            AND v.current_status_id = " . Status::PENDING . "
        ";
        
        $params = [];
        if ($managerId !== null) {
            $sql .= " AND (p.id IN (SELECT project_id FROM project_assignments WHERE user_id = :mid1) OR b.created_by = :mid2)";
            $params['mid1'] = $managerId;
            $params['mid2'] = $managerId;
        }

        if ($onlyManual) {
            $sql .= " AND b.project_assignment_id IS NULL";
        }
        
        $sql .= " ORDER BY b.created_at DESC";

        $statement = $database->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function getFullyApproved($limitToUserId = null, $startDate = null, $endDate = null)
    {
        $database = Database::getConnection();

        $sql = "SELECT b.*, 
                COALESCE(u.first_name, u_man.first_name) as first_name, 
                COALESCE(u.last_name, u_man.last_name) as last_name, 
                COALESCE(p.name, 'Manuelle Prämie') as project_name, 
                v.last_update as approved_at
                FROM bonuses b 
                JOIN view_bonus_status v ON b.id = v.bonus_id
                LEFT JOIN project_assignments pa ON b.project_assignment_id = pa.id 
                LEFT JOIN users u ON pa.user_id = u.id 
                LEFT JOIN projects p ON pa.project_id = p.id 
                LEFT JOIN users u_man ON b.target_user_id = u_man.id
                WHERE b.deleted_at IS NULL AND v.current_status_id = " . Status::APPROVED;

        $params = [];

        if ($limitToUserId) {
            $sql .= " AND (pa.user_id = :uid OR b.target_user_id = :uid)";
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

        if (empty(trim($comment))) {
            $comment = $isApproved ? 'Prämie genehmigt' : 'Prämie abgelehnt';
        }

        $statement = $database->prepare("INSERT INTO approvals (bonus_id, user_id, approval_status_id, comment) VALUES (?, ?, ?, ?)");
        return $statement->execute([$bonusId, $userId, $statusId, $comment]);
    }

    public static function getAuditLog()
    {
        $database = Database::getConnection();
        $sql = "
            SELECT a.id, a.created_at, a.comment, a.approval_status_id, b.comment as bonus_comment,
                   u_actor.first_name as actor_first, u_actor.last_name as actor_last,
                   b.amount, COALESCE(p.name, 'Manuelle Prämie') as project_name,
                   COALESCE(u_target.first_name, u_man.first_name) as target_first, 
                   COALESCE(u_target.last_name, u_man.last_name) as target_last
            FROM approvals a
            JOIN bonuses b ON a.bonus_id = b.id
            JOIN users u_actor ON a.user_id = u_actor.id
            LEFT JOIN project_assignments pa ON b.project_assignment_id = pa.id
            LEFT JOIN users u_target ON pa.user_id = u_target.id
            LEFT JOIN projects p ON pa.project_id = p.id
            LEFT JOIN users u_man ON b.target_user_id = u_man.id
            ORDER BY a.created_at DESC
        ";
        return $database->query($sql)->fetchAll();
    }
}