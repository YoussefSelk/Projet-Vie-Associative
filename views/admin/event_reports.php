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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <link rel="stylesheet" href="css/admin.css">
    <style>
/* Event Reports Page Styles */
.event-reports-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-header h1 {
    font-size: 1.8rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
}

.page-header h1 i { color: #0066cc; }

.header-actions {
    display: flex;
    gap: 10px;
}

/* Stats Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border-left: 4px solid #0066cc;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card.success { border-left-color: #28a745; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.info { border-left-color: #17a2b8; }

.stat-card .icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    background: rgba(0, 102, 204, 0.1);
    color: #0066cc;
}

.stat-card.success .icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.stat-card.warning .icon { background: rgba(255, 193, 7, 0.15); color: #e6a700; }
.stat-card.info .icon { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }

.stat-card .content .value {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-card .content .label {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 4px;
}

/* Tabs */
.tabs-container {
    margin-bottom: 20px;
}

.tabs {
    display: flex;
    gap: 6px;
    background: #f1f5f9;
    padding: 6px;
    border-radius: 12px;
    width: fit-content;
}

.tab-btn {
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #64748b;
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    color: #0066cc;
    background: rgba(255,255,255,0.7);
}

.tab-btn.active {
    background: white;
    color: #0066cc;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.tab-btn .badge {
    background: #e2e8f0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
}

.tab-btn.active .badge {
    background: #0066cc;
    color: white;
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Reports Grid */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

/* Report Card */
.report-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.report-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    border-color: #0066cc;
}

.report-card-header {
    padding: 18px 20px;
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    border-bottom: 1px solid #f1f5f9;
}

.report-card-header h3 {
    margin: 0 0 8px 0;
    font-size: 1.05rem;
    color: #1e293b;
    line-height: 1.3;
}

.report-card-header .club-name {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #0066cc;
    font-weight: 500;
}

.report-card-body {
    padding: 18px 20px;
}

.report-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.report-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #64748b;
}

.report-meta-item i {
    color: #94a3b8;
    font-size: 0.9rem;
}

.report-file {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 15px;
}

.report-file .file-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #dc3545, #c82333);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.report-file .file-info {
    flex: 1;
}

.report-file .file-name {
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
    word-break: break-all;
}

.report-file .file-type {
    font-size: 0.75rem;
    color: #64748b;
}

.report-file .file-type .text-danger {
    color: #dc3545;
    font-weight: 500;
}

/* Missing file styles */
.report-file.file-missing {
    background: #fef2f2;
    border: 1px dashed #fca5a5;
}

.report-file .file-icon.error {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.report-card-actions {
    display: flex;
    gap: 10px;
}

.report-card-actions .btn {
    flex: 1;
    justify-content: center;
}

.report-card-actions .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Missing Report Card */
.missing-report-card {
    background: #fffbeb;
    border: 1px solid #fcd34d;
}

.missing-report-card:hover {
    border-color: #f59e0b;
}

.missing-report-card .report-card-header {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.missing-report-card .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #fef3c7;
    color: #b45309;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Buttons */
.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-primary { background: #0066cc; color: white; }
.btn-primary:hover { background: #0052a3; }

.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; color: #334155; }

.btn-success { background: #28a745; color: white; }
.btn-success:hover { background: #218838; }

.btn-outline { 
    background: transparent; 
    color: #0066cc; 
    border: 1px solid #0066cc; 
}
.btn-outline:hover { 
    background: #0066cc; 
    color: white; 
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 15px;
    opacity: 0.3;
    color: #94a3b8;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #475569;
    font-size: 1.2rem;
}

.empty-state p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

/* ==========================================
   MODAL STYLES
   ========================================== */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    display: flex;
    opacity: 1;
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 1100px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    transform: scale(0.9);
    transition: transform 0.3s ease;
    overflow: hidden;
}

.modal-overlay.active .modal-container {
    transform: scale(1);
}

.modal-header {
    padding: 20px 25px;
    background: linear-gradient(135deg, #0066cc, #004080);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.modal-header-content {
    flex: 1;
}

.modal-header h2 {
    margin: 0 0 5px 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-header .subtitle {
    opacity: 0.9;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-header .subtitle i {
    opacity: 0.8;
}

.modal-close {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

.modal-info-bar {
    padding: 15px 25px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.modal-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.modal-info-item i {
    color: #0066cc;
}

.modal-info-item .label {
    color: #64748b;
}

.modal-info-item .value {
    color: #1e293b;
    font-weight: 500;
}

.modal-body {
    flex: 1;
    overflow: hidden;
    background: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 500px;
}

.pdf-viewer {
    width: 100%;
    height: 100%;
    min-height: 500px;
    border: none;
}

.pdf-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    gap: 15px;
}

.pdf-loading .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255,255,255,0.2);
    border-top-color: #0066cc;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.pdf-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
    padding: 40px;
    gap: 15px;
}

.pdf-error i {
    font-size: 3rem;
    opacity: 0.5;
}

.pdf-error h4 {
    margin: 0;
    font-size: 1.1rem;
}

.pdf-error p {
    margin: 0;
    opacity: 0.7;
    font-size: 0.9rem;
}

.modal-footer {
    padding: 15px 25px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.modal-footer-info {
    color: #64748b;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-footer-actions {
    display: flex;
    gap: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reports-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        width: 100%;
        justify-content: center;
    }
    
    .modal-container {
        max-height: 95vh;
    }
    
    .modal-header {
        padding: 15px 20px;
    }
    
    .modal-info-bar {
        padding: 12px 20px;
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer {
        flex-direction: column;
        text-align: center;
    }
    
    .modal-footer-actions {
        width: 100%;
        justify-content: center;
    }
}
    </style>
</head>
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
