<?php
// src/Models/Status.php
namespace App\Models;

// Hilfsklasse mit Statuskonstanten.
// Die numerischen Werte entsprechen den IDs in der Datenbanktabelle 'approval_statuses'.
class Status
{
    const PENDING  = 1; // Ausstehend: Prämie wurde beantragt und wartet auf Genehmigung.
    const APPROVED = 2; // Genehmigt: Prämie wurde final freigegeben.
    const REJECTED = 3; // Abgelehnt: Prämie wurde abgelehnt.
}
