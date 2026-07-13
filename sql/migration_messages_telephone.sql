-- Migration : ajout du téléphone aux messages de contact (pour réponse WhatsApp).
USE cahier_lumineux;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cahier_lumineux' AND TABLE_NAME = 'messages_contact' AND COLUMN_NAME = 'telephone');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE messages_contact ADD COLUMN telephone VARCHAR(30) NULL AFTER email', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
