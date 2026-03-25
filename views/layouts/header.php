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

    
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="site-header no-print">
    <div class="container nav-container">
        <a href="index.php" class="brand-logo">
            Click<span>Bonus</span>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php $roleId = (int)($_SESSION['role_id'] ?? 0); ?>
            
            <!-- Button für mobiles Menü. -->
            <button class="menu-toggle" id="mobile-menu-btn" aria-label="Menü öffnen">☰</button>
            
            <nav class="nav-links" id="main-nav">

                
                <?php if ($roleId === 1): ?>
                    <a href="?action=projects">Projekte</a>
                <?php
    endif; ?>

                <?php if ($roleId === 2): ?>
                    <a href="?action=my_projects">Projekte</a>
                <?php
    endif; ?>

                <?php if (in_array($roleId, [1, 2])): ?>
                    <a href="?action=bonuses">Freigaben</a>
                <?php
    endif; ?>

                <?php if (in_array($roleId, [1, 3])): ?>
                    <a href="?action=hr_list">HR-Liste</a>
                <?php
    endif; ?>


                
<?php
    // Bestimme den Anzeigenamen (Vorname) und die Rolle
    $displayName = $_SESSION['first_name'] ?? explode(' ', $_SESSION['user_name'] ?? '')[0];
    $displayRole = $_SESSION['role_name'] ?? 'User';
?>
                
                <!-- Logout-Link mit Benutzerinfo. -->
                <a href="?action=logout" style="color: #ffcccc; border-left: 1px solid #444; padding-left: 15px; margin-left: 5px;">
                    Logout (<?php echo htmlspecialchars($displayName); ?> | <small><?php echo htmlspecialchars($displayRole); ?></small>)
                </a>
            </nav>
        <?php
endif; ?>
    </div>
</header>   

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- JavaScript für das mobile Menü. -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const mainNav = document.getElementById('main-nav');
            if(menuBtn && mainNav) {
                menuBtn.addEventListener('click', () => {
                    mainNav.classList.toggle('active');
                    menuBtn.textContent = mainNav.classList.contains('active') ? '✕' : '☰';
                });
            }
        });
    </script>
<?php
endif; ?>

<main class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
    
    <!-- Zeigt Erfolgs- oder Fehlermeldungen an. -->
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-error">
            ❌ <?php echo htmlspecialchars($_SESSION['error_msg']);
    unset($_SESSION['error_msg']); ?>
        </div>
    <?php
endif; ?>
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            ✅ <?php echo htmlspecialchars($_SESSION['success_msg']);
    unset($_SESSION['success_msg']); ?>
        </div>
    <?php
endif; ?>