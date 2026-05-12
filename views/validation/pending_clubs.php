<?php
/**
 * Liste des clubs en attente de validation
 *
 * Interface harmonisee avec la validation des evenements :
 * - Cartes detaillees (design admin)
 * - Filtres campus / statut
 * - Modales SweetAlert2 detail + actions
 *
 * @package Views/Validation
 */
$pageTitle = 'Clubs en attente - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'validation', 'clubs'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body class="validation-page pending-clubs-page">
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
                <h1><i class="fas fa-building"></i> Clubs en attente</h1>
                <p class="subtitle"><?= count($clubs) ?> club(s) en attente de validation</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
            <?php endif; ?>
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
            <?php endif; ?>

            <?php if (empty($clubs)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state-advanced">
                            <div class="empty-icon"><i class="fas fa-check"></i></div>
                            <h3>Aucun club en attente</h3>
                            <p>Tous les clubs ont ete valides.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php
                    $campusList = array_values(array_unique(array_filter(array_map(
                        fn($c) => trim((string)($c['campus'] ?? '')),
                        $clubs
                    ))));
                    sort($campusList, SORT_NATURAL | SORT_FLAG_CASE);
                ?>

                <div class="filters-row pending-clubs-filters">
                    <div class="filter-item filter-item-select">
                        <label for="pendingCampusFilter">Filtrer par campus :</label>
                        <select id="pendingCampusFilter" class="filter-select">
                            <option value="">Tous les campus</option>
                            <?php foreach ($campusList as $campus): ?>
                                <option value="<?= htmlspecialchars(mb_strtolower($campus)) ?>"><?= htmlspecialchars($campus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-item filter-item-select">
                        <label for="pendingStatusFilter">Filtrer par état de validation :</label>
                        <select id="pendingStatusFilter" class="filter-select">
                            <option value="">Toutes les fiches</option>
                            <option value="en_cours">Fiches en cours de traitement</option>
                            <option value="valide">Fiches validées</option>
                            <option value="refuse">Fiches refusées</option>
                        </select>
                    </div>

                    <div class="filter-item filter-item-action">
                        <label class="filter-action-label" aria-hidden="true">Action</label>
                        <button type="button" id="pendingResetFilters" class="filter-reset-btn">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>

                    <div class="filter-item filter-item-results">
                        <label class="filter-action-label" aria-hidden="true">Résultats</label>
                        <div id="pendingResultsCount" class="browse-count"><?= count($clubs) ?> club(s)</div>
                    </div>
                </div>

                <div class="validation-cards-container" id="validationCards">
                    <?php foreach ($clubs as $club): ?>
                        <?php
                            $validationFinale = isset($club['validation_finale']) ? (int)$club['validation_finale'] : null;
                            $validationBde = isset($club['validation_bde']) ? (int)$club['validation_bde'] : null;
                            $validationTuteur = isset($club['validation_tuteur']) ? (int)$club['validation_tuteur'] : null;
                            $validationAdmin = isset($club['validation_admin']) ? (int)$club['validation_admin'] : null;

                            $statusFilter = 'a_valider';
                            if ($validationFinale === 1) {
                                $statusFilter = 'valide';
                            } elseif ($validationFinale === 0 || $validationFinale === -1) {
                                $statusFilter = 'refuse';
                            } elseif ($validationBde === 1 || $validationTuteur === 1 || $validationAdmin === 1) {
                                $statusFilter = 'en_cours';
                            }
                            $statusBadgeClass = 'badge-warning';
                            $statusBadgeIcon = 'fa-clock';
                            $statusLabel = 'En attente';
                            if ($statusFilter === 'valide') {
                                $statusBadgeClass = 'badge-success';
                                $statusBadgeIcon = 'fa-check-circle';
                                $statusLabel = 'Validée';
                            } elseif ($statusFilter === 'refuse') {
                                $statusBadgeClass = 'badge-danger';
                                $statusBadgeIcon = 'fa-times-circle';
                                $statusLabel = 'Refusée';
                            } elseif ($statusFilter === 'en_cours') {
                                $statusBadgeClass = 'badge-info';
                                $statusBadgeIcon = 'fa-spinner';
                                $statusLabel = 'En cours';
                            }

                            $campusNormalized = mb_strtolower(trim((string)($club['campus'] ?? '')));
                            $clubMembers = [];
                            try {
                                $stmtMembers = $db->prepare("
                                    SELECT u.prenom, u.nom, u.mail, u.promo, mc.fonction, mc.soutenance
                                    FROM membres_club mc
                                    JOIN users u ON u.id = mc.membre_id
                                    WHERE mc.club_id = ? AND mc.valide = 1
                                    ORDER BY
                                        CASE
                                            WHEN mc.fonction IN ('Président','President') THEN 0
                                            WHEN mc.fonction IN ('Vice-Président','Vice-President','Vice-président','Vice-president') THEN 1
                                            ELSE 2
                                        END,
                                        u.nom, u.prenom
                                ");
                                $stmtMembers->execute([(int)$club['club_id']]);
                                $clubMembers = $stmtMembers->fetchAll(PDO::FETCH_ASSOC) ?: [];
                            } catch (Exception $e) {
                                $clubMembers = [];
                            }
                        ?>
                        <div class="validation-card-advanced club-card"
                            data-campus="<?= htmlspecialchars($campusNormalized) ?>"
                            data-status="<?= htmlspecialchars($statusFilter) ?>">
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
                                            <span class="card-subtitle"><i class="fas fa-tag"></i> <?= htmlspecialchars($club['type_club']) ?></span>
                                        </div>
                                        <span class="badge <?= $statusBadgeClass ?>"><i class="fas <?= $statusBadgeIcon ?>"></i> <?= $statusLabel ?></span>
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

                                    <div class="validation-badges-row">
                                        <span class="badge <?= $validationAdmin === 1 ? 'badge-success' : 'badge-light' ?>"><i class="fas <?= $validationAdmin === 1 ? 'fa-check' : 'fa-times' ?>"></i> Admin</span>
                                        <span class="badge <?= $validationBde === 1 ? 'badge-success' : 'badge-light' ?>"><i class="fas <?= $validationBde === 1 ? 'fa-check' : 'fa-times' ?>"></i> BDE</span>
                                        <span class="badge <?= $validationTuteur === 1 ? 'badge-success' : 'badge-light' ?>"><i class="fas <?= $validationTuteur === 1 ? 'fa-check' : 'fa-times' ?>"></i> Tuteur</span>
                                        <span class="badge <?= $validationFinale === 1 ? 'badge-success' : 'badge-light' ?>"><i class="fas <?= $validationFinale === 1 ? 'fa-check' : 'fa-times' ?>"></i> Finale</span>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <button type="button" class="btn-view-details btn-swal-club-details"
                                        data-club='<?= htmlspecialchars(json_encode($club), ENT_QUOTES, "UTF-8") ?>'
                                        data-members='<?= htmlspecialchars(json_encode($clubMembers), ENT_QUOTES, "UTF-8") ?>'>
                                        <i class="fas fa-eye"></i> Voir details
                                    </button>
                                    <?php if ($statusFilter !== 'valide' && $statusFilter !== 'refuse'): ?>
                                        <button type="button" class="btn-approve btn-swal-club-approve"
                                            data-id="<?= $club['club_id'] ?>"
                                            data-name="<?= htmlspecialchars($club['nom_club']) ?>">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                        <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                                        <button type="button" class="btn-force btn-swal-club-force"
                                            data-id="<?= $club['club_id'] ?>"
                                            data-name="<?= htmlspecialchars($club['nom_club']) ?>"
                                            title="Valider immediatement sans attendre le tuteur">
                                            <i class="fas fa-bolt"></i> Forcer
                                        </button>
                                        <?php endif; ?>
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
                </div>

                <form id="swalApproveClubForm" method="POST" style="display:none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="swalApproveClubId" value="">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="validate_club" value="1">
                    <input type="hidden" name="remarques" id="swalApproveClubRemarques" value="">
                </form>

                <form id="swalRejectClubForm" method="POST" style="display:none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="swalRejectClubId" value="">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="validate_club" value="1">
                    <input type="hidden" name="remarques" id="swalRejectClubRemarques" value="">
                </form>

                <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                <form id="swalForceClubForm" method="POST" style="display:none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="swalForceClubId" value="">
                    <input type="hidden" name="action" value="force_approve">
                    <input type="hidden" name="validate_club" value="1">
                </form>
                <?php endif; ?>

                <div id="pendingNoResults" class="empty-state-advanced" style="display:none; margin-top: 1rem;">
                    <div class="empty-icon empty-icon-search"><i class="fas fa-filter"></i></div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun club ne correspond aux filtres sélectionnés.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const campusFilter = document.getElementById('pendingCampusFilter');
            const statusFilter = document.getElementById('pendingStatusFilter');
            const resetFiltersBtn = document.getElementById('pendingResetFilters');
            const cards = Array.from(document.querySelectorAll('#validationCards .validation-card-advanced'));
            const noResults = document.getElementById('pendingNoResults');
            const resultsCount = document.getElementById('pendingResultsCount');

            function esc(str) {
                const div = document.createElement('div');
                div.textContent = str == null ? '' : String(str);
                return div.innerHTML;
            }

            function statusBadge(label, val) {
                if (val == 1) return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
                if (val == -1 || val === 0 || val === '0') return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
                return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
            }

            function applyPendingFilters() {
                if (!cards.length) return;

                const campus = String(campusFilter ? (campusFilter.value || '') : '').trim().toLowerCase();
                const status = String(statusFilter ? (statusFilter.value || '') : '').trim();
                let visibleCount = 0;

                for (const card of cards) {
                    const cardCampus = String(card.dataset.campus || '').trim().toLowerCase();
                    const cardStatus = String(card.dataset.status || '').trim();
                    const campusOk = (campus === '' || campus === 'all') ? true : (cardCampus === campus);
                    const statusOk = (status === '' || status === 'all') ? true : (cardStatus === status);
                    const visible = campusOk && statusOk;

                    card.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                }

                if (resultsCount) resultsCount.textContent = visibleCount + ' club(s)';
                if (noResults) noResults.style.display = visibleCount === 0 ? '' : 'none';
            }

            if (campusFilter && statusFilter) {
                campusFilter.addEventListener('change', applyPendingFilters);
                statusFilter.addEventListener('change', applyPendingFilters);
            }

            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    if (campusFilter) campusFilter.value = '';
                    if (statusFilter) statusFilter.value = '';
                    applyPendingFilters();
                });
            }

            applyPendingFilters();

            function buildClubDetailHtml(club, members) {
                const logoHtml = club.logo_club
                    ? '<img src="' + esc(club.logo_club) + '" alt="Logo" class="swal-detail-logo" />'
                    : '<div class="swal-detail-logo-placeholder"><i class="fas fa-building"></i></div>';
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
                const list = Array.isArray(members) ? members : [];
                const soutenanceCount = list.filter(function(m) { return m.soutenance == 1 || m.soutenance === '1'; }).length;
                const membersHtml = list.length
                    ? '<div class="swal-members-list">' + list.map(function(m) {
                        const fullName = [m.prenom, m.nom].filter(Boolean).join(' ').trim() || 'Membre';
                        const roleLine = [m.fonction, m.promo].filter(Boolean).join(' · ');
                        const soutenanceTag = (m.soutenance == 1 || m.soutenance === '1')
                            ? '<span class="swal-badge swal-badge-success"><i class="fas fa-graduation-cap"></i> Soutenance</span>'
                            : '<span class="swal-badge swal-badge-pending"><i class="fas fa-minus-circle"></i> Sans soutenance</span>';
                        const mailLine = m.mail ? '<span class="swal-member-role">' + esc(m.mail) + '</span>' : '';
                        return '<div class="swal-member-item">' +
                            '<div class="swal-member-avatar"><i class="fas fa-user"></i></div>' +
                            '<div class="swal-member-info">' +
                                '<span class="swal-member-name">' + esc(fullName) + '</span>' +
                                '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
                                    '<span class="swal-member-role">' + esc(roleLine || 'Rôle non renseigné') + '</span>' +
                                    soutenanceTag +
                                '</div>' +
                                mailLine +
                            '</div>' +
                        '</div>';
                    }).join('') + '</div>'
                    : '<div class="swal-empty-state"><i class="fas fa-users"></i> Aucun membre renseigné</div>';

                return '<div class="swal-detail-content">' +
                    '<div class="swal-detail-hero">' +
                        '<div class="swal-detail-hero-media">' + logoHtml + '</div>' +
                        '<div class="swal-detail-hero-main">' +
                            '<div class="swal-detail-hero-title">' + esc(club.nom_club || 'Club sans nom') + '</div>' +
                            '<div class="swal-detail-hero-sub"><span><i class="fas fa-tag"></i> ' + esc(club.type_club || 'N/A') + '</span></div>' +
                            '<div class="swal-detail-hero-chips"><span class="swal-chip campus"><i class="fas fa-map-marker-alt"></i> ' + esc(club.campus || 'N/A') + '</span></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="swal-detail-grid">' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-tag"></i> Type</div><div class="swal-detail-value">' + esc(club.type_club || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-marker-alt"></i> Campus</div><div class="swal-detail-value"><span class="campus-badge ' + (club.campus || 'calais').toLowerCase() + '">' + esc(club.campus || 'N/A') + '</span></div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-envelope"></i> Contact</div><div class="swal-detail-value">' + esc(contact || 'Non renseigné') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-fingerprint"></i> Club ID</div><div class="swal-detail-value">' + esc(club.club_id || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-user"></i> Responsable</div><div class="swal-detail-value">' + esc(responsable || club.responsable || 'Non renseigné') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-chalkboard-teacher"></i> Tuteur</div><div class="swal-detail-value">' + esc(tuteur || 'Non assigné') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-inbox"></i> Dépôt</div><div class="swal-detail-value">' + esc(dateDepot || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-layer-group"></i> Statut fiche</div><div class="swal-detail-value">' + esc(club.statut || club.status || 'En attente') + '</div></div>' +
                    '</div>' +
                    '<div class="swal-detail-section">' +
                        '<div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div>' +
                        '<div class="swal-detail-description">' + esc(club.description || 'Aucune description fournie.') + '</div>' +
                    '</div>' +
                    '<div class="swal-detail-section">' +
                        '<div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> État des validations</div>' +
                        '<div class="swal-badges-row">' +
                            statusBadge('Admin', club.validation_admin) +
                            statusBadge('BDE', club.validation_bde) +
                            statusBadge('Tuteur', club.validation_tuteur) +
                            statusBadge('Finale', club.validation_finale) +
                        '</div>' +
                    '</div>' +
                    '<div class="swal-detail-section swal-detail-section-compact">' +
                        '<div class="swal-detail-label"><i class="fas fa-chart-pie"></i> Résumé de la fiche</div>' +
                        '<div class="swal-keyline">' +
                            '<span><strong>Membres:</strong> ' + list.length + '</span>' +
                            '<span><strong>Soutenance:</strong> ' + soutenanceCount + '</span>' +
                            '<span><strong>Sans soutenance:</strong> ' + Math.max(0, list.length - soutenanceCount) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="swal-detail-section">' +
                        '<div class="swal-detail-label"><i class="fas fa-users"></i> Membres (' + list.length + ')</div>' +
                        membersHtml +
                    '</div>' +
                    (motifRefus ? '<div class="swal-detail-section swal-reject-box"><div class="swal-detail-label"><i class="fas fa-comment-alt"></i> Motif de rejet</div><div class="swal-detail-description">' + esc(motifRefus) + '</div></div>' : '') +
                '</div>';
            }

            document.querySelectorAll('.btn-swal-club-details').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const club = JSON.parse(this.dataset.club || '{}');
                    const members = JSON.parse(this.dataset.members || '[]');
                    Swal.fire({
                        title: esc(club.nom_club || 'Club sans nom'),
                        html: buildClubDetailHtml(club, members),
                        width: 700,
                        confirmButtonText: 'Fermer',
                        confirmButtonColor: '#6c757d',
                        customClass: { popup: 'swal-detail-popup' }
                    });
                });
            });

            document.querySelectorAll('.btn-swal-club-approve').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const clubId = this.dataset.id;
                    const clubName = this.dataset.name;
                    Swal.fire({
                        title: 'Approuver le club',
                        html: '<p style="margin-bottom:12px;">Approuver le club <strong>&laquo; ' + esc(clubName) + ' &raquo;</strong> ?</p>',
                        input: 'textarea',
                        inputLabel: 'Remarques (optionnel)',
                        inputPlaceholder: 'Ajoutez un commentaire pour l\'étudiant...',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check"></i> Approuver',
                        cancelButtonText: 'Annuler',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        customClass: { popup: 'swal-validation-popup' }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            document.getElementById('swalApproveClubId').value = clubId;
                            document.getElementById('swalApproveClubRemarques').value = result.value || '';
                            document.getElementById('swalApproveClubForm').submit();
                        }
                    });
                });
            });

            document.querySelectorAll('.btn-swal-club-reject').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const clubId = this.dataset.id;
                    const clubName = this.dataset.name;
                    Swal.fire({
                        title: 'Rejeter le club',
                        html: '<p style="margin-bottom:12px;">Rejeter le club <strong>&laquo; ' + esc(clubName) + ' &raquo;</strong> ?</p>',
                        input: 'textarea',
                        inputLabel: 'Motif du rejet',
                        inputPlaceholder: 'Expliquez la raison du rejet...',
                        inputValidator: function(value) {
                            if (!value || !value.trim()) return 'Le motif de rejet est obligatoire.';
                        },
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-times"></i> Rejeter',
                        cancelButtonText: 'Annuler',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        customClass: { popup: 'swal-validation-popup' }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            document.getElementById('swalRejectClubId').value = clubId;
                            document.getElementById('swalRejectClubRemarques').value = result.value || '';
                            document.getElementById('swalRejectClubForm').submit();
                        }
                    });
                });
            });

            document.querySelectorAll('.btn-swal-club-force').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const clubId = this.dataset.id;
                    const clubName = this.dataset.name;
                    Swal.fire({
                        title: 'Forcer la validation ?',
                        html: '<p>Le club <strong>&laquo; ' + esc(clubName) + ' &raquo;</strong> sera valide immediatement.</p><p style="color:#d97706;font-size:0.9em;"><i class="fas fa-exclamation-triangle"></i> Cette action contourne le circuit normal.</p>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-bolt"></i> Oui, forcer',
                        cancelButtonText: 'Annuler',
                        confirmButtonColor: '#d97706',
                        cancelButtonColor: '#6c757d'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            const forceId = document.getElementById('swalForceClubId');
                            const forceForm = document.getElementById('swalForceClubForm');
                            if (forceId && forceForm) {
                                forceId.value = clubId;
                                forceForm.submit();
                            }
                        }
                    });
                });
            });
        });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
