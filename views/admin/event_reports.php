<?php
/**
 * Consultation des Rapports d'Événements - Administration
 * 
 * Interface de visualisation des rapports déposés :
 * - Liste des événements avec rapports soumis
 * - Prévisualisation des PDF dans un modal
 * - Téléchargement direct des rapports
 * - Statistiques de complétion
 * 
 * Variables attendues :
 * - $events_with_reports : Liste des événements avec rapport
 * - $events_without_reports : Liste des événements sans rapport
 * - $stats : Statistiques de complétion
 * 
 * Permissions : Tuteur (niveau 2+) et Admin requis
 * 
 * @package Views/Admin
 */

// Formatter la date en français
function formatDateFr($date) {
    $months = [
        '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril',
        '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août',
        '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'
    ];
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $months[$d->format('m')] . ' ' . $d->format('Y');
}

/**
 * Génère l'URL encodée pour un fichier rapport
 * @param string $rapportPath Chemin du rapport depuis la BDD
 * @return string URL encodée correctement
 */
function getReportUrl($rapportPath) {
    $filename = basename($rapportPath);
    return 'uploads/rapports/' . rawurlencode($filename);
}

/**
 * Vérifie si le fichier rapport existe
 * @param string $rapportPath Chemin du rapport
 * @return bool True si le fichier existe
 */
function reportFileExists($rapportPath) {
    $filename = basename($rapportPath);
    $fullPath = 'uploads/rapports/' . $filename;
    return file_exists($fullPath);
}

$pageCss = ['shared', 'buttons', 'tables', 'admin', 'events'];
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
        <div class="event-reports-page">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-file-pdf"></i> Rapports d'Événements</h1>
                <div class="header-actions">
                    <?php if (($_SESSION['permission'] ?? 0) >= 3): ?>
                    <a href="?page=admin-reports" class="btn btn-secondary">
                        <i class="fas fa-chart-line"></i> Statistiques
                    </a>
                    <a href="?page=admin" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Tableau de bord
                    </a>
                    <?php else: ?>
                    <a href="?page=home" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="content">
                        <div class="value"><?= $stats['total_with_reports'] ?? 0 ?></div>
                        <div class="label">Rapports déposés</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="content">
                        <div class="value"><?= $stats['total_without_reports'] ?? 0 ?></div>
                        <div class="label">En attente</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="content">
                        <div class="value"><?= $stats['completion_rate'] ?? 0 ?>%</div>
                        <div class="label">Taux de complétion</div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="reports">
                        <i class="fas fa-file-alt"></i> 
                        Rapports déposés
                        <span class="badge"><?= count($events_with_reports ?? []) ?></span>
                    </button>
                    <button class="tab-btn" data-tab="missing">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Rapports manquants
                        <span class="badge"><?= count($events_without_reports ?? []) ?></span>
                    </button>
                </div>
            </div>

            <!-- Tab: Reports Deposited -->
            <div class="tab-content active" id="tab-reports">
                <?php if (empty($events_with_reports)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-pdf"></i>
                        <h3>Aucun rapport déposé</h3>
                        <p>Les organisateurs n'ont pas encore soumis de rapports d'événements.</p>
                    </div>
                <?php else: ?>
                    <div class="reports-grid">
                        <?php foreach ($events_with_reports as $event): ?>
                            <div class="report-card">
                                <div class="report-card-header">
                                    <h3><?= htmlspecialchars($event['titre'] ?? 'Événement sans titre') ?></h3>
                                    <div class="club-name">
                                        <i class="fas fa-users"></i>
                                        <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                    </div>
                                </div>
                                <div class="report-card-body">
                                    <div class="report-meta">
                                        <div class="report-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?= formatDateFr($event['date_ev'] ?? date('Y-m-d')) ?>
                                        </div>
                                        <?php if (!empty($event['lieu'])): ?>
                                        <div class="report-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($event['lieu']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php 
                                    $reportUrl = getReportUrl($event['rapport_event']);
                                    $fileExists = reportFileExists($event['rapport_event']);
                                    ?>
                                    <div class="report-file <?= !$fileExists ? 'file-missing' : '' ?>">
                                        <div class="file-icon <?= !$fileExists ? 'error' : '' ?>">
                                            <i class="fas <?= $fileExists ? 'fa-file-pdf' : 'fa-exclamation-triangle' ?>"></i>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-name"><?= htmlspecialchars(basename($event['rapport_event'])) ?></div>
                                            <div class="file-type">
                                                <?php if ($fileExists): ?>
                                                    Document PDF
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Fichier introuvable</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="report-card-actions">
                                        <?php if ($fileExists): ?>
                                        <button class="btn btn-primary view-report-btn" 
                                                data-report-url="<?= htmlspecialchars($reportUrl) ?>"
                                                data-event-title="<?= htmlspecialchars($event['titre'] ?? '') ?>"
                                                data-club-name="<?= htmlspecialchars($event['nom_club'] ?? '') ?>"
                                                data-event-date="<?= formatDateFr($event['date_ev'] ?? date('Y-m-d')) ?>"
                                                data-event-location="<?= htmlspecialchars($event['lieu'] ?? 'Non précisé') ?>">
                                            <i class="fas fa-eye"></i> Visualiser
                                        </button>
                                        <a href="<?= htmlspecialchars($reportUrl) ?>" 
                                           class="btn btn-outline" download>
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-secondary" disabled title="Le fichier n'existe plus sur le serveur">
                                            <i class="fas fa-eye-slash"></i> Indisponible
                                        </button>
                                        <button class="btn btn-outline" disabled>
                                            <i class="fas fa-times"></i> Fichier manquant
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Missing Reports -->
            <div class="tab-content" id="tab-missing">
                <?php if (empty($events_without_reports)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Tous les rapports sont déposés</h3>
                        <p>Félicitations ! Tous les événements passés ont un rapport.</p>
                    </div>
                <?php else: ?>
                    <div class="reports-grid">
                        <?php foreach ($events_without_reports as $event): ?>
                            <div class="report-card missing-report-card">
                                <div class="report-card-header">
                                    <h3><?= htmlspecialchars($event['titre'] ?? 'Événement sans titre') ?></h3>
                                    <div class="club-name">
                                        <i class="fas fa-users"></i>
                                        <?= htmlspecialchars($event['nom_club'] ?? 'Club inconnu') ?>
                                    </div>
                                </div>
                                <div class="report-card-body">
                                    <div class="report-meta">
                                        <div class="report-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?= formatDateFr($event['date_ev'] ?? date('Y-m-d')) ?>
                                        </div>
                                        <?php if (!empty($event['lieu'])): ?>
                                        <div class="report-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($event['lieu']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="status-badge">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Rapport non déposé
                                    </div>
                                    
                                    <p style="color: #64748b; font-size: 0.9rem; margin: 15px 0 0 0;">
                                        L'organisateur n'a pas encore déposé le rapport pour cet événement.
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- PDF Viewer Modal -->
    <div class="modal-overlay" id="pdfModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-header-content">
                    <h2 id="modal-title">Rapport d'événement</h2>
                    <div class="subtitle">
                        <i class="fas fa-users"></i>
                        <span id="modal-club">Club</span>
                    </div>
                </div>
                <button class="modal-close" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-info-bar">
                <div class="modal-info-item">
                    <i class="fas fa-calendar"></i>
                    <span class="label">Date :</span>
                    <span class="value" id="modal-date">-</span>
                </div>
                <div class="modal-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="label">Lieu :</span>
                    <span class="value" id="modal-location">-</span>
                </div>
            </div>
            
            <div class="modal-body">
                <div class="pdf-loading" id="pdfLoading">
                    <div class="spinner"></div>
                    <span>Chargement du document...</span>
                </div>
                <iframe class="pdf-viewer" id="pdfViewer" style="display: none;"></iframe>
                <div class="pdf-error" id="pdfError" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Impossible de charger le document</h4>
                    <p>Le fichier PDF n'a pas pu être affiché. Essayez de le télécharger.</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="modal-footer-info">
                    <i class="fas fa-file-pdf"></i>
                    <span id="modal-filename">document.pdf</span>
                </div>
                <div class="modal-footer-actions">
                    <a href="#" class="btn btn-success" id="downloadBtn" download>
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                    <a href="#" class="btn btn-outline" id="openNewTabBtn" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Nouvel onglet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Update active states
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById('tab-' + tabId).classList.add('active');
            });
        });
        
        // Modal handling
        const modal = document.getElementById('pdfModal');
        const closeModal = document.getElementById('closeModal');
        const pdfViewer = document.getElementById('pdfViewer');
        const pdfLoading = document.getElementById('pdfLoading');
        const pdfError = document.getElementById('pdfError');
        const downloadBtn = document.getElementById('downloadBtn');
        const openNewTabBtn = document.getElementById('openNewTabBtn');
        
        // Open modal on view button click
        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reportUrl = this.dataset.reportUrl;
                const eventTitle = this.dataset.eventTitle;
                const clubName = this.dataset.clubName;
                const eventDate = this.dataset.eventDate;
                const eventLocation = this.dataset.eventLocation;
                
                // Update modal info
                document.getElementById('modal-title').textContent = eventTitle || 'Rapport d\'événement';
                document.getElementById('modal-club').textContent = clubName || 'Club';
                document.getElementById('modal-date').textContent = eventDate || '-';
                document.getElementById('modal-location').textContent = eventLocation || '-';
                document.getElementById('modal-filename').textContent = reportUrl.split('/').pop();
                
                // Update download/open links
                downloadBtn.href = reportUrl;
                openNewTabBtn.href = reportUrl;
                
                // Show loading, hide others
                pdfLoading.style.display = 'flex';
                pdfViewer.style.display = 'none';
                pdfError.style.display = 'none';
                
                // Show modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Load PDF
                pdfViewer.onload = function() {
                    pdfLoading.style.display = 'none';
                    pdfViewer.style.display = 'block';
                };
                
                pdfViewer.onerror = function() {
                    pdfLoading.style.display = 'none';
                    pdfError.style.display = 'flex';
                };
                
                // Set source (add delay to ensure animation completes)
                setTimeout(() => {
                    pdfViewer.src = reportUrl;
                }, 100);
                
                // Fallback timeout for error
                setTimeout(() => {
                    if (pdfLoading.style.display !== 'none') {
                        pdfLoading.style.display = 'none';
                        pdfViewer.style.display = 'block';
                    }
                }, 3000);
            });
        });
        
        // Close modal
        function closeModalFn() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => {
                pdfViewer.src = '';
            }, 300);
        }
        
        closeModal.addEventListener('click', closeModalFn);
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalFn();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModalFn();
            }
        });
    });
    </script>
</body>
</html>
