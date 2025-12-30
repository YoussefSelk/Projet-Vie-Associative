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
                <div class="header-left">
                    <a href="?page=admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <div class="header-center">
                    <h1><i class="fas fa-building"></i> Gestion des clubs</h1>
                    <p class="subtitle">Modifier et gérer les clubs de l'EILCO</p>
                </div>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-search"></i> Rechercher un club</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="search-form">
                        <?= Security::csrfField() ?>
                        <div class="form-group">
                            <label for="club">Sélectionner un club :</label>
                            <input list="clubs-list" id="club" name="club" class="form-control"
                                placeholder="Rechercher un club..." required 
                                value="<?= htmlspecialchars($_POST['club'] ?? '') ?>">
                            <datalist id="clubs-list">
                                <?php foreach ($clubs as $rc): ?>
                                    <option value="<?= htmlspecialchars($rc['nom_club']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                    </form>
                </div>
            </div>

            <?php if ($req_club): ?>
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> Modifier le club: <?= htmlspecialchars($req_club['nom_club']) ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-modern">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="club_id" value="<?= htmlspecialchars($req_club['club_id']) ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Nom du club</label>
                                <input type="text" name="nom_club" class="form-control" value="<?= htmlspecialchars($req_club['nom_club']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-tag"></i> Type de club</label>
                                <input type="text" name="type_club" class="form-control" value="<?= htmlspecialchars($req_club['type_club']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($req_club['description']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Campus</label>
                            <select name="campus" class="form-control" required>
                                <option value="Calais" <?= ($req_club['campus'] ?? '') == "Calais" ? 'selected' : '' ?>>Calais</option>
                                <option value="Longuenesse" <?= ($req_club['campus'] ?? '') == "Longuenesse" ? 'selected' : '' ?>>Longuenesse</option>
                                <option value="Dunkerque" <?= ($req_club['campus'] ?? '') == "Dunkerque" ? 'selected' : '' ?>>Dunkerque</option>
                                <option value="Boulogne" <?= ($req_club['campus'] ?? '') == "Boulogne" ? 'selected' : '' ?>>Boulogne</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_club" class="btn btn-success"><i class="fas fa-save"></i> Mettre à jour</button>
                            <a href="?page=club-view&id=<?= $req_club['club_id'] ?>" class="btn btn-outline"><i class="fas fa-eye"></i> Voir le club</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Clubs Table -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Tous les clubs (<?= count($clubs) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($clubs)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-building"></i>
                            <p>Aucun club trouvé</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Campus</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clubs as $c): ?>
                                    <tr>
                                        <td data-label="Nom"><strong><?= htmlspecialchars($c['nom_club']) ?></strong></td>
                                        <td data-label="Type"><span class="badge badge-info"><?= htmlspecialchars($c['type_club'] ?? 'N/A') ?></span></td>
                                        <td data-label="Campus"><span class="campus-badge <?= strtolower($c['campus'] ?? 'calais') ?>"><?= htmlspecialchars($c['campus'] ?? 'N/A') ?></span></td>
                                        <td data-label="Actions" class="actions">
                                            <a href="?page=club-view&id=<?= $c['club_id'] ?>" class="btn btn-sm btn-primary" title="Voir"><i class="fas fa-eye"></i></a>
                                            <form method="POST" style="display: inline;">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="club" value="<?= htmlspecialchars($c['nom_club']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline" title="Modifier"><i class="fas fa-edit"></i></button>
                                            </form>
                                            <a href="?page=export-members&club_id=<?= $c['club_id'] ?>" class="btn btn-sm btn-success" title="Exporter membres"><i class="fas fa-file-csv"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
