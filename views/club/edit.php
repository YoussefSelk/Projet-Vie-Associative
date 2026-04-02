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
                            
                            <div id="members-container">
                                <?php if (!empty($currentMembers)): ?>
                                    <?php foreach ($currentMembers as $index => $member): ?>
                                        <div class="member-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center; flex-wrap: wrap; background: #f8f9fa; padding: 10px; border-radius: 8px;">
                                            <select name="members[<?= $index ?>][user_id]" class="form-control" style="flex: 1; min-width: 200px;">
                                                <option value="">-- Sélectionner un membre --</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= ($member['id'] == $user['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                                        <?php if (!empty($user['promo'])): ?> (<?= htmlspecialchars($user['promo']) ?>)<?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            
                                            <select name="members[<?= $index ?>][role]" class="form-control" style="flex: 0 0 auto; width: 200px;">
                                                <option value="Membre" <?= ($member['fonction'] == 'Membre') ? 'selected' : '' ?>>Membre</option>
                                                <option value="Vice-Président" <?= ($member['fonction'] == 'Vice-Président') ? 'selected' : '' ?>>Vice-Président</option>
                                                <option value="Trésorier" <?= ($member['fonction'] == 'Trésorier') ? 'selected' : '' ?>>Trésorier</option>
                                                <option value="Secrétaire" <?= ($member['fonction'] == 'Secrétaire') ? 'selected' : '' ?>>Secrétaire</option>
                                                <option value="Charge d'événement / communication" <?= ($member['fonction'] == "Charge d'événement / communication") ? 'selected' : '' ?>>Charge d'événement / communication</option>
                                            </select>
                                            
                                            <label class="form-check" style="margin: 0; white-space: nowrap; flex: 0 0 auto;">
                                                <input type="checkbox" name="members[<?= $index ?>][soutenance]" value="1" <?= (!empty($member['soutenance'])) ? 'checked' : '' ?>>
                                                <i class="fas fa-graduation-cap"></i> Soutenance
                                            </label>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.member-row').remove(); updateUniqueRoleOptions();" style="flex: 0 0 auto;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 10px;" onclick="addMemberRow()">
                                <i class="fas fa-plus"></i> Ajouter un membre
                            </button>
                        </div>

                        <script>
                        let memberIndex = <?= !empty($currentMembers) ? count($currentMembers) : 0 ?>;
                        const users = <?= json_encode($users ?? []) ?>;
                        const MAX_SOUTENANCE_MEMBERS = 5;
                        const PRESIDENT_SOUTENANCE = <?= (int)($presidentSoutenance ?? 0) ?>;

                        // Rôles uniques à gérer (Le président est géré en backend)
                        const UNIQUE_ROLES = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
                        const PRINCIPAL_ROLES = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
                        const REQUIRED_ROLES = [
                            { key: 'Trésorier', badgeId: 'badge_Tresorier',  label: 'Trésorier'  },
                            { key: 'Secrétaire', badgeId: 'badge_Secretaire', label: 'Secrétaire' }
                        ];

                        function getRoleSelects() {
                            return Array.from(document.querySelectorAll('#members-container select[name$="[role]"]'));
                        }

                        function updateUniqueRoleOptions() {
                            const roleSelects = getRoleSelects();
                            const counts = {};

                            roleSelects.forEach(select => {
                                const role = select.value;
                                if (UNIQUE_ROLES.includes(role)) {
                                    counts[role] = (counts[role] || 0) + 1;
                                }
                            });

                            // Mise à jour des options désactivées
                            roleSelects.forEach(select => {
                                Array.from(select.options).forEach(option => {
                                    if (!UNIQUE_ROLES.includes(option.value)) {
                                        option.disabled = false;
                                        return;
                                    }
                                    const isTaken = (counts[option.value] || 0) > 0;
                                    const isCurrent = select.value === option.value;
                                    option.disabled = isTaken && !isCurrent;
                                    option.text = option.disabled ? `${option.value} (déjà attribué)` : option.value;
                                });
                            });

                            // Mise à jour de l'affichage des rôles obligatoires
                            updateRequiredRoles(counts);
                            updateSoutenanceQuotaStatus();
                        }

                        function getDynamicSoutenanceCount() {
                            return Array.from(document.querySelectorAll('#members-container input[type="checkbox"][name$="[soutenance]"]'))
                                .filter(input => input.checked && !input.disabled)
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

                        function syncSoutenanceState(roleSelect, soutenanceInput, soutenanceLabel) {
                            if (!PRINCIPAL_ROLES.includes(roleSelect.value)) {
                                soutenanceInput.checked = false;
                                soutenanceInput.disabled = true;
                                if (soutenanceLabel) {
                                    soutenanceLabel.style.display = 'none';
                                }
                            } else {
                                soutenanceInput.disabled = false;
                                if (soutenanceLabel) {
                                    soutenanceLabel.style.display = '';
                                }
                            }
                        }

                        function wireMemberRow(row) {
                            const roleSelect = row.querySelector('select[name$="[role]"]');
                            const soutenanceInput = row.querySelector('input[type="checkbox"][name$="[soutenance]"]');
                            const soutenanceLabel = soutenanceInput ? soutenanceInput.closest('label') : null;

                            if (!roleSelect || !soutenanceInput) return;

                            roleSelect.dataset.previousRole = roleSelect.value;
                            syncSoutenanceState(roleSelect, soutenanceInput, soutenanceLabel);

                            roleSelect.addEventListener('change', () => {
                                const nextRole = roleSelect.value;
                                if (UNIQUE_ROLES.includes(nextRole)) {
                                    const duplicate = getRoleSelects().some(select =>
                                        select !== roleSelect && select.value === nextRole
                                    );
                                    if (duplicate) {
                                        alert(`Le rôle "${nextRole}" est déjà attribué. Il doit être unique.`);
                                        roleSelect.value = roleSelect.dataset.previousRole || 'Membre';
                                    }
                                }
                                roleSelect.dataset.previousRole = roleSelect.value;
                                syncSoutenanceState(roleSelect, soutenanceInput, soutenanceLabel);
                                updateUniqueRoleOptions();
                            });

                            if (soutenanceInput) {
                                soutenanceInput.addEventListener('change', () => {
                                    if (!PRINCIPAL_ROLES.includes(roleSelect.value)) {
                                        soutenanceInput.checked = false;
                                        return;
                                    }

                                    if (getTotalSoutenanceCount() > MAX_SOUTENANCE_MEMBERS) {
                                        soutenanceInput.checked = false;
                                        alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                                    }
                                    updateSoutenanceQuotaStatus();
                                });
                            }
                        }

                        function addMemberRow() {
                            const container = document.getElementById('members-container');
                            const row = document.createElement('div');
                            row.className = 'member-row';
                            row.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center; flex-wrap: wrap; background: #f8f9fa; padding: 10px; border-radius: 8px;';

                            let optionsHtml = '<option value="">-- Sélectionner un membre --</option>';
                            users.forEach(user => {
                                optionsHtml += `<option value="${user.id}">${user.nom} ${user.prenom}${user.promo ? ' (' + user.promo + ')' : ''}</option>`;
                            });

                            row.innerHTML = `
                                <select name="members[${memberIndex}][user_id]" class="form-control" style="flex: 1; min-width: 200px;">
                                    ${optionsHtml}
                                </select>
                                <select name="members[${memberIndex}][role]" class="form-control" style="flex: 0 0 auto; width: 200px;">
                                    <option value="Membre" selected>Membre</option>
                                    <option value="Vice-Président">Vice-Président</option>
                                    <option value="Trésorier">Trésorier</option>
                                    <option value="Secrétaire">Secrétaire</option>
                                    <option value="Charge d'événement / communication">Charge d'événement / communication</option>
                                </select>
                                <label class="form-check" style="margin: 0; white-space: nowrap; flex: 0 0 auto;">
                                    <input type="checkbox" name="members[${memberIndex}][soutenance]" value="1">
                                    <span><i class="fas fa-graduation-cap"></i> Soutenance</span>
                                </label>
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.member-row').remove(); updateUniqueRoleOptions();" style="flex: 0 0 auto;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;

                            container.appendChild(row);
                            wireMemberRow(row);
                            updateUniqueRoleOptions();
                            memberIndex++;
                        }

                        // Validation du formulaire pour vérifier les rôles obligatoires
                        document.getElementById('editClubForm').addEventListener('submit', function(e) {
                            const roleSelects = getRoleSelects();
                            const counts = {};
                            roleSelects.forEach(select => {
                                counts[select.value] = (counts[select.value] || 0) + 1;
                            });

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

                        document.querySelectorAll('#members-container .member-row').forEach(wireMemberRow);
                        updateUniqueRoleOptions();
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