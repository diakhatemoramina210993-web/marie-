<?php
/**
 * Identifiants SMTP pour l'envoi réel des e-mails (notifications citoyens,
 * accusés de réception, alertes admin). Tant que SMTP_USERNAME est vide,
 * send_notification() se contente de journaliser dans data/emails.log
 * sans tenter d'envoi réel.
 *
 * Compte Gmail : utiliser un "mot de passe d'application" (16 caractères,
 * généré depuis Compte Google > Sécurité > Validation en 2 étapes > Mots de
 * passe des applications), jamais le mot de passe normal du compte.
 */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'diakhatemoramina210993@gmail.com');
define('SMTP_PASSWORD', ''); // mot de passe d'application à 16 caractères — à compléter
define('SMTP_FROM_NAME', 'Mairie de Chérif Lo');
