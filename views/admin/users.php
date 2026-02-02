<?php
/**
 * Gestion des utilisateurs - Administration
 * 
 * Liste complete des utilisateurs avec :
 * - Filtres par role et campus
 * - Recherche par nom/email
 * - Modification des permissions
 * - Suppression d'utilisateurs
 * - Statistiques par role
 * 
 * Variables attendues :
 * - $users : Liste des utilisateurs
 * - $stats : Statistiques par role
 * - $campuses : Liste des campus disponibles
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
$pageCss = ['shared', 'buttons', 'tables', 'search', 'admin', 'profiles'];
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
