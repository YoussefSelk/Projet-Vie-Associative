<?php
/**
 * Formulaire de creation d'un evenement
 * 
 * Permet aux membres de clubs de proposer des evenements :
 * - Selection du club organisateur
 * - Details (titre, description, lieu)
 * - Date et horaires
 * - Nombre de places (optionnel)
 * 
 * L'evenement sera soumis a validation BDE puis tuteur.
 * 
 * Variables attendues :
 * - $clubs : Liste des clubs dont l'utilisateur est membre
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Event
 */
?>
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
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-plus"></i> Créer un nouvel événement ou activité</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>
                    
                    <!-- BDE Guide Info Box -->
                    <div class="info-box info-box-primary" style="margin-bottom: 20px;">
                        <h4><i class="fas fa-info-circle"></i> Guide BDE - Événements & Activités</h4>
                        <div class="info-content">
                            <p><strong>Événement</strong> : Soirée, gala, tournoi sportif, concert... Nécessite une validation complète et un budget.</p>
                            <p><strong>Activité</strong> : Réunion de club, atelier, entraînement... Plus simple, certains champs sont optionnels.</p>
                            <details>
                                <summary><i class="fas fa-book"></i> Règles du BDE</summary>
                                <ul style="margin-top: 10px; padding-left: 20px;">
                                    <li>Les événements doivent être soumis au moins 2 semaines à l'avance</li>
                                    <li>Le budget doit être détaillé pour obtenir un financement</li>
                                    <li>La réservation de salle se fait via le secrétariat</li>
                                    <li>Les événements avec alcool nécessitent une autorisation spéciale</li>
                                    <li>Un rapport doit être rédigé après chaque événement</li>
                                </ul>
                            </details>
                        </div>
                    </div>

                    <form method="POST" class="form-modern" id="eventForm">
                        <?= Security::csrfField() ?>
                        
                        <!-- Type Selection -->
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
                                <label><i class="fas fa-calendar"></i> Date et heure <span class="required">*</span></label>
                                <input type="datetime-local" name="date_event" class="form-control" required>
                            </div>
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
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-map-pin"></i> Lieu précis <span class="event-required">*</span></label>
                            <input type="text" name="lieu" class="form-control" placeholder="Ex: Amphi A, Salle B203, Parvis...">
                        </div>
                        
                        <!-- Event-specific fields -->
                        <div id="eventFields" class="form-section">
                            <h4><i class="fas fa-clipboard-list"></i> Détails de l'événement</h4>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-euro-sign"></i> Budget estimé</label>
                                    <input type="number" name="budget" class="form-control" placeholder="Ex: 500" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-users"></i> Nombre de participants max</label>
                                    <input type="number" name="nb_participants" class="form-control" placeholder="Ex: 100" min="1">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Club organisateur</label>
                                <select name="club_id" class="form-control">
                                    <option value="">Aucun club</option>
                                    <?php
                                    global $db;
                                    $clubs = $db->query("SELECT club_id, nom_club FROM fiche_club WHERE validation_finale = 1 ORDER BY nom_club ASC")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($clubs as $club):
                                    ?>
                                        <option value="<?= $club['club_id'] ?>"><?= htmlspecialchars($club['nom_club']) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
                
                const isEvent = this.dataset.type === 'event';
                document.getElementById('eventFields').style.display = isEvent ? 'block' : 'none';
                
                // Toggle required for event-specific fields
                document.querySelectorAll('.event-required').forEach(el => {
                    el.style.display = isEvent ? 'inline' : 'none';
                });
            });
        });
    </script>
    
    <style>
        .type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .type-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        
        .type-option:hover {
            border-color: #0066cc;
            background: #f0f7ff;
        }
        
        .type-option.selected {
            border-color: #0066cc;
            background: #e8f4fc;
        }
        
        .type-option input {
            display: none;
        }
        
        .type-option i {
            font-size: 2rem;
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .type-option span {
            font-weight: 600;
            color: #333;
        }
        
        .type-option small {
            color: #666;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .info-box {
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .info-box-primary {
            background: #e8f4fc;
            border-color: #0066cc;
        }
        
        .info-box h4 {
            margin: 0 0 15px 0;
            color: #0066cc;
        }
        
        .info-box p {
            margin: 5px 0;
        }
        
        .info-box details {
            margin-top: 15px;
        }
        
        .info-box summary {
            cursor: pointer;
            font-weight: 500;
            color: #0066cc;
        }
        
        .required, .event-required {
            color: #dc3545;
        }
        
        @media (max-width: 600px) {
            .type-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
