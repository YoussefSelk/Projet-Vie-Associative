<?php
/**
 * Vue de detail d'un club
 * * Affiche les informations completes d'un club :
 * - En-tete avec logo, nom, type et campus
 * - Description du club
 * - Liste des membres
 * - Actions (rejoindre, quitter, gerer)
 * * Variables attendues du controleur :
 * - $club : Donnees du club
 * - $error_msg : Message d'erreur eventuel
 * - $success_msg : Message de succes eventuel
 * - $is_member : Indicateur si l'utilisateur est membre
 * - $members : Liste des membres du club
 * * @package Views/Club
 */
$pageTitle = 'Détails du club - EILCO';
$pageCss = ['shared', 'buttons', 'tables', 'clubs'];

// --- SECURITE : Vérifier si l'utilisateur peut voir les soutenances (Permission >= 2) ---
$user_permission = isset($_SESSION['permission']) ? (int)$_SESSION['permission'] : 0;
$can_view_soutenance = ($user_permission >= 2);
// ----------------------------------------------------------------------------------------
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
            <?php if (isset($_GET['created']) && $_GET['created'] == 1): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> Club créé avec succès ! Il est maintenant en attente de validation. Les membres apparaissent ci-dessous.
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-shield-alt"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars(strip_tags((string)$error_msg)) ?>
                </div>
                <div class="text-center mt-20">
                    <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </div>
            <?php elseif ($club): ?>
                <?php 
                // Definition des icones par type de club
                $clubIcons = [
                    'sport' => 'fa-running',
                    'musique' => 'fa-music',
                    'culture' => 'fa-theater-masks',
                    'tech' => 'fa-laptop-code',
                    'informatique' => 'fa-laptop-code',
                    'jeux' => 'fa-gamepad',
                    'gaming' => 'fa-gamepad',
                    'esport' => 'fa-gamepad',
                    'art' => 'fa-palette',
                    'photo' => 'fa-camera',
                    'video' => 'fa-video',
                    'humanitaire' => 'fa-hands-helping',
                    'environnement' => 'fa-leaf',
                    'lecture' => 'fa-book',
                    'cuisine' => 'fa-utensils',
                    'danse' => 'fa-person-booth',
                    'default' => 'fa-users'
                ];
                
                // Selection de l'icone correspondant au type
                $clubType = strtolower($club['type_club'] ?? '');
                $clubIcon = $clubIcons['default'];
                foreach ($clubIcons as $key => $icon) {
                    if (strpos($clubType, $key) !== false) {
                        $clubIcon = $icon;
                        break;
                    }
                }
                
                // Couleurs par campus
                $campusColors = [
                    'calais' => '#0066cc',
                    'longuenesse' => '#28a745',
                    'dunkerque' => '#dc3545',
                    'boulogne' => '#ffc107'
                ];
                $campusColor = $campusColors[strtolower($club['campus'] ?? 'calais')] ?? '#0066cc';
                ?>
                
                <div class="club-detail-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden; background: #fff;">
                    <div class="club-header" style="background: linear-gradient(135deg, <?= $campusColor ?> 0%, <?= $campusColor ?>cc 100%); padding: 30px; color: white; text-align: center;">
                        <div class="club-icon-large" style="width: 100px; height: 100px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: <?= $campusColor ?>; font-size: 2.5rem; overflow: hidden;">
                            <?php if (!empty($club['logo']) && file_exists(ROOT_PATH . '/uploads/logos/' . $club['logo'])): ?>
                                <img src="<?= defined('ASSET_BASE') ? ASSET_BASE : '' ?>/uploads/logos/<?= htmlspecialchars($club['logo']) ?>" alt="Logo <?= htmlspecialchars($club['nom_club']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas <?= $clubIcon ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h1 style="margin: 0 0 10px; font-size: 2rem; font-weight: 700;"><?= htmlspecialchars($club['nom_club']) ?></h1>
                        <div class="club-badges" style="display: flex; gap: 10px; justify-content: center;">
                            <span class="badge" style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 0.9rem;">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($club['type_club'] ?? 'Club') ?>
                            </span>
                            <span class="badge" style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 0.9rem;">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($club['campus'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="club-body" style="padding: 30px;">
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #333;"><i class="fas fa-info-circle" style="color: <?= $campusColor ?>;"></i> À propos</h3>
                            <?php 
                            // Traitement de la description
                            $description = $club['description'] ?? '';
                            $description = trim($description);
                            $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                            ?>
                            <?php if (!empty($description)): ?>
                                <p class="club-description" style="line-height: 1.6; color: #555;"><?= nl2br(htmlspecialchars($description)) ?></p>
                            <?php else: ?>
                                <p class="club-description text-muted"><em>Aucune description disponible.</em></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($club['mail'])): ?>
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #333;"><i class="fas fa-envelope" style="color: <?= $campusColor ?>;"></i> Contact</h3>
                            <p><a href="mailto:<?= htmlspecialchars($club['mail']) ?>" style="color: <?= $campusColor ?>; font-weight: 500; text-decoration: none;"><?= htmlspecialchars($club['mail']) ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                            <?php if (!empty($club['president'])): ?>
                            <div class="club-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-top: 0; color: #333; font-size: 1.1rem;"><i class="fas fa-user-tie" style="color: <?= $campusColor ?>;"></i> Président</h3>
                                <p style="margin: 0; font-weight: 600; color: #555;"><?= htmlspecialchars($club['president']) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tutor)): ?>
                            <div class="club-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-top: 0; color: #333; font-size: 1.1rem;"><i class="fas fa-chalkboard-teacher" style="color: <?= $campusColor ?>;"></i> Tuteur</h3>
                                <p style="margin: 0; font-weight: 600; color: #555;">
                                    <?= htmlspecialchars($tutor['prenom'] . ' ' . $tutor['nom']) ?>
                                    <?php if (!empty($tutor['mail'])): ?>
                                        <br><small><a href="mailto:<?= htmlspecialchars($tutor['mail']) ?>" style="color: #666; font-weight: normal;"><?= htmlspecialchars($tutor['mail']) ?></a></small>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($members)): ?>
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #333;">
                                <i class="fas fa-users" style="color: <?= $campusColor ?>;"></i> Membres (<?= count($members) ?>)
                            </h3>
                            
                            <div class="members-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
                                <?php foreach ($members as $member): ?>
                                <div class="member-item" style="display: flex; align-items: center; background: #fff; border: 1px solid #eaeaea; padding: 12px 15px; border-radius: 8px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="member-avatar" style="width: 45px; height: 45px; min-width: 45px; border-radius: 50%; background: #e8f4fd; color: <?= $campusColor ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; margin-right: 15px;">
                                        <?= strtoupper(substr($member['prenom'] ?? '', 0, 1) . substr($member['nom'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div class="member-info" style="flex: 1; overflow: hidden;">
                                        <div class="member-name" style="font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars(($member['prenom'] ?? '') . ' ' . ($member['nom'] ?? '')) ?>
                                        </div>
                                        <?php if (!empty($member['fonction'])): ?>
                                            <div class="member-role" style="font-size: 0.85em; color: #6b7280;">
                                                <?= htmlspecialchars($member['fonction']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($can_view_soutenance && !empty($member['soutenance'])): ?>
                                        <div class="member-soutenance" style="margin-left: 10px;" title="Soutenance prévue">
                                            <span style="background: #fdf6b2; color: #9a3412; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-graduation-cap"></i> <span class="hide-mobile">Soutenance</span>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($events)): ?>
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #333;"><i class="fas fa-calendar-alt" style="color: <?= $campusColor ?>;"></i> Événements récents</h3>
                            <div class="events-list" style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($events as $event): ?>
                                <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="event-link" style="text-decoration: none;">
                                    <div class="event-item" style="display: flex; gap: 15px; background: #f8f9fa; padding: 12px 15px; border-radius: 8px; border-left: 4px solid <?= $campusColor ?>; color: #333;">
                                        <span class="event-date" style="font-weight: 600; color: <?= $campusColor ?>; min-width: 80px;"><?= date('d/m/Y', strtotime($event['date_ev'])) ?></span>
                                        <span class="event-title" style="font-weight: 500;"><?= htmlspecialchars($event['titre']) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($club['motif_refus']) && isset($_SESSION['id'])): ?>
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #dc3545;"><i class="fas fa-comment-alt"></i> Remarques de l'administration</h3>
                            <div style="background: #fff5f5; border: 1px solid #ffcdd2; padding: 15px; border-radius: 8px; color: #c53030;">
                                <p class="club-remarks" style="margin: 0; font-style: italic;"><?= nl2br(htmlspecialchars($club['motif_refus'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty(trim((string)($club['motif_forcage'] ?? ''))) && isset($_SESSION['id'])): ?>
                        <div class="club-section" style="margin-bottom: 30px;">
                            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #d97706;"><i class="fas fa-bolt"></i> Motif de validation forcée</h3>
                            <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 15px; border-radius: 8px; color: #92400e;">
                                <p class="club-remarks" style="margin: 0; font-style: italic;"><?= nl2br(htmlspecialchars((string)$club['motif_forcage'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="club-actions" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 15px; flex-wrap: wrap;">
    
                            <?php if (!empty($canEditClub) && (!isset($club['validation_finale']) || $club['validation_finale'] != 1)): ?>
                                <a href="?page=club-edit&id=<?= $club['club_id'] ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Modifier la demande
                                </a>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['id'])): ?>
                                <a href="?page=event-list" class="btn btn-primary">
                                    <i class="fas fa-calendar-alt"></i> Voir les événements
                                </a>
                            <?php endif; ?>
                            <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                                <a href="?page=club-edit&id=<?= $club['club_id'] ?>" class="btn btn-outline" style="border-color:#0066cc;color:#0066cc;">
                                    <i class="fas fa-shield-alt"></i> Modifier (Admin)
                                </a>
                            <?php endif; ?>
                            <a href="?page=home" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 50px 20px;">
                    <i class="fas fa-building" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>Club non trouvé</h3>
                    <p style="color: #666; margin-bottom: 20px;">Le club que vous recherchez n'existe pas ou a été supprimé.</p>
                    <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <style>
        .member-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05) !important;
        }
        .event-item:hover {
            background: #f1f3f5 !important;
        }
        @media (max-width: 600px) {
            .hide-mobile { display: none; }
            .member-soutenance span { padding: 6px; }
            .club-actions { justify-content: center; }
        }
    </style>
</body>
</html>
