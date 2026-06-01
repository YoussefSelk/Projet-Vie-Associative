<?php
/**
 * Page d'accueil principale
 * 
 * Vitrine de l'application avec plusieurs sections :
 * - Hero banner avec accroche principale
 * - Présentation des clubs par campus
 * - Événements à venir
 * - Calendrier général
 * - Appel à l'action (inscription/connexion)
 *
 * Animations CSS pour une expérience moderne :
 * - Fade in up/down au scroll
 * - Hover effects sur les cartes
 * - Transitions fluides
 *
 * Variables attendues :
 * - $clubs : Liste des clubs par campus
 * - $events : Événements récents
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
