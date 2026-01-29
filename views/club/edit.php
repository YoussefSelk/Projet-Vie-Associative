<?php
/**
 * Édition d'un club en attente ou refusé
 * 
 * Permet à un membre du bureau (Président ou Secrétaire) de modifier les informations d'un club
 * et de le (re)soumettre à validation.
 * 
 * Le Président et le Secrétaire peuvent modifier le club.
 * Une fois modifié, le club retourne en état de validation.
 * 
 * Variables attendues :
 * - $club : Données actuelles du club
 * - $error_msg : Message d'erreur éventuel
 * - $success_msg : Message de succès éventuel
 * 
 * @package Views/Club
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        .edit-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .form-actions .btn {
            flex: 1;
        }

        .warning-box {
            background-color: #ffe6e6;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .warning-box h5 {
            margin: 0 0 10px 0;
            color: #721c24;
        }

        .warning-box p {
            margin: 0;
            color: #721c24;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .edit-container {
                padding: 20px;
            }
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
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Modifier ma demande de club</h1>
                <p class="subtitle">Mettez à jour les informations de votre club</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($club): ?>
                <div class="edit-container">
                    <div class="warning-box">
                        <h5><i class="fas fa-info-circle"></i> Information importante</h5>
                        <p>
                            Une fois vos modifications sauvegardées, votre demande de club retournera en attente de validation.
                            Un administrateur et un tuteur examineront à nouveau votre demande.
                        </p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="form-group">
                            <label for="nom_club">Nom du club <span style="color: red;">*</span></label>
                            <input 
                                type="text" 
                                id="nom_club" 
                                name="nom_club" 
                                value="<?= htmlspecialchars($club['nom_club'] ?? '') ?>" 
                                required
                                placeholder="Entrez le nom de votre club"
                            >
                        </div>

                        <div class="form-row">
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

                            <div class="form-group">
                                <label for="campus">Campus <span style="color: red;">*</span></label>
                                <select id="campus" name="campus" required>
                                    <option value="">-- Sélectionnez un campus --</option>
                                    <option value="Calais" <?= ($club['campus'] === 'Calais') ? 'selected' : '' ?>>Calais</option>
                                    <option value="Longuenesse" <?= ($club['campus'] === 'Longuenesse') ? 'selected' : '' ?>>Longuenesse</option>
                                    <option value="Dunkerque" <?= ($club['campus'] === 'Dunkerque') ? 'selected' : '' ?>>Dunkerque</option>
                                    <option value="Boulogne" <?= ($club['campus'] === 'Boulogne') ? 'selected' : '' ?>>Boulogne</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span style="color: red;">*</span></label>
                            <textarea 
                                id="description" 
                                name="description" 
                                required
                                placeholder="Décrivez les objectifs et activités de votre club"
                            ><?= htmlspecialchars($club['description'] ?? '') ?></textarea>
                            <small style="color: #666;">Décrivez les objectifs, les activités et l'impact de votre club.</small>
                        </div>

                        <!-- Section Gestion des Membres -->
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Membres du club</label>
                            <small style="color: #666; display: block; margin-bottom: 10px;">
                                Ajoutez ou modifiez les membres de votre club. Vous (Président) êtes automatiquement membre.
                            </small>
                            
                            <div id="members-container">
                                <?php if (!empty($currentMembers)): ?>
                                    <?php foreach ($currentMembers as $index => $member): ?>
                                        <div class="member-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <select name="members[<?= $index ?>][user_id]" class="form-control" style="flex: 2;">
                                                <option value="">-- Sélectionner un membre --</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= ($member['id'] == $user['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                                        <?php if (!empty($user['promo'])): ?> (<?= htmlspecialchars($user['promo']) ?>)<?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="members[<?= $index ?>][role]" class="form-control" style="flex: 1;">
                                                <option value="Membre" <?= ($member['fonction'] == 'Membre') ? 'selected' : '' ?>>Membre</option>
                                                <option value="Vice-président" <?= ($member['fonction'] == 'Vice-président') ? 'selected' : '' ?>>Vice-président</option>
                                                <option value="Trésorier" <?= ($member['fonction'] == 'Trésorier') ? 'selected' : '' ?>>Trésorier</option>
                                                <option value="Secrétaire" <?= ($member['fonction'] == 'Secrétaire') ? 'selected' : '' ?>>Secrétaire</option>
                                                <option value="Responsable Communication" <?= ($member['fonction'] == 'Responsable Communication') ? 'selected' : '' ?>>Responsable Communication</option>
                                                <option value="Responsable Événements" <?= ($member['fonction'] == 'Responsable Événements') ? 'selected' : '' ?>>Responsable Événements</option>
                                            </select>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
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
                        
                        function addMemberRow() {
                            const container = document.getElementById('members-container');
                            const row = document.createElement('div');
                            row.className = 'member-row';
                            row.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
                            
                            let optionsHtml = '<option value="">-- Sélectionner un membre --</option>';
                            users.forEach(user => {
                                optionsHtml += `<option value="${user.id}">${user.nom} ${user.prenom}${user.promo ? ' (' + user.promo + ')' : ''}</option>`;
                            });
                            
                            row.innerHTML = `
                                <select name="members[${memberIndex}][user_id]" class="form-control" style="flex: 2;">
                                    ${optionsHtml}
                                </select>
                                <select name="members[${memberIndex}][role]" class="form-control" style="flex: 1;">
                                    <option value="Membre" selected>Membre</option>
                                    <option value="Vice-président">Vice-président</option>
                                    <option value="Trésorier">Trésorier</option>
                                    <option value="Secrétaire">Secrétaire</option>
                                    <option value="Responsable Communication">Responsable Communication</option>
                                    <option value="Responsable Événements">Responsable Événements</option>
                                </select>
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                            
                            container.appendChild(row);
                            memberIndex++;
                        }
                        </script>

                        <div class="form-actions">
                            <a href="?page=my-clubs" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" name="update_club" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer et resoummettre
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
