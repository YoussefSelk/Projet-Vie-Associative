<?php
/**
 * =============================================================================
 * VUE – Module Export CSV
 * =============================================================================
 *
 * Interface permettant aux tuteurs, au BDE et aux administrateurs d'exporter
 * les données de la plateforme au format CSV compatible Microsoft Excel
 * (encodage UTF-16 LE, séparateur tabulation).
 *
 * Variables attendues :
 *  - $clubs : array  – liste des clubs validés (club_id, nom_club, campus)
 *
 * Permissions : Tuteur (2) et supérieur
 * @package Views/Export
 */

$pageTitle = 'Export CSV – Vie Étudiante EILCO';
$pageCss   = ['shared', 'buttons', 'forms', 'export'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <header class="header">
        <?php include VIEWS_PATH . '/includes/header.php'; ?>
    </header>

    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main>
        <div class="export-page">
            <div class="header-left">
                <a href="?page=home" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            <!-- ─── Hero ──────────────────────────────────────────────────── -->
            <div class="export-hero">
                <h1><i class="fas fa-file-download"></i> Module Export CSV</h1>
                <p>Exportez les données de la plateforme au format CSV compatible Microsoft Excel.</p>
            </div>

            <!-- ─── Alerte d'erreur éventuelle ────────────────────────────── -->
            <?php if (!empty($_SESSION['export_error'])): ?>
                <div class="export-alert error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($_SESSION['export_error']) ?></span>
                </div>
                <?php unset($_SESSION['export_error']); ?>
            <?php endif; ?>

            <!-- =============================================================
                 SECTION 1 – Clubs
                 ============================================================= -->
            <h2 class="export-section-title">
                <i class="fas fa-building"></i> Clubs
            </h2>

            <div class="export-grid">

                <!-- Carte : export global des clubs validés -->
                <div class="export-card">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Export global des clubs validés</h3>
                            <p class="export-card-desc">Tous les clubs validés, avec tuteur, membres, événements et participants regroupés sur une ligne par club dans un tableau Excel.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Nom du club</span>
                        <span class="export-col-badge">Type de club</span>
                        <span class="export-col-badge">Campus</span>
                        <span class="export-col-badge">Tuteur</span>
                        <span class="export-col-badge">Membres et rôles</span>
                        <span class="export-col-badge">Événements</span>
                        <span class="export-col-badge">Participants</span>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExport(
                                'Exporter tous les clubs validés',
                                'Exporter tous les clubs validés dans un CSV global, sans sélection de club.',
                                'index.php?page=export-clubs'
                            )">
                        <i class="fas fa-download"></i>
                        Exporter tous les clubs validés
                    </button>
                </div>

            </div><!-- /.export-grid -->

            <!-- =============================================================
                 SECTION 2 – Membres
                 ============================================================= -->
            <h2 class="export-section-title">
                <i class="fas fa-users"></i> Membres de clubs
            </h2>

            <div class="export-grid">
                <!-- Carte : clubs par utilisateur -->
                <div class="export-card accent-teal">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Clubs par utilisateur</h3>
                            <p class="export-card-desc">Tous les utilisateurs membres d'au moins un club validé, avec leurs clubs, rôles et statuts.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Utilisateur</span>
                        <span class="export-col-badge">Email</span>
                        <span class="export-col-badge">Promo</span>
                        <span class="export-col-badge">Nombre de clubs</span>
                        <span class="export-col-badge">Clubs et rôles</span>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExport(
                                'Clubs par utilisateur',
                                'Exporter la liste des utilisateurs avec leurs clubs validés.',
                                'index.php?page=export-user-clubs'
                            )">
                        <i class="fas fa-download"></i>
                        Exporter les clubs par utilisateur
                    </button>
                </div>


                <!-- Carte : membres d'un club spécifique -->
                <div class="export-card accent-green">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Membres d'un club</h3>
                            <p class="export-card-desc">Tous les membres validés d'un club avec leur rôle, promotion et tuteur.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Nom</span>
                        <span class="export-col-badge">Prénom</span>
                        <span class="export-col-badge">Email</span>
                        <span class="export-col-badge">Promotion / Spécialité</span>
                        <span class="export-col-badge">Rôle</span>
                        <span class="export-col-badge">Soutenance</span>
                        <span class="export-col-badge">Tuteur du club</span>
                    </div>

                    <div class="export-form-group">
                        <label for="club-members-select">
                            <i class="fas fa-building"></i> Sélectionner un club
                        </label>
                        <select id="club-members-select">
                            <option value="">— Choisir un club —</option>
                            <option value="all">Tous les clubs</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?= (int)$club['club_id'] ?>">
                                    [<?= htmlspecialchars($club['campus']) ?>] <?= htmlspecialchars($club['nom_club']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExportWithSelect(
                                'club-members-select',
                                'Membres du club',
                                'index.php?page=export-club-members&club_id='
                            )">
                        <i class="fas fa-download"></i>
                        Exporter les membres
                    </button>
                </div>

                <!-- Carte : membres avec soutenance -->
                <div class="export-card accent-purple">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Membres avec soutenance</h3>
                            <p class="export-card-desc">Uniquement les membres marqués « soutenance » dans un club.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Nom</span>
                        <span class="export-col-badge">Prénom</span>
                        <span class="export-col-badge">Email</span>
                        <span class="export-col-badge">Promotion / Spécialité</span>
                        <span class="export-col-badge">Rôle</span>
                        <span class="export-col-badge">Tuteur du club</span>
                    </div>

                    <div class="export-form-group">
                        <label for="club-soutenance-select">
                            <i class="fas fa-building"></i> Sélectionner un club
                        </label>
                        <select id="club-soutenance-select">
                            <option value="">— Choisir un club —</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?= (int)$club['club_id'] ?>">
                                    [<?= htmlspecialchars($club['campus']) ?>] <?= htmlspecialchars($club['nom_club']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExportWithSelect(
                                'club-soutenance-select',
                                'Membres soutenance',
                                'index.php?page=export-soutenance-members&club_id='
                            )">
                        <i class="fas fa-download"></i>
                        Exporter soutenance
                    </button>
                </div>

            </div><!-- /.export-grid -->

            <!-- =============================================================
                 SECTION 3 – Événements
                 ============================================================= -->
            <h2 class="export-section-title">
                <i class="fas fa-calendar-alt"></i> Événements
            </h2>

            <div class="export-grid">

                <!-- Carte : événements d'un club -->
                <div class="export-card accent-orange">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Événements d'un club</h3>
                            <p class="export-card-desc">Tous les événements validés organisés par un club.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Titre</span>
                        <span class="export-col-badge">Date</span>
                        <span class="export-col-badge">Horaires</span>
                        <span class="export-col-badge">Campus / Lieu</span>
                        <span class="export-col-badge">Responsable</span>
                        <span class="export-col-badge">Financement BDE</span>
                        <span class="export-col-badge">Soutenance</span>
                    </div>

                    <div class="export-form-group">
                        <label for="club-events-select">
                            <i class="fas fa-building"></i> Sélectionner un club
                        </label>
                        <select id="club-events-select">
                            <option value="">— Choisir un club —</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?= (int)$club['club_id'] ?>">
                                    [<?= htmlspecialchars($club['campus']) ?>] <?= htmlspecialchars($club['nom_club']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExportWithSelect(
                                'club-events-select',
                                'Événements du club',
                                'index.php?page=export-club-events&club_id='
                            )">
                        <i class="fas fa-download"></i>
                        Exporter les événements
                    </button>
                </div>

                <!-- Carte : événements par période -->
                <div class="export-card accent-teal">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Événements par période</h3>
                            <p class="export-card-desc">Tous les événements validés compris entre deux dates, tous clubs confondus.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Titre</span>
                        <span class="export-col-badge">Date</span>
                        <span class="export-col-badge">Club organisateur</span>
                        <span class="export-col-badge">Campus / Lieu</span>
                        <span class="export-col-badge">Responsable</span>
                    </div>

                    <div class="export-period-row">
                        <div class="export-form-group">
                            <label for="period-start">
                                <i class="fas fa-calendar-minus"></i> Date de début
                            </label>
                            <input type="date" id="period-start" value="<?= date('Y-01-01') ?>">
                        </div>
                        <div class="export-form-group">
                            <label for="period-end">
                                <i class="fas fa-calendar-plus"></i> Date de fin
                            </label>
                            <input type="date" id="period-end" value="<?= date('Y-12-31') ?>">
                        </div>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExportWithPeriod()">
                        <i class="fas fa-download"></i>
                        Exporter la période
                    </button>
                </div>

                <!-- Carte : événements passés -->
                <div class="export-card accent-red">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Événements passés</h3>
                            <p class="export-card-desc">Tous les événements validés dont la date est antérieure à aujourd'hui.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Titre</span>
                        <span class="export-col-badge">Date</span>
                        <span class="export-col-badge">Club organisateur</span>
                        <span class="export-col-badge">Campus</span>
                        <span class="export-col-badge">Rapport déposé</span>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExport(
                                'Événements passés',
                                'Exporter tous les événements validés dont la date est passée.',
                                'index.php?page=export-past-events'
                            )">
                        <i class="fas fa-download"></i>
                        Exporter les passés
                    </button>
                </div>

                <!-- Carte : événements à venir -->
                <div class="export-card">
                    <div class="export-card-header">
                        <div class="export-card-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div>
                            <h3 class="export-card-title">Événements à venir</h3>
                            <p class="export-card-desc">Tous les événements validés dont la date est égale ou postérieure à aujourd'hui.</p>
                        </div>
                    </div>

                    <div class="export-columns-preview">
                        <span class="export-col-badge">Titre</span>
                        <span class="export-col-badge">Date</span>
                        <span class="export-col-badge">Club organisateur</span>
                        <span class="export-col-badge">Campus</span>
                        <span class="export-col-badge">Financement BDE</span>
                    </div>

                    <button class="btn-export"
                            onclick="triggerExport(
                                'Événements à venir',
                                'Exporter tous les événements validés à venir.',
                                'index.php?page=export-upcoming-events'
                            )">
                        <i class="fas fa-download"></i>
                        Exporter les à venir
                    </button>
                </div>

            </div><!-- /.export-grid -->

        </div><!-- /.export-page -->
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <!-- Modal gérée par SweetAlert2 (pas de markup statique nécessaire) -->

    <!-- =====================================================================
         JAVASCRIPT – SweetAlert2 + fetch + Blob download
         ===================================================================== -->
    <script>
    (function () {
        'use strict';

        const BTN_COLOR    = '#2563eb'; // bleu primaire du thème
        const CANCEL_COLOR = '#6b7280';
        const INFO_HTML    = '<br><small style="color:#6b7280"><i class="fas fa-info-circle"></i>&nbsp;'
                           + 'Format compatible Microsoft Excel.</small>';

        /* ── Fetch AJAX + déclenchement du téléchargement Blob ────────────── */
        async function fetchCsv(url) {
            let response;
            try {
                response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (_) {
                Swal.showValidationMessage('Impossible de contacter le serveur. Vérifiez votre connexion.');
                return false;
            }

            if (!response.ok) {
                let msg = `Erreur serveur (${response.status}).`;
                try {
                    const json = await response.json();
                    if (json && json.message) { msg = json.message; }
                } catch (_) {}
                Swal.showValidationMessage(msg);
                return false;
            }

            // Nom de fichier depuis Content-Disposition
            const disposition = response.headers.get('Content-Disposition') || '';
            let filename = 'export.csv';
            const m = disposition.match(/filename="([^"]+)"/);
            if (m) { filename = m[1]; }

            // Déclencher le téléchargement
            const blob = await response.blob();
            const a    = document.createElement('a');
            a.href     = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => { URL.revokeObjectURL(a.href); document.body.removeChild(a); }, 150);
            return true;
        }

        /* ── Modale SweetAlert2 de confirmation + téléchargement ─────────── */
        async function doExport(title, descHtml, url) {
            const result = await Swal.fire({
                title: title,
                html: `<p>${descHtml}</p>${INFO_HTML}`,
                icon: 'question',
                iconColor: BTN_COLOR,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download"></i>&nbsp;Télécharger',
                cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Annuler',
                confirmButtonColor: BTN_COLOR,
                cancelButtonColor:  CANCEL_COLOR,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => fetchCsv(url)
            });

            if (result.isConfirmed && result.value === true) {
                Swal.fire({
                    title: 'Téléchargement lancé !',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        }

        /* ── API publique ────────────────────────────────────────────────── */

        window.triggerExport = function (title, desc, url) {
            doExport(title, desc, url);
        };

        window.triggerExportWithSelect = function (selectId, title, baseUrl) {
            const sel = document.getElementById(selectId);
            if (!sel || !sel.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Club non sélectionné',
                    text: 'Veuillez sélectionner un club dans la liste avant d\'exporter.',
                    confirmButtonColor: BTN_COLOR
                });
                return;
            }
            const clubName = sel.options[sel.selectedIndex].text.replace(/^\[.*?\]\s*/, '').trim();
            doExport(title, 'Club : <strong>' + clubName + '</strong>', baseUrl + encodeURIComponent(sel.value));
        };

        window.triggerExportWithPeriod = function () {
            const dateDebut = document.getElementById('period-start').value;
            const dateFin   = document.getElementById('period-end').value;

            if (!dateDebut || !dateFin) {
                Swal.fire({ icon: 'warning', title: 'Dates manquantes',
                    text: 'Renseignez les deux dates de la période.', confirmButtonColor: BTN_COLOR });
                return;
            }
            if (dateDebut > dateFin) {
                Swal.fire({ icon: 'warning', title: 'Période invalide',
                    text: 'La date de début doit être antérieure ou égale à la date de fin.', confirmButtonColor: BTN_COLOR });
                return;
            }

            const fmt = d => d.split('-').reverse().join('/');
            const url = 'index.php?page=export-events-period'
                      + '&date_debut=' + encodeURIComponent(dateDebut)
                      + '&date_fin='   + encodeURIComponent(dateFin);

            doExport(
                'Événements par période',
                `Du <strong>${fmt(dateDebut)}</strong> au <strong>${fmt(dateFin)}</strong>`,
                url
            );
        };

    })();
    </script>

</body>
</html>
