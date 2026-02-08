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
                             data-search="<?= strtolower(htmlspecialchars($club['nom_club'] . ' ' . $club['type_club'] . ' ' . $club['campus'])) ?>"
                             data-members='<?= htmlspecialchars(json_encode($clubMembers), ENT_QUOTES, 'UTF-8') ?>'>
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
                                    <button type="button" class="btn-view-details" onclick="openClubModal(<?= htmlspecialchars(json_encode($club)) ?>, this)">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <?php if ($is_admin || $is_tutor): ?>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" name="validate_club_admin" class="btn-approve">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($is_admin): ?>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                        <input type="hidden" name="action" value="force_approve">
                                        <button type="submit" name="validate_club_admin" class="btn-force" title="Valider sans attendre le tuteur">
                                            <i class="fas fa-bolt"></i> Forcer
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($is_admin || $is_tutor): ?>
                                    <button type="button" class="btn-reject" onclick="openClubModalReject(<?= htmlspecialchars(json_encode($club)) ?>, this)">
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
                                    <button type="button" class="btn-view-details" onclick="openEventModal(<?= htmlspecialchars(json_encode($event)) ?>)">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <?php 
                                        // Déterminer le nom du bouton selon le rôle
                                        if ($is_admin) {
                                            $btn_name = 'validate_event_admin';
                                        } elseif ($is_bde) {
                                            $btn_name = 'validate_event_bde';
                                        } else {
                                            $btn_name = 'validate_event_tutor';
                                        }
                                        ?>
                                        <button type="submit" name="<?= $btn_name ?>" class="btn-approve">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <?php if ($is_admin): ?>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="action" value="force_approve">
                                        <button type="submit" name="validate_event_admin" class="btn-force" title="Valider sans attendre le tuteur">
                                            <i class="fas fa-bolt"></i> Forcer
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn-reject" onclick="openEventModalReject(<?= htmlspecialchars(json_encode($event)) ?>)">
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

    <!-- Club Detail Modal -->
    <div class="modal-overlay" id="clubModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-with-logo">
                    <div class="modal-club-logo" id="modalClubLogo">
                        <i class="fas fa-building no-logo"></i>
                    </div>
                    <h2><span id="modalClubName">Détails du club</span></h2>
                </div>
                <button class="modal-close" onclick="closeModal('clubModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4>Type de club</h4>
                        <p id="modalClubType">-</p>
                    </div>
                    <div class="detail-section">
                        <h4>Campus</h4>
                        <p id="modalClubCampus">-</p>
                    </div>
                </div>
                <div class="detail-section">
                    <h4>Email de contact</h4>
                    <p id="modalClubEmail">-</p>
                </div>
                <div class="detail-section">
                    <h4>Description</h4>
                    <p id="modalClubDescription">-</p>
                </div>
                <div class="detail-section">
                    <h4>Membres</h4>
                    <div id="modalClubMembers" class="members-list"></div>
                </div>
            </div>
            <!-- Rejection Reason Section -->
            <div class="modal-reject-section" id="clubRejectSection" style="display: none;">
                <div class="reject-reason-box">
                    <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                    <p class="reject-hint">Expliquez la raison du rejet pour aider le créateur à améliorer sa demande.</p>
                    <textarea id="clubRejectMotif" name="motif_preview" class="reject-textarea" rows="3" placeholder="Ex: Description insuffisante, objectifs pas clairs, doublon avec un club existant..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="clubCancelReject" style="display: none;" onclick="cancelClubReject()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn-reject-init" id="clubRejectInit" onclick="showClubRejectForm()">
                    <i class="fas fa-times"></i> Rejeter
                </button>
                <form method="POST" id="modalClubRejectForm" style="display: none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="modalClubIdReject" value="">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="motif" id="clubMotifInput" value="">
                    <button type="submit" name="validate_club_admin" class="btn-reject">
                        <i class="fas fa-times"></i> Confirmer le rejet
                    </button>
                </form>
                <?php if ($is_admin): ?>
                <form method="POST" id="modalClubForceForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="modalClubIdForce" value="">
                    <input type="hidden" name="action" value="force_approve">
                    <button type="submit" name="validate_club_admin" class="btn-force" title="Valider immédiatement sans attendre le tuteur">
                        <i class="fas fa-bolt"></i> Forcer la validation
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" id="modalClubApproveForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="modalClubIdApprove" value="">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" name="validate_club_admin" class="btn-approve">
                        <i class="fas fa-check"></i> Approuver ce club
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-alt"></i> <span id="modalEventName">Détails de l'événement</span></h2>
                <button class="modal-close" onclick="closeModal('eventModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-section">
                    <h4>Club organisateur</h4>
                    <p id="modalEventClub">-</p>
                </div>
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4>Date et heure</h4>
                        <p id="modalEventDate">-</p>
                    </div>
                    <div class="detail-section">
                        <h4>Campus</h4>
                        <p id="modalEventCampus">-</p>
                    </div>
                </div>
                <div class="detail-section">
                    <h4>Lieu</h4>
                    <p id="modalEventLieu">-</p>
                </div>
                <div class="detail-section">
                    <h4>Description</h4>
                    <p id="modalEventDescription">-</p>
                </div>
            </div>
            <!-- Rejection Reason Section -->
            <div class="modal-reject-section" id="eventRejectSection" style="display: none;">
                <div class="reject-reason-box">
                    <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                    <p class="reject-hint">Expliquez la raison du rejet pour aider l'organisateur à améliorer sa demande.</p>
                    <textarea id="eventRejectMotif" name="motif_preview" class="reject-textarea" rows="3" placeholder="Ex: Date non disponible, lieu inapproprié, informations manquantes..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="eventCancelReject" style="display: none;" onclick="cancelEventReject()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn-reject-init" id="eventRejectInit" onclick="showEventRejectForm()">
                    <i class="fas fa-times"></i> Rejeter
                </button>
                <form method="POST" id="modalEventRejectForm" style="display: none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="event_id" id="modalEventIdReject" value="">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="motif" id="eventMotifInput" value="">
                        <button type="submit" 
                            name="<?php 
                                if($is_admin) echo 'validate_event_admin'; 
                                elseif($is_bde) echo 'validate_event_bde'; 
                                else echo 'validate_event_tutor'; 
                            ?>" 
                            class="btn-reject">
                            <i class="fas fa-times"></i> Confirmer le rejet
                        </button>                       
                    </button>
                </form>
                <?php if ($is_admin): ?>
                <form method="POST" id="modalEventForceForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="event_id" id="modalEventIdForce" value="">
                    <input type="hidden" name="action" value="force_approve">
                    <button type="submit" name="validate_event_admin" class="btn-force" title="Valider immédiatement sans attendre le tuteur">
                        <i class="fas fa-bolt"></i> Forcer la validation
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" id="modalEventApproveForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="event_id" id="modalEventIdApprove" value="">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" name="<?= $is_admin ? 'validate_event_admin' : 'validate_event_tutor' ?>" class="btn-approve">
                        <i class="fas fa-check"></i> Approuver cet événement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
        (function() {
            'use strict';

            // --- UTILITAIRES ---
            function esc(str) {
                if (!str) return '';
                return String(str).replace(/[&<>"']/g, function(c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]);
                });
            }

            // Aide pour remplir le texte ou la valeur d'un élément s'il existe
            const setContent = (id, value, isInput = false) => {
                const el = document.getElementById(id);
                if (el) {
                    if (isInput) el.value = value;
                    else el.textContent = value;
                }
            };

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

                    if (matchesSearch && matchesFilter) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
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

            // --- FONCTIONS DE MODALE (CLUBS) ---
            window.openClubModal = function(club, el) {
                if (!club) return;

                setContent('modalClubName', club.nom_club || 'Club sans nom');
                setContent('modalClubType', club.type_club || '-');
                setContent('modalClubCampus', club.campus || '-');
                setContent('modalClubEmail', club.mail || 'Non renseigné');
                setContent('modalClubDescription', club.description || 'Aucune description fournie.');
                
                // IDs pour les formulaires
                setContent('modalClubIdApprove', club.club_id, true);
                setContent('modalClubIdReject', club.club_id, true);
                setContent('modalClubIdForce', club.club_id, true);

                // Logo
                const logoContainer = document.getElementById('modalClubLogo');
                if (logoContainer) {
                    logoContainer.innerHTML = club.logo_club 
                        ? `<img src="${esc(club.logo_club)}" alt="Logo">` 
                        : '<i class="fas fa-building no-logo"></i>';
                }

                // Membres (Récupération via data-attribute de la carte)
                const membersContainer = document.getElementById('modalClubMembers');
                if (membersContainer) {
                    membersContainer.innerHTML = '';
                    let members = [];
                    if (el) {
                        const card = el.closest('.validation-card-advanced');
                        if (card && card.dataset.members) {
                            try { members = JSON.parse(card.dataset.members); } catch (e) { console.error("Erreur JSON membres", e); }
                        }
                    }

                    if (members.length > 0) {
                        members.forEach(m => {
                            const row = document.createElement('div');
                            row.className = 'member-row';
                            row.innerHTML = `
                                <span class="member-name">${esc(m.prenom)} ${esc(m.nom)}</span>
                                <span class="member-role">${esc(m.fonction)}</span>
                                ${m.promo ? `<span class="member-promo">${esc(m.promo)}</span>` : ''}
                            `;
                            membersContainer.appendChild(row);
                        });
                    } else {
                        membersContainer.innerHTML = '<div class="text-muted">Aucun membre renseigné.</div>';
                    }
                }

                document.getElementById('clubModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            // --- FONCTIONS DE MODALE (EVENTS) ---
            window.openEventModal = function(event) {
                if (!event) return;

                setContent('modalEventName', event.titre || 'Événement sans titre');
                setContent('modalEventClub', event.nom_club || '-');
                setContent('modalEventCampus', event.campus || '-');
                setContent('modalEventLieu', event.lieu || 'Non renseigné');
                setContent('modalEventDescription', event.description || 'Aucune description fournie.');
                
                setContent('modalEventIdApprove', event.event_id, true);
                setContent('modalEventIdReject', event.event_id, true);
                setContent('modalEventIdForce', event.event_id, true);

                const dateEl = document.getElementById('modalEventDate');
                if (dateEl && event.date_ev) {
                    const d = new Date(event.date_ev);
                    dateEl.textContent = d.toLocaleDateString('fr-FR', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                }

                document.getElementById('eventModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            // --- GESTION DU REJET ---
            window.showClubRejectForm = function() {
                document.getElementById('clubRejectSection').style.display = 'block';
                document.getElementById('clubRejectInit').style.display = 'none';
                document.getElementById('clubCancelReject').style.display = 'flex';
                document.getElementById('modalClubRejectForm').style.display = 'inline';
                document.getElementById('modalClubApproveForm').style.display = 'none';
            };

            window.cancelClubReject = function() {
                document.getElementById('clubRejectSection').style.display = 'none';
                document.getElementById('clubRejectInit').style.display = 'flex';
                document.getElementById('clubCancelReject').style.display = 'none';
                document.getElementById('modalClubRejectForm').style.display = 'none';
                document.getElementById('modalClubApproveForm').style.display = 'inline';
                const textarea = document.getElementById('clubRejectMotif');
                if (textarea) textarea.value = '';
            };

            window.showEventRejectForm = function() {
                document.getElementById('eventRejectSection').style.display = 'block';
                document.getElementById('eventRejectInit').style.display = 'none';
                document.getElementById('eventCancelReject').style.display = 'flex';
                document.getElementById('modalEventRejectForm').style.display = 'inline';
                document.getElementById('modalEventApproveForm').style.display = 'none';
            };

            window.cancelEventReject = function() {
                document.getElementById('eventRejectSection').style.display = 'none';
                document.getElementById('eventRejectInit').style.display = 'flex';
                document.getElementById('eventCancelReject').style.display = 'none';
                document.getElementById('modalEventRejectForm').style.display = 'none';
                document.getElementById('modalEventApproveForm').style.display = 'inline';
                const textarea = document.getElementById('eventRejectMotif');
                if (textarea) textarea.value = '';
            };

            // Fonctions spécifiques pour ouvrir directement en mode rejet
            window.openClubModalReject = function(club, el) {
                window.openClubModal(club, el);
                setTimeout(window.showClubRejectForm, 50);
            };

            window.openEventModalReject = function(event) {
                window.openEventModal(event);
                setTimeout(window.showEventRejectForm, 50);
            };

            // --- FERMETURE ET EVENTS ---
            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                    if (modalId === 'clubModal') window.cancelClubReject();
                    if (modalId === 'eventModal') window.cancelEventReject();
                }
            };

            // Transfert du motif vers l'input caché avant envoi
            const setupSubmit = (formId, textareaId, hiddenId) => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const text = document.getElementById(textareaId).value.trim();
                        if (!text) {
                            alert("Veuillez saisir un motif de rejet.");
                            e.preventDefault();
                            return;
                        }
                        document.getElementById(hiddenId).value = text;
                    });
                }
            };

            setupSubmit('modalClubRejectForm', 'clubRejectMotif', 'clubMotifInput');
            setupSubmit('modalEventRejectForm', 'eventRejectMotif', 'eventMotifInput');

            // Fermeture clic extérieur et Echap
            window.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal-overlay')) window.closeModal(e.target.id);
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const activeModal = document.querySelector('.modal-overlay.active');
                    if (activeModal) window.closeModal(activeModal.id);
                }
            });

        })();
</script>
</body>
</html>
