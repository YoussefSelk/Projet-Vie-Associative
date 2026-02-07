# Implémentation du Cycle de Rejet et Correction de Club

## 🎯 Objectif
Transformer le rejet d'un club par l'admin en une demande de modification pour l'étudiant, plutôt qu'une suppression définitive.

---

## 📋 Logique Métier Implémentée

### 1. **Action de Rejet (Admin)**
Quand l'admin clique sur "Rejeter" :
- **Base de données** : `validation_admin = 0`, `validation_finale = 0`
- **Motif stocké** : colonne `motif_refus` sauvegardé avec le message de l'admin
- **Localisé dans** : `ValidationController.php` → `validateClub()` → `rejectClub()`

```php
// Validation.php - rejectClub()
public function rejectClub($club_id, $motif_refus = '') {
    $stmt = $this->db->prepare(
        "UPDATE fiche_club SET validation_admin = 0, validation_finale = 0, motif_refus = ? 
         WHERE club_id = ?"
    );
    return $stmt->execute([$motif_refus, $club_id]);
}
```

### 2. **Visibilité Étudiant (À implémenter dans la Vue)**
Dans la page `views/club/my_clubs.php` ou `views/club/view.php` :
- **Condition** : Si `validation_finale = 0` ET `validation_admin = 0`
- **Affichage** : Statut "🔴 À corriger"
- **Contenu** : Motif du refus + Bouton "Modifier"

#### Code Vue suggéré :
```php
<?php if ($club['validation_finale'] == 0 && $club['validation_admin'] == 0): ?>
    <div class="alert alert-warning">
        <h4>⚠️ Votre club nécessite des corrections</h4>
        <p><strong>Motif :</strong> <?= htmlspecialchars($club['motif_refus']) ?></p>
        <a href="/club/edit/<?= $club['club_id'] ?>" class="btn btn-primary">
            ✏️ Modifier mon club
        </a>
    </div>
<?php endif; ?>
```

### 3. **Action de Correction (Étudiant)**
Quand l'étudiant enregistre ses modifications :
- **Automatique** : `Club.php` → `updateClub()` détecte `validation_admin = 0`
- **Réinitialisation** : Passe `validation_admin = NULL` et `validation_finale = NULL`
- **Cycle relancé** : Le club réapparaît dans la liste admin "Clubs en attente"

```php
// Club.php - updateClub()
public function updateClub($id, $data, $resetValidation = false) {
    // ... traitement des champs autorisés ...
    
    // Vérifier si le club était en correction (validation_admin = 0)
    $check = $this->db->prepare("SELECT validation_admin FROM fiche_club WHERE club_id = ?");
    $check->execute([$id]);
    $club = $check->fetch(PDO::FETCH_ASSOC);
    
    if ($club && $club['validation_admin'] === 0) {
        // Le club était rejeté, le remettre en attente
        $fields[] = "validation_admin = NULL";
        $fields[] = "validation_finale = NULL";
    }
    
    // Exécuter la mise à jour
    $stmt = $this->db->prepare("UPDATE fiche_club SET " . implode(", ", $fields) . " WHERE club_id = ?");
    return $stmt->execute($values);
}
```

### 4. **Retour chez l'Admin**
Puisque les valeurs deviennent `NULL` :
- **Automatique** : La requête `getPendingClubs()` filtre `WHERE validation_finale IS NULL OR validation_finale = 0`
- **Résultat** : Le club réapparaît dans la liste des "Clubs en attente"
- **Admin peut** : L'examiner à nouveau et approuver ou rejeter

```php
// Validation.php - getPendingClubs()
public function getPendingClubs() {
    $stmt = $this->db->prepare(
        "SELECT * FROM fiche_club WHERE validation_finale IS NULL OR validation_finale = 0"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

---

## 🔄 Flux Complet

```
┌─────────────────────────────────────────────────┐
│ 1. CRÉATION / MODIFICATION ÉTUDIANT             │
│ validation_admin = NULL, validation_finale = NULL│
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│ 2. EXAMEN ADMIN (Club en attente)               │
│ Admin clique "Rejeter"                          │
└────────────────┬────────────────────────────────┘
                 │
                 ▼ rejectClub()
┌─────────────────────────────────────────────────┐
│ 3. CLUB REJETÉ - À CORRIGER                     │
│ validation_admin = 0, validation_finale = 0     │
│ motif_refus = "message de l'admin"              │
└────────────────┬────────────────────────────────┘
                 │
        Étudiant voit "À corriger"
        Affiche le motif + bouton "Modifier"
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│ 4. ÉTUDIANT CORRIGE LE CLUB                     │
│ Enregistre les modifications                    │
└────────────────┬────────────────────────────────┘
                 │
                 ▼ updateClub() [détecte admin=0]
┌─────────────────────────────────────────────────┐
│ 5. CLUB EN ATTENTE (RÉINTÉGRÉ)                  │
│ validation_admin = NULL, validation_finale = NULL│
│ motif_refus = conservé (traçabilité)            │
└────────────────┬────────────────────────────────┘
                 │
        Club réapparaît dans la liste admin
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│ 6. NOUVEL EXAMEN ADMIN                          │
│ (Boucle si nécessaire, ou approbation)          │
└─────────────────────────────────────────────────┘
```

---

## 📁 Fichiers Modifiés

### ✅ **models/Validation.php**
**Méthode : `rejectClub()`**
- **Ancienne logique** : Définissait `validation_finale = -1` (suppression)
- **Nouvelle logique** : Définit `validation_admin = 0, validation_finale = 0` + stocke `motif_refus`

### ✅ **models/Club.php**
**Méthode : `updateClub()`**
- **Nouvelle fonctionnalité** : Détecte automatiquement si `validation_admin = 0`
- **Action** : Réinitialise `validation_admin` et `validation_finale` à `NULL`
- **Résultat** : Club remis en attente d'examen

---

## 🛠️ Implémentations Supplémentaires Requises

### 1. **Vue Étudiant** (`views/club/my_clubs.php`)
Afficher le statut "À corriger" avec motif et bouton d'édition.

### 2. **Vue Admin** (Optionnel pour clarté)
Ajouter un badge "Demande de modification" pour les clubs avec `validation_admin = 0`.

### 3. **Documentation/Traçabilité** (Optionnel)
Envisager un log d'audit des rejets et corrections pour traçabilité.

---

## 📊 Structure de Base de Données Requise

Vérifiez que la table `fiche_club` contient ces colonnes :

```sql
ALTER TABLE fiche_club ADD COLUMN motif_refus TEXT NULL;
-- ou si déjà présente, assurez-vous que c'est TEXT et nullable
```

**Colonnes utilisées :**
- `validation_admin` : NULL, 0, ou 1
- `validation_finale` : NULL, 0, ou 1
- `validation_tuteur` : NULL ou 1
- `motif_refus` : Texte du motif de refus

---

## ✨ Avantages du Système

✅ **Pour l'admin** : Pas de suppression définitive, cycle d'amélioration contrôlé  
✅ **Pour l'étudiant** : Feedback clair + possibilité de correction immédiate  
✅ **Traçabilité** : Historique conservé avec `motif_refus`  
✅ **Flexibilité** : Rejets multiples possibles si corrections insuffisantes  

---

## 🚀 Prochaines Étapes

1. ✅ Modifier `Validation.php::rejectClub()` → **FAIT**
2. ✅ Modifier `Club.php::updateClub()` → **FAIT**
3. ⏳ Ajouter affichage du statut dans la vue étudiant
4. ⏳ Tester le cycle complet (création → rejet → correction → attente)
5. ⏳ Optionnel : Ajouter badges/indicators dans l'interface admin

---

**Date d'implémentation** : 4 janvier 2026  
**Version** : 1.0
