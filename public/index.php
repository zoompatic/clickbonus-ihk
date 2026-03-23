<?php
// public/index.php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
// Wenn HTTPS aktiv ist: ini_set('session.cookie_secure', 1);

session_start();

/**
 * 1. SICHERHEITSEINSTELLUNGEN (Antigravity Punkt 1.E)
 * Schaltet die Fehlerausgabe im Browser ab, um Systempfade zu verbergen.
 */
ini_set('display_errors', 0); 
error_reporting(E_ALL);
ini_set('log_errors', 1);

/**
 * 2. MAGIC NUMBERS ELIMINIEREN (Antigravity Punkt 2.B)
 */
class Role {
    const IT_MANAGER = 1; // Maps both old Admin/Manager logic
    const PROJECT_MANAGER = 2;
    const HR = 3;
    const EMPLOYEE = 4;
}

// NEU: Status-Konstanten für bessere Lesbarkeit
class Status {
    const DRAFT = 1;
    const PENDING = 2;
    const APPROVED = 3;
    const REJECTED = 4;
}

/**
 * 3. CSRF-SCHUTZ GENERIEREN (Antigravity Punkt 1.A)
 * Erzeugt ein Token, das in Formularen als verstecktes Feld genutzt wird.
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

\App\Config::load(); // Lädt die .env Datei und macht die Werte verfügbar
use App\Database;
use App\Models\User;
use App\Models\Project;
use App\Models\Bonus;

try {
    $db = Database::getConnection();
} catch (Exception $e) {
    error_log("DB Fehler: " . $e->getMessage());
    die("Kritischer Fehler: Datenbankverbindung fehlgeschlagen.");
}

$action = $_GET['action'] ?? '';

/**
 * 4. CSRF VALIDIERUNG (Antigravity Punkt 1.A)
 * Prüft bei allen POST-Anfragen, ob das Token korrekt ist.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'login') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF-Token ungültig. Anfrage wurde aus Sicherheitsgründen blockiert.");
    }
}

// --- BASIS LOGIK (Logout/Login) ---
if ($action === 'logout') {
    session_destroy();
    header("Location: ?action=login");
    exit;
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = User::authenticate($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($user) {
        // FIX: Session Fixation Schutz (Antigravity Punkt 1.D)
        session_regenerate_id(true); 
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = (int)$user['role_id'];
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

// --- ZUGRIFFSSCHUTZ ---
if (!isset($_SESSION['user_id']) && $action !== 'login') {
    header("Location: ?action=login");
    exit;
}

$roleId = (int)($_SESSION['role_id'] ?? 0);

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

if ($roleId === Role::HR && !in_array($action, ['hr_list', 'logout'])) {
    header("Location: ?action=hr_list");
    exit;
}

// --- AKTIONEN (Speichern in der Datenbank) ---

if ($action === 'sync') {
    $importer = new \App\Services\ClickUpImport();
    $count = $importer->syncProjects();
    $_SESSION['success_msg'] = "$count Projekte synchronisiert!";
    header("Location: ?action=projects");
    exit;
}

if ($action === 'assign_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Project::assignUser($_POST['project_id'], $_POST['user_id']);
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

if ($action === 'store_bonus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)str_replace(',', '.', $_POST['amount']);
    if (Bonus::create($_POST['assignment_id'], $amount, $_POST['comment'] ?? '', $_SESSION['user_id'])) {
        $_SESSION['success_msg'] = "Prämie erfolgreich beantragt!";
    }
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

if ($action === 'update_bonus_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Bonus::processApproval($_POST['bonus_id'], $_SESSION['user_id'], ($_POST['action_type'] === 'approve'), $_POST['comment'] ?? '');
    header("Location: ?action=bonuses");
    exit;
}

// --- VIEWS ---
require_once __DIR__ . '/../views/layouts/header.php';

switch ($action) {
    case 'login':
        require_once __DIR__ . '/../views/login.php';
        break;
    case 'projects':
        $projects = Project::getAll();
        require_once __DIR__ . '/../views/projects.php';
        break;
    case 'my_projects':
        $projects = Project::getByUserId($_SESSION['user_id']);
        require_once __DIR__ . '/../views/my_projects.php';
        break;

    case 'assign':
        $project = Project::getById($_GET['project_id']);
        $assignedUsers = Project::getAssignedUsers($_GET['project_id']);
        foreach ($assignedUsers as $key => $au) {
            $assignedUsers[$key]['bonuses'] = Bonus::getForAssignment($au['assignment_id']);
        }
        $allUsers = User::getAllActive();
        require_once __DIR__ . '/../views/assign.php';
        break;
    case 'bonuses':
        $allBonuses = Bonus::getAllWithDetails();
        require_once __DIR__ . '/../views/bonuses.php';
        break;
    case 'hr_list':
        $bonuses = Bonus::getFullyApproved(null, $_GET['start_date'] ?? null, $_GET['end_date'] ?? null);
        if (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1') {
            $grouped = [];
            foreach ($bonuses as $b) {
                $name = $b['last_name'] . ', ' . $b['first_name'];
                $grouped[$name]['total'] = ($grouped[$name]['total'] ?? 0) + $b['amount'];
                $grouped[$name]['items'][] = $b;
            }
            $bonuses = $grouped;
        }
        require_once __DIR__ . '/../views/hr_list.php';
        break;
    default:
        echo "<h2>404 - Seite nicht gefunden</h2>";
        break;
}

require_once __DIR__ . '/../views/layouts/footer.php';