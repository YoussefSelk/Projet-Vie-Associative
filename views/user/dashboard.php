<?php
/**
 * Tableau de bord utilisateur
 * 
 * Page d'accueil personnalisee pour l'utilisateur connecte :
 * - Resume des clubs dont il est membre
 * - Prochains evenements
 * - Actions rapides (creer club, creer evenement)
 * - Statistiques personnelles
 * 
 * Le contenu s'adapte au niveau de permission :
 * - Utilisateur : Vue basique
 * - Membre club : Actions club visibles
 * - BDE : Validations en attente
 * - Tuteur : Clubs a superviser
 * - Admin : Acces complet
 * 
 * Variables attendues :
 * - $user : Donnees de l'utilisateur courant
 * - $clubs : Clubs de l'utilisateur
 * - $events : Evenements a venir
 * - $stats : Statistiques personnelles
 * 
 * @package Views/User
 */
$pageCss = ['shared', 'buttons', 'dashboard', 'profiles'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main class="dashboard-wrapper">
        <div class="dashboard-container">
            <!-- Hero Welcome Section -->
            <div class="dashboard-hero">
                <div class="hero-content">
                    <div class="hero-avatar">
                        <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? 'U', 0, 1)) ?>
                    </div>
                    <h1>Bonjour, <?= htmlspecialchars($user['prenom'] ?? 'Utilisateur') ?> !</h1>
                    <p>Bienvenue sur votre tableau de bord personnel</p>
                </div>
                <div class="hero-actions">
                    <a href="?page=event-list" class="hero-btn">
                        <i class="fas fa-calendar-alt"></i>
                        Voir les événements
                    </a>
                    <a href="?page=club-list" class="hero-btn">
                        <i class="fas fa-users"></i>
                        Découvrir les clubs
                    </a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon clubs">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['clubs_count'] ?? 0 ?></h3>
                        <p>Clubs rejoints</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon events">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['subscriptions_count'] ?? 0 ?></h3>
                        <p>Inscriptions</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon upcoming">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['upcoming_count'] ?? 0 ?></h3>
                        <p>Événements à venir</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon participated">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($past_events ?? []) ?></h3>
                        <p>Événements participés</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="content-grid">
                <div class="main-column">
                    <!-- Upcoming Events -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-alt"></i> Mes prochains événements</h3>
                            <a href="?page=my-events" class="view-all-link">
                                Voir tout <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($upcoming_events)): ?>
                                <div class="event-list">
                                    <?php 
                                    $months_fr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                                    foreach (array_slice($upcoming_events, 0, 4) as $event): 
                                        $date = strtotime($event['date_ev']);
                                    ?>
                                        <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="event-item">
                                            <div class="event-date">
                                                <div class="day"><?= date('d', $date) ?></div>
                                                <div class="month"><?= $months_fr[date('n', $date) - 1] ?></div>
                                            </div>
                                            <div class="event-details">
                                                <h4><?= htmlspecialchars(html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                                <p>
                                                    <i class="fas fa-building"></i> <?= htmlspecialchars(html_entity_decode($event['nom_club'] ?? 'Club', ENT_QUOTES, 'UTF-8')) ?>
                                                    &bull;
                                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus']) ?>
                                                </p>
                                            </div>
                                            <span class="event-badge <?= $event['status'] ?>">
                                                <?= $event['status'] === 'soon' ? 'Bientôt' : 'À venir' ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>Aucun événement à venir</p>
                                    <a href="?page=event-list" class="empty-state-btn">
                                        <i class="fas fa-search"></i> Découvrir les événements
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recommended Events -->
                    <?php if (!empty($recommended_events)): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-lightbulb"></i> Recommandés pour vous</h3>
                            <span class="card-badge">Basé sur vos clubs</span>
                        </div>
                        <div class="card-body">
                            <div class="event-list">
                                <?php foreach (array_slice($recommended_events, 0, 3) as $event): 
                                    $date = strtotime($event['date_ev']);
                                ?>
                                    <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="event-item">
                                        <div class="event-date">
                                            <div class="day"><?= date('d', $date) ?></div>
                                            <div class="month"><?= $months_fr[date('n', $date) - 1] ?></div>
                                        </div>
                                        <div class="event-details">
                                            <h4><?= htmlspecialchars(html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                            <p><i class="fas fa-building"></i> <?= htmlspecialchars(html_entity_decode($event['nom_club'] ?? 'Club', ENT_QUOTES, 'UTF-8')) ?></p>
                                        </div>
                                        <span class="event-badge recommended">
                                            <i class="fas fa-star"></i> Recommandé
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="side-column">
                    <!-- My Clubs -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-users"></i> Mes clubs</h3>
                            <span class="card-badge"><?= count($my_clubs) ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($my_clubs)): ?>
                                <div class="club-list">
                                    <?php foreach ($my_clubs as $club): ?>
                                        <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="club-item">
                                            <div class="club-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="club-details">
                                                <h4><?= htmlspecialchars(html_entity_decode($club['nom_club'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($club['campus']) ?></p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>Vous n'avez pas encore rejoint de club</p>
                                    <a href="?page=club-list" class="empty-state-btn">
                                        <i class="fas fa-search"></i> Découvrir les clubs
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Profile Summary -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-circle"></i> Mon profil</h3>
                        </div>
                        <div class="card-body">
                            <div class="profile-summary">
                                <?php 
                                $profile_fields = ['mail', 'promo', 'nom', 'prenom'];
                                $filled = 0;
                                foreach ($profile_fields as $field) {
                                    if (!empty($user[$field])) $filled++;
                                }
                                $completion = round(($filled / count($profile_fields)) * 100);
                                ?>
                                
                                <div class="profile-completion">
                                    <div class="progress-header">
                                        <span>Complétion du profil</span>
                                        <span><?= $completion ?>%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: <?= $completion ?>%"></div>
                                    </div>
                                </div>
                                
                                <div class="profile-info-list">
                                    <div class="profile-info-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?= htmlspecialchars($user['mail'] ?? 'Non renseigné') ?></span>
                                    </div>
                                    <div class="profile-info-item">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?= htmlspecialchars($user['promo'] ?? 'Non renseigné') ?></span>
                                    </div>
                                </div>
                                
                                <a href="?page=profile-edit" class="profile-edit-btn">
                                    <i class="fas fa-edit"></i>
                                    Modifier mon profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
