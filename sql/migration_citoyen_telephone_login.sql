-- Migration : simplifie l'inscription citoyenne (Prénom, Nom, Contact, Mot de passe).
-- Le téléphone devient l'identifiant de connexion ; l'e-mail devient optionnel.
USE cahier_lumineux;

SET @email_nullable = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cahier_lumineux' AND TABLE_NAME = 'citoyens' AND COLUMN_NAME = 'email' AND IS_NULLABLE = 'YES');
SET @sql1 = IF(@email_nullable = 0, 'ALTER TABLE citoyens MODIFY COLUMN email VARCHAR(150) NULL', 'SELECT 1');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @tel_unique = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'cahier_lumineux' AND TABLE_NAME = 'citoyens' AND INDEX_NAME = 'telephone');
SET @sql2 = IF(@tel_unique = 0, 'ALTER TABLE citoyens ADD UNIQUE INDEX telephone (telephone)', 'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
