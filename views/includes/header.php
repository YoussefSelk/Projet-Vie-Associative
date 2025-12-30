<?php
// Inclusion des fichiers nécessaires (bootstrap already included via index.php)
require_once("include.php");
global $db;

if(isset($_SESSION['id'])){
    // Récupérer les informations de l'utilisateur
    $req_membre_club = $db->prepare("SELECT mc.* FROM membres_club mc LEFT JOIN fiche_club fc ON fc.club_id = mc.club_id WHERE mc.membre_id = ? AND mc.valide = 1 AND fc.validation_admin = 1 AND fc.validation_tuteur = 1");
    $req_membre_club->execute([$_SESSION['id']]);
    $infos_membre_club = $req_membre_club->fetchAll();    


    if(empty($infos_membre_club)){
        $is_membre_club = 0;
    } else {
        $is_membre_club = 1;
    }

    // Récupérer les informations des clubs gérés par le tuteur
    $req_0 = $db->prepare("SELECT club_id, nom_club FROM fiche_club
    WHERE validation_finale = 1 AND tuteur = ?");
    $req_0->execute([$_SESSION['id']]);
    $req_clubs=$req_0->fetchAll();

    // Récupérer le nombre d'événements en attente de validation
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_event WHERE validation_finale IS NULL AND validation_bde = 1 AND validation_tuteur = 1");
    $req->execute();
    $row = $req->fetchAll();
    $nb_events_admin = $row[0]['total'];

    // Récupérer le nombre de fiches club en attente de validation
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
    $req->execute();
    $row = $req->fetchAll();
    $nb_clubs_admin = $row[0]['total'];

    $nb_badge_admin = $nb_events_admin + $nb_clubs_admin;

    // Récupérer le nombre de demandes de tutorat en attente de validation
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_club WHERE (validation_tuteur IS NULL) AND tuteur = ?");
    $req->execute([$_SESSION['id']]);
    $row = $req->fetchAll();
    $nb_clubs_tuteur = $row[0]['total'];

    // Récupérer le nombre de fiches event en attente de validation
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_event f LEFT JOIN fiche_club fc ON fc.club_id = f.club_orga WHERE (f.validation_tuteur IS NULL) AND fc.tuteur = ?");
    $req->execute([$_SESSION['id']]);
    $row = $req->fetchAll();
    $nb_events_tuteur = $row[0]['total'];

    $nb_badge_tuteur = $nb_clubs_tuteur + $nb_events_tuteur;

    // Get user info
    $q = $db->prepare("SELECT * FROM users WHERE id = ?");
    $q->execute([$_SESSION['id']]);
    $current_user = $q->fetch();
}
else {
    $is_membre_club = 0;
    $nb_badge_admin = 0;
    $nb_badge_tuteur = 0;
    $current_user = null;
}

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /");
    exit();
}
?>

<!-- Modern Top Bar -->
<div class="top-bar">
    <div class="top-bar-container">
        <div class="top-bar-left">
            <a href="https://eilco.univ-littoral.fr" target="_blank" class="top-link">
                <i class="fas fa-external-link-alt"></i> Site EILCO
            </a>
            <a href="mailto:contact@eilco.fr" class="top-link">
                <i class="fas fa-envelope"></i> Contact
            </a>
        </div>
        <div class="top-bar-right">
            <?php if(isset($_SESSION["nom"])): ?>
                <span class="user-welcome">
                    <i class="fas fa-user-circle"></i> 
                    Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?>
                </span>
            <?php else: ?>
                <span class="top-link">
                    <i class="fas fa-clock"></i> <?= date('d/m/Y') ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modern Header -->
<header class="main-header">
    <div class="header-container">
        <!-- Logo Section -->
        <a href="?page=home" class="header-logo">
            <img src="images/EILCO-LOGO-2022.png" alt="EILCO" class="logo-img">
        </a>
        
        <!-- Title Section -->
        <div class="header-title">
            <h1>Vie Étudiante EILCO</h1>
            <p>Clubs & Événements</p>
        </div>
        
        <!-- Actions Section -->
        <div class="header-actions">
            <?php if(isset($_SESSION["nom"])): ?>
                <!-- User Menu -->
                <div class="user-menu-dropdown">
                    <button class="user-menu-btn" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)) ?>
                        </div>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['prenom']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown-menu" id="userDropdown">
                        <a href="?page=profile" class="dropdown-item">
                            <i class="fas fa-user"></i> Mon Profil
                        </a>
                        <?php if($is_membre_club == 1): ?>
                            <a href="?page=my-events" class="dropdown-item">
                                <i class="fas fa-calendar-alt"></i> Mes Événements
                            </a>
                        <?php endif; ?>
                        <a href="?page=my-subscriptions" class="dropdown-item">
                            <i class="fas fa-bookmark"></i> Mes Inscriptions
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="post" class="dropdown-form">
                            <?= Security::csrfField() ?>
                            <button type="submit" name="logout" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="/?page=login" class="header-btn header-btn-outline">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
                <a href="/?page=register" class="header-btn header-btn-primary">
                    <i class="fas fa-user-plus"></i> Inscription
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button class="header-mobile-toggle" onclick="toggleMobileHeader()">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<!-- Quick Actions Bar (for logged-in users) -->
<?php if(isset($_SESSION["nom"])): ?>
<div class="quick-actions-bar">
    <div class="quick-actions-container">
        <div class="quick-actions-scroll">
            <?php 
            $stmt = $db->query("SELECT creation_club_active FROM config LIMIT 1");
            $club_creation_active = $stmt->fetchColumn();
            
            if ($club_creation_active): ?>
                <a href="?page=club-create" class="quick-action-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Créer un club</span>
                </a>
            <?php endif; ?>
            
            <?php if($is_membre_club == 1): ?>
                <a href="?page=event-create" class="quick-action-item">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Créer un événement</span>
                </a>
                <a href="?page=event-report" class="quick-action-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Déposer un rapport</span>
                </a>
            <?php endif; ?>
            
            <a href="?page=event-list" class="quick-action-item">
                <i class="fas fa-calendar"></i>
                <span>Événements</span>
            </a>
            
            <a href="?page=club-list" class="quick-action-item">
                <i class="fas fa-users"></i>
                <span>Clubs</span>
            </a>
            
            <?php if ($current_user && $current_user['permission'] >= 3): ?>
                <a href="?page=admin" class="quick-action-item highlight">
                    <i class="fas fa-cog"></i>
                    <span>Gestion</span>
                    <?php if ($nb_badge_admin > 0): ?>
                        <span class="action-badge"><?= $nb_badge_admin ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            
            <?php if(!empty($req_clubs) || $_SESSION['permission'] == 5): ?>
                <a href="?page=tutoring" class="quick-action-item highlight">
                    <i class="fas fa-user-graduate"></i>
                    <span>Tutorat</span>
                    <?php if ($nb_badge_tuteur > 0): ?>
                        <span class="action-badge"><?= $nb_badge_tuteur ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleMobileHeader() {
    const header = document.querySelector('.main-header');
    header.classList.toggle('mobile-open');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const userMenu = document.querySelector('.user-menu-dropdown');
    const dropdown = document.getElementById('userDropdown');
    if (userMenu && dropdown && !userMenu.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
