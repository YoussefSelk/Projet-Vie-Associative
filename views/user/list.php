<?php
/**
 * Liste des utilisateurs - Interface BDE/Tuteur
 * 
 * Affiche la liste des utilisateurs filtrables :
 * - Filtrage par campus, role, recherche
 * - Modification des permissions (selon niveau)
 * - Actions sur les utilisateurs
 * 
 * Permissions :
 * - BDE : Visualisation uniquement
 * - Tuteur : Modification limitee
 * - Admin : Controle complet
 * 
 * Variables attendues :
 * - $users : Liste des utilisateurs
 * - $current_filter : Filtres actifs
 * 
 * @package Views/User
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        .permission-select {
            padding: 0.35rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .permission-select:hover {
            border-color: #0066cc;
        }
        .permission-select:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }
        .action-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .inline-form {
            display: inline-block;
        }
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #6c757d;
            background: #f8f9fa;
        }
        .btn-icon:hover {
            background: #e9ecef;
        }
        .btn-icon.danger:hover {
            background: #dc3545;
            color: white;
        }
        .badge-purple {
            background: #6f42c1;
            color: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="page-container">
            <div class="page-header">
                <div class="header-left">
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-users-cog"></i> Gestion des utilisateurs</h1>
                <p class="subtitle"><?= count($users) ?> utilisateurs inscrits</p>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="userSearch" class="search-input" 
                           placeholder="Rechercher un utilisateur (nom, email, rôle)..." 
                           autocomplete="off">
                    <button type="button" class="search-clear" aria-label="Effacer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-filters">
                    <span class="filter-chip" data-search-filter="etudiant">
                        <i class="fas fa-user-graduate"></i> Étudiants
                    </span>
                    <span class="filter-chip" data-search-filter="bde">
                        <i class="fas fa-users"></i> BDE
                    </span>
                    <span class="filter-chip" data-search-filter="tuteur">
                        <i class="fas fa-chalkboard-teacher"></i> Tuteurs
                    </span>
                    <span class="filter-chip" data-search-filter="admin">
                        <i class="fas fa-user-shield"></i> Admin
                    </span>
                </div>
                <div class="search-results-info">
                    <span class="search-results-count"><strong><?= count($users) ?></strong> utilisateur<?= count($users) !== 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-users"></i>
                            <p>Aucun utilisateur trouvé</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Promo</th>
                                        <th>Rôle</th>
                                        <?php if (($_SESSION['permission'] ?? 0) == 5): ?>
                                        <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $roleNames = [
                                        0 => ['name' => 'Non vérifié', 'class' => 'badge-warning', 'filter' => ''],
                                        1 => ['name' => 'Étudiant', 'class' => 'badge-info', 'filter' => 'etudiant'],
                                        2 => ['name' => 'Tuteur', 'class' => 'badge-purple', 'filter' => 'tuteur'],
                                        3 => ['name' => 'BDE', 'class' => 'badge-success', 'filter' => 'bde'],
                                        4 => ['name' => 'Personnel', 'class' => 'badge-primary', 'filter' => 'personnel'],
                                        5 => ['name' => 'Admin', 'class' => 'badge-danger', 'filter' => 'admin']
                                    ];
                                    foreach ($users as $u): 
                                        $role = $roleNames[$u['permission'] ?? 1] ?? $roleNames[1];
                                        $searchData = strtolower($u['nom'] . ' ' . $u['prenom'] . ' ' . $u['mail'] . ' ' . $role['name'] . ' ' . ($u['promo'] ?? ''));
                                    ?>
                                        <tr data-search="<?= htmlspecialchars($searchData) ?>" data-filter="<?= htmlspecialchars($role['filter']) ?>">
                                            <td><span class="text-muted">#<?= htmlspecialchars($u['id']) ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-envelope text-muted"></i>
                                                <?= htmlspecialchars($u['mail']) ?>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($u['promo'] ?? '-') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $role['class'] ?>"><?= $role['name'] ?></span>
                                            </td>
                                            <?php if (($_SESSION['permission'] ?? 0) == 5): ?>
                                            <td>
                                                <?php if ($u['id'] != $_SESSION['id']): ?>
                                                <div class="action-group">
                                                    <form method="POST" action="?page=update-permission" class="inline-form">
                                                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <select name="permission" class="permission-select" onchange="this.form.submit()">
                                                            <option value="0" <?= $u['permission'] == 0 ? 'selected' : '' ?>>Non vérifié</option>
                                                            <option value="1" <?= $u['permission'] == 1 ? 'selected' : '' ?>>Étudiant</option>
                                                            <option value="2" <?= $u['permission'] == 2 ? 'selected' : '' ?>>Tuteur</option>
                                                            <option value="3" <?= $u['permission'] == 3 ? 'selected' : '' ?>>BDE</option>
                                                            <option value="4" <?= $u['permission'] == 4 ? 'selected' : '' ?>>Personnel</option>
                                                            <option value="5" <?= $u['permission'] == 5 ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </form>
                                                    <a href="?page=delete-user&id=<?= $u['id'] ?>" 
                                                       class="btn-icon danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');"
                                                       title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                                <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-crown"></i> Vous</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
