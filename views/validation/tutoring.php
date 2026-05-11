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
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
            <?php endif; ?>

            <?php if(!empty($info_msg)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($info_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
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
                <?php
                    $campus_values = [];
                    foreach (($pending_clubs ?? []) as $item) {
                        $camp = trim((string)($item['campus'] ?? ''));
                        if ($camp !== '') $campus_values[$camp] = true;
                    }
                    foreach (($pending_events ?? []) as $item) {
                        $camp = trim((string)($item['campus'] ?? ''));
                        if ($camp !== '') $campus_values[$camp] = true;
                    }
                    $campus_options = array_keys($campus_values);
                    sort($campus_options, SORT_NATURAL | SORT_FLAG_CASE);
                    $all_pending_count = count($pending_clubs ?? []) + count($pending_events ?? []);
                ?>
                <div class="search-row" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto auto; gap:12px; align-items:end;">
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <label for="cardTypeFilter" style="font-weight:600; color:#334155;">Filtrer par type :</label>
                        <select id="cardTypeFilter" class="form-control">
                            <option value="all">Clubs &amp; Événements</option>
                            <?php if (!$is_bde): ?>
                            <option value="clubs">Clubs uniquement</option>
                            <?php endif; ?>
                            <option value="events">Événements uniquement</option>
                        </select>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <label for="cardCampusFilter" style="font-weight:600; color:#334155;">Filtrer par campus :</label>
                        <select id="cardCampusFilter" class="form-control">
                            <option value="all">Tous les campus</option>
                            <?php foreach ($campus_options as $campus_option): ?>
                                <option value="<?= strtolower(htmlspecialchars($campus_option, ENT_QUOTES, 'UTF-8')) ?>">
                                    <?= htmlspecialchars($campus_option, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <label for="cardStatusFilter" style="font-weight:600; color:#334155;">Filtrer par état :</label>
                        <select id="cardStatusFilter" class="form-control">
                            <option value="">Tous les états</option>
                            <option value="a_valider">Fiches à valider</option>
                            <option value="en_cours">Fiches en cours de traitement</option>
                            <option value="valide">Fiches validées</option>
                            <option value="refuse">Fiches refusées</option>
                        </select>
                    </div>
                    <button type="button" id="cardFiltersReset" class="btn btn-outline" style="height:44px;">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <div id="cardFilterCount" class="campus-badge" style="justify-self:end; min-width:92px; text-align:center; font-weight:700;">
                        <?= (int)$all_pending_count ?> fiche(s)
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
                        // Calcul du statut de validation pour club
                        $statusFilter = 'a_valider';
                        $validationFinale = isset($club['validation_finale']) ? (int)$club['validation_finale'] : null;
                        $validationAdmin = isset($club['validation_admin']) ? (int)$club['validation_admin'] : null;
                        $validationTuteur = isset($club['validation_tuteur']) ? (int)$club['validation_tuteur'] : null;
                        
                        if ($validationFinale === 1) {
                            $statusFilter = 'valide';
                            $badgeClass = 'badge-success';
                            $badgeText = 'Validé';
                            $badgeIcon = 'fa-check-circle';
                        } elseif ($validationFinale === 0 || $validationFinale === -1) {
                            $statusFilter = 'refuse';
                            $badgeClass = 'badge-danger';
                            $badgeText = 'Refusé';
                            $badgeIcon = 'fa-times-circle';
                        } elseif ($validationAdmin === 1 || $validationTuteur === 1) {
                            $statusFilter = 'en_cours';
                            $badgeClass = 'badge-info';
                            $badgeText = 'En cours';
                            $badgeIcon = 'fa-hourglass-half';
                        } else {
                            $statusFilter = 'a_valider';
                            $badgeClass = 'badge-warning';
                            $badgeText = 'À valider';
                            $badgeIcon = 'fa-clock';
                        }
                        
                        $campusNormalized = mb_strtolower(trim((string)($club['campus'] ?? '')));
                        
                        // Précharger les membres du club
                        $clubMembers = [];
                        try {
                            $stmt = $db->prepare("SELECT u.prenom, u.nom, u.mail, u.promo, mc.fonction
                                                   FROM membres_club mc
                                                   JOIN users u ON mc.membre_id = u.id
                                                   WHERE mc.club_id = ? AND mc.valide = 1
                                                   ORDER BY CASE WHEN mc.fonction = 'Président' THEN 0 ELSE 1 END, u.nom, u.prenom");
                            $stmt->execute([$club['club_id']]);
                            $clubMembers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                        } catch (Exception $e) {
                            $clubMembers = [];
                        }
                        ?>
                        <div class="validation-card-advanced club-card" 
                             data-type="clubs"
                             data-campus="<?= htmlspecialchars($campusNormalized) ?>"
                             data-status="<?= htmlspecialchars($statusFilter) ?>"
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
                                        <span class="badge <?= $badgeClass ?>"><i class="fas <?= $badgeIcon ?>"></i> <?= $badgeText ?></span>
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
    <?php if (!in_array($statusFilter, ['valide', 'refuse'])): ?>
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
    <?php endif; ?>
</div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pending Events -->
                    <?php foreach ($pending_events ?? [] as $event): ?>
                        <?php
                        // Calcul du statut de validation pour event
                        $validationFinale = isset($event['validation_finale']) ? (int)$event['validation_finale'] : null;
                        $validationAdmin = isset($event['validation_admin']) ? (int)$event['validation_admin'] : null;
                        $validationTuteur = isset($event['validation_tuteur']) ? (int)$event['validation_tuteur'] : null;
                        $validationBde = isset($event['validation_bde']) ? (int)$event['validation_bde'] : null;
                        
                        if ($validationFinale === 1) {
                            $statusFilter = 'valide';
                            $badgeClass = 'badge-success';
                            $badgeText = 'Validé';
                            $badgeIcon = 'fa-check-circle';
                        } elseif ($validationFinale === 0 || $validationFinale === -1) {
                            $statusFilter = 'refuse';
                            $badgeClass = 'badge-danger';
                            $badgeText = 'Refusé';
                            $badgeIcon = 'fa-times-circle';
                        } elseif ($validationAdmin === 1 || $validationTuteur === 1 || $validationBde === 1) {
                            $statusFilter = 'en_cours';
                            $badgeClass = 'badge-info';
                            $badgeText = 'En cours';
                            $badgeIcon = 'fa-hourglass-half';
                        } else {
                            $statusFilter = 'a_valider';
                            $badgeClass = 'badge-warning';
                            $badgeText = 'À valider';
                            $badgeIcon = 'fa-clock';
                        }
                        
                        $campusNormalized = mb_strtolower(trim((string)($event['campus'] ?? '')));
                        ?>
                        <div class="validation-card-advanced event-card" 
                             data-type="events"
                             data-campus="<?= htmlspecialchars($campusNormalized) ?>"
                             data-status="<?= htmlspecialchars($statusFilter) ?>"
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
                                        <span class="badge <?= $badgeClass ?>"><i class="fas <?= $badgeIcon ?>"></i> <?= $badgeText ?></span>
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

                                        <?php if (isset($event['type_event'])): ?>
                                            <span class="type-badge <?= (!empty($event['type_event']) && $event['type_event'] == 'event') ? 'event' : 'activity' ?>">
                                                <?= htmlspecialchars((!empty($event['type_event']) && $event['type_event'] == 'event') ? 'Événement' : 'Activité') ?>
                                            </span>
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
                                        <span class="badge <?= $validationBde === 1 ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $validationBde === 1 ? 'fa-check' : 'fa-times' ?>"></i> BDE
                                        </span>
                                        <span class="badge <?= $validationTuteur === 1 ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $validationTuteur === 1 ? 'fa-check' : 'fa-times' ?>"></i> Tuteur
                                        </span>
                                        <span class="badge <?= $validationAdmin === 1 ? 'badge-success' : 'badge-light' ?>">
                                            <i class="fas <?= $validationAdmin === 1 ? 'fa-check' : 'fa-times' ?>"></i> Admin
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
    <button type="button" class="btn-view-details btn-swal-event-details"
        data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, "UTF-8") ?>'>
        <i class="fas fa-eye"></i> Voir détails
    </button>
    <?php if (!in_array($statusFilter, ['valide', 'refuse'])): ?>
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
    <?php endif; ?>
</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="empty-state-advanced" id="noResults" style="display:none;">
                    <div class="empty-icon empty-icon-search">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun élément ne correspond à votre recherche.</p>
                </div>

                <!-- Pagination -->
                <div id="tutoringPagination" class="pagination-wrapper"></div>
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
                            <div class="empty-icon empty-icon-info">
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
        const filterTypeEl   = document.getElementById('cardTypeFilter');
        const filterCampusEl = document.getElementById('cardCampusFilter');
        const filterStatusEl = document.getElementById('cardStatusFilter');
        const resetBtn       = document.getElementById('cardFiltersReset');
        const filterCountEl  = document.getElementById('cardFilterCount');
        const cards          = document.querySelectorAll('.validation-card-advanced');
        const noResults      = document.getElementById('noResults');
        const cardsContainer = document.getElementById('validationCards');

        let tutoringPagination = null;

        function applyFilters() {
            const typeVal   = filterTypeEl   ? filterTypeEl.value   : 'all';
            const campusVal = filterCampusEl ? filterCampusEl.value : 'all';
            const statusVal = filterStatusEl ? filterStatusEl.value : '';

            let visible = 0;
            cards.forEach(card => {
                const matchType   = typeVal === 'all' || card.dataset.type === typeVal;
                const matchCampus = campusVal === 'all' || card.dataset.campus === campusVal;
                const matchStatus = statusVal === '' || card.dataset.status === statusVal;

                if (matchType && matchCampus && matchStatus) {
                    card.classList.remove('filter-hidden');
                    card.style.display = '';
                    visible++;
                } else {
                    card.classList.add('filter-hidden');
                    card.style.display = 'none';
                }
            });

            if (filterCountEl) filterCountEl.textContent = visible + ' fiche(s)';

            if (noResults && cardsContainer) {
                const isEmpty = visible === 0 && cards.length > 0;
                noResults.style.display      = isEmpty ? 'block' : 'none';
                cardsContainer.style.display = isEmpty ? 'none'  : 'grid';
            }

            if (tutoringPagination) {
                tutoringPagination.currentPage = 1;
                tutoringPagination.update();
            }
        }

        if (filterTypeEl)   filterTypeEl.addEventListener('change',   applyFilters);
        if (filterCampusEl) filterCampusEl.addEventListener('change', applyFilters);
        if (filterStatusEl) filterStatusEl.addEventListener('change', applyFilters);

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (filterTypeEl)   filterTypeEl.value   = 'all';
                if (filterCampusEl) filterCampusEl.value = 'all';
                if (filterStatusEl) filterStatusEl.value = '';
                applyFilters();
            });
        }

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
            applyFilters(); // initialise le compteur au chargement
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

                const valAdmin  = club.validation_admin;
                const valTuteur = club.validation_tuteur;
                const valFinale = club.validation_finale;

                function statusBadge(label, val) {
                    if (val == 1)  return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
                    if (val == -1 || val === '0' || val === 0) return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
                    return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
                }

                let membersHtml = '<div class="swal-empty-state"><i class="fas fa-users"></i> Aucun membre renseigné</div>';
                if (members.length > 0) {
                    membersHtml = '<div class="swal-members-list">' +
                        members.map(m =>
                            '<div class="swal-member-item">' +
                            '<div class="swal-member-avatar"><i class="fas fa-user"></i></div>' +
                            '<div class="swal-member-info">' +
                            '<span class="swal-member-name">' + esc(m.prenom) + ' ' + esc(m.nom) + '</span>' +
                            '<span class="swal-member-role">' + esc(m.fonction) +
                            (m.promo ? ' · ' + esc(m.promo) : '') + '</span>' +
                            '</div></div>'
                        ).join('') + '</div>';
                }

                Swal.fire({
                    title: esc(club.nom_club || 'Club sans nom'),
                    html:
                        '<div class="swal-detail-content">' +
                        '<div class="swal-detail-grid">' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-tag"></i> Type</div><div class="swal-detail-value">' + esc(club.type_club || '-') + '</div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-marker-alt"></i> Campus</div><div class="swal-detail-value"><span class="campus-badge ' + (club.campus || 'calais').toLowerCase() + '">' + esc(club.campus || '-') + '</span></div></div>' +
                            '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-envelope"></i> Email</div><div class="swal-detail-value">' + esc(club.mail || 'Non renseigné') + '</div></div>' +
                            (club.tuteur_nom ? '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-chalkboard-teacher"></i> Tuteur</div><div class="swal-detail-value">' + esc(club.tuteur_nom) + '</div></div>' : '') +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div>' +
                            '<div class="swal-detail-description">' + esc(club.description || 'Aucune description fournie.') + '</div>' +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> État des validations</div>' +
                            '<div class="swal-badges-row">' +
                                statusBadge('Admin', valAdmin) +
                                statusBadge('Tuteur', valTuteur) +
                                statusBadge('Finale', valFinale) +
                            '</div>' +
                        '</div>' +
                        '<div class="swal-detail-section">' +
                            '<div class="swal-detail-label"><i class="fas fa-users"></i> Membres (' + members.length + ')</div>' +
                            membersHtml +
                        '</div>' +
                        (club.motif_refus ? '<div class="swal-detail-section swal-reject-box"><div class="swal-detail-label"><i class="fas fa-comment-alt"></i> Motif de rejet</div><div class="swal-detail-description">' + esc(club.motif_refus) + '</div></div>' : '') +
                        '</div>',
                    width: 650,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d',
                    customClass: { popup: 'swal-detail-popup' }
                });
            });
        });

        // Approve club
        document.querySelectorAll('.btn-swal-club-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const clubId   = this.dataset.id;
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
                const clubId   = this.dataset.id;
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
                const clubId   = this.dataset.id;
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
                        + ' à ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                }
                function valBadge(label, val) {
                    if (val == 1) return '<span class="swal-badge swal-badge-success"><i class="fas fa-check-circle"></i> ' + label + '</span>';
                    if (val === null || val === undefined || val === '') return '<span class="swal-badge swal-badge-pending"><i class="fas fa-hourglass-half"></i> ' + label + '</span>';
                    return '<span class="swal-badge swal-badge-danger"><i class="fas fa-times-circle"></i> ' + label + '</span>';
                }

                let logoHtml = '';
                if (ev.logo_club) {
                    const lp = ev.logo_club.match(/^https?:\/\//i) ? ev.logo_club : '/' + String(ev.logo_club).replace(/^\/+/, '');
                    logoHtml = '<div style="text-align:center;margin-bottom:10px;"><img src="' + esc(lp) + '" alt="Logo" class="swal-detail-logo" /></div>';
                }

                let resp = '<span class="swal-muted">N/A</span>';
                if (ev.responsable_prenom) {
                    resp = esc(ev.responsable_prenom + ' ' + (ev.responsable_nom || ''));
                    if (ev.responsable_mail)  resp += ' <span style="color:#64748b;">(' + esc(ev.responsable_mail) + ')</span>';
                    if (ev.responsable_promo) resp += ' · ' + esc(ev.responsable_promo);
                }

                let finHtml = '<span class="swal-muted">Non demandé</span>';
                if (ev.financement_bde == 1) {
                    finHtml = '<span class="swal-finance-highlight"><i class="fas fa-check-circle"></i> Oui — ' + parseInt(ev.montant || 0) + ' €</span>';
                }

                let filesHtml = '';
                if (ev.fiche_sanitaire || ev.affiche || ev.doc_organisation) {
                    filesHtml = '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-paperclip"></i> Documents joints</div><div class="swal-files-row">';
                    if (ev.doc_organisation) filesHtml += '<a href="' + esc(ev.doc_organisation) + '" target="_blank" class="swal-file-link" style="display:inline-block;margin-right:10px;"><i class="fas fa-file-alt"></i> Document d&apos;organisation</a>';
                    if (ev.fiche_sanitaire)  filesHtml += '<a href="' + esc(ev.fiche_sanitaire)  + '" target="_blank" class="swal-file-link" style="display:inline-block;margin-right:10px;"><i class="fas fa-file-medical"></i> Fiche sanitaire</a>';
                    if (ev.affiche)          filesHtml += '<a href="' + esc(ev.affiche)          + '" target="_blank" class="swal-file-link"><i class="fas fa-image"></i> Affiche</a>';
                    filesHtml += '</div></div>';
                }

                const html =
                    '<div class="swal-detail-content">' + logoHtml +
                    '<div class="swal-detail-grid">' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-building"></i> Club</div><div class="swal-detail-value">' + esc(ev.nom_club || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-tag"></i> Type</div><div class="swal-detail-value">' + (ev.is_event == 1 ? 'Événement' : 'Activité') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-user"></i> Responsable</div><div class="swal-detail-value">' + resp + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-calendar"></i> Date</div><div class="swal-detail-value">' + formatDate(ev.date_ev) + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-clock"></i> Horaires</div><div class="swal-detail-value">' + formatTime(ev.horaire_debut) + ' - ' + formatTime(ev.horaire_fin) + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-marker-alt"></i> Campus</div><div class="swal-detail-value"><span class="campus-badge ' + (ev.campus || 'calais').toLowerCase() + '">' + esc(ev.campus || 'N/A') + '</span></div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-map-pin"></i> Lieu</div><div class="swal-detail-value">' + esc(ev.lieu || 'N/A') + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-euro-sign"></i> Financement BDE</div><div class="swal-detail-value">' + finHtml + '</div></div>' +
                        '<div class="swal-detail-item"><div class="swal-detail-label"><i class="fas fa-inbox"></i> Dépôt</div><div class="swal-detail-value">' + formatDatetime(ev.date_depot) + '</div></div>' +
                    '</div>' +
                    (ev.description ? '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-align-left"></i> Description</div><div class="swal-detail-description">' + esc(ev.description) + '</div></div>' : '') +
                    filesHtml +
                    '<div class="swal-detail-section"><div class="swal-detail-label"><i class="fas fa-clipboard-check"></i> État des validations</div><div class="swal-badges-row">' +
                        valBadge('BDE', ev.validation_bde) + valBadge('Tuteur', ev.validation_tuteur) + valBadge('Admin', ev.validation_admin) +
                    '</div></div>' +
                    (ev.motif_refus ? '<div class="swal-detail-section swal-reject-box"><div class="swal-detail-label"><i class="fas fa-comment-alt"></i> Motif de rejet</div><div class="swal-detail-description">' + esc(ev.motif_refus) + '</div></div>' : '') +
                    '</div>';

                Swal.fire({
                    title: '<i class="fas fa-calendar-alt" style="color:var(--color-primary, #0066cc);"></i> ' + esc(ev.titre || 'Événement sans titre'),
                    html: html,
                    width: 700,
                    showCloseButton: true,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#6c757d',
                    customClass: { popup: 'swal-detail-popup' }
                });
            });
        });

        // Approve event
        document.querySelectorAll('.btn-swal-event-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const eventId       = this.dataset.id;
                const eventName     = this.dataset.name;
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
                const eventId   = this.dataset.id;
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
                const eventId       = this.dataset.id;
                const eventName     = this.dataset.name;
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