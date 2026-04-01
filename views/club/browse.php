<?php
/**
 * Découvrir les clubs — vue publique pour étudiants (permission 1)
 *
 * Affiche tous les clubs validés sous forme de cartes avec :
 * - Recherche en temps réel
 * - Filtres campus / type
 * - Lien vers la page de détail du club
 *
 * Variables attendues :
 * - $clubs : Liste des clubs validés (avec membres_count)
 *
 * @package Views/Club
 */
$pageTitle = 'Découvrir les clubs - EILCO';
$pageCss = ['shared', 'buttons', 'clubs'];
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
        <div class="page-container">

            <!-- En-tête de page -->
            <div class="page-header">
                <div class="header-center">
                    <h1><i class="fas fa-users"></i> Découvrir les clubs</h1>
                    <p class="subtitle">
                        <?= count($clubs) ?> club<?= count($clubs) > 1 ? 's' : '' ?> actif<?= count($clubs) > 1 ? 's' : '' ?> à l'EILCO
                    </p>
                </div>
            </div>

            <?php if (empty($clubs)): ?>
                <div class="no-results" style="padding: 4rem 2rem; text-align: center;">
                    <i class="fas fa-building" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p style="color: #666;">Aucun club disponible pour le moment.</p>
                </div>
            <?php else: ?>

            <!-- Barre de filtres -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-body" style="padding: 1rem 1.5rem;">
                    <div class="filters-row" style="flex-wrap: wrap; gap: 1rem; align-items: flex-end;">

                        <div class="filter-item filter-item-search">
                            <label><i class="fas fa-search"></i> Recherche</label>
                            <input type="text" id="browseSearch" class="filter-input"
                                   placeholder="Nom du club..." autocomplete="off">
                        </div>

                        <div class="filter-item filter-item-select">
                            <label><i class="fas fa-map-marker-alt"></i> Campus</label>
                            <select id="browseCampus" class="filter-select">
                                <option value="">Tous</option>
                                <option value="calais">Calais</option>
                                <option value="longuenesse">Longuenesse</option>
                                <option value="dunkerque">Dunkerque</option>
                                <option value="boulogne">Boulogne</option>
                            </select>
                        </div>

                        <div class="filter-item filter-item-select">
                            <label><i class="fas fa-tag"></i> Type</label>
                            <select id="browseType" class="filter-select">
                                <option value="">Tous les types</option>
                                <?php
                                $types = array_unique(array_filter(array_column($clubs, 'type_club')));
                                sort($types);
                                foreach ($types as $t): ?>
                                    <option value="<?= htmlspecialchars(strtolower($t)) ?>">
                                        <?= htmlspecialchars($t) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-item filter-item-action">
                            <button type="button" id="browseReset" class="filter-reset-btn">
                                <i class="fas fa-undo"></i> Réinitialiser
                            </button>
                        </div>

                        <div class="filter-item" style="margin-left: auto; align-self: center;">
                            <span id="browseCount" style="color: #666; font-size: .9rem;">
                                <?= count($clubs) ?> résultat<?= count($clubs) > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille de cartes -->
            <div id="clubsGrid" style="
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            ">
                <?php
                $clubIcons = [
                    'sport'         => ['icon' => 'fa-running',       'color' => '#dc3545'],
                    'musique'       => ['icon' => 'fa-music',          'color' => '#6f42c1'],
                    'culture'       => ['icon' => 'fa-theater-masks',  'color' => '#e83e8c'],
                    'tech'          => ['icon' => 'fa-laptop-code',    'color' => '#0066cc'],
                    'informatique'  => ['icon' => 'fa-laptop-code',    'color' => '#0066cc'],
                    'jeux'          => ['icon' => 'fa-gamepad',        'color' => '#fd7e14'],
                    'gaming'        => ['icon' => 'fa-gamepad',        'color' => '#fd7e14'],
                    'esport'        => ['icon' => 'fa-gamepad',        'color' => '#fd7e14'],
                    'art'           => ['icon' => 'fa-palette',        'color' => '#e83e8c'],
                    'photo'         => ['icon' => 'fa-camera',         'color' => '#20c997'],
                    'video'         => ['icon' => 'fa-video',          'color' => '#17a2b8'],
                    'humanitaire'   => ['icon' => 'fa-hands-helping',  'color' => '#28a745'],
                    'environnement' => ['icon' => 'fa-leaf',           'color' => '#28a745'],
                    'lecture'       => ['icon' => 'fa-book',           'color' => '#6c757d'],
                    'cuisine'       => ['icon' => 'fa-utensils',       'color' => '#fd7e14'],
                    'danse'         => ['icon' => 'fa-person-booth',   'color' => '#e83e8c'],
                ];
                $campusColors = [
                    'calais'       => '#0066cc',
                    'longuenesse'  => '#28a745',
                    'dunkerque'    => '#dc3545',
                    'boulogne'     => '#ffc107',
                ];

                foreach ($clubs as $c):
                    $clubType  = strtolower($c['type_club'] ?? '');
                    $typeData  = ['icon' => 'fa-users', 'color' => '#0066cc'];
                    foreach ($clubIcons as $key => $data) {
                        if (strpos($clubType, $key) !== false) {
                            $typeData = $data;
                            break;
                        }
                    }
                    $campusKey   = strtolower($c['campus'] ?? 'calais');
                    $campusColor = $campusColors[$campusKey] ?? '#0066cc';
                    $desc        = trim($c['description'] ?? '');
                    $shortDesc   = mb_strlen($desc) > 120 ? mb_substr($desc, 0, 120) . '…' : $desc;
                    $membersCount = (int)($c['membres_count'] ?? 0);
                ?>
                <div class="club-browse-card"
                     data-name="<?= htmlspecialchars(strtolower($c['nom_club'])) ?>"
                     data-campus="<?= htmlspecialchars($campusKey) ?>"
                     data-type="<?= htmlspecialchars($clubType) ?>"
                     style="
                        background: #fff;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0,0,0,.08);
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        transition: transform .2s, box-shadow .2s;
                     "
                     onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.14)'"
                     onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'">

                    <!-- Bannière colorée -->
                    <div style="
                        background: linear-gradient(135deg, <?= $typeData['color'] ?> 0%, <?= $typeData['color'] ?>bb 100%);
                        padding: 1.5rem 1.25rem 1rem;
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                    ">
                        <div style="
                            width: 52px; height: 52px;
                            border-radius: 50%;
                            background: rgba(255,255,255,.2);
                            display: flex; align-items: center; justify-content: center;
                            flex-shrink: 0;
                        ">
                            <?php if (!empty($c['logo']) && file_exists(ROOT_PATH . '/uploads/logos/' . $c['logo'])): ?>
                                <img src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/uploads/logos/<?= htmlspecialchars($c['logo']) ?>"
                                     alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <i class="fas <?= $typeData['icon'] ?>" style="color:#fff;font-size:1.4rem;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h3 style="
                                color: #fff; margin: 0 0 .25rem;
                                font-size: 1rem; font-weight: 700;
                                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                            "><?= htmlspecialchars($c['nom_club']) ?></h3>
                            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                <span style="
                                    background: rgba(255,255,255,.25); color:#fff;
                                    border-radius: 20px; padding: .15rem .6rem;
                                    font-size: .75rem; font-weight: 600;
                                ">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($c['type_club'] ?? 'Club') ?>
                                </span>
                                <span style="
                                    background: <?= $campusColor ?>cc; color:#fff;
                                    border-radius: 20px; padding: .15rem .6rem;
                                    font-size: .75rem; font-weight: 600;
                                ">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($c['campus'] ?? 'N/A') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Corps de la carte -->
                    <div style="padding: 1rem 1.25rem; flex: 1; display: flex; flex-direction: column; gap: .75rem;">
                        <?php if ($shortDesc): ?>
                            <p style="margin:0; color:#555; font-size:.875rem; line-height:1.5;">
                                <?= htmlspecialchars($shortDesc) ?>
                            </p>
                        <?php else: ?>
                            <p style="margin:0; color:#aaa; font-size:.875rem; font-style:italic;">
                                Aucune description disponible.
                            </p>
                        <?php endif; ?>

                        <div style="display:flex; align-items:center; gap:.5rem; color:#888; font-size:.8rem; margin-top:auto;">
                            <i class="fas fa-users"></i>
                            <span><?= $membersCount ?> membre<?= $membersCount !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>

                    <!-- Pied de carte -->
                    <div style="padding: .75rem 1.25rem; border-top: 1px solid #f0f0f0;">
                        <a href="?page=club-view&id=<?= $c['club_id'] ?>" class="btn btn-primary" style="width:100%; text-align:center;">
                            <i class="fas fa-eye"></i> Voir le club
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Message vide après filtrage -->
            <div id="noFilterResults" style="display:none; text-align:center; padding:3rem; color:#888;">
                <i class="fas fa-search" style="font-size:2.5rem; margin-bottom:1rem; display:block;"></i>
                Aucun club ne correspond à votre recherche.
            </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = Array.from(document.querySelectorAll('.club-browse-card'));
        const searchInput = document.getElementById('browseSearch');
        const campusFilter = document.getElementById('browseCampus');
        const typeFilter = document.getElementById('browseType');
        const resetBtn = document.getElementById('browseReset');
        const countEl = document.getElementById('browseCount');
        const noResults = document.getElementById('noFilterResults');

        if (!cards.length) return;

        function applyFilters() {
            const search = (searchInput.value || '').toLowerCase().trim();
            const campus = (campusFilter.value || '').toLowerCase();
            const type   = (typeFilter.value || '').toLowerCase();

            let visible = 0;
            cards.forEach(card => {
                const matchName   = !search || card.dataset.name.includes(search);
                const matchCampus = !campus || card.dataset.campus === campus;
                const matchType   = !type   || card.dataset.type.includes(type);

                if (matchName && matchCampus && matchType) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countEl) countEl.textContent = visible + ' résultat' + (visible !== 1 ? 's' : '');
            if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
        }

        searchInput.addEventListener('input', applyFilters);
        campusFilter.addEventListener('change', applyFilters);
        typeFilter.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function () {
            searchInput.value = '';
            campusFilter.value = '';
            typeFilter.value = '';
            applyFilters();
        });
    });
    </script>
</body>
</html>
