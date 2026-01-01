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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
.admin-user-view {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 1.8rem;
    color: #2c3e50;
}

/* User Profile Card */
.user-profile-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

.profile-header {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 25px;
}

.profile-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    border: 4px solid rgba(255,255,255,0.3);
}

.profile-info h2 {
    font-size: 1.8rem;
    margin-bottom: 5px;
}

.profile-info .email {
    opacity: 0.9;
    font-size: 1rem;
    margin-bottom: 10px;
}

.profile-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.profile-badges .badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-permission {
    background: rgba(255,255,255,0.2);
}

.badge-promo {
    background: rgba(52, 152, 219, 0.8);
}

/* Profile Body */
.profile-body {
    padding: 25px 30px;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.profile-stat {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.profile-stat .number {
    font-size: 2rem;
    font-weight: bold;
    color: #3498db;
    display: block;
}

.profile-stat .label {
    color: #666;
    font-size: 0.9rem;
}

/* Actions */
.profile-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.profile-actions .btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary { background: #3498db; color: white; }
.btn-warning { background: #f39c12; color: white; }
.btn-danger { background: #e74c3c; color: white; }
.btn-secondary { background: #6c757d; color: white; }

/* Sections */
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
}

.section-header {
    padding: 18px 25px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-body {
    padding: 20px 25px;
}

/* List Items */
.item-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.item-details .name {
    font-weight: 600;
    color: #2c3e50;
}

.item-details .meta {
    font-size: 0.85rem;
    color: #7f8c8d;
}

.item-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.item-badge.past { background: #dfe6e9; color: #636e72; }
.item-badge.future { background: #d5f5e3; color: #27ae60; }
.item-badge.soon { background: #fdeaa7; color: #d68910; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.5;
}

/* Permission Change */
.permission-change-form {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #fef9e7;
    border-radius: 8px;
    margin-bottom: 15px;
}

.permission-change-form select {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.permission-change-form button {
    padding: 8px 20px;
    background: #27ae60;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
    </style>
</head>
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
                       class="btn btn-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ?');">
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

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
