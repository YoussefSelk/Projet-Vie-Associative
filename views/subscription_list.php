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
            <div class="page-header">
                <h1><i class="fas fa-calendar-check"></i> Mes inscriptions</h1>
                <p class="subtitle">Gérez vos inscriptions aux événements</p>
            </div>

            <?php if (empty($subscriptions)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucune inscription</h3>
                    <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
                    <a href="?page=event-list" class="btn btn-primary"><i class="fas fa-calendar-alt"></i> Voir les événements</a>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($subscriptions as $event): ?>
                        <div class="event-card">
                            <div class="event-date-badge">
                                <span class="day"><?= date('d', strtotime($event['date_ev'] ?? 'now')) ?></span>
                                <span class="month"><?= strftime('%b', strtotime($event['date_ev'] ?? 'now')) ?></span>
                            </div>
                            <div class="event-content">
                                <h3><?= htmlspecialchars($event['titre'] ?? 'Sans titre') ?></h3>
                                <div class="event-meta">
                                    <span class="campus-badge <?= strtolower($event['campus'] ?? 'calais') ?>">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['campus'] ?? 'N/A') ?>
                                    </span>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Inscrit
                                    </span>
                                </div>
                                <div class="event-card-actions">
                                    <a href="?page=event-view&id=<?= $event['event_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="?page=unsubscribe&event_id=<?= $event['event_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment vous désinscrire ?');">
                                        <i class="fas fa-times"></i> Se désinscrire
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
