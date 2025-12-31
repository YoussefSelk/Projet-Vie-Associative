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
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-user-circle"></i>
                    <?php if($reset_step == 0): ?>
                        <h2>Connexion</h2>
                        <p>Accédez à votre espace étudiant</p>
                    <?php elseif($reset_step == 1): ?>
                        <h2>Mot de passe oublié</h2>
                        <p>Entrez votre email pour recevoir un code</p>
                    <?php elseif($reset_step == 2): ?>
                        <h2>Vérification</h2>
                        <p>Entrez le code reçu par email</p>
                    <?php else: ?>
                        <h2>Nouveau mot de passe</h2>
                        <p>Créez votre nouveau mot de passe</p>
                    <?php endif; ?>
                </div>
                
                <div class="auth-body">
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    
                    <?php if($reset_step == 0): ?>
                        <form method="POST" class="auth-form">
                            <?= Security::csrfField() ?>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="mail" class="form-control" placeholder="votre@email.com" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="formsend" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </button>
                            <div class="auth-links">
                                <button type="submit" name="check-email" class="link-button">
                                    <i class="fas fa-key"></i> Mot de passe oublié ?
                                </button>
                            </div>
                        </form>
                        <div class="auth-footer">
                            <p>Pas encore inscrit ? <a href="?page=register">Créer un compte</a></p>
                        </div>
                    <?php elseif($reset_step == 1): ?>
                        <form method="POST" class="auth-form">
                            <?= Security::csrfField() ?>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="mail" class="form-control" placeholder="votre@email.com" required>
                            </div>
                            <button type="submit" name="send_reset_code" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-paper-plane"></i> Envoyer le code
                            </button>
                        </form>
                    <?php elseif($reset_step == 2): ?>
                        <form method="POST" class="auth-form">
                            <?= Security::csrfField() ?>
                            <div class="form-group">
                                <label><i class="fas fa-key"></i> Code de vérification</label>
                                <input type="text" name="reset_code" class="form-control" placeholder="123456" required>
                            </div>
                            <button type="submit" name="verify_reset_code" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-check"></i> Vérifier
                            </button>
                        </form>
                    <?php elseif($reset_step == 3): ?>
                        <form method="POST" class="auth-form">
                            <?= Security::csrfField() ?>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Confirmer</label>
                                <input type="password" name="cpassword" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-save"></i> Réinitialiser
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
