<?php
/**
 * Journal d'audit sécurité - Administration
 *
 * Affiche l'historique des actions sensibles :
 * - Connexions/déconnexions
 * - Modifications de permissions
 * - Suppressions de données
 * - Tentatives d'accès non autorisé
 *
 * Variables attendues :
 * - $logs : Liste des entrées du journal
 * - $stats : Statistiques des événements
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
$pageCss = ['shared', 'buttons', 'tables', 'search', 'pagination', 'admin'];
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
        <!-- Search for security logs -->
        <div class="search-container" style="padding: 12px 16px 0;">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="securityLogSearch" class="search-input" 
                       placeholder="Rechercher dans les logs de sécurité..." 
                       autocomplete="off">
                <button type="button" class="search-clear" aria-label="Effacer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="log-body" id="securityLogBody">
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
                    ?>" data-search="<?php echo htmlspecialchars(strtolower(trim($log))); ?>">
                        <?php echo htmlspecialchars(trim($log)); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Pagination for security logs -->
        <div id="securityLogPagination" class="pagination-wrapper" style="padding: 0 16px;"></div>
    </div>
    
    <!-- Error Logs -->
    <div class="log-section">
        <div class="log-header" style="background: #991b1b;">
            <span class="title"><i class="fas fa-exclamation-circle"></i> Logs d'erreurs</span>
            <span class="count"><?php echo count($error_logs); ?> entrées</span>
        </div>
        <!-- Search for error logs -->
        <div class="search-container" style="padding: 12px 16px 0;">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="errorLogSearch" class="search-input" 
                       placeholder="Rechercher dans les logs d'erreurs..." 
                       autocomplete="off">
                <button type="button" class="search-clear" aria-label="Effacer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="log-body" id="errorLogBody">
            <?php if (empty($error_logs)): ?>
                <div class="empty-logs">
                    <i class="fas fa-smile"></i>
                    <h4>Aucune erreur récente</h4>
                    <p>Le système fonctionne normalement</p>
                </div>
            <?php else: ?>
                <?php foreach ($error_logs as $log): ?>
                    <div class="log-entry error" data-search="<?php echo htmlspecialchars(strtolower(trim($log))); ?>">
                        <?php echo htmlspecialchars(trim($log)); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Pagination for error logs -->
        <div id="errorLogPagination" class="pagination-wrapper" style="padding: 0 16px;"></div>
    </div>
</div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search + Pagination for security logs
        let securitySearch = null;
        if (document.querySelector('#securityLogSearch') && document.querySelector('#securityLogBody')) {
            securitySearch = new SearchComponent({
                input: '#securityLogSearch',
                items: '#securityLogBody',
                fields: ['data-search'],
                noResultsMessage: 'Aucun log trouvé'
            });
        }
        if (document.querySelector('#securityLogBody')) {
            window.securityPagination = new PaginationComponent({
                itemsSelector: '#securityLogBody',
                paginationSelector: '#securityLogPagination',
                perPage: 20,
                perPageOptions: [10, 20, 50, 100],
                searchComponent: securitySearch
            });
        }

        // Search + Pagination for error logs
        let errorSearch = null;
        if (document.querySelector('#errorLogSearch') && document.querySelector('#errorLogBody')) {
            errorSearch = new SearchComponent({
                input: '#errorLogSearch',
                items: '#errorLogBody',
                fields: ['data-search'],
                noResultsMessage: 'Aucun log trouvé'
            });
        }
        if (document.querySelector('#errorLogBody')) {
            window.errorPagination = new PaginationComponent({
                itemsSelector: '#errorLogBody',
                paginationSelector: '#errorLogPagination',
                perPage: 20,
                perPageOptions: [10, 20, 50, 100],
                searchComponent: errorSearch
            });
        }
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
