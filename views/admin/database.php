<?php
/**
 * Outils base de donnees - Administration
 * 
 * Interface de maintenance de la base de donnees :
 * - Statistiques des tables (taille, lignes)
 * - Optimisation des tables
 * - Nettoyage des donnees orphelines
 * - Export/Import de donnees
 * 
 * Variables attendues :
 * - $tables : Liste des tables avec statistiques
 * - $db_size : Taille totale de la base
 * 
 * ATTENTION : Operations potentiellement destructives
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
.admin-database {
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
    color: #27ae60;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Database Stats Grid */
.db-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.db-stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    text-align: center;
}

.db-stat-card .icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.3rem;
}

.db-stat-card .table-name {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.db-stat-card .count {
    font-size: 1.8rem;
    font-weight: bold;
    color: #2c3e50;
}

/* Issues Section */
.issues-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    overflow: hidden;
}

.issues-header {
    padding: 18px 25px;
    background: #f39c12;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.issues-body {
    padding: 20px 25px;
}

.issue-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #fff3cd;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid #f39c12;
}

.issue-item:last-child {
    margin-bottom: 0;
}

.issue-item i {
    color: #f39c12;
}

.no-issues {
    text-align: center;
    padding: 30px;
    color: #27ae60;
}

.no-issues i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    display: block;
}

/* Actions Section */
.actions-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
}

.actions-header {
    padding: 18px 25px;
    background: #2c3e50;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.actions-body {
    padding: 25px;
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.action-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s;
}

.action-card:hover {
    border-color: #3498db;
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.15);
}

.action-card h4 {
    font-size: 1.1rem;
    color: #2c3e50;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-card h4 i {
    color: #3498db;
}

.action-card p {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.action-card .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.action-card .btn-warning {
    background: #f39c12;
    color: white;
}

.action-card .btn-danger {
    background: #e74c3c;
    color: white;
}

.action-card .btn-primary {
    background: #3498db;
    color: white;
}

.action-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Warning Box */
.warning-box {
    background: linear-gradient(135deg, #fff3cd, #fef9e7);
    border-left: 4px solid #f39c12;
    padding: 20px 25px;
    border-radius: 0 12px 12px 0;
    margin-bottom: 25px;
}

.warning-box h4 {
    margin-bottom: 10px;
    color: #856404;
    display: flex;
    align-items: center;
    gap: 10px;
}

.warning-box p {
    color: #856404;
    margin: 0;
}
    </style>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="admin-database">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-database"></i> Outils Base de Données</h1>
        <a href="?page=admin" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
    </div>
    
    <!-- Alert Messages -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>
    
    <!-- Database Stats -->
    <div class="db-stats-grid">
        <?php 
        $table_icons = [
            'users' => 'fa-users',
            'fiche_club' => 'fa-user-friends',
            'fiche_event' => 'fa-calendar',
            'membres_club' => 'fa-id-card',
            'subscribe_event' => 'fa-calendar-check',
            'rapport_event' => 'fa-file-alt'
        ];
        foreach ($db_stats as $table => $data): 
        ?>
            <div class="db-stat-card">
                <div class="icon">
                    <i class="fas <?php echo $table_icons[$table] ?? 'fa-table'; ?>"></i>
                </div>
                <div class="table-name"><?php echo htmlspecialchars($table); ?></div>
                <div class="count"><?php echo $data['count']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Issues Section -->
    <div class="issues-section">
        <div class="issues-header">
            <i class="fas fa-exclamation-triangle"></i>
            Problèmes détectés
        </div>
        <div class="issues-body">
            <?php if (empty($issues)): ?>
                <div class="no-issues">
                    <i class="fas fa-check-circle"></i>
                    <h4>Aucun problème détecté</h4>
                    <p>La base de données est en bon état</p>
                </div>
            <?php else: ?>
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-item">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($issue); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Warning Box -->
    <div class="warning-box">
        <h4><i class="fas fa-exclamation-triangle"></i> Attention</h4>
        <p>Les actions ci-dessous modifient directement la base de données. Assurez-vous de créer une sauvegarde avant d'effectuer des opérations de nettoyage ou d'archivage.</p>
    </div>
    
    <!-- Actions Section -->
    <div class="actions-section">
        <div class="actions-header">
            <i class="fas fa-tools"></i>
            Actions de maintenance
        </div>
        <div class="actions-body">
            <div class="action-cards">
                <!-- Cleanup Orphans -->
                <div class="action-card">
                    <h4><i class="fas fa-broom"></i> Nettoyer les orphelins</h4>
                    <p>Supprime les enregistrements orphelins : membres de clubs inexistants, inscriptions à des événements supprimés, etc.</p>
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir nettoyer les enregistrements orphelins ?');">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                        <button type="submit" name="cleanup_orphans" class="btn btn-warning">
                            <i class="fas fa-broom"></i> Nettoyer
                        </button>
                    </form>
                </div>
                
                <!-- Archive Old Events -->
                <div class="action-card">
                    <h4><i class="fas fa-archive"></i> Archiver anciens événements</h4>
                    <p>Archive les événements de plus d'un an. Les événements archivés ne seront plus affichés dans les listes publiques.</p>
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver les anciens événements ?');">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                        <button type="submit" name="archive_old_events" class="btn btn-primary">
                            <i class="fas fa-archive"></i> Archiver
                        </button>
                    </form>
                </div>
                
                <!-- Backup Reminder -->
                <div class="action-card">
                    <h4><i class="fas fa-download"></i> Exporter les données</h4>
                    <p>Téléchargez une exportation CSV des données pour sauvegarde ou analyse externe.</p>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="?page=export-data&type=users" class="btn btn-primary">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a href="?page=export-data&type=clubs" class="btn btn-primary">
                            <i class="fas fa-user-friends"></i> Clubs
                        </a>
                        <a href="?page=export-data&type=events" class="btn btn-primary">
                            <i class="fas fa-calendar"></i> Events
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
