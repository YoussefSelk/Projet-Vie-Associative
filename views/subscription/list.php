<?php
/**
 * Liste des inscriptions aux evenements
 * 
 * Permet a l'utilisateur de gerer ses inscriptions :
 * - Affichage des evenements auxquels il est inscrit
 * - Recherche dans ses inscriptions
 * - Annulation d'inscription
 * - Tri par date
 * 
 * Variables attendues :
 * - $subscriptions : Liste des inscriptions de l'utilisateur
 * 
 * @package Views/Subscription
 */
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
                <h1><i class="fas fa-calendar-check"></i> Mes inscriptions</h1>
                <p class="subtitle">Gérez vos inscriptions aux événements</p>
            </div>

            <!-- Search Bar -->
            <?php if (!empty($subscriptions)): ?>
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="subscriptionSearch" class="search-input" 
                           placeholder="Rechercher dans mes inscriptions..." 
                           autocomplete="off">
                    <button type="button" class="search-clear" aria-label="Effacer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-results-info">
                    <span class="search-results-count"><strong><?= count($subscriptions) ?></strong> inscription<?= count($subscriptions) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($subscriptions)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucune inscription</h3>
                    <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
                    <a href="?page=event-list" class="btn btn-primary"><i class="fas fa-calendar-alt"></i> Voir les événements</a>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($subscriptions as $event): 
                        $searchData = strtolower(($event['titre'] ?? '') . ' ' . ($event['campus'] ?? ''));
                    ?>
                        <div class="event-card" data-search="<?= htmlspecialchars($searchData) ?>">
                            <div class="event-date-badge">
                                <span class="day"><?= date('d', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                <span class="month">
                                    <?php
                                    setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

                                    $timestamp = strtotime($event['date_ev'] ?? 'now');
                                    echo strftime('%b', $timestamp);
                                    ?>
                                </span>

                            </div>
                            <div class="event-content">
                                <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                <div class="event-meta">
                                    <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                    </span>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Inscrit
                                    </span>
                                </div>
                                <div class="event-card-actions">
                                    <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="?page=unsubscribe&event_id=<?= $event['event_id'] ?>" 
                                       class="btn btn-danger btn-sm btn-unsubscribe" 
                                       data-event-title="<?= htmlspecialchars($event['titre']) ?>">
                                        <i class="fas fa-times"></i> Se désinscrire
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div id="subscriptionPagination" class="pagination-wrapper"></div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure search component exists
        if (document.querySelector('#subscriptionSearch') && !window.subscriptionSearch) {
            window.subscriptionSearch = new SearchComponent({
                input: '#subscriptionSearch',
                items: '.events-grid',
                fields: ['data-search', 'h3'],
                noResultsMessage: 'Aucune inscription trouvée'
            });
        }

        // Initialize pagination for subscriptions
        if (document.querySelector('.events-grid')) {
            window.subPagination = new PaginationComponent({
                itemsSelector: '.events-grid',
                paginationSelector: '#subscriptionPagination',
                perPage: 9,
                perPageOptions: [6, 9, 18, 30],
                searchComponent: window.subscriptionSearch || null
            });
        }
    });

    // Unsubscribe confirmation with SweetAlert2
    document.querySelectorAll('.btn-unsubscribe').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const eventTitle = btn.dataset.eventTitle;
            const unsubscribeUrl = btn.href;
            
            SwalHelper.confirm(
                'Se désinscrire ?',
                'Voulez-vous vraiment vous désinscrire de "' + eventTitle + '" ?',
                'Oui, me désinscrire',
                'Annuler'
            ).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = unsubscribeUrl;
                }
            });
        });
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
