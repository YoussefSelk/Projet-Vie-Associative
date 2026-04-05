<?php
/**
 * Page des parametres administrateur
 * 
 * Interface de configuration globale de l'application :
 * - Parametres de securite (CSRF, sessions)
 * - Configuration email (SMTP)
 * - Parametres de l'application
 * - Outils de maintenance
 * 
 * Chaque section est une carte avec formulaire independant.
 * Les modifications sont appliquees immediatement.
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
$pageCss = ['shared', 'buttons', 'forms', 'admin'];
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
        <div class="settings-container">
            <div class="settings-header">
                <h1>
                    <i class="fas fa-shield-alt"></i>
                    Paramètres Super Administrateur
                </h1>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="super-admin-badge">
                        <i class="fas fa-crown"></i> Super Admin
                    </span>
                    <a href="?page=admin" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
            
            <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars(strip_tags((string)$success_msg)) ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars(strip_tags((string)$error_msg)) ?>
            </div>
            <?php endif; ?>
            
            <div class="settings-grid">
                <!-- Quick Stats Overview -->
                <div class="settings-card" style="grid-column: 1 / -1;">
                    <div class="settings-card-header quick">
                        <i class="fas fa-tachometer-alt"></i>
                        <h3>Aperçu Rapide</h3>
                    </div>
                    <div class="settings-card-body">
                        <div class="db-stats-grid quick-overview-grid">
                            <div class="db-stat-item quick-stat pending-clubs">
                                <div class="count"><?= $advanced_stats['pending_clubs'] ?? 0 ?></div>
                                <div class="label">Clubs en attente</div>
                            </div>
                            <div class="db-stat-item quick-stat pending-events">
                                <div class="count"><?= $advanced_stats['pending_events'] ?? 0 ?></div>
                                <div class="label">Événements en attente</div>
                            </div>
                            <div class="db-stat-item quick-stat rejected-clubs">
                                <div class="count"><?= $advanced_stats['rejected_clubs'] ?? 0 ?></div>
                                <div class="label">Clubs refusés</div>
                            </div>
                            <div class="db-stat-item quick-stat rejected-events">
                                <div class="count"><?= $advanced_stats['rejected_events'] ?? 0 ?></div>
                                <div class="label">Événements refusés</div>
                            </div>
                            <div class="db-stat-item quick-stat no-report">
                                <div class="count"><?= $advanced_stats['events_no_report'] ?? 0 ?></div>
                                <div class="label">Sans rapport</div>
                            </div>
                            <div class="db-stat-item quick-stat old-events">
                                <div class="count"><?= $advanced_stats['old_events'] ?? 0 ?></div>
                                <div class="label">Anciens (+1 an)</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Settings -->
                <div class="settings-card">
                    <div class="settings-card-header success">
                        <i class="fas fa-cogs"></i>
                        <h3>Paramètres Système</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                            
                            <div class="setting-item">
                                <div class="setting-label">
                                    <strong>Création de clubs</strong>
                                    <span>Autoriser les utilisateurs à créer de nouveaux clubs</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="creation_club_active" 
                                           <?= ($config['creation_club_active'] ?? 1) ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="setting-item">
                                <div class="setting-label">
                                    <strong>Création d'événements</strong>
                                    <span>Autoriser la création de nouveaux événements</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="creation_event_active" 
                                           <?= ($config['creation_event_active'] ?? 1) ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="setting-item">
                                <div class="setting-label">
                                    <strong>Mode maintenance</strong>
                                    <span>Bloquer l'accès au site pour les utilisateurs</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="maintenance_mode" 
                                           <?= ($config['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div style="margin-top: 1.5rem;">
                                <button type="submit" name="update_settings" class="export-btn success">
                                    <i class="fas fa-save"></i> Enregistrer les paramètres
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="settings-card">
                    <div class="settings-card-header accent">
                        <i class="fas fa-bolt"></i>
                        <h3>Actions en Masse</h3>
                    </div>
                    <div class="settings-card-body">
                        <p class="settings-warning-text" style="margin-bottom: 1rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Ces actions sont irréversibles. Utilisez-les avec précaution.
                        </p>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                            
                            <div class="export-buttons" style="flex-direction: column;">
                                <button type="submit" name="bulk_validate_clubs" class="export-btn success btn-bulk-action" style="width: 100%; justify-content: center;"
                                        data-action="validate-clubs" data-count="<?= $advanced_stats['pending_clubs'] ?? 0 ?>">
                                    <i class="fas fa-check-double"></i> Valider tous les clubs en attente (<?= $advanced_stats['pending_clubs'] ?? 0 ?>)
                                </button>
                                <button type="submit" name="bulk_validate_events" class="export-btn btn-bulk-action" style="width: 100%; justify-content: center;"
                                        data-action="validate-events" data-count="<?= $advanced_stats['pending_events'] ?? 0 ?>">
                                    <i class="fas fa-check-double"></i> Valider tous les événements en attente (<?= $advanced_stats['pending_events'] ?? 0 ?>)
                                </button>
                                <button type="submit" name="clean_old_events" class="export-btn neutral btn-bulk-action" style="width: 100%; justify-content: center;"
                                        data-action="clean-events">
                                    <i class="fas fa-broom"></i> Analyser les anciens événements (+1 an)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Database Stats -->
                <div class="settings-card">
                    <div class="settings-card-header info">
                        <i class="fas fa-database"></i>
                        <h3>Statistiques Base de Données</h3>
                    </div>
                    <div class="settings-card-body">
                        <div class="db-stats-grid">
                            <?php foreach ($db_stats as $table => $count): ?>
                            <div class="db-stat-item">
                                <div class="count"><?= $count ?></div>
                                <div class="label"><?= str_replace(['fiche_', 'subscribe_', 'membres_'], ['', 'inscr. ', 'membres '], $table) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Users by Permission -->
                <div class="settings-card">
                    <div class="settings-card-header roles">
                        <i class="fas fa-user-shield"></i>
                        <h3>Utilisateurs par Rôle</h3>
                    </div>
                    <div class="settings-card-body">
                        <?php 
                        $permissionNames = [
                            0 => 'Invité',
                            1 => 'Utilisateur',
                            2 => 'Tuteur',
                            3 => 'BDE',
                            4 => 'Personnel',
                            5 => 'Super Admin'
                        ];
                        $permissionColors = [
                            0 => '#475569',
                            1 => '#0284c7',
                            2 => '#d97706',
                            3 => '#059669',
                            4 => '#5b21b6',
                            5 => '#991b1b'
                        ];
                        ?>
                        <div class="system-info-list">
                            <?php foreach ($advanced_stats['users_by_permission'] ?? [] as $perm): ?>
                            <div class="system-info-item">
                                <span class="key" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="width: 12px; height: 12px; border-radius: 50%; background: <?= $permissionColors[$perm['permission']] ?? '#ccc' ?>;"></span>
                                    <?= $permissionNames[$perm['permission']] ?? 'Niveau ' . $perm['permission'] ?>
                                </span>
                                <span class="value" style="font-weight: 700;"><?= $perm['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-server"></i>
                        <h3>Informations Système</h3>
                    </div>
                    <div class="settings-card-body">
                        <div class="system-info-list">
                            <div class="system-info-item">
                                <span class="key">PHP Version</span>
                                <span class="value"><?= $system_info['php_version'] ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Serveur</span>
                                <span class="value" style="font-size: 0.75rem;"><?= htmlspecialchars(substr($system_info['server_software'], 0, 30)) ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Mémoire limite</span>
                                <span class="value"><?= $system_info['memory_limit'] ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Upload max</span>
                                <span class="value"><?= $system_info['max_upload'] ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">POST max</span>
                                <span class="value"><?= $system_info['post_max_size'] ?? 'N/A' ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Temps max exec</span>
                                <span class="value"><?= $system_info['max_execution_time'] ?? 'N/A' ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Fuseau horaire</span>
                                <span class="value"><?= $system_info['timezone'] ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="key">Taille uploads</span>
                                <span class="value"><?= $system_info['uploads_size'] ?? 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Export Data -->
                <div class="settings-card">
                    <div class="settings-card-header warning">
                        <i class="fas fa-download"></i>
                        <h3>Exporter les Données</h3>
                    </div>
                    <div class="settings-card-body">
                        <p style="margin-bottom: 1rem; color: #6c757d;">
                            Exportez les données du système au format CSV (compatible Excel).
                        </p>
                        <div class="export-buttons">
                            <a href="?page=export-data&type=users" class="export-btn">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a>
                            <a href="?page=export-data&type=clubs" class="export-btn">
                                <i class="fas fa-building"></i> Clubs
                            </a>
                            <a href="?page=export-data&type=events" class="export-btn">
                                <i class="fas fa-calendar"></i> Événements
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="settings-card" style="grid-column: 1 / -1;">
                    <div class="settings-card-header recent">
                        <i class="fas fa-user-plus"></i>
                        <h3>Derniers Utilisateurs Inscrits</h3>
                    </div>
                    <div class="settings-card-body">
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                <thead>
                                    <tr style="background: #f8f9fa; text-align: left;">
                                        <th style="padding: 0.75rem; border-bottom: 2px solid #dee2e6;">ID</th>
                                        <th style="padding: 0.75rem; border-bottom: 2px solid #dee2e6;">Nom</th>
                                        <th style="padding: 0.75rem; border-bottom: 2px solid #dee2e6;">Email</th>
                                        <th style="padding: 0.75rem; border-bottom: 2px solid #dee2e6;">Permission</th>
                                        <th style="padding: 0.75rem; border-bottom: 2px solid #dee2e6;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($advanced_stats['recent_users'] ?? [] as $user): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 0.75rem;"><?= $user['id'] ?></td>
                                        <td style="padding: 0.75rem;"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                                        <td style="padding: 0.75rem;"><?= htmlspecialchars($user['mail']) ?></td>
                                        <td style="padding: 0.75rem;">
                                            <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: <?= $permissionColors[$user['permission']] ?? '#ccc' ?>; color: white;">
                                                <?= $permissionNames[$user['permission']] ?? $user['permission'] ?>
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <a href="?page=profile&id=<?= $user['id'] ?>" class="export-btn" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 1rem; text-align: center;">
                            <a href="?page=users-list" class="export-btn neutral">
                                <i class="fas fa-list"></i> Voir tous les utilisateurs
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Error Logs -->
                <div class="settings-card" style="grid-column: 1 / -1;">
                    <div class="settings-card-header danger">
                        <i class="fas fa-bug"></i>
                        <h3>Journaux d'Erreurs</h3>
                    </div>
                    <div class="settings-card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span style="color: #6c757d;">
                                <i class="fas fa-info-circle"></i> Dernières 50 entrées
                            </span>
                            <form method="POST" class="form-clear-logs">
                                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                <input type="hidden" name="clear_logs" value="1">
                                <button type="submit" class="export-btn danger">
                                    <i class="fas fa-trash"></i> Effacer les logs
                                </button>
                            </form>
                        </div>
                        <div class="logs-container">
                            <?php if (!empty($error_logs)): ?>
                                <?php foreach ($error_logs as $log): ?>
                                    <?php 
                                    $logClass = 'log-entry';
                                    if (stripos($log, 'error') !== false || stripos($log, 'exception') !== false) {
                                        $logClass .= ' error';
                                    } elseif (stripos($log, 'warning') !== false || stripos($log, 'deprecated') !== false) {
                                        $logClass .= ' warning';
                                    }
                                    ?>
                                    <div class="<?= $logClass ?>"><?= htmlspecialchars(trim($log)) ?></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-logs">
                                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                    <p>Aucune erreur enregistrée</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Bulk actions confirmation
    document.querySelectorAll('.btn-bulk-action').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = btn.closest('form');
            const action = btn.dataset.action;
            const btnName = btn.name;
            let title, text;
            
            if (action === 'validate-clubs') {
                const count = btn.dataset.count;
                title = 'Valider tous les clubs ?';
                text = `Voulez-vous valider les ${count} club(s) en attente ?`;
            } else if (action === 'validate-events') {
                const count = btn.dataset.count;
                title = 'Valider tous les événements ?';
                text = `Voulez-vous valider les ${count} événement(s) en attente ?`;
            } else if (action === 'clean-events') {
                title = 'Analyser les anciens événements ?';
                text = 'Cette action va analyser et archiver les événements de plus d\'un an';
            }
            
            SwalHelper.confirm(title, text, 'Oui, continuer', 'Annuler')
                .then((result) => {
                    if (result.isConfirmed) {
                        // Add hidden input so the server knows which button was clicked
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = btnName;
                        hidden.value = '1';
                        form.appendChild(hidden);
                        form.submit();
                    }
                });
        });
    });
    
    // Clear logs confirmation
    const clearLogsForm = document.querySelector('.form-clear-logs');
    if (clearLogsForm) {
        clearLogsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            SwalHelper.confirmDelete('tous les logs')
                .then((result) => {
                    if (result.isConfirmed) {
                        clearLogsForm.submit();
                    }
                });
        });
    }
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
