<?php
// views/layouts/header.php
// Dies ist der obere Teil jeder Seite: Logo, Navigation und Meldungen.
// Wie der Kopf einer Webseite mit Menü und Benachrichtigungen.
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClickBonus | Monolith West</title>
    <link rel="icon" href="favicon.png" type="image/png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Eigene Styles (Overrides) -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-primary border-4 mb-4 d-print-none">
    <div class="container">
        <a class="navbar-brand fw-bold mb-0 h1" href="index.php">
            Click<span class="text-secondary fw-light">Bonus</span>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php $roleId = (int)($_SESSION['role_id'] ?? 0); ?>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-lg-0 text-uppercase d-flex align-items-center gap-2" style="font-size: 0.75rem; font-weight: 600;">
                    
                    <?php if ($roleId === 1): ?>
                        <li class="nav-item"><a class="nav-link px-3" href="?action=projects">Projekte</a></li>
                    <?php endif; ?>

                    <?php if ($roleId === 2): ?>
                        <li class="nav-item"><a class="nav-link px-3" href="?action=my_projects">Projekte</a></li>
                    <?php endif; ?>

                    <?php if (in_array($roleId, [1, 2])): ?>
                        <li class="nav-item"><a class="nav-link px-3" href="?action=bonuses">Freigaben</a></li>
                    <?php endif; ?>

                    <?php if (in_array($roleId, [1, 3])): ?>
                        <li class="nav-item"><a class="nav-link px-3" href="?action=hr_list">HR-Liste</a></li>
                    <?php endif; ?>

                    <!-- Vertical divider for the layout (desktop only) -->
                    <li class="nav-item d-none d-lg-block">
                        <span class="text-secondary" style="border-left: 1px solid #444; height: 20px; display: inline-block; margin: 0 10px; vertical-align: middle;"></span>
                    </li>

                    <?php 
                        $displayName = $_SESSION['first_name'] ?? explode(' ', $_SESSION['user_name'] ?? '')[0];
                        $displayRole = $_SESSION['role_name'] ?? 'User';
                    ?>
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link text-uppercase" style="color: #ffcccc !important;">
                            Logout (<span class="fw-bold"><?php echo htmlspecialchars($displayName); ?></span> | <span style="font-size: 0.65rem;"><?php echo htmlspecialchars($displayRole); ?></span>)
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>

<main class="container py-2">
    
    <!-- Meldungen -->
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger d-print-none" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success d-print-none" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>