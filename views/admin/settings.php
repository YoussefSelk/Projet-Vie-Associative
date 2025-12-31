<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        .settings-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .settings-header h1 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #1a1a2e;
            font-size: 1.75rem;
        }
        
        .settings-header h1 i {
            color: #dc3545;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #6c757d;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }
        
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .settings-card-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .settings-card-header.danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        .settings-card-header.success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        
        .settings-card-header.info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .settings-card-header.warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #1a1a2e;
        }
        
        .settings-card-body {
            padding: 1.5rem;
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .setting-label strong {
            color: #1a1a2e;
        }
        
        .setting-label span {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 30px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background-color: #28a745;
        }
        
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .db-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        
        .db-stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .db-stat-item .count {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0066cc;
        }
        
        .db-stat-item .label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: capitalize;
        }
        
        .system-info-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .system-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .system-info-item .key {
            font-weight: 600;
            color: #495057;
        }
        
        .system-info-item .value {
            color: #0066cc;
            font-family: monospace;
        }
        
        .logs-container {
            max-height: 400px;
            overflow-y: auto;
            background: #1a1a2e;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .log-entry {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.8rem;
            color: #00ff00;
            padding: 0.25rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            word-break: break-all;
        }
        
        .log-entry.error {
            color: #ff6b6b;
        }
        
        .log-entry.warning {
            color: #ffc107;
        }
        
        .no-logs {
            color: #6c757d;
            text-align: center;
            padding: 2rem;
        }
        
        .export-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: #0066cc;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .export-btn:hover {
            background: #0052a3;
            transform: translateY(-2px);
        }
        
        .export-btn.danger {
            background: #dc3545;
        }
        
        .export-btn.danger:hover {
            background: #c82333;
        }
        
        .export-btn.success {
            background: #28a745;
        }
        
        .export-btn.success:hover {
            background: #1e7e34;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .super-admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
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
                <?= htmlspecialchars($success_msg) ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_msg) ?>
            </div>
            <?php endif; ?>
            
            <div class="settings-grid">
                <!-- Quick Stats Overview -->
                <div class="settings-card" style="grid-column: 1 / -1;">
                    <div class="settings-card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-tachometer-alt"></i>
                        <h3>Aperçu Rapide</h3>
                    </div>
                    <div class="settings-card-body">
                        <div class="db-stats-grid" style="grid-template-columns: repeat(6, 1fr);">
                            <div class="db-stat-item" style="background: #d4edda;">
                                <div class="count" style="color: #155724;"><?= $advanced_stats['pending_clubs'] ?? 0 ?></div>
                                <div class="label">Clubs en attente</div>
                            </div>
                            <div class="db-stat-item" style="background: #cce5ff;">
                                <div class="count" style="color: #004085;"><?= $advanced_stats['pending_events'] ?? 0 ?></div>
                                <div class="label">Événements en attente</div>
                            </div>
                            <div class="db-stat-item" style="background: #f8d7da;">
                                <div class="count" style="color: #721c24;"><?= $advanced_stats['rejected_clubs'] ?? 0 ?></div>
                                <div class="label">Clubs refusés</div>
                            </div>
                            <div class="db-stat-item" style="background: #f8d7da;">
                                <div class="count" style="color: #721c24;"><?= $advanced_stats['rejected_events'] ?? 0 ?></div>
                                <div class="label">Événements refusés</div>
                            </div>
                            <div class="db-stat-item" style="background: #fff3cd;">
                                <div class="count" style="color: #856404;"><?= $advanced_stats['events_no_report'] ?? 0 ?></div>
                                <div class="label">Sans rapport</div>
                            </div>
                            <div class="db-stat-item" style="background: #e2e3e5;">
                                <div class="count" style="color: #383d41;"><?= $advanced_stats['old_events'] ?? 0 ?></div>
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
                    <div class="settings-card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-bolt"></i>
                        <h3>Actions en Masse</h3>
                    </div>
                    <div class="settings-card-body">
                        <p style="margin-bottom: 1rem; color: #6c757d; font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                            Ces actions sont irréversibles. Utilisez-les avec précaution.
                        </p>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                            
                            <div class="export-buttons" style="flex-direction: column;">
                                <button type="submit" name="bulk_validate_clubs" class="export-btn success" style="width: 100%; justify-content: center;"
                                        onclick="return confirm('Valider TOUS les clubs en attente ?');">
                                    <i class="fas fa-check-double"></i> Valider tous les clubs en attente (<?= $advanced_stats['pending_clubs'] ?? 0 ?>)
                                </button>
                                <button type="submit" name="bulk_validate_events" class="export-btn" style="width: 100%; justify-content: center;"
                                        onclick="return confirm('Valider TOUS les événements en attente ?');">
                                    <i class="fas fa-check-double"></i> Valider tous les événements en attente (<?= $advanced_stats['pending_events'] ?? 0 ?>)
                                </button>
                                <button type="submit" name="clean_old_events" class="export-btn" style="width: 100%; justify-content: center; background: #6c757d;"
                                        onclick="return confirm('Analyser les anciens événements ?');">
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
                    <div class="settings-card-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
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
                            0 => '#6c757d',
                            1 => '#17a2b8',
                            2 => '#ffc107',
                            3 => '#28a745',
                            4 => '#6f42c1',
                            5 => '#dc3545'
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
                    <div class="settings-card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
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
                            <a href="?page=users-list" class="export-btn" style="background: #6c757d;">
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
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                <button type="submit" name="clear_logs" class="export-btn danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir effacer tous les logs ?');">
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

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
