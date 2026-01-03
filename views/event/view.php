<?php
/**
 * Vue detaillee d'un evenement
 * 
 * Affiche les informations completes d'un evenement :
 * - En-tete avec couleur du campus
 * - Description complete
 * - Club organisateur
 * - Date, lieu et nombre de places
 * - Boutons d'action (inscription, partage)
 * 
 * Variables attendues :
 * - $event : Donnees de l'evenement
 * - $club : Club organisateur
 * - $is_subscribed : Si l'utilisateur est inscrit
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
            <div class="header-left">
                <a href="?page=event-list" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
            </div>

            <?php if (!$event): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Événement non trouvé</h3>
                    <p>L'événement que vous recherchez n'existe pas.</p>
                </div>
            <?php else: ?>
                <?php
                $campusColors = [
                    'calais' => '#0066cc',
                    'longuenesse' => '#28a745',
                    'dunkerque' => '#dc3545',
                    'boulogne' => '#ffc107'
                ];
                $campusColor = $campusColors[strtolower($event['campus'] ?? 'calais')] ?? '#0066cc';
                ?>
                
                <div class="event-detail-card">
                    <div class="event-detail-header" style="background: linear-gradient(135deg, <?= $campusColor ?> 0%, <?= $campusColor ?>dd 100%);">
                        <div class="event-date-large">
                            <span class="day"><?= date('d', strtotime($event['date_ev'] ?? 'now')) ?></span>
                            <span class="month"><?php 
                                $moisFr = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                                $dateTs = strtotime($event['date_ev'] ?? 'now');
                                echo $moisFr[date('n', $dateTs) - 1] . ' ' . date('Y', $dateTs);
                            ?></span>
                        </div>
                        <h1><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h1>
                        <div class="event-badges">
                            <span class="badge badge-light">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                            </span>
                            <?php if (!empty($event['horaire_debut'])): ?>
                            <span class="badge badge-light">
                                <i class="fas fa-clock"></i> <?= htmlspecialchars($event['horaire_debut']) ?>
                                <?php if (!empty($event['horaire_fin'])): ?> - <?= htmlspecialchars($event['horaire_fin']) ?><?php endif; ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($event['lieu'])): ?>
                            <span class="badge badge-light">
                                <i class="fas fa-location-arrow"></i> <?= htmlspecialchars($event['lieu']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="event-detail-body">
                        <div class="event-section">
                            <h3><i class="fas fa-info-circle"></i> Description</h3>
                            <p class="event-description-full"><?= nl2br(htmlspecialchars($event['description'] ?? 'Aucune description disponible.')) ?></p>
                        </div>
                        
                        <?php if (!empty($event['places_max'])): ?>
                        <div class="event-section">
                            <h3><i class="fas fa-users"></i> Places disponibles</h3>
                            <p><?= htmlspecialchars($event['places_max']) ?> places</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="event-actions">
                            <a href="?page=subscribe&event_id=<?= $event['event_id'] ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> S'inscrire à cet événement
                            </a>
                            <a href="?page=event-list" class="btn btn-outline">
                                <i class="fas fa-calendar-alt"></i> Voir tous les événements
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
