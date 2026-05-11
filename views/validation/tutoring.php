<?php
/**
 * Espace administrateur - Tableau de bord
 * 
 * Interface dediee aux administrateurs pour superviser tous les clubs et evenements :
 * - Clubs en attente de validation finale
 * - Evenements a approuver
 * - Liste de tous les clubs
 * - Rapports d'evenements a consulter
 * 
 * Un administrateur voit tous les elements du systeme
 * sans restriction.
 * 
 * Variables attendues :
 * - $pending_clubs : Clubs a valider
 * - $pending_events : Evenements a valider
 * - $all_clubs : Tous les clubs du systeme
 * 
 * Permissions : Administrateur (niveau 5)
 * 
 * @package Views/Validation
 */
$pageTitle = 'Validations - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'search', 'pagination', 'validation', 'clubs', 'events', 'dashboard'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body class="validation-page">
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="page-container admin-dashboard">
            <div class="page-header">
                <div class="header-left">
                    <a href="?page=home" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
               <h1><?= $is_admin ? 'Administration - Validations' : 'Tutorat - Validations' ?></h1>
               <p class="subtitle"><?= $is_admin ? 'Supervisez et validez tous les clubs et evenements du systeme' : 'Validez les clubs et evenements sous votre tutelle' ?></p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
            <?php endif; ?>

            <?php if(!empty($info_msg)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($info_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
            <?php endif; ?>

            <?php
                $clubsPendingCount = count(array_filter($pending_clubs ?? [], function($c) use ($is_tutor) {
                    $final = isset($c['validation_finale']) ? (int)$c['validation_finale'] : null;
                    $valTuteur = isset($c['validation_tuteur']) ? (int)$c['validation_tuteur'] : null;
                    if ($final === 1 || $final === -1 || $final === 0) return false;
                    if ($is_tutor && $valTuteur === 1) return false;
                    return true;
                }));
                $eventsPendingCount = count(array_filter($pending_events ?? [], function($e) use ($is_tutor) {
                    $final = isset($e['validation_finale']) ? (int)$e['validation_finale'] : null;
                    $valTuteur = isset($e['validation_tuteur']) ? (int)$e['validation_tuteur'] : null;
                    if ($final === 1 || $final === -1 || $final === 0) return false;
                    if ($is_tutor && $valTuteur === 1) return false;
                    return true;
                }));
                $totalPendingCount = $clubsPendingCount + $eventsPendingCount;
            ?>

            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-content">
                        <h3><?= $totalPendingCount ?></h3>
                        <p>A traiter</p>
                    </div>
                </div>
                <div class="stat-card clubs">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-content">
                        <h3><?= $clubsPendingCount ?></h3>
                        <p>Clubs a valider</p>
                    </div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-content">
                        <h3><?= $eventsPendingCount ?></h3>
                        <p>Evenements a valider</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-section">
                <div class="search-row">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher par nom, type, campus..." autocomplete="off">
                    </div>
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">
                            <i class="fas fa-layer-group"></i> Tout
                            <span class="count"><?= $totalPendingCount ?></span>
                        </button>
                        <?php if (!$is_bde): ?>
                        <button class="filter-tab" data-filter="clubs">
                            <i class="fas fa-building"></i> Clubs
                            <span class="count"><?= $clubsPendingCount ?></span>
                        </button>
                        <?php endif; ?>
                        <button class="filter-tab" data-filter="events">
                            <i class="fas fa-calendar-alt"></i> Evenements
                            <span class="count"><?= $eventsPendingCount ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pending Items -->
            <?php if (empty($pending_clubs) && empty($pending_events)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state-advanced">
                            <div class="empty-icon"><i class="fas fa-check"></i></div>
                            <h3>Tout est a jour !</h3>
                            <p>Aucune validation en attente. Revenez plus tard.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="validation-cards-container" id="validationCards">
                    <!-- Pending Clubs -->
                    <?php foreach ($pending_clubs ?? [] as $club): ?>
                        <?php
                        // Precharger les membres du club pour affichage dans la modale
                        $clubMembers = [];
                        try {
                            $stmt = $db->prepare("SELECT u.prenom, u.nom, u.mail, u.promo, mc.fonction, mc.soutenance\n                                                   FROM membres_club mc\n                                                   JOIN users u ON mc.membre_id = u.id\n                                                   WHERE mc.club_id = ? AND mc.valide = 1\n                                                   ORDER BY CASE WHEN mc.fonction = 'President' THEN 0 ELSE 1 END, u.nom, u.prenom");
                            $stmt->execute([$club['club_id']]);
                            $clubMembers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                        } catch (Exception $e) {
                            $clubMembers = [];
                        }
                            $clubFinal = isset($club['validation_finale']) ? (int)$club['validation_finale'] : null;
                            $clubValTuteur = isset($club['validation_tuteur']) ? (int)$club['validation_tuteur'] : null;
                            $clubValAdmin = isset($club['validation_admin']) ? (int)$club['validation_admin'] : null;
                            $clubValBde = isset($club['validation_bde']) ? (int)$club['validation_bde'] : null;

                            $clubStatus = 'en_attente';
                            if ($clubFinal === 1) {
                                $clubStatus = 'valide';
                            } elseif ($clubFinal === -1 || $clubFinal === 0) {
                                $clubStatus = 'refuse';
                            } elseif ($clubValAdmin === 1 || $clubValBde === 1 || $clubValTuteur === 1) {
                                $clubStatus = 'en_cours';
                            }

                            $clubStatusClass = 'badge-warning';
                            $clubStatusIcon = 'fa-clock';
                            $clubStatusLabel = 'En attente';
                            if ($clubStatus === 'valide') {
                                $clubStatusClass = 'badge-success';
                                $clubStatusIcon = 'fa-check-circle';
                                $clubStatusLabel = 'Valide';
                            } elseif ($clubStatus === 'refuse') {
                                $clubStatusClass = 'badge-danger';
                                $clubStatusIcon = 'fa-times-circle';
                                $clubStatusLabel = 'Refuse';
                            } elseif ($clubStatus === 'en_cours') {
                                $clubStatusClass = 'badge-info';
                                $clubStatusIcon = 'fa-spinner';
                                $clubStatusLabel = 'En cours';
                            }

                            $clubActionable = ($clubStatus !== 'valide' && $clubStatus !== 'refuse');
                            if ($is_tutor && $clubValTuteur === 1) {
                                $clubActionable = false;
                            }
                        ?>
                        <div class="validation-card-advanced club-card" 
                             data-type="clubs" 
                             data-search="<?= strtolower(htmlspecialchars($club['nom_club'] . ' ' . $club['type_club'] . ' ' . $club['campus'])) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row">
                                        <div class="card-type-icon <?= !empty($club['logo_club']) ? 'has-logo' : '' ?>">
                                            <?php if (!empty($club['logo_club'])): ?>
                                                <img src="<?= htmlspecialchars($club['logo_club']) ?>" alt="Logo <?= htmlspecialchars($club['nom_club']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-building"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($club['nom_club']) ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($club['type_club']) ?>
                                            </span>
                                        </div>
                                        <span class="badge <?= $clubStatusClass ?>"><i class="fas <?= $clubStatusIcon ?>"></i> <?= $clubStatusLabel ?></span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                        </div>
                                        <?php if (!empty($club['mail'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= htmlspecialchars($club['mail']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($club['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($club['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details btn-swal-club-details"
                                        data-club='<?= htmlspecialchars(json_encode($club), ENT_QUOTES, "UTF-8") ?>'
                                        data-members='<?= htmlspecialchars(json_encode($clubMembers), ENT_QUOTES, "UTF-8") ?>'>
                                        <i class="fas fa-eye"></i> Voir details
                                    </button>
                                    <?php if (($is_admin || $is_tutor) && $clubActionable): ?>
                                    <button type="button" class="btn-approve btn-swal-club-approve"
                                        data-id="<?= $club['club_id'] ?>"
                                        data-name="<?= htmlspecialchars($club['nom_club']) ?>">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($is_admin && $clubActionable): ?>
                                    <button type="button" class="btn-force btn-swal-club-force"
                                        data-id="<?= $club['club_id'] ?>"
                                        data-name="<?= htmlspecialchars($club['nom_club']) ?>"
                                        title="Valider sans attendre le tuteur">
                                        <i class="fas fa-bolt"></i> Forcer
                                    </button>
                                    <?php endif; ?>
                                    <?php if (($is_admin || $is_tutor) && $clubActionable): ?>
                                    <button type="button" class="btn-reject btn-swal-club-reject"
                                        data-id="<?= $club['club_id'] ?>"
                                        data-name="<?= htmlspecialchars($club['nom_club']) ?>">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pending Events -->
                    <?php foreach ($pending_events ?? [] as $event): ?>
                        <?php
                            $eventFinal = isset($event['validation_finale']) ? (int)$event['validation_finale'] : null;
                            $eventValTuteur = isset($event['validation_tuteur']) ? (int)$event['validation_tuteur'] : null;
                            $eventValAdmin = isset($event['validation_admin']) ? (int)$event['validation_admin'] : null;
                            $eventValBde = isset($event['validation_bde']) ? (int)$event['validation_bde'] : null;

                            $eventStatus = 'en_attente';
                            if ($eventFinal === 1) {
                                $eventStatus = 'valide';
                            } elseif ($eventFinal === -1 || $eventFinal === 0) {
                                $eventStatus = 'refuse';
                            } elseif ($eventValAdmin === 1 || $eventValBde === 1 || $eventValTuteur === 1) {
                                $eventStatus = 'en_cours';
                            }

                            $eventStatusClass = 'badge-warning';
                            $eventStatusIcon = 'fa-clock';
                            $eventStatusLabel = 'En attente';
                            if ($eventStatus === 'valide') {
                                $eventStatusClass = 'badge-success';
                                $eventStatusIcon = 'fa-check-circle';
                                $eventStatusLabel = 'Valide';
                            } elseif ($eventStatus === 'refuse') {
                                $eventStatusClass = 'badge-danger';
                                $eventStatusIcon = 'fa-times-circle';
                                $eventStatusLabel = 'Refuse';
                            } elseif ($eventStatus === 'en_cours') {
                                $eventStatusClass = 'badge-info';
                                $eventStatusIcon = 'fa-spinner';
                                $eventStatusLabel = 'En cours';
                            }

                            $eventActionable = ($eventStatus !== 'valide' && $eventStatus !== 'refuse');
                            if ($is_tutor && $eventValTuteur === 1) {
                                $eventActionable = false;
                            }
                        ?>
                        <div class="validation-card-advanced event-card" 
                             data-type="events" 
                             data-search="<?= strtolower(htmlspecialchars(($event['titre'] ?? '') . ' ' . ($event['nom_club'] ?? '') . ' ' . ($event['campus'] ?? ''))) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row">
                                        <div class="card-type-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($event['nom_club'] ?? 'N/A') ?>
                                            </span>
                                        </div>
                                        <span class="badge <?= $eventStatusClass ?>"><i class="fas <?= $eventStatusIcon ?>"></i> <?= $eventStatusLabel ?></span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('d/m/Y a H:i', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>"><?= htmlspecialchars($event['campus'] ?? 'N/A') ?></span>
                                        </div>
                                        <?php if (!empty($event['lieu'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-location-dot"></i>
                                            <span><?= htmlspecialchars($event['lieu']) ?></span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (isset($event['type_event'])): ?>
                                            <span class="type-badge <?= (!empty($event['type_event']) && $event['type_event'] == 'event') ? 'event' : 'activity' ?>">
                                                <?= htmlspecialchars((!empty($event['type_event']) && $event['type_event'] == 'event') ? 'Evenement' : 'Activite') ?>
                                            </span>
                                        <?php endif; ?>
                                    
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($event['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details btn-swal-event-details"
                                        data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>'>
                                        <i class="fas fa-eye"></i> Voir details
                                    </button>
                                    <?php 
                                    if ($is_admin) {
                                        $btn_name = 'validate_event_admin';
                                    } else {
                                        $btn_name = 'validate_event_tutor';
                                    }
                                    ?>
                                    <?php if ($eventActionable): ?>
                                        <button type="button" class="btn-approve btn-swal-event-approve"
                                            data-id="<?= $event['event_id'] ?>"
                                            data-name="<?= htmlspecialchars($event['titre'] ?? 'Evenement') ?>"
                                            data-validate-field="<?= $btn_name ?>">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                        <?php if ($is_admin): ?>
                                        <button type="button" class="btn-force btn-swal-event-force"
                                            data-id="<?= $event['event_id'] ?>"
                                            data-name="<?= htmlspecialchars($event['titre'] ?? 'Evenement') ?>"
                                            title="Valider sans attendre le tuteur">
                                            <i class="fas fa-bolt"></i> Forcer
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn-reject btn-swal-event-reject"
                                            data-id="<?= $event['event_id'] ?>"
                                            data-name="<?= htmlspecialchars($event['titre'] ?? 'Evenement') ?>"
                                            data-validate-field="<?= $btn_name ?>">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="empty-state-advanced" id="noResults">
                    <div class="empty-icon empty-icon-search">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun resultat</h3>
                    <p>Aucun element ne correspond a votre recherche.</p>
                </div>

                <!-- Pagination -->
                <div id="tutoringPagination" class="pagination-wrapper"></div>
            <?php endif; ?>

            <!-- All Clubs Section -->
            <?php if (!$is_bde): ?>
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Tous les clubs tutores (<?= count($tutored_clubs ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($tutored_clubs)): ?>
                        <div class="empty-state-advanced">
                            <div class="empty-icon empty-icon-info">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3>Aucun club</h3>
                            <p>Aucun club tutore pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="tutored-clubs-enhanced">
                            <?php foreach ($tutored_clubs as $club): ?>
                                <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="tutored-club-enhanced">
                                    <div class="club-avatar">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="club-details">
                                        <h4><?= htmlspecialchars($club['nom_club']) ?></h4>
                                        <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-right arrow"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <!-- CSRF token for AJAX requests -->
    <input type="hidden" id="csrf_token" value="<?= htmlspecialchars(Security::generateCsrfToken()) ?>">

    <script>
    (function() {
        'use strict';

        // --- UTILITAIRES ---
        const csrfToken = document.getElementById('csrf_token').value;

        function esc(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(c) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]);
            });
        }

        function decodeHtmlEntities(str) {
            if (!str) return '';
            const txt = document.createElement('textarea');
            txt.innerHTML = String(str);
            return txt.value;
        }

        function renderCollapsibleDescription(text, key) {
            const raw = decodeHtmlEntities((text || '').trim());
            if (!raw) return '<div class="swal-detail-description">Aucune description fournie.</div>';
            if (raw.length <= 420) {
                return '<div class="swal-detail-description">' + esc(raw).replace(/\n/g, '<br>') + '</div>';
            }

            const shortText = esc(raw.slice(0, 420)).replace(/\n/g, '<br>') + '...';
            const fullText = esc(raw).replace(/\n/g, '<br>');
            const id = 'desc_' + key;

            return '' +
                '<div class="swal-detail-description">' +
                    '<div id="' + id + '_short">' + shortText + '</div>' +
                    '<div id="' + id + '_full" style="display:none;">' + fullText + '</div>' +
                    '<button type="button" class="swal-file-link js-desc-toggle" data-target="' + id + '" style="margin-top:10px;">Voir plus</button>' +
                '</div>';
        }

        function bindDescriptionToggles() {
            document.querySelectorAll('.js-desc-toggle').forEach(function(btn) {
                if (btn.dataset.bound === '1') return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', function() {
                    const id = this.dataset.target;
                    const shortEl = document.getElementById(id + '_short');
                    const fullEl = document.getElementById(id + '_full');
                    if (!shortEl || !fullEl) return;
                    const expanded = fullEl.style.display !== 'none';
                    fullEl.style.display = expanded ? 'none' : '';
                    shortEl.style.display = expanded ? '' : 'none';
                    this.textContent = expanded ? 'Voir plus' : 'Voir moins';
                });
            });
        }

        /**
         * Submit a validation action via AJAX (form POST) and show SweetAlert feedback
         */
        function submitAction(formData) {
            // Show loading
            Swal.fire({ title: 'Traitement...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    Swal.fire({ icon: 'success', title: 'Succes', text: 'Action effectuee avec succes.', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    throw new Error('Erreur serveur');
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Une erreur est survenue. Veuillez reessayer.' });
            });
        }

        // --- FILTRAGE ET RECHERCHE ---
        const searchInput = document.getElementById('searchInput');
        const filterTabs = document.querySelectorAll('.filter-tab');
        const cards = Array.from(document.querySelectorAll('#validationCards > .validation-card-advanced')); 
        const noResults = document.getElementById('noResults');
        const cardsContainer = document.getElementById('validationCards');
        let currentFilter = 'all';

        // Pagination instance (initialized in DOMContentLoaded below, after deferred scripts load)
        let tutoringPagination = null;

        function filterCards() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            let visibleCount = 0;

            // Reset display state before applying a new filter to avoid stale hidden items
            cards.forEach(card => {
                card.classList.remove('pagination-hidden');
                card.style.display = '';
            });

            cards.forEach(card => {
                const haystack = (card.dataset.search || '').toLowerCase(); const matchesSearch = haystack.includes(searchTerm);
                const cardType = (card.dataset.type || '').toLowerCase(); const matchesFilter = currentFilter === 'all' || cardType === currentFilter;
                if (matchesSearch && matchesFilter) {
                    card.classList.remove('filter-hidden');
                    if (!tutoringPagination) card.style.display = '';
                    visibleCount++;
                } else {
                    card.classList.add('filter-hidden');
                    if (!tutoringPagination) card.style.display = 'none';
                }
            });
            if (noResults && cardsContainer) {
                const isEmpty = visibleCount === 0 && cards.length > 0;
                noResults.style.display = isEmpty ? 'block' : 'none';
                cardsContainer.style.display = isEmpty ? 'none' : 'grid';
            }
            // Re-paginate after filter change
            if (tutoringPagination) {
                tutoringPagination.currentPage = 1;
                tutoringPagination.update();
            }
        }

        if (searchInput) { searchInput.addEventListener('input', filterCards); }
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterCards();
            });
        });

        // Initialize pagination after deferred scripts have loaded
        document.addEventListener('DOMContentLoaded', function() {
            if (cardsContainer && document.getElementById('tutoringPagination') && typeof PaginationComponent !== 'undefined') {
                tutoringPagination = new PaginationComponent({
                    itemsSelector: '#validationCards',
                    paginationSelector: '#tutoringPagination',
                    perPage: 9,
                    perPageOptions: [6, 9, 18, 30]
                });
                window.tutoringPagination = tutoringPagination;
            }
        });

        // =============================================
        // CLUB ACTIONS - SweetAlert + AJAX
        // =============================================

        // View club details
        document.querySelectorAll('.btn-swal-club-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const club = JSON.parse(this.dataset.club);
                let members = [];
                try { members = JSON.parse(this.dataset.members || '[]'); } catch(e) {}

                // Validation status badges
                const valAdmin = club.validation_admin;
                const valTuteur = club.validation_tuteur;
                const valFinale = club.validation_finale;

                function statusBadge(label, val) {
                    if (val == 1) return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
                    if (val == -1 || val === '0' || val === 0) return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
                    return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
                }

                const contact = club.mail || club.email || club.contact_mail || club.responsable_mail || '';
                const responsable = [
                    club.responsable_prenom,
                    club.responsable_nom
                ].filter(Boolean).join(' ').trim()
                    || club.responsable
                    || [club.president_prenom, club.president_nom].filter(Boolean).join(' ').trim()
                    || club.president_nom
                    || club.createur_nom
                    || [club.user_prenom, club.user_nom].filter(Boolean).join(' ').trim()
                    || '';
                const tuteur = [club.tuteur_prenom, club.tuteur_nom].filter(Boolean).join(' ').trim()
                    || club.tuteur_nom
                    || club.tuteur
                    || '';
                const dateDepot = club.date_depot || club.created_at || club.date_creation || club.created_on || '';
                const motifRefus = club.motif_refus || club.remarques_refus || '';
                const soutenanceCount = members.filter(m => (m.soutenance == 1 || m.soutenance === '1')).length;
                const logoHtml = club.logo_club
                    ? '<img src="' + esc(club.logo_club) + '" alt="Logo" class="swal-detail-logo" />'
                    : '<div class="swal-detail-logo-placeholder"><i class="fas fa-building"></i></div>';

                let membersHtml = '<div class="swal-empty-state"><i class="fas fa-users"></i> Aucun membre renseigne</div>';
                if (members.length > 0) {
                    membersHtml = '<div class="swal-members-list">' +
                        members.map(m =>
                            ((m.soutenance == 1 || m.soutenance === '1')
                                ? '<div class="swal-member-item">' +
                                    '<div class="swal-member-avatar"><i class="fas fa-user"></i></div>' +
                                    '<div class="swal-member-info">' +
                                    '<span class="swal-member-name">' + esc(m.prenom) + ' ' + esc(m.nom) + '</span>' +
                                    '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
                                        '<span class="swal-member-role">' + esc(m.fonction) + (m.promo ? ' · ' + esc(m.promo) : '') + '</span>' +
                                        '<span class="swal-badge swal-badge-success"><i class="fas fa-graduation-cap"></i> Soutenance</span>' +
                                    '</div>' +
                                    (m.mail ? '<span class="swal-member-role">' + esc(m.mail) + '</span>' : '') +
                                    '</div></div>'
                                : '<div class="swal-member-item">' +
                                    '<div class="swal-member-avatar"><i class="fas fa-user"></i></div>' +
                                    '<div class="swal-member-info">' +
                                    '<span class="swal-member-name">' + esc(m.prenom) + ' ' + esc(m.nom) + '</span>' +
                                    '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
                                        '<span class="swal-member-role">' + esc(m.fonction) + (m.promo ? ' · ' + esc(m.promo) : '') + '</span>' +
                                        '<span class="swal-badge swal-badge-pending"><i class="fas fa-minus-circle"></i> Sans soutenance</span>' +
                                    '</div>' +
                                    (m.mail ? '<span class="swal-member-role">' + esc(m.mail) + '</span>' : '') +
                                    '</div></div>')
                        ).join('') + '</div>';
                }

                Swal.fire({
                    title: esc(club.nom_club || 'Club sans nom'),
                    html:
                        '<div class="swal-detail-content">' +
                        '<div class="swal-detail-hero">' +
                            '<div class="swal-detail-hero-media">' + logoHtml + '</div>' +
                            '<div class="swal-detail-hero-main">' +
                                '<div class="swal-detail-hero-title">' + esc(club.nom_club || 'Club sans nom') + '</div>' +
                                '<div class="swal-detail-hero-sub"><span><i class="fas fa-tag"></i> ' + esc(club.type_club || 'N/A') + '</span></div>' +
                                '<div class="swal-detail-hero-chips"><span class="swal-chip campus"><i class="fas fa-map-marker-alt"></i> ' + esc(club.campus || 'N/A') + '</span></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="swal-detail-grid">' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-tag"></i> Type</div><div class="swal-detail-value">' + esc(club.type_club || '-') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-marker-alt"></i> Campus</div><div class="swal-detail-value"><span class="campus-badge ' + (club.campus || 'calais').toLowerCase() + '">' + esc(club.campus || '-') + '</span></div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-envelope"></i> Contact</div><div class="swal-detail-value">' + esc(contact || 'Non renseigne') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-fingerprint"></i> Club ID</div><div class="swal-detail-value">' + esc(club.club_id || 'N/A') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-user"></i> Responsable</div><div class="swal-detail-value">' + esc(responsable || 'Non renseigne') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-chalkboard-teacher"></i> Tuteur</div><div class="swal-detail-value">' + esc(tuteur || 'Non assigne') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-inbox"></i> Depot</div><div class="swal-detail-value">' + esc(dateDepot || 'N/A') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-layer-group"></i> Statut fiche</div><div class="swal-detail-value">' + esc(club.statut || club.status || 'En attente') + '</div></div>' +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div>' +
                            renderCollapsibleDescription(club.description || '', 'club_' + (club.club_id || 'x')) +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> Etat des validations</div>' +
                            '<div class="swal-badges-row">' +
                                statusBadge('Admin', valAdmin) +
                                statusBadge('Tuteur', valTuteur) +
                                statusBadge('Finale', valFinale) +
                            '</div>' +
                        '</div>' +
                        '<div class="swal-detail-section swal-detail-section-compact">' +
                            '<div class="swal-detail-label"><i class="fas fa-chart-pie"></i> Resume de la fiche</div>' +
                            '<div class="swal-keyline">' +
                                '<span><strong>Membres:</strong> ' + members.length + '</span>' +
                                '<span><strong>Soutenance:</strong> ' + soutenanceCount + '</span>' +
                                '<span><strong>Sans soutenance:</strong> ' + Math.max(0, members.length - soutenanceCount) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-users"></i> Membres (' + members.length + ')</div>' +
                            membersHtml +
                        '</div>' +
                        (motifRefus ? '<div class="swal-detail-section swal-reject-box"><div class="swal-detail-label"><i class="fas fa-comment-alt"></i> Motif de rejet</div><div class="swal-detail-description">' + esc(motifRefus) + '</div></div>' : '') +
                        '</div>',
                    width: 650,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d',
                    customClass: { popup: 'swal-detail-popup' },
                    didOpen: function() { bindDescriptionToggles(); }
                });
            });
        });

        // Approve club
        document.querySelectorAll('.btn-swal-club-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const clubId = this.dataset.id;
                const clubName = this.dataset.name;
                SwalHelper.confirm(
                    'Approuver ce club ?',
                    'Le club "' + clubName + '" sera valide.',
                    'Oui, approuver',
                    'Annuler'
                ).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('club_id', clubId);
                        fd.append('action', 'approve');
                        fd.append('validate_club_admin', '1');
                        submitAction(fd);
                    }
                });
            });
        });

        // Force approve club
        document.querySelectorAll('.btn-swal-club-force').forEach(btn => {
            btn.addEventListener('click', function() {
                const clubId = this.dataset.id;
                const clubName = this.dataset.name;
                SwalHelper.confirm(
                    'Forcer la validation ?',
                    'Le club "' + clubName + '" sera valide immediatement sans attendre le tuteur.',
                    'Oui, forcer',
                    'Annuler'
                ).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('club_id', clubId);
                        fd.append('action', 'force_approve');
                        fd.append('validate_club_admin', '1');
                        submitAction(fd);
                    }
                });
            });
        });

        // Reject club (with motif textarea)
        document.querySelectorAll('.btn-swal-club-reject').forEach(btn => {
            btn.addEventListener('click', function() {
                const clubId = this.dataset.id;
                const clubName = this.dataset.name;
                Swal.fire({
                    title: 'Rejeter le club "' + clubName + '" ?',
                    html: '<p>Expliquez la raison du rejet pour aider le createur a ameliorer sa demande.</p>',
                    input: 'textarea',
                    inputPlaceholder: 'Ex: Description insuffisante, objectifs pas clairs, doublon avec un club existant...',
                    inputAttributes: { 'aria-label': 'Motif du rejet' },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer le rejet',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#d33',
                    inputValidator: value => { if (!value || !value.trim()) return 'Veuillez saisir un motif de rejet.'; }
                }).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('club_id', clubId);
                        fd.append('action', 'reject');
                        fd.append('motif', result.value.trim());
                        fd.append('validate_club_admin', '1');
                        submitAction(fd);
                    }
                });
            });
        });

        // =============================================
        // EVENT ACTIONS - SweetAlert + AJAX
        // =============================================

        // View event details
        document.querySelectorAll('.btn-swal-event-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const ev = JSON.parse(this.dataset.event);

                // --- Fonctions utilitaires (identiques a validation_bde.php) ---
                function formatDate(dateStr) {
                    if (!dateStr) return 'N/A';
                    const d = new Date(dateStr);
                    if (isNaN(d)) return dateStr;
                    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                function formatTime(timeStr) {
                    if (!timeStr) return '?';
                    return String(timeStr).substring(0, 5);
                }
                function formatDatetime(dtStr) {
                    if (!dtStr) return 'N/A';
                    const d = new Date(dtStr);
                    if (isNaN(d)) return dtStr;
                    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
                        + ' a ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                }
                function valBadge(label, val) {
                    if (val == 1) return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
                    if (val === null || val === undefined || val === '') return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
                    return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
                }

                // Logo
                let logoHtml = '<div class="swal-detail-logo-placeholder"><i class="fas fa-calendar-alt"></i></div>';
                if (ev.logo_club) {
                    const lp = ev.logo_club.match(/^https?:\/\//i) ? ev.logo_club : '/' + String(ev.logo_club).replace(/^\/+/, '');
                    logoHtml = '<img src="' + esc(lp) + '" alt="Logo" class="swal-detail-logo" />';
                }

                // Responsable
                let resp = '<span class="swal-muted">N/A</span>';
                if (ev.responsable_prenom) {
                    resp = esc(ev.responsable_prenom + ' ' + (ev.responsable_nom || ''));
                    if (ev.responsable_mail) resp += ' <span style="color:#64748b;">(' + esc(ev.responsable_mail) + ')</span>';
                    if (ev.responsable_promo) resp += ' · ' + esc(ev.responsable_promo);
                }

                // Financement
                let finHtml = '<span class="swal-muted">Non demande</span>';
                if (ev.financement_bde == 1) {
                    finHtml = '<span class="swal-finance-highlight"><i class="fas fa-check-circle"></i> Oui - ' + parseInt(ev.montant || 0) + ' EUR</span>';
                }

                function docStatusBadge(label, available, icon) {
                    const cls = available ? 'swal-doc-badge available' : 'swal-doc-badge missing';
                    const ico = available ? icon : 'fa-times-circle';
                    return '<span class="' + cls + '"><i class="fas ' + ico + '"></i> ' + label + '</span>';
                }

                const hasAnyPartial = (ev.validation_bde == 1 || ev.validation_tuteur == 1 || ev.validation_admin == 1);
                const typeText = (ev.is_event == 1 || ev.type_event === 'event') ? 'Evenement' : 'Activite';
                const statusText = (ev.validation_finale == 1)
                    ? 'Valide'
                    : ((ev.validation_finale == 0 || ev.validation_finale == -1)
                        ? 'Rejete'
                        : (hasAnyPartial ? 'Validation partielle' : 'En attente'));
                const statusClass = (ev.validation_finale == 1)
                    ? 'done'
                    : ((ev.validation_finale == 0 || ev.validation_finale == -1)
                        ? 'rejected'
                        : (hasAnyPartial ? 'partial' : 'pending'));

                const docsStatusHtml = '<div class="swal-doc-status-row">' +
                    docStatusBadge('Document d\'organisation', !!ev.doc_organisation, 'fa-file-alt') +
                    docStatusBadge('Fiche sanitaire', !!ev.fiche_sanitaire, 'fa-file-medical') +
                    docStatusBadge('Affiche', !!ev.affiche, 'fa-image') +
                '</div>';

                let filesHtml = '';
                if (ev.fiche_sanitaire || ev.affiche || ev.doc_organisation) {
                    filesHtml = '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-paperclip"></i> Documents joints</div><div class="swal-files-row">';
                    if (ev.doc_organisation) filesHtml += '<a href="' + esc(ev.doc_organisation) + '" target="_blank" class="swal-file-link"><i class="fas fa-file-alt"></i> Document d&apos;organisation</a>';
                    if (ev.fiche_sanitaire) filesHtml += '<a href="' + esc(ev.fiche_sanitaire) + '" target="_blank" class="swal-file-link"><i class="fas fa-file-medical"></i> Fiche sanitaire</a>';
                    if (ev.affiche) filesHtml += '<a href="' + esc(ev.affiche) + '" target="_blank" class="swal-file-link"><i class="fas fa-image"></i> Affiche</a>';
                    filesHtml += '</div></div>';
                }

                const html = '<div class="swal-detail-content">' +
                    '<div class="swal-detail-hero">' +
                        '<div class="swal-detail-hero-media">' + logoHtml + '</div>' +
                        '<div class="swal-detail-hero-main">' +
                            '<div class="swal-detail-hero-title">' + esc(ev.titre || 'Evenement sans titre') + '</div>' +
                            '<div class="swal-detail-hero-sub">' +
                                '<span><i class="fas fa-building"></i> ' + esc(ev.nom_club || 'Club inconnu') + '</span>' +
                                (ev.responsable_prenom ? '<span><i class="fas fa-user"></i> ' + esc(ev.responsable_prenom + ' ' + (ev.responsable_nom || '')) + '</span>' : '') +
                            '</div>' +
                            '<div class="swal-detail-hero-chips">' +
                                '<span class="swal-chip type"><i class="fas fa-tag"></i> ' + typeText + '</span>' +
                                '<span class="swal-chip campus"><i class="fas fa-map-marker-alt"></i> ' + esc(ev.campus || 'N/A') + '</span>' +
                                '<span class="swal-chip status ' + statusClass + '"><i class="fas fa-circle"></i> ' + statusText + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="swal-detail-grid">' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-building"></i> Club</div><div class="swal-detail-value">' + esc(ev.nom_club || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-tag"></i> Type</div><div class="swal-detail-value">' + typeText + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-user"></i> Responsable</div><div class="swal-detail-value">' + resp + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-calendar"></i> Date</div><div class="swal-detail-value">' + formatDate(ev.date_ev) + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-clock"></i> Horaires</div><div class="swal-detail-value">' + formatTime(ev.horaire_debut) + ' - ' + formatTime(ev.horaire_fin) + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-marker-alt"></i> Campus</div><div class="swal-detail-value"><span class="campus-badge ' + (ev.campus || 'calais').toLowerCase() + '">' + esc(ev.campus || 'N/A') + '</span></div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-pin"></i> Lieu</div><div class="swal-detail-value">' + esc(ev.lieu || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-euro-sign"></i> Financement BDE</div><div class="swal-detail-value">' + finHtml + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-inbox"></i> Depot</div><div class="swal-detail-value">' + formatDatetime(ev.date_depot) + '</div></div>' +
                    '</div>' +
                    (ev.description ? '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div>' + renderCollapsibleDescription(ev.description, 'event_' + (ev.event_id || 'x')) + '</div>' : '') +
                    '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-file-circle-check"></i> Disponibilite des pieces</div>' + docsStatusHtml + '</div>' +
                    filesHtml +
                    '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> Etat des validations</div><div class="swal-badges-row">' +
                        valBadge('BDE', ev.validation_bde) + valBadge('Tuteur', ev.validation_tuteur) + valBadge('Admin', ev.validation_admin) +
                    '</div></div>' +
                    (ev.motif_refus ? '<div class="swal-detail-section swal-reject-box"><div class="swal-detail-label"><i class="fas fa-comment-alt"></i> Motif de rejet</div><div class="swal-detail-description">' + esc(ev.motif_refus) + '</div></div>' : '') +
                '</div>';

                Swal.fire({
                    title: '<i class="fas fa-calendar-alt" style="color:var(--color-primary, #0066cc);"></i> ' + esc(ev.titre || 'Evenement sans titre'),
                    html: html,
                    width: 700,
                    showCloseButton: true,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d',
                    customClass: { popup: 'swal-detail-popup' },
                    didOpen: function() { bindDescriptionToggles(); }
                });
            });
        });

        // Approve event
        document.querySelectorAll('.btn-swal-event-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const eventId = this.dataset.id;
                const eventName = this.dataset.name;
                const validateField = this.dataset.validateField;
                SwalHelper.confirm(
                    'Approuver cet evenement ?',
                    'L\'evenement "' + eventName + '" sera valide.',
                    'Oui, approuver',
                    'Annuler'
                ).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('event_id', eventId);
                        fd.append('action', 'approve');
                        fd.append(validateField, '1');
                        submitAction(fd);
                    }
                });
            });
        });

        // Force approve event
        document.querySelectorAll('.btn-swal-event-force').forEach(btn => {
            btn.addEventListener('click', function() {
                const eventId = this.dataset.id;
                const eventName = this.dataset.name;
                SwalHelper.confirm(
                    'Forcer la validation ?',
                    'L\'evenement "' + eventName + '" sera valide immediatement sans attendre le tuteur.',
                    'Oui, forcer',
                    'Annuler'
                ).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('event_id', eventId);
                        fd.append('action', 'force_approve');
                        fd.append('validate_event_admin', '1');
                        submitAction(fd);
                    }
                });
            });
        });

        // Reject event (with motif textarea)
        document.querySelectorAll('.btn-swal-event-reject').forEach(btn => {
            btn.addEventListener('click', function() {
                const eventId = this.dataset.id;
                const eventName = this.dataset.name;
                const validateField = this.dataset.validateField;
                Swal.fire({
                    title: 'Rejeter l\'evenement "' + eventName + '" ?',
                    html: '<p>Expliquez la raison du rejet pour aider l\'organisateur a ameliorer sa demande.</p>',
                    input: 'textarea',
                    inputPlaceholder: 'Ex: Date non disponible, lieu inapproprie, informations manquantes...',
                    inputAttributes: { 'aria-label': 'Motif du rejet' },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer le rejet',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#d33',
                    inputValidator: value => { if (!value || !value.trim()) return 'Veuillez saisir un motif de rejet.'; }
                }).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('event_id', eventId);
                        fd.append('action', 'reject');
                        fd.append('motif', result.value.trim());
                        fd.append(validateField, '1');
                        submitAction(fd);
                    }
                });
            });
        });

    })();
    </script>
</body>
</html>




