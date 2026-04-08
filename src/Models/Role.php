<?php
// src/Models/Role.php
namespace App\Models;

use App\Database;
use PDO;

// Hilfsklasse mit Rollenkonstanten.
class Role
{
    const IT_MANAGER      = 1;
    const PROJECT_MANAGER = 2;
    const HR              = 3;
    const EMPLOYEE        = 4;

    public static function getAllRoles()
    {
        $database = Database::getConnection();
        $statement = $database->query("SELECT id, role_name FROM roles WHERE id <= 4 ORDER BY id ASC");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
