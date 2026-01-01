<?php
/**
 * En-tete du site avec barre superieure
 * 
 * Affiche :
 * - Logo et lien vers l'accueil
 * - Menu utilisateur avec liens profil, dashboard, administration
 * - Badges de notification pour validation (admin/tuteur)
 * - Bouton connexion/deconnexion
 * 
 * Variables calculees :
 * - $is_membre_club : Indicateur si l'utilisateur est membre d'un club valide
 * - $nb_badge_admin : Nombre d'elements en attente de validation admin
 * - $nb_badge_tuteur : Nombre d'elements en attente de validation tuteur
 * - $current_user : Informations de l'utilisateur connecte
 * 
 * @package Views/Includes
 */

// Inclusion des dependances
require_once("include.php");
global $db;

if(isset($_SESSION['id'])){
    // Recuperer les clubs dont l'utilisateur est membre valide
    // Un club est considéré validé si validation_finale = 1
    $req_membre_club = $db->prepare("SELECT mc.* FROM membres_club mc LEFT JOIN fiche_club fc ON fc.club_id = mc.club_id WHERE mc.membre_id = ? AND mc.valide = 1 AND fc.validation_finale = 1");
    $req_membre_club->execute([$_SESSION['id']]);
    $infos_membre_club = $req_membre_club->fetchAll();    

    // Indicateur d'appartenance a un club
    if(empty($infos_membre_club)){
        $is_membre_club = 0;
    } else {
        $is_membre_club = 1;
    }

    // Recuperer les clubs dont l'utilisateur est tuteur
    $req_0 = $db->prepare("SELECT club_id, nom_club FROM fiche_club
    WHERE validation_finale = 1 AND tuteur = ?");
    $req_0->execute([$_SESSION['id']]);
    $req_clubs=$req_0->fetchAll();

    // Compteur d'evenements en attente de validation finale
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_event WHERE validation_finale IS NULL AND validation_bde = 1 AND validation_tuteur = 1");
    $req->execute();
    $row = $req->fetchAll();
    $nb_events_admin = $row[0]['total'];

    // Compteur de clubs en attente de validation finale
    $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
    $req->execute();
    $row = $req->fetchAll();
    $nb_clubs_admin = $row[0]['total'];

    // Total des elements en attente pour le badge admin
    $nb_badge_admin = $nb_events_admin + $nb_clubs_admin;

    // Compteur de clubs en attente de validation tuteur
    // Les admins voient tout, les tuteurs voient seulement leurs clubs
    if (($_SESSION['permission'] ?? 0) == 5) {
        $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_club WHERE validation_tuteur IS NULL");
        $req->execute();
    } else {
        $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_club WHERE (validation_tuteur IS NULL) AND tuteur = ?");
        $req->execute([$_SESSION['id']]);
    }
    $row = $req->fetchAll();
    $nb_clubs_tuteur = $row[0]['total'];

    // Compteur d'evenements en attente de validation tuteur
    if (($_SESSION['permission'] ?? 0) == 5) {
        $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_event f WHERE f.validation_tuteur IS NULL");
        $req->execute();
    } else {
        $req = $db->prepare("SELECT COUNT(*) AS total FROM fiche_event f LEFT JOIN fiche_club fc ON fc.club_id = f.club_orga WHERE (f.validation_tuteur IS NULL) AND fc.tuteur = ?");
        $req->execute([$_SESSION['id']]);
    }
    $row = $req->fetchAll();
    $nb_events_tuteur = $row[0]['total'];

    // Total des elements en attente pour le badge tuteur
    $nb_badge_tuteur = $nb_clubs_tuteur + $nb_events_tuteur;

    // Recuperer les informations de l'utilisateur connecte
    $q = $db->prepare("SELECT * FROM users WHERE id = ?");
    $q->execute([$_SESSION['id']]);
    $current_user = $q->fetch();
}
else {
    // Valeurs par defaut pour utilisateur non connecte
    $is_membre_club = 0;
    $nb_badge_admin = 0;
    $nb_badge_tuteur = 0;
    $current_user = null;
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
                        <a href="?page=dashboard" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i> Mon Tableau de bord
                        </a>
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
                        <a href="?page=logout" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
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
            $user_permission = $_SESSION['permission'] ?? 0;
            
            if ($club_creation_active): ?>
                <a href="?page=club-create" class="quick-action-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Créer un club</span>
                </a>
            <?php endif; ?>
            
            <?php if($is_membre_club == 1 && $user_permission >= 2): ?>
                <a href="?page=event-create" class="quick-action-item">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Créer un événement</span>
                </a>
            <?php endif; ?>
            
            <?php if($is_membre_club == 1): ?>
                <a href="?page=event-report" class="quick-action-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Déposer un rapport</span>
                </a>
            <?php endif; ?>
            
            <a href="?page=event-list" class="quick-action-item">
                <i class="fas fa-calendar"></i>
                <span>Événements</span>
            </a>
            
            <?php if ($user_permission >= 3): ?>
                <?php // Club management requires permission 3+ ?>
                <a href="?page=club-list" class="quick-action-item">
                    <i class="fas fa-th-large"></i>
                    <span>Gérer les clubs</span>
                </a>
            <?php endif; ?>
            
            <?php if ($user_permission >= 3): ?>
                <!-- Administration Dropdown -->
                <div class="quick-action-dropdown">
                    <button class="quick-action-item highlight" onclick="toggleQuickDropdown(this)">
                        <i class="fas fa-shield-alt"></i>
                        <span>Administration</span>
                        <?php if ($nb_badge_admin > 0): ?>
                            <span class="action-badge"><?= $nb_badge_admin ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="quick-dropdown-menu">
                        <a href="?page=admin" class="quick-dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="?page=pending-clubs" class="quick-dropdown-item">
                            <i class="fas fa-building"></i>
                            Clubs en attente
                            <?php if ($nb_clubs_admin > 0): ?>
                                <span class="dropdown-badge"><?= $nb_clubs_admin ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="?page=pending-events" class="quick-dropdown-item">
                            <i class="fas fa-calendar-check"></i>
                            Événements en attente
                            <?php if ($nb_events_admin > 0): ?>
                                <span class="dropdown-badge"><?= $nb_events_admin ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="?page=event-analytics" class="quick-dropdown-item">
                            <i class="fas fa-chart-bar"></i>
                            Analytics
                        </a>
                        <a href="?page=admin-reports" class="quick-dropdown-item">
                            <i class="fas fa-chart-line"></i>
                            Rapports
                        </a>
                        <a href="?page=club-list" class="quick-dropdown-item">
                            <i class="fas fa-users"></i>
                            Gérer les clubs
                        </a>
                        <?php if ($user_permission >= 5): ?>
                            <div class="dropdown-divider"></div>
                            <a href="?page=admin-users" class="quick-dropdown-item">
                                <i class="fas fa-users-cog"></i>
                                Utilisateurs
                            </a>
                            <a href="?page=admin-audit" class="quick-dropdown-item">
                                <i class="fas fa-history"></i>
                                Audit & Sécurité
                            </a>
                            <a href="?page=admin-database" class="quick-dropdown-item">
                                <i class="fas fa-database"></i>
                                Base de données
                            </a>
                            <a href="?page=admin-settings" class="quick-dropdown-item">
                                <i class="fas fa-cog"></i>
                                Paramètres
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($req_clubs) || $user_permission >= 2): ?>
                <!-- Tutorat Dropdown -->
                <div class="quick-action-dropdown">
                    <button class="quick-action-item" onclick="toggleQuickDropdown(this)">
                        <i class="fas fa-user-graduate"></i>
                        <span>Tutorat</span>
                        <?php if ($nb_badge_tuteur > 0): ?>
                            <span class="action-badge"><?= $nb_badge_tuteur ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="quick-dropdown-menu">
                        <a href="?page=tutoring" class="quick-dropdown-item">
                            <i class="fas fa-tasks"></i>
                            Validations
                            <?php if ($nb_badge_tuteur > 0): ?>
                                <span class="dropdown-badge"><?= $nb_badge_tuteur ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if(!empty($req_clubs)): ?>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">Mes clubs tutorés</span>
                            <?php foreach($req_clubs as $c): ?>
                                <a href="?page=club-view&id=<?= $c['club_id'] ?>" class="quick-dropdown-item">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($c['nom_club']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
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

function toggleQuickDropdown(button) {
    const dropdown = button.closest('.quick-action-dropdown');
    const isOpen = dropdown.classList.contains('open');
    
    // Close all other dropdowns
    document.querySelectorAll('.quick-action-dropdown.open').forEach(d => {
        if (d !== dropdown) d.classList.remove('open');
    });
    
    dropdown.classList.toggle('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const userMenu = document.querySelector('.user-menu-dropdown');
    const dropdown = document.getElementById('userDropdown');
    if (userMenu && dropdown && !userMenu.contains(e.target)) {
        dropdown.classList.remove('show');
    }
    
    // Close quick action dropdowns
    if (!e.target.closest('.quick-action-dropdown')) {
        document.querySelectorAll('.quick-action-dropdown.open').forEach(d => {
            d.classList.remove('open');
        });
    }
});
</script>
