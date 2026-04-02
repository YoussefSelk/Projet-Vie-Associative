<?php
/**
 * Formulaire de modification d'un evenement
 * * Permet aux membres de clubs de modifier un evenement existant :
 * - Les donnees actuelles sont pre-remplies
 * - Gestion du changement de type (Event vs Activité)
 * - Mise a jour des fichiers optionnelle
 * * Variables attendues :
 * - $event : Tableau contenant les donnees de l'evenement a modifier
 * - $clubs : Liste des clubs dont l'utilisateur est membre
 * - $error_msg / $success_msg : Messages de feedback
 * * @package Views/Event
 */
$pageTitle = 'Modifier l\'événement - ' . htmlspecialchars($event['nom_event']);
$pageCss = ['shared', 'buttons', 'forms', 'events'];

// Logique pour determiner si c'est un event ou une activite au chargement
$isEvent = ($event['type_event'] === 'event');
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
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> Modifier : <?= htmlspecialchars($event['nom_event']) ?></h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="form-modern" id="editEventForm">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                        
                        <div class="form-section">
                            <h4><i class="fas fa-tags"></i> Type</h4>
                            <div class="type-selector">
                                <label class="type-option <?= $isEvent ? 'selected' : '' ?>" data-type="event">
                                    <input type="radio" name="type_event" value="event" <?= $isEvent ? 'checked' : '' ?>>
                                    <i class="fas fa-calendar-star"></i>
                                    <span>Événement</span>
                                    <small>Validation complète</small>
                                </label>
                                <label class="type-option <?= !$isEvent ? 'selected' : '' ?>" data-type="activity">
                                    <input type="radio" name="type_event" value="activity" <?= !$isEvent ? 'checked' : '' ?>>
                                    <i class="fas fa-users"></i>
                                    <span>Activité</span>
                                    <small>Processus simplifié</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Nom <span class="required">*</span></label>
                            <input type="text" name="nom_event" class="form-control" value="<?= htmlspecialchars($event['nom_event']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description <span class="required">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($event['description']) ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Date <span class="required">*</span></label>
                                <input type="date" name="date_ev" class="form-control" value="<?= $event['date_ev'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure début <span class="required">*</span></label>
                                <input type="time" name="horaire_debut" class="form-control" value="<?= substr($event['horaire_debut'], 0, 5) ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure fin <span class="required">*</span></label>
                                <input type="time" name="horaire_fin" class="form-control" value="<?= substr($event['horaire_fin'], 0, 5) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Campus <span class="required">*</span></label>
                                <select name="campus" class="form-control" required>
                                    <?php $campuses = ['Calais', 'Longuenesse', 'Dunkerque', 'Boulogne']; ?>
                                    <?php foreach($campuses as $c): ?>
                                        <option value="<?= $c ?>" <?= ($event['campus'] == $c) ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-map-pin"></i> Lieu précis <span class="required">*</span></label>
                                <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($event['lieu']) ?>" required>
                            </div>
                        </div>
                        
                        <div id="eventFields" class="form-section">
                            <h4><i class="fas fa-clipboard-list"></i> Détails et Documents</h4>
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Club organisateur <span class="required">*</span></label>
                                <select name="club_id" class="form-control" required>
                                    <?php foreach ($clubs as $club): ?>
                                        <option value="<?= $club['club_id'] ?>" <?= ($event['club_id'] == $club['club_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($club['nom_club']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-check">
                                        <input type="checkbox" name="financement_bde" value="1" <?= $event['financement_bde'] ? 'checked' : '' ?>>
                                        <span><i class="fas fa-hand-holding-usd"></i> Demande de financement BDE</span>
                                    </label>
                                </div>
                                <div class="form-group" id="montantGroup" style="<?= $event['financement_bde'] ? '' : 'display: none;' ?>">
                                    <label><i class="fas fa-euro-sign"></i> Montant (€)</label>
                                    <input type="number" name="montant" class="form-control" value="<?= $event['montant'] ?? 0 ?>" min="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-file-archive"></i> Dossier d'organisation <span class="required event-required" id="asterisque-doc" style="<?= $isEvent ? '' : 'display:none;' ?>">*</span></label>
                                    <input type="file" name="doc_organisation" id="doc_organisation" class="form-control" accept=".pdf">
                                    <?php if(!empty($event['doc_organisation'])): ?>
                                        <small class="form-help text-success"><i class="fas fa-file-pdf"></i> Fichier actuel : <a href="<?= $event['doc_organisation'] ?>" target="_blank">Consulter</a></small>
                                    <?php endif; ?>
                                    <small class="form-help">Laissez vide pour conserver le fichier actuel.</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-file-medical"></i> Fiche sanitaire (PDF)</label>
                                    <input type="file" name="fiche_sanitaire" class="form-control" accept=".pdf">
                                    <?php if(!empty($event['fiche_sanitaire'])): ?>
                                        <small class="form-help"><i class="fas fa-paperclip"></i> <a href="<?= $event['fiche_sanitaire'] ?>" target="_blank">Voir l'ancienne fiche</a></small>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Affiche</label>
                                    <input type="file" name="affiche" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                    <?php if(!empty($event['affiche'])): ?>
                                        <small class="form-help"><i class="fas fa-image"></i> <a href="<?= $event['affiche'] ?>" target="_blank">Affiche actuelle</a></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_event" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="?page=my-events" class="btn btn-outline">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        const form = document.getElementById('editEventForm');
        const docInput = document.getElementById('doc_organisation');
        const asterisque = document.getElementById('asterisque-doc');
        
        // On vérifie si un fichier existe déjà en base (via PHP)
        const hasExistingFile = <?= !empty($event['doc_organisation']) ? 'true' : 'false' ?>;

        // Logique de sélection du type
        document.querySelectorAll('.type-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('input');
                radio.checked = true;
                
                const isEvent = radio.value === 'event';
                if (asterisque) asterisque.style.display = isEvent ? 'inline' : 'none';
            });
        });

        // Validation au moment de soumettre le formulaire
        form.addEventListener('submit', function(e) {
            const selectedType = document.querySelector('input[name="type_event"]:checked').value;
            
            // Si c'est un EVENT, qu'il n'y a pas de fichier en BDD et pas de fichier sélectionné
            if (selectedType === 'event' && !hasExistingFile && docInput.files.length === 0) {
                e.preventDefault(); // On bloque l'envoi
                alert("Le dossier d'organisation est obligatoire pour un événement.");
                docInput.focus();
                docInput.style.borderColor = "red";
            }
        });

        // Toggle montant financement
        const financementCheckbox = document.querySelector('input[name="financement_bde"]');
        const montantGroup = document.getElementById('montantGroup');
        if (financementCheckbox && montantGroup) {
            financementCheckbox.addEventListener('change', function() {
                montantGroup.style.display = this.checked ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>