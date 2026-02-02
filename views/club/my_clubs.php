<?php
/**
 * Mes Demandes de Clubs
 * 
 * Affiche la liste de tous les clubs où l'utilisateur connecté est Président ou Secrétaire :
 * - Statut de validation (En attente, Validé, Refusé)
 * - Motif de refus si applicable
 * - Actions : Modifier (si non validé), Supprimer (si refusé)
 * 
 * Variables attendues :
 * - $clubs : Liste des clubs de l'utilisateur (avec son rôle)
 * - $error_msg : Message d'erreur éventuel
 * - $success_msg : Message de succès éventuel
 * 
 * @package Views/Club
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        .club-status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .club-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .club-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .club-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .club-title {
            flex: 1;
        }

        .club-title h3 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 18px;
        }

        .club-type {
            color: #666;
            font-size: 14px;
        }

        .club-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 14px;
        }

        .meta-icon {
            color: #007bff;
        }

        .club-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #007bff;
            border-radius: 4px;
        }

        .club-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .refusal-reason {
            background-color: #ffe6e6;
            border-left: 4px solid #dc3545;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .refusal-reason h5 {
            margin: 0 0 8px 0;
            color: #721c24;
            font-size: 14px;
        }

        .refusal-reason p {
            margin: 0;
            color: #721c24;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }

        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        .btn-group-vertical {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .club-header {
                flex-direction: column;
            }

            .club-meta {
                flex-direction: column;
                gap: 10px;
            }

            .club-actions {
                flex-direction: column;
            }

            .club-actions .btn {
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
        <div class="page-container">
            <div class="page-header">
                <h1><i class="fas fa-folder-open"></i> Mes Demandes de Clubs</h1>
                <p class="subtitle">Suivi de vos demandes de création de clubs</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> Club modifié avec succès. Il est en attente de validation.
                </div>
            <?php endif; ?>

            <?php if (empty($clubs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucun club créé</h3>
                    <p>Vous n'avez pas encore créé de club. Commencez par créer votre première demande.</p>
                    <a href="?page=club-create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un nouveau club
                    </a>
                </div>
            <?php else: ?>
                <div class="clubs-list">
                    <?php foreach ($clubs as $club): ?>
                        <div class="club-card">
                            <div class="club-header">
                                <div class="club-title">
                                    <h3><?= htmlspecialchars($club['nom_club']) ?></h3>
                                    <p class="club-type"><?= htmlspecialchars($club['type_club'] ?? 'Type non défini') ?></p>
                                </div>
                                <?php
                                    $status = '';
                                    $statusClass = '';
                                    
                                    // Détermine le statut basé sur validation_finale
                                    if ($club['validation_finale'] == 1) {
                                        // Club validé avec succès
                                        $status = 'Validé';
                                        $statusClass = 'status-approved';
                                    } elseif ($club['validation_finale'] == 0 && !empty($club['motif_refus'])) {
                                        // Club refusé (validation_finale = 0 et motif_refus n'est pas vide)
                                        $status = 'Refusé';
                                        $statusClass = 'status-rejected';
                                    } else {
                                        // Club en attente de validation
                                        $status = 'En attente';
                                        $statusClass = 'status-pending';
                                    }
                                ?>
                                <span class="club-status-badge <?= $statusClass ?>">
                                    <?= $status ?>
                                </span> 
                            </div>

                            <div class="club-meta">
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt meta-icon"></i>
                                    <span><?= htmlspecialchars($club['campus'] ?? 'Campus non défini') ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar meta-icon"></i>
                                    <span>Créé le <?= date('d/m/Y', strtotime($club['date_creation'] ?? 'now')) ?? 'Date non définie' ?></span>
                                </div>
                            </div>

                            <?php if (!empty($club['description'])): ?>
                                <div class="club-description">
                                    <?= htmlspecialchars($club['description']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($club['validation_finale'] == 0 && !empty($club['motif_refus'])): ?>
                                <div class="refusal-reason">
                                    <h5><i class="fas fa-times-circle"></i> Motif du refus :</h5>
                                    <p><?= htmlspecialchars($club['motif_refus']) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="club-actions">
                                <?php if ($club['validation_finale'] != 1): ?>
                                    <!-- Club non validé: affiche Modifier et Supprimer -->
                                    <a href="?page=club-edit&id=<?= $club['club_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce club ?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <input type="hidden" name="club_id" value="<?= $club['club_id'] ?>">
                                        <button type="submit" name="delete_club" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Club validé: affiche Voir détails -->
                                    <a href="?page=club-view&id=<?= $club['club_id'] ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <a href="?page=club-create" class="btn btn-primary" style="margin-top: 30px;">
                    <i class="fas fa-plus"></i> Créer un nouveau club
                </a>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
