<?php
/**
 * Journal d'audit securite - Administration
 * 
 * Affiche l'historique des actions sensibles :
 * - Connexions/deconnexions
 * - Modifications de permissions
 * - Suppressions de donnees
 * - Tentatives d'acces non autorise
 * 
 * Variables attendues :
 * - $logs : Liste des entrees du journal
 * - $stats : Statistiques des evenements
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
