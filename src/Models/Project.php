<?php
namespace App\Models;

use App\Database;
use PDO;

class Project {
    
    /**
     * Holt alle Projekte aus der Datenbank
     */
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM projects WHERE deleted_at IS NULL ORDER BY last_sync_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Holt ein einzelnes Projekt anhand seiner ID
     */
    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Holt alle Mitarbeiter, die diesem Projekt bereits zugewiesen sind
     */
    public static function getAssignedUsers($projectId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pa.id as assignment_id, u.first_name, u.last_name, r.role_name 
            FROM project_assignments pa
            JOIN users u ON pa.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            WHERE pa.project_id = ?
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Weist einem Projekt einen User zu (speichert in project_assignments)
     */
    public static function assignUser($projectId, $userId) {
        $db = Database::getConnection();
        
        $check = $db->prepare("SELECT id FROM project_assignments WHERE project_id = ? AND user_id = ?");
        $check->execute([$projectId, $userId]);
        if ($check->fetch()) {
            return false; // Ist schon zugewiesen
        }

        $stmt = $db->prepare("INSERT INTO project_assignments (project_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$projectId, $userId]);
    }

    /**
     * Zählt alle aktiven Projekte für das Dashboard
     */
    public static function getTotalCount() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL");
        return $stmt->fetchColumn();
    }

    /**
     * Holt alle Projekte, denen ein bestimmter Benutzer zugewiesen ist (Für "Meine Projekte")
     */
    public static function getByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.* FROM projects p
            JOIN project_assignments pa ON p.id = pa.project_id
            WHERE pa.user_id = ? AND p.deleted_at IS NULL
            ORDER BY p.last_sync_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}