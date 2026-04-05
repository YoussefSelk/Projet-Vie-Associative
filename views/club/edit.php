<?php
/**
 * Édition d'un club en attente ou refusé
 * * Permet à un membre du bureau (Président ou Secrétaire) de modifier les informations d'un club
 * et de le (re)soumettre à validation.
 * * Le Président et le Secrétaire peuvent modifier le club.
 * Une fois modifié, le club retourne en état de validation.
 * * Variables attendues :
 * - $club : Données actuelles du club
 * - $error_msg : Message d'erreur éventuel
 * - $success_msg : Message de succès éventuel
 * * @package Views/Club
 */
$pageTitle = 'Modifier un club - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'clubs'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="page-container">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Modifier ma demande de club</h1>
                <p class="subtitle">Mettez à jour les informations de votre club</p>
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

            <?php if ($club): ?>
                <?php
                $isEditPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_club']);
                $formNomClub = $isEditPost ? ($_POST['nom_club'] ?? '') : ($club['nom_club'] ?? '');
                $formTypeClub = $isEditPost ? ($_POST['type_club'] ?? '') : ($club['type_club'] ?? '');
                $formCampus = $isEditPost ? ($_POST['campus'] ?? '') : ($club['campus'] ?? '');
                $formDescription = $isEditPost ? ($_POST['description'] ?? '') : ($club['description'] ?? '');
                ?>
                <div class="edit-container">
                    <div class="warning-box" style="background: #e8f4fd; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 20px;">
                        <h5 style="margin-top: 0; color: #1e3a8a;"><i class="fas fa-info-circle"></i> Information importante</h5>
                        <p style="margin-bottom: 0;">
                            Une fois vos modifications sauvegardées, votre demande de club retournera en attente de validation.
                            Un administrateur et un tuteur examineront à nouveau votre demande. Le rôle de Président est conservé automatiquement.
                        </p>
                    </div>

                    <form method="POST" id="editClubForm">
                        <?= Security::csrfField() ?>
                        <div class="form-group">
                            <label for="nom_club">Nom du club <span style="color: red;">*</span></label>
                            <input 
                                type="text" 
                                id="nom_club" 
                                name="nom_club" 
                                class="form-control"
                                value="<?= htmlspecialchars($formNomClub) ?>" 
                                required
                                placeholder="Entrez le nom de votre club"
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="type_club">Type de club <span style="color: red;">*</span></label>
                                <select id="type_club" name="type_club" class="form-control" required>
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="Culturel" <?= ($formTypeClub === 'Culturel') ? 'selected' : '' ?>>Culturel</option>
                                    <option value="Sportif" <?= ($formTypeClub === 'Sportif') ? 'selected' : '' ?>>Sportif</option>
                                    <option value="Artistique" <?= ($formTypeClub === 'Artistique') ? 'selected' : '' ?>>Artistique</option>
                                    <option value="Gastronomique" <?= ($formTypeClub === 'Gastronomique') ? 'selected' : '' ?>>Gastronomique</option>
                                    <option value="Humanitaire" <?= ($formTypeClub === 'Humanitaire') ? 'selected' : '' ?>>Humanitaire</option>
                                    <option value="Professionnel" <?= ($formTypeClub === 'Professionnel') ? 'selected' : '' ?>>Professionnel</option>
                                    <option value="Autre" <?= ($formTypeClub === 'Autre') ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="campus">Campus <span style="color: red;">*</span></label>
                                <select id="campus" name="campus" class="form-control" required>
                                    <option value="">-- Sélectionnez un campus --</option>
                                    <option value="Calais" <?= ($formCampus === 'Calais') ? 'selected' : '' ?>>Calais</option>
                                    <option value="Longuenesse" <?= ($formCampus === 'Longuenesse') ? 'selected' : '' ?>>Longuenesse</option>
                                    <option value="Dunkerque" <?= ($formCampus === 'Dunkerque') ? 'selected' : '' ?>>Dunkerque</option>
                                    <option value="Boulogne" <?= ($formCampus === 'Boulogne') ? 'selected' : '' ?>>Boulogne</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span style="color: red;">*</span></label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="form-control"
                                rows="4"
                                required
                                placeholder="Décrivez les objectifs et activités de votre club"
                            ><?= htmlspecialchars($formDescription) ?></textarea>
                            <small style="color: #666;">Décrivez les objectifs, les activités et l'impact de votre club.</small>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <h4><i class="fas fa-users"></i> Membres du club</h4>
                            <p style="color: #666; margin-bottom: 15px;">
                                Ajoutez ou modifiez les membres de votre club. 
                                <strong>Le Président est automatiquement conservé et n'apparaît pas dans cette liste.</strong>
                            </p>

                            <div id="requiredRolesNotif" style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 15px;margin-bottom:15px;">
                                <p style="margin:0 0 8px;font-weight:600;" id="requiredRolesMsg">
                                    <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
                                    Rôles obligatoires manquants :
                                </p>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <span id="badge_Tresorier"  style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:.85em;font-weight:600;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-times-circle"></i> Trésorier</span>
                                    <span id="badge_Secretaire" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:.85em;font-weight:600;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-times-circle"></i> Secrétaire</span>
                                </div>
                            </div>

                            <div id="soutenanceQuotaNotif" style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:12px 15px;margin-bottom:15px;">
                                <p id="soutenanceQuotaMsg" style="margin:0;font-weight:600;color:#1e3a8a;">
                                    <i class="fas fa-graduation-cap"></i> Soutenance : 0 / 5
                                </p>
                                <small style="color:#1d4ed8;">Le Président est conservé automatiquement et compte dans le quota si sa case soutenance est active.</small>
                            </div>
                            
                            <h5 style="margin-top: 20px; margin-bottom: 10px;"><i class="fas fa-user-plus"></i> Autres membres</h5>
                            <div id="membersList" class="members-form-list">
                                <?php if (!empty($currentMembers)): ?>
                                    <?php foreach ($currentMembers as $index => $member): ?>
                                        <?php
                                        $role = $member['fonction'] ?? 'Membre';
                                        $isSoutenance = !empty($member['soutenance']) ? 1 : 0;
                                        $memberFullName = trim(($member['prenom'] ?? '') . ' ' . ($member['nom'] ?? ''));
                                        $memberMeta = trim((string)($member['promo'] ?? ''));
                                        if ($memberMeta === '' && !empty($member['mail'])) {
                                            $memberMeta = trim((string)$member['mail']);
                                        }
                                        ?>
                                        <div class="member-form-row" id="member_<?= (int)$index ?>" data-user-id="<?= (int)$member['id'] ?>" <?= in_array($role, ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"], true) ? 'data-unique-role="' . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                                            <input type="hidden" name="members[<?= (int)$index ?>][user_id]" value="<?= (int)$member['id'] ?>">
                                            <input type="hidden" name="members[<?= (int)$index ?>][role]" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="members[<?= (int)$index ?>][soutenance]" value="<?= $isSoutenance ?>">
                                            <div class="member-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="member-details">
                                                <span class="member-name"><?= htmlspecialchars($memberFullName) ?></span>
                                                <?php if ($memberMeta !== ''): ?>
                                                    <small><?= htmlspecialchars($memberMeta) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="member-role-badge"><?= htmlspecialchars($role) ?></span>
                                            <?php if ($isSoutenance === 1): ?>
                                                <span class="member-role-badge" style="background-color: #10b981; margin-left: 5px;"><i class="fas fa-graduation-cap"></i> Soutenance</span>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-member" data-member-id="<?= (int)$index ?>" data-user-id="<?= (int)$member['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="member-add-row">
                                <div class="member-search-container">
                                    <input type="text"
                                        id="memberSearchInput"
                                        class="form-control"
                                        placeholder="Rechercher un membre par nom..."
                                        autocomplete="off">
                                    <div id="memberSuggestions" class="autocomplete-suggestions"></div>
                                </div>

                                <div class="role-select-container">
                                    <select id="newMemberRole" class="form-control role-select">
                                        <option value="Membre">Membre</option>
                                        <option value="Vice-Président">Vice-Président</option>
                                        <option value="Trésorier">Trésorier</option>
                                        <option value="Charge d'événement / communication">Charge d'événement / communication</option>
                                        <option value="Secrétaire">Secrétaire</option>
                                    </select>
                                </div>

                                <div class="soutenance-check-container">
                                    <label>
                                        <input type="checkbox" id="newMemberSoutenance" value="1">
                                        <i class="fas fa-graduation-cap"></i> Soutenance
                                    </label>
                                </div>

                                <button type="button" class="btn btn-primary btn-add-disabled" id="addMemberBtn" disabled>
                                    <i class="fas fa-plus"></i> Ajouter
                                </button>
                            </div>
                        </div>

                        <script>
                        let memberIndex = <?= !empty($currentMembers) ? count($currentMembers) : 0 ?>;
                        const users = <?= json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                        const MAX_SOUTENANCE_MEMBERS = 5;
                        const PRESIDENT_SOUTENANCE = <?= (int)($presidentSoutenance ?? 0) ?>;

                        // Rôles uniques à gérer (Le président est géré en backend)
                        const UNIQUE_ROLES = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
                        const PRINCIPAL_ROLES = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
                        const REQUIRED_ROLES = [
                            { key: 'Trésorier', badgeId: 'badge_Tresorier',  label: 'Trésorier'  },
                            { key: 'Secrétaire', badgeId: 'badge_Secretaire', label: 'Secrétaire' }
                        ];

                        function setAddButtonEnabled(enabled) {
                            addBtn.disabled = !enabled;
                            if (enabled) {
                                addBtn.classList.remove('btn-add-disabled');
                            } else {
                                addBtn.classList.add('btn-add-disabled');
                            }
                        }

                        function getRoleCounts() {
                            const counts = {};
                            const roleInputs = Array.from(document.querySelectorAll('#membersList input[name$="[role]"]'));
                            roleInputs.forEach(input => {
                                const role = input.value;
                                if (UNIQUE_ROLES.includes(role)) {
                                    counts[role] = (counts[role] || 0) + 1;
                                }
                            });
                            return counts;
                        }

                        function syncRoleDropdown() {
                            const counts = getRoleCounts();

                            Array.from(roleSelect.options).forEach(option => {
                                if (!UNIQUE_ROLES.includes(option.value)) {
                                    option.disabled = false;
                                    return;
                                }
                                const isTaken = (counts[option.value] || 0) > 0;
                                option.disabled = isTaken;
                                option.text = isTaken ? option.value + ' (déjà attribué)' : option.value;
                            });

                            if (roleSelect.options[roleSelect.selectedIndex] && roleSelect.options[roleSelect.selectedIndex].disabled) {
                                roleSelect.value = 'Membre';
                            }

                            updateRequiredRoles(counts);
                        }

                        function syncSoutenanceInputState() {
                            if (roleSelect.value === 'Membre') {
                                newMemberSoutenance.checked = false;
                                soutenanceContainer.style.display = 'none';
                            } else {
                                soutenanceContainer.style.display = '';
                            }
                        }

                        function resetMemberAddForm() {
                            searchInput.value = '';
                            suggestionsDiv.style.display = 'none';
                            selectedUser = null;
                            roleSelect.value = 'Membre';
                            newMemberSoutenance.checked = false;
                            syncSoutenanceInputState();
                            setAddButtonEnabled(false);
                        }

                        function escapeHtml(text) {
                            var div = document.createElement('div');
                            div.textContent = text || '';
                            return div.innerHTML;
                        }

                        function showSuggestions(matches) {
                            if (matches.length === 0) {
                                suggestionsDiv.innerHTML = '<div class="no-results"><i class="fas fa-search"></i> Aucun résultat</div>';
                            } else {
                                var html = '';
                                for (var i = 0; i < matches.length; i++) {
                                    var u = matches[i];
                                    html += '<div class="suggestion-item" data-id="' + u.id + '" data-name="' + escapeHtml(u.name) + '" data-email="' + escapeHtml(u.email) + '" data-promo="' + escapeHtml(u.promo) + '">' +
                                        '<div class="suggestion-name"><i class="fas fa-user"></i>' + escapeHtml(u.name) + '</div>' +
                                        '<div class="suggestion-details">' + escapeHtml(u.promo || 'N/A') + ' • ' + escapeHtml(u.email || '') + '</div>' +
                                    '</div>';
                                }
                                suggestionsDiv.innerHTML = html;
                            }
                            suggestionsDiv.style.display = 'block';
                        }

                        function addMember() {
                            if (!selectedUser) {
                                alert('Veuillez sélectionner un membre dans les suggestions.');
                                return;
                            }

                            if (addedMembers[selectedUser.id]) {
                                alert('Ce membre a déjà été ajouté.');
                                return;
                            }

                            var role = roleSelect.value;
                            var counts = getRoleCounts();
                            if (UNIQUE_ROLES.includes(role) && (counts[role] || 0) > 0) {
                                alert('Le rôle "' + role + '" est déjà attribué dans ce club.');
                                return;
                            }

                            var isSoutenance = PRINCIPAL_ROLES.includes(role) && newMemberSoutenance.checked;
                            if (isSoutenance && getTotalSoutenanceCount() >= MAX_SOUTENANCE_MEMBERS) {
                                alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                                return;
                            }

                            var soutenanceValue = isSoutenance ? 1 : 0;
                            var metaParts = [];
                            if (selectedUser.promo) {
                                metaParts.push(escapeHtml(selectedUser.promo));
                            }
                            if (selectedUser.email) {
                                metaParts.push(escapeHtml(selectedUser.email));
                            }
                            var memberMetaHtml = metaParts.length > 0
                                ? '<small>' + metaParts.join(' • ') + '</small>'
                                : '';
                            var soutenanceBadge = isSoutenance
                                ? '<span class="member-role-badge" style="background-color: #10b981; margin-left: 5px;"><i class="fas fa-graduation-cap"></i> Soutenance</span>'
                                : '';

                            var row = document.createElement('div');
                            row.className = 'member-form-row';
                            row.id = 'member_' + memberIndex;
                            row.setAttribute('data-user-id', selectedUser.id);
                            if (UNIQUE_ROLES.includes(role)) {
                                row.setAttribute('data-unique-role', role);
                            }

                            row.innerHTML =
                                '<input type="hidden" name="members[' + memberIndex + '][user_id]" value="' + selectedUser.id + '">' +
                                '<input type="hidden" name="members[' + memberIndex + '][role]" value="' + escapeHtml(role) + '">' +
                                '<input type="hidden" name="members[' + memberIndex + '][soutenance]" value="' + soutenanceValue + '">' +
                                '<div class="member-avatar"><i class="fas fa-user"></i></div>' +
                                '<div class="member-details">' +
                                    '<span class="member-name">' + escapeHtml(selectedUser.name) + '</span>' +
                                    memberMetaHtml +
                                '</div>' +
                                '<span class="member-role-badge">' + escapeHtml(role) + '</span>' +
                                soutenanceBadge +
                                '<button type="button" class="btn btn-danger btn-sm btn-remove-member" data-member-id="' + memberIndex + '" data-user-id="' + selectedUser.id + '"><i class="fas fa-trash"></i></button>';

                            membersList.appendChild(row);
                            addedMembers[selectedUser.id] = true;
                            memberIndex++;

                            resetMemberAddForm();
                            syncRoleDropdown();
                            updateSoutenanceQuotaStatus();
                        }

                        function removeMember(memberId, userId) {
                            var row = document.getElementById('member_' + memberId);
                            if (!row) {
                                return;
                            }
                            row.remove();
                            delete addedMembers[userId];
                            syncRoleDropdown();
                            updateSoutenanceQuotaStatus();
                        }

                        function getDynamicSoutenanceCount() {
                            return Array.from(document.querySelectorAll('#membersList input[name$="[soutenance]"]'))
                                .filter(input => parseInt(input.value, 10) === 1)
                                .length;
                        }

                        function getTotalSoutenanceCount() {
                            return PRESIDENT_SOUTENANCE + getDynamicSoutenanceCount();
                        }

                        function updateSoutenanceQuotaStatus() {
                            var notif = document.getElementById('soutenanceQuotaNotif');
                            var msg = document.getElementById('soutenanceQuotaMsg');
                            if (!notif || !msg) return;

                            var total = getTotalSoutenanceCount();
                            msg.innerHTML = '<i class="fas fa-graduation-cap"></i> Soutenance : ' + total + ' / ' + MAX_SOUTENANCE_MEMBERS;

                            if (total >= MAX_SOUTENANCE_MEMBERS) {
                                notif.style.background = '#fef2f2';
                                notif.style.borderColor = '#fca5a5';
                                msg.style.color = '#991b1b';
                            } else {
                                notif.style.background = '#eff6ff';
                                notif.style.borderColor = '#93c5fd';
                                msg.style.color = '#1e3a8a';
                            }
                        }

                        function updateRequiredRoles(counts) {
                            var notif = document.getElementById('requiredRolesNotif');
                            var msg   = document.getElementById('requiredRolesMsg');
                            if (!notif) return;

                            var allFilled = true;
                            REQUIRED_ROLES.forEach(r => {
                                var badge = document.getElementById(r.badgeId);
                                if (!badge) return;
                                if (counts[r.key] > 0) {
                                    badge.style.background = '#d1fae5';
                                    badge.style.color      = '#065f46';
                                    badge.style.borderColor= '#6ee7b7';
                                    badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + r.label;
                                } else {
                                    badge.style.background = '#fee2e2';
                                    badge.style.color      = '#dc2626';
                                    badge.style.borderColor= '#fca5a5';
                                    badge.innerHTML = '<i class="fas fa-times-circle"></i> ' + r.label;
                                    allFilled = false;
                                }
                            });

                            if (allFilled) {
                                notif.style.background   = '#d1fae5';
                                notif.style.borderColor  = '#6ee7b7';
                                msg.innerHTML = '<i class="fas fa-check-circle" style="color:#10b981;"></i> Tous les rôles obligatoires sont attribués.';
                            } else {
                                notif.style.background  = '#fff3cd';
                                notif.style.borderColor = '#ffc107';
                                msg.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> Rôles obligatoires — chacun doit être attribué à un membre :';
                            }
                        }

                        const usersData = users.map(function(u) {
                            return {
                                id: String(u.id),
                                name: (u.prenom || '') + ' ' + (u.nom || ''),
                                email: u.mail || '',
                                promo: u.promo || ''
                            };
                        });
                        const addedMembers = {};
                        let selectedUser = null;

                        const searchInput = document.getElementById('memberSearchInput');
                        const suggestionsDiv = document.getElementById('memberSuggestions');
                        const addBtn = document.getElementById('addMemberBtn');
                        const membersList = document.getElementById('membersList');
                        const roleSelect = document.getElementById('newMemberRole');
                        const newMemberSoutenance = document.getElementById('newMemberSoutenance');
                        const soutenanceContainer = document.querySelector('.soutenance-check-container');

                        Array.from(membersList.querySelectorAll('.member-form-row')).forEach(function(row) {
                            var userId = row.getAttribute('data-user-id');
                            if (userId) {
                                addedMembers[String(userId)] = true;
                            }
                        });

                        searchInput.addEventListener('input', function() {
                            var query = this.value.toLowerCase().trim();
                            selectedUser = null;
                            setAddButtonEnabled(false);

                            if (query.length < 2) {
                                suggestionsDiv.style.display = 'none';
                                return;
                            }

                            var matches = [];
                            for (var i = 0; i < usersData.length && matches.length < 10; i++) {
                                var u = usersData[i];
                                if (addedMembers[u.id]) {
                                    continue;
                                }
                                if (u.name.toLowerCase().indexOf(query) !== -1 ||
                                    u.email.toLowerCase().indexOf(query) !== -1 ||
                                    u.promo.toLowerCase().indexOf(query) !== -1) {
                                    matches.push(u);
                                }
                            }

                            showSuggestions(matches);
                        });

                        suggestionsDiv.addEventListener('click', function(e) {
                            var item = e.target.closest('.suggestion-item');
                            if (!item) {
                                return;
                            }

                            selectedUser = {
                                id: item.getAttribute('data-id'),
                                name: item.getAttribute('data-name'),
                                email: item.getAttribute('data-email'),
                                promo: item.getAttribute('data-promo')
                            };

                            searchInput.value = selectedUser.name;
                            suggestionsDiv.style.display = 'none';
                            setAddButtonEnabled(true);
                        });

                        document.addEventListener('click', function(e) {
                            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                                suggestionsDiv.style.display = 'none';
                            }
                        });

                        roleSelect.addEventListener('change', function() {
                            syncSoutenanceInputState();
                        });

                        addBtn.addEventListener('click', addMember);

                        membersList.addEventListener('click', function(e) {
                            var removeBtn = e.target.closest('.btn-remove-member');
                            if (!removeBtn) {
                                return;
                            }
                            var memberId = removeBtn.getAttribute('data-member-id');
                            var userId = removeBtn.getAttribute('data-user-id');
                            removeMember(memberId, userId);
                        });

                        searchInput.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                if (selectedUser) {
                                    addMember();
                                }
                            }
                        });

                        newMemberSoutenance.addEventListener('change', function() {
                            if (this.checked && getTotalSoutenanceCount() >= MAX_SOUTENANCE_MEMBERS) {
                                this.checked = false;
                                alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                            }
                            updateSoutenanceQuotaStatus();
                        });

                        // Validation du formulaire pour vérifier les rôles obligatoires
                        document.getElementById('editClubForm').addEventListener('submit', function(e) {
                            const counts = getRoleCounts();

                            var missingRoles = [];
                            REQUIRED_ROLES.forEach(r => {
                                if (!counts[r.key] || counts[r.key] === 0) {
                                    missingRoles.push(r.label);
                                }
                            });

                            if (missingRoles.length > 0) {
                                e.preventDefault();
                                alert('Les rôles suivants sont obligatoires et non attribués :\n• ' + missingRoles.join('\n• ') + '\n\nVeuillez les assigner avant de sauvegarder.');
                                var notif = document.getElementById('requiredRolesNotif');
                                if (notif) notif.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                return false;
                            }

                            if (getTotalSoutenanceCount() > MAX_SOUTENANCE_MEMBERS) {
                                e.preventDefault();
                                alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                                var soutenanceNotif = document.getElementById('soutenanceQuotaNotif');
                                if (soutenanceNotif) soutenanceNotif.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                return false;
                            }
                        });

                        syncSoutenanceInputState();
                        syncRoleDropdown();
                        updateSoutenanceQuotaStatus();
                        </script>

                        <div class="form-actions" style="margin-top: 30px;">
                            <a href="?page=my-clubs" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" name="update_club" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer et resoumettre
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>