<?php
/**
 * Espace tuteur - Tableau de bord
 * 
 * Interface dediee aux tuteurs pour superviser leurs clubs :
 * - Clubs en attente de validation finale
 * - Evenements a approuver
 * - Liste des clubs dont ils sont tuteurs
 * - Rapports d'evenements a consulter
 * 
 * Un tuteur ne voit que les elements des clubs
 * qui lui sont assignes.
 * 
 * Variables attendues :
 * - $pending_clubs : Clubs a valider
 * - $pending_events : Evenements a valider
 * - $my_clubs : Clubs tutores
 * 
 * Permissions : Tuteur (niveau 3) ou superieur
 * 
 * @package Views/Validation
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        /* Tutor Dashboard Styles */
        .tutor-dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Stats Cards */
        .tutor-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card.pending {
            border-left: 4px solid #f59e0b;
        }
        
        .stat-card.approved {
            border-left: 4px solid #10b981;
        }
        
        .stat-card.clubs {
            border-left: 4px solid #3b82f6;
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card.pending .stat-icon {
            background: #fef3c7;
            color: #d97706;
        }
        
        .stat-card.approved .stat-icon {
            background: #d1fae5;
            color: #059669;
        }
        
        .stat-card.clubs .stat-icon {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .stat-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .stat-content p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Search Section */
        .search-section {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .search-row {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-input-wrapper {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }
        
        .search-input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8fafc;
        }
        
        .search-input-wrapper input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .filter-tabs {
            display: flex;
            gap: 8px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-tab:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .filter-tab.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
        }
        
        .filter-tab .count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
        
        .filter-tab.active .count {
            background: rgba(255,255,255,0.3);
        }
        
        /* Validation Cards Grid */
        .validation-cards-container {
            display: grid;
            gap: 20px;
        }
        
        .validation-card-advanced {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .validation-card-advanced:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .validation-card-advanced.club-card {
            border-left: 4px solid #8b5cf6;
        }
        
        .validation-card-advanced.event-card {
            border-left: 4px solid #06b6d4;
        }
        
        .card-main {
            padding: 24px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: start;
        }
        
        .card-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .card-title-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .card-type-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        
        .club-card .card-type-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
        }
        
        .club-card .card-type-icon.has-logo {
            background: #f8fafc;
            padding: 0;
            overflow: hidden;
        }
        
        .club-card .card-type-icon.has-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-card .card-type-icon {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: #fff;
        }
        
        /* Modal club logo */
        .modal-club-logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            border: 2px solid #e2e8f0;
        }
        
        .modal-club-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .modal-club-logo .no-logo {
            font-size: 2rem;
            color: #94a3b8;
        }
        
        .modal-header-with-logo {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .card-title-info h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #1e293b;
            font-weight: 600;
        }
        
        .card-title-info .card-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .card-meta {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 0.9rem;
        }
        
        .meta-item i {
            color: #94a3b8;
            width: 18px;
        }
        
        .card-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }
        
        .btn-view-details {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-view-details:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f0f7ff;
        }
        
        .btn-approve, .btn-reject {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-approve {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
        }
        
        .btn-approve:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        
        .btn-reject:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: #fff;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-close {
            width: 40px;
            height: 40px;
            border: none;
            background: #f1f5f9;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .detail-section {
            margin-bottom: 20px;
        }
        
        .detail-section:last-child {
            margin-bottom: 0;
        }
        
        .detail-section h4 {
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 8px 0;
        }
        
        .detail-section p, .detail-section .detail-value {
            color: #1e293b;
            font-size: 1rem;
            margin: 0;
            line-height: 1.6;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        
        .modal-footer .btn-approve,
        .modal-footer .btn-reject {
            padding: 14px 28px;
            font-size: 1rem;
        }
        
        /* Rejection reason section */
        .modal-reject-section {
            padding: 0 24px 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .reject-reason-box {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 20px;
        }
        
        .reject-reason-box h4 {
            color: #dc2626;
            margin: 0 0 8px 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .reject-hint {
            color: #7f1d1d;
            font-size: 0.85rem;
            margin: 0 0 12px 0;
        }
        
        .reject-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #fecaca;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: all 0.3s;
            background: #fff;
        }
        
        .reject-textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }
        
        .reject-textarea::placeholder {
            color: #9ca3af;
        }
        
        .btn-reject-init {
            padding: 14px 28px;
            border: 2px solid #ef4444;
            border-radius: 10px;
            background: #fff;
            color: #ef4444;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1rem;
        }
        
        .btn-reject-init:hover {
            background: #fef2f2;
        }
        
        .btn-cancel {
            padding: 14px 28px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1rem;
        }
        
        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        
        /* Empty State */
        .empty-state-advanced {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state-advanced .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: #059669;
        }
        
        .empty-state-advanced h3 {
            color: #1e293b;
            margin: 0 0 8px 0;
        }
        
        .empty-state-advanced p {
            color: #64748b;
            margin: 0;
        }
        
        /* Tutored Clubs Enhanced */
        .tutored-clubs-enhanced {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        
        .tutored-club-enhanced {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .tutored-club-enhanced:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);
            transform: translateX(4px);
        }
        
        .tutored-club-enhanced .club-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        
        .tutored-club-enhanced .club-details h4 {
            margin: 0 0 4px 0;
            color: #1e293b;
            font-size: 1rem;
        }
        
        .tutored-club-enhanced .club-details span {
            font-size: 0.85rem;
        }
        
        .tutored-club-enhanced .arrow {
            margin-left: auto;
            color: #94a3b8;
        }
        
        @media (max-width: 768px) {
            .card-main {
                grid-template-columns: 1fr;
            }
            
            .card-actions {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-tabs {
                width: 100%;
                overflow-x: auto;
            }
            
            .search-row {
                flex-direction: column;
            }
            
            .search-input-wrapper {
                width: 100%;
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
        <div class="page-container tutor-dashboard">
            <div class="page-header">
                <h1><i class="fas fa-user-graduate"></i> Espace Tuteur</h1>
                <p class="subtitle">Gérez les validations de vos clubs et événements</p>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="tutor-stats">
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_clubs ?? []) + count($pending_events ?? []) ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
                <div class="stat-card clubs">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_clubs ?? []) ?></h3>
                        <p>Clubs à valider</p>
                    </div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-content">
                        <h3><?= count($pending_events ?? []) ?></h3>
                        <p>Événements à valider</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-section">
                <div class="search-row">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher par nom, type, campus..." autocomplete="off">
                    </div>
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">
                            <i class="fas fa-layer-group"></i> Tout
                            <span class="count"><?= count($pending_clubs ?? []) + count($pending_events ?? []) ?></span>
                        </button>
                        <button class="filter-tab" data-filter="clubs">
                            <i class="fas fa-building"></i> Clubs
                            <span class="count"><?= count($pending_clubs ?? []) ?></span>
                        </button>
                        <button class="filter-tab" data-filter="events">
                            <i class="fas fa-calendar-alt"></i> Événements
                            <span class="count"><?= count($pending_events ?? []) ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pending Items -->
            <?php if (empty($pending_clubs) && empty($pending_events)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state-advanced">
                            <div class="empty-icon"><i class="fas fa-check"></i></div>
                            <h3>Tout est à jour !</h3>
                            <p>Aucune validation en attente. Revenez plus tard.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="validation-cards-container" id="validationCards">
                    <!-- Pending Clubs -->
                    <?php foreach ($pending_clubs ?? [] as $club): ?>
                        <div class="validation-card-advanced club-card" 
                             data-type="clubs" 
                             data-search="<?= strtolower(htmlspecialchars($club['nom_club'] . ' ' . $club['type_club'] . ' ' . $club['campus'])) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row">
                                        <div class="card-type-icon <?= !empty($club['logo_club']) ? 'has-logo' : '' ?>">
                                            <?php if (!empty($club['logo_club'])): ?>
                                                <img src="<?= htmlspecialchars($club['logo_club']) ?>" alt="Logo <?= htmlspecialchars($club['nom_club']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-building"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($club['nom_club']) ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($club['type_club']) ?>
                                            </span>
                                        </div>
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                        </div>
                                        <?php if (!empty($club['mail'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= htmlspecialchars($club['mail']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($club['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($club['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details" onclick="openClubModal(<?= htmlspecialchars(json_encode($club)) ?>)">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" name="validate_club_tutor" class="btn-approve">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <button type="button" class="btn-reject" onclick="openClubModalReject(<?= htmlspecialchars(json_encode($club)) ?>)">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pending Events -->
                    <?php foreach ($pending_events ?? [] as $event): ?>
                        <div class="validation-card-advanced event-card" 
                             data-type="events" 
                             data-search="<?= strtolower(htmlspecialchars(($event['titre'] ?? '') . ' ' . ($event['nom_club'] ?? '') . ' ' . ($event['campus'] ?? ''))) ?>">
                            <div class="card-main">
                                <div class="card-content">
                                    <div class="card-title-row">
                                        <div class="card-type-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="card-title-info">
                                            <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                            <span class="card-subtitle">
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($event['nom_club'] ?? 'N/A') ?>
                                            </span>
                                        </div>
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('d/m/Y à H:i', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>"><?= htmlspecialchars($event['campus'] ?? 'N/A') ?></span>
                                        </div>
                                        <?php if (!empty($event['lieu'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-location-dot"></i>
                                            <span><?= htmlspecialchars($event['lieu']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="card-description"><?= htmlspecialchars($event['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button type="button" class="btn-view-details" onclick="openEventModal(<?= htmlspecialchars(json_encode($event)) ?>)">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <form method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" name="validate_event_tutor" class="btn-approve">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <button type="button" class="btn-reject" onclick="openEventModalReject(<?= htmlspecialchars(json_encode($event)) ?>)">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="empty-state-advanced" id="noResults" style="display: none;">
                    <div class="empty-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucun résultat</h3>
                    <p>Aucun élément ne correspond à votre recherche.</p>
                </div>
            <?php endif; ?>

            <!-- Tutored Clubs Section -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Mes clubs tutorés (<?= count($tutored_clubs ?? []) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($tutored_clubs)): ?>
                        <div class="empty-state-advanced">
                            <div class="empty-icon" style="background: #dbeafe; color: #2563eb;">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3>Aucun club tutoré</h3>
                            <p>Vous ne tutorez aucun club actuellement.</p>
                        </div>
                    <?php else: ?>
                        <div class="tutored-clubs-enhanced">
                            <?php foreach ($tutored_clubs as $club): ?>
                                <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="tutored-club-enhanced">
                                    <div class="club-avatar">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="club-details">
                                        <h4><?= htmlspecialchars($club['nom_club']) ?></h4>
                                        <span class="campus-badge <?= strtolower($club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($club['campus']) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-right arrow"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Club Detail Modal -->
    <div class="modal-overlay" id="clubModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-with-logo">
                    <div class="modal-club-logo" id="modalClubLogo">
                        <i class="fas fa-building no-logo"></i>
                    </div>
                    <h2><span id="modalClubName">Détails du club</span></h2>
                </div>
                <button class="modal-close" onclick="closeModal('clubModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4>Type de club</h4>
                        <p id="modalClubType">-</p>
                    </div>
                    <div class="detail-section">
                        <h4>Campus</h4>
                        <p id="modalClubCampus">-</p>
                    </div>
                </div>
                <div class="detail-section">
                    <h4>Email de contact</h4>
                    <p id="modalClubEmail">-</p>
                </div>
                <div class="detail-section">
                    <h4>Description</h4>
                    <p id="modalClubDescription">-</p>
                </div>
            </div>
            <!-- Rejection Reason Section -->
            <div class="modal-reject-section" id="clubRejectSection" style="display: none;">
                <div class="reject-reason-box">
                    <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                    <p class="reject-hint">Expliquez la raison du rejet pour aider le créateur à améliorer sa demande.</p>
                    <textarea id="clubRejectMotif" name="motif_preview" class="reject-textarea" rows="3" placeholder="Ex: Description insuffisante, objectifs pas clairs, doublon avec un club existant..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="clubCancelReject" style="display: none;" onclick="cancelClubReject()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn-reject-init" id="clubRejectInit" onclick="showClubRejectForm()">
                    <i class="fas fa-times"></i> Rejeter
                </button>
                <form method="POST" id="modalClubRejectForm" style="display: none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="modalClubIdReject" value="">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="motif" id="clubMotifInput" value="">
                    <button type="submit" name="validate_club_tutor" class="btn-reject">
                        <i class="fas fa-times"></i> Confirmer le rejet
                    </button>
                </form>
                <form method="POST" id="modalClubApproveForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="club_id" id="modalClubIdApprove" value="">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" name="validate_club_tutor" class="btn-approve">
                        <i class="fas fa-check"></i> Approuver ce club
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-alt"></i> <span id="modalEventName">Détails de l'événement</span></h2>
                <button class="modal-close" onclick="closeModal('eventModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-section">
                    <h4>Club organisateur</h4>
                    <p id="modalEventClub">-</p>
                </div>
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4>Date et heure</h4>
                        <p id="modalEventDate">-</p>
                    </div>
                    <div class="detail-section">
                        <h4>Campus</h4>
                        <p id="modalEventCampus">-</p>
                    </div>
                </div>
                <div class="detail-section">
                    <h4>Lieu</h4>
                    <p id="modalEventLieu">-</p>
                </div>
                <div class="detail-section">
                    <h4>Description</h4>
                    <p id="modalEventDescription">-</p>
                </div>
            </div>
            <!-- Rejection Reason Section -->
            <div class="modal-reject-section" id="eventRejectSection" style="display: none;">
                <div class="reject-reason-box">
                    <h4><i class="fas fa-comment-alt"></i> Motif du rejet</h4>
                    <p class="reject-hint">Expliquez la raison du rejet pour aider l'organisateur à améliorer sa demande.</p>
                    <textarea id="eventRejectMotif" name="motif_preview" class="reject-textarea" rows="3" placeholder="Ex: Date non disponible, lieu inapproprié, informations manquantes..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="eventCancelReject" style="display: none;" onclick="cancelEventReject()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn-reject-init" id="eventRejectInit" onclick="showEventRejectForm()">
                    <i class="fas fa-times"></i> Rejeter
                </button>
                <form method="POST" id="modalEventRejectForm" style="display: none;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="event_id" id="modalEventIdReject" value="">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="motif" id="eventMotifInput" value="">
                    <button type="submit" name="validate_event_tutor" class="btn-reject">
                        <i class="fas fa-times"></i> Confirmer le rejet
                    </button>
                </form>
                <form method="POST" id="modalEventApproveForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="event_id" id="modalEventIdApprove" value="">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" name="validate_event_tutor" class="btn-approve">
                        <i class="fas fa-check"></i> Approuver cet événement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    (function() {
        'use strict';
        
        // Search and Filter functionality
        var searchInput = document.getElementById('searchInput');
        var filterTabs = document.querySelectorAll('.filter-tab');
        var cards = document.querySelectorAll('.validation-card-advanced');
        var noResults = document.getElementById('noResults');
        var cardsContainer = document.getElementById('validationCards');
        
        var currentFilter = 'all';
        
        function filterCards() {
            var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            var visibleCount = 0;
            
            cards.forEach(function(card) {
                var matchesSearch = card.dataset.search.includes(searchTerm);
                var matchesFilter = currentFilter === 'all' || card.dataset.type === currentFilter;
                
                if (matchesSearch && matchesFilter) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (noResults && cardsContainer) {
                if (visibleCount === 0 && cards.length > 0) {
                    noResults.style.display = '';
                    cardsContainer.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    cardsContainer.style.display = '';
                }
            }
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', filterCards);
        }
        
        filterTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                filterTabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterCards();
            });
        });
        
        // Modal functions - exposed globally
        window.openClubModal = function(club) {
            document.getElementById('modalClubName').textContent = club.nom_club || '-';
            document.getElementById('modalClubType').textContent = club.type_club || '-';
            document.getElementById('modalClubCampus').textContent = club.campus || '-';
            document.getElementById('modalClubEmail').textContent = club.mail || 'Non renseigné';
            document.getElementById('modalClubDescription').textContent = club.description || 'Aucune description fournie.';
            document.getElementById('modalClubIdApprove').value = club.club_id;
            document.getElementById('modalClubIdReject').value = club.club_id;
            
            // Display club logo if available
            var logoContainer = document.getElementById('modalClubLogo');
            if (club.logo_club) {
                logoContainer.innerHTML = '<img src="' + club.logo_club + '" alt="Logo du club">';
            } else {
                logoContainer.innerHTML = '<i class="fas fa-building no-logo"></i>';
            }
            
            document.getElementById('clubModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        window.openEventModal = function(event) {
            document.getElementById('modalEventName').textContent = event.titre || '-';
            document.getElementById('modalEventClub').textContent = event.nom_club || '-';
            document.getElementById('modalEventCampus').textContent = event.campus || '-';
            document.getElementById('modalEventLieu').textContent = event.lieu || 'Non renseigné';
            document.getElementById('modalEventDescription').textContent = event.description || 'Aucune description fournie.';
            
            if (event.date_ev) {
                var date = new Date(event.date_ev);
                document.getElementById('modalEventDate').textContent = date.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                document.getElementById('modalEventDate').textContent = '-';
            }
            
            document.getElementById('modalEventIdApprove').value = event.event_id;
            document.getElementById('modalEventIdReject').value = event.event_id;
            document.getElementById('eventModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        // Open club modal directly in reject mode
        window.openClubModalReject = function(club) {
            openClubModal(club);
            // Delay to ensure modal is open first
            setTimeout(function() {
                showClubRejectForm();
            }, 100);
        };
        
        // Open event modal directly in reject mode
        window.openEventModalReject = function(event) {
            openEventModal(event);
            // Delay to ensure modal is open first
            setTimeout(function() {
                showEventRejectForm();
            }, 100);
        };
        
        window.closeModal = function(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
            // Reset rejection forms when closing
            if (modalId === 'clubModal') {
                cancelClubReject();
            } else if (modalId === 'eventModal') {
                cancelEventReject();
            }
        };
        
        // Club rejection functions
        window.showClubRejectForm = function() {
            document.getElementById('clubRejectSection').style.display = 'block';
            document.getElementById('clubRejectInit').style.display = 'none';
            document.getElementById('clubCancelReject').style.display = 'flex';
            document.getElementById('modalClubRejectForm').style.display = 'inline';
            document.getElementById('modalClubApproveForm').style.display = 'none';
            document.getElementById('clubRejectMotif').focus();
        };
        
        window.cancelClubReject = function() {
            document.getElementById('clubRejectSection').style.display = 'none';
            document.getElementById('clubRejectInit').style.display = 'flex';
            document.getElementById('clubCancelReject').style.display = 'none';
            document.getElementById('modalClubRejectForm').style.display = 'none';
            document.getElementById('modalClubApproveForm').style.display = 'inline';
            document.getElementById('clubRejectMotif').value = '';
        };
        
        // Event rejection functions
        window.showEventRejectForm = function() {
            document.getElementById('eventRejectSection').style.display = 'block';
            document.getElementById('eventRejectInit').style.display = 'none';
            document.getElementById('eventCancelReject').style.display = 'flex';
            document.getElementById('modalEventRejectForm').style.display = 'inline';
            document.getElementById('modalEventApproveForm').style.display = 'none';
            document.getElementById('eventRejectMotif').focus();
        };
        
        window.cancelEventReject = function() {
            document.getElementById('eventRejectSection').style.display = 'none';
            document.getElementById('eventRejectInit').style.display = 'flex';
            document.getElementById('eventCancelReject').style.display = 'none';
            document.getElementById('modalEventRejectForm').style.display = 'none';
            document.getElementById('modalEventApproveForm').style.display = 'inline';
            document.getElementById('eventRejectMotif').value = '';
        };
        
        // Copy motif to hidden input before submit
        document.getElementById('modalClubRejectForm').addEventListener('submit', function() {
            document.getElementById('clubMotifInput').value = document.getElementById('clubRejectMotif').value;
        });
        
        document.getElementById('modalEventRejectForm').addEventListener('submit', function() {
            document.getElementById('eventMotifInput').value = document.getElementById('eventRejectMotif').value;
        });
        
        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                    // Reset rejection forms
                    cancelClubReject();
                    cancelEventReject();
                }
            });
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
                // Reset rejection forms
                cancelClubReject();
                cancelEventReject();
            }
        });
    })();
    </script>
</body>
</html>
