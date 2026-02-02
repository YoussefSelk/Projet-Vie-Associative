<?php
/**
 * Tableau de bord analytique des evenements
 * 
 * Affiche des statistiques detaillees sur les evenements :
 * - Graphiques d'evolution temporelle (Chart.js)
 * - Repartition par type d'evenement
 * - Taux de participation
 * - Comparaison entre campus
 * 
 * Utilise Chart.js pour la visualisation des donnees.
 * 
 * Variables attendues :
 * - $stats : Statistiques globales
 * - $events_by_month : Donnees pour graphique temporel
 * - $events_by_type : Repartition par type
 * 
 * Permissions : BDE ou Admin requis
 * 
 * @package Views/Event
 */
$pageCss = ['shared', 'buttons', 'tables', 'events', 'dashboard'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
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
