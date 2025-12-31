<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
</head>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="page-container">
            <div class="page-header">
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
                                <h3><?= htmlspecialchars($event['titre']) ?></h3>
                                <div class="event-meta">
                                    <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                    </span>
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
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
