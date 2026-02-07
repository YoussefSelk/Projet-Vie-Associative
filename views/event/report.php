<?php
/**
 * Depot de rapport d'evenement
 * * Permet aux organisateurs de deposer un bilan avec PDF et photos (Max 5, 500Ko/unité).
 * * @package Views/Event
 */
$pageCss = ['shared', 'buttons', 'forms', 'events'];
?>
<!DOCTYPE html>
<html lang="fr">
<<<<<<< HEAD
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        /* Design du conteneur d'images */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .upload-zone:hover {
            border-color: #6366f1;
            background: #f1f5f9;
        }
        .upload-zone i {
            font-size: 2rem;
            color: #6366f1;
            margin-bottom: 10px;
        }
        .upload-zone p {
            margin: 0;
            color: #475569;
            font-weight: 500;
        }
        .upload-zone small {
            color: #94a3b8;
        }

        /* Grille de prévisualisation */
        #imagePreview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        .preview-wrapper {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .preview-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Alertes de taille */
        .size-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
=======
<?php include VIEWS_PATH . '/includes/head.php'; ?>
>>>>>>> origin/main
<body>
    <header class="header">
        <?php include VIEWS_PATH . "/includes/header.php"; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="page-container">
            <div class="card" style="max-width: 750px; margin: 0 auto; border: none; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: transparent; border-bottom: 1px solid #f1f5f9; padding: 20px;">
                    <h3 style="margin:0; color: #1e293b;"><i class="fas fa-file-alt"></i> Déposer un rapport d'événement</h3>
                </div>
                
                <div class="card-body" style="padding: 30px;">
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <?php if (empty($events)): ?>
                        <div class="empty-state-small" style="text-align: center; padding: 40px;">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                            <p style="font-weight: 600;">Aucun événement en attente de rapport.</p>
                            <a href="?page=home" class="btn btn-primary" style="margin-top: 15px;">Retour à l'accueil</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" id="reportForm">
                            <?= Security::csrfField() ?>
                            
                            <div class="form-group" style="margin-bottom: 25px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">
                                    <i class="fas fa-calendar-alt"></i> Événement
                                </label>
                                <select name="event_id" class="form-control" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <option value="">-- Sélectionner l'événement --</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?= $event['event_id'] ?>">
                                            <?= htmlspecialchars($event['nom_club'] ?? '') ?> - <?= htmlspecialchars($event['titre'] ?? '') ?> 
                                            (<?= date('d/m/Y', strtotime($event['date_ev'] ?? 'now')) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 25px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">
                                    <i class="fas fa-file-pdf"></i> Bilan Officiel (PDF obligatoire)
                                </label>
                                <input type="file" name="rapport_file" class="form-control" accept=".pdf" required style="width: 100%; padding: 10px;">
                                <small style="color: #94a3b8;">Format accepté : PDF uniquement (Max 2Mo)</small>
                            </div>

                            <div class="form-group" style="margin-bottom: 30px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">
                                    <i class="fas fa-camera"></i> Photos souvenirs (Optionnel)
                                </label>
                                
                                <div class="upload-zone" onclick="document.getElementById('eventPhotos').click()">
                                    <i class="fas fa-images"></i>
                                    <p>Ajouter des photos (max 5)</p>
                                    <small>JPG, PNG, WEBP • <strong>Max 500 Ko par photo</strong></small>
                                    <input type="file" name="event_photos[]" id="eventPhotos" multiple accept="image/*" style="display: none;">
                                </div>
                                <div id="sizeErrorMessage" class="size-error">
                                    <i class="fas fa-exclamation-triangle"></i> Certaines images sont trop lourdes (> 500 Ko) et ont été retirées.
                                </div>

                                <div id="imagePreview"></div>
                            </div>

                            <div class="form-actions" style="display: flex; gap: 15px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                                <button type="submit" name="submit_report" class="btn btn-success" style="flex: 1; padding: 14px; font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-paper-plane"></i> Envoyer le rapport
                                </button>
                                <a href="?page=my-events" class="btn btn-outline" style="padding: 14px 20px; border-radius: 8px;">Annuler</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('eventPhotos');
            const previewGrid = document.getElementById('imagePreview');
            const errorMsg = document.getElementById('sizeErrorMessage');
            
            // Tableau pour stocker physiquement les fichiers cumulés
            let allFiles = []; 
            const MAX_FILES = 5;
            const MAX_SIZE = 512000; // 500 Ko

            fileInput.addEventListener('change', function() {
                errorMsg.style.display = 'none';
                const selectedFiles = Array.from(this.files);

                selectedFiles.forEach(file => {
                    // 1. Vérifier si on ne dépasse pas 5 photos au total
                    if (allFiles.length >= MAX_FILES) {
                        return; // On ignore les fichiers en trop
                    }

                    // 2. Vérifier la taille (500 Ko)
                    if (file.size > MAX_SIZE) {
                        errorMsg.style.display = 'block';
                        return;
                    }

                    // 3. Ajouter le fichier au cumul s'il n'est pas déjà présent
                    allFiles.push(file);

                    // 4. Création de l'aperçu visuel
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const index = allFiles.length - 1;
                            const wrapper = document.createElement('div');
                            wrapper.className = 'preview-wrapper';
                            wrapper.setAttribute('data-index', index);
                            
                            // On ajoute un petit bouton "x" pour supprimer une image précise
                            wrapper.innerHTML = `
                                <img src="${e.target.result}" alt="Aperçu">
                                <div onclick="removeSpecificImage(${index})" style="position:absolute; top:2px; right:2px; background:rgba(220, 38, 38, 0.8); color:white; border-radius:50%; width:20px; height:20px; cursor:pointer; text-align:center; line-height:18px; font-size:14px;">×</div>
                            `;
                            previewGrid.appendChild(wrapper);
                        }
                        reader.readAsDataURL(file);
                    }
                });

                // 5. Synchroniser le input avec le tableau cumulé
                syncFiles();
            });

            // Fonction pour supprimer une image de la liste
            window.removeSpecificImage = function(index) {
                allFiles.splice(index, 1); // Retirer du tableau
                renderPreviews(); // Refaire la grille d'aperçu
                syncFiles(); // Synchroniser avec le formulaire
            };

            function renderPreviews() {
                previewGrid.innerHTML = '';
                allFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'preview-wrapper';
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" alt="Aperçu">
                            <div onclick="removeSpecificImage(${index})" style="position:absolute; top:2px; right:2px; background:rgba(220, 38, 38, 0.8); color:white; border-radius:50%; width:20px; height:20px; cursor:pointer; text-align:center; line-height:18px; font-size:14px;">×</div>
                        `;
                        previewGrid.appendChild(wrapper);
                    }
                    reader.readAsDataURL(file);
                });
            }

            function syncFiles() {
                const dataTransfer = new DataTransfer();
                allFiles.forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                
                // Alerte si on atteint le max
                if (allFiles.length >= MAX_FILES) {
                    console.log("Limite de 5 photos atteinte.");
                }
            }
        });
        </script>
</body>
</html>