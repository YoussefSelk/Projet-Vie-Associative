# Guide de Contribution

## Introduction

Merci de votre intérêt pour contribuer au projet Vie Étudiante EILCO ! Ce document décrit les conventions et processus à suivre.

## Prérequis

- PHP 8.0+ installé localement
- MySQL 5.7+ ou MariaDB 10.3+
- Composer 2.x
- Git
- Un éditeur de code (VS Code recommandé)

## Configuration de l'Environnement

### 1. Fork et Clone

```bash
# Fork le repo sur GitHub, puis :
git clone https://github.com/votre-username/vie-etudiante.git
cd vie-etudiante
```

### 2. Installation

```bash
composer install
cp .env.example .env
# Éditer .env avec vos paramètres locaux
```

### 3. Base de Données

```bash
mysql -u root -p -e "CREATE DATABASE vieasso_dev"
mysql -u root -p vieasso_dev < database/schema.sql
```

## Structure du Projet

```
├── config/           # Configuration et classes core
├── controllers/      # Contrôleurs MVC
├── models/           # Modèles de données
├── views/            # Templates PHP
├── routes/           # Définition des routes
├── assets/           # JS, images, librairies
├── css/              # Feuilles de style
├── uploads/          # Fichiers uploadés (gitignored)
├── logs/             # Logs d'erreurs (gitignored)
├── Docs/             # Documentation
└── vendor/           # Dépendances (gitignored)
```

## Conventions de Code

### PHP

#### Style de Code

```php
<?php
// Déclarations strictes recommandées
declare(strict_types=1);

/**
 * Description de la classe.
 */
class MaClasse
{
    /**
     * Propriétés en camelCase
     */
    private int $maVariable;

    /**
     * Méthodes en camelCase avec documentation.
     *
     * @param string $param Description du paramètre
     * @return bool Description du retour
     */
    public function maMethode(string $param): bool
    {
        // Code avec indentation 4 espaces
        if ($condition) {
            // Accolades sur même ligne
        }

        return true;
    }
}
```

#### Conventions de Nommage

| Type         | Convention  | Exemple          |
| ------------ | ----------- | ---------------- |
| Classes      | PascalCase  | `ClubController` |
| Méthodes     | camelCase   | `getUserById()`  |
| Variables    | camelCase   | `$userName`      |
| Constantes   | UPPER_SNAKE | `MAX_FILE_SIZE`  |
| Tables BDD   | snake_case  | `fiche_club`     |
| Colonnes BDD | snake_case  | `date_creation`  |

#### Bonnes Pratiques

```php
// ✅ Bon - Type hints
public function getUser(int $id): ?User

// ❌ Mauvais - Pas de type hints
public function getUser($id)

// ✅ Bon - Requête préparée
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);

// ❌ Mauvais - Injection SQL possible
$pdo->query("SELECT * FROM users WHERE id = $id");

// ✅ Bon - Échappement HTML
<?= htmlspecialchars($user['name']) ?>

// ❌ Mauvais - XSS possible
<?= $user['name'] ?>
```

### CSS

#### Organisation

```css
/* ==========================================================================
   Section Title
   ========================================================================== */

/**
 * Sous-section avec commentaire de description
 */
.component {
  /* Positionnement */
  position: relative;
  display: flex;

  /* Box Model */
  margin: 10px;
  padding: 15px;
  width: 100%;

  /* Typographie */
  font-size: 16px;
  color: #333;

  /* Visuel */
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;

  /* Animation */
  transition: all 0.3s ease;
}
```

#### Nommage des Classes

```css
/* Composant principal */
.card {
}

/* Élément du composant */
.card-header {
}
.card-body {
}
.card-footer {
}

/* Modificateur */
.card-primary {
}
.card-large {
}

/* État */
.card.is-active {
}
.card.has-error {
}
```

### JavaScript

```javascript
// Utiliser const/let, pas var
const CONFIG = { ... };
let counter = 0;

// Fonctions fléchées pour callbacks
items.forEach(item => {
    console.log(item);
});

// Async/await pour asynchrone
async function fetchData() {
    try {
        const response = await fetch('/api/data');
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
    }
}
```

## Workflow Git

### Branches

| Branche     | Usage                           |
| ----------- | ------------------------------- |
| `main`      | Production stable               |
| `develop`   | Développement actif             |
| `feature/*` | Nouvelles fonctionnalités       |
| `bugfix/*`  | Corrections de bugs             |
| `hotfix/*`  | Corrections urgentes production |

### Processus de Contribution

```bash
# 1. Créer une branche depuis develop
git checkout develop
git pull origin develop
git checkout -b feature/ma-fonctionnalite

# 2. Développer et commiter
git add .
git commit -m "feat: description de la fonctionnalité"

# 3. Pousser et créer PR
git push origin feature/ma-fonctionnalite
# Créer Pull Request sur GitHub
```

### Messages de Commit

Format : `type: description courte`

| Type       | Usage                                       |
| ---------- | ------------------------------------------- |
| `feat`     | Nouvelle fonctionnalité                     |
| `fix`      | Correction de bug                           |
| `docs`     | Documentation                               |
| `style`    | Formatage (pas de changement de code)       |
| `refactor` | Refactoring (pas de changement fonctionnel) |
| `test`     | Ajout/modification de tests                 |
| `chore`    | Maintenance, dépendances                    |

**Exemples :**

```
feat: ajout export CSV des membres de club
fix: correction affichage logo club dans modal
docs: mise à jour guide d'installation
refactor: extraction méthode validation email
```

## Ajout d'une Nouvelle Fonctionnalité

### 1. Planification

Avant de coder :

- Vérifier qu'un issue n'existe pas déjà
- Discuter de l'approche si changement majeur
- Définir les critères d'acceptation

### 2. Structure Type

```
Nouvelle fonctionnalité : Export PDF des événements

Fichiers à créer/modifier :
├── controllers/EventController.php  (ajouter méthode exportPdf)
├── models/Event.php                 (ajouter méthode getData)
├── views/event/export-pdf.php       (template PDF)
├── routes/web.php                   (ajouter route)
└── css/pdf.css                      (styles PDF)
```

### 3. Implémentation

```php
// 1. Ajouter la route
'event-export-pdf' => [
    'controller' => 'EventController',
    'action' => 'exportPdf',
    'auth' => true,
    'permission' => 2
],

// 2. Créer la méthode contrôleur
public function exportPdf(): void
{
    Security::requirePermission(2);
    $id = (int)($_GET['id'] ?? 0);

    $event = Event::find($id);
    if (!$event) {
        Router::redirect('events');
        return;
    }

    // Logique d'export...
    require 'views/event/export-pdf.php';
}

// 3. Créer la vue
// views/event/export-pdf.php
```

### 4. Tests Manuels

Avant de soumettre :

- [ ] Fonctionnalité marche comme prévu
- [ ] Pas de régression sur fonctionnalités existantes
- [ ] Responsive (mobile/desktop)
- [ ] Permissions vérifiées
- [ ] Messages d'erreur appropriés

## Correction d'un Bug

### 1. Reproduction

```markdown
## Bug Report

**Description:** Le logo du club ne s'affiche pas dans la modal

**Étapes pour reproduire:**

1. Aller sur ?page=tutoring
2. Cliquer sur un club avec logo
3. Observer la modal

**Comportement attendu:** Logo visible dans la modal
**Comportement actuel:** Image cassée (404)

**Environnement:**

- PHP: 8.1
- Navigateur: Chrome 120
```

### 2. Investigation

```bash
# Rechercher les usages
grep -r "logo_club" --include="*.php"

# Vérifier les logs
tail -f logs/error.log
```

### 3. Fix et Test

```php
// Avant (bug)
$logoPath = $club['logo_club'];

// Après (fix)
$logoPath = '../uploads/logos/' . basename($club['logo_club']);
```

## Revue de Code

### Checklist pour Reviewers

- [ ] Code lisible et bien documenté
- [ ] Conventions de nommage respectées
- [ ] Pas de vulnérabilités de sécurité
- [ ] Requêtes SQL préparées
- [ ] Sorties échappées (XSS)
- [ ] Vérification des permissions
- [ ] Pas de code mort/commenté
- [ ] Messages d'erreur appropriés

### Feedback Constructif

```markdown
# ✅ Bon feedback

"Cette requête pourrait être vulnérable à l'injection SQL.
Suggestion : utiliser une requête préparée comme ceci : ..."

# ❌ Mauvais feedback

"Ce code est mauvais."
```

## Documentation

### Code

```php
/**
 * Récupère un club par son ID.
 *
 * @param int $id Identifiant du club
 * @return array|null Données du club ou null si non trouvé
 * @throws PDOException En cas d'erreur BDD
 */
public static function find(int $id): ?array
```

### Fonctionnalités

Pour toute nouvelle fonctionnalité, mettre à jour :

- `README.md` si changement majeur
- `Docs/` pour documentation détaillée
- `API_REFERENCE.md` si nouvelle API

## Ressources

### Documentation Externe

- [PHP Documentation](https://www.php.net/docs.php)
- [PDO Tutorial](https://www.php.net/manual/en/book.pdo.php)
- [MDN Web Docs](https://developer.mozilla.org/)

### Outils Recommandés

- **VS Code** - Éditeur avec extensions PHP
- **PHP CS Fixer** - Formatage automatique
- **PHPStan** - Analyse statique
- **XAMPP/Laragon** - Stack locale

## Questions ?

- Ouvrir un issue sur GitHub
- Contacter l'équipe de développement
- Consulter la documentation existante

Merci de contribuer ! 🎉
