<?php
/**
 * Page de profil utilisateur
 * 
 * Affiche les informations du profil :
 * - Avatar et nom complet
 * - Role et campus
 * - Email et date d'inscription
 * - Clubs dont l'utilisateur est membre
 * - Bouton de modification du profil
 * 
 * Variables attendues :
 * - $user : Donnees de l'utilisateur
 * - $clubs : Clubs de l'utilisateur
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/User
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
            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                    <?php 
                    $roles = [
                        0 => 'Non vérifié',
                        1 => 'Étudiant',
                        2 => 'Membre club',
                        3 => 'BDE',
                        4 => 'Administrateur',
                        5 => 'Tuteur'
                    ];
                    $role = $roles[$user['permission'] ?? 1] ?? 'Étudiant';
                    ?>
                    <span class="badge badge-primary"><?= $role ?></span>
                </div>
                
                <div class="profile-body">
                    <div class="profile-section">
                        <h3><i class="fas fa-id-card"></i> Informations personnelles</h3>
                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <label>Nom</label>
                                <span><?= htmlspecialchars($user['nom']) ?></span>
                            </div>
                            <div class="profile-info-item">
                                <label>Prénom</label>
                                <span><?= htmlspecialchars($user['prenom']) ?></span>
                            </div>
                            <div class="profile-info-item">
                                <label>Adresse email</label>
                                <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['mail']) ?></span>
                            </div>
                            <?php if (!empty($user['promo'])): ?>
                            <div class="profile-info-item">
                                <label>Promotion</label>
                                <span><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($user['promo']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(isset($_SESSION['id']) && ($_SESSION['id'] == $user['id'] || ($_SESSION['permission'] ?? 0) >= 3)): ?>
                    <div class="profile-actions">
                        <a href="?page=profile-edit" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Éditer le profil
                        </a>
                        <a href="?page=my-subscriptions" class="btn btn-outline">
                            <i class="fas fa-calendar-check"></i> Mes inscriptions
                        </a>
                        <a href="?page=my-events" class="btn btn-outline">
                            <i class="fas fa-calendar-alt"></i> Mes événements
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
