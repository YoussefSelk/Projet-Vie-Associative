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
                <div class="header-left">
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <h1><i class="fas fa-users-cog"></i> Gestion des utilisateurs</h1>
                <p class="subtitle"><?= count($users) ?> utilisateurs inscrits</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-users"></i>
                            <p>Aucun utilisateur trouvé</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $roleNames = [
                                        0 => ['name' => 'Non vérifié', 'class' => 'badge-warning'],
                                        1 => ['name' => 'Étudiant', 'class' => 'badge-info'],
                                        2 => ['name' => 'Membre club', 'class' => 'badge-primary'],
                                        3 => ['name' => 'BDE', 'class' => 'badge-success'],
                                        4 => ['name' => 'Admin', 'class' => 'badge-danger'],
                                        5 => ['name' => 'Tuteur', 'class' => 'badge-purple']
                                    ];
                                    foreach ($users as $user): 
                                        $role = $roleNames[$user['permission'] ?? 1] ?? $roleNames[1];
                                    ?>
                                        <tr>
                                            <td><span class="text-muted">#<?= htmlspecialchars($user['id']) ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-envelope text-muted"></i>
                                                <?= htmlspecialchars($user['mail']) ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $role['class'] ?>"><?= $role['name'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
