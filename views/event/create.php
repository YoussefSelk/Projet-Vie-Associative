<?php
/**
 * Formulaire de création d'un événement
 * * Permet aux membres de clubs de proposer des événements :
 * - Sélection du club organisateur
 * - Détails (titre, description, lieu)
 * - Date et horaires
 * - Nombre de places (optionnel)
 * * L'événement sera soumis à validation BDE puis tuteur.
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
                    
                    <div class="info-box info-box-primary submission-guide">
                        <h4><i class="fas fa-info-circle"></i> Guide de soumission BDE</h4>
                        <div class="submission-guide-content">
                            <div class="submission-guide-types">
                                <div class="submission-guide-type-card event">
                                    <p class="submission-guide-type-title"><i class="fas fa-calendar-check"></i> Événement</p>
                                    <p class="submission-guide-type-text">Soirée, gala, tournoi... Validation complète requise.</p>
                                </div>
                                <div class="submission-guide-type-card activity">
                                    <p class="submission-guide-type-title"><i class="fas fa-users"></i> Activité</p>
                                    <p class="submission-guide-type-text">Réunion, atelier, entraînement... Processus simplifié.</p>
                                </div>
                            </div>

                            <div class="submission-guide-dossier">
                                <h5><i class="fas fa-file-import"></i> Le dossier d'organisation</h5>
                                <p class="submission-guide-dossier-intro">
                                    Obligatoire pour les <strong>Événements</strong> / Facultatif pour les <strong>Activités</strong>.
                                </p>
                                <div class="submission-guide-dossier-grid">
                                    <div class="submission-guide-dossier-item">
                                        <i class="fas fa-sitemap"></i> <strong>Organisation</strong><br>
                                        Gantt, répartition tâches.
                                    </div>
                                    <div class="submission-guide-dossier-item">
                                        <i class="fas fa-coins"></i> <strong>Budget</strong><br>
                                        Recettes/Dépenses, financements.
                                    </div>
                                    <div class="submission-guide-dossier-item">
                                        <i class="fas fa-bullhorn"></i> <strong>Communication</strong><br>
                                        Canaux, visuels des affiches.
                                    </div>
                                </div>
                            </div>

                            <details class="submission-guide-rules">
                                <summary>
                                    <span>Rappel des règles générales</span>
                                </summary>
                                <ul>
                                    <li>Dépôt au moins <strong>2 semaines à l'avance</strong>.</li>
                                    <li>Réservation de salle via le secrétariat uniquement.</li>
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
                                    <i class="fas fa-calendar-check"></i>
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
                            <?php include_once VIEWS_PATH . '/includes/time_select.php'; ?>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure début <span class="required">*</span></label>
                                <?php renderTimeSelect('horaire_debut', 'horaire_debut', '13:30'); ?>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Heure fin <span class="required">*</span></label>
                                <?php renderTimeSelect('horaire_fin', 'horaire_fin', '17:30'); ?>
                            </div>
                        </div>
                        <?php include VIEWS_PATH . '/includes/time_select_script.php'; ?>
                        
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
                            
                            <div class="form-row" style="display: flex; align-items: center; gap: 16px;">
                                <div class="form-group" style="margin: 0;">
                                    <label class="form-check" style="display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="financement_bde" value="1" id="financement_bde_checkbox">
                                        <span><i class="fas fa-hand-holding-usd"></i> Demande de financement BDE</span>
                                    </label>
                                </div>
                                <div class="form-group" id="montantGroup" class="montant-group montant-hidden" style="display: flex; align-items: center; min-width: 220px;">
                                    <label style="margin: 0 8px 0 0;"><i class="fas fa-euro-sign"></i> Montant demandé (€)</label>
                                    <input type="number" name="montant" class="form-control" placeholder="Ex: 100" min="0" value="0" style="max-width:160px;">
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
        
        // Financement BDE toggle — toggle class au lieu de display pour éviter les sauts
        const financementCheckbox = document.getElementById('financement_bde_checkbox');
        const montantGroup = document.getElementById('montantGroup');
        if (financementCheckbox && montantGroup) {
            // initial state
            montantGroup.classList.toggle('montant-visible', financementCheckbox.checked);

            financementCheckbox.addEventListener('change', function() {
                montantGroup.classList.toggle('montant-visible', this.checked);
            });
        }
    </script>
</body>
</html>