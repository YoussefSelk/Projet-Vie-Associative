<?php
/**
 * Vue detaillee d'un utilisateur - Administration
 * 
 * Affiche le profil complet d'un utilisateur :
 * - Informations personnelles
 * - Historique des activites
 * - Clubs et evenements associes
 * - Actions administratives (modifier, supprimer)
 * 
 * Variables attendues :
 * - $user : Donnees de l'utilisateur
 * - $clubs : Clubs dont l'utilisateur est membre
 * - $events : Evenements auxquels il a participe
 * - $activity : Historique d'activite
 * 
 * Permissions : Admin (niveau 5) requis
 * 
 * @package Views/Admin
 */
$pageCss = ['shared', 'buttons', 'forms', 'tables', 'admin', 'profiles'];
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
        <div class="admin-user-view">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-user-edit"></i> Détails de l'utilisateur</h1>
        <a href="?page=admin-users" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
    </div>
    
    <!-- User Profile Card -->
    <div class="user-profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
                <div class="email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['mail']); ?></div>
                <div class="profile-badges">
                    <?php
                        $perm_names = [0 => 'Invité', 1 => 'Utilisateur', 2 => 'Tuteur', 3 => 'BDE', 4 => 'Personnel', 5 => 'Super Admin'];
                    ?>
                    <span class="badge badge-permission">
                        <i class="fas fa-shield-alt"></i> <?php echo $perm_names[$user['permission']] ?? 'Inconnu'; ?> (<?php echo $user['permission']; ?>)
                    </span>
                    <?php if (!empty($user['promo'])): ?>
                        <span class="badge badge-promo">
                            <i class="fas fa-graduation-cap"></i> Promo <?php echo htmlspecialchars($user['promo']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="profile-body">
            <div class="profile-stats">
                <div class="profile-stat">
                    <span class="number"><?php echo count($clubs); ?></span>
                    <span class="label">Clubs rejoints</span>
                </div>
                <div class="profile-stat">
                    <span class="number"><?php echo count($subscriptions); ?></span>
                    <span class="label">Inscriptions</span>
                </div>
                <div class="profile-stat">
                    <span class="number"><?php echo $user['id']; ?></span>
                    <span class="label">ID Utilisateur</span>
                </div>
            </div>
            
            <?php if ($user['id'] != $_SESSION['id']): ?>
                <form class="permission-change-form" method="POST" action="?page=update-permission">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <label><i class="fas fa-key"></i> Modifier la permission :</label>
                    <select name="permission">
                        <?php for ($p = 0; $p <= 5; $p++): ?>
                            <option value="<?php echo $p; ?>" <?php if ($user['permission'] == $p) echo 'selected'; ?>>
                                <?php echo $p; ?> - <?php echo $perm_names[$p]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit"><i class="fas fa-save"></i> Enregistrer</button>
                </form>
            <?php endif; ?>
            
            <div class="profile-actions">
                <?php if ($user['id'] != $_SESSION['id']): ?>
                    <a href="?page=delete-user&id=<?php echo $user['id']; ?>" 
                       class="btn btn-danger btn-delete-this-user"
                       data-user-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>">
                        <i class="fas fa-trash"></i> Supprimer l'utilisateur
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Clubs Section -->
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-users"></i> Clubs rejoints (<?php echo count($clubs); ?>)
        </div>
        <div class="section-body">
            <?php if (empty($clubs)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Cet utilisateur n'a rejoint aucun club</p>
                </div>
            <?php else: ?>
                <div class="item-list">
                    <?php foreach ($clubs as $club): ?>
                        <div class="item-row">
                            <div class="item-info">
                                <div class="item-icon"><i class="fas fa-users"></i></div>
                                <div class="item-details">
                                    <div class="name"><?php echo htmlspecialchars($club['nom_club']); ?></div>
                                    <div class="meta"><?php echo htmlspecialchars($club['campus'] ?? ''); ?> - <?php echo htmlspecialchars($club['type_club'] ?? ''); ?></div>
                                </div>
                            </div>
                            <a href="?page=club-view&id=<?php echo $club['club_id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Subscriptions Section -->
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-calendar-check"></i> Inscriptions aux événements (<?php echo count($subscriptions); ?>)
        </div>
        <div class="section-body">
            <?php if (empty($subscriptions)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Cet utilisateur n'est inscrit à aucun événement</p>
                </div>
            <?php else: ?>
                <div class="item-list">
                    <?php foreach ($subscriptions as $sub): ?>
                        <?php
                            $event_date = strtotime($sub['date_ev']);
                            $now = time();
                            $is_past = $event_date < $now;
                            $is_soon = !$is_past && $event_date <= $now + (7 * 24 * 60 * 60);
                            $badge_class = $is_past ? 'past' : ($is_soon ? 'soon' : 'future');
                            $badge_text = $is_past ? 'Passé' : ($is_soon ? 'Bientôt' : 'À venir');
                        ?>
                        <div class="item-row">
                            <div class="item-info">
                                <div class="item-icon" style="background: <?php echo $is_past ? '#95a5a6' : '#27ae60'; ?>;">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="item-details">
                                    <div class="name"><?php echo htmlspecialchars($sub['titre']); ?></div>
                                    <div class="meta">
                                        <?php echo date('d/m/Y H:i', $event_date); ?>
                                        <?php if (!empty($sub['nom_club'])): ?>
                                            - <?php echo htmlspecialchars($sub['nom_club']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="item-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    </main>

    <script>
    // Delete user confirmation with SweetAlert2
    const deleteBtn = document.querySelector('.btn-delete-this-user');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const userName = deleteBtn.dataset.userName;
            const deleteUrl = deleteBtn.href;
            
            SwalHelper.confirmDelete('l\'utilisateur "' + userName + '"')
                .then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
        });
    }
    </script>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
