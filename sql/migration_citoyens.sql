-- Migration : ajout des comptes citoyens sur une base déjà installée.
-- (sql/schema.sql a été mis à jour pour les nouvelles installations ; ce fichier
-- sert uniquement à mettre à niveau une base existante sans perdre les données.)
USE cahier_lumineux;

CREATE TABLE IF NOT EXISTS citoyens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  telephone VARCHAR(30) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cahier_lumineux' AND TABLE_NAME = 'demandes' AND COLUMN_NAME = 'citoyen_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE demandes ADD COLUMN citoyen_id INT NULL AFTER code_suivi, ADD INDEX (citoyen_id), ADD CONSTRAINT fk_demandes_citoyen FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cahier_lumineux' AND TABLE_NAME = 'rendezvous' AND COLUMN_NAME = 'citoyen_id');
SET @sql2 = IF(@col_exists2 = 0, 'ALTER TABLE rendezvous ADD COLUMN citoyen_id INT NULL AFTER code_suivi, ADD INDEX (citoyen_id), ADD CONSTRAINT fk_rdv_citoyen FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
