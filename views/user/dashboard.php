<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        :root {
            --primary: #0066cc;
            --primary-dark: #004999;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --purple: #6f42c1;
            --dark: #1a1a2e;
            --gray: #6c757d;
            --light-bg: #f4f6f9;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .dashboard-wrapper {
            background: var(--light-bg);
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        
        /* Hero Welcome Section */
        .dashboard-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            color: white;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .dashboard-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 20%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border: 3px solid rgba(255,255,255,0.3);
        }
        
        .hero-content h1 {
            font-size: 2rem;
            margin: 0 0 0.5rem;
        }
        
        .hero-content p {
            opacity: 0.9;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .hero-actions {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .hero-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(5px);
        }
        
        .hero-btn i {
            width: 24px;
            text-align: center;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            border-color: var(--primary);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }
        
        .stat-icon.clubs { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .stat-icon.events { background: linear-gradient(135deg, #0066cc 0%, #17a2b8 100%); }
        .stat-icon.upcoming { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
        .stat-icon.participated { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); }
        
        .stat-content h3 {
            font-size: 2rem;
            color: var(--dark);
            margin: 0;
            line-height: 1;
        }
        
        .stat-content p {
            color: var(--gray);
            margin: 0.25rem 0 0;
            font-size: 0.9rem;
        }
        
        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
        }
        
        @media (max-width: 1100px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .main-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .side-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        /* Cards */
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }
        
        .card-header.teal { background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); }
        .card-header.green { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .card-header.purple { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); }
        .card-header.orange { background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%); }
        
        .card-header h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .card-badge {
            background: rgba(255,255,255,0.25);
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .view-all-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .view-all-link:hover {
            background: rgba(255,255,255,0.35);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Event Items */
        .event-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .event-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .event-item:hover {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,102,204,0.15);
        }
        
        .event-date {
            min-width: 55px;
            text-align: center;
            background: white;
            padding: 0.6rem 0.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .event-date .day {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        
        .event-date .month {
            font-size: 0.7rem;
            color: var(--gray);
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }
        
        .event-details {
            flex: 1;
            min-width: 0;
        }
        
        .event-details h4 {
            margin: 0;
            font-size: 0.95rem;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .event-details p {
            margin: 0.25rem 0 0;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .event-details p i {
            margin-right: 0.25rem;
        }
        
        .event-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .event-badge.soon {
            background: #fff3cd;
            color: #856404;
        }
        
        .event-badge.upcoming {
            background: #d4edda;
            color: #155724;
        }
        
        .event-badge.recommended {
            background: #fce4ec;
            color: #c2185b;
        }
        
        /* Club Items */
        .club-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .club-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
        }
        
        .club-item:hover {
            background: #e8f4fd;
            transform: translateX(5px);
        }
        
        .club-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .club-details h4 {
            margin: 0;
            font-size: 0.95rem;
            color: var(--dark);
        }
        
        .club-details p {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        /* Profile Card */
        .profile-summary {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        
        .profile-completion {
            margin-bottom: 0.5rem;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .progress-header span:first-child {
            color: var(--gray);
        }
        
        .progress-header span:last-child {
            color: var(--success);
            font-weight: 600;
        }
        
        .progress-track {
            background: #e9ecef;
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .profile-info-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .profile-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--light-bg);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .profile-info-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        .profile-edit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .profile-edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,102,204,0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1.5rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            display: block;
        }
        
        .empty-state p {
            margin: 0 0 1rem;
        }
        
        .empty-state-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .empty-state-btn:hover {
            background: var(--primary-dark);
        }
        
        @media (max-width: 768px) {
            .dashboard-hero {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-avatar {
                margin: 0 auto 1rem;
            }
            
            .hero-actions {
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
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
                        <div class="card-header teal">
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
                        <div class="card-header orange">
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
                        <div class="card-header green">
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
                        <div class="card-header purple">
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
