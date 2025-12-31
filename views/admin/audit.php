<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
.admin-audit {
    max-width: 1400px;
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
    color: #e74c3c;
}

/* Stats Cards */
.audit-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.audit-stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 18px;
}

.stat-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.security { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.stat-icon.errors { background: linear-gradient(135deg, #f39c12, #d68910); }
.stat-icon.privileged { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.stat-content .number {
    font-size: 1.8rem;
    font-weight: bold;
    color: #2c3e50;
}

.stat-content .label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* Log Sections */
.log-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
}

.log-header {
    padding: 18px 25px;
    background: #2c3e50;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.log-header .title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-header .count {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
}

.log-body {
    max-height: 400px;
    overflow-y: auto;
}

.log-entry {
    padding: 12px 25px;
    border-bottom: 1px solid #eee;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.5;
    word-break: break-all;
}

.log-entry:nth-child(even) {
    background: #f8f9fa;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-entry.error {
    color: #e74c3c;
}

.log-entry.warning {
    color: #f39c12;
}

.log-entry.info {
    color: #3498db;
}

/* Empty State */
.empty-logs {
    padding: 50px;
    text-align: center;
    color: #7f8c8d;
}

.empty-logs i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Info Box */
.info-box {
    background: linear-gradient(135deg, #e8f4f8, #d6eaf8);
    border-left: 4px solid #3498db;
    padding: 20px 25px;
    border-radius: 0 12px 12px 0;
    margin-bottom: 25px;
}

.info-box h4 {
    margin-bottom: 10px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-box ul {
    margin: 0;
    padding-left: 25px;
    color: #555;
}

.info-box li {
    margin: 5px 0;
}

/* Tab Navigation */
.log-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.log-tab {
    padding: 12px 25px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.log-tab:hover {
    border-color: #3498db;
}

.log-tab.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}
    </style>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="admin-audit">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-shield-alt"></i> Audit & Sécurité</h1>
        <a href="?page=admin" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
    </div>
    
    <!-- Stats Cards -->
    <div class="audit-stats">
        <div class="audit-stat-card">
            <div class="stat-icon security"><i class="fas fa-lock"></i></div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['security_events'] ?? 0; ?></div>
                <div class="label">Événements sécurité</div>
            </div>
        </div>
        <div class="audit-stat-card">
            <div class="stat-icon errors"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['error_count'] ?? 0; ?></div>
                <div class="label">Erreurs récentes</div>
            </div>
        </div>
        <div class="audit-stat-card">
            <div class="stat-icon privileged"><i class="fas fa-user-shield"></i></div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['privileged_users'] ?? 0; ?></div>
                <div class="label">Utilisateurs privilégiés</div>
            </div>
        </div>
    </div>
    
    <!-- Info Box -->
    <div class="info-box">
        <h4><i class="fas fa-info-circle"></i> À propos de l'audit</h4>
        <ul>
            <li>Les logs de sécurité enregistrent les tentatives de connexion suspectes</li>
            <li>Les logs d'erreurs contiennent les erreurs PHP et les exceptions</li>
            <li>Les utilisateurs privilégiés ont un niveau de permission ≥ 3</li>
            <li>Vérifiez régulièrement ces logs pour détecter les anomalies</li>
        </ul>
    </div>
    
    <!-- Security Logs -->
    <div class="log-section">
        <div class="log-header">
            <span class="title"><i class="fas fa-lock"></i> Logs de sécurité</span>
            <span class="count"><?php echo count($login_attempts); ?> entrées</span>
        </div>
        <div class="log-body">
            <?php if (empty($login_attempts)): ?>
                <div class="empty-logs">
                    <i class="fas fa-check-circle"></i>
                    <h4>Aucun événement de sécurité</h4>
                    <p>Aucune tentative suspecte n'a été enregistrée</p>
                </div>
            <?php else: ?>
                <?php foreach ($login_attempts as $log): ?>
                    <div class="log-entry <?php 
                        echo strpos($log, 'FAIL') !== false ? 'error' : 
                            (strpos($log, 'WARN') !== false ? 'warning' : 'info'); 
                    ?>">
                        <?php echo htmlspecialchars(trim($log)); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Error Logs -->
    <div class="log-section">
        <div class="log-header" style="background: #e74c3c;">
            <span class="title"><i class="fas fa-exclamation-circle"></i> Logs d'erreurs</span>
            <span class="count"><?php echo count($error_logs); ?> entrées</span>
        </div>
        <div class="log-body">
            <?php if (empty($error_logs)): ?>
                <div class="empty-logs">
                    <i class="fas fa-smile"></i>
                    <h4>Aucune erreur récente</h4>
                    <p>Le système fonctionne normalement</p>
                </div>
            <?php else: ?>
                <?php foreach ($error_logs as $log): ?>
                    <div class="log-entry error">
                        <?php echo htmlspecialchars(trim($log)); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
