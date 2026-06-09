<?php
/**
 * Script de désactivation dynamique des horaires de fin (retour client juin 2026).
 *
 * Désactive (disabled) dans le menu « heure de fin » toutes les options
 * inférieures ou égales à l'« heure de début » sélectionnée : il devient
 * impossible de choisir une fin avant le début. Si la sélection de fin devient
 * invalide après un changement de début, elle est repositionnée automatiquement
 * sur le premier créneau valide.
 *
 * Suppose deux <select> d'identifiants 'horaire_debut' et 'horaire_fin'.
 *
 * @package Views/Includes
 */
?>
<script>
(function () {
    var debut = document.getElementById('horaire_debut');
    var fin = document.getElementById('horaire_fin');
    if (!debut || !fin) return;

    function syncFinOptions() {
        var start = debut.value;
        // Désactive toute heure de fin <= heure de début (comparaison "HH:MM" lexicographique, valeurs zéro-paddées)
        for (var i = 0; i < fin.options.length; i++) {
            var opt = fin.options[i];
            opt.disabled = (start !== '' && opt.value !== '' && opt.value <= start);
        }
        // Si la fin actuellement choisie est désormais désactivée, basculer sur le 1er créneau valide
        var current = fin.options[fin.selectedIndex];
        if (!current || current.disabled) {
            for (var j = 0; j < fin.options.length; j++) {
                if (!fin.options[j].disabled && fin.options[j].value !== '') {
                    fin.selectedIndex = j;
                    break;
                }
            }
        }
    }

    debut.addEventListener('change', syncFinOptions);
    syncFinOptions(); // état initial au chargement
})();
</script>
