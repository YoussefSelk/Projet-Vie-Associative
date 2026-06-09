<?php
/**
 * Helper de rendu d'un menu déroulant d'horaires (HH:MM).
 *
 * Retour client juin 2026 : les horaires d'événement sont saisis via des
 * <select> afin de pouvoir DÉSACTIVER les choix invalides (heure de fin
 * antérieure à l'heure de début). La désactivation dynamique selon le début
 * choisi est gérée en JavaScript ; ce helper produit la liste complète des
 * options et présélectionne la valeur courante.
 *
 * @package Views/Includes
 */

if (!function_exists('renderTimeSelect')) {
    /**
     * Génère un <select> d'horaires par pas de $stepMinutes entre $startHour et $endHour.
     *
     * @param string $name       Attribut name du select (ex: 'horaire_fin')
     * @param string $id         Attribut id du select
     * @param string $selected   Valeur sélectionnée au format 'HH:MM' (ou 'HH:MM:SS')
     * @param int    $stepMinutes Pas en minutes (défaut 15)
     * @param int    $startHour  Première heure proposée (défaut 7)
     * @param int    $endHour    Dernière heure proposée incluse (défaut 23)
     */
    function renderTimeSelect(
        string $name,
        string $id,
        string $selected = '',
        int $stepMinutes = 15,
        int $startHour = 7,
        int $endHour = 23
    ): void {
        // Normalise la valeur sélectionnée en 'HH:MM'
        $selected = trim($selected);
        if ($selected !== '') {
            $selected = substr($selected, 0, 5);
        }

        // Construit la liste des créneaux
        $options = [];
        for ($h = $startHour; $h <= $endHour; $h++) {
            for ($m = 0; $m < 60; $m += max(1, $stepMinutes)) {
                $options[] = sprintf('%02d:%02d', $h, $m);
            }
        }

        // Préserve une valeur existante hors grille (ancien événement) en l'ajoutant.
        if ($selected !== '' && !in_array($selected, $options, true)) {
            $options[] = $selected;
            sort($options);
        }

        echo '<select id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"'
            . ' name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'
            . ' class="form-control time-select" required>';

        foreach ($options as $value) {
            $isSelected = ($value === $selected) ? ' selected' : '';
            // La VALEUR reste en 24h (HH:MM) ; le LIBELLE est affiché en 12h avec AM/PM.
            [$h, $m] = array_map('intval', explode(':', $value));
            $period  = ($h < 12) ? 'AM' : 'PM';
            $h12     = $h % 12;
            if ($h12 === 0) { $h12 = 12; }
            $label = sprintf('%02d:%02d %s', $h12, $m, $period);
            echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $isSelected . '>'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        echo '</select>';
    }
}
