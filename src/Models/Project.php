<?php
// src/Models/Project.php
namespace App\Models;

use App\Database;
use PDO;

// Diese Klasse verwaltet Projekte: Abrufen, Zuweisen von Benutzern usw.
class Project
{

    public static function getAll()
    {
        $database = Database::getConnection();
        $statement = $database->query("SELECT * FROM projects WHERE deleted_at IS NULL ORDER BY last_sync_at DESC");
        return $statement->fetchAll();
    }

    public static function getById($id)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("SELECT * FROM projects WHERE id = ? AND deleted_at IS NULL");
        $statement->execute([$id]);
        return $statement->fetch();
    }

    public static function getAssignedUsers($projectId)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("
            SELECT pa.id as assignment_id, u.first_name, u.last_name, r.role_name 
            FROM project_assignments pa
            JOIN users u ON pa.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            WHERE pa.project_id = ?
        ");
        $statement->execute([$projectId]);
        return $statement->fetchAll();
    }

    public static function assignUser($projectId, $userId)
    {
        $database = Database::getConnection();

        $checkStatement = $database->prepare("SELECT id FROM project_assignments WHERE project_id = ? AND user_id = ?");
        $checkStatement->execute([$projectId, $userId]);
        if ($checkStatement->fetch()) {
            return false;
        }

        $statement = $database->prepare("INSERT INTO project_assignments (project_id, user_id) VALUES (?, ?)");
        return $statement->execute([$projectId, $userId]);
    }

    public static function getTotalCount()
    {
        $database = Database::getConnection();
        $statement = $database->query("SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL");
        return $statement->fetchColumn();
    }

    public static function getByUserId($userId)
    {
        $database = Database::getConnection();
        $statement = $database->prepare("
            SELECT p.* FROM projects p
            JOIN project_assignments pa ON p.id = pa.project_id
            WHERE pa.user_id = ? AND p.deleted_at IS NULL
            ORDER BY p.last_sync_at DESC
        ");
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }
}