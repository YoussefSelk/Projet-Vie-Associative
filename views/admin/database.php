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
$pageCss = ['shared', 'buttons', 'tables', 'admin'];
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
            'abonnements' => 'fa-calendar-check',
            'mails' => 'fa-envelope',
            'config' => 'fa-cog',
            'ville' => 'fa-city'
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
