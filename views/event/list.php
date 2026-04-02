<?php
/**
 * Liste publique des evenements valides
 * 
 * Affiche tous les evenements approuves :
 * - Barre de recherche avec filtrage dynamique
 * - Affichage en grille de cartes
 * - Tri par date (plus recents d'abord)
 * - Couleurs par campus
 * 
 * Variables attendues :
 * - $events : Liste des evenements valides
 * 
 * @package Views/Event
 */
$pageTitle = 'Événements - EILCO';
$pageCss = ['shared', 'buttons', 'search', 'pagination', 'events'];
$user_permission = (int)($_SESSION['permission'] ?? 1);
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
                    <a href="?page=home" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-calendar-alt"></i> Événements</h1>
                <p class="subtitle">Découvrez tous les événements validés de l'EILCO</p>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="eventSearch" class="search-input" 
                           placeholder="Rechercher un événement..." 
                           autocomplete="off">
                    <button type="button" class="search-clear" aria-label="Effacer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-filters">
                    <span class="filter-chip" data-search-filter="calais">
                        <i class="fas fa-map-marker-alt"></i> Calais
                    </span>
                    <span class="filter-chip" data-search-filter="longuenesse">
                        <i class="fas fa-map-marker-alt"></i> Longuenesse
                    </span>
                    <span class="filter-chip" data-search-filter="dunkerque">
                        <i class="fas fa-map-marker-alt"></i> Dunkerque
                    </span>
                    <span class="filter-chip" data-search-filter="boulogne">
                        <i class="fas fa-map-marker-alt"></i> Boulogne
                    </span>
                </div>
                <div class="search-results-info">
                    <span class="search-results-count"><strong><?= count($events) ?></strong> événement<?= count($events) !== 1 ? 's' : '' ?></span>
                </div>
            </div>

            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucun événement disponible</h3>
                    <p>Il n'y a pas encore d'événements validés pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php 
                    $months_fr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                    foreach ($events as $event): 
                        $eventDate = strtotime($event['date_ev']);
                        $monthIndex = (int)date('n', $eventDate) - 1;
                        $searchData = strtolower($event['titre'] . ' ' . ($event['description'] ?? '') . ' ' . ($event['campus'] ?? ''));
                        $filterData = strtolower($event['campus'] ?? 'calais');
                    ?>
                        <div class="event-card" data-search="<?= htmlspecialchars($searchData) ?>" data-filter="<?= htmlspecialchars($filterData) ?>">
                            <div class="event-date-badge">
                                <span class="day"><?= date('d', $eventDate) ?></span>
                                <span class="month"><?= $months_fr[$monthIndex] ?></span>
                            </div>
                            <div class="event-content">
                                <?php if (!empty($event['logo_club'])): ?>
                                    <?php
                                        $rawLogo = $event['logo_club'];
                                        $logoPath = preg_match('#^https?://#i', $rawLogo) ? $rawLogo : '/' . ltrim($rawLogo, '/');
                                        $logoEscaped = htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8');
                                        $alt = htmlspecialchars($event['nom_club'] ?? 'Logo du club', ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <img src="<?= $logoEscaped ?>" alt="<?= $alt ?>" class="event-card-logo" loading="lazy" />
                                <?php endif; ?>

                                <div class="event-content-main">
                                    <p class="club-name">
                                        <i class="fas fa-users"></i> <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                    </p>
                                    
                                    <h3><?= htmlspecialchars($event['titre']) ?></h3>
                                    
                                    <div class="event-meta">
                                        <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                        </span>
                                        <?php if (!empty($event['type_event'])): ?>
                                            <span class="type-badge <?= ($event['type_event'] === 'event') ? 'event' : 'activity' ?>">
                                                <?= htmlspecialchars($event['type_event'] === 'event' ? 'Événement' : 'Activité') ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($event['horaire_debut'])): ?>
                                            <span class="time">
                                                <i class="fas fa-clock"></i> <?= htmlspecialchars($event['horaire_debut']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="event-description"><?= htmlspecialchars(mb_substr($event['description'], 0, 120)) ?>...</p>
                                    <?php endif; ?>
                                    <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div id="eventPagination" class="pagination-wrapper"></div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure search component exists (may not be initialized yet by auto-init)
        if (document.querySelector('#eventSearch') && !window.eventSearch) {
            window.eventSearch = new SearchComponent({
                input: '#eventSearch',
                items: '.events-grid',
                fields: ['data-search', 'h3', '.event-description'],
                noResultsMessage: 'Aucun événement trouvé'
            });
        }

        if (document.querySelector('.events-grid')) {
            window.eventPagination = new PaginationComponent({
                itemsSelector: '.events-grid',
                paginationSelector: '#eventPagination',
                perPage: 9,
                perPageOptions: [6, 9, 18, 30],
                searchComponent: window.eventSearch || null
            });
        }
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
