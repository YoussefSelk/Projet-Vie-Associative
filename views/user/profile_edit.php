<?php
/**
 * Formulaire d'edition du profil
 * 
 * Permet a l'utilisateur de modifier ses informations :
 * - Nom et prenom
 * - Changement de mot de passe
 * - Preferences (campus, notifications)
 * 
 * Toutes les modifications sont protegees par CSRF.
 * Le changement de mot de passe requiert l'ancien mot de passe.
 * 
 * Variables attendues :
 * - $user : Donnees actuelles de l'utilisateur
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/User
 */
$pageTitle = 'Modifier mon profil - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'profiles'];
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
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-user-edit"></i> Éditer le profil</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
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
                            <label for="mail"><i class="fas fa-envelope"></i> Adresse e-mail</label>
                            <input type="email" id="mail" name="mail" class="form-control" 
                                required 
                                placeholder="votre.nom@etu.eilco.univ-littoral.fr" 
                                value="<?= htmlspecialchars($user['mail']) ?>">
                            
                            <small id="mailError" class="form-hint" style="color: #dc3545; display: none; font-weight: bold; margin-top: 5px;">
                                <i class="fas fa-exclamation-triangle"></i> Veuillez utiliser une adresse @etu.eilco.univ-littoral.fr ou @eilco.univ-littoral.fr
                            </small>
                            
                            <small class="form-hint">Utilisez votre adresse email EILCO académique</small>
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


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const editForm = document.querySelector(".form-modern");
            const mailInput = document.getElementById("mail");
            const mailError = document.getElementById("mailError");

            if (editForm && mailInput) {
                editForm.addEventListener("submit", function(e) {
                    // Regex identique à l'inscription : autorise @etu.eilco... OU @eilco...
                    const re = /^[A-Za-z0-9._%+-]+@(etu\.)?eilco\.univ-littoral\.fr$/i;
                    const v = (mailInput.value || '').trim();
                    
                    if (!re.test(v)) {
                        e.preventDefault(); // Bloque l'envoi du formulaire
                        if (mailError) mailError.style.display = 'block';
                        mailInput.style.borderColor = '#dc3545';
                        mailInput.focus();
                        return false;
                    }
                });

                // Cache l'erreur dès que l'utilisateur recommence à taper
                mailInput.addEventListener('input', function() {
                    if (mailError) mailError.style.display = 'none';
                    mailInput.style.borderColor = '';
                });
            }
        });
        </script>


</body>
</html>
