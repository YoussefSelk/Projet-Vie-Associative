<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
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

                    <form method="POST" class="form-modern" id="clubForm">
                        <?= Security::csrfField() ?>
                        
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
                                    <?php
                                    // Get tutors (permission level 5 or personnel)
                                    global $db;
                                    $tutors = $db->query("SELECT id, nom, prenom FROM users WHERE permission >= 5 OR promo = 'personnel' ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($tutors as $tutor):
                                    ?>
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
                        
                        <!-- Members Section -->
                        <div class="form-section" id="membersSection">
                            <h4><i class="fas fa-users"></i> Membres fondateurs <span id="memberCount">(0 membre)</span></h4>
                            <p class="text-muted" id="memberRequirement" style="display: none;">
                                <i class="fas fa-info-circle"></i> Un projet associatif nécessite au moins 3 membres.
                            </p>
                            <div id="membersList" class="members-form-list">
                                <!-- Members will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="addMemberField()">
                                <i class="fas fa-plus"></i> Ajouter un membre
                            </button>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="create_club" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Créer le club
                            </button>
                            <a href="?page=admin" class="btn btn-outline"><i class="fas fa-times"></i> Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        let memberCount = 0;
        
        function addMemberField() {
            memberCount++;
            const container = document.getElementById('membersList');
            const memberDiv = document.createElement('div');
            memberDiv.className = 'member-form-row';
            memberDiv.id = 'member_' + memberCount;
            memberDiv.innerHTML = `
                <input type="email" name="members[${memberCount}][email]" class="form-control" placeholder="Email du membre">
                <select name="members[${memberCount}][role]" class="form-control">
                    <option value="membre">Membre</option>
                    <option value="president">Président</option>
                    <option value="vice-president">Vice-Président</option>
                    <option value="tresorier">Trésorier</option>
                    <option value="secretaire">Secrétaire</option>
                </select>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(${memberCount})">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(memberDiv);
            updateMemberCount();
        }
        
        function removeMember(id) {
            document.getElementById('member_' + id).remove();
            updateMemberCount();
        }
        
        function updateMemberCount() {
            const count = document.querySelectorAll('.member-form-row').length;
            document.getElementById('memberCount').textContent = '(' + count + ' membre' + (count > 1 ? 's' : '') + ')';
        }
        
        // Show/hide soutenance date based on checkbox
        document.querySelector('input[name="soutenance"]').addEventListener('change', function() {
            document.getElementById('soutenanceDateGroup').style.display = this.checked ? 'block' : 'none';
        });
        
        // Show warning for projet associatif
        document.getElementById('projetAssociatif').addEventListener('change', function() {
            document.getElementById('memberRequirement').style.display = this.checked ? 'block' : 'none';
        });
        
        // Form validation
        document.getElementById('clubForm').addEventListener('submit', function(e) {
            const isProjetAssociatif = document.getElementById('projetAssociatif').checked;
            const memberCount = document.querySelectorAll('.member-form-row').length;
            
            if (isProjetAssociatif && memberCount < 3) {
                e.preventDefault();
                alert('Un projet associatif nécessite au moins 3 membres fondateurs.');
                return false;
            }
        });
    </script>
</body>
</html>
