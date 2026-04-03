<?php
/**
 * Formulaire de creation d'un nouveau club
 * 
 * Permet a un etudiant de proposer la creation d'un club :
 * - Informations de base (nom, type, description)
 * - Localisation (campus)
 * - Membres fondateurs avec autocomplétion
 * - Upload du logo (optionnel)
 * 
 * Le club cree sera en attente de validation par le BDE puis un tuteur.
 * 
 * Variables attendues :
 * - $error_msg : Message d'erreur eventuel
 * - $success_msg : Message de succes eventuel
 * - $tutors : Liste des tuteurs disponibles
 * - $users : Liste des utilisateurs pour l'autocomplétion
 * 
 * @package Views/Club
 */
$pageTitle = 'Créer un club - EILCO';
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
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Créer un nouveau club</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="form-modern" id="clubForm" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        
                        <!-- Logo Upload Section -->
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Logo du club (optionnel)</label>
                            <div class="logo-upload-container">
                                <div class="logo-preview" id="logoPreview">
                                    <i class="fas fa-camera placeholder-icon" id="logoPlaceholder"></i>
                                    <img id="logoImage" src="" alt="Aperçu du logo" style="display: none;">
                                </div>
                                <div class="logo-upload-info">
                                    <label class="logo-upload-btn">
                                        <i class="fas fa-upload"></i> Choisir une image
                                        <input type="file" name="logo" id="logoInput" accept="image/png, image/jpeg, image/gif, image/webp">
                                    </label>
                                    <ul class="logo-hints">
                                        <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Formats acceptés : PNG, JPG, GIF, WebP</li>
                                        <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Taille maximale : 2 Mo</li>
                                        <li><i class="fas fa-info-circle" style="color: #3b82f6;"></i> Idéalement carré (ex: 200x200 px)</li>
                                    </ul>
                                    <button type="button" class="remove-logo-btn" id="removeLogo">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Nom du club</label>
                                <input type="text" name="nom_club" class="form-control" placeholder="Ex: Club Robotique" required>
                            </div>
                            <div class="form-group">
                                <label for="type_club">Type de club <span style="color: red;">*</span></label>
                                <select id="type_club" name="type_club" required>
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="Culturel" <?= ($club['type_club'] === 'Culturel') ? 'selected' : '' ?>>Culturel</option>
                                    <option value="Sportif" <?= ($club['type_club'] === 'Sportif') ? 'selected' : '' ?>>Sportif</option>
                                    <option value="Artistique" <?= ($club['type_club'] === 'Artistique') ? 'selected' : '' ?>>Artistique</option>
                                    <option value="Gastronomique" <?= ($club['type_club'] === 'Gastronomique') ? 'selected' : '' ?>>Gastronomique</option>
                                    <option value="Humanitaire" <?= ($club['type_club'] === 'Humanitaire') ? 'selected' : '' ?>>Humanitaire</option>
                                    <option value="Professionnel" <?= ($club['type_club'] === 'Professionnel') ? 'selected' : '' ?>>Professionnel</option>
                                    <option value="Autre" <?= ($club['type_club'] === 'Autre') ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>
                        </div>



                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Décrivez les activités du club..." required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Campus</label>
                                <select name="campus" class="form-control" required>
                                    <option value="">Sélectionnez un campus...</option>
                                    <option value="Calais">Calais</option>
                                    <option value="Longuenesse">Longuenesse</option>
                                    <option value="Dunkerque">Dunkerque</option>
                                    <option value="Boulogne">Boulogne</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-chalkboard-teacher"></i> Tuteur (si disponible)</label>
                                <select name="tuteur_id" class="form-control">
                                    <option value="">Pas de tuteur assigné</option>
                                    <?php foreach ($tutors ?? [] as $tutor): ?>
                                        <option value="<?= $tutor['id'] ?>"><?= htmlspecialchars($tutor['prenom'] . ' ' . $tutor['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Projet Associatif Section -->
                        <!-- <div class="form-section">
                            <h4>
                                <i class="fas fa-project-diagram"></i> Soutenance
                                <span class="tooltip-trigger" title="La création d'un club nécessite au moins 3 personnes (vous + 2 autres membres). La soutenance est obligatoire pour certains clubs.">
                                    <i class="fas fa-question-circle"></i>
                                </span>
                            </h4>
                            <div class="form-check-group">
                                <label class="form-check">
                                    <input type="checkbox" name="projet_associatif" value="1" id="projetAssociatif">
                                    <span>Ce club fait partie d'un projet associatif</span>
                                </label>
                                <label class="form-check">
                                    <input type="checkbox" name="soutenance" value="1">
                                    <span>Soutenance prévue</span>
                                </label>
                            </div>
                            <div class="form-group" id="soutenanceDateGroup" style="display: none;">
                                <label><i class="fas fa-calendar-alt"></i> Date de soutenance</label>
                                <input type="date" name="soutenance_date" class="form-control">
                            </div>
                        </div> -->
                        
                        <!-- Section Membres Fondateurs -->
                        <div class="form-section" id="membersSection">
                            <h4>
                                <i class="fas fa-users"></i> Membres fondateurs 
                                <span id="memberCount">(0 membre)</span>
                            </h4>
                            
                            <!-- Votre rôle dans le club -->
                            <div class="form-group" style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                <label><i class="fas fa-user-tag"></i> Votre rôle dans le club</label>
                                <select name="creator_role" class="form-control" required>
                                    <option value="Président">Président</option>
                                    <option value="Vice-Président">Vice-Président</option>
                                    <option value="Trésorier">Trésorier</option>
                                    <option value="Secrétaire">Secrétaire</option>
                                    <option value="Charge d'événement / communication" selected>Charge d'événement / communication</option>
                                    <option value="Membre">Membre</option>
                                </select>
                                <div id="creatorSoutenanceContainer" style="margin-top: 10px;">
                                    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-weight:500;">
                                        <input type="checkbox" name="creator_soutenance" id="creatorSoutenance" value="1">
                                        <i class="fas fa-graduation-cap" style="color:#6366f1;"></i> Soutenance
                                    </label>
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Vous serez automatiquement ajouté avec ce rôle.</small>
                            </div>
                            
                            <!-- Rôles obligatoires -->
                            <div id="requiredRolesNotif" style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 15px;margin-bottom:15px;">
                                <p style="margin:0 0 8px;font-weight:600;" id="requiredRolesMsg">
                                    <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
                                    Rôles obligatoires — chacun doit être attribué à un membre :
                                </p>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <span id="badge_President"  style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:.85em;font-weight:600;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-times-circle"></i> Président</span>
                                    <span id="badge_Tresorier"  style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:.85em;font-weight:600;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-times-circle"></i> Trésorier</span>
                                    <span id="badge_Secretaire" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:.85em;font-weight:600;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-times-circle"></i> Secrétaire</span>
                                </div>
                            </div>

                            <div id="soutenanceQuotaNotif" style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:12px 15px;margin-bottom:15px;">
                                <p id="soutenanceQuotaMsg" style="margin:0;font-weight:600;color:#1e3a8a;">
                                    <i class="fas fa-graduation-cap"></i> Soutenance : 0 / 5
                                </p>
                                <small style="color:#1d4ed8;">La soutenance est autorisée uniquement pour les rôles principaux (pas pour Membre).</small>
                            </div>

                            <p class="text-muted" id="memberRequirement" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> <span id="memberRequirementText">Ajoutez au moins 2 autres membres fondateurs (vous + 2 autres minimum).</span>
                            </p>
                            <p class="text-success" id="memberRequirementOk" style="display: none;">
                                <i class="fas fa-check-circle"></i> Nombre de membres suffisant.
                            </p>
                            
                            <!-- Liste des membres ajoutés -->
                            <h5 style="margin-top: 20px; margin-bottom: 10px;"><i class="fas fa-user-plus"></i> Autres membres</h5>
                            <div id="membersList" class="members-form-list"></div>
                            
                            <!-- Formulaire d'ajout de membre -->
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
                                        <option value="Président">Président</option>
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
                        
                        <!-- Données utilisateurs pour JS -->
                        <script>
                            var usersData = <?= json_encode(array_map(function($u) {
                                return [
                                    'id' => $u['id'],
                                    'name' => $u['prenom'] . ' ' . $u['nom'],
                                    'email' => $u['mail'],
                                    'promo' => $u['promo'] ?? ''
                                ];
                            }, $users ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                        </script>

                        <!-- Actions du formulaire -->
                        <div class="form-actions">
                            <button type="submit" name="create_club" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Créer le club
                            </button>
                            <a href="?page=home" class="btn btn-outline">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
    (function() {
        'use strict';
        
        // Logo upload preview functionality
        var logoInput = document.getElementById('logoInput');
        var logoPreview = document.getElementById('logoPreview');
        var logoImage = document.getElementById('logoImage');
        var logoPlaceholder = document.getElementById('logoPlaceholder');
        var removeLogoBtn = document.getElementById('removeLogo');
        
        if (logoInput) {
            logoInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Le fichier est trop volumineux. Taille maximale : 2 Mo');
                        logoInput.value = '';
                        return;
                    }
                    
                    // Validate file type
                    if (!file.type.match(/^image\/(png|jpeg|jpg|gif|webp)$/)) {
                        alert('Format non supporté. Utilisez PNG, JPG, GIF ou WebP.');
                        logoInput.value = '';
                        return;
                    }
                    
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        logoImage.src = event.target.result;
                        logoImage.style.display = 'block';
                        logoPlaceholder.style.display = 'none';
                        logoPreview.classList.add('has-image');
                        removeLogoBtn.classList.add('visible');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (removeLogoBtn) {
            removeLogoBtn.addEventListener('click', function() {
                logoInput.value = '';
                logoImage.src = '';
                logoImage.style.display = 'none';
                logoPlaceholder.style.display = 'block';
                logoPreview.classList.remove('has-image');
                removeLogoBtn.classList.remove('visible');
            });
        }
        
        // ─────────────────────────────────────────────────────────────
        // Rôles uniques : un seul membre autorisé par rôle dans le club
        // ─────────────────────────────────────────────────────────────
        var MAX_SOUTENANCE_MEMBERS = 5;

        /** Liste des rôles qui ne peuvent être attribués qu'une seule fois */
        var UNIQUE_ROLES = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];

        /** Rôles autorisés à avoir la soutenance */
        var PRINCIPAL_ROLES = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];

        /** { 'Président': true } si le rôle est déjà pris */
        var usedUniqueRoles = {};

        /** Rôle unique actuellement tenu par le créateur (null sinon) */
        var creatorUniqueRole = null;

        // Variables d'état
        var memberCount = 0;
        var addedMembers = {};
        var selectedUser = null;
        
        // Éléments DOM
        var searchInput = document.getElementById('memberSearchInput');
        var suggestionsDiv = document.getElementById('memberSuggestions');
        var addBtn = document.getElementById('addMemberBtn');
        var membersList = document.getElementById('membersList');
        var roleSelect = document.getElementById('newMemberRole');
        var memberCountSpan = document.getElementById('memberCount');
        var soutenanceContainer = document.querySelector('.soutenance-check-container');
        var clubForm = document.getElementById('clubForm');
        var projetAssociatifCheck = document.getElementById('projetAssociatif');
        var soutenanceCheck = document.querySelector('input[name="soutenance"]');
        var soutenanceDateGroup = document.getElementById('soutenanceDateGroup');
        var memberRequirement = document.getElementById('memberRequirement');
        var creatorRoleSelect = document.querySelector('select[name="creator_role"]');
        
        // Fonction pour activer/désactiver le bouton Ajouter
        function setAddButtonEnabled(enabled) {
            addBtn.disabled = !enabled;
            if (enabled) {
                addBtn.classList.remove('btn-add-disabled');
            } else {
                addBtn.classList.add('btn-add-disabled');
            }
        }

        // ─────────────────────────────────────────────────────────────
        // Synchronisation du dropdown des rôles
        // Désactive les options dont le rôle unique est déjà attribué.
        // ─────────────────────────────────────────────────────────────
        function syncRoleDropdown() {
            for (var i = 0; i < roleSelect.options.length; i++) {
                var opt = roleSelect.options[i];
                if (UNIQUE_ROLES.indexOf(opt.value) !== -1) {
                    var isTaken = !!usedUniqueRoles[opt.value];
                    opt.disabled = isTaken;
                    opt.text    = isTaken ? opt.value + ' (déjà attribué)' : opt.value;
                }
            }
            // Si l'option sélectionnée est maintenant désactivée → revenir à "Membre"
            if (roleSelect.options[roleSelect.selectedIndex] &&
                roleSelect.options[roleSelect.selectedIndex].disabled) {
                roleSelect.value = 'Membre';
            }
        }

        // — Marquer le rôle initial du créateur puis écouter ses changements
        if (creatorRoleSelect) {
            var initCreatorRole = creatorRoleSelect.value;
            if (UNIQUE_ROLES.indexOf(initCreatorRole) !== -1) {
                usedUniqueRoles[initCreatorRole] = true;
                creatorUniqueRole = initCreatorRole;
            }
            syncRoleDropdown();

            // Masquer/afficher la case soutenance du créateur selon son rôle
            var creatorSoutenanceContainer = document.getElementById('creatorSoutenanceContainer');
            if (creatorSoutenanceContainer) {
                if (creatorRoleSelect.value === 'Membre') {
                    creatorSoutenanceContainer.style.display = 'none';
                }
            }

            creatorRoleSelect.addEventListener('change', function () {
                // Libérer l'ancien rôle unique du créateur
                if (creatorUniqueRole) {
                    delete usedUniqueRoles[creatorUniqueRole];
                    creatorUniqueRole = null;
                }
                // Réserver le nouveau rôle si c'est un rôle unique
                var newRole = this.value;
                if (UNIQUE_ROLES.indexOf(newRole) !== -1) {
                    usedUniqueRoles[newRole] = true;
                    creatorUniqueRole = newRole;
                }
                // Masquer/afficher la case soutenance du créateur
                if (creatorSoutenanceContainer) {
                    if (newRole === 'Membre') {
                        creatorSoutenanceContainer.style.display = 'none';
                        document.getElementById('creatorSoutenance').checked = false;
                    } else {
                        creatorSoutenanceContainer.style.display = '';
                    }
                }
                syncRoleDropdown();
                updateRequiredRoles();
                updateSoutenanceQuotaStatus();
            });
        }

        var creatorSoutenanceCheckbox = document.getElementById('creatorSoutenance');
        if (creatorSoutenanceCheckbox) {
            creatorSoutenanceCheckbox.addEventListener('change', function() {
                if (getTotalSoutenanceCount() > MAX_SOUTENANCE_MEMBERS) {
                    this.checked = false;
                    alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                }
                updateSoutenanceQuotaStatus();
            });
        }
        
        function getRequiredOtherMembers() {
            return 2; // Toujours 2 autres membres minimum (3 personnes au total)
        }

        // ─────────────────────────────────────────────────────────────
        // Rôles obligatoires : Président, Trésorier, Secrétaire
        // ─────────────────────────────────────────────────────────────
        var REQUIRED_ROLES = [
            { key: 'Président',  badgeId: 'badge_President',  label: 'Président'  },
            { key: 'Trésorier', badgeId: 'badge_Tresorier',  label: 'Trésorier'  },
            { key: 'Secrétaire', badgeId: 'badge_Secretaire', label: 'Secrétaire' }
        ];

        function updateRequiredRoles() {
            var notif = document.getElementById('requiredRolesNotif');
            var msg   = document.getElementById('requiredRolesMsg');
            if (!notif) return;

            var allFilled = true;
            for (var ri = 0; ri < REQUIRED_ROLES.length; ri++) {
                var r     = REQUIRED_ROLES[ri];
                var badge = document.getElementById(r.badgeId);
                if (!badge) continue;
                if (usedUniqueRoles[r.key]) {
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
            }

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

        function getCreatorSoutenanceCount() {
            var creatorSoutenance = document.getElementById('creatorSoutenance');
            if (!creatorRoleSelect || !creatorSoutenance) {
                return 0;
            }

            var creatorRole = creatorRoleSelect.value;
            var creatorCanSoutenance = PRINCIPAL_ROLES.indexOf(creatorRole) !== -1;
            return (creatorCanSoutenance && creatorSoutenance.checked) ? 1 : 0;
        }

        function getMembersSoutenanceCount() {
            var count = 0;
            var soutenanceInputs = membersList.querySelectorAll('input[name$="[soutenance]"]');
            for (var i = 0; i < soutenanceInputs.length; i++) {
                if (parseInt(soutenanceInputs[i].value, 10) === 1) {
                    count++;
                }
            }
            return count;
        }

        function getTotalSoutenanceCount() {
            return getCreatorSoutenanceCount() + getMembersSoutenanceCount();
        }

        function updateSoutenanceQuotaStatus() {
            var notif = document.getElementById('soutenanceQuotaNotif');
            var msg = document.getElementById('soutenanceQuotaMsg');
            if (!notif || !msg) {
                return;
            }

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

        // Fonction pour mettre à jour le compteur de membres et vérifier les conditions
        function updateMemberCount() {
            var count = membersList.querySelectorAll('.member-form-row').length;
            var totalCount = count + 1; // +1 pour le créateur
            memberCountSpan.textContent = '(' + totalCount + ' membre' + (totalCount > 1 ? 's' : '') + ' au total)';

            updateMemberRequirement(count);
            updateRequiredRoles();
        }
        
        // Fonction pour mettre à jour l'affichage du requirement
        function updateMemberRequirement(otherMembersCount) {
            var memberRequirementOk = document.getElementById('memberRequirementOk');
            var memberRequirementText = document.getElementById('memberRequirementText');

            var required = getRequiredOtherMembers();
            if (otherMembersCount >= required) {
                // Suffisamment de membres
                memberRequirement.style.display = 'none';
                if (memberRequirementOk) memberRequirementOk.style.display = 'block';
            } else {
                // Pas assez de membres
                var remaining = required - otherMembersCount;
                if (memberRequirementText) {
                    memberRequirementText.textContent = 'Ajoutez encore ' + remaining + ' membre' + (remaining > 1 ? 's' : '') + ' (vous + 2 autres minimum).';
                }
                memberRequirement.style.display = 'block';
                if (memberRequirementOk) memberRequirementOk.style.display = 'none';
            }
        }
        
        // Fonction pour échapper le HTML
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }
        
        // Fonction pour afficher les suggestions
        function showSuggestions(matches) {
            if (matches.length === 0) {
                suggestionsDiv.innerHTML = '<div class="no-results"><i class="fas fa-search"></i> Aucun résultat</div>';
            } else {
                var html = '';
                for (var i = 0; i < matches.length; i++) {
                    var u = matches[i];
                    html += '<div class="suggestion-item" data-id="' + u.id + '" data-name="' + escapeHtml(u.name) + '" data-email="' + escapeHtml(u.email) + '" data-promo="' + escapeHtml(u.promo) + '">' +
                        '<div class="suggestion-name"><i class="fas fa-user"></i>' + escapeHtml(u.name) + '</div>' +
                        '<div class="suggestion-details">' + escapeHtml(u.promo || 'N/A') + ' • ' + escapeHtml(u.email) + '</div>' +
                    '</div>';
                }
                suggestionsDiv.innerHTML = html;
            }
            suggestionsDiv.style.display = 'block';
        }
        
        // Fonction pour ajouter un membre
        // Fonction pour ajouter un membre
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

                // ── Vérification rôle unique ─────────────────────────────
                if (UNIQUE_ROLES.indexOf(role) !== -1 && usedUniqueRoles[role]) {
                    alert('Le rôle "' + role + '" est déjà attribué dans ce club.\nChaque rôle unique ne peut être assigné qu\'à une seule personne.');
                    return;
                }
                // ──────────────────────────────────────────────────────────

                // On lit si la case est cochée (autorisée uniquement pour rôles principaux)
                var isSoutenance = (PRINCIPAL_ROLES.indexOf(role) !== -1) && document.getElementById('newMemberSoutenance').checked;
                if (isSoutenance && getTotalSoutenanceCount() >= MAX_SOUTENANCE_MEMBERS) {
                    alert('Quota dépassé : maximum ' + MAX_SOUTENANCE_MEMBERS + ' membres en soutenance par club.');
                    return;
                }
                var soutenanceValue = isSoutenance ? 1 : 0;
                
                // On prépare un joli badge visuel si l'étudiant a une soutenance
                var soutenanceBadge = isSoutenance 
                    ? '<span class="member-role-badge" style="background-color: #10b981; margin-left: 5px;"><i class="fas fa-graduation-cap"></i> Soutenance</span>' 
                    : '';

                memberCount++;
                addedMembers[selectedUser.id] = true;
                
                var memberDiv = document.createElement('div');
                memberDiv.className = 'member-form-row';
                memberDiv.id = 'member_' + memberCount;
                memberDiv.setAttribute('data-user-id', selectedUser.id);
                
                // On génère la ligne avec un input HIDDEN pour le PHP
                memberDiv.innerHTML = 
                    '<input type="hidden" name="members[' + memberCount + '][user_id]" value="' + selectedUser.id + '">' +
                    '<input type="hidden" name="members[' + memberCount + '][email]" value="' + escapeHtml(selectedUser.email) + '">' +
                    '<input type="hidden" name="members[' + memberCount + '][role]" value="' + escapeHtml(role) + '">' +
                    '<input type="hidden" name="members[' + memberCount + '][soutenance]" value="' + soutenanceValue + '">' +
                    '<div class="member-avatar">' +
                        '<i class="fas fa-user"></i>' +
                    '</div>' +
                    '<div class="member-details">' +
                        '<span class="member-name">' + escapeHtml(selectedUser.name) + '</span>' +
                        '<small>' + escapeHtml(selectedUser.promo || 'N/A') + ' • ' + escapeHtml(selectedUser.email) + '</small>' +
                    '</div>' +
                    '<span class="member-role-badge">' + escapeHtml(role) + '</span>' +
                    soutenanceBadge + // Le badge apparaît ici
                    '<button type="button" class="btn btn-danger btn-sm btn-remove-member" data-member-id="' + memberCount + '" data-user-id="' + selectedUser.id + '">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>';
                
                membersList.appendChild(memberDiv);

                // ── Réserver le rôle unique si nécessaire ───────────────
                if (UNIQUE_ROLES.indexOf(role) !== -1) {
                    usedUniqueRoles[role] = true;
                    memberDiv.setAttribute('data-unique-role', role);
                    syncRoleDropdown();
                }
                // ──────────────────────────────────────────────────────────
                
                // Reset complet de la barre d'ajout
                searchInput.value = '';
                roleSelect.value = 'Membre';
                document.getElementById('newMemberSoutenance').checked = false; // On décoche la case
                selectedUser = null;
                setAddButtonEnabled(false);
                suggestionsDiv.style.display = 'none';
                
                updateMemberCount();
                updateSoutenanceQuotaStatus();
            }
        
        // Fonction pour supprimer un membre
        function removeMember(memberId, userId) {
            var memberDiv = document.getElementById('member_' + memberId);
            if (memberDiv) {
                // ── Libérer le rôle unique associé si nécessaire ────────
                var uniqueRole = memberDiv.getAttribute('data-unique-role');
                if (uniqueRole) {
                    delete usedUniqueRoles[uniqueRole];
                    syncRoleDropdown();
                }
                // ──────────────────────────────────────────────────────────
                memberDiv.remove();
                delete addedMembers[userId];
                updateMemberCount();
                updateSoutenanceQuotaStatus();
            }
        }
        
        // Event: Recherche de membre
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
                if (!addedMembers[u.id]) {
                    if (u.name.toLowerCase().indexOf(query) !== -1 || 
                        u.email.toLowerCase().indexOf(query) !== -1 ||
                        u.promo.toLowerCase().indexOf(query) !== -1) {
                        matches.push(u);
                    }
                }
            }
            
            showSuggestions(matches);
        });
        
        // Event: Clic sur une suggestion
        suggestionsDiv.addEventListener('click', function(e) {
            var item = e.target.closest('.suggestion-item');
            if (item) {
                selectedUser = {
                    id: item.getAttribute('data-id'),
                    name: item.getAttribute('data-name'),
                    email: item.getAttribute('data-email'),
                    promo: item.getAttribute('data-promo')
                };
                searchInput.value = selectedUser.name;
                suggestionsDiv.style.display = 'none';
                setAddButtonEnabled(true);
            }
        });
        
        // Event: Clic en dehors des suggestions
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
        
        // Event: Clic sur le bouton Ajouter
        addBtn.addEventListener('click', addMember);
        
        // Event: Clic sur le bouton Supprimer d'un membre
        membersList.addEventListener('click', function(e) {
            var removeBtn = e.target.closest('.btn-remove-member');
            if (removeBtn) {
                var memberId = removeBtn.getAttribute('data-member-id');
                var userId = removeBtn.getAttribute('data-user-id');
                removeMember(memberId, userId);
            }
        });
        
        // Event: Checkbox soutenance
        if (soutenanceCheck) {
            soutenanceCheck.addEventListener('change', function() {
                soutenanceDateGroup.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Event: Checkbox projet associatif
        if (projetAssociatifCheck) {
            projetAssociatifCheck.addEventListener('change', function() {
                var count = membersList.querySelectorAll('.member-form-row').length;
                var memberRequirementOk = document.getElementById('memberRequirementOk');

                // La règle des 3 personnes reste la même, on met juste à jour l'affichage.
                updateMemberRequirement(count);
            });
        }
        
        // Event: Validation du formulaire
        clubForm.addEventListener('submit', function(e) {
            var currentMemberCount = membersList.querySelectorAll('.member-form-row').length;

            if (currentMemberCount < 2) {
                e.preventDefault();
                alert("La création d'un club nécessite au moins 3 personnes (vous + 2 autres membres fondateurs). ");
                return false;
            }

            // Vérifier que les rôles obligatoires sont tous attribués
            var missingRoles = [];
            for (var ri = 0; ri < REQUIRED_ROLES.length; ri++) {
                if (!usedUniqueRoles[REQUIRED_ROLES[ri].key]) {
                    missingRoles.push(REQUIRED_ROLES[ri].label);
                }
            }
            if (missingRoles.length > 0) {
                e.preventDefault();
                alert('Les rôles suivants sont obligatoires et non attribués :\n• ' + missingRoles.join('\n• ') + '\n\nVeuillez les assigner avant de créer le club.');
                // Faire défiler vers la notification
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

        // Masquer la case soutenance si le rôle initial est "Membre"
        if (soutenanceContainer) {
            if (roleSelect.value === 'Membre') {
                soutenanceContainer.style.display = 'none';
            }
            roleSelect.addEventListener('change', function() {
                if (this.value === 'Membre') {
                    soutenanceContainer.style.display = 'none';
                    document.getElementById('newMemberSoutenance').checked = false;
                } else {
                    soutenanceContainer.style.display = '';
                }
            });
        }

        // Initial UI state
        updateMemberCount();
        updateMemberRequirement(0);
        updateRequiredRoles();
        updateSoutenanceQuotaStatus();
        
        // Event: Touche Entrée sur le champ de recherche
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedUser) {
                    addMember();
                }
            }
        });
    })();
    </script>
</body>
</html>
