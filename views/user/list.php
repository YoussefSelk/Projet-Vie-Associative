<?php
/**
 * Liste des utilisateurs - Interface BDE/Tuteur
 * 
 * Affiche la liste des utilisateurs filtrables :
 * - Filtrage par campus, rôle, recherche
 * - Modification des permissions (selon niveau)
 * - Actions sur les utilisateurs
 *
 * Permissions :
 * - BDE : Visualisation uniquement
 * - Tuteur : Modification limitée
 * - Admin : Contrôle complet
 * 
 * Variables attendues :
 * - $users : Liste des utilisateurs
 * - $current_filter : Filtres actifs
 * 
 * @package Views/User
 */
$pageCss = ['shared', 'buttons', 'tables', 'search', 'pagination', 'profiles'];
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
                                                    <form method="POST" action="?page=update-permission" class="inline-form form-permission-change">
                                                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <select name="permission" class="permission-select" data-original="<?= $u['permission'] ?>">
                                                            <option value="0" <?= $u['permission'] == 0 ? 'selected' : '' ?>>Non vérifié</option>
                                                            <option value="1" <?= $u['permission'] == 1 ? 'selected' : '' ?>>Étudiant</option>
                                                            <option value="2" <?= $u['permission'] == 2 ? 'selected' : '' ?>>Tuteur</option>
                                                            <option value="3" <?= $u['permission'] == 3 ? 'selected' : '' ?>>BDE</option>
                                                            <option value="4" <?= $u['permission'] == 4 ? 'selected' : '' ?>>Personnel</option>
                                                            <option value="5" <?= $u['permission'] == 5 ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </form>
                                                    <form method="POST" action="?page=delete-user" class="form-delete-user">
                                                        <?= Security::csrfField() ?>
                                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn-icon danger btn-delete-user"
                                                                data-user-name="<?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?>"
                                                                title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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

                    <!-- Pagination -->
                    <div id="userPagination" class="pagination-wrapper"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure search component exists
        if (document.querySelector('#userSearch') && !window.userSearch) {
            window.userSearch = new SearchComponent({
                input: '#userSearch',
                items: '.data-table tbody',
                fields: ['data-search'],
                noResultsMessage: 'Aucun utilisateur trouvé'
            });
        }

        // Initialize pagination for user table
        if (document.querySelector('.data-table tbody')) {
            window.userPagination = new PaginationComponent({
                itemsSelector: '.data-table tbody',
                paginationSelector: '#userPagination',
                perPage: 15,
                perPageOptions: [10, 15, 25, 50],
                searchComponent: window.userSearch || null
            });
        }
    });

    // Delete user confirmation with SweetAlert2
    document.querySelectorAll('.btn-delete-user').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const userName = btn.dataset.userName;
            const form = btn.closest('form');
            
            SwalHelper.confirmDelete('l\'utilisateur "' + userName + '"')
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
        });
    });

    // Permission change confirmation with SweetAlert2
    document.querySelectorAll('.form-permission-change select').forEach(select => {
        select.addEventListener('change', (e) => {
            const form = select.closest('form');
            const original = select.dataset.original;
            const permNames = {0: 'Non vérifié', 1: 'Étudiant', 2: 'Tuteur', 3: 'BDE', 4: 'Personnel', 5: 'Admin'};
            const newPerm = permNames[select.value] || select.value;
            
            SwalHelper.confirm(
                'Modifier la permission ?',
                'Changer la permission vers "' + newPerm + '" ?',
                'Oui, modifier',
                'Annuler'
            ).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    select.value = original;
                }
            });
        });
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
