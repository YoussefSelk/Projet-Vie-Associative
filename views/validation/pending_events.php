<?php
/**
 * Liste des evenements en attente de validation
 * 
 * Interface pour le BDE/Tuteur afin de valider les evenements :
 * - Affichage des evenements soumis
 * - Details (date, lieu, club organisateur)
 * - Boutons d'approbation ou de rejet
 * 
 * Workflow identique aux clubs :
 * 1. BDE approuve -> bde_approuve
 * 2. Tuteur approuve -> valide
 * 
 * Variables attendues :
 * - $events : Liste des evenements en attente
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Validation
 */
?>
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
            <div class="page-header">
                <div class="header-left">
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-calendar-check"></i> Événements en attente</h1>
                <p class="subtitle"><?= count($events) ?> événement(s) en attente de validation</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Aucun événement en attente</h3>
                    <p>Tous les événements ont été validés.</p>
                </div>
            <?php else: ?>
                <div class="validation-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="validation-card">
                            <div class="validation-card-header">
                                <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                            </div>
                            <div class="validation-card-body">
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-calendar"></i> Date</span>
                                    <span class="value"><?= date('d/m/Y', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-map-marker-alt"></i> Campus</span>
                                    <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>"><?= htmlspecialchars($event['campus'] ?? 'N/A') ?></span>
                                </div>
                                <?php if (!empty($event['nom_club'])): ?>
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-building"></i> Club</span>
                                    <span class="value"><?= htmlspecialchars($event['nom_club']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="validation-card-actions">
                                <form method="POST" style="display:inline;">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" name="validate_event" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" name="validate_event" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
