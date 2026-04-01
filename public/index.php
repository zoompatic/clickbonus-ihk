<?php
// public/index.php
// Dies ist der zentrale Einstiegspunkt der Anwendung (Front-Controller).
// Alle Anfragen laufen hier durch: Sicherheitsprüfungen, Routing und Ausgabe.

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);


// CSRF-Token erstellen, falls noch keiner in der Session vorhanden ist.
// Dieser Token schützt alle Formulare vor Cross-Site-Request-Forgery-Angriffen.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Autoloader: Lädt Klassen automatisch anhand ihres Namespaces und Dateinamens.
// So müssen nicht alle Klassen manuell per require_once eingebunden werden.
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file))
        require $file;
});

// Konfiguration aus der .env-Datei laden und Datenbankverbindung aufbauen.
\App\Config::load();
use App\Database;
use App\Models\User;
use App\Models\Project;
use App\Models\Bonus;
use App\Models\Role;
use App\Models\Status;

try {
    $database = Database::getConnection();
} catch (Exception $error) {
    error_log("DB Fehler: " . $error->getMessage());
    die("Kritischer Fehler: Datenbankverbindung fehlgeschlagen.");
}

// Aktuelle Aktion aus der URL lesen (z. B. ?action=login).
$action = $_GET['action'] ?? '';

// CSRF-Prüfung: Alle POST-Anfragen außer dem Login werden auf ein gültiges Token geprüft.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'login') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF-Token ungültig. Anfrage wurde aus Sicherheitsgründen blockiert.");
    }
}

// Logout: Session komplett zerstören und zur Login-Seite weiterleiten.
if ($action === 'logout') {
    session_destroy();
    header("Location: ?action=login");
    exit;
}

// Login: Benutzer anhand von E-Mail und Passwort prüfen und Session aufbauen.
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = User::authenticate($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($user) {
        // Session-ID nach erfolgreichem Login erneuern, um Session-Fixation zu verhindern.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = (int) $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['first_name'] = $user['first_name'];

        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "Login fehlgeschlagen. Bitte prüfe E-Mail und Passwort.";
        header("Location: ?action=login");
        exit;
    }
}

// Zugriffsschutz: Nicht angemeldete Benutzer werden immer zur Login-Seite umgeleitet.
if (!isset($_SESSION['user_id']) && $action !== 'login') {
    header("Location: ?action=login");
    exit;
}

$roleId = (int) ($_SESSION['role_id'] ?? 0);

// Startseiten-Weiterleitung: Je nach Rolle wird die passende Startseite geladen.
if ($action === '') {
    if ($roleId === Role::IT_MANAGER) {
        header("Location: ?action=projects");
    } elseif ($roleId === Role::PROJECT_MANAGER) {
        header("Location: ?action=my_projects");
    } elseif ($roleId === Role::HR) {
        header("Location: ?action=hr_list");
    }
    exit;
}

// Rollenbasierte Zugriffsbeschränkung: HR darf nur die HR-Liste aufrufen.
if ($roleId === Role::HR && !in_array($action, ['hr_list', 'logout'])) {
    header("Location: ?action=hr_list");
    exit;
}

// Rollenbasierte Zugriffsbeschränkung: Die HR-Liste ist nur für IT-Manager und HR zugänglich.
if ($action === 'hr_list' && !in_array($roleId, [Role::IT_MANAGER, Role::HR])) {
    header("Location: index.php");
    exit;
}

// Synchronisation: Projekte von der ClickUp-API abrufen und in der Datenbank aktualisieren.
if ($action === 'sync') {
    $importer = new \App\Services\ClickUpImport();
    $count = $importer->syncProjects();
    $_SESSION['success_msg'] = "$count Projekte synchronisiert!";
    header("Location: ?action=projects");
    exit;
}

// Mitarbeiterzuweisung: Einen Benutzer einem Projekt zuordnen.
if ($action === 'assign_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Project::assignUser($_POST['project_id'], $_POST['user_id']);
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

// Prämie beantragen: Eine neue Prämie für eine Projektzuweisung anlegen.
if ($action === 'store_bonus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Betrag normalisieren: Komma als Dezimaltrennzeichen wird in einen Punkt umgewandelt.
    $amount = (float) str_replace(',', '.', $_POST['amount']);
    if (Bonus::create($_POST['assignment_id'], $amount, $_POST['comment'] ?? '', $_SESSION['user_id'])) {
        $_SESSION['success_msg'] = "Prämie erfolgreich beantragt!";
    }
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

// Prämie genehmigen oder ablehnen.
// Vier-Augen-Prinzip: Wer eine Prämie beantragt hat, darf sie nicht selbst freigeben.
if ($action === 'update_bonus_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bonus = Bonus::getById($_POST['bonus_id']);

    if ($bonus && $bonus['created_by'] == $_SESSION['user_id']) {
        // Sicherheitsregel: Eigene Prämien können nicht selbst genehmigt werden.
        $_SESSION['error_msg'] = "Vier-Augen-Prinzip: Sie können keine Prämien freigeben, die Sie selbst beantragt haben.";
    } else {
        Bonus::processApproval($_POST['bonus_id'], $_SESSION['user_id'], ($_POST['action_type'] === 'approve'), $_POST['comment'] ?? '');
    }

    header("Location: ?action=bonuses");
    exit;
}

// Ausgabe: Gemeinsamen Header für alle Seiten laden.
require_once __DIR__ . '/../views/layouts/header.php';

// Routing: Anhand der Aktion wird die passende View geladen und mit Daten versorgt.
switch ($action) {
    case 'login':
        require_once __DIR__ . '/../views/login.php';
        break;

    case 'projects':
        // Alle aktiven Projekte für die IT-Manager-Übersicht abrufen.
        $projects = Project::getAll();
        $viewModus = 'manager'; // Modus für IT-Manager: zeigt Import-Button und Zuweisungsfunktion.
        require_once __DIR__ . '/../views/projects.php';
        break;

    case 'my_projects':
        // Nur die Projekte laden, denen der angemeldete Projektmanager zugewiesen ist.
        $projects = Project::getByUserId($_SESSION['user_id']);
        $viewModus = 'my_projects'; // Modus für Projektmanager: zeigt nur eigene Projekte und Prämien-Detail.
        require_once __DIR__ . '/../views/projects.php';
        break;

    case 'assign':
        // Projektdetails, zugewiesene Mitarbeiter und deren Prämien für die Zuweisungsseite laden.
        $project = Project::getById($_GET['project_id']);
        $assignedUsers = Project::getAssignedUsers($_GET['project_id']);
        foreach ($assignedUsers as $key => $assignedEmployee) {
            // Prämien je Mitarbeiterzuweisung ergänzen.
            $assignedUsers[$key]['bonuses'] = Bonus::getForAssignment($assignedEmployee['assignment_id']);
        }
        $allUsers = User::getAllActive();
        require_once __DIR__ . '/../views/assign.php';
        break;

    case 'bonuses':
        // Alle offenen Prämien (Entwurf & Ausstehend) für die Freigabe-Übersicht laden.
        $allBonuses = Bonus::getAllWithDetails();
        require_once __DIR__ . '/../views/bonuses.php';
        break;

    case 'hr_list':
        // Genehmigte Prämien für die HR-Auszahlungsliste laden, optional gefiltert nach Zeitraum.
        $allBonuses = Bonus::getFullyApproved(null, $_GET['start_date'] ?? null, $_GET['end_date'] ?? null);
        if (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1') {
            // Prämien nach Mitarbeiter gruppieren und Gesamtbetrag je Person berechnen.
            $groupedBonuses = [];
            foreach ($allBonuses as $bonus) {
                $name = $bonus['last_name'] . ', ' . $bonus['first_name'];
                $groupedBonuses[$name]['total'] = ($groupedBonuses[$name]['total'] ?? 0) + $bonus['amount'];
                $groupedBonuses[$name]['items'][] = $bonus;
            }
            $allBonuses = $groupedBonuses;
        }
        require_once __DIR__ . '/../views/hr_list.php';
        break;

    default:
        echo "<h2>404 - Seite nicht gefunden</h2>";
        break;
}

// Gemeinsamen Footer für alle Seiten laden.
require_once __DIR__ . '/../views/layouts/footer.php';