# Architecture du Projet

## Vue d'ensemble

Vie Étudiante EILCO est une plateforme web de gestion de la vie associative pour l'École d'Ingénieurs du Littoral Côte d'Opale (EILCO). L'application suit une architecture **MVC (Model-View-Controller)** pure en PHP.

## Diagramme d'Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              NAVIGATEUR                                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              index.php                                        │
│                          (Point d'entrée unique)                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           config/bootstrap.php                                │
│  ┌──────────────┬──────────────┬──────────────┬──────────────┐              │
│  │ Environment  │   Security   │   Database   │ ErrorHandler │              │
│  └──────────────┴──────────────┴──────────────┴──────────────┘              │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            config/Router.php                                  │
│                    (Routing basé sur routes/web.php)                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
            ┌───────────┐   ┌───────────┐   ┌───────────┐
            │Controllers│   │  Models   │   │   Views   │
            └───────────┘   └───────────┘   └───────────┘
                    │               │               │
                    └───────────────┼───────────────┘
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         Base de données MySQL                                 │
│                           (vieasso / test_projet_tech)                        │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Structure des Dossiers

```
Code Source/
│
├── config/                    # Configuration et initialisation
│   ├── bootstrap.php          # Point d'initialisation principal
│   ├── Database.php           # Connexion PDO à la base
│   ├── DatabaseUtil.php       # Utilitaires SQL
│   ├── Email.php              # Configuration PHPMailer
│   ├── Environment.php        # Gestion des variables .env
│   ├── ErrorHandler.php       # Gestion centralisée des erreurs
│   ├── Router.php             # Routeur MVC
│   └── Security.php           # Headers HTTP et CSRF
│
├── controllers/               # Logique métier
│   ├── AdminController.php    # Administration système
│   ├── AuthController.php     # Authentification
│   ├── ClubController.php     # Gestion des clubs
│   ├── EventController.php    # Gestion des événements
│   ├── HomeController.php     # Page d'accueil
│   ├── SubscriptionController.php  # Inscriptions événements
│   ├── UserController.php     # Profils utilisateurs
│   └── ValidationController.php    # Workflow de validation
│
├── models/                    # Accès aux données
│   ├── Club.php               # Modèle clubs
│   ├── ClubMember.php         # Modèle membres de clubs
│   ├── Event.php              # Modèle événements
│   ├── EventReport.php        # Modèle rapports
│   ├── EventSubscription.php  # Modèle inscriptions
│   ├── User.php               # Modèle utilisateurs
│   └── Validation.php         # Modèle validations
│
├── views/                     # Templates HTML/PHP
│   ├── admin/                 # Vues administration
│   ├── auth/                  # Vues connexion/inscription
│   ├── club/                  # Vues clubs
│   ├── errors/                # Pages d'erreur personnalisées
│   ├── event/                 # Vues événements
│   ├── home/                  # Page d'accueil
│   ├── includes/              # Composants réutilisables
│   ├── subscription/          # Vues inscriptions
│   ├── user/                  # Vues utilisateur
│   └── validation/            # Vues validation
│
├── routes/                    # Définition des routes
│   └── web.php                # Configuration du routage
│
├── assets/                    # Ressources front-end
│   ├── js/                    # Scripts JavaScript
│   └── lib/                   # Bibliothèques externes
│
├── css/                       # Feuilles de style
├── images/                    # Images statiques
├── uploads/                   # Fichiers uploadés
│   ├── logos/                 # Logos des clubs
│   └── rapports/              # Rapports d'événements
│
├── logs/                      # Journaux d'application
├── vendor/                    # Dépendances Composer
├── Docs/                      # Documentation
│
└── index.php                  # Point d'entrée unique
```

## Flux de Requête

1. **Réception** : `index.php` reçoit toutes les requêtes
2. **Bootstrap** : `config/bootstrap.php` initialise l'environnement
3. **Routage** : `Router::dispatch()` analyse `?page=xxx`
4. **Contrôleur** : Le contrôleur approprié est instancié
5. **Modèle** : Les données sont récupérées via les modèles
6. **Vue** : Les données sont passées à la vue pour rendu
7. **Réponse** : HTML généré envoyé au navigateur

## Composants Clés

### Router (config/Router.php)

Le routeur centralise la gestion des URLs :

- Charge les routes depuis `routes/web.php`
- Vérifie l'authentification et les permissions
- Valide les tokens CSRF pour les POST
- Dispatch vers le bon contrôleur/méthode

### Security (config/Security.php)

Gère la sécurité applicative :

- Headers HTTP sécurisés (X-Frame-Options, CSP, etc.)
- Génération/validation tokens CSRF
- Détection HTTPS
- Force HTTPS en production

### ErrorHandler (config/ErrorHandler.php)

Gestion centralisée des erreurs :

- Pages d'erreur personnalisées (403, 404, 500, 503)
- Mode développement avec stack trace
- Mode production avec messages utilisateur
- Journalisation des erreurs

## Niveaux de Permission

| Niveau | Rôle         | Description                               |
| ------ | ------------ | ----------------------------------------- |
| 0      | Visiteur     | Accès lecture seule aux contenus publics  |
| 1      | Membre       | Utilisateur inscrit standard              |
| 2      | Gestionnaire | Membre de bureau de club                  |
| 3      | BDE          | Bureau des Étudiants, validation niveau 1 |
| 4      | Tuteur       | Enseignant tuteur, validation niveau 2    |
| 5      | Admin        | Administrateur système                    |

## Base de Données

### Tables Principales

- `users` : Utilisateurs et authentification
- `fiche_club` : Fiches descriptives des clubs
- `membres_club` : Adhésions aux clubs
- `fiche_event` : Événements et activités
- `abonnements` : Inscriptions aux événements
- `config` : Configuration applicative

### Schéma de Validation

```
Club créé (validation_* = NULL)
         │
         ▼
┌─────────────────┐
│ Validation BDE  │ (validation_admin = 1)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│Validation Tuteur│ (validation_tuteur = 1)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Club Validé   │ (validation_finale = 1)
└─────────────────┘
```

## Technologies Utilisées

- **Backend** : PHP 8.0+
- **Base de données** : MySQL 5.7+ / MariaDB
- **Serveur** : Apache (mod_rewrite) ou Nginx
- **Email** : PHPMailer avec SMTP
- **Configuration** : vlucas/phpdotenv
- **Front-end** : HTML5, CSS3, JavaScript vanilla
- **Icônes** : FontAwesome 6
