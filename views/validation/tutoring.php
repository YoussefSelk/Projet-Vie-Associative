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
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'search', 'validation', 'clubs', 'events', 'dashboard'];
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
               <h1>
                   <?php if ($is_admin): ?>
                       Administration - Validations
                   <?php elseif ($is_bde): ?>
                       BDE - Validation des Événements
                   <?php else: ?>
                       Tutorat - Validations
                   <?php endif; ?>
               </h1>
               <p class="subtitle">
                   <?php if ($is_admin): ?>
                       Supervisez et validez tous les clubs et événements du système
                   <?php elseif ($is_bde): ?>
                       Confirmez ou rejetez les événements proposés par les clubs
                   <?php else: ?>
                       Validez les clubs et événements sous votre tutelle
                   <?php endif; ?>
               </p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <?php if(!empty($info_msg)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($info_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_clubs ?? []) + count($pending_events ?? []) ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
                <div class="stat-card clubs">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_clubs ?? []) ?></h3>
                        <p>Clubs à valider</p>
                    </div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_events ?? []) ?></h3>
                        <p>Événements à valider</p>
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
                            <span class="count"><?= count($pending_clubs ?? []) + count($pending_events ?? []) ?></span>
                        </button>
                        <?php if (!$is_bde): ?>
                        <button class="filter-tab" data-filter="clubs">
                            <i class="fas fa-building"></i> Clubs
                            <span class="count"><?= count($pending_clubs ?? []) ?></span>
                        </button>
                        <?php endif; ?>
                        <button class="filter-tab" data-filter="events">
                            <i class="fas fa-calendar-alt"></i> Événements
                            <span class="count"><?= count($pending_events ?? []) ?></span>
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
                            <h3>Tout est à jour !</h3>
                            <p>Aucune validation en attente. Revenez plus tard.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="validation-cards-container" id="validationCards">
                    <!-- Pending Clubs -->
                    <?php foreach ($pending_clubs ?? [] as $club): ?>
                        <?php if (($club['validation_finale'] ?? 0) == 1) continue; ?>
                        <?php
                        // Précharger les membres du club pour affichage dans la modale
                        $clubMembers = [];
                        try {
                            $stmt = $db->prepare("SELECT u.prenom, u.nom, u.mail, u.promo, mc.fonction\n                                                   FROM membres_club mc\n                                                   JOIN users u ON mc.membre_id = u.id\n                                                   WHERE mc.club_id = ? AND mc.valide = 1\n                                                   ORDER BY CASE WHEN mc.fonction = 'Président' THEN 0 ELSE 1 END, u.nom, u.prenom");
                            $stmt->execute([$club['club_id']]);
                            $clubMembers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                        } catch (Exception $e) {
                            $clubMembers = [];
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
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
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
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <?php if ($is_admin || $is_tutor): ?>
                                    <button type="button" class="btn-approve btn-swal-club-approve"
                                        data-id="<?= $club['club_id'] ?>"
                                        data-name="<?= htmlspecialchars($club['nom_club']) ?>">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($is_admin): ?>
                                    <button type="button" class="btn-force btn-swal-club-force"
                                        data-id="<?= $club['club_id'] ?>"
                                        data-name="<?= htmlspecialchars($club['nom_club']) ?>"
                                        title="Valider sans attendre le tuteur">
                                        <i class="fas fa-bolt"></i> Forcer
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($is_admin || $is_tutor): ?>
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
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('d/m/Y à H:i', strtotime($event['date_ev'] ?? 'now')) ?></span>
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
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($event['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details btn-swal-event-details"
                                        data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>'>
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <?php 
                                    if ($is_admin) {
                                        $btn_name = 'validate_event_admin';
                                    } elseif ($is_bde) {
                                        $btn_name = 'validate_event_bde';
                                    } else {
                                        $btn_name = 'validate_event_tutor';
                                    }
                                    ?>
                                    <button type="button" class="btn-approve btn-swal-event-approve"
                                        data-id="<?= $event['event_id'] ?>"
                                        data-name="<?= htmlspecialchars($event['titre'] ?? 'Événement') ?>"
                                        data-validate-field="<?= $btn_name ?>">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <?php if ($is_admin): ?>
                                    <button type="button" class="btn-force btn-swal-event-force"
                                        data-id="<?= $event['event_id'] ?>"
                                        data-name="<?= htmlspecialchars($event['titre'] ?? 'Événement') ?>"
                                        title="Valider sans attendre le tuteur">
                                        <i class="fas fa-bolt"></i> Forcer
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn-reject btn-swal-event-reject"
                                        data-id="<?= $event['event_id'] ?>"
                                        data-name="<?= htmlspecialchars($event['titre'] ?? 'Événement') ?>"
                                        data-validate-field="<?= $btn_name ?>">
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
                    <p>Aucun élément ne correspond à votre recherche.</p>
                </div>
            <?php endif; ?>

            <!-- All Clubs Section -->
            <?php if (!$is_bde): ?>
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Tous les clubs du système (<?= count($tutored_clubs ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($tutored_clubs)): ?>
                        <div class="empty-state-advanced">
                            <div class="empty-icon" style="background: #dbeafe; color: #2563eb;">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3>Aucun club</h3>
                            <p>Aucun club dans le système pour le moment.</p>
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
                    Swal.fire({ icon: 'success', title: 'Succès', text: 'Action effectuée avec succès.', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    throw new Error('Erreur serveur');
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Une erreur est survenue. Veuillez réessayer.' });
            });
        }

        // --- FILTRAGE ET RECHERCHE ---
        const searchInput = document.getElementById('searchInput');
        const filterTabs = document.querySelectorAll('.filter-tab');
        const cards = document.querySelectorAll('.validation-card-advanced');
        const noResults = document.getElementById('noResults');
        const cardsContainer = document.getElementById('validationCards');
        let currentFilter = 'all';

        function filterCards() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            let visibleCount = 0;
            cards.forEach(card => {
                const matchesSearch = card.dataset.search ? card.dataset.search.includes(searchTerm) : true;
                const matchesFilter = currentFilter === 'all' || card.dataset.type === currentFilter;
                if (matchesSearch && matchesFilter) { card.style.display = ''; visibleCount++; }
                else { card.style.display = 'none'; }
            });
            if (noResults && cardsContainer) {
                const isEmpty = visibleCount === 0 && cards.length > 0;
                noResults.style.display = isEmpty ? 'block' : 'none';
                cardsContainer.style.display = isEmpty ? 'none' : 'grid';
            }
        }

        if (searchInput) searchInput.addEventListener('input', filterCards);
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterCards();
            });
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

                let membersHtml = '<p style="color:#888;">Aucun membre renseigné.</p>';
                if (members.length > 0) {
                    membersHtml = '<div style="text-align:left;max-height:200px;overflow-y:auto;">' +
                        members.map(m =>
                            '<div style="padding:4px 0;border-bottom:1px solid #eee;">' +
                            '<strong>' + esc(m.prenom) + ' ' + esc(m.nom) + '</strong> — ' + esc(m.fonction) +
                            (m.promo ? ' <span style="color:#888;">(' + esc(m.promo) + ')</span>' : '') +
                            '</div>'
                        ).join('') + '</div>';
                }

                Swal.fire({
                    title: esc(club.nom_club || 'Club sans nom'),
                    html:
                        '<div style="text-align:left;">' +
                        '<p><strong><i class="fas fa-tag"></i> Type :</strong> ' + esc(club.type_club || '-') + '</p>' +
                        '<p><strong><i class="fas fa-map-marker-alt"></i> Campus :</strong> ' + esc(club.campus || '-') + '</p>' +
                        '<p><strong><i class="fas fa-envelope"></i> Email :</strong> ' + esc(club.mail || 'Non renseigné') + '</p>' +
                        '<p><strong><i class="fas fa-align-left"></i> Description :</strong></p>' +
                        '<p>' + esc(club.description || 'Aucune description fournie.') + '</p>' +
                        '<hr><p><strong><i class="fas fa-users"></i> Membres :</strong></p>' +
                        membersHtml +
                        '</div>',
                    width: 600,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d'
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
                    'Le club "' + clubName + '" sera validé.',
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
                    'Le club "' + clubName + '" sera validé immédiatement sans attendre le tuteur.',
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
                    html: '<p>Expliquez la raison du rejet pour aider le créateur à améliorer sa demande.</p>',
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
                let dateStr = '-';
                if (ev.date_ev) {
                    const d = new Date(ev.date_ev);
                    dateStr = d.toLocaleDateString('fr-FR', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                }
                Swal.fire({
                    title: '<i class="fas fa-calendar-alt"></i> ' + esc(ev.titre || 'Événement sans titre'),
                    html:
                        '<div style="text-align:left;">' +
                        '<p><strong><i class="fas fa-building"></i> Club :</strong> ' + esc(ev.nom_club || '-') + '</p>' +
                        '<p><strong><i class="fas fa-calendar"></i> Date :</strong> ' + esc(dateStr) + '</p>' +
                        '<p><strong><i class="fas fa-map-marker-alt"></i> Campus :</strong> ' + esc(ev.campus || '-') + '</p>' +
                        '<p><strong><i class="fas fa-location-dot"></i> Lieu :</strong> ' + esc(ev.lieu || 'Non renseigné') + '</p>' +
                        '<p><strong><i class="fas fa-align-left"></i> Description :</strong></p>' +
                        '<p>' + esc(ev.description || 'Aucune description fournie.') + '</p>' +
                        '</div>',
                    width: 600,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d'
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
                    'Approuver cet événement ?',
                    'L\'événement "' + eventName + '" sera validé.',
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
                    'L\'événement "' + eventName + '" sera validé immédiatement sans attendre le tuteur.',
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
                    title: 'Rejeter l\'événement "' + eventName + '" ?',
                    html: '<p>Expliquez la raison du rejet pour aider l\'organisateur à améliorer sa demande.</p>',
                    input: 'textarea',
                    inputPlaceholder: 'Ex: Date non disponible, lieu inapproprié, informations manquantes...',
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