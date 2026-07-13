-- Base de données du portail de la Mairie de Chérif Lo
-- À importer via phpMyAdmin (XAMPP) ou : mysql -u root < schema.sql

CREATE DATABASE IF NOT EXISTS cahier_lumineux CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cahier_lumineux;

-- Comptes du back-office (espace mairie)
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  nom_complet VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Compte par défaut : identifiant "admin" / mot de passe "MairieCL2026!"
-- ⚠️ À changer après la première connexion.
INSERT INTO admin_users (username, password_hash, nom_complet) VALUES
('admin', '$2y$10$7cKoFFPtkxv7GekJKi/Hg.aCz0I9aZ5ctK/fE6HdO96V5d06RzQgK', 'Administrateur Mairie')
ON DUPLICATE KEY UPDATE username = username;

-- Comptes citoyens (espace personnel : suivi et historique des démarches).
-- Le téléphone est l'identifiant de connexion (inscription simplifiée) ;
-- l'e-mail est optionnel (saisi au besoin sur une démarche précise).
CREATE TABLE IF NOT EXISTS citoyens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NULL,
  telephone VARCHAR(30) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Demandes de documents / services administratifs
CREATE TABLE IF NOT EXISTS demandes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code_suivi VARCHAR(30) UNIQUE NOT NULL,
  citoyen_id INT NULL,
  type_document VARCHAR(50) NOT NULL,
  civilite VARCHAR(10) NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  date_naissance DATE NULL,
  lieu_naissance VARCHAR(150) NULL,
  telephone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL,
  adresse VARCHAR(255) NULL,
  mode_retrait ENUM('guichet','postal') NOT NULL DEFAULT 'guichet',
  nombre_copies INT NOT NULL DEFAULT 1,
  details_json TEXT NULL,
  piece_jointe VARCHAR(255) NULL,
  statut ENUM('recue','en_cours','prete','rejetee') NOT NULL DEFAULT 'recue',
  note_admin TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (code_suivi),
  INDEX (statut),
  INDEX (citoyen_id),
  CONSTRAINT fk_demandes_citoyen FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Rendez-vous / audiences / doléances
CREATE TABLE IF NOT EXISTS rendezvous (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code_suivi VARCHAR(30) UNIQUE NOT NULL,
  citoyen_id INT NULL,
  objet VARCHAR(150) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  telephone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL,
  date_rdv DATE NOT NULL,
  heure_rdv TIME NOT NULL,
  motif TEXT NULL,
  statut ENUM('en_attente','confirme','annule') NOT NULL DEFAULT 'en_attente',
  note_admin TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (code_suivi),
  INDEX (date_rdv, heure_rdv),
  INDEX (citoyen_id),
  CONSTRAINT fk_rdv_citoyen FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Messages du formulaire de contact
CREATE TABLE IF NOT EXISTS messages_contact (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telephone VARCHAR(30) NULL,
  sujet VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  statut ENUM('non_lu','lu','traite') NOT NULL DEFAULT 'non_lu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (statut)
) ENGINE=InnoDB;

-- Actualités publiées par l'administrateur (avec photo)
CREATE TABLE IF NOT EXISTS actualites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(200) NOT NULL,
  tag VARCHAR(50) NOT NULL DEFAULT 'Communiqué',
  extrait VARCHAR(400) NOT NULL,
  contenu TEXT NULL,
  image VARCHAR(255) NULL,
  date_publication DATE NOT NULL,
  statut ENUM('publie','brouillon') NOT NULL DEFAULT 'publie',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (statut),
  INDEX (date_publication)
) ENGINE=InnoDB;

-- Paramètres du site, éditables depuis l'espace admin (contact, réseaux
-- sociaux, textes de l'accueil, mot du maire...). Stockage clé/valeur simple.
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('contact_telephone', '78 352 62 23'),
  ('contact_email', 'diakhatemoramina210993@gmail.com'),
  ('contact_whatsapp', '221783526223'),
  ('contact_adresse', 'Mairie de Chérif Lo, Arrondissement de Pambal, Département de Tivaouane, Sénégal'),
  ('horaires_semaine', 'Lundi – Vendredi : 08h — 17h'),
  ('horaires_samedi', 'Samedi : 09h — 13h'),
  ('horaires_dimanche', 'Dimanche : Fermé'),
  ('social_facebook', ''),
  ('social_youtube', ''),
  ('stats_villages', '18'),
  ('stats_habitants', '24 500'),
  ('stats_projets', '12'),
  ('stats_demarches_pct', '100%'),
  ('hero_titre', "Chérif Lo, {accent}une commune{/accent} tournée vers demain."),
  ('hero_description', "Département de Tivaouane · Arrondissement de Pambal. Accédez à vos démarches, suivez les projets municipaux et échangez avec votre administration en toute simplicité."),
  ('maire_nom', 'Chérif Lo'),
  ('mot_maire_citation', "« Ensemble, bâtissons une commune moderne, solidaire et fière de ses racines. »"),
  ('mot_maire_texte1', "Ce portail est le prolongement numérique de notre engagement quotidien. Il rapproche l'administration des habitants, ouvre l'accès aux services et offre à chacun la possibilité de participer à la vie de la commune."),
  ('mot_maire_texte2', "Chérif Lo est une terre de traditions et d'innovation. Nous voulons en faire un modèle de transparence et de proximité au Sénégal."),
  ('mairie_citation_longue', "« L'avenir de Chérif Lo se construit ensemble, avec fierté et responsabilité. »"),
  ('mairie_texte1', "Chers habitants, chers visiteurs, je vous souhaite la bienvenue sur le portail officiel de la commune de Chérif Lo."),
  ('mairie_texte2', "Ce site est le fruit d'une volonté forte : celle de mettre la technologie au service de la citoyenneté. Vous y trouverez toutes les informations utiles sur nos services, nos projets et nos actions."),
  ('mairie_texte3', "Je vous invite à vous en emparer, à y participer, à en faire un espace vivant de dialogue entre l'administration et les habitants.")
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Médiathèque (photos et vidéos), gérée depuis l'espace admin
CREATE TABLE IF NOT EXISTS medias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('photo','video') NOT NULL,
  titre VARCHAR(200) NOT NULL,
  fichier VARCHAR(255) NULL,
  video_url VARCHAR(500) NULL,
  duree VARCHAR(20) NULL,
  ordre INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (type)
) ENGINE=InnoDB;
