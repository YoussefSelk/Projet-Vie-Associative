<?php
/**
 * Liste des evenements de l'utilisateur
 * 
 * Affiche les evenements des clubs dont l'utilisateur est membre :
 * - Evenements passes et a venir
 * - Actions specifiques (modifier, deposer rapport)
 * - Etat vide si aucun evenement
 * 
 * Variables attendues :
 * - $events : Liste des evenements des clubs de l'utilisateur
 * 
 * @package Views/Event
 */
$pageTitle = 'Mes événements - EILCO';
$pageCss = ['shared', 'buttons', 'search', 'pagination', 'events'];
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
                <h1><i class="fas fa-calendar-check"></i> Mes Événements</h1>
                <p class="subtitle">Événements organisés par mes clubs</p>
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

            <!-- Search Bar -->
            <?php if (!empty($events)): ?>
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="myEventSearch" class="search-input" 
                           placeholder="Rechercher un événement..." 
                           autocomplete="off">
                    <button type="button" class="search-clear" aria-label="Effacer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-filters">
                    <span class="filter-chip" data-search-filter="approuve">
                        <i class="fas fa-check-circle"></i> Approuvé
                    </span>
                    <span class="filter-chip" data-search-filter="attente">
                        <i class="fas fa-clock"></i> En attente
                    </span>
                    <span class="filter-chip" data-search-filter="refuse">
                        <i class="fas fa-times-circle"></i> Refusé
                    </span>
                </div>
                <div class="search-results-info">
                    <span class="search-results-count"><strong><?= count($events) ?></strong> événement<?= count($events) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucun événement</h3>
                    <p>Vous n'êtes membre d'aucun club ayant organisé des événements.</p>
                    <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($events as $event): 
                        // Logique de filtrage
                        $statusFilter = '';
                        if ($event['validation_finale'] === null || ($event['validation_finale'] == 0 && empty($event['motif_refus']))) {
                            $statusFilter = 'attente';
                        } elseif ($event['validation_finale'] == 1) {
                            $statusFilter = 'approuve';
                        } else {
                            $statusFilter = 'refuse';
                        }
                        $searchData = strtolower(($event['titre'] ?? '') . ' ' . ($event['description'] ?? '') . ' ' . ($event['campus'] ?? ''));
                        
                        // Détermination du statut pour l'affichage
                        $status = '';
                        $statusClass = '';
                        $isRejected = false;
                        if ($event['validation_finale'] === null || ($event['validation_finale'] == 0 && empty($event['motif_refus']))) {
                            $status = 'En attente';
                            $statusClass = 'badge-warning';
                        } elseif ($event['validation_finale'] == 1) {
                            $status = 'Approuvé';
                            $statusClass = 'badge-success';
                        } else {
                            $status = 'Refusé';
                            $statusClass = 'badge-danger';
                            $isRejected = true;
                        }
                    ?>
                        <div class="event-card" data-search="<?= htmlspecialchars($searchData) ?>" data-filter="<?= $statusFilter ?>">
                            <div class="event-card-header">
                                <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                <div class="event-date">
                                    <i class="fas fa-calendar-day"></i> 
                                    <?= date('d', strtotime($event['date_ev'])) ?> 
                                    <?php 
                                        $moisFr = ['jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
                                        echo $moisFr[date('n', strtotime($event['date_ev'])) - 1];
                                    ?>
                                </div>
                            </div>

                            <div class="event-card-body">
                                <div style="margin-bottom: 10px;">
                                    <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                    </span>
                                    <span class="badge <?= $statusClass ?>" style="margin-left: 5px;"><?= $status ?></span>
                                </div>

                                <?php if ($isRejected && !empty($event['motif_refus'])): ?>
                                    <div class="refusal-reason">
                                        <small><strong><i class="fas fa-info-circle"></i> Motif :</strong> <?= htmlspecialchars($event['motif_refus']) ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($event['description'])): ?>
                                    <p class="event-description">
                                        <?= htmlspecialchars(mb_substr($event['description'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="event-card-footer">
                                <a href="?page=event-view&id=<?= $event['event_id'] ?>" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <?php 
                                $isPending = ($event['validation_finale'] === null || ($event['validation_finale'] == 0 && empty($event['motif_refus'])));
                                if ($isPending || $isRejected): ?>
                                    <a href="?page=update-event&id=<?= $event['event_id'] ?>" style="color: #f39c12;" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($isRejected): ?>
                                    <form method="POST" class="form-delete-event" data-event-title="<?= htmlspecialchars($event['titre'] ?? '') ?>" style="display:inline;">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="delete_event" value="1">
                                        <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer; padding:0; margin-left:10px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div id="myEventPagination" class="pagination-wrapper"></div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize search for my events
        if (document.querySelector('#myEventSearch')) {
            window.myEventSearch = new SearchComponent({
                input: '#myEventSearch',
                items: '.events-grid',
                fields: ['data-search', 'h3', '.event-description'],
                noResultsMessage: 'Aucun événement trouvé'
            });
        }

        // Initialize pagination
        if (document.querySelector('.events-grid')) {
            window.myEventPagination = new PaginationComponent({
                itemsSelector: '.events-grid',
                paginationSelector: '#myEventPagination',
                perPage: 9,
                perPageOptions: [6, 9, 18, 30],
                searchComponent: window.myEventSearch || null
            });
        }
    });

    // Delete event confirmation with SweetAlert2
    document.querySelectorAll('.form-delete-event').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const eventTitle = form.dataset.eventTitle;
            
            SwalHelper.confirmDelete('l\'événement "' + eventTitle + '"')
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
