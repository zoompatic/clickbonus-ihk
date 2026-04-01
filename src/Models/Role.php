<?php
// src/Models/Role.php
namespace App\Models;

// Hilfsklasse mit Rollenkonstanten.
// Die numerischen Werte entsprechen den IDs in der Datenbanktabelle 'roles'.
class Role
{
    const IT_MANAGER      = 1;
    const PROJECT_MANAGER = 2;
    const HR              = 3;
    const EMPLOYEE        = 4;
}
