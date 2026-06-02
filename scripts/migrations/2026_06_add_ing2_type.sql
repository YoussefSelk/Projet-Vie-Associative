-- =============================================================================
-- Migration : ajout de la colonne ing2_type à la table users
-- Date       : juin 2026
-- Contexte   : retour client EILCO — "Seuls les ING2 FISE peuvent s'inscrire
--              à la soutenance". On a besoin de distinguer FISE / FISEA, ce qui
--              n'était jusqu'ici pas persisté en base (l'info était collectée à
--              l'inscription puis perdue).
--
-- Sûr à rejouer : la colonne n'est ajoutée que si elle n'existe pas déjà
-- (MySQL 8 / MariaDB 10.4+ supportent ADD COLUMN IF NOT EXISTS).
-- =============================================================================

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `ing2_type` VARCHAR(10) NULL DEFAULT NULL AFTER `promo`;

-- (Optionnel) Backfill : si vous disposez d'une source fiable du type FISE/FISEA
-- pour les ING2 existants, renseignez-la ici. Tant que ing2_type est NULL pour un
-- compte ING2, le code accorde le bénéfice du doute (l'étudiant reste éligible à la
-- soutenance) afin de ne pas bloquer les comptes historiques.
--
-- Exemple :
-- UPDATE `users` SET `ing2_type` = 'FISE'  WHERE `promo` = 'ING2' AND id IN (...);
-- UPDATE `users` SET `ing2_type` = 'FISEA' WHERE `promo` = 'ING2' AND id IN (...);
