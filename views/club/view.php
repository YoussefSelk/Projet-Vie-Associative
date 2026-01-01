<?php
/**
 * Vue de detail d'un club
 * 
 * Affiche les informations completes d'un club :
 * - En-tete avec logo, nom, type et campus
 * - Description du club
 * - Liste des membres
 * - Actions (rejoindre, quitter, gerer)
 * 
 * Variables attendues du controleur :
 * - $club : Donnees du club
 * - $error_msg : Message d'erreur eventuel
 * - $success_msg : Message de succes eventuel
 * - $is_member : Indicateur si l'utilisateur est membre
 * - $members : Liste des membres du club
 * 
 * @package Views/Club
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
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error_msg) ?>
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
                
                <!-- Carte de detail du club -->
                <div class="club-detail-card">
                    <div class="club-header" style="background: linear-gradient(135deg, <?= $campusColor ?> 0%, <?= $campusColor ?>aa 100%);">
                        <div class="club-icon-large">
                            <?php if (!empty($club['logo']) && file_exists(ROOT_PATH . '/uploads/logos/' . $club['logo'])): ?>
                                <img src="/uploads/logos/<?= htmlspecialchars($club['logo']) ?>" alt="Logo <?= htmlspecialchars($club['nom_club']) ?>">
                            <?php else: ?>
                                <i class="fas <?= $clubIcon ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h1><?= htmlspecialchars($club['nom_club']) ?></h1>
                        <div class="club-badges">
                            <span class="badge badge-light">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($club['type_club'] ?? 'Club') ?>
                            </span>
                            <span class="badge badge-light">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($club['campus'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="club-body">
                        <!-- Section description -->
                        <div class="club-section">
                            <h3><i class="fas fa-info-circle"></i> À propos</h3>
                            <?php 
                            // Traitement de la description
                            $description = $club['description'] ?? '';
                            $description = trim($description);
                            // Decodage des entites HTML si presentes en BDD
                            $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                            ?>
                            <?php if (!empty($description)): ?>
                                <p class="club-description"><?= nl2br(htmlspecialchars($description)) ?></p>
                            <?php else: ?>
                                <p class="club-description text-muted"><em>Aucune description disponible.</em></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($club['mail'])): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-envelope"></i> Contact</h3>
                            <p><a href="mailto:<?= htmlspecialchars($club['mail']) ?>"><?= htmlspecialchars($club['mail']) ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($club['president'])): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-user-tie"></i> Président</h3>
                            <p><?= htmlspecialchars($club['president']) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tutor)): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-chalkboard-teacher"></i> Tuteur</h3>
                            <p>
                                <?= htmlspecialchars($tutor['prenom'] . ' ' . $tutor['nom']) ?>
                                <?php if (!empty($tutor['mail'])): ?>
                                    <br><small><a href="mailto:<?= htmlspecialchars($tutor['mail']) ?>"><?= htmlspecialchars($tutor['mail']) ?></a></small>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($members)): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-users"></i> Membres (<?= count($members) ?>)</h3>
                            <div class="members-list">
                                <?php foreach ($members as $member): ?>
                                <div class="member-item">
                                    <div class="member-avatar">
                                        <?= strtoupper(substr($member['prenom'] ?? '', 0, 1) . substr($member['nom'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div class="member-info">
                                        <span class="member-name"><?= htmlspecialchars(($member['prenom'] ?? '') . ' ' . ($member['nom'] ?? '')) ?></span>
                                        <?php if (!empty($member['fonction'])): ?>
                                            <span class="member-role"><?= htmlspecialchars($member['fonction']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($events)): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-calendar-alt"></i> Événements récents</h3>
                            <div class="events-list">
                                <?php foreach ($events as $event): ?>
                                <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="event-link">
                                    <div class="event-item">
                                        <span class="event-date"><?= date('d/m/Y', strtotime($event['date_ev'])) ?></span>
                                        <span class="event-title"><?= htmlspecialchars($event['titre']) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($club['motif_refus']) && isset($_SESSION['id'])): ?>
                        <div class="club-section">
                            <h3><i class="fas fa-comment-alt"></i> Remarques</h3>
                            <p class="club-remarks"><?= nl2br(htmlspecialchars($club['motif_refus'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="club-actions">
                            <?php if (isset($_SESSION['id'])): ?>
                                <a href="?page=event-list" class="btn btn-primary">
                                    <i class="fas fa-calendar-alt"></i> Voir les événements
                                </a>
                            <?php endif; ?>
                            <a href="?page=home" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <h3>Club non trouvé</h3>
                    <p>Le club que vous recherchez n'existe pas ou a été supprimé.</p>
                    <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
