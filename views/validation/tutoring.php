<?php
/**
 * Espace tuteur - Tableau de bord
 * 
 * Interface dediee aux tuteurs pour superviser leurs clubs :
 * - Clubs en attente de validation finale
 * - Evenements a approuver
 * - Liste des clubs dont ils sont tuteurs
 * - Rapports d'evenements a consulter
 * 
 * Un tuteur ne voit que les elements des clubs
 * qui lui sont assignes.
 * 
 * Variables attendues :
 * - $pending_clubs : Clubs a valider
 * - $pending_events : Evenements a valider
 * - $my_clubs : Clubs tutores
 * 
 * Permissions : Tuteur (niveau 3) ou superieur
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
                <h1><i class="fas fa-user-graduate"></i> Espace Tuteur</h1>
                <p class="subtitle">Gérez les validations de vos clubs</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <!-- Pending Clubs Section -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Clubs en attente de validation (<?= count($pending_clubs ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_clubs)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-check-circle"></i>
                            <p>Aucun club en attente de validation</p>
                        </div>
                    <?php else: ?>
                        <div class="validation-grid">
                            <?php foreach ($pending_clubs as $club): ?>
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
                                    </div>
                                    <div class="validation-card-actions">
                                        <form method="POST" style="display:inline;">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" name="validate_club_tutor" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approuver
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="validate_club_tutor" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Rejeter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Events Section -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Événements en attente (<?= count($pending_events ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_events)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-check-circle"></i>
                            <p>Aucun événement en attente de validation</p>
                        </div>
                    <?php else: ?>
                        <div class="validation-grid">
                            <?php foreach ($pending_events as $event): ?>
                                <div class="validation-card">
                                    <div class="validation-card-header">
                                        <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                                    </div>
                                    <div class="validation-card-body">
                                        <div class="info-row">
                                            <span class="label"><i class="fas fa-building"></i> Club</span>
                                            <span class="value"><?= htmlspecialchars($event['nom_club'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span class="label"><i class="fas fa-calendar"></i> Date</span>
                                            <span class="value"><?= date('d/m/Y', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span class="label"><i class="fas fa-map-marker-alt"></i> Campus</span>
                                            <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>"><?= htmlspecialchars($event['campus'] ?? 'N/A') ?></span>
                                        </div>
                                    </div>
                                    <div class="validation-card-actions">
                                        <form method="POST" style="display:inline;">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" name="validate_event_tutor" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approuver
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="validate_event_tutor" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Rejeter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tutored Clubs Section -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Mes clubs tutorés (<?= count($tutored_clubs ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($tutored_clubs)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-building"></i>
                            <p>Vous ne tutorez aucun club actuellement</p>
                        </div>
                    <?php else: ?>
                        <div class="tutored-clubs-grid">
                            <?php foreach ($tutored_clubs as $club): ?>
                                <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="tutored-club-card">
                                    <div class="club-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="club-info">
                                        <h4><?= htmlspecialchars($club['nom_club']) ?></h4>
                                        <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
