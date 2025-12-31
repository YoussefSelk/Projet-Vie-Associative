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
                
                <?php if (($_SESSION['permission'] ?? 0) == 5): ?>
                <!-- Super Admin Extra Stats -->
                <div class="stat-box" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                    <div class="icon" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['new_users_week'] ?? 0) ?></h3>
                        <p>Nouveaux (7j)</p>
                    </div>
                </div>
                
                <div class="stat-box" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                    <div class="icon" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['upcoming_events'] ?? 0) ?></h3>
                        <p>À venir (30j)</p>
                    </div>
                </div>
                
                <div class="stat-box" style="background: linear-gradient(135deg, #fd7e14 0%, #e06b0a 100%);">
                    <div class="icon" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_club_members'] ?? 0) ?></h3>
                        <p>Membres clubs</p>
                    </div>
                </div>
                
                <div class="stat-box" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <div class="icon" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format(($stats['rejected_clubs'] ?? 0) + ($stats['rejected_events'] ?? 0)) ?></h3>
                        <p>Rejetés</p>
                    </div>
                </div>
                <?php endif; ?>
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
                                            <h4><?= html_entity_decode(htmlspecialchars_decode($activity['title'] ?? 'Sans titre')) ?></h4>
                                            <p>
                                                <?= $activity['type'] === 'club' ? 'Nouveau club' : 'Nouvel événement' ?>
                                                - <?= htmlspecialchars($activity['campus'] ?? '') ?>
                                            </p>
                                        </div>
                                        <span class="activity-time">
                                            <?php 
                                            if (isset($activity['date']) && $activity['date']) {
                                                $date = strtotime($activity['date']);
                                                $now = time();
                                                $diff = $now - $date;
                                                
                                                if ($diff < 0) {
                                                    // Future date
                                                    $diff = abs($diff);
                                                    if ($diff < 3600) {
                                                        echo 'Dans ' . floor($diff / 60) . ' min';
                                                    } elseif ($diff < 86400) {
                                                        echo 'Dans ' . floor($diff / 3600) . ' h';
                                                    } elseif ($diff < 604800) {
                                                        echo 'Dans ' . floor($diff / 86400) . ' j';
                                                    } else {
                                                        echo date('d/m', $date);
                                                    }
                                                } else {
                                                    // Past date
                                                    if ($diff < 60) {
                                                        echo 'À l\'instant';
                                                    } elseif ($diff < 3600) {
                                                        echo 'Il y a ' . floor($diff / 60) . ' min';
                                                    } elseif ($diff < 86400) {
                                                        echo 'Il y a ' . floor($diff / 3600) . ' h';
                                                    } elseif ($diff < 604800) {
                                                        echo 'Il y a ' . floor($diff / 86400) . ' j';
                                                    } else {
                                                        echo date('d/m', $date);
                                                    }
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
                            <a href="?page=event-analytics" class="action-item" style="background: linear-gradient(135deg, rgba(0, 102, 204, 0.1) 0%, rgba(0, 73, 153, 0.1) 100%); border-left: 3px solid #0066cc;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #0066cc 0%, #004999 100%);">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Analytiques événements</h4>
                                    <p>Statistiques et tendances</p>
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
                            
                            <?php if (($_SESSION['permission'] ?? 0) == 5): ?>
                            <!-- Super Admin Only -->
                            <a href="?page=admin-users" class="action-item" style="background: linear-gradient(135deg, rgba(0, 123, 255, 0.1) 0%, rgba(0, 86, 179, 0.1) 100%); border-left: 3px solid #007bff;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Gestion utilisateurs avancée</h4>
                                    <p>Recherche, filtres, permissions</p>
                                </div>
                            </a>
                            <a href="?page=admin-settings" class="action-item" style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%); border-left: 3px solid #dc3545;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Paramètres système</h4>
                                    <p>Configuration avancée</p>
                                </div>
                            </a>
                            <a href="?page=admin-audit" class="action-item" style="background: linear-gradient(135deg, rgba(253, 126, 20, 0.1) 0%, rgba(230, 107, 10, 0.1) 100%); border-left: 3px solid #fd7e14;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #fd7e14 0%, #e06b0a 100%);">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Audit & Sécurité</h4>
                                    <p>Logs et événements sécurité</p>
                                </div>
                            </a>
                            <a href="?page=admin-database" class="action-item" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 134, 55, 0.1) 100%); border-left: 3px solid #28a745;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #28a745 0%, #208637 100%);">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Outils base de données</h4>
                                    <p>Nettoyage et maintenance</p>
                                </div>
                            </a>
                            <a href="?page=admin-reports" class="action-item" style="background: linear-gradient(135deg, rgba(155, 89, 182, 0.1) 0%, rgba(142, 68, 173, 0.1) 100%); border-left: 3px solid #9b59b6;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Rapports avancés</h4>
                                    <p>Statistiques détaillées</p>
                                </div>
                            </a>
                            <a href="?page=tutoring" class="action-item" style="background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(90, 50, 163, 0.1) 100%); border-left: 3px solid #6f42c1;">
                                <div class="action-icon" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Gestion tutorat</h4>
                                    <p>Valider en tant que tuteur</p>
                                </div>
                            </a>
                            <?php endif; ?>
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
