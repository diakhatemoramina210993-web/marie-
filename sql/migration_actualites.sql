-- Migration : table des actualités publiées par l'administrateur.
USE cahier_lumineux;

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

-- Amorce avec les actualités déjà présentes sur le site (une seule fois)
INSERT INTO actualites (titre, tag, extrait, date_publication, statut)
SELECT * FROM (SELECT
    "Journée citoyenne de reboisement" AS titre, "Événement" AS tag,
    "500 arbres à planter dans les 18 villages de la commune. Rendez-vous à 8h à la place centrale." AS extrait,
    "2026-07-12" AS date_publication, "publie" AS statut
  UNION ALL SELECT
    "Ouverture du service d'état civil en ligne", "Communiqué",
    "Les demandes d'actes peuvent être introduites depuis la plateforme, 7j/7.",
    "2026-07-05", "publie"
  UNION ALL SELECT
    "Marché central : lancement de la phase 2", "Projet",
    "Nouvelles halles, éclairage LED et espaces sanitaires. Livraison prévue fin 2026.",
    "2026-06-28", "publie"
  UNION ALL SELECT
    "Semaine de la jeunesse et du sport", "Événement",
    "Tournois, ateliers, conférences : sept jours dédiés à la jeunesse de Chérif Lo.",
    "2026-06-20", "publie"
  UNION ALL SELECT
    "Campagne de vaccination gratuite", "Communiqué",
    "En partenariat avec le district sanitaire de Tivaouane.",
    "2026-06-10", "publie"
  UNION ALL SELECT
    "Adduction d'eau potable — village de Ndiack", "Projet",
    "Un forage moderne pour desservir plus de 2 000 habitants.",
    "2026-06-01", "publie"
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM actualites LIMIT 1);
