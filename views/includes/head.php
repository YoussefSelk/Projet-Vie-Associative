<!--
    En-tete HTML commun a toutes les pages
    
    Contient :
    - Configuration meta (charset, viewport, theme-color)
    - Balises de compatibilite cross-browser
    - Feuilles de style CSS modulaires
    - FontAwesome pour les icones
    - Chart.js pour les graphiques admin
    - Script de recherche
    
    Compatibilite navigateurs :
    - Chrome, Firefox, Safari, Edge (modernes)
    - Safari iOS, Chrome Android
    - Internet Explorer 11 (degradation gracieuse)
    
    @package Views/Includes
-->
<head>
    <!-- Configuration meta de base -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0066cc">
    <meta name="format-detection" content="telephone=no">
    
    <!-- Preconnect pour optimisation des ressources externes -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- DNS Prefetch pour ameliorer les performances -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <!-- Feuilles de style CSS modulaires -->
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/compatibility.css">
    <link rel="stylesheet" href="/css/responsive.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/buttons.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/tables.css">
    <link rel="stylesheet" href="/css/login.css">
    <link rel="stylesheet" href="/css/clubs.css">
    <link rel="stylesheet" href="/css/events.css">
    <link rel="stylesheet" href="/css/profiles.css">
    <link rel="stylesheet" href="/css/calendar.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/search.css">
    
    <!-- Icones FontAwesome (version locale) -->
    <link rel="stylesheet" href="/assets/lib/fontawesome/css/all.min.css">
    
    <!-- Chart.js pour les graphiques du dashboard admin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    
    <!-- Composant de recherche -->
    <script src="/assets/js/search.js" defer></script>
    
    <!-- Script de compatibilite pour les anciens navigateurs -->
    <script>
        // Polyfill pour Element.closest() (IE11)
        if (!Element.prototype.closest) {
            Element.prototype.closest = function(s) {
                var el = this;
                do {
                    if (el.matches(s)) return el;
                    el = el.parentElement || el.parentNode;
                } while (el !== null && el.nodeType === 1);
                return null;
            };
        }
        // Polyfill pour Element.matches() (IE11)
        if (!Element.prototype.matches) {
            Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
        }
        // Polyfill pour NodeList.forEach() (IE11)
        if (window.NodeList && !NodeList.prototype.forEach) {
            NodeList.prototype.forEach = Array.prototype.forEach;
        }
    </script>
    
    <title>Vie Étudiante à l'EILCO</title>
</head>