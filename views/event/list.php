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
$isAuthenticated = !empty($_SESSION['id']);
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body class="event-list-page">
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
                        // Détection robuste du type : prend en compte variantes, accents et libellés hétérogènes.
                        $typeRaw = trim((string)($event['type_event'] ?? ''));
                        $typeValue = mb_strtolower($typeRaw, 'UTF-8');
                        $typeNormalized = strtr($typeValue, [
                            'à' => 'a', 'â' => 'a',
                            'ç' => 'c',
                            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                            'î' => 'i', 'ï' => 'i',
                            'ô' => 'o',
                            'ù' => 'u', 'û' => 'u', 'ü' => 'u'
                        ]);

                        $isActivity = false;
                        $activityExactValues = [
                            'activity', 'activite', 'atelier', 'workshop', 'club_activity', 'club-activity'
                        ];

                        if (in_array($typeNormalized, $activityExactValues, true)) {
                            $isActivity = true;
                        } else {
                            $activityHints = ['activit', 'atelier', 'workshop'];
                            foreach ($activityHints as $hint) {
                                if ($typeNormalized !== '' && str_contains($typeNormalized, $hint)) {
                                    $isActivity = true;
                                    break;
                                }
                            }
                        }
                    ?>
                        <div class="event-card" data-search="<?= htmlspecialchars($searchData) ?>" data-filter="<?= htmlspecialchars($filterData) ?>">
                            <div class="event-date-badge">
                                <span class="day"><?= date('d', $eventDate) ?></span>
                                <span class="month"><?= $months_fr[$monthIndex] ?></span>
                            </div>
                            <div class="event-content">
                                <div class="event-media" aria-hidden="true">
                                    <?php if (!empty($event['logo_club'])): ?>
                                        <?php
                                            $rawLogo = $event['logo_club'];
                                            $logoPath = preg_match('#^https?://#i', $rawLogo) ? $rawLogo : '/' . ltrim($rawLogo, '/');
                                            $logoEscaped = htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8');
                                            $alt = htmlspecialchars($event['nom_club'] ?? 'Logo du club', ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <img src="<?= $logoEscaped ?>" alt="<?= $alt ?>" class="event-card-logo" loading="lazy" />
                                    <?php else: ?>
                                        <div class="event-card-logo-placeholder">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="event-content-main">
                                    <p class="club-name">
                                        <i class="fas fa-users"></i> <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                    </p>
                                    
                                    <h3><?= htmlspecialchars($event['titre']) ?></h3>
                                    
                                    <div class="event-meta">
                                        <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                        </span>
                                        <span class="type-badge <?= $isActivity ? 'activity' : 'event' ?>">
                                            <i class="fas <?= $isActivity ? 'fa-shapes' : 'fa-calendar-check' ?>"></i>
                                            <?= $isActivity ? 'Activité' : 'Événement' ?>
                                        </span>
                                        <?php if (!empty($event['horaire_debut'])): ?>
                                            <span class="time">
                                                <i class="fas fa-clock"></i> <?= htmlspecialchars($event['horaire_debut']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="participants">
                                            <i class="fas fa-user-friends"></i>
                                            <?= (int)($event['subscription_count'] ?? 0) ?> inscrit<?= ((int)($event['subscription_count'] ?? 0) !== 1) ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <?php
                                            $description = trim((string)$event['description']);
                                            $isLongDescription = mb_strlen($description) > 120;
                                            $shortDescription = $isLongDescription ? mb_substr($description, 0, 120) . '...' : $description;
                                        ?>
                                        <div class="event-description-block">
                                            <p class="event-description"
                                               data-short="<?= htmlspecialchars($shortDescription, ENT_QUOTES, 'UTF-8') ?>"
                                               data-full="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
                                               <?= htmlspecialchars($shortDescription) ?>
                                            </p>
                                            <?php if ($isLongDescription): ?>
                                                <button type="button" class="event-see-more" aria-expanded="false">Voir plus...</button>
                                            <?php else: ?>
                                                <span class="event-see-more-placeholder" aria-hidden="true">Voir plus...</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="event-card-actions">
                                        <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Voir détails
                                        </a>

                                        <?php if ($isAuthenticated): ?>
                                            <?php if (!empty($event['is_subscribed'])): ?>
                                                <a href="?page=my-subscriptions" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Inscrit
                                                </a>
                                            <?php else: ?>
                                                <form method="POST" action="?page=subscribe" class="form-subscribe-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="event_id" value="<?= (int)$event['event_id'] ?>">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-user-plus"></i> S'inscrire
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="?page=login" class="btn btn-secondary">
                                                <i class="fas fa-sign-in-alt"></i> Se connecter
                                            </a>
                                        <?php endif; ?>
                                    </div>
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

        // Inscription AJAX sans redirection : popup de succès + mise à jour visuelle du bouton.
        document.querySelectorAll('.form-subscribe-inline').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn || submitBtn.disabled) {
                    return;
                }

                submitBtn.disabled = true;
                const originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';

                try {
                    const formData = new FormData(form);
                    formData.append('action', 'subscribe');

                    const response = await fetch('?page=subscribe-ajax', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.error || 'Erreur lors de l\'inscription.');
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'Inscription confirmée',
                        text: 'Vous êtes inscrit à cet événement.',
                        confirmButtonText: 'OK'
                    });

                    const cardActions = form.closest('.event-card-actions');
                    if (cardActions) {
                        const subscribedLink = document.createElement('a');
                        subscribedLink.href = '?page=my-subscriptions';
                        subscribedLink.className = 'btn btn-success';
                        subscribedLink.innerHTML = '<i class="fas fa-check"></i> Inscrit';
                        form.replaceWith(subscribedLink);
                    }
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Impossible de s\'inscrire',
                        text: error.message || 'Veuillez réessayer.'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            });
        });

        // Description toggle for long texts.
        document.querySelectorAll('.event-see-more').forEach((toggleBtn) => {
            toggleBtn.addEventListener('click', function () {
                const card = this.closest('.event-content-main');
                const descriptionEl = card ? card.querySelector('.event-description') : null;
                if (!descriptionEl) {
                    return;
                }

                const expanded = this.getAttribute('aria-expanded') === 'true';
                if (expanded) {
                    descriptionEl.textContent = descriptionEl.dataset.short || descriptionEl.textContent;
                    descriptionEl.classList.remove('is-expanded');
                    this.setAttribute('aria-expanded', 'false');
                    this.textContent = 'Voir plus...';
                } else {
                    descriptionEl.textContent = descriptionEl.dataset.full || descriptionEl.textContent;
                    descriptionEl.classList.add('is-expanded');
                    this.setAttribute('aria-expanded', 'true');
                    this.textContent = 'Voir moins';
                }
            });
        });
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
