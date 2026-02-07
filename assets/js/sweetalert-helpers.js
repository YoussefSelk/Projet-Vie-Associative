/**
 * SweetAlert2 Simple Helper Functions
 *
 * Basic utilities for confirmations, warnings, and info messages.
 *
 * @requires SweetAlert2 v11+
 */

const SwalHelper = {
  /**
   * Show a success message
   * @param {string} title - The title of the alert
   * @param {string} text - Optional descriptive text
   */
  success: (title, text = "") => {
    return Swal.fire({
      icon: "success",
      title: title,
      text: text,
      confirmButtonText: "OK",
    });
  },

  /**
   * Show an error message
   * @param {string} title - The title of the alert
   * @param {string} text - Optional descriptive text
   */
  error: (title, text = "") => {
    return Swal.fire({
      icon: "error",
      title: title,
      text: text,
      confirmButtonText: "OK",
    });
  },

  /**
   * Show a warning message
   * @param {string} title - The title of the alert
   * @param {string} text - Optional descriptive text
   */
  warning: (title, text = "") => {
    return Swal.fire({
      icon: "warning",
      title: title,
      text: text,
      confirmButtonText: "OK",
    });
  },

  /**
   * Show an info message
   * @param {string} title - The title of the alert
   * @param {string} text - Optional descriptive text
   */
  info: (title, text = "") => {
    return Swal.fire({
      icon: "info",
      title: title,
      text: text,
      confirmButtonText: "OK",
    });
  },

  /**
   * Show a confirmation dialog
   * @param {string} title - The title of the confirmation
   * @param {string} text - The confirmation message
   * @param {string} confirmText - Text for confirm button (default: 'Confirmer')
   * @param {string} cancelText - Text for cancel button (default: 'Annuler')
   * @returns {Promise} - Resolves with the result object
   */
  confirm: (
    title,
    text = "",
    confirmText = "Confirmer",
    cancelText = "Annuler",
  ) => {
    return Swal.fire({
      title: title,
      text: text,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
    });
  },

  /**
   * Show a delete confirmation dialog
   * @param {string} itemName - Name of the item to delete (default: 'cet élément')
   * @returns {Promise} - Resolves with the result object
   */
  confirmDelete: (itemName = "cet élément") => {
    return Swal.fire({
      title: "Êtes-vous sûr ?",
      text: `Voulez-vous vraiment supprimer ${itemName} ?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Oui, supprimer",
      cancelButtonText: "Annuler",
    });
  },
};

// Make it globally available
window.SwalHelper = SwalHelper;
