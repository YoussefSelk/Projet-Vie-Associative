<?php
/**
 * Depot de rapport d'evenement
 * 
 * Permet aux organisateurs de deposer un bilan apres l'evenement :
 * - Selection de l'evenement concerne
 * - Upload de fichier (PDF, Word, images)
 * - Commentaires et retours
 * 
 * Les rapports sont consultables par les tuteurs et l'administration.
 * 
 * Variables attendues :
 * - $events : Liste des evenements passes sans rapport
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Event
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
            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt"></i> Déposer un rapport d'événement</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <?php if (empty($events)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-calendar-times"></i>
                            <p>Aucun événement disponible pour déposer un rapport.</p>
                            <p class="text-muted" style="font-size: 0.9em; margin-top: 10px;">
                                <i class="fas fa-info-circle"></i> Pour déposer un rapport, vous devez :
                            </p>
                            <ul class="text-muted" style="font-size: 0.85em; text-align: left; max-width: 400px; margin: 10px auto;">
                                <li>Être membre validé d'un club</li>
                                <li>Le club doit avoir un événement validé</li>
                                <li>L'événement ne doit pas déjà avoir de rapport</li>
                            </ul>
                            <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Retour</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" class="form-modern">
                            <?= Security::csrfField() ?>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Sélectionnez l'événement</label>
                                <select name="event_id" class="form-control" required>
                                    <option value="">-- Choisir un événement --</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?= $event['event_id'] ?>">
                                            <?= htmlspecialchars($event['nom_club'] ?? '') ?> - <?= htmlspecialchars($event['titre'] ?? '') ?> 
                                            (<?= date('d/m/Y', strtotime($event['date_ev'] ?? 'now')) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-file-pdf"></i> Fichier de rapport (PDF obligatoire)</label>
                                <input type="file" name="rapport_file" class="form-control" accept=".pdf" required>
                                <small class="form-help">Format accepté : PDF uniquement</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit_report" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> Déposer le rapport
                                </button>
                                <a href="?page=my-events" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Retour</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
