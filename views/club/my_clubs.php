<?php
/**
 * Mes Demandes de Clubs
 * 
 * Affiche la liste de tous les clubs où l'utilisateur connecté est Président ou Secrétaire :
 * - Statut de validation (En attente, Validé, Refusé)
 * - Motif de refus si applicable
 * - Actions : Modifier (si non validé), Supprimer (si refusé)
 * 
 * Variables attendues :
 * - $clubs : Liste des clubs de l'utilisateur (avec son rôle)
 * - $error_msg : Message d'erreur éventuel
 * - $success_msg : Message de succès éventuel
 * 
 * @package Views/Club
 */
$pageTitle = 'Mes clubs - EILCO';
$pageCss = ['shared', 'buttons', 'tables', 'search', 'pagination', 'clubs'];
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
                <h1><i class="fas fa-folder-open"></i> Mes Demandes de Clubs</h1>
                <p class="subtitle">Suivi de vos demandes de création de clubs</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> Club modifié avec succès. Il est en attente de validation.
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <?php if (!empty($clubs)): ?>
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="myClubSearch" class="search-input" 
                        placeholder="Rechercher un club..." 
                        autocomplete="off">
                    <button type="button" class="search-clear" aria-label="Effacer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-results-info">
                    <span class="search-results-count"><strong><?= count($clubs) ?></strong> club<?= count($clubs) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($clubs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucun club créé</h3>
                    <p>Vous n'avez pas encore créé de club. Commencez par créer votre première demande.</p>
                    <a href="?page=club-create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un nouveau club
                    </a>
                </div>
            <?php else: ?>
                <div class="clubs-list">
                    <?php foreach ($clubs as $club): 
                        $searchData = strtolower(($club['nom_club'] ?? '') . ' ' . ($club['type_club'] ?? '') . ' ' . ($club['campus'] ?? '') . ' ' . ($club['description'] ?? ''));
                    ?>
                        <div class="club-card" data-search="<?= htmlspecialchars($searchData) ?>">
                            <div class="club-header">
                                <div class="club-title">
                                    <h3><?= htmlspecialchars($club['nom_club']) ?></h3>
                                    <p class="club-type"><?= htmlspecialchars($club['type_club'] ?? 'Type non défini') ?></p>
                                </div>
                                <?php
                                    $status = '';
                                    $statusClass = '';
                                    
                                    // Détermine le statut basé sur validation_finale
                                    if ($club['validation_finale'] == 1) {
                                        // Club validé avec succès
                                        $status = 'Validé';
                                        $statusClass = 'status-approved';
                                    } elseif (($club['validation_finale'] == -1 || $club['validation_finale'] === 0) && !empty($club['motif_refus'])) {
                                        // Club refusé (validation_finale = -1 et motif_refus n'est pas vide)
                                        $status = 'Refusé';
                                        $statusClass = 'status-rejected';
                                    } else {
                                        // Club en attente de validation
                                        $status = 'En attente';
                                        $statusClass = 'status-pending';
                                    }
                                ?>
                                <span class="club-status-badge <?= $statusClass ?>">
                                    <?= $status ?>
                                </span> 
                            </div>

                            <div class="club-meta">
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt meta-icon"></i>
                                    <span><?= htmlspecialchars($club['campus'] ?? 'Campus non défini') ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar meta-icon"></i>
                                    <span>Créé le <?= date('d/m/Y', strtotime($club['date_creation'] ?? 'now')) ?? 'Date non définie' ?></span>
                                </div>
                            </div>

                            <?php if (!empty($club['description'])): ?>
                                <div class="club-description">
                                    <?= htmlspecialchars($club['description']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (($club['validation_finale'] == -1 || $club['validation_finale'] === 0) && !empty($club['motif_refus'])): ?>
                                <div class="refusal-reason">
                                    <h5><i class="fas fa-times-circle"></i> Motif du refus :</h5>
                                    <p><?= htmlspecialchars($club['motif_refus']) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="club-actions">
                                <?php if (!empty($canEditClub)): ?>
                                <a href="?page=club-edit&id=<?= $club['club_id'] ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Modifier le club
                                </a>
                                <?php endif; ?>
                                <?php if ($club['validation_finale'] != 1): ?>
                                    <!-- Club non validé: affiche Modifier et Supprimer -->
                                    <a href="?page=club-edit&id=<?= $club['club_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <form method="POST" class="form-delete-club" data-club-name="<?= htmlspecialchars($club['nom_club'] ?? '') ?>">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                        <input type="hidden" name="delete_club" value="1">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Club validé: affiche Voir détails -->
                                    <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div id="myClubsPagination" class="pagination-wrapper"></div>

                <a href="?page=club-create" class="btn btn-primary mt-20">
                    <i class="fas fa-plus"></i> Créer un nouveau club
                </a>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize search for my clubs
        if (document.querySelector('#myClubSearch')) {
            window.myClubSearch = new SearchComponent({
                input: '#myClubSearch',
                items: '.clubs-list',
                fields: ['data-search', 'h3', '.club-type', '.club-description'],
                noResultsMessage: 'Aucun club trouvé'
            });
        }

        // Initialize pagination
        if (document.querySelector('.clubs-list')) {
            window.myClubPagination = new PaginationComponent({
                itemsSelector: '.clubs-list',
                paginationSelector: '#myClubsPagination',
                perPage: 6,
                perPageOptions: [6, 12, 24],
                searchComponent: window.myClubSearch || null
            });
        }
    });

    // Delete club confirmation with SweetAlert2
    document.querySelectorAll('.form-delete-club').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const clubName = form.dataset.clubName;
            
            SwalHelper.confirmDelete('le club "' + clubName + '"')
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
        });
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
