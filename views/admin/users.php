<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
/* Admin Users Page Styles */
.admin-users-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.admin-users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-users-header h1 {
    font-size: 2rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-users-header h1 i {
    color: #3498db;
}

/* Stats Row */
.users-stats-row {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.stat-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 500;
}

.stat-badge.admin { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.stat-badge.bde { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
.stat-badge.tutor { background: linear-gradient(135deg, #f39c12, #d68910); }
.stat-badge.user { background: linear-gradient(135deg, #27ae60, #229954); }

/* Filter Section */
.filter-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #e9ecef;
}

.filter-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
    font-size: 0.85rem;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
}

.filter-group input:focus,
.filter-group select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-buttons .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.filter-buttons .btn-primary {
    background: #3498db;
    color: white;
}

.filter-buttons .btn-secondary {
    background: #6c757d;
    color: white;
}

/* Users Table */
.users-table-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    overflow: hidden;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.users-table th a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.users-table th a:hover {
    color: #3498db;
}

.users-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.users-table tr:last-child td {
    border-bottom: none;
}

/* Permission Badge */
.permission-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.permission-badge.p-0 { background: #dfe6e9; color: #636e72; }
.permission-badge.p-1 { background: #dff9fb; color: #00b894; }
.permission-badge.p-2 { background: #ffeaa7; color: #d68910; }
.permission-badge.p-3 { background: #e8daef; color: #8e44ad; }
.permission-badge.p-4 { background: #d6eaf8; color: #2980b9; }
.permission-badge.p-5 { background: #fadbd8; color: #c0392b; }

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db, #2980b9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
}

.user-name {
    font-weight: 600;
    color: #2c3e50;
}

.user-email {
    font-size: 0.8rem;
    color: #7f8c8d;
}

/* Stats Mini */
.stats-mini {
    display: flex;
    gap: 8px;
}

.stats-mini span {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 0.8rem;
    color: #666;
}

.stats-mini span i {
    font-size: 0.7rem;
}

/* Actions */
.actions-cell {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s;
}

.action-btn.view { background: #3498db; color: white; }
.action-btn.edit { background: #f39c12; color: white; }
.action-btn.delete { background: #e74c3c; color: white; }

.action-btn:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

/* Permission Form */
.permission-form {
    display: flex;
    gap: 5px;
    align-items: center;
}

.permission-form select {
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.8rem;
}

.permission-form button {
    padding: 4px 10px;
    background: #27ae60;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.75rem;
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

/* Results Count */
.results-info {
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
    color: #666;
}

.results-info strong {
    color: #2c3e50;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-users-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .users-table {
        font-size: 0.85rem;
    }
    
    .users-table th,
    .users-table td {
        padding: 10px;
    }
    
    .action-btn {
        padding: 5px 8px;
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
        <div class="admin-users-container">
    <!-- Header -->
    <div class="admin-users-header">
        <h1><i class="fas fa-users-cog"></i> Gestion des Utilisateurs</h1>
        <a href="?page=admin" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
    </div>
    
    <!-- Stats Row -->
    <div class="users-stats-row">
        <?php
        $permission_labels = [
            0 => ['Invités', 'user'],
            1 => ['Utilisateurs', 'user'],
            2 => ['Tuteurs', 'tutor'],
            3 => ['BDE', 'bde'],
            4 => ['Personnel', 'bde'],
            5 => ['Super Admins', 'admin']
        ];
        foreach ($stats['by_permission'] as $stat):
            $p = $stat['permission'];
            $label = $permission_labels[$p][0] ?? 'Inconnu';
            $class = $permission_labels[$p][1] ?? 'user';
        ?>
            <span class="stat-badge <?php echo $class; ?>">
                <i class="fas fa-user"></i>
                <?php echo $stat['count']; ?> <?php echo $label; ?>
            </span>
        <?php endforeach; ?>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <form class="filter-form" method="GET">
            <input type="hidden" name="page" value="admin-users">
            
            <div class="filter-group">
                <label>Rechercher</label>
                <input type="text" name="search" placeholder="Nom, prénom ou email..." 
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
            
            <div class="filter-group">
                <label>Permission</label>
                <select name="permission">
                    <option value="">Toutes les permissions</option>
                    <option value="0" <?php if ($filters['permission'] === '0') echo 'selected'; ?>>0 - Invité</option>
                    <option value="1" <?php if ($filters['permission'] === '1') echo 'selected'; ?>>1 - Utilisateur</option>
                    <option value="2" <?php if ($filters['permission'] === '2') echo 'selected'; ?>>2 - Tuteur</option>
                    <option value="3" <?php if ($filters['permission'] === '3') echo 'selected'; ?>>3 - BDE</option>
                    <option value="4" <?php if ($filters['permission'] === '4') echo 'selected'; ?>>4 - Personnel</option>
                    <option value="5" <?php if ($filters['permission'] === '5') echo 'selected'; ?>>5 - Super Admin</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Promo</label>
                <select name="promo">
                    <option value="">Toutes les promos</option>
                    <?php foreach ($promos as $promo): ?>
                        <option value="<?php echo htmlspecialchars($promo); ?>" 
                                <?php if ($filters['promo'] === $promo) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($promo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
                <a href="?page=admin-users" class="btn btn-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="users-table-container">
        <div class="results-info">
            <strong><?php echo count($users); ?></strong> utilisateur(s) trouvé(s)
        </div>
        
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Aucun utilisateur trouvé</h3>
                <p>Modifiez vos filtres de recherche</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>
                            <a href="?page=admin-users&sort=id&order=<?php echo ($filters['sort'] === 'id' && $filters['order'] === 'DESC') ? 'ASC' : 'DESC'; ?>&search=<?php echo urlencode($filters['search']); ?>&permission=<?php echo $filters['permission']; ?>&promo=<?php echo urlencode($filters['promo']); ?>">
                                ID <?php if ($filters['sort'] === 'id'): ?><i class="fas fa-sort-<?php echo $filters['order'] === 'ASC' ? 'up' : 'down'; ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?page=admin-users&sort=nom&order=<?php echo ($filters['sort'] === 'nom' && $filters['order'] === 'DESC') ? 'ASC' : 'DESC'; ?>&search=<?php echo urlencode($filters['search']); ?>&permission=<?php echo $filters['permission']; ?>&promo=<?php echo urlencode($filters['promo']); ?>">
                                Utilisateur <?php if ($filters['sort'] === 'nom'): ?><i class="fas fa-sort-<?php echo $filters['order'] === 'ASC' ? 'up' : 'down'; ?>"></i><?php endif; ?>
                            </a>
                        </th>
                        <th>Promo</th>
                        <th>Permission</th>
                        <th>Stats</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($u['prenom'] ?? 'U', 0, 1) . substr($u['nom'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="user-name"><?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($u['mail']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['promo'] ?? '-'); ?></td>
                            <td>
                                <?php
                                    $perm_names = [0 => 'Invité', 1 => 'Utilisateur', 2 => 'Tuteur', 3 => 'BDE', 4 => 'Personnel', 5 => 'Admin'];
                                ?>
                                <span class="permission-badge p-<?php echo $u['permission']; ?>">
                                    <?php echo $perm_names[$u['permission']] ?? 'Inconnu'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="stats-mini">
                                    <span title="Clubs"><i class="fas fa-users"></i> <?php echo $u['clubs_count'] ?? 0; ?></span>
                                    <span title="Inscriptions"><i class="fas fa-calendar-check"></i> <?php echo $u['subscriptions_count'] ?? 0; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="?page=admin-user-view&id=<?php echo $u['id']; ?>" class="action-btn view" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($u['id'] != $_SESSION['id']): ?>
                                        <form class="permission-form" method="POST" action="?page=update-permission" style="display: inline-flex;">
                                            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="permission">
                                                <?php for ($p = 0; $p <= 5; $p++): ?>
                                                    <option value="<?php echo $p; ?>" <?php if ($u['permission'] == $p) echo 'selected'; ?>>
                                                        <?php echo $p; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                            <button type="submit"><i class="fas fa-check"></i></button>
                                        </form>
                                        
                                        <a href="?page=delete-user&id=<?php echo $u['id']; ?>" 
                                           class="action-btn delete" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');"
                                           title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.8rem;">(Vous)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
