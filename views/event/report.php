<?php
/**
 * Dépôt de rapport d'événement
 * * Permet aux organisateurs de déposer un bilan avec PDF et photos souvenir (max 2 Mo/photo).
 * * @package Views/Event
 */
$pageTitle = 'Déposer un rapport - EILCO';
$pageCss = ['shared', 'buttons', 'forms', 'events'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body class="event-report-page">
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
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(strip_tags((string)$error_msg)) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(strip_tags((string)$success_msg)) ?></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Rapport déposé',
                                    text: <?= json_encode(strip_tags((string)$success_msg), JSON_UNESCAPED_UNICODE) ?>,
                                    confirmButtonText: 'OK'
                                });
                            });
                        </script>
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
                                    <i class="fas fa-image"></i>
                                    <p>Ajouter une photo (1 max)</p>
                                    <small>JPG, PNG, WEBP • <strong>Max 2 Mo</strong></small>
                                    <input type="file" name="event_photos[]" id="eventPhotos" accept="image/jpeg,image/png,image/webp" style="display: none;">
                                </div>
                                <div id="sizeErrorMessage" class="size-error">
                                    <i class="fas fa-exclamation-triangle"></i> Certaines images sont trop lourdes (> 2 Mo) et ont été retirées.
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
            const MAX_FILES = 1;
            const MAX_SIZE = 2 * 1024 * 1024; // 2 Mo

            fileInput.addEventListener('change', function() {
                errorMsg.style.display = 'none';
                const selectedFiles = Array.from(this.files);

                selectedFiles.forEach(file => {
                    // 1. Vérifier si on ne dépasse pas 5 photos au total
                    if (allFiles.length >= MAX_FILES) {
                        return; // On ignore les fichiers en trop
                    }

                    // 2. Vérifier la taille (2 Mo)
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
                            
                            wrapper.innerHTML = `
                                <img src="${e.target.result}" alt="Aperçu">
                                <button type="button" class="preview-remove" onclick="removeSpecificImage(${index})">&times;</button>
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
                            <button type="button" class="preview-remove" onclick="removeSpecificImage(${index})">&times;</button>
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
                    console.log("Limite de 1 photo atteinte.");
                }
            }
        });
        </script>
</body>
</html>