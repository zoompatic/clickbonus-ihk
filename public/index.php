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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

\App\Config::load();
use App\Database;
use App\Models\User;
use App\Models\Project;
use App\Models\Bonus;
use App\Models\Role;

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

if (!isset($_SESSION['user_id']) && $action !== 'login') {
    header("Location: ?action=login");
    exit;
}

$roleId = (int) ($_SESSION['role_id'] ?? 0);

if ($action === '' || $action === 'index') {
    if (in_array($roleId, [Role::IT_MANAGER, Role::PROJECT_MANAGER])) {
        header("Location: ?action=projects");
    } elseif ($roleId === Role::HR) {
        header("Location: ?action=hr_list");
    }
    exit;
}

if ($roleId === Role::HR && !in_array($action, ['hr_list', 'profile', 'update_profile_password', 'logout', 'bonuses', 'update_bonus_status'])) {
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

if ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role_id'] == Role::IT_MANAGER && $_POST['user_id'] != $_SESSION['user_id']) {
        User::delete($_POST['user_id']);
        $_SESSION['success_msg'] = "Benutzer wurde gelöscht.";
    }
    header("Location: ?action=users");
    exit;
}

if ($action === 'update_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role_id'] == Role::IT_MANAGER) {
        $ok = User::update($_POST['user_id'], $_POST['role_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'] ?? '');
        if ($ok) {
            $_SESSION['success_msg'] = "Benutzer wurde erfolgreich aktualisiert!";
        } else {
            $_SESSION['error_msg'] = "Fehler beim Aktualisieren.";
        }
    }
    header("Location: ?action=users");
    exit;
}

if ($action === 'update_profile_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_msg'] = "Das neue Passwort und die Wiederholung stimmen nicht überein.";
        header("Location: ?action=profile");
        exit;
    }

    if (User::verifyPasswordById($_SESSION['user_id'], $oldPassword)) {
        User::updatePassword($_SESSION['user_id'], $newPassword);
        $_SESSION['success_msg'] = "Passwort wurde erfolgreich geändert.";
        header("Location: ?action=profile");
    } else {
        $_SESSION['error_msg'] = "Das alte Passwort ist nicht korrekt.";
        header("Location: ?action=profile");
    }
    exit;
}

if ($action === 'store_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role_id'] == Role::IT_MANAGER) {
        $ok = User::create($_POST['role_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'] ?? '');
        if ($ok) {
            $_SESSION['success_msg'] = "Benutzer " . htmlspecialchars($_POST['first_name'] . ' ' . $_POST['last_name']) . " erfolgreich angelegt!";
        } else {
            $_SESSION['error_msg'] = "Fehler beim Anlegen des Benutzers (Evtl. existiert die E-Mail bereits).";
        }
    }
    header("Location: ?action=users");
    exit;
}

if ($action === 'assign_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Project::assignUser($_POST['project_id'], $_POST['user_id']);
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

if ($action === 'store_bonus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) str_replace(',', '.', $_POST['amount']);
    if (Bonus::create($_POST['assignment_id'], $amount, $_POST['comment'] ?? '', $_SESSION['user_id'])) {
        $_SESSION['success_msg'] = "Prämie erfolgreich beantragt!";
    }
    header("Location: ?action=assign&project_id=" . $_POST['project_id']);
    exit;
}

if ($action === 'store_manual_bonus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role_id'] == Role::IT_MANAGER) {
        $ok = Bonus::createManual($_POST['target_user_id'], str_replace(',', '.', $_POST['amount']), $_POST['comment'], $_SESSION['user_id']);
        if ($ok) {
            $_SESSION['success_msg'] = "Manuelle Prämie erfolgreich vergeben.";
        } else {
            $_SESSION['error_msg'] = "Fehler beim Erstellen der Prämie.";
        }
    }
    header("Location: ?action=bonuses");
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
        if ($_SESSION['role_id'] == Role::IT_MANAGER) {
            $projects = Project::getAll();
        } else {
            $projects = Project::getByUserId($_SESSION['user_id']);
        }
        require_once __DIR__ . '/../views/projects.php';
        break;

    case 'users':
        if ($_SESSION['role_id'] == Role::IT_MANAGER) {
            $allUsers = User::getAllActive();
            $allRoles = Role::getAllRoles();
            require_once __DIR__ . '/../views/users.php';
        } else {
            header("Location: index.php");
        }
        break;

    case 'edit_user':
        if ($_SESSION['role_id'] == Role::IT_MANAGER && isset($_GET['id'])) {
            $editUser = User::getById($_GET['id']);
            $allRoles = Role::getAllRoles();
            if ($editUser) {
                require_once __DIR__ . '/../views/user_edit.php';
            } else {
                header("Location: ?action=users");
            }
        } else {
            header("Location: index.php");
        }
        break;

    case 'profile':
        require_once __DIR__ . '/../views/profile.php';
        break;

    case 'manual_bonus':
        if ($_SESSION['role_id'] == Role::IT_MANAGER) {
            $allUsers = User::getAllActive();
            require_once __DIR__ . '/../views/manual_bonus.php';
        } else {
            header("Location: index.php");
        }
        break;

    case 'history':
        if ($_SESSION['role_id'] == Role::IT_MANAGER) {
            $auditLogs = Bonus::getAuditLog();
            require_once __DIR__ . '/../views/history.php';
        } else {
            header("Location: index.php");
        }
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
        if ($_SESSION['role_id'] == Role::PROJECT_MANAGER) {
            $allBonuses = Bonus::getAllWithDetails($_SESSION['user_id']);
        } elseif ($_SESSION['role_id'] == Role::HR) {
            // HR sees ONLY manual bonuses
            $allBonuses = Bonus::getAllWithDetails(null, true);
        } else {
            // IT Manager sees everything
            $allBonuses = Bonus::getAllWithDetails();
        }
        $itManagers = Database::getConnection()->query("SELECT id, first_name, last_name FROM users WHERE role_id = " . Role::IT_MANAGER . " AND deleted_at IS NULL")->fetchAll();
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