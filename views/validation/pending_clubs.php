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
$pageTitle = 'Clubs en attente - EILCO';
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
                                <form method="POST" class="approve-form">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="button" class="btn btn-success btn-sm swal-action-btn" data-action="approve" data-name="<?= htmlspecialchars($club['nom_club']) ?>">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                                <form method="POST">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="force_approve">
                                    <input type="hidden" name="validate_club" value="1">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Valider immédiatement sans attendre le tuteur">
                                        <i class="fas fa-bolt"></i> Forcer
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="reject-form">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="button" class="btn btn-danger btn-sm swal-action-btn" data-action="reject" data-name="<?= htmlspecialchars($club['nom_club']) ?>">
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.swal-action-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const form = this.closest('form');
                    const action = this.dataset.action;
                    const clubName = this.dataset.name;
                    const isReject = action === 'reject';
                    
                    Swal.fire({
                        title: isReject ? 'Rejeter le club' : 'Approuver le club',
                        html: `<p style="margin-bottom:12px;">${isReject ? 'Rejeter' : 'Approuver'} le club <strong>&laquo; ${clubName} &raquo;</strong> ?</p>`,
                        input: 'textarea',
                        inputLabel: 'Remarques (optionnel)',
                        inputPlaceholder: 'Ajoutez un commentaire pour l\'étudiant...',
                        inputAttributes: { 'aria-label': 'Remarques' },
                        icon: isReject ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonText: isReject ? '<i class="fas fa-times"></i> Rejeter' : '<i class="fas fa-check"></i> Approuver',
                        cancelButtonText: 'Annuler',
                        confirmButtonColor: isReject ? '#dc3545' : '#28a745',
                        cancelButtonColor: '#6c757d',
                        focusCancel: isReject,
                        customClass: {
                            popup: 'swal-validation-popup'
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            var remarquesInput = document.createElement('input');
                            remarquesInput.type = 'hidden';
                            remarquesInput.name = 'remarques';
                            remarquesInput.value = result.value || '';
                            form.appendChild(remarquesInput);
                            
                            var validateInput = document.createElement('input');
                            validateInput.type = 'hidden';
                            validateInput.name = 'validate_club';
                            validateInput.value = '1';
                            form.appendChild(validateInput);
                            
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
