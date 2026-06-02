-- =============================================================================
-- Migration : colonne ing2_type sur la table users (OPTIONNELLE)
-- Date       : juin 2026
-- Contexte   : retour client EILCO — "Seuls les ING2 FISE peuvent s'inscrire
--              à la soutenance".
--
-- IMPORTANT : dans la base existante, la spécialité est DÉJÀ encodée directement
-- dans la colonne `promo` ("ING2FISE" / "ING2FISEA"). C'est cette valeur qui fait
-- foi pour l'éligibilité à la soutenance (cf. User::isEligibleForSoutenance).
-- La colonne `ing2_type` n'est qu'un complément facultatif (souvent NULL) ; cette
-- migration est donc surtout utile pour les nouvelles installations. Elle est sûre
-- à rejouer et n'est PAS indispensable au fonctionnement de la règle.
-- =============================================================================

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `ing2_type` VARCHAR(10) NULL DEFAULT NULL AFTER `promo`;
