<?php
// public/index.php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();

ini_set('display_errors', 0); 
error_reporting(E_ALL);
ini_set('log_errors', 1);

class Role {
    const IT_MANAGER = 1; 
    const PROJECT_MANAGER = 2;
    const HR = 3;
    const EMPLOYEE = 4;
}

class Status {
    const DRAFT = 1;
    const PENDING = 2;
    const APPROVED = 3;
    const REJECTED = 4;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

\App\Config::load(); 
use App\Database;
use App\Models\User;
use App\Models\Project;
use App\Models\Bonus;

try {
    $database = Database::getConnection();
} catch (Exception $error) {
    error_log("DB Fehler: " . $error->getMessage());
    die("Kritischer Fehler: Datenbankverbindung fehlgeschlagen.");
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'login') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF-Token ungültig. Anfrage wurde aus Sicherheitsgründen blockiert.");
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: ?action=login");
    exit;
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = User::authenticate($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($user) {
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

if ($action === 'hr_list' && !in_array($roleId, [Role::IT_MANAGER, Role::HR])) {
    header("Location: index.php");
    exit;
}

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
    $bonus = Bonus::getById($_POST['bonus_id']);
    
    if ($bonus && $bonus['created_by'] == $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "Vier-Augen-Prinzip: Sie können keine Prämien freigeben, die Sie selbst beantragt haben.";
    } else {
        Bonus::processApproval($_POST['bonus_id'], $_SESSION['user_id'], ($_POST['action_type'] === 'approve'), $_POST['comment'] ?? '');
    }
    
    header("Location: ?action=bonuses");
    exit;
}

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
        foreach ($assignedUsers as $key => $assignedEmployee) {
            $assignedUsers[$key]['bonuses'] = Bonus::getForAssignment($assignedEmployee['assignment_id']);
        }
        $allUsers = User::getAllActive();
        require_once __DIR__ . '/../views/assign.php';
        break;
    case 'bonuses':
        $allBonuses = Bonus::getAllWithDetails();
        require_once __DIR__ . '/../views/bonuses.php';
        break;
    case 'hr_list':
        $allBonuses = Bonus::getFullyApproved(null, $_GET['start_date'] ?? null, $_GET['end_date'] ?? null);
        if (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1') {
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

require_once __DIR__ . '/../views/layouts/footer.php';