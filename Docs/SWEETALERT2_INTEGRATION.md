# SweetAlert2 Integration Guide

## 📚 Overview

SweetAlert2 has been integrated for beautiful confirmations, warnings, and info messages. This replaces standard JavaScript `alert()` and `confirm()` with modern popups.

## 🔗 Resources

- **Official Website**: https://sweetalert2.github.io/
- **CDN Version**: v11 (latest)

## 📦 Files

1. **`views/includes/head.php`** - SweetAlert2 CDN loaded automatically
2. **`assets/js/sweetalert-helpers.js`** - Simple helper functions

## 🎯 Quick Usage

### 1. Success Message

```javascript
SwalHelper.success("Opération réussie !");
SwalHelper.success("Club créé", "Le club a été créé avec succès");
```

### 2. Error Message

```javascript
SwalHelper.error("Erreur", "Une erreur est survenue");
```

### 3. Warning Message

```javascript
SwalHelper.warning("Attention", "Cette action est irréversible");
```

### 4. Info Message

```javascript
SwalHelper.info("Information", "Votre session expire dans 5 minutes");
```

### 5. Simple Confirmation

```javascript
SwalHelper.confirm(
  "Êtes-vous sûr ?",
  "Cette action va publier l'événement",
).then((result) => {
  if (result.isConfirmed) {
    // User clicked "Confirmer"
    console.log("Confirmed");
  }
});

// Custom button text
SwalHelper.confirm("Publier ?", "Visible par tous", "Oui, publier", "Non").then(
  (result) => {
    if (result.isConfirmed) {
      // Publish
    }
  },
);
```

### 6. Delete Confirmation

```javascript
SwalHelper.confirmDelete("ce club").then((result) => {
  if (result.isConfirmed) {
    // Proceed with deletion
    deleteItem();
  }
});
```

## 💡 Complete Example

```javascript
// Delete button click handler
document.querySelector(".btn-delete").addEventListener("click", () => {
  SwalHelper.confirmDelete("cet événement").then((result) => {
    if (result.isConfirmed) {
      // User confirmed - delete the item
      fetch("/api/event/delete", { method: "DELETE" }).then((response) => {
        if (response.ok) {
          SwalHelper.success("Supprimé !", "L'événement a été supprimé");
        } else {
          SwalHelper.error("Erreur", "Impossible de supprimer");
        }
      });
    }
  });
});
```

## 🎨 Available Icons

- `success` ✅ Green checkmark
- `error` ❌ Red X
- `warning` ⚠️ Yellow exclamation
- `info` ℹ️ Blue i
- `question` ❓ Blue question mark

## 📖 Advanced Usage

Need more control? Use SweetAlert2 directly:

```javascript
Swal.fire({
  title: "Custom Title",
  text: "Custom message",
  icon: "warning",
  showCancelButton: true,
  confirmButtonText: "Yes",
  cancelButtonText: "No",
}).then((result) => {
  if (result.isConfirmed) {
    // Action
  }
});
```

Full documentation: https://sweetalert2.github.io/

## 🧪 Test Page

Open `sweetalert-test.html` in your browser to see examples.

---

That's it! Simple confirmations and alerts for your project. 🎉
