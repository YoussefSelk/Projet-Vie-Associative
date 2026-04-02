<?php
/**
 * En-tête HTML optimisé pour toutes les pages
 * 
 * Contient :
 * - Configuration meta (charset, viewport, theme-color)
 * - SEO optimizations (Open Graph, Twitter Cards, robots)
 * - Balises de compatibilité cross-browser
 * - Feuilles de style CSS modulaires avec chemins absolus
 * - FontAwesome pour les icônes
 * - Chart.js pour les graphiques admin
 * - Script de recherche
 * 
 * Compatibilité navigateurs :
 * - Chrome, Firefox, Safari, Edge (modernes)
 * - Safari iOS, Chrome Android
 * - Internet Explorer 11 (dégradation gracieuse)
 * 
 * @package Views/Includes
 */

// Use BASE_URL from bootstrap or fallback to hardened environment helper
$baseUrl = defined('BASE_URL') ? BASE_URL : Environment::getBaseUrl();

// Current page URL for canonical
$currentUrl = $baseUrl . ($_SERVER['REQUEST_URI'] ?? '/');

// Page title - can be overridden before including head.php
$pageTitle = $pageTitle ?? 'Vie Étudiante EILCO - Clubs & Événements';
$pageDescription = $pageDescription ?? 'Découvrez la vie associative de l\'École d\'Ingénieurs du Littoral Côte d\'Opale. Rejoignez les clubs, participez aux événements et vivez pleinement votre expérience étudiante.';
$pageKeywords = $pageKeywords ?? 'EILCO, vie étudiante, clubs, événements, BDE, association, Calais, Dunkerque, Boulogne, Longuenesse';
?>
<head>
    <!-- ========================================
         BASE META TAGS
         ======================================== -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    
    <!-- ========================================
         SEO META TAGS
         ======================================== -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="author" content="EILCO">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    <meta name="revisit-after" content="7 days">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    
    <!-- ========================================
         OPEN GRAPH (Facebook, LinkedIn)
         ======================================== -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $baseUrl ?>/images/og-image.png">
    <meta property="og:site_name" content="Vie Étudiante EILCO">
    <meta property="og:locale" content="fr_FR">
    
    <!-- ========================================
         TWITTER CARDS
         ======================================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image" content="<?= $baseUrl ?>/images/twitter-card.png">
    
    <!-- ========================================
         MOBILE & PWA
         ======================================== -->
    <meta name="theme-color" content="#0066cc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EILCO Vie Étudiante">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $baseUrl ?>/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/images/apple-touch-icon.png">
    
    <!-- ========================================
         PERFORMANCE OPTIMIZATIONS
         ======================================== -->
    <!-- Preconnect for external resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?= $baseUrl ?>/css/core/base.css" as="style">
    <link rel="preload" href="<?= $baseUrl ?>/assets/lib/fontawesome/css/all.min.css" as="style">
    
    <!-- ========================================
         CSS STYLESHEETS (Optimized Loading)
         Only essential CSS loads automatically.
         Other CSS loaded via $pageCss array.
         ======================================== -->
    
    <!-- 1. CORE - Always required -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/core/variables.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/core/base.css">
    
    <!-- 2. LAYOUT - Always required (header, nav, footer on every page) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/layout/header.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/layout/navbar.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/layout/footer.css">
    
    <!-- 3. PAGE-SPECIFIC CSS (defined via $pageCss array) -->
    <?php 
    // Map CSS names to their folder paths
    $cssMap = [
        // Core
        'compatibility' => 'core/compatibility',
        // Components
        'shared' => 'components/shared',
        'buttons' => 'components/buttons',
        'forms' => 'components/forms',
        'tables' => 'components/tables',
        'search' => 'components/search',
        'pagination' => 'components/pagination',
        'calendar' => 'components/calendar',
        // Pages
        'home' => 'pages/home',
        'auth' => 'pages/auth',
        'login' => 'pages/auth',  // legacy alias
        'clubs' => 'pages/clubs',
        'events' => 'pages/events',
        'profiles' => 'pages/profiles',
        'dashboard' => 'pages/dashboard',
        'admin' => 'pages/admin',
        'validation' => 'pages/validation',
        'errors' => 'pages/errors',
        'export'     => 'pages/export',
    ];
    
    if (!empty($pageCss) && is_array($pageCss)): 
        foreach ($pageCss as $css):
            $path = $cssMap[$css] ?? "pages/$css";
    ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/<?= htmlspecialchars($path) ?>.css">
    <?php 
        endforeach;
    endif; 
    ?>
    
    <!-- 5. RESPONSIVE - Media queries (must be last) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/responsive.css">
    
    <!-- ========================================
         ICON LIBRARY (FontAwesome Local)
         ======================================== -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/lib/fontawesome/css/all.min.css">
    
    <!-- ========================================
         JAVASCRIPT
         ======================================== -->
    <!-- SweetAlert2 for beautiful alerts and confirmations -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.20/dist/sweetalert2.all.min.js"
            integrity="sha384-6W5GbmA1e/krqeMofQ3ghXcfiCAF0UOhB5fh5Hp6BenD1NBQq4BFtyz4nAfOrryB"
            crossorigin="anonymous"></script>
    
    <!-- SweetAlert2 Helper Functions -->
    <script src="<?= $baseUrl ?>/assets/js/sweetalert-helpers.js"></script>
    
    <!-- Chart.js for admin dashboards (deferred) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
            integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4"
            crossorigin="anonymous" defer></script>
    
    <!-- Search component (deferred) -->
    <script src="<?= $baseUrl ?>/assets/js/search.js" defer></script>
    
    <!-- Pagination component (deferred) -->
    <script src="<?= $baseUrl ?>/assets/js/pagination.js" defer></script>
    
    <!-- ========================================
         BROWSER COMPATIBILITY POLYFILLS
         ======================================== -->
    <script>
        // Polyfill for Element.closest() (IE11)
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
        // Polyfill for Element.matches() (IE11)
        if (!Element.prototype.matches) {
            Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
        }
        // Polyfill for NodeList.forEach() (IE11)
        if (window.NodeList && !NodeList.prototype.forEach) {
            NodeList.prototype.forEach = Array.prototype.forEach;
        }
        // Polyfill for Array.from() (IE11)
        if (!Array.from) {
            Array.from = function(arrayLike) {
                return [].slice.call(arrayLike);
            };
        }
    </script>
    
    <!-- ========================================
         STRUCTURED DATA (JSON-LD)
         ======================================== -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "EILCO - École d'Ingénieurs du Littoral Côte d'Opale",
        "url": "<?= $baseUrl ?>",
        "logo": "<?= $baseUrl ?>/images/logo.png",
        "description": "<?= htmlspecialchars($pageDescription) ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Calais",
            "addressRegion": "Hauts-de-France",
            "addressCountry": "FR"
        }
    }
    </script>
</head>