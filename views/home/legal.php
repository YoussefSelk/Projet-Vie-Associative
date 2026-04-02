<?php
declare(strict_types=1);

$pageTitle = 'Mentions légales et confidentialité - Vie Étudiante EILCO';
$pageCss = ['shared', 'home'];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    <?php include VIEWS_PATH . '/includes/barre_nav.php'; ?>

    <main class="home-page" style="padding: 2rem 0;">
        <section class="features-section" style="padding-top: 1rem;">
            <div class="features-container" style="display:block; max-width: 980px;">
                <div class="section-header-home" style="margin-bottom: 1.25rem;">
                    <span class="section-tag">Conformité</span>
                    <h1>Mentions légales et politique de confidentialité</h1>
                    <p>Dernière mise à jour: <?= htmlspecialchars($updated_at ?? date('Y-m-d')) ?></p>
                </div>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>1. Éditeur du service</h2>
                    <p>La plateforme Vie Étudiante EILCO est exploitée dans un cadre pédagogique pour la gestion des clubs et événements étudiants.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>2. Données collectées</h2>
                    <p>Les données traitées incluent les informations de compte, d'appartenance aux clubs, d'inscription aux événements et les fichiers nécessaires au fonctionnement de la plateforme.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>3. Finalités</h2>
                    <p>Les traitements sont réalisés pour assurer l'authentification, la gestion des activités associatives, la validation administrative et la sécurité du service.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>4. Base légale et conservation</h2>
                    <p>Les données sont traitées sur la base de l'intérêt légitime de fonctionnement de la vie étudiante et conservées pour la durée nécessaire aux finalités pédagogiques et administratives.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>5. Sécurité</h2>
                    <p>La plateforme applique des mesures de sécurité techniques: mots de passe hachés, protections CSRF, journalisation de sécurité et limitation de tentatives sur les flux sensibles.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>6. Cookies et session</h2>
                    <p>Des cookies techniques strictement nécessaires sont utilisés pour maintenir la session, protéger les formulaires et garantir l'intégrité de la navigation.</p>
                </article>

                <article class="feature-card" style="margin-bottom: 1rem; text-align: left;">
                    <h2>7. Vos droits</h2>
                    <p>Vous pouvez demander l'accès, la rectification ou la suppression de vos données selon les règles applicables de votre établissement.</p>
                </article>

                <article class="feature-card" style="text-align: left;">
                    <h2>8. Contact</h2>
                    <p>Pour toute question juridique ou relative aux données personnelles, contactez l'administration EILCO via les canaux institutionnels.</p>
                </article>
            </div>
        </section>
    </main>

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
