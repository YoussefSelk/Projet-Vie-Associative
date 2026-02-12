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
$pageTitle = 'Détails de l\'événement - EILCO';
$pageCss = ['shared', 'buttons', 'events'];
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
                        
                        <?php 
                            // On récupère les informations nécessaires
                            $user_id_session = (int)($_SESSION['id'] ?? 0);
                            $user_permission = (int)($_SESSION['permission'] ?? 0);
                            
                            // On récupère l'ID du tuteur que vous avez injecté dans le contrôleur
                            $id_tuteur_responsable = (int)($event['tuteur_id'] ?? 0);

                            /**
                             * Accès autorisé si :
                             * - Admin ou SuperAdmin (perm > 2)
                             * - OU Tuteur spécifique (perm == 2) ET son ID match avec celui du club organisateur
                             */
                            $est_autorise = ($user_permission > 2) || ($user_permission === 2 && $user_id_session === $id_tuteur_responsable);

                            if ($est_autorise): 
                        ?>
                            <div class="event-section" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                    <h3 style="color: #1e293b; margin: 0; font-size: 1.25rem;">
                                        <i class="fas fa-file-signature" style="color: #64748b;"></i> Documentation & Photos
                                    </h3>
                                    <?php if ($user_permission === 2): ?>
                                        <span style="background: #e0f2fe; color: #0369a1; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <i class="fas fa-user-shield"></i> Espace Tuteur
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($event['rapport_event'])): ?>
                                    <div class="mb-20">
                                        <a href="<?= htmlspecialchars($event['rapport_event']) ?>" class="btn btn-primary" target="_blank">
                                            <i class="fas fa-file-pdf"></i> 
                                            Consulter le rapport d'événement
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-20">Le rapport n'a pas encore été déposé.</p>
                                <?php endif; ?>

                                <?php 
                                $photos = !empty($event['images_event']) ? explode(',', $event['images_event']) : [];
                                if (!empty($photos)): 
                                ?>
                                    <div style="border-top: 1px solid #e2e8f0; padding-top: 15px;">
                                        <h4 style="font-size: 0.95rem; color: #475569; margin-bottom: 15px;">
                                            <i class="fas fa-camera"></i> Photos souvenirs
                                        </h4>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                                            <?php foreach ($photos as $url): ?>
                                                <div class="photo-item" style="aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                    <a href="<?= htmlspecialchars(trim($url)) ?>" target="_blank">
                                                        <img src="<?= htmlspecialchars(trim($url)) ?>" 
                                                            alt="Souvenir" 
                                                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                                            onmouseover="this.style.transform='scale(1.1)'"
                                                            onmouseout="this.style.transform='scale(1)'">
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-actions">
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
