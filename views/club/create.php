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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        /* Styles pour l'autocomplétion des membres */
        .member-search-container {
            flex: 2;
            position: relative;
        }
        
        .autocomplete-suggestions {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        
        .suggestion-item:hover {
            background: #f0f7ff;
        }
        
        .suggestion-item .suggestion-name {
            font-weight: 500;
        }
        
        .suggestion-item .suggestion-name i {
            color: #3498db;
            margin-right: 8px;
        }
        
        .suggestion-item .suggestion-details {
            color: #666;
            margin-left: 24px;
            font-size: 0.85em;
        }
        
        .no-results {
            padding: 10px;
            color: #666;
        }
        
        /* Styles pour la ligne d'ajout de membre */
        .member-add-row {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            align-items: flex-start;
        }
        
        .role-select-container {
            flex: 1;
        }
        
        .role-select {
            width: 100%;
        }
        
        /* Styles pour les membres ajoutés */
        .member-form-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }
        
        .member-form-row .member-avatar {
            width: 45px;
            height: 45px;
            min-width: 45px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .member-form-row .member-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .member-form-row .member-details .member-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .member-form-row .member-details small {
            display: block;
            color: #666;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .member-role-badge {
            flex-shrink: 0;
            padding: 6px 14px;
            border-radius: 20px;
            background: #3498db;
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .btn-remove-member {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e74c3c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        
        .btn-remove-member:hover {
            background: #c0392b;
            transform: scale(1.05);
        }
        
        /* Bouton désactivé */
        .btn-add-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Logo Upload Styles */
        .logo-upload-container {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        
        .logo-preview {
            width: 120px;
            height: 120px;
            border: 3px dashed #d1d5db;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            overflow: hidden;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        
        .logo-preview:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .logo-preview.has-image {
            border-style: solid;
            border-color: #3b82f6;
        }
        
        .logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .logo-preview .placeholder-icon {
            color: #9ca3af;
            font-size: 2.5rem;
        }
        
        .logo-upload-info {
            flex: 1;
        }
        
        .logo-upload-info label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .logo-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .logo-upload-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-1px);
        }
        
        .logo-upload-btn input[type="file"] {
            display: none;
        }
        
        .logo-hints {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .logo-hints li {
            margin-bottom: 4px;
        }
        
        .remove-logo-btn {
            display: none;
            margin-top: 10px;
            padding: 6px 12px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .remove-logo-btn:hover {
            background: #fecaca;
        }
        
        .remove-logo-btn.visible {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
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
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
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
                                <label><i class="fas fa-tag"></i> Type de club</label>
                                <input type="text" name="type_club" class="form-control" placeholder="Ex: Tech, Sport, Culture..." required>
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
                        <div class="form-section">
                            <h4>
                                <i class="fas fa-project-diagram"></i> Soutenance
                                <span class="tooltip-trigger" title="Si votre club fait partie d'un projet associatif, vous devez avoir au moins 3 membres au moment de la création. La soutenance est obligatoire pour certains clubs.">
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
                        </div>
                        
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
                                    <option value="Membre">Membre</option>
                                </select>
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Vous serez automatiquement ajouté avec ce rôle.</small>
                            </div>
                            
                            <p class="text-muted" id="memberRequirement" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> <span id="memberRequirementText">Ajoutez au moins 2 autres membres pour un projet associatif.</span>
                            </p>
                            <p class="text-success" id="memberRequirementOk" style="display: none;">
                                <i class="fas fa-check-circle"></i> Nombre de membres suffisant pour un projet associatif.
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
                                        <option value="Secrétaire">Secrétaire</option>
                                    </select>
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
                            }, $users ?? [])) ?>;
                        </script>

                        <!-- Actions du formulaire -->
                        <div class="form-actions">
                            <button type="submit" name="create_club" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Créer le club
                            </button>
                            <a href="?page=dashboard" class="btn btn-outline">
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
        var clubForm = document.getElementById('clubForm');
        var projetAssociatifCheck = document.getElementById('projetAssociatif');
        var soutenanceCheck = document.querySelector('input[name="soutenance"]');
        var soutenanceDateGroup = document.getElementById('soutenanceDateGroup');
        var memberRequirement = document.getElementById('memberRequirement');
        
        // Fonction pour activer/désactiver le bouton Ajouter
        function setAddButtonEnabled(enabled) {
            addBtn.disabled = !enabled;
            if (enabled) {
                addBtn.classList.remove('btn-add-disabled');
            } else {
                addBtn.classList.add('btn-add-disabled');
            }
        }
        
        // Fonction pour mettre à jour le compteur de membres et vérifier les conditions
        function updateMemberCount() {
            var count = membersList.querySelectorAll('.member-form-row').length;
            var totalCount = count + 1; // +1 pour le créateur
            memberCountSpan.textContent = '(' + totalCount + ' membre' + (totalCount > 1 ? 's' : '') + ' au total)';
            
            // Vérifier si projet associatif est coché
            if (projetAssociatifCheck && projetAssociatifCheck.checked) {
                updateMemberRequirement(count);
            }
        }
        
        // Fonction pour mettre à jour l'affichage du requirement
        function updateMemberRequirement(otherMembersCount) {
            var memberRequirementOk = document.getElementById('memberRequirementOk');
            var memberRequirementText = document.getElementById('memberRequirementText');
            
            if (otherMembersCount >= 2) {
                // Suffisamment de membres
                memberRequirement.style.display = 'none';
                if (memberRequirementOk) memberRequirementOk.style.display = 'block';
            } else {
                // Pas assez de membres
                var remaining = 2 - otherMembersCount;
                if (memberRequirementText) {
                    memberRequirementText.textContent = 'Ajoutez encore ' + remaining + ' membre' + (remaining > 1 ? 's' : '') + ' pour un projet associatif (vous + 2 autres minimum).';
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
            memberCount++;
            addedMembers[selectedUser.id] = true;
            
            var memberDiv = document.createElement('div');
            memberDiv.className = 'member-form-row';
            memberDiv.id = 'member_' + memberCount;
            memberDiv.setAttribute('data-user-id', selectedUser.id);
            
            memberDiv.innerHTML = 
                '<input type="hidden" name="members[' + memberCount + '][user_id]" value="' + selectedUser.id + '">' +
                '<input type="hidden" name="members[' + memberCount + '][email]" value="' + escapeHtml(selectedUser.email) + '">' +
                '<input type="hidden" name="members[' + memberCount + '][role]" value="' + escapeHtml(role) + '">' +
                '<div class="member-avatar">' +
                    '<i class="fas fa-user"></i>' +
                '</div>' +
                '<div class="member-details">' +
                    '<span class="member-name">' + escapeHtml(selectedUser.name) + '</span>' +
                    '<small>' + escapeHtml(selectedUser.promo || 'N/A') + ' • ' + escapeHtml(selectedUser.email) + '</small>' +
                '</div>' +
                '<span class="member-role-badge">' + escapeHtml(role) + '</span>' +
                '<button type="button" class="btn btn-danger btn-sm btn-remove-member" data-member-id="' + memberCount + '" data-user-id="' + selectedUser.id + '">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>';
            
            membersList.appendChild(memberDiv);
            
            // Reset
            searchInput.value = '';
            roleSelect.value = 'Membre';
            selectedUser = null;
            setAddButtonEnabled(false);
            suggestionsDiv.style.display = 'none';
            
            updateMemberCount();
        }
        
        // Fonction pour supprimer un membre
        function removeMember(memberId, userId) {
            var memberDiv = document.getElementById('member_' + memberId);
            if (memberDiv) {
                memberDiv.remove();
                delete addedMembers[userId];
                updateMemberCount();
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
                
                if (this.checked) {
                    updateMemberRequirement(count);
                } else {
                    memberRequirement.style.display = 'none';
                    if (memberRequirementOk) memberRequirementOk.style.display = 'none';
                }
            });
        }
        
        // Event: Validation du formulaire
        clubForm.addEventListener('submit', function(e) {
            var isProjetAssociatif = projetAssociatifCheck && projetAssociatifCheck.checked;
            var currentMemberCount = membersList.querySelectorAll('.member-form-row').length;
            
            if (isProjetAssociatif && currentMemberCount < 2) {
                e.preventDefault();
                alert('Un projet associatif nécessite au moins 3 membres fondateurs (vous + 2 autres).');
                return false;
            }
        });
        
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
