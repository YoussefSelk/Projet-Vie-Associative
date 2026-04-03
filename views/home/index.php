<?php
/**
 * Page d'accueil principale
 * 
 * Vitrine de l'application avec plusieurs sections :
 * - Hero banner avec accroche principale
 * - Presentation des clubs par campus
 * - Evenements a venir
 * - Calendrier general
 * - Appel a l'action (inscription/connexion)
 * 
 * Animations CSS pour une experience moderne :
 * - Fade in up/down au scroll
 * - Hover effects sur les cartes
 * - Transitions fluides
 * 
 * Variables attendues :
 * - $clubs : Liste des clubs par campus
 * - $events : Evenements recents
 * 
 * @package Views/Home
 */
$pageTitle = 'Accueil - Vie Étudiante EILCO';
$pageCss = ['shared', 'buttons', 'calendar', 'home'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
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
                        <a href="?page=clubs-browse" class="hero-btn hero-btn-secondary">
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
                    <div class="stat-item">
                        <span class="stat-number"><?= $clubs_count ?? 0 ?></span>
                        <span class="stat-label">Clubs actifs</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $events_count ?? 0 ?></span>
                        <span class="stat-label">Événements</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $users_count ?? 0 ?></span>
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

        <!-- Quick Access Section -->
        <section class="quick-access-section">
            <div class="quick-access-container">
                <div class="quick-access-header">
                    <h2><i class="fas fa-bolt"></i> Accès rapide</h2>
                    <p>Retrouvez rapidement les fonctionnalités principales de la plateforme</p>
                </div>
                <div class="quick-access-grid">
                    <a href="#calendar-section" class="quick-access-card">
                        <div class="quick-access-icon" style="background: linear-gradient(135deg, #0066cc 0%, #4cc9f0 100%);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3>Calendrier</h3>
                        <p>Consultez tous les événements à venir dans un calendrier interactif</p>
                    </a>
                    <a href="?page=clubs-browse" class="quick-access-card">
                        <div class="quick-access-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <h3>Tous les clubs</h3>
                        <p>Découvrez l'ensemble des clubs actifs sur les différents campus</p>
                    </a>
                    <a href="?page=event-list" class="quick-access-card">
                        <div class="quick-access-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h3>Événements</h3>
                        <p>Inscrivez-vous aux prochains événements organisés par les clubs</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- Guides Section -->
        <section class="guides-section">
            <div class="guides-container">
                <div class="guides-header">
                    <span class="section-tag">Guide</span>
                    <h2>Comment ça marche ?</h2>
                    <p>Découvrez comment profiter pleinement de la plateforme en quelques étapes simples</p>
                </div>
                <div class="guides-grid">
                    <div class="guide-card">
                        <div class="guide-number">1</div>
                        <h3>Créez votre compte</h3>
                        <p>Inscrivez-vous avec votre email universitaire pour accéder à toutes les fonctionnalités de la plateforme.</p>
                    </div>
                    <div class="guide-card">
                        <div class="guide-number">2</div>
                        <h3>Explorez les clubs</h3>
                        <p>Parcourez la liste des clubs actifs sur votre campus et découvrez leurs activités et événements.</p>
                    </div>
                    <div class="guide-card">
                        <div class="guide-number">3</div>
                        <h3>Rejoignez un club</h3>
                        <p>Demandez à rejoindre les clubs qui vous intéressent et participez à leurs activités.</p>
                    </div>
                    <div class="guide-card">
                        <div class="guide-number">4</div>
                        <h3>Inscrivez-vous aux événements</h3>
                        <p>Consultez le calendrier et inscrivez-vous aux événements pour ne rien manquer.</p>
                    </div>
                    <div class="guide-card">
                        <div class="guide-number">5</div>
                        <h3>Créez votre propre club</h3>
                        <p>Vous avez une passion ? Créez votre club et rassemblez une communauté autour de vous.</p>
                    </div>
                    <div class="guide-card">
                        <div class="guide-number">6</div>
                        <h3>Organisez des événements</h3>
                        <p>En tant que membre d'un club, proposez et organisez des événements pour la communauté.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Calendar Section -->
        <section class="calendar-section-home" id="calendar-section">
            <div class="section-header-home">
                <span class="section-tag">Agenda</span>
                <h2>Calendrier des événements</h2>
                <p>Consultez les événements à venir sur tous les campus</p>
            </div>
            <div class="calendar-wrapper">
                <?php include VIEWS_PATH . '/includes/calendrier-general.php'; ?>
            </div>
        </section>

        <!-- CTA Section -->
        <?php if (!isset($_SESSION['id'])): ?>
        <section class="cta-section">
            <div class="cta-container">
                <h2>Prêt à rejoindre la communauté ?</h2>
                <p>Créez votre compte gratuitement et commencez à participer à la vie étudiante de l'EILCO</p>
                <div class="cta-buttons">
                    <a href="?page=register" class="cta-btn cta-btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Créer mon compte
                    </a>
                    <a href="?page=login" class="cta-btn cta-btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        J'ai déjà un compte
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
