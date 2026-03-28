<?php
/**
 * Calendrier general - Version AJAX
 * 
 * Ce fichier genere uniquement le conteneur HTML.
 * Le contenu est charge dynamiquement via assets/js/calendar.js
 * 
 * @package Views/Includes
 */

$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

$campus_colors = [
    'Calais'      => ['bg' => '#f8d7da', 'class' => 'campus-calais'],
    'Longuenesse' => ['bg' => '#add8e6', 'class' => 'campus-longuenesse'],
    'Boulogne'    => ['bg' => '#b2f2bb', 'class' => 'campus-boulogne'],
    'Dunkerque'   => ['bg' => '#fff3b0', 'class' => 'campus-dunkerque'],
];
?>

<!-- Calendar Component - AJAX Powered -->
<div class="calendar-component" id="calendarApp" data-logged-in="<?= $user_id ? 'true' : 'false' ?>" data-csrf="<?= htmlspecialchars(Security::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="calendar-title">
        <h2><i class="fas fa-calendar-alt"></i> Programme associatif du mois</h2>
    </div>

    <!-- Navigation -->
    <div class="nav-calendrier" id="calendarNav">
        <button type="button" class="cal-nav-btn" id="calPrev" aria-label="Mois precedent">
            <i class="fas fa-chevron-left"></i> Mois precedent
        </button>
        <div class="cal-current-month" id="calMonthLabel">
            <span class="cal-month-text"></span>
            <span class="cal-year-text"></span>
        </div>
        <button type="button" class="cal-nav-btn" id="calNext" aria-label="Mois suivant">
            Mois suivant <i class="fas fa-chevron-right"></i>
        </button>
        <button type="button" class="cal-nav-btn cal-today-btn" id="calToday" aria-label="Aujourd hui" title="Revenir au mois actuel">
            <i class="fas fa-dot-circle"></i> Aujourd'hui
        </button>
    </div>

    <!-- Calendar Grid -->
    <div class="cal-grid-wrapper" id="calGridWrapper">
        <div class="cal-loading" id="calLoading">
            <div class="cal-spinner"></div>
            <span>Chargement du calendrier...</span>
        </div>
        <div class="cal-grid" id="calGrid"></div>
    </div>

    <!-- Legend/Filter -->
    <div class="legend" id="campusLegend">
        <form id="campus-filter-form">
            <?php foreach ($campus_colors as $campus => $info): ?>
                <label class="legend-item">
                    <input type="checkbox" name="campus" value="<?= htmlspecialchars(strtolower($campus)) ?>" checked>
                    <span class="legend-color" style="background-color: <?= $info['bg'] ?>;"></span>
                    <?= htmlspecialchars($campus) ?>
                </label>
            <?php endforeach; ?>
        </form>
    </div>

</div><!-- End Calendar Component -->

<!-- Event Detail Modal -->
<div class="cal-modal-overlay" id="calModalOverlay" aria-hidden="true">
    <div class="cal-modal" id="calModal" role="dialog" aria-modal="true">
        <div class="cal-modal-content" id="calModalContent"></div>
        <button type="button" class="cal-modal-close" id="calModalClose">Fermer</button>
    </div>
</div>

<!-- Reminders Container -->
<div id="calReminders" class="cal-reminders-stack"></div>

<!-- Toast notifications -->
<div id="calToast" class="cal-toast" aria-live="polite"></div>

<script src="assets/js/calendar.js"></script>
