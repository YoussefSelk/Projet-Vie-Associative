# Documentation de la Base de Données

## Vue d'Ensemble

La base de données `vieasso` utilise MySQL/MariaDB et gère toutes les données du système de vie étudiante EILCO.

## Schéma Relationnel

```
┌──────────────┐     ┌──────────────────┐     ┌───────────────┐
│    users     │     │   fiche_club     │     │  fiche_event  │
├──────────────┤     ├──────────────────┤     ├───────────────┤
│ id (PK)      │◄────┤ responsable (FK) │     │ id (PK)       │
│ email        │     │ id (PK)          │◄────┤ id_club (FK)  │
│ pseudo       │     │ nom_club         │     │ nom_event     │
│ password     │     │ description      │     │ date_event    │
│ permission   │     │ date_creation    │     │ description   │
│ ...          │     │ logo_club        │     │ statut        │
└──────────────┘     │ tuteur           │     │ ...           │
       │             │ statut           │     └───────────────┘
       │             └──────────────────┘            │
       │                     │                       │
       │             ┌──────────────────┐    ┌──────────────────┐
       │             │  membres_club    │    │   abonnements    │
       │             ├──────────────────┤    ├──────────────────┤
       │             │ id (PK)          │    │ id (PK)          │
       └─────────────┤ id_user (FK)     │    │ id_user (FK)     │
                     │ id_club (FK)     │    │ id_event (FK)    │
                     │ role             │    │ date_inscription │
                     │ date_inscription │    │ statut           │
                     └──────────────────┘    └──────────────────┘

┌──────────────────┐     ┌──────────────────┐
│  event_reports   │     │     config       │
├──────────────────┤     ├──────────────────┤
│ id (PK)          │     │ id (PK)          │
│ event_id (FK)    │     │ config_key       │
│ fichier          │     │ config_value     │
│ date_upload      │     │ updated_at       │
└──────────────────┘     └──────────────────┘
```

## Tables Détaillées

### Table `users`

Stocke les informations des utilisateurs du système.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique auto-incrémenté |
| `email` | VARCHAR(255) | NON | UNIQUE | Email universitaire |
| `pseudo` | VARCHAR(100) | NON | | Nom d'affichage |
| `password` | VARCHAR(255) | NON | | Hash bcrypt du mot de passe |
| `permission` | TINYINT | NON | | Niveau de permission (0-5) |
| `promo` | VARCHAR(50) | OUI | | Promotion/Année d'études |
| `filiaire` | VARCHAR(100) | OUI | | Filière d'études |
| `date_creation` | DATETIME | NON | | Date d'inscription |
| `avatar` | VARCHAR(255) | OUI | | Chemin vers l'avatar |
| `bio` | TEXT | OUI | | Biographie utilisateur |

**Niveaux de Permission :**

| Valeur | Rôle | Description |
|--------|------|-------------|
| 0 | Visiteur | Compte non vérifié |
| 1 | Membre | Étudiant standard |
| 2 | Tuteur | Enseignant référent |
| 3 | BDE | Bureau des Étudiants |
| 5 | Admin | Administrateur système |

**Index :**
- `PRIMARY KEY (id)`
- `UNIQUE INDEX (email)`

### Table `fiche_club`

Informations sur les clubs/associations.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `nom_club` | VARCHAR(255) | NON | | Nom du club |
| `description` | TEXT | NON | | Description détaillée |
| `responsable` | INT | NON | FK | ID du responsable (users.id) |
| `date_creation` | DATETIME | NON | | Date de création |
| `logo_club` | VARCHAR(255) | OUI | | Chemin vers le logo |
| `tuteur` | VARCHAR(255) | NON | | Nom du tuteur (peut être vide) |
| `statut` | ENUM | NON | | 'en_attente', 'valide', 'rejete' |
| `motif_rejet` | TEXT | OUI | | Raison du rejet si applicable |

**Clés Étrangères :**
```sql
FOREIGN KEY (responsable) REFERENCES users(id) ON DELETE CASCADE
```

### Table `membres_club`

Association entre utilisateurs et clubs.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `id_user` | INT | NON | FK | ID utilisateur |
| `id_club` | INT | NON | FK | ID club |
| `role` | VARCHAR(50) | NON | | Rôle dans le club |
| `date_inscription` | DATETIME | NON | | Date d'adhésion |

**Rôles Disponibles :**
- `membre` - Membre standard
- `gestionnaire` - Peut gérer le club
- `responsable` - Responsable principal

**Clés Étrangères :**
```sql
FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (id_club) REFERENCES fiche_club(id) ON DELETE CASCADE
```

**Contrainte d'Unicité :**
```sql
UNIQUE INDEX (id_user, id_club)
```

### Table `fiche_event`

Événements organisés par les clubs.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `nom_event` | VARCHAR(255) | NON | | Nom de l'événement |
| `description` | TEXT | NON | | Description détaillée |
| `date_event` | DATETIME | NON | | Date et heure de l'événement |
| `date_limite` | DATETIME | OUI | | Date limite d'inscription |
| `lieu` | VARCHAR(255) | NON | | Lieu de l'événement |
| `capacite` | INT | OUI | | Capacité maximale (null = illimité) |
| `id_club` | INT | NON | FK | Club organisateur |
| `statut` | ENUM | NON | | 'en_attente', 'valide', 'rejete' |
| `motif_rejet` | TEXT | OUI | | Raison du rejet |
| `date_creation` | DATETIME | NON | | Date de création |

**Clés Étrangères :**
```sql
FOREIGN KEY (id_club) REFERENCES fiche_club(id) ON DELETE CASCADE
```

### Table `abonnements`

Inscriptions des utilisateurs aux événements.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `id_user` | INT | NON | FK | ID utilisateur |
| `id_event` | INT | NON | FK | ID événement |
| `date_inscription` | DATETIME | NON | | Date d'inscription |
| `statut` | ENUM | NON | | 'inscrit', 'annule' |

**Clés Étrangères :**
```sql
FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (id_event) REFERENCES fiche_event(id) ON DELETE CASCADE
```

**Contrainte d'Unicité :**
```sql
UNIQUE INDEX (id_user, id_event)
```

### Table `event_reports`

Rapports post-événement (bilans).

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `event_id` | INT | NON | FK | ID événement |
| `fichier` | VARCHAR(255) | NON | | Chemin vers le fichier |
| `date_upload` | DATETIME | NON | | Date de téléversement |
| `description` | TEXT | OUI | | Description du rapport |

### Table `config`

Configuration système dynamique.

| Colonne | Type | Null | Clé | Description |
|---------|------|------|-----|-------------|
| `id` | INT | NON | PK | Identifiant unique |
| `config_key` | VARCHAR(100) | NON | UNIQUE | Clé de configuration |
| `config_value` | TEXT | OUI | | Valeur de configuration |
| `updated_at` | DATETIME | NON | | Dernière modification |

**Clés de Configuration :**
- `site_name` - Nom du site
- `maintenance_mode` - Mode maintenance (0/1)
- `registration_enabled` - Inscriptions ouvertes (0/1)
- `email_verification` - Vérification email requise (0/1)

## Requêtes Courantes

### Obtenir tous les clubs validés avec leur responsable

```sql
SELECT 
    c.*, 
    u.pseudo as responsable_nom,
    u.email as responsable_email
FROM fiche_club c
JOIN users u ON c.responsable = u.id
WHERE c.statut = 'valide'
ORDER BY c.nom_club;
```

### Compter les membres par club

```sql
SELECT 
    c.id,
    c.nom_club,
    COUNT(m.id) as nombre_membres
FROM fiche_club c
LEFT JOIN membres_club m ON c.id = m.id_club
WHERE c.statut = 'valide'
GROUP BY c.id, c.nom_club
ORDER BY nombre_membres DESC;
```

### Événements à venir avec nombre d'inscrits

```sql
SELECT 
    e.*,
    c.nom_club,
    COUNT(a.id) as inscrits
FROM fiche_event e
JOIN fiche_club c ON e.id_club = c.id
LEFT JOIN abonnements a ON e.id = a.id_event AND a.statut = 'inscrit'
WHERE e.date_event > NOW() AND e.statut = 'valide'
GROUP BY e.id
ORDER BY e.date_event;
```

### Clubs en attente de validation pour un tuteur

```sql
SELECT c.*, u.pseudo as responsable_nom
FROM fiche_club c
JOIN users u ON c.responsable = u.id
WHERE c.statut = 'en_attente'
ORDER BY c.date_creation DESC;
```

### Historique d'un utilisateur

```sql
-- Clubs rejoints
SELECT 'club' as type, c.nom_club as nom, m.date_inscription
FROM membres_club m
JOIN fiche_club c ON m.id_club = c.id
WHERE m.id_user = ?

UNION ALL

-- Événements inscrits
SELECT 'event' as type, e.nom_event as nom, a.date_inscription
FROM abonnements a
JOIN fiche_event e ON a.id_event = e.id
WHERE a.id_user = ? AND a.statut = 'inscrit'

ORDER BY date_inscription DESC;
```

## Scripts SQL

### Création de la Base

```sql
CREATE DATABASE IF NOT EXISTS vieasso 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE vieasso;
```

### Création des Tables

```sql
-- Table users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    pseudo VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    permission TINYINT NOT NULL DEFAULT 0,
    promo VARCHAR(50) DEFAULT NULL,
    filiaire VARCHAR(100) DEFAULT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    INDEX idx_permission (permission)
) ENGINE=InnoDB;

-- Table fiche_club
CREATE TABLE fiche_club (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_club VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    responsable INT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    logo_club VARCHAR(255) DEFAULT NULL,
    tuteur VARCHAR(255) NOT NULL DEFAULT '',
    statut ENUM('en_attente', 'valide', 'rejete') NOT NULL DEFAULT 'en_attente',
    motif_rejet TEXT DEFAULT NULL,
    FOREIGN KEY (responsable) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- Table membres_club
CREATE TABLE membres_club (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_club INT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'membre',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_club) REFERENCES fiche_club(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_user_club (id_user, id_club)
) ENGINE=InnoDB;

-- Table fiche_event
CREATE TABLE fiche_event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_event VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date_event DATETIME NOT NULL,
    date_limite DATETIME DEFAULT NULL,
    lieu VARCHAR(255) NOT NULL,
    capacite INT DEFAULT NULL,
    id_club INT NOT NULL,
    statut ENUM('en_attente', 'valide', 'rejete') NOT NULL DEFAULT 'en_attente',
    motif_rejet TEXT DEFAULT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_club) REFERENCES fiche_club(id) ON DELETE CASCADE,
    INDEX idx_date_event (date_event),
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- Table abonnements
CREATE TABLE abonnements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_event INT NOT NULL,
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('inscrit', 'annule') NOT NULL DEFAULT 'inscrit',
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_event) REFERENCES fiche_event(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_user_event (id_user, id_event)
) ENGINE=InnoDB;

-- Table event_reports
CREATE TABLE event_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    date_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description TEXT DEFAULT NULL,
    FOREIGN KEY (event_id) REFERENCES fiche_event(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table config
CREATE TABLE config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Données Initiales

```sql
-- Administrateur par défaut (mot de passe: admin123 - À CHANGER)
INSERT INTO users (email, pseudo, password, permission) VALUES
('admin@eilco.fr', 'Admin', '$2y$10$...', 5);

-- Configuration initiale
INSERT INTO config (config_key, config_value) VALUES
('site_name', 'Vie Étudiante EILCO'),
('maintenance_mode', '0'),
('registration_enabled', '1'),
('email_verification', '1');
```

## Maintenance

### Sauvegardes

```bash
# Sauvegarde complète
mysqldump -u root -p vieasso > backup_$(date +%Y%m%d).sql

# Restauration
mysql -u root -p vieasso < backup_20240115.sql
```

### Nettoyage Périodique

```sql
-- Supprimer les événements passés de plus de 2 ans
DELETE FROM fiche_event 
WHERE date_event < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Nettoyer les comptes non vérifiés (permission = 0) de plus de 30 jours
DELETE FROM users 
WHERE permission = 0 
AND date_creation < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Optimisation

```sql
-- Analyser et optimiser les tables
ANALYZE TABLE users, fiche_club, membres_club, fiche_event, abonnements;
OPTIMIZE TABLE users, fiche_club, membres_club, fiche_event, abonnements;
```
