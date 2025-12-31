<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php include VIEWS_PATH . "/includes/header.php"; ?>
    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main class="home-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-background">
                <div class="hero-gradient"></div>
                <div class="hero-pattern"></div>
            </div>
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-star"></i> Vie Étudiante EILCO
                </div>
                <h1 class="hero-title">
                    Découvrez la vie<br>
                    <span class="gradient-text">associative</span>
                </h1>
                <p class="hero-subtitle">
                    Rejoignez les clubs, participez aux événements et vivez pleinement votre expérience étudiante à l'École d'Ingénieurs du Littoral Côte d'Opale
                </p>
                
                <div class="hero-actions">
                    <?php if (isset($_SESSION['id'])): ?>
                        <a href="?page=event-list" class="hero-btn hero-btn-primary">
                            <i class="fas fa-calendar-alt"></i>
                            Voir les événements
                        </a>
                        <a href="?page=club-list" class="hero-btn hero-btn-secondary">
                            <i class="fas fa-users"></i>
                            Explorer les clubs
                        </a>
                    <?php else: ?>
                        <a href="?page=register" class="hero-btn hero-btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Rejoindre la communauté
                        </a>
                        <a href="?page=login" class="hero-btn hero-btn-secondary">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Stats -->
                <div class="hero-stats">
                    <?php
                    // Get stats
                    global $db;
                    $clubs_count = $db->query("SELECT COUNT(*) FROM fiche_club WHERE validation_finale = 1")->fetchColumn();
                    $events_count = $db->query("SELECT COUNT(*) FROM fiche_event WHERE validation_finale = 1")->fetchColumn();
                    $users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    ?>
                    <div class="stat-item">
                        <span class="stat-number"><?= $clubs_count ?></span>
                        <span class="stat-label">Clubs actifs</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $events_count ?></span>
                        <span class="stat-label">Événements</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $users_count ?></span>
                        <span class="stat-label">Membres</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="features-container">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>4 Campus</h3>
                    <p>Calais, Longuenesse, Dunkerque et Boulogne réunis sur une seule plateforme</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Événements</h3>
                    <p>Inscrivez-vous aux événements et recevez des rappels automatiques</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Clubs</h3>
                    <p>Créez ou rejoignez un club et développez vos passions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p>Restez informé des dernières actualités et événements</p>
                </div>
            </div>
        </section>

        <!-- Calendar Section -->
        <section class="calendar-section-home">
            <div class="section-header-home">
                <span class="section-tag">Agenda</span>
                <h2>Calendrier des événements</h2>
                <p>Consultez les événements à venir sur tous les campus</p>
            </div>
            <div class="calendar-wrapper">
                <?php include VIEWS_PATH . '/includes/calendrier-general.php'; ?>
            </div>
        </section>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
