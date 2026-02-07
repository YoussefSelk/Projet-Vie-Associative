<?php
/**
 * Liste des clubs en attente de validation
 * 
 * Interface pour le BDE/Tuteur afin de valider les clubs :
 * - Affichage des clubs soumis
 * - Details de chaque club (description, campus, createur)
 * - Boutons d'approbation ou de rejet
 * - Champ pour commentaires de rejet
 * 
 * Workflow de validation :
 * 1. BDE approuve -> statut = bde_approuve
 * 2. Tuteur approuve -> statut = actif
 * 
 * Variables attendues :
 * - $clubs : Liste des clubs en attente
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Validation
 */
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'validation', 'clubs'];
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
                <div class="header-left">
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-building"></i> Clubs en attente</h1>
                <p class="subtitle"><?= count($clubs) ?> club(s) en attente de validation</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <?php if (empty($clubs)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Aucun club en attente</h3>
                    <p>Tous les clubs ont été validés.</p>
                </div>
            <?php else: ?>
                <div class="validation-grid">
                    <?php foreach ($clubs as $club): ?>
                        <div class="validation-card">
                            <div class="validation-card-header">
                                <h3><?= htmlspecialchars($club['nom_club']) ?></h3>
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                            </div>
                            <div class="validation-card-body">
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-tag"></i> Type</span>
                                    <span class="value"><?= htmlspecialchars($club['type_club']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-map-marker-alt"></i> Campus</span>
                                    <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                </div>
                                <?php if (!empty($club['description'])): ?>
                                <div class="info-row">
                                    <p class="description"><?= htmlspecialchars(mb_substr($club['description'], 0, 150)) ?>...</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="validation-card-actions">
                                <form method="POST" style="display:inline;" class="approve-form">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="button" class="btn btn-success btn-sm" onclick="showCommentModal(this.form, 'Approuver le club', 'approve')">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                                <form method="POST" style="display:inline;">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="force_approve">
                                    <input type="hidden" name="validate_club" value="1">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Valider immédiatement sans attendre le tuteur">
                                        <i class="fas fa-bolt"></i> Forcer
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;" class="reject-form">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="showCommentModal(this.form, 'Rejeter le club', 'reject')">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </form>
                                <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Comment Modal -->
    <div id="commentModal" class="simple-modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Ajouter un commentaire</h3>
                <button type="button" class="modal-close" onclick="closeCommentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Remarques (optionnel)</label>
                    <textarea id="commentInput" class="form-control" rows="3" placeholder="Ajoutez un commentaire pour l'étudiant..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCommentModal()">Annuler</button>
                <button type="button" id="confirmBtn" class="btn btn-primary" onclick="submitWithComment()">Confirmer</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentForm = null;
        let currentAction = '';
        
        function showCommentModal(form, title, action) {
            currentForm = form;
            currentAction = action;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('commentModal').style.display = 'flex';
            document.getElementById('confirmBtn').className = action === 'reject' ? 'btn btn-danger' : 'btn btn-success';
            document.getElementById('commentInput').value = '';
        }
        
        function closeCommentModal() {
            document.getElementById('commentModal').style.display = 'none';
            currentForm = null;
        }
        
        function submitWithComment() {
            if (currentForm) {
                const comment = document.getElementById('commentInput').value;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remarques';
                input.value = comment;
                currentForm.appendChild(input);
                
                // Add the validate_club input
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'validate_club';
                submitInput.value = '1';
                currentForm.appendChild(submitInput);
                
                currentForm.submit();
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('commentModal').addEventListener('click', function(e) {
            if (e.target === this) closeCommentModal();
        });
    </script>
    
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .modal-content {
            background: #fff;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #0066cc;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Fix pour les boutons d'action des cartes */
        .validation-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 15px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }
        
        .validation-card-actions form {
            display: inline-flex;
        }
        
        .validation-card-actions .btn {
            white-space: nowrap;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
        }
    </style>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
