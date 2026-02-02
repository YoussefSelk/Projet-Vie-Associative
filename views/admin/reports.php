<?php
/**
 * Rapports & Statistiques - Administration
 * 
 * Interface de visualisation et gestion des rapports :
 * - Rapports mensuels d'activité
 * - Performance des clubs
 * - Engagement utilisateurs par promotion
 * - Export des données en CSV
 * 
 * Variables attendues :
 * - $report_type : Type de rapport sélectionné
 * - $report_data : Données du rapport
 * 
 * Permissions : Admin (niveau 3+) requis
 * 
 * @package Views/Admin
 */

// Calculer les statistiques supplémentaires pour le rapport mensuel
$monthlyStats = [];
if ($report_type === 'monthly' && isset($report_data['events'])) {
    $total = $report_data['events']['total_events'] ?? 0;
    $validated = $report_data['events']['validated'] ?? 0;
    $monthlyStats['validation_rate'] = $total > 0 ? round(($validated / $total) * 100, 1) : 0;
    $monthlyStats['pending'] = $total - $validated - ($report_data['events']['rejected'] ?? 0);
}

// Calculer les totaux pour le rapport clubs
$clubTotals = [];
if ($report_type === 'clubs' && !empty($report_data['clubs'])) {
    $clubTotals['total_members'] = array_sum(array_column($report_data['clubs'], 'members_count'));
    $clubTotals['total_events'] = array_sum(array_column($report_data['clubs'], 'events_count'));
    $clubTotals['avg_members'] = count($report_data['clubs']) > 0 ? round($clubTotals['total_members'] / count($report_data['clubs']), 1) : 0;
}

// Calculer les totaux pour le rapport utilisateurs
$userTotals = [];
if ($report_type === 'users' && !empty($report_data['by_promo'])) {
    $userTotals['total_users'] = array_sum(array_column($report_data['by_promo'], 'total_users'));
    $userTotals['avg_active'] = count($report_data['by_promo']) > 0 ? round(array_sum(array_column($report_data['by_promo'], 'active_percentage')) / count($report_data['by_promo']), 1) : 0;
}

// Formatter le mois en français
function formatMonthFr($yearMonth) {
    $months = [
        '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
        '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
        '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
    ];
    $parts = explode('-', $yearMonth);
    if (count($parts) === 2) {
        return ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    }
    return $yearMonth;
}
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
        <div class="admin-reports">
            <!-- Page Header -->
            <div class="reports-header">
                <h1><i class="fas fa-chart-line"></i> Rapports & Statistiques</h1>
                <a href="?page=admin" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
            
            <!-- Report Navigation -->
            <nav class="report-nav">
                <a href="?page=admin-reports&type=monthly" class="report-nav-item <?= $report_type === 'monthly' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Rapport Mensuel
                </a>
                <a href="?page=admin-reports&type=clubs" class="report-nav-item <?= $report_type === 'clubs' ? 'active' : '' ?>">
                    <i class="fas fa-trophy"></i> Performance Clubs
                </a>
                <a href="?page=admin-reports&type=users" class="report-nav-item <?= $report_type === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Engagement Utilisateurs
                </a>
            </nav>

            <?php if ($report_type === 'monthly'): ?>
            <!-- ===================== MONTHLY REPORT ===================== -->
            
            <!-- Quick Summary -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="label">Événements du mois</div>
                    <div class="value"><?= $report_data['events']['total_events'] ?? 0 ?></div>
                    <div class="subtext"><?= formatMonthFr($report_data['month'] ?? date('Y-m')) ?></div>
                </div>
                <div class="summary-card success">
                    <div class="label">Taux de validation</div>
                    <div class="value"><?= $monthlyStats['validation_rate'] ?? 0 ?>%</div>
                    <div class="subtext"><?= $report_data['events']['validated'] ?? 0 ?> validés</div>
                </div>
                <div class="summary-card info">
                    <div class="label">Inscriptions</div>
                    <div class="value"><?= $report_data['subscriptions'] ?? 0 ?></div>
                    <div class="subtext">participants inscrits</div>
                </div>
                <div class="summary-card warning">
                    <div class="label">Nouveaux clubs</div>
                    <div class="value"><?= $report_data['new_clubs'] ?? 0 ?></div>
                    <div class="subtext">clubs créés ce mois</div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-card-header">
                    <div>
                        <h2><i class="fas fa-calendar-alt"></i> Rapport Mensuel Détaillé</h2>
                        <div class="subtitle">Statistiques pour : <?= formatMonthFr($report_data['month'] ?? date('Y-m')) ?></div>
                    </div>
                </div>
                <div class="report-card-body">
                    <!-- Month Selector -->
                    <form class="month-form" method="GET">
                        <input type="hidden" name="page" value="admin-reports">
                        <input type="hidden" name="type" value="monthly">
                        <label><i class="fas fa-calendar"></i> Sélectionner un mois :</label>
                        <input type="month" name="month" value="<?= htmlspecialchars($report_data['month'] ?? date('Y-m')) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Afficher
                        </button>
                    </form>
                    
                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-box">
                            <span class="number"><?= $report_data['events']['total_events'] ?? 0 ?></span>
                            <span class="label">Total événements</span>
                        </div>
                        <div class="stat-box success">
                            <span class="number"><?= $report_data['events']['validated'] ?? 0 ?></span>
                            <span class="label">Événements validés</span>
                        </div>
                        <div class="stat-box danger">
                            <span class="number"><?= $report_data['events']['rejected'] ?? 0 ?></span>
                            <span class="label">Événements rejetés</span>
                        </div>
                        <div class="stat-box warning">
                            <span class="number"><?= $monthlyStats['pending'] ?? 0 ?></span>
                            <span class="label">En attente</span>
                        </div>
                        <div class="stat-box">
                            <span class="number"><?= $report_data['subscriptions'] ?? 0 ?></span>
                            <span class="label">Inscriptions</span>
                        </div>
                    </div>

                    <?php if (($report_data['events']['total_events'] ?? 0) > 0): ?>
                    <!-- Visual Chart -->
                    <div class="chart-container">
                        <h4><i class="fas fa-chart-bar"></i> Répartition des événements</h4>
                        <div class="bar-chart">
                            <?php 
                            $total = $report_data['events']['total_events'] ?? 1;
                            $validated = $report_data['events']['validated'] ?? 0;
                            $rejected = $report_data['events']['rejected'] ?? 0;
                            $pending = $total - $validated - $rejected;
                            ?>
                            <div class="bar-chart-item">
                                <span class="bar-chart-label">Validés</span>
                                <div class="bar-chart-bar">
                                    <div class="bar-chart-fill" style="width: <?= ($validated / $total) * 100 ?>%; background: linear-gradient(90deg, #28a745, #34ce57);"></div>
                                </div>
                                <span class="bar-chart-value"><?= $validated ?></span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-chart-label">En attente</span>
                                <div class="bar-chart-bar">
                                    <div class="bar-chart-fill" style="width: <?= ($pending / $total) * 100 ?>%; background: linear-gradient(90deg, #ffc107, #ffda6a);"></div>
                                </div>
                                <span class="bar-chart-value"><?= $pending ?></span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-chart-label">Rejetés</span>
                                <div class="bar-chart-bar">
                                    <div class="bar-chart-fill" style="width: <?= ($rejected / $total) * 100 ?>%; background: linear-gradient(90deg, #dc3545, #e4606d);"></div>
                                </div>
                                <span class="bar-chart-value"><?= $rejected ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="export-info">
                            <i class="fas fa-info-circle"></i>
                            Données du mois de <?= formatMonthFr($report_data['month'] ?? date('Y-m')) ?>
                        </div>
                        <div class="export-buttons">
                            <a href="?page=export-data&type=events&month=<?= htmlspecialchars($report_data['month'] ?? date('Y-m')) ?>" class="btn btn-success">
                                <i class="fas fa-download"></i> Exporter (CSV)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($report_type === 'clubs'): ?>
            <!-- ===================== CLUBS PERFORMANCE ===================== -->
            
            <?php if (!empty($report_data['clubs'])): ?>
            <!-- Quick Summary -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="label">Total des clubs</div>
                    <div class="value"><?= count($report_data['clubs']) ?></div>
                    <div class="subtext">clubs validés</div>
                </div>
                <div class="summary-card success">
                    <div class="label">Total membres</div>
                    <div class="value"><?= $clubTotals['total_members'] ?? 0 ?></div>
                    <div class="subtext">dans tous les clubs</div>
                </div>
                <div class="summary-card info">
                    <div class="label">Événements organisés</div>
                    <div class="value"><?= $clubTotals['total_events'] ?? 0 ?></div>
                    <div class="subtext">événements validés</div>
                </div>
                <div class="summary-card warning">
                    <div class="label">Moyenne membres</div>
                    <div class="value"><?= $clubTotals['avg_members'] ?? 0 ?></div>
                    <div class="subtext">par club</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="report-card">
                <div class="report-card-header">
                    <div>
                        <h2><i class="fas fa-trophy"></i> Classement des Clubs</h2>
                        <div class="subtitle">Classement par activité et nombre d'événements</div>
                    </div>
                </div>
                <div class="report-card-body">
                    <?php if (empty($report_data['clubs'])): ?>
                        <div class="empty-state">
                            <i class="fas fa-building"></i>
                            <h3>Aucun club trouvé</h3>
                            <p>Il n'y a pas encore de clubs validés dans le système.</p>
                        </div>
                    <?php else: ?>
                        <!-- Info Box -->
                        <div class="info-box">
                            <i class="fas fa-lightbulb"></i>
                            <div class="info-box-content">
                                <p>Ce classement est basé sur le nombre d'événements organisés par chaque club. Les clubs les plus actifs apparaissent en premier.</p>
                            </div>
                        </div>

                        <!-- Clubs Table -->
                        <div style="overflow-x: auto;">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Rang</th>
                                        <th>Club</th>
                                        <th>Campus</th>
                                        <th style="text-align: center;">Membres</th>
                                        <th style="text-align: center;">Événements</th>
                                        <th style="width: 180px;">Activité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $max_events = max(array_column($report_data['clubs'], 'events_count')) ?: 1;
                                    $rank = 1;
                                    foreach ($report_data['clubs'] as $club): 
                                        $percentage = ($club['events_count'] / $max_events) * 100;
                                        $rankClass = '';
                                        if ($rank === 1) $rankClass = 'gold';
                                        elseif ($rank === 2) $rankClass = 'silver';
                                        elseif ($rank === 3) $rankClass = 'bronze';
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="rank <?= $rankClass ?>">
                                                    <?php if ($rank <= 3): ?>
                                                        <i class="fas fa-medal"></i>
                                                    <?php endif; ?>
                                                    #<?= $rank++ ?>
                                                </span>
                                            </td>
                                            <td><strong><?= htmlspecialchars($club['nom_club']) ?></strong></td>
                                            <td><?= htmlspecialchars($club['campus'] ?? '-') ?></td>
                                            <td style="text-align: center;"><?= $club['members_count'] ?></td>
                                            <td style="text-align: center;"><strong><?= $club['events_count'] ?></strong></td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="fill" style="width: <?= $percentage ?>%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Visual Chart for Top 5 -->
                        <?php if (count($report_data['clubs']) > 0): ?>
                        <div class="chart-container" style="margin-top: 25px;">
                            <h4><i class="fas fa-chart-bar"></i> Top 5 des clubs les plus actifs</h4>
                            <div class="bar-chart">
                                <?php 
                                $topClubs = array_slice($report_data['clubs'], 0, 5);
                                foreach ($topClubs as $club): 
                                    $pct = ($club['events_count'] / $max_events) * 100;
                                ?>
                                <div class="bar-chart-item">
                                    <span class="bar-chart-label"><?= htmlspecialchars(mb_substr($club['nom_club'], 0, 15)) ?><?= mb_strlen($club['nom_club']) > 15 ? '...' : '' ?></span>
                                    <div class="bar-chart-bar">
                                        <div class="bar-chart-fill" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span class="bar-chart-value"><?= $club['events_count'] ?> évts</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="export-info">
                            <i class="fas fa-info-circle"></i>
                            <?= count($report_data['clubs'] ?? []) ?> clubs au total
                        </div>
                        <div class="export-buttons">
                            <a href="?page=export-data&type=clubs" class="btn btn-success">
                                <i class="fas fa-download"></i> Exporter les clubs (CSV)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($report_type === 'users'): ?>
            <!-- ===================== USERS ENGAGEMENT ===================== -->
            
            <?php if (!empty($report_data['by_promo'])): ?>
            <!-- Quick Summary -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="label">Promotions</div>
                    <div class="value"><?= count($report_data['by_promo']) ?></div>
                    <div class="subtext">promotions différentes</div>
                </div>
                <div class="summary-card success">
                    <div class="label">Total utilisateurs</div>
                    <div class="value"><?= $userTotals['total_users'] ?? 0 ?></div>
                    <div class="subtext">avec promotion renseignée</div>
                </div>
                <div class="summary-card info">
                    <div class="label">Taux d'activité moyen</div>
                    <div class="value"><?= $userTotals['avg_active'] ?? 0 ?>%</div>
                    <div class="subtext">utilisateurs actifs</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="report-card">
                <div class="report-card-header">
                    <div>
                        <h2><i class="fas fa-users"></i> Engagement par Promotion</h2>
                        <div class="subtitle">Analyse de la participation et de l'activité par promotion</div>
                    </div>
                </div>
                <div class="report-card-body">
                    <?php if (empty($report_data['by_promo'])): ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <h3>Aucune donnée disponible</h3>
                            <p>Les utilisateurs n'ont pas encore renseigné leur promotion dans leur profil.</p>
                        </div>
                    <?php else: ?>
                        <!-- Info Box -->
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <div class="info-box-content">
                                <p>Le taux d'activité est calculé en fonction du niveau de permission des utilisateurs (membres de clubs, organisateurs, etc.).</p>
                            </div>
                        </div>

                        <!-- Promo Cards Grid -->
                        <div class="promo-grid">
                            <?php foreach ($report_data['by_promo'] as $promo): 
                                $activeRate = round($promo['active_percentage'] ?? 0, 1);
                            ?>
                                <div class="promo-card">
                                    <div class="promo-card-header">
                                        <i class="fas fa-graduation-cap"></i>
                                        <?= htmlspecialchars($promo['promo']) ?>
                                    </div>
                                    <div class="promo-stat">
                                        <span class="label">Total utilisateurs</span>
                                        <span class="value"><?= $promo['total_users'] ?></span>
                                    </div>
                                    <div class="promo-stat">
                                        <span class="label">Taux d'activité</span>
                                        <span class="value highlight"><?= $activeRate ?>%</span>
                                    </div>
                                    <div class="promo-stat">
                                        <span class="label">Utilisateurs actifs</span>
                                        <span class="value"><?= round($promo['total_users'] * $activeRate / 100) ?></span>
                                    </div>
                                    <!-- Mini progress bar -->
                                    <div style="margin-top: 10px;">
                                        <div class="progress-bar" style="height: 6px;">
                                            <div class="fill" style="width: <?= $activeRate ?>%; background: <?= $activeRate >= 50 ? 'linear-gradient(90deg, #28a745, #34ce57)' : ($activeRate >= 25 ? 'linear-gradient(90deg, #ffc107, #ffda6a)' : 'linear-gradient(90deg, #dc3545, #e4606d)') ?>;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Visual Chart -->
                        <div class="chart-container" style="margin-top: 25px;">
                            <h4><i class="fas fa-chart-bar"></i> Utilisateurs par promotion</h4>
                            <div class="bar-chart">
                                <?php 
                                $maxUsers = max(array_column($report_data['by_promo'], 'total_users')) ?: 1;
                                foreach ($report_data['by_promo'] as $promo): 
                                    $pct = ($promo['total_users'] / $maxUsers) * 100;
                                ?>
                                <div class="bar-chart-item">
                                    <span class="bar-chart-label"><?= htmlspecialchars($promo['promo']) ?></span>
                                    <div class="bar-chart-bar">
                                        <div class="bar-chart-fill" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span class="bar-chart-value"><?= $promo['total_users'] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="export-info">
                            <i class="fas fa-info-circle"></i>
                            <?= $userTotals['total_users'] ?? 0 ?> utilisateurs avec promotion renseignée
                        </div>
                        <div class="export-buttons">
                            <a href="?page=export-data&type=users" class="btn btn-success">
                                <i class="fas fa-download"></i> Exporter les utilisateurs (CSV)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
