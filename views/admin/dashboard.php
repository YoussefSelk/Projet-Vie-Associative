<?php
/**
 * Tableau de bord administrateur
 * 
 * Vue principale d'administration affichant :
 * - Statistiques globales (utilisateurs, clubs, événements)
 * - Graphiques d'activité (Chart.js)
 * - Actions en attente de validation
 * - Raccourcis vers les fonctionnalités admin
 * 
 * Variables attendues :
 * - $stats : Tableau des statistiques globales
 * - $pending_clubs : Clubs en attente de validation
 * - $pending_events : Événements en attente
 * - $recent_users : Derniers utilisateurs inscrits
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
$pageTitle = 'Tableau de bord - Administration EILCO';
$pageCss = ['shared', 'buttons', 'tables', 'admin', 'dashboard'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="admin-dashboard">
            <div class="admin-header">
                <div class="header-left">
                    <a href="?page=home" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
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
                <div class="stat-box superadmin new-users">
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['new_users_week'] ?? 0) ?></h3>
                        <p>Nouveaux (7j)</p>
                    </div>
                </div>
                
                <div class="stat-box superadmin upcoming">
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['upcoming_events'] ?? 0) ?></h3>
                        <p>À venir (30j)</p>
                    </div>
                </div>
                
                <div class="stat-box superadmin members">
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="info">
                        <h3><?= number_format($stats['total_club_members'] ?? 0) ?></h3>
                        <p>Membres clubs</p>
                    </div>
                </div>
                
                <div class="stat-box superadmin rejected">
                    <div class="icon">
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
                                        <div class="activity-icon <?= htmlspecialchars($activity['type'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas <?= $activity['type'] === 'club' ? 'fa-building' : 'fa-calendar' ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4><?= htmlspecialchars($activity['title'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8') ?></h4>
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
                            <a href="?page=event-analytics" class="action-item superadmin-analytics">
                                <div class="action-icon">
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
                            <a href="?page=admin-users" class="action-item superadmin-users">
                                <div class="action-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Gestion utilisateurs avancée</h4>
                                    <p>Recherche, filtres, permissions</p>
                                </div>
                            </a>
                            <a href="?page=admin-settings" class="action-item superadmin-settings">
                                <div class="action-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Paramètres système</h4>
                                    <p>Configuration avancée</p>
                                </div>
                            </a>
                            <a href="?page=admin-audit" class="action-item superadmin-audit">
                                <div class="action-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Audit & Sécurité</h4>
                                    <p>Logs et événements sécurité</p>
                                </div>
                            </a>
                            <a href="?page=admin-database" class="action-item superadmin-database">
                                <div class="action-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Outils base de données</h4>
                                    <p>Nettoyage et maintenance</p>
                                </div>
                            </a>
                            <a href="?page=admin-reports" class="action-item superadmin-reports">
                                <div class="action-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-text">
                                    <h4>Rapports avancés</h4>
                                    <p>Statistiques détaillées</p>
                                </div>
                            </a>
                            <a href="?page=tutoring" class="action-item superadmin-tutoring">
                                <div class="action-icon">
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
            success: '#059669',
            warning: '#d97706',
            danger: '#dc2626',
            info: '#0284c7',
            purple: '#5b21b6',
            pink: '#9d174d',
            orange: '#b45309'
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
                    labels: <?= json_encode($campusLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    datasets: [{
                        data: <?= json_encode($campusData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
                    labels: <?= json_encode($monthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    datasets: [{
                        label: 'Événements',
                        data: <?= json_encode($monthData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
                    labels: <?= json_encode($roleLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    datasets: [{
                        data: <?= json_encode($roleData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
