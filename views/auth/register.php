<?php
/**
 * Page d'inscription utilisateur
 * * Processus d'inscription en plusieurs étapes :
 * 1. Saisie des informations personnelles (nom, prénom, email)
 * 2. Vérification de l'email avec code de confirmation
 * 3. Création du mot de passe
 */

// Redirection si déjà connecté
if (isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}
$pageCss = ['shared', 'buttons', 'forms', 'auth'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php $pageTitle = 'Inscription - EILCO'; ?>
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="auth-page">
            <div class="auth-card auth-card-wide">
                <div class="auth-header">
                    <i class="fas fa-user-plus"></i>
                    <h2>Créer un compte</h2>
                    <p>Rejoignez la communauté de l'EILCO</p>
                </div>

                <div class="auth-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_message)) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_message)) ?>
                            <p style="margin-top:10px;"><a href="/?page=login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Accéder à la connexion</a></p>
                        </div>
                    <?php else: ?>

                        <?php if (isset($reset_step) && $reset_step == 0): ?>
                            <form id="registerForm" class="auth-form" method="post" action="">
                                <?= Security::csrfField() ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nom"><i class="fas fa-user"></i> Nom</label>
                                        <input type="text" id="nom" name="nom" class="form-control" required placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="prenom"><i class="fas fa-user"></i> Prénom</label>
                                        <input type="text" id="prenom" name="prenom" class="form-control" required placeholder="Votre prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="promo-select"><i class="fas fa-id-badge"></i> Vous êtes</label>
                                    <select name="promo" id="promo-select" class="form-control" required>
                                        <option value="">Sélectionnez votre statut</option>
                                        <option value="etu" <?= (isset($_POST['promo']) && $_POST['promo'] == "etu") ? 'selected' : '' ?>>Étudiant</option>
                                        <option value="tuteur" <?= (isset($_POST['promo']) && $_POST['promo'] == "tuteur") ? 'selected' : '' ?>>Futur tuteur</option>
                                        <option value="admin" <?= (isset($_POST['promo']) && $_POST['promo'] == "admin") ? 'selected' : '' ?>>Personnel administratif</option>
                                    </select>
                                </div>

                                <div id="champ_promo" class="form-group" style="display: <?= (isset($_POST['promo']) && $_POST['promo'] == "etu") ? 'block' : 'none' ?>;">
                                    <label for="niveau-select"><i class="fas fa-graduation-cap"></i> Promotion</label>
                                    <select name="niveau" id="niveau-select" class="form-control">
                                        <option value="">Sélectionnez votre promotion</option>
                                        <option value="CP1" <?= (isset($_POST['niveau']) && $_POST['niveau'] == "CP1") ? 'selected' : '' ?>>CP1</option>
                                        <option value="CP2" <?= (isset($_POST['niveau']) && $_POST['niveau'] == "CP2") ? 'selected' : '' ?>>CP2</option>
                                        <option value="ING1" <?= (isset($_POST['niveau']) && $_POST['niveau'] == "ING1") ? 'selected' : '' ?>>ING1</option>
                                        <option value="ING2" <?= (isset($_POST['niveau']) && $_POST['niveau'] == "ING2") ? 'selected' : '' ?>>ING2</option>
                                        <option value="ING3" <?= (isset($_POST['niveau']) && $_POST['niveau'] == "ING3") ? 'selected' : '' ?>>ING3</option>
                                    </select>
                                </div>

                                <div id="champ_niveau" class="form-group" style="display: <?= (isset($_POST['niveau']) && $_POST['niveau'] == "ING2") ? 'block' : 'none' ?>;">
                                    <label for="ing2-select"><i class="fas fa-book"></i> Spécialité ING2</label>
                                    <select name="ing2_type" id="ing2-select" class="form-control">
                                        <option value="">Sélectionnez</option>
                                        <option value="FISE" <?= (isset($_POST['ing2_type']) && $_POST['ing2_type'] == "FISE") ? 'selected' : '' ?>>FISE</option>
                                        <option value="FISEA" <?= (isset($_POST['ing2_type']) && $_POST['ing2_type'] == "FISEA") ? 'selected' : '' ?>>FISEA</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="mail"><i class="fas fa-envelope"></i> Adresse e-mail</label>
                                    <input type="email" id="mail" name="mail" class="form-control" required placeholder="votre.nom@etu.eilco.univ-littoral.fr" value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>">
                                    <small id="mailError" class="form-hint" style="color: #dc3545; display: none; font-weight: bold;">Veuillez utiliser une adresse @etu.eilco.univ-littoral.fr ou @eilco.univ-littoral.fr</small>
                                    <small class="form-hint">Utilisez votre adresse email EILCO académique</small>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="password"><i class="fas fa-lock"></i> Mot de passe <span id="password-check-icon" class="check-icon"></span></label>
                                        <input type="password" id="password" name="password" class="form-control" required placeholder="Minimum 8 caractères">
                                    </div>
                                    <div class="form-group">
                                        <label for="cpassword"><i class="fas fa-lock"></i> Confirmer <span id="cpassword-check-icon" class="check-icon"></span></label>
                                        <input type="password" id="cpassword" name="cpassword" class="form-control" required placeholder="Répétez le mot de passe">
                                    </div>
                                </div>

                                <button type="submit" name="send_code" class="btn btn-primary btn-block btn-lg">
                                    <i class="fas fa-paper-plane"></i> Recevoir code de validation
                                </button>
                            </form>

                        <?php elseif (isset($reset_step) && $reset_step == 1): ?>
                            <form class="auth-form" method="post" action="">
                                <?= Security::csrfField() ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Un code de vérification a été envoyé à votre adresse email.
                                </div>
                                <div class="form-group">
                                    <label for="verification_code"><i class="fas fa-key"></i> Code de vérification</label>
                                    <input type="text" id="verification_code" name="verification_code" class="form-control form-control-lg text-center" required placeholder="000000" maxlength="6" style="letter-spacing: 5px; font-size: 1.5rem;">
                                </div>
                                <button type="submit" name="verify_code" class="btn btn-success btn-block btn-lg">
                                    <i class="fas fa-check-circle"></i> Valider et s'inscrire
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="auth-footer">
                            <p>Vous avez déjà un compte? <a href="/?page=login">Se connecter</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const promoSelect = document.getElementById("promo-select");
        const champPromo = document.getElementById("champ_promo");
        const niveauSelect = document.getElementById("niveau-select");
        const champNiveau = document.getElementById("champ_niveau");
        
        const registerForm = document.getElementById("registerForm");
        const mailInput = document.getElementById("mail");
        const mailError = document.getElementById("mailError");

        if (promoSelect) {
            promoSelect.addEventListener("change", function() {
                if (this.value === "etu") {
                    champPromo.style.display = "block";
                    if(niveauSelect) niveauSelect.required = true;
                } else {
                    champPromo.style.display = "none";
                    if(niveauSelect) niveauSelect.required = false;
                    champNiveau.style.display = "none";
                }
            });
        }

        if (niveauSelect) {
            niveauSelect.addEventListener("change", function() {
                champNiveau.style.display = (this.value === "ING2") ? "block" : "none";
            });
        }

        // Validation du domaine (autorise @etu.eilco... OU @eilco...)
        if (registerForm && mailInput) {
            registerForm.addEventListener("submit", function(e) {
                // Regex mise à jour : le (etu\.)? rend la partie "etu." optionnelle
                const re = /^[A-Za-z0-9._%+-]+@(etu\.)?eilco\.univ-littoral\.fr$/i;
                const v = (mailInput.value || '').trim();
                
                if (!re.test(v)) {
                    e.preventDefault();
                    if (mailError) mailError.style.display = 'block';
                    mailInput.focus();
                    return false;
                }
            });

            mailInput.addEventListener('input', function() {
                if (mailError) mailError.style.display = 'none';
            });
        }

        const password = document.getElementById("password");
        const cpassword = document.getElementById("cpassword");
        const passwordIcon = document.getElementById("password-check-icon");
        const cpasswordIcon = document.getElementById("cpassword-check-icon");

        if (password) {
            function validatePassword() {
                const isValid = password.value.length >= 8;
                passwordIcon.className = "check-icon " + (isValid ? "valid" : "invalid");
                passwordIcon.innerHTML = isValid ? "✓" : "✗";
            }

            function validateCPassword() {
                const match = cpassword.value === password.value && cpassword.value !== "";
                cpasswordIcon.className = "check-icon " + (match ? "valid" : "invalid");
                cpasswordIcon.innerHTML = match ? "✓" : "✗";
            }

            password.addEventListener("input", () => { validatePassword(); validateCPassword(); });
            if (cpassword) cpassword.addEventListener("input", validateCPassword);
        }
    });
    </script>
</body>
</html>