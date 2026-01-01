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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <link rel="stylesheet" href="/style.css">
    <style>
    /* ================================================
       Home Page Animations
       ================================================ */
    
    /* Fade in up animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    /* Apply animations */
    .hero-badge {
        animation: fadeInDown 0.8s ease-out;
    }
    
    .hero-title {
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }
    
    .hero-subtitle {
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }
    
    .hero-actions {
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }
    
    .hero-stats {
        animation: scaleIn 0.8s ease-out 0.8s both;
    }
    
    .feature-card {
        animation: fadeInUp 0.6s ease-out both;
    }
    
    .feature-card:nth-child(1) { animation-delay: 0.1s; }
    .feature-card:nth-child(2) { animation-delay: 0.2s; }
    .feature-card:nth-child(3) { animation-delay: 0.3s; }
    .feature-card:nth-child(4) { animation-delay: 0.4s; }
    
    .feature-icon {
        transition: transform 0.3s ease;
    }
    
    .feature-card:hover .feature-icon {
        animation: pulse 0.6s ease-in-out;
    }
    
    .stat-number {
        transition: transform 0.3s ease;
    }
    
    .stat-item:hover .stat-number {
        transform: scale(1.1);
    }
    
    /* Quick Access Section */
    .quick-access-section {
        padding: 80px 20px;
        background: linear-gradient(135deg, #0a1628 0%, #1a365d 100%);
        position: relative;
        overflow: hidden;
    }
    
    .quick-access-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 80%, rgba(76, 201, 240, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(0, 102, 204, 0.15) 0%, transparent 50%);
        pointer-events: none;
    }
    
    .quick-access-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .quick-access-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .quick-access-header h2 {
        font-size: 2.2rem;
        font-weight: 700;
        color: white;
        margin-bottom: 15px;
    }
    
    .quick-access-header p {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }
    
    .quick-access-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 35px 30px;
        text-align: center;
        text-decoration: none;
        transition: all 0.4s ease;
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }
    
    .quick-access-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(76, 201, 240, 0.1) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .quick-access-card:hover {
        transform: translateY(-10px);
        border-color: rgba(76, 201, 240, 0.5);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .quick-access-card:hover::before {
        opacity: 1;
    }
    
    .quick-access-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: white;
        position: relative;
        z-index: 1;
        transition: transform 0.4s ease;
    }
    
    .quick-access-card:hover .quick-access-icon {
        transform: scale(1.1);
    }
    
    .quick-access-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: white;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    
    .quick-access-card p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    /* Guides Section */
    .guides-section {
        padding: 80px 20px;
        background: white;
    }
    
    .guides-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .guides-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .guides-header .section-tag {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .guides-header h2 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .guides-header p {
        font-size: 1.1rem;
        color: #64748b;
    }
    
    .guides-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    
    .guide-card {
        background: #f8fafc;
        border-radius: 20px;
        padding: 30px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .guide-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #0066cc 0%, #4cc9f0 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .guide-card:hover {
        background: white;
        border-color: #e2e8f0;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }
    
    .guide-card:hover::before {
        opacity: 1;
    }
    
    .guide-number {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #0066cc 0%, #4cc9f0 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 20px;
    }
    
    .guide-card h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
    }
    
    .guide-card p {
        font-size: 0.95rem;
        color: #64748b;
        line-height: 1.7;
        margin: 0;
    }
    
    /* CTA Section */
    .cta-section {
        padding: 80px 20px;
        background: linear-gradient(135deg, #0066cc 0%, #004080 100%);
        text-align: center;
    }
    
    .cta-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .cta-section h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
    }
    
    .cta-section p {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 35px;
    }
    
    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 35px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .cta-btn-primary {
        background: white;
        color: #0066cc;
    }
    
    .cta-btn-primary:hover {
        background: #f0f7ff;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .cta-btn-secondary {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }
    
    .cta-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        transform: translateY(-3px);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .quick-access-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .guides-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .quick-access-section {
            padding: 60px 20px;
        }
        
        .quick-access-header h2,
        .guides-header h2 {
            font-size: 1.8rem;
        }
        
        .quick-access-grid,
        .guides-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .quick-access-card {
            padding: 30px 25px;
        }
        
        .guides-section {
            padding: 60px 20px;
        }
        
        .cta-section {
            padding: 60px 20px;
        }
        
        .cta-section h2 {
            font-size: 1.8rem;
        }
        
        .cta-buttons {
            flex-direction: column;
        }
        
        .cta-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
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
                    <a href="?page=club-list" class="quick-access-card">
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
