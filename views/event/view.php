<?php
/**
 * Vue détaillée d'un événement
 *
 * Affiche les informations complètes d'un événement :
 * - En-tête avec couleur du campus
 * - Description complète
 * - Club organisateur
 * - Date, lieu et nombre de places
 * - Boutons d'action (inscription, partage)
 *
 * Variables attendues :
 * - $event : Données de l'événement
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
                        <?php if (!empty($event['logo_club'])): ?>
                            <?php 
                                $logoPath = '/' . ltrim($event['logo_club'], '/'); 
                                $logoEscaped = htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8');
                                $alt = htmlspecialchars($event['nom_club'] ?? 'Logo du club', ENT_QUOTES, 'UTF-8');
                            ?>
                            <img src="<?= $logoEscaped ?>" class="event-header-bg-logo" alt="<?= $alt ?>" loading="lazy" />
                        <?php endif; ?>

                        <div style="width: 100%;">
                            <div class="event-date-large">
                                <span class="day" style="display: block; font-size: 3.5rem; font-weight: 800; line-height: 1;"><?= date('d', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                <span class="month" style="display: block; font-size: 1.3rem; margin-top: 5px;">
                                    <?php 
                                        $moisFr = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                                        $dateTs = strtotime($event['date_ev'] ?? 'now');
                                        echo $moisFr[date('n', $dateTs) - 1] . ' ' . date('Y', $dateTs);
                                    ?>
                                </span>
                            </div>

                            <h1 style="margin: 20px 0; font-size: 2.8rem;"><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h1>

                            <p style="margin-bottom: 20px; font-weight: 600; font-size: 1.1rem;">
                                <i class="fas fa-users"></i> <?= htmlspecialchars($event['nom_club'] ?? 'EILCook') ?>
                            </p>

                            <div class="event-badges" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                                <span class="badge badge-light">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                </span>

                                <?php if (!empty($event['type_event'])): ?>
                                    <span class="badge badge-light" style="text-transform: uppercase; letter-spacing: 0.5px;">
                                        <?= ($event['type_event'] === 'activity') ? '<i class="fas fa-tools"></i> Activité' : '<i class="fas fa-star"></i> Événement' ?>
                                    </span>
                                <?php endif; ?>
                                
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
                        // Affiche (communication) : visible par TOUS les rôles, y compris les
                        // personnes non connectées. Voir retour client (juin 2026).
                        // L'affiche peut être une image (JPG/PNG) ou un PDF.
                        if (!empty($event['affiche'])):
                            $afficheUrl = htmlspecialchars((string)$event['affiche'], ENT_QUOTES, 'UTF-8');
                            $afficheExt = strtolower(pathinfo((string)$event['affiche'], PATHINFO_EXTENSION));
                            $afficheIsImage = in_array($afficheExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                        ?>
                        <div class="event-section">
                            <h3><i class="fas fa-image"></i> Affiche</h3>
                            <?php if ($afficheIsImage): ?>
                                <a href="<?= $afficheUrl ?>" target="_blank" rel="noopener">
                                    <img src="<?= $afficheUrl ?>" alt="Affiche de l'événement"
                                         style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #e2e8f0;" loading="lazy">
                                </a>
                            <?php else: ?>
                                <a href="<?= $afficheUrl ?>" class="btn btn-sm" style="background: #f59e0b; color: white;" target="_blank" rel="noopener">
                                    <i class="fas fa-file-pdf"></i> Voir l'affiche
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php
                            // On récupère les informations nécessaires
                            $user_id_session = (int)($_SESSION['id'] ?? 0);
                            $user_permission = (int)($_SESSION['permission'] ?? 0);
                            
                            // On récupère l'ID du tuteur que vous avez injecté dans le contrôleur
                            $id_tuteur_responsable = (int)($event['tuteur_id'] ?? 0);

                            // On vérifie si l'utilisateur est membre du club_orga de l'événement
                            // Note : Cette information doit être présente dans votre base de données (table membres_club)
                            $stmtMembre = $this->db->prepare("
                                SELECT 1 FROM membres_club 
                                WHERE membre_id = ? AND club_id = ? AND valide = 1
                            ");
                            $stmtMembre->execute([$user_id_session, $event['club_orga']]);
                            $est_membre_du_club = (bool)$stmtMembre->fetch();

                            /**
                             * Accès autorisé si :
                             * - Admin ou SuperAdmin (perm > 2)
                             * - OU Tuteur spécifique (perm == 2) ET son ID match avec celui du club organisateur
                             */
                            $est_autorise = ($user_permission > 2) || ($user_permission === 2 && $user_id_session === $id_tuteur_responsable) || ($est_membre_du_club);

                            if ($est_autorise): ?>
                                <div class="event-section event-reserved-card">
                                    <div class="event-reserved-header">
                                        <h3 class="event-reserved-title">
                                            <i class="fas fa-folder-open"></i> Espace réservé
                                        </h3>
                                        <span class="event-reserved-tag">
                                            <i class="fas fa-shield-alt"></i> Réservé
                                        </span>
                                    </div>
                                    
                                    <div class="event-reserved-section">
                                        <h3><i class="fas fa-user-friends"></i> Inscrits (<?= (int)($subscription_count ?? 0) ?>)</h3>
                                        <?php if (!empty($subscribers)): ?>
                                            <ul class="event-subscribers-list">
                                                <?php foreach ($subscribers as $subscriber): ?>
                                                    <?php
                                                        $fullName = trim((string)($subscriber['prenom'] ?? '') . ' ' . (string)($subscriber['nom'] ?? ''));
                                                        $displayName = $fullName !== '' ? $fullName : 'Utilisateur';
                                                    ?>
                                                    <li><?= htmlspecialchars($displayName) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p>Aucun inscrit pour le moment.</p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="event-reserved-grid">
                                        <?php if ($event['financement_bde']): ?>
                                        <div class="event-reserved-item">
                                            <small>Budget BDE</small>
                                            <p><?= htmlspecialchars($event['montant']) ?> €</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="event-reserved-section">
                                        <h4 class="event-reserved-subtitle">Documents de planification</h4>
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <?php if (!empty($event['doc_organisation'])): ?>
                                                <a href="<?= htmlspecialchars($event['doc_organisation']) ?>" class="btn btn-sm" style="background: #0ea5e9; color: white;" target="_blank">
                                                    <i class="fas fa-file-invoice"></i> Dossier Organisation
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-sm" style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; cursor: not-allowed;">
                                                    <i class="fas fa-times-circle"></i> Dossier non déposé
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($event['affiche'])): ?>
                                                <a href="<?= htmlspecialchars($event['affiche']) ?>" class="btn btn-sm" style="background: #f59e0b; color: white;" target="_blank">
                                                    <i class="fas fa-image"></i> Affiche
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-sm" style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; cursor: not-allowed;">
                                                    <i class="fas fa-times-circle"></i> Affiche non déposée
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($event['fiche_sanitaire'])): ?>
                                                <a href="<?= htmlspecialchars($event['fiche_sanitaire']) ?>" class="btn btn-sm" style="background: #ef4444; color: white;" target="_blank">
                                                    <i class="fas fa-notes-medical"></i> Fiche Sanitaire
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-sm" style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; cursor: not-allowed;">
                                                    <i class="fas fa-times-circle"></i> Fiche sanitaire non déposée
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="event-reserved-section event-reserved-section--bordered">
                                        <h4 class="event-reserved-subtitle">Bilan & Souvenirs</h4>
                                        
                                        <?php if (!empty($event['rapport_event'])): ?>
                                            <div class="mb-20">
                                                <a href="<?= htmlspecialchars($event['rapport_event']) ?>" class="btn btn-outline btn-sm" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> Rapport final
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php 
                                        $photos = !empty($event['images_event']) ? explode(',', $event['images_event']) : [];
                                        if (!empty($photos)): 
                                        ?>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 15px;">
                                                <?php foreach ($photos as $url): ?>
                                                    <div class="photo-item" style="aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                                        <a href="<?= htmlspecialchars(trim($url)) ?>" target="_blank">
                                                            <img src="<?= htmlspecialchars(trim($url)) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php
                            // Motif de validation forcée : visible UNIQUEMENT par les membres du club,
                            // le tuteur du club et l'administration (perm >= 4). Le BDE en est EXCLU,
                            // conformément au retour client (juin 2026) — cohérent avec la fiche club.
                            $peut_voir_forcage = $est_membre_du_club
                                || ($user_permission === 2 && $user_id_session === $id_tuteur_responsable)
                                || $user_permission >= 4;
                            if (!empty(trim((string)($event['motif_forcage'] ?? ''))) && $peut_voir_forcage): ?>
                                <div class="event-reserved-section mb-20">
                                    <div class="event-reserved-card" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
                                        <h4 class="event-reserved-subtitle" style="color: #d97706;">
                                            <i class="fas fa-bolt"></i> Motif de validation forcée
                                        </h4>
                                        <p style="margin: 0; font-style: italic;"><?= nl2br(htmlspecialchars((string)$event['motif_forcage'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php
                            // Commentaire laissé par un valideur (tuteur/admin) lors de la validation.
                            // Visible par les membres du club, le tuteur et l'administration. Voir retour client (juin 2026).
                            if (!empty(trim((string)($event['commentaire_validation'] ?? ''))) && $est_autorise): ?>
                                <div class="event-reserved-section mb-20">
                                    <div class="event-reserved-card" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;">
                                        <h4 class="event-reserved-subtitle" style="color: #1d4ed8;">
                                            <i class="fas fa-comment-dots"></i> Commentaire des valideurs
                                        </h4>
                                        <p style="margin: 0; font-style: italic;"><?= nl2br(htmlspecialchars((string)$event['commentaire_validation'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
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
