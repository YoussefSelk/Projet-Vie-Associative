<!--
    Pied de page du site
    
    Contient :
    - Logo et description de l'EILCO
    - Liens vers reseaux sociaux
    - Liens rapides vers les campus
    - Navigation secondaire
    - Informations de contact
    - Copyright et credits
    
    @package Views/Includes
-->
<footer class="modern-footer">
    <div class="footer-top">
        <div class="footer-container">
            <!-- Section marque avec logo et reseaux sociaux -->
            <div class="footer-brand">
                <img src="images/EILCO-LOGO-2022.png" alt="EILCO" class="footer-logo">
                <p>Vie Étudiante EILCO - La plateforme des clubs et événements de l'École d'Ingénieurs du Littoral Côte d'Opale</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/EILCO.Officiel" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.linkedin.com/school/eilco/" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/eilco_officiel/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://twitter.com/EILCO_officiel" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <!-- Colonnes de liens -->
            <div class="footer-links">
                <!-- Colonne campus -->
                <div class="footer-column">
                    <h4>Campus</h4>
                    <ul>
                        <li><a href="?page=home"><i class="fas fa-map-marker-alt"></i> Calais</a></li>
                        <li><a href="?page=home"><i class="fas fa-map-marker-alt"></i> Longuenesse</a></li>
                        <li><a href="?page=home"><i class="fas fa-map-marker-alt"></i> Dunkerque</a></li>
                        <li><a href="?page=home"><i class="fas fa-map-marker-alt"></i> Boulogne</a></li>
                    </ul>
                </div>
                
                <!-- Colonne navigation -->
                <div class="footer-column">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="?page=home"><i class="fas fa-home"></i> Accueil</a></li>
                        <li><a href="?page=event-list"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                        <li><a href="?page=club-list"><i class="fas fa-users"></i> Clubs</a></li>
                        <li><a href="?page=login"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                    </ul>
                </div>
                
                <!-- Colonne contact -->
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-map-pin"></i> 50 Rue Ferdinand Buisson</li>
                        <li><i class="fas fa-city"></i> 62228 CALAIS CEDEX</li>
                        <li><i class="fas fa-phone"></i> 03 21 17 10 05</li>
                        <li><a href="https://eilco.univ-littoral.fr" target="_blank"><i class="fas fa-globe"></i> eilco.univ-littoral.fr</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Barre inferieure avec copyright -->
    <div class="footer-bottom">
        <div class="footer-container">
            <p>&copy; <?= date('Y') ?> EILCO - Vie Étudiante. Tous droits réservés.</p>
            <p class="footer-credits">Développé avec <i class="fas fa-heart"></i> par les étudiants de l'EILCO</p>
        </div>
    </div>
</footer>