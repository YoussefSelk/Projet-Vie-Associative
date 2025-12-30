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
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-user-edit"></i> Éditer le profil</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="form-modern">
                        <?= Security::csrfField() ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="mail" class="form-control" value="<?= htmlspecialchars($user['mail']) ?>" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="edit_profile" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                            <a href="?page=profile" class="btn btn-outline"><i class="fas fa-times"></i> Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
