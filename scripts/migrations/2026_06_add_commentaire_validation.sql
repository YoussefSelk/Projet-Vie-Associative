-- =============================================================================
-- Migration : ajout de la colonne commentaire_validation
-- Date       : juin 2026
-- Contexte   : retour client EILCO — permettre à un tuteur / admin d'ajouter un
--              commentaire lorsqu'il VALIDE un club ou un événement (ex. alerter
--              sur des règles de sécurité même si l'événement est validé).
--              Ce commentaire est distinct du motif_refus (rejet) et du
--              motif_forcage (validation forcée).
--
-- Sûr à rejouer : la colonne n'est ajoutée que si elle n'existe pas déjà.
-- =============================================================================

ALTER TABLE `fiche_club`
  ADD COLUMN IF NOT EXISTS `commentaire_validation` TEXT NULL DEFAULT NULL;

ALTER TABLE `fiche_event`
  ADD COLUMN IF NOT EXISTS `commentaire_validation` TEXT NULL DEFAULT NULL;
