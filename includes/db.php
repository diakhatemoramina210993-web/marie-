<?php
/**
 * Connexion PDO à la base MySQL. Adapter ces constantes si besoin
 * (identifiants XAMPP par défaut : root sans mot de passe).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'cahier_lumineux');
define('DB_USER', 'root');
define('DB_PASS', '');
define('ADMIN_EMAIL', 'diakhatemoramina210993@gmail.com');
define('ADMIN_PHONE', '78 352 62 23');
define('ADMIN_PHONE_INTL', '221783526223'); // format wa.me : indicatif Sénégal + numéro, sans espaces ni 0

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(
        "<div style='font-family:sans-serif;max-width:640px;margin:60px auto;padding:24px;border:1px solid #e5b800;background:#fffceb;border-radius:12px;'>" .
        "<h2 style='margin-top:0;'>Base de données indisponible</h2>" .
        "<p>Impossible de se connecter à la base <code>" . DB_NAME . "</code>. Vérifiez que :</p>" .
        "<ul><li>MySQL est démarré dans le panneau XAMPP</li>" .
        "<li>La base a été créée en important <code>sql/schema.sql</code> (via phpMyAdmin)</li></ul>" .
        "<p style='color:#7a5b00;font-size:13px;'>Détail technique : " . htmlspecialchars($e->getMessage()) . "</p></div>"
    );
}
