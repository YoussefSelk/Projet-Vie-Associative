<?php
/**
 * Liste des evenements en attente de validation (BDE)
 * 
 * Interface avancee pour le BDE afin de valider les evenements :
 * - Dashboard de stats (en attente, approuves, rejetes)
 * - Recherche & filtrage par statut/campus
 * - Cartes detaillees avec infos completes
 * - Modal de details avec description, budget, fichiers
 * - Modal de rejet avec champ motif obligatoire
 * - Section evenements rejetes avec suppression
 * 
 * Variables attendues :
 * - $events : Liste des evenements en attente
 * - $rejected_events : Liste des evenements rejetes
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Validation
 */
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'validation', 'events', 'search'];
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
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-calendar-check"></i> Validation des événements</h1>
                <p class="subtitle">Examinez et validez les événements soumis par les clubs</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
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
                <div class="stat-card clubs" style="border-left-color: #ef4444;">
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-content">
                        <h3><?= count($rejected_events ?? []) ?></h3>
                        <p>Rejetés</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-section">
                <div class="search-row">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher par titre, club, campus, lieu..." autocomplete="off">
                    </div>
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">
                            <i class="fas fa-layer-group"></i> Tout
                            <span class="count"><?= count($events ?? []) ?></span>
                        </button>
                        <button class="filter-tab" data-filter="pending">
                            <i class="fas fa-clock"></i> En attente
                        </button>
                        <button class="filter-tab" data-filter="partial">
                            <i class="fas fa-check"></i> Partiellement validés
                        </button>
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
                        $filter_type = $is_partial ? 'partial' : 'pending';
                        
                        $date_depot = !empty($event['date_depot']) ? date('d/m/Y à H:i', strtotime($event['date_depot'])) : 'N/A';
                        $date_ev = !empty($event['date_ev']) ? date('d/m/Y', strtotime($event['date_ev'])) : 'N/A';
                        $horaire = (!empty($event['horaire_debut']) ? substr($event['horaire_debut'], 0, 5) : '?') . ' - ' . (!empty($event['horaire_fin']) ? substr($event['horaire_fin'], 0, 5) : '?');
                    ?>
                        <div class="validation-card-advanced event-card" 
                             data-type="<?= $filter_type ?>" 
                             data-search="<?= strtolower(htmlspecialchars(($event['titre'] ?? '') . ' ' . ($event['nom_club'] ?? '') . ' ' . ($event['campus'] ?? '') . ' ' . ($event['lieu'] ?? ''))) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row">
                                        <div class="card-type-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                                <?php if (!empty($event['responsable_prenom'])): ?>
                                                    &mdash; <i class="fas fa-user"></i> <?= htmlspecialchars($event['responsable_prenom'] . ' ' . $event['responsable_nom']) ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if ($is_partial): ?>
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
                                        <div class="meta-item">
                                            <i class="fas fa-euro-sign" style="color: #d97706;"></i>
                                            <span style="color: #d97706; font-weight: 600;"><?= intval($event['montant'] ?? 0) ?> €</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($event['description']) ?></p>
                                    <?php endif; ?>
                                    <!-- Validation Progress -->
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <span class="badge <?= $has_bde ? 'badge-success' : 'badge-light' ?>" style="font-size: 0.75rem;">
                                            <i class="fas <?= $has_bde ? 'fa-check' : 'fa-hourglass-half' ?>"></i> BDE
                                        </span>
                                        <span class="badge <?= $has_tuteur ? 'badge-success' : 'badge-light' ?>" style="font-size: 0.75rem;">
                                            <i class="fas <?= $has_tuteur ? 'fa-check' : 'fa-hourglass-half' ?>"></i> Tuteur
                                        </span>
                                        <span class="badge <?= $has_admin ? 'badge-success' : 'badge-light' ?>" style="font-size: 0.75rem;">
                                            <i class="fas <?= $has_admin ? 'fa-check' : 'fa-hourglass-half' ?>"></i> Admin
                                        </span>
                                        <?php if (!empty($event['fiche_sanitaire'])): ?>
                                        <span class="badge badge-info" style="font-size: 0.75rem;">
                                            <i class="fas fa-file-medical"></i> Fiche sanitaire
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($event['affiche'])): ?>
                                        <span class="badge badge-info" style="font-size: 0.75rem;">
                                            <i class="fas fa-image"></i> Affiche
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details" onclick='openEventModal(<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>)'>
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <form method="POST" class="form-approve-event">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="validate_event" value="1">
                                        <button type="submit" class="btn-approve">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <button type="button" class="btn-reject" onclick='openEventModalReject(<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>)'>
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="empty-state-advanced" id="noResults" style="display: none;">
                    <div class="empty-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun événement ne correspond à votre recherche.</p>
                </div>
            <?php endif; ?>

            <!-- Rejected Events Section -->
            <?php if (!empty($rejected_events)): ?>
            <div class="card" style="margin-top: 30px;">
                <div class="card-header" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom: 2px solid #fecaca;">
                    <h3 style="color: #dc2626;"><i class="fas fa-times-circle"></i> Événements rejetés (<?= count($rejected_events) ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="validation-cards-container">
                        <?php foreach ($rejected_events as $event): ?>
                            <div class="validation-card-advanced event-card" style="border-left-color: #ef4444; opacity: 0.85;">
                                <div class="card-main">
                                    <div class="card-content">
                                        <div class="card-title-row">
                                            <div class="card-type-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
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
                                        <div class="reject-reason-box" style="margin-top: 10px;">
                                            <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                                            <p style="color: #7f1d1d; margin: 0;"><?= htmlspecialchars($event['motif_refus']) ?></p>
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
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Hidden forms for SweetAlert modal actions -->
    <form method="POST" id="swalApproveForm" style="display:none;">
        <?= Security::csrfField() ?>
        <input type="hidden" name="event_id" id="swalApproveEventId" value="">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="validate_event" value="1">
    </form>
    <form method="POST" id="swalRejectForm" style="display:none;">
        <?= Security::csrfField() ?>
        <input type="hidden" name="event_id" id="swalRejectEventId" value="">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="motif" id="swalRejectMotif" value="">
        <input type="hidden" name="validate_event" value="1">
    </form>

    <script>
    (function() {
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
            if (val == 1) return '<span class="badge badge-success" style="font-size:0.8rem;"><i class="fas fa-check"></i> ' + label + '</span>';
            if (val === null || val === undefined || val === '') return '<span class="badge badge-light" style="font-size:0.8rem;"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
            return '<span class="badge badge-danger" style="font-size:0.8rem;"><i class="fas fa-times"></i> ' + label + '</span>';
        }

        // ---- Search & Filter ----
        var searchInput = document.getElementById('searchInput');
        var filterTabs = document.querySelectorAll('.filter-tab');
        var cards = document.querySelectorAll('#validationCards > .validation-card-advanced');
        var noResults = document.getElementById('noResults');
        var cardsContainer = document.getElementById('validationCards');
        var currentFilter = 'all';

        function filterCards() {
            var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            var visibleCount = 0;
            cards.forEach(function(card) {
                var matchesSearch = card.dataset.search.includes(searchTerm);
                var matchesFilter = currentFilter === 'all' || card.dataset.type === currentFilter;
                if (matchesSearch && matchesFilter) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            if (noResults && cardsContainer) {
                if (visibleCount === 0 && cards.length > 0) {
                    noResults.style.display = '';
                    cardsContainer.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    cardsContainer.style.display = '';
                }
            }
        }

        if (searchInput) searchInput.addEventListener('input', filterCards);
        filterTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                filterTabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterCards();
            });
        });

        // ---- SweetAlert2 Detail Modal ----
        function buildDetailHtml(ev) {
            var resp = 'N/A';
            if (ev.responsable_prenom) {
                resp = esc(ev.responsable_prenom + ' ' + (ev.responsable_nom || ''));
                if (ev.responsable_mail) resp += ' <span style="color:#64748b;">(' + esc(ev.responsable_mail) + ')</span>';
                if (ev.responsable_promo) resp += ' &mdash; ' + esc(ev.responsable_promo);
            }

            var finHtml = '<span style="color:#64748b;">Non demandé</span>';
            if (ev.financement_bde == 1) {
                finHtml = '<span style="color:#d97706;font-weight:600;"><i class="fas fa-check-circle"></i> Oui &mdash; ' + parseInt(ev.montant || 0) + ' &euro;</span>';
            }

            var filesHtml = '';
            if (ev.fiche_sanitaire || ev.affiche) {
                filesHtml = '<div style="margin-top:12px;"><strong><i class="fas fa-paperclip"></i> Documents</strong><div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;">';
                if (ev.fiche_sanitaire) filesHtml += '<a href="' + esc(ev.fiche_sanitaire) + '" target="_blank" class="btn btn-outline btn-sm" style="text-decoration:none;font-size:0.8rem;"><i class="fas fa-file-medical"></i> Fiche sanitaire</a>';
                if (ev.affiche) filesHtml += '<a href="' + esc(ev.affiche) + '" target="_blank" class="btn btn-outline btn-sm" style="text-decoration:none;font-size:0.8rem;"><i class="fas fa-image"></i> Affiche</a>';
                filesHtml += '</div></div>';
            }

            return '<div style="text-align:left;font-size:0.92rem;line-height:1.6;">' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;">' +
                    '<div><strong><i class="fas fa-building"></i> Club</strong><br>' + esc(ev.nom_club || 'N/A') + '</div>' +
                    '<div><strong><i class="fas fa-user"></i> Responsable</strong><br>' + resp + '</div>' +
                    '<div><strong><i class="fas fa-calendar"></i> Date</strong><br>' + formatDate(ev.date_ev) + '</div>' +
                    '<div><strong><i class="fas fa-clock"></i> Horaires</strong><br>' + formatTime(ev.horaire_debut) + ' - ' + formatTime(ev.horaire_fin) + '</div>' +
                    '<div><strong><i class="fas fa-map-marker-alt"></i> Campus</strong><br>' + esc(ev.campus || 'N/A') + '</div>' +
                    '<div><strong><i class="fas fa-map-pin"></i> Lieu</strong><br>' + esc(ev.lieu || 'N/A') + '</div>' +
                    '<div><strong><i class="fas fa-euro-sign"></i> Financement BDE</strong><br>' + finHtml + '</div>' +
                    '<div><strong><i class="fas fa-inbox"></i> Dépôt</strong><br>' + formatDatetime(ev.date_depot) + '</div>' +
                '</div>' +
                (ev.description ? '<div style="margin-top:12px;"><strong><i class="fas fa-align-left"></i> Description</strong><p style="margin:4px 0 0;color:#475569;">' + esc(ev.description) + '</p></div>' : '') +
                filesHtml +
                '<div style="margin-top:12px;"><strong>Validations</strong><div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;">' +
                    valBadge('BDE', ev.validation_bde) + valBadge('Tuteur', ev.validation_tuteur) + valBadge('Admin', ev.validation_admin) +
                '</div></div>' +
            '</div>';
        }

        window.openEventModal = function(ev) {
            Swal.fire({
                title: '<i class="fas fa-calendar-alt" style="color:#3b82f6;"></i> ' + esc(ev.titre || 'Sans titre'),
                html: buildDetailHtml(ev),
                width: 700,
                showCloseButton: true,
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Approuver',
                denyButtonText: '<i class="fas fa-times"></i> Rejeter',
                cancelButtonText: 'Fermer',
                confirmButtonColor: '#28a745',
                denyButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                customClass: { popup: 'swal-event-detail-popup' },
                reverseButtons: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Approve this event
                    document.getElementById('swalApproveEventId').value = ev.event_id;
                    document.getElementById('swalApproveForm').submit();
                } else if (result.isDenied) {
                    // Open reject SweetAlert
                    openRejectSwal(ev);
                }
            });
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
                customClass: { popup: 'swal-event-detail-popup' }
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
                SwalHelper.confirm(
                    'Approuver cet événement ?',
                    'L\'événement sera validé par le BDE.',
                    'Oui, approuver',
                    'Annuler'
                ).then(function(result) {
                    if (result.isConfirmed) form.submit();
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

    })();
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
