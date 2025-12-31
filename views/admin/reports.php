<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
.admin-reports {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 1.8rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h1 i {
    color: #9b59b6;
}

/* Report Type Tabs */
.report-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.report-tab {
    padding: 12px 25px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.report-tab:hover {
    border-color: #9b59b6;
    background: #f8f4fc;
}

.report-tab.active {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
    border-color: transparent;
}

/* Report Container */
.report-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}

.report-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
}

.report-header h2 {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.report-header .subtitle {
    opacity: 0.9;
    font-size: 0.95rem;
}

.report-body {
    padding: 30px;
}

/* Monthly Report */
.monthly-selector {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.monthly-selector label {
    font-weight: 500;
    color: #555;
}

.monthly-selector input[type="month"] {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.monthly-selector .btn {
    padding: 10px 20px;
    background: #9b59b6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

/* Stats Grid */
.report-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.report-stat {
    background: linear-gradient(135deg, #f8f9fa, #fff);
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s;
}

.report-stat:hover {
    border-color: #9b59b6;
    transform: translateY(-3px);
}

.report-stat .number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #9b59b6;
    display: block;
}

.report-stat .label {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-top: 5px;
}

/* Clubs Table */
.clubs-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
}

.clubs-table th {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.clubs-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.clubs-table tr:hover {
    background: #f8fafc;
}

.clubs-table tr:last-child td {
    border-bottom: none;
}

/* Progress Bar */
.progress-bar {
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar .fill {
    height: 100%;
    background: linear-gradient(90deg, #9b59b6, #8e44ad);
    border-radius: 5px;
}

/* Promo Stats */
.promo-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.promo-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s;
}

.promo-card:hover {
    border-color: #3498db;
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.15);
}

.promo-card .promo-name {
    font-size: 1.3rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 15px;
}

.promo-card .stat-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.promo-card .stat-row:last-child {
    border-bottom: none;
}

.promo-card .stat-label {
    color: #7f8c8d;
}

.promo-card .stat-value {
    font-weight: 600;
    color: #2c3e50;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Download Button */
.download-section {
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.download-section .btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary { background: #3498db; color: white; }
.btn-success { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="admin-reports">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Rapports & Statistiques</h1>
        <a href="?page=admin" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
    
    <!-- Report Type Tabs -->
    <div class="report-tabs">
        <a href="?page=admin-reports&type=monthly" class="report-tab <?php if ($report_type === 'monthly') echo 'active'; ?>">
            <i class="fas fa-calendar-alt"></i> Rapport Mensuel
        </a>
        <a href="?page=admin-reports&type=clubs" class="report-tab <?php if ($report_type === 'clubs') echo 'active'; ?>">
            <i class="fas fa-users"></i> Performance Clubs
        </a>
        <a href="?page=admin-reports&type=users" class="report-tab <?php if ($report_type === 'users') echo 'active'; ?>">
            <i class="fas fa-user-graduate"></i> Engagement Utilisateurs
        </a>
    </div>
    
    <!-- Report Container -->
    <div class="report-container">
        <?php if ($report_type === 'monthly'): ?>
            <!-- Monthly Report -->
            <div class="report-header">
                <h2><i class="fas fa-calendar-alt"></i> Rapport Mensuel</h2>
                <div class="subtitle">Statistiques du mois: <?php echo $report_data['month'] ?? date('Y-m'); ?></div>
            </div>
            <div class="report-body">
                <form class="monthly-selector" method="GET">
                    <input type="hidden" name="page" value="admin-reports">
                    <input type="hidden" name="type" value="monthly">
                    <label><i class="fas fa-calendar"></i> Sélectionner un mois :</label>
                    <input type="month" name="month" value="<?php echo $report_data['month'] ?? date('Y-m'); ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i> Afficher</button>
                </form>
                
                <div class="report-stats">
                    <div class="report-stat">
                        <span class="number"><?php echo $report_data['events']['total_events'] ?? 0; ?></span>
                        <span class="label">Total événements</span>
                    </div>
                    <div class="report-stat">
                        <span class="number"><?php echo $report_data['events']['validated'] ?? 0; ?></span>
                        <span class="label">Validés</span>
                    </div>
                    <div class="report-stat">
                        <span class="number"><?php echo $report_data['events']['rejected'] ?? 0; ?></span>
                        <span class="label">Rejetés</span>
                    </div>
                    <div class="report-stat">
                        <span class="number"><?php echo $report_data['subscriptions'] ?? 0; ?></span>
                        <span class="label">Inscriptions</span>
                    </div>
                </div>
            </div>
            
        <?php elseif ($report_type === 'clubs'): ?>
            <!-- Clubs Performance Report -->
            <div class="report-header">
                <h2><i class="fas fa-trophy"></i> Performance des Clubs</h2>
                <div class="subtitle">Classement par activité et engagement</div>
            </div>
            <div class="report-body">
                <?php if (empty($report_data['clubs'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>Aucun club trouvé</h3>
                        <p>Il n'y a pas encore de clubs validés</p>
                    </div>
                <?php else: ?>
                    <table class="clubs-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Club</th>
                                <th>Campus</th>
                                <th>Membres</th>
                                <th>Événements</th>
                                <th>Activité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_events = max(array_column($report_data['clubs'], 'events_count')) ?: 1;
                            $rank = 1;
                            foreach ($report_data['clubs'] as $club): 
                                $percentage = ($club['events_count'] / $max_events) * 100;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $rank++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($club['nom_club']); ?></td>
                                    <td><?php echo htmlspecialchars($club['campus'] ?? '-'); ?></td>
                                    <td><?php echo $club['members_count']; ?></td>
                                    <td><?php echo $club['events_count']; ?></td>
                                    <td style="width: 200px;">
                                        <div class="progress-bar">
                                            <div class="fill" style="width: <?php echo $percentage; ?>%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <div class="download-section">
                    <a href="?page=export-data&type=clubs" class="btn btn-success">
                        <i class="fas fa-download"></i> Exporter les clubs (CSV)
                    </a>
                </div>
            </div>
            
        <?php elseif ($report_type === 'users'): ?>
            <!-- Users Engagement Report -->
            <div class="report-header">
                <h2><i class="fas fa-user-graduate"></i> Engagement par Promotion</h2>
                <div class="subtitle">Analyse de la participation par promo</div>
            </div>
            <div class="report-body">
                <?php if (empty($report_data['by_promo'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>Aucune donnée disponible</h3>
                        <p>Les utilisateurs n'ont pas encore renseigné leur promotion</p>
                    </div>
                <?php else: ?>
                    <div class="promo-cards">
                        <?php foreach ($report_data['by_promo'] as $promo): ?>
                            <div class="promo-card">
                                <div class="promo-name">
                                    <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($promo['promo']); ?>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Total utilisateurs</span>
                                    <span class="stat-value"><?php echo $promo['total_users']; ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Utilisateurs actifs</span>
                                    <span class="stat-value"><?php echo round($promo['active_percentage'] ?? 0, 1); ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="download-section">
                    <a href="?page=export-data&type=users" class="btn btn-success">
                        <i class="fas fa-download"></i> Exporter les utilisateurs (CSV)
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
