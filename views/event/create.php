<?php
/**
 * Formulaire de creation d'un evenement
 * * Permet aux membres de clubs de proposer des evenements :
 * - Selection du club organisateur
 * - Details (titre, description, lieu)
 * - Date et horaires
 * - Nombre de places (optionnel)
 * * L'evenement sera soumis a validation BDE puis tuteur.
 * * Variables attendues :
 * - $clubs : Liste des clubs dont l'utilisateur est membre
 * - $error_msg / $success_msg : Messages de feedback
 * * @package Views/Event
 */
$pageTitle = 'Créer un événement - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'events'];
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
                    <h3><i class="fas fa-calendar-plus"></i> Créer un nouvel événement ou activité</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
                    <?php endif; ?>
                    
                    <div class="info-box info-box-primary" style="margin-bottom: 25px; border-left: 5px solid #0056b3;">
                        <h4><i class="fas fa-info-circle"></i> Guide de soumission BDE</h4>
                        <div class="info-content">
                            <div style="display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 250px;">
                                    <p><strong><i class="fas fa-calendar-star" style="color: #e67e22;"></i> Événement</strong><br>
                                    <small>Soirée, gala, tournoi... Validation complète requise.</small></p>
                                </div>
                                <div style="flex: 1; min-width: 250px;">
                                    <p><strong><i class="fas fa-users" style="color: #27ae60;"></i> Activité</strong><br>
                                    <small>Réunion, atelier, entraînement... Processus simplifié.</small></p>
                                </div>
                            </div>

                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
                                <h5 style="margin-top: 0; color: #333;"><i class="fas fa-file-import"></i> Le dossier d'organisation</h5>
                                <p style="font-size: 0.9em; margin-bottom: 10px;">
                                    Obligatoire pour les <strong>Événements</strong> / Facultatif pour les <strong>Activités</strong>.
                                </p>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                                    <div style="font-size: 0.85em;">
                                        <i class="fas fa-sitemap"></i> <strong>Organisation</strong><br>
                                        Gantt, répartition tâches.
                                    </div>
                                    <div style="font-size: 0.85em;">
                                        <i class="fas fa-coins"></i> <strong>Budget</strong><br>
                                        Recettes/Dépenses, financements.
                                    </div>
                                    <div style="font-size: 0.85em;">
                                        <i class="fas fa-bullhorn"></i> <strong>Communication</strong><br>
                                        Canaux, visuels des affiches.
                                    </div>
                                </div>
                            </div>

                            <details style="margin-top: 15px;">
                                <summary style="cursor: pointer; color: #0056b3; font-weight: bold;">
                                    <i class="fas fa-chevron-right"></i> Rappel des règles générales
                                </summary>
                                <ul style="margin-top: 10px; padding-left: 20px; font-size: 0.9em;">
                                    <li>Dépôt au moins <strong>2 semaines à l'avance</strong>.</li>
                                    <li>Réservation de salle via le secrétariat uniquement.</li>
                                    <li>Autorisation spéciale requise pour les événements avec alcool.</li>
                                    <li>Rapport d'événement obligatoire après réalisation.</li>
                                </ul>
                            </details>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="form-modern" id="eventForm">
                        <?= Security::csrfField() ?>
                        
                        <div class="form-section">
                            <h4><i class="fas fa-tags"></i> Type</h4>
                            <div class="type-selector">
                                <label class="type-option selected" data-type="event">
                                    <input type="radio" name="type_event" value="event" checked>
                                    <i class="fas fa-calendar-star"></i>
                                    <span>Événement</span>
                                    <small>Validation complète requise</small>
                                </label>
                                <label class="type-option" data-type="activity">
                                    <input type="radio" name="type_event" value="activity">
                                    <i class="fas fa-users"></i>
                                    <span>Activité</span>
                                    <small>Champs optionnels</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Nom <span class="required">*</span></label>
                            <input type="text" name="nom_event" class="form-control" placeholder="Ex: Soirée d'intégration" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description <span class="required">*</span></label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Décrivez votre événement..." required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Date <span class="required">*</span></label>
                                <?php $minDate = date('Y-m-d', strtotime('+15 days')); ?>
                                <input type="date" name="date_ev" class="form-control" min="<?= $minDate ?>" required>
                                <small class="form-help">La date doit être au minimum dans 15 jours (à partir du <?= date('d/m/Y', strtotime('+15 days')) ?>)</small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure début <span class="required">*</span></label>
                                <input type="time" name="horaire_debut" class="form-control" value="13:30" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure fin <span class="required">*</span></label>
                                <input type="time" name="horaire_fin" class="form-control" value="17:30" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Campus <span class="required">*</span></label>
                                <select name="campus" class="form-control" required>
                                    <option value="">Sélectionnez...</option>
                                    <option value="Calais">Calais</option>
                                    <option value="Longuenesse">Longuenesse</option>
                                    <option value="Dunkerque">Dunkerque</option>
                                    <option value="Boulogne">Boulogne</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-map-pin"></i> Lieu précis <span class="required">*</span></label>
                                <input type="text" name="lieu" class="form-control" placeholder="Ex: Amphi A, Salle B203, Parvis..." required>
                            </div>
                        </div>
                        
                        <div id="eventFields" class="form-section">
                            <h4><i class="fas fa-clipboard-list"></i> Détails de l'événement</h4>
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Club organisateur <span class="required">*</span></label>
                                <select name="club_id" class="form-control" required>
                                    <option value="">Sélectionnez un club...</option>
                                    <?php foreach ($clubs as $club): ?>
                                        <option value="<?= $club['club_id'] ?>"><?= htmlspecialchars($club['nom_club']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-check">
                                        <input type="checkbox" name="financement_bde" value="1">
                                        <span><i class="fas fa-hand-holding-usd"></i> Demande de financement BDE</span>
                                    </label>
                                </div>
                                <div class="form-group" id="montantGroup" style="display: none;">
                                    <label><i class="fas fa-euro-sign"></i> Montant demandé (€)</label>
                                    <input type="number" name="montant" class="form-control" placeholder="Ex: 100" min="0" value="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-file-archive"></i> Dossier d'organisation (Gantt, Budget, Communication) <span class="required event-required" id="asterisque-doc">*</span></label>
                                    <input type="file" name="doc_organisation" id="doc_organisation" class="form-control" accept=".pdf" required>
                                    <small class="form-help">Obligatoire pour les événements (Gantt, budget prévisionnel et affiches).</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-file-medical"></i> Fiche sanitaire (PDF)</label>
                                    <input type="file" name="fiche_sanitaire" class="form-control" accept=".pdf">
                                    <small class="form-help">Obligatoire si l'événement implique de la nourriture ou des activités à risque</small>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Affiche de l'événement</label>
                                    <input type="file" name="affiche" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="form-help">Formats acceptés : JPG, PNG, PDF</small>
                                </div>
                            </div>
                            
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="create_event" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Créer
                            </button>
                            <a href="?page=event-list" class="btn btn-outline"><i class="fas fa-times"></i> Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        // Type selector logic
        document.querySelectorAll('.type-option').forEach(option => {
            option.addEventListener('click', function() {
                // Style visuel des boutons
                document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
                
                // Logique de distinction
                const isEvent = this.dataset.type === 'event';
                const docInput = document.getElementById('doc_organisation');
                const asterisque = document.getElementById('asterisque-doc');
                
                if (isEvent) {
                    docInput.setAttribute('required', 'required');
                    asterisque.style.display = 'inline';
                } else {
                    docInput.removeAttribute('required');
                    asterisque.style.display = 'none';
                }
            });
        });
        
        // Financement BDE toggle
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