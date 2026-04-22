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
$pageTitle = 'Mes demandes - EILCO';
$pageCss = ['shared', 'buttons', 'search', 'pagination', 'events'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body class="my-events-page">
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
                <h1><i class="fas fa-layer-group"></i> Mes Demandes</h1>
                <p class="subtitle">Suivi global de mes événements et activités</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?>
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <?php if (!empty($events)): ?>
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                          <input type="text" id="myEventSearch" class="search-input" 
                              placeholder="Rechercher un événement ou une activité..." 
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
                        // Statut principal de la demande
                        $validationFinaleRaw = $event['validation_finale'] ?? null;
                        $validationFinale = ($validationFinaleRaw === null) ? null : (string)$validationFinaleRaw;
                        $validationBde = ($event['validation_bde'] ?? null);
                        $validationTuteur = ($event['validation_tuteur'] ?? null);
                        $validationAdmin = ($event['validation_admin'] ?? null);
                        $validationBdeState = ($validationBde === null) ? null : (string)$validationBde;
                        $validationTuteurState = ($validationTuteur === null) ? null : (string)$validationTuteur;
                        $validationAdminState = ($validationAdmin === null) ? null : (string)$validationAdmin;
                        $rejectedBy = [];

                        if ($validationBdeState === '0' || $validationBdeState === '-1') {
                            $rejectedBy[] = 'BDE';
                        }
                        if ($validationTuteurState === '0' || $validationTuteurState === '-1') {
                            $rejectedBy[] = 'tuteur';
                        }
                        if ($validationAdminState === '0' || $validationAdminState === '-1') {
                            $rejectedBy[] = 'admin';
                        }

                        $isRejected = ($validationFinale === '-1')
                            || ($validationFinale === '0')
                            || !empty($rejectedBy);

                        $allSignaturesApproved = (
                            $validationBdeState === '1'
                            && $validationTuteurState === '1'
                            && $validationAdminState === '1'
                        );
                        $isApproved = ($validationFinale === '1') || $allSignaturesApproved;
                        $isPending = !$isApproved && !$isRejected;

                        // Logique de filtrage
                        if ($isPending) {
                            $statusFilter = 'attente';
                        } elseif ($isApproved) {
                            $statusFilter = 'approuve';
                        } else {
                            $statusFilter = 'refuse';
                        }

                        $searchData = strtolower(($event['titre'] ?? '') . ' ' . ($event['description'] ?? '') . ' ' . ($event['campus'] ?? '') . ' ' . ($event['type_event'] ?? ''));
                        $typeValue = strtolower(trim((string)($event['type_event'] ?? '')));
                        $isActivity = ($typeValue === 'activity');
                        
                        // Détermination du statut pour l'affichage
                        if ($isPending) {
                            $status = 'En attente';
                            $statusClass = 'badge-warning';
                        } elseif ($isApproved) {
                            $status = 'Approuvé';
                            $statusClass = 'badge-success';
                        } else {
                            $status = 'Refusé';
                            $statusClass = 'badge-danger';
                        }
                    ?>
                        <div class="event-card" data-search="<?= htmlspecialchars($searchData) ?>" data-filter="<?= $statusFilter ?>">
                            <div class="event-card-inner">
                                <aside class="event-left">
                                    <div class="event-date-badge">
                                        <span class="day"><?= date('d', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                        <span class="month"><?php 
                                            $moisFr = ['jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
                                            echo $moisFr[date('n', strtotime($event['date_ev'] ?? 'now')) - 1];
                                        ?></span>
                                    </div>
                                </aside>

                                <div class="event-body">
                                    <header class="event-head">
                                        <h3 class="event-title"><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                        <div class="event-head-right">
                                            <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                            </span>
                                            <span class="type-badge <?= $isActivity ? 'activity' : 'event' ?>">
                                                <i class="fas <?= $isActivity ? 'fa-shapes' : 'fa-calendar-check' ?>"></i>
                                                <?= $isActivity ? 'Activité' : 'Événement' ?>
                                            </span>
                                            <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                        </div>
                                    </header>

                                    <?php if ($isRejected && !empty($event['motif_refus'])): ?>
                                        <div class="refusal-reason">
                                            <small><strong>Motif :</strong> <?= htmlspecialchars($event['motif_refus']) ?></small>
                                        </div>
                                    <?php elseif ($isRejected && !empty($rejectedBy)): ?>
                                        <div class="refusal-reason">
                                            <small><strong>Refus :</strong> <?= htmlspecialchars(ucfirst(implode(', ', $rejectedBy))) ?></small>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                        $eventSignatureState = static function ($value) {
                                            if ($value === 1 || $value === '1') {
                                                return 'done';
                                            }
                                            if ($value === 0 || $value === '0' || $value === -1 || $value === '-1') {
                                                return 'rejected';
                                            }
                                            return 'pending';
                                        };

                                        $eventTrackingSteps = [
                                            ['label' => 'Demande envoyée', 'state' => 'done', 'icon' => 'fa-paper-plane', 'forced' => false],
                                            ['label' => 'Validation BDE', 'state' => $eventSignatureState($validationBde), 'icon' => 'fa-people-group', 'forced' => false],
                                            ['label' => 'Validation tuteur', 'state' => $eventSignatureState($validationTuteur), 'icon' => 'fa-user-check', 'forced' => false],
                                            ['label' => 'Validation admin', 'state' => $eventSignatureState($validationAdmin), 'icon' => 'fa-user-shield', 'forced' => false],
                                            ['label' => 'Décision finale', 'state' => $isRejected ? 'rejected' : ($isApproved ? 'done' : 'pending'), 'icon' => 'fa-flag-checkered', 'forced' => false]
                                        ];

                                        $eventIsForcedApproval = $isApproved
                                            && (
                                                (int)($validationBde ?? 0) !== 1
                                                || (int)($validationAdmin ?? 0) !== 1
                                                || (int)($validationTuteur ?? 0) !== 1
                                            );

                                        if ($eventIsForcedApproval) {
                                            foreach ($eventTrackingSteps as $eventStepIndex => $eventStep) {
                                                $isMiddleStep = ($eventStepIndex > 0 && $eventStepIndex < (count($eventTrackingSteps) - 1));
                                                if ($isMiddleStep && $eventStep['state'] === 'pending') {
                                                    $eventTrackingSteps[$eventStepIndex]['state'] = 'done';
                                                    $eventTrackingSteps[$eventStepIndex]['forced'] = true;
                                                }
                                            }
                                        }

                                        if ($isPending) {
                                            $currentEventStepAssigned = false;
                                            foreach ($eventTrackingSteps as $eventStepIndex => $eventStep) {
                                                if ($eventStep['state'] === 'pending' && !$currentEventStepAssigned) {
                                                    $eventTrackingSteps[$eventStepIndex]['state'] = 'current';
                                                    $currentEventStepAssigned = true;
                                                }
                                            }
                                        }
                                    ?>

                                    <section class="request-tracker" aria-label="Suivi de validation de l'événement <?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?>">
                                        <h4 class="tracker-title"><i class="fas fa-route"></i> Suivi de validation</h4>
                                        <?php if ($eventIsForcedApproval): ?>
                                            <p class="tracker-subtitle tracker-subtitle-warning"><i class="fas fa-bolt"></i> Validation forcée administrativement</p>
                                        <?php elseif ($isPending): ?>
                                            <p class="tracker-subtitle tracker-subtitle-info"><i class="fas fa-hourglass-half"></i> En attente des validations BDE, tuteur et admin</p>
                                        <?php elseif ($isRejected): ?>
                                            <p class="tracker-subtitle tracker-subtitle-danger"><i class="fas fa-circle-xmark"></i> Demande refusée<?= !empty($rejectedBy) ? ' par ' . htmlspecialchars(implode(', ', $rejectedBy)) : '' ?></p>
                                        <?php endif; ?>
                                        <ol class="tracker-timeline">
                                            <?php foreach ($eventTrackingSteps as $eventStep): ?>
                                                <li class="tracker-step is-<?= $eventStep['state'] ?><?= !empty($eventStep['forced']) ? ' is-forced' : '' ?>">
                                                    <span class="tracker-dot"><i class="fas <?= $eventStep['icon'] ?>"></i></span>
                                                    <span class="tracker-label"><?= htmlspecialchars($eventStep['label']) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </section>

                                    <?php if (!empty($event['description'])): ?>
                                        <p class="event-description"><?= htmlspecialchars(mb_substr($event['description'], 0, 120)) ?><?= mb_strlen($event['description']) > 120 ? '...' : '' ?></p>
                                    <?php endif; ?>

                                    <div class="event-meta-row">
                                        <div class="meta-item"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars(date('d/m/Y', strtotime($event['date_ev'] ?? 'now'))) ?></div>
                                        <?php if (!empty($event['horaire_debut']) || !empty($event['horaire_fin'])): ?>
                                            <div class="meta-item"><i class="fas fa-clock"></i> <?= htmlspecialchars((!empty($event['horaire_debut']) ? substr($event['horaire_debut'],0,5) : '?') . ' - ' . (!empty($event['horaire_fin']) ? substr($event['horaire_fin'],0,5) : '?')) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <footer class="event-actions">
                                        <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <?php if ($isPending || $isRejected): ?>
                                            <a href="?page=update-event&id=<?= $event['event_id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($isRejected): ?>
                                            <form method="POST" class="form-delete-event" data-event-title="<?= htmlspecialchars($event['titre'] ?? '') ?>" style="display:inline-block;margin-left:6px;">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                                <input type="hidden" name="delete_event" value="1">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </footer>
                                </div>
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
    function cleanupStaleSwalOverlay() {
        const hasVisiblePopup = !!document.querySelector('.swal2-popup.swal2-show');
        if (hasVisiblePopup) {
            return;
        }

        // Si un backdrop SweetAlert2 reste bloque sans popup visible, on le nettoie.
        document.querySelectorAll('.swal2-container').forEach((container) => {
            container.remove();
        });
        document.body.classList.remove('swal2-shown', 'swal2-height-auto', 'swal2-no-backdrop');
        document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto', 'swal2-no-backdrop');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    document.addEventListener('DOMContentLoaded', function() {
        cleanupStaleSwalOverlay();

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

    window.addEventListener('pageshow', cleanupStaleSwalOverlay);

    // Delete event confirmation with SweetAlert2
    document.querySelectorAll('.form-delete-event').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const eventTitle = form.dataset.eventTitle;
            
            SwalHelper.confirmDelete('l\'événement "' + eventTitle + '"')
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                        return;
                    }

                    cleanupStaleSwalOverlay();
                });
        });
    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
