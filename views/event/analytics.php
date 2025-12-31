<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0066cc;
            --primary-dark: #004999;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --purple: #6f42c1;
            --pink: #e83e8c;
            --dark: #1a1a2e;
            --gray: #6c757d;
            --light-bg: #f4f6f9;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .analytics-wrapper {
            background: var(--light-bg);
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        
        .analytics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        
        /* Header */
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-title h1 {
            font-size: 1.75rem;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .header-title h1 i {
            color: var(--primary);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--dark);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #2d2d44;
            transform: translateY(-2px);
        }
        
        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1100px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 576px) {
            .stats-row { grid-template-columns: 1fr; }
        }
        
        .stat-box {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .stat-box.blue::before { background: linear-gradient(90deg, var(--primary), var(--info)); }
        .stat-box.green::before { background: linear-gradient(90deg, var(--success), #20c997); }
        .stat-box.orange::before { background: linear-gradient(90deg, #fd7e14, var(--warning)); }
        .stat-box.red::before { background: linear-gradient(90deg, var(--danger), var(--pink)); }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        
        .stat-box .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-box .label {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 992px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .chart-header {
            padding: 1.25rem 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .chart-header.blue { background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%); }
        .chart-header.teal { background: linear-gradient(135deg, var(--info) 0%, #20c997 100%); }
        .chart-header.green { background: linear-gradient(135deg, var(--success) 0%, #20c997 100%); }
        .chart-header.purple { background: linear-gradient(135deg, var(--purple) 0%, var(--pink) 100%); }
        .chart-header.orange { background: linear-gradient(135deg, #fd7e14 0%, var(--warning) 100%); }
        .chart-header.red { background: linear-gradient(135deg, var(--danger) 0%, #c82333 100%); }
        
        .chart-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .chart-body {
            padding: 1.5rem;
        }
        
        .chart-container {
            position: relative;
            height: 280px;
        }
        
        /* Rankings */
        .ranking-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .ranking-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            gap: 1rem;
            transition: all 0.3s;
        }
        
        .ranking-item:last-child {
            margin-bottom: 0;
        }
        
        .ranking-item:hover {
            background: #e8f4fd;
            transform: translateX(5px);
        }
        
        .rank-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .rank-badge.gold { 
            background: linear-gradient(135deg, #ffd700, #ffb347);
            color: #8b6914;
            box-shadow: 0 4px 15px rgba(255,215,0,0.4);
        }
        .rank-badge.silver { 
            background: linear-gradient(135deg, #c0c0c0, #a8a8a8);
            color: #5a5a5a;
            box-shadow: 0 4px 15px rgba(192,192,192,0.4);
        }
        .rank-badge.bronze { 
            background: linear-gradient(135deg, #cd7f32, #b87333);
            color: white;
            box-shadow: 0 4px 15px rgba(205,127,50,0.4);
        }
        .rank-badge.default {
            background: #e9ecef;
            color: var(--gray);
        }
        
        .rank-info {
            flex: 1;
            min-width: 0;
        }
        
        .rank-info h4 {
            margin: 0;
            font-size: 0.95rem;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .rank-info p {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .rank-count {
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }
        
        /* Event Rows */
        .event-list-card {
            grid-column: 1 / -1;
        }
        
        .event-rows {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .event-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .event-row:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .event-date-box {
            min-width: 60px;
            text-align: center;
            background: white;
            padding: 0.75rem 0.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .event-date-box .day {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        
        .event-date-box .month {
            font-size: 0.7rem;
            color: var(--gray);
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }
        
        .event-row-info {
            flex: 1;
            min-width: 0;
        }
        
        .event-row-info h4 {
            margin: 0;
            font-size: 0.95rem;
            color: var(--dark);
        }
        
        .event-row-info p {
            margin: 0.25rem 0 0;
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .event-row-info p i {
            margin-right: 0.25rem;
        }
        
        .subscribers-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #d4edda;
            color: #155724;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .alert-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            display: block;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main class="analytics-wrapper">
        <div class="analytics-container">
            <!-- Header -->
            <div class="analytics-header">
                <div class="header-title">
                    <h1><i class="fas fa-chart-line"></i> Analytiques des Événements</h1>
                </div>
                <a href="?page=admin" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-box blue">
                    <div class="number"><?= $stats['total_events'] ?? 0 ?></div>
                    <div class="label">Événements validés</div>
                </div>
                <div class="stat-box green">
                    <div class="number"><?= $stats['total_subscriptions'] ?? 0 ?></div>
                    <div class="label">Inscriptions totales</div>
                </div>
                <div class="stat-box orange">
                    <div class="number"><?= count($stats['upcoming_events'] ?? []) ?></div>
                    <div class="label">À venir (30 jours)</div>
                </div>
                <div class="stat-box red">
                    <div class="number"><?= count($stats['events_without_reports'] ?? []) ?></div>
                    <div class="label">Sans rapport</div>
                </div>
            </div>
            
            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Events by Month -->
                <div class="chart-card">
                    <div class="chart-header blue">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Événements par mois</h3>
                    </div>
                    <div class="chart-body">
                        <?php if (!empty($stats['by_month'])): ?>
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <p>Aucune donnée disponible</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Events by Campus -->
                <div class="chart-card">
                    <div class="chart-header teal">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Répartition par campus</h3>
                    </div>
                    <div class="chart-body">
                        <?php if (!empty($stats['by_campus'])): ?>
                        <div class="chart-container">
                            <canvas id="campusChart"></canvas>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <p>Aucune donnée disponible</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Popular Events -->
                <div class="chart-card">
                    <div class="chart-header green">
                        <i class="fas fa-fire"></i>
                        <h3>Événements les plus populaires</h3>
                    </div>
                    <div class="chart-body">
                        <?php if (!empty($stats['popular_events'])): ?>
                            <ul class="ranking-list">
                                <?php foreach (array_slice($stats['popular_events'], 0, 5) as $i => $event): 
                                    $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'));
                                ?>
                                    <li class="ranking-item">
                                        <span class="rank-badge <?= $rankClass ?>"><?= $i + 1 ?></span>
                                        <div class="rank-info">
                                            <h4><?= htmlspecialchars(html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                            <p><?= htmlspecialchars(html_entity_decode($event['nom_club'] ?? 'Club', ENT_QUOTES, 'UTF-8')) ?> • <?= htmlspecialchars($event['campus']) ?></p>
                                        </div>
                                        <span class="rank-count"><?= $event['subscription_count'] ?> <i class="fas fa-users"></i></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <p>Aucune donnée disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Most Active Clubs -->
                <div class="chart-card">
                    <div class="chart-header purple">
                        <i class="fas fa-trophy"></i>
                        <h3>Clubs les plus actifs</h3>
                    </div>
                    <div class="chart-body">
                        <?php if (!empty($stats['club_ranking'])): ?>
                            <ul class="ranking-list">
                                <?php foreach (array_slice($stats['club_ranking'], 0, 5) as $i => $club): 
                                    $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'));
                                ?>
                                    <li class="ranking-item">
                                        <span class="rank-badge <?= $rankClass ?>"><?= $i + 1 ?></span>
                                        <div class="rank-info">
                                            <h4><?= htmlspecialchars(html_entity_decode($club['nom_club'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                            <p><?= htmlspecialchars($club['campus']) ?></p>
                                        </div>
                                        <span class="rank-count"><?= $club['event_count'] ?> événements</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <p>Aucun club trouvé</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="chart-card event-list-card">
                    <div class="chart-header orange">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Événements à venir (30 prochains jours)</h3>
                    </div>
                    <div class="chart-body">
                        <?php if (!empty($stats['upcoming_events'])): ?>
                            <div class="event-rows">
                                <?php 
                                $months_fr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                                foreach ($stats['upcoming_events'] as $event): 
                                    $date = strtotime($event['date_ev']);
                                ?>
                                    <div class="event-row">
                                        <div class="event-date-box">
                                            <div class="day"><?= date('d', $date) ?></div>
                                            <div class="month"><?= $months_fr[date('n', $date) - 1] ?></div>
                                        </div>
                                        <div class="event-row-info">
                                            <h4><?= htmlspecialchars(html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                            <p>
                                                <i class="fas fa-building"></i> <?= htmlspecialchars(html_entity_decode($event['nom_club'] ?? 'Club', ENT_QUOTES, 'UTF-8')) ?>
                                                &bull;
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus']) ?>
                                            </p>
                                        </div>
                                        <span class="subscribers-badge">
                                            <i class="fas fa-users"></i> <?= $event['subscription_count'] ?? 0 ?> inscrits
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucun événement à venir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Events Without Reports -->
                <?php if (!empty($stats['events_without_reports'])): ?>
                <div class="chart-card event-list-card">
                    <div class="chart-header red">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Événements passés sans rapport</h3>
                    </div>
                    <div class="chart-body">
                        <div class="event-rows">
                            <?php foreach ($stats['events_without_reports'] as $event): 
                                $date = strtotime($event['date_ev']);
                            ?>
                                <div class="event-row">
                                    <div class="event-date-box">
                                        <div class="day"><?= date('d', $date) ?></div>
                                        <div class="month"><?= $months_fr[date('n', $date) - 1] ?></div>
                                    </div>
                                    <div class="event-row-info">
                                        <h4><?= htmlspecialchars(html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8')) ?></h4>
                                        <p><i class="fas fa-building"></i> <?= htmlspecialchars(html_entity_decode($event['nom_club'] ?? 'Club', ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                    <span class="alert-badge">
                                        <i class="fas fa-file-alt"></i> Rapport manquant
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const colors = {
            primary: '#0066cc',
            info: '#17a2b8',
            success: '#28a745',
            warning: '#ffc107',
            danger: '#dc3545',
            purple: '#6f42c1',
            pink: '#e83e8c',
            teal: '#20c997'
        };
        
        // Monthly Chart
        <?php 
        $monthLabels = [];
        $monthData = [];
        $monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        if (!empty($stats['by_month'])) {
            foreach ($stats['by_month'] as $item) {
                $date = DateTime::createFromFormat('Y-m', $item['month']);
                $monthLabels[] = $date ? $monthNames[$date->format('n') - 1] . ' ' . $date->format('y') : $item['month'];
                $monthData[] = $item['count'];
            }
        }
        ?>
        
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($monthLabels) ?>,
                    datasets: [{
                        label: 'Événements',
                        data: <?= json_encode($monthData) ?>,
                        backgroundColor: 'rgba(0, 102, 204, 0.8)',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        
        // Campus Chart
        <?php 
        $campusLabels = [];
        $campusData = [];
        if (!empty($stats['by_campus'])) {
            foreach ($stats['by_campus'] as $item) {
                $campusLabels[] = $item['campus'];
                $campusData[] = $item['count'];
            }
        }
        ?>
        
        const campusCtx = document.getElementById('campusChart');
        if (campusCtx) {
            new Chart(campusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($campusLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($campusData) ?>,
                        backgroundColor: [
                            colors.primary,
                            colors.success,
                            colors.warning,
                            colors.danger,
                            colors.purple,
                            colors.teal
                        ],
                        borderWidth: 0,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
