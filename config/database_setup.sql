-- =====================================================
-- Database Setup Script for Vie Étudiante EILCO
-- Run this script to create missing tables
-- =====================================================

-- Create subscribe_event table if not exists
CREATE TABLE IF NOT EXISTS subscribe_event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subscription (event_id, user_id),
    FOREIGN KEY (event_id) REFERENCES fiche_event(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rapport_event table if not exists
CREATE TABLE IF NOT EXISTS rapport_event (
    rapport_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    contenu TEXT,
    photo_path VARCHAR(255),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES fiche_event(event_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create membres_club table if not exists
CREATE TABLE IF NOT EXISTS membres_club (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    membre_id INT NOT NULL,
    role VARCHAR(100) DEFAULT 'membre',
    valide TINYINT(1) DEFAULT 0,
    date_adhesion DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_membership (club_id, membre_id),
    FOREIGN KEY (club_id) REFERENCES fiche_club(club_id) ON DELETE CASCADE,
    FOREIGN KEY (membre_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to fiche_club if they don't exist
-- Note: Run these individually if needed

-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS tuteur_id INT;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS remarques TEXT;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS remarques_refus TEXT;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS projet_associatif TINYINT(1) DEFAULT 0;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS soutenance_date DATE;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS validation_bde TINYINT(1) DEFAULT 0;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS validation_tuteur TINYINT(1) DEFAULT 0;
-- ALTER TABLE fiche_club ADD COLUMN IF NOT EXISTS validation_admin TINYINT(1) DEFAULT 0;

-- Add missing columns to fiche_event if they don't exist
-- ALTER TABLE fiche_event ADD COLUMN IF NOT EXISTS remarques TEXT;
-- ALTER TABLE fiche_event ADD COLUMN IF NOT EXISTS remarques_refus TEXT;
-- ALTER TABLE fiche_event ADD COLUMN IF NOT EXISTS type_event ENUM('event', 'activity') DEFAULT 'event';

-- Add index for performance
-- CREATE INDEX IF NOT EXISTS idx_club_campus ON fiche_club(campus);
-- CREATE INDEX IF NOT EXISTS idx_event_campus ON fiche_event(campus);
-- CREATE INDEX IF NOT EXISTS idx_event_date ON fiche_event(date_ev);
