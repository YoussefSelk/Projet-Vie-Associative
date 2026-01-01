<?php
/**
 * Barre de navigation principale
 * 
 * Affiche la navigation horizontale avec :
 * - Lien vers l'accueil
 * - Menus deroulants par campus (Calais, Longuenesse, Dunkerque, Boulogne)
 * - Liens evenements et clubs (authentifies uniquement)
 * 
 * Variables globales utilisees :
 * - $db : Connexion PDO a la base de donnees
 * 
 * @package Views/Includes
 */

// Verification de l'authentification et recuperation du niveau de permission
$isAuth_NAVBAR = AuthController::isAuthenticated();
$auth_permission_NAVBAR = AuthController::getPermission();

// Recuperation des clubs valides par campus
$req0 = $db->prepare("SELECT club_id, nom_club FROM fiche_club WHERE campus = 'Calais' AND validation_finale = 1");
$req0->execute();
$req_clubs_calais = $req0->fetchAll();

$req1 = $db->prepare("SELECT club_id, nom_club FROM fiche_club WHERE campus = 'Longuenesse' AND validation_finale = 1");
$req1->execute();
$req_clubs_sto = $req1->fetchAll();

$req2 = $db->prepare("SELECT club_id, nom_club FROM fiche_club WHERE campus = 'Dunkerque' AND validation_finale = 1");
$req2->execute();
$req_clubs_dk = $req2->fetchAll();

$req3 = $db->prepare("SELECT club_id, nom_club FROM fiche_club WHERE campus = 'Boulogne'AND validation_finale = 1");
$req3->execute();
$req_clubs_boulogne = $req3->fetchAll();

// Configuration des campus avec icones et couleurs associees
$campuses = [
    'Calais' => ['clubs' => $req_clubs_calais, 'icon' => 'fa-anchor', 'color' => '#dc3545'],
    'Longuenesse' => ['clubs' => $req_clubs_sto, 'icon' => 'fa-university', 'color' => '#28a745'],
    'Dunkerque' => ['clubs' => $req_clubs_dk, 'icon' => 'fa-ship', 'color' => '#ffc107'],
    'Boulogne' => ['clubs' => $req_clubs_boulogne, 'icon' => 'fa-water', 'color' => '#17a2b8']
];
?>

<nav class="main-nav">
    <div class="nav-container">
        <!-- Mobile Menu Toggle -->
        <button class="nav-mobile-toggle" onclick="toggleMobileNav()" aria-label="Menu">
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
        </button>
        
        <ul class="nav-menu" id="mainNavMenu">
            <!-- Home -->
            <li class="nav-item">
                <a href="?page=home" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                </a>
            </li>
            
            <!-- Campus Dropdowns -->
            <?php foreach ($campuses as $campus_name => $campus_data): ?>
            <li class="nav-item nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle" onclick="toggleNavDropdown(event, this)">
                    <span class="campus-indicator" style="background: <?= $campus_data['color'] ?>"></span>
                    <i class="fas <?= $campus_data['icon'] ?>"></i>
                    <span><?= $campus_name ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </a>
                <?php if (!empty($campus_data['clubs'])): ?>
                <ul class="dropdown-menu">
                    <li class="dropdown-header">
                        <i class="fas fa-building"></i> Clubs à <?= $campus_name ?>
                    </li>
                    <?php foreach ($campus_data['clubs'] as $club_loop): ?>
                    <li>
                        <a href="?page=club-view&id=<?= $club_loop['club_id'] ?>" class="dropdown-link">
                            <i class="fas fa-circle-dot"></i>
                            <?= htmlspecialchars($club_loop['nom_club']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <ul class="dropdown-menu">
                    <li class="dropdown-empty">
                        <i class="fas fa-info-circle"></i> Aucun club
                    </li>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            <?php if ($isAuth_NAVBAR): ?>
                <!-- Events Link -->
                <li class="nav-item">
                    <a href="?page=event-list" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Événements</span>
                    </a>
                </li>
                
                <!-- All Clubs Link -->
                <li class="nav-item">
                    <a href="?page=club-list" class="nav-link">
                        <i class="fas fa-th-large"></i>
                        <span>Tous les clubs</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
function toggleMobileNav() {
    const nav = document.querySelector('.main-nav');
    const menu = document.getElementById('mainNavMenu');
    const toggle = document.querySelector('.nav-mobile-toggle');
    
    nav.classList.toggle('mobile-open');
    menu.classList.toggle('active');
    toggle.classList.toggle('active');
}

function toggleNavDropdown(event, element) {
    event.preventDefault();
    const parent = element.parentElement;
    const dropdown = parent.querySelector('.dropdown-menu');
    
    // Close other dropdowns on mobile
    if (window.innerWidth <= 992) {
        document.querySelectorAll('.nav-dropdown').forEach(item => {
            if (item !== parent) {
                item.classList.remove('open');
            }
        });
    }
    
    parent.classList.toggle('open');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const nav = document.querySelector('.main-nav');
    if (!nav.contains(event.target)) {
        nav.classList.remove('mobile-open');
        document.getElementById('mainNavMenu')?.classList.remove('active');
        document.querySelector('.nav-mobile-toggle')?.classList.remove('active');
        document.querySelectorAll('.nav-dropdown').forEach(item => {
            item.classList.remove('open');
        });
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        const nav = document.querySelector('.main-nav');
        const menu = document.getElementById('mainNavMenu');
        const toggle = document.querySelector('.nav-mobile-toggle');
        
        nav?.classList.remove('mobile-open');
        menu?.classList.remove('active');
        toggle?.classList.remove('active');
        document.querySelectorAll('.nav-dropdown').forEach(item => {
            item.classList.remove('open');
        });
    }
});
</script>
