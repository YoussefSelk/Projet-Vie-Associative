<?php
/**
 * Liste des événements en attente de validation (BDE)
 *
 * Interface avancée pour le BDE afin de valider les événements :
 * - Dashboard de stats (en attente, approuvés, rejetés)
 * - Recherche & filtrage par statut/campus
 * - Cartes détaillées avec infos complètes
 * - Modal de détails avec description, budget, fichiers
 * - Modal de rejet avec champ motif obligatoire
 * - Section événements rejetés avec suppression
 *
 * Variables attendues :
 * - $events : Liste des événements en attente
 * - $rejected_events : Liste des événements rejetés
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Validation
 */
$pageTitle = 'Événements en attente - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'validation', 'events', 'search', 'pagination'];
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
                <h1><i class="fas fa-calendar-check"></i> Validation des événements</h1>
                <p class="subtitle">Examinez et validez les événements soumis par les clubs</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
            <?php endif; ?>
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-content">
                        <h3><?= count($events ?? []) ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($events ?? [], fn($e) => ($e['validation_bde'] ?? null) == 1)) ?></h3>
                        <p>Approuvés BDE</p>
                    </div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-content">
                        <h3><?= count($rejected_events ?? []) ?></h3>
                        <p>Rejetés</p>
                    </div>
                </div>
            </div>
            <!-- Search & Filter Section -->
            <div class="search-section">
                <?php
                    $campus_values = [];
                    foreach (($events ?? []) as $evf) {
                        $camp = trim((string)($evf['campus'] ?? ''));
                        if ($camp !== '') $campus_values[$camp] = true;
                    }
                    foreach (($rejected_events ?? []) as $evf) {
                        $camp = trim((string)($evf['campus'] ?? ''));
                        if ($camp !== '') $campus_values[$camp] = true;
                    }
                    $campus_options = array_keys($campus_values);
                    sort($campus_options, SORT_NATURAL | SORT_FLAG_CASE);
                    $all_events_count = count($events ?? []) + count($rejected_events ?? []);
                ?>
                <div class="search-row" style="display:grid; grid-template-columns: 1fr 1fr auto auto; gap:12px; align-items:end;">
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <label for="eventCampusFilter" style="font-weight:600; color:#334155;">Filtrer par campus :</label>
                        <select id="eventCampusFilter" class="form-control">
                            <option value="all">Tous les campus</option>
                            <?php foreach ($campus_options as $campus_option): ?>
                                <option value="<?= strtolower(htmlspecialchars($campus_option, ENT_QUOTES, 'UTF-8')) ?>"><?= htmlspecialchars($campus_option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <label for="eventStatusFilter" style="font-weight:600; color:#334155;">Filtrer par état de validation :</label>
                        <select id="eventStatusFilter" class="form-control">
                            <option value="all">Toutes les fiches</option>
                            <option value="partial">Fiches en cours de traitement</option>
                            <option value="validated">Fiches validées</option>
                            <option value="rejected">Fiches refusées</option>
                        </select>
                    </div>
                    <button type="button" id="eventFiltersReset" class="btn btn-outline" style="height:44px;">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <div id="eventFilterCount" class="campus-badge" style="justify-self:end; min-width:92px; text-align:center; font-weight:700;">
                        <?= (int)$all_events_count ?> fiche(s)
                    </div>
                </div>
            </div>
            <!-- Pending Events -->
            <?php if (empty($events)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state-advanced">
                            <div class="empty-icon"><i class="fas fa-check"></i></div>
                            <h3>Aucun événement en attente</h3>
                            <p>Tous les événements ont été traités. Revenez plus tard.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="validation-cards-container" id="validationCards">
                    <?php foreach ($events as $event): 
                        // Determine validation status
                        $has_bde = ($event['validation_bde'] ?? null) == 1;
                        $has_tuteur = ($event['validation_tuteur'] ?? null) == 1;
                        $has_admin = ($event['validation_admin'] ?? null) == 1;
                        $is_partial = $has_bde || $has_tuteur || $has_admin;
                        $is_fully_validated = isset($event['validation_finale']) && ((int)$event['validation_finale'] === 1);
                        $is_rejected = isset($event['validation_finale']) && (((int)$event['validation_finale'] === 0) || ((int)$event['validation_finale'] === -1));
                        $is_actionable = !$is_fully_validated && !$is_rejected;
                        $filter_type = $is_partial ? 'partial' : 'pending';
                        $status_type = 'pending';
                        if ($is_fully_validated) {
                            $status_type = 'validated';
                        } elseif ($is_rejected) {
                            $status_type = 'rejected';
                        } elseif ($is_partial) {
                            $status_type = 'partial';
                        }
                        $is_event_type = false;
                        if (isset($event['is_event'])) {
                            $is_event_type = ((int)$event['is_event'] === 1);
                        } elseif (!empty($event['type_event'])) {
                            $is_event_type = (mb_strtolower((string)$event['type_event'], 'UTF-8') === 'event');
                        }
                        
                        $date_depot = !empty($event['date_depot']) ? date('d/m/Y à H:i', strtotime($event['date_depot'])) : 'N/A';
                        $date_ev = !empty($event['date_ev']) ? date('d/m/Y', strtotime($event['date_ev'])) : 'N/A';
                        $horaire = (!empty($event['horaire_debut']) ? substr($event['horaire_debut'], 0, 5) : '?') . ' - ' . (!empty($event['horaire_fin']) ? substr($event['horaire_fin'], 0, 5) : '?');
                    ?>
                        <div class="validation-card-advanced event-card" 
                             data-type="<?= $filter_type ?>" 
                             data-status="<?= $status_type ?>"
                             data-campus="<?= strtolower(htmlspecialchars((string)($event['campus'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>"
                             data-search="<?= strtolower(htmlspecialchars(($event['titre'] ?? '') . ' ' . ($event['nom_club'] ?? '') . ' ' . ($event['campus'] ?? '') . ' ' . ($event['lieu'] ?? ''))) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row" style="display: flex; align-items: center; gap: 15px;">
                                        <?php if ($logoPath): ?>
                                            <img src="<?= htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8') ?>" 
                                                alt="Logo" 
                                                style="width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: #f8fafc; padding: 3px; border: 1px solid #e2e8f0;">
                                        <?php else: ?>
                                            <div class="card-type-icon" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                                <?php if (!empty($event['responsable_prenom'])): ?>
                                                    &mdash; <i class="fas fa-user"></i> <?= htmlspecialchars($event['responsable_prenom'] . ' ' . $event['responsable_nom']) ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <span class="badge event-kind-badge <?= $is_event_type ? 'event' : 'activity' ?>">
                                            <i class="fas <?= $is_event_type ? 'fa-calendar-check' : 'fa-shapes' ?>"></i>
                                            <?= $is_event_type ? 'Événement' : 'Activité' ?>
                                        </span>
                                        <?php if ($is_fully_validated): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> <?= !empty(trim((string)($event['motif_forcage'] ?? ''))) ? 'Approuvé par forçage' : 'Validé' ?></span>
                                        <?php elseif ($is_rejected): ?>
                                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Refusé</span>
                                        <?php elseif ($is_partial): ?>
                                            <span class="badge badge-info"><i class="fas fa-spinner"></i> Validation partielle</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= $date_ev ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?= $horaire ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>"><?= htmlspecialchars($event['campus'] ?? 'N/A') ?></span>
                                        </div>
                                        <?php if (!empty($event['lieu'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-map-pin"></i>
                                            <span><?= htmlspecialchars($event['lieu']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (($event['financement_bde'] ?? 0) == 1): ?>
                                        <div class="meta-item finance-highlight">
                                            <i class="fas fa-euro-sign"></i>
                                            <span><?= intval($event['montant'] ?? 0) ?> €</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($event['description']) ?></p>
                                    <?php endif; ?>
                                    <!-- Validation Progress -->
                                    <div class="validation-badges-row">
                                        <span class="badge <?= $has_bde ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $has_bde ? 'fa-check' : 'fa-times' ?>"></i> BDE
                                        </span>
                                        <span class="badge <?= $has_tuteur ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $has_tuteur ? 'fa-check' : 'fa-times' ?>"></i> Tuteur
                                        </span>
                                        <span class="badge <?= $has_admin ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $has_admin ? 'fa-check' : 'fa-times' ?>"></i> Admin
                                        </span>
                                        <span class="badge doc-badge <?= !empty($event['doc_organisation']) ? 'badge-info' : 'badge-light is-missing' ?>">
                                            <i class="fas <?= !empty($event['doc_organisation']) ? 'fa-image' : 'fa-times' ?>"></i>
                                            Document d'organisation
                                        </span>
                                        <span class="badge doc-badge <?= !empty($event['fiche_sanitaire']) ? 'badge-info' : 'badge-light is-missing' ?>">
                                            <i class="fas <?= !empty($event['fiche_sanitaire']) ? 'fa-file-medical' : 'fa-times' ?>"></i>
                                            Fiche sanitaire
                                        </span>
                                        <span class="badge doc-badge <?= !empty($event['affiche']) ? 'badge-info' : 'badge-light is-missing' ?>">
                                            <i class="fas <?= !empty($event['affiche']) ? 'fa-image' : 'fa-times' ?>"></i>
                                            Affiche
                                        </span>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details" onclick='openEventModal(<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>)'>
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <?php if ($is_actionable): ?>
                                        <form method="POST" class="form-approve-event">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="remarques" value="">
                                            <input type="hidden" name="validate_event" value="1">
                                            <button type="submit" class="btn-approve">
                                                <i class="fas fa-check"></i> Approuver
                                            </button>
                                        </form>
                                        <?php if ($is_admin): ?>
                                        <form method="POST" class="form-force-event">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <input type="hidden" name="action" value="force_approve">
                                            <input type="hidden" name="validate_event" value="1">
                                            <input type="hidden" name="motif_forcage" value="">
                                            <button type="submit" class="btn-force" title="Valider immédiatement (Admin)">
                                                <i class="fas fa-bolt"></i> Forcer
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn-reject" onclick='openEventModalReject(<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>)'>
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="pendingEventsPagination" class="pagination-wrapper"></div>
                
                <div class="empty-state-advanced" id="noResults">
                    <div class="empty-icon empty-icon-search">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun événement ne correspond à votre recherche.</p>
                </div>
            <?php endif; ?>

            <!-- Rejected Events Section -->
            <?php if (!empty($rejected_events)): ?>
            <div class="card rejected-events-card">
                <div class="card-header">
                    <h3><i class="fas fa-times-circle"></i> Événements rejetés (<?= count($rejected_events) ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="validation-cards-container" id="rejectedEventsCards">
                        <?php foreach ($rejected_events as $event): ?>
                            <div class="validation-card-advanced event-card rejected-event" data-status="rejected" data-campus="<?= strtolower(htmlspecialchars((string)($event['campus'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>" data-search="<?= strtolower(htmlspecialchars(($event['titre'] ?? '') . ' ' . ($event['nom_club'] ?? '') . ' ' . ($event['campus'] ?? '') . ' ' . ($event['lieu'] ?? '') . ' ' . ($event['motif_refus'] ?? ''))) ?>">
                                <div class="card-main">
                                    <div class="card-content">
                                        <div class="card-title-row">
                                            <div class="card-type-icon">
                                                <i class="fas fa-calendar-times"></i>
                                            </div>
                                            <div class="card-title-info">
                                                <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                                <span class="card-subtitle">
                                                    <i class="fas fa-building"></i> <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                                </span>
                                            </div>
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> Rejeté</span>
                                        </div>
                                        <?php if (!empty($event['motif_refus'])): ?>
                                        <div class="reject-reason-box mt-10">
                                            <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                                            <p><?= htmlspecialchars($event['motif_refus']) ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-actions">
                                        <form method="POST" class="form-delete-event" data-event-title="<?= htmlspecialchars($event['titre'] ?? '') ?>">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <input type="hidden" name="delete_event" value="1">
                                            <button type="submit" class="btn-reject">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="rejectedEventsPagination" class="pagination-wrapper"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Hidden forms for SweetAlert modal actions -->
    <form method="POST" id="swalApproveForm" class="hidden-form">
        <?= Security::csrfField() ?>
        <input type="hidden" name="event_id" id="swalApproveEventId" value="">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="remarques" id="swalApproveRemarques" value="">
        <input type="hidden" name="validate_event" value="1">
    </form>
    <form method="POST" id="swalRejectForm" class="hidden-form">
        <?= Security::csrfField() ?>
        <input type="hidden" name="event_id" id="swalRejectEventId" value="">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="motif" id="swalRejectMotif" value="">
        <input type="hidden" name="validate_event" value="1">
    </form>
    <form method="POST" id="swalForceForm" class="hidden-form">
        <?= Security::csrfField() ?>
        <input type="hidden" name="event_id" id="swalForceEventId" value="">
        <input type="hidden" name="action" value="force_approve">
        <input type="hidden" name="validate_event" value="1">
        <input type="hidden" name="motif_forcage" id="swalForceEventMotif" value="">
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        function esc(str) {
            return String(str || '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            var d = new Date(dateStr);
            if (isNaN(d)) return dateStr;
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function formatTime(timeStr) {
            if (!timeStr) return '?';
            return timeStr.substring(0, 5);
        }

        function formatDatetime(dtStr) {
            if (!dtStr) return 'N/A';
            var d = new Date(dtStr);
            if (isNaN(d)) return dtStr;
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
                 + ' à ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }

        function valBadge(label, val) {
            if (val == 1) return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
            if (val === null || val === undefined || val === '') return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
            return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
        }

        function docStatusBadge(label, available, icon) {
            var cls = available ? 'swal-doc-badge available' : 'swal-doc-badge missing';
            var ico = available ? icon : 'fa-times-circle';
            return '<span class="' + cls + '"><i class="fas ' + ico + '"></i> ' + label + '</span>';
        }

        // ---- Search & Filter ----
        var campusFilter = document.getElementById('eventCampusFilter');
        var statusFilter = document.getElementById('eventStatusFilter');
        var resetFiltersBtn = document.getElementById('eventFiltersReset');
        var filterCountBadge = document.getElementById('eventFilterCount');
        var cards = document.querySelectorAll('#validationCards > .validation-card-advanced');
        var noResults = document.getElementById('noResults');
        var cardsContainer = document.getElementById('validationCards');
        var paginationWrapper = document.getElementById('pendingEventsPagination');
        var rejectedCardsContainer = document.getElementById('rejectedEventsCards');
        var rejectedCards = document.querySelectorAll('#rejectedEventsCards > .validation-card-advanced');
        var rejectedEventsSection = document.querySelector('.rejected-events-card');
        var rejectedPaginationWrapper = document.getElementById('rejectedEventsPagination');
        // Simple mode: no pagination, direct filtering only.
        var currentCampusFilter = 'all';
        var currentStatusFilter = 'all';
        if (paginationWrapper) {
            paginationWrapper.style.display = 'none';
        }
        if (rejectedPaginationWrapper) {
            rejectedPaginationWrapper.style.display = 'none';
        }

        function filterCards() {
            var visibleCount = 0;
            var rejectedVisibleCount = 0;
            var totalVisibleCount = 0;
            currentCampusFilter = campusFilter ? (campusFilter.value || 'all') : 'all';
            currentStatusFilter = statusFilter ? (statusFilter.value || 'all') : 'all';
            var showRejectedOnly = currentStatusFilter === 'rejected';
            var showPendingArea = !showRejectedOnly;

            if (cardsContainer) cardsContainer.style.display = showPendingArea ? '' : 'none';
            if (paginationWrapper) paginationWrapper.style.display = showPendingArea ? '' : 'none';

            cards.forEach(function(card) {
                var status = card.dataset.status || 'pending';
                var campus = (card.dataset.campus || '').toLowerCase();
                var matchesCampus = currentCampusFilter === 'all' || campus === currentCampusFilter;
                var matchesStatus = (currentStatusFilter === 'all')
                    || (currentStatusFilter === 'pending' && status === 'pending')
                    || (currentStatusFilter === 'partial' && status === 'partial')
                    || (currentStatusFilter === 'validated' && status === 'validated');

                if (showPendingArea && matchesCampus && matchesStatus) {
                    card.style.display = '';
                    visibleCount++;
                    totalVisibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (rejectedCards && rejectedCards.length) {
                rejectedCards.forEach(function(card) {
                    var campusRejected = (card.dataset.campus || '').toLowerCase();
                    var matchesCampusRejected = currentCampusFilter === 'all' || campusRejected === currentCampusFilter;
                    if ((currentStatusFilter === 'all' || currentStatusFilter === 'rejected') && matchesCampusRejected) {
                        card.style.display = '';
                        rejectedVisibleCount++;
                        totalVisibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (rejectedEventsSection) {
                    rejectedEventsSection.style.display = ((currentStatusFilter === 'all' || currentStatusFilter === 'rejected') && rejectedVisibleCount > 0) ? '' : 'none';
                }
            } else if (rejectedEventsSection) {
                rejectedEventsSection.style.display = 'none';
            }

            if (filterCountBadge) {
                filterCountBadge.textContent = totalVisibleCount + ' fiche(s)';
            }

            if (noResults && cardsContainer) {
                if (showPendingArea) {
                    if (visibleCount === 0 && cards.length > 0) {
                        noResults.style.display = '';
                        cardsContainer.style.display = 'none';
                        if (paginationWrapper) paginationWrapper.style.display = 'none';
                    } else {
                        noResults.style.display = 'none';
                        cardsContainer.style.display = '';
                        if (paginationWrapper) paginationWrapper.style.display = '';
                    }
                } else {
                    if (rejectedVisibleCount === 0) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                    cardsContainer.style.display = 'none';
                    if (paginationWrapper) paginationWrapper.style.display = 'none';
                }
            }
        }

        if (campusFilter) campusFilter.addEventListener('change', filterCards);
        if (statusFilter) statusFilter.addEventListener('change', filterCards);
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function() {
                if (campusFilter) campusFilter.value = 'all';
                if (statusFilter) statusFilter.value = 'all';
                filterCards();
            });
        }

        filterCards();

        // ---- SweetAlert2 Detail Modal ----
        function buildDetailHtml(ev) {
            var resp = '<span class="swal-muted">N/A</span>';
            if (ev.responsable_prenom) {
                resp = esc(ev.responsable_prenom + ' ' + (ev.responsable_nom || ''));
                if (ev.responsable_mail) resp += ' <span style="color:#64748b;">(' + esc(ev.responsable_mail) + ')</span>';
                if (ev.responsable_promo) resp += ' · ' + esc(ev.responsable_promo);
            }

            // Logo for modal (keep external URLs or prefix with /)
            var logoHtml = '<div class="swal-detail-logo-placeholder"><i class="fas fa-calendar-alt"></i></div>';
            if (ev.logo_club) {
                var lp = ev.logo_club.match(/^https?:\/\//i) ? ev.logo_club : '/' + String(ev.logo_club).replace(/^\/+/, '');
                logoHtml = '<img src="' + esc(lp) + '" alt="Logo" class="swal-detail-logo" />';
            }

            var finHtml = '<span class="swal-muted">Non demandé</span>';
            if (ev.financement_bde == 1) {
                finHtml = '<span class="swal-finance-highlight"><i class="fas fa-check-circle"></i> Oui — ' + parseInt(ev.montant || 0) + ' €</span>';
            }

            var typeText = (ev.is_event == 1 || ev.type_event === 'event') ? 'Événement' : 'Activité';
            var statusText = (ev.validation_finale == 1)
                ? 'Validé'
                : ((ev.validation_finale == 0)
                    ? 'Rejeté'
                    : ((ev.validation_bde == 1 || ev.validation_tuteur == 1 || ev.validation_admin == 1)
                        ? 'Validation partielle'
                        : 'En attente'));
            var statusClass = (ev.validation_finale == 1)
                ? 'done'
                : ((ev.validation_finale == 0)
                    ? 'rejected'
                    : ((ev.validation_bde == 1 || ev.validation_tuteur == 1 || ev.validation_admin == 1)
                        ? 'partial'
                        : 'pending'));

            var docsStatusHtml = '<div class="swal-doc-status-row">' +
                docStatusBadge('Document d\'organisation', !!ev.doc_organisation, 'fa-file-alt') +
                docStatusBadge('Fiche sanitaire', !!ev.fiche_sanitaire, 'fa-file-medical') +
                docStatusBadge('Affiche', !!ev.affiche, 'fa-image') +
            '</div>';

            var filesHtml = '';
            if (ev.fiche_sanitaire || ev.affiche || ev.doc_organisation) {
                filesHtml = '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-paperclip"></i> Documents joints</div><div class="swal-files-row">';

                // Document d'organisation
                if (ev.doc_organisation) {
                    // Use HTML entity for the apostrophe to avoid breaking the JS string
                    filesHtml += '<a href="' + esc(ev.doc_organisation) + '" target="_blank" class="swal-file-link" style="display:inline-block;margin-right:10px;"><i class="fas fa-file-alt"></i> Document d&apos;organisation</a>';
                }

                // Fiche sanitaire
                if (ev.fiche_sanitaire) {
                    filesHtml += '<a href="' + esc(ev.fiche_sanitaire) + '" target="_blank" class="swal-file-link" style="display:inline-block;margin-right:10px;"><i class="fas fa-file-medical"></i> Fiche sanitaire</a>';
                }

                // Lien vers l'affiche (après la preview)
                if (ev.affiche) {
                    filesHtml += '<a href="' + esc(ev.affiche) + '" target="_blank" class="swal-file-link"><i class="fas fa-image"></i> Affiche</a>';
                }

                filesHtml += '</div></div>';
            }


            return '<div class="swal-detail-content">' +
                 '<div class="swal-detail-hero">' +
                     '<div class="swal-detail-hero-media">' + logoHtml + '</div>' +
                     '<div class="swal-detail-hero-main">' +
                         '<div class="swal-detail-hero-title">' + esc(ev.titre || 'Sans titre') + '</div>' +
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
                     '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-inbox"></i> Dépôt</div><div class="swal-detail-value">' + formatDatetime(ev.date_depot) + '</div></div>' +
                '</div>' +
                (ev.description ? '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div><div class="swal-detail-description">' + esc(ev.description) + '</div></div>' : '') +
                '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-file-circle-check"></i> Disponibilité des pièces</div>' + docsStatusHtml + '</div>' +
                filesHtml +
                '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> État des validations</div><div class="swal-badges-row">' +
                    valBadge('BDE', ev.validation_bde) + valBadge('Tuteur', ev.validation_tuteur) + valBadge('Admin', ev.validation_admin) +
                '</div></div>' +
            '</div>';
        }

        window.openEventModal = function(ev) {
            var isAdmin = <?= json_encode($is_admin) ?>;
            var isFinalValidated = parseInt(ev.validation_finale, 10) === 1;
            var isRejected = parseInt(ev.validation_finale, 10) === 0 || parseInt(ev.validation_finale, 10) === -1;
            var isLocked = isFinalValidated || isRejected;
            var swalOptions = {
                title: '<i class="fas fa-calendar-alt" style="color:#3b82f6;"></i> ' + esc(ev.titre || 'Sans titre'),
                html: buildDetailHtml(ev),
                width: 700,
                showCloseButton: true,
                showCancelButton: !isLocked,
                showDenyButton: !isLocked,
                confirmButtonText: isLocked ? 'Fermer' : '<i class="fas fa-check"></i> Approuver',
                denyButtonText: '<i class="fas fa-times"></i> Rejeter',
                cancelButtonText: isLocked ? '' : 'Fermer',
                confirmButtonColor: '#28a745',
                denyButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                customClass: { popup: 'swal-detail-popup' },
                reverseButtons: false,
                footer: (!isLocked && isAdmin) ? '<button id="swalForceBtn" class="btn-force" style="background:#d97706;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:0.9rem;font-weight:600;"><i class="fas fa-bolt"></i> Forcer la validation</button>' : ''
            };
            Swal.fire(swalOptions).then(function(result) {
                if (isLocked) {
                    return;
                }
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Commentaire (optionnel)',
                        text: 'Vous pouvez ajouter un commentaire, par exemple pour alerter sur les règles de sécurité.',
                        input: 'textarea',
                        inputPlaceholder: 'Commentaire pour le club (optionnel)…',
                        inputAttributes: { maxlength: 1000, 'aria-label': 'Commentaire de validation' },
                        showCancelButton: true,
                        confirmButtonText: 'Valider',
                        cancelButtonText: 'Annuler'
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            document.getElementById('swalApproveEventId').value = ev.event_id;
                            document.getElementById('swalApproveRemarques').value = (r.value || '').trim();
                            document.getElementById('swalApproveForm').submit();
                        }
                    });
                } else if (result.isDenied) {
                    openRejectSwal(ev);
                }
            });
            // Bind force button inside modal
            if (!isLocked && isAdmin) {
                var forceBtn = document.getElementById('swalForceBtn');
                if (forceBtn) {
                    forceBtn.addEventListener('click', function() {
                        Swal.fire({
                            title: 'Forcer la validation ?',
                            html: '<p>L\'événement <strong>«\u00a0' + esc(ev.titre) + '\u00a0»</strong> sera validé immédiatement (Admin + BDE + Tuteur).</p><p style="color:#d97706;font-size:0.9em;margin-top:8px;"><i class="fas fa-exclamation-triangle"></i> Cette action contourne le circuit de validation normal.</p>',
                            input: 'textarea',
                            inputLabel: 'Motif du forçage',
                            inputPlaceholder: 'Expliquez pourquoi cette validation est forcée. Ce message sera visible par l’étudiant.',
                            inputAttributes: { maxlength: 1000, 'aria-label': 'Motif du forçage' },
                            inputValidator: function(value) {
                                if (!value || !value.trim()) return 'Le motif du forçage est obligatoire.';
                                if (value.trim().length > 1000) return 'Le motif ne peut pas dépasser 1000 caractères.';
                            },
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-bolt"></i> Oui, forcer',
                            cancelButtonText: 'Annuler',
                            confirmButtonColor: '#d97706',
                            cancelButtonColor: '#6c757d'
                        }).then(function(r) {
                            if (r.isConfirmed) {
                                document.getElementById('swalForceEventId').value = ev.event_id;
                                document.getElementById('swalForceEventMotif').value = (r.value || '').trim();
                                document.getElementById('swalForceForm').submit();
                            }
                        });
                    });
                }
            }
        };

        // ---- SweetAlert2 Reject Modal ----
        function openRejectSwal(ev) {
            Swal.fire({
                title: 'Rejeter l\'événement',
                html: '<p style="margin-bottom:8px;">Rejeter <strong>&laquo; ' + esc(ev.titre) + ' &raquo;</strong> ?</p>',
                input: 'textarea',
                inputLabel: 'Motif du rejet (obligatoire)',
                inputPlaceholder: 'Ex: Date non disponible, lieu inapproprié, fiche sanitaire manquante...',
                inputAttributes: { 'aria-label': 'Motif du rejet' },
                inputValidator: function(value) {
                    if (!value || !value.trim()) return 'Vous devez renseigner un motif de rejet.';
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-times"></i> Confirmer le rejet',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                focusCancel: true,
                customClass: { popup: 'swal-detail-popup' }
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('swalRejectEventId').value = ev.event_id;
                    document.getElementById('swalRejectMotif').value = result.value;
                    document.getElementById('swalRejectForm').submit();
                }
            });
        }

        window.openEventModalReject = function(ev) {
            openRejectSwal(ev);
        };

        // ---- Card-level approve (SweetAlert confirm) ----
        document.querySelectorAll('.form-approve-event').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Récupère le titre de l'événement depuis la carte parente
                var card = form.closest('.validation-card-advanced');
                var titleEl = card ? card.querySelector('.card-title-info h3') : null;
                var evTitle = titleEl ? titleEl.textContent.trim() : 'cet événement';
                evTitle = esc(evTitle);

                Swal.fire({
                    title: 'Approuver « ' + evTitle + ' » ?',
                    text: 'L\'événement sera validé. Vous pouvez ajouter un commentaire, par exemple pour alerter sur les règles de sécurité (optionnel).',
                    input: 'textarea',
                    inputPlaceholder: 'Commentaire pour le club (optionnel)…',
                    inputAttributes: { maxlength: 1000, 'aria-label': 'Commentaire de validation' },
                    showCancelButton: true,
                    confirmButtonText: 'Oui, approuver',
                    cancelButtonText: 'Annuler'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var remInput = form.querySelector('input[name="remarques"]');
                        if (remInput) remInput.value = (result.value || '').trim();
                        form.submit();
                    }
                });
            });
        });

        // ---- Card-level force approve (Admin only, SweetAlert confirm) ----
        document.querySelectorAll('.form-force-event').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Forcer la validation ?',
                    html: '<p>L\'événement sera <strong>validé immédiatement</strong> (Admin + BDE + Tuteur).</p><p style="color:#d97706;font-size:0.9em;"><i class="fas fa-exclamation-triangle"></i> Cette action contourne le circuit de validation normal.</p>',
                    input: 'textarea',
                    inputLabel: 'Motif du forçage',
                    inputPlaceholder: 'Expliquez pourquoi cette validation est forcée. Ce message sera visible par l’étudiant.',
                    inputAttributes: { maxlength: 1000, 'aria-label': 'Motif du forçage' },
                    inputValidator: function(value) {
                        if (!value || !value.trim()) return 'Le motif du forçage est obligatoire.';
                        if (value.trim().length > 1000) return 'Le motif ne peut pas dépasser 1000 caractères.';
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-bolt"></i> Oui, forcer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6c757d'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var motifInput = form.querySelector('input[name="motif_forcage"]');
                        if (motifInput) motifInput.value = (result.value || '').trim();
                        form.submit();
                    }
                });
            });
        });

        // ---- Delete rejected event (SweetAlert confirm) ----
        document.querySelectorAll('.form-delete-event').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var title = form.dataset.eventTitle || 'cet événement';
                SwalHelper.confirmDelete('"' + title + '"')
                    .then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
            });
        });

    });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>


