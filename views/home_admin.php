<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="admin-dashboard">
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord administrateur</h1>
                <div class="header-actions">
                    <span class="badge badge-info">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
                    </span>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="stats-overview">
                <div class="stat-box users">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_users'] ?? 0) ?></h3>
                        <p>Utilisateurs inscrits</p>
                    </div>
                </div>
                
                <div class="stat-box clubs">
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_clubs'] ?? 0) ?></h3>
                        <p>Clubs actifs</p>
                    </div>
                </div>
                
                <div class="stat-box events">
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_events'] ?? 0) ?></h3>
                        <p>Événements validés</p>
                    </div>
                </div>
                
                <div class="stat-box pending">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_pending'] ?? 0) ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="dashboard-row">
                <div class="dashboard-col">
                    <!-- Charts Section -->
                    <div class="charts-grid">
                        <div class="chart-panel">
                            <h3><i class="fas fa-chart-pie"></i> Clubs par campus</h3>
                            <div class="chart-wrapper">
                                <canvas id="clubsByCampusChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="chart-panel">
                            <h3><i class="fas fa-chart-bar"></i> Événements par mois</h3>
                            <div class="chart-wrapper">
                                <canvas id="eventsByMonthChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="recent-activity">
                        <h3>
                            <span><i class="fas fa-history"></i> Activité récente</span>
                        </h3>
                        <div class="activity-list">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?= $activity['type'] ?>">
                                            <i class="fas <?= $activity['type'] === 'club' ? 'fa-building' : 'fa-calendar' ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4><?= htmlspecialchars($activity['title'] ?? 'Sans titre') ?></h4>
                                            <p>
                                                <?= $activity['type'] === 'club' ? 'Nouveau club' : 'Nouvel événement' ?>
                                                - <?= htmlspecialchars($activity['campus'] ?? '') ?>
                                            </p>
                                        </div>
                                        <span class="activity-time">
                                            <?php 
                                            if (isset($activity['date']) && $activity['date']) {
                                                $date = strtotime($activity['date']);
                                                $diff = time() - $date;
                                                if ($diff < 3600) {
                                                    echo floor($diff / 60) . ' min';
                                                } elseif ($diff < 86400) {
                                                    echo floor($diff / 3600) . ' h';
                                                } else {
                                                    echo floor($diff / 86400) . ' j';
                                                }
                                            } else {
                                                echo 'Récent';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucune activité récente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard-col">
                    <!-- Quick Actions -->
                    <div class="quick-actions-panel">
                        <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                        <div class="action-list">
                            <a href="?page=club-list" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Gérer les clubs</h4>
                                    <p>Modifier, supprimer des clubs</p>
                                </div>
                            </a>
                            <a href="?page=club-create" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Créer un club</h4>
                                    <p>Ajouter un nouveau club</p>
                                </div>
                            </a>
                            <a href="?page=users-list" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Gérer les utilisateurs</h4>
                                    <p>Permissions et comptes</p>
                                </div>
                            </a>
                            <a href="?page=pending-clubs" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Valider les clubs</h4>
                                    <p><?= $stats['pending_clubs'] ?? 0 ?> en attente</p>
                                </div>
                            </a>
                            <a href="?page=pending-events" class="action-item">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Valider les événements</h4>
                                    <p><?= $stats['pending_events'] ?? 0 ?> en attente</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Pending Items -->
                    <?php if (($stats['pending_clubs'] ?? 0) > 0 || ($stats['pending_events'] ?? 0) > 0): ?>
                    <div class="pending-panel">
                        <h3><i class="fas fa-exclamation-triangle"></i> Éléments en attente</h3>
                        <div class="pending-list">
                            <?php if (($stats['pending_clubs'] ?? 0) > 0): ?>
                            <div class="pending-item">
                                <div class="info">
                                    <i class="fas fa-building"></i>
                                    <a href="?page=pending-clubs">Clubs à valider</a>
                                </div>
                                <span class="count"><?= $stats['pending_clubs'] ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (($stats['pending_events'] ?? 0) > 0): ?>
                            <div class="pending-item">
                                <div class="info">
                                    <i class="fas fa-calendar"></i>
                                    <a href="?page=pending-events">Événements à valider</a>
                                </div>
                                <span class="count"><?= $stats['pending_events'] ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- User Distribution -->
                    <div class="chart-panel">
                        <h3><i class="fas fa-user-shield"></i> Répartition des utilisateurs</h3>
                        <div class="chart-wrapper">
                            <canvas id="usersByRoleChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    // Chart.js configuration
    document.addEventListener('DOMContentLoaded', function() {
        // Color palette
        const colors = {
            primary: '#0066cc',
            success: '#28a745',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#17a2b8',
            purple: '#6f42c1',
            pink: '#e83e8c',
            orange: '#fd7e14'
        };

        const campusColors = [colors.primary, colors.success, colors.warning, colors.danger];

        // Clubs by Campus Chart
        <?php 
        $campusLabels = [];
        $campusData = [];
        if (!empty($stats['clubs_by_campus'])) {
            foreach ($stats['clubs_by_campus'] as $item) {
                $campusLabels[] = $item['campus'];
                $campusData[] = $item['count'];
            }
        }
        ?>
        
        const clubsByCampusCtx = document.getElementById('clubsByCampusChart');
        if (clubsByCampusCtx) {
            new Chart(clubsByCampusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($campusLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($campusData) ?>,
                        backgroundColor: campusColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }

        // Events by Month Chart
        <?php 
        $monthLabels = [];
        $monthData = [];
        if (!empty($stats['events_by_month'])) {
            foreach ($stats['events_by_month'] as $item) {
                $date = DateTime::createFromFormat('Y-m', $item['month']);
                $monthLabels[] = $date ? $date->format('M Y') : $item['month'];
                $monthData[] = $item['count'];
            }
        }
        ?>
        
        const eventsByMonthCtx = document.getElementById('eventsByMonthChart');
        if (eventsByMonthCtx) {
            new Chart(eventsByMonthCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($monthLabels) ?>,
                    datasets: [{
                        label: 'Événements',
                        data: <?= json_encode($monthData) ?>,
                        backgroundColor: colors.primary,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Users by Role Chart
        <?php 
        $roleLabels = [];
        $roleData = [];
        $roleNames = [
            1 => 'Étudiant',
            2 => 'Membre club',
            3 => 'BDE',
            4 => 'Admin',
            5 => 'Tuteur'
        ];
        if (!empty($stats['users_by_permission'])) {
            foreach ($stats['users_by_permission'] as $item) {
                $roleLabels[] = $roleNames[$item['permission']] ?? 'Niveau ' . $item['permission'];
                $roleData[] = $item['count'];
            }
        }
        ?>
        
        const usersByRoleCtx = document.getElementById('usersByRoleChart');
        if (usersByRoleCtx) {
            new Chart(usersByRoleCtx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($roleLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($roleData) ?>,
                        backgroundColor: [colors.info, colors.success, colors.warning, colors.danger, colors.purple],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
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
