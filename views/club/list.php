<?php
/**
 * Gestion des clubs - Interface administrateur
 * 
 * Permet la recherche et modification des clubs :
 * - Recherche par nom avec autocompletion
 * - Affichage des details du club selectionne
 * - Modification des informations
 * - Gestion des membres
 * - Suppression (admin uniquement)
 * 
 * Variables attendues :
 * - $clubs : Liste de tous les clubs pour l'autocompletion
 * - $selected_club : Club selectionne (si recherche effectuee)
 * - $error_msg / $success_msg : Messages de feedback
 * 
 * @package Views/Club
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
    <style>
        /* Club Details Grid */
        .club-details-grid {
            gap: 15px;
            background: #ffffff;
            padding: 10px;
        }
        .club-details-grid .detail-item {
            border: 1px solid #f1f5f9; /* Bordure légère au lieu d'un fond gris lourd */
            background: #fff;
        }
        .club-details-grid .detail-item.full-width {
            grid-column: 1 / -1;
        }
        .club-details-grid .detail-item label {
            font-weight: 600;
            color: var(--text-secondary, #6c757d);
            font-size: 0.85rem;
            display: block;
            margin-bottom: 8px;
        }
        .club-details-grid .detail-item p {
            margin: 0;
            font-size: 1rem;
            color: var(--text-primary, #333);
        }

        /* Filters Container */
        .clubs-filters {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filters-row {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr) auto;
            gap: 16px;
            align-items: end;
        }
        @media (max-width: 1200px) {
            .filters-row {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 768px) {
            .filters-row {
                grid-template-columns: 1fr;
            }
        }
        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-item label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-item label i {
            margin-right: 5px;
            color: #4361ee;
        }
        .filter-item .filter-input,
        .filter-item .filter-select {
            padding: 10px 14px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            transition: all 0.2s ease;
            width: 100%;
        }
        .filter-item .filter-input:focus,
        .filter-item .filter-select:focus {
            border-color: #4361ee;
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        .filter-item .filter-input::placeholder {
            color: #adb5bd;
        }
        .filter-reset-btn {
            padding: 10px 16px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            height: 42px;
        }
        .filter-reset-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }
        .filters-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #dee2e6;
        }
        .filters-summary .result-count {
            font-size: 0.9rem;
            color: #495057;
        }
        .filters-summary .result-count strong {
            color: #4361ee;
            font-weight: 700;
        }

        /* Table Improvements */
        .clubs-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .clubs-table thead th {
            background: #f8f9fa;
            padding: 14px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        .clubs-table tbody tr {
            transition: background 0.15s ease;
        }
        .clubs-table tbody tr:hover {
            background: #f8f9fa;
        }
        .clubs-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .clubs-table .club-name {
            font-weight: 600;
            color: #212529;
            font-size: 0.95rem;
        }
        .clubs-table .type-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #6366f1;
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .clubs-table .campus-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-info {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .clubs-table .campus-tag.calais { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .clubs-table .campus-tag.longuenesse { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .clubs-table .campus-tag.dunkerque { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .clubs-table .campus-tag.boulogne { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .clubs-table .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
        }
        .clubs-table .action-btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            width: 38px; /* Largeur fixe pour l'alignement */
            height: 38px;
        }

        .clubs-table .action-btn.view {
            background: #4361ee !important; /* Bleu vif */
            color: white !important;
        }
        .clubs-table .action-btn.view:hover {
            background: #3046bd;
        }
        .action-btn.view {
    background: #4361ee !important; /* Bleu vif */
    color: white !important;
}
.action-btn.view:hover {
    background: #3046bd !important;
}

/* Bouton ÉDITER (Orange/Ambre) */
.action-btn.edit {
    background: #f59e0b !important; /* Orange/Ambre */
    color: white !important;
}
.action-btn.edit:hover {
    background: #d97706 !important;
}

/* Bouton CSV / EXPORT (Vert) */
.action-btn.export {
    background: #10b981 !important; /* Vert émeraude */
    color: white !important;
}
.action-btn.export:hover {
    background: #059669 !important;
}

/* Style commun pour s'assurer que les icônes sont blanches */
.action-btn i {
    color: white !important;
}

        .clubs-table .action-btn.edit {
            background: #f1f5f9;
            color: #475569;
        }
        .clubs-table .action-btn.edit:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .clubs-table .action-btn.export {
            background: #10b981; /* Vert pour l'export CSV */
            color: white;
        }
        .clubs-table .action-btn.export:hover {
            background: #059669;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .no-results i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        .no-results p {
            margin: 0;
            font-size: 1rem;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .clubs-table thead {
                display: none;
            }
            .clubs-table tbody tr {
                display: block;
                margin-bottom: 16px;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 16px;
                background: white;
            }
            .clubs-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border: none;
            }
            .clubs-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6c757d;
                font-size: 0.8rem;
            }
            .clubs-table .actions-cell {
                justify-content: flex-end;
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


            <!-- <div class="card">
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
            </div> -->

            <?php if ($req_club): ?>
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Détails du club: <?= htmlspecialchars($req_club['nom_club']) ?></h3>
                </div>
                <div class="card-body">
                    <div class="club-details-grid">
                        <div class="detail-item">
                            <label><i class="fas fa-building"></i> Nom du club</label>
                            <p><?= htmlspecialchars($req_club['nom_club']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fas fa-tag"></i> Type de club</label>
                            <p><span class="badge badge-info"><?= htmlspecialchars($req_club['type_club']) ?></span></p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fas fa-map-marker-alt"></i> Campus</label>
                            <p><span class="campus-badge <?= strtolower($req_club['campus'] ?? 'calais') ?>"><?= htmlspecialchars($req_club['campus'] ?? 'N/A') ?></span></p>
                        </div>
                        <div class="detail-item full-width">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <p><?= nl2br(htmlspecialchars($req_club['description'])) ?></p>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top: 20px;">
                        <a href="?page=club-view&id=<?= $req_club['club_id'] ?>" class="btn btn-primary"><i class="fa fa-eye"></i> Voir la page du club</a>
                        <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                            <a href="?page=club-edit&id=<?= $req_club['club_id'] ?>" class="btn btn-outline"><i class="fas fa-edit"></i> Modifier</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Clubs Table -->
            <div class="card mt-20">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Tous les clubs (<span id="clubCount"><?= count($clubs) ?></span>)</h3>
                </div>
                <div class="card-body">
                    <div class="filters-row" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; border: none; background: transparent; padding: 0;">
                        
                        <div class="filter-item" style="flex: 2; min-width: 200px;">
                            <label style="margin-bottom: 8px; display: block;"><i class="fas fa-search"></i> Recherche</label>
                            <input type="text" id="clubTableFilter" class="filter-input" 
                                placeholder="Nom du club..." 
                                style="border: 1px solid #e2e8f0; background: #f8fafc; width: 100%; padding: 10px;"
                                autocomplete="off">
                        </div>
                        
                        <div class="filter-item" style="flex: 1; min-width: 150px;">
                            <label style="margin-bottom: 8px; display: block;"><i class="fas fa-map-marker-alt"></i> Campus</label>
                            <select id="campusFilter" class="filter-select" style="border: 1px solid #e2e8f0; background: #f8fafc; width: 100%; padding: 10px;">
                                <option value="">Tous</option>
                                <option value="calais">Calais</option>
                                <option value="longuenesse">Longuenesse</option>
                                <option value="dunkerque">Dunkerque</option>
                                <option value="boulogne">Boulogne</option>
                            </select>
                        </div>
                        
                        <div class="filter-item" style="flex: 1; min-width: 150px;">
                            <label style="margin-bottom: 8px; display: block;"><i class="fas fa-tag"></i> Type</label>
                            <select id="typeFilter" class="filter-select" style="border: 1px solid #e2e8f0; background: #f8fafc; width: 100%; padding: 10px;">
                                <option value="">Tous les types</option>
                                <?php 
                                $types = array_unique(array_filter(array_column($clubs, 'type_club')));
                                sort($types);
                                foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars(strtolower($type)) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-item" style="flex: 1; min-width: 150px;">
                            <label style="margin-bottom: 8px; display: block;"><i class="fas fa-sort"></i> Trier par</label>
                            <select id="sortFilter" class="filter-select" style="border: 1px solid #e2e8f0; background: #f8fafc; width: 100%; padding: 10px;">
                                <option value="name-asc">Nom (A → Z)</option>
                                <option value="name-desc">Nom (Z → A)</option>
                                <option value="type-asc">Type (A → Z)</option>
                                <option value="campus-asc">Campus (A → Z)</option>
                            </select>
                        </div>
                        
                        <div class="filter-item" style="flex: 0 0 auto;">
                            <button type="button" id="resetFilters" class="filter-reset-btn" 
                                    style="border: none; background: #f1f5f9; color: #64748b; font-weight: 600; height: 42px; padding: 0 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-undo"></i> Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
                    
                    <?php if (empty($clubs)): ?>
                        <div class="no-results">
                            <i class="fas fa-building"></i>
                            <p>Aucun club trouvé</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="clubs-table">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Nom</th>
                                        <th style="width: 25%;">Type</th>
                                        <th style="width: 15%;">Campus</th>
                                        <th style="width: 25%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="clubsTableBody">
                                    <?php foreach ($clubs as $c): 
                                        $searchData = strtolower($c['nom_club'] . ' ' . ($c['type_club'] ?? '') . ' ' . ($c['campus'] ?? ''));
                                    ?>
                                    <tr data-search="<?= htmlspecialchars($searchData) ?>" 
                                        data-name="<?= htmlspecialchars(strtolower($c['nom_club'])) ?>"
                                        data-type="<?= htmlspecialchars(strtolower($c['type_club'] ?? '')) ?>"
                                        data-campus="<?= htmlspecialchars(strtolower($c['campus'] ?? '')) ?>">
                                        <td data-label="Nom">
                                            <span class="club-name"><?= htmlspecialchars($c['nom_club']) ?></span>
                                        </td>
                                        <td data-label="Type">
                                            <span class="type-badge" title="<?= htmlspecialchars($c['type_club'] ?? 'N/A') ?>">
                                                <?= htmlspecialchars($c['type_club'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td data-label="Campus">
                                            <span class="campus-tag <?= strtolower($c['campus'] ?? 'calais') ?>">
                                                <?= htmlspecialchars($c['campus'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td data-label="Actions" class="actions-cell">
                                            <a href="?page=club-view&id=<?= $c['club_id'] ?>" class="action-btn view" title="Voir">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            
                                            <?php if (($_SESSION['permission'] ?? 0) >= 4): ?>
                                            <form method="POST" style="margin: 0; display: inline-flex;">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="club" value="<?= htmlspecialchars($c['nom_club']) ?>">
                                                <button type="submit" class="action-btn edit" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <a href="?page=export-members&club_id=<?= $c['club_id'] ?>" class="action-btn export" title="Exporter membres">
                                                <i class="fas fa-file-csv"></i>
                                            </a>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('clubsTableBody');
        const searchInput = document.getElementById('clubTableFilter');
        const campusFilter = document.getElementById('campusFilter');
        const typeFilter = document.getElementById('typeFilter');
        const sortFilter = document.getElementById('sortFilter');
        const resetBtn = document.getElementById('resetFilters');
        const clubCount = document.getElementById('clubCount');
        
        if (!tbody) return;
        
        // Get all rows
        const allRows = Array.from(tbody.querySelectorAll('tr'));
        
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const campusValue = campusFilter.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();
            
            let visibleCount = 0;
            
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const type = row.dataset.type || '';
                const campus = row.dataset.campus || '';
                const searchData = row.dataset.search || '';
                
                // Check all filters
                const matchesSearch = !searchTerm || searchData.includes(searchTerm);
                const matchesCampus = !campusValue || campus === campusValue;
                const matchesType = !typeValue || type === typeValue;
                
                if (matchesSearch && matchesCampus && matchesType) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            clubCount.textContent = visibleCount;
        }
        
        function applySort() {
            const sortValue = sortFilter.value;
            const [field, direction] = sortValue.split('-');
            
            const sortedRows = [...allRows].sort((a, b) => {
                let aVal, bVal;
                
                switch(field) {
                    case 'name':
                        aVal = a.dataset.name || '';
                        bVal = b.dataset.name || '';
                        break;
                    case 'type':
                        aVal = a.dataset.type || '';
                        bVal = b.dataset.type || '';
                        break;
                    case 'campus':
                        aVal = a.dataset.campus || '';
                        bVal = b.dataset.campus || '';
                        break;
                    default:
                        aVal = a.dataset.name || '';
                        bVal = b.dataset.name || '';
                }
                
                const comparison = aVal.localeCompare(bVal, 'fr', {sensitivity: 'base'});
                return direction === 'desc' ? -comparison : comparison;
            });
            
            // Re-append sorted rows
            sortedRows.forEach(row => tbody.appendChild(row));
            
            // Re-apply filters after sort
            applyFilters();
        }
        
        function resetAllFilters() {
            searchInput.value = '';
            campusFilter.value = '';
            typeFilter.value = '';
            sortFilter.value = 'name-asc';
            applySort();
        }
        
        // Event listeners
        searchInput.addEventListener('input', applyFilters);
        campusFilter.addEventListener('change', applyFilters);
        typeFilter.addEventListener('change', applyFilters);
        sortFilter.addEventListener('change', applySort);
        resetBtn.addEventListener('click', resetAllFilters);
        
        // Initial sort
        applySort();
    });
    </script>
</body>
</html>
